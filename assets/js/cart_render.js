// Tên file: assets/js/cart_render.js

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

    // --- 1. CÁC HÀM TIỆN ÍCH HỖ TRỢ XỬ LÝ ẢNH ---
    function toSlug(text) {
        return text
            .normalize("NFD").replace(/[\u0300-\u036f]/g, "")
            .toLowerCase()
            .trim()
            .replace(/\s+/g, "-")
            .replace(/[^a-z0-9\-]/g, "");
    }

    const validImages = [
        "caramen-hoa-qua.png", "caramen-thach-hoa-qua.png", "che-thai-buoi.png",
        "che-thai-caramen.png", "che-thai-dua.png", "che-thai-hoa-qua.png",
        "che-thai-khoai-deo.png", "che-thai-sau-hoa-qua.png", "dua-dam-thai-sau-rieng.png",
        "dua-dam-thai.png", "sua-chua-hoa-qua.png", "sua-chua-mit.png",
        "sua-chua-nep-cam.png", "sua-chua-thach-oc-que.png"
    ];

    function getImagePathByName(productName) {
        const slug = toSlug(productName);
        const fileName = slug + ".png";
        return validImages.includes(fileName) ? "assets/images/menu/" + fileName : "assets/images/menu/default.png";
    }

    // --- 2. HÀM CẬP NHẬT TRẠNG THÁI CHECKBOX (Sửa lỗi undefined) ---
    function updateSelectAllState() {
        const checkboxes = document.querySelectorAll('.item-checkbox');
        const checkedCount = document.querySelectorAll('.item-checkbox:checked').length;
        if (selectAllCart) {
            selectAllCart.checked = (checkboxes.length > 0 && checkboxes.length === checkedCount);
        }
    }

    // --- 3. HÀM RENDER GIỎ HÀNG ---
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
                if (!cartItems || cartItems.length === 0) {
                    cartContainer.innerHTML = '<p style="text-align:center; color:#888;">🛒 Giỏ hàng của bạn trống.</p>';
                    totalContainer.textContent = '0 ₫';
                    currentTotalAmount = 0;
return;
                }
                
                let total = 0;
                cartContainer.innerHTML = cartItems.map(item => {
                    const price = parseFloat(item.price) * 1000; 
                    const itemTotal = price * item.quantity;
                    total += itemTotal;
                    const imgUrl = item.img ? `assets/images/menu/${item.img}` : getImagePathByName(item.name);

                    return `
                        <div class="cart-item" data-product-id="${item.product_id}">
                            <input type="checkbox" class="item-checkbox" checked>
                            <img src="${imgUrl}" alt="${item.name}">
                            <div class="item-info">
                                <strong>${item.name}</strong>
                                <p>${price.toLocaleString('vi-VN')} ₫</p>
                            </div>
                            <div class="quantity">
                                <button class="decrease">-</button>
                                <span>${item.quantity}</span>
                                <button class="increase">+</button>
                            </div>
                            <p class="item-total-price">${itemTotal.toLocaleString('vi-VN')} ₫</p>
                        </div>
                    `;
                }).join('');

                currentTotalAmount = total;
                totalContainer.textContent = total.toLocaleString('vi-VN') + ' ₫';
                attachCartEventListeners();
            });
    }

    // --- 4. HÀM GÁN SỰ KIỆN ---
    function attachCartEventListeners() {
        // Sự kiện Tăng/Giảm số lượng
        document.querySelectorAll('.increase, .decrease').forEach((btn) => {
            btn.onclick = () => {
                const itemDiv = btn.closest('.cart-item');
                const productId = itemDiv.dataset.productId;
                let currentQuantity = parseInt(itemDiv.querySelector('.quantity span').textContent);
                let newQuantity = btn.classList.contains('increase') ? currentQuantity + 1 : currentQuantity - 1;

                const promise = (newQuantity < 1) 
                    ? updateCartItem('remove_item', productId, 0) 
                    : updateCartItem('update_quantity', productId, newQuantity);

                promise.then(data => {
                    if (data.success) renderCart();
                    else Swal.fire('Lỗi', data.message, 'error');
                });
            };
        });

        // Sự kiện Xóa các mục đã chọn (Sửa lỗi 400 bằng cách truyền đủ 3 tham số)
        // --- SỬA LẠI LOGIC TRONG HÀM attachCartEventListeners ---
if (removeSelected) {
    removeSelected.onclick = function () {
        const selected = [...document.querySelectorAll('.item-checkbox:checked')];
        if (selected.length === 0) {
Swal.fire('Chú ý', 'Bạn chưa chọn sản phẩm nào.', 'warning');
            return;
        }

        Swal.fire({
            title: 'Xác nhận xóa?',
            text: `Bạn muốn xóa ${selected.length} sản phẩm đã chọn?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Đồng ý xóa',
            cancelButtonText: 'Hủy'
        }).then((result) => {
            if (result.isConfirmed) {
                // Hiển thị loading để người dùng không thao tác lung tung
                Swal.fire({
                    title: 'Đang xử lý...',
                    allowOutsideClick: false,
                    didOpen: () => { Swal.showLoading(); }
                });

                const ids = selected.map(cb => cb.closest('.cart-item').dataset.productId);
                
                // Dùng async/await hoặc Promise.all để chờ Backend thực hiện xong hết
                Promise.all(ids.map(id => updateCartItem('remove_item', id, 0)))
                    .then(resultsArray => {
                        // Kiểm tra xem có bất kỳ yêu cầu nào thất bại không
                        const failures = resultsArray.filter(r => !r.success);
                        
                        if (failures.length === 0) {
                            // CHỈ KHI TẤT CẢ THÀNH CÔNG MỚI RENDER LẠI
                            renderCart(); 
                            Swal.fire('Thành công', 'Đã xóa các sản phẩm được chọn.', 'success');
                        } else {
                            // Nếu có lỗi từ server (ví dụ: lỗi SQL)
                            renderCart();
                            Swal.fire('Thông báo', 'Một số sản phẩm không thể xóa. Vui lòng thử lại.', 'error');
                        }
                    })
                    .catch(err => {
                        console.error("Lỗi xóa sản phẩm:", err);
                        Swal.fire('Lỗi kết nối', 'Không thể kết nối với máy chủ.', 'error');
                    });
            }
        });
    };
}

        // Sự kiện Select All
        if (selectAllCart) {
            selectAllCart.onclick = function() {
                const isChecked = this.checked;
                document.querySelectorAll('.item-checkbox').forEach(cb => {
                    cb.checked = isChecked;
                });
            };
        }

        // Sự kiện cho từng Checkbox lẻ
        document.querySelectorAll('.item-checkbox').forEach(cb => {
            cb.onchange = updateSelectAllState;
        });

        updateSelectAllState();
    }

    // --- 5. LOGIC THANH TOÁN & ĐIỀU HƯỚNG ---
    function redirectToOrderPage(totalAmount) {
        const tempOrderId = Date.now(); 
const redirectURL = `index.php?page=donhang&order_id=${tempOrderId}&total=${totalAmount}`; 

        Swal.fire({
            title: 'Đang chuẩn bị Đơn hàng...',
            text: 'Chuyển đến trang xác nhận địa chỉ và thanh toán.',
            icon: 'info',
            showConfirmButton: false,
            timer: 1500,
            timerProgressBar: true
        });
        
        setTimeout(() => { window.location.href = redirectURL; }, 1500);
    }

    checkoutBtn?.addEventListener('click', function() {
        if (currentTotalAmount <= 0) {
            Swal.fire('Giỏ hàng trống!', 'Vui lòng chọn món trước khi thanh toán.', 'warning');
            return;
        }
        redirectToOrderPage(currentTotalAmount);
    });

    paymentCompleteBtn?.addEventListener('click', function () {
        updateCartItem('checkout_complete', 0, 0).then(data => {
            if (data.success) {
                Swal.fire('Thành công!', 'Đơn hàng đã được xác nhận.', 'success');
                renderCart();
            } else {
                Swal.fire('Lỗi', data.message, 'error');
            }
            if (qrModal) qrModal.style.display = 'none';
        });
    });

    // --- KHỞI CHẠY ---
    renderCart();
    window.renderCart = renderCart;
});