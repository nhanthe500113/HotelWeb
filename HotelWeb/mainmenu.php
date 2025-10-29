<?php session_start(); ?>
<!DOCTYPE html>
<html>
  <head>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta charset="utf-8" />
    <link rel="stylesheet" href="globals.css" />
    <link rel="stylesheet" href="styleguide.css" />
    <link rel="stylesheet" href="mainmenu.css" />
  </head>
  <body>
    <div class="page-container">
      <header class="navigation">
        <div class="nav-content-wrapper">
          <div class="nav-logo active">AA Hotel</div>
          <div class="nav-items">
    <a href="reserveroom.php" class="nav-link">Đặt Phòng</a>

    <?php if (isset($_SESSION['user_id'])): 
        // Lấy vai trò để dễ kiểm tra
        $role = $_SESSION['user_role'];
    ?>
        <span class="nav-link-welcome">Xin chào, <?php echo htmlspecialchars($_SESSION['user_name']); ?>!</span>
        
        <?php 
        // Hiển thị "Panel Nhân Viên" nếu là Admin HOẶC Nhân viên
        if ($role === 'Admin' || $role === 'Nhân viên'): ?>
            <a href="staff.php" class="nav-link">Panel Nhân Viên</a>
        <?php endif; ?>

        <?php 
        // Chỉ hiển thị "Panel Quản Trị" nếu là Admin
        if ($role === 'Admin'): ?>
            <a href="admin.php" class="nav-link">Panel Quản Trị</a>
        <?php endif; ?>

        <a href="logout.php" class="nav-button"><div class="nav-button-text">Đăng Xuất</div></a>

    <?php else: ?>
        <a href="register.php" class="nav-link">Đăng Ký</a>
        <a href="login.php" class="nav-button"><div class="nav-button-text">Đăng Nhập</div></a>
    <?php endif; ?>
</div>
        </div>
      </header>

      <main class="main-content">
        <div class="hero-section">
            <div class="hero-text">
                <div class="hero-title">Chào Mừng</div>
                <p class="hero-subtitle">Đặt Phòng Của Khách Sạn Của Chúng Tôi Tại Đây:</p>
            </div>
            <a href="reserveroom.php"><button class="hero-button">Đặt Phòng</button></a>
        </div>
        <img class="hero-image" src="img/hotel.jfif" />

        <div class="room-list-section">
            <h2 class="room-list-title">Danh Sách Phòng</h2>
            <div class="room-cards-container">
                <div class="card">
                    <div class="image-placeholder image-don"></div>
                    <div class="copy">
                        <div class="room-name">Phòng Đơn</div>
                        <p class="room-desc">Phòng đơn tiêu chuẩn với các dịch vụ cơ bản.</p>
                        <a href="reserveroom.php" class="card-button">Đặt ngay</a>
                    </div>
                </div>
                <div class="card">
                    <div class="image-placeholder image-doi"></div>
                    <div class="copy">
                        <div class="room-name">Phòng Đôi</div>
                        <p class="room-desc">Lý tưởng cho gia đình và các cặp đôi.</p>
                        <a href="reserveroom.php" class="card-button">Đặt ngay</a>
                    </div>
                </div>
                <div class="card">
                    <div class="image-placeholder image-vip"></div>
                    <div class="copy">
                        <div class="room-name">Phòng VIP</div>
                        <p class="room-desc">Tận hưởng các dịch vụ cao cấp nhất của chúng tôi!</p>
                        <a href="reserveroom.php" class="card-button">Đặt ngay</a>
                    </div>
                </div>
            </div>
        </div>
      </main>

      <footer class="footer">
        <div class="footer-content">
            <div class="footer-logo">Khách Sạn AA</div>
            <a href="admin.php"><button class="footer-button"><div class="footer-button-text">Quản Trị Hệ Thống</div></button></a>
        </div>
      </footer>
    </div>
  </body>
</html>