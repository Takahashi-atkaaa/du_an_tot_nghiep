@extends('admin_xem_truoc.layouts.admin')

@section('title', 'Chi tiết hoạt động - SmartMart')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <a href="{{ route('admin.canh-bao') }}" class="text-decoration-none text-muted">
            <i class="fas fa-arrow-left me-1"></i>Quay lại danh sách
        </a>
        <h4 class="fw-bold mb-1 mt-2">Chi tiết hoạt động #{{ $canhBao->id }}</h4>
    </div>
    <div>
        @if (! $canhBao->da_doc)
            <form method="POST" action="{{ route('admin.canh-bao.da-doc', $canhBao->id) }}" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-sm btn-success">
                    <i class="fas fa-check me-1"></i>Đánh dấu đã xem
                </button>
            </form>
        @else
            <span class="badge bg-light text-muted">
                <i class="fas fa-check me-1"></i>Đã xem
            </span>
        @endif
    </div>
</div>

@if (session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

<div class="card border-0 shadow-sm mb-3">
    <div class="card-body">
        <div class="row mb-3">
            <div class="col-md-8">
                <small class="text-muted d-block">Tiêu đề</small>
                <strong>{{ $canhBao->tieu_de }}</strong>
            </div>
            <div class="col-md-4">
                <small class="text-muted d-block">Thời gian</small>
                <strong>{{ $canhBao->created_at?->format('d/m/Y H:i:s') }}</strong>
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-md-6">
                <small class="text-muted d-block">Hành động</small>
                <code>{{ $canhBao->hanh_dong }}</code>
            </div>
            <div class="col-md-6">
                <small class="text-muted d-block">Người thực hiện</small>
                <strong>{{ $canhBao->nguoiDungThucHien?->ho_ten ?? 'Hệ thống' }}</strong>
            </div>
        </div>
        <div class="mb-3">
            <small class="text-muted d-block">Nội dung</small>
            <p class="mb-0">{{ $canhBao->noi_dung }}</p>
        </div>
        @if ($canhBao->url_lien_ket)
            <div class="mb-3">
                <small class="text-muted d-block">Liên kết</small>
                <a href="{{ $canhBao->url_lien_ket }}" target="_blank">{{ $canhBao->url_lien_ket }}</a>
            </div>
        @endif
    </div>
</div>

@if ($canhBao->auditLog)
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-header bg-white">
            <h5 class="mb-0"><i class="fas fa-history me-2"></i>Chi tiết audit log #{{ $canhBao->auditLog->id }}</h5>
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-4">
                    <small class="text-muted d-block">Bảng bị tác động</small>
                    <code>{{ $canhBao->auditLog->bang_bi_tac_dong ?: '—' }}</code>
                </div>
                <div class="col-md-4">
                    <small class="text-muted d-block">ID bản ghi</small>
                    <code>{{ $canhBao->auditLog->id_ban_ghi ?: '—' }}</code>
                </div>
                <div class="col-md-4">
                    <small class="text-muted d-block">IP</small>
                    <code>{{ $canhBao->auditLog->ip_address ?: '—' }}</code>
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-md-12">
                    <small class="text-muted d-block">User agent</small>
                    <small>{{ $canhBao->auditLog->user_agent ?: '—' }}</small>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <small class="text-muted d-block mb-1">Dữ liệu cũ</small>
                    <pre class="bg-light p-2 rounded small mb-0" style="max-height: 240px; overflow: auto;">{{ $canhBao->auditLog->du_lieu_cu ? json_encode($canhBao->auditLog->du_lieu_cu, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : '—' }}</pre>
                </div>
                <div class="col-md-6">
                    <small class="text-muted d-block mb-1">Dữ liệu mới</small>
                    <pre class="bg-light p-2 rounded small mb-0" style="max-height: 240px; overflow: auto;">{{ $canhBao->auditLog->du_lieu_moi ? json_encode($canhBao->auditLog->du_lieu_moi, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : '—' }}</pre>
                </div>
            </div>
        </div>
    </div>
@endif

@endsection
