<?php
// Tên file: backend/get_order_details.php
session_start();
header('Content-Type: application/json');

require_once 'connect.php'; 
require_once 'utils.php'; // Cần respondWithError

// Đảm bảo không có lỗi kết nối CSDL
if ($conn->connect_error) {
    respondWithError(null, 'Lỗi kết nối database.', 500);
}

$order_id = $_GET['order_id'] ?? null;
$user_id = $_SESSION['user_id'] ?? null;

// Kiểm tra quyền Admin
$is_admin = isset($_SESSION['username']) && in_array(strtolower($_SESSION['username']), ['admin1', 'admin2']);

if (!$order_id) {
    respondWithError(null, 'Thiếu ID đơn hàng.', 400);
}

try {
    // 1. Lấy thông tin chung đơn hàng (bao gồm địa chỉ đã được lưu)
    $sql_info = "SELECT * FROM orders WHERE order_id = ?";
    if (!$is_admin) {
        // Khách hàng chỉ xem được đơn hàng của mình
        if (!$user_id) respondWithError(null, 'Vui lòng đăng nhập để xem đơn hàng.', 401);
        $sql_info .= " AND user_id = ?"; 
    }

    $stmt = $conn->prepare($sql_info);
    if ($stmt === false) throw new Exception("Lỗi chuẩn bị truy vấn thông tin đơn hàng.");
    
    if ($is_admin) {
        $stmt->bind_param("i", $order_id);
    } else {
        $stmt->bind_param("ii", $order_id, $user_id);
    }
    $stmt->execute();
    $order_info = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$order_info) {
        respondWithError(null, 'Không tìm thấy đơn hàng hoặc bạn không có quyền xem.', 404);
    }

    // 2. Lấy danh sách sản phẩm trong đơn
    $sql_items = "SELECT od.*, p.name 
                  FROM order_details od 
                  JOIN products p ON od.product_id = p.product_id 
                  WHERE od.order_id = ?";
    $stmt_items = $conn->prepare($sql_items);
    if ($stmt_items === false) throw new Exception("Lỗi chuẩn bị truy vấn chi tiết sản phẩm.");
    
    $stmt_items->bind_param("i", $order_id);
    $stmt_items->execute();
    $items = $stmt_items->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt_items->close();

    // 3. TRẢ VỀ KẾT QUẢ THÀNH CÔNG
    echo json_encode([
        'success' => true,
        'order' => $order_info,
        'items' => $items
    ]);
    exit; // 🔥 QUAN TRỌNG
    
} catch (Exception $e) {
    respondWithError($conn, 'Lỗi tải chi tiết đơn hàng: ' . $e->getMessage(), 500);
}
// KHÔNG CÓ THẺ ĐÓNG PHP.