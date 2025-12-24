// Tên file: assets/js/cart_api.js
// Mục đích: Giao tiếp với backend API để quản lý giỏ hàng và xử lý checkout

const cartHandlerUrl = 'backend/cart_controller.php'; 

/**
 * Gửi yêu cầu cập nhật giỏ hàng hoặc hoàn tất checkout đến API.
 * @param {string} action - Hành động ('get_cart', 'add_to_cart', 'remove_item', 'update_quantity', 'checkout_complete').
 * @param {number} productId - ID sản phẩm.
 * @param {number} quantity - Số lượng mới.
 * @param {string|null} method - Phương thức thanh toán ('cod' hoặc 'qr').
 * @param {object|null} customerInfo - Thông tin người nhận ({name, phone, address}).
 * @returns {Promise<object>} - Kết quả từ API.
 */
function updateCartItem(action, productId, quantity = 0, method = null, customerInfo = null) {
    
    // Khởi tạo params bằng URLSearchParams để tự động encode dữ liệu an toàn
    let params = new URLSearchParams();
    params.append('action', action);

    // --- 1. XỬ LÝ CÁC HÀNH ĐỘNG CƠ BẢN (Đồng bộ tên action với PHP) ---
    // Chuyển 'remove' thành 'remove_item' và 'add' thành 'add_to_cart' để khớp với cart_actions.php
    if (action === 'add_to_cart' || action === 'remove_item' || action === 'update_quantity') {
        params.append('product_id', productId);
        params.append('quantity', quantity); // Luôn gửi quantity (mặc định 0) để tránh lỗi thiếu tham số
    }
    
    // --- 2. XỬ LÝ HOÀN TẤT ĐƠN HÀNG (CHECKOUT) ---
    if (action === 'checkout_complete') {
        // Kiểm tra phương thức thanh toán
        if (!method) {
            return Promise.resolve({ success: false, message: 'Vui lòng chọn phương thức thanh toán.' });
        }
        
        // Kiểm tra thông tin khách hàng
        if (!customerInfo || !customerInfo.name || !customerInfo.phone || !customerInfo.address) {
             return Promise.resolve({ success: false, message: 'Vui lòng nhập đầy đủ thông tin giao hàng.' });
        }
        
        params.append('method', method);
        params.append('name', customerInfo.name);
        params.append('phone', customerInfo.phone);
        params.append('address', customerInfo.address);
    }
    
    // --- 3. GỌI FETCH API ---
    return fetch(cartHandlerUrl, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: params.toString() // Chuyển đổi object params sang chuỗi query string
    })
    .then(res => {
        // Kiểm tra nếu phản hồi không phải JSON hoặc có lỗi HTTP
        if (!res.ok) { 
             return res.json().catch(() => {
                 return { success: false, message: `Lỗi hệ thống (${res.status}).` };
             }).then(errData => {
                 return { success: false, message: errData.message || `Lỗi Server (${res.status})` };
             });
        }
return res.json();
    })
    .catch(error => {
        console.error('Lỗi kết nối API:', error);
        return { success: false, message: 'Không thể kết nối đến máy chủ.' };
    });
}