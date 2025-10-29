<?php
include 'db.php';
header('Content-Type: application/json');

$cccd = $_GET['cccd'] ?? '';
$historyMode = $_GET['history'] ?? ''; // [MỚI]

if (empty($cccd)) {
    echo json_encode(['success' => false, 'message' => 'Vui lòng nhập CCCD để tìm.']);
    exit;
}

try {
    /* [CẬP NHẬT] Thêm b.Status và logic WHERE động */
    $sql = "SELECT 
                c.CCCD, c.FullName, r.RoomName,
                b.CheckInDate, b.CheckOutDate, b.BookingID, b.Status
            FROM Customer c
            JOIN Booking b ON c.CustomerID = b.CustomerID
            JOIN Room r ON b.RoomID = r.RoomID
            WHERE c.CCCD LIKE ?";
            
    $params = ["%$cccd%"]; // Tham số cho CCCD

    // [MỚI] Thêm điều kiện Status nếu không phải xem lịch sử
    if ($historyMode !== 'all') {
        $sql .= " AND b.Status = ?";
        $params[] = 'Đang ở'; // Thêm 'Đang ở' vào tham số
    }
    
    $sql .= " ORDER BY b.CheckInDate DESC";
            
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params); // Gửi mảng tham số
    $customers = $stmt->fetchAll();
    
    echo json_encode(['success' => true, 'customers' => $customers]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Lỗi CSDL: ' . $e->getMessage()]);
}
?>