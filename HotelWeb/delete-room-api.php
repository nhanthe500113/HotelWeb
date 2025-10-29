<?php
include 'db.php';
header('Content-Type: application/json');

// 1. Lấy tên phòng cần xóa từ JavaScript (gửi bằng POST)
$roomName = $_POST['room_name'] ?? '';

// 2. Validate
if (empty($roomName)) {
    echo json_encode(['success' => false, 'message' => 'Vui lòng nhập số phòng cần xóa.']);
    exit;
}

try {
    // 3. Chuẩn bị câu lệnh DELETE
    $sql = "DELETE FROM Room WHERE RoomName = ?";
    $stmt = $pdo->prepare($sql);
    
    // 4. Thực thi
    $stmt->execute([$roomName]);

    // 5. Kiểm tra xem có dòng nào bị ảnh hưởng không
    if ($stmt->rowCount() > 0) {
        // Nếu có dòng bị xóa -> thành công
        echo json_encode(['success' => true, 'message' => 'Đã xóa phòng ' . $roomName . ' thành công!']);
    } else {
        // Nếu không có dòng nào bị xóa -> phòng không tồn tại
        echo json_encode(['success' => false, 'message' => 'Không tìm thấy phòng ' . $roomName . ' để xóa.']);
    }

} catch (Exception $e) {
    // 6. Xử lý lỗi (ví dụ: phòng đang được đặt, không xóa được do khóa ngoại)
     if ($e->getCode() == 23000) { // Lỗi khóa ngoại
        echo json_encode(['success' => false, 'message' => 'Không thể xóa phòng '. $roomName .' vì đang có người đặt.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Lỗi CSDL: ' . $e->getMessage()]);
    }
}
?>