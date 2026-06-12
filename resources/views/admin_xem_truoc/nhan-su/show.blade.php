@extends('admin_xem_truoc.layouts.admin')

@section('title', 'Chi tiết nhân sự - SmartMart')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1">Chi tiết nhân sự</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ url('admin/dashboard') }}">Admin</a></li>
                <li class="breadcrumb-item"><a href="{{ route('nguoi-dung.index') }}">Nhân sự</a></li>
                <li class="breadcrumb-item active">Chi tiết</li>
            </ol>
        </nav>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('nguoi-dung.edit', $nguoiDung) }}" class="btn btn-primary">
            <i class="fas fa-edit me-2"></i>Chỉnh sửa
        </a>
        <a href="{{ route('nguoi-dung.index') }}" class="btn btn-outline-secondary">Quay lại</a>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-4">
        <div class="card table-admin h-100">
            <div class="card-body text-center">
                <div class="mb-3">
                    @if($nguoiDung->anh_dai_dien)
                        <img src="{{ asset('storage/' . $nguoiDung->anh_dai_dien) }}" alt="{{ $nguoiDung->ho_ten }}" class="rounded-circle border" style="width: 140px; height: 140px; object-fit: cover;">
                    @else
                        <div class="rounded-circle bg-light border d-inline-flex align-items-center justify-content-center" style="width: 140px; height: 140px;">
                            <i class="fas fa-user fa-3x text-muted"></i>
                        </div>
                    @endif
                </div>

                <h5 class="mb-1">{{ $nguoiDung->ho_ten }}</h5>
                <p class="text-muted mb-2">Mã ND{{ str_pad((string) $nguoiDung->id, 4, '0', STR_PAD_LEFT) }}</p>

                <div class="d-flex justify-content-center gap-2 flex-wrap mb-3">
                    <span class="badge bg-secondary">{{ $nguoiDung->vai_tro }}</span>
                    <span class="status-badge {{ $nguoiDung->trang_thai ? 'status-active' : 'status-inactive' }}">
                        {{ $nguoiDung->trang_thai ? 'Hoạt động' : 'Ngưng hoạt động' }}
                    </span>
                </div>

                <div class="text-start">
                    <div class="mb-2"><strong>Email:</strong> {{ $nguoiDung->email }}</div>
                    <div class="mb-2"><strong>Số điện thoại:</strong> {{ $nguoiDung->sdt }}</div>
                    <div class="mb-2"><strong>Giới tính:</strong> {{ $nguoiDung->gioi_tinh ?? '-' }}</div>
                    <div class="mb-2"><strong>CCCD:</strong> {{ $nguoiDung->cccd ?? '-' }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card table-admin mb-4">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 fw-bold">Thông tin hồ sơ</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="border rounded p-3 h-100">
                            <div class="text-muted small mb-1">Họ tên</div>
                            <div class="fw-semibold">{{ $nguoiDung->ho_ten }}</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="border rounded p-3 h-100">
                            <div class="text-muted small mb-1">Email</div>
                            <div class="fw-semibold">{{ $nguoiDung->email }}</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="border rounded p-3 h-100">
                            <div class="text-muted small mb-1">Số điện thoại</div>
                            <div class="fw-semibold">{{ $nguoiDung->sdt }}</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="border rounded p-3 h-100">
                            <div class="text-muted small mb-1">Vai trò</div>
                            <div class="fw-semibold">{{ $nguoiDung->vai_tro }}</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="border rounded p-3 h-100">
                            <div class="text-muted small mb-1">Giới tính</div>
                            <div class="fw-semibold">{{ $nguoiDung->gioi_tinh ?? '-' }}</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="border rounded p-3 h-100">
                            <div class="text-muted small mb-1">CCCD</div>
                            <div class="fw-semibold">{{ $nguoiDung->cccd ?? '-' }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card table-admin">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 fw-bold">Ảnh giấy tờ</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="border rounded p-3 text-center h-100">
                            <div class="text-muted small mb-2">Ảnh đại diện</div>
                            @if($nguoiDung->anh_dai_dien)
                                <img src="{{ asset('storage/' . $nguoiDung->anh_dai_dien) }}" alt="Ảnh đại diện" class="img-fluid rounded" style="max-height: 180px; object-fit: cover;">
                            @else
                                <div class="text-muted py-5">Chưa có ảnh</div>
                            @endif
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="border rounded p-3 text-center h-100">
                            <div class="text-muted small mb-2">CCCD mặt trước</div>
                            @if($nguoiDung->anh_cccd_mat_truoc)
                                <img src="{{ asset('storage/' . $nguoiDung->anh_cccd_mat_truoc) }}" alt="CCCD mặt trước" class="img-fluid rounded" style="max-height: 180px; object-fit: cover;">
                            @else
                                <div class="text-muted py-5">Chưa có ảnh</div>
                            @endif
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="border rounded p-3 text-center h-100">
                            <div class="text-muted small mb-2">CCCD mặt sau</div>
                            @if($nguoiDung->anh_cccd_mat_sau)
                                <img src="{{ asset('storage/' . $nguoiDung->anh_cccd_mat_sau) }}" alt="CCCD mặt sau" class="img-fluid rounded" style="max-height: 180px; object-fit: cover;">
                            @else
                                <div class="text-muted py-5">Chưa có ảnh</div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection