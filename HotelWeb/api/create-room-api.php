<?php
include 'db.php';
header('Content-Type: application/json');

// 1. Lấy dữ liệu
$roomName = $_POST['room_name'] ?? '';
$roomType = $_POST['room_type'] ?? '';
$price = $_POST['price'] ?? '';

// 2. Validate dữ liệu (giữ nguyên)
if (empty($roomName) || empty($roomType) || !is_numeric($price) || $price <= 0) {
    echo json_encode(['success' => false, 'message' => 'Vui lòng nhập đầy đủ Số phòng, chọn Loại phòng và nhập Giá hợp lệ.']);
    exit;
}

try {
    // === BƯỚC KIỂM TRA MỚI ===
    // 3. Kiểm tra xem RoomName đã tồn tại chưa
    $sqlCheck = "SELECT RoomID FROM Room WHERE RoomName = ?";
    $stmtCheck = $pdo->prepare($sqlCheck);
    $stmtCheck->execute([$roomName]);

    if ($stmtCheck->fetch()) {
        // Nếu fetch() trả về kết quả (tìm thấy phòng), báo lỗi
        echo json_encode(['success' => false, 'message' => 'Số phòng "' . $roomName . '" đã tồn tại. Vui lòng chọn số khác.']);
        exit; // Dừng lại, không INSERT nữa
    }
    // === KẾT THÚC BƯỚC KIỂM TRA ===

    // 4. Nếu không trùng, tiến hành INSERT (giữ nguyên)
    $sqlInsert = "INSERT INTO Room (RoomName, RoomType, Price, Status) VALUES (?, ?, ?, 'Trống')";
    $stmtInsert = $pdo->prepare($sqlInsert);
    $stmtInsert->execute([$roomName, $roomType, $price]);

    // 5. Trả về thành công
    echo json_encode(['success' => true, 'message' => 'Đã thêm phòng ' . $roomName . ' thành công!']);

} catch (Exception $e) {
    // 6. Xử lý lỗi CSDL khác (nếu có)
    echo json_encode(['success' => false, 'message' => 'Lỗi CSDL: ' . $e->getMessage()]);
}
?>