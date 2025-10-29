<?php session_start(); ?>
<!DOCTYPE html>
<html>
  <head>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta charset="utf-8" />
    <link rel="stylesheet" href="globals.css" />
    <link rel="stylesheet" href="styleguide.css" />
    <link rel="stylesheet" href="changepass.css" />
  </head>
  <body>
    <div class="page-container">
      <header class="navigation">
        <div class="nav-content-wrapper">
          <a href="mainmenu.php" class="nav-logo">AA Hotel</a>
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
        <div class="form-container">
          <h1 class="form-title">Đổi Mật Khẩu</h1>
          <form class="form">
            <div class="input-group">
              <label for="phone">SĐT*</label>
              <input class="field" type="tel" id="phone" name="phone" placeholder="0000-000-000" />
            </div>
            <div class="input-group">
              <label for="old_password">Mật Khẩu Cũ</label>
              <input class="field" type="password" id="old_password" name="old_password" placeholder="xxxxxx" />
            </div>
            <div class="input-group">
              <label for="new_password">Mật Khẩu Mới</label>
              <input class="field" type="password" id="new_password" name="new_password" placeholder="xxxxxx" />
            </div>
            <div class="input-group">
              <label for="confirm_password">Xác Nhận Mật Khẩu</label>
              <input class="field" type="password" id="confirm_password" name="confirm_password" placeholder="xxxxxx" />
            </div>
            <button class="submit-button" type="submit">Đổi Mật Khẩu</button>
          </form>
        </div>
      </main>

      <footer class="footer">
        <div class="footer-content">
            <div class="footer-logo">Khách Sạn AA</div>
            <a href="admin.php"><button class="footer-button"><div class="footer-button-text">Quản Trị Hệ Thống</div></button></a>
        </div>
      </footer>
    </div>
    <script>
        // 1. Tìm form
        const changePassForm = document.querySelector('.form');
        
        // 2. Thêm sự kiện khi nhấn nút "Đổi Mật Khẩu"
        changePassForm.addEventListener('submit', function(event) {
            // Ngăn form tải lại trang
            event.preventDefault(); 
            
            // 3. Lấy toàn bộ dữ liệu trong form
            const formData = new FormData(changePassForm);
            
            // 4. Gửi dữ liệu đến file API
            fetch('changepassword-api.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json()) // Đọc kết quả JSON
            .then(data => {
                // 5. Xử lý kết quả
                alert(data.message); // Hiển thị thông báo
                
                if (data.success) {
                    // Nếu đổi thành công, chuyển đến trang đăng nhập
                    window.location.href = data.redirect; 
                }
            })
            .catch(error => {
                console.error('Lỗi nghiêm trọng:', error);
                alert('Đã xảy ra lỗi, không thể gửi yêu cầu.');
            });
        });
    </script>
  </body>
</html>