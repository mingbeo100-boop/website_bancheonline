<?php
// backend/insert_order.php
session_start(); 
header('Content-Type: application/json');

require_once 'connect.php'; 

if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Lỗi kết nối CSDL: ' . $conn->connect_error]);
    exit();
}

// Lấy dữ liệu POST từ JavaScript
$payment_method = isset($_POST['payment_method']) ? $_POST['payment_method'] : 'unknown';
$total_amount_from_js = isset($_POST['total_amount']) ? (float)$_POST['total_amount'] : 0.00; 

// Lấy User ID từ Session (BẮT BUỘC)
$user_id = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0; 
if ($user_id === 0) {
    // Để gỡ lỗi/test, gán cứng User ID, ví dụ: 9 (có trong dữ liệu của bạn)
    $user_id = 9; 
}

// Giả định tổng tiền cuối cùng (tạm thời dùng giá trị từ JS)
$final_total = $total_amount_from_js; 
$new_status = 'pending'; // Trạng thái ban đầu cho đơn hàng COD mới

// --- TRUY VẤN THÊM ĐƠN HÀNG MỚI (INSERT) ---
// Chỉ bao gồm 5 cột mà bảng của bạn có: user_id, order_date, total_amount, payment_method, order_status
$sql = "INSERT INTO orders 
        (user_id, order_date, total_amount, payment_method, order_status) 
        VALUES (?, NOW(), ?, ?, ?)";

if ($stmt = $conn->prepare($sql)) {
    // 💡 Kiểu dữ liệu: idss (integer, double/decimal, string, string)
    // total_amount (169.00) là DECIMAL/DOUBLE, nên dùng 'd' hoặc 's'. Ta dùng 'd' nếu total_amount là float/decimal.
    // Nếu bảng orders có total_amount là DECIMAL, dùng 'd'. Nếu là VARCHAR, dùng 's'.
    // Ta dùng 'd' cho total_amount và 's' cho hai trường còn lại.
    $stmt->bind_param("idss", 
        $user_id, 
        $final_total, 
        $payment_method, 
        $new_status
    );

    if ($stmt->execute()) {
        $last_id = $conn->insert_id; // Lấy ID của đơn hàng vừa tạo
        echo json_encode([
            'success' => true, 
            'message' => 'Đơn hàng mới đã được thêm thành công.',
            'order_id' => $last_id
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Lỗi thực thi INSERT: ' . $stmt->error]);
    }
    
    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Lỗi chuẩn bị truy vấn: ' . $conn->error]);
}

$conn->close();
?>