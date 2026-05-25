Markdown
# SmartMart 🛒

Hệ thống quản lý bán hàng thông minh (Dự án tốt nghiệp) được xây dựng trên nền tảng Laravel 12.

---

### Dev Dependencies (PHP)

| Thư viện | Phiên bản | Mục đích |
| :--- | :--- | :--- |
| **fakerphp/faker** | `^1.23` | Tạo dữ liệu mẫu |
| **laravel/pint** | `^1.24` | Code formatter |
| **laravel/sail** | `^1.41` | Testing framework |
| **mockery/mockery** | `^1.6` | Mocking library |
| **phpunit/phpunit** | `^11.5.50` | Unit testing |

### Frontend (Node.js)

| Thư viện | Phiên bản | Mục đích |
| :--- | :--- | :--- |
| **tailwindcss** | `^4.0.0` | CSS framework |
| **vite** | `^7.0.7` | Build tool |
| **laravel-vite-plugin** | `^2.0.0` | Vite integration cho Laravel |
| **axios** | `^1.11.0` | HTTP client |
| **concurrently** | `^9.0.1` | Chạy nhiều command cùng lúc |
| **@tailwindcss/vite**| `^4.0.0` | Tailwind Vite plugin |

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