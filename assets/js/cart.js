// ====================== ADD TO CART FUNCTION (KÍCH THƯỚC LỚN) ======================
function addToCart(productId, name) { 
    const cartHandlerUrl = 'backend/cart_controller.php'; 

    fetch(cartHandlerUrl, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `action=add_to_cart&product_id=${productId}&quantity=1` 
    })
    .then(response => {
        // 🔑 1. KIỂM TRA LỖI 401 (CHƯA ĐĂNG NHẬP)
        if (response.status === 401) {
            // Chặn phản hồi JSON và hiển thị thông báo
            response.json().then(err => {
                Swal.fire({
                    title: 'Vui Lòng Đăng Nhập!',
                    text: 'Bạn cần đăng nhập để thêm sản phẩm vào giỏ hàng.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Đăng nhập ngay',
                    cancelButtonText: 'Hủy'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Chuyển hướng đến trang đăng nhập khi xác nhận
                        window.location.href = 'index.php?page=dangnhap';
                    }
                });
            });
            // Trả về đối tượng Promise không thành công để dừng luồng .then tiếp theo
            return Promise.reject('Unauthorized'); 
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            // 1. Hiển thị thông báo thành công (SweetAlert2 KÍCH THƯỚC LỚN)
            Swal.fire({
                title: 'Đã Thêm Vào Giỏ Hàng!',
                text: `Món "${name}" đã được thêm vào giỏ.`,
                icon: 'success',
                confirmButtonText: 'Tiếp tục mua hàng', // Tùy chọn 1
                showCancelButton: true,
                cancelButtonText: 'Xem Giỏ Hàng',       // Tùy chọn 2
                confirmButtonColor: '#28a745'
            }).then((result) => {
                if (result.dismiss === Swal.DismissReason.cancel) {
                    // Nếu người dùng chọn "Xem Giỏ Hàng"
                    window.location.href = 'index.php?page=giohang'; 
                }
            });
            
            // 2. Cập nhật giao diện giỏ hàng (Không tải lại trang)
            if (typeof renderCart === 'function') {
                renderCart(); 
            }
        } else {
            // Xử lý lỗi từ PHP
            Swal.fire('Lỗi', data.message || 'Có lỗi xảy ra khi thêm vào giỏ.', 'error');
        }
    })
    .catch(error => {
        console.error('Lỗi mạng hoặc server:', error);
        Swal.fire('Lỗi', 'Không thể kết nối đến server.', 'error');
    });
}


// ====================== DOM READY (ĐÃ SỬA ĐỔI) ======================
document.addEventListener('DOMContentLoaded', () => {
    const selectAll = document.getElementById('selectAll');
    const addSelectedBtn = document.getElementById('addSelectedToCart');
    const checkboxes = document.querySelectorAll('.product-checkbox');

    // ⚠️ Nếu không có phần tử phù hợp => dừng, tránh lỗi
    if (!selectAll && !addSelectedBtn && checkboxes.length === 0) {
        console.warn("⚠️ Không tìm thấy phần tử phù hợp, bỏ qua cart.js");
        return;
    }

    // ✅ Gọn hơn: toggle class khi checkbox thay đổi (GIỮ NGUYÊN)
    checkboxes.forEach(cb => {
        cb.addEventListener('change', () => {
            cb.closest('.product-card')?.classList.toggle('selected', cb.checked);
        });
    });

    // ✅ Chọn tất cả / Bỏ chọn tất cả (GIỮ NGUYÊN)
    selectAll?.addEventListener('change', () => {
        checkboxes.forEach(cb => {
            cb.checked = selectAll.checked;
            cb.closest('.product-card')?.classList.toggle('selected', selectAll.checked);
        });
    });

    // ✅ Thêm tất cả món đã chọn vào giỏ (PHẦN ĐÃ SỬA ĐỔI)
    addSelectedBtn?.addEventListener('click', () => {
        const selected = Array.from(checkboxes).filter(cb => cb.checked);

        if (selected.length === 0) {
            // ... (SweetAlert2 code: Lỗi chọn sản phẩm) ...
            Swal.fire({
                title: 'Lỗi',
                text: 'Vui lòng chọn ít nhất một sản phẩm!',
                icon: 'warning',
                confirmButtonText: 'Đã hiểu'
            });
            return;
        }

        selected.forEach(cb => {
            const card = cb.closest('.product-card');
            if (!card) return;

            // 🔑 LẤY product_id và TÊN sản phẩm
            const productId = card.dataset.productId; // Lấy ID từ data attribute
            const name = card.querySelector('strong')?.innerText || '';
            
            if (!productId) {
                 console.error("Thiếu data-product-id trên thẻ sản phẩm. Vui lòng kiểm tra HTML.");
                 return;
            }

            // GỌI HÀM ADD TO CART bằng ID
            addToCart(productId, name);
        });

        // ⚡ BỎ ALERT() DƯ THỪA VÀ CHỈ DÙNG SWEETALERT2
        Swal.fire({
            title: 'Hoàn Tất!',
            text: `Đã thêm ${selected.length} món đã chọn vào giỏ hàng!`,
            icon: 'success',
            confirmButtonText: 'Xem giỏ hàng'
        });
    });
});   