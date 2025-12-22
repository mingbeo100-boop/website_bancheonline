<?php
session_start(); // LUÔN LUÔN là dòng đầu tiên trong file PHP nếu bạn dùng session
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chè Anh Em Cây Khế</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">

    <link href="assets/css/main.css?v=<?php echo time(); ?>" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/login.css">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

    <link rel="stylesheet" href="fonts/material-design-iconic-font/css/material-design-iconic-font.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        /* Custom CSS cho SweetAlert2 Toast (nếu cần) */
        .swal2-toast-popup {
            max-width: 300px;
            font-size: 0.9em;
        }
    </style>
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
                    <span style="color: #fff; font-weight: bold;"><?php echo htmlspecialchars($_SESSION['fullname']); ?></span>
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
    // Logic điều hướng trang
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
            include 'pages/home.php'; // Trang mặc định nếu page không hợp lệ
        }
    } else {
        include 'pages/home.php'; // Trang mặc định khi không có tham số 'page'
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
                    <a href="https://www.facebook.com/minhbiker2006" aria-label="Facebook" target="_blank">
                        <img src="./assets/images/facebook.png" alt="Facebook">
                    </a>
                    <a href="https://www.instagram.com/nhatlong.tran.3979/" aria-label="Instagram" target="_blank">
                        <img src="./assets/images/instagram.png" alt="Instagram" class="ig-small">
                    </a>
                </div>
                <?php if (!isset($_SESSION['fullname'])): ?>
                    <button class="login-btn" onclick="window.location.href='index.php?page=dangnhap'">ĐĂNG NHẬP</button>
                <?php endif; ?>
            </div>
        </div>
        </div>

        <div class="footer-bottom">
            <nav class="footer-nav">
                <a href="index.php?page=about_us">VỀ CHÚNG TÔI</a>
                <a href="index.php?page=menu">MENU</a>
                <a href="index.php?page=hethongcuahang">CỬA HÀNG</a>
                <a href="#">TIN TỨC</a>
                <a target="_blank" href="https://www.facebook.com/minhbiker2006">LIÊN HỆ VỚI CHÚNG TÔI!</a>
            </nav>
            <p class="footer-copy">
                Copyright © 2025 Chè Anh Em Cây Khế | Được phát triển bởi
                <a href="#">Team4AE.vn</a>
            </p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM"
        crossorigin="anonymous"></script>

    <script src="assets/js/cart.js"></script>
    <script src="assets/js/validation.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const slideshowContainer = document.querySelector('.slideshow');
            if (slideshowContainer) { // Chỉ chạy script nếu có container slideshow
                const slides = slideshowContainer.querySelectorAll('img');
                if (slides.length === 0) {
                    console.warn("Không tìm thấy ảnh trong slideshow. Đảm bảo class 'slideshow' và thẻ 'img' tồn tại.");
                    return;
                }

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
            }
        });

        // --- Logic hiển thị SweetAlert2 cho Đăng nhập/Đăng ký ---
        <?php
        $swal_message = isset($_SESSION['swal_message']) ? $_SESSION['swal_message'] : '';
        unset($_SESSION['swal_message']); // Xóa message ngay lập tức
        ?>
        <?php if ($swal_message): // Chỉ chạy script nếu có thông báo SweetAlert2 
        ?>
            document.addEventListener('DOMContentLoaded', function() {
                // Kiểm tra cờ hiệu đăng nhập thành công
                <?php if (isset($_SESSION['show_login_success']) && $_SESSION['show_login_success'] === true): ?>
                    Swal.fire({
                        title: 'Đăng nhập Thành Công!',
                        text: '<?php echo htmlspecialchars($swal_message); ?>',
                        icon: 'success',
                        confirmButtonText: 'OK',
                        confirmButtonColor: '#3085d6',
                        timerProgressBar: true
                    });
                    <?php unset($_SESSION['show_login_success']); ?>
                    // Kiểm tra cờ hiệu đăng nhập thất bại
                <?php elseif (isset($_SESSION['show_login_error']) && $_SESSION['show_login_error'] === true): ?>
                    Swal.fire({
                        title: 'Đăng nhập Thất Bại!',
                        text: '<?php echo htmlspecialchars($swal_message); ?>',
                        icon: 'error',
                        confirmButtonText: 'Thử lại',
                        confirmButtonColor: '#d33'
                    });
                    <?php unset($_SESSION['show_login_error']); ?>
                    // Kiểm tra cờ hiệu đăng ký thành công
                <?php elseif (isset($_SESSION['show_register_success']) && $_SESSION['show_register_success'] === true): ?>
                    Swal.fire({
                        title: 'Đăng Ký Thành Công!',
                        text: '<?php echo htmlspecialchars($swal_message); ?>',
                        icon: 'success',
                        confirmButtonText: 'Đăng nhập ngay', // Đổi text để khuyến khích đăng nhập
                        confirmButtonColor: '#28a745'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = 'index.php?page=dangnhap'; // Chuyển hướng sang trang đăng nhập
                        }
                    });
                    <?php unset($_SESSION['show_register_success']); ?>
                    // Kiểm tra cờ hiệu đăng ký thất bại
                <?php elseif (isset($_SESSION['show_register_error']) && $_SESSION['show_register_error'] === true): ?>
                    Swal.fire({
                        title: 'Đăng Ký Thất Bại!',
                        text: '<?php echo htmlspecialchars($swal_message); ?>',
                        icon: 'error',
                        confirmButtonText: 'Thử lại',
                        confirmButtonColor: '#d33'
                    });
                    <?php unset($_SESSION['show_register_error']); ?>
                <?php endif; ?>
            });
        <?php endif; ?>
    </script>

</body>

</html>