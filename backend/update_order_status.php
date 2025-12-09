<?php
session_start();
header('Content-Type: application/json');

require_once 'connect.php'; 

// Kiểm tra quyền Admin (CHỈ ADMIN MỚI ĐƯỢC CẬP NHẬT)
$is_admin = false;
if (isset($_SESSION['username'])) {
    $username = strtolower($_SESSION['username']);
    if ($username === 'admin1' || $username === 'admin2') {
        $is_admin = true;
    }
}

if (!$is_admin) {
    echo json_encode(['success' => false, 'error' => 'Bạn không có quyền cập nhật trạng thái đơn hàng.']);
    exit();
}

if ($conn->connect_error) {
    echo json_encode(['success' => false, 'error' => 'Kết nối database thất bại.']);
    exit();
}

// Kiểm tra dữ liệu POST
if (!isset($_POST['order_id']) || !isset($_POST['new_status'])) {
    echo json_encode(['success' => false, 'error' => 'Thiếu dữ liệu: order_id hoặc new_status.']);
    $conn->close();
    exit();
}

$order_id = (int)$_POST['order_id'];
$new_status = $_POST['new_status']; 

// Đảm bảo trạng thái hợp lệ để tránh SQL Injection
$allowed_statuses = ['pending', 'processing', 'delivered', 'cancelled'];
if (!in_array(strtolower($new_status), $allowed_statuses)) {
    echo json_encode(['success' => false, 'error' => 'Trạng thái mới không hợp lệ.']);
    $conn->close();
    exit();
}

// --- PREPARED STATEMENT CẬP NHẬT ---
$sql = "UPDATE orders SET order_status = ? WHERE order_id = ?";

if ($stmt = $conn->prepare($sql)) {
    // 'si' nghĩa là string (new_status) và integer (order_id)
    $stmt->bind_param("si", $new_status, $order_id); 
    
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            echo json_encode(['success' => true, 'message' => "Cập nhật đơn hàng $order_id thành công."]);
        } else {
            echo json_encode(['success' => false, 'error' => "Không tìm thấy đơn hàng $order_id hoặc trạng thái không thay đổi."]);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'Lỗi thực thi truy vấn: ' . $stmt->error]);
    }
    
    $stmt->close();
} else {
    echo json_encode(['success' => false, 'error' => 'Lỗi chuẩn bị truy vấn SQL: ' . $conn->error]);
}

$conn->close();
?>