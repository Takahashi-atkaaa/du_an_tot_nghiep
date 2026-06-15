<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\nhan_vien\TestNhanVienController;

/*
|--------------------------------------------------------------------------
| Routes Test - Nhân viên (Preview)
|--------------------------------------------------------------------------
| Routes này không yêu cầu đăng nhập, chỉ để xem trước giao diện
|--------------------------------------------------------------------------
*/

Route::prefix('nhan-vien-xem-truoc')->group(function () {
    Route::get('/', [TestNhanVienController::class, 'index'])->name('nhan-vien.test.dashboard');
    Route::get('/ban-hang', [TestNhanVienController::class, 'banHang'])->name('nhan-vien.test.ban-hang');
    Route::get('/hoa-don', [TestNhanVienController::class, 'hoaDon'])->name('nhan-vien.test.hoa-don');
    Route::get('/san-pham', [TestNhanVienController::class, 'sanPham'])->name('nhan-vien.test.san-pham');
    Route::get('/khach-hang', [TestNhanVienController::class, 'khachHang'])->name('nhan-vien.test.khach-hang');
    Route::get('/lich-lam-viec', [TestNhanVienController::class, 'lichLamViec'])->name('nhan-vien.test.lich');
    Route::get('/cham-cong', [TestNhanVienController::class, 'chamCong'])->name('nhan-vien.test.cham-cong');
    Route::get('/ho-so', [TestNhanVienController::class, 'hoSo'])->name('nhan-vien.test.ho-so');
});
