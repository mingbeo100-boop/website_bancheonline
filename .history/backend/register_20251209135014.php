<?php
session_start(); // LUÔN LUÔN gọi session_start() ở đầu file
require 'connect.php'; // Gọi file kết nối database (Giả định chứa đối tượng $conn kiểu mysqli)

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

    // --- KIỂM TRA LỖI (GIỮ NGUYÊN) ---
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
    
    // Kiểm tra tên đăng nhập đã tồn tại (GIỮ NGUYÊN)
    $check_sql = "SELECT USERNAME FROM users WHERE USERNAME = ?"; // Đã đổi user -> users
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
    
    // --- XỬ LÝ THÀNH CÔNG (PHẦN ĐÃ SỬA ĐỔI) ---
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // BẮT ĐẦU TRANSACTION (Giao dịch)
    // Nếu một lệnh SQL thất bại, tất cả sẽ bị rollback (an toàn hơn)
    $conn->begin_transaction();
    $success = false;

    try {
        // BƯỚC 1: INSERT USER VÀO BẢNG users
        // Giả định tên bảng là 'users' (đã sửa từ 'user' trong code gốc)
        $sql_user = "INSERT INTO users (USERNAME, PASSWORD, FULLNAME) VALUES (?, ?, ?)";
        $stmt_user = $conn->prepare($sql_user);
        
        if ($stmt_user === false) {
             throw new Exception("Lỗi chuẩn bị SQL USER: " . $conn->error);
        }
        
        $stmt_user->bind_param("sss", $username, $hashed_password, $fullname);
        
        if (!$stmt_user->execute()) {
            throw new Exception("Lỗi thực thi SQL USER: " . $stmt_user->error);
        }
        
        // Lấy ID VỪA TẠO (USER_ID)
        $new_user_id = $conn->insert_id;
        $stmt_user->close();


        // BƯỚC 2: INSERT GIỎ HÀNG VÀO BẢNG carts
        // user_id trong bảng carts phải khớp với USER_ID vừa tạo
        $sql_cart = "INSERT INTO carts (user_id) VALUES (?)";
        $stmt_cart = $conn->prepare($sql_cart);
        
        if ($stmt_cart === false) {
             throw new Exception("Lỗi chuẩn bị SQL CART: " . $conn->error);
        }
        
        $stmt_cart->bind_param("i", $new_user_id);
        
        if (!$stmt_cart->execute()) {
            throw new Exception("Lỗi thực thi SQL CART: " . $stmt_cart->error);
        }
        $stmt_cart->close();

        // KẾT THÚC TRANSACTION (Lưu các thay đổi)
        $conn->commit();
        $success = true;

    } catch (Exception $e) {
        // Nếu có lỗi, ROLLBACK (Hủy bỏ các thay đổi)
        $conn->rollback();
        // Ghi lại lỗi chi tiết hơn nếu cần
        error_log("Đăng ký thất bại: " . $e->getMessage()); 
    }
    
    // --- XỬ LÝ CHUYỂN HƯỚNG DỰA TRÊN KẾT QUẢ TRANSACTION ---
    if ($success) {
        handleRedirect('success', 'Đăng ký tài khoản thành công! Vui lòng đăng nhập.', $login_page_url); // Chuyển hướng về trang ĐĂNG NHẬP
    } else {
        // Lỗi thường do SQL (ví dụ: mất kết nối, lỗi bảng)
        handleRedirect('error', 'Có lỗi xảy ra trong quá trình đăng ký. Vui lòng thử lại.', $register_page_url, $old_data);
    }

    $conn->close();
} else {
    // Nếu truy cập trực tiếp file này mà không qua form POST
    header("Location: " . $register_page_url);
    exit;
}
?>