<?php
// Tệp này chỉ dùng một lần để tạo admin, sau đó nên xóa đi

// 1. Kết nối CSDL
include 'db.php';

// 2. Thông tin tài khoản admin
$username = 'admin';
$password = 'admin123'; // Mật khẩu gốc
$fullname = 'Quản trị viên';
$email = 'admin@khachsan.com';
$role = 'Admin';

try {
    // 3. Mã hóa mật khẩu (Đây là bước quan trọng nhất)
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
    
    // 4. Chuẩn bị câu lệnh INSERT
    $sql = "INSERT INTO Users (Username, PasswordHash, FullName, Role, Email) 
            VALUES (?, ?, ?, ?, ?)";
    
    $stmt = $pdo->prepare($sql);
    
    // 5. Thực thi
    $stmt->execute([$username, $passwordHash, $fullname, $role, $email]);

    echo "<h1>Tài khoản admin đã được tạo thành công!</h1>";
    echo "<p>Username: $username</p>";
    echo "<p>Email: $email</p>";
    echo "<p>Mật khẩu: $password</p>";
    echo "<p>Bản mã hóa (đã lưu vào CSDL): $passwordHash</p>";
    echo "<a href='login.html'>Quay lại trang Đăng nhập</a>";

} catch (Exception $e) {
    echo "<h1>LỖI!</h1>";
    // Kiểm tra xem có phải lỗi trùng lặp không
    if ($e->getCode() == 23000) {
        echo "<p>Tài khoản admin đã tồn tại. Hãy đăng nhập.</p>";
    } else {
        echo "<p>Không thể tạo tài khoản: " . $e->getMessage() . "</p>";
    }
}
?>