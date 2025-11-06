📖 1. Giới thiệu
Hệ thống Quản lý học tập cá nhân được xây dựng nhằm hỗ trợ công tác quản lý, giám sát và đánh giá hoạt động của sinh viên hoặc học sinh. Hệ thống giúp
các bạn chủ động trong việc sắp xếp thời gian để không bỏ qua kiến thức.

🔧 2. Các công nghệ được sử dụng
Hệ điều hành
<img width="93" height="28" alt="image" src="https://github.com/user-attachments/assets/b2485204-99c1-496e-b323-28d2c0090848" />

Công nghệ chính
<img width="71" height="28" alt="image" src="https://github.com/user-attachments/assets/1b295979-610f-4868-90e3-7034e8076e3c" />
<img width="88" height="28" alt="image" src="https://github.com/user-attachments/assets/90f26b5c-3f68-417a-b88a-75451568b0eb" />
<img width="49" height="28" alt="image" src="https://github.com/user-attachments/assets/b91d9813-5d22-470c-877c-b3120aab634e" />
<img width="123" height="28" alt="image" src="https://github.com/user-attachments/assets/5afa5cb3-d384-4f80-9d66-257c09ceb38a" />

Máy chủ web và cơ sở dữ liệu
<img width="97" height="28" alt="image" src="https://github.com/user-attachments/assets/de87f281-b70f-442b-8651-b5b478771a3f" />
<img width="88" height="28" alt="image" src="https://github.com/user-attachments/assets/afe0ea66-3561-49a0-a733-4a7ee3de4fdd" />
<img width="89" height="28" alt="image" src="https://github.com/user-attachments/assets/c9276a9e-2e5a-4ffc-a948-827e41b12c1d" />

Công cụ quản lý cơ sở dữ liệu
<img width="179" height="28" alt="image" src="https://github.com/user-attachments/assets/b8f9a20d-65dc-4e73-91c8-5d4177ceedcb" />

🚀 3. Hình ảnh các chức năng
Trang đăng nhập
<img width="1919" height="983" alt="image" src="https://github.com/user-attachments/assets/6da124b9-90b7-4358-a713-47ff8259acda" />

Trang quản trị viên
<img width="1897" height="977" alt="image" src="https://github.com/user-attachments/assets/e3523d4d-ea78-4b3f-8512-2705fe9d8911" />

Trang lịch học
<img width="1906" height="503" alt="image" src="https://github.com/user-attachments/assets/f39469d9-74f3-43d1-bcb6-34f92b3bd548" />

Trang ghi chú 
<img width="1916" height="428" alt="image" src="https://github.com/user-attachments/assets/75ab34b1-1f2f-4f9b-add2-6ac0dfdf70e9" />

Trang mục tiêu
<img width="1916" height="192" alt="image" src="https://github.com/user-attachments/assets/f645c128-61fd-44a3-bb2a-1e4669700b03" />

## ⚙️ 4. Cài đặt
4.1. Cài đặt công cụ, môi trường và các thư viện cần thiết
Tải và cài đặt XAMPP
👉 https://www.apachefriends.org/download.html
(Khuyến nghị bản XAMPP với PHP 8.x)

Cài đặt Visual Studio Code và các tiện ích mở rộng:

PHP Intelephense
MySQL

4.2. Tải dự án
Clone project về thư mục htdocscủa XAMPP (ví dụ ổ C):
cd C:\xampp\htdocs
Truy cập project qua đường dẫn:
👉 ((https://github.com/Hung17082005/BTL_Quan_ly_hoc_tap)

4.3. Thiết lập cơ sở dữ liệu
Mở Control Panel XAMPP, Khởi động Apache và MySQL

Truy cập cơ sở dữ liệu MySQL WorkBench Create:

CREATE DATABASE IF NOT EXISTS quan_ly_doan_vien
   CHARACTER SET utf8mb4
   COLLATE utf8mb4_unicode_ci;

4.4. Setup kết nối tham số
Mở file db.php trong dự án, chỉnh sửa DB thông tin:

<?php
    function getDbConnection() {
        $servername = "localhost";
        $username = "root";
        $password = "100725";
        $dbname = "btl";
        $port = 3306;
        $conn = mysqli_connect($servername, $username, $password, $dbname, $port);
        if (!$conn) {
            die("Kết nối database thất bại: " . mysqli_connect_error());
        }
        mysqli_set_charset($conn, "utf8");
        return $conn;
    }
?>
4.5. Chạy hệ thống
Mở Control Panel XAMPP → Khởi động Apache và MySQL

Truy cập hệ thống: 👉(http://localhost/btl/index.php?page=dashboard)

4.6. Đăng nhập lần đầu
Hệ thống có thể cung cấp tài khoản quản trị viên

Sau khi đăng nhập Quản trị viên có thể:

Tạo lịch học, sửa xóa ghi chú mà mục tiêu

Thêm thành viên và cấp tài khoản
