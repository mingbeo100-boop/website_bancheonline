<?php
// Tên file: backend/cart_actions.php
// File này giả định $conn, $user_id, $cart_id đã được định nghĩa và respondWithError đã được include.

// 🔥 SỬA: Thêm tham số $method vào định nghĩa hàm
function handle_cart_action($conn, $user_id, $cart_id, $action, $method = null) {
    // Kích hoạt Strict Reporting để try...catch bắt được lỗi SQL (quan trọng)
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

    switch ($action) {
        
        // --- LẤY DỮ LIỆU GIỎ HÀNG (GIỮ NGUYÊN) ---
        case 'get_cart':
            $sql = "
                SELECT ci.quantity, p.product_id, p.name, p.price 
                FROM cart_items ci
                JOIN products p ON ci.product_id = p.product_id
                WHERE ci.cart_id = ?
            ";
            $stmt = $conn->prepare($sql);
            if ($stmt === false) respondWithError($conn, 'Lỗi chuẩn bị lấy giỏ hàng.');
            
            $stmt->bind_param("i", $cart_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $items = $result->fetch_all(MYSQLI_ASSOC);
            $stmt->close();

            
            echo json_encode(['success' => true, 'items' => $items]);
            break;
            
        // --- THÊM SẢN PHẨM VÀO GIỎ (GIỮ NGUYÊN) ---
        case 'add_to_cart':
            $product_id = $_POST['product_id'] ?? null;
            $quantity = $_POST['quantity'] ?? 1;

            if (!$product_id || $quantity < 1) { respondWithError(null, 'Dữ liệu không hợp lệ.'); }
            
            // ÉP KIỂU SANG SỐ NGUYÊN
            $product_id = (int) $product_id; 
            $quantity = (int) $quantity;
            
            $conn->begin_transaction();
            try {
                // 1. Kiểm tra sản phẩm đã có trong giỏ chưa
                $stmt_check = $conn->prepare("SELECT cart_item_id, quantity FROM cart_items WHERE cart_id = ? AND product_id = ?");
                if ($stmt_check === false) throw new Exception("Lỗi chuẩn bị kiểm tra item.");
                
                $stmt_check->bind_param("ii", $cart_id, $product_id);
                $stmt_check->execute();
                $existing_item = $stmt_check->get_result()->fetch_assoc();
                $stmt_check->close();

                if ($existing_item) {
                    // 2a. Nếu đã có: Cập nhật số lượng
                    $new_quantity = $existing_item['quantity'] + $quantity;
                    $stmt_update = $conn->prepare("UPDATE cart_items SET quantity = ? WHERE cart_item_id = ?");
                    if ($stmt_update === false) throw new Exception("Lỗi chuẩn bị update item.");
                    
                    $stmt_update->bind_param("ii", $new_quantity, $existing_item['cart_item_id']);
                    if (!$stmt_update->execute()) throw new Exception("Lỗi thực thi update item.");
                    $stmt_update->close();
                } else {
                    // 2b. Nếu chưa có: Thêm mới
                    $stmt_insert = $conn->prepare("INSERT INTO cart_items (cart_id, product_id, quantity) VALUES (?, ?, ?)");
                    if ($stmt_insert === false) throw new Exception("Lỗi chuẩn bị insert item.");
                    
                    $stmt_insert->bind_param("iii", $cart_id, $product_id, $quantity);
                    if (!$stmt_insert->execute()) throw new Exception("Lỗi thực thi insert item.");
                    $stmt_insert->close();
                }
                
                $conn->commit();
                echo json_encode(['success' => true, 'message' => 'Đã thêm sản phẩm vào giỏ hàng thành công.']);

            } catch (Exception $e) {
                $conn->rollback();
                respondWithError($conn, 'Lỗi xử lý giỏ hàng: ' . $e->getMessage());
            }
            break;

        // --- CẬP NHẬT SỐ LƯỢNG (GIỮ NGUYÊN) ---
        case 'update_quantity':
            $product_id = $_POST['product_id'] ?? null;
            $new_quantity = $_POST['quantity'] ?? null;

            if (!$product_id || $new_quantity === null || $new_quantity < 1) { respondWithError(null, 'Dữ liệu không hợp lệ.'); }

            $new_quantity = (int) $new_quantity;
            $product_id = (int) $product_id;

            $conn->begin_transaction();
            try {
                $sql = "
                    UPDATE cart_items ci
                    SET ci.quantity = ? 
                    WHERE ci.cart_id = ? AND ci.product_id = ?
                ";
                $stmt_update = $conn->prepare($sql);
                if ($stmt_update === false) throw new Exception("Lỗi chuẩn bị update quantity.");

                // Cần đảm bảo thứ tự bind_param: quantity, cart_id, product_id
                $stmt_update->bind_param("iii", $new_quantity, $cart_id, $product_id); 
                if (!$stmt_update->execute()) throw new Exception("Lỗi thực thi update quantity.");
                $stmt_update->close();

                $conn->commit();
                echo json_encode(['success' => true, 'message' => 'Cập nhật số lượng thành công.']);
            } catch (Exception $e) {
                $conn->rollback();
                respondWithError($conn, 'Lỗi cập nhật số lượng: ' . $e->getMessage());
            }
            break;

        // --- XÓA SẢN PHẨM (GIỮ NGUYÊN) ---
        case 'remove_item':
            $product_id = $_POST['product_id'] ?? null;
            
            if (!$product_id) { respondWithError(null, 'Thiếu ID sản phẩm.'); }

            $product_id = (int) $product_id;
            
            $conn->begin_transaction();
            try {
                $sql = "
                    DELETE FROM cart_items
                    WHERE cart_id = ? AND product_id = ?
                ";
                $stmt_delete = $conn->prepare($sql);
                if ($stmt_delete === false) throw new Exception("Lỗi chuẩn bị delete item.");

                $stmt_delete->bind_param("ii", $cart_id, $product_id);
                if (!$stmt_delete->execute()) throw new Exception("Lỗi thực thi delete item.");
                $stmt_delete->close();

                $conn->commit();
                echo json_encode(['success' => true, 'message' => 'Xóa sản phẩm thành công.']);
            } catch (Exception $e) {
                $conn->rollback();
                respondWithError($conn, 'Lỗi xóa sản phẩm: ' . $e->getMessage());
            }
            break;
            
        // --- HOÀN TẤT THANH TOÁN ---
        case 'checkout_complete':
        
            // LOGIC: Chuyển đổi method từ JS ('cod', 'qr') sang tên hiển thị
            $payment_method = match ($method) {
                'cod' => 'COD (Cash on Delivery)',
                'qr' => 'QR/Bank Transfer',
                default => 'Unknown' // Đảm bảo luôn có giá trị
            };
            
            // Bắt đầu giao dịch (Transaction)
            $conn->begin_transaction();
            try {
                // 1. KHÔI PHỤC VÀ TÍNH TỔNG SỐ TIỀN CỦA ĐƠN HÀNG
                $sql_total = "
                    SELECT SUM(ci.quantity * p.price) AS total_amount
                    FROM cart_items ci
                    JOIN products p ON ci.product_id = p.product_id
                    WHERE ci.cart_id = ?
                ";
                
                $stmt_total = $conn->prepare($sql_total);
                if ($stmt_total === false) throw new Exception("Lỗi chuẩn bị tính tổng tiền.");
                
                $stmt_total->bind_param("i", $cart_id);
                $stmt_total->execute();
                $result = $stmt_total->get_result();
                $row = $result->fetch_assoc();
                $total_amount = $row['total_amount'] ?? 0;
                $stmt_total->close();

                // Kiểm tra nếu tổng tiền là 0
                if ($total_amount <= 0) {
                    throw new Exception("Giỏ hàng rỗng hoặc tổng tiền không hợp lệ. Đơn hàng không được tạo.");
                }
                
                // 2. GHI BẢN GHI MỚI VÀO BẢNG ORDERS
                $sql_insert_order = "
                    INSERT INTO orders (user_id, total_amount, payment_method, order_date)
                    VALUES (?, ?, ?, NOW())
                ";
                
                $stmt_insert = $conn->prepare($sql_insert_order);
                if ($stmt_insert === false) throw new Exception("Lỗi chuẩn bị ghi vào bảng orders.");
                
                // Bind: user_id (i), total_amount (d), payment_method (s)
                $stmt_insert->bind_param("ids", $user_id, $total_amount, $payment_method);
                if (!$stmt_insert->execute()) throw new Exception("Lỗi thực thi ghi vào bảng orders.");
                $stmt_insert->close();

                // LẤY ORDER_ID (ID tự tăng) VÀ SINH ORDER_CODE TÙY CHỈNH
                $new_order_id = $conn->insert_id; // Lấy ID tự tăng
                $order_code = 'AEKH-' . date('ymd') . '-' . str_pad($new_order_id, 4, '0', STR_PAD_LEFT);

                // 3. CẬP NHẬT ORDER_CODE CHO BẢN GHI VỪA TẠO
                $sql_update_code = "
                    UPDATE orders 
                    SET order_code = ? 
                    WHERE order_id = ?
                ";
                $stmt_update_code = $conn->prepare($sql_update_code);
                if ($stmt_update_code === false) throw new Exception("Lỗi chuẩn bị update order_code.");
                
                $stmt_update_code->bind_param("si", $order_code, $new_order_id);
                if (!$stmt_update_code->execute()) throw new Exception("Lỗi thực thi update order_code.");
                $stmt_update_code->close();
                
                // 4. 🔥 SỬA LỖI: CẬP NHẬT TRẠNG THÁI GIỎ HÀNG CŨ (Đóng Giỏ hàng)
                // ĐÃ XÓA DẤU PHẨY VÀ CỘT updated_at
                $sql_update_cart_status = "
                    UPDATE carts 
                    SET status = 'completed'
                    WHERE cart_id = ?
                ";
                
                $stmt_update_status = $conn->prepare($sql_update_cart_status);
                if ($stmt_update_status === false) throw new Exception("Lỗi chuẩn bị update cart status.");
                $stmt_update_status->bind_param("i", $cart_id);
                if (!$stmt_update_status->execute()) throw new Exception("Lỗi thực thi update cart status.");
                $stmt_update_status->close();

                // 5. XÓA TẤT CẢ ITEMS TRONG GIỎ HÀNG HIỆN TẠI (Làm sạch giỏ)
                $stmt_delete_items = $conn->prepare("DELETE FROM cart_items WHERE cart_id = ?");
                if ($stmt_delete_items === false) throw new Exception("Lỗi chuẩn bị xóa chi tiết giỏ hàng.");
                
                $stmt_delete_items->bind_param("i", $cart_id);
                if (!$stmt_delete_items->execute()) throw new Exception("Lỗi thực thi xóa chi tiết giỏ hàng.");
                $stmt_delete_items->close();

                // 6. HOÀN TẤT VÀ PHẢN HỒI
                $conn->commit();
                
                echo json_encode([
                    'success' => true, 
                    'message' => 'Thanh toán thành công. Đơn hàng đã được tạo và giỏ hàng đã được làm sạch.',
                    // TRẢ VỀ MÃ ĐƠN HÀNG TÙY CHỈNH CHO FRONTEND
                    'order_id' => $new_order_id, // Giữ lại ID tự tăng (Dùng cho debug)
                    'order_code' => $order_code 
                ]);
                
            } catch (Exception $e) {
                $conn->rollback();
                respondWithError($conn, 'Lỗi hoàn tất thanh toán: ' . $e->getMessage()); 
            }
            break;
    }
}