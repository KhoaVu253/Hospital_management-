# Hệ thống Quản lý Bệnh viện

Một hệ thống quản lý bệnh viện hoàn chỉnh được xây dựng bằng PHP, MySQL với giao diện Bootstrap hiện đại.

## Tính năng chính

### 🔐 Hệ thống phân quyền
- **Admin**: Quản lý toàn bộ hệ thống
- **Bác sĩ**: Quản lý lịch khám và bệnh nhân
- **Bệnh nhân**: Đặt lịch khám và xem thông tin cá nhân

### 👥 Quản lý người dùng
- Đăng ký tài khoản bệnh nhân
- Đăng nhập/đăng xuất
- Quản lý hồ sơ cá nhân
- Phân quyền truy cập

### 👨‍⚕️ Quản lý bác sĩ
- Thêm/sửa/xóa thông tin bác sĩ
- Quản lý chuyên khoa
- Theo dõi lịch làm việc
- Xem lịch khám

### 🏥 Quản lý bệnh nhân
- Lưu trữ thông tin bệnh nhân
- Tìm kiếm và lọc bệnh nhân
- Xem lịch sử khám bệnh
- Quản lý hồ sơ y tế

### 📅 Đặt lịch khám
- Đặt lịch khám trực tuyến
- Chọn bác sĩ và thời gian
- Kiểm tra lịch trùng
- Xác nhận lịch khám

### 💰 Quản lý hóa đơn
- Tạo hóa đơn tự động
- Tính toán chi phí khám và thuốc
- Theo dõi trạng thái thanh toán
- Xuất hóa đơn

### 💊 Quản lý thuốc
- Danh sách thuốc
- Quản lý kho thuốc
- Kê đơn thuốc
- Theo dõi tồn kho

## Cài đặt

### Yêu cầu hệ thống
- PHP 7.4 trở lên
- MySQL 5.7 trở lên
- Apache/Nginx web server
- XAMPP (khuyến nghị)

### Bước 1: Cài đặt XAMPP
1. Tải và cài đặt XAMPP từ [https://www.apachefriends.org/](https://www.apachefriends.org/)
2. Khởi động Apache và MySQL

### Bước 2: Tạo cơ sở dữ liệu
1. Mở phpMyAdmin: `http://localhost/phpmyadmin`
2. Tạo database mới tên `hospital_management`
3. Import file `database.sql` để tạo các bảng và dữ liệu mẫu

### Bước 3: Cấu hình
1. Copy toàn bộ code vào thư mục `htdocs` của XAMPP
2. Chỉnh sửa file `config/db.php` nếu cần:
   ```php
   $host = "localhost";
   $username = "root";
   $password = "";
   $database = "hospital_management";
   ```

### Bước 4: Truy cập
- Mở trình duyệt và truy cập: `http://localhost/hospital_management 1/`

## Tài khoản mặc định

### Admin
- **Username**: admin
- **Password**: password
- **Email**: admin@hospital.com

### Bác sĩ mẫu
- **Username**: doctor1
- **Password**: password
- **Chuyên khoa**: Tim mạch

- **Username**: doctor2  
- **Password**: password
- **Chuyên khoa**: Nhi khoa

### Bệnh nhân mẫu
- **Username**: patient1
- **Password**: password
- **Email**: patient1@email.com

- **Username**: patient2
- **Password**: password
- **Email**: patient2@email.com

## Cấu trúc thư mục

```
hospital_management 1/
├── config/
│   └── db.php                 # Kết nối cơ sở dữ liệu
├── includes/
│   ├── functions.php          # Các hàm tiện ích
│   ├── header.php             # Header chung
│   └── footer.php             # Footer chung
├── auth/
│   ├── login.php              # Đăng nhập
│   ├── register.php           # Đăng ký
│   └── logout.php             # Đăng xuất
├── admin/
│   ├── dashboard.php          # Dashboard admin
│   ├── manage_patients.php    # Quản lý bệnh nhân
│   ├── manage_doctors.php     # Quản lý bác sĩ
│   ├── manage_appointments.php # Quản lý lịch khám
│   ├── manage_bills.php       # Quản lý hóa đơn
│   └── manage_medicines.php   # Quản lý thuốc
├── patient/
│   ├── dashboard.php          # Dashboard bệnh nhân
│   ├── book_appointment.php   # Đặt lịch khám
│   ├── my_appointments.php    # Lịch khám của tôi
│   ├── medical_history.php    # Lịch sử khám bệnh
│   └── my_bills.php           # Hóa đơn của tôi
├── doctor/
│   ├── dashboard.php          # Dashboard bác sĩ
│   ├── appointments.php       # Lịch khám
│   └── patients.php           # Danh sách bệnh nhân
├── database.sql               # Cơ sở dữ liệu
├── index.php                  # Trang chủ
└── README.md                  # Hướng dẫn sử dụng
```

## Tính năng chi tiết

### Dashboard Admin
- Thống kê tổng quan
- Biểu đồ lịch khám theo tháng
- Phân bố chuyên khoa
- Lịch khám gần đây

### Quản lý bệnh nhân
- Tìm kiếm và lọc bệnh nhân
- Thêm/sửa/xóa thông tin
- Xem lịch sử khám bệnh
- Quản lý hồ sơ y tế

### Quản lý bác sĩ
- Thêm bác sĩ mới
- Quản lý chuyên khoa
- Theo dõi lịch làm việc
- Xem lịch khám

### Đặt lịch khám
- Chọn bác sĩ và chuyên khoa
- Chọn ngày và giờ khám
- Kiểm tra lịch trùng
- Mô tả triệu chứng

### Quản lý hóa đơn
- Tạo hóa đơn tự động
- Tính toán chi phí
- Theo dõi thanh toán
- Xuất hóa đơn PDF

## Bảo mật

- Mật khẩu được mã hóa bằng `password_hash()`
- Sử dụng Prepared Statements để tránh SQL Injection
- Kiểm tra quyền truy cập cho từng trang
- Lọc dữ liệu đầu vào
- Sử dụng CSRF token

## Giao diện

- Sử dụng Bootstrap 5
- Responsive design
- Giao diện hiện đại và thân thiện
- Icons Font Awesome
- Biểu đồ Chart.js

## Hỗ trợ

Nếu gặp vấn đề, vui lòng:
1. Kiểm tra cấu hình XAMPP
2. Đảm bảo database đã được tạo đúng
3. Kiểm tra quyền truy cập file
4. Xem log lỗi trong Apache

## Tác giả

Hệ thống được phát triển với mục đích học tập và nghiên cứu.

## License

MIT License - Xem file LICENSE để biết thêm chi tiết. 