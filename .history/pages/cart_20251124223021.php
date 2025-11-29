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

    <div id="qrModal" class="modal">
        <div class="modal-content">
            <span class="close-btn">&times;</span>
            <h3 style="font-weight: bold;">💰 Thanh toán bằng Chuyển khoản QR</h3>
            <p>Tổng số tiền cần thanh toán:</p>
            <h4 id="modalTotalPrice" style="color:black; font-weight: bold;">0 ₫</h4>

            <div class="qr-code-area">
                <div id="qrcode"></div>
                <p style="font-size: 0.9em; margin-top: 10px;">Quét mã QR để chuyển tiền chính xác số trên.</p>
            </div>

            <button id="paymentCompleteBtn" class="btn-complete-payment">ĐÃ HOÀN THÀNH CHUYỂN TIỀN</button>
            <p class="warning-text">Vui lòng chỉ nhấn nút sau khi đã chuyển khoản thành công!</p>
        </div>
    </div>

    <style>
        /* CSS cơ bản cho Modal */
        .modal {
            display: none;
            position: fixed;
            z-index: 9999 !important;
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
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const cartContainer = document.getElementById('cartItems');
        const totalContainer = document.getElementById('totalPrice');
        const selectAllCart = document.getElementById('selectAllCart');
        const removeSelected = document.getElementById('removeSelected');

        // 🎯 KHAI BÁO BIẾN CHO MODAL 🎯
        const checkoutBtn = document.getElementById('checkoutBtn');
        const qrModal = document.getElementById('qrModal');
        const modalTotalPriceContainer = document.getElementById('modalTotalPrice');
        const closeBtn = document.querySelector('.close-btn');
        const paymentCompleteBtn = document.getElementById('paymentCompleteBtn');

        let currentTotalAmount = 0; // Biến lưu tổng tiền dưới dạng số
        let qrCodeInstance = null; // Biến lưu đối tượng QR Code

        // Thoát nếu các phần tử chính không tồn tại
        if (!cartContainer || !totalContainer || !checkoutBtn) return;

        // Lấy dữ liệu giỏ hàng
        let cartItems = JSON.parse(localStorage.getItem('cartItems')) || [];

        // --- HÀM TẠO MÃ QR ---
        function generateQRCode(amount) {
            // Dọn dẹp mã QR cũ nếu đã tồn tại
            const qrCodeContainer = document.getElementById('qrcode');
            if (qrCodeContainer) {
                qrCodeContainer.innerHTML = '';
            }

            // Nội dung mã QR (Cần thay đổi bằng thông tin STK/Ngân hàng thực tế)
            const paymentInfo = `STK: 0123456789 | NGAN HANG: VIETCOMBANK | SOTIEN: ${amount} | NOIDUNG: THANHTOAN_CHEXK`;

            // Khởi tạo mã QR bằng qrcode.js
            qrCodeInstance = new QRCode(qrCodeContainer, {
                text: paymentInfo,
                width: 180,
                height: 180,
                colorDark: "#000000",
                colorLight: "#ffffff",
                correctLevel: QRCode.CorrectLevel.H
            });
        }

        // --- HÀM RENDER VÀ LƯU GIỎ HÀNG ---
        function saveAndRender() {
            localStorage.setItem('cartItems', JSON.stringify(cartItems));
            renderCart();
        }

        // Hàm cập nhật trạng thái "Chọn tất cả"
        function updateSelectAllState() {
            const totalCheckboxes = document.querySelectorAll('.item-checkbox').length;
            const checkedCheckboxes = document.querySelectorAll('.item-checkbox:checked').length;
            // Đảm bảo selectAllCart tồn tại trước khi cập nhật
            if (selectAllCart) {
                selectAllCart.checked = (totalCheckboxes > 0 && totalCheckboxes === checkedCheckboxes);
            }
        }

        // --- HÀM RENDER GIỎ HÀNG CHÍNH ---
        function renderCart() {
            if (cartItems.length === 0) {
                cartContainer.innerHTML = '<p style="text-align:center; color:#888;">🛒 Giỏ hàng của bạn đang trống.</p>';
                totalContainer.textContent = '0 ₫';
                currentTotalAmount = 0;
                checkoutBtn.disabled = true; // Vô hiệu hóa nút thanh toán
                return;
            }

            checkoutBtn.disabled = false; // Kích hoạt nút thanh toán

            let total = 0;
            cartContainer.innerHTML = cartItems.map((item, index) => {
                // Lọc bỏ ký tự không phải số và chuyển sang số thực
                const priceValue = parseFloat(item.price.replace(/[^\d]/g, ''));
                const itemTotal = priceValue * item.quantity;
                total += itemTotal;

                return `
                <div class="cart-item" data-index="${index}">
                    <input type="checkbox" class="item-checkbox">
                    <img src="${item.img}" alt="${item.name}">
                    <div class="item-info">
                        <strong>${item.name}</strong>
                        <p>${item.price}</p>
                    </div>
                    <div class="quantity">
                        <button class="decrease">-</button>
                        <span>${item.quantity}</span>
                        <button class="increase">+</button>
                    </div>
                    <p>${itemTotal.toLocaleString()} ₫</p>
                </div>
            `;
            }).join('');

            currentTotalAmount = total; // Cập nhật tổng tiền số
            totalContainer.textContent = total.toLocaleString() + ' ₫';

            // Gán sự kiện cho các nút tăng giảm số lượng
            document.querySelectorAll('.increase').forEach((btn, i) => {
                btn.addEventListener('click', () => {
                    // Lấy index dựa trên data-index của item cha để tránh lỗi do render lại
                    const itemIndex = parseInt(btn.closest('.cart-item').dataset.index);
                    cartItems[itemIndex].quantity++;
                    saveAndRender();
                });
            });

            document.querySelectorAll('.decrease').forEach((btn, i) => {
                btn.addEventListener('click', () => {
                    const itemIndex = parseInt(btn.closest('.cart-item').dataset.index);
                    if (cartItems[itemIndex].quantity > 1) {
                        cartItems[itemIndex].quantity--;
                    } else {
                        cartItems.splice(itemIndex, 1);
                    }
                    saveAndRender();
                });
            });

            // Cập nhật trạng thái của Select All và gán lại sự kiện change cho checkbox
            updateSelectAllState();
            document.querySelectorAll('.item-checkbox').forEach(cb => {
                cb.addEventListener('change', updateSelectAllState);
            });
        }

        // --- LOGIC CHỌN TẤT CẢ ---
        if (selectAllCart) {
            selectAllCart.addEventListener('change', function() {
                document.querySelectorAll('.item-checkbox').forEach(cb => {
                    cb.checked = this.checked;
                });
            });
        }

        // --- LOGIC XÓA ĐÃ CHỌN (Đã thay thế alert) ---
        if (removeSelected) {
            removeSelected.addEventListener('click', function() {
                const selectedItems = Array.from(document.querySelectorAll('.item-checkbox')).filter(cb => cb.checked);

                if (selectedItems.length === 0) {
                    // ⚡ THAY THẾ ALERT() BẰNG SWEETALERT2
                    Swal.fire({
                        title: 'Lỗi',
                        text: 'Vui lòng chọn ít nhất một sản phẩm để xóa.',
                        icon: 'warning',
                        confirmButtonText: 'Đã hiểu'
                    });
                    return;
                }

                // Lấy data-index của các item cần xóa (theo thứ tự giảm dần để splice không bị lệch index)
                const indexesToRemove = selectedItems
                    .map(cb => parseInt(cb.closest('.cart-item').dataset.index))
                    .sort((a, b) => b - a);

                indexesToRemove.forEach(index => {
                    cartItems.splice(index, 1);
                });

                saveAndRender();

                // ⚡ THAY THẾ ALERT() BẰNG SWEETALERT2 (Thông báo thành công)
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
        }


        // --- LOGIC MODAL & THANH TOÁN QR (Đã thay thế alert) ---

        // 1. Mở Modal khi nhấn THANH TOÁN
        checkoutBtn.addEventListener('click', function() {
            if (cartItems.length === 0) {
                // ⚡ THAY THẾ ALERT() BẰNG SWEETALERT2
                Swal.fire({
                    title: 'Giỏ hàng trống!',
                    text: 'Vui lòng thêm sản phẩm trước khi thanh toán.',
                    icon: 'info',
                    confirmButtonText: 'Đã hiểu'
                });
                return;
            }

            // Hiển thị tổng tiền trong Modal
            modalTotalPriceContainer.textContent = currentTotalAmount.toLocaleString() + ' ₫';

            // Tạo Mã QR
            generateQRCode(currentTotalAmount);

            qrModal.style.display = 'block';
        });

        // 2. Đóng Modal khi nhấn X hoặc click ra ngoài
        closeBtn.addEventListener('click', function() {
            qrModal.style.display = 'none';
        });

        window.addEventListener('click', function(event) {
            if (event.target == qrModal) {
                qrModal.style.display = 'none';
            }
        });

        // 3. Xử lý nút ĐÃ HOÀN THÀNH CHUYỂN TIỀN
        paymentCompleteBtn.addEventListener('click', function() {
            // ⚡ THAY THẾ ALERT() BẰNG SWEETALERT2
            Swal.fire({
                title: 'Thanh toán thành công!',
                text: `Yêu cầu thanh toán ${currentTotalAmount.toLocaleString()} ₫ đã được ghi nhận. Quán xin chúc bạn có một trải nghiệm tuyệt vời. Cảm ơn bạn!`,
                icon: 'success',
                confirmButtonText: 'Tuyệt vời!'
            });

            // Xóa giỏ hàng sau khi xác nhận thanh toán
            cartItems = [];
            saveAndRender();
            qrModal.style.display = 'none';
        });


        renderCart(); // Gọi hàm render ban đầu
    });
</script>