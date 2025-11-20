<?php
// Lấy thông báo lỗi nếu có
$errorMessage = '';
if (isset($_SESSION['error'])) {
    $errorMessage = $_SESSION['error'];
    unset($_SESSION['error']);
}

// Lấy thông báo thành công nếu có (ví dụ: sau khi đăng ký thành công)
$successMessage = '';
if (isset($_SESSION['success'])) {
    $successMessage = $_SESSION['success'];
    unset($_SESSION['success']);
}
?>

<div class="login-scope">
    <div class="wrapper" style="background-image: url('assets/images/bg-registration-form-2.jpg');">
        <div class="inner">
            <form action="backend/login.php" method="POST">
                
                <div class="login-page-content">
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
                <h3>Đăng Nhập</h3>
                
                <div class="form-wrapper">
                    <label for="">User</label>
                    <input type="text" name="username" class="form-control" required>
                </div>

                <div class="form-wrapper">
                    <label for="">Password</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
              
                <div class="checkbox">
                    <label>
                        <input type="checkbox"> Ghi nhớ đăng nhập
                        <span class="checkmark"></span>
                    </label>
                </div>

                <button type="submit" name="btn-login">Đăng Nhập</button>
                
                <button type="button" onclick="window.location.href='index.php?page=dangki'">Đăng Kí</button>
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
    text-align: left;
    display: block; 
}
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
.login-page-content {
    width: 100%;
}
</style>