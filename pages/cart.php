<div class="cart">
    
    <h2 class="cart-main-header">
        <i class="bi bi-cart-fill mr-2"></i> Giỏ Hàng Của Bạn
    </h2>
    <p class="page-description-cart">Kiểm tra và hoàn tất đơn hàng của bạn.</p>
    
    <div class="cart-controls">
        <label class="text-gray-700 font-semibold cursor-pointer">
            <input type="checkbox" id="selectAllCart"> Chọn tất cả
        </label>
        <button class="btn-remove-selected" id="removeSelected">XÓA ĐÃ CHỌN</button>
    </div>

    <div class="cart-items-scroll">
        <div class="cart-items" id="cartItems">
            <div class="p-4 text-center text-gray-500 italic">Giỏ hàng trống.</div>
        </div>
    </div>
    
    <div class="discount-box">
        <input type="text" id="discountCode" placeholder="Nhập mã giảm giá">
        <button id="applyDiscount">Áp dụng</button>
    </div>
    <div class="cart-total">
        Tổng tiền: <span id="totalPrice">0 ₫</span>
    </div>
    <button class="checkout" id="checkoutBtn">THANH TOÁN</button>

    <div id="qrModal" class="modal">
        <div class="modal-content">
            <span class="close-btn">&times;</span>
            <h3 style="font-weight: bold;">💰 Thanh toán bằng Chuyển khoản QR</h3>
            <p>Tổng số tiền cần thanh toán:</p>
            <h4 id="modalTotalPrice">0 ₫</h4>

            <div class="qr-code-area">
                <div id="qrcode"></div>
                <p style="font-size: 0.9em; margin-top: 10px;">Quét mã QR để chuyển tiền chính xác số trên.</p>
            </div>

            <button id="paymentCompleteBtn" class="btn-complete-payment">ĐÃ HOÀN THÀNH CHUYỂN TIỀN</button>
            <p class="warning-text">Vui lòng chỉ nhấn nút sau khi đã chuyển khoản thành công!</p>
        </div>
    </div>
</div>
<style>
    /* --- BIẾN MÀU ĐỒNG BỘ NÂU/KEM --- */
    :root {
        --main-bg-color: #FFF8E1; 
        --primary-text-color: #a0522d; 
        --accent-color: #b8860b;  
        --border-color: #d1b88e; 
    }

    /* ------------------------------------------------------------- */
    /* 1. GIỎ HÀNG CHÍNH (CART) */
    /* ------------------------------------------------------------- */
    .cart {
        max-width: 900px;
        margin: 40px auto;
        padding: 30px;
        background-color: var(--main-bg-color); 
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        border: 1px solid var(--border-color);
        font-family: 'Quicksand', sans-serif;
    }
    
    /* 🌟 TIÊU ĐỀ GIỎ HÀNG LỚN MỚI 🌟 */
    .cart-main-header {
        color:black; 
        font-family: 'Dancing Script', cursive; 
        font-size: 4.5rem; 
        font-weight: 700;
        text-align: center;
        margin-bottom: 10px; 
        padding-bottom: 20px;
        border-bottom: 2px solid #f0e6c7; 
    }
    .page-description-cart {
        text-align: center;
        color: #6c757d; 
        margin-top: 5px;
        margin-bottom: 5x; 
        font-size: 1.1rem;
    }

    /* 🌟 KHU VỰC CUỘN SẢN PHẨM MỚI 🌟 */
    .cart-items-scroll {
        max-height: 400px; /* Chiều cao tối đa, có thể điều chỉnh */
        overflow-y: auto;
        margin-bottom: 20px;
        border: 1px solid var(--border-color);
        border-radius: 8px;
        background-color: #ebe1d4ab; /* Nền trắng cho khu vực cuộn */
    }

    .cart-items {
        /* Đảm bảo nội dung bên trong khu vực cuộn không có min-height cứng */
        min-height: auto; 
        margin: 10px 10px;
        padding:10px;
    }

    /* CONTROLS (Chọn tất cả / Xóa) */
    .cart-controls {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 15px; /* Giảm margin để gần khu vực cuộn hơn */
        padding-bottom: 10px;
        border-bottom: 1px solid #f0e6c7;
    }
   
    
    /* (Đặt lại tất cả CSS Giỏ hàng khác ở dưới đây...) */
    .btn-remove-selected {
        display: block; 
        text-align: center; 
        background-color: #ce5543ff; /* Teal nhạt */
        color: white !important; 
        padding: 4px 6px; 
        border-radius: 6px;
        font-weight: 700;
        text-decoration: none;
        transition: all 0.2s;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
    }
    .btn-remove-selected:hover {
         background-color: #761c1cff; /* Teal đậm hơn */
        color: white !important;
        text-decoration: none;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
        transform: translateY(-1px);
    }
    
    /* DISCOUNT BOX */
    .discount-box {
        display: flex;
        margin-bottom: 20px;
    }
    .discount-box input {
        flex-grow: 1;
        padding: 8px;
        border: 1px solid var(--border-color);
        border-radius: 6px 0 0 6px;
        outline: none;
        background-color: white;
    }
    .discount-box button {
        background-color: var(--primary-text-color);
        color: white;
        padding: 10px 15px;
        border: none;
        border-radius: 0 6px 6px 0;
        font-weight: 600;
        transition: background-color 0.3s;
    }
    .discount-box button:hover {
        background-color: var(--accent-color);
    }

    /* TOTAL */
    .cart-total {
        text-align: right;
        font-size: 1.25rem;
        font-weight: 700;
        color: var(--primary-text-color);
        margin-bottom: 20px;
    }
    #totalPrice {
        color: #0b0404ff; 
        font-size: 1.8rem;
    }

    /* CHECKOUT BUTTON */
    .checkout {
         display: block; 
        width: 100%; 
        text-align: center; 
        background-color: #9b774eff; /* Teal nhạt */
        color: white !important; 
        padding: 8px 10px; 
        border-radius: 6px;
        font-size: 1.4rem; 
        font-weight: 700;
        text-decoration: none;
        transition: all 0.2s;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
    }
    .checkout:hover {
         background-color: #49371dff; /* Teal đậm hơn */
        color: white !important;
        text-decoration: none;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
        transform: translateY(-1px);
    }
    
    /* ------------------------------------------------------------- */
    /* 2. MODAL QR THANH TOÁN (Giữ nguyên cấu trúc) */
    /* ------------------------------------------------------------- */
    .modal {
        display: none; 
        position: fixed;
        z-index: 2000; 
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        overflow: auto;
        background-color: rgba(0,0,0,0.5); 
    }
    .modal-content {
        background-color: var(--secondary-bg-color);
        margin: 7% auto; 
        padding: 30px;
        border: 2px solid var(--primary-text-color); 
        width: 90%; 
        max-width: 450px;
        border-radius: 10px;
        text-align: center;
        box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
    }
    .close-btn {
        color: var(--primary-text-color);
        float: right;
        font-size: 32px;
        font-weight: bold;
        cursor: pointer;
        transition: color 0.2s;
    }
    .close-btn:hover {
        color: var(--accent-color);
    }
    .modal-content h3 {
        color: var(--primary-text-color);
        font-size: 1.5rem;
        margin-bottom: 15px;
    }
    #modalTotalPrice {
        color: #dc3545;
        font-size: 2rem;
        margin-top: 5px;
    }
    .qr-code-area {
        margin: 20px 0;
        border: 2px dashed var(--border-color); 
        padding: 20px;
        background-color: white;
    }
    #qrcode {
        display: flex;
        justify-content: center;
        align-items: center;
    }
    .btn-complete-payment {
        background-color: var(--primary-text-color);
        color: white;
        padding: 12px 20px;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        margin-top: 20px;
        font-weight: 700;
        transition: background-color 0.3s;
    }
    .btn-complete-payment:hover {
        background-color: var(--accent-color);
    }
    .warning-text {
        font-size: 0.9em;
        color: #dc3545;
        margin-top: 10px;
    }
</style>

---


<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script> 
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>

<script src="assets/js/cart_api.js"></script> 

<script src="assets/js/cart_render.js"></script>