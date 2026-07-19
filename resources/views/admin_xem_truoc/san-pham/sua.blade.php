@extends('admin_xem_truoc.layouts.admin')

@section('title', 'Sửa Sản phẩm - SmartMart')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1">Sửa Sản phẩm</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('admin/dashboard') }}">Admin</a></li>
                <li class="breadcrumb-item"><a href="{{ url('admin/san-pham') }}">Sản phẩm</a></li>
                <li class="breadcrumb-item active">{{ $product->ten_san_pham }}</li>
            </ol>
        </nav>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif
<div id="formErrorBox" class="alert alert-danger d-none mb-3"></div>
{{-- ============================================================
YÊU CẦU 2: CẢNH BÁO TRÙNG NHÓM THUỘC TÍNH (FRONTEND)
============================================================ --}}
<div id="duplicateAttrGroupWarning" class="alert alert-danger mb-3 d-none" role="alert" style="white-space:pre-line;">
    <i class="fas fa-exclamation-circle me-1"></i>
</div>
{{-- ============================================================
YÊU CẦU 2: CẢNH BÁO TRÙNG LẶP BIẾN THỂ (FRONTEND)
============================================================ --}}
<div id="duplicateVariantWarning" class="alert alert-warning mb-3 d-none" role="alert" style="white-space:pre-line;">
    <i class="fas fa-exclamation-triangle me-1"></i>
</div>

<div class="modal fade show d-block" id="editProductModal" tabindex="-1" style="background:rgba(0,0,0,0.5);">
    <div class="modal-dialog modal-xl modal-dialog-centered" style="max-width:1100px;margin:2rem auto;">
        <div class="modal-content" style="max-height:calc(100vh - 4rem);display:flex;flex-direction:column;">
            <div class="modal-header" style="background:linear-gradient(135deg,#0d6efd 0%,#0b5ed7 100%);color:white;flex-shrink:0;padding:1rem 1.5rem;">
                <div>
                    <h5 class="modal-title fw-bold mb-0"><i class="fas fa-edit me-2"></i>SỬA SẢN PHẨM</h5>
                    <small class="text-white-50">Cập nhật thông tin sản phẩm và các biến thể</small>
                </div>
                <a href="{{ url('admin/san-pham') }}" class="btn btn-sm btn-light"><i class="fas fa-arrow-left me-1"></i>Quay lại</a>
            </div>
            <form action="{{ url('admin/san-pham/' . $product->id) }}" method="POST" enctype="multipart/form-data" id="productForm">
                @csrf
                <input type="hidden" name="_method" value="PUT">
                <div class="modal-body p-3" id="editProductModalBody" style="overflow-y:auto;flex:1;padding:1.25rem 1.5rem;">
                    <div id="variantIdsContainer" class="d-none"></div>
                    <div id="editProductApp" v-cloak></div>
                </div>
                <div class="modal-footer bg-light" style="flex-shrink:0;padding:0.75rem 1.5rem;">
                    <div class="d-flex justify-content-between w-100 align-items-center">
                        <span class="text-muted small"><i class="fas fa-info-circle me-1"></i> Kiểm tra lại thông tin trước khi lưu</span>
                        <div class="d-flex gap-2">
                            <a href="{{ url('admin/san-pham') }}" class="btn btn-outline-secondary">Hủy bỏ</a>
                            <button type="button" class="btn btn-success px-4" id="btnLuuSanPham" disabled>
                                <span class="spinner-border spinner-border-sm me-1 d-none" id="btnLuuSpinner" role="status"></span>
                                <i class="fas fa-save me-1" id="btnLuuIcon"></i>Lưu thay đổi
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('page_scripts')
<script src="https://unpkg.com/html5-qrcode@2.3.7/minified/html5-qrcode.min.js"></script>
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
    'id'   => $u->id,
    'name' => $u->ten_hien_thi,
    'qty'  => $u->so_luong_san_pham_trong_don_vi,
])->values()->all();
@endphp
<script>
window.thuocTinhChasData = @json($thuocTinhChas);
window.__CREATE_PRODUCT_DATA__ = {
    danhMucs: @json($danhMucs->map(fn($d)=>['id'=>$d->id,'ten'=>$d->ten_danh_muc])),
    csrfToken: '{{ csrf_token() }}',
    editMode: true,
    productData: @json($product->toEditVueData()),
    availableAttributes: @json($attrsPayload),
    availableUnits: @json($unitsPayload)
};
</script>
<script src="{{ asset('js/admin/san-pham-edit-vue.js') }}"></script>
<script src="{{ asset('js/admin/san-pham.js') }}"></script>
@endsection

@section('styles')
<link rel="stylesheet" href="{{ asset('css/admin/san-pham.css') }}">
<style>
[v-cloak] { display: none !important; }
#editProductModal {
    padding-left: 0;
    padding-right: 0;
}
</style>
@endsection
