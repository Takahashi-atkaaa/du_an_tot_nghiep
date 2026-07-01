@extends('admin_xem_truoc.layouts.admin')

@section('title', 'Lịch sử điểm danh - SmartMart')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1">Lịch sử điểm danh</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ url('admin/dashboard') }}">Admin</a></li>
                <li class="breadcrumb-item"><a href="{{ url('admin/nhan-su') }}">Nhân sự</a></li>
                <li class="breadcrumb-item"><a href="{{ route('diem-danh.index') }}">Điểm danh</a></li>
                <li class="breadcrumb-item active">Lịch sử</li>
            </ol>
        </nav>
    </div>
    <a href="{{ route('diem-danh.index') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i>Quay lại
    </a>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif
@if(session('warning'))
    <div class="alert alert-warning">{{ session('warning') }}</div>
@endif

{{-- Filter --}}
<div class="card table-admin mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('diem-danh.lich-su') }}" class="row g-3">
            <div class="col-md-2">
                <label class="form-label small text-muted">Từ ngày</label>
                <input type="date" name="tu_ngay" value="{{ request('tu_ngay') }}" class="form-control">
            </div>
            <div class="col-md-2">
                <label class="form-label small text-muted">Đến ngày</label>
                <input type="date" name="den_ngay" value="{{ request('den_ngay') }}" class="form-control">
            </div>
            <div class="col-md-3">
                <label class="form-label small text-muted">Nhân viên</label>
                <select name="id_nguoi_dung" class="form-select">
                    <option value="">-- Tất cả nhân viên --</option>
                    @foreach($nhanViens as $nv)
                        <option value="{{ $nv->id }}" {{ request('id_nguoi_dung') == $nv->id ? 'selected' : '' }}>{{ $nv->ho_ten }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small text-muted">Ca làm</label>
                <select name="id_ca_lam_viec" class="form-select">
                    <option value="">-- Tất cả ca --</option>
                    @foreach($caLamViecs as $ca)
                        <option value="{{ $ca->id }}" {{ request('id_ca_lam_viec') == $ca->id ? 'selected' : '' }}>{{ $ca->ten_ca }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small text-muted">Trạng thái vào</label>
                <select name="trang_thai_vao_lam" class="form-select">
                    <option value="">-- Tất cả --</option>
                    <option value="Đúng giờ" {{ request('trang_thai_vao_lam') == 'Đúng giờ' ? 'selected' : '' }}>Đúng giờ</option>
                    <option value="Đi muộn" {{ request('trang_thai_vao_lam') == 'Đi muộn' ? 'selected' : '' }}>Đi muộn</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small text-muted">Trạng thái tan ca</label>
                <select name="trang_thai_tan_ca" class="form-select">
                    <option value="">-- Tất cả --</option>
                    <option value="Đúng giờ" {{ request('trang_thai_tan_ca') == 'Đúng giờ' ? 'selected' : '' }}>Đúng giờ</option>
                    <option value="Về sớm" {{ request('trang_thai_tan_ca') == 'Về sớm' ? 'selected' : '' }}>Về sớm</option>
                    <option value="Tăng ca" {{ request('trang_thai_tan_ca') == 'Tăng ca' ? 'selected' : '' }}>Tăng ca</option>
                </select>
            </div>
            <div class="col-md-12">
                <button type="submit" class="btn btn-primary"><i class="fas fa-search me-1"></i>Lọc</button>
                <a href="{{ route('diem-danh.lich-su') }}" class="btn btn-outline-secondary ms-1"><i class="fas fa-redo me-1"></i>Đặt lại</a>
            </div>
        </form>
    </div>
</div>

<div class="card table-admin">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>STT</th>
                        <th>Nhân viên</th>
                        <th>Ngày</th>
                        <th>Ca làm</th>
                        <th>Giờ vào</th>
                        <th>Giờ tan ca</th>
                        <th>Đi muộn</th>
                        <th>Tăng ca</th>
                        <th>Trạng thái vào</th>
                        <th>Trạng thái tan ca</th>
                        <th class="text-end">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($diemDanhs as $item)
                        <tr>
                            <td>{{ $loop->iteration + ($diemDanhs->currentPage() - 1) * $diemDanhs->perPage() }}</td>
                            <td>{{ $item->chiaCaLamViec->nguoiDung->ho_ten ?? 'N/A' }}</td>
                            <td>{{ $item->gio_vao ? $item->gio_vao->format('d/m/Y') : 'N/A' }}</td>
                            <td>{{ $item->chiaCaLamViec->caLamViec->ten_ca ?? 'N/A' }}</td>
                            <td>{{ $item->gio_vao ? $item->gio_vao->format('H:i') : '--' }}</td>
                            <td>{{ $item->gio_tan_ca ? $item->gio_tan_ca->format('H:i') : '--' }}</td>
                            <td>
                                @if($item->so_gio_di_lam_muon > 0)
                                    <span class="text-danger">{{ $item->so_gio_di_lam_muon }}p</span>
                                @else
                                    <span class="text-success">-</span>
                                @endif
                            </td>
                            <td>
                                @if($item->so_gio_lam_them > 0)
                                    <span class="text-info">+{{ $item->so_gio_lam_them }}h</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($item->trang_thai_vao_lam === 'Đi muộn')
                                    <span class="badge bg-danger">{{ $item->trang_thai_vao_lam }}</span>
                                @else
                                    <span class="badge bg-success">{{ $item->trang_thai_vao_lam ?? '--' }}</span>
                                @endif
                            </td>
                            <td>
                                @if($item->trang_thai_tan_ca === 'Về sớm')
                                    <span class="badge bg-warning text-dark">{{ $item->trang_thai_tan_ca }}</span>
                                @elseif($item->trang_thai_tan_ca === 'Tăng ca')
                                    <span class="badge bg-info">{{ $item->trang_thai_tan_ca }}</span>
                                @elseif($item->trang_thai_tan_ca === 'Đúng giờ')
                                    <span class="badge bg-success">{{ $item->trang_thai_tan_ca }}</span>
                                @else
                                    <span class="badge bg-secondary">{{ $item->trang_thai_tan_ca ?? '--' }}</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <form action="{{ route('diem-danh.destroy', $item) }}" method="POST" class="d-inline" onsubmit="return confirm('Hủy điểm danh này?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger btn-action" title="Hủy điểm danh">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" class="text-center text-muted py-5">Không có bản ghi nào.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer bg-white">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div class="text-muted">
                Hiển thị {{ $diemDanhs->count() }} / {{ $diemDanhs->total() }} bản ghi
            </div>
            <div>
                {{ $diemDanhs->withQueryString()->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>
</div>
@endsection
