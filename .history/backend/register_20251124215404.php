<?php
session_start(); // LUÔN LUÔN gọi session_start() ở đầu file
require 'connect.php'; // Gọi file kết nối database

$register_page_url = '../index.php?page=dangki'; // URL của trang đăng ký
$login_page_url = '../index.php?page=dangnhap'; // URL của trang đăng nhập

// Hàm tiện ích để chuẩn hóa việc set session cho SweetAlert2 và chuyển hướng
function handleRedirect($type, $message, $redirect_url, $old_data = []) {
    // Xóa tất cả các cờ hiệu SweetAlert2 cũ trước khi thiết lập cái mới
    unset($_SESSION['show_login_success']);
    unset($_SESSION['show_login_error']);
    unset($_SESSION['show_register_success']);
    unset($_SESSION['show_register_error']);
    unset($_SESSION['swal_message']);

    if ($type === 'success') {
        $_SESSION['show_register_success'] = true; // Cờ hiệu đăng ký thành công
        $_SESSION['swal_message'] = $message;
        unset($_SESSION['old_data']); // Xóa dữ liệu cũ khi thành công
    } else { // type === 'error'
        $_SESSION['show_register_error'] = true; // Cờ hiệu đăng ký thất bại
        $_SESSION['swal_message'] = $message;
        $_SESSION['old_data'] = $old_data; // Giữ lại dữ liệu cũ khi có lỗi
    }
    
    header("Location: " . $redirect_url);
    exit;
}

if (isset($_POST['btn-reg'])) { // Đảm bảo tên nút submit trong form là 'btn-reg'
    // Lấy dữ liệu từ POST và gán giá trị mặc định ('') nếu không tồn tại (an toàn hơn)
    $first_name = $_POST['firstname'] ?? ''; 
    $last_name = $_POST['lastname'] ?? '';
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm_pw = $_POST['confirm_password'] ?? '';
    $fullname = trim($first_name . " " . $last_name); // Ghép họ và tên, loại bỏ khoảng trắng thừa

    // Lưu dữ liệu cũ để điền lại form nếu có lỗi
    $old_data = [
        'firstname' => $first_name,
        'lastname' => $last_name,
        'username' => $username
        // Không lưu mật khẩu cũ vì lý do bảo mật
    ];

    // --- KIỂM TRA LỖI ---
    if (empty($first_name) || empty($last_name) || empty($username) || empty($password) || empty($confirm_pw)) {
        handleRedirect('error', 'Vui lòng điền đầy đủ thông tin!', $register_page_url, $old_data);
    }
    if ($password != $confirm_pw) {
        handleRedirect('error', 'Mật khẩu xác nhận không trùng khớp!', $register_page_url, $old_data);
    }
    
    if (!preg_match("/^\S{6,}$/", $username)) {
        handleRedirect('error', 'Tên đăng nhập phải từ 6 ký tự trở lên và không được chứa khoảng trắng!', $register_page_url, $old_data);
    }
    if (!preg_match("/^(?=.*[a-z])(?=.*[0-9])(?=\S+).{8,}$/", $password)) {
        handleRedirect('error', 'Mật khẩu phải có ít nhất 8 ký tự, bao gồm chữ thường và số, và không chứa khoảng trắng!', $register_page_url, $old_data);
    } 
    
    // Kiểm tra tên đăng nhập đã tồn tại
    $check_sql = "SELECT USERNAME FROM users WHERE USERNAME = ?"; // Chỉ cần chọn USERNAME
    $stmt_check = $conn->prepare($check_sql);
    if ($stmt_check === false) {
        handleRedirect('error', 'Lỗi hệ thống: Không thể chuẩn bị kiểm tra trùng lặp.', $register_page_url, $old_data);
    }
    $stmt_check->bind_param("s", $username);
    $stmt_check->execute();
    $result = $stmt_check->get_result();
    if ($result->num_rows > 0) {
        $stmt_check->close();
        handleRedirect('error', 'Tên đăng nhập này đã tồn tại! Vui lòng chọn tên khác.', $register_page_url, $old_data);
    }
    $stmt_check->close();

    // --- XỬ LÝ THÀNH CÔNG ---
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Đảm bảo tên cột khớp với cấu trúc bảng 'user' của bạn
    // Giả định các cột là: USERNAME, PASSWORD, FULLNAME
    $sql = "INSERT INTO users (USERNAME, PASSWORD, FULLNAME) VALUES (?, ?, ?)";

    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        handleRedirect('error', 'Lỗi hệ thống: Không thể chuẩn bị chèn dữ liệu. Vui lòng thử lại sau.', $register_page_url, $old_data);
    }
    $stmt->bind_param("sss", $username, $hashed_password, $fullname);

    if ($stmt->execute()) {
        // Đăng ký thành công
        handleRedirect('success', 'Đăng ký tài khoản thành công! Vui lòng đăng nhập.', $register_page_url);
    } else {
        // Lỗi khi thực thi câu lệnh SQL
        handleRedirect('error', 'Có lỗi xảy ra khi lưu dữ liệu: ' . $stmt->error, $register_page_url, $old_data);
    }

    $stmt->close();
    $conn->close();
} else {
    // Nếu truy cập trực tiếp file này mà không qua form POST
    header("Location: " . $register_page_url);
    exit;
}
?>