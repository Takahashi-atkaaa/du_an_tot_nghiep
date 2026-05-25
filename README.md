## Các Bước Cài Đặt

### 1. Clone Repository

```bash
git clone https://github.com/Takahashi-atkaaa/du_an_tot_nghiep
cd Smart_Mart
```

### 2. Cài Đặt Dependencies

```bash
# Cài đặt PHP dependencies
composer install

# Cài đặt NPM dependencies
npm install
```

### 3. Tạo Database

Đăng nhập vào MySQL ( qua phpMyAdmin hoặc command line):

```sql
CREATE DATABASE Smart_Mart CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### 4. Cấu Hình Môi Trường

```bash
# Copy file .env.example thành .env
cp .env.example .env

# Hoặc tạo file .env thủ công với nội dung:
```

Nội dung file `.env`:

```env
APP_NAME="Smart_Mart"
APP_ENV=local
APP_KEY= (them Application Key vao day )
APP_DEBUG=true
APP_URL=http://localhost

LOG_CHANNEL=stack
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=debug

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=Smart_Mart
DB_USERNAME=root
DB_PASSWORD=

BROADCAST_DRIVER=log
CACHE_DRIVER=file
FILESYSTEM_DISK=local
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
SESSION_LIFETIME=120
```

### 5. Tạo Application Key

```bash
php artisan key:generate
```

### 6. Chạy Migration & Seeder (tùy chọn)

```bash
# Chạy migration
php artisan migrate

# Seed dữ liệu mẫu (nếu có)
php artisan db:seed
```
