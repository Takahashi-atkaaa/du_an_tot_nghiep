@extends('admin_xem_truoc.layouts.admin')

@section('title', 'Danh sách điểm danh - SmartMart')

<style>
    .info-card {
        border: none;
        border-radius: 18px;
        box-shadow: 0 2px 15px rgba(0,0,0,0.08);
    }

    .icon-box {
        width: 60px;
        height: 60px;
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 28px;
    }

    .icon-primary { background: #e7f0ff; color: #2563eb; }
    .icon-success { background: #eaf7ef; color: #198754; }
    .icon-warning { background: #fff4df; color: #f59e0b; }
    .icon-danger  { background: #fdecec; color: #dc3545; }
    .icon-purple  { background: #f3e8ff; color: #9333ea; }
</style>

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1">Quản lý điểm danh</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ url('admin/dashboard') }}">Admin</a></li>
                <li class="breadcrumb-item"><a href="{{ url('admin/nhan-su') }}">Nhân sự</a></li>
                <li class="breadcrumb-item active">Điểm danh</li>
            </ol>
        </nav>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('diem-danh.lich-su') }}" class="btn btn-outline-info">
            <i class="fas fa-history me-1"></i>Lịch sử
        </a>
        <a href="{{ route('diem-danh.thong-ke') }}" class="btn btn-outline-purple" style="background:#f3e8ff;color:#9333ea;border-color:#9333ea;">
            <i class="fas fa-chart-bar me-1"></i>Thống kê
        </a>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif
@if(session('warning'))
    <div class="alert alert-warning">{{ session('warning') }}</div>
@endif

{{-- Thông tin tổng quan --}}
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card info-card">
            <div class="card-body d-flex align-items-center">
                <div class="icon-box icon-primary me-3"><i class="fas fa-clock"></i></div>
                <div>
                    <div class="text-muted small">Giờ hiện tại</div>
                    <h5 id="clock" class="mb-0"></h5>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card info-card">
            <div class="card-body d-flex align-items-center">
                <div class="icon-box icon-success me-3"><i class="fas fa-calendar-alt"></i></div>
                <div>
                    <div class="text-muted small">Ngày</div>
                    <h5 class="mb-0">{{ \Carbon\Carbon::parse($ngay)->format('d/m/Y') }}</h5>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card info-card">
            <div class="card-body d-flex align-items-center">
                <div class="icon-box icon-purple me-3"><i class="fas fa-users"></i></div>
                <div>
                    <div class="text-muted small">Tổng nhân viên</div>
                    <h5 class="mb-0">{{ $tongNhanVien }}</h5>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card info-card">
            <div class="card-body d-flex align-items-center">
                <div class="icon-box icon-success me-3"><i class="fas fa-check-circle"></i></div>
                <div>
                    <div class="text-muted small">Đã điểm danh</div>
                    <h5 class="mb-0">{{ $daDiemDanh }}</h5>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card info-card">
            <div class="card-body d-flex align-items-center">
                <div class="icon-box icon-danger me-3"><i class="fas fa-user-times"></i></div>
                <div>
                    <div class="text-muted small">Chưa điểm danh</div>
                    <h5 class="mb-0">{{ $chuaDiemDanh }}</h5>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Filter --}}
<div class="card table-admin mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('diem-danh.index') }}" class="row g-3">
            <input type="hidden" name="ngay" value="{{ $ngay }}">
            <div class="col-md-3">
                <select name="id_ca_lam_viec" class="form-select">
                    <option value="">-- Tất cả ca --</option>
                    @foreach($caLamViecs as $ca)
                        <option value="{{ $ca->id }}" {{ request('id_ca_lam_viec') == $ca->id ? 'selected' : '' }}>
                            {{ $ca->ten_ca }} ({{ substr($ca->gio_bat_dau, 0, 5) }} - {{ substr($ca->gio_ket_thuc, 0, 5) }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <select name="id_nguoi_dung" class="form-select">
                    <option value="">-- Tất cả nhân viên --</option>
                    @foreach($nhanViens as $nv)
                        <option value="{{ $nv->id }}" {{ request('id_nguoi_dung') == $nv->id ? 'selected' : '' }}>{{ $nv->ho_ten }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-outline-primary w-100"><i class="fas fa-search me-1"></i>Lọc</button>
            </div>
        </form>
    </div>
</div>

{{-- Bảng điểm danh --}}
<div class="card table-admin">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>STT</th>
                        <th>Nhân viên</th>
                        <th>Ca làm</th>
                        <th>Giờ ca</th>
                        <th>Giờ vào</th>
                        <th>Giờ tan ca</th>
                        <th>Trạng thái vào</th>
                        <th>Trạng thái tan ca</th>
                        <th class="text-end" style="width: 200px;">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($danhSachChiaCa as $item)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $item->nguoiDung->ho_ten ?? 'N/A' }}</td>
                            <td>{{ $item->caLamViec->ten_ca ?? 'N/A' }}</td>
                            <td>
                                @if($item->caLamViec)
                                    {{ substr($item->caLamViec->gio_bat_dau, 0, 5) }} - {{ substr($item->caLamViec->gio_ket_thuc, 0, 5) }}
                                @else
                                    N/A
                                @endif
                            </td>
                            <td>
                                @if($item->diemDanh?->gio_vao)
                                    {{ $item->diemDanh->gio_vao->format('H:i') }}
                                @else
                                    <span class="text-muted">--</span>
                                @endif
                            </td>
                            <td>
                                @if($item->diemDanh?->gio_tan_ca)
                                    {{ $item->diemDanh->gio_tan_ca->format('H:i') }}
                                @else
                                    <span class="text-muted">--</span>
                                @endif
                            </td>
                            <td>
                                @if($item->diemDanh)
                                    @if($item->diemDanh->trang_thai_vao_lam === 'Đi muộn')
                                        <span class="badge bg-danger">{{ $item->diemDanh->trang_thai_vao_lam }}
                                            @if($item->diemDanh->so_gio_di_lam_muon > 0)
                                                ({{ $item->diemDanh->so_gio_di_lam_muon }}p)
                                            @endif
                                        </span>
                                    @else
                                        <span class="badge bg-success">{{ $item->diemDanh->trang_thai_vao_lam }}</span>
                                    @endif
                                @else
                                    <span class="badge bg-secondary">Chưa điểm danh</span>
                                @endif
                            </td>
                            <td>
                                @if($item->diemDanh?->trang_thai_tan_ca)
                                    @if($item->diemDanh->trang_thai_tan_ca === 'Về sớm')
                                        <span class="badge bg-warning text-dark">{{ $item->diemDanh->trang_thai_tan_ca }}</span>
                                    @elseif($item->diemDanh->trang_thai_tan_ca === 'Tăng ca')
                                        <span class="badge bg-info">{{ $item->diemDanh->trang_thai_tan_ca }}
                                            @if($item->diemDanh->so_gio_lam_them > 0)
                                                (+{{ $item->diemDanh->so_gio_lam_them }}h)
                                            @endif
                                        </span>
                                    @else
                                        <span class="badge bg-success">{{ $item->diemDanh->trang_thai_tan_ca }}</span>
                                    @endif
                                @else
                                    <span class="badge bg-secondary">--</span>
                                @endif
                            </td>
                            <td class="text-end">
                                @if(!$item->diemDanh)
                                    <form action="{{ route('diem-danh.xac-nhan', $item) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success btn-action" title="Xác nhận điểm danh">
                                            <i class="fas fa-check"></i> Xác nhận
                                        </button>
                                    </form>
                                @elseif(!$item->diemDanh->gio_tan_ca)
                                    <form action="{{ route('diem-danh.ket-thuc-ca', $item) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-warning btn-action" title="Kết thúc ca">
                                            <i class="fas fa-sign-out-alt"></i> Kết thúc ca
                                        </button>
                                    </form>
                                @else
                                    <span class="badge bg-success"><i class="fas fa-check-double me-1"></i>Hoàn tất</span>
                                @endif

                                @if($item->diemDanh)
                                    <form action="{{ route('diem-danh.destroy', $item->diemDanh) }}" method="POST" class="d-inline" onsubmit="return confirm('Hủy điểm danh này?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger btn-action" title="Hủy">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted py-5">Không có nhân viên nào được phân ca ngày này.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function CapNhatGio() {
        const now = new Date();
        const gio = String(now.getHours()).padStart(2, '0');
        const phut = String(now.getMinutes()).padStart(2, '0');
        const giay = String(now.getSeconds()).padStart(2, '0');
        document.getElementById('clock').innerText = gio + ':' + phut + ':' + giay;
    }
    CapNhatGio();
    setInterval(CapNhatGio, 1000);
</script>
@endsection
