<?php
session_start();
include 'db.php';
header('Content-Type: application/json');

// 1. Kiểm tra xem người dùng đã đăng nhập chưa
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Bạn phải đăng nhập để đặt phòng.']);
    exit;
}

// 2. Lấy dữ liệu
// Dữ liệu đặt phòng cơ bản
$roomID = $_POST['room_id'] ?? 0;
$checkinDate = $_POST['checkin_date'] ?? '';
$checkoutDate = $_POST['checkout_date'] ?? '';

// Thông tin người dùng đang đăng nhập
$loggedInUserID = $_SESSION['user_id'];
$loggedInUserRole = $_SESSION['user_role'];

// [MỚI] Dữ liệu khách vãng lai (nếu có)
$walkinFullname = $_POST['walkin_fullname'] ?? '';
$walkinCccd = $_POST['walkin_cccd'] ?? '';
$walkinPhone = $_POST['walkin_phone'] ?? '';

// 3. Validate dữ liệu cơ bản
if (empty($roomID) || empty($checkinDate) || empty($checkoutDate)) {
    echo json_encode(['success' => false, 'message' => 'Vui lòng chọn phòng và ngày nhận/trả phòng hợp lệ.']);
    exit;
}

// 4. Validate ngày tháng
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

    $customerID = null; // Biến để lưu CustomerID

    // Xác định CustomerID
    
    //Admin/Nhân viên đang đặt cho khách vãng lai
    if (($loggedInUserRole === 'Admin' || $loggedInUserRole === 'Nhân viên') && !empty($walkinFullname)) 
    {
        if (empty($walkinCccd)) {
             throw new Exception('Nhân viên: Vui lòng nhập CCCD của khách.');
        }

        // 1.1. Kiểm tra xem khách đã tồn tại (dựa trên CCCD)
        $sqlCheckCust = "SELECT CustomerID FROM Customer WHERE CCCD = ?";
        $stmtCheckCust = $pdo->prepare($sqlCheckCust);
        $stmtCheckCust->execute([$walkinCccd]);
        $existingCustomer = $stmtCheckCust->fetch();

        if ($existingCustomer) {
            // 1.2. Khách đã tồn tại -> Lấy CustomerID
            $customerID = $existingCustomer['CustomerID'];
        } else {
            // 1.3. Khách mới -> Tạo hồ sơ Customer (UserID là NULL vì họ không có tài khoản)
            $sqlNewCust = "INSERT INTO Customer (FullName, Phone, CCCD, UserID) VALUES (?, ?, ?, NULL)";
            $stmtNewCust = $pdo->prepare($sqlNewCust);
            $stmtNewCust->execute([$walkinFullname, $walkinPhone, $walkinCccd]);
            $customerID = $pdo->lastInsertId(); // Lấy CustomerID vừa tạo
        }
    } 
    // Customer tự đặt phòng
    elseif ($loggedInUserRole === 'Customer') 
    {
        $sqlCustomer = "SELECT CustomerID FROM Customer WHERE UserID = ?";
        $stmtCustomer = $pdo->prepare($sqlCustomer);
        $stmtCustomer->execute([$loggedInUserID]);
        $customer = $stmtCustomer->fetch();
        
        if (!$customer) {
            throw new Exception('Tài khoản của bạn chưa có hồ sơ khách hàng. Vui lòng liên hệ hỗ trợ.');
        }
        $customerID = $customer['CustomerID'];
    }
    
    // 1.4. Nếu vì lý do nào đó không tìm thấy CustomerID
    if (empty($customerID)) {
        throw new Exception('Không thể xác định thông tin khách hàng để đặt phòng. Admin/Nhân viên phải điền form khách vãng lai.');
    }

    // 5. Lấy thông tin phòng VÀ KHÓA HÀNG LẠI
    $sqlRoom = "SELECT Price, Status FROM Room WHERE RoomID = ? FOR UPDATE";
    $stmtRoom = $pdo->prepare($sqlRoom);
    $stmtRoom->execute([$roomID]);
    $room = $stmtRoom->fetch();

    if (!$room) {
        throw new Exception('Phòng không tồn tại.');
    }
    if ($room['Status'] !== 'Trống') {
        throw new Exception('Rất tiếc, phòng này vừa được đặt. Vui lòng chọn phòng khác.');
    }
    
    // 6. Tính toán tổng tiền
    $totalAmount = $room['Price'] * $numDays;

    // 7. TẠO Booking mới (Dùng $customerID đã được xác định ở trên)
    $sqlInsertBooking = "INSERT INTO Booking (RoomID, CustomerID, CheckInDate, CheckOutDate, TotalAmount, Status) 
                         VALUES (?, ?, ?, ?, ?, 'Đang ở')";
    $stmtInsert = $pdo->prepare($sqlInsertBooking);
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