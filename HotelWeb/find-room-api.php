<?php
// 1. Kết nối CSDL
include 'db.php';

// 2. Báo cho trình duyệt biết đây là file JSON
header('Content-Type: application/json');

// 3. Lấy tên/số phòng cần tìm
$searchTerm = isset($_GET['searchTerm']) ? trim($_GET['searchTerm']) : '';

// 4. Nếu searchTerm rỗng, trả về tất cả phòng (hoặc lỗi, tùy bạn chọn)
if (empty($searchTerm)) {
    //Trả về lỗi
     echo json_encode(['success' => false, 'message' => 'Vui lòng nhập số phòng để tìm.']);
     exit;
}

try {
    // 5. Chuẩn bị câu lệnh SQL để tìm phòng (dùng LIKE)
     $sql = "SELECT RoomID, RoomName, RoomType, Price, Status FROM Room WHERE RoomName LIKE ? ORDER BY RoomID ASC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['%' . $searchTerm . '%']);

    // 6. Lấy TẤT CẢ kết quả phù hợp
    $rooms = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 7. Trả về kết quả
    if ($rooms) {
        // Tìm thấy phòng(s)
        echo json_encode(['success' => true, 'rooms' => $rooms]);
    } else {
        // Không tìm thấy phòng nào
        echo json_encode(['success' => true, 'rooms' => []]); // Trả về mảng rỗng
    }

} catch (Exception $e) {
    // 8. Nếu có lỗi CSDL
    echo json_encode(['success' => false, 'message' => 'Lỗi CSDL: ' . $e->getMessage()]);
}
?>