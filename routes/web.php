<?php

use App\Http\Controllers\admin\AuthController;
use App\Http\Controllers\nhan_vien\NhanVienController;
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
use App\Http\Controllers\admin\KhoHang\NhaCungCapController;
use App\Http\Controllers\admin\Api\ThuocTinhApiController;
use App\Http\Controllers\admin\Api\SanPhamApiController;
use App\Http\Controllers\admin\CaiDat\ThietLapSanPhamController;
use App\Http\Controllers\admin\CaLam\CaLam;
use App\Http\Controllers\admin\CaLam\LichSuCaLam;
use App\Http\Controllers\admin\KhuyenMaiController;
use App\Http\Controllers\admin\PhanQuyenDong\PhanQuyen;
use App\Http\Controllers\nhan_vien\KhachHangController as NhanVienKhachHangController;

use App\Http\Middleware\AuthAdmin;
use App\Http\Middleware\KiemTraVaiTro;
use App\Http\Middleware\KTVaiTro;
use App\Models\NhaCungCap;

Route::get('/', function () {
    return view('admin_xem_truoc.auth.login');
});



Route::get('/admin/login', [AuthController::class, 'showLoginForm'])
    ->name('admin.login');
Route::post('/admin/login', [AuthController::class, 'login'])
    ->name('admin.login.submit');
Route::get('/admin/logout', [AuthController::class, 'logout'])
    ->name('admin.logout');

// Quen mat khau
Route::get('/admin/quen-mat-khau', [AuthController::class, 'showQuenMatKhau'])->name('admin.quen-mat-khau');
Route::post('/admin/quen-mat-khau', [AuthController::class, 'guiEmailQuenMatKhau'])->name('admin.quen-mat-khau.submit');

// Dat lai mat khau
Route::get('/admin/dat-lai-mat-khau/{token}', [AuthController::class, 'showFormDatLaiMatKhau'])->name('admin.dat-lai-mat-khau');
Route::post('/admin/dat-lai-mat-khau', [AuthController::class, 'datLaiMatKhau'])->name('admin.dat-lai-mat-khau.submit');

// Trang doi mat khau
Route::get('/admin/doi-mat-khau', [AuthController::class, 'showDoiMatKhau'])->name('admin.doi-mat-khau');
Route::post('/admin/doi-mat-khau', [AuthController::class, 'doiMatKhau'])->name('admin.doi-mat-khau.submit');

// Admin Routes - Preview
Route::get('/admin/dashboard', function () {
    return view('admin_xem_truoc.dashboard');
});


// Route::get('/admin/ban-hang', function () {
//     return view('admin_xem_truoc.ban-hang');
// });



// Route::get('/admin/kho-hang', function () {
//     return view('admin_xem_truoc.kho-hang');
// });

// Route::get('/admin/khach-hang', function () {
//     return view('admin_xem_truoc.khach-hang');
// });

// // Route::get('/admin/khuyen-mai', function () {
// //     return view('admin_xem_truoc.khuyen-mai');
// // });

Route::get('/admin/cai-dat', function () {
    return view('admin_xem_truoc.cai-dat');
});


// // Trang ban hang
// Route::get('/admin/ban-hang', function () {
//     return view('admin_xem_truoc.ban-hang');
// });
//chuc nang admin va truong ca trung nhau, ma quyen do can su cho phep cua admin thi moi dc vao cho vao  middleware [KTVaiTroQuanTri::class]
//chuc nang mac dinh cua truonwg ca thi vut vao middleware [AuthTruongCa::class] (k hieu inb thuan chim to)
//phe admin
    // Lô hàng
    Route::get('/admin/api/lo-hang', [LoHangApiController::class, 'index']);
    Route::get('/admin/api/lo-hang/nha-cung-cap', [LoHangApiController::class, 'nhaCungCaps']);
    Route::get('/admin/api/lo-hang/ton-kho', [LoHangApiController::class, 'tonKho']);
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

    // Phiếu nhập
    Route::get('/admin/api/phieu-nhap', [PhieuNhapApiController::class, 'index']);
    Route::get('/admin/api/phieu-nhap/lo-hang', [PhieuNhapApiController::class, 'danhSachLoHang']);
    Route::get('/admin/api/phieu-nhap/{id}', [PhieuNhapApiController::class, 'show']);
    Route::post('/admin/api/phieu-nhap', [PhieuNhapApiController::class, 'store']);
    Route::put('/admin/api/phieu-nhap/{id}', [PhieuNhapApiController::class, 'update']);
    Route::delete('/admin/api/phieu-nhap/{id}', [PhieuNhapApiController::class, 'destroy']);

    // Phiếu xuất
    Route::get('/admin/api/phieu-xuat', [PhieuXuatApiController::class, 'index']);
    Route::get('/admin/api/phieu-xuat/{id}', [PhieuXuatApiController::class, 'show']);
    Route::post('/admin/api/phieu-xuat', [PhieuXuatApiController::class, 'store']);
    Route::put('/admin/api/phieu-xuat/{id}', [PhieuXuatApiController::class, 'update']);
    Route::delete('/admin/api/phieu-xuat/{id}', [PhieuXuatApiController::class, 'destroy']);

    Route::middleware([KTVaiTro::class])->group(function () {
        // API - phải đặt TRƯỚC san-pham/{id} để tránh bị match nhầm (KHÔNG bị chặn bởi KTVaiTro)
        Route::get('/admin/api/san-pham', [SanPhamApiController::class, 'index']);
        Route::get('/admin/api/san-pham/{id}', [SanPhamApiController::class, 'show']);
        Route::delete('/admin/api/san-pham/variant/{id}', [SanPhamApiController::class, 'destroyVariant']);
        Route::delete('/admin/api/san-pham/{id}/variants', [SanPhamApiController::class, 'destroyAllVariants']);
        Route::get('/admin/api/thuoc-tinh/con/{id}', [ThuocTinhApiController::class, 'getThuocTinhCon']);
        Route::get('/admin/api/thuoc-tinh/all-con', [ThuocTinhApiController::class, 'getAllThuocTinhCon']);
        Route::post('/admin/api/thuoc-tinh', [ThuocTinhApiController::class, 'store']);
        // Nha cung cap routes
        Route::get('/admin/kho-hang/nha-cung-cap', [NhaCungCapController::class, 'index'])->middleware('permission:xem_nha_cung_cap');
    Route::post('/admin/kho-hang/nha-cung-cap', [NhaCungCapController::class, 'store'])->middleware('permission:them_nha_cung_cap');
    Route::get('/admin/kho-hang/nha-cung-cap/{id}/lich-su-giao-dich',  [NhaCungCapController::class, 'lichSuGiaoDich'])->middleware('permission:xem_lich_su_giao_dich');
    Route::get('/admin/kho-hang/nha-cung-cap/{id}/edit', [NhaCungCapController::class, 'edit'])->middleware('permission:sua_nha_cung_cap');
    Route::put('/admin/kho-hang/nha-cung-cap/{id}', [NhaCungCapController::class, 'update'])->middleware('permission:sua_nha_cung_cap');
    Route::delete('/admin/kho-hang/nha-cung-cap/{id}', [NhaCungCapController::class, 'destroy'])->middleware('permission:xoa_nha_cung_cap');


    // Thùng rác nhà cung cấp
    Route::get('/admin/kho-hang/nha-cung-cap/thung-rac', [NhaCungCapController::class, 'trash'])->middleware('permission:xem_nha_cung_cap');
    Route::post('/admin/kho-hang/nha-cung-cap/bulk-restore', [NhaCungCapController::class, 'bulkRestore'])->middleware('permission:xem_nha_cung_cap');
    Route::delete('/admin/kho-hang/nha-cung-cap/bulk-force', [NhaCungCapController::class, 'bulkForceDelete'])->middleware('permission:xem_nha_cung_cap');
    Route::post('/admin/kho-hang/nha-cung-cap/{id}/restore', [NhaCungCapController::class, 'restore'])->middleware('permission:xem_nha_cung_cap');
    Route::delete('/admin/kho-hang/nha-cung-cap/{id}/force', [NhaCungCapController::class, 'forceDelete'])->middleware('permission:xem_nha_cung_cap');


    // quản lý danh mục
    Route::get('quan-ly-danh-muc', [DanhMucSanPhamController::class, 'index'])->name('danh_muc.index')->middleware('permission:xem_danh_muc');
    Route::post('quan-ly-danh-muc-store', [DanhMucSanPhamController::class, 'store'])->name('danh_muc.store')->middleware('permission:them_danh_muc');
    Route::get('quan-ly-danh-muc-edit/{id}', [DanhMucSanPhamController::class, 'edit'])->name('danh_muc.edit')->middleware('permission:sua_danh_muc');
    Route::put('quan-ly-danh-muc-update/{id}', [DanhMucSanPhamController::class, 'update'])->name('danh_muc.update')->middleware('permission:sua_danh_muc');
    Route::delete('quan-ly-danh-muc-delete/{id}', [DanhMucSanPhamController::class, 'destroy'])->name('danh_muc.destroy')->middleware('permission:xoa_danh_muc');


    //quản lý người dùng
    Route::get('/nguoi-dung', [NguoiDungController::class, 'index'])->name('nguoi-dung.index')->middleware('permission:xem_nguoi_dung');
    Route::delete('nguoi-dung/{nguoiDung}', [NguoiDungController::class, 'destroy'])->name('nguoi-dung.destroy')->middleware('permission:xoa_nguoi_dung');
    Route::get('/nguoi-dung/create', [NguoiDungController::class, 'create'])->name('nguoi-dung.create')->middleware('permission:them_nguoi_dung');
    Route::post('/nguoi-dung', [NguoiDungController::class, 'store'])->name('nguoi-dung.store')->middleware('permission:them_nguoi_dung');
    Route::get('/nguoi-dung/{nguoiDung}', [NguoiDungController::class, 'show'])->name('nguoi-dung.show')->middleware('permission:xem_nguoi_dung');
    Route::get('/nguoi-dung/{nguoiDung}/edit', [NguoiDungController::class, 'edit'])->name('nguoi-dung.edit')->middleware('permission:sua_nguoi_dung');
    Route::put('/nguoi-dung/{nguoiDung}', [NguoiDungController::class, 'update'])->name('nguoi-dung.update')->middleware('permission:sua_nguoi_dung');

    //Phân quyền người dùng
    Route::get('nguoi-dung-phan-quyen/{id_vai_tro}', [PhanQuyen::class, 'phanQuyen'])->name('nguoi-dung.phan-quyen')->middleware('permission:phan_quyen_nguoi_dung');
    Route::Post('nguoi-dung-phan-quyen/{id_vai_tro}', [PhanQuyen::class, 'capNhatPhanQuyen'])->name('admin.quyen.update')->middleware('permission:cap_nhat_phan_quyen');


    //quản lý sản phẩm

    Route::get('/admin/san-pham', [SanPhamController::class, 'index'])
        ->middleware('permission:xem_san_pham')->name('san-pham.index');
    Route::post('/admin/san-pham', [SanPhamController::class, 'store'])->middleware('permission:them_san_pham');
    Route::post('/admin/san-pham/bulk-action', [SanPhamController::class, 'bulkAction'])->middleware('permission:sua_san_pham');
    Route::get('/admin/san-pham/trash', [SanPhamController::class, 'trash'])->middleware('permission:xoa_san_pham')->name('san-pham.trash');
    Route::get('/admin/san-pham/export', [SanPhamController::class, 'export'])->middleware('permission:xem_san_pham');
    Route::get('/admin/san-pham/export-template', [SanPhamController::class, 'exportTemplate'])->middleware('permission:xem_san_pham');
    Route::post('/admin/san-pham/import', [SanPhamController::class, 'import'])->middleware('permission:them_san_pham');
    Route::post('/admin/san-pham/{id}/restore', [SanPhamController::class, 'restore'])->middleware('permission:xoa_san_pham');
    Route::delete('/admin/san-pham/{id}/force', [SanPhamController::class, 'forceDelete'])->middleware('permission:xoa_san_pham');
    Route::get('/admin/san-pham/{id}/edit', [SanPhamController::class, 'edit'])->middleware('permission:sua_san_pham');
    Route::put('/admin/san-pham/{id}', [SanPhamController::class, 'update'])->middleware('permission:sua_san_pham');
    Route::delete('/admin/san-pham/{id}', [SanPhamController::class, 'destroy'])->middleware('permission:xoa_san_pham');
    Route::get('/admin/san-pham/{id}', [SanPhamController::class, 'show'])->middleware('permission:xem_san_pham');

    Route::get('/admin/cai-dat/san-pham', [ThietLapSanPhamController::class, 'index']);
    Route::post('/admin/cai-dat/san-pham/don-vi', [ThietLapSanPhamController::class, 'storeDonVi']);
    Route::put('/admin/cai-dat/san-pham/don-vi/{id}', [ThietLapSanPhamController::class, 'updateDonVi']);
    Route::delete('/admin/cai-dat/san-pham/don-vi/{id}', [ThietLapSanPhamController::class, 'destroyDonVi']);
    Route::post('/admin/cai-dat/san-pham/thuoc-tinh', [ThietLapSanPhamController::class, 'storeThuocTinh']);
    Route::put('/admin/cai-dat/san-pham/thuoc-tinh/{id}', [ThietLapSanPhamController::class, 'updateThuocTinh']);
    Route::delete('/admin/cai-dat/san-pham/thuoc-tinh/{id}', [ThietLapSanPhamController::class, 'destroyThuocTinh']);

    // Quản lý ca làm việc
    Route::get('/admin/ca-lam-viec', [CaLamViecController::class, 'index'])->name('ca-lam-viec.index')->middleware('permission:xem_ca_lam_viec');
    Route::get('/admin/ca-lam-viec/create', [CaLamViecController::class, 'create'])->name('ca-lam-viec.create')->middleware('permission:them_ca_lam_viec');
    Route::post('/admin/ca-lam-viec', [CaLamViecController::class, 'store'])->name('ca-lam-viec.store')->middleware('permission:them_ca_lam_viec');
    Route::get('/admin/ca-lam-viec/{caLamViec}/edit', [CaLamViecController::class, 'edit'])->name('ca-lam-viec.edit')->middleware('permission:sua_ca_lam_viec');
    Route::put('/admin/ca-lam-viec/{caLamViec}', [CaLamViecController::class, 'update'])->name('ca-lam-viec.update')->middleware('permission:sua_ca_lam_viec');
    Route::delete('/admin/ca-lam-viec/{caLamViec}', [CaLamViecController::class, 'destroy'])->name('ca-lam-viec.destroy')->middleware('permission:xoa_ca_lam_viec');

    // Trang hoa don
    Route::get('/admin/hoa-don', function () {
        return view('admin_xem_truoc.hoa-don');
    });


    // Trang kho hang
    Route::get('/admin/kho-hang', function () {
        $nhaCungCaps = NhaCungCap::orderBy('id', 'asc')->get();

        return view('admin_xem_truoc.kho-hang', compact('nhaCungCaps'));
    });

    Route::get('/admin/kho-hang/lo-hang', function () {
        return view('admin_xem_truoc.warehouse.lo-hang');
    });

    Route::get('/admin/kho-hang/phieu-nhap', function () {
        return view('admin_xem_truoc.warehouse.phieu-nhap');
    });

    Route::get('/admin/kho-hang/phieu-xuat', function () {
        return view('admin_xem_truoc.warehouse.phieu-xuat');
    });


    // Quan ly khach hang
    Route::get('/admin/khach-hang', [KhachHangController::class, 'index'])
        ->name('khach-hang.index')->middleware('permission:xem_khach_hang');

    Route::get('/admin/khach-hang/create', [KhachHangController::class, 'create'])
        ->name('khach-hang.create')->middleware('permission:them_khach_hang');

    Route::post('/admin/khach-hang', [KhachHangController::class, 'store'])
        ->name('khach-hang.store')->middleware('permission:them_khach_hang');

    // ===== THÙNG RÁC =====
    Route::get('/admin/khach-hang/thung-rac', [KhachHangController::class, 'trash'])
        ->name('khach-hang.trash')->middleware('permission:xem_khach_hang');

    Route::put('/admin/khach-hang/{id}/restore', [KhachHangController::class, 'restore'])
        ->name('khach-hang.restore')->middleware('permission:xem_khach_hang');

    Route::delete('/admin/khach-hang/{id}/force-delete', [KhachHangController::class, 'forceDelete'])
        ->name('khach-hang.force-delete')->middleware('permission:xem_khach_hang');

    // ===== ROUTE CÓ {khachHang} PHẢI ĐỂ CUỐI =====
    Route::get('/admin/khach-hang/{khachHang}', [KhachHangController::class, 'show'])
        ->name('khach-hang.show')->middleware('permission:xem_khach_hang');

    Route::get('/admin/khach-hang/{khachHang}/edit', [KhachHangController::class, 'edit'])
        ->name('khach-hang.edit')->middleware('permission:sua_khach_hang');

    Route::put('/admin/khach-hang/{khachHang}', [KhachHangController::class, 'update'])
        ->name('khach-hang.update')->middleware('permission:sua_khach_hang');

    Route::delete('/admin/khach-hang/{khachHang}', [KhachHangController::class, 'destroy'])
        ->name('khach-hang.destroy')->middleware('permission:xoa_khach_hang');


    // Trang khuyen mai
    Route::get('/admin/khuyen-mai', [KhuyenMaiController::class, 'index'])
    ->name('khuyen-mai.index')->middleware('permission:xem_khuyen_mai');

    Route::post('/admin/khuyen-mai', [KhuyenMaiController::class, 'store'])
        ->name('khuyen-mai.store')->middleware('permission:them_khuyen_mai');

    Route::get('/admin/khuyen-mai/thung-rac', [KhuyenMaiController::class, 'trash'])
        ->name('khuyen-mai.trash')->middleware('permission:xem_khuyen_mai');

    Route::post('/admin/khuyen-mai/{id}/toggle', [KhuyenMaiController::class, 'toggle'])
        ->name('khuyen-mai.toggle')->middleware('permission:xem_khuyen_mai');

    Route::post('/admin/khuyen-mai/{id}/ajax-toggle', [KhuyenMaiController::class, 'ajaxToggle'])
        ->name('khuyen-mai.ajaxToggle')->middleware('permission:xem_khuyen_mai');

    Route::post('/admin/khuyen-mai/{id}/restore', [KhuyenMaiController::class, 'restore'])
        ->name('khuyen-mai.restore')->middleware('permission:xem_khuyen_mai');

    Route::delete('/admin/khuyen-mai/{id}/force', [KhuyenMaiController::class, 'forceDelete'])
        ->name('khuyen-mai.forceDelete')->middleware('permission:xem_khuyen_mai');

    Route::get('/admin/khuyen-mai/{id}/edit', [KhuyenMaiController::class, 'edit'])
        ->name('khuyen-mai.edit')->middleware('permission:xem_khuyen_mai');

    Route::put('/admin/khuyen-mai/{id}', [KhuyenMaiController::class, 'update'])
        ->name('khuyen-mai.update')->middleware('permission:xem_khuyen_mai');

    Route::delete('/admin/khuyen-mai/{id}', [KhuyenMaiController::class, 'destroy'])
        ->name('khuyen-mai.destroy')->middleware('permission:xem_khuyen_mai');

   // / chia ca làm việc
    Route::get('/admin/chia-ca-lam-viec', [ChiaCaController::class, 'index'])->name('chia-ca-lam-viec.index')->middleware('permission:xem_chia_ca_lam_viec');
    Route::get('/admin/chia-ca-lam-viec/create', [ChiaCaController::class, 'create'])->name('chia-ca-lam-viec.create')->middleware('permission:them_chia_ca_lam_viec');
    Route::post('/admin/chia-ca-lam-viec', [ChiaCaController::class, 'store'])->name('chia-ca-lam-viec.store')->middleware('permission:them_chia_ca_lam_viec');
    Route::get('/admin/chia-ca-lam-viec/export', [ChiaCaController::class, 'export'])->name('chia-ca-lam-viec.export')->middleware('permission:xem_chia_ca_lam_viec');
    Route::delete('/admin/chia-ca-lam-viec/xoa-o-lich', [ChiaCaController::class, 'destroyCell'])->name('chia-ca-lam-viec.destroy-cell')->middleware('permission:xoa_chia_ca_lam_viec');
    Route::get('/admin/chia-ca-lam-viec/{chiaCaLamViec}/edit', [ChiaCaController::class, 'edit'])->name('chia-ca-lam-viec.edit')->middleware('permission:sua_chia_ca_lam_viec');
    Route::put('/admin/chia-ca-lam-viec/{chiaCaLamViec}', [ChiaCaController::class, 'update'])->name('chia-ca-lam-viec.update')->middleware('permission:sua_chia_ca_lam_viec');
    Route::delete('/admin/chia-ca-lam-viec/{chiaCaLamViec}', [ChiaCaController::class, 'destroy'])->name('chia-ca-lam-viec.destroy')->middleware('permission:xoa_chia_ca_lam_viec');

    // quản lý ca làm
    Route::get('/ca-lam',[CaLam::class, 'index'])->name('ca-lam.index')->middleware('permission::ca-lam');
    Route::get('/chi-tiet-hoa-don/{id_hoadon}',[CaLam::class, 'show'])->name('chi-tiet-hoa-don.show')->middleware('permission::ca-lam');
        //lịch sử ca làm
        Route::get('/lich-su-ca-lam-viec',[LichSuCaLam::class, 'index'])->name('lich-su-ca-lam-viec.index')->middleware('permission::ca-lam');
        Route::get('/lich-su-ca-lam-viec-cac-ca/{ngay}',[LichSuCaLam::class, 'cacCa'])->name('lich-su-ngay-lam-viec.cac-ca-lam')->middleware('permission::ca-lam');
        Route::get('/lich-su-ca-lam-chi_tiet_ca_lam/{id_ca}/{ngay}',[LichSuCaLam::class, 'chi_tiet_ca'])->name('lich-su-ngay-lam-viec.chi_tiet_ca_lam')->middleware('permission::ca-lam');
        Route::get('/lich-su-ca-lam-chi_tiet-hoa-don/{id_hoaDon}/{ngay}',[LichSuCaLam::class, 'chi_tiet_hoa_don'])->name('lich-su-ca-lam-chi-tiet-hoa-don.show')->middleware('permission::ca-lam');


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

});//het router admin


// Routes nhân viên (Preview)
Route::middleware([KTVaiTro::class])->prefix('nhan-vien')->group(function () {
    Route::get('/', [NhanVienController::class, 'index'])->name('nhan-vien.dashboard');
    Route::get('/ban-hang', [NhanVienController::class, 'banHang'])->name('nhan-vien.ban-hang');
    Route::get('/hoa-don', [NhanVienController::class, 'hoaDon'])->name('nhan-vien.hoa-don');
    Route::get('/san-pham', [NhanVienController::class, 'sanPham'])->name('nhan-vien.san-pham');
  // khách hàng 
  Route::get('/ban-hang/san-pham', [NhanVienController::class, 'laySanPham'])
  ->name('nhan-vien.ban-hang.san-pham');
  Route::get('/ban-hang/danh-muc', [NhanVienController::class, 'layDanhMuc'])
  ->name('nhan-vien.ban-hang.danh-muc');
  Route::post('/ban-hang/thanh-toan', [NhanVienController::class, 'thanhToan'])
  ->name('nhan-vien.ban-hang.thanh-toan');
     Route::get('/hoa-don', [NhanVienController::class, 'hoaDon'])
      ->name('nhan-vien.hoa-don');
  Route::get('/hoa-don/{id}', [NhanVienController::class, 'chiTietHoaDon'])
  ->name('nhan-vien.hoa-don.chi-tiet');
  Route::get('/hoa-don/{id}/in', [NhanVienController::class, 'inHoaDon'])
  ->name('nhan-vien.hoa-don.in');
  Route::post('/hoa-don/{id}/huy', [NhanVienController::class, 'huyHoaDon'])
  ->name('nhan-vien.hoa-don.huy');
  Route::get('/ban-hang/khach-hang', [NhanVienController::class, 'layKhachHang'])
  ->name('nhan-vien.ban-hang.khach-hang');
  
    Route::get('/khach-hang', [NhanVienKhachHangController::class, 'index'])->name('nhan-vien.khach-hang.index');
    Route::get('/khach-hang/create', [NhanVienKhachHangController::class, 'create'])->name('nhan-vien.khach-hang.create');
    Route::post('/khach-hang', [NhanVienKhachHangController::class, 'store'])->name('nhan-vien.khach-hang.store');
    Route::get('/khach-hang/{khachHang}', [NhanVienKhachHangController::class, 'show'])->name('nhan-vien.khach-hang.show');
    Route::get('/khach-hang/{khachHang}/edit-phone', [NhanVienKhachHangController::class, 'editPhone'])->name('nhan-vien.khach-hang.edit_phone');
    Route::put('/khach-hang/{khachHang}/update-phone', [NhanVienKhachHangController::class, 'updatePhone'])->name('nhan-vien.khach-hang.update_phone');

    Route::get('/lich-lam-viec', [NhanVienController::class, 'lichLamViec'])->name('nhan-vien.lich');
    Route::get('/lich-lam-viec/tuan', [NhanVienController::class, 'lichLamViecTuan'])->name('nhan-vien.lich-tuan');
    Route::get('/cham-cong', [NhanVienController::class, 'chamCong'])->name('nhan-vien.cham-cong');
    Route::get('/ho-so', [NhanVienController::class, 'hoSo'])->name('nhan-vien.ho-so');
});
