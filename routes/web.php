<?php

use App\Http\Controllers\admin\AuthController;
use App\Http\Controllers\Admin\DanhMuc\DanhMucSanPhamController;
use App\Http\Controllers\Admin\SanPham\SanPhamController;
use App\Http\Controllers\admin\NhanSu\CaLamViecController;
use App\Http\Controllers\admin\NhanSu\ChiaCaController;
use App\Http\Controllers\admin\NhanSu\NguoiDungController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\admin\KhoHang\NhaCungCapController;
use App\Http\Controllers\admin\CaiDat\ThietLapSanPhamController;
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

// Route::get('/admin/login', function () {
//     return view('admin_xem_truoc.auth.login');
// });

Route::get('/admin/ban-hang', function () {
    return view('admin_xem_truoc.ban-hang');
});

Route::get('/admin/hoa-don', function () {
    return view('admin_xem_truoc.hoa-don');
});

// Route::get('/admin/danh-muc', function () {
//     return view('admin_xem_truoc.danh-muc');
// });



// Route quan ly nhan su
Route::get('/nguoi-dung', [NguoiDungController::class, 'index'])->name('nguoi-dung.index');
Route::get('/admin/nhan-su', function () {
    return redirect('/nguoi-dung');
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


Route::get('/admin/ca-lam-viec', [CaLamViecController::class, 'index'])->name('ca-lam-viec.index');
Route::get('/admin/ca-lam-viec/create', [CaLamViecController::class, 'create'])->name('ca-lam-viec.create');
Route::post('/admin/ca-lam-viec', [CaLamViecController::class, 'store'])->name('ca-lam-viec.store');
Route::get('/admin/ca-lam-viec/{caLamViec}/edit', [CaLamViecController::class, 'edit'])->name('ca-lam-viec.edit');
Route::put('/admin/ca-lam-viec/{caLamViec}', [CaLamViecController::class, 'update'])->name('ca-lam-viec.update');
Route::delete('/admin/ca-lam-viec/{caLamViec}', [CaLamViecController::class, 'destroy'])->name('ca-lam-viec.destroy');
// chia ca làm việc
Route::get('/admin/chia-ca-lam-viec', [ChiaCaController::class, 'index'])->name('chia-ca-lam-viec.index');
Route::get('/admin/chia-ca-lam-viec/create', [ChiaCaController::class, 'create'])->name('chia-ca-lam-viec.create');
Route::post('/admin/chia-ca-lam-viec', [ChiaCaController::class, 'store'])->name('chia-ca-lam-viec.store');
Route::get('/admin/chia-ca-lam-viec/export', [ChiaCaController::class, 'export'])->name('chia-ca-lam-viec.export');
Route::delete('/admin/chia-ca-lam-viec/xoa-o-lich', [ChiaCaController::class, 'destroyCell'])->name('chia-ca-lam-viec.destroy-cell');
Route::get('/admin/chia-ca-lam-viec/{chiaCaLamViec}/edit', [ChiaCaController::class, 'edit'])->name('chia-ca-lam-viec.edit');
Route::put('/admin/chia-ca-lam-viec/{chiaCaLamViec}', [ChiaCaController::class, 'update'])->name('chia-ca-lam-viec.update');
Route::delete('/admin/chia-ca-lam-viec/{chiaCaLamViec}', [ChiaCaController::class, 'destroy'])->name('chia-ca-lam-viec.destroy');

Route::get('/nguoi-dung/create', [NguoiDungController::class, 'create'])->name('nguoi-dung.create');
Route::post('/nguoi-dung', [NguoiDungController::class, 'store'])->name('nguoi-dung.store');
Route::get('/nguoi-dung/{nguoiDung}', [NguoiDungController::class, 'show'])->name('nguoi-dung.show');
Route::get('/nguoi-dung/{nguoiDung}/edit', [NguoiDungController::class, 'edit'])->name('nguoi-dung.edit');
Route::put('/nguoi-dung/{nguoiDung}', [NguoiDungController::class, 'update'])->name('nguoi-dung.update');

Route::get('/admin/cai-dat', function () {
    return view('admin_xem_truoc.cai-dat');
});




Route::get('/admin/cai-dat/san-pham', [ThietLapSanPhamController::class, 'index']);
Route::post('/admin/cai-dat/san-pham/don-vi', [ThietLapSanPhamController::class, 'storeDonVi']);
Route::delete('/admin/cai-dat/san-pham/don-vi/{id}', [ThietLapSanPhamController::class, 'destroyDonVi']);
Route::post('/admin/cai-dat/san-pham/thuoc-tinh', [ThietLapSanPhamController::class, 'storeThuocTinh']);
Route::delete('/admin/cai-dat/san-pham/thuoc-tinh/{id}', [ThietLapSanPhamController::class, 'destroyThuocTinh']);

// Trang ban hang
Route::get('/admin/ban-hang', function () {
    return view('admin_xem_truoc.ban-hang');
});

// Trang hoa don
Route::get('/admin/hoa-don', function () {
    return view('admin_xem_truoc.hoa-don');
});

// // Trang san pham
// Route::get('/admin/san-pham', function () {
//     return view('admin_xem_truoc.san-pham');
// });
Route::get('/admin/san-pham', [SanPhamController::class, 'index'])->name('san-pham.index');
Route::post('/admin/san-pham', [SanPhamController::class, 'store'])->name('san-pham.store');
Route::get('/admin/san-pham/{id}/edit', [SanPhamController::class, 'edit'])->name('san-pham.edit');
Route::put('/admin/san-pham/{id}', [SanPhamController::class, 'update'])->name('san-pham.update');
Route::delete('/admin/san-pham/{id}', [SanPhamController::class, 'destroy'])->name('san-pham.destroy');
Route::get('/admin/san-pham/{id}', [SanPhamController::class, 'show'])->name('san-pham.show');

// Trang danh muc
Route::get('/admin/danh-muc', function () {
    return view('admin_xem_truoc.danh-muc');
});

// Trang kho hang
Route::get('/admin/kho-hang', function () {
    $nhaCungCaps = NhaCungCap::orderBy('id', 'asc')->get();

    return view('admin_xem_truoc.kho-hang', compact('nhaCungCaps'));
});

// Trang khach hang
Route::get('/admin/khach-hang', function () {
    return view('admin_xem_truoc.khach-hang');
});

// Trang khuyen mai
Route::get('/admin/khuyen-mai', function () {
    return view('admin_xem_truoc.khuyen-mai');
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
