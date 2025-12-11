<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lịch Sử Đơn Hàng Của Bạn</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Dancing+Script:wght@400..700&family=Quicksand:wght@300..700&display=swap" rel="stylesheet">
    
    
</head>
<body class="p-5 md:p-10">

<div class="container max-w-6xl mx-auto mt-5 mb-5">
    
    <div class="content-block">
        
        <div class="text-center">
            <h1 class="main-page-header">
                <i class="bi bi-cart-fill mr-2"></i> Lịch Sử Đơn Hàng Của Bạn
            </h1>
            <p class="page-description">Theo dõi và quản lý các đơn hàng đã đặt của bạn.</p>
        </div>
        
        <h4 class="fw-bold">
            <i class="bi bi-list-columns-reverse mr-2"></i> Danh Sách Chi Tiết
        </h4>
        
        <div class="overflow-x-auto">
            
            <table class="styled-table" id="ordersTable">
                
                <thead>
                    <tr>
                        <th>Mã Đơn hàng</th>
                        <th>Ngày Đặt</th>
                        <th>Tổng Tiền</th>
                        <th>Trạng Thái</th>
                        <th>Chi Tiết</th>
                    </tr>
                </thead>
                
                <tbody id="ordersListBody">
                    <tr>
                        <td class="font-semibold">30</td>
                        <td>2023-12-06</td>
                        <td class="text-red-600 font-semibold">30.000 VNĐ</td>
                        <td><span class="badge-pending"><i class="bi bi-hourglass-split mr-1"></i> Đang chờ</span></td>
                        <td><button class="text-blue-600 hover:underline">Xem</button></td>
                    </tr>
                    <tr>
                        <td class="font-semibold">29</td>
                        <td>2023-12-05</td>
                        <td class="text-green-600 font-semibold">67.000 VNĐ</td>
                        <td><span class="badge-delivered"><i class="bi bi-check-circle-fill mr-1"></i> Hoàn thành</span></td>
                        <td><button class="text-blue-600 hover:underline">Xem</button></td>
                    </tr>
                    <tr>
                        <td colspan="5" class="text-center text-gray-500 italic py-3">
                            <div class="inline-block animate-spin rounded-full h-4 w-4 border-2 border-b-transparent border-teal-500 mr-2" role="status"></div> Đang tải đơn hàng...
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        
        <div class="mt-6 text-right">
            <button class="btn-vibrant transition duration-300">
                
            </button>
        </div>

    </div> </div>


<div id="orderDetailModal" class="fixed inset-0 bg-gray-900 bg-opacity-75 hidden justify-center z-50 p-4 pt-16">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-3xl overflow-hidden transform transition-all duration-300 scale-100 opacity-100 flex flex-col max-h-[90vh]">
        
        <div class="bg-teal-700 px-6 py-4 flex justify-between items-center border-b border-teal-800 flex-shrink-0">
            <h3 class="text-white text-2xl font-extrabold" id="modalOrderTitle">Chi Tiết Đơn Hàng</h3>
            <button id="closeDetailModal" class="text-white hover:text-teal-200 text-3xl transition duration-150 leading-none">&times;</button>
        </div>

        <div class="p-6 overflow-y-auto flex-grow"> 
            
            <div id="modalLoading" class="text-center py-10 hidden">
                <div class="inline-block animate-spin rounded-full h-10 w-10 border-4 border-teal-500 border-t-transparent"></div>
                <p class="mt-3 text-lg text-gray-600">Đang tải dữ liệu...</p>
            </div>

            <div id="modalContent">
                
                <div class="bg-teal-50 border border-teal-200 rounded-lg p-5 mb-6 shadow-sm">
                    <h5 class="text-teal-800 font-bold mb-3 uppercase text-sm tracking-widest flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                        </svg>
                        Địa Chỉ Nhận Hàng
                    </h5>
                    <div class="text-gray-700 space-y-2 text-sm">
                        <p><span class="font-semibold text-gray-800">Người nhận:</span> <span id="detailName">...</span></p>
                        <p><span class="font-semibold text-gray-800">SĐT:</span> <span id="detailPhone">...</span></p>
                        <p><span class="font-semibold text-gray-800">Địa chỉ:</span> <span id="detailAddress">...</span></p>
                        <p><span class="font-semibold text-gray-800">Thanh toán:</span> <span id="detailPayment">...</span></p>
                    </div>
                </div>

                <h5 class="font-bold text-gray-700 mb-3 text-lg"><i class="bi bi-basket-fill mr-1"></i> Danh sách Sản phẩm</h5>
                
                <div id="orderItemsContainer" class="max-h-64 overflow-y-auto border border-gray-200 rounded-xl shadow-lg mb-4">
                    <table class="w-full text-left border-collapse">
                        <thead class="bg-gray-100 text-gray-600 text-sm uppercase tracking-wider sticky top-0 z-10 shadow-sm">
                            <tr>
                                <th class="p-4 border-b">Sản phẩm</th>
                                <th class="p-4 border-b text-center">SL</th>
                                <th class="p-4 border-b text-right">Giá</th>
                                <th class="p-4 border-b text-right">Thành tiền</th>
                            </tr>
                        </thead>
                        <tbody id="detailItemsList" class="text-sm divide-y divide-gray-100">
                            <tr class="hover:bg-gray-50"><td class="p-4">Chè Thái Caramel</td><td class="p-4 text-center">x1</td><td class="p-4 text-right">32.000 đ</td><td class="p-4 text-right">32.000 đ</td></tr>
                            <tr class="hover:bg-gray-50"><td class="p-4">Chè Thái Hoa Quả</td><td class="p-4 text-center">x1</td><td class="p-4 text-right">29.000 đ</td><td class="p-4 text-right">29.000 đ</td></tr>
                            <tr class="hover:bg-gray-50"><td class="p-4">Chè Thái Khoai Dẻo</td><td class="p-4 text-center">x1</td><td class="p-4 text-right">33.000 đ</td><td class="p-4 text-right">33.000 đ</td></tr>
                            <tr class="hover:bg-gray-50"><td class="p-4">Chè Thái Sầu Hoa Quả</td><td class="p-4 text-center">x1</td><td class="p-4 text-right">34.000 đ</td><td class="p-4 text-right">34.000 đ</td></tr>
                            <tr class="hover:bg-gray-50"><td class="p-4">Dừa Dầm Thái</td><td class="p-4 text-center">x1</td><td class="p-4 text-right">30.000 đ</td><td class="p-4 text-right">30.000 đ</td></tr>
                            <tr class="hover:bg-gray-50"><td class="p-4">Sữa Chua Hoa Quả</td><td class="p-4 text-center">x1</td><td class="p-4 text-right">27.000 đ</td><td class="p-4 text-right">27.000 đ</td></tr>
                            <tr class="hover:bg-gray-50"><td class="p-4">Dừa Dầm Thái Sầu Riêng</td><td class="p-4 text-center">x1</td><td class="p-4 text-right">35.000 đ</td><td class="p-4 text-right">35.000 đ</td></tr>
                            <tr class="hover:bg-gray-50"><td class="p-4">Chè Thái Caramel (Thêm)</td><td class="p-4 text-center">x1</td><td class="p-4 text-right">32.000 đ</td><td class="p-4 text-right">32.000 đ</td></tr>
                            <tr class="hover:bg-gray-50"><td class="p-4">Chè Thái Hoa Quả (Thêm)</td><td class="p-4 text-center">x1</td><td class="p-4 text-right">29.000 đ</td><td class="p-4 text-right">29.000 đ</td></tr>
                            <tr class="hover:bg-gray-50"><td class="p-4">Chè Thái Khoai Dẻo (Thêm)</td><td class="p-4 text-center">x1</td><td class="p-4 text-right">33.000 đ</td><td class="p-4 text-right">33.000 đ</td></tr>
                            <tr class="hover:bg-gray-50"><td class="p-4">Chè Thái Sầu Hoa Quả (Thêm)</td><td class="p-4 text-center">x1</td><td class="p-4 text-right">34.000 đ</td><td class="p-4 text-right">34.000 đ</td></tr>
                            </tbody>
                    </table>
                </div>
                
                <div class="bg-gray-50 border border-gray-200 rounded-xl shadow-lg p-4 mt-4 flex-shrink-0">
                    <div class="flex justify-between items-center">
                        <span class="font-extrabold text-gray-700 uppercase text-xl">Tổng cộng:</span>
                        <span class="font-extrabold text-red-600 text-2xl" id="detailTotal">... đ</span>
                    </div>
                </div>

            </div>
        </div>
        
        <div class="bg-gray-50 px-6 py-4 text-right border-t border-gray-200 flex-shrink-0">
            <button id="closeDetailBtnBottom" class="bg-teal-600 hover:bg-teal-700 text-white px-6 py-2 rounded-lg font-semibold shadow-md transition duration-150">Đóng</button>
        </div>
    </div>
</div>
<script src="assets/js/order_list.js"></script>
</body>
</html>

<style>
    /* FONT VÀ NỀN CHUNG */
    body {
        background-color: #f7f9fc;
        margin-top: 40px;
        font-family: 'Quicksand', sans-serif; 
    }

    /* 2. KHỐI BỌC TOÀN BỘ (Màu DA/KEM ẤM) */
    .content-block {
        background-color: #FFF8E1; 
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15); 
        border-radius: 12px; 
        padding: 30px; 
        margin-top: 20px; 
    }

    /* 🌟 TIÊU ĐỀ CHÍNH - FONT VIẾT TAY 🌟 */
    .main-page-header {
        color: #000000ff; 
        font-family: 'Dancing Script', cursive; 
        font-size: 3.5rem; 
        font-weight: 700;
        text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.1);
        margin-bottom: 5px; 
        padding-bottom: 15px;
        border-bottom: 1px solid #f0e6c7; 
    }
    
    /* Mô tả ngay dưới tiêu đề chính */
    .page-description {
        color: #4a5568; 
        margin-bottom: 25px; 
        font-size: 1.1rem;
    }

    /* TIÊU ĐỀ NỘI DUNG BÊN TRONG KHỐI */
    .content-block h4 {
        color: #70400cff; 
        font-family: 'Quicksand', sans-serif; 
        font-weight: 600;
        font-size: 20px;
        margin-bottom: 20px;
        margin-top: 20px;
    }

    /* KHỐI BẢNG (Nền Trắng) */
    .styled-table {
        border-collapse: collapse;
        width: 100%;
        margin-top: 15px;
        box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
        border-radius: 0.5rem; 
        overflow: hidden;
        background-color: #ebddcaff; 
    }

    .styled-table thead tr {
        background-color: #935427ff; 
        color: white;
        text-align: left;
        text-transform: uppercase;
    }

    .styled-table th,
    .styled-table td {
        padding: 15px;
        text-align: left;
        border: 1px solid #e0f2f1; 
    }

    .styled-table tbody tr:nth-child(even) {
        background-color: #f3ede5ff; 
    }

    /* --- CSS CHO TRẠNG THÁI VÀ NÚT XEM --- */
    
    /* Định nghĩa màu nền chính */
    .color-pending { background-color: #cc8400; color: white; border-color: #cc8400; }
    .color-processing { background-color: #007bff; color: white; border-color: #007bff; }
    .color-delivered { background-color: #1e7e34; color: white; border-color: #1e7e34; }
    .color-cancelled { background-color: #dc3545; color: white; border-color: #dc3545; }


    /* 1. CSS cho Ô TRẠNG THÁI (TD) - Sử dụng màu nền RẤT NHẠT hoặc TRẮNG */
    /* Loại bỏ màu nền nhạt để tránh xung đột màu quá nhiều */
    .status-cell.pending, 
    .status-cell.processing, 
    .status-cell.delivered, 
    .status-cell.cancelled { 
        background-color: transparent !important; /* Giữ nền trắng của bảng */
    }
    
    /* Badge (Dùng cho User thường) - Áp dụng màu nền ĐẬM và chữ TRẮNG */
    .badge-pending { 
        display: inline-block; padding: 4px 10px; border-radius: 9999px; font-weight: 600; font-size: 0.875rem; 
        background-color: #cc8400; /* Đậm */
        color: white; 
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.2);
    }
    .badge-delivered { 
        display: inline-block; padding: 4px 10px; border-radius: 9999px; font-weight: 600; font-size: 0.875rem;
        background-color: #1e7e34; /* Đậm */
        color: white; 
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.2);
    }
    /* Thêm style cho badge Processing và Cancelled (nếu JS tạo ra) */
    .badge-processing {
        display: inline-block; padding: 4px 10px; border-radius: 9999px; font-weight: 600; font-size: 0.875rem;
        background-color: #007bff; 
        color: white; 
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.2);
    }
    .badge-cancelled {
        display: inline-block; padding: 4px 10px; border-radius: 9999px; font-weight: 600; font-size: 0.875rem;
        background-color: #dc3545; 
        color: white; 
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.2);
    }

    /* 2. CSS CHO DROPDOWN TRẠNG THÁI (Dùng cho Admin) */
    .status-dropdown {
        padding: 6px 10px;
        border-radius: 4px; 
        border: 1px solid #cce0d8; 
        font-size: 0.9rem;
        cursor: pointer;
        transition: all 0.2s;
        min-width: 130px; 
        font-weight: 600;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        /* Mặc định màu trắng */
        background-color: white;
        color: #333;
    }
    .status-dropdown:hover {
        border-color: #4db6ac;
    }
    
    /* Quy tắc MỚI: Màu nền cho DROPDOWN khi nó có class trạng thái */
    .status-dropdown.pending { background-color: #fefcf3; color: #cc8400; border-color: #ffc107; }
    .status-dropdown.processing { background-color: #f0f7f3; color: #007bff; border-color: #007bff; }
    .status-dropdown.delivered { background-color: #f0fff0; color: #1e7e34; border-color: #28a745; }
    .status-dropdown.cancelled { background-color: #fff0f0; color: #dc3545; border-color: #dc3545; }


    /* 3. CSS CHO NÚT "XEM" (btn-view-detail) */
    
    
    .btn-view-detail {
        display: block; 
        width: 100%; 
        text-align: center; 
        background-color: #ad783bff; /* Teal nhạt */
        color: white !important; 
        padding: 8px 10px; 
        border-radius: 6px;
        font-size: 0.9rem; 
        font-weight: 600;
        text-decoration: none;
        transition: all 0.2s;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
    }
    .btn-view-detail:hover {
        background-color: #865612ff; /* Teal đậm hơn */
        color: white !important;
        text-decoration: none;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
        transform: translateY(-1px);
        
    }
/* Đảm bảo SweetAlert2 Container cuộn toàn bộ nếu cần */
/* --- BIẾN MÀU ĐỒNG BỘ --- */
:root {
    
    --main-bg-color: #FFF8E1; 
    /* Màu chủ đạo/chữ nhấn (Nâu đất/Sienna) */
    --primary-text-color: #a0522d; 
    /* Màu nền phụ/viền (Vàng đất nhạt) */
    --secondary-bg-color: #fcf7ed; 
    /* Màu đậm hơn cho các thành phần (Goldenrod đậm) */
    --accent-color: #b8860b;
}

.swal2-container {
    align-items: flex-start !important; 
    overflow-y: auto !important;
}

/* 🌟 POPUP CHÍNH (Nền Kem) 🌟 */
.swal2-popup {
    margin-top: 105px !important; 
    max-height: 100vh; 
    padding: 0 !important; 
    /* 🔥 Thay đổi: Sử dụng màu nền Kem ấm */
    background-color: var(--main-bg-color); 
    border: 1px solid #d1b88e; /* Thêm viền ấm */
}

/* TIÊU ĐỀ */
.swal2-title {
    padding: 0.6em 1.25em 0em  1.25em!important;
    margin: 0 !important;
    max-width: 100% !important;
     /* Màu tiêu đề Nâu đất */
    font-weight: 800;
    font-family: 'Dancing Script', cursive !important;
    
}

/* KHU VỰC NỘI DUNG HTML */
.swal2-html-container {
    padding: 1rem 1.25rem 0.5rem 1.25rem !important; 
    display: flex;
    flex-direction: column;
    max-height: calc(95vh - 100px); 
    overflow: hidden; 
}

.swal2-actions {
    margin-top: 0em !important; 
    justify-content: flex-end !important;
    padding: 0 1.25em 0.5em 1.25em !important;
}

.swal2-confirm {
    font-size: 1rem !important;
    padding: 0.3em 1.5em !important;
    font-weight: 600 !important;
    background-color: var(--primary-text-color) !important; 
}

.swal2-confirm:hover{
    transform: scale(1.1);
}
.scrollable-items-container {
    overflow-y: auto !important;
    flex-grow: 1; 
    max-height: 180px; 
    margin-bottom: 0.5rem;
    /* 🔥 Thay đổi: Màu nền phụ (Kem nhạt hơn) */
    background-color :  #fef1e1ff ; 
    /* 🔥 Thay đổi: Viền Nâu đất */
    border: 1px solid blanchedalmond; 
    border-radius: 0.5rem;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.2);
    font-weight: 400;
}
.scrollable-items-container thead {
    /* Sử dụng màu Teal của bảng chính */
    background-color: #b87b4fff !important; 
    color: white !important; 
    font-size: 0.9rem;
}
.scrollable-items-container table td {
    /* Thiết lập viền dưới đậm hơn */
    border-bottom: 2px solid #f9f7f7ff !important; /* Độ dày 2px, màu Vàng Đất Nhạt */
    padding: 10px 12px; /* Tăng padding nhẹ cho ô */
}
</style>