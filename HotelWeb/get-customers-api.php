<?php
include 'db.php';
header('Content-Type: application/json');

// [MỚI] Kiểm tra xem có đang ở chế độ xem lịch sử không
$historyMode = $_GET['history'] ?? ''; 

try {
    /* [CẬP NHẬT] Thêm b.Status vào SELECT */
    $sql = "SELECT 
                c.CCCD, c.FullName, r.RoomName,
                b.CheckInDate, b.CheckOutDate, b.BookingID, b.Status 
            FROM Customer c
            JOIN Booking b ON c.CustomerID = b.CustomerID
            JOIN Room r ON b.RoomID = r.RoomID";
    
    // [MỚI] Chỉ thêm WHERE nếu KHÔNG ở chế độ xem lịch sử
    if ($historyMode !== 'all') {
        $sql .= " WHERE b.Status = 'Đang ở'";
    }
    
    $sql .= " ORDER BY b.CheckInDate DESC"; // Sắp xếp theo ngày cho hợp lý
            
    $stmt = $pdo->query($sql);
    $customers = $stmt->fetchAll();
    
    echo json_encode(['success' => true, 'customers' => $customers]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Lỗi CSDL: ' . $e->getMessage()]);
}
?>