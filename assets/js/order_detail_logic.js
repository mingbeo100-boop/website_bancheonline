/**
 * Tệp JavaScript xử lý Checkout và Thanh toán
 * (Sử dụng ID tự tăng của SQL làm mã đơn hàng hiển thị)
 */

// Giả định hàm updateCartItem đã được định nghĩa và nhận (action, productId, quantity, method)

function generateQRCode(amount) {
    const qrCodeContainer = document.getElementById('qrcode');
    
    // Xóa mã QR cũ nếu có
    qrCodeContainer.innerHTML = '';

    // 🔥 THÔNG TIN CẦN THAY ĐỔI 🔥 (Giữ nguyên)
    const bankId = '970403'; 
    const accountNumber = '0796727753'; 
    const receiverName = 'TRAN NHAT LONG'; 
    const transferNote = `TTCHEAE${Math.floor(Math.random() * 1000)}`; 

    // Tạo chuỗi dữ liệu cho QR code
    const dataForQR = `Dich vu: Thanh toan Che; STK: ${accountNumber}; Tien: ${amount.toFixed(0)} VND; ND: ${transferNote}`;


    // Tạo mã QR bằng thư viện QRCode.js
    new QRCode(qrCodeContainer, {
        text: dataForQR, 
        width: 180,
        height: 180,
        colorDark : "#000000",
        colorLight : "#ffffff",
        correctLevel : QRCode.CorrectLevel.H
    });
}


document.addEventListener('DOMContentLoaded', function() {
    
    // 1. Lấy các phần tử cần thiết
    const deliveryBlock = document.getElementById('deliveryBlock');
    const paymentBlock = document.getElementById('paymentBlock');
    const confirmAddressBtn = document.getElementById('confirmAddressBtn');
    const paymentSelection = document.getElementById('payment-selection');
    const finalConfirmBtn = document.getElementById('finalConfirmBtn');
    
    // Lấy các phần tử Modal
    const qrModal = document.getElementById('qrModal'); 
    const modalTotalPriceContainer = document.getElementById('modalTotalPriceContainer'); 
    const paymentCompleteBtn = document.getElementById('paymentCompleteBtn'); 
    const closeBtn = document.getElementById('closeModalBtn'); 

    let selectedPaymentMethod = null;
    
    // Lấy ID và Tổng tiền từ URL
    const urlParams = new URLSearchParams(window.location.search);
    const orderId = urlParams.get('order_id');
    const totalAmount = parseInt(urlParams.get('total')); 
    
    
    // --- A. XỬ LÝ CHỌN PHƯƠNG THỨC THANH TOÁN (Giữ nguyên) ---
    paymentSelection.querySelectorAll('.payment-option').forEach(option => {
        option.addEventListener('click', function() {
            selectedPaymentMethod = this.dataset.method;
            if (selectedPaymentMethod === 'qr') {
                finalConfirmBtn.textContent = `Hoàn Tất Đơn Hàng (QR)`;
            } else {
                finalConfirmBtn.textContent = `Hoàn Tất Đơn Hàng (COD)`;
            }
        });
    });

    // --- B. BƯỚC 1: XÁC NHẬN ĐỊA CHỈ (Giữ nguyên) ---
    confirmAddressBtn.addEventListener('click', function(e) {
        
        // ... (Logic xác thực dữ liệu giữ nguyên) ...
        const name = document.getElementById('name').value.trim();
        const phone = document.getElementById('phone').value.trim();
        const address = document.getElementById('pastedAddress').value.trim();
        if (name === "" || phone === "" || address === "") {
             Swal.fire('Thiếu thông tin', 'Vui lòng nhập đầy đủ Tên, SĐT và Địa chỉ ghim.', 'error');
             return;
        }

        // 2. TẠO KHỐI TÓM TẮT ĐỊA CHỈ ĐÃ XÁC NHẬN
        const confirmedSummaryHTML = `
            <div id="addressSummaryBlock" class="address-confirmed-summary address-form-container">
                <h2>✅ Địa Chỉ Đã Xác Nhận</h2>
                <p><strong>Người nhận:</strong> ${name}</p>
                <p><strong>Điện thoại:</strong> ${phone}</p>
                <p><strong>Địa chỉ:</strong> ${address}</p>
                <hr>
                <button type="button" class="btn btn-sm btn-outline-secondary" id="editAddressBtn">Sửa Địa Chỉ</button>
            </div>
        `;
        
        // 3. Ẩn khối nhập liệu và Chèn khối tóm tắt
        deliveryBlock.style.display = 'none';
        deliveryBlock.insertAdjacentHTML('beforebegin', confirmedSummaryHTML); 
        
        // Gắn sự kiện cho nút Sửa Địa Chỉ
        document.getElementById('editAddressBtn').addEventListener('click', function() {
            document.getElementById('addressSummaryBlock').remove();
            deliveryBlock.style.display = 'block'; 
            paymentBlock.style.display = 'none'; 
            finalConfirmBtn.style.display = 'none'; 
        });

        // 4. HIỂN THỊ KHỐI THANH TOÁN (Cột 2)
        paymentBlock.style.display = 'block'; 
        finalConfirmBtn.style.display = 'block';
        
        Swal.fire('Thành Công!', 'Địa chỉ đã được ghi nhận. Hãy chọn phương thức thanh toán.', 'success');
    });

    // --- C. BƯỚC 2: HOÀN TẤT ĐƠN HÀNG (Final Submit) ---
    finalConfirmBtn.addEventListener('click', function(e) {
        
        if (!selectedPaymentMethod) {
            Swal.fire('Thiếu thông tin', 'Vui lòng chọn Phương thức Thanh toán.', 'warning');
            return;
        }

        if (selectedPaymentMethod === 'cod') {
            const method = 'cod';
        
            Swal.fire({ 
                title: 'Đang gửi Đơn hàng...', 
                text: 'Vui lòng chờ xác nhận từ hệ thống.',
                didOpen: () => { Swal.showLoading() }, 
                allowOutsideClick: false 
            });

            // 🔥 SỬA: Thêm tham số 'cod' vào updateCartItem
            updateCartItem('checkout_complete', 0, 0, method).then(data => {
                Swal.close(); 
                
                if (data.success) {
                    const finalOrderId = data.order_id || orderId; // Sử dụng ID từ Backend nếu có
                    
                    Swal.fire(
                        'Hoàn tất!', 
                        `Đơn hàng #${finalOrderId} đã được xác nhận. Vui lòng chuẩn bị tiền khi nhận hàng.`, 
                        'success'
                    ).then(() => {
                        // Chuyển hướng với ID nhận được từ Backend (hoặc ID tạm)
                        const redirectURL = `index.php?page=hoantat&order_id=${finalOrderId}&total=${totalAmount}&method=${method}`;
                        window.location.href = redirectURL;
                    });
                } else {
                    Swal.fire('Lỗi', data.message || 'Có lỗi xảy ra khi hoàn tất đơn hàng.', 'error');
                }
            }).catch(error => {
                Swal.close();
                Swal.fire('Lỗi', 'Không thể kết nối đến máy chủ. Vui lòng thử lại.', 'error');
            });

        } else if (selectedPaymentMethod === 'qr') {
            // Logic QR: Hiển thị Modal để thanh toán
            if (qrModal && modalTotalPriceContainer) {
                modalTotalPriceContainer.textContent = totalAmount.toLocaleString('vi-VN', { maximumFractionDigits: 0 }) + ' ₫';
                generateQRCode(totalAmount);
                qrModal.style.display = 'block';
            } else {
                Swal.fire('Lỗi', 'Không tìm thấy Modal QR. Vui lòng kiểm tra lại ID HTML.', 'error'); 
            }
        }
    });

    // --- D. XỬ LÝ SỰ KIỆN MODAL QR CODE ---
    
    // 2. Đóng Modal (Giữ nguyên)
    if (closeBtn) {
        closeBtn.addEventListener('click', function() { qrModal.style.display = 'none'; });
    }
    
    // Đóng Modal khi click ra ngoài (Giữ nguyên)
    window.addEventListener('click', function(event) {
        if (event.target === qrModal) { qrModal.style.display = 'none'; }
    });

    // 3. Hoàn tất Thanh toán trong Modal (Nút 'Đã Hoàn Thành Chuyển Khoản')
    if (paymentCompleteBtn) {
        const method = 'qr';
        
        paymentCompleteBtn.addEventListener('click', function () {
            
            Swal.fire({ 
                title: 'Đang hoàn tất Đơn hàng...', 
                text: 'Vui lòng chờ xác nhận từ hệ thống.',
                didOpen: () => { Swal.showLoading() }, 
                allowOutsideClick: false 
            });

            // 🔥 SỬA: Thêm tham số 'qr' vào updateCartItem
            updateCartItem('checkout_complete', 0, 0, method).then(data => {
                
                Swal.close(); 
                
                if (data.success) {
                    const finalOrderId = data.order_id || orderId; // Sử dụng ID từ Backend nếu có
                    
                    Swal.fire(
                        'Hoàn tất!', 
                        `Đơn hàng #${finalOrderId} đã được xác nhận. Cảm ơn bạn đã thanh toán!`, 
                        'success'
                    ).then(() => {
                        // Chuyển hướng với ID nhận được từ Backend (hoặc ID tạm)
                        const redirectURL = `index.php?page=hoantat&order_id=${finalOrderId}&total=${totalAmount}&method=${method}`;
                        window.location.href = redirectURL;
                    });
                } else {
                    Swal.fire('Lỗi', data.message || 'Có lỗi xảy ra khi hoàn tất đơn hàng.', 'error');
                    qrModal.style.display = 'none'; 
                }
            }).catch(error => {
                Swal.close();
                Swal.fire('Lỗi', 'Không thể kết nối đến máy chủ. Vui lòng thử lại.', 'error');
                qrModal.style.display = 'none';
            });
        });
    }

});