<?php
session_start(); 
session_unset(); // Xóa hết biến session
session_destroy(); // Hủy phiên làm việc

// Quay về trang chủ
header("Location: ../index.php");
exit;
?>