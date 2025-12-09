<?php
// Lấy thông báo lỗi nếu có
$errorMessage = '';
if (isset($_SESSION['error'])) {
    $errorMessage = $_SESSION['error'];
    unset($_SESSION['error']);
}

// Lấy thông báo thành công nếu có
$successMessage = '';
if (isset($_SESSION['success'])) {
    $successMessage = $_SESSION['success'];
    unset($_SESSION['success']);
}

// 🎯 LẤY DỮ LIỆU CŨ ĐÃ LƯU TỪ SESSION 🎯
$old_data = isset($_SESSION['old_data']) ? $_SESSION['old_data'] : [];
unset($_SESSION['old_data']);
?>

<div class="login-scope">
    <div class="wrapper" style="background-image: url('assets/images/bg-registration-form-2.jpg');">
        <div class="inner">
            
            <form action="backend/register.php" method="POST"> 
                
                <div class="registration-page-content">
                    <?php if ($errorMessage): ?>
                        <div class="alert alert-danger custom-alert" role="alert">
                            <i class="fa-solid fa-triangle-exclamation"></i>
                            <strong>Lỗi:</strong> <?php echo htmlspecialchars($errorMessage); ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($successMessage): ?>
                        <div class="alert alert-success custom-alert" role="alert">
                            <i class="fa-solid fa-circle-check"></i>
                            <strong>Thành công:</strong> <?php echo htmlspecialchars($successMessage); ?>
                        </div>
                    <?php endif; ?>
                </div>

                <h3>Đăng Kí</h3>
                <div class="form-group">
                    <div class="form-wrapper">
                        <label for="">First Name</label>
                        <input type="text" name="firstname" class="form-control" 
                               value="<?php echo htmlspecialchars($old_data['firstname'] ?? ''); ?>" required>
                    </div>
                    <div class="form-wrapper">
                        <label for="">Last Name</label>
                        <input type="text" name="lastname" class="form-control" 
                               value="<?php echo htmlspecialchars($old_data['lastname'] ?? ''); ?>" required>
                    </div>
                </div>
                <div class="form-wrapper">
                    <label for="">Tên đăng nhập</label>
                    <input type="text" name="username" class="form-control" 
                           value="<?php echo htmlspecialchars($old_data['username'] ?? ''); ?>" required>
                </div>
                <div class="form-wrapper">
                    <label for="">Mật khẩu</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <div class="form-wrapper">
                    <label for="">Xác nhận mật khẩu</label>
                    <input type="password" name="confirm_password" class="form-control" required>
                </div>
                <div class="checkbox">
                    <label>
                        <input type="checkbox" required> Tôi chấp nhận Điều khoản Sử dụng và Chính sách Bảo mật.
                        <span class="checkmark"></span>
                    </label>
                </div>
                
                <button type="submit" name="btn-reg">Đăng Kí Ngay</button>
                <button type="button" onclick="window.location.href='index.php?page=dangnhap'">Quay Lại Đăng Nhập</button>
                    
            </form>
        </div>
    </div>
</div>

<style>
.custom-alert {
    padding: 15px 20px;
    border-radius: 4px;
    margin: 20px auto; 
    width: 100%;
    text-align: left; /* Bỏ căn giữa */
    display: block; 
}
/* ... các quy tắc CSS còn lại giữ nguyên ... */
.alert.alert-danger {
    background-color: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
    font-weight: 500;
}
.alert.alert-success {
    background-color: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
    font-weight: 500;
}
.custom-alert i {
    margin-right: 8px;
    color: inherit;
}
.registration-page-content {
    width: 100%;
}
</style>