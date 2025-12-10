// Đặt trong assets/js/order_list.js
document.addEventListener('DOMContentLoaded', function() {
    const ordersListBody = document.getElementById('ordersListBody');
    const apiUrl = '../backend/fetch_user_orders.php'; 
    const updateStatusUrl = '../backend/update_order_status.php'; // URL mới
    
    // Gán ID để test (Giữ nguyên, nhưng sẽ bị ghi đè nếu admin đăng nhập)
    const CURRENT_USER_ID = 27; 
    
    // --- 1. HÀM ĐỊNH DẠNG TIỀN TỆ ---
    function formatCurrency(amount) {
        const numberAmount = parseFloat(amount);
        const totalInDong = numberAmount * 1000; 
        
        if (isNaN(totalInDong)) {
            return amount; 
        }

        const formattedNumber = new Intl.NumberFormat('vi-VN', {
            minimumFractionDigits: 0,
            maximumFractionDigits: 0
        }).format(totalInDong);
        
        return formattedNumber + ' đ';
    }
    
    // --- 2. HÀM DỮ LIỆU TRẠNG THÁI ---
    function getStatusData(status) {
        const lowerStatus = status ? status.toLowerCase() : '';
        const dataMap = {
            'pending': { class: 'badge-pending', text: 'Đang chờ', icon: 'bi-hourglass-split' },
            'processing': { class: 'badge-processing', text: 'Đang xử lý', icon: 'bi-gear-fill' },
            'delivered': { class: 'badge-delivered', text: 'Hoàn thành', icon: 'bi-check-circle-fill' },
            'cancelled': { class: 'badge-cancelled', text: 'Đã hủy', icon: 'bi-x-octagon-fill' }
        };
        return dataMap[lowerStatus] || { class: 'bg-secondary', text: 'Không rõ', icon: 'bi-question-circle' };
    }
    
    // Các trạng thái hợp lệ để tạo dropdown
    const ALLOWED_STATUSES = ['pending', 'processing', 'delivered', 'cancelled'];

    // --- 3. HÀM TẠO HTML CHO DROPDOWN ---
    function getStatusDropdownHTML(orderId, currentStatus, isAdmin) {
        const statusClass = currentStatus.toLowerCase(); 
        
        if (!isAdmin) {
            const statusData = getStatusData(currentStatus);
            return `<span class="${statusData.class.replace('bg-', 'badge-')}">${statusData.text}</span>`;
        }
        
        let optionsHTML = '';
        ALLOWED_STATUSES.forEach(status => {
            const statusText = getStatusData(status).text;
            const selected = status === statusClass ? 'selected' : '';
            optionsHTML += `<option value="${status}" ${selected}>${statusText}</option>`;
        });

        return `
            <select class="status-dropdown ${statusClass}" data-order-id="${orderId}">
                ${optionsHTML}
            </select>
        `;
    }

    // --- 4. HÀM GỬI CẬP NHẬT TRẠNG THÁI (Dùng Swal.fire) ---
    function updateOrderStatus(orderId, newStatus, element) {
        const originalStatus = element.dataset.originalStatus;
        const newStatusText = getStatusData(newStatus).text; 
        
        element.disabled = true;
        element.classList.add('opacity-50'); 

        const formData = new URLSearchParams();
        formData.append('order_id', orderId); 
        formData.append('new_status', newStatus);

        fetch(updateStatusUrl, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                
                // Lấy mã hiển thị (ID tự tăng hoặc order_code)
                // Giả định Backend trả về order_code nếu có
                const displayCode = data.order_code || orderId; 
                
                // 🔥 SỬ DỤNG SWEETALERT2 VÀ displayCode
                Swal.fire({
                    icon: 'success', 
                    title: 'Cập nhật thành công!',
                    text: `Đơn hàng #${displayCode} đã được chuyển sang trạng thái "${newStatusText}".`,
                    confirmButtonText: 'OK' 
                });
                
                // Cập nhật trạng thái gốc và loại bỏ hiệu ứng tải
                element.dataset.originalStatus = newStatus; 
                element.classList.remove('opacity-50');
                
                // =======================================================
                // LOGIC CẬP NHẬT GIAO DIỆN TỨC THÌ
                // =======================================================
                
                const statusCell = element.closest('.status-cell'); 
                const row = element.closest('tr'); 
                const newStatusLower = newStatus.toLowerCase();
                
                // 1. CẬP NHẬT CỘT TRẠNG THÁI (Màu nền ô và dropdown)
                ALLOWED_STATUSES.forEach(s => {
                    element.classList.remove(s); 
                    if (statusCell) {
                        statusCell.classList.remove(s); 
                    }
                });
                element.classList.add(newStatusLower);
                if (statusCell) {
                    statusCell.classList.add(newStatusLower);
                }
                
                // 2. CẬP NHẬT CỘT TỔNG TIỀN (Màu chữ)
                const totalAmountCell = row.children[2]; 
                
                if (totalAmountCell) {
                    totalAmountCell.classList.remove('text-green-600', 'text-red-600');
                    
                    if (newStatusLower === 'delivered' || newStatusLower === 'completed') {
                        totalAmountCell.classList.add('text-green-600'); 
                    } else {
                        totalAmountCell.classList.add('text-red-600'); 
                    }
                }
                // =======================================================

            } else {
                Swal.fire('Lỗi cập nhật', data.error || `Lỗi không xác định khi cập nhật đơn hàng #${orderId}.`, 'error');
                element.value = originalStatus; 
                element.classList.remove('opacity-50');
            }
        })
        .catch(error => {
            Swal.fire('Lỗi kết nối', `Không thể kết nối đến máy chủ để cập nhật trạng thái: ${error.message}`, 'error');
            element.value = originalStatus; 
            element.classList.remove('opacity-50');
        })
        .finally(() => {
            element.disabled = false;
        });
    }

    // --- 5. HÀM TẢI ĐƠN HÀNG (Sử dụng order_code cho hiển thị) ---
    function showOrderDetails(orderId) {
    Swal.fire({
        title: 'Đang tải chi tiết...',
        didOpen: () => Swal.showLoading()
    });

    fetch(`../backend/get_order_details.php?order_id=${orderId}`)
        .then(res => res.json())
        .then(data => {
            Swal.close();
            if (!data.success) {
                Swal.fire('Lỗi', data.error, 'error');
                return;
            }

            const o = data.order;
            const items = data.items;

            // Xây dựng HTML danh sách sản phẩm
            let itemsHtml = items.map(item => `
                <tr class="border-b">
                    <td class="text-left py-2">${item.name}</td>
                    <td class="text-center">x${item.quantity}</td>
                    <td class="text-right">${formatCurrency(item.price_at_purchase)}</td>
                </tr>
            `).join('');

            const htmlContent = `
                <div class="text-left text-sm" >
                    <div class="mb-4 bg-gray-50 p-3 rounded">
                        <h3 class="font-bold text-teal-700 mb-2 border-b pb-1">📍 Thông Tin Giao Hàng</h3>
                        <p><strong>Người nhận:</strong> ${o.recipient_name || 'Không có tên'}</p>
                        <p><strong>SĐT:</strong> ${o.recipient_phone || '---'}</p>
                        <p><strong>Địa chỉ:</strong> ${o.shipping_address || 'Tại cửa hàng'}</p>
                        <p class="mt-2"><strong>Thanh toán:</strong> ${o.payment_method}</p>
                    </div>

                    <h3 class="font-bold text-teal-700 mb-2">🛒 Danh Sách Sản Phẩm</h3>
                    <table class="w-full mb-3">
                        <thead class="bg-gray-100 font-bold">
                            <tr>
                                <th class="text-left p-2">Sản phẩm</th>
                                <th class="text-center p-2">SL</th>
                                <th class="text-right p-2">Giá</th>
                            </tr>
                        </thead>
                        <tbody>${itemsHtml}</tbody>
                    </table>
                    
                    <div class="text-right font-bold text-lg text-red-600 border-t pt-2">
                        Tổng tiền: ${formatCurrency(o.total_amount)}
                    </div>
                </div>
            `;

            Swal.fire({
                title: `Chi tiết đơn #${o.order_code || o.order_id}`,
                html: htmlContent,
                width: '600px',
                confirmButtonText: 'Đóng'
            });
        })
        .catch(err => Swal.fire('Lỗi', 'Không thể tải chi tiết.', 'error'));
}
    function loadOrders(userId) {
        ordersListBody.innerHTML = '<tr><td colspan="5" class="text-center py-5"><div class="inline-block animate-spin rounded-full h-4 w-4 border-2 border-b-transparent border-teal-500 mr-2" role="status"></div> Đang tải đơn hàng...</td></tr>';

        const formData = new URLSearchParams();
        formData.append('user_id', userId);

        fetch(apiUrl, {
            method: 'POST', 
            body: formData  
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP Error! Status: ${response.status}`);
            }
            return response.json(); 
        })
        .then(data => {
            const queriedId = data.debug_id || 'N/A'; 
            const isAdmin = data.is_admin || false;
            
            if (!data.success) {
                ordersListBody.innerHTML = `<tr><td colspan="5" class="text-center text-red-600 py-5">⚠️ Lỗi Server. Đang truy vấn ID: ${queriedId}. Chi tiết: ${data.error}</td></tr>`;
                console.error("Server Error:", data.error);
                return;
            }

            if (data.orders && data.orders.length > 0) {
                ordersListBody.innerHTML = ''; 
                
                data.orders.forEach(order => {
                    const row = document.createElement('tr');
                    const statusClass = order.order_status.toLowerCase(); 
                    
                    const totalClass = (statusClass === 'delivered' || statusClass === 'completed') 
                                     ? 'text-green-600'  
                                     : 'text-red-600';  
                    
               
                    const rawCode = order.order_code || order.order_id;
                    const displayCode = `#${rawCode}`;
                    
                    row.innerHTML = `
                        <td class="align-middle font-semibold">${displayCode}</td>
                        <td class="align-middle">${order.order_date.substring(0, 10)}</td>
                        
                        <td class="align-middle font-semibold ${totalClass}">${formatCurrency(order.total_amount)}</td>
                        
                        <td class="align-middle status-cell ${statusClass}">
                            ${getStatusDropdownHTML(order.order_id, order.order_status, isAdmin)}
                        </td>
                        
                        <td class="align-middle">
                            <button class="btn-view-detail" data-order-id="${order.order_id}">
                                Xem
                            </button>
                        </td>
                    `;
                    ordersListBody.appendChild(row);
                    
                    // Gán sự kiện cho dropdown (Chỉ Admin)
                    if (isAdmin) {
                        const statusDropdown = row.querySelector('.status-dropdown');
                        if (statusDropdown) {
                            statusDropdown.dataset.originalStatus = statusDropdown.value; 

                            statusDropdown.addEventListener('change', function() {
                                const newStatus = this.value;
                                const orderId = this.dataset.orderId; // Vẫn dùng ID tự tăng (PK)
                                updateOrderStatus(orderId, newStatus, this);
                            });
                        }
                    }

                    // Gán sự kiện cho nút "Xem" (Dùng mã tùy chỉnh để hiển thị)
                    row.querySelector('.btn-view-detail').addEventListener('click', function() {
    const id = this.dataset.orderId;
    showOrderDetails(id);
                    });
                });
            } else {
                ordersListBody.innerHTML = `<tr><td colspan="5" class="text-center py-5">🛒 Không tìm thấy đơn hàng nào trong giỏ hàng của bạn`;
            }
        })
        .catch(error => {
            ordersListBody.innerHTML = `<tr><td colspan="5" class="text-center text-red-600 py-5">❌ Lỗi tải dữ liệu. Chi tiết: ${error.message}</td></tr>`;
            console.error("Fetch Error:", error);
        });
    }

    // Bắt đầu tải dữ liệu
    loadOrders(CURRENT_USER_ID);
});

