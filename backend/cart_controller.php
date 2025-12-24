<?php
// backend/cart_controller.php
require_once 'connect.php';
require_once 'utils.php';
require_once 'cart_actions.php';

// Đảm bảo không có khoảng trắng nào trước thẻ mở PHP
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Đặt header JSON ngay đầu file
header('Content-Type: application/json');

// Tắt hiển thị lỗi trực tiếp ra màn hình để tránh làm hỏng JSON
ini_set('display_errors', 0); 
error_reporting(E_ALL);

$user_id = $_SESSION['user_id'] ?? null;
$action = $_POST['action'] ?? $_GET['action'] ?? null;
$method = $_POST['method'] ?? null;

// Nhận thông tin khách hàng
$customer_info = [
    'name' => $_POST['name'] ?? '',
    'phone'  => $_POST['phone'] ?? '',
    'address' => $_POST['address'] ?? ''
];

// Hàm bổ trợ trả về JSON nhanh để tránh lỗi 400/500
function sendJsonResponse($success, $message, $code = 200) {
    http_response_code($code);
    echo json_encode([
        'success' => $success,
        'message' => $message
    ]);
    exit;
}

if (!$user_id) {
    sendJsonResponse(false, 'Vui lòng đăng nhập.', 401); 
}

$cart_id = null; 
$stmt_cart = null; 

try {
    // 1. Lấy Cart ID hiện tại
    $stmt_cart = $conn->prepare("SELECT cart_id FROM carts WHERE user_id = ? LIMIT 1");
    if ($stmt_cart === false) throw new Exception("Lỗi chuẩn bị truy vấn Cart ID.");

    $stmt_cart->bind_param("i", $user_id);
    $stmt_cart->execute();
    $result_cart = $stmt_cart->get_result();

    if ($result_cart->num_rows === 0) {
        // 2. TẠO CART MỚI NẾU CHƯA CÓ
        $conn->begin_transaction();
        
        $stmt_insert = $conn->prepare("INSERT INTO carts (user_id) VALUES (?)");
        if ($stmt_insert === false) throw new Exception("Lỗi chuẩn bị tạo Cart mới.");

        $stmt_insert->bind_param("i", $user_id);
        if (!$stmt_insert->execute()) throw new Exception("Lỗi thực thi tạo Cart mới.");

        $cart_id = $conn->insert_id;
        $stmt_insert->close();
        $conn->commit(); 
    } else {
        $cart_data = $result_cart->fetch_assoc();
        $cart_id = $cart_data['cart_id'];
    }
    $stmt_cart->close();

    // 3. GỌI ACTION TƯƠNG ỨNG
    if ($action && $cart_id) {
        // Truyền đầy đủ 6 tham số vào hàm xử lý
        handle_cart_action($conn, $user_id, $cart_id, $action, $method, $customer_info); 
    } else if (!$action) {
        sendJsonResponse(false, 'Hành động không được chỉ định.', 400);
    } else {
        sendJsonResponse(false, 'Không thể xác định giỏ hàng.', 500);
    }

} catch (Exception $e) {
   
        $conn->rollback(); 
    
    sendJsonResponse(false, 'Lỗi hệ thống: ' . $e->getMessage(), 500);
}