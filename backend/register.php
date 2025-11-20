<?php
session_start(); 
require 'connect.php'; 

if (isset($_POST['btn-reg'])) {
    // Lấy dữ liệu từ POST và gán giá trị mặc định ('') nếu không tồn tại (an toàn hơn)
    $first_name = $_POST['firstname'] ?? ''; 
    $last_name = $_POST['lastname'] ?? '';
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm_pw = $_POST['confirm_password'] ?? '';

    $fullname = $first_name . " " . $last_name;

    // Hàm chuyển hướng về form đăng ký khi có lỗi
    function redirectToRegister($errorMessage) {
        // Không cần global $conn ở đây
        
        $_SESSION['error'] = $errorMessage;
        
        // Giữ lại dữ liệu cũ khi có lỗi
        $_SESSION['old_data'] = [
            'firstname' => $_POST['firstname'] ?? '',
            'lastname' => $_POST['lastname'] ?? '',
            'username' => $_POST['username'] ?? ''
        ];
        
        // Chuyển hướng người dùng về trang đăng ký
        header('Location: ../index.php?page=dangki');
        exit;
    }

    // --- KIỂM TRA LỖI ---

    if (empty($first_name) || empty($last_name) || empty($username) || empty($password)) {
        redirectToRegister('Vui lòng điền đầy đủ thông tin!');
    }

    if ($password != $confirm_pw) {
        redirectToRegister('Mật khẩu xác nhận không trùng khớp!');
    }
    
    if (!preg_match("/^\S{6,}$/", $username)) {
        redirectToRegister('Tên đăng nhập phải từ 6 ký tự trở lên và không được chứa khoảng trắng!');
    }

    if (!preg_match("/^(?=.*[a-z])(?=.*[0-9])(?=\S+).{8,}$/", $password)) {
        redirectToRegister('Mật khẩu phải có ít nhất 8 ký tự, bao gồm chữ thường và số, và không chứa khoảng trắng!');
    } 
    
    // Kiểm tra tên đăng nhập đã tồn tại
    $check_sql = "SELECT * FROM user WHERE USERNAME = ?";
    $stmt_check = $conn->prepare($check_sql);
    $stmt_check->bind_param("s", $username);
    $stmt_check->execute();
    $result = $stmt_check->get_result();

    if ($result->num_rows > 0) {
        redirectToRegister('Tên đăng nhập này đã tồn tại! Vui lòng chọn tên khác.');
    }
    $stmt_check->close();

    // --- XỬ LÝ THÀNH CÔNG ---
    
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $sql = "INSERT INTO user (USERNAME, PASSWORD, FULLNAME) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $username, $hashed_password, $fullname);

    if ($stmt->execute()) {
        // 1. Đặt cờ hiệu: "Vừa đăng ký xong, hãy hiện thông báo nhé"
        $_SESSION['show_register_success'] = true;
        
        // 2. Chuyển hướng về trang Login
        // Giả sử trong index.php bạn quy định page=dangnhap sẽ gọi file login_form.php
        header('Location: ../index.php?page=dangnhap'); 
        exit;
    } else {
        redirectToRegister('Có lỗi xảy ra khi lưu dữ liệu: ' . $stmt->error);
    }
}
?>