<?php

use App\Http\Controllers\BanHang\PayOSController;
use App\Http\Controllers\admin\AuthController;
use App\Http\Controllers\ban_hang\NhanVienController;
use App\Http\Controllers\Admin\DanhMuc\DanhMucSanPhamController;
use App\Http\Controllers\Admin\SanPham\SanPhamController;
use App\Http\Controllers\admin\NhanSu\CaLamViecController;
use App\Http\Controllers\admin\NhanSu\ChiaCaController;
use App\Http\Controllers\admin\NhanSu\NguoiDungController;
use App\Http\Controllers\admin\KhachHang\KhachHangController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\Api\LoHangApiController;
use App\Http\Controllers\Admin\Api\NhaCungCapApiController;
use App\Http\Controllers\Admin\Api\PhieuNhapApiController;
use App\Http\Controllers\Admin\Api\PhieuXuatApiController;
use App\Http\Controllers\Admin\Api\KiemKhoApiController;
use App\Http\Controllers\Admin\KiemKho\KiemKhoController;
use App\Http\Controllers\admin\KhoHang\NhaCungCapController;
use App\Http\Controllers\admin\KhoHang\HangLoiController;
use App\Http\Controllers\admin\KhoHang\PhieuNhapController;
use App\Http\Controllers\admin\KhoHang\PhieuXuatController;
use App\Http\Controllers\admin\KhoHang\KhoHangController;
use App\Http\Controllers\admin\Api\ThuocTinhApiController;
use App\Http\Controllers\admin\Api\SanPhamApiController;
use App\Http\Controllers\admin\CaiDat\ThietLapSanPhamController;
use App\Http\Controllers\admin\CaLam\CaLam;
use App\Http\Controllers\admin\CaLam\LichSuCaLam;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\admin\KhuyenMaiController;
use App\Http\Controllers\admin\PhanQuyenDong\PhanQuyen;
use App\Http\Controllers\ban_hang\KhachHangController as NhanVienKhachHangController;
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
Route::get('/admin/doi-mat-khau', [AuthController::class, 'showDoiMatKhau'])->name('admin.doi-mat-khau')->middleware(KTVaiTro::class);
Route::post('/admin/doi-mat-khau', [AuthController::class, 'doiMatKhau'])->name('admin.doi-mat-khau.submit')->middleware(KTVaiTro::class);


Route::get('/admin/cai-dat', function () {
    return view('admin_xem_truoc.cai-dat');
})->middleware([KTVaiTro::class, 'permission:quan_ly_cai_dat']);

// Các API quản trị phải đi qua middleware xác thực và permission.
Route::middleware([KTVaiTro::class])->group(function () {
    // Lô hàng / tồn kho
    Route::get('/admin/api/lo-hang', [LoHangApiController::class, 'index'])->middleware('permission:quan_ly_kho_hang|xem_kho_hang');
    Route::get('/admin/api/lo-hang/nha-cung-cap', [LoHangApiController::class, 'nhaCungCaps'])->middleware('permission:quan_ly_kho_hang|xem_kho_hang');
    Route::get('/admin/api/lo-hang/ton-kho', [LoHangApiController::class, 'tonKho'])->middleware('permission:quan_ly_kho_hang|xem_kho_hang');
    Route::get('/admin/api/lo-hang/ton-kho-list', [LoHangApiController::class, 'tonKhoList'])->middleware('permission:quan_ly_kho_hang|xem_kho_hang');
    Route::get('/admin/api/lo-hang/thong-ke', [LoHangApiController::class, 'thongKe'])->middleware('permission:quan_ly_kho_hang|xem_kho_hang');
    Route::get('/admin/api/lo-hang/canh-bao', [LoHangApiController::class, 'canhBao'])->middleware('permission:quan_ly_kho_hang|xem_kho_hang');
    Route::get('/admin/api/lo-hang/ton-kho-tong', [LoHangApiController::class, 'tonKhoTong'])->middleware('permission:quan_ly_kho_hang|xem_kho_hang');
    Route::get('/admin/api/lo-hang/{id}', [LoHangApiController::class, 'show'])->middleware('permission:quan_ly_kho_hang|xem_kho_hang');
    Route::post('/admin/api/lo-hang', [LoHangApiController::class, 'store'])->middleware('permission:nhap_hang');
    Route::put('/admin/api/lo-hang/{id}', [LoHangApiController::class, 'update'])->middleware('permission:nhap_hang');
    Route::delete('/admin/api/lo-hang/{id}', [LoHangApiController::class, 'destroy'])->middleware('permission:xoa_lo_hang');

    // Nhà cung cấp API (Trưởng ca mặc định không có quyền này)
    Route::get('/admin/api/nha-cung-cap', [NhaCungCapApiController::class, 'index'])->middleware('permission:quan_ly_nha_cung_cap');
    Route::post('/admin/api/nha-cung-cap', [NhaCungCapApiController::class, 'store'])->middleware('permission:quan_ly_nha_cung_cap');
    Route::put('/admin/api/nha-cung-cap/{id}', [NhaCungCapApiController::class, 'update'])->middleware('permission:quan_ly_nha_cung_cap');
    Route::delete('/admin/api/nha-cung-cap/{id}', [NhaCungCapApiController::class, 'destroy'])->middleware('permission:quan_ly_nha_cung_cap');
    Route::get('/admin/api/nha-cung-cap/{id}', [NhaCungCapApiController::class, 'show'])->middleware('permission:quan_ly_nha_cung_cap');
    Route::get('/admin/api/nha-cung-cap/dropdown', [NhaCungCapApiController::class, 'dropdown'])->middleware('permission:quan_ly_nha_cung_cap');

    // Phiếu nhập
    Route::get('/admin/api/phieu-nhap/download-template', [PhieuNhapApiController::class, 'downloadTemplate'])->middleware('permission:nhap_hang');
    Route::post('/admin/api/phieu-nhap/import', [PhieuNhapApiController::class, 'importExcel'])->middleware('permission:nhap_hang');
    Route::get('/admin/api/phieu-nhap/export', [PhieuNhapApiController::class, 'exportDanhSach'])->middleware('permission:quan_ly_kho_hang|xem_kho_hang');
    Route::get('/admin/api/phieu-nhap/{id}/export', [PhieuNhapApiController::class, 'exportChiTiet'])->middleware('permission:quan_ly_kho_hang|xem_kho_hang');
    Route::get('/admin/api/phieu-nhap', [PhieuNhapApiController::class, 'index'])->middleware('permission:quan_ly_kho_hang|xem_kho_hang');
    Route::get('/admin/api/phieu-nhap/lo-hang', [PhieuNhapApiController::class, 'danhSachLoHang'])->middleware('permission:nhap_hang');
    Route::get('/admin/api/phieu-nhap/{id}', [PhieuNhapApiController::class, 'show'])->middleware('permission:quan_ly_kho_hang|xem_kho_hang');
    Route::post('/admin/api/phieu-nhap', [PhieuNhapApiController::class, 'store'])->middleware('permission:nhap_hang');
    Route::put('/admin/api/phieu-nhap/{id}', [PhieuNhapApiController::class, 'update'])->middleware('permission:nhap_hang');
    Route::delete('/admin/api/phieu-nhap/{id}', [PhieuNhapApiController::class, 'destroy'])->middleware('permission:xoa_phieu_nhap');

    // Phiếu xuất
    Route::get('/admin/api/phieu-xuat/export', [PhieuXuatApiController::class, 'exportDanhSach'])->middleware('permission:quan_ly_kho_hang|xem_kho_hang');
    Route::get('/admin/api/phieu-xuat/lo-hang', [PhieuXuatApiController::class, 'danhSachLoHang'])->middleware('permission:xuat_hang');
    Route::get('/admin/api/phieu-xuat/{id}/export', [PhieuXuatApiController::class, 'exportChiTiet'])->middleware('permission:quan_ly_kho_hang|xem_kho_hang');
    Route::get('/admin/api/phieu-xuat', [PhieuXuatApiController::class, 'index'])->middleware('permission:quan_ly_kho_hang|xem_kho_hang');
    Route::get('/admin/api/phieu-xuat/{id}', [PhieuXuatApiController::class, 'show'])->middleware('permission:quan_ly_kho_hang|xem_kho_hang');
    Route::post('/admin/api/phieu-xuat', [PhieuXuatApiController::class, 'store'])->middleware('permission:xuat_hang');
    Route::put('/admin/api/phieu-xuat/{id}', [PhieuXuatApiController::class, 'update'])->middleware('permission:xuat_hang');
    Route::delete('/admin/api/phieu-xuat/{id}', [PhieuXuatApiController::class, 'destroy'])->middleware('permission:xoa_phieu_xuat');
});

// Trang tạo phiếu nhập (chuyển từ modal sang trang riêng)
Route::middleware([KTVaiTro::class])->group(function () {
    // Admin Routes - Preview
    Route::get('/admin/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard')->middleware('permission:xem_dashboard');


    // API - phải đặt TRƯỚC san-pham/{id} để tránh bị match nhầm (KHÔNG bị chặn bởi KTVaiTro)
    Route::get('/admin/api/san-pham', [SanPhamApiController::class, 'index'])->middleware('permission:quan_ly_san_pham|xem_san_pham|them_san_pham|sua_san_pham');
    Route::get('/admin/api/san-pham/{id}/thong-ke', [SanPhamApiController::class, 'thongKe'])->middleware('permission:quan_ly_san_pham|xem_san_pham');
    Route::get('/admin/api/san-pham/{id}/sales-trend', [SanPhamApiController::class, 'salesTrend'])->middleware('permission:quan_ly_san_pham|xem_san_pham');
    Route::get('/admin/api/san-pham/{id}', [SanPhamApiController::class, 'show'])->middleware('permission:quan_ly_san_pham|xem_san_pham');
    Route::delete('/admin/api/san-pham/variant/{id}', [SanPhamApiController::class, 'destroyVariant'])->middleware('permission:xoa_san_pham');
    Route::delete('/admin/api/san-pham/{id}/variants', [SanPhamApiController::class, 'destroyAllVariants'])->middleware('permission:xoa_san_pham');
    Route::get('/admin/api/thuoc-tinh/con/{id}', [ThuocTinhApiController::class, 'getThuocTinhCon'])->middleware('permission:quan_ly_san_pham|xem_san_pham|sua_san_pham');
    Route::get('/admin/api/thuoc-tinh/all-con', [ThuocTinhApiController::class, 'getAllThuocTinhCon'])->middleware('permission:quan_ly_san_pham|xem_san_pham|sua_san_pham');
    Route::post('/admin/api/thuoc-tinh', [ThuocTinhApiController::class, 'store'])->middleware('permission:sua_san_pham');


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
    Route::get('quan-ly-danh-muc', [DanhMucSanPhamController::class, 'index'])->name('danh_muc.index')->middleware('permission:quan_ly_danh_muc|xem_danh_muc');
    Route::post('quan-ly-danh-muc-store', [DanhMucSanPhamController::class, 'store'])->name('danh_muc.store')->middleware('permission:them_danh_muc');
    Route::get('quan-ly-danh-muc-edit/{id}', [DanhMucSanPhamController::class, 'edit'])->name('danh_muc.edit')->middleware('permission:sua_danh_muc');
    Route::put('quan-ly-danh-muc-update/{id}', [DanhMucSanPhamController::class, 'update'])->name('danh_muc.update')->middleware('permission:sua_danh_muc');
    Route::delete('quan-ly-danh-muc-delete/{id}', [DanhMucSanPhamController::class, 'destroy'])->name('danh_muc.destroy')->middleware('permission:xoa_danh_muc');
    Route::get('quan-ly-danh-muc-san-pham/{id}', [DanhMucSanPhamController::class, 'sanPhamTheoDanhMuc'])->name('danh_muc.san_pham')->middleware('permission:quan_ly_danh_muc|xem_danh_muc');


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

    // #region agent log
    Route::get('debug-truong-ca-permissions', function() {
        $truongCa = \App\Models\VaiTro::find(2);
        $allPermissions = $truongCa->quyens()->get();
        $nhanSu = \App\Models\Quyen::where('ma_quyen', 'quan_ly_nhan_su')->first();
        return response()->json([
            'vai_tro' => $truongCa->ten_vai_tro,
            'total_permissions' => $allPermissions->count(),
            'permission_ids' => $truongCa->quyens()->pluck('id_quyen')->toArray(),
            'all_permissions' => $allPermissions->map(fn($q) => ['id' => $q->id, 'ma' => $q->ma_quyen, 'ten' => $q->ten_quyen]),
            'nhan_su_quyen_id' => $nhanSu->id,
            'has_nhan_su' => $allPermissions->contains('id', $nhanSu->id),
        ]);
    });
    // #endregion


    //quản lý sản phẩm
    Route::get('/admin/san-pham', [SanPhamController::class, 'index'])->middleware('permission:quan_ly_san_pham|xem_san_pham|them_san_pham|sua_san_pham')->name('san-pham.index');
    Route::get('/admin/san-pham/export', [SanPhamController::class, 'export'])
        ->middleware('permission:quan_ly_san_pham|xem_san_pham')
        ->name('san-pham.export');
    Route::get('/admin/san-pham/export-template', [SanPhamController::class, 'exportTemplate'])
        ->middleware('permission:them_san_pham')
        ->name('san-pham.export-template');
    Route::get('/admin/san-pham/create', [SanPhamController::class, 'create'])->middleware('permission:them_san_pham')->name('san-pham.create');
    Route::post('/admin/san-pham', [SanPhamController::class, 'store'])->middleware('permission:them_san_pham')->name('san-pham.store');
    Route::post('/admin/san-pham/bulk-action', [SanPhamController::class, 'bulkAction'])->middleware('permission:sua_san_pham|xoa_san_pham');
    Route::get('/admin/san-pham/trash', [SanPhamController::class, 'trash'])->middleware('permission:xoa_san_pham')->name('san-pham.trash');
    Route::post('/admin/san-pham/bulk-restore', [SanPhamController::class, 'bulkRestore'])->middleware('permission:xoa_san_pham');
    Route::delete('/admin/san-pham/bulk-force', [SanPhamController::class, 'bulkForceDelete'])->middleware('permission:xoa_san_pham');
    Route::get('/admin/san-pham/{id}/constraints', [SanPhamController::class, 'getConstraints'])->middleware('permission:xoa_san_pham');
    Route::post('/admin/san-pham/import', [SanPhamController::class, 'import'])
        ->middleware('permission:them_san_pham')
        ->name('san-pham.import');
    Route::post('/admin/san-pham/{id}/restore', [SanPhamController::class, 'restore'])->middleware('permission:xoa_san_pham');
    Route::delete('/admin/san-pham/{id}/force', [SanPhamController::class, 'forceDelete'])->middleware('permission:xoa_san_pham');
    Route::get('/admin/san-pham/{id}/edit', [SanPhamController::class, 'edit'])->middleware('permission:sua_san_pham')->name('san-pham.edit');
    Route::put('/admin/san-pham/{id}', [SanPhamController::class, 'update'])->middleware('permission:sua_san_pham')->name('san-pham.update');
    Route::delete('/admin/san-pham/{id}', [SanPhamController::class, 'destroy'])->middleware('permission:xoa_san_pham')->name('san-pham.destroy');
    Route::get('/admin/san-pham/{id}', [SanPhamController::class, 'show'])->middleware('permission:xem_san_pham');

    Route::get('/admin/cai-dat/san-pham', [ThietLapSanPhamController::class, 'index'])->middleware('permission:sua_san_pham');
    Route::post('/admin/cai-dat/san-pham/don-vi', [ThietLapSanPhamController::class, 'storeDonVi'])->middleware('permission:sua_san_pham');
    Route::put('/admin/cai-dat/san-pham/don-vi/{id}', [ThietLapSanPhamController::class, 'updateDonVi'])->middleware('permission:sua_san_pham');
    Route::delete('/admin/cai-dat/san-pham/don-vi/{id}', [ThietLapSanPhamController::class, 'destroyDonVi'])->middleware('permission:sua_san_pham');
    Route::post('/admin/cai-dat/san-pham/thuoc-tinh', [ThietLapSanPhamController::class, 'storeThuocTinh'])->middleware('permission:sua_san_pham');
    Route::put('/admin/cai-dat/san-pham/thuoc-tinh/{id}', [ThietLapSanPhamController::class, 'updateThuocTinh'])->middleware('permission:sua_san_pham');
    Route::delete('/admin/cai-dat/san-pham/thuoc-tinh/{id}', [ThietLapSanPhamController::class, 'destroyThuocTinh'])->middleware('permission:sua_san_pham');


    // Quản lý ca làm việc
    Route::get('/admin/ca-lam-viec', [CaLamViecController::class, 'index'])->name('ca-lam-viec.index')->middleware('permission:quan_ly_ca_lam');
    Route::get('/admin/ca-lam-viec/create', [CaLamViecController::class, 'create'])->name('ca-lam-viec.create')->middleware('permission:quan_ly_ca_lam');
    Route::post('/admin/ca-lam-viec', [CaLamViecController::class, 'store'])->name('ca-lam-viec.store')->middleware('permission:quan_ly_ca_lam');
    Route::get('/admin/ca-lam-viec/{caLamViec}/edit', [CaLamViecController::class, 'edit'])->name('ca-lam-viec.edit')->middleware('permission:quan_ly_ca_lam');
    Route::put('/admin/ca-lam-viec/{caLamViec}', [CaLamViecController::class, 'update'])->name('ca-lam-viec.update')->middleware('permission:quan_ly_ca_lam');
    Route::delete('/admin/ca-lam-viec/{caLamViec}', [CaLamViecController::class, 'destroy'])->name('ca-lam-viec.destroy')->middleware('permission:quan_ly_ca_lam');

    Route::get('/admin/hoa-don', [HoaDonController::class, 'index'])->name('admin.hoa-don.index')->middleware('permission:quan_ly_hoa_don|xem_hoa_don');
    Route::get('/admin/hoa-don/{id}/modal', [HoaDonController::class, 'showModal'])->name('admin.hoa-don.modal')->middleware('permission:quan_ly_hoa_don|xem_hoa_don');


    // Đổi trả hàng
    Route::get('/admin/hoa-don/search-product', [HoaDonController::class, 'searchProduct'])->name('admin.hoa-don.search-product')->middleware('permission:quan_ly_hoa_don|xem_hoa_don|doi_tra_hoa_don');
    Route::get('/admin/hoa-don/{id}/doi-tra', [HoaDonController::class, 'formDoiTra'])->name('admin.hoa-don.doi-tra')->middleware('permission:doi_tra_hoa_don');
    Route::post('/admin/hoa-don/{id}/doi-tra', [HoaDonController::class, 'xuLyDoiTra'])->name('admin.hoa-don.xu-ly-doi-tra')->middleware('permission:doi_tra_hoa_don');
    Route::get('/admin/hoa-don/{id}/chi-tiet-doi-tra', [HoaDonController::class, 'chiTietDoiTra'])->name('admin.hoa-don.chi-tiet-doi-tra')->middleware('permission:quan_ly_hoa_don|xem_hoa_don|doi_tra_hoa_don');
    Route::get('/admin/hoa-don/{id}', [HoaDonController::class, 'show'])->name('admin.hoa-don.show')->middleware('permission:quan_ly_hoa_don|xem_hoa_don');
    Route::post('/admin/hoa-don/{id}/huy', [HoaDonController::class, 'huy'])->name('admin.hoa-don.huy')->middleware('permission:huy_hoa_don');
    Route::get('/admin/hang-loi', [HangLoiController::class, 'index'])->name('admin.hang-loi.index')->middleware('permission:quan_ly_hang_loi|xem_hang_loi|them_hang_loi|sua_hang_loi|xu_ly_hang_loi');
    Route::post('/admin/hang-loi/{id}/xac-nhan-tieu-huy', [HangLoiController::class, 'xacNhanTieuHuy'])->name('admin.hang-loi.xac-nhan-tieu-huy')->middleware('permission:xu_ly_hang_loi');

    // Kiểm kho - View
    Route::prefix('/admin/kho-hang/kiem-kho')->name('kiem-kho.')->group(function () {
        Route::get('/', [KiemKhoController::class, 'index'])->middleware('permission:quan_ly_kho_hang|xem_kho_hang|kiem_kho_xem')->name('index');
        Route::get('/tao-moi', [KiemKhoController::class, 'create'])->middleware('permission:kiem_kho_tao')->name('create');
        Route::post('/', [KiemKhoController::class, 'store'])->middleware('permission:kiem_kho_tao')->name('store');
        Route::get('/thung-rac', [KiemKhoController::class, 'trash'])->middleware('permission:kiem_kho_huy')->name('trash');
        Route::get('/bao-cao', [KiemKhoController::class, 'baoCao'])->middleware('permission:quan_ly_kho_hang|xem_kho_hang|kiem_kho_xem')->name('bao-cao');
        Route::get('/{id}', [KiemKhoController::class, 'show'])->whereNumber('id')->middleware('permission:quan_ly_kho_hang|xem_kho_hang|kiem_kho_xem')->name('show');
        Route::get('/{id}/dem', [KiemKhoController::class, 'dem'])->whereNumber('id')->middleware('permission:kiem_kho_dem')->name('dem');
        Route::get('/{id}/sua', [KiemKhoController::class, 'edit'])->whereNumber('id')->middleware('permission:kiem_kho_tao')->name('edit');
        Route::put('/{id}', [KiemKhoController::class, 'update'])->whereNumber('id')->middleware('permission:kiem_kho_tao')->name('update');
        Route::delete('/{id}', [KiemKhoController::class, 'destroy'])->whereNumber('id')->middleware('permission:kiem_kho_huy')->name('destroy');
        Route::post('/{id}/khoi-phuc', [KiemKhoController::class, 'restore'])->whereNumber('id')->middleware('permission:kiem_kho_huy')->name('restore');
        Route::delete('/{id}/xoa-vinh-vien', [KiemKhoController::class, 'forceDelete'])->whereNumber('id')->middleware('permission:kiem_kho_huy')->name('force-delete');
        Route::get('/{id}/in', [KiemKhoController::class, 'print'])->whereNumber('id')->middleware('permission:quan_ly_kho_hang|xem_kho_hang|kiem_kho_xem')->name('print');
    });

    // Kiểm kho - API
    Route::prefix('/admin/api/kiem-kho')->name('admin.api.kiem-kho.')->group(function () {
        Route::get('/tim-variant', [KiemKhoApiController::class, 'timVariant'])->middleware('permission:kiem_kho_xem|kiem_kho_tao|kiem_kho_dem')->name('tim-variant');
        Route::get('/bao-cao', [KiemKhoApiController::class, 'baoCao'])->middleware('permission:quan_ly_kho_hang|xem_kho_hang|kiem_kho_xem')->name('bao-cao');
        Route::get('/{id}/detail', [KiemKhoApiController::class, 'layChiTietPhieu'])->whereNumber('id')->middleware('permission:quan_ly_kho_hang|xem_kho_hang|kiem_kho_xem')->name('detail');
        Route::get('/{id}/thong-ke', [KiemKhoApiController::class, 'thongKe'])->whereNumber('id')->middleware('permission:quan_ly_kho_hang|xem_kho_hang|kiem_kho_xem')->name('thong-ke');
        Route::post('/{id}/items/bulk', [KiemKhoApiController::class, 'capNhatHangLo'])->whereNumber('id')->middleware('permission:kiem_kho_dem')->name('items.bulk');
        Route::post('/{id}/items/{itemId}', [KiemKhoApiController::class, 'capNhatSoLuongThucTe'])->whereNumber('id')->whereNumber('itemId')->middleware('permission:kiem_kho_dem')->name('items.update');
        Route::post('/{id}/bat-dau-kiem', [KiemKhoApiController::class, 'batDauKiem'])->whereNumber('id')->middleware('permission:kiem_kho_dem')->name('bat-dau-kiem');
        Route::post('/{id}/hoan-tat-kiem', [KiemKhoApiController::class, 'hoanTatKiem'])->whereNumber('id')->middleware('permission:kiem_kho_dem')->name('hoan-tat-kiem');
        Route::post('/{id}/duyet', [KiemKhoApiController::class, 'duyet'])->whereNumber('id')->middleware('permission:kiem_kho_duyet')->name('duyet');
        Route::post('/{id}/tu-choi', [KiemKhoApiController::class, 'tuChoi'])->whereNumber('id')->middleware('permission:kiem_kho_duyet')->name('tu-choi');
        Route::post('/{id}/dem-lai', [KiemKhoApiController::class, 'demLai'])->whereNumber('id')->middleware('permission:kiem_kho_dem')->name('dem-lai');
        Route::post('/{id}/hoan-tat', [KiemKhoApiController::class, 'hoanTat'])->whereNumber('id')->middleware('permission:kiem_kho_dieu_chinh')->name('hoan-tat');
        Route::post('/{id}/huy', [KiemKhoApiController::class, 'huy'])->whereNumber('id')->middleware('permission:kiem_kho_huy')->name('huy');
    });

    // Trang kho hang
    Route::get('/admin/kho-hang', [KhoHangController::class, 'index'])->middleware('permission:quan_ly_kho_hang');
    Route::get('/admin/kho-hang/lo-hang/{id}', [KhoHangController::class, 'chiTietLoHang'])->name('kho-hang.lo-hang.chi-tiet')->middleware('permission:quan_ly_kho_hang');
    Route::get('/admin/kho-hang/phieu-nhap', function () {
        return view('admin_xem_truoc.warehouse.phieu-nhap');
    })->middleware('permission:quan_ly_kho_hang');
    Route::get('/admin/kho-hang/phieu-xuat', function () {
        return view('admin_xem_truoc.warehouse.phieu-xuat');
    })->middleware('permission:quan_ly_kho_hang');

    // Trang tạo phiếu nhập (chuyển từ modal sang trang riêng)
    Route::get('/admin/kho-hang/phieu-nhap/create', [PhieuNhapController::class, 'create'])
        ->middleware('permission:nhap_hang')
        ->name('phieu-nhap.create');

    // Trang chi tiết phiếu nhập
    Route::get('/admin/kho-hang/phieu-nhap/{id}', [PhieuNhapController::class, 'show'])
        ->middleware('permission:quan_ly_kho_hang|xem_kho_hang|nhap_hang')
        ->name('phieu-nhap.show');

    // Trang tạo phiếu xuất (trang riêng, không dùng modal)
    Route::get('/admin/kho-hang/phieu-xuat/create', [PhieuXuatController::class, 'create'])
        ->middleware('permission:xuat_hang')
        ->name('phieu-xuat.create');

    // Trang chi tiết phiếu xuất
    Route::get('/admin/kho-hang/phieu-xuat/{id}', [PhieuXuatController::class, 'show'])
        ->whereNumber('id')
        ->middleware('permission:quan_ly_kho_hang|xem_kho_hang')
        ->name('phieu-xuat.show');

    // Trang sửa phiếu xuất (không popup)
    Route::get('/admin/kho-hang/phieu-xuat/{id}/edit', [App\Http\Controllers\admin\Api\PhieuXuatApiController::class, 'showEdit'])
        ->middleware('permission:xuat_hang');

    // Quan ly khach hang
    Route::get('/admin/khach-hang', [KhachHangController::class, 'index'])->name('khach-hang.index')->middleware('permission:quan_ly_khach_hang|xem_khach_hang|them_khach_hang|sua_khach_hang');
    Route::get('/admin/khach-hang/create', [KhachHangController::class, 'create'])->name('khach-hang.create')->middleware('permission:them_khach_hang');
    Route::post('/admin/khach-hang', [KhachHangController::class, 'store'])->name('khach-hang.store')->middleware('permission:them_khach_hang');

    // ===== THÙNG RÁC =====
    Route::get('/admin/khach-hang/thung-rac', [KhachHangController::class, 'trash'])->name('khach-hang.trash')->middleware('permission:xoa_khach_hang');
    Route::put('/admin/khach-hang/{id}/restore', [KhachHangController::class, 'restore'])->name('khach-hang.restore')->middleware('permission:xoa_khach_hang');
    Route::delete('/admin/khach-hang/{id}/force-delete', [KhachHangController::class, 'forceDelete'])->name('khach-hang.force-delete')->middleware('permission:xoa_khach_hang');

    // ===== ROUTE CÓ {khachHang} PHẢI ĐỂ CUỐI =====
    Route::get('/admin/khach-hang/{khachHang}', [KhachHangController::class, 'show'])->name('khach-hang.show')->middleware('permission:quan_ly_khach_hang|xem_khach_hang');
    Route::get('/admin/khach-hang/{khachHang}/edit', [KhachHangController::class, 'edit'])->name('khach-hang.edit')->middleware('permission:sua_khach_hang');
    Route::put('/admin/khach-hang/{khachHang}', [KhachHangController::class, 'update'])->name('khach-hang.update')->middleware('permission:sua_khach_hang');
    Route::delete('/admin/khach-hang/{khachHang}', [KhachHangController::class, 'destroy'])->name('khach-hang.destroy')->middleware('permission:xoa_khach_hang');


    // Trang khuyen mai
    Route::get('/admin/khuyen-mai', [KhuyenMaiController::class, 'index'])->name('khuyen-mai.index')->middleware('permission:quan_ly_khuyen_mai|xem_khuyen_mai|them_khuyen_mai|sua_khuyen_mai');
    Route::get('/admin/khuyen-mai/create', [KhuyenMaiController::class, 'create'])
    ->name('khuyen-mai.create')
    ->middleware('permission:them_khuyen_mai');
    Route::post('/admin/khuyen-mai', [KhuyenMaiController::class, 'store'])->name('khuyen-mai.store')->middleware('permission:them_khuyen_mai');
    Route::get('/admin/khuyen-mai/thung-rac', [KhuyenMaiController::class, 'trash'])->name('khuyen-mai.trash')->middleware('permission:xoa_khuyen_mai');
    Route::post('/admin/khuyen-mai/{id}/toggle', [KhuyenMaiController::class, 'toggle'])->name('khuyen-mai.toggle')->middleware('permission:sua_khuyen_mai');
    Route::post('/admin/khuyen-mai/{id}/ajax-toggle', [KhuyenMaiController::class, 'ajaxToggle'])->name('khuyen-mai.ajaxToggle')->middleware('permission:sua_khuyen_mai');
    Route::post('/admin/khuyen-mai/{id}/restore', [KhuyenMaiController::class, 'restore'])->name('khuyen-mai.restore')->middleware('permission:xoa_khuyen_mai');
    Route::delete('/admin/khuyen-mai/{id}/force', [KhuyenMaiController::class, 'forceDelete'])->name('khuyen-mai.forceDelete')->middleware('permission:xoa_khuyen_mai');
    Route::get('/admin/khuyen-mai/{id}/edit', [KhuyenMaiController::class, 'edit'])->name('khuyen-mai.edit')->middleware('permission:sua_khuyen_mai');
    Route::put('/admin/khuyen-mai/{id}', [KhuyenMaiController::class, 'update'])->name('khuyen-mai.update')->middleware('permission:sua_khuyen_mai');
    Route::delete('/admin/khuyen-mai/{id}', [KhuyenMaiController::class, 'destroy'])->name('khuyen-mai.destroy')->middleware('permission:xoa_khuyen_mai');
    Route::get('/admin/khuyen-mai/{id}', [KhuyenMaiController::class, 'show'])
        ->name('khuyen-mai.show')
        ->middleware('permission:quan_ly_khuyen_mai|xem_khuyen_mai');

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
    Route::get('/ca-lam', [CaLam::class, 'index'])->name('ca-lam.index')->middleware('permission:quan_ly_ca_lam');
    //lịch sử ca làm
    Route::get('/lich-su-ca-lam-viec', [LichSuCaLam::class, 'index'])->name('lich-su-ca-lam-viec.index')->middleware('permission:quan_ly_ca_lam');
    Route::get('/cac-hoa-don-cua-ca-hoan-thanh/{ngay}/{id_ca}', [LichSuCaLam::class, 'hoa_don_cua_ca'])->name('hoa-don-cua-ca')->middleware('permission:quan_ly_ca_lam');
    Route::get('/cac-hoa-don-cua-ca-huy/{ngay}/{id_ca}', [LichSuCaLam::class, 'hoa_don_doi_tra_cua_ca'])->name('hoa-don-cua-ca.doi-tra')->middleware('permission:quan_ly_ca_lam');
    Route::get('/tat-ca-hoa-don-trong-ngay/{ngay}', [LichSuCaLam::class, 'tat_ca_hoa_don_trong_ngay'])->name('tat-ca-hoa-don-trong-ngay')->middleware('permission:quan_ly_ca_lam');
    // Route::get('/lich-su-ca-lam-viec-cac-ca/{ngay}',[LichSuCaLam::class, 'cacCa'])->name('lich-su-ngay-lam-viec.cac-ca-lam')->middleware('permission:quan_ly_ca_lam');
    Route::get('/lich-su-ca-lam-viec-cac-ca/{ngay}/{id_ca?}', [LichSuCaLam::class, 'cacCa'])->name('lich-su-ngay-lam-viec.cac-ca-lam')->middleware('permission:quan_ly_ca_lam');



    Route::get('/lich-su-ca-lam-chi_tiet_ca_lam/{id_ca}/{ngay}', [LichSuCaLam::class, 'chi_tiet_ca'])->name('lich-su-ngay-lam-viec.chi_tiet_ca_lam')->middleware('permission:quan_ly_ca_lam');
    Route::get('/lich-su-ca-lam-chi_tiet-hoa-don/{id_hoaDon}/{ngay}', [LichSuCaLam::class, 'chi_tiet_hoa_don'])->name('lich-su-ca-lam-chi-tiet-hoa-don.show')->middleware('permission:quan_ly_ca_lam');
    //giao ca
    Route::get('/lich-su-ca-lam-giao-ca/{id_ca}/{ngay}', [LichSuCaLam::class, 'tao_giao_ca'])->name('lich-su-ca-lam-giao-ca.tao-giao-ca')->middleware('permission:quan_ly_ca_lam');
    Route::post('/lich-su-ca-lam-giao-ca', [LichSuCaLam::class, 'giao_ca_store'])->name('giao-ca.store')->middleware('permission:quan_ly_ca_lam');
    Route::get('/lich-su-ca-lam-giao-ca-chi-tiet/{id}', [LichSuCaLam::class, 'giao_ca_chi_tiet'])->name('giao-ca.chi-tiet')->middleware('permission:quan_ly_ca_lam');
    Route::get('/lich-su-ca-lam-giao-ca-sua/{id}', [LichSuCaLam::class, 'sua_giao_ca'])->name('giao-ca.sua')->middleware('permission:quan_ly_ca_lam');
    Route::put('/lich-su-ca-lam-giao-cap-nhat/{id}', [LichSuCaLam::class, 'cap_nhat_giao_ca'])->name('giao-ca.cap-nhat')->middleware('permission:quan_ly_ca_lam');
    Route::put('/lich-su-ca-lam-giao-ca/{id}/xac-nhan', [LichSuCaLam::class, 'xac_nhan_giao_ca'])->name('giao-ca.xac-nhan')->middleware('permission:quan_ly_ca_lam');
    Route::put('/lich-su-ca-lam-giao-ca/{id}/tu-choi', [LichSuCaLam::class, 'tu_choi_giao_ca'])->name('giao-ca.tu-choi')->middleware('permission:quan_ly_ca_lam');

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
    Route::get('/hoa-don/{id}/chi-tiet-doi-tra', [NhanVienController::class, 'chiTietDoiTra'])->name('nhan-vien.hoa-don.chi-tiet-doi-tra')->middleware('permission:ban_hang');
    //   Route::post('/hoa-don/{id}/huy', [NhanVienController::class, 'huyHoaDon'])
    //   ->name('nhan-vien.hoa-don.huy');
    Route::get('/ban-hang/khach-hang', [NhanVienController::class, 'layKhachHang'])->name('nhan-vien.ban-hang.khach-hang')->middleware('permission:ban_hang');
    Route::get('/ban-hang/khuyen-mai', [NhanVienController::class, 'layKhuyenMai'])->name('nhan-vien.ban-hang.khuyen-mai')->middleware('permission:ban_hang');
    Route::get('/ban-hang/nhan-vien-ban-hang', [NhanVienController::class, 'layNhanVienBanHang'])->name('nhan-vien.ban-hang.nhan-vien')->middleware('permission:ban_hang');
    Route::get('/ban-hang/don-cho-thanh-toan', [NhanVienController::class, 'donChoThanhToan'])->name('nhan-vien.ban-hang.don-cho-thanh-toan')->middleware('permission:ban_hang');
    Route::post('/ban-hang/don-cho-thanh-toan/{id}/doi-phuong-thuc', [NhanVienController::class, 'doiPhuongThucThanhToanDonCho'])->name('nhan-vien.ban-hang.don-cho-thanh-toan.doi-phuong-thuc')->middleware('permission:ban_hang');

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
    Route::get('/ho-so', [NhanVienNhanVienController::class, 'hoSo'])->name('nhan-vien.ho-so');
    Route::post('/ho-so/doi-mat-khau', [NhanVienNhanVienController::class, 'doiMatKhau'])->name('nhan-vien.ho-so.doi-mat-khau');
});

// PayOS payment routes
Route::match(['get', 'post'], '/payos/create', [PayOSController::class, 'createPayment'])
    ->name('payos.create')
    ->middleware([KTVaiTro::class, 'permission:ban_hang']);
Route::get('/payos/return', [PayOSController::class, 'return'])->name('payos.return');
Route::get('/payos/cancel', [PayOSController::class, 'cancel'])->name('payos.cancel');
Route::match(['get', 'post'], '/payos/webhook', [PayOSController::class, 'webhook'])
    ->name('payos.webhook')
    ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrf::class]);
