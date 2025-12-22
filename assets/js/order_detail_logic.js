


// --- 1. HÀM TẠO QR CODE ---
function generateQRCode(amount, currentOrderId) { 
    const qrCodeContainer = document.getElementById('qrcode');
    
    // Xóa mã QR cũ nếu có
    if (qrCodeContainer) {
        qrCodeContainer.innerHTML = '';
        
        // Thông tin ngân hàng cố định
        const bankId = '970403'; 
        const accountNumber = '0796727753'; 
        const transferNote = `TTCHEAE${currentOrderId || Math.floor(Math.random() * 10000)}`;
    
        // Định dạng dữ liệu thô
        const rawData = `STK:${accountNumber};Tien:${amount};ND:${transferNote}`;

        // Khởi tạo QRCode
        new QRCode(qrCodeContainer, {
            text: rawData, 
            width: 180,
            height: 180,
            colorDark : "#000000",
            colorLight : "#ffffff",
            correctLevel : QRCode.CorrectLevel.H
        });
    }
}

document.addEventListener('DOMContentLoaded', function() {
    
    // --- 2. KHỞI TẠO CÁC ELEMENT VÀ BIẾN ---
    const deliveryBlock = document.getElementById('deliveryBlock');
    const paymentBlock = document.getElementById('paymentBlock');
    const confirmAddressBtn = document.getElementById('confirmAddressBtn');
    const paymentSelection = document.getElementById('payment-selection');
    const finalConfirmBtn = document.getElementById('finalConfirmBtn');
    
    const nameInput = document.getElementById('name');
    const phoneInput = document.getElementById('phone');
    const addressInput = document.getElementById('pastedAddress'); 
    
    const qrModal = document.getElementById('qrModal'); 
    const modalTotalPriceContainer = document.getElementById('modalTotalPriceContainer'); 
    const paymentCompleteBtn = document.getElementById('paymentCompleteBtn'); 
    const closeBtn = document.getElementById('closeModalBtn'); 

    let selectedPaymentMethod = null;
    
    // 🔥 BIẾN LƯU TRỮ THÔNG TIN ĐỊA CHỈ ĐÃ XÁC NHẬN
    let confirmedAddressData = null; 

    const urlParams = new URLSearchParams(window.location.search);
    const totalAmount = parseInt(urlParams.get('total')); 

    // Kiểm tra tổng tiền hợp lệ
    if (isNaN(totalAmount) || totalAmount <= 0) {
        Swal.fire({ icon: 'error', title: 'Lỗi Dữ Liệu', text: 'Tổng tiền không hợp lệ. Vui lòng quay lại giỏ hàng.' });
        return; 
    }
    
    // --- 3. HÀM XỬ LÝ THANH TOÁN CHUNG (CORE LOGIC) ---
    function processCheckout(method) {
        
        // KIỂM TRA MẠNH MẼ VÀ DỪNG NGAY nếu dữ liệu confirmedAddressData bị null/thiếu
        if (!confirmedAddressData || !confirmedAddressData.name || !confirmedAddressData.phone || !confirmedAddressData.address) {
            Swal.fire('Lỗi Dữ liệu!', 'Thông tin giao hàng không đầy đủ. Vui lòng nhấn "Sửa thông tin" và xác nhận lại.', 'error');
            return;
        }
        
        const { name, phone, address } = confirmedAddressData;
        const info = { name, phone, address }; // Dữ liệu khách hàng đã được xác nhận

        // Hiển thị loading
        Swal.fire({ 
            title: 'Đang xử lý đơn hàng...', 
            didOpen: () => Swal.showLoading(),
            allowOutsideClick: false
        });

        // 🔥 GỌI API VỚI THÔNG TIN KHÁCH HÀNG ĐÃ ĐƯỢC LƯU
        // (updateCartItem được định nghĩa trong cart_api.js)
        updateCartItem('checkout_complete', 0, 0, method, info) 
        .then(data => {
            Swal.close(); 
            
            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Đặt hàng thành công!', 
                    text: `Mã đơn: #${data.order_code}. Cảm ơn bạn đã ủng hộ!`,
                    confirmButtonText: 'Xem chi tiết'
                }).then(() => {
                    // Chuyển hướng đến trang hoàn tất
                    window.location.href = `index.php?page=hoantat&order_id=${data.order_id}&code=${data.order_code}&total=${totalAmount}&method=${method}`;
                });
            } else {
                // Hiển thị lỗi từ Backend 
                Swal.fire('Lỗi', data.message || 'Có lỗi xảy ra khi tạo đơn hàng.', 'error');
                if (qrModal) qrModal.style.display = 'none';
            }
        })
        .catch(error => {
            Swal.close();
            Swal.fire('Lỗi kết nối', 'Không thể kết nối đến máy chủ.', 'error');
        });
    }

    // --- 4. XỬ LÝ CHỌN PHƯƠNG THỨC THANH TOÁN (Giữ nguyên) ---
    if (paymentSelection) {
        paymentSelection.querySelectorAll('.payment-option').forEach(option => {
            option.addEventListener('click', function() {
                paymentSelection.querySelectorAll('.payment-option').forEach(el => el.classList.remove('active', 'border-teal-500', 'bg-teal-50'));
                this.classList.add('active', 'border-teal-500', 'bg-teal-50');
                selectedPaymentMethod = this.dataset.method;
                
                if (selectedPaymentMethod === 'qr') {
                    finalConfirmBtn.textContent = `Hoàn Tất Đơn Hàng (QR)`;
                } else {
                    finalConfirmBtn.textContent = `Hoàn Tất Đơn Hàng (COD)`;
                }
            });
        });
    }

    // --- 5. BƯỚC 1: NÚT XÁC NHẬN ĐỊA CHỈ ---
    if (confirmAddressBtn) {
        confirmAddressBtn.addEventListener('click', function(e) {
            const name = nameInput ? nameInput.value.trim() : '';
            const phone = phoneInput ? phoneInput.value.trim() : '';
            const address = addressInput ? addressInput.value.trim() : '';

            if (name === "" || phone === "" || address === "") {
                Swal.fire('Thiếu thông tin', 'Vui lòng nhập đầy đủ Tên, SĐT và Địa chỉ.', 'error');
                return;
            }
            
            // 🔥 LƯU DỮ LIỆU ĐỊA CHỈ ĐÃ XÁC NHẬN
            confirmedAddressData = { name, phone, address };
            
            // TẠO KHỐI TÓM TẮT ĐỊA CHỈ
            const confirmedSummaryHTML = `
                <div id="addressSummaryBlock" class="p-4 border rounded-lg bg-green-50 border-green-200 mb-4">
                    <h3 class="text-green-700 font-bold mb-2">✅ Địa Chỉ Đã Xác Nhận</h3>
                    <p><strong>Người nhận:</strong> ${name}</p>
                    <p><strong>SĐT:</strong> ${phone}</p>
                    <p><strong>Địa chỉ:</strong> ${address}</p>
                    <button type="button" class="mt-3 text-sm text-blue-600 underline hover:text-blue-800" id="editAddressBtn">Sửa thông tin</button>
                </div>
            `;
            
            deliveryBlock.style.display = 'none';
            deliveryBlock.insertAdjacentHTML('beforebegin', confirmedSummaryHTML); 
            
            if (paymentBlock) paymentBlock.style.display = 'block'; 
            if (finalConfirmBtn) finalConfirmBtn.style.display = 'block';
            
            Swal.fire({ icon: 'success', title: 'Đã lưu địa chỉ', text: 'Vui lòng chọn phương thức thanh toán.', timer: 1500, showConfirmButton: false });
            
            // Sự kiện nút "Sửa địa chỉ"
            document.getElementById('editAddressBtn').addEventListener('click', function() {
                document.getElementById('addressSummaryBlock').remove();
                deliveryBlock.style.display = 'block'; // Hiện lại form nhập liệu
                paymentBlock.style.display = 'none'; 
                finalConfirmBtn.style.display = 'none'; 
                // 🔥 XÓA DỮ LIỆU ĐÃ LƯU KHI NGƯỜI DÙNG QUAY LẠI SỬA
                confirmedAddressData = null; 
            });
        });
    }

    // --- 6. BƯỚC 2: NÚT HOÀN TẤT (FINAL CONFIRM) ---
    if (finalConfirmBtn) {
        finalConfirmBtn.addEventListener('click', function(e) {
            if (!selectedPaymentMethod) {
                Swal.fire('Chưa chọn thanh toán', 'Vui lòng chọn Phương thức Thanh toán (COD hoặc QR).', 'warning');
                return;
            }
            // Kiểm tra: Phải có thông tin địa chỉ đã xác nhận
            if (!confirmedAddressData) {
                 Swal.fire('Thiếu thông tin', 'Vui lòng xác nhận địa chỉ giao hàng trước.', 'warning');
                 return;
            }

            if (selectedPaymentMethod === 'cod') {
                processCheckout('cod'); // Thanh toán COD, gọi API ngay
            } else if (selectedPaymentMethod === 'qr') {
                // Hiển thị Modal QR
                if (qrModal && modalTotalPriceContainer) {
                    modalTotalPriceContainer.textContent = totalAmount.toLocaleString('vi-VN') + ' đ';
                    generateQRCode(totalAmount, 'TEMP' + Math.floor(Math.random() * 1000)); 
                    qrModal.style.display = 'block';
                } else {
                    Swal.fire('Lỗi giao diện', 'Không tìm thấy Modal QR.', 'error'); 
                }
            }
        });
    }

    // --- 7. XỬ LÝ SỰ KIỆN TRONG MODAL QR ---
    if (closeBtn) {
        closeBtn.addEventListener('click', function() { qrModal.style.display = 'none'; });
    }
    // Đóng modal khi click ra ngoài
    window.addEventListener('click', function(event) {
        if (event.target === qrModal) { qrModal.style.display = 'none'; }
    });

    if (paymentCompleteBtn) {
        // Nút người dùng nhấn xác nhận đã chuyển khoản
        paymentCompleteBtn.addEventListener('click', function () {
            processCheckout('qr'); 
        });
    }
});