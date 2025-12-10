<?php
// backend/utils.php
// Hàm trả về JSON lỗi và dừng chương trình
// 🔥 SỬA: Đổi giá trị mặc định của $http_code từ 200 thành 400 (Bad Request)
function respondWithError($conn, $message, $http_code = 400) {
    
    // Đảm bảo Content-Type đã được đặt ở file Controller (Chúng ta không đặt lại ở đây)
    
    // Chỉ thêm lỗi chi tiết từ DB nếu tồn tại
    if ($conn && $conn->error) {
        // Ghi log lỗi chi tiết, nhưng không gửi lỗi DB ra ngoài cho người dùng cuối
        error_log("Lỗi DB chi tiết: " . $conn->error); 
    }
    
    http_response_code($http_code);
    
    // Gửi JSON lỗi cho Frontend
    echo json_encode(['success' => false, 'message' => $message]);
    
    // 🔥 QUAN TRỌNG: DỪNG CHƯƠNG TRÌNH NGAY LẬP TỨC
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

// KHÔNG CÓ THẺ ĐÓNG PHP Ở CUỐI FILE.