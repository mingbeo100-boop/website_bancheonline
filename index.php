<?php
session_start(); 
?>
<!DOCTYPE html>
<html lang="en">

<head>
    
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chè Anh Em Cây Khế</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <link href="assets/css/main.css?v=<?php echo time(); ?>" rel="stylesheet">

    <link rel="stylesheet" href="assets/css/login.css">

    <link rel="stylesheet" href="assets/js/login.js">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

    <link rel="stylesheet" href="fonts/material-design-iconic-font/css/material-design-iconic-font.min.css">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>
     
    <div class="navbar-fixed">
        <ul class="nav-left">
            <li class="nav-item">
                <a href="index.php?page=home">
                    <img src="./assets/images/logo2.png" alt="Trang chủ" class="nav-logo">
                </a>
            </li>
            <li class="nav-item"><a href="index.php?page=about_us">ABOUT US</a></li>
            
            <?php if (isset($_SESSION['fullname'])): ?>
                <li class="nav-item">
                    <span style="color: #fff; font-weight: bold;"><?php echo $_SESSION['fullname']; ?></span>
                </li>
                <li class="nav-item"><a href="backend/logout.php">Đăng xuất</a></li>
            <?php else: ?>
                <li class="nav-item"><a href="index.php?page=dangnhap">ĐĂNG NHẬP</a></li>
            <?php endif; ?>

            <li class="nav-item"><a href="index.php?page=menu">MENU</a></li>
        </ul>

        <ul class="nav-right">
            <li class="nav-item"><a href="index.php?page=hethongcuahang">HỆ THỐNG CỬA HÀNG</a></li>
            <li class="nav-item"><a href="index.php?page=giohang">GIỎ HÀNG</a></li>
            <li class="nav-item"><a href="index.php?page=lienhe" class="contact"></a></li>
        </ul>
    </div>


    <?php
    if (isset($_GET['page'])) {
        $page = $_GET['page'];

        if ($page == 'home') {
            include 'pages/home.php';
        } elseif ($page == 'about_us') {
            include 'pages/about_us.php';
        } elseif ($page == 'menu') {
            include 'pages/menu.php';
        } elseif ($page == 'hethongcuahang') {
            include 'pages/hethongcuahang.php';
        } elseif ($page == 'dangnhap') {
            include 'pages/login_form.php';
        } elseif ($page == 'dangki') {
            include 'pages/register_form.php';
        } elseif ($page == 'giohang') {
            include 'pages/cart.php';
        } else {
            include 'pages/home.php';
        }
    } else {
        include 'pages/home.php';
    }
    ?>


    <footer class="footer mt-4">
        <div class="footer-top">
            <div class="footer-left">
                <img src="./assets/images/logo2.png" alt="Chè Liên" class="footer-logo">
                <p class="footer-thank">Cảm ơn bạn đã đến với<br><strong>Chè Anh Em Cây Khế</strong></p>
            </div>

            <div class="footer-right">
                <p class="footer-contact-title">Liên hệ với chúng tôi</p>
                <div class="footer-socials">
                    <a href="#" aria-label="Facebook">
                        <img src="./assets/images/facebook.png" alt="Facebook">
                    </a>
                    <a href="#" aria-label="Instagram">
                        <img src="./assets/images/instagram.png" alt="Instagram" class="ig-small">
                    </a>
                </div>
                
                <?php if (!isset($_SESSION['fullname'])): ?>
                    <button class="login-btn" onclick="window.location.href='index.php?page=dangnhap'">ĐĂNG NHẬP</button>
                <?php endif; ?>
            </div>
        </div>

        <div class="footer-bottom">
            <nav class="footer-nav">
                <a href="#">VỀ CHÚNG TÔI</a>
                <a href="#">MENU</a>
                <a href="#">CỬA HÀNG</a>
                <a href="#">TIN TỨC</a>
                <a href="#">LIÊN HỆ VỚI CHÚNG TÔI!</a>
            </nav>
            <p class="footer-copy">
                Copyright © 2025 Chè Anh Em Cây Khế | Được phát triển bởi
                <a href="#">Team4AE.vn</a>
            </p>
        </div>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const slides = document.querySelectorAll('.slideshow img');
            if (slides.length === 0) return; 

            let index = 0;

            function updateSlides() {
                slides.forEach(img => img.classList.remove('active', 'prev', 'next'));
                const prev = (index - 1 + slides.length) % slides.length;
                const next = (index + 1) % slides.length;

                slides[index].classList.add('active');
                slides[prev].classList.add('prev');
                slides[next].classList.add('next');
            }

            updateSlides();

            setInterval(() => {
                index = (index + 1) % slides.length;
                updateSlides();
            }, 3000);
        });
    </script>

    <script src="assets/js/cart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
     <script src="assets/js/login.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM"
        crossorigin="anonymous"></script>
    
    <?php
// Kiểm tra nếu có cờ hiệu đăng nhập thành công
if (isset($_SESSION['show_login_success']) && $_SESSION['show_login_success'] === true) {
    
    // Lấy tên người dùng để hiển thị cho thân thiện
    $welcome_name = isset($_SESSION['fullname']) ? $_SESSION['fullname'] : 'Bạn';
    
    echo "<script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                title: 'Đăng nhập Thành Công!',
                text: 'Chào mừng " . htmlspecialchars($welcome_name) . " đã tới quán chè của chúng tôi ',
                icon: 'success',
                confirmButtonText: 'OK',
                confirmButtonColor: '#3085d6',
                
                timerProgressBar: true
            });
        });
    </script>";

    // Quan trọng: Xóa cờ hiệu ngay lập tức để không hiện lại khi F5 trang
    unset($_SESSION['show_login_success']);
}
?>

<?php
session_start();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>

    <?php
    // --- LOGIC ĐIỀU HƯỚNG TRANG ---
    if (isset($_GET['page'])) {
        $page = $_GET['page'];
        
        if ($page == 'dangnhap') {
            include 'pages/login_form.php'; // Gọi file login_form.php của bạn ra
        } elseif ($page == 'dangki') {
            include 'pages/register_form.php';
        } 
        // ... các trang khác
    } else {
        include 'pages/home.php'; // Trang chủ mặc định
    }
    ?>

    <?php
    // 1. Kiểm tra cờ ĐĂNG KÝ thành công
    if (isset($_SESSION['show_register_success']) && $_SESSION['show_register_success'] === true) {
        echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    title: 'Đăng Ký Thành Công!',
                    text: 'Tài khoản đã được tạo. Vui lòng đăng nhập.',
                    icon: 'success',
                    confirmButtonText: 'OK',
                    confirmButtonColor: '#3085d6',
                    timerProgressBar: true
                });
            });
        </script>";
        unset($_SESSION['show_register_success']); // Xóa cờ ngay
    }

    
    ?>

</body>
</html>
        
</body>

</html>