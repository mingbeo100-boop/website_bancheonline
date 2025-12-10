<?php
// Tên file: backend/cart_actions.php
// File này giả định $conn, $user_id, $cart_id đã được định nghĩa và respondWithError đã được include.
// 🔥 SỬA: Thêm tham số $customer_info vào định nghĩa hàm
function handle_cart_action($conn, $user_id, $cart_id, $action, $method = null, $customer_info = []) {
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
            exit;
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
            
        // --- HOÀN TẤT THANH TOÁN (CHECKOUT_COMPLETE) ---
        case 'checkout_complete':
            
            // XỬ LÝ PAYMENT METHOD (An toàn PHP < 8.0)
            $payment_method = 'Không xác định';
            if ($method === 'cod') {
                $payment_method = 'COD (Thanh toán khi nhận)';
            } elseif ($method === 'qr') {
                $payment_method = 'Chuyển khoản QR';
            }

            // Lấy thông tin người nhận từ mảng $customer_info
            $name = $customer_info['name'] ?? '';
            $phone = $customer_info['phone'] ?? '';
            $address = $customer_info['address'] ?? '';

            // Kiểm tra thông tin người nhận
            if (!$name || !$phone || !$address) {
                // Lỗi này không nên xảy ra nếu frontend đã xác nhận, nhưng là kiểm tra an toàn
                respondWithError($conn, 'Lỗi: Thiếu thông tin người nhận khi tạo đơn hàng.', 400);
            }

            $conn->begin_transaction();
            try {
                // 1. Tính tổng tiền
                $stmt_total = $conn->prepare("SELECT SUM(ci.quantity * p.price) AS total FROM cart_items ci JOIN products p ON ci.product_id = p.product_id WHERE ci.cart_id = ?");
                $stmt_total->bind_param("i", $cart_id);
                $stmt_total->execute();
                $total_amount = $stmt_total->get_result()->fetch_assoc()['total'] ?? 0;
                $stmt_total->close();

                if ($total_amount <= 0) throw new Exception("Giỏ hàng rỗng.");
                
                // 2. TẠO ĐƠN HÀNG (INSERT vào orders với 3 cột địa chỉ)
               $sql_order = "INSERT INTO orders (user_id, total_amount, payment_method, order_date, recipient_name, recipient_phone, shipping_address, order_status) VALUES (?, ?, ?, NOW(), ?, ?, ?, 'pending')";
                $stmt_order = $conn->prepare($sql_order);
                
                // 🔥 SỬA LỖI FATAL: CHUỖI BIND CÓ 6 KÝ TỰ - "idssss"
                // i (user_id), d (total_amount), s (payment_method), s (name), s (phone), s (address)
                $stmt_order->bind_param("idssss", $user_id, $total_amount, $payment_method, $name, $phone, $address);
                
                if (!$stmt_order->execute()) throw new Exception("Lỗi thực thi tạo đơn: " . $stmt_order->error);
                
                $new_order_id = $conn->insert_id;
                
                // Đóng Statement INSERT
                $stmt_order->close();
                // 3. Cập nhật Mã đơn hàng (AEKH-...)
                $order_code = 'AEKH-' . date('ymd') . '-' . str_pad($new_order_id, 4, '0', STR_PAD_LEFT);
                $conn->query("UPDATE orders SET order_code = '$order_code' WHERE order_id = $new_order_id");

                // 4. COPY SẢN PHẨM TỪ GIỎ SANG ORDER_DETAILS
                $sql_copy = "INSERT INTO order_details (order_id, product_id, quantity, price_at_purchase)
                             SELECT ?, ci.product_id, ci.quantity, p.price FROM cart_items ci JOIN products p ON ci.product_id = p.product_id WHERE ci.cart_id = ?";
                $stmt_copy = $conn->prepare($sql_copy);
                $stmt_copy->bind_param("ii", $new_order_id, $cart_id);
                $stmt_copy->execute();
                $stmt_copy->close();
                
                // 5. Xóa sạch giỏ hàng cũ
                $conn->query("DELETE FROM cart_items WHERE cart_id = $cart_id");

                $conn->commit();
                
                // TRẢ VỀ JSON THÀNH CÔNG VÀ DỪNG SCRIPT
                echo json_encode(['success' => true, 'order_id' => $new_order_id, 'order_code' => $order_code]);
                exit; 

            } catch (Exception $e) {
                $conn->rollback();
                respondWithError($conn, 'Lỗi hoàn tất thanh toán: ' . $e->getMessage());
            }
            break;
            
        default:
             respondWithError($conn, 'Hành động không hợp lệ.', 400);
    }
}