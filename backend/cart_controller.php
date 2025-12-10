<?php
// backend/cart_controller.php
require_once 'connect.php';
require_once 'utils.php';
require_once 'cart_actions.php';

session_start();
// Đặt header JSON ngay đầu file 
header('Content-Type: application/json');

// Tắt hiển thị lỗi PHP ra màn hình (chỉ log vào file)
ini_set('display_errors', 1);
error_reporting(E_ALL);

$user_id = $_SESSION['user_id'] ?? null;
$action = $_POST['action'] ?? $_GET['action'] ?? null;
$method = $_POST['method'] ?? null;

// 🔥 NHẬN THÔNG TIN NGƯỜI NHẬN TỪ POST
$customer_info = [
    // LƯU Ý: Frontend đã dùng encodeURIComponent, nên PHP tự động decode
    'name' => $_POST['name'] ?? '',
    'phone'  => $_POST['phone'] ?? '',
    'address' => $_POST['address'] ?? ''
];

if (!$user_id) {
    respondWithError(null, 'Vui lòng đăng nhập.', 401); 
}

// --- 3. LẤY CART ID HOẶC TẠO MỚI (Giữ nguyên logic giỏ hàng) ---
$cart_id = null; 
$stmt_cart = null; 

try {
    // --- Lấy Cart ID hiện tại ---
    $stmt_cart = $conn->prepare("SELECT cart_id FROM carts WHERE user_id = ?");
    if ($stmt_cart === false) throw new Exception("Lỗi chuẩn bị truy vấn Cart ID.");

    $stmt_cart->bind_param("i", $user_id);
    $stmt_cart->execute();
    $result_cart = $stmt_cart->get_result();

    if ($result_cart->num_rows === 0) {
        
        // --- TẠO CART MỚI ---
        $conn->begin_transaction();
        
        $stmt_insert = $conn->prepare("INSERT INTO carts (user_id) VALUES (?)");
        if ($stmt_insert === false) throw new Exception("Lỗi chuẩn bị tạo Cart mới.");

        $stmt_insert->bind_param("i", $user_id);
        if (!$stmt_insert->execute()) throw new Exception("Lỗi thực thi tạo Cart mới.");

        $cart_id = $conn->insert_id;
        $stmt_insert->close();
        
        $conn->commit(); 
        
    } else {
        // Lấy Cart ID đã tồn tại
        $cart_data = $result_cart->fetch_assoc();
        $cart_id = $cart_data['cart_id'];
    }

} catch (Exception $e) {
    $conn->rollback(); 
    respondWithError($conn, 'Lỗi hệ thống khi thiết lập giỏ hàng: ' . $e->getMessage(), 500);

} finally {
    if (isset($stmt_cart) && $stmt_cart instanceof mysqli_stmt) {
        $stmt_cart->close();
    }
}


// --- 4. GỌI ACTION TƯƠNG ỨNG ---
if ($action && $cart_id) {
    // 🔥 SỬA: Truyền đầy đủ 6 tham số, bao gồm $customer_info
    handle_cart_action($conn, $user_id, $cart_id, $action, $method, $customer_info); 
} else if (!$action) {
    respondWithError(null, 'Hành động không được chỉ định.', 400);
} else {
    respondWithError(null, 'Không thể xác định giỏ hàng của người dùng.', 500);
}

// KHÔNG CÓ THẺ ĐÓNG PHP