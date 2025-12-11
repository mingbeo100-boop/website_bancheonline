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
                    ? updateCartItem('remove_item', productId,0) 
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
    

    
let isAddressConfirmed = false;

function redirectToOrderPage(totalAmount) {
    // THỰC TẾ: Gọi API để tạo đơn hàng nháp và nhận ORDER_ID thực
    const tempOrderId =  Date.now(); 
    
    const redirectURL = `index.php?page=donhang&order_id=${tempOrderId}&total=${totalAmount}`; 

    // Thông báo trước khi chuyển trang (Tùy chọn)
    Swal.fire({
        title: 'Đang chuẩn bị Đơn hàng...',
        text: 'Chuyển đến trang xác nhận địa chỉ và chọn phương thức thanh toán.',
        icon: 'info',
        showConfirmButton: false,
        timer: 2000,
        timerProgressBar: true
    });
    
    // Chuyển hướng sau khi thông báo hiện lên
    setTimeout(() => {
        window.location.href = redirectURL;
    }, 1500);
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

    if (!isAddressConfirmed) { 
        Swal.fire({
            title: 'Tiến hành Đặt hàng',
            text: 'Vui lòng xác nhận vị trí giao hàng và phương thức thanh toán.',
            icon: 'info', // Đổi icon sang info vì đây là bước tiếp theo, không phải lỗi
            confirmButtonText: 'Bắt đầu'
        }).then((result) => {
            if (result.isConfirmed) {
                // Chuyển thẳng đến trang DonHang để hoàn tất địa chỉ và chọn thanh toán
                redirectToOrderPage(currentTotalAmount);
            }
        });
        return; 
    }

    
    redirectToOrderPage(currentTotalAmount);

        modalTotalPriceContainer.textContent = currentTotalAmount.toLocaleString('vi-VN', { maximumFractionDigits: 0 }) + ' ₫';
        generateQRCode(currentTotalAmount); // Gọi hàm tạo QR
        qrModal.style.display = 'block';
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