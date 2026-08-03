<?php

use App\Http\Controllers\BanHang\PayOSController;
use App\Http\Controllers\admin\AuthController;
use App\Http\Controllers\ban_hang\NhanVienController;
use App\Http\Controllers\Admin\DanhMuc\DanhMucSanPhamController;
use App\Http\Controllers\Admin\SanPham\SanPhamController;
use App\Http\Controllers\admin\NhanSu\CaLamViecController;
use App\Http\Controllers\admin\NhanSu\ChiaCaController;
use App\Http\Controllers\admin\NhanSu\NguoiDungController;
use App\Http\Controllers\admin\NhanSu\ThietLapLuongController;
use App\Http\Controllers\admin\NhanSu\BangLuongController;
use App\Http\Controllers\admin\NhanSu\PhieuLuongController;
use App\Http\Controllers\admin\KhachHang\KhachHangController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\Api\LoHangApiController;
use App\Http\Controllers\Admin\Api\NhaCungCapApiController;
use App\Http\Controllers\Admin\Api\PhieuNhapApiController;
use App\Http\Controllers\Admin\Api\PhieuXuatApiController;
use App\Http\Controllers\Admin\Api\KiemKhoApiController;
use App\Http\Controllers\Admin\KiemKho\KiemKhoController;
use App\Http\Controllers\admin\KhoHang\NhaCungCapController;
use App\Http\Controllers\admin\Api\ThuocTinhApiController;
use App\Http\Controllers\admin\Api\SanPhamApiController;
use App\Http\Controllers\admin\CaiDat\ThietLapSanPhamController;
use App\Http\Controllers\admin\CaLam\CaLam;
use App\Http\Controllers\admin\CaLam\DiemDanhNhanVien;
use App\Http\Controllers\admin\CaLam\LichSuCaLam;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\admin\KhuyenMaiController;
use App\Http\Controllers\admin\PhanQuyenDong\PhanQuyen;
use App\Http\Controllers\admin\NhanSu\DiemDanhController;

use App\Http\Controllers\ban_hang\KhachHangController as NhanVienKhachHangController;
use App\Http\Controllers\nhan_vien\DiemDanhController as NhanVienDiemDanhController;
use App\Http\Controllers\nhan_vien\TrangChuController;
use App\Http\Controllers\nhan_vien\NhanVienController as NhanVienNhanVienController;


use App\Http\Middleware\AuthAdmin;
use App\Http\Middleware\KiemTraVaiTro;
use App\Http\Middleware\KTVaiTro;
use App\Models\NhaCungCap;
use App\Http\Controllers\admin\BanHang\HoaDonController;
use App\Http\Middleware\NhanVien;

Route::get('/', function () {
    return view('admin_xem_truoc.auth.login');
});



Route::get('/admin/login', [AuthController::class, 'showLoginForm'])->name('admin.login');
Route::post('/admin/login', [AuthController::class, 'login'])->name('admin.login.submit');
Route::get('/admin/logout', [AuthController::class, 'logout'])->name('admin.logout');

// Quen mat khau
Route::get('/admin/quen-mat-khau', [AuthController::class, 'showQuenMatKhau'])->name('admin.quen-mat-khau');
Route::post('/admin/quen-mat-khau', [AuthController::class, 'guiEmailQuenMatKhau'])->name('admin.quen-mat-khau.submit');

// Dat lai mat khau
Route::get('/admin/dat-lai-mat-khau/{token}', [AuthController::class, 'showFormDatLaiMatKhau'])->name('admin.dat-lai-mat-khau');
Route::post('/admin/dat-lai-mat-khau', [AuthController::class, 'datLaiMatKhau'])->name('admin.dat-lai-mat-khau.submit');

// Trang doi mat khau
Route::get('/admin/doi-mat-khau', [AuthController::class, 'showDoiMatKhau'])->name('admin.doi-mat-khau');
Route::post('/admin/doi-mat-khau', [AuthController::class, 'doiMatKhau'])->name('admin.doi-mat-khau.submit');


Route::get('/admin/cai-dat', function () { return view('admin_xem_truoc.cai-dat');});

// Lô hàng
Route::get('/admin/api/lo-hang', [LoHangApiController::class, 'index']);
Route::get('/admin/api/lo-hang/nha-cung-cap', [LoHangApiController::class, 'nhaCungCaps']);
Route::get('/admin/api/lo-hang/ton-kho', [LoHangApiController::class, 'tonKho']);
Route::get('/admin/api/lo-hang/ton-kho-list', [LoHangApiController::class, 'tonKhoList']);
Route::get('/admin/api/lo-hang/thong-ke', [LoHangApiController::class, 'thongKe']);
Route::get('/admin/api/lo-hang/canh-bao', [LoHangApiController::class, 'canhBao']);
Route::get('/admin/api/lo-hang/ton-kho-tong', [LoHangApiController::class, 'tonKhoTong']);
Route::get('/admin/api/lo-hang/{id}', [LoHangApiController::class, 'show']);
Route::post('/admin/api/lo-hang', [LoHangApiController::class, 'store']);
Route::put('/admin/api/lo-hang/{id}', [LoHangApiController::class, 'update']);
Route::delete('/admin/api/lo-hang/{id}', [LoHangApiController::class, 'destroy']);

// Nhà cung cấp API
Route::get('/admin/api/nha-cung-cap', [NhaCungCapApiController::class, 'index']);
Route::post('/admin/api/nha-cung-cap', [NhaCungCapApiController::class, 'store']);
Route::put('/admin/api/nha-cung-cap/{id}', [NhaCungCapApiController::class, 'update']);
Route::delete('/admin/api/nha-cung-cap/{id}', [NhaCungCapApiController::class, 'destroy']);
Route::get('/admin/api/nha-cung-cap/dropdown', [NhaCungCapApiController::class, 'dropdown']);

// Phiếu nhập - Import/Export Excel (đặt TRƯỚC route có {id} để tránh bị nhầm)
Route::get('/admin/api/phieu-nhap/download-template', [PhieuNhapApiController::class, 'downloadTemplate']);
Route::post('/admin/api/phieu-nhap/import', [PhieuNhapApiController::class, 'importExcel']);
Route::get('/admin/api/phieu-nhap/export', [PhieuNhapApiController::class, 'exportDanhSach']);
Route::get('/admin/api/phieu-nhap/{id}/export', [PhieuNhapApiController::class, 'exportChiTiet']);

// Phiếu nhập - CRUD
Route::get('/admin/api/phieu-nhap', [PhieuNhapApiController::class, 'index']);
Route::get('/admin/api/phieu-nhap/lo-hang', [PhieuNhapApiController::class, 'danhSachLoHang']);
Route::get('/admin/api/phieu-nhap/{id}', [PhieuNhapApiController::class, 'show']);
Route::post('/admin/api/phieu-nhap', [PhieuNhapApiController::class, 'store']);
Route::put('/admin/api/phieu-nhap/{id}', [PhieuNhapApiController::class, 'update']);
Route::delete('/admin/api/phieu-nhap/{id}', [PhieuNhapApiController::class, 'destroy']);

// Phiếu xuất - Import/Export Excel (đặt TRƯỚC route có {id} để tránh bị nhầm)
Route::get('/admin/api/phieu-xuat/download-template', [PhieuXuatApiController::class, 'downloadTemplate']);
Route::post('/admin/api/phieu-xuat/import', [PhieuXuatApiController::class, 'importExcel']);
Route::get('/admin/api/phieu-xuat/export', [PhieuXuatApiController::class, 'exportDanhSach']);
Route::get('/admin/api/phieu-xuat/{id}/export', [PhieuXuatApiController::class, 'exportChiTiet']);

// Phiếu xuất - CRUD
Route::get('/admin/api/phieu-xuat', [PhieuXuatApiController::class, 'index']);
Route::get('/admin/api/phieu-xuat/{id}', [PhieuXuatApiController::class, 'show']);
Route::post('/admin/api/phieu-xuat', [PhieuXuatApiController::class, 'store']);
Route::put('/admin/api/phieu-xuat/{id}', [PhieuXuatApiController::class, 'update']);
Route::delete('/admin/api/phieu-xuat/{id}', [PhieuXuatApiController::class, 'destroy']);

// ===== KIỂM KHO =====
// [TEST MODE] Đã tạm bỏ middleware 'permission:*' để test thử.
// Sau khi test xong, sẽ khôi phục lại các permission middleware.
Route::prefix('/admin/api/kiem-kho')->name('admin.api.kiem-kho.')
    ->middleware([AuthAdmin::class])
    ->group(function () {
        Route::get('/search', [KiemKhoApiController::class, 'searchItems']);
        Route::get('/draft', [KiemKhoApiController::class, 'getDraft']);
        Route::post('/draft', [KiemKhoApiController::class, 'storeDraft']);
        Route::post('/import-preview', [KiemKhoApiController::class, 'importPreview']);
        Route::post('/import-execute', [KiemKhoApiController::class, 'importExecute']);
        Route::get('/history', [KiemKhoApiController::class, 'history']);
        Route::get('/trash', [KiemKhoApiController::class, 'trash']);
        Route::post('/bulk-action', [KiemKhoApiController::class, 'bulkAction']);
        Route::post('/{id}/balance', [KiemKhoApiController::class, 'balanceInventory'])
            ->whereNumber('id');
        Route::post('/{id}/cancel', [KiemKhoApiController::class, 'cancel'])
            ->whereNumber('id');
        Route::post('/{id}/restore', [KiemKhoApiController::class, 'restore'])
            ->whereNumber('id');
        Route::put('/{id}', [KiemKhoApiController::class, 'updateDraft'])
            ->whereNumber('id');
        Route::delete('/{id}/force', [KiemKhoApiController::class, 'forceDelete'])
            ->whereNumber('id');
        Route::delete('/{id}', [KiemKhoApiController::class, 'softDelete'])
            ->whereNumber('id');
        Route::get('/{id}', [KiemKhoApiController::class, 'show'])
            ->whereNumber('id');
    });

// View (Blade) - [TEST MODE] không check permission
Route::get('/admin/kho-hang/kiem-kho', [KiemKhoController::class, 'index'])
    ->name('kiem-kho.create');
Route::get('/admin/kho-hang/kiem-kho/lich-su', [KiemKhoController::class, 'history'])
    ->name('kiem-kho.history');
Route::get('/admin/kho-hang/kiem-kho/thung-rac', [KiemKhoController::class, 'trash'])
    ->name('kiem-kho.trash');
Route::get('/admin/kho-hang/kiem-kho/{id}', [KiemKhoController::class, 'show'])
    ->whereNumber('id')
    ->name('kiem-kho.show');

Route::middleware([KTVaiTro::class])->group(function () {
    // Admin Routes - Preview
    Route::get('/admin/dashboard',[DashboardController::class, 'index'])->name('admin.dashboard');


    // API - phải đặt TRƯỚC san-pham/{id} để tránh bị match nhầm (KHÔNG bị chặn bởi KTVaiTro)
    Route::get('/admin/api/san-pham', [SanPhamApiController::class, 'index']);
    Route::get('/admin/api/san-pham/{id}', [SanPhamApiController::class, 'show']);
    Route::delete('/admin/api/san-pham/variant/{id}', [SanPhamApiController::class, 'destroyVariant']);
    Route::delete('/admin/api/san-pham/{id}/variants', [SanPhamApiController::class, 'destroyAllVariants']);
    Route::get('/admin/api/thuoc-tinh/con/{id}', [ThuocTinhApiController::class, 'getThuocTinhCon']);
    Route::get('/admin/api/thuoc-tinh/all-con', [ThuocTinhApiController::class, 'getAllThuocTinhCon']);
    Route::post('/admin/api/thuoc-tinh', [ThuocTinhApiController::class, 'store']);


    // Nha cung cap routes
    Route::get('/admin/kho-hang/nha-cung-cap', [NhaCungCapController::class, 'index'])->middleware('permission:quan_ly_nha_cung_cap');
    Route::post('/admin/kho-hang/nha-cung-cap', [NhaCungCapController::class, 'store'])->middleware('permission:quan_ly_nha_cung_cap');
    Route::get('/admin/kho-hang/nha-cung-cap/{id}/lich-su-giao-dich',  [NhaCungCapController::class, 'lichSuGiaoDich'])->middleware('permission:quan_ly_nha_cung_cap');
    Route::get('/admin/kho-hang/nha-cung-cap/{id}/edit', [NhaCungCapController::class, 'edit'])->middleware('permission:quan_ly_nha_cung_cap');
    Route::put('/admin/kho-hang/nha-cung-cap/{id}', [NhaCungCapController::class, 'update'])->middleware('permission:quan_ly_nha_cung_cap');
    Route::delete('/admin/kho-hang/nha-cung-cap/{id}', [NhaCungCapController::class, 'destroy'])->middleware('permission:quan_ly_nha_cung_cap');


    // Thùng rác nhà cung cấp
    Route::get('/admin/kho-hang/nha-cung-cap/thung-rac', [NhaCungCapController::class, 'trash'])->middleware('permission:quan_ly_nha_cung_cap');
    Route::post('/admin/kho-hang/nha-cung-cap/bulk-restore', [NhaCungCapController::class, 'bulkRestore'])->middleware('permission:quan_ly_nha_cung_cap');
    Route::delete('/admin/kho-hang/nha-cung-cap/bulk-force', [NhaCungCapController::class, 'bulkForceDelete'])->middleware('permission:quan_ly_nha_cung_cap');
    Route::post('/admin/kho-hang/nha-cung-cap/{id}/restore', [NhaCungCapController::class, 'restore'])->middleware('permission:quan_ly_nha_cung_cap');
    Route::delete('/admin/kho-hang/nha-cung-cap/{id}/force', [NhaCungCapController::class, 'forceDelete'])->middleware('permission:quan_ly_nha_cung_cap');


    // quản lý danh mục
    Route::get('quan-ly-danh-muc', [DanhMucSanPhamController::class, 'index'])->name('danh_muc.index')->middleware('permission:quan_ly_danh_muc');
    Route::post('quan-ly-danh-muc-store', [DanhMucSanPhamController::class, 'store'])->name('danh_muc.store')->middleware('permission:quan_ly_danh_muc');
    Route::get('quan-ly-danh-muc-edit/{id}', [DanhMucSanPhamController::class, 'edit'])->name('danh_muc.edit')->middleware('permission:quan_ly_danh_muc');
    Route::put('quan-ly-danh-muc-update/{id}', [DanhMucSanPhamController::class, 'update'])->name('danh_muc.update')->middleware('permission:quan_ly_danh_muc');
    Route::delete('quan-ly-danh-muc-delete/{id}', [DanhMucSanPhamController::class, 'destroy'])->name('danh_muc.destroy')->middleware('permission:quan_ly_danh_muc');


    //quản lý người dùng
    Route::get('/nguoi-dung', [NguoiDungController::class, 'index'])->name('nguoi-dung.index')->middleware('permission:quan_ly_nhan_su');
    Route::delete('nguoi-dung/{nguoiDung}', [NguoiDungController::class, 'destroy'])->name('nguoi-dung.destroy')->middleware('permission:quan_ly_nhan_su');
    Route::get('/nguoi-dung/create', [NguoiDungController::class, 'create'])->name('nguoi-dung.create')->middleware('permission:quan_ly_nhan_su');
    Route::post('/nguoi-dung', [NguoiDungController::class, 'store'])->name('nguoi-dung.store')->middleware('permission:quan_ly_nhan_su');
    Route::get('/nguoi-dung/{nguoiDung}', [NguoiDungController::class, 'show'])->name('nguoi-dung.show')->middleware('permission:quan_ly_nhan_su');
    Route::get('/nguoi-dung/{nguoiDung}/edit', [NguoiDungController::class, 'edit'])->name('nguoi-dung.edit')->middleware('permission:quan_ly_nhan_su');
    Route::put('/nguoi-dung/{nguoiDung}', [NguoiDungController::class, 'update'])->name('nguoi-dung.update')->middleware('permission:quan_ly_nhan_su');


    //Phân quyền người dùng
    Route::get('nguoi-dung-phan-quyen/{id_vai_tro}', [PhanQuyen::class, 'phanQuyen'])->name('nguoi-dung.phan-quyen')->middleware('permission:phan_quyen');
    Route::Post('nguoi-dung-phan-quyen/{id_vai_tro}', [PhanQuyen::class, 'capNhatPhanQuyen'])->name('admin.quyen.update')->middleware('permission:phan_quyen');


    //quản lý sản phẩm
    Route::get('/admin/san-pham', [SanPhamController::class, 'index'])->middleware('permission:quan_ly_san_pham')->name('san-pham.index');
    Route::post('/admin/san-pham', [SanPhamController::class, 'store'])->middleware('permission:quan_ly_san_pham');
    Route::post('/admin/san-pham/bulk-action', [SanPhamController::class, 'bulkAction'])->middleware('permission:sua_san_pham');
    Route::get('/admin/san-pham/trash', [SanPhamController::class, 'trash'])->middleware('permission:quan_ly_san_pham')->name('san-pham.trash');
    Route::get('/admin/san-pham/export', [SanPhamController::class, 'export'])->middleware('permission:quan_ly_san_pham');
    Route::get('/admin/san-pham/export-template', [SanPhamController::class, 'exportTemplate'])->middleware('permission:quan_ly_san_pham');
    Route::post('/admin/san-pham/import', [SanPhamController::class, 'import'])->middleware('permission:quan_ly_san_pham');
    Route::post('/admin/san-pham/{id}/restore', [SanPhamController::class, 'restore'])->middleware('permission:quan_ly_san_pham');
    Route::delete('/admin/san-pham/{id}/force', [SanPhamController::class, 'forceDelete'])->middleware('permission:quan_ly_san_pham');
    Route::get('/admin/san-pham/{id}/edit', [SanPhamController::class, 'edit'])->middleware('permission:quan_ly_san_pham');
    Route::put('/admin/san-pham/{id}', [SanPhamController::class, 'update'])->middleware('permission:quan_ly_san_pham');
    Route::delete('/admin/san-pham/{id}', [SanPhamController::class, 'destroy'])->middleware('permission:quan_ly_san_pham');
    Route::get('/admin/san-pham/{id}', [SanPhamController::class, 'show'])->middleware('permission:quan_ly_san_pham');


    Route::get('/admin/cai-dat/san-pham', [ThietLapSanPhamController::class, 'index']);
    Route::post('/admin/cai-dat/san-pham/don-vi', [ThietLapSanPhamController::class, 'storeDonVi']);
    Route::put('/admin/cai-dat/san-pham/don-vi/{id}', [ThietLapSanPhamController::class, 'updateDonVi']);
    Route::delete('/admin/cai-dat/san-pham/don-vi/{id}', [ThietLapSanPhamController::class, 'destroyDonVi']);
    Route::post('/admin/cai-dat/san-pham/thuoc-tinh', [ThietLapSanPhamController::class, 'storeThuocTinh']);
    Route::put('/admin/cai-dat/san-pham/thuoc-tinh/{id}', [ThietLapSanPhamController::class, 'updateThuocTinh']);
    Route::delete('/admin/cai-dat/san-pham/thuoc-tinh/{id}', [ThietLapSanPhamController::class, 'destroyThuocTinh']);


    // Quản lý ca làm việc
    Route::get('/admin/ca-lam-viec', [CaLamViecController::class, 'index'])->name('ca-lam-viec.index')->middleware('permission:quan_ly_ca_lam');
    Route::get('/admin/ca-lam-viec/create', [CaLamViecController::class, 'create'])->name('ca-lam-viec.create')->middleware('permission:quan_ly_ca_lam');
    Route::post('/admin/ca-lam-viec', [CaLamViecController::class, 'store'])->name('ca-lam-viec.store')->middleware('permission:quan_ly_ca_lam');
    Route::get('/admin/ca-lam-viec/{caLamViec}/edit', [CaLamViecController::class, 'edit'])->name('ca-lam-viec.edit')->middleware('permission:quan_ly_ca_lam');
    Route::put('/admin/ca-lam-viec/{caLamViec}', [CaLamViecController::class, 'update'])->name('ca-lam-viec.update')->middleware('permission:quan_ly_ca_lam');
    Route::delete('/admin/ca-lam-viec/{caLamViec}', [CaLamViecController::class, 'destroy'])->name('ca-lam-viec.destroy')->middleware('permission:quan_ly_ca_lam');

    Route::get('/admin/hoa-don', [HoaDonController::class, 'index'])->name('admin.hoa-don.index');


    // Đổi trả hàng
    Route::get('/admin/hoa-don/search-product', [HoaDonController::class, 'searchProduct'])->name('admin.hoa-don.search-product');
    Route::get('/admin/hoa-don/{id}/doi-tra', [HoaDonController::class, 'formDoiTra'])->name('admin.hoa-don.doi-tra');
    Route::post('/admin/hoa-don/{id}/doi-tra', [HoaDonController::class, 'xuLyDoiTra'])->name('admin.hoa-don.xu-ly-doi-tra');
    Route::get('/admin/hoa-don/{id}', [HoaDonController::class, 'show'])->name('admin.hoa-don.show');
    Route::post('/admin/hoa-don/{id}/huy', [HoaDonController::class, 'huy'])->name('admin.hoa-don.huy');

    // Trang kho hang
    Route::get('/admin/kho-hang', function () {
        $nhaCungCaps = NhaCungCap::orderBy('id', 'asc')->get();
        return view('admin_xem_truoc.kho-hang.index', compact('nhaCungCaps'));
    });
    Route::get('/admin/kho-hang/lo-hang', function () { return view('admin_xem_truoc.warehouse.lo-hang');});
    Route::get('/admin/kho-hang/phieu-nhap', function () { return view('admin_xem_truoc.warehouse.phieu-nhap');});
    Route::get('/admin/kho-hang/phieu-xuat', function () { return view('admin_xem_truoc.warehouse.phieu-xuat');});


    // Quan ly khach hang
    Route::get('/admin/khach-hang', [KhachHangController::class, 'index'])->name('khach-hang.index')->middleware('permission:quan_ly_khach_hang');
    Route::get('/admin/khach-hang/create', [KhachHangController::class, 'create'])->name('khach-hang.create')->middleware('permission:quan_ly_khach_hang');
    Route::post('/admin/khach-hang', [KhachHangController::class, 'store'])->name('khach-hang.store')->middleware('permission:quan_ly_khach_hang');

    // ===== THÙNG RÁC =====
    Route::get('/admin/khach-hang/thung-rac', [KhachHangController::class, 'trash'])->name('khach-hang.trash')->middleware('permission:quan_ly_khach_hang');
    Route::put('/admin/khach-hang/{id}/restore', [KhachHangController::class, 'restore'])->name('khach-hang.restore')->middleware('permission:quan_ly_khach_hang');
    Route::delete('/admin/khach-hang/{id}/force-delete', [KhachHangController::class, 'forceDelete'])->name('khach-hang.force-delete')->middleware('permission:quan_ly_khach_hang');

    // ===== ROUTE CÓ {khachHang} PHẢI ĐỂ CUỐI =====
    Route::get('/admin/khach-hang/{khachHang}', [KhachHangController::class, 'show'])->name('khach-hang.show')->middleware('permission:quan_ly_khach_hang');
    Route::get('/admin/khach-hang/{khachHang}/edit', [KhachHangController::class, 'edit'])->name('khach-hang.edit')->middleware('permission:quan_ly_khach_hang');
    Route::put('/admin/khach-hang/{khachHang}', [KhachHangController::class, 'update'])->name('khach-hang.update')->middleware('permission:quan_ly_khach_hang');
    Route::delete('/admin/khach-hang/{khachHang}', [KhachHangController::class, 'destroy'])->name('khach-hang.destroy')->middleware('permission:quan_ly_khach_hang');


    // Trang khuyen mai
    Route::get('/admin/khuyen-mai', [KhuyenMaiController::class, 'index'])->name('khuyen-mai.index')->middleware('permission:quan_ly_khuyen_mai');
    Route::post('/admin/khuyen-mai', [KhuyenMaiController::class, 'store'])->name('khuyen-mai.store')->middleware('permission:quan_ly_khuyen_mai');
    Route::get('/admin/khuyen-mai/thung-rac', [KhuyenMaiController::class, 'trash'])->name('khuyen-mai.trash')->middleware('permission:quan_ly_khuyen_mai');
    Route::post('/admin/khuyen-mai/{id}/toggle', [KhuyenMaiController::class, 'toggle'])->name('khuyen-mai.toggle')->middleware('permission:quan_ly_khuyen_mai');
    Route::post('/admin/khuyen-mai/{id}/ajax-toggle', [KhuyenMaiController::class, 'ajaxToggle'])->name('khuyen-mai.ajaxToggle')->middleware('permission:quan_ly_khuyen_mai');
    Route::post('/admin/khuyen-mai/{id}/restore', [KhuyenMaiController::class, 'restore'])->name('khuyen-mai.restore')->middleware('permission:quan_ly_khuyen_mai');
    Route::delete('/admin/khuyen-mai/{id}/force', [KhuyenMaiController::class, 'forceDelete'])->name('khuyen-mai.forceDelete')->middleware('permission:quan_ly_khuyen_mai');
    Route::get('/admin/khuyen-mai/{id}/edit', [KhuyenMaiController::class, 'edit'])->name('khuyen-mai.edit')->middleware('permission:quan_ly_khuyen_mai');
    Route::put('/admin/khuyen-mai/{id}', [KhuyenMaiController::class, 'update'])->name('khuyen-mai.update')->middleware('permission:quan_ly_khuyen_mai');
    Route::delete('/admin/khuyen-mai/{id}', [KhuyenMaiController::class, 'destroy'])->name('khuyen-mai.destroy')->middleware('permission:quan_ly_khuyen_mai');

    // / chia ca làm việc
    Route::get('/admin/chia-ca-lam-viec', [ChiaCaController::class, 'index'])->name('chia-ca-lam-viec.index')->middleware('permission:quan_ly_ca_lam');
    Route::get('/admin/chia-ca-lam-viec/create', [ChiaCaController::class, 'create'])->name('chia-ca-lam-viec.create')->middleware('permission:quan_ly_ca_lam');
    Route::post('/admin/chia-ca-lam-viec', [ChiaCaController::class, 'store'])->name('chia-ca-lam-viec.store')->middleware('permission:quan_ly_ca_lam');
    Route::get('/admin/chia-ca-lam-viec/export', [ChiaCaController::class, 'export'])->name('chia-ca-lam-viec.export')->middleware('permission:quan_ly_ca_lam');
    Route::delete('/admin/chia-ca-lam-viec/xoa-o-lich', [ChiaCaController::class, 'destroyCell'])->name('chia-ca-lam-viec.destroy-cell')->middleware('permission:quan_ly_ca_lam');
    Route::get('/admin/chia-ca-lam-viec/{chiaCaLamViec}/edit', [ChiaCaController::class, 'edit'])->name('chia-ca-lam-viec.edit')->middleware('permission:quan_ly_ca_lam');
    Route::put('/admin/chia-ca-lam-viec/{chiaCaLamViec}', [ChiaCaController::class, 'update'])->name('chia-ca-lam-viec.update')->middleware('permission:quan_ly_ca_lam');
    Route::delete('/admin/chia-ca-lam-viec/{chiaCaLamViec}', [ChiaCaController::class, 'destroy'])->name('chia-ca-lam-viec.destroy')->middleware('permission:quan_ly_ca_lam');

     // quản lý ca làm hiện tại 
        Route::get('/ca-lam',[CaLam::class, 'index'])->name('ca-lam.index')->middleware('permission:quan_ly_ca_lam');
        Route::get('/chi-tiet-hoa-don/{id_hoadon}',[CaLam::class, 'show'])->name('chi-tiet-hoa-don.show')->middleware('permission:quan_ly_ca_lam');
        //lịch sử ca làm
        Route::get('/lich-su-ca-lam-viec',[LichSuCaLam::class, 'index'])->name('lich-su-ca-lam-viec.index')->middleware('permission:quan_ly_ca_lam');
        Route::get('/lich-su-ca-lam-viec-cac-ca/{ngay}',[LichSuCaLam::class, 'cacCa'])->name('lich-su-ngay-lam-viec.cac-ca-lam')->middleware('permission:quan_ly_ca_lam');
        Route::get('/lich-su-ca-lam-chi_tiet_ca_lam/{id_ca}/{ngay}',[LichSuCaLam::class, 'chi_tiet_ca'])->name('lich-su-ngay-lam-viec.chi_tiet_ca_lam')->middleware('permission:quan_ly_ca_lam');
        Route::get('/lich-su-ca-lam-chi_tiet-hoa-don/{id_hoaDon}/{ngay}',[LichSuCaLam::class, 'chi_tiet_hoa_don'])->name('lich-su-ca-lam-chi-tiet-hoa-don.show')->middleware('permission:ca_lam');
        //giao ca
        Route::get('/lich-su-ca-lam-giao-ca/{id_ca}/{ngay}',[LichSuCaLam::class, 'tao_giao_ca'])->name('lich-su-ca-lam-giao-ca.tao-giao-ca')->middleware('permission:quan_ly_ca_lam');
        Route::post('/lich-su-ca-lam-giao-ca',[LichSuCaLam::class, 'giao_ca_store'])->name('giao-ca.store')->middleware('permission:quan_ly_ca_lam');
        Route::get('/lich-su-ca-lam-giao-ca-chi-tiet/{id}',[LichSuCaLam::class, 'giao_ca_chi_tiet'])->name('giao-ca.chi-tiet')->middleware('permission:quan_ly_ca_lam');
        Route::get('/lich-su-ca-lam-giao-ca-sua/{id}',[LichSuCaLam::class, 'sua_giao_ca'])->name('giao-ca.sua')->middleware('permission:ca_lam');
        Route::put('/lich-su-ca-lam-giao-cap-nhat/{id}',[LichSuCaLam::class, 'cap_nhat_giao_ca'])->name('giao-ca.cap-nhat')->middleware('permission:quan_ly_ca_lam');
        Route::put('/lich-su-ca-lam-giao-ca/{id}/xac-nhan', [LichSuCaLam::class, 'xac_nhan_giao_ca'])->name('giao-ca.xac-nhan')->middleware('permission:quan_ly_ca_lam');
        Route::put('/lich-su-ca-lam-giao-ca/{id}/tu-choi', [LichSuCaLam::class, 'tu_choi_giao_ca'])->name('giao-ca.tu-choi')->middleware('permission:quan_ly_ca_lam');

    //điểm danh
    Route::get('/chi-tiet-diem-danh-nhan-vien/{id_chia_ca_lam_viec}/{id_nv}', [DiemDanhNhanVien::class, 'chi_tiet_diem_danh'])->name('lich-su-ca-lam.chi_tiet_diem_danh')->middleware('permission:quan_ly_ca_lam');
    Route::get('/tao-diem-danh-nhan-vien/{id_chia_ca_lam_viec}/{id_nv}', [DiemDanhNhanVien::class, 'diem_danh_bu'])->name('lich-su-ca-lam.tao-diem-danh-bu')->middleware('permission:quan_ly_ca_lam');
    Route::post('/luu-diem-danh-nhan-vien', [DiemDanhNhanVien::class, 'luu_diem_danh_bu'])->name('diem-danh.luu-diem-danh-bu')->middleware('permission:quan_ly_ca_lam');
    Route::put('/cap-nhat-diem-danh-nhan-vien/{id}', [DiemDanhNhanVien::class, 'cap_nhat_diem_danh'])->name('diem-danh.cap-nhat')->middleware('permission:quan_ly_ca_lam');


    // Quản lý điểm danh
    Route::get('/admin/diem-danh', [DiemDanhController::class, 'index'])->name('diem-danh.index');
    Route::post('/admin/diem-danh/{chiaCaLamViec}/xac-nhan', [DiemDanhController::class, 'xacNhanDiemDanh'])->name('diem-danh.xac-nhan');
    Route::post('/admin/diem-danh/{chiaCaLamViec}/ket-thuc-ca', [DiemDanhController::class, 'ketThucCa'])->name('diem-danh.ket-thuc-ca');
    Route::delete('/admin/diem-danh/{diemDanh}', [DiemDanhController::class, 'huyDiemDanh'])->name('diem-danh.destroy');
    Route::get('/admin/diem-danh/lich-su', [DiemDanhController::class, 'lichSu'])->name('diem-danh.lich-su');
    Route::get('/admin/diem-danh/thong-ke', [DiemDanhController::class, 'thongKe'])->name('diem-danh.thong-ke');


    // Thiết lập lương
    Route::get('/admin/thiet-lap-luong', [ThietLapLuongController::class, 'index'])->name('thiet-lap-luong.index')->middleware('permission:xem_thiet_lap_luong');
    Route::get('/admin/thiet-lap-luong/create', [ThietLapLuongController::class, 'create'])->name('thiet-lap-luong.create')->middleware('permission:them_thiet_lap_luong');
    Route::post('/admin/thiet-lap-luong', [ThietLapLuongController::class, 'store'])->name('thiet-lap-luong.store')->middleware('permission:them_thiet_lap_luong');
    Route::get('/admin/thiet-lap-luong/{thietLapLuong}', [ThietLapLuongController::class, 'show'])->name('thiet-lap-luong.show')->middleware('permission:xem_thiet_lap_luong');
    Route::get('/admin/thiet-lap-luong/{thietLapLuong}/edit', [ThietLapLuongController::class, 'edit'])->name('thiet-lap-luong.edit')->middleware('permission:sua_thiet_lap_luong');
    Route::put('/admin/thiet-lap-luong/{thietLapLuong}', [ThietLapLuongController::class, 'update'])->name('thiet-lap-luong.update')->middleware('permission:sua_thiet_lap_luong');
    Route::delete('/admin/thiet-lap-luong/{thietLapLuong}', [ThietLapLuongController::class, 'destroy'])->name('thiet-lap-luong.destroy')->middleware('permission:xoa_thiet_lap_luong');


    // Bảng lương
    Route::get('/admin/bang-luong', [BangLuongController::class, 'index'])->name('bang-luong.index')->middleware('permission:xem_bang_luong');
    Route::get('/admin/bang-luong/create', [BangLuongController::class, 'create'])->name('bang-luong.create')->middleware('permission:them_bang_luong');
    Route::post('/admin/bang-luong', [BangLuongController::class, 'store'])->name('bang-luong.store')->middleware('permission:them_bang_luong');
    Route::get('/admin/bang-luong/{bangLuong}', [BangLuongController::class, 'show'])->name('bang-luong.show')->middleware('permission:xem_bang_luong');
    Route::get('/admin/bang-luong/{bangLuong}/edit', [BangLuongController::class, 'edit'])->name('bang-luong.edit')->middleware('permission:sua_bang_luong');
    Route::put('/admin/bang-luong/{bangLuong}', [BangLuongController::class, 'update'])->name('bang-luong.update')->middleware('permission:sua_bang_luong');
    Route::delete('/admin/bang-luong/{bangLuong}', [BangLuongController::class, 'destroy'])->name('bang-luong.destroy')->middleware('permission:xoa_bang_luong');
    Route::post('/admin/bang-luong/{bangLuong}/tinh-luong', [BangLuongController::class, 'tinhLuong'])->name('bang-luong.tinh-luong')->middleware('permission:xem_bang_luong');

    // Phiếu lương
    Route::get('/admin/phieu-luong', [PhieuLuongController::class, 'index'])->name('phieu-luong.index')->middleware('permission:xem_phieu_luong');
    Route::get('/admin/phieu-luong/{phieuLuong}', [PhieuLuongController::class, 'show'])->name('phieu-luong.show')->middleware('permission:xem_phieu_luong');
    Route::get('/admin/phieu-luong/{phieuLuong}/edit', [PhieuLuongController::class, 'edit'])->name('phieu-luong.edit')->middleware('permission:sua_phieu_luong');
    Route::put('/admin/phieu-luong/{phieuLuong}', [PhieuLuongController::class, 'update'])->name('phieu-luong.update')->middleware('permission:sua_phieu_luong');


    /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    // Routes bán hàng
    Route::get('/dashboard', [NhanVienController::class, 'index'])->name('nhan-vien.dashboard')->middleware('permission:ban_hang');
    Route::get('/ban-hang', [NhanVienController::class, 'banHang'])->name('nhan-vien.ban-hang')->middleware('permission:ban_hang');
    Route::get('/hoa-don', [NhanVienController::class, 'hoaDon'])->name('nhan-vien.hoa-don')->middleware('permission:ban_hang');
    // AJAX tìm kiếm sản phẩm cho chức năng đổi hàng (nhân viên)
    Route::get('/hoa-don/search-product', [HoaDonController::class, 'searchProduct'])->name('nhan-vien.hoa-don.search-product')->middleware('permission:ban_hang');
    Route::get('/san-pham', [NhanVienController::class, 'sanPham'])->name('nhan-vien.san-pham')->middleware('permission:ban_hang');
    // khách hàng 
    Route::get('/ban-hang/san-pham', [NhanVienController::class, 'laySanPham'])->name('nhan-vien.ban-hang.san-pham')->middleware('permission:ban_hang');
    Route::get('/ban-hang/danh-muc', [NhanVienController::class, 'layDanhMuc'])->name('nhan-vien.ban-hang.danh-muc')->middleware('permission:ban_hang');
    Route::post('/ban-hang/thanh-toan', [NhanVienController::class, 'thanhToan'])->name('nhan-vien.ban-hang.thanh-toan')->middleware('permission:ban_hang');
    Route::get('/hoa-don', [NhanVienController::class, 'hoaDon'])->name('nhan-vien.hoa-don')->middleware('permission:ban_hang');
    Route::get('/hoa-don/{id}', [NhanVienController::class, 'chiTietHoaDon'])->name('nhan-vien.hoa-don.chi-tiet')->middleware('permission:ban_hang');
    Route::get('/hoa-don/{id}/in', [NhanVienController::class, 'inHoaDon'])->name('nhan-vien.hoa-don.in')->middleware('permission:ban_hang');
    // Đổi / Trả hàng - nhân viên uses admin logic via wrapper methods
    Route::get('/hoa-don/{id}/doi-tra', [NhanVienController::class, 'formDoiTra'])->name('nhan-vien.hoa-don.doi-tra')->middleware('permission:ban_hang');
    Route::post('/hoa-don/{id}/doi-tra', [NhanVienController::class, 'xuLyDoiTraNhanVien'])->name('nhan-vien.hoa-don.xu-ly-doi-tra')->middleware('permission:ban_hang');
    //   Route::post('/hoa-don/{id}/huy', [NhanVienController::class, 'huyHoaDon'])
    //   ->name('nhan-vien.hoa-don.huy');
    Route::get('/ban-hang/khach-hang', [NhanVienController::class, 'layKhachHang'])->name('nhan-vien.ban-hang.khach-hang')->middleware('permission:ban_hang');
    Route::get('/ban-hang/khuyen-mai', [NhanVienController::class, 'layKhuyenMai'])->name('nhan-vien.ban-hang.khuyen-mai')->middleware('permission:ban_hang');

    Route::get('/khach-hang', [NhanVienKhachHangController::class, 'index'])->name('nhan-vien.khach-hang.index')->middleware('permission:ban_hang');
    Route::get('/khach-hang/create', [NhanVienKhachHangController::class, 'create'])->name('nhan-vien.khach-hang.create')->middleware('permission:ban_hang');
    Route::post('/khach-hang/them-nhanh', [NhanVienKhachHangController::class, 'themNhanh'])->name('nhan-vien.khach-hang.them-nhanh')->middleware('permission:ban_hang');
    Route::post('/khach-hang', [NhanVienKhachHangController::class, 'store'])->name('nhan-vien.khach-hang.store')->middleware('permission:ban_hang');
    Route::get('/khach-hang/{khachHang}', [NhanVienKhachHangController::class, 'show'])->name('nhan-vien.khach-hang.show')->middleware('permission:ban_hang');
    Route::get('/khach-hang/{khachHang}/edit-phone', [NhanVienKhachHangController::class, 'editPhone'])->name('nhan-vien.khach-hang.edit_phone')->middleware('permission:ban_hang');
    Route::put('/khach-hang/{khachHang}/update-phone', [KhachHangController::class, 'updatePhone'])->name('nhan-vien.khach-hang.update_phone')->middleware('permission:ban_hang');
}); //het router admin



// Routes nhân viên (Preview)
Route::middleware([NhanVien::class])->group(function () {
    route::get('/trang-chu', [TrangChuController::class, 'trang_chu'])->name('nhan-vien.trang-chu');
    Route::get('/lich-lam-viec', [NhanVienNhanVienController::class, 'lichLamViec'])->name('nhan-vien.lich');
    Route::get('/lich-lam-viec/tuan', [NhanVienNhanVienController::class, 'lichLamViecTuan'])->name('nhan-vien.lich-tuan');
    Route::get('/cham-cong', [NhanVienDiemDanhController::class, 'index'])->name('nhan-vien.cham-cong');
    Route::get('/ho-so', [NhanVienNhanVienController::class, 'hoSo'])->name('nhan-vien.ho-so');
    Route::post('/diem-danh/vao-ca', [NhanVienDiemDanhController::class, 'vaoCa'])->name('nhan-vien.diem-danh.vao-ca');
    Route::post('/diem-danh/ket-thuc-ca', [NhanVienDiemDanhController::class, 'ketThucCa'])->name('nhan-vien.diem-danh.ket-thuc-ca');
    Route::post('/ho-so/doi-mat-khau', [NhanVienNhanVienController::class, 'doiMatKhau'])->name('nhan-vien.ho-so.doi-mat-khau');
});

// PayOS payment routes
Route::match(['get', 'post'], '/payos/create', [PayOSController::class, 'createPayment'])->name('payos.create');
Route::get('/payos/return', [PayOSController::class, 'return'])->name('payos.return');
Route::get('/payos/cancel', [PayOSController::class, 'cancel'])->name('payos.cancel');
Route::match(['get', 'post'], '/payos/webhook', [PayOSController::class, 'webhook'])
    ->name('payos.webhook')
    ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrf::class]);

