<?php
// backend/cart_actions.php

function handle_cart_action($conn, $user_id, $cart_id, $action, $method = null, $customer_info = []) {
    // Kích hoạt báo lỗi nghiêm ngặt cho MySQLi để dễ debug
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

    switch ($action) {
        
        // --- LẤY DỮ LIỆU GIỎ HÀNG ---
        case 'get_cart':
            $sql = "
                SELECT ci.quantity, p.product_id, p.name, p.price 
                FROM cart_items ci
                JOIN products p ON ci.product_id = p.product_id
                WHERE ci.cart_id = ?
            ";
            $stmt = $conn->prepare($sql);
            if (!$stmt) respondWithError($conn, 'Lỗi truy vấn giỏ hàng.');
            
            $stmt->bind_param("i", $cart_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $items = $result->fetch_all(MYSQLI_ASSOC);
            $stmt->close();

            echo json_encode(['success' => true, 'items' => $items]);
            exit;

        // --- THÊM SẢN PHẨM VÀO GIỎ ---
        case 'add_to_cart':
            $product_id = (int)($_POST['product_id'] ?? 0);
            $quantity = (int)($_POST['quantity'] ?? 1);

            if ($product_id <= 0 || $quantity < 1) { 
                respondWithError(null, 'Dữ liệu sản phẩm không hợp lệ.'); 
            }
            
            $conn->begin_transaction();
            try {
                $stmt_check = $conn->prepare("SELECT cart_item_id, quantity FROM cart_items WHERE cart_id = ? AND product_id = ?");
                $stmt_check->bind_param("ii", $cart_id, $product_id);
                $stmt_check->execute();
                $existing_item = $stmt_check->get_result()->fetch_assoc();
                $stmt_check->close();

                if ($existing_item) {
                    $new_quantity = $existing_item['quantity'] + $quantity;
                    $stmt_update = $conn->prepare("UPDATE cart_items SET quantity = ? WHERE cart_item_id = ?");
                    $stmt_update->bind_param("ii", $new_quantity, $existing_item['cart_item_id']);
                    $stmt_update->execute();
                    $stmt_update->close();
                } else {
                    $stmt_insert = $conn->prepare("INSERT INTO cart_items (cart_id, product_id, quantity) VALUES (?, ?, ?)");
                    $stmt_insert->bind_param("iii", $cart_id, $product_id, $quantity);
                    $stmt_insert->execute();
                    $stmt_insert->close();
                }
                
                $conn->commit();
                echo json_encode(['success' => true, 'message' => 'Đã thêm vào giỏ hàng.']);
                exit;
            } catch (Exception $e) {
                $conn->rollback();
                respondWithError($conn, 'Lỗi: ' . $e->getMessage());
            }
            break;

        // --- CẬP NHẬT SỐ LƯỢNG ---
case 'update_quantity':
            $product_id = (int)($_POST['product_id'] ?? 0);
            $new_quantity = (int)($_POST['quantity'] ?? 0);

            if ($product_id <= 0 || $new_quantity < 1) { 
                respondWithError(null, 'Số lượng không hợp lệ.'); 
            }

            $conn->begin_transaction();
            try {
                $stmt_update = $conn->prepare("UPDATE cart_items SET quantity = ? WHERE cart_id = ? AND product_id = ?");
                $stmt_update->bind_param("iii", $new_quantity, $cart_id, $product_id); 
                $stmt_update->execute();
                $stmt_update->close();

                $conn->commit();
                echo json_encode(['success' => true, 'message' => 'Đã cập nhật số lượng.']);
                exit;
            } catch (Exception $e) {
                $conn->rollback();
                respondWithError($conn, 'Lỗi cập nhật: ' . $e->getMessage());
            }
            break;

        // --- XÓA SẢN PHẨM ---
        case 'remove_item':
            $product_id = (int)($_POST['product_id'] ?? 0);
            if ($product_id <= 0) respondWithError(null, 'Thiếu ID sản phẩm.');

            $conn->begin_transaction();
            try {
                $stmt_delete = $conn->prepare("DELETE FROM cart_items WHERE cart_id = ? AND product_id = ?");
                $stmt_delete->bind_param("ii", $cart_id, $product_id);
                $stmt_delete->execute();
                $stmt_delete->close();

                $conn->commit();
                echo json_encode(['success' => true, 'message' => 'Đã xóa sản phẩm.']);
                exit;
            } catch (Exception $e) {
                $conn->rollback();
                respondWithError($conn, 'Lỗi xóa: ' . $e->getMessage());
            }
            break;
            
        // --- HOÀN TẤT THANH TOÁN (CHECKOUT_COMPLETE) ---
        case 'checkout_complete':
            $payment_method = ($method === 'cod') ? 'COD (Thanh toán khi nhận)' : (($method === 'qr') ? 'Chuyển khoản QR' : 'Khác');

            $name = trim($customer_info['name'] ?? '');
            $phone = trim($customer_info['phone'] ?? '');
            $address = trim($customer_info['address'] ?? '');

            if (empty($name) || empty($phone) || empty($address)) {
                respondWithError(null, 'Thiếu thông tin người nhận đơn hàng.', 400);
            }

            $conn->begin_transaction();
            try {
                // 1. Tính tổng tiền
                $stmt_total = $conn->prepare("SELECT SUM(ci.quantity * p.price) AS total FROM cart_items ci JOIN products p ON ci.product_id = p.product_id WHERE ci.cart_id = ?");
                $stmt_total->bind_param("i", $cart_id);
                $stmt_total->execute();
                $total_res = $stmt_total->get_result()->fetch_assoc();
                $total_amount = $total_res['total'] ?? 0;
                $stmt_total->close();
if ($total_amount <= 0) throw new Exception("Giỏ hàng của bạn đang trống.");
                
                // 2. Tạo Đơn hàng
                $sql_order = "INSERT INTO orders (user_id, total_amount, payment_method, order_date, recipient_name, recipient_phone, shipping_address, order_status) VALUES (?, ?, ?, NOW(), ?, ?, ?, 'pending')";
                $stmt_order = $conn->prepare($sql_order);
                $stmt_order->bind_param("idssss", $user_id, $total_amount, $payment_method, $name, $phone, $address);
                $stmt_order->execute();
                
                $new_order_id = $conn->insert_id;
                $stmt_order->close();

                // 3. Tạo Mã đơn hàng
                $order_code = 'AEKH-' . date('ymd') . '-' . str_pad($new_order_id, 4, '0', STR_PAD_LEFT);
                $conn->query("UPDATE orders SET order_code = '$order_code' WHERE order_id = $new_order_id");

                // 4. Lưu chi tiết đơn hàng
                $sql_copy = "INSERT INTO order_details (order_id, product_id, quantity, price_at_purchase)
                             SELECT ?, ci.product_id, ci.quantity, p.price 
                             FROM cart_items ci 
                             JOIN products p ON ci.product_id = p.product_id 
                             WHERE ci.cart_id = ?";
                $stmt_copy = $conn->prepare($sql_copy);
                $stmt_copy->bind_param("ii", $new_order_id, $cart_id);
                $stmt_copy->execute();
                $stmt_copy->close();
                
                // 5. Xóa giỏ hàng sau khi thanh toán
                $conn->query("DELETE FROM cart_items WHERE cart_id = $cart_id");

                $conn->commit();
                echo json_encode(['success' => true, 'order_id' => $new_order_id, 'order_code' => $order_code]);
                exit; 

            } catch (Exception $e) {
                $conn->rollback();
                respondWithError($conn, 'Lỗi thanh toán: ' . $e->getMessage());
            }
            break;
            
        default:
             respondWithError($conn, 'Hành động không xác định.', 400);
    }
}
