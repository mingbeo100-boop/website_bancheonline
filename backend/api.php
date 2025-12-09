<?php
// Thiết lập header để trả về JSON và cho phép CORS
header('Content-Type: application/json');

// Biến chứa dữ liệu và lỗi
$response = ['error' => null, 'data' => []];

// Lấy tham số lọc từ request (GET)
$current_month = date('m');
$current_year = date('Y');
$selected_month = filter_input(INPUT_GET, 'month', FILTER_SANITIZE_STRING) ?: $current_month;
$selected_year = filter_input(INPUT_GET, 'year', FILTER_SANITIZE_STRING) ?: $current_year;

// --- THÔNG TIN KẾT NỐI CSDL (GIỮ NGUYÊN) ---
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'dacs2');

// --- HÀM KẾT NỐI CSDL (GIỮ NGUYÊN) ---
function connect_db(&$response) {
    $conn = @new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

    if ($conn->connect_error) {
        $response['error'] = "Lỗi kết nối CSDL: " . $conn->connect_error . " - Vui lòng kiểm tra thông tin DB.";
        return null;
    }
    $conn->set_charset("utf8");
    return $conn;
}

// --- KẾT NỐI CSDL ---
$conn = connect_db($response);

if ($conn) {
    // Khởi tạo các biến tổng
    $daily_sales_data = [];
    $customer_stats = []; 
    $total_revenue = 0;
    $total_orders = 0;
    $total_customers = 0;
    
    // ===================================================================
    // === 1. TRUY VẤN: DOANH THU VÀ SỐ LƯỢNG ĐƠN HÀNG THEO NGÀY (KHÔNG DÙNG CART_ID) ===
    // ===================================================================
    // Dùng order_id để đếm đơn hàng.
    $sql_daily_sales = "
        SELECT
            DAY(o.order_date) AS day,
            SUM(o.total_amount) AS revenue,
            COUNT(DISTINCT o.order_id) AS orders
        FROM
            orders o
        WHERE
            MONTH(o.order_date) = ?
            AND YEAR(o.order_date) = ?
        GROUP BY
            day
        ORDER BY
            day ASC
    ";
    
    if ($stmt_sales = $conn->prepare($sql_daily_sales)) {
        $stmt_sales->bind_param("ss", $selected_month, $selected_year);
        $stmt_sales->execute();
        $result_sales = $stmt_sales->get_result();

        while($row = $result_sales->fetch_assoc()) {
            $daily_sales_data[] = [
                'day' => (int)$row['day'], 
                'revenue' => (float)$row['revenue'], 
                'orders' => (int)$row['orders']
            ];
            $total_revenue += $row['revenue'];
            $total_orders += $row['orders'];
        }
        $stmt_sales->close();
    } else {
        $response['error'] = "Lỗi chuẩn bị truy vấn Doanh thu: " . $conn->error;
    }


    // ===================================================================
    // === 2. TRUY VẤN: THỐNG KÊ KHÁCH HÀNG (KHÔNG DÙNG CART_ID) ===
    // ===================================================================
    $sql_customer_stats = "
        SELECT 
            CASE 
                -- Lấy MIN(order_date) để xác định lần mua đầu tiên
                WHEN YEAR(min_order.min_order_date) = ? 
                    AND MONTH(min_order.min_order_date) = ?
                THEN 'Khách hàng mới'
                ELSE 'Khách hàng quay lại'
            END AS customer_type,
            COUNT(DISTINCT o.user_id) AS count
        FROM orders o
        JOIN ( 
            -- Subquery để tìm ngày đặt hàng sớm nhất cho mỗi người dùng 
            SELECT 
                user_id, 
                MIN(order_date) AS min_order_date
            FROM orders
            GROUP BY user_id
        ) AS min_order ON o.user_id = min_order.user_id
        WHERE 
            MONTH(o.order_date) = ? 
            AND YEAR(o.order_date) = ?
        GROUP BY customer_type
    ";
    
    $customer_data_map = ['Khách hàng mới' => 0, 'Khách hàng quay lại' => 0];

    if ($stmt_customers = $conn->prepare($sql_customer_stats)) {
        // Thứ tự bind: YEAR (1), MONTH (2), MONTH (3), YEAR (4)
        $stmt_customers->bind_param("ssss", 
            $selected_year,    
            $selected_month,   
            $selected_month,   
            $selected_year     
        ); 
        $stmt_customers->execute();
        $result_customers = $stmt_customers->get_result();

        while($row = $result_customers->fetch_assoc()) {
            $customer_data_map[$row['customer_type']] = (int)$row['count'];
        }
        $stmt_customers->close();
    } else {
        $response['error'] = "Lỗi chuẩn bị truy vấn Khách hàng: " . $conn->error;
    }

    $total_customers = array_sum($customer_data_map);
    
    $customer_stats = [
        ['type' => 'Khách hàng mới', 'count' => $customer_data_map['Khách hàng mới'], 'percent' => ($total_customers > 0) ? round(($customer_data_map['Khách hàng mới'] / $total_customers) * 100) . '%' : '0%'],
        ['type' => 'Khách hàng quay lại', 'count' => $customer_data_map['Khách hàng quay lại'], 'percent' => ($total_customers > 0) ? round(($customer_data_map['Khách hàng quay lại'] / $total_customers) * 100) . '%' : '0%'],
    ];

    $conn->close();
}

// Chuẩn bị response JSON (GIỮ NGUYÊN)
$response['data'] = [
    'daily_sales' => $daily_sales_data ?? [],
    'customer_stats' => $customer_stats ?? [],
    'totals' => [
        'revenue' => $total_revenue ?? 0,
        'orders' => $total_orders ?? 0,
        'customers' => $total_customers ?? 0
    ],
    'selected_month' => $selected_month,
    'selected_year' => $selected_year,
];

// Xuất kết quả
echo json_encode($response);
?>