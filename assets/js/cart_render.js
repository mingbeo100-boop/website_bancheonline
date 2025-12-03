// Tên file: assets/js/cart_render.js
// Giả định: updateCartItem (từ cart_api.js) và các thư viện khác đã có sẵn.

document.addEventListener('DOMContentLoaded', function () {
    const cartContainer = document.getElementById('cartItems');
    const totalContainer = document.getElementById('totalPrice');
    const selectAllCart = document.getElementById('selectAllCart');
    const removeSelected = document.getElementById('removeSelected');

    const checkoutBtn = document.getElementById('checkoutBtn');
    const qrModal = document.getElementById('qrModal');
    const modalTotalPriceContainer = document.getElementById('modalTotalPrice');
    const closeBtn = document.querySelector('.close-btn');
    const paymentCompleteBtn = document.getElementById('paymentCompleteBtn');
    
    let currentTotalAmount = 0;

    // --- HÀM RENDER GIỎ HÀNG ---
    function toSlug(text) {
    return text
        .normalize("NFD").replace(/[\u0300-\u036f]/g, "") // bỏ dấu tiếng Việt
        .toLowerCase()
        .trim()
        .replace(/\s+/g, "-")         // khoảng trắng → -
        .replace(/[^a-z0-9\-]/g, ""); // ký tự lạ → bỏ
}

// ---------------- DANH SÁCH FILE ẢNH ĐANG CÓ ----------------
const validImages = [
    "caramen-hoa-qua.png",
    "caramen-thach-hoa-qua.png",
    "che-thai-buoi.png",
    "che-thai-caramen.png",
    "che-thai-dua.png",
    "che-thai-hoa-qua.png",
    "che-thai-khoai-deo.png",
    "che-thai-sau-hoa-qua.png",
    "dua-dam-thai-sau-rieng.png",
    "dua-dam-thai.png",
    "sua-chua-hoa-qua.png",
    "sua-chua-mit.png",
    "sua-chua-nep-cam.png",
    "sua-chua-thach-oc-que.png"
];

// ---------------- HÀM LẤY ẢNH TỪ TÊN MÓN ----------------
function getImagePathByName(productName) {
    const slug = toSlug(productName);
    const fileName = slug + ".png";

    if (validImages.includes(fileName)) {
        return "assets/images/menu/" + fileName;
    }

    return "assets/images/menu/default.png"; // fallback
}
    function renderCart() {
        updateCartItem('get_cart', 0)
        .then(data => {
            if (!data.success) {
                cartContainer.innerHTML = `<p style="text-align:center; color:red;">${data.message}</p>`;
                totalContainer.textContent = '0 ₫';
                currentTotalAmount = 0;
                
                return;
            }

            const cartItems = data.items;
            if (cartItems.length === 0) {
                cartContainer.innerHTML = '<p style="text-align:center; color:#888;">🛒 Giỏ hàng của bạn trống.</p>';
                totalContainer.textContent = '0 ₫';
                currentTotalAmount = 0;
                
                return;
            }
            
            let total = 0;

            const currencyOptions = { maximumFractionDigits: 0 };

            cartContainer.innerHTML = cartItems.map(item => {
                // Giá trị tiền tệ đã là số (ví dụ: 28000)
                const price = parseFloat(item.price)* 1000; 
                const itemTotal = price * item.quantity;
                total += itemTotal;

               const imgUrl = item.img ? `assets/images/menu/${item.img}`: getImagePathByName(item.name);

                return `
                    <div class="cart-item" data-product-id="${item.product_id}">
                        <input type="checkbox" class="item-checkbox" checked>
                        <img src="${imgUrl}">
                        <div class="item-info">
                            <strong>${item.name}</strong>
                            <p>${price.toLocaleString('vi-VN')} ₫</p>
                        </div>
                        <div class="quantity">
                            <button class="decrease">-</button>
                            <span>${item.quantity}</span>
                            <button class="increase">+</button>
                        </div>
                        <p>${itemTotal.toLocaleString('vi-VN')} ₫</p>
                    </div>
                `;
            }).join('');

            currentTotalAmount = total;
            totalContainer.textContent = total.toLocaleString('vi-VN') + ' ₫';

            attachCartEventListeners();
        });
    }
    
    // --- HÀM GÁN EVENT CHO CÁC NÚT (Giữ nguyên logic) ---
    function attachCartEventListeners() {
        // Logic Tăng/Giảm (Giữ nguyên)
        document.querySelectorAll('.increase, .decrease').forEach((btn) => {
            btn.addEventListener('click', () => {
                const itemDiv = btn.closest('.cart-item');
                const productId = itemDiv.dataset.productId;
                let currentQuantity = parseInt(itemDiv.querySelector('.quantity span').textContent);
                let newQuantity = btn.classList.contains('increase') ? currentQuantity + 1 : currentQuantity - 1;

                const promise = (newQuantity < 1) 
                    ? updateCartItem('remove_item', productId) 
                    : updateCartItem('update_quantity', productId, newQuantity);

                promise.then(data => {
                    if (data.success) {
                        renderCart();
                    } else {
                        Swal.fire('Lỗi', data.message, 'error');
                    }
                });
            });
        });

        // Xóa nhiều item cùng lúc (Giữ nguyên)
        removeSelected?.addEventListener('click', function () {
            const selected = [...document.querySelectorAll('.item-checkbox:checked')];
            if (selected.length === 0) { Swal.fire('Chú ý', 'Bạn chưa chọn sản phẩm nào.', 'warning'); return; }

            const ids = selected.map(cb => cb.closest('.cart-item').dataset.productId);
            Promise.all(ids.map(id => updateCartItem('remove_item', id)))
                .then(results => {
                    const successfulDeletes = results.filter(r => r.success).length;
                    if (successfulDeletes > 0) {
                        Swal.fire('Thành Công!', `Đã xóa ${successfulDeletes} sản phẩm.`, 'success');
                    }
                    renderCart();
                });
        });
        
        // Logic Checkbox và Select All (Giữ nguyên)
        const all = document.querySelectorAll('.item-checkbox');
        const checked = document.querySelectorAll('.item-checkbox:checked');
        if (selectAllCart) selectAllCart.checked = (all.length > 0 && all.length === checked.length);
        
        document.querySelectorAll('.item-checkbox').forEach(cb => {
            cb.addEventListener('change', updateSelectAllState);
        });
        
        updateSelectAllState();
    }
    
    // --- LOGIC MODAL & THANH TOÁN (HOÀN THIỆN) ---
   function generateQRCode(amount) {
    const qrCodeContainer = document.getElementById('qrcode');
    if (typeof QRCode === 'undefined') {
        qrCodeContainer.innerHTML = 'Lỗi: Thư viện QRCode.js bị thiếu.';
        return;
    }

    const bankId = '970422'; // Ví dụ: Ngân hàng TMCP Quân đội (MB)
    const accountNumber = '0796727753'; // Số tài khoản nhận tiền
    const transferAmount = amount.toFixed(0); // Đảm bảo không có thập phân
    const transferNote = 'THANHTOAN_CHE'; // Nội dung chuyển khoản (không dấu, không khoảng trắng)

    // Chuỗi dữ liệu chuẩn VietQR (Cần code backend phức tạp hơn để tạo chuẩn chính xác)
    // Để đơn giản, chúng ta sẽ tạo chuỗi định dạng nhanh được nhiều app ngân hàng nhận diện:
   const paymentInfo =`
ID Bank:              ${bankId}
STK:                  ${accountNumber}
Tong tien:              ${transferAmount}
Noi dung thanh toan:  ${transferNote} `;
    // Nếu bạn muốn hiển thị thông báo thân thiện hơn:
    const friendlyText = `Chuyển khoản: ${accountNumber} - 
                          Ngân hàng VP Bank
                          Số tiền: ${amount.toLocaleString('vi-VN')} VND. 
                          Nội dung: ${transferNote}`;

    qrCodeContainer.innerHTML = '';
    new QRCode(qrCodeContainer, { 
        // QUAN TRỌNG: Sử dụng chuỗi định dạng có cấu trúc
        text: paymentInfo, 
        width: 180, 
        height: 180 
    });
}
    
    // 1. Mở Modal khi nhấn THANH TOÁN
   checkoutBtn.addEventListener('click', function() {
    
    if (currentTotalAmount <= 0 ) { 
        // Hiển thị thông báo khi "chưa mua" hoặc giỏ hàng trống
        Swal.fire({ 
            title: 'Giỏ hàng trống!', 
            text: 'Bạn chưa thêm sản phẩm nào vào giỏ để thanh toán. Vui lòng quay lại Menu.', 
            icon: 'warning', // Đổi icon sang warning cho rõ ràng
            confirmButtonText: 'Đã hiểu' 
        }); 
        // KHÔNG CHẠY PHẦN CÒN LẠI CỦA CODE VÀ DỪNG LẠI
        return; 
    }

        modalTotalPriceContainer.textContent = currentTotalAmount.toLocaleString('vi-VN', { maximumFractionDigits: 0 }) + ' ₫';
        generateQRCode(currentTotalAmount); // Gọi hàm tạo QR
        qrModal.style.display = 'block';
    });
    
    // 2. Đóng Modal
    closeBtn.addEventListener('click', function() { qrModal.style.display = 'none'; });
    window.addEventListener('click', function(event) {
        if (event.target === qrModal) { qrModal.style.display = 'none'; }
    });

    // 3. Hoàn tất Thanh toán (Xóa giỏ hàng trên DB)
    paymentCompleteBtn.addEventListener('click', function () {
        
        // 1. Gọi API để CẬP NHẬT trạng thái đơn hàng (checkout_complete)
        updateCartItem('checkout_complete', 0).then(data => {
            if (data.success) {
                Swal.fire(
                    'Thành công!', 
                    'Đơn hàng đã được xác nhận. Vui lòng thêm sản phẩm mới để tiếp tục mua sắm.', 
                    'success'
                );
                
                // 2. Sau khi xác nhận thành công, gọi renderCart()
                // Backend sẽ tạo giỏ mới (status='pending') và Frontend hiển thị giỏ trống.
                renderCart(); 
                
            } else {
                Swal.fire('Lỗi', data.message, 'error');
            }
            
            // 3. Đóng modal sau khi xử lý xong (dù thành công hay thất bại)
            qrModal.style.display = 'none'; 
 });
            });


    // KHỞI CHẠY CHÍNH
    renderCart();
    window.renderCart = renderCart;
});