<?php
session_start();
require 'connect.php'; 

$login_page_url = '../index.php?page=dangnhap'; 

if (isset($_POST['btn-login'])) {
    $username_input = $_POST['username'];
    $password_input = $_POST['password'];

    if (empty($username_input) || empty($password_input)) {
        $_SESSION['error'] = "Vui lòng nhập đầy đủ tên đăng nhập và mật khẩu!";
        header("Location: " . $login_page_url); 
        exit;
    }

    $sql = "SELECT USERNAME, PASSWORD, FULLNAME FROM user WHERE USERNAME = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username_input);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        
        if (password_verify($password_input, $row['PASSWORD'])) {
            // --- ĐĂNG NHẬP THÀNH CÔNG ---
            
            $_SESSION['username'] = $row['USERNAME'];
            $_SESSION['fullname'] = $row['FULLNAME'];
            unset($_SESSION['error']); // Xóa lỗi cũ

            // ✅ THÊM DÒNG NÀY: Đánh dấu là vừa đăng nhập thành công
            $_SESSION['show_login_success'] = true; 
            
            // Chuyển hướng về trang chủ
            header("Location: ../index.php"); 
            exit;

        } else {
            $_SESSION['error'] = "Mật khẩu không chính xác!";
            header("Location: " . $login_page_url); 
            exit;
        }
    } else {
        $_SESSION['error'] = "Tên đăng nhập không tồn tại!";
        header("Location: " . $login_page_url); 
        exit;
    }

    $stmt->close();
    $conn->close();
} else {
    header("Location: ../index.php");
    exit;
}
?>