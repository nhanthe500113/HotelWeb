<?php
session_start();
include 'db.php';
header('Content-Type: application/json');

// 1. Kiểm tra xem người dùng đã đăng nhập chưa
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Bạn phải đăng nhập để đặt phòng.']);
    exit;
}

// 2. Lấy dữ liệu từ POST (ĐÃ THAY ĐỔI)
$roomID = $_POST['room_id'] ?? 0;
$checkinDate = $_POST['checkin_date'] ?? '';
$checkoutDate = $_POST['checkout_date'] ?? '';
$userID = $_SESSION['user_id'];

// 3. Validate dữ liệu (ĐÃ THAY ĐỔI)
if (empty($roomID) || empty($checkinDate) || empty($checkoutDate)) {
    echo json_encode(['success' => false, 'message' => 'Vui lòng chọn phòng và ngày nhận/trả phòng hợp lệ.']);
    exit;
}

// Chuyển đổi ngày thành đối tượng DateTime để so sánh
try {
    $checkin_dt = new DateTime($checkinDate);
    $checkout_dt = new DateTime($checkoutDate);
    $today_dt = new DateTime(date('Y-m-d')); // Lấy ngày hôm nay (không có giờ)

    if ($checkin_dt < $today_dt) {
        throw new Exception('Ngày nhận phòng không thể ở trong quá khứ.');
    }
    if ($checkout_dt <= $checkin_dt) {
        throw new Exception('Ngày trả phòng phải sau ngày nhận phòng ít nhất 1 ngày.');
    }

    // Tính số ngày ở
    $interval = $checkin_dt->diff($checkout_dt);
    $numDays = $interval->days;
    
    if ($numDays <= 0) {
         throw new Exception('Số ngày ở không hợp lệ.');
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    exit;
}


try {
    // Bắt đầu một Transaction
    $pdo->beginTransaction();

    // 4. Lấy CustomerID từ UserID
    $sqlCustomer = "SELECT CustomerID FROM Customer WHERE UserID = ?";
    $stmtCustomer = $pdo->prepare($sqlCustomer);
    $stmtCustomer->execute([$userID]);
    $customer = $stmtCustomer->fetch();

    if (!$customer) {
        throw new Exception('Tài khoản của bạn không thể dùng để đặt phòng.');
    }
    $customerID = $customer['CustomerID'];

    // 5. Lấy thông tin phòng VÀ KHÓA HÀNG LẠI
    $sqlRoom = "SELECT Price, Status FROM Room WHERE RoomID = ? FOR UPDATE";
    $stmtRoom = $pdo->prepare($sqlRoom);
    $stmtRoom->execute([$roomID]);
    $room = $stmtRoom->fetch();

    if (!$room) {
        throw new Exception('Phòng không tồn tại.');
    }
    // (Chúng ta sẽ kiểm tra phòng trống theo ngày sau, tạm thời vẫn check status 'Trống')
    if ($room['Status'] !== 'Trống') {
        throw new Exception('Rất tiếc, phòng này vừa được đặt. Vui lòng chọn phòng khác.');
    }
    
    // 6. Tính toán tổng tiền (DÙNG $numDays đã tính)
    $totalAmount = $room['Price'] * $numDays;

    // 7. TẠO Booking mới (ĐÃ CẬP NHẬT QUERY)
    $sqlInsertBooking = "INSERT INTO Booking (RoomID, CustomerID, CheckInDate, CheckOutDate, TotalAmount, Status) 
                         VALUES (?, ?, ?, ?, ?, 'Đang ở')";
    $stmtInsert = $pdo->prepare($sqlInsertBooking);
    // Gửi ngày đã validate vào
    $stmtInsert->execute([$roomID, $customerID, $checkinDate, $checkoutDate, $totalAmount]);

    // 8. CẬP NHẬT trạng thái phòng
    $sqlUpdateRoom = "UPDATE Room SET Status = 'Đang thuê' WHERE RoomID = ?";
    $stmtUpdate = $pdo->prepare($sqlUpdateRoom);
    $stmtUpdate->execute([$roomID]);

    // 9. Hoàn tất transaction
    $pdo->commit();

    echo json_encode(['success' => true, 'message' => 'Đặt phòng thành công!']);

} catch (Exception $e) {
    // Nếu có lỗi, hủy bỏ mọi thay đổi
    $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>