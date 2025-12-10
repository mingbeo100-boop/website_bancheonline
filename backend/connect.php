<?php
// Tên tệp: backend/connect.php
$servername = "localhost";
$username = "root";
$password = ""; 
$dbname = "dacs2";

// Tắt báo cáo lỗi MySQLi ở đây để không tạo output thừa
mysqli_report(MYSQLI_REPORT_OFF);

// Tạo kết nối
$conn = new mysqli($servername, $username, $password, $dbname);

// Kiểm tra kết nối
if ($conn->connect_error) {
    // Chúng ta không in ra lỗi gì ở đây.
    // Lỗi sẽ được kiểm tra và trả về JSON an toàn trong các file controller (ví dụ: cart_controller.php)
    // bằng cách kiểm tra biến $conn->connect_error.
} else {
    // Thiết lập bảng mã tiếng Việt
    $conn->set_charset('utf8');
}

// KHÔNG CÓ THẺ ĐÓNG PHP.