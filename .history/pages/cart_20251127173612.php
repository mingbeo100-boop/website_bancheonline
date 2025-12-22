<div class="cart">
    <div class="cart-controls">
        <label>
            <input type="checkbox" id="selectAllCart"> Chọn tất cả
        </label>
        <button id="removeSelected" class="btn-remove-selected">XÓA ĐÃ CHỌN</button>
    </div>

    <div class="cart-items" id="cartItems">
        <!-- Các sản phẩm sẽ được thêm bằng JS -->
    </div>

    <div class="discount-box">
        <input type="text" id="discountCode" placeholder="Nhập mã giảm giá">
        <button id="applyDiscount">Áp dụng</button>
    </div>

    <div class="cart-total">
        Tổng tiền: <span id="totalPrice">0 ₫</span>
    </div>

    <button class="checkout" id="checkoutBtn">THANH TOÁN</button>

    <!-- Modal thanh toán -->
    <div id="qrModal" class="modal">
        <div class="modal-content">
            <span class="close-btn">&times;</span>

            <!-- FORM THÔNG TIN NGƯỜI NHẬN -->
            <div id="buyerInfoForm" style="text-align:left; display:none; margin-top:20px;">
                <h3>Thông tin người nhận</h3>
                <label>Họ tên:</label>
                <input type="text" id="buyerName" placeholder="Nhập họ tên" style="width:100%; margin-bottom:10px;">

                <label>Số điện thoại:</label>
                <input type="text" id="buyerPhone" placeholder="Nhập số điện thoại" style="width:100%; margin-bottom:10px;">

                <label>Địa chỉ:</label>
                <input type="text" id="buyerAddress" placeholder="Nhập địa chỉ" style="width:100%; margin-bottom:10px;">

                <label>Lưu ý cho shop:</label>
                <textarea id="buyerNote" placeholder="Nhập lưu ý" style="width:100%; margin-bottom:10px;"></textarea>

                <div id="map" style="width:100%; height:200px; margin-bottom:10px;"></div>

                <button id="submitBuyerInfo" class="btn-complete-payment">Xác nhận thông tin</button>
            </div>

            <!-- QR & TỔNG TIỀN (ẨN BAN ĐẦU) -->
            <div id="qrPaymentSection" style="display:none; text-align:center;">
                <h3 style="font-weight:bold;">💰 Thanh toán bằng Chuyển khoản QR</h3>
                <p>Tổng số tiền cần thanh toán:</p>
                <h4 id="modalTotalPrice" style="color:black; font-weight:bold;">0 ₫</h4>

                <div class="qr-code-area">
                    <div id="qrcode"></div>
                    <p style="font-size:0.9em; margin-top:10px;">Quét mã QR để chuyển tiền chính xác số trên.</p>
                </div>

                <button id="paymentCompleteBtn" class="btn-complete-payment">ĐÃ HOÀN THÀNH CHUYỂN TIỀN</button>
                <p class="warning-text">Vui lòng chỉ nhấn nút sau khi đã chuyển khoản thành công!</p>
            </div>
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