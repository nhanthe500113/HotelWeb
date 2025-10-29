<?php
include 'db.php';
header('Content-Type: application/json');

$username = $_POST['username'] ?? '';
$fullname = $_POST['fullname'] ?? '';
$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';
$role = $_POST['role'] ?? '';

if (empty($username) || empty($fullname) || empty($email) || empty($password) || empty($role)) {
    echo json_encode(['success' => false, 'message' => 'Vui lòng nhập đầy đủ các trường bắt buộc (*).']);
    exit;
}

try {
    // Kiểm tra trùng email hoặc username
    $sqlCheck = "SELECT UserID FROM Users WHERE Email = ? OR Username = ?";
    $stmtCheck = $pdo->prepare($sqlCheck);
    $stmtCheck->execute([$email, $username]);
    if ($stmtCheck->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Email hoặc Tên đăng nhập đã tồn tại.']);
        exit;
    }

    // Mã hóa mật khẩu
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

    $sql = "INSERT INTO Users (Username, PasswordHash, FullName, Role, Email) 
            VALUES (?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$username, $hashedPassword, $fullname, $role, $email]);
    
    // [NÂNG CẤP] Nếu vai trò là 'Customer', tự động tạo hồ sơ Customer
    if ($role === 'Customer') {
        // Lấy UserID vừa được tạo
        $lastUserID = $pdo->lastInsertId();
        
        // Thêm hồ sơ khách hàng (chỉ cần FullName và UserID)
        $sqlCustomer = "INSERT INTO Customer (FullName, UserID) VALUES (?, ?)";
        $stmtCustomer = $pdo->prepare($sqlCustomer);
        $stmtCustomer->execute([$fullname, $lastUserID]);
    }

    echo json_encode(['success' => true, 'message' => 'Thêm tài khoản ' . $fullname . ' thành công!']);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Lỗi CSDL: ' . $e->getMessage()]);
}
?>