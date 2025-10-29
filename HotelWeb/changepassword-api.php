<?php
include 'db.php';
session_start();
header('Content-Type: application/json');

// 1. Lấy dữ liệu từ form
$sdt = $_POST['phone'];
$mat_khau_cu = $_POST['old_password'];
$mat_khau_moi = $_POST['new_password'];
$xac_nhan_mat_khau = $_POST['confirm_password'];

// 2. Kiểm tra dữ liệu
if (empty($sdt) || empty($mat_khau_cu) || empty($mat_khau_moi)) {
    echo json_encode(['success' => false, 'message' => 'Vui lòng nhập đầy đủ thông tin.']);
    exit;
}

if ($mat_khau_moi !== $xac_nhan_mat_khau) {
    echo json_encode(['success' => false, 'message' => 'Mật khẩu mới không khớp.']);
    exit;
}

try {
    // 3. Tìm người dùng
    // Vì SĐT nằm ở bảng Customer, chúng ta cần JOIN 2 bảng
    $sql = "SELECT U.UserID, U.PasswordHash 
            FROM Users U
            JOIN Customer C ON U.UserID = C.UserID
            WHERE C.Phone = ?";
            
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$sdt]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // 4. Xác minh người dùng
    if ($user && password_verify($mat_khau_cu, $user['PasswordHash'])) {
        // Mật khẩu cũ chính xác!
        
        // 5. Mã hóa và cập nhật mật khẩu mới
        $newPasswordHash = password_hash($mat_khau_moi, PASSWORD_DEFAULT);
        
        $sqlUpdate = "UPDATE Users SET PasswordHash = ? WHERE UserID = ?";
        $stmtUpdate = $pdo->prepare($sqlUpdate);
        $stmtUpdate->execute([$newPasswordHash, $user['UserID']]);
        
        echo json_encode([
            'success' => true, 
            'message' => 'Đổi mật khẩu thành công! Vui lòng đăng nhập lại.',
            'redirect' => 'login.html'
        ]);

    } else {
        // Sai SĐT hoặc Mật khẩu cũ
        echo json_encode(['success' => false, 'message' => 'Sai Số điện thoại hoặc Mật khẩu cũ.']);
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Lỗi máy chủ: ' . $e->getMessage()]);
}
?>