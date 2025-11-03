<?php
session_start();
include 'db.php';
header('Content-Type: application/json');

$userID = $_POST['user_id'] ?? 0;

if (empty($userID)) {
    echo json_encode(['success' => false, 'message' => 'Vui lòng chọn tài khoản để xóa.']);
    exit;
}

// Kiểm tra không cho Admin tự xóa mình
if ($userID == $_SESSION['user_id']) {
    echo json_encode(['success' => false, 'message' => 'Bạn không thể tự xóa tài khoản Quản trị viên của chính mình.']);
    exit;
}

try {
    // [NÂNG CẤP] Dùng Transaction để đảm bảo an toàn
    $pdo->beginTransaction();

    // BƯỚC 1: Ngắt kết nối tài khoản này khỏi bảng Customer
    // Đặt UserID = NULL trong bảng Customer để phá vỡ ràng buộc
    $sqlUnlink = "UPDATE Customer SET UserID = NULL WHERE UserID = ?";
    $stmtUnlink = $pdo->prepare($sqlUnlink);
    $stmtUnlink->execute([$userID]);

    // BƯỚC 2: Xóa tài khoản khỏi bảng Users
    $sqlDelete = "DELETE FROM Users WHERE UserID = ?";
    $stmtDelete = $pdo->prepare($sqlDelete);
    $stmtDelete->execute([$userID]);

    // BƯỚC 3: Hoàn tất
    $pdo->commit();

    if ($stmtDelete->rowCount() > 0) {
        echo json_encode(['success' => true, 'message' => 'Xóa tài khoản thành công! (Hồ sơ khách hàng liên quan đã được ngắt kết nối).']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Không tìm thấy tài khoản để xóa.']);
    }

} catch (Exception $e) {
    // Nếu có lỗi, hoàn tác lại
    $pdo->rollBack();
    
    // Gửi về thông báo lỗi chi tiết
    echo json_encode(['success' => false, 'message' => 'Lỗi CSDL khi xóa: ' . $e->getMessage()]);
}
?>
