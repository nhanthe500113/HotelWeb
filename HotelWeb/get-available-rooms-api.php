<?php
include 'db.php';
header('Content-Type: application/json');

// Lấy loại phòng từ tham số (ví dụ: ?room_type=Đơn)
$roomType = $_GET['room_type'] ?? '';

if (empty($roomType)) {
    echo json_encode(['success' => false, 'message' => 'Vui lòng chọn loại phòng.']);
    exit;
}

try {
    // Truy vấn CSDL để tìm các phòng 'Trống' theo 'Loại Phòng'
    $sql = "SELECT RoomID, RoomName 
            FROM Room 
            WHERE RoomType = ? AND Status = 'Trống'
            ORDER BY RoomName";
            
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$roomType]);
    $rooms = $stmt->fetchAll();
    
    echo json_encode(['success' => true, 'rooms' => $rooms]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Lỗi CSDL: ' . $e->getMessage()]);
}
?>