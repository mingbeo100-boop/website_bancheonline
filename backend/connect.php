<?php
// Bạn có thể đặt tên tệp này là 'db_connect.php'

$servername = "localhost";
$username = "root";
$password = ""; 
$dbname = "dacs2";

// Tạo kết nối
$conn = new mysqli($servername, $username, $password, $dbname);

// Kiểm tra kết nối
if ($conn->connect_error) {
    // Không dùng die() vì chúng ta muốn trả về JSON lỗi trong tệp chính.
    // Thay vào đó, chúng ta có thể kiểm tra $conn->connect_error trong tệp chính.
    // Hoặc chỉ thiết lập biến kết nối.
    // Trong ví dụ này, tôi giữ nguyên logic của bạn nhưng không dùng die() để xử lý lỗi tốt hơn ở tệp chính.
}

// Thiết lập bảng mã tiếng Việt
if ($conn && !$conn->connect_error) {
    mysqli_set_charset($conn, 'UTF8');
}
?>