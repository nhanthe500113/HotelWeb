<?php
session_start();
include 'db.php';
header('Content-Type: application/json');

// 1. Kiểm tra vai trò Admin (chỉ Admin mới được xác nhận)
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'Admin') {
    echo json_encode(['success' => false, 'message' => 'Bạn không có quyền thực hiện hành động này.']);
    exit;
}

$bookingID = $_POST['booking_id'] ?? 0;
if (empty($bookingID)) {
    echo json_encode(['success' => false, 'message' => 'Thiếu Booking ID.']);
    exit;
}

try {
    $pdo->beginTransaction();

    // 2. Kiểm tra xem hóa đơn đã tồn tại chưa
    $sqlCheck = "SELECT InvoiceID FROM Invoice WHERE BookingID = ?";
    $stmtCheck = $pdo->prepare($sqlCheck);
    $stmtCheck->execute([$bookingID]);
    
    if ($stmtCheck->fetch()) {
        echo json_encode(['success' => true, 'message' => 'Hóa đơn này đã được xác nhận thanh toán trước đó.']);
        $pdo->commit();
        exit;
    }

    // 3. Lấy tổng tiền phòng từ Booking
    $sqlGetTotal = "SELECT TotalAmount FROM Booking WHERE BookingID = ?";
    $stmtGetTotal = $pdo->prepare($sqlGetTotal);
    $stmtGetTotal->execute([$bookingID]);
    $booking = $stmtGetTotal->fetch();

    if (!$booking) {
        throw new Exception('Không tìm thấy đơn đặt phòng.');
    }

    // 4. Tính toán tổng cuối cùng (giả định có 10% phí DV như trong HTML)
    $roomCost = $booking['TotalAmount'];
    $serviceFee = $roomCost * 0.10; // 10% phí dịch vụ
    $finalTotal = $roomCost + $serviceFee;

    // 5. Tạo Hóa Đơn Mới
    $sqlInsert = "INSERT INTO Invoice (BookingID, TotalAmount, InvoiceDate) VALUES (?, ?, NOW())";
    $stmtInsert = $pdo->prepare($sqlInsert);
    $stmtInsert->execute([$bookingID, $finalTotal]);
    
    // (Tùy chọn) Cập nhật trạng thái Booking thành 'Đã thanh toán'
    // Bạn cần thêm 'Đã thanh toán' vào CSDL nếu muốn
    // $sqlUpdate = "UPDATE Booking SET Status = 'Đã thanh toán' WHERE BookingID = ?";
    // $pdo->prepare($sqlUpdate)->execute([$bookingID]);

    $pdo->commit();
    echo json_encode(['success' => true, 'message' => 'Đã xác nhận thanh toán và tạo hóa đơn thành công!']);

} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => 'Lỗi CSDL: ' . $e->getMessage()]);
}
?>