<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\nhan_vien\NhanVienController;

/*
|--------------------------------------------------------------------------
| Routes Test - Nhân viên (Preview)
|--------------------------------------------------------------------------
| Routes này không yêu cầu đăng nhập, chỉ để xem trước giao diện
|--------------------------------------------------------------------------
*/

Route::prefix('nhan-vien')->group(function () {
    Route::get('/', [NhanVienController::class, 'index'])->name('nhan-vien.dashboard');
    Route::get('/ban-hang', [NhanVienController::class, 'banHang'])->name('nhan-vien.ban-hang');
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
    
    Route::get('/hoa-don', [NhanVienController::class, 'hoaDon'])->name('nhan-vien.hoa-don');
    Route::get('/san-pham', [NhanVienController::class, 'sanPham'])->name('nhan-vien.san-pham');
    Route::get('/khach-hang', [NhanVienController::class, 'khachHang'])->name('nhan-vien.khach-hang');
    Route::get('/lich-lam-viec', [NhanVienController::class, 'lichLamViec'])->name('nhan-vien.lich');
    Route::get('/lich-lam-viec/tuan', [NhanVienController::class, 'lichLamViecTuan'])->name('nhan-vien.lich-tuan');
    Route::get('/cham-cong', [NhanVienController::class, 'chamCong'])->name('nhan-vien.cham-cong');
    Route::get('/ho-so', [NhanVienController::class, 'hoSo'])->name('nhan-vien.ho-so');
});
