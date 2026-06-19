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
    Route::get('/hoa-don', [NhanVienController::class, 'hoaDon'])->name('nhan-vien.hoa-don');
    Route::get('/san-pham', [NhanVienController::class, 'sanPham'])->name('nhan-vien.san-pham');
    Route::get('/khach-hang', [NhanVienController::class, 'khachHang'])->name('nhan-vien.khach-hang');
    Route::get('/lich-lam-viec', [NhanVienController::class, 'lichLamViec'])->name('nhan-vien.lich');
    Route::get('/lich-lam-viec/tuan', [NhanVienController::class, 'lichLamViecTuan'])->name('nhan-vien.lich-tuan');
    Route::get('/cham-cong', [NhanVienController::class, 'chamCong'])->name('nhan-vien.cham-cong');
    Route::get('/ho-so', [NhanVienController::class, 'hoSo'])->name('nhan-vien.ho-so');
});
