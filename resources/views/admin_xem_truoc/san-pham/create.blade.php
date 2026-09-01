@extends('admin_xem_truoc.layouts.admin')

@section('title', 'Thêm Sản phẩm - SmartMart')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1">Thêm Sản phẩm</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('admin/dashboard') }}">Admin</a></li>
                <li class="breadcrumb-item"><a href="{{ route('san-pham.index') }}">Sản phẩm</a></li>
                <li class="breadcrumb-item active">Tạo mới</li>
            </ol>
        </nav>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('san-pham.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>Quay lại danh sách
        </a>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif

<div id="formErrorBox" class="alert alert-danger py-2 px-3 mb-3 small d-none" role="alert" style="white-space:pre-line;"></div>
{{-- ============================================================
YÊU CẦU 2: CẢNH BÁO TRÙNG NHÓM THUỘC TÍNH (FRONTEND)
============================================================ --}}
<div id="duplicateAttrGroupWarning" class="alert alert-danger py-2 px-3 mb-3 small d-none" role="alert" style="white-space:pre-line;">
    <i class="fas fa-exclamation-circle me-1"></i>
</div>
{{-- ============================================================
YÊU CẦU 2: CẢNH BÁO TRÙNG LẶP BIẾN THỂ (FRONTEND)
============================================================ --}}
<div id="duplicateVariantWarning" class="alert alert-warning py-2 px-3 mb-3 small d-none" role="alert" style="white-space:pre-line;">
    <i class="fas fa-exclamation-triangle me-1"></i>
</div>

<form action="{{ route('san-pham.store') }}" method="POST" enctype="multipart/form-data" id="productForm">
    @csrf
    <input type="file" name="hinh_anh" id="nativeHinhAnhInput" class="d-none" accept="image/*">
    <div id="createProductApp" v-cloak></div>

    {{-- Sticky Footer Action Bar --}}
    <div class="card mt-4 shadow-sm border-0 sticky-bottom-action">
        <div class="card-body py-3 px-4">
            <div class="d-flex justify-content-between align-items-center">
                <span class="text-muted small"><i class="fas fa-info-circle me-1"></i> Điền đầy đủ thông tin trước khi lưu</span>
                <div class="d-flex gap-2">
                    <a href="{{ route('san-pham.index') }}" class="btn btn-outline-secondary">Hủy bỏ</a>
                    <button type="button" class="btn btn-success px-4" id="btnLuuSanPham">
                        <span class="spinner-border spinner-border-sm me-1 d-none" id="btnLuuSpinner" role="status"></span>
                        <i class="fas fa-save me-1" id="btnLuuIcon"></i>Lưu sản phẩm
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection

@section('page_scripts')
<script src="https://unpkg.com/vue@3.4.27/dist/vue.global.prod.js"></script>
<script src="https://cdn.tailwindcss.com"></script>
@php
    $attrsPayload = $thuocTinhChas->map(function ($g) {
        return [
            'id' => $g->id,
            'name' => $g->ten_thuoc_tinh,
            'values' => $g->thuocTinhCons->map(function ($v) {
                return ['id' => $v->id, 'label' => $v->ten_thuoc_tinh];
            })->values()->all(),
        ];
    })->values()->all();
    $unitsPayload = $danhMucDonVis->map(fn($u) => [
        'id' => $u->id,
        'ten_don_vi' => $u->ten_don_vi,
        'qty' => $u->so_luong_san_pham_trong_don_vi,
    ])->values()->all();
@endphp
<script>
    window.__CREATE_PRODUCT_DATA__ = {
        danhMucs: @json($danhMucs->map(fn($d) => ['id' => $d->id, 'ten' => $d->ten_danh_muc])),
        csrfToken: document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}',
        editMode: false,
        availableAttributes: @json($attrsPayload),
        availableUnits: @json($unitsPayload)
    };
</script>
<script src="{{ asset('js/admin/san-pham-create-vue.js') }}?v={{ filemtime(public_path('js/admin/san-pham-create-vue.js')) }}"></script>
<script src="{{ asset('js/admin/san-pham.js') }}?v={{ time() }}"></script>
@endsection

@section('styles')
<link rel="stylesheet" href="{{ asset('css/admin/san-pham.css') }}">
<style>
    [v-cloak] { display: none !important; }

    /* Sticky action bar ở footer */
    .sticky-bottom-action {
        position: sticky;
        bottom: 0;
        z-index: 100;
        border-radius: 8px;
        background: #fff;
        box-shadow: 0 -2px 8px rgba(0,0,0,0.06);
    }
</style>
@endsection
