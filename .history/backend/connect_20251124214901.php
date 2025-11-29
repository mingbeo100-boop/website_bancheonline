<?php
$servername = "localhost";
$username = "root"; // Mặc định của XAMPP/WAMP là root
$password = ""; // Mặc định thường để trống
$dbname = "login_aecaykhe";

// Tạo kết nối
$conn = new mysqli($servername, $username, $password, $dbname);

// Kiểm tra kết nối
if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}
// Thiết lập bảng mã tiếng Việt
mysqli_set_charset($conn, 'UTF8');
?>