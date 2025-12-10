// Tên file: assets/js/cart_api.js
// Mục đích: Giao tiếp với backend API để quản lý giỏ hàng và xử lý checkout
// Sử dụng controller mới (cart_controller.php)
const cartHandlerUrl = 'backend/cart_controller.php'; 
/**
 * Gửi yêu cầu cập nhật giỏ hàng hoặc hoàn tất checkout đến API.
 * @param {string} action - Hành động cần thực hiện.
 * @param {number} productId - ID sản phẩm.
 * @param {number} quantity - Số lượng mới.
 * @param {string|null} method - Phương thức thanh toán ('cod' hoặc 'qr').
 * @param {object|null} customerInfo - Thông tin người nhận ({name, phone, address}) (chỉ cần cho 'checkout_complete').
 * @returns {Promise<object>} - Kết quả từ API.
 */
function updateCartItem(action, productId, quantity = 0, method = null, customerInfo = null) {
    
    let body = `action=${action}`; // Bắt đầu body với action

    // --- 1. XỬ LÝ CÁC HÀNH ĐỘNG CƠ BẢN TRONG GIỎ HÀNG ---
    if (action === 'add' || action === 'remove' || action === 'update_quantity') {
        body += `&product_id=${productId}`;

        if (action === 'update_quantity') {
            body += `&quantity=${quantity}`;
        }
    }
    
    // --- 2. XỬ LÝ HOÀN TẤT ĐƠN HÀNG (CHECKOUT) ---
    if (action === 'checkout_complete') {
        
        // Kiểm tra phương thức thanh toán
        if (!method) {
            console.error("Lỗi: Phương thức thanh toán (method) bị thiếu trong quá trình checkout.");
            return Promise.resolve({ success: false, message: 'Thiếu phương thức thanh toán.' });
        }
        
        // Kiểm tra thông tin khách hàng
        if (!customerInfo || !customerInfo.name || !customerInfo.phone || !customerInfo.address) {
             console.error("Lỗi: Thiếu thông tin người nhận khi checkout.");
             return Promise.resolve({ success: false, message: 'Thiếu thông tin giao hàng (Tên, SĐT, Địa chỉ).' });
        }
        
        // Bắt đầu lại body để chỉ chứa thông tin checkout
        body = `action=${action}&method=${method}`; 
        
        // 🔥 Gắn thông tin người nhận vào body (sử dụng encodeURIComponent)
        body += `&name=${encodeURIComponent(customerInfo.name)}`;
        body += `&phone=${encodeURIComponent(customerInfo.phone)}`;
        body += `&address=${encodeURIComponent(customerInfo.address)}`;
    }
    
    // --- 3. GỌI FETCH API ---
    return fetch(cartHandlerUrl, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: body 
    })
    .then(res => {
        // Xử lý các status code lỗi (ví dụ 400, 401)
        if (!res.ok) { 
             return res.json().catch(() => {
                 return { success: false, message: `Lỗi Server (${res.status}): Không thể đọc phản hồi.` };
             }).then(errData => {
                 return { success: false, message: errData.message || `Lỗi Server (${res.status})` };
             });
        }
        return res.json();
    })
    .catch(error => {
        console.error('Lỗi kết nối API:', error);
        return { success: false, message: 'Lỗi kết nối mạng hoặc server không phản hồi.' };
    });
}