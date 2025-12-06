<?php
// Đọc dữ liệu từ URL (được truyền từ trang donhang.php sau khi hoàn tất)
$order_id = isset($_GET['order_id']) ? htmlspecialchars($_GET['order_id']) : 'KHÔNG XÁC ĐỊNH';
$total_amount = isset($_GET['total']) ? (int)$_GET['total'] : 0;
$method = isset($_GET['method']) ? htmlspecialchars($_GET['method']) : 'Chưa rõ';

// Định dạng lại dữ liệu
$formatted_total = number_format($total_amount, 0, ',', '.') . ' ₫';
$payment_method_display = ($method == 'cod') ? 'Thanh toán khi nhận hàng (COD)' : 'Chuyển khoản (QR/Bank)';

?>

<div class="container text-center mt-5 p-5 bg-white shadow-lg rounded">
    
    <div class="icon-success mb-4">
        <i class="fas fa-check-circle" style="font-size: 5em; color: #28a745;"></i>
    </div>
    
    <h1 style="color: #8b0000; font-size: 2.5em;">🎉 Đặt Hàng Thành Công!</h1>
    
    <p class="lead mt-3">Cảm ơn bạn đã đặt hàng tại Chè Anh Em Cây Khế.</p>
    
    <hr class="my-4">
    
    <div class="order-summary-box mx-auto p-3 border rounded bg-light" style="max-width: 450px;">
        <h3 class="mb-3" style="color: #555;">Chi Tiết Đơn Hàng</h3>
        
        <div class="d-flex justify-content-between mb-2">
            <strong>Mã đơn hàng:</strong>
            <span>#<?php echo $order_id; ?></span>
        </div>
        
        <div class="d-flex justify-content-between mb-2">
            <strong>Phương thức:</strong>
            <span><?php echo $payment_method_display; ?></span>
        </div>
        
        <div class="d-flex justify-content-between pt-2 border-top">
            <strong class="text-danger">Tổng cộng:</strong>
            <strong class="text-danger"><?php echo $formatted_total; ?></strong>
        </div>
    </div>
    
    <p class="mt-4">
        Cửa hàng sẽ sớm xác nhận đơn hàng và tiến hành giao hàng. Vui lòng kiểm tra email (hoặc mục Đơn Hàng) để theo dõi trạng thái đơn hàng chi tiết.
    </p>

    <div class="mt-5 d-flex justify-content-center gap-4">

    <button type="button" id="continueShoppingBtn" 
        class="btn btn-primary btn-lg** custom-btn-effect">
        Tiếp tục Mua sắm
    </button>
    
    <button type="button" id="viewOrdersBtn" 
        class="btn btn-outline-secondary btn-lg** custom-btn-effect">
        Xem Đơn Hàng Của Tôi
    </button>
</div>

</div>

<style>

    /* Ví dụ CSS tùy chỉnh cho các nút mới */
.custom-btn-effect {
    /* Đảm bảo style cơ bản không bị mất */
    font-weight: bold;
    
    /* Hiệu ứng tùy chỉnh */
    transition: transform 0.4s, box-shadow 0.2s;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.custom-btn-effect:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 10px rgba(0, 0, 0, 0.2);
}
    .order-summary-box strong { font-weight: 600; }
</style>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const continueShoppingBtn = document.getElementById('continueShoppingBtn');
    const viewOrdersBtn = document.getElementById('viewOrdersBtn');

    if (continueShoppingBtn) {
        continueShoppingBtn.addEventListener('click', function() {
            // Chuyển hướng đến trang Menu
            window.location.href = 'index.php?page=menu';
        });
    }

    if (viewOrdersBtn) {
        viewOrdersBtn.addEventListener('click', function() {
            // Chuyển hướng đến trang Đơn hàng (Danh sách đơn hàng)
            window.location.href = 'index.php?page=orders';
        });
    }
});
</script>
