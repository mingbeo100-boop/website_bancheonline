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

            <!-- Form nhập thông tin người nhận -->
            <div id="buyerInfoForm" style="display:none; text-align:left; margin-top:20px;">
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

            <!-- Phần QR & tổng tiền -->
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
<script src="https://maps.googleapis.com/maps/api/js?key=YOUR_API_KEY"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const cartContainer = document.getElementById('cartItems');
    const totalContainer = document.getElementById('totalPrice');
    const selectAllCart = document.getElementById('selectAllCart');
    const removeSelected = document.getElementById('removeSelected');
    const checkoutBtn = document.getElementById('checkoutBtn');
    const qrModal = document.getElementById('qrModal');
    const modalTotalPriceContainer = document.getElementById('modalTotalPrice');
    const closeBtn = document.querySelector('.close-btn');
    const paymentCompleteBtn = document.getElementById('paymentCompleteBtn');
    const submitBuyerInfoBtn = document.getElementById('submitBuyerInfo');

    let currentTotalAmount = 0;
    let qrCodeInstance = null;
    let cartItems = JSON.parse(localStorage.getItem('cartItems')) || [];

    if (!cartContainer || !totalContainer || !checkoutBtn) return;

    // --- Hàm tạo QR ---
    function generateQRCode(amount) {
        const qrCodeContainer = document.getElementById('qrcode');
        qrCodeContainer.innerHTML = '';
        const paymentInfo = `STK: 0123456789 | NGAN HANG: VIETCOMBANK | SOTIEN: ${amount} | NOIDUNG: THANHTOAN_CHEXK`;
        qrCodeInstance = new QRCode(qrCodeContainer, {
            text: paymentInfo,
            width: 180,
            height: 180,
            colorDark: "#000000",
            colorLight: "#ffffff",
            correctLevel: QRCode.CorrectLevel.H
        });
    }

    // --- Hàm render giỏ hàng ---
    function saveAndRender() {
        localStorage.setItem('cartItems', JSON.stringify(cartItems));
        renderCart();
    }

    function updateSelectAllState() {
        const totalCheckboxes = document.querySelectorAll('.item-checkbox').length;
        const checkedCheckboxes = document.querySelectorAll('.item-checkbox:checked').length;
        if (selectAllCart) {
            selectAllCart.checked = (totalCheckboxes > 0 && totalCheckboxes === checkedCheckboxes);
        }
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
                if (cartItems[itemIndex].quantity > 1) {
                    cartItems[itemIndex].quantity--;
                } else {
                    cartItems.splice(itemIndex, 1);
                }
                saveAndRender();
            });
        });

        updateSelectAllState();
        document.querySelectorAll('.item-checkbox').forEach(cb => {
            cb.addEventListener('change', updateSelectAllState);
        });
    }

    // --- Chọn tất cả ---
    if (selectAllCart) {
        selectAllCart.addEventListener('change', function() {
            document.querySelectorAll('.item-checkbox').forEach(cb => cb.checked = this.checked);
        });
    }

    // --- Xóa đã chọn ---
    if (removeSelected) {
        removeSelected.addEventListener('click', function() {
            const selectedItems = Array.from(document.querySelectorAll('.item-checkbox')).filter(cb => cb.checked);
            if (selectedItems.length === 0) {
                Swal.fire({title: 'Lỗi', text: 'Vui lòng chọn ít nhất một sản phẩm để xóa.', icon: 'warning', confirmButtonText: 'Đã hiểu'});
                return;
            }
            const indexesToRemove = selectedItems.map(cb => parseInt(cb.closest('.cart-item').dataset.index)).sort((a,b)=>b-a);
            indexesToRemove.forEach(index => cartItems.splice(index,1));
            saveAndRender();
            Swal.fire({title:'Thành Công!', text:`Đã xóa ${indexesToRemove.length} sản phẩm khỏi giỏ hàng.`, icon:'success', toast:true, position:'top-end', showConfirmButton:false, timer:2000});
        });
    }

    // --- Modal thanh toán ---
    checkoutBtn.addEventListener('click', function() {
        if (cartItems.length === 0) {
            Swal.fire({title:'Giỏ hàng trống!', text:'Vui lòng thêm sản phẩm trước khi thanh toán.', icon:'info', confirmButtonText:'Đã hiểu'});
            return;
        }
        modalTotalPriceContainer.textContent = currentTotalAmount.toLocaleString() + ' ₫';
        generateQRCode(currentTotalAmount);
        qrModal.style.display = 'block';
        // Reset hiển thị QR/form nếu mở lại modal
        document.getElementById('qrcode').style.display = 'block';
        paymentCompleteBtn.style.display = 'inline-block';
        modalTotalPriceContainer.style.display = 'block';
        document.getElementById('buyerInfoForm').style.display = 'none';
    });

    closeBtn.addEventListener('click', () => qrModal.style.display = 'none');
    window.addEventListener('click', e => { if(e.target == qrModal) qrModal.style.display='none'; });

    paymentCompleteBtn.addEventListener('click', function() {
        document.getElementById('qrcode').style.display = 'none';
        paymentCompleteBtn.style.display = 'none';
        modalTotalPriceContainer.style.display = 'none';
        document.getElementById('buyerInfoForm').style.display = 'block';
        qrModal.scrollTop = 0;
        initMap();
    });

    submitBuyerInfoBtn.addEventListener('click', function() {
        const name = document.getElementById('buyerName').value;
        const phone = document.getElementById('buyerPhone').value;
        const address = document.getElementById('buyerAddress').value;
        const note = document.getElementById('buyerNote').value;

        if(!name || !phone || !address){
            Swal.fire({title:'Lỗi', text:'Vui lòng điền đủ thông tin', icon:'warning'});
            return;
        }

        console.log({name, phone, address, note});
        Swal.fire({title:'Cảm ơn bạn!', text:'Thông tin đã được ghi nhận. Shop sẽ liên hệ bạn sớm.', icon:'success'});

        qrModal.style.display = 'none';
        cartItems = [];
        saveAndRender();
    });

    renderCart();
});

// --- Google Maps ---
function initMap() {
    const mapDiv = document.getElementById('map');
    const map = new google.maps.Map(mapDiv, {center: {lat:10.762622,lng:106.660172}, zoom:16});
    const marker = new google.maps.Marker({position:{lat:10.762622,lng:106.660172}, map:map, draggable:true});
    marker.addListener('dragend', e => console.log("Vị trí mới:", e.latLng.lat(), e.latLng.lng()));
}
</script>
