@extends('admin_xem_truoc.layouts.admin')

@section('title', 'Nhật ký hoạt động - SmartMart')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1">Nhật ký hoạt động</h4>
        <p class="text-muted mb-0">
            <span class="badge bg-primary">{{ $soChuaDoc }}</span> hoạt động chưa xem
        </p>
    </div>
    <div>
        @if ($soChuaDoc > 0)
            <form method="POST" action="{{ route('admin.canh-bao.doc-tat-ca') }}" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-sm btn-outline-success">
                    <i class="fas fa-check-double me-1"></i>Đánh dấu tất cả đã xem
                </button>
            </form>
        @endif
    </div>
</div>

@if (session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

{{-- Bộ lọc --}}
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body">
        <form method="GET" action="{{ route('admin.canh-bao') }}">
            <div class="row g-2">
                <div class="col-md-4">
                    <select name="chua_doc" class="form-select">
                        <option value="">Tất cả trạng thái</option>
                        <option value="1" {{ ($boLoc['chua_doc'] ?? '') == '1' ? 'selected' : '' }}>Chưa xem</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <input type="date" name="tu_ngay" class="form-control" value="{{ $boLoc['tu_ngay'] ?? '' }}" placeholder="Từ ngày">
                </div>
                <div class="col-md-3">
                    <input type="date" name="den_ngay" class="form-control" value="{{ $boLoc['den_ngay'] ?? '' }}" placeholder="Đến ngày">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-filter me-1"></i>Lọc
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Danh sách --}}
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Thời gian</th>
                        <th>Người thực hiện</th>
                        <th>Hành động</th>
                        <th>Chi tiết</th>
                        <th>Trạng thái</th>
                        <th class="text-end"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($canhBaos as $cb)
                        <tr class="{{ $cb->da_doc ? '' : 'fw-semibold table-active' }}">
                            <td>
                                <small>{{ $cb->created_at?->format('d/m/Y H:i') }}</small>
                            </td>
                            <td>
                                <i class="fas fa-user-circle text-muted me-1"></i>
                                <strong>{{ $cb->nguoiDungThucHien?->ho_ten ?? 'Hệ thống' }}</strong>
                            </td>
                            <td>
                                <code class="small">{{ $cb->hanh_dong }}</code>
                            </td>
                            <td>
                                <a href="{{ route('admin.canh-bao.chi-tiet', $cb->id) }}" class="text-decoration-none text-dark">
                                    {{ $cb->tieu_de }}
                                </a>
                                @if ($cb->noi_dung)
                                    <div class="text-muted small">{{ $cb->noi_dung }}</div>
                                @endif
                            </td>
                            <td>
                                @if ($cb->da_doc)
                                    <span class="badge bg-light text-muted">
                                        <i class="fas fa-check me-1"></i>Đã xem
                                    </span>
                                @else
                                    <span class="badge bg-primary">
                                        <i class="fas fa-circle me-1"></i>Mới
                                    </span>
                                @endif
                            </td>
                            <td class="text-end">
                                <a href="{{ route('admin.canh-bao.chi-tiet', $cb->id) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">Chưa có hoạt động nào.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if ($canhBaos->hasPages())
        <div class="card-footer bg-white">
            {{ $canhBaos->withQueryString()->links() }}
        </div>
    @endif
</div>

@endsection