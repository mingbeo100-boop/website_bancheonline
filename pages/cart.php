<div class="cart">
    <div class="cart-controls">
        <label>
            <input type="checkbox" id="selectAllCart"> Chọn tất cả
        </label>
        <button  class="btn-remove-selected" id="removeSelected" >XÓA ĐÃ CHỌN</button>
    </div>

    <div class="cart-items" id="cartItems">
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
            <h4 id="modalTotalPrice" style="color:black; font-weight: bold;">0 ₫</h4>

            <div class="qr-code-area">
                <div id="qrcode"></div>
                <p style="font-size: 0.9em; margin-top: 10px;;">Quét mã QR để chuyển tiền chính xác số trên.</p>
            </div>

            <button id="paymentCompleteBtn" class="btn-complete-payment">ĐÃ HOÀN THÀNH CHUYỂN TIỀN</button>
            <p class="warning-text">Vui lòng chỉ nhấn nút sau khi đã chuyển khoản thành công!</p>
        </div>
    </div>
</div>

<style>
/* CSS cơ bản cho Modal */
.modal {
    display: none; 
    position: fixed;
    z-index: 2000; 
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    overflow: auto;
    background-color: rgba(0,0,0,0.4); 
}
.modal-content {
    background-color: #fefefe;
    margin: 7% auto; 
    padding: 20px;
    border: 1px solid #888;
    width: 80%; 
    max-width: 400px;
    border-radius: 8px;
    text-align: center;
}
.close-btn {
    color: #aaa;
    float: right;
    font-size: 28px;
    font-weight: bold;
    cursor: pointer;
}
.qr-code-area {
    margin: 20px 0;
    border: 1px dashed #ccc;
    padding: 15px;
}
.btn-complete-payment {
    background-color: #4CAF50;
    color: white;
    padding: 10px 15px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    margin-top: 15px;
}
.warning-text {
    font-size: 0.8em;
    color: #ff0000;
    margin-top: 5px;
}
</style>

---


<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script> 
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>

<script src="assets/js/cart_api.js"></script> 

<script src="assets/js/cart_render.js"></script>