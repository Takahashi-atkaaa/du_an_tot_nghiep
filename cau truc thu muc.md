SmartMart/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Backend/          # Controllers cho trang quan tri
│   │   │   │   ├── KhachHang/    # Khoi Khach hang & Khuyen mai
│   │   │   │   │   ├── KhachHangController.php
│   │   │   │   │   ├── KhuyenMaiController.php
│   │   │   │   │   └── LichSuTichDiemController.php
│   │   │   │   ├── BanHang/       # Khoi Ban hang
│   │   │   │   │   ├── HoaDonController.php
│   │   │   │   │   ├── ChiTietHoaDonController.php
│   │   │   │   │   └── SoQuyController.php
│   │   │   │   ├── NhanSu/        # Khoi Nhan su
│   │   │   │   │   ├── NguoiDungController.php
│   │   │   │   │   ├── ThietLapLuongController.php
│   │   │   │   │   ├── CaLamViecController.php
│   │   │   │   │   ├── ChiaCaController.php
│   │   │   │   │   └── DiemDanhController.php
│   │   │   │   ├── KhoHang/      # Khoi Kho hang
│   │   │   │   │   ├── SanPhamController.php
│   │   │   │   │   ├── PhieuController.php
│   │   │   │   │   ├── ChiTietPhieuController.php
│   │   │   │   │   └── NhaCungCapController.php
│   │   │   │   ├── DanhMuc/      # Danh muc ho tro
│   │   │   │   │   ├── DanhMucSanPhamController.php
│   │   │   │   │   ├── ThuocTinhController.php
│   │   │   │   │   └── DonViController.php
│   │   │   │   ├── AuthController.php
│   │   │   │   └── DashboardController.php
│   │   │   └── Api/              # API Controllers (neu can)
│   │   └── Middleware/
│   │       ├── AuthAdmin.php     # Kiem tra quyen admin
│   │       └── KiemTraVaiTro.php # Kiem tra vai tro
│   └── Requests/                # Form Requests cho validation
│       ├── KhachHang/
│       │   ├── ThemKhachHangRequest.php
│       │   └── CapNhatKhachHangRequest.php
│       ├── KhuyenMai/
│       │   ├── ThemKhuyenMaiRequest.php
│       │   └── CapNhatKhuyenMaiRequest.php
│       ├── BanHang/
│       │   ├── ThemHoaDonRequest.php
│       │   └── CapNhatHoaDonRequest.php
│       ├── NhanSu/
│       │   ├── ThemNhanVienRequest.php
│       │   └── CapNhatNhanVienRequest.php
│       ├── KhoHang/
│       │   ├── ThemSanPhamRequest.php
│       │   └── CapNhatSanPhamRequest.php
│       ├── NhaCungCap/
│       │   ├── ThemNhaCungCapRequest.php
│       │   └── CapNhatNhaCungCapRequest.php
│       └── Auth/
│           └── DangNhapRequest.php
│   └── Models/
│       ├── KhachHang.php
│       ├── KhuyenMai.php
│       ├── KhuyenMaiSanPham.php
│       ├── LichSuTichDiem.php
│       ├── HoaDon.php
│       ├── ChiTietHoaDon.php
│       ├── SoQuy.php
│       ├── NguoiDung.php
│       ├── ThietLapLuong.php
│       ├── CaLamViec.php
│       ├── ChiaCaLamViec.php
│       ├── DiemDanh.php
│       ├── SanPham.php
│       ├── Phieu.php
│       ├── ChiTietPhieu.php
│       ├── NhaCungCap.php
│       ├── DanhMucSanPham.php
│       ├── ThuocTinhSanPham.php
│       └── DonViSanPham.php
├── database/
│   ├── migrations/               # Giu nguyen Laravel chuan
│   ├── seeders/                  # Seeders theo module
│   │   ├── DatabaseSeeder.php
│   │   ├── KhachHangSeeder.php
│   │   ├── BanHangSeeder.php
│   │   ├── NhanSuSeeder.php
│   │   └── KhoHangSeeder.php
│   └── factories/
├── resources/
│   └── views/
│       ├── layouts/              # Layouts chung
│       │   ├── admin.blade.php   # Layout trang quan tri
│       │   └── partials/        # Header, Sidebar, Footer
│       ├── backend/              # Views trang quan tri
│       │   ├── dashboard/
│       │   ├── khach-hang/
│       │   ├── khuyen-mai/
│       │   ├── ban-hang/
│       │   ├── nhan-su/
│       │   ├── kho-hang/
│       │   ├── nha-cung-cap/
│       │   └── danh-muc/
│       └── errors/               # Trang loi
├── routes/
│   ├── web.php                   # Routes web (co views)
│   └── api.php                   # Routes API (neu can)
├── public/
│   ├── css/
│   ├── js/
│   └── uploads/                  # Hinh anh san pham
└── tests/
    ├── Unit/
    └── Feature/