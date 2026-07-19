@extends('admin_xem_truoc.layouts.admin')

@section('title', 'Quản lý Sản phẩm - SmartMart')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1">Quản lý Sản phẩm</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('admin/dashboard') }}">Admin</a></li>
                <li class="breadcrumb-item active">Sản phẩm</li>
            </ol>
        </nav>
    </div>
    <div class="d-flex gap-2">
        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#importProductModal">
            <i class="fas fa-file-import me-2"></i>Import
        </button>
        <button class="btn btn-outline-success" id="btnExportExcel">
            <i class="fas fa-file-export me-2"></i>Export
        </button>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addProductModal">
            <i class="fas fa-plus me-2"></i>Thêm sản phẩm
        </button>
        <button class="btn btn-outline-secondary" id="startQrScanBtn">
            <i class="fas fa-barcode me-2"></i>Quét mã vạch
        </button>
        <a href="{{ url('admin/san-pham/trash') }}" class="btn btn-outline-danger">
            <i class="fas fa-trash me-2"></i>Thùng rác
        </a>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif

<div class="card table-admin mb-4">
    <div class="card-body">
        <form action="{{ url('admin/san-pham') }}" method="GET">
            <div class="row g-3">
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                        <input type="text" id="searchKeywordInput" class="form-control" name="keyword" value="{{ $keyword ?? '' }}" placeholder="Tìm kiếm sản phẩm...">
                    </div>
                </div>
                <div class="col-md-3">
                    <select class="form-select" name="danh_muc">
                        <option value="">Tất cả danh mục</option>
                        @foreach($danhMucs as $danhMuc)
                            <option value="{{ $danhMuc->id }}" {{ (string)$danhMuc->id === (string)($danhMucId ?? '') ? 'selected' : '' }}>{{ $danhMuc->ten_danh_muc }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <select class="form-select" name="trang_thai">
                        <option value="">Tất cả trạng thái</option>
                        <option value="1" {{ $trangThai === '1' || $trangThai === 1 ? 'selected' : '' }}>Đang bán</option>
                        <option value="0" {{ $trangThai === '0' || $trangThai === 0 ? 'selected' : '' }}>Ngừng bán</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-outline-secondary w-100">
                        <i class="fas fa-filter me-2"></i>Lọc
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="qrScannerModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Quét mã vạch</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="qrScanner" style="width:100%; min-height:400px;"></div>
                <div class="mt-3 text-center">
                    <button type="button" class="btn btn-secondary" id="stopQrScanBtn">Dừng quét</button>
                </div>
            </div>
        </div>
    </div>
</div>

<form id="bulkActionForm" action="{{ url('admin/san-pham/bulk-action') }}" method="POST" style="display:none;">
    @csrf
    <input type="hidden" name="action" id="bulkActionInput">
    <div id="selectedIdsContainer"></div>
</form>

<div class="card">
    <div class="card-body p-0">
        <div class="d-flex justify-content-between align-items-center px-3 py-2 bg-light border-bottom">
            <div class="d-flex align-items-center gap-3">
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" id="selectAllCheckbox">
                    <label class="form-check-label text-muted" for="selectAllCheckbox">Chọn tất cả</label>
                </div>
                <div id="bulkActionButtons" class="d-none">
                    <span class="text-muted me-2" id="selectedCount">0 đã chọn</span>
                    <button type="button" class="btn btn-sm btn-success" onclick="submitBulkAction('activate')"><i class="fas fa-check me-1"></i>Bật</button>
                    <button type="button" class="btn btn-sm btn-warning" onclick="submitBulkAction('deactivate')"><i class="fas fa-ban me-1"></i>Tắt</button>
                    <button type="button" class="btn btn-sm btn-danger" onclick="submitBulkAction('delete')"><i class="fas fa-trash me-1"></i>Xóa</button>
                </div>
            </div>
            <div class="d-flex align-items-center gap-2">
                <span class="text-muted small">Tổng: <strong id="totalProducts">{{ $sanPhams->total() }}</strong> sản phẩm</span>
            </div>
        </div>

        @if($sanPhams->count() > 0)
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th style="width:40px;"></th>
                        <th style="width:60px;">Ảnh</th>
                        <th>Tên sản phẩm</th>
                        <th>Danh mục</th>
                        <th style="width:100px;">Đơn vị tính</th>
                        <th style="width:130px;">Biến thể</th>
                        <th style="width:110px;">Giá bán</th>
                        <th style="width:80px;">Tồn kho</th>
                        <th style="width:100px;">Trạng thái</th>
                    </tr>
                </thead>
                <tbody id="productTableBody">
                    @foreach($sanPhams as $sp)
                        @php
                            $rows = $sp->flattenedRows;
                            $hasMoreThanOneRow = $rows->count() > 1;
                            $firstRow = $rows->first();
                        @endphp

                        @if($rows->isNotEmpty())
                        {{-- DÒNG CHÍNH (dòng đầu tiên) --}}
                        <tr class="product-parent-row {{ !$firstRow->trang_thai ? 'table-secondary opacity-50' : '' }}"
                            style="cursor:pointer;"
                            data-id="{{ $firstRow->variant->id ?? $sp->id }}"
                            data-variant-id="{{ $firstRow->variant->id ?? '' }}"
                            data-unit-id="{{ $firstRow->unit->id ?? '' }}"
                            data-product-id="{{ $sp->id }}"
                            data-target-id="{{ $sp->id }}"
                            data-row-type="goc">

                            {{-- Toggle expand --}}
                            <td onclick="event.stopPropagation();">
                                <div class="d-flex align-items-center gap-1">
                                    @if($hasMoreThanOneRow)
                                        <button class="btn btn-sm btn-light p-0 border-0 expand-btn"
                                                id="expandBtn{{ $sp->id }}"
                                                onclick="event.stopPropagation(); window.toggleVariants && window.toggleVariants({{ $sp->id }})"
                                                title="Mở rộng">
                                            <i class="fas fa-chevron-down" style="font-size:0.7rem; transition:transform 0.2s;"></i>
                                        </button>
                                    @endif
                                    <input type="checkbox" class="form-check-input product-checkbox"
                                           value="{{ $firstRow->variant->id ?? $sp->id }}"
                                           data-product-id="{{ $sp->id }}"
                                           data-type="{{ $firstRow->loai_dong }}"
                                           onclick="event.stopPropagation();">
                                </div>
                            </td>

                            {{-- Hình ảnh --}}
                            <td>
                                @if(!empty($firstRow->hinh_anh))
                                    <img src="{{ asset($firstRow->hinh_anh) }}" alt="{{ $sp->ten_san_pham }}"
                                         style="width:48px;height:48px;object-fit:cover;border-radius:6px;">
                                @elseif(!empty($sp->hinh_anh))
                                    <img src="{{ asset($sp->hinh_anh) }}" alt="{{ $sp->ten_san_pham }}"
                                         style="width:48px;height:48px;object-fit:cover;border-radius:6px;">
                                @else
                                    <div style="width:48px;height:48px;border-radius:6px;background:#f0f0f0;display:flex;align-items:center;justify-content:center;">
                                        <i class="fas fa-image text-muted"></i>
                                    </div>
                                @endif
                            </td>

                            {{-- Tên sản phẩm + số dòng --}}
                            <td>
                                <div>
                                    <span class="fw-semibold" style="font-size:0.9rem;">{{ $sp->ten_san_pham }}</span>
                                    @if(!empty($sp->thuong_hieu))
                                        <div class="small text-muted">{{ $sp->thuong_hieu }}</div>
                                    @endif
                                    @if($hasMoreThanOneRow)
                                        <div class="mt-1">
                                            <span class="badge bg-light text-dark border" style="font-size:0.68rem;">
                                                <i class="fas fa-layer-group me-1"></i>{{ $rows->count() }} đơn vị
                                            </span>
                                        </div>
                                    @endif
                                </div>
                            </td>

                            {{-- Danh mục --}}
                            <td>
                                <span class="text-muted small">{{ $sp->danhMuc?->ten_danh_muc ?? '-' }}</span>
                            </td>

                            {{-- Đơn vị tính (dòng đầu = gốc) --}}
                            <td>
                                <span class="text-muted small">{{ $firstRow->ten_don_vi ?: '—' }}</span>
                            </td>

                            {{-- Biến thể (dòng đầu) --}}
                            <td>
                                @if(!empty($firstRow->ten_bien_the_display))
                                    <span class="small">{{ $firstRow->ten_bien_the_display }}</span>
                                @else
                                    <span class="text-muted small">—</span>
                                @endif
                            </td>

                            {{-- Giá bán (dòng đầu) --}}
                            <td>
                                <span class="fw-bold text-primary" style="font-size:0.88rem;">
                                    {{ number_format((float)$firstRow->gia_ban, 0, ',', '.') }} d
                                </span>
                            </td>

                            {{-- Tồn kho (dòng đầu) --}}
                            <td>
                                <span class="{{ $firstRow->so_luong_ton <= 0 ? 'text-danger' : ($firstRow->so_luong_ton <= 10 ? 'text-warning' : 'text-muted') }} small">
                                    {{ $firstRow->so_luong_ton }}
                                </span>
                            </td>

                            {{-- Trạng thái (dòng đầu) --}}
                            <td>
                                @if(!$firstRow->trang_thai)
                                    <span class="badge bg-danger">Ngừng bán</span>
                                @elseif($firstRow->so_luong_ton <= 0)
                                    <span class="badge bg-secondary">Hết hàng</span>
                                @elseif($firstRow->so_luong_ton <= 10)
                                    <span class="badge bg-warning text-dark">Sắp hết</span>
                                @else
                                    <span class="badge bg-success">Còn hàng</span>
                                @endif
                            </td>
                        </tr>
                        @endif

                        @if($rows->count() > 1)
                        {{-- CÁC DÒNG CON (các đơn vị còn lại) --}}
                        @foreach($rows->slice(1) as $rowIndex => $row)
                            <tr id="variantRow{{ $sp->id }}_{{ $rowIndex }}"
                                class="variant-child-row"
                                style="display:none; background:#fafafa; cursor:pointer;"
                                data-id="{{ $row->variant->id ?? $sp->id }}"
                                data-variant-id="{{ $row->variant->id ?? '' }}"
                                data-unit-id="{{ $row->unit->id ?? '' }}"
                                data-target-id="{{ $row->variant->id ?? $sp->id }}"
                                data-type="{{ $row->loai_dong }}"
                                data-row-type="{{ $row->loai_dong }}"
                                data-product-id="{{ $sp->id }}">
                                <td></td>
                                <td>
                                    @if(!empty($row->hinh_anh))
                                        <img src="{{ asset($row->hinh_anh) }}" alt=""
                                             style="width:40px;height:40px;object-fit:cover;border-radius:4px;">
                                    @else
                                        <div style="width:40px;height:40px;border-radius:4px;background:#f0f0f0;display:flex;align-items:center;justify-content:center;">
                                            <i class="fas fa-cube text-muted" style="font-size:0.8rem;"></i>
                                        </div>
                                    @endif
                                </td>
                                <td style="padding-left: 2.5rem !important;">
                                    <div>
                                        <div class="d-flex align-items-center gap-2">
                                            <i class="fas fa-arrow-turn-down-right text-muted" style="font-size:0.55rem;"></i>
                                            <span class="fw-semibold" style="font-size:0.85rem;">
                                                {{ $row->ten_don_vi }}
                                            </span>
                                            @if($row->loai_dong === 'quy_doi')
                                                <span class="badge bg-info" style="font-size:0.6rem;">
                                                    x{{ $row->ty_le }} {{ $firstRow->ten_don_vi }}
                                                </span>
                                            @endif
                                        </div>
                                        @if(!empty($row->ma_vach))
                                            <div class="small text-muted" style="padding-left: 1.1rem;">#{{ $row->ma_vach }}</div>
                                        @endif
                                    </div>
                                </td>
                                <td><span class="text-muted small">—</span></td>

                                {{-- Đơn vị tính --}}
                                <td>
                                    <span class="text-muted small">{{ $row->ten_don_vi }}</span>
                                </td>

                                {{-- Biến thể --}}
                                <td>
                                    @if(!empty($row->ten_bien_the_display))
                                        <span class="small">{{ $row->ten_bien_the_display }}</span>
                                    @else
                                        <span class="text-muted small">—</span>
                                    @endif
                                </td>

                                {{-- Giá bán --}}
                                <td>
                                    <span class="fw-bold text-primary" style="font-size:0.85rem;">
                                        {{ number_format((float)$row->gia_ban, 0, ',', '.') }} d
                                    </span>
                                </td>

                                {{-- Tồn kho --}}
                                <td>
                                    <span class="{{ $row->so_luong_ton <= 0 ? 'text-danger' : ($row->so_luong_ton <= 3 ? 'text-warning' : 'text-muted') }} small">
                                        {{ $row->so_luong_ton }}
                                    </span>
                                </td>

                                {{-- Trạng thái --}}
                                <td>
                                    @if(!$row->trang_thai)
                                        <span class="badge bg-danger">Ngừng</span>
                                    @elseif($row->so_luong_ton <= 0)
                                        <span class="badge bg-secondary">Hết</span>
                                    @elseif($row->so_luong_ton <= 3)
                                        <span class="badge bg-warning text-dark">Sắp hết</span>
                                    @else
                                        <span class="badge bg-success">Còn hàng</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                        @endif
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="text-center text-muted py-5">
            <i class="fas fa-box-open fa-3x mb-3"></i>
            <p class="mb-0">Hiện chưa có sản phẩm nào.</p>
        </div>
        @endif
    </div>
    <div class="card-footer bg-white">
        <div class="d-flex justify-content-between align-items-center">
            <span class="text-muted">
                Hiển thị {{ $sanPhams->firstItem() ?? 0 }} - {{ $sanPhams->lastItem() ?? 0 }} trên {{ $sanPhams->total() }} sản phẩm
            </span>
            <nav>{{ $sanPhams->links('pagination::bootstrap-5') }}</nav>
        </div>
    </div>
</div>

<div class="offcanvas offcanvas-end" tabindex="-1" id="productDetailDrawer" style="width:680px;">
    <div class="offcanvas-header border-bottom">
        <h5 class="offcanvas-title fw-bold"><i class="fas fa-box-open me-2 text-primary"></i>Chi tiết sản phẩm</h5>
        <div class="d-flex gap-2">
            <a href="#" id="drawerEditBtn" class="btn btn-sm btn-primary"><i class="fas fa-edit me-1"></i>Sửa</a>
            <button type="button" class="btn btn-sm btn-danger" id="drawerDeleteBtn" onclick="confirmDeleteFromDrawer()"><i class="fas fa-trash me-1"></i>Xóa</button>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
        </div>
    </div>
    <div class="offcanvas-body p-0" id="drawerBody" style="overflow-y:auto;">
        <div class="d-flex justify-content-center align-items-center" style="min-height:300px;">
            <div class="text-center">
                <div class="spinner-border text-primary mb-3" role="status"></div>
                <p class="text-muted mb-0">Đang tải...</p>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="addProductModal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-fullheight">
        <div class="modal-content">
            <div class="modal-header" style="background:linear-gradient(135deg,#0d6efd 0%,#0b5ed7 100%);color:white;">
                <div>
                    <h5 class="modal-title fw-bold mb-0"><i class="fas fa-box-open me-2"></i>THÊM SẢN PHẨM</h5>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ url('admin/san-pham') }}" method="POST" enctype="multipart/form-data" id="productForm">
                @csrf
                <input type="file" name="hinh_anh" id="nativeHinhAnhInput" class="d-none" accept="image/*">
                <div class="modal-body p-0" id="addProductModalBody">
                    <div id="createProductApp" v-cloak></div>
                </div>
                <div class="modal-footer bg-light flex-column align-items-stretch">
                    <div id="formErrorBox" class="alert alert-danger py-2 px-3 mb-2 small d-none" role="alert" style="white-space:pre-line;"></div>
                    {{-- ============================================================
                    YÊU CẦU 2: CẢNH BÁO TRÙNG NHÓM THUỘC TÍNH (FRONTEND)
                    ============================================================ --}}
                    <div id="duplicateAttrGroupWarning" class="alert alert-danger py-2 px-3 mb-2 small d-none" role="alert" style="white-space:pre-line;">
                        <i class="fas fa-exclamation-circle me-1"></i>
                    </div>
                    {{-- ============================================================
                    YÊU CẦU 2: CẢNH BÁO TRÙNG LẶP BIẾN THỂ (FRONTEND)
                    ============================================================ --}}
                    <div id="duplicateVariantWarning" class="alert alert-warning py-2 px-3 mb-2 small d-none" role="alert" style="white-space:pre-line;">
                        <i class="fas fa-exclamation-triangle me-1"></i>
                    </div>
                    <div class="d-flex justify-content-between w-100 align-items-center">
                        <span class="text-muted small"><i class="fas fa-info-circle me-1"></i> Điền đầy đủ thông tin trước khi lưu</span>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Hủy bỏ</button>
                            <button type="button" class="btn btn-success px-4" id="btnLuuSanPham">
                                <span class="spinner-border spinner-border-sm me-1 d-none" id="btnLuuSpinner" role="status"></span>
                                <i class="fas fa-save me-1" id="btnLuuIcon"></i>Lưu sản phẩm
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
$unitsPayload = $donViMacDinhs->map(fn($u) => [
    'id'   => $u->id,
    'name' => $u->ten_hien_thi, // "Thùng 24"
    'qty'  => $u->so_luong_san_pham_trong_don_vi,
])->values()->all();
@endphp
<script>
    // Bridge data từ Blade sang Vue 3
    window.__CREATE_PRODUCT_DATA__ = {
        danhMucs: @json($danhMucs->map(fn($d) => ['id' => $d->id, 'ten' => $d->ten_danh_muc])),
        csrfToken: document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}',
        availableAttributes: @json($attrsPayload),
        availableUnits: @json($unitsPayload)
    };
</script>
<script src="{{ asset('js/admin/san-pham-create-vue.js') }}"></script>
<script src="{{ asset('js/admin/san-pham.js') }}?v={{ time() }}"></script>
@endsection

<!-- ===================== IMPORT MODAL ===================== -->
<div class="modal fade" id="importProductModal" tabindex="-1" aria-labelledby="importProductModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" style="background:linear-gradient(135deg,#198754 0%,#157347 100%);color:white;">
                <div>
                    <h5 class="modal-title fw-bold mb-0" id="importProductModalLabel">
                        <i class="fas fa-file-import me-2"></i>Nhập dữ liệu sản phẩm
                    </h5>
                    <small class="text-white-50">Từ file CSV (.csv)</small>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ url('admin/san-pham/import') }}" method="POST" enctype="multipart/form-data" id="importProductForm">
                @csrf
                <input type="hidden" name="_action" value="import">
                <div class="modal-body">
                    <div class="text-center mb-4">
                        <i class="fas fa-file-csv fa-4x text-success mb-3"></i>
                        <h5>Chọn file CSV để import</h5>
                        <p class="text-muted small mb-2">File phải có định dạng <strong>.csv</strong> (UTF-8).</p>
                        <a href="{{ url('admin/san-pham/export-template') }}" class="btn btn-outline-success btn-sm">
                            <i class="fas fa-download me-1"></i>Tải mẫu import
                        </a>
                    </div>

                    <div class="mb-3">
                        <label for="importFileInput" class="form-label fw-semibold">Chọn file CSV</label>
                        <input type="file" class="form-control" id="importFileInput" name="excel_file" accept=".csv,text/csv">
                        <div class="form-text">Dung lượng tối đa: 5MB</div>
                    </div>

                    <!-- Preview section -->
                    <div id="importPreviewSection" class="d-none">
                        <hr>
                        <h6 class="mb-2"><i class="fas fa-eye me-1"></i>Xem trước dữ liệu (5 dòng đầu tiên)</h6>
                        <div class="table-responsive border rounded">
                            <table class="table table-sm table-bordered table-hover mb-0" id="importPreviewTable">
                                <thead class="table-light"></thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Hủy bỏ</button>
                    <button type="submit" class="btn btn-success" id="btnImportSubmit">
                        <i class="fas fa-upload me-1"></i>Import sản phẩm
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@section('styles')
<link rel="stylesheet" href="{{ asset('css/admin/san-pham.css') }}">
<style>
    [v-cloak] { display: none !important; }
</style>
@endsection
