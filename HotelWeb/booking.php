<?php 
session_start(); 
?>
<!DOCTYPE html>
<html>
  <head>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta charset="utf-8" />
    <link rel="stylesheet" href="globals.css" />
    <link rel="stylesheet" href="styleguide.css" />
    <link rel="stylesheet" href="booking.css" />
  </head>
  <body>
    <div class="page-container">
      
      <header class="navigation">
        <div class="nav-content-wrapper">
          <a href="mainmenu.php" class="nav-logo <?php if ($currentPage === 'mainmenu') echo 'active'; ?>">AA Hotel</a>
          <div class="nav-items">
    <a href="reserveroom.php" class="nav-link <?php if ($currentPage === 'reserveroom') echo 'active'; ?>">Đặt Phòng</a>
    <?php if (isset($_SESSION['user_id'])): 
        $role = $_SESSION['user_role'];
    ?>
        <span class="nav-link-welcome">Xin chào, <?php echo htmlspecialchars($_SESSION['user_name']); ?>!</span>
        <?php if ($role === 'Admin' || $role === 'Nhân viên'): ?>
            <a href="staff.php" class="nav-link <?php if ($currentPage === 'staff') echo 'active'; ?>">Panel Nhân Viên</a>
        <?php endif; ?>
        <?php if ($role === 'Admin'): ?>
            <a href="admin.php" class="nav-link <?php if ($currentPage === 'admin') echo 'active'; ?>">Panel Quản Trị</a>
        <?php endif; ?>
        <?php if ($role === 'Customer'): ?>
            <a href="changepass.php" class="nav-link <?php if ($currentPage === 'changepass') echo 'active'; ?>">Đổi Mật Khẩu</a>
        <?php endif; ?>
        <a href="logout.php" class="nav-button"><div class="nav-button-text">Đăng Xuất</div></a>
    <?php else: ?>
        <a href="register.php" class="nav-link <?php if ($currentPage === 'register') echo 'active'; ?>">Đăng Ký</a>
        <a href="login.php" class="nav-button"><div class="nav-button-text">Đăng Nhập</div></a>
    <?php endif; ?>
</div>
        </div>
      </header>
      
      <main class="main-content">
        <div class="form-container">
            <h1 class="form-title">Đặt Phòng</h1>

            <?php if (isset($_SESSION['user_id'])): ?>
                
                <form class="form" id="booking-form">
                    
                    <?php 
                    $role = $_SESSION['user_role'];
                    if ($role === 'Admin' || $role === 'Nhân viên'): 
                    ?>
                        <div class="walkin-guest-section" style="background-color: #f0f0f0; padding: 20px; border-radius: 8px;">
                            <h3 style="margin-top: 0; margin-bottom: 16px; font-family: 'Inter-SemiBold', Helvetica;">Check-in trực tiếp</h3>
                            <div class="input-group">
                                <label for="walkin-fullname">Họ Tên Khách Hàng*</label>
                                <input type="text" class="field" id="walkin-fullname" name="walkin_fullname">
                            </div>
                            <div class="input-group">
                                <label for="walkin-cccd">CCCD Khách Hàng*</label>
                                <input type="text" class="field" id="walkin-cccd" name="walkin_cccd">
                            </div>
                            <div class="input-group">
                                <label for="walkin-phone">SĐT Khách Hàng</label>
                                <input type="tel" class="field" id="walkin-phone" name="walkin_phone">
                            </div>
                        </div>
                    <?php endif; ?>
                    <div class="input-group">
                        <label for="checkin-date">Ngày Nhận Phòng</label>
                        <input type="date" class="field" id="checkin-date">
                    </div>
                    <div class="input-group">
                        <label for="checkout-date">Ngày Trả Phòng</label>
                        <input type="date" class="field" id="checkout-date" disabled>
                    </div>
                    <div class="input-group">
                        <label for="room-type">Loại Phòng</label>
                        <select id="room-type" name="room_type" class="field">
                            <option value="">-- Chọn loại phòng --</option>
                            <option value="Đơn">Phòng Đơn</option>
                            <option value="Đôi">Phòng Đôi</option>
                            <option value="VIP">Phòng VIP</option>
                        </select>
                    </div>
                    <div class="input-group">
                        <label for="available-rooms">Danh Sách Phòng Trống</label>
                        <select id="available-rooms" name="available_rooms" class="field" disabled>
                            <option value="">-- Vui lòng chọn loại phòng trước --</option>
                        </select>
                    </div>
                    <button class="submit-button" type="submit" id="submit-booking-button">Đặt Phòng</button>
                </form>

            <?php else: ?>
                <div class="login-prompt">
                    <p>Vui lòng <a href="login.php">đăng nhập</a> hoặc <a href="register.php">đăng ký</a> để tiếp tục đặt phòng.</p>
                </div>
            <?php endif; ?>
        </div>
      </main>

      <footer class="footer">
        </footer>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            
            const bookingForm = document.getElementById('booking-form');
            if (!bookingForm) return; 

            // --- 1. LẤY CÁC ELEMENT ---
            const checkinDateInput = document.getElementById('checkin-date');
            const checkoutDateInput = document.getElementById('checkout-date');
            const roomTypeSelect = document.getElementById('room-type');
            const availableRoomsSelect = document.getElementById('available-rooms');
            const submitButton = document.getElementById('submit-booking-button');

            // [MỚI] Lấy các trường walk-in (nếu chúng tồn tại)
            const walkinFullnameInput = document.getElementById('walkin-fullname');
            const walkinCccdInput = document.getElementById('walkin-cccd');
            const walkinPhoneInput = document.getElementById('walkin-phone');

            // --- 2. LOGIC XỬ LÝ NGÀY (Giữ nguyên) ---
            function getTomorrow(dateStr) {
                const date = new Date(dateStr);
                date.setDate(date.getDate() + 1);
                return date.toISOString().split('T')[0];
            }
            const today = new Date().toISOString().split('T')[0];
            checkinDateInput.min = today;
            checkinDateInput.addEventListener('change', function() {
                const checkinValue = this.value;
                if (checkinValue) {
                    checkoutDateInput.disabled = false;
                    const minCheckoutDate = getTomorrow(checkinValue);
                    checkoutDateInput.min = minCheckoutDate;
                    if (checkoutDateInput.value && checkoutDateInput.value < minCheckoutDate) {
                        checkoutDateInput.value = '';
                    }
                } else {
                    checkoutDateInput.disabled = true;
                    checkoutDateInput.value = '';
                }
            });

            // --- 3. LOGIC XỬ LÝ PHÒNG (Giữ nguyên) ---
            roomTypeSelect.addEventListener('change', function() {
                const selectedType = this.value;
                availableRoomsSelect.innerHTML = '<option value="">-- Đang tải... --</option>'; 
                availableRoomsSelect.disabled = true;
                if (!selectedType) {
                    availableRoomsSelect.innerHTML = '<option value="">-- Vui lòng chọn loại phòng trước --</option>';
                    return;
                }
                fetch(`get-available-rooms-api.php?room_type=${encodeURIComponent(selectedType)}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success && data.rooms.length > 0) {
                            availableRoomsSelect.innerHTML = '<option value="">-- Chọn phòng --</option>';
                            data.rooms.forEach(room => {
                                const option = document.createElement('option');
                                option.value = room.RoomID; 
                                option.textContent = room.RoomName; 
                                availableRoomsSelect.appendChild(option);
                            });
                            availableRoomsSelect.disabled = false;
                        } else if (data.success) {
                            availableRoomsSelect.innerHTML = '<option value="">-- Hết phòng loại này --</option>';
                        } else {
                            availableRoomsSelect.innerHTML = '<option value="">-- Lỗi tải phòng --</option>';
                            alert(data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Fetch Error:', error);
                        availableRoomsSelect.innerHTML = '<option value="">-- Lỗi kết nối --</option>';
                    });
            });

            // --- 4. SỰ KIỆN KHI NHẤN NÚT ĐẶT PHÒNG (Cập nhật) ---
            bookingForm.addEventListener('submit', function(event) {
                event.preventDefault(); 

                // Lấy dữ liệu đặt phòng
                const roomID = availableRoomsSelect.value;
                const checkinDate = checkinDateInput.value;
                const checkoutDate = checkoutDateInput.value;
                
                // [MỚI] Lấy dữ liệu walk-in (nếu có)
                const walkinFullname = walkinFullnameInput ? walkinFullnameInput.value.trim() : null;
                const walkinCccd = walkinCccdInput ? walkinCccdInput.value.trim() : null;
                const walkinPhone = walkinPhoneInput ? walkinPhoneInput.value.trim() : null;

                // Validate dữ liệu
                if (!roomID || !checkinDate || !checkoutDate) {
                    alert('Vui lòng chọn đầy đủ Ngày nhận, Ngày trả, Loại phòng và Phòng trống.');
                    return;
                }
                if (checkoutDate <= checkinDate) {
                    alert('Ngày trả phòng phải sau ngày nhận phòng.');
                    return;
                }
                
                // [MỚI] Validate trường walk-in NẾU chúng tồn tại
                if (walkinFullnameInput && (!walkinFullname || !walkinCccd)) {
                     alert('Nhân viên: Vui lòng nhập Họ Tên và CCCD của khách vãng lai.');
                     return;
                }

                submitButton.disabled = true;
                submitButton.textContent = 'Đang xử lý...';

                // Gửi FormData mới
                const formData = new FormData();
                formData.append('room_id', roomID);
                formData.append('checkin_date', checkinDate);
                formData.append('checkout_date', checkoutDate);

                // [MỚI] Thêm dữ liệu walk-in NẾU có
                if (walkinFullname) {
                    formData.append('walkin_fullname', walkinFullname);
                    formData.append('walkin_cccd', walkinCccd);
                    formData.append('walkin_phone', walkinPhone);
                }

                // Gọi API 2 (create-booking-api.php)
                fetch('create-booking-api.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    alert(data.message); 
                    if (data.success) {
                        // Nếu thành công, chuyển về panel nhân viên (nếu là nhân viên)
                        // hoặc trang chủ (nếu là khách)
                        <?php 
                        $role = $_SESSION['user_role'] ?? 'Customer';
                        if ($role === 'Admin' || $role === 'Nhân viên') {
                            echo "window.location.href = 'staff.php';";
                        } else {
                            echo "window.location.href = 'mainmenu.php';";
                        }
                        ?>
                    } else {
                        submitButton.disabled = false;
                        submitButton.textContent = 'Đặt Phòng';
                        if (data.message.includes('vừa được đặt')) {
                            roomTypeSelect.dispatchEvent(new Event('change'));
                        }
                    }
                })
                .catch(error => {
                    console.error('Fetch Error:', error);
                    alert('Lỗi kết nối nghiêm trọng. Vui lòng thử lại.');
                    submitButton.disabled = false;
                    submitButton.textContent = 'Đặt Phòng';
                });
            });

        });
    </script>

  </body>
</html>