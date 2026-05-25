# SmartMart 🛒

Chào mừng đến với dự án **SmartMart**. Đây là hệ thống được phát triển dựa trên framework Laravel 12. Dưới đây là hướng dẫn từng bước để cài đặt và khởi chạy dự án trên môi trường local của bạn.

---

## 🛠 Yêu cầu hệ thống

Trước khi bắt đầu, hãy đảm bảo máy tính của bạn đã cài đặt sẵn các công cụ sau:
* **PHP** (Khuyến nghị phiên bản >= 8.2 phù hợp với Laravel 12)
* **Composer** (Trình quản lý thư viện của PHP)
* **Node.js & npm** (Để biên dịch các file Frontend)
* **MySQL** (Hoặc hệ quản trị cơ sở dữ liệu tương đương)

---

## 🚀 Hướng dẫn Cài đặt

### Bước 1: Khởi tạo / Tải dự án
Nếu bạn tự khởi tạo dự án từ đầu, hãy mở Terminal và chạy lệnh sau:
```bash
composer create-project laravel/laravel SmartMart "12.*"
Sau đó, di chuyển vào thư mục dự án:

Bash
cd SmartMart
(Lưu ý: Nếu bạn clone dự án này từ GitHub về, hãy chạy lệnh composer install để tải các thư viện cần thiết thay vì lệnh create-project ở trên).

Bước 2: Cấu hình Môi trường (Environment)
Copy file .env.example và đổi tên thành .env:

Bash
cp .env.example .env
Mở file .env lên và cấu hình thông tin kết nối Cơ sở dữ liệu (Database) của bạn:

Code snippet
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=tên_database_cua_ban
DB_USERNAME=root
DB_PASSWORD=mật_khẩu_database_cua_ban
Bước 3: Khởi tạo Application Key
Tạo mã khóa bảo mật cho ứng dụng Laravel của bạn:

Bash
php artisan key:generate
Bước 4: Chạy Migration (Tạo bảng cơ sở dữ liệu)
Đảm bảo bạn đã tạo sẵn database trống có tên giống với DB_DATABASE trong file .env, sau đó chạy:

Bash
php artisan migrate
Bước 5: Cài đặt và Build Frontend
Tải các thư viện giao diện và biên dịch chúng:

Bash
npm install
npm run build
Bước 6: Khởi chạy Server Local
Bật server của Laravel lên:

Bash
php artisan serve