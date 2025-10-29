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
    // (Lưu ý: Nếu UserID này đã liên kết với bảng Customer, CSDL của bạn cần cài đặt 'ON DELETE SET NULL')
    $sql = "DELETE FROM Users WHERE UserID = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$userID]);

    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true, 'message' => 'Xóa tài khoản thành công!']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Không tìm thấy tài khoản để xóa.']);
    }

} catch (Exception $e) {
    // Bắt lỗi nếu không thể xóa (do ràng buộc khóa ngoại)
    echo json_encode(['success' => false, 'message' => 'Lỗi CSDL: Không thể xóa tài khoản này (có thể do đã liên kết với khách hàng hoặc đơn đặt phòng). Lỗi: ' . $e->getMessage()]);
}
?>