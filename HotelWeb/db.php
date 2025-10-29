<?php
// Tên máy chủ CSDL (thường là 127.0.0.1 hoặc localhost)
$host = '127.0.0.1'; 
// Tên CSDL
$dbname = 'HotelManagement';
// Tên người dùng CSDL (mặc định của XAMPP/WAMP thường là 'root')
$username = 'root'; 
// Mật khẩu CSDL (mặc định của XAMPP/WAMP thường là để trống)
$password = '123456'; 
$charset = 'utf8mb4';

try {
    // Tạo chuỗi DSN (Data Source Name)
    $dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";
    
    // Các tùy chọn cho PDO
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    
    // Tạo đối tượng PDO (kết nối CSDL)
    $pdo = new PDO($dsn, $username, $password, $options);

} catch (\PDOException $e) {
    // Ném lỗi nếu kết nối thất bại
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}
