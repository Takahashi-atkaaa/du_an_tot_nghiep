<?php

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
