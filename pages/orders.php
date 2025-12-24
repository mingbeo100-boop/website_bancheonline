
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

    </div> 
</div>

<script src="assets/js/order_list.js"></script>
<div id="orderDetailModal" class="fixed inset-0 bg-gray-900 bg-opacity-50 hidden items-center justify-center z-50 p-4">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-3xl overflow-hidden transform transition-all">
        <div class="bg-[#4db6ac] px-6 py-4 flex justify-between items-center">
            <h3 class="text-white text-xl font-bold" id="modalOrderTitle">Chi Tiết Đơn Hàng</h3>
            <button id="closeDetailModal" class="text-white hover:text-gray-200 text-2xl">&times;</button>
        </div>

        <div class="p-6 max-h-[80vh] overflow-y-auto">
            <div id="modalLoading" class="text-center py-10 hidden">
                <div class="inline-block animate-spin rounded-full h-8 w-8 border-4 border-teal-500 border-t-transparent"></div>
                <p class="mt-2 text-gray-500">Đang tải dữ liệu...</p>
            </div>

            <div id="modalContent">
                <div class="bg-orange-50 border border-orange-200 rounded-md p-4 mb-6">
                    <h5 class="text-[#cc8400] font-bold mb-2 uppercase text-sm tracking-wide">
                        <i class="bi bi-geo-alt-fill mr-1"></i> Địa Chỉ Nhận Hàng
                    </h5>
                    <div class="text-gray-700 space-y-1">
                        <p><span class="font-semibold">Người nhận:</span> <span id="detailName">...</span></p>
                        <p><span class="font-semibold">SĐT:</span> <span id="detailPhone">...</span></p>
                        <p><span class="font-semibold">Địa chỉ:</span> <span id="detailAddress">...</span></p>
                    </div>
                </div>

                <h5 class="font-bold text-gray-700 mb-3">Sản phẩm</h5>
                <div class="overflow-x-auto border rounded-lg">
                    <table class="w-full text-left border-collapse">
                        <thead class="bg-gray-100 text-gray-600 text-sm uppercase">
                            <tr>
                                <th class="p-3 border-b">Sản phẩm</th>
                                <th class="p-3 border-b text-center">SL</th>
                                <th class="p-3 border-b text-right">Giá</th>
                                <th class="p-3 border-b text-right">Thành tiền</th>
                            </tr>
                        </thead>
                        <tbody id="detailItemsList" class="text-sm">
                            </tbody>
                        <tfoot class="bg-gray-50">
                            <tr>
                                <td colspan="3" class="p-3 text-right font-bold text-gray-600">Tổng cộng:</td>
                                <td class="p-3 text-right font-bold text-red-600 text-lg" id="detailTotal">0 đ</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
        
        <div class="bg-gray-50 px-6 py-3 text-right border-t">
            <button id="closeDetailBtnBottom" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded shadow transition">Đóng</button>
        </div>
</div>
</div>

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
        color: #00897b; 
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
        background-color: white; 
    }

    .styled-table thead tr {
        background-color: #4db6ac; 
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
        background-color: #f7f7f7; 
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
        width: 95%; 
        margin: 0 auto; 
        text-align: center; 
        background-color: #90d8c2; 
        border: 1px solid #4db6ac; 
        color: #333 !important; 
        padding: 6px 10px; 
        border-radius: 4px;
        font-size: 0.9rem; 
        font-weight: 600;
        text-decoration: none;
        transition: all 0.2s;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    }
    .btn-view-detail:hover {
        background-color: #4db6ac; 
        color: white !important;
        text-decoration: none;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
    }
    
</style>