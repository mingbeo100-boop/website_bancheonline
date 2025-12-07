

<<?php
$order_id = isset($_GET['order_id']) ? htmlspecialchars($_GET['order_id']) : 'CHƯA CÓ ID';
$total_amount = isset($_GET['total']) ? (int)$_GET['total'] : 0;
$formatted_total = number_format($total_amount, 0, ',', '.') . ' ₫';
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chi Tiết Đơn Hàng - AE Cây Khế</title>
    <link rel="stylesheet" href="styles.css"> 
    <style>
    
    body {
    font-family: sans-serif;
    background-color: #f4f4f9;
    color: #333;
    margin-top: 130px;
    padding: 20px;
}

.container {
    max-width: 900px;
    margin: 0 auto;
    background: #fff;
    padding: 30px;
    border-radius: 8px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

h1 {
    color: #8b0000; /* Màu đỏ đậm */
    margin-bottom: 20px;
}

.status-box {
    padding: 15px;
    border-radius: 5px;
    margin-bottom: 20px;
    font-size: 1.1em;
}

.pending-address {
    background-color: #fff3cd; /* Màu vàng nhạt */
    border: 1px solid #ffeeba;
    color: #856404;
}

.content-wrapper {
    display: flex;
    gap: 30px;
}


.order-summary, .address-form-container {
    flex: 1;
    padding: 20px;
    border: 1px solid #ddd;
    border-radius: 5px;
}

.item {
    display: flex;
    justify-content: space-between;
    padding: 5px 0;
}

.total {
    display: flex;
    justify-content: space-between;
    padding-top: 10px;
    font-size: 1.2em;
    color: #8b0000;
}

.form-group {
    margin-bottom: 15px;
}

.form-group label {
    display: block;
    margin-bottom: 5px;
    font-weight: bold;
}

.form-group input, .form-group textarea {
    width: 100%;
    padding: 10px;
    border: 1px solid #ccc;
    border-radius: 4px;
    box-sizing: border-box; /* Quan trọng để padding không làm tăng kích thước */
}

.confirm-btn {
    background-color: #28a745; /* Màu xanh lá cây */
    color: white;
    padding: 12px 20px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-size: 1.1em;
    width: 100%;
    transition: background-color 0.3s;
}

.confirm-btn:hover {
    background-color: #218838;
}
/* Styling cho các tùy chọn thanh toán */
#payment-selection {
    margin-bottom: 20px;
}

.payment-option {
    border: 1px solid #ddd;
    padding: 15px;
    margin-bottom: 10px;
    border-radius: 5px;
    cursor: pointer;
    transition: all 0.2s;
}

.payment-option:hover {
    border-color: #8b0000;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
}

.payment-option.selected {
    border: 2px solid #8b0000; /* Màu đỏ nổi bật khi chọn */
    background-color: #fff0f0;
}

.payment-option h3 {
    margin-top: 0;
    font-size: 1.1em;
    color: #8b0000;
}

/* Ẩn hiện khu vực chi tiết thanh toán */
#payment-details-area {
    padding-top: 20px;
    border-top: 1px dashed #ddd;
}

/* Styling cho form nhập địa chỉ (lấy lại từ giao diện cũ) */
.form-group {
    margin-bottom: 15px;
}
.map-btn {
    display: inline-block;
    background-color: #4285F4; /* Màu xanh Google */
    color: white;
    padding: 10px 15px;
    border-radius: 4px;
    text-decoration: none;
    font-weight: bold;
    margin: 15px 0;
    text-align: center;
    transition: background-color 0.3s;
}

.map-btn:hover {
    background-color: #357ae8;
}

.map-link-area h3 {
    margin-top: 20px;
}
/* ... các style input, textarea, button đã có ... */
/* CSS Cần thiết cho Modal */
.modal {
    display: none; /* Ẩn theo mặc định */
    position: fixed; /* Giữ vị trí cố định */
    z-index: 1000; /* Đảm bảo nổi trên tất cả các phần tử khác */
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    overflow: auto; /* Cho phép cuộn nếu nội dung lớn */
    background-color: rgba(0, 0, 0, 0.4); /* Màu nền đen mờ */
}

.modal-content {
    background-color: #fefefe;
    margin: 10% auto; /* Đặt ở giữa màn hình (trên cùng 10%) */
    padding: 25px;
    border: 1px solid #888;
    width: 80%;
    max-width: 450px; /* Kích thước tối đa cho Modal */
    border-radius: 8px;
    position: relative;
    box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2), 0 6px 20px 0 rgba(0, 0, 0, 0.19);
}

.close-btn {
    color: #aaa;
    float: right;
    font-size: 28px;
    font-weight: bold;
}

.close-btn:hover,
.close-btn:focus {
    color: #000;
    text-decoration: none;
    cursor: pointer;
}
.guide-image {
    max-width: 100%; /* Đảm bảo hình ảnh không tràn ra ngoài khối */
    height: auto;
    border: 1px solid #ccc;
    border-radius: 5px;
    margin-top: 10px;
}
    
    </style>
</head>
    
<body>

<div class="container">
    <h1>📦 Đơn hàng #<?php echo $order_id; ?> Cần Hoàn Tất</h1>

    <div class="status-box pending-address">
        <p><strong>Trạng thái:</strong> Vui lòng nhập thông tin giao hàng để hoàn tất đơn hàng.</p>
    </div>
    
    <div class="content-wrapper">
        
        <div id="deliveryBlock" class="address-form-container">
            <h2>Thông Tin Giao Hàng</h2>
            
            <form id="deliveryForm">
                <div class="form-group">
                    <label for="name">Tên người nhận:</label>
                    <input type="text" id="name" name="name" required>
                </div>
                <div class="form-group">
                    <label for="phone">Số điện thoại:</label>
                    <input type="tel" id="phone" name="phone" required>
                </div>
                
                <h2>📍 Chọn Vị Trí Giao Hàng</h2><br>
                <div class="map-link-area">
                    <h style="font-weight: bold;font-size: 20px;">🗺️ Hướng dẫn lấy địa chỉ chính xác: </h>
                       

                    <div id="mapGuide" class="guide-steps-container">

    <div class="guide-step">
        <br> 
        <h4 class="step-title" style="color:#000;">Bước 1: Mở Maps và Ghim Vị Trí</h4>
        <p>Nhấn vào nút "Mở Google Maps" và tìm kiếm/nhấn giữ để ghim vị trí giao hàng của bạn.</p>
        <img src="assets/images/guide/b1.png" alt="Hướng dẫn ghim vị trí trên Google Maps" class="guide-image">
    </div>
    
    <div class="guide-step" >
        <br>
        <h4 class="step-title" style="color:#000">Bước 2: Sao Chép Địa Chỉ Văn Bản</h4>
        <p>Trong chi tiết vị trí đã ghim, tìm và nhấn vào nút "Copy address" (Sao chép địa chỉ).</p>
        <img src="assets/images/guide/b2.jpg" alt="Hướng dẫn copy address trong Google Maps" class="guide-image">
    </div>
    
    <div class="guide-step">
         <br>
        <h4 class="step-title" style="color:#000">Bước 3: Dán và Xác Nhận</h4>
        <p>Quay lại trang này và dán địa chỉ đã sao chép vào ô trống để xác nhận.</p>
        <img src="assets/images/guide/b3.png" alt="Hướng dẫn dán địa chỉ vào form" class="guide-image">
    </div>
</div>
                    
                    <a id="openMapLink" href="https://www.google.com/maps/@15.9824648,108.2496147,15z?entry=ttu&g_ep=EgoyMDI1MTIwMi4wIKXMDSoASAFQAw%3D%3D" target="_blank" class="map-btn">
                        Mở Google Maps để ghim vị trí
                    </a>

                    
                    <form id="mapAddressForm">
                        <div class="form-group">
                            <label for="pastedAddress">Dán địa chỉ sau khi bạn đã copy vào đây: </label>
                            <textarea id="pastedAddress" name="pastedAddress" rows="2" required></textarea>
                        </div>
                    </form>
                    
                    <hr>
                    <div class="order-summary-footer">
                        <h4 style="color:#000;font-weight: bold;">Tổng Tiền Đơn Hàng</h4>
                        <div class="total d-flex justify-content-between">
                            <span>Tổng cộng</span>
                            <strong><?php echo $formatted_total; ?></strong>
                        </div>
                    </div>
                    <hr>
                </div>
                <button type="button" id="confirmAddressBtn" class="confirm-btn">Xác Nhận Thông Tin Giao Hàng</button>
            </form>
        </div>
        
        <div id="paymentBlock" class="address-form-container" style="display: none;">
            <div class="payment-address-container">
                <h2>Chọn Phương Thức Thanh Toán</h2>
                <div id="payment-selection">
                    <div class="payment-option" data-method="qr">
                        <h3>💳 Chuyển Khoản Ngân Hàng (QR)</h3>
                        <p>Thanh toán ngay bằng cách quét mã QR.</p>
                    </div>
                    <div class="payment-option" data-method="cod">
                        <h3>💰 Thanh Toán Khi Nhận Hàng (COD)</h3>
                        <p>Thanh toán bằng tiền mặt khi shipper giao hàng.</p>
                    </div>
                </div>
                <div id="payment-details-area">
                    <button type="button" id="finalConfirmBtn" class="confirm-btn mt-3" >Hoàn Tất Đơn Hàng</button>
                </div>
            </div>
            
            
            
        </div>
        <div id="qrModal" class="modal" style="display: none;">
    <div class="modal-content">
        <span id="closeModalBtn" class="close-btn">&times;</span> 
        
        <h2 style="color: #8b0000;">💰 Thanh toán bằng Chuyển Khoản</h2>
        <hr>
        
        <div class="payment-info-modal text-center">
            
            <p style="font-size: 1.1em;">Tổng số tiền cần thanh toán:</p>
            <strong id="modalTotalPriceContainer" style="font-size: 1.8em; color: #28a745;">0 ₫</strong> 
            
            <div id="qrcode" style="margin: 20px auto; width: 200px; height: 200px;">
                
            </div>

            <p style="font-style: italic;">Vui lòng quét mã QR để chuyển khoản chính xác số tiền.</p>
        </div>

        <button type="button" id="paymentCompleteBtn" class="confirm-btn mt-4">
            Đã Hoàn Thành Chuyển Khoản
        </button>
    </div>
</div>
        
        
    </div>
    
</div>
<script src="https://cdn.rawgit.com/davidshimjs/qrcodejs/gh-pages/qrcode.min.js"></script>
<script src="https://maps.googleapis.com/maps/api/js?key=https://www.google.com/maps&callback=initMap" async defer></script>
<script src="/assets/js/order_detail_logic.js"></script> 
<script src="assets/js/cart_api.js"></script> 

</body>

</html>



