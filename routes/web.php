<?php

// Khai bao su dung helpers cua Laravel
use Illuminate\Support\Facades\Route;

// Khai bao su dung controller
use App\Http\Controllers\admin\AuthController;

// Trang chu - hien thi dashboard
Route::get('/', function () {
    return view('admin_xem_truoc.auth.login');
});


Route::get('/admin/login', [AuthController::class, 'showLoginForm'])
    ->name('admin.login');
Route::post('/admin/login', [AuthController::class, 'login'])
    ->name('admin.login.submit');
Route::get('/admin/logout', [AuthController::class, 'logout'])
    ->name('admin.logout');

// Trang dashboard chinh
Route::get('/admin/dashboard', function () {
    return view('admin_xem_truoc.dashboard');
})->name('admin.dashboard');

// Trang doi mat khau
Route::get('/admin/doi-mat-khau', [AuthController::class, 'showDoiMatKhau'])->name('admin.doi-mat-khau');
Route::post('/admin/doi-mat-khau', [AuthController::class, 'doiMatKhau'])->name('admin.doi-mat-khau.submit');

// Quen mat khau
Route::get('/admin/quen-mat-khau', [AuthController::class, 'showQuenMatKhau'])->name('admin.quen-mat-khau');
Route::post('/admin/quen-mat-khau', [AuthController::class, 'guiEmailQuenMatKhau'])->name('admin.quen-mat-khau.submit');

// Dat lai mat khau
Route::get('/admin/dat-lai-mat-khau/{token}', [AuthController::class, 'showFormDatLaiMatKhau'])->name('admin.dat-lai-mat-khau');
Route::post('/admin/dat-lai-mat-khau', [AuthController::class, 'datLaiMatKhau'])->name('admin.dat-lai-mat-khau.submit');

// Route quan ly nhan su
Route::get('/admin/nhan-su', function () {
    return view('admin_xem_truoc.nhan-vien');
});

// Route cau hinh he thong
Route::get('/admin/cai-dat', function () {
    return view('admin_xem_truoc.cai-dat');
});

// Trang ban hang
Route::get('/admin/ban-hang', function () {
    return view('admin_xem_truoc.ban-hang');
});

// Trang hoa don
Route::get('/admin/hoa-don', function () {
    return view('admin_xem_truoc.hoa-don');
});

// Trang san pham
Route::get('/admin/san-pham', function () {
    return view('admin_xem_truoc.san-pham');
});

// Trang danh muc
Route::get('/admin/danh-muc', function () {
    return view('admin_xem_truoc.danh-muc');
});

// Trang kho hang
Route::get('/admin/kho-hang', function () {
    return view('admin_xem_truoc.kho-hang');
});

// Trang khach hang
Route::get('/admin/khach-hang', function () {
    return view('admin_xem_truoc.khach-hang');
});

// Trang khuyen mai
Route::get('/admin/khuyen-mai', function () {
    return view('admin_xem_truoc.khuyen-mai');
});
