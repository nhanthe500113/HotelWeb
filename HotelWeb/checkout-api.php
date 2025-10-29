<?php
include 'db.php';
header('Content-Type: application/json');

$bookingID = $_POST['booking_id'] ?? 0;

if (empty($bookingID)) {
    echo json_encode(['success' => false, 'message' => 'Vui lòng chọn một đơn đặt phòng để trả.']);
    exit;
}

try {
    // Dùng Transaction để đảm bảo cả 2 bảng được cập nhật
    $pdo->beginTransaction();
    
    // 1. Lấy RoomID từ BookingID để cập nhật phòng
    $sqlGetRoom = "SELECT RoomID FROM Booking WHERE BookingID = ?";
    $stmtGetRoom = $pdo->prepare($sqlGetRoom);
    $stmtGetRoom->execute([$bookingID]);
    $booking = $stmtGetRoom->fetch();
    
    if (!$booking) {
        throw new Exception('Không tìm thấy đơn đặt phòng này.');
    }
    $roomID = $booking['RoomID'];

    // 2. Cập nhật Booking: Đổi Status, set ngày trả phòng là NGAY BÂY GIỜ
    $sqlBooking = "UPDATE Booking 
                   SET Status = 'Đã trả', CheckOutDate = NOW() 
                   WHERE BookingID = ?";
    $stmtBooking = $pdo->prepare($sqlBooking);
    $stmtBooking->execute([$bookingID]);

    // 3. Cập nhật Room: Đổi Status thành 'Đang dọn'
    $sqlRoom = "UPDATE Room SET Status = 'Đang dọn' WHERE RoomID = ?";
    $stmtRoom = $pdo->prepare($sqlRoom);
    $stmtRoom->execute([$roomID]);

    // 4. Hoàn tất
    $pdo->commit();
    
    echo json_encode(['success' => true, 'message' => 'Trả phòng thành công! Phòng ' . $roomID . ' đã được chuyển sang trạng thái "Đang dọn".']);

} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => 'Lỗi CSDL khi trả phòng: ' . $e->getMessage()]);
}
?>