# TEST TÍNH NĂNG "CHỌN LÔ" - PHIẾU XUẤT

## 📋 Tóm tắt tính năng

Đã triển khai tính năng **"Chọn lô"** cho trang tạo phiếu xuất, cho phép:
- Click nút "Chọn lô" → Mở modal danh sách lô hàng có tồn kho
- Tìm kiếm lô theo mã lô, nhà cung cấp
- Chọn 1 lô → Tự động thêm TẤT CẢ sản phẩm trong lô vào danh sách xuất
- Tự động điền số lượng = tồn kho của lô đó
- Tránh trùng lặp (nếu sản phẩm đã có trong danh sách thì bỏ qua)

## 🔧 Files đã chỉnh sửa

### 1. **View** - `resources/views/admin_xem_truoc/kho-hang/phieu-xuat/create.blade.php`
- ✅ Thêm nút "Chọn lô" vào thanh công cụ (dòng 82-90)
- ✅ Điều chỉnh layout: col-md-4 + col-md-3 + col-md-2 + col-md-3
- ✅ Thêm modal chọn lô với bảng danh sách (modal-xl, scrollable)
- ✅ Có filter theo NCC và search theo mã lô

### 2. **JavaScript** - `public/js/admin/phieu-xuat-create.js`
- ✅ Event handler cho nút "Chọn lô" (#px-btn-chon-lo)
- ✅ Function `loadDanhSachLoHangDeChon()` - Load danh sách lô (có tồn)
- ✅ Function `chonToanBoLoHang(idLo)` - Chọn toàn bộ sản phẩm trong lô
- ✅ Function `addVariantToXuatTable()` - Thêm sản phẩm vào bảng xuất
- ✅ Function `formatDateDisplay()` - Format ngày tháng
- ✅ Debounce search (300ms)
- ✅ Validation: không thêm trùng sản phẩm

### 3. **API Controller** - `app/Http/Controllers/admin/Api/LoHangApiController.php`
- ✅ Thêm parameter `co_ton` vào method `index()`
- ✅ Filter: `whereHas('chiTietLoHang', fn($ct) => $ct->where('so_luong_ton', '>', 0))`
- ✅ Eager load `chiTietLoHang.variant.product` để lấy đầy đủ thông tin

## 🎯 Flow hoạt động

```
1. User click nút "Chọn lô" 
   ↓
2. Modal hiện ra, gọi API: GET /admin/api/lo-hang?co_ton=1
   ↓
3. Hiển thị danh sách lô hàng (chỉ lô có tồn kho > 0)
   - Mã lô
   - Nhà cung cấp
   - Ngày nhập
   - Số sản phẩm (có tồn)
   - Tổng tồn kho
   ↓
4. User có thể:
   - Search theo mã lô
   - Filter theo nhà cung cấp
   ↓
5. User click nút "Chọn" trên 1 lô
   ↓
6. Gọi API: GET /admin/api/lo-hang/{id}
   - Lấy chi tiết lô + tất cả sản phẩm
   ↓
7. JavaScript xử lý:
   - Lọc các chi_tiet_lo_hang có so_luong_ton > 0
   - Với mỗi sản phẩm:
     * Check đã có trong bảng chưa (theo variant_id)
     * Nếu chưa → thêm row mới
     * Nếu đã có → bỏ qua (đếm vào soLuongBoQua)
   ↓
8. Thêm vào bảng "Danh sách sản phẩm xuất":
   - Tên sản phẩm + biến thể
   - Tồn kho
   - SL xuất (auto = tồn kho)
   - Lô hàng (auto chọn lô này)
   - HSD (auto điền)
   ↓
9. Modal đóng, hiện thông báo thành công
   - "Đã thêm X sản phẩm từ lô [Mã lô]"
   - "(Y sản phẩm đã có trong danh sách)" - nếu có
   ↓
10. User có thể:
    - Điều chỉnh số lượng xuất
    - Xóa sản phẩm không cần
    - Tiếp tục chọn thêm lô khác
    ↓
11. Submit form bình thường
```

## 🧪 Cách test

### Test 1: Hiển thị modal
```
1. Vào trang: /admin/kho-hang/phieu-xuat/create
2. Click nút "Chọn lô" (màu primary, icon fa-layer-group)
3. ✓ Modal hiện ra với title "Chọn lô hàng để xuất"
4. ✓ Có thanh search và filter NCC
5. ✓ Có bảng danh sách lô hàng
```

### Test 2: Load danh sách lô
```
1. Mở modal
2. ✓ Thấy loading spinner
3. ✓ Sau vài giây hiện danh sách lô (chỉ lô có tồn kho)
4. ✓ Mỗi lô hiển thị:
   - Mã lô (badge màu info)
   - Tên NCC
   - Ngày nhập (dd/mm/yyyy)
   - Số SP (badge màu secondary)
   - Tổng tồn (màu xanh, in đậm)
   - Nút "Chọn" (màu primary)
```

### Test 3: Search và filter
```
1. Nhập mã lô vào ô search
2. ✓ Sau 300ms tự động tìm kiếm
3. ✓ Kết quả lọc theo mã lô
4. Chọn NCC trong dropdown
5. ✓ Kết quả lọc theo NCC
6. Kết hợp cả 2
7. ✓ Kết quả lọc theo cả mã lô VÀ NCC
```

### Test 4: Chọn lô (có sản phẩm tồn)
```
1. Click nút "Chọn" trên 1 lô có nhiều sản phẩm
2. ✓ Modal đóng
3. ✓ Hiện toast thông báo: "Đã thêm X sản phẩm từ lô [Mã lô]"
4. ✓ Bảng "Danh sách sản phẩm xuất" có X rows mới
5. ✓ Mỗi row:
   - Tên sản phẩm + biến thể
   - Tồn kho (badge màu sáng)
   - SL xuất = tồn kho
   - Lô hàng đã auto chọn
   - HSD đã auto điền
6. ✓ Tổng số sản phẩm (badge đỏ) tăng lên
```

### Test 5: Chọn lô (không có tồn)
```
1. Tạo 1 lô không có sản phẩm tồn kho
2. ✓ Lô này KHÔNG hiện trong modal (vì có filter co_ton=1)
```

### Test 6: Chọn lô trùng lặp
```
1. Chọn lô A → thêm được 5 sản phẩm
2. Chọn lại lô A
3. ✓ Thông báo: "Đã thêm 0 sản phẩm từ lô A (5 sản phẩm đã có trong danh sách)"
4. ✓ Không có row trùng lặp
```

### Test 7: Validation tồn kho
```
1. Chọn lô → thêm sản phẩm (ví dụ: tồn = 50)
2. Sửa SL xuất = 100 (vượt tồn)
3. ✓ Input chuyển màu đỏ (is-invalid)
4. ✓ Hiện text cảnh báo: "Vượt tồn kho (tối đa: 50)"
5. Sửa lại SL xuất = 30
6. ✓ Cảnh báo biến mất
```

### Test 8: Xóa sản phẩm đã chọn từ lô
```
1. Chọn lô → thêm sản phẩm
2. Click nút xóa (icon trash) trên 1 row
3. ✓ Row bị xóa khỏi bảng
4. ✓ Tổng số sản phẩm giảm đi
5. ✓ Có thể chọn lại sản phẩm đó
```

### Test 9: Submit form
```
1. Chọn loại xuất = "Tiêu hủy"
2. Nhập lý do xuất
3. Chọn 1 lô → thêm sản phẩm
4. Điều chỉnh số lượng (nếu muốn)
5. Click "Lưu phiếu xuất"
6. ✓ API POST /admin/api/phieu-xuat nhận đúng data:
   {
     "loai_xuat": "tieu_huy",
     "ly_do": "...",
     "chi_tiet": [
       {
         "variant_id": 123,
         "id_chi_tiet_lo_hang": 456,
         "so_luong": 50
       },
       ...
     ]
   }
7. ✓ Redirect về /admin/kho-hang/phieu-xuat
```

## 🎨 UI/UX

- **Nút "Chọn lô"**: màu outline-primary, icon fa-layer-group, size lg
- **Modal**: size xl, scrollable, max-height 500px cho bảng
- **Sticky header**: thead sticky-top trong modal
- **Loading state**: spinner với text "Đang tải..."
- **Empty state**: icon + text khi không có dữ liệu
- **Badge màu sắc**:
  - Mã lô: bg-info
  - Số SP: bg-secondary
  - Tổng tồn: text-success fw-bold
- **Toast notification**: dùng toastr hoặc hienBaoPage()

## 🐛 Edge cases đã xử lý

1. ✅ Lô không có sản phẩm → không hiện trong modal
2. ✅ Lô có sản phẩm nhưng tồn = 0 → không hiện trong modal
3. ✅ Sản phẩm đã có trong danh sách → bỏ qua, không thêm trùng
4. ✅ API trả về lỗi → hiển thị error state
5. ✅ Search rỗng + filter rỗng → load tất cả lô có tồn
6. ✅ Format date an toàn (check isNaN)
7. ✅ Escape HTML để tránh XSS
8. ✅ Debounce search 300ms để tránh spam request

## 📌 Lưu ý

- **Permission**: Cần quyền `quan_ly_kho_hang` hoặc `xuat_hang` để access
- **API endpoint**: `/admin/api/lo-hang?co_ton=1&q=...&id_nha_cung_cap=...`
- **Eager loading**: `chiTietLoHang.variant.product` để tránh N+1 query
- **CSRF token**: Đã có trong meta tag, submit form dùng AJAX + JSON

## ✅ Checklist hoàn thành

- [x] Thêm nút "Chọn lô" vào view
- [x] Tạo modal chọn lô với bảng + search + filter
- [x] Load danh sách lô hàng (API + JS)
- [x] Search theo mã lô với debounce
- [x] Filter theo nhà cung cấp
- [x] Chọn lô → load chi tiết
- [x] Thêm sản phẩm vào bảng xuất
- [x] Tránh trùng lặp
- [x] Auto điền số lượng, lô hàng, HSD
- [x] Format date dd/mm/yyyy
- [x] Escape HTML để bảo mật
- [x] Loading state & error handling
- [x] Toast notification
- [x] Cập nhật API controller (filter co_ton)
- [x] Test edge cases

## 🚀 Deployment

Không cần chạy migration hoặc composer update. Chỉ cần:
```bash
# Clear cache (nếu cần)
php artisan route:clear
php artisan view:clear
php artisan config:clear

# Hoặc restart server
php artisan serve
```

---

**Ngày hoàn thành:** 2026-09-03
**Developer:** AI Assistant
**Status:** ✅ READY FOR TESTING
