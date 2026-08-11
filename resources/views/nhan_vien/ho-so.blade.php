@extends('nhan_vien.layouts.nhan_vien')

@section('title', 'Hồ sơ cá nhân')

@section('content')

@php
    $nguoiDung = $nguoiDung ?? auth()->user();
    $nguoiDung->loadMissing('vaiTro');
    $tenVaiTro = optional($nguoiDung->vaiTro)->ten_vai_tro ?? 'N/A';
    $vietTat = strtoupper(mb_substr($nguoiDung->ho_ten ?? 'NV', 0, 2));
    $gioiTinhHienTai = $nguoiDung->gioi_tinh ?? null;
@endphp

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Hồ sơ cá nhân</h1>
</div>

@if (session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

@if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="row">
    {{-- Thông tin cá nhân --}}
    <div class="col-md-4">
        <div class="card">
            <div class="card-body text-center py-5">
                <div class="mb-3">
                    <div style="width:120px;height:120px;margin:0 auto;background:#2e7d32;border-radius:50%;display:flex;align-items:center;justify-content:center;">
                        <span style="font-size:48px;color:#fff;font-weight:bold;">{{ $vietTat }}</span>
                    </div>
                </div>
                <h4>{{ $nguoiDung->ho_ten }}</h4>
                <p class="text-muted mb-1">{{ $tenVaiTro }}</p>
                @if ($nguoiDung->trang_thai == 1)
                    <span class="status-badge status-success">Đang hoạt động</span>
                @else
                    <span class="status-badge status-danger">Không hoạt động</span>
                @endif
                <hr>
                <div class="text-start">
                    <p class="mb-2"><i class="fas fa-phone me-2 text-success"></i>{{ $nguoiDung->sdt }}</p>
                    <p class="mb-0"><i class="fas fa-envelope me-2 text-success"></i>{{ $nguoiDung->email }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Form đổi mật khẩu --}}
    <div class="col-md-8">
        <div class="card">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="fas fa-key me-2 text-success"></i>Đổi mật khẩu</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('nhan-vien.ho-so.doi-mat-khau') }}" method="POST">
                    @csrf
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <p class="mb-1"><strong>Họ và tên:</strong> {{ $nguoiDung->ho_ten }}</p>
                            <p class="mb-1"><strong>Số điện thoại:</strong> {{ $nguoiDung->sdt }}</p>
                            <p class="mb-1"><strong>Email:</strong> {{ $nguoiDung->email }}</p>
                            <p class="mb-1"><strong>Vai trò:</strong> {{ $tenVaiTro }}</p>
                        </div>
                    </div>
                    <hr>
                    <div class="mb-3">
                        <label class="form-label">Mật khẩu hiện tại</label>
                        <input type="password" name="mat_khau_cu" class="form-control @error('mat_khau_cu') is-invalid @enderror" placeholder="Nhập mật khẩu hiện tại" required>
                        @error('mat_khau_cu')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Mật khẩu mới</label>
                            <input type="password" name="mat_khau_moi" class="form-control @error('mat_khau_moi') is-invalid @enderror" placeholder="Mật khẩu mới" required minlength="6">
                            @error('mat_khau_moi')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Xác nhận mật khẩu mới</label>
                            <input type="password" name="xac_nhan_mat_khau_moi" class="form-control @error('xac_nhan_mat_khau_moi') is-invalid @enderror" placeholder="Xác nhận mật khẩu mới" required minlength="6">
                            @error('xac_nhan_mat_khau_moi')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="text-end">
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-save me-2"></i>Lưu thay đổi
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="fas fa-briefcase me-2 text-success"></i>Thông tin làm việc</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6 col-lg-4">
                        <div class="border rounded p-3 h-100">
                            <div class="text-muted small mb-1">Ca làm việc gần nhất</div>
                            @if($caGanNhat && $caGanNhat->caLamViec)
                                <div class="fw-semibold">
                                    {{ $caGanNhat->caLamViec->ten_ca }}
                                    ({{ \Carbon\Carbon::parse($caGanNhat->caLamViec->gio_bat_dau)->format('H:i') }}-{{ \Carbon\Carbon::parse($caGanNhat->caLamViec->gio_ket_thuc)->format('H:i') }})
                                </div>
                                <div class="small text-muted">{{ $caGanNhat->ngay ? \Carbon\Carbon::parse($caGanNhat->ngay)->format('d/m/Y') : '-' }}</div>
                            @else
                                <div class="fw-semibold text-muted">Chưa có</div>
                            @endif
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-4">
                        <div class="border rounded p-3 h-100">
                            <div class="text-muted small mb-1">Ca làm việc hôm nay</div>
                            @if($caHomNay && $caHomNay->count() > 0)
                                <div class="fw-semibold">{{ $caHomNay->count() }} ca</div>
                                <div class="small text-muted">
                                    @foreach($caHomNay as $ca)
                                        @if($ca->caLamViec)
                                            {{ $ca->caLamViec->ten_ca }}@if(!$loop->last), @endif
                                        @endif
                                    @endforeach
                                </div>
                            @else
                                <div class="fw-semibold text-muted">Chưa có ca</div>
                            @endif
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-4">
                        <div class="border rounded p-3 h-100">
                            <div class="text-muted small mb-1">Tổng số hóa đơn trong tháng</div>
                            <div class="fw-semibold">{{ number_format($tongHoaDonThang) }}</div>
                            <div class="small text-muted">Gồm cả hóa đơn hủy</div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-4">
                        <div class="border rounded p-3 h-100">
                            <div class="text-muted small mb-1">Tổng doanh thu trong tháng</div>
                            <div class="fw-semibold">{{ number_format($tongDoanhThuThang, 0, ',', '.') }}đ</div>
                            <div class="small text-muted">Tháng {{ \Carbon\Carbon::now()->format('m/Y') }}</div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-4">
                        <div class="border rounded p-3 h-100">
                            <div class="text-muted small mb-1">Ngày vào làm</div>
                            <div class="fw-semibold">{{ $ngayVaoLam ? \Carbon\Carbon::parse($ngayVaoLam)->format('d/m/Y') : '-' }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection