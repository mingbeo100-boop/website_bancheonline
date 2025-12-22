@ -1,98 +1,286 @@
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

    <!-- CSS giữ nguyên -->
    <style>
        .modal {
            display: none;
            position: fixed;
            z-index: 2000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.4);
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

        .swal2-container {
            z-index: 99999 !important;
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
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<!-- Thêm CSS cho Leaflet -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">

<!-- Thêm JS cho Leaflet -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const cartContainer = document.getElementById('cartItems');
        const totalContainer = document.getElementById('totalPrice');
        const selectAllCart = document.getElementById('selectAllCart');
        const removeSelected = document.getElementById('removeSelected');
        const checkoutBtn = document.getElementById('checkoutBtn');
        const qrModal = document.getElementById('qrModal');
        const closeBtn = document.querySelector('.close-btn');
        const paymentCompleteBtn = document.getElementById('paymentCompleteBtn');
        const submitBuyerInfoBtn = document.getElementById('submitBuyerInfo');
        const buyerInfoForm = document.getElementById('buyerInfoForm');
        const qrPaymentSection = document.getElementById('qrPaymentSection');
        const modalTotalPriceContainer = document.getElementById('modalTotalPrice');
        const applyDiscountBtn = document.getElementById('applyDiscount');
        const discountInput = document.getElementById('discountCode');

        let discountApplied = false; // để tránh áp dụng nhiều lần
        let currentTotalAmount = 0;
        let cartItems = JSON.parse(localStorage.getItem('cartItems')) || [];
        let qrCodeInstance = null;

        // --- Hàm tạo QR ---
        function generateQRCode(amount) {
            const qrCodeContainer = document.getElementById('qrcode');
            qrCodeContainer.innerHTML = '';
            const paymentInfo = `STK:0123456789 | NGAN HANG:VIETCOMBANK | SOTIEN:${amount} | NOIDUNG:THANHTOAN_CHEXK`;
            qrCodeInstance = new QRCode(qrCodeContainer, {
                text: paymentInfo,
                width: 180,
                height: 180,
                colorDark: "#000",
                colorLight: "#fff",
                correctLevel: QRCode.CorrectLevel.H
            });
        }

        applyDiscountBtn.addEventListener('click', function() {
            const code = discountInput.value.trim();

            if (discountApplied) {
                Swal.fire({
                    title: 'Thông báo',
                    text: 'Bạn đã áp dụng mã giảm giá rồi!',
                    icon: 'info'
                });
                return;
            }

            if (code === 'luckydacs2') {
                // Giảm 50% tổng tiền
                currentTotalAmount = currentTotalAmount * 0.5;
                document.getElementById('totalPrice').textContent = currentTotalAmount.toLocaleString() + ' ₫';

                Swal.fire({
                    title: 'Thành công!',
                    text: 'Mã giảm giá luckydacs2 đã được áp dụng. Tổng tiền giảm 50%.',
                    icon: 'success'
                });

                discountApplied = true; // đánh dấu đã áp dụng
            } else {
                Swal.fire({
                    title: 'Sai mã',
                    text: 'Mã giảm giá không hợp lệ.',
                    icon: 'error'
                });
            }
        });

        // --- Hàm Google Maps ---
        let map, marker;

        function initMap() {
            // Tọa độ ví dụ: trung tâm TP.HCM
            const defaultLatLng = [16.041, 108.221];


            map = L.map('map').setView(defaultLatLng, 16);

            // Thêm layer OpenStreetMap
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
            }).addTo(map);

            // Thêm marker có thể kéo thả
            marker = L.marker(defaultLatLng, {
                draggable: true
            }).addTo(map);

            marker.on('dragend', function(e) {
                const pos = e.target.getLatLng();
                console.log("Vị trí mới:", pos.lat, pos.lng);
            });
        }

        // Gọi initMap khi hiển thị form thông tin người mua
        document.getElementById('paymentCompleteBtn').addEventListener('click', function() {
            document.getElementById('qrcode').style.display = 'none';
            this.style.display = 'none';
            document.getElementById('modalTotalPrice').style.display = 'none';
            document.getElementById('buyerInfoForm').style.display = 'block';
            qrModal.scrollTop = 0;

            setTimeout(initMap, 100); // delay nhỏ để div map render đúng
        });

        // --- Render giỏ hàng ---
        function saveAndRender() {
            localStorage.setItem('cartItems', JSON.stringify(cartItems));
            renderCart();
        }

        function updateSelectAllState() {
            const totalCheckboxes = document.querySelectorAll('.item-checkbox').length;
            const checkedCheckboxes = document.querySelectorAll('.item-checkbox:checked').length;
            if (selectAllCart) selectAllCart.checked = (totalCheckboxes > 0 && totalCheckboxes === checkedCheckboxes);
        }

        function renderCart() {
            if (cartItems.length === 0) {
                cartContainer.innerHTML = '<p style="text-align:center; color:#888;">🛒 Giỏ hàng của bạn đang trống.</p>';
                totalContainer.textContent = '0 ₫';
                currentTotalAmount = 0;
                checkoutBtn.disabled = true;
                return;
            }
            checkoutBtn.disabled = false;
            let total = 0;
            cartContainer.innerHTML = cartItems.map((item, index) => {
                const priceValue = parseFloat(item.price.replace(/[^\d]/g, ''));
                const itemTotal = priceValue * item.quantity;
                total += itemTotal;
                return `
            <div class="cart-item" data-index="${index}">
                <input type="checkbox" class="item-checkbox">
                <img src="${item.img}" alt="${item.name}">
                <div class="item-info"><strong>${item.name}</strong><p>${item.price}</p></div>
                <div class="quantity">
                    <button class="decrease">-</button>
                    <span>${item.quantity}</span>
                    <button class="increase">+</button>
                </div>
                <p>${itemTotal.toLocaleString()} ₫</p>
            </div>`;
            }).join('');
            currentTotalAmount = total;
            totalContainer.textContent = total.toLocaleString() + ' ₫';

            document.querySelectorAll('.increase').forEach(btn => {
                btn.addEventListener('click', () => {
                    const itemIndex = parseInt(btn.closest('.cart-item').dataset.index);
                    cartItems[itemIndex].quantity++;
                    saveAndRender();
                });
            });

            document.querySelectorAll('.decrease').forEach(btn => {
                btn.addEventListener('click', () => {
                    const itemIndex = parseInt(btn.closest('.cart-item').dataset.index);
                    if (cartItems[itemIndex].quantity > 1) cartItems[itemIndex].quantity--;
                    else cartItems.splice(itemIndex, 1);
                    saveAndRender();
                });
            });

            updateSelectAllState();
            document.querySelectorAll('.item-checkbox').forEach(cb => cb.addEventListener('change', updateSelectAllState));
        }

        // --- Chọn tất cả ---
        if (selectAllCart) selectAllCart.addEventListener('change', () => document.querySelectorAll('.item-checkbox').forEach(cb => cb.checked = selectAllCart.checked));

        // --- Xóa đã chọn ---
        if (removeSelected) removeSelected.addEventListener('click', () => {
            const selectedItems = Array.from(document.querySelectorAll('.item-checkbox')).filter(cb => cb.checked);
            if (selectedItems.length === 0) {
                Swal.fire({
                    title: 'Lỗi',
                    text: 'Vui lòng chọn ít nhất một sản phẩm để xóa.',
                    icon: 'warning',
                    confirmButtonText: 'Đã hiểu'
                });
                return;
            }
            const indexesToRemove = selectedItems.map(cb => parseInt(cb.closest('.cart-item').dataset.index)).sort((a, b) => b - a);
            indexesToRemove.forEach(index => cartItems.splice(index, 1));
            saveAndRender();
            Swal.fire({
                title: 'Thành Công!',
                text: `Đã xóa ${indexesToRemove.length} sản phẩm khỏi giỏ hàng.`,
                icon: 'success',
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 2000
            });
        });

        // --- Mở modal (hiển thị form đầu tiên) ---
        checkoutBtn.addEventListener('click', () => {
            if (cartItems.length === 0) {
                Swal.fire({
                    title: 'Giỏ hàng trống!',
                    text: 'Vui lòng thêm sản phẩm trước khi thanh toán.',
                    icon: 'info',
                    confirmButtonText: 'Đã hiểu'
                });
                return;
            }
            buyerInfoForm.style.display = 'block';
            qrPaymentSection.style.display = 'none';
            qrModal.style.display = 'block';
            initMap();
        });

        // --- Đóng modal ---
        closeBtn.addEventListener('click', () => qrModal.style.display = 'none');

        // --- Xác nhận thông tin người nhận ---
        submitBuyerInfoBtn.addEventListener('click', () => {
            const name = document.getElementById('buyerName').value;
            const phone = document.getElementById('buyerPhone').value;
            const address = document.getElementById('buyerAddress').value;
            const note = document.getElementById('buyerNote').value;

            if (!name || !phone || !address) {
                Swal.fire({
                    title: 'Lỗi',
                    text: 'Vui lòng điền đủ thông tin',
                    icon: 'warning'
                });
                return;
            }

            buyerInfoForm.style.display = 'none';
            qrPaymentSection.style.display = 'block';

            modalTotalPriceContainer.textContent = currentTotalAmount.toLocaleString() + ' ₫';
            generateQRCode(currentTotalAmount);
        });

        // --- Hoàn thành thanh toán ---
        paymentCompleteBtn.addEventListener('click', () => {
            Swal.fire({
                title: 'Thanh toán thành công!',
                text: `Yêu cầu thanh toán ${currentTotalAmount.toLocaleString()} ₫ đã được ghi nhận. Đơn hàng của bạn sẽ sớm được giao!`,
                icon: 'success'
            });
            qrModal.style.display = 'none';
            cartItems = [];
            saveAndRender();
        });

        renderCart();
    });
</script>
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
