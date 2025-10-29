<?php
// 1. Kết nối CSDL
include 'db.php';

// 2. Báo cho trình duyệt biết đây là file JSON
header('Content-Type: application/json');

try {
    // 3. Chuẩn bị câu lệnh SQL để lấy tất cả phòng
    // Sắp xếp theo RoomID để có thứ tự nhất quán
    $sql = "SELECT RoomID, RoomName, RoomType, Price, Status FROM Room ORDER BY RoomID ASC";
    
    // 4. Thực thi câu lệnh
    $stmt = $pdo->query($sql);
    
    // 5. Lấy tất cả kết quả dưới dạng mảng
    $rooms = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // 6. Trả về kết quả dưới dạng JSON
    echo json_encode(['success' => true, 'rooms' => $rooms]);

} catch (Exception $e) {
    // 7. Nếu có lỗi, trả về thông báo lỗi JSON
    echo json_encode(['success' => false, 'message' => 'Lỗi khi lấy danh sách phòng: ' . $e->getMessage()]);
}
