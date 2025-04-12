# 🎬 Dự Án Đặt Lịch Xem Phim (PHP)

[![Status](https://img.shields.io/badge/Status-Đang%20Phát%20Triển-brightgreen)](https://github.com/BanhCute/DatLichXemPhim_PHP)
[![PHP](https://img.shields.io/badge/Backend-PHP-blue)](https://www.php.net/)
[![MySQL](https://img.shields.io/badge/Database-MySQL-purple)](https://www.mysql.com/)

**Dự Án Đặt Lịch Xem Phim (PHP)** là một ứng dụng web cho phép người dùng tìm kiếm, xem thông tin phim, đặt lịch xem phim và quản lý vé. Ứng dụng được xây dựng bằng **PHP** theo mô hình MVC, sử dụng **MySQL** làm cơ sở dữ liệu, và có thể tích hợp gửi email để xác nhận đặt vé. Đây là một dự án cá nhân được phát triển bởi [BanhCute](https://github.com/BanhCute).

---

## 📋 Tổng Quan Dự Án

Ứng dụng cung cấp các tính năng chính:
- **Tìm kiếm phim**: Tìm kiếm phim theo tên hoặc thể loại.
- **Xem chi tiết phim**: Hiển thị thông tin chi tiết (mô tả, thời lượng, thể loại, v.v.).
- **Đặt lịch xem phim**: Chọn suất chiếu và đặt vé.
- **Quản lý vé**: Quản lý thông tin vé đã đặt (yêu cầu đăng nhập).
- **Quản lý phim và suất chiếu**: Admin có thể thêm, sửa, xóa phim và lịch chiếu.
- **Gửi email xác nhận** (tùy chọn): Gửi email xác nhận đặt vé (nếu tích hợp thư viện mail).

### Cấu trúc dự án
- **`config/`**: Chứa các tệp cấu hình (database, mail, v.v.).
- **`controllers/`**: Chứa các controller xử lý logic (theo mô hình MVC).
- **`database/`**: Chứa các script SQL hoặc tệp liên quan đến database.
- **`helpers/`**: Chứa các hàm hỗ trợ.
- **`logs/`**: Lưu trữ log (nếu có).
- **`models/`**: Chứa các model để tương tác với cơ sở dữ liệu (theo mô hình MVC).
- **`public/`**: Thư mục gốc chứa các tệp tĩnh (CSS, JS, hình ảnh, v.v.).
- **`vendor/`**: Chứa các thư viện bên thứ ba được cài đặt qua Composer.
- **`views/`**: Chứa các tệp giao diện (theo mô hình MVC).
- **`.htaccess`**: Cấu hình URL rewriting (dùng với Apache).
- **`composer.json`**: Quản lý dependencies của PHP.

---

## 🛠️ Công Nghệ Sử Dụng

| **Phần**                  | **Công Nghệ**                     |
|---------------------------|-----------------------------------|
| **Backend**               | PHP (MVC)                         |
| **Database**              | MySQL                             |
| **Quản lý Dependencies**  | Composer                          |
| **Email (tùy chọn)**      | PHPMailer                         |
| **Môi trường phát triển** | Laragon (Apache/MySQL)            |

---

## 📦 Yêu Cầu Hệ Thống

Trước khi bắt đầu, hãy đảm bảo bạn đã cài đặt:
- **Laragon** (hoặc một môi trường PHP khác như XAMPP, WAMP).
- **PHP** (phiên bản 7.4 hoặc cao hơn).
- **Composer** (để quản lý dependencies).
- **MySQL** (đi kèm với Laragon).
- Trình duyệt web (Chrome, Firefox, v.v.).

---

## 🚀 Hướng Dẫn Cài Đặt Với Laragon

Dưới đây là các bước chi tiết để thiết lập dự án trên Laragon:

### 1. Cài Đặt Laragon
- Tải và cài đặt Laragon từ: [https://laragon.org/download/](https://laragon.org/download/).
- Khởi động Laragon và nhấn **Start All** để chạy Apache/MySQL.

### 2. Clone Dự Án
1. Mở terminal trong Laragon (nhấn nút **Terminal**).
2. Di chuyển đến thư mục `www`:
   ```bash
   cd www
   ```
3. Clone dự án từ GitHub:
   ```bash
   git clone https://github.com/BanhCute/DatLichXemPhim_PHP.git
   cd DatLichXemPhim_PHP
   ```

### 3. Cài Đặt Dependencies
Dự án sử dụng Composer để quản lý thư viện:
1. Chạy lệnh sau để cài đặt các thư viện:
   ```bash
   composer install
   ```
2. (Tùy chọn) Nếu bạn muốn tích hợp gửi email, cài PHPMailer:
   ```bash
   composer require phpmailer/phpmailer
   ```

### 4. Cấu Hình Cơ Sở Dữ Liệu
1. **Tạo Cơ Sở Dữ Liệu**:
   - Mở **HeidiSQL** hoặc **phpMyAdmin** trong Laragon (nhấn nút **Database**).
   - Tạo cơ sở dữ liệu mới, ví dụ: `datlichxemphim`.
   - Nếu có tệp SQL trong thư mục `database/`, import nó để tạo bảng.
2. **Cấu Hình Kết Nối Database**:
   - Mở tệp cấu hình trong `config/` (thường là `config/database.php`).
   - Cập nhật thông tin kết nối:
     ```php
     $db_host = 'localhost';
     $db_name = 'datlichxemphim';
     $db_user = 'root';
     $db_pass = '';
     ```
   - Nếu dự án dùng `.env`, tạo tệp `.env` trong thư mục gốc:
     ```env
     DB_HOST=localhost
     DB_NAME=datlichxemphim
     DB_USER=root
     DB_PASS=
     ```

### 5. Cấu Hình Gửi Email (Tùy Chọn)
Nếu dự án cần gửi email (ví dụ: xác nhận đặt vé):
1. Đảm bảo đã cài PHPMailer:
   ```bash
   composer require phpmailer/phpmailer
   ```
2. Cấu hình SMTP trong `config/mail.php` hoặc `.env`:
   ```env
   MAIL_HOST=smtp.gmail.com
   MAIL_PORT=587
   MAIL_USERNAME=your-email@gmail.com
   MAIL_PASSWORD=your-app-password
   MAIL_FROM=your-email@gmail.com
   ```
   - **Lưu ý**: Nếu dùng Gmail, bạn cần tạo **App Password** trong tài khoản Google.

### 6. Chạy Dự Án
1. Đảm bảo Laragon đang chạy (nhấn **Start All**).
2. Truy cập dự án qua URL:
   ```
   http://datlichxemphim_php.test
   ```
   - Nếu không truy cập được, kiểm tra tệp `.htaccess` và đảm bảo `mod_rewrite` đã được bật trong Apache (xem phần khắc phục sự cố).

---

## 🛠️ Các Lệnh Thường Dùng

| **Lệnh**                | **Mô Tả**                                    |
|-------------------------|----------------------------------------------|
| `composer install`      | Cài đặt dependencies của PHP.               |
| `composer require phpmailer/phpmailer` | Cài PHPMailer để gửi email.          |

---

## ⚠️ Lưu Ý Khi Clone Dự Án
1. **Thiếu Dependencies**: Nếu `composer install` không cài hết thư viện, kiểm tra `composer.json` và chạy lại.
2. **Tệp `.env`**: Tệp này không được đẩy lên Git (do `.gitignore`). Bạn phải tạo lại `.env` với các biến môi trường cần thiết.
3. **Kết Nối Cơ Sở Dữ Liệu**: Đảm bảo thông tin database trong `config/` hoặc `.env` chính xác.
4. **Phiên Bản PHP**: Sử dụng PHP 7.4 hoặc cao hơn để tránh lỗi tương thích.

---

## ❓ Khắc Phục Sự Cố
- **Lỗi `composer install`**:
  - Cập nhật Composer: `composer self-update`.
  - Xóa thư mục `vendor/` và tệp `composer.lock`, sau đó chạy lại `composer install`.
- **Lỗi Kết Nối Database**:
  - Kiểm tra thông tin kết nối trong `config/` hoặc `.env`.
  - Đảm bảo MySQL đang chạy trong Laragon.
- **Lỗi Truy Cập URL**:
  - Đảm bảo Laragon đang chạy.
  - Kiểm tra `mod_rewrite` trong Apache:
    - Mở `C:\laragon\etc\apache2\httpd.conf`.
    - Tìm `LoadModule rewrite_module modules/mod_rewrite.so` và bỏ comment (xóa `#`).
    - Khởi động lại Laragon.
- **Lỗi Gửi Email**:
  - Kiểm tra thông tin SMTP trong `config/` hoặc `.env`.
  - Đảm bảo đã cài PHPMailer và sử dụng đúng thông tin SMTP.

---

## 📢 Góp Ý
Nếu bạn gặp vấn đề hoặc muốn bổ sung tính năng, hãy tạo issue trên repository hoặc liên hệ tác giả.

---

## 👤 Tác Giả
- [BanhCute](https://github.com/BanhCute)

---

**Dự Án Đặt Lịch Xem Phim (PHP)** là một dự án cá nhân được phát triển với mục tiêu học tập và thực hành công nghệ web. Cảm ơn bạn đã quan tâm! 🎥
