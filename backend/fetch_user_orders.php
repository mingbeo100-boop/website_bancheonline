<?php
// BẮT ĐẦU SESSION ĐỂ LẤY USER_ID SAU KHI ĐĂNG NHẬP
session_start(); 

// Thiết lập header để trả về JSON
header('Content-Type: application/json');

// --- 1. SỬ DỤNG TỆP KẾT NỐI CỦA BẠN ---
require_once 'connect.php'; 

// Kiểm tra lỗi kết nối ngay lập tức
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'error' => 'Kết nối database thất bại: ' . $conn->connect_error]);
    exit();
}

// --- 2. LOGIC LẤY USER_ID VÀ KIỂM TRA ADMIN (Giữ nguyên) ---
$user_id = null;
$is_admin = false;

// A. Kiểm tra Admin (Ưu tiên)
if (isset($_SESSION['username'])) {
    $username = strtolower($_SESSION['username']);
    if ($username === 'admin1' || $username === 'admin2') {
        $is_admin = true;
    }
}

// B. Lấy User ID (Nếu không phải Admin)
if (!$is_admin) {
    // Lấy từ Session (Cách bảo mật và chuẩn nhất sau khi đăng nhập)
    if (isset($_SESSION['user_id']) && (int)$_SESSION['user_id'] > 0) {
        $user_id = (int)$_SESSION['user_id'];
    }

    // Lấy từ POST (Nếu Session chưa có, dùng dữ liệu gửi từ AJAX)
    if (!$user_id && isset($_POST['user_id'])) {
        $user_id = (int)$_POST['user_id'];
    }

    // C. BUỘC GÁN GIÁ TRỊ CÓ ĐƠN HÀNG (CHỈ DÙNG KHI GỠ LỖI - Vẫn giữ để test)
    if (!$user_id) {
        $user_id = 27; 
    }
}

// Kiểm tra nếu không phải Admin và cũng không có User ID
if (!$is_admin && !$user_id) {
    echo json_encode(['success' => false, 'error' => 'Không tìm thấy User ID hợp lệ hoặc không phải Admin.']);
    $conn->close();
    exit();
}

// --- 3. PREPARED STATEMENT (TRUY VẤN AN TOÀN) ---

// 🔥 SỬA: Thêm cột order_code vào truy vấn SELECT cho cả hai trường hợp

$sql_select = "SELECT order_id, order_code, order_date, total_amount, order_status 
           FROM orders 
           ORDER BY order_date DESC";

// Nếu KHÔNG phải Admin, thêm điều kiện WHERE
if (!$is_admin) {
    $sql_select = "SELECT order_id, order_code, order_date, total_amount, order_status 
               FROM orders 
               WHERE user_id = ? 
               ORDER BY order_date DESC";
}

$orders = [];

if ($stmt = $conn->prepare($sql_select)) {
    // Chỉ bind_param nếu KHÔNG phải Admin
    if (!$is_admin) {
        // 'i' nghĩa là kiểu integer (số nguyên) cho user_id
        $stmt->bind_param("i", $user_id); 
    }
    
    $stmt->execute();
    
    $result = $stmt->get_result();
    
    while($row = $result->fetch_assoc()) {
        $orders[] = $row;
    }
    
    $stmt->close();
    
    // TRẢ VỀ KẾT QUẢ THÀNH CÔNG 
    $debug_id = $is_admin ? 'ADMIN' : $user_id;
    echo json_encode(['success' => true, 'orders' => $orders, 'is_admin' => $is_admin, 'debug_id' => $debug_id]);

} else {
    // Xử lý lỗi chuẩn bị truy vấn
    echo json_encode(['success' => false, 'error' => 'Lỗi chuẩn bị truy vấn SQL: ' . $conn->error]);
}

// --- 4. ĐÓNG KẾT NỐI ---
$conn->close();
?>