<?php
// Bật hiển thị lỗi để debug
ini_set('display_errors', 1);
error_reporting(E_ALL);

include 'db.php';

echo "<h1>Bắt đầu tạo tài khoản Nhân viên...</h1>";

// --- THÔNG TIN TÀI KHOẢN CẦN TẠO ---
$email = 'nhanvien@gmail.com';
$username = 'nhanvien'; // Tên đăng nhập (nếu cần)
$password = 'admin123'; // Mật khẩu bạn muốn đặt
$fullName = 'Nhân Viên A';
$role = 'Nhân viên'; // QUAN TRỌNG: Phải viết hoa chữ 'N'
// ------------------------------------

try {
    // 1. Mã hóa mật khẩu
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
    if (!$hashedPassword) {
        throw new Exception("Không thể hash mật khẩu.");
    }
    echo "Đã mã hóa mật khẩu: " . $hashedPassword . "<br>";

    // 2. XÓA tài khoản cũ (nếu có)
    // Chúng ta xóa dựa trên Email VÀ Username để đảm bảo sạch
    $sqlDelete = "DELETE FROM Users WHERE Email = ? OR Username = ?";
    $stmtDelete = $pdo->prepare($sqlDelete);
    $stmtDelete->execute([$email, $username]);
    echo "Đã xóa " . $stmtDelete->rowCount() . " tài khoản cũ (nếu có).<br>";

    // 3. THÊM tài khoản mới
    $sqlInsert = "INSERT INTO Users (Username, PasswordHash, FullName, Role, Email) 
                  VALUES (?, ?, ?, ?, ?)";
    $stmtInsert = $pdo->prepare($sqlInsert);
    $stmtInsert->execute([$username, $hashedPassword, $fullName, $role, $email]);

    echo "------------------------------------<br>";
    echo "<h2>THÀNH CÔNG!</h2>";
    echo "Đã tạo tài khoản nhân viên thành công.<br>";
    echo "<strong>Email (dùng để đăng nhập):</strong> " . $email . "<br>";
    echo "<strong>Mật khẩu:</strong> " . $password . "<br>";
    echo "<strong>Vai trò:</strong> " . $role . "<br>";

} catch (Exception $e) {
    echo "<h2>LỖI NGHIÊM TRỌNG:</h2>";
    echo $e->getMessage();
}
?>