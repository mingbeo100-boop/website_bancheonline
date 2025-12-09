<?php
// backend/utils.php

// Hàm trả về JSON lỗi và dừng chương trình
function respondWithError($conn, $message, $http_code = 200) {
    // Đảm bảo không có output nào trước JSON header
    header('Content-Type: application/json');
    
    // Chỉ thêm lỗi chi tiết từ DB nếu tồn tại
    if ($conn && $conn->error) {
        $message .= " Lỗi DB: " . $conn->error;
    }
    http_response_code($http_code);
    echo json_encode(['success' => false, 'message' => $message]);
    exit;
}
function slugify($text) {
    // 1. Loại bỏ dấu tiếng Việt
    $text = iconv('UTF-8', 'ASCII//TRANSLIT', $text);
    
    // 2. Chuyển sang chữ thường
    $text = strtolower($text);
    
    // 3. Loại bỏ ký tự không phải chữ cái, số, hoặc dấu gạch ngang/khoảng trắng
    $text = preg_replace('/[^a-z0-9\s-]/', '', $text);
    
    // 4. Thay thế khoảng trắng bằng dấu gạch ngang
    $text = preg_replace('/[\s-]+/', '-', $text);
    
    // 5. Cắt bỏ dấu gạch ngang ở đầu và cuối
    $text = trim($text, '-');
    
    return $text;
}

// ... (Các hàm tiện ích khác) ...

?>
