<?php
session_start(); // LUÔN LUÔN gọi session_start() ở đầu file
require 'connect.php'; // Gọi file kết nối database

$login_page_url = '../index.php?page=dangnhap'; // URL chuyển hướng về trang đăng nhập

if (isset($_POST['btn-login'])) {
    $username_input = $_POST['username'] ?? '';
    $password_input = $_POST['password'] ?? '';

    // Xóa tất cả cờ hiệu SweetAlert2 cũ trước khi xử lý
    unset($_SESSION['show_login_success']);
    unset($_SESSION['show_login_error']);
    unset($_SESSION['show_register_success']); // Có thể từ trang đăng ký
    unset($_SESSION['show_register_error']);   // Có thể từ trang đăng ký
    unset($_SESSION['swal_message']); // Message chung nếu bạn muốn

    // 1. Kiểm tra dữ liệu đầu vào rỗng
    if (empty($username_input) || empty($password_input)) {
        $_SESSION['show_login_error'] = true;
        $_SESSION['swal_message'] = "Vui lòng nhập đầy đủ tên đăng nhập và mật khẩu!";
        header("Location: " . $login_page_url);
        exit;
    }

    // 2. Tìm user trong database
    $sql = "SELECT NAME, USERNAME, PASSWORD FROM users WHERE USERNAME = ?";
    $stmt = $conn->prepare($sql);

    if ($stmt === false) {
        $_SESSION['show_login_error'] = true;
        $_SESSION['swal_message'] = "Lỗi hệ thống: Không thể chuẩn bị truy vấn. Vui lòng thử lại sau.";
        header("Location: " . $login_page_url);
        exit;
    }

    $stmt->bind_param("s", $username_input);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        $stmt->close();
        $_SESSION['show_login_error'] = true;
        $_SESSION['swal_message'] = "Tên đăng nhập không tồn tại!";
        header("Location: " . $login_page_url);
        exit;
    }

    $row = $result->fetch_assoc();
    $stmt->close();

    // 3. Kiểm tra mật khẩu
    if (password_verify($password_input, $row['PASSWORD'])) {
        // --- ĐĂNG NHẬP THÀNH CÔNG ---
        $_SESSION['username'] = $row['USERNAME'];
        $_SESSION['fullname'] = $row['FULLNAME'];
        
        $_SESSION['show_login_success'] = true;
        $_SESSION['swal_message'] = "Chào mừng " . htmlspecialchars($row['FULLNAME']) . " đã tới quán chè của chúng tôi!";
        
        header("Location: ../index.php?page=home"); // Chuyển hướng về trang chủ
        exit;

    } else {
        // Mật khẩu không chính xác
        $_SESSION['show_login_error'] = true;
        $_SESSION['swal_message'] = "Mật khẩu không chính xác!";
        header("Location: " . $login_page_url);
        exit;
    }

} else {
    // Nếu truy cập login.php mà không qua form POST
    header("Location: " . $login_page_url);
    exit;
}

$conn->close();
?>