<?php session_start(); ?>
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
            <h1 class="form-title">Đặt Phòng</h1>

            <?php if (isset($_SESSION['user_id'])): ?>
                
                <form class="form" id="booking-form">
                    
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
        <div class="footer-content">
            <div class="footer-logo">Khách Sạn AA</div>
            <a href="admin.php"><button class="footer-button"><div class="footer-button-text">Quản Trị Hệ Thống</div></button></a>
        </div>
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

            // --- 2. LOGIC XỬ LÝ NGÀY ---
            
            // Hàm lấy ngày mai (định dạng YYYY-MM-DD)
            function getTomorrow(dateStr) {
                const date = new Date(dateStr);
                date.setDate(date.getDate() + 1);
                return date.toISOString().split('T')[0];
            }
            
            // Lấy ngày hôm nay
            const today = new Date().toISOString().split('T')[0];
            
            // Cài đặt ngày nhận phòng tối thiểu là hôm nay
            checkinDateInput.min = today;

            // Khi chọn ngày nhận phòng...
            checkinDateInput.addEventListener('change', function() {
                const checkinValue = this.value;
                if (checkinValue) {
                    // Mở khóa ô ngày trả phòng
                    checkoutDateInput.disabled = false;
                    
                    // Đặt ngày trả phòng tối thiểu là ngày mai
                    const minCheckoutDate = getTomorrow(checkinValue);
                    checkoutDateInput.min = minCheckoutDate;
                    
                    // Nếu ngày trả phòng cũ bị sai (trước ngày nhận), reset nó
                    if (checkoutDateInput.value && checkoutDateInput.value < minCheckoutDate) {
                        checkoutDateInput.value = '';
                    }
                } else {
                    // Nếu reset ngày nhận, khóa và reset ngày trả
                    checkoutDateInput.disabled = true;
                    checkoutDateInput.value = '';
                }
                // (Sau này có thể thêm: gọi lại API tìm phòng nếu ngày thay đổi)
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

                // Gọi API 1 (get-available-rooms-api.php)
                // (Lưu ý: API này chưa kiểm tra ngày, chỉ check phòng 'Trống')
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

            // --- 4. SỰ KIỆN KHI NHẤN NÚT ĐẶT PHÒNG (Đã cập nhật) ---
            bookingForm.addEventListener('submit', function(event) {
                event.preventDefault(); 

                // Lấy dữ liệu mới
                const roomID = availableRoomsSelect.value;
                const checkinDate = checkinDateInput.value;
                const checkoutDate = checkoutDateInput.value;

                // Validate dữ liệu mới
                if (!roomID || !checkinDate || !checkoutDate) {
                    alert('Vui lòng chọn đầy đủ Ngày nhận, Ngày trả, Loại phòng và Phòng trống.');
                    return;
                }
                
                // (Validation so sánh ngày đã được PHP xử lý, nhưng ta check lại cho chắc)
                if (checkoutDate <= checkinDate) {
                    alert('Ngày trả phòng phải sau ngày nhận phòng.');
                    return;
                }

                submitButton.disabled = true;
                submitButton.textContent = 'Đang xử lý...';

                // Gửi FormData mới
                const formData = new FormData();
                formData.append('room_id', roomID);
                formData.append('checkin_date', checkinDate);
                formData.append('checkout_date', checkoutDate);

                // Gọi API 2 (create-booking-api.php)
                fetch('create-booking-api.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    alert(data.message); 
                    if (data.success) {
                        window.location.href = 'mainmenu.php';
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