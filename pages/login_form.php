<?php
// Bạn cần đảm bảo session_start() đã được gọi ở index.php (trước khi include file này).
// Lấy lại tên đăng nhập cũ nếu có lỗi (để điền lại vào form)
$old_username = '';
if (isset($_SESSION['old_data']['username'])) {
    $old_username = htmlspecialchars($_SESSION['old_data']['username']);
    unset($_SESSION['old_data']['username']); // Xóa ngay sau khi hiển thị
}
?>

<div class="login-scope">
  <div class="wrapper" style="background-image: url('assets/images/bg-registration-form-2.jpg');">
    <div class="inner">
            <form action="backend/login.php" method="POST">
                
                <h3>Đăng Nhập</h3>
                
                <div class="form-wrapper">
                    <label for="username">User</label>
                    <input 
                        type="text" 
                        name="username" 
                        id="username" 
                        class="form-control" 
                        required 
                        value="<?php echo $old_username; ?>"
                    >
                </div>

                <div class="form-wrapper">
                    <label for="password">Password</label>
                    <input type="password" name="password" id="password" class="form-control" required>
                </div>
            
                <div class="checkbox">
                    <label>
                        <input type="checkbox" name="remember_me"> Ghi nhớ đăng nhập
                        <span class="checkmark"></span>
                    </label>
                </div>

                <button type="submit" name="btn-login">Đăng Nhập</button>
                
                <button type="button" onclick="window.location.href='index.php?page=dangki'">Đăng Kí</button>
            </form>
        </div>
    </div>
</div>