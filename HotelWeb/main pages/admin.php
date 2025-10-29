<?php 
session_start(); 
// --- BẢO VỆ TRANG ADMIN ---
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'Admin') {
    // Nếu không phải Admin, đặt thông báo lỗi
    $_SESSION['flash_message'] = 'Bạn phải đăng nhập với tư cách Quản trị viên để truy cập trang này.';
    // Chuyển hướng về trang đăng nhập
    header('Location: login.php');
    exit;
}
// --- KẾT THÚC BẢO VỆ ---
// --- [MỚI] LẤY DỮ LIỆU THỐNG KÊ ---
include 'db.php'; // Kết nối CSDL

try {
    // 1. Doanh Thu (Từ hóa đơn đã xác nhận)
    $stmt_rev = $pdo->query("SELECT SUM(TotalAmount) AS TotalRevenue FROM Invoice");
    $totalRevenue = $stmt_rev->fetchColumn() ?: 0; // Lấy 0 nếu NULL

    // 2. Lượng Khách (Tổng số tài khoản khách hàng)
    $stmt_guests = $pdo->query("SELECT COUNT(*) FROM Customer"); // Giả định Customer là khách
    $totalGuests = $stmt_guests->fetchColumn() ?: 0;

    // 3. Lượng Đặt Trước (Đang ở)
    $stmt_bookings = $pdo->query("SELECT COUNT(*) FROM Booking WHERE Status = 'Đang ở'");
    $totalBookings = $stmt_bookings->fetchColumn() ?: 0;

} catch (Exception $e) {
    // Đặt giá trị mặc định nếu lỗi CSDL
    $totalRevenue = 0;
    $totalGuests = 0;
    $totalBookings = 0;
    // (Tùy chọn) Hiển thị lỗi: echo $e->getMessage();
}
// --- KẾT THÚC LẤY DỮ LIỆU ---
?>
<!DOCTYPE html>
<html>
  <head>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta charset="utf-8" />
    <link rel="stylesheet" href="globals.css" />
    <link rel="stylesheet" href="styleguide.css" />
    <link rel="stylesheet" href="admin.css" />
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
            <a href="admin.php" class="nav-link active">Panel Quản Trị</a>
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
        <h1 class="panel-title">Panel Quản Trị Viên</h1>
        <div class="panel">
          <section class="stats-section">
            <div class="stat-card">
              <div class="stat-title revenue">Doanh Thu</div>
              <div class="stat-value"><?php echo number_format($totalRevenue); ?> VNĐ</div>
            </div>
            <div class="stat-card">
              <div class="stat-title guests">Lượng Khách</div>
              <div class="stat-value"><?php echo number_format($totalGuests); ?></div>
            </div>
            <div class="stat-card">
              <div class="stat-title bookings">Lượng Đặt Trước</div>
              <div class="stat-value"><?php echo number_format($totalBookings); ?></div>
            </div>
          </section>

          <section class="management-section">
            <h2>Thêm Người Dùng</h2>
            <div class="employee-form">
              <div class="input-group"><label>Mã Nhân Viên</label><input type="text" id="user-id-input" readonly placeholder="Tự động điền khi chọn"/></div>
              <div class="input-group"><label>Họ và Tên*</label><input type="text" id="user-fullname-input" placeholder="Nguyễn Văn A"/></div>
              <div class="input-group"><label>Email*</label><input type="email" id="user-email-input" placeholder="NgVanA@gmail.com"/></div>
              <div class="input-group"><label>Tên đăng nhập*</label><input type="text" id="user-username-input" placeholder="nguyenvana"/></div>
              <div class="input-group"><label>Mật Khẩu*</label><input type="password" id="user-password-input" placeholder="Để trống nếu không muốn đổi"/></div>
              <div class="input-group">
                <label>Quyền Hạn*</label>
                <select id="user-role-select">
                    <option value="Customer">Khách hàng (Customer)</option>
                    <option value="Nhân viên">Nhân viên (Nhân viên)</option>
                    <option value="Admin">Quản trị (Admin)</option>
                </select>
              </div>
            </div>
            <div class="action-buttons">
              <button class="btn-primary" id="add-user-button">Thêm</button>
              <button class="btn-primary" id="edit-user-button">Chỉnh Sửa</button>
              <button class="btn-primary" id="delete-user-button">Xóa</button>
            </div>
          </section>

          <section class="management-section">
            <h2>Quản Lý Người Dùng</h2>
            <div class="table-container">
              <table>
                <thead>
                  <tr>
                    <th>Mã NV (UserID)</th>
                    <th>Họ và Tên</th>
                    <th>Email</th>
                    <th>Quyền Hạn (Role)</th>
                  </tr>
                </thead>
                <tbody id="user-table-body">
                </tbody>
              </table>
            </div>
            <div class="search-container">
                <input type="text" class="search-input" id="user-search-input" placeholder="Nhập tên hoặc email...">
                <button class="btn-primary" id="user-search-button">Tìm Kiếm</button>
            </div>
          </section>
        </div>
      </main>

      <footer class="footer">
        <div class="footer-content">
            <div class="footer-logo">Khách Sạn AA</div>
            <a href="mainmenu.php"><button class="footer-button"><div class="footer-button-text">Về Lại Trang Chủ</div></button></a>
        </div>
      </footer>
    </div>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        
        // --- LẤY CÁC ELEMENT ---
        const userTableBody = document.getElementById('user-table-body');
        const userSearchInput = document.getElementById('user-search-input');
        const userSearchButton = document.getElementById('user-search-button');
        
        // Form fields
        const userIdInput = document.getElementById('user-id-input');
        const userFullnameInput = document.getElementById('user-fullname-input');
        const userEmailInput = document.getElementById('user-email-input');
        const userUsernameInput = document.getElementById('user-username-input');
        const userPasswordInput = document.getElementById('user-password-input');
        const userRoleSelect = document.getElementById('user-role-select');
        
        // Buttons
        const addButton = document.getElementById('add-user-button');
        const editButton = document.getElementById('edit-user-button');
        const deleteButton = document.getElementById('delete-user-button');

        // --- TẢI DỮ LIỆU BAN ĐẦU ---
        fetchUsers();

        // --- CÁC HÀM XỬ LÝ ---

        // Hàm helper để vẽ bảng
        function populateUserTable(users) {
            userTableBody.innerHTML = ''; // Xóa bảng
            if (users.length > 0) {
                users.forEach(user => {
                    const row = document.createElement('tr');
                    // Lưu trữ dữ liệu vào row
                    row.dataset.userId = user.UserID;
                    row.dataset.fullName = user.FullName;
                    row.dataset.email = user.Email;
                    row.dataset.role = user.Role;
                    row.dataset.username = user.Username;
                    // Lấy Username (CSDL có nhưng API chưa lấy, ta sẽ cập nhật API)
                    // (Cập nhật: API `get-users-api.php` chưa lấy Username, nhưng các API khác cần. Tạm thời để trống)
                    
                    row.style.cursor = 'pointer';
                    row.innerHTML = `
                        <td>${user.UserID}</td>
                        <td>${escapeHtml(user.FullName)}</td>
                        <td>${escapeHtml(user.Email)}</td>
                        <td>${escapeHtml(user.Role)}</td>
                    `;
                    userTableBody.appendChild(row);
                });
            } else {
                userTableBody.innerHTML = '<tr><td colspan="4" style="text-align: center;">Không tìm thấy nhân viên.</td></tr>';
            }
        }

        // Hàm Tải/Tải lại danh sách
        function fetchUsers() {
            fetch('get-users-api.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        populateUserTable(data.users);
                    } else {
                        alert('Lỗi tải danh sách: ' + data.message);
                    }
                })
                .catch(error => alert('Lỗi kết nối khi tải danh sách.'));
        }
        
        // Hàm xóa sạch form
        function clearUserFormFields() {
            userIdInput.value = '';
            userFullnameInput.value = '';
            userEmailInput.value = '';
            userUsernameInput.value = '';
            userPasswordInput.value = ''; // Xóa mật khẩu
            userRoleSelect.value = 'Customer'; // Reset về mặc định
        }

        // --- CÁC EVENT LISTENER ---

        // 1. Click vào bảng
        userTableBody.addEventListener('click', function(event) {
            const row = event.target.closest('tr');
            if (!row || !row.dataset.userId) return;

            // Điền dữ liệu vào form
            userIdInput.value = row.dataset.userId;
            userFullnameInput.value = row.dataset.fullName;
            userEmailInput.value = row.dataset.email;
            userRoleSelect.value = row.dataset.role;
            
            // [SỬA] Lấy Username từ dataset
            userUsernameInput.value = row.dataset.username || '';
            userPasswordInput.placeholder = "Để trống nếu không muốn đổi";
        });
        
        // 2. Nút Tìm Kiếm
        userSearchButton.addEventListener('click', function() {
            const searchTerm = userSearchInput.value.trim();
            fetch(`find-user-api.php?searchTerm=${encodeURIComponent(searchTerm)}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        populateUserTable(data.users);
                    } else {
                        alert('Lỗi tìm kiếm: ' + data.message);
                    }
                })
                .catch(error => alert('Lỗi kết nối khi tìm kiếm.'));
        });

        // 3. Nút Thêm
        addButton.addEventListener('click', function() {
            if (!confirm('Bạn có chắc muốn thêm tài khoản mới này?')) return;

            const formData = new FormData();
            formData.append('username', userUsernameInput.value);
            formData.append('fullname', userFullnameInput.value);
            formData.append('email', userEmailInput.value);
            formData.append('password', userPasswordInput.value);
            formData.append('role', userRoleSelect.value);

            fetch('create-user-api.php', { method: 'POST', body: formData })
                .then(response => response.json())
                .then(data => {
                    alert(data.message);
                    if (data.success) {
                        fetchUsers();
                        clearUserFormFields();
                    }
                })
                .catch(error => alert('Lỗi kết nối khi thêm.'));
        });

        // 4. Nút Sửa
        editButton.addEventListener('click', function() {
            const userID = userIdInput.value;
            if (!userID) {
                alert('Vui lòng chọn một nhân viên từ danh sách để sửa.');
                return;
            }
            if (!confirm('Bạn có chắc muốn cập nhật tài khoản này?')) return;

            const formData = new FormData();
            formData.append('user_id', userID);
            formData.append('username', userUsernameInput.value);
            formData.append('fullname', userFullnameInput.value);
            formData.append('email', userEmailInput.value);
            formData.append('password', userPasswordInput.value); // Gửi cả mật khẩu (có thể trống)
            formData.append('role', userRoleSelect.value);
            
            fetch('update-user-api.php', { method: 'POST', body: formData })
                .then(response => response.json())
                .then(data => {
                    alert(data.message);
                    if (data.success) {
                        fetchUsers();
                        clearUserFormFields();
                    }
                })
                .catch(error => alert('Lỗi kết nối khi sửa.'));
        });

        // 5. Nút Xóa
        deleteButton.addEventListener('click', function() {
            const userID = userIdInput.value;
            if (!userID) {
                alert('Vui lòng chọn một nhân viên từ danh sách để xóa.');
                return;
            }
            if (!confirm(`BẠN CÓ CHẮC CHẮN MUỐN XÓA TÀI KHOẢN (UserID: ${userID}) NÀY KHÔNG?\nHành động này không thể hoàn tác.`)) return;

            const formData = new FormData();
            formData.append('user_id', userID);

            fetch('delete-user-api.php', { method: 'POST', body: formData })
                .then(response => response.json())
                .then(data => {
                    alert(data.message);
                    if (data.success) {
                        fetchUsers();
                        clearUserFormFields();
                    }
                })
                .catch(error => alert('Lỗi kết nối khi xóa.'));
        });
        
        // Hàm helper (vì đã có ở staff.php nên copy qua)
        function escapeHtml(unsafe) {
            if (unsafe === null || unsafe === undefined) return '';
            return unsafe.toString()
                 .replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;")
                 .replace(/"/g, "&quot;").replace(/'/g, "&#039;");
        }
    });
    </script>
  </body>
</html>