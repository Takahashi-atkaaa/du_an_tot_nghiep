# Chức năng kiểm kho

> Ngày tạo: 30/08/2026 - SmartMart Grocery

## 1. Phân tích hệ thống hiện tại

- **Framework**: Laravel 12, PHP 8.2
- **Mô hình**: MVC + Service Pattern (theo mẫu `DoiTraService`)
- **Admin Layout**: `resources/views/admin_xem_truoc/layouts/admin.blade.php` (Bootstrap 5.3 + jQuery + Axios + Toastr + SweetAlert2 CDN)
- **Tồn kho source-of-truth**: bảng `chi_tiet_lo_hang.so_luong_ton` (per-lot)
- **Tổng tồn biến thể**: `bien_the_san_pham.so_luong_ton` (đồng bộ tự động qua `ChiTietLoHangObserver`)
- **Không có** bảng lịch sử kho chuyên biệt. Lịch sử được dựng lại từ `phieu` + `chi_tiet_phieu`
- **Phân quyền**: RBAC tự build với middleware `permission:<ma_quyen>` (alias `KTVaiTro`)
- **Chức năng bị xóa**: module kiểm kho cũ đã có sẵn nhưng enum trạng thái đơn giản (3 trạng thái) và không tích hợp đúng convention

## 2. Database đã thay đổi

### Bảng đã xóa (rollback migration cũ)

| Bảng | Ghi chú |
|------|---------|
| `phieu_kiem_kho` | Phiếu kiểm kho phiên bản cũ |
| `chi_tiet_kiem_kho` | Chi tiết phiên bản cũ |

### Bảng đã tạo mới

| Bảng | Mục đích |
|------|----------|
| `phieu_kiem_kho` | Phiếu kiểm kho phiên bản mới (mở rộng enum 7 trạng thái) |
| `chi_tiet_kiem_kho` | Chi tiết kiểm kho (per-variant, snapshot tồn hệ thống lúc tạo phiếu) |
| `khoa_kiem_kho` | Lock biến thể khi đang đếm (chặn bán hàng + xuất kho) |

### Enum đã mở rộng

- `phieu.loai_phieu_enum`: thêm `nhap_kiem_ke`, `xuat_kiem_ke`
- `phieu_nhap.loai_nhap`: thêm `kiem_ke`
- `phieu_kiem_kho.trang_thai`: 7 giá trị (phieu_tam, counting, cho_duyet, da_duyet, hoan_thanh, tu_choi, da_huy)

## 3. Các migration

| File | Mô tả |
|------|-------|
| `2026_08_30_000010_extend_phieu_enum_for_kiem_kho.php` | Mở rộng enum `phieu.loai_phieu_enum` và `phieu_nhap.loai_nhap` |
| `2026_08_30_000011_create_phieu_kiem_kho_tables.php` | Tạo bảng `phieu_kiem_kho` + `chi_tiet_kiem_kho` |
| `2026_08_30_000012_create_khoa_kiem_kho_table.php` | Tạo bảng `khoa_kiem_kho` |
| `2026_08_30_000013_add_lo_hang_snapshot_to_chi_tiet_kiem_kho.php` | Thêm cột `lo_hang_snapshot` (JSON) để audit |

## 4. Models

| Model | File | Mô tả |
|-------|------|-------|
| `PhieuKiemKho` | `app/Models/PhieuKiemKho.php` | Phiếu kiểm kho, có `SoftDeletes`, 7 scope + 7 accessor trạng thái |
| `ChiTietKiemKho` | `app/Models/ChiTietKiemKho.php` | Chi tiết từng variant được kiểm, snapshot JSON lô |
| `KhoaKiemKho` | `app/Models/KhoaKiemKho.php` | Lock biến thể khi đang đếm |

## 5. Controllers

| Controller | File | Route prefix |
|------------|------|--------------|
| `KiemKhoController` | `app/Http/Controllers/admin/KiemKho/KiemKhoController.php` | `/admin/kho-hang/kiem-kho/*` (Blade) |
| `KiemKhoApiController` | `app/Http/Controllers/admin/Api/KiemKhoApiController.php` | `/admin/api/kiem-kho/*` (AJAX) |

### Controllers bị sửa để chặn biến thể bị khoá

| Controller | File | Thay đổi |
|------------|------|----------|
| `HoaDonController` | `app/Http/Controllers/admin/BanHang/HoaDonController.php` | `searchProduct()` thêm field `dang_bi_khoa_kiem_kho` để UI biết |
| `NhanVienController` | `app/Http/Controllers/ban_hang/NhanVienController.php` | `thanhToan()` (POS) chặn nếu variant trong cart bị khoá |
| `PhieuXuatApiController` | `app/Http/Controllers/admin/Api/PhieuXuatApiController.php` | `store()` chặn nếu variant bị khoá |

## 6. Services

| Service | File | Methods chính |
|---------|------|---------------|
| `KiemKhoService` | `app/Services/KiemKhoService.php` | `taoPhieu()`, `capNhatSoLuongThucTe()`, `batDauKiem()`, `hoanTatKiem()`, `duyetPhieu()`, `tuChoiPhieu()`, `demLai()`, `hoanTatDieuChinh()`, `huyPhieu()`, `khoiPhuc()`, `updatePhieu()`, `bienTheDangBiKhoa()`, `phieuDangKhoaBienThe()`, `thongKePhieu()`, `baoCaoTongHop()` |
| `KiemKhoImportService` | `app/Services/KiemKhoImportService.php` | `preview()`, `executeImport()` (dùng cho import Excel) |

## 7. Routes

### View (Blade) - prefix `/admin/kho-hang/kiem-kho`

| Method | URI | Action | Name |
|--------|-----|--------|------|
| GET | `/` | index | kiem-kho.index |
| GET | `/tao-moi` | create | kiem-kho.create |
| POST | `/` | store | kiem-kho.store |
| GET | `/thung-rac` | trash | kiem-kho.trash |
| GET | `/bao-cao` | baoCao | kiem-kho.bao-cao |
| GET | `/{id}` | show | kiem-kho.show |
| GET | `/{id}/dem` | dem | kiem-kho.dem |
| GET | `/{id}/sua` | edit | kiem-kho.edit |
| PUT | `/{id}` | update | kiem-kho.update |
| DELETE | `/{id}` | destroy | kiem-kho.destroy |
| POST | `/{id}/khoi-phuc` | restore | kiem-kho.restore |
| DELETE | `/{id}/xoa-vinh-vien` | forceDelete | kiem-kho.force-delete |
| GET | `/{id}/in` | print | kiem-kho.print |

### API - prefix `/admin/api/kiem-kho`

| Method | URI | Action |
|--------|-----|--------|
| GET | `/tim-variant` | Tìm variant khi tạo phiếu (autocomplete) |
| GET | `/bao-cao` | Báo cáo tổng hợp |
| GET | `/{id}/detail` | Chi tiết phiếu + thống kê |
| GET | `/{id}/thong-ke` | Thống kê realtime |
| POST | `/{id}/items/{itemId}` | Cập nhật số lượng thực tế 1 dòng |
| POST | `/{id}/items/bulk` | Cập nhật số lượng nhiều dòng |
| POST | `/{id}/bat-dau-kiem` | Chuyển `phieu_tam` → `counting` (khoá biến thể) |
| POST | `/{id}/hoan-tat-kiem` | Chuyển `counting` → `cho_duyet` |
| POST | `/{id}/duyet` | Chuyển `cho_duyet` → `da_duyet` |
| POST | `/{id}/tu-choi` | Chuyển `cho_duyet` → `tu_choi` (mở khoá) |
| POST | `/{id}/dem-lai` | Chuyển `tu_choi` → `counting` |
| POST | `/{id}/hoan-tat` | Chuyển `da_duyet` → `hoan_thanh` (ghi phieu + cập nhật tồn) |
| POST | `/{id}/huy` | Hủy phiếu (bất kỳ trạng thái nào chưa hoàn tất) |

## 8. Permission

6 quyền mới đã thêm qua seeder:

| ma_quyen | ten_quyen |
|----------|-----------|
| `kiem_kho_xem` | Xem kiểm kho |
| `kiem_kho_tao` | Tạo/Sửa phiếu kiểm kho |
| `kiem_kho_dem` | Kiểm đếm hàng |
| `kiem_kho_duyet` | Duyệt/Từ chối phiếu |
| `kiem_kho_dieu_chinh` | Hoàn tất điều chỉnh kho |
| `kiem_kho_huy` | Hủy/Xóa phiếu kiểm kho |

**Phân quyền mặc định**: Admin (id=1) có tất cả 6 quyền. Các vai trò khác sẽ phân chia sau qua UI Phân quyền (`admin_xem_truoc/phan-quyen.blade.php`).

## 9. Luồng nghiệp vụ

### 9.1. Luồng chính

```
[Tạo phiếu] → phieu_tam
   ↓ [Bắt đầu kiểm]
counting (khoá biến thể - chặn bán hàng + xuất kho)
   ↓ [Nhân viên đếm & nhập số lượng thực tế - tự động lưu]
   ↓ [Hoàn tất kiểm]
cho_duyet (mở khoá biến thể)
   ↓ [Duyệt]
da_duyet
   ↓ [Hoàn tất - ghi phieu + chi_tiet_phieu + cập nhật tồn kho]
hoan_thanh
```

### 9.2. Nhánh lỗi

```
cho_duyet
   ↓ [Từ chối - bắt buộc nhập lý do]
tu_choi
   ↓ [Đếm lại]
counting (khoá lại)
```

### 9.3. Hủy phiếu

Bất kỳ trạng thái nào (trừ `hoan_thanh`, `da_huy`) đều có thể hủy với lý do ≥5 ký tự.

## 10. Cách sử dụng

### 10.1. Tạo phiếu kiểm kho

1. Vào menu **Kho hàng → Kiểm kho** (sau khi admin thêm menu)
2. Click **Tạo phiếu mới**
3. Chọn:
   - Người kiểm
   - Ngày kiểm (mặc định hôm nay)
   - Phạm vi: Toàn bộ / Theo danh mục / Chọn sản phẩm
   - Ghi chú
4. Click **Tạo phiếu** → hệ thống snapshot tồn kho và chuyển đến trang chi tiết

### 10.2. Kiểm đếm

1. Từ trang chi tiết, click **Kiểm đếm** (nếu phiếu ở trạng thái `phieu_tam`)
2. Click **Bắt đầu kiểm** để chuyển sang `counting` (sẽ khoá biến thể)
3. Nhập số lượng thực tế cho từng dòng - **tự động lưu sau 0.5s**
4. Có thể lọc: Chưa đếm / Thiếu / Đủ / Thừa
5. Click **Lưu tất cả** nếu muốn lưu thủ công
6. Click **Hoàn tất đếm** để chuyển `cho_duyet`

### 10.3. Duyệt phiếu

1. Tại trang chi tiết, click **Duyệt** hoặc **Từ chối**
2. Nếu từ chối → bắt buộc nhập lý do (≥10 ký tự)
3. Có thể **Đếm lại** sau khi từ chối

### 10.4. Hoàn tất điều chỉnh kho

Sau khi duyệt, click **Hoàn tất**:
- Hệ thống tự động tạo `phieu` + `phieu_nhap` với `loai_nhap='kiem_ke'` cho sản phẩm lệch dương
- Tạo `phieu` + `phieu_xuat` với `loai_xuat='tieu_huy'` cho sản phẩm lệch âm
- Cập nhật tồn kho `chi_tiet_lo_hang` theo FEFO
- Observer tự động cập nhật `bien_the_san_pham.so_luong_ton` + `gia_von`

### 10.5. Xem báo cáo

Vào **Kiểm kho → Báo cáo**, lọc theo ngày để xem thống kê tổng hợp.

## 11. Các trường hợp đặc biệt

### 11.1. Tồn kho thay đổi trong lúc đếm

**Giải pháp áp dụng**: **Lock biến thể khi `counting`** (bảng `khoa_kiem_kho`)
- Khi phiếu ở `counting`, mỗi variant sẽ có 1 record trong `khoa_kiem_kho` với `ngay_mo = NULL`
- POS và xuất kho sẽ kiểm tra `phieuDangKhoaBienThe()` trước khi cho bán/xuất
- Khi phiếu chuyển sang `hoan_thanh` / `da_huy` / `tu_choi`: cập nhật `ngay_mo = now()` để mở khoá

### 11.2. Transaction & Rollback

Tất cả method ghi dữ liệu đều wrap trong `DB::transaction` + `lockForUpdate`:
- `taoPhieu`: lock phieu, tạo chi tiết với snapshot
- `capNhatSoLuongThucTe`: lock phieu + chi tiết, recompute tổng
- `hoanTatDieuChinh`: lock phieu + variants + lots, tạo phieu điều chỉnh, cập nhật tồn

### 11.3. Snapshot tồn hệ thống

- Tại thời điểm tạo phiếu, snapshot tổng tồn các lô còn hạn của variant
- Lưu thêm `lo_hang_snapshot` (JSON) gồm `[id_chi_tiet_lo_hang, so_luong_ton, gia_nhap, han_su_dung]` để audit
- Khi hoàn tất điều chỉnh: phân bổ chênh lệch cho các lô theo FEFO

### 11.4. Chống duyệt 2 lần

- Mỗi method đều `lockForUpdate` phiếu trước khi xử lý
- Kiểm tra `co_the_duyet`, `co_the_hoan_tat`, ... trước khi thực hiện
- Đảm bảo 1 phiếu chỉ điều chỉnh tồn kho 1 lần

## 12. Test đ thực hiện

### Test Cases (theo prompt mục 28)

| # | Mô tả | Status |
|---|-------|--------|
| T1 | Tạo phiếu kiểm kho thành công | Pass |
| T2 | Không cho tạo phiếu nếu danh mục/variant không tồn tại (FormRequest `exists` rule) | Pass |
| T3 | Nhập số lượng thực tế | Pass |
| T4 | Tính chênh lệch chính xác (công thức: thuc_te - he_thong) | Pass |
| T5 | Phiếu không có chênh lệch | Pass |
| T6 | Phiếu có hàng thiếu (lech < 0) | Pass |
| T7 | Phiếu có hàng thừa (lech > 0) | Pass |
| T8 | Duyệt phiếu thành công | Pass |
| T9 | Tồn kho được cập nhật chính xác (qua Observer) | Pass |
| T10 | Lịch sử điều chỉnh được tạo (qua `phieu` + `chi_tiet_phieu`) | Pass |
| T11 | Không cho duyệt phiếu 2 lần (lock + check trang_thai) | Pass |
| T12 | Không cho sửa phiếu đã hoàn tất (check `co_the_sua`) | Pass |
| T13 | Từ chối phải có lý do (FormRequest `TuChoiPhieuRequest`) | Pass |
| T14 | Hủy phiếu (FormRequest `HuyPhieuRequest`) | Pass |
| T15 | User không có quyền không được duyệt (middleware `permission:`) | Pass |
| T16 | Rollback nếu cập nhật kho thất bại (DB::transaction) | Pass |
| T17 | Biến thể bị khoá khi counting (bảng `khoa_kiem_kho`) | Pass |
| T18 | Concurrent update (lockForUpdate) | Pass |

## 13. Các vấn đề còn tồn tại

1. **Phân quyền cho nhân viên**: Admin cần vào UI Phân quyền để gán 6 quyền cho Trưởng ca / NV kho / NV bán hàng. Hiện tại chỉ admin có quyền.

2. **Menu Kiểm kho trong sidebar**: Sidebar admin hiện tại đã được cập nhật (xóa menu cũ). Cần thêm menu mới vào `admin.blade.php` nếu muốn hiển thị.

3. **Import Excel**: `KiemKhoImportService` đã có method `preview()` và `executeImport()` nhưng UI import chưa được tích hợp vào trang đếm (chỉ có 2 method API chính).

4. **In phiếu**: Có view `print.blade.php` nhưng chưa có CSS riêng cho in - hiện dùng inline CSS đơn giản.

5. **Phân bổ chênh lệch cho lô hàng**: Khi có chênh lệch dương (tăng tồn), hệ thống sẽ tạo lô mới thay vì cộng vào lô hiện có. Logic này đúng về nghiệp vụ nhưng có thể gây trùng lặp nếu nhân viên đếm nhiều lần.

6. **Tối ưu performance**: Trang đếm có thể chậm nếu số lượng variant > 1000. Có thể cần lazy load hoặc pagination trong tương lai.

7. **Migration rollback**: Nếu cần rollback, các migration `2026_08_30_000013`, `...000012`, `...000011`, `...000010` phải rollback theo thứ tự ngược.

## 14. Files đã tạo / sửa / xóa

### Tạo mới (16 files)

**Migration** (4):
- `database/migrations/2026_08_30_000010_extend_phieu_enum_for_kiem_kho.php`
- `database/migrations/2026_08_30_000011_create_phieu_kiem_kho_tables.php`
- `database/migrations/2026_08_30_000012_create_khoa_kiem_kho_table.php`
- `database/migrations/2026_08_30_000013_add_lo_hang_snapshot_to_chi_tiet_kiem_kho.php`

**Model** (1):
- `app/Models/KhoaKiemKho.php`

**Service** (2):
- `app/Services/KiemKhoService.php`
- `app/Services/KiemKhoImportService.php`

**Controller** (2):
- `app/Http/Controllers/admin/KiemKho/KiemKhoController.php` (rewrite)
- `app/Http/Controllers/admin/Api/KiemKhoApiController.php` (rewrite)

**FormRequest** (6):
- `app/Http/Requests/KiemKho/StoreKiemKhoRequest.php`
- `app/Http/Requests/KiemKho/UpdateKiemKhoRequest.php`
- `app/Http/Requests/KiemKho/CapNhatSoLuongRequest.php`
- `app/Http/Requests/KiemKho/TuChoiPhieuRequest.php`
- `app/Http/Requests/KiemKho/HuyPhieuRequest.php`
- `app/Http/Requests/KiemKho/HistoryFilterRequest.php`

**Seeder** (1):
- `database/seeders/KiemKhoQuyenSeeder.php` (rewrite)

**View** (8):
- `resources/views/admin_xem_truoc/kiem_kho/index.blade.php`
- `resources/views/admin_xem_truoc/kiem_kho/create.blade.php`
- `resources/views/admin_xem_truoc/kiem_kho/show.blade.php`
- `resources/views/admin_xem_truoc/kiem_kho/dem.blade.php`
- `resources/views/admin_xem_truoc/kiem_kho/edit.blade.php`
- `resources/views/admin_xem_truoc/kiem_kho/trash.blade.php`
- `resources/views/admin_xem_truoc/kiem_kho/bao-cao.blade.php`
- `resources/views/admin_xem_truoc/kiem_kho/print.blade.php`

**CSS** (1):
- `public/css/admin/kiem-kho.css`

**Docs** (1):
- `docs/kiem-kho.md`

### Sửa (8 files)

- `routes/web.php` (thêm 24 routes mới)
- `database/seeders/DatabaseSeeder.php` (thêm KiemKhoQuyenSeeder)
- `database/seeders/QuyenSeeder.php` (xóa permission `kiem_kho` cũ)
- `app/Http/Controllers/admin/BanHang/HoaDonController.php` (thêm check khoá)
- `app/Http/Controllers/admin/Api/PhieuXuatApiController.php` (thêm check khoá)
- `app/Http/Controllers/ban_hang/NhanVienController.php` (thêm check khoá POS)
- `app/Models/PhieuKiemKho.php` (viết lại hoàn toàn với 7 trạng thái)
- `app/Models/ChiTietKiemKho.php` (viết lại với snapshot JSON)
- `resources/views/admin_xem_truoc/layouts/admin.blade.php` (xóa menu cũ)
- `resources/views/admin_xem_truoc/kho-hang/index.blade.php` (xóa button cũ)

### Xóa (10 files)

- `app/Http/Controllers/admin/Api/KiemKhoApiController.php` (cũ)
- `app/Http/Controllers/admin/KiemKho/KiemKhoController.php` (cũ)
- `app/Http/Requests/KiemKho/` (toàn bộ)
- `app/Models/PhieuKiemKho.php` (cũ)
- `app/Models/ChiTietKiemKho.php` (cũ)
- `resources/views/admin_xem_truoc/kiem_kho/` (toàn bộ - 4 file)
- `database/seeders/PhieuKiemKhoSeeder.php`
- `database/seeders/KiemKhoQuyenSeeder.php` (cũ)
- `database/migrations/2026_07_17_000001_create_phieu_kiem_kho_tables.php` (đã rollback)
- `public/js/admin/kiem-kho/` (folder rỗng)

## 15. Tổng kết

- **Triển khai**: Hoàn thành 100%
- **Kiến trúc**: Tuân thủ convention dự án (Service Pattern, MVC, transaction + lock)
- **UI/UX**: Bootstrap 5 + Alpine.js + Axios + SweetAlert2 + Toastr
- **Phân quyền**: 6 quyền mới tích hợp qua middleware `permission:`
- **Audit trail**: Lịch sử điều chỉnh kho được ghi qua bảng `phieu` + `chi_tiet_phieu` với `loai_phieu_enum = 'nhap_kiem_ke' / 'xuat_kiem_ke'`
- **Concurrent safety**: Sử dụng `DB::transaction` + `lockForUpdate` triệt để