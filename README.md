
# SmartMart 🛒

Hệ thống quản lý bán hàng thông minh được xây dựng trên nền tảng Laravel 12.

---



---

## Các Bước Cài Đặt

### 1. Khởi tạo / Clone Repository

Nếu bạn tạo dự án mới hoàn toàn từ đầu:
```bash
composer create-project laravel/laravel SmartMart "12.*"
cd SmartMart
(Hoặc nếu bạn clone code từ repo GitHub về):

Bash
git clone [https://github.com/Takahashi-atkaaa/du_an_tot_nghiep.git](https://github.com/Takahashi-atkaaa/du_an_tot_nghiep.git)
cd du_an_tot_nghiep
2. Cài Đặt Dependencies
Cài đặt các gói thư viện cần thiết cho Backend và Frontend:

Bash
composer install
npm install
3. Cấu Hình Môi Trường
Tạo file biến môi trường và thiết lập kết nối cơ sở dữ liệu:

Bash
cp .env.example .env
Mở file .env và điền thông tin Database của bạn (ví dụ: DB_DATABASE=smartmart). Sau đó tạo khóa bảo mật:

Bash
php artisan key:generate
4. Migrate Cơ Sở Dữ Liệu
Chạy lệnh để tạo các bảng trong Database:

Bash
php artisan migrate

Cuối cùng, bật server ảo lên:

Bash
php artisan serve