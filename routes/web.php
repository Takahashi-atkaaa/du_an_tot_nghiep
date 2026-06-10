<?php

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

Route::get('/admin/san-pham', function () {
    return view('admin_xem_truoc.san-pham');
});

Route::get('/admin/danh-muc', function () {
    return view('admin_xem_truoc.danh-muc');
});

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

// Nha cung cap routes
Route::get('/admin/kho-hang/nha-cung-cap', [NhaCungCapController::class, 'index']);
Route::post('/admin/kho-hang/nha-cung-cap', [NhaCungCapController::class, 'store']);
Route::get('/admin/kho-hang/nha-cung-cap/{id}/lich-su-giao-dich', 
    [NhaCungCapController::class, 'lichSuGiaoDich']
);
Route::get('/admin/kho-hang/nha-cung-cap/{id}/edit', [NhaCungCapController::class, 'edit']);
Route::put('/admin/kho-hang/nha-cung-cap/{id}', [NhaCungCapController::class, 'update']);
Route::delete('/admin/kho-hang/nha-cung-cap/{id}', [NhaCungCapController::class, 'destroy']);
