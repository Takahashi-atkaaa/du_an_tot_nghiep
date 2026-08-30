# Báo cáo Kiểm kho

> Module: Kiểm kho - SmartMart Grocery
> Ngày tạo: 30/08/2026

## 1. Tổng quan

Module báo cáo kiểm kho cung cấp các thống kê và phân tích về:
- Tình hình kiểm kho theo thời gian
- Chênh lệch tồn kho (thiếu/thừa)
- Giá trị hàng hóa bị chênh lệch
- Hiệu suất kiểm đếm của nhân viên

---

## 2. Các loại báo cáo

### 2.1. Báo cáo Tổng hợp

**Route:** `/admin/kho-hang/kiem-kho/bao-cao`

**Quyền:** `kiem_kho_xem`

**Tham số lọc:**
- `tu_ngay`: Từ ngày (date)
- `den_ngay`: Đến ngày (date)
- `id_nguoi_kiem`: Người kiểm (nullable)
- `trang_thai`: Trạng thái phiếu (nullable)

**API Endpoint:** `GET /admin/api/kiem-kho/bao-cao`

**Response:**
```json
{
  "tong_phieu": 10,
  "tong_san_pham_kiem": 250,
  "tong_sp_thieu": 15,
  "tong_sp_thua": 8,
  "tong_sp_dung": 227,
  "tong_sl_thieu": 45,
  "tong_sl_thua": 30,
  "tong_gia_tri_lech": -150000,
  "danh_sach_phieu": [...]
}
```

**Chỉ số hiển thị:**

| Chỉ số | Công thức | Ý nghĩa |
|--------|-----------|---------|
| Tổng phiếu | COUNT(phieu WHERE trang_thai='hoan_thanh') | Số phiếu đã hoàn tất |
| Tổng SP kiểm | SUM(tong_so_san_pham) | Tổng biến thể được kiểm |
| SP thiếu | SUM(so_sp_thieu) | Số SP có tồn thực tế < hệ thống |
| SP thừa | SUM(so_sp_thua) | Số SP có tồn thực tế > hệ thống |
| SP đúng | SUM(so_sp_dung) | Số SP tồn thực tế = hệ thống |
| Tỷ lệ chính xác | (SP đúng / Tổng SP) × 100% | % sản phẩm không có chênh lệch |
| Giá trị lệch | SUM(tong_gia_tri_lech) | Tổng giá trị chênh lệch (âm = thiếu) |

---

### 2.2. Báo cáo Chi tiết theo Phiếu

**Route:** `/admin/kho-hang/kiem-kho/{id}`

**Thông tin hiển thị:**

**Header:**
- Mã phiếu: `KK00001`
- Người tạo: `Nguyễn Văn A`
- Người kiểm: `Trần Thị B`
- Người duyệt: `Lê Văn C`
- Ngày kiểm: `30/08/2026`
- Trạng thái: Badge màu theo trạng thái
- Phạm vi: `Toàn bộ` / `Theo danh mục: Đồ uống` / `Chọn sản phẩm`

**Thống kê:**
```
┌────────────────────────────────────────────┐
│  Tổng SP: 50  │  Thiếu: 5  │  Thừa: 3    │
│  Đúng: 42     │  Chưa đếm: 0             │
├────────────────────────────────────────────┤
│  Hệ thống: 1,250  │  Thực tế: 1,235      │
│  Chênh lệch: -15  │  Giá trị: -450,000đ  │
└────────────────────────────────────────────┘
```

**Bảng chi tiết:**

| Mã hàng | Tên sản phẩm | ĐVT | HSD gần nhất | SL Hệ thống | SL Thực tế | Lệch | Giá vốn | Giá trị lệch | Người đếm | Thời gian |
|---------|--------------|-----|--------------|-------------|------------|------|---------|--------------|-----------|-----------|
| SP001 | Nước suối | Chai | 31/12/2026 | 100 | 98 | -2 | 5,000 | -10,000 | NV A | 14:30 |

**Filter:**
- Tất cả
- Chưa đếm (so_luong_thuc_te = NULL)
- Thiếu (so_luong_lech < 0)
- Đủ (so_luong_lech = 0)
- Thừa (so_luong_lech > 0)

---

### 2.3. Timeline Phiếu

Hiển thị trên trang chi tiết phiếu:

```
● Tạo phiếu          30/08/2026 08:00  Nguyễn Văn A
● Bắt đầu kiểm       30/08/2026 09:00  Trần Thị B
● Hoàn tất đếm       30/08/2026 14:30  Trần Thị B
● Duyệt              30/08/2026 15:00  Lê Văn C
● Hoàn tất điều chỉnh 30/08/2026 15:05  Kế toán D
```

---

### 2.4. Báo cáo Hiệu suất Nhân viên

**Chỉ số theo nhân viên:**

| Nhân viên | Tổng phiếu | Tổng SP đếm | Tỷ lệ chính xác | Thời gian TB/SP | Phiếu bị từ chối |
|-----------|------------|-------------|-----------------|-----------------|------------------|
| Trần Thị B | 5 | 250 | 95% | 2 phút | 0 |
| Nguyễn Văn C | 3 | 150 | 88% | 3 phút | 1 |

**Công thức:**
- **Tỷ lệ chính xác** = (Tổng SP đúng / Tổng SP đếm) × 100%
- **Thời gian TB/SP** = (hoan_tat_dem_luc - bat_dau_luc) / tong_so_san_pham

---

### 2.5. Báo cáo Sản phẩm hay bị Chênh lệch

**Top 10 sản phẩm:**
- Thiếu nhiều nhất
- Thừa nhiều nhất
- Sai lệch thường xuyên (số lần xuất hiện trong phiếu có lệch)

| Sản phẩm | Số lần kiểm | Số lần thiếu | Số lần thừa | Tổng lệch | Giá trị lệch |
|----------|-------------|--------------|-------------|-----------|--------------|
| Nước suối 500ml | 10 | 8 | 0 | -45 | -225,000 |

**Mục đích:** Giúp phát hiện sản phẩm cần:
- Kiểm tra quy trình nhập/xuất
- Cải thiện cách bảo quản
- Phát hiện hành vi gian lận

---

## 3. API Reference

### 3.1. Lấy báo cáo tổng hợp

```http
GET /admin/api/kiem-kho/bao-cao
Authorization: Bearer {token}

Query Parameters:
- tu_ngay: 2026-08-01 (optional)
- den_ngay: 2026-08-31 (optional)
- id_nguoi_kiem: 5 (optional)
```

**Service Method:**
```php
KiemKhoService::baoCaoTongHop([
    'tu_ngay' => '2026-08-01',
    'den_ngay' => '2026-08-31',
])
```

---

### 3.2. Lấy thống kê theo phiếu

```http
GET /admin/api/kiem-kho/{id}/thong-ke
```

**Service Method:**
```php
KiemKhoService::thongKePhieu($phieu)
```

**Response:**
```json
{
  "tong_so_san_pham": 50,
  "so_sp_dung": 42,
  "so_sp_thieu": 5,
  "so_sp_thua": 3,
  "so_sp_chua_dem": 0,
  "tong_sl_he_thong": 1250,
  "tong_sl_thuc_te": 1235,
  "tong_sl_lech": -15,
  "tong_gia_tri_lech": -450000
}
```

---

## 4. Biểu đồ đề xuất

### 4.1. Biểu đồ Cột (Chart.js)
- **Trục X:** Tháng
- **Trục Y:** Số lượng
- **Cột:** Thiếu (đỏ) | Thừa (xanh) | Đúng (xám)

### 4.2. Biểu đồ Tròn
- **Tỷ lệ:** SP đúng vs SP sai

### 4.3. Line Chart
- **Xu hướng chênh lệch** theo thời gian

---

## 5. Export Excel

**Route:** `/admin/kho-hang/kiem-kho/bao-cao/export`

**Format:**

**Sheet 1: Tổng hợp**
- Header: Từ ngày X đến ngày Y
- Các chỉ số tổng hợp

**Sheet 2: Chi tiết từng phiếu**
- Danh sách tất cả phiếu trong kỳ

**Sheet 3: Chi tiết sản phẩm**
- Tất cả dòng chi tiết của tất cả phiếu

**Library:** `Maatwebsite\Excel` (Laravel Excel)

---

## 6. Print Phiếu

**Route:** `/admin/kho-hang/kiem-kho/{id}/in`

**View:** `resources/views/admin_xem_truoc/kiem_kho/print.blade.php`

**Nội dung in:**
1. Header công ty
2. Tiêu đề: PHIẾU KIỂM KHO
3. Mã phiếu, ngày kiểm
4. Bảng chi tiết sản phẩm
5. Tổng kết: Tổng SL hệ thống, thực tế, lệch
6. Chữ ký:
   - Người kiểm
   - Người duyệt
   - Thủ kho
   - Kế toán

**CSS:** `@media print` để ẩn header/sidebar

---

## 7. Lưu ý Implementation

### 7.1. Performance
- Nếu số phiếu > 1000: Phân trang
- Cache báo cáo tháng trước (không thay đổi)
- Index: `(trang_thai, hoan_thanh_luc)` cho query nhanh

### 7.2. Quyền truy cập
- **kiem_kho_xem:** Xem tất cả báo cáo
- **Admin/Kế toán:** Xem tất cả
- **Nhân viên:** Chỉ xem phiếu do mình kiểm

### 7.3. Audit
- Log mỗi lần xem/export báo cáo (nếu cần)
- Ghi nhận: Ai, Khi nào, Báo cáo gì, Filter gì

---

## 8. Tóm tắt

**Báo cáo kiểm kho** giúp:
- ✅ Giám sát tình hình chênh lệch tồn kho
- ✅ Đánh giá hiệu suất nhân viên kiểm đếm
- ✅ Phát hiện sản phẩm hay bị sai lệch
- ✅ Đưa ra quyết định cải tiến quy trình
- ✅ Audit và tuân thủ quy định kế toán

**View đã có:** `resources/views/admin_xem_truoc/kiem_kho/bao-cao.blade.php`

**API đã có:** 
- `GET /admin/api/kiem-kho/bao-cao` (Controller: `KiemKhoApiController@baoCao`)
- Service method: `KiemKhoService::baoCaoTongHop()`
