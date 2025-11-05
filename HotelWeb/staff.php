<?php 
$currentPage = 'staff'; 
session_start(); 

if (!isset($_SESSION['user_role']) || ($_SESSION['user_role'] !== 'Admin' && $_SESSION['user_role'] !== 'Nhân viên')) {
    header('Location: mainmenu.php');
    exit;
}

?>
<!DOCTYPE html>
<html>
  <head>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta charset="utf-8" />
    <link rel="stylesheet" href="globals.css" />
    <link rel="stylesheet" href="styleguide.css" />
    <link rel="stylesheet" href="staff.css" />
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
        <h1 class="panel-title">Panel Nhân Viên</h1>
        <div class="panel">
          <section class="management-section">
            <h2>Quản Lý Phòng</h2>
            <div class="controls-grid-rooms">
              <div class="input-group full-width"><label>Số Phòng</label><input type="text" id="room-number-input"/></div>
              <div class="input-group full-width">
                <label>Loại Phòng</label>
                <select id="room-type-select">
                  <option hidden>Chọn loại phòng</option>
                  <option value="Đơn">Phòng Đơn</option>
                  <option value="Đôi">Phòng Đôi</option>
                  <option value="VIP">Phòng VIP</option>
                </select>
              </div>
              <div class="input-group">
                <label>Trạng Thái</label>
                <select id="room-status-select">
                  <option hidden>Chọn trạng thái</option>
                  <option value="Trống">Trống</option>
                  <option value="Đang thuê">Đã Đặt</option>
                  <option value="Đang dọn">Đang Dọn Dẹp</option>
                </select>
              </div>
              <div class="input-group"><label>Giá:</label><input type="text" id="room-price-input" placeholder="VND"/></div>
            </div>
            <div class="action-buttons">
                <input type="text" class="search-input" id="search-term-input" placeholder="Nhập số phòng để tìm">
                <button class="btn-primary" id="search-room-button">Tìm Kiếm</button>
                
                <div class="button-group-right">
                    <button class="btn-secondary" id="update-room-button">Cập Nhật</button>
                    
                    <?php 
                    
                    if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'Admin'): 
                    ?>
                        <button class="btn-primary" id="create-room-button">Tạo Phòng</button> 
                        <button class="btn-primary" id="delete-room-button">Xóa Phòng</button>
                    
                    <?php 
                    
                    endif; 
                    ?>
               </div>
            </div>
            <div class="table-container">
              <table>
                <thead>
                  <tr>
                    <th>Số Phòng</th>
                    <th>Loại Phòng</th>
                    <th>Trạng Thái</th>
                    <th>Giá</th>
                  </tr>
                </thead>
                <tbody id="room-table-body">
                </tbody>
              </table>
            </div>
          </section>

          <section class="management-section">
            <h2>Quản Lý Khách Hàng</h2>
            <div class="controls-grid-customers">
              <div class="input-group"><label>Họ Tên</label><input type="text" id="customer-name-input" readonly/></div>
              <div class="input-group"><label>CCCD</label><input type="text" id="customer-cccd-input" readonly/></div>
              <div class="input-group"><label>Phòng Đang Ở</label><input type="text" id="customer-room-input" readonly/></div>
              <div class="input-group"><label>Ngày Trả Phòng (Sửa)</label><input type="date" id="customer-checkout-input" /></div>
            </div>
             <div class="action-buttons customer-actions">
              <button class="btn-primary" id="customer-edit-button">Sửa (Ngày Trả)</button>
              <button class="btn-primary" id="customer-delete-button">Xóa (Trả Phòng)</button>
              <button class="btn-secondary" id="customer-print-invoice-button">In Hóa Đơn</button>
              <div class="search-group-right">
                <input type="text" placeholder="Nhập Số CCCD" id="customer-search-input">
                <button class="btn-primary" id="customer-search-button">Tìm Kiếm</button>
                <button class="btn-secondary" id="customer-history-button" title="Hiển thị tất cả (Đang ở, Đã trả)">Xem Lịch Sử</button>
                <button class="btn-secondary" id="customer-active-button" style="display: none;" title="Chỉ hiển thị khách đang ở">Xem Khách Đang Ở</button>
              </div>
            </div>
            <div class="table-container">
              <table>
                <thead>
                  <tr>
                    <th>CCCD</th>
                    <th>Họ và Tên</th>
                    <th>Phòng</th>
                    <th>Ngày Nhận</th>
                    <th>Ngày Trả</th>
                    <th>Trạng Thái</th>
                  </tr>
                </thead>
                <tbody id="customer-table-body">
                </tbody>
              </table>
            </div>
          </section>
        </div>
      </main>

      <footer class="footer">
        </footer>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            
            
            const searchTermInput = document.getElementById('search-term-input');
            const searchRoomButton = document.getElementById('search-room-button');
            const roomTableBody = document.getElementById('room-table-body');
            const roomNumberInput = document.getElementById('room-number-input'); 
            const roomTypeSelect = document.getElementById('room-type-select');
            const roomStatusSelect = document.getElementById('room-status-select');
            const roomPriceInput = document.getElementById('room-price-input');
            const createRoomButton = document.getElementById('create-room-button'); 
            const deleteRoomButton = document.getElementById('delete-room-button'); 
            const updateRoomButton = document.getElementById('update-room-button');

            
            const customerTableBody = document.getElementById('customer-table-body');
            const customerNameInput = document.getElementById('customer-name-input');
            const customerCccdInput = document.getElementById('customer-cccd-input');
            const customerRoomInput = document.getElementById('customer-room-input');
            const customerCheckoutInput = document.getElementById('customer-checkout-input');
            const customerSearchInput = document.getElementById('customer-search-input');
            const customerSearchButton = document.getElementById('customer-search-button');
            const customerEditButton = document.getElementById('customer-edit-button');
            const customerDeleteButton = document.getElementById('customer-delete-button');
            const customerPrintButton = document.getElementById('customer-print-invoice-button');
            const customerHistoryButton = document.getElementById('customer-history-button');
            const customerActiveButton = document.getElementById('customer-active-button');
            
            let selectedBookingID = null;
            let customerViewMode = 'active'; 

            
            fetchRooms(); 
            fetchCustomers(); 

            
            function fetchRooms() {
                fetch('get-rooms-api.php') 
                    .then(response => response.json()) 
                    .then(data => {
                        roomTableBody.innerHTML = ''; 
                        if (data.success && data.rooms) {
                            if (data.rooms.length > 0) {
                                data.rooms.forEach(room => {
                                    const row = document.createElement('tr');
                                    row.dataset.roomName = room.RoomName;
                                    row.dataset.roomType = room.RoomType;
                                    row.dataset.status = room.Status;
                                    row.dataset.price = room.Price;
                                    row.style.cursor = 'pointer'; 
                                    row.innerHTML = `
                                        <td>${escapeHtml(room.RoomName)}</td> 
                                        <td>${escapeHtml(room.RoomType)}</td>
                                        <td>${escapeHtml(room.Status)}</td>
                                        <td>${formatCurrency(room.Price)}</td> 
                                    `;
                                    roomTableBody.appendChild(row);
                                });
                            }
                        } else {
                            console.error('API Error (get rooms):', data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Fetch Error (get rooms):', error);
                    });
            }
            
            searchRoomButton.addEventListener('click', function() {
                const searchTerm = searchTermInput.value.trim();
                if (!searchTerm) {
                    fetchRooms(); 
                    return;
                }
                fetch(`find-room-api.php?searchTerm=${encodeURIComponent(searchTerm)}`)
                    .then(response => response.json())
                    .then(data => {
                        roomTableBody.innerHTML = ''; 
                        if (data.success && data.rooms) {
                            if (data.rooms.length > 0) {
                                data.rooms.forEach(room => {
                                    const row = document.createElement('tr');
                                    row.dataset.roomName = room.RoomName;
                                    row.dataset.roomType = room.RoomType;
                                    row.dataset.status = room.Status;
                                    row.dataset.price = room.Price;
                                    row.style.cursor = 'pointer';
                                    row.innerHTML = `
                                        <td>${escapeHtml(room.RoomName)}</td> 
                                        <td>${escapeHtml(room.RoomType)}</td>
                                        <td>${escapeHtml(room.Status)}</td>
                                        <td>${formatCurrency(room.Price)}</td> 
                                    `;
                                    roomTableBody.appendChild(row);
                                });
                            } else {
                                alert('Không tìm thấy phòng nào phù hợp.');
                            }
                        } else {
                            alert(data.message || 'Có lỗi xảy ra khi tìm kiếm.');
                        }
                    })
                    .catch(error => {
                        console.error('Fetch Error (find room):', error);
                        alert('Lỗi kết nối khi tìm kiếm phòng.');
                    });
            });
            
            
            if (createRoomButton) {
                createRoomButton.addEventListener('click', function() { 
                    const roomName = roomNumberInput.value.trim();
                    const roomType = roomTypeSelect.value;
                    const price = roomPriceInput.value.replace(/[^0-9]/g, ''); 
                    if (!roomName || !roomType || roomTypeSelect.selectedIndex === 0 || !price || parseFloat(price) <= 0) {
                        alert('Vui lòng điền Số Phòng, chọn Loại Phòng và nhập Giá hợp lệ (lớn hơn 0).');
                        return;
                    }
                    const formData = new FormData();
                    formData.append('room_name', roomName);
                    formData.append('room_type', roomType);
                    formData.append('price', price); 
                    fetch('create-room-api.php', { method: 'POST', body: formData })
                        .then(response => response.json())
                        .then(data => {
                            alert(data.message);
                            if (data.success) {
                                fetchRooms(); 
                                clearFormFields(); 
                            }
                        })
                        .catch(error => {
                            console.error('Fetch Error (create room):', error);
                            alert('Lỗi kết nối khi tạo phòng.');
                        });
                });
            }
            
            updateRoomButton.addEventListener('click', function() { 
                const roomName = roomNumberInput.value.trim(); 
                const roomType = roomTypeSelect.value;
                const status = roomStatusSelect.value;
                const price = roomPriceInput.value.replace(/[^0-9]/g, ''); 
                const formData = new FormData();
                let confirmMessage = '';
                let apiEndpoint = 'update-room-api.php'; 
                if (roomName) {
                    if (!roomType || roomTypeSelect.selectedIndex === 0 || !status || roomStatusSelect.selectedIndex === 0 || !price) {
                        alert('Vui lòng điền đầy đủ thông tin Loại phòng, Trạng thái và Giá mới để cập nhật phòng cụ thể.');
                        return;
                    }
                    confirmMessage = `Bạn có chắc chắn muốn cập nhật thông tin cho phòng ${roomName} không?`;
                    formData.append('room_name', roomName); 
                    formData.append('room_type', roomType); 
                    formData.append('status', status);     
                    formData.append('price', price);       
                } else {
                    if (!roomType || roomTypeSelect.selectedIndex === 0 || !price) {
                        alert('Vui lòng chọn Loại Phòng và nhập Giá mới để cập nhật giá theo loại.');
                        return;
                    }
                    const formattedPrice = parseFloat(price).toLocaleString('vi-VN') + ' VNĐ';
                    confirmMessage = `Bạn có chắc chắn muốn cập nhật giá cho TẤT CẢ phòng loại "${roomType}" thành ${formattedPrice} không? (Để trống ô Số Phòng)`;
                    formData.append('room_type', roomType); 
                    formData.append('price', price);       
                }
                if (!confirm(confirmMessage)) {
                    return; 
                }
                fetch(apiEndpoint, { method: 'POST', body: formData })
                .then(response => response.json())
                .then(data => {
                    alert(data.message); 
                    if (data.success) {
                        fetchRooms(); 
                    }
                })
                .catch(error => {
                    console.error('Lỗi fetch cập nhật phòng:', error);
                    alert('Lỗi kết nối khi cập nhật phòng.');
                });
            });
            
            
            if (deleteRoomButton) {
                deleteRoomButton.addEventListener('click', function() { 
                    const roomNameToDelete = roomNumberInput.value.trim();
                    if (!roomNameToDelete) {
                        alert('Vui lòng chọn hoặc nhập Số Phòng cần xóa vào ô "Số Phòng" ở trên.');
                        return;
                    }
                    if (!confirm(`Bạn có chắc chắn muốn xóa phòng ${roomNameToDelete} không?`)) {
                        return; 
                    }
                    const formData = new FormData();
                    formData.append('room_name', roomNameToDelete);
                    fetch('delete-room-api.php', { method: 'POST', body: formData })
                        .then(response => response.json())
                        .then(data => {
                            alert(data.message);
                            if (data.success) {
                                fetchRooms(); 
                                clearFormFields(); 
                            }
                        })
                        .catch(error => {
                            console.error('Fetch Error (delete room):', error);
                            alert('Lỗi kết nối khi xóa phòng.');
                        });
                });
            }
            
            roomTableBody.addEventListener('click', function(event) { 
                const clickedRow = event.target.closest('tr'); 
                if (clickedRow && clickedRow.dataset.roomName) { 
                    roomNumberInput.value = clickedRow.dataset.roomName;
                    roomTypeSelect.value = clickedRow.dataset.roomType;
                    roomStatusSelect.value = clickedRow.dataset.status;
                    const numberPrice = parseFloat(clickedRow.dataset.price);
                    roomPriceInput.value = isNaN(numberPrice) ? '' : numberPrice.toLocaleString('vi-VN'); 
                }
            });

            
            
            function populateCustomerTable(customers) {
                customerTableBody.innerHTML = ''; 
                if (customers.length > 0) {
                    customers.forEach(customer => {
                        const row = document.createElement('tr');
                        row.dataset.bookingId = customer.BookingID;
                        row.dataset.cccd = customer.CCCD;
                        row.dataset.fullName = customer.FullName;
                        row.dataset.roomName = customer.RoomName;
                        row.dataset.checkIn = customer.CheckInDate;
                        row.dataset.checkOut = customer.CheckOutDate;
                        row.dataset.status = customer.Status; 
                        row.style.cursor = 'pointer';
                        row.innerHTML = `
                            <td>${escapeHtml(customer.CCCD)}</td>
                            <td>${escapeHtml(customer.FullName)}</td>
                            <td>${escapeHtml(customer.RoomName)}</td>
                            <td>${formatDate(customer.CheckInDate)}</td>
                            <td>${formatDate(customer.CheckOutDate)}</td>
                            <td>${escapeHtml(customer.Status)}</td>
                        `;
                        customerTableBody.appendChild(row);
                    });
                } else {
                     customerTableBody.innerHTML = '<tr><td colspan="6" style="text-align: center;">Không tìm thấy khách hàng nào.</td></tr>';
                }
            }

            function fetchCustomers() {
                let url = 'get-customers-api.php';
                if (customerViewMode === 'history') {
                    url += '?history=all'; 
                }
                fetch(url) 
                    .then(response => response.json())
                    .then(data => {
                        if (data.success && data.customers) {
                            populateCustomerTable(data.customers); 
                        } else {
                            console.error('API Error (get customers):', data.message);
                            alert('Không thể tải danh sách khách hàng. ' + (data.message || ''));
                        }
                    })
                    .catch(error => {
                        console.error('Fetch Error (get customers):', error);
                        alert('Lỗi kết nối đến máy chủ khi tải danh sách khách hàng.');
                    });
            }

            customerTableBody.addEventListener('click', function(event) {
                const clickedRow = event.target.closest('tr'); 
                if (clickedRow && clickedRow.dataset.bookingId) {
                    selectedBookingID = clickedRow.dataset.bookingId; 
                    customerCccdInput.value = clickedRow.dataset.cccd || '';
                    customerNameInput.value = clickedRow.dataset.fullName || '';
                    customerRoomInput.value = clickedRow.dataset.roomName || '';
                    customerCheckoutInput.value = formatDateToInput(clickedRow.dataset.checkOut);
                    
                    const status = clickedRow.dataset.status;
                    const isCheckOutDisabled = (status !== 'Đang ở');
                    
                    customerEditButton.disabled = isCheckOutDisabled;
                    customerDeleteButton.disabled = isCheckOutDisabled;
                    customerPrintButton.disabled = false; 
                    
                    if (isCheckOutDisabled) {
                        customerEditButton.title = 'Chỉ có thể sửa đơn "Đang ở"';
                        customerDeleteButton.title = 'Đơn này đã trả phòng hoặc bị hủy';
                    } else {
                        customerEditButton.title = 'Sửa ngày trả phòng';
                        customerDeleteButton.title = 'Xóa (Trả phòng)';
                    }
                }
            });

            customerSearchButton.addEventListener('click', function() {
                const cccd = customerSearchInput.value.trim();
                if (!cccd) {
                    fetchCustomers(); 
                    return;
                }
                
                let url = `find-customer-api.php?cccd=${encodeURIComponent(cccd)}`;
                if (customerViewMode === 'history') {
                    url += '&history=all'; 
                }

                fetch(url) 
                    .then(response => response.json())
                    .then(data => {
                        if (data.success && data.customers) {
                            populateCustomerTable(data.customers); 
                        } else {
                            alert(data.message || 'Lỗi khi tìm kiếm');
                        }
                    })
                    .catch(error => {
                        console.error('Fetch Error (find customer):', error);
                        alert('Lỗi kết nối khi tìm khách hàng.');
                    });
            });

            customerHistoryButton.addEventListener('click', function() {
                customerViewMode = 'history'; 
                fetchCustomers(); 
                customerHistoryButton.style.display = 'none'; 
                customerActiveButton.style.display = 'inline-flex'; 
            });

            customerActiveButton.addEventListener('click', function() {
                customerViewMode = 'active'; 
                fetchCustomers(); 
                customerHistoryButton.style.display = 'inline-flex'; 
                customerActiveButton.style.display = 'none'; 
            });
            
            customerEditButton.addEventListener('click', function() {
                const newCheckoutDate = customerCheckoutInput.value;
                if (!selectedBookingID) {
                    alert('Vui lòng chọn một khách hàng từ danh sách trước.');
                    return;
                }
                if (!newCheckoutDate) {
                    alert('Vui lòng chọn ngày trả phòng mới.');
                    return;
                }
                if (!confirm(`Bạn có chắc muốn đổi ngày trả phòng cho đơn ${selectedBookingID} không?`)) {
                    return;
                }
                const formData = new FormData();
                formData.append('booking_id', selectedBookingID);
                formData.append('checkout_date', newCheckoutDate);
                fetch('update-booking-api.php', { method: 'POST', body: formData })
                    .then(response => response.json())
                    .then(data => {
                        alert(data.message);
                        if (data.success) {
                            fetchCustomers(); 
                            clearCustomerFormFields();
                        }
                    })
                    .catch(error => {
                        console.error('Fetch Error (update booking):', error);
                        alert('Lỗi kết nối khi cập nhật.');
                    });
            });
            
            customerDeleteButton.addEventListener('click', function() { 
                if (!selectedBookingID) {
                    alert('Vui lòng chọn một khách hàng từ danh sách để trả phòng.');
                    return;
                }
                const customerName = customerNameInput.value || 'khách';
                if (!confirm(`Bạn có chắc chắn muốn CHECK-OUT (Trả phòng) cho ${customerName} (Booking ID: ${selectedBookingID}) không?\n\nHành động này sẽ cập nhật phòng về trạng thái "Đang dọn".`)) {
                    return;
                }
                const formData = new FormData();
                formData.append('booking_id', selectedBookingID);
                fetch('checkout-api.php', { method: 'POST', body: formData })
                    .then(response => response.json())
                    .then(data => {
                        alert(data.message);
                        if (data.success) {
                            fetchCustomers(); 
                            fetchRooms(); 
                            clearCustomerFormFields();
                        }
                    })
                    .catch(error => {
                        console.error('Fetch Error (checkout):', error);
                        alert('Lỗi kết nối khi trả phòng.');
                    });
            });
            
            customerPrintButton.addEventListener('click', function() { 
                if (!selectedBookingID) {
                    alert('Vui lòng chọn một khách hàng từ danh sách để in hóa đơn.');
                    return;
                }
                window.open(`orderdetail.php?booking_id=${selectedBookingID}`, '_blank');
            });

            
            function formatCurrency(amount) {
                const numberAmount = parseFloat(amount);
                if (isNaN(numberAmount)) return '';
                return numberAmount.toLocaleString('vi-VN') + ' VNĐ'; 
            }
            function formatDate(sqlDateTime) {
                if (!sqlDateTime) return 'Chưa có'; 
                const date = new Date(sqlDateTime);
                const day = String(date.getDate()).padStart(2, '0');
                const month = String(date.getMonth() + 1).padStart(2, '0'); 
                const year = date.getFullYear();
                return `${day}/${month}/${year}`;
            }
            function formatDateToInput(sqlDateTime) {
                if (!sqlDateTime) return ''; 
                const date = new Date(sqlDateTime);
                const year = date.getFullYear();
                const month = String(date.getMonth() + 1).padStart(2, '0');
                const day = String(date.getDate()).padStart(2, '0');
                return `${year}-${month}-${day}`;
            }
            function escapeHtml(unsafe) {
                if (unsafe === null || unsafe === undefined) return '';
                return unsafe.toString()
                     .replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;")
                     .replace(/"/g, "&quot;").replace(/'/g, "&#039;");
            }
            function clearFormFields() {
                roomNumberInput.value = '';
                roomTypeSelect.selectedIndex = 0; 
                roomStatusSelect.selectedIndex = 0;
                roomPriceInput.value = '';
                searchTermInput.value = ''; 
            }
            function clearCustomerFormFields() {
                customerNameInput.value = '';
                customerCccdInput.value = '';
                customerRoomInput.value = '';
                customerCheckoutInput.value = '';
                customerSearchInput.value = '';
                selectedBookingID = null; 
                
                customerEditButton.disabled = false;
                customerDeleteButton.disabled = false;
                customerPrintButton.disabled = false;
                customerEditButton.title = 'Sửa ngày trả phòng';
                customerDeleteButton.title = 'Xóa (Trả phòng)';
            }

        }); 
    </script>
  </body>
</html>
