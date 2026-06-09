<?php

use App\Http\Controllers\Admin\DanhMuc\DanhMucSanPhamController;
use Illuminate\Support\Facades\Route;

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

Route::get('/admin/san-pham', function () {
    return view('admin_xem_truoc.san-pham');
});

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

Route::get('/admin/nhan-su', function () {
    return view('admin_xem_truoc.nhan-vien');
});

Route::get('/admin/cai-dat', function () {
    return view('admin_xem_truoc.cai-dat');
});

// quản lý danh mục
Route::get('quan-ly-danh-muc', [DanhMucSanPhamController::class, 'index'])->name('danh_muc.index');
Route::post('quan-ly-danh-muc-store', [DanhMucSanPhamController::class, 'store'])->name('danh_muc.store');
Route::get('quan-ly-danh-muc-edit/{id}', [DanhMucSanPhamController::class, 'edit'])->name('danh_muc.edit');
Route::put('quan-ly-danh-muc-update/{id}', [DanhMucSanPhamController::class, 'update'])->name('danh_muc.update');
Route::delete('quan-ly-danh-muc-delete/{id}', [DanhMucSanPhamController::class, 'destroy'])->name('danh_muc.destroy');
