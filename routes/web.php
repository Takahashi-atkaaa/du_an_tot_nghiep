<?php

use App\Http\Controllers\Admin\DanhMuc\DanhMucSanPhamController;
use App\Http\Controllers\Admin\SanPham\SanPhamController;
use App\Http\Controllers\admin\NhanSu\CaLamViecController;
use App\Http\Controllers\admin\NhanSu\NguoiDungController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\admin\KhoHang\NhaCungCapController;

Route::get('/', function () {
    return view('admin_xem_truoc.dashboard');
});

// Admin Routes - Preview
Route::get('/admin/dashboard', function () {
    return view('admin_xem_truoc.dashboard');
});

Route::get('/admin/login', function () {
    return view('admin_xem_truoc.auth.login');
});

Route::get('/admin/ban-hang', function () {
    return view('admin_xem_truoc.ban-hang');
});

Route::get('/admin/hoa-don', function () {
    return view('admin_xem_truoc.hoa-don');
});

Route::get('/admin/san-pham', [SanPhamController::class, 'index']);

// Route::get('/admin/danh-muc', function () {
//     return view('admin_xem_truoc.danh-muc');
// });

Route::get('/admin/kho-hang', function () {
    return view('admin_xem_truoc.kho-hang');
});

Route::get('/admin/khach-hang', function () {
    return view('admin_xem_truoc.khach-hang');
});

Route::get('/admin/khuyen-mai', function () {
    return view('admin_xem_truoc.khuyen-mai');
});

Route::get('/admin/ca-lam-viec', [CaLamViecController::class, 'index'])->name('ca-lam-viec.index');
Route::get('/admin/ca-lam-viec/create', [CaLamViecController::class, 'create'])->name('ca-lam-viec.create');
Route::post('/admin/ca-lam-viec', [CaLamViecController::class, 'store'])->name('ca-lam-viec.store');
Route::get('/admin/ca-lam-viec/{caLamViec}/edit', [CaLamViecController::class, 'edit'])->name('ca-lam-viec.edit');
Route::put('/admin/ca-lam-viec/{caLamViec}', [CaLamViecController::class, 'update'])->name('ca-lam-viec.update');
Route::delete('/admin/ca-lam-viec/{caLamViec}', [CaLamViecController::class, 'destroy'])->name('ca-lam-viec.destroy');

Route::get('/admin/nhan-su', [NguoiDungController::class, 'index']);

Route::get('/admin/cai-dat', function () {
    return view('admin_xem_truoc.cai-dat');
});


// Nha cung cap routes
Route::get('/admin/kho-hang/nha-cung-cap', [NhaCungCapController::class, 'index']);
Route::post('/admin/kho-hang/nha-cung-cap', [NhaCungCapController::class, 'store']);
Route::get('/admin/kho-hang/nha-cung-cap/{id}/lich-su-giao-dich', 
    [NhaCungCapController::class, 'lichSuGiaoDich']
);
Route::get('/admin/kho-hang/nha-cung-cap/{id}/edit', [NhaCungCapController::class, 'edit']);
Route::put('/admin/kho-hang/nha-cung-cap/{id}', [NhaCungCapController::class, 'update']);
Route::delete('/admin/kho-hang/nha-cung-cap/{id}', [NhaCungCapController::class, 'destroy']);

// quản lý danh mục
Route::get('quan-ly-danh-muc', [DanhMucSanPhamController::class, 'index'])->name('danh_muc.index');
Route::post('quan-ly-danh-muc-store', [DanhMucSanPhamController::class, 'store'])->name('danh_muc.store');
Route::get('quan-ly-danh-muc-edit/{id}', [DanhMucSanPhamController::class, 'edit'])->name('danh_muc.edit');
Route::put('quan-ly-danh-muc-update/{id}', [DanhMucSanPhamController::class, 'update'])->name('danh_muc.update');
Route::delete('quan-ly-danh-muc-delete/{id}', [DanhMucSanPhamController::class, 'destroy'])->name('danh_muc.destroy');

Route::get('/nguoi-dung', [NguoiDungController::class, 'index'])->name('nguoi-dung.index');
Route::get('/nguoi-dung/create', [NguoiDungController::class, 'create'])->name('nguoi-dung.create');
Route::post('/nguoi-dung', [NguoiDungController::class, 'store'])->name('nguoi-dung.store');
Route::get('/nguoi-dung/{nguoiDung}/edit', [NguoiDungController::class, 'edit'])->name('nguoi-dung.edit');
Route::put('/nguoi-dung/{nguoiDung}', [NguoiDungController::class, 'update'])->name('nguoi-dung.update');
Route::delete('/nguoi-dung/{nguoiDung}', [NguoiDungController::class, 'destroy'])->name('nguoi-dung.destroy');

