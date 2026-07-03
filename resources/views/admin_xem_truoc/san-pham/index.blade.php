@extends('admin_xem_truoc.layouts.admin')

@section('title', 'Quan ly San pham - SmartMart')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1">Quan ly San pham</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('admin/dashboard') }}">Admin</a></li>
                <li class="breadcrumb-item active">San pham</li>
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
            <i class="fas fa-plus me-2"></i>Them san pham
        </button>
        <button class="btn btn-outline-secondary" id="startQrScanBtn">
            <i class="fas fa-barcode me-2"></i>Quet ma vach
        </button>
        <a href="{{ url('admin/san-pham/trash') }}" class="btn btn-outline-danger">
            <i class="fas fa-trash me-2"></i>Thung rac
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
                        <input type="text" id="searchKeywordInput" class="form-control" name="keyword" value="{{ $keyword ?? '' }}" placeholder="Tim kiem san pham...">
                    </div>
                </div>
                <div class="col-md-3">
                    <select class="form-select" name="danh_muc">
                        <option value="">Tat ca danh muc</option>
                        @foreach($danhMucs as $danhMuc)
                            <option value="{{ $danhMuc->id }}" {{ (string)$danhMuc->id === (string)($danhMucId ?? '') ? 'selected' : '' }}>{{ $danhMuc->ten_danh_muc }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <select class="form-select" name="trang_thai">
                        <option value="">Tat ca trang thai</option>
                        <option value="1" {{ $trangThai === '1' || $trangThai === 1 ? 'selected' : '' }}>Dang ban</option>
                        <option value="0" {{ $trangThai === '0' || $trangThai === 0 ? 'selected' : '' }}>Ngung ban</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-outline-secondary w-100">
                        <i class="fas fa-filter me-2"></i>Loc
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
                <h5 class="modal-title">Quet ma vach</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="qrScanner" style="width:100%; min-height:400px;"></div>
                <div class="mt-3 text-center">
                    <button type="button" class="btn btn-secondary" id="stopQrScanBtn">Dung quet</button>
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
                    <label class="form-check-label text-muted" for="selectAllCheckbox">Chon tat ca</label>
                </div>
                <div id="bulkActionButtons" class="d-none">
                    <span class="text-muted me-2" id="selectedCount">0 da chon</span>
                    <button type="button" class="btn btn-sm btn-success" onclick="submitBulkAction('activate')"><i class="fas fa-check me-1"></i>Bat</button>
                    <button type="button" class="btn btn-sm btn-warning" onclick="submitBulkAction('deactivate')"><i class="fas fa-ban me-1"></i>Tat</button>
                    <button type="button" class="btn btn-sm btn-danger" onclick="submitBulkAction('delete')"><i class="fas fa-trash me-1"></i>Xoa</button>
                </div>
            </div>
            <div class="d-flex align-items-center gap-2">
                <span class="text-muted small">Tong: <strong id="totalProducts">{{ $variants->total() }}</strong> dong</span>
            </div>
        </div>

        @if($variants->count() > 0)
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th style="width:40px;"></th>
                        <th style="width:60px;">Anh</th>
                        <th>Ten san pham</th>
                        <th>Danh muc</th>
                        <th>Loai</th>
                        <th style="width:130px;">Gia ban</th>
                        <th style="width:80px;">Ton kho</th>
                        <th style="width:100px;">Trang thai</th>
                    </tr>
                </thead>
                <tbody id="productTableBody">
                    @foreach($flatItems as $item)
                        @php
                            $isUnit = ($item['type'] ?? '') === 'unit';
                            $trangThai = $item['trang_thai'] ?? true;
                            $tonKho = $item['so_luong_ton'] ?? 0;
                            $dinhMuc = $item['dinh_muc_toi_thieu'] ?? 0;
                        @endphp
                        <tr class="{{ $isUnit ? 'unit-row' : 'variant-row' }}"
                            style="cursor:pointer; {{ $isUnit ? 'background:#fafafa;' : '' }}"
                            data-id="{{ $item['id'] }}"
                            data-type="{{ $item['type'] ?? 'variant' }}"
                            data-product-id="{{ $item['product_id'] }}"
                            onclick="window.openProductDrawer && window.openProductDrawer('{{ $item['id'] }}')">
                            <td onclick="event.stopPropagation();">
                                <input type="checkbox" class="form-check-input product-checkbox" value="{{ $item['id'] }}">
                            </td>
                            <td>
                                @if(!empty($item['hinh_anh']))
                                    <img src="{{ asset($item['hinh_anh']) }}" alt="{{ $item['ten_san_pham'] }}" style="width:48px;height:48px;object-fit:cover;border-radius:6px;">
                                @else
                                    <div style="width:48px;height:48px;border-radius:6px;background:#f0f0f0;display:flex;align-items:center;justify-content:center;">
                                        <i class="fas fa-image text-muted"></i>
                                    </div>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    @if($isUnit)
                                        <i class="fas fa-arrow-turn-down-right text-muted" style="font-size:0.6rem;"></i>
                                    @endif
                                    <div>
                                        <span class="fw-semibold" style="font-size:0.88rem;">{{ $item['ten_san_pham'] }}</span>
                                        @if(!empty($item['ten_bien_the']))
                                            <div class="small text-muted">{{ $item['ten_bien_the'] }}</div>
                                        @endif
                                        @if(!empty($item['thuoc_tinh_labels']))
                                            <div class="d-flex gap-1 mt-1">
                                                @foreach($item['thuoc_tinh_labels'] as $label)
                                                    <span class="badge bg-light text-dark border" style="font-size:0.65rem;">{{ $label }}</span>
                                                @endforeach
                                            </div>
                                        @endif
                                        @if(!empty($item['ma_vach']))
                                            <div class="small text-muted">#{{ $item['ma_vach'] }}</div>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="text-muted small">{{ $item['danh_muc'] ?: '-' }}</span>
                            </td>
                            <td>
                                @if($isUnit)
                                    <span class="badge bg-info" style="font-size:0.7rem;">Don vi QD</span>
                                @else
                                    <span class="badge bg-secondary" style="font-size:0.7rem;">Bien the</span>
                                @endif
                            </td>
                            <td>
                                <span class="fw-bold text-primary" style="font-size:0.88rem;">
                                    {{ number_format((float)($item['gia_ban'] ?? 0), 0, ',', '.') }} d
                                </span>
                            </td>
                            <td>
                                <span class="{{ $tonKho <= $dinhMuc ? 'text-warning' : 'text-muted' }} small">{{ $tonKho }}</span>
                            </td>
                            <td>
                                @if(!$trangThai)
                                    <span class="badge bg-danger">Ngung ban</span>
                                @elseif($tonKho <= 0)
                                    <span class="badge bg-secondary">Het hang</span>
                                @elseif($tonKho <= $dinhMuc)
                                    <span class="badge bg-warning text-dark">Sap het</span>
                                @else
                                    <span class="badge bg-success">Con hang</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="text-center text-muted py-5">
            <i class="fas fa-box-open fa-3x mb-3"></i>
            <p class="mb-0">Hien chua co san pham nao.</p>
        </div>
        @endif
    </div>
    <div class="card-footer bg-white">
        <div class="d-flex justify-content-between align-items-center">
            <span class="text-muted">
                Hien thi {{ $variants->firstItem() ?? 0 }} - {{ $variants->lastItem() ?? 0 }} tren {{ $variants->total() }} dong
            </span>
            <nav>{{ $variants->links('pagination::bootstrap-5') }}</nav>
        </div>
    </div>
</div>

<div class="offcanvas offcanvas-end" tabindex="-1" id="productDetailDrawer" style="width:680px;">
    <div class="offcanvas-header border-bottom">
        <h5 class="offcanvas-title fw-bold"><i class="fas fa-box-open me-2 text-primary"></i>Chi tiet san pham</h5>
        <div class="d-flex gap-2">
            <a href="#" id="drawerEditBtn" class="btn btn-sm btn-primary"><i class="fas fa-edit me-1"></i>Sua</a>
            <button type="button" class="btn btn-sm btn-danger" id="drawerDeleteBtn" onclick="confirmDeleteFromDrawer()"><i class="fas fa-trash me-1"></i>Xoa</button>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
        </div>
    </div>
    <div class="offcanvas-body p-0" id="drawerBody" style="overflow-y:auto;">
        <div class="d-flex justify-content-center align-items-center" style="min-height:300px;">
            <div class="text-center">
                <div class="spinner-border text-primary mb-3" role="status"></div>
                <p class="text-muted mb-0">Dang tai...</p>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="addProductModal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-fullheight">
        <div class="modal-content">
            <div class="modal-header" style="background:linear-gradient(135deg,#0d6efd 0%,#0b5ed7 100%);color:white;">
                <div>
                    <h5 class="modal-title fw-bold mb-0"><i class="fas fa-box-open me-2"></i>THEM SAN PHAM</h5>
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
                    <div class="d-flex justify-content-between w-100 align-items-center">
                        <span class="text-muted small"><i class="fas fa-info-circle me-1"></i> Dien day du thong tin truoc khi luu</span>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Huy bo</button>
                            <button type="button" class="btn btn-success px-4" id="btnLuuSanPham">
                                <span class="spinner-border spinner-border-sm me-1 d-none" id="btnLuuSpinner" role="status"></span>
                                <i class="fas fa-save me-1" id="btnLuuIcon"></i>Luu san pham
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
<script>
    // Bridge data từ Blade sang Vue 3
    window.__CREATE_PRODUCT_DATA__ = {
        danhMucs: @json($danhMucs->map(fn($d) => ['id' => $d->id, 'ten' => $d->ten_danh_muc])),
        csrfToken: document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}'
    };
</script>
<script src="{{ asset('js/admin/san-pham-create-vue.js') }}"></script>
<script src="{{ asset('js/admin/san-pham.js') }}"></script>
@endsection

<!-- ===================== IMPORT MODAL ===================== -->
<div class="modal fade" id="importProductModal" tabindex="-1" aria-labelledby="importProductModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" style="background:linear-gradient(135deg,#198754 0%,#157347 100%);color:white;">
                <div>
                    <h5 class="modal-title fw-bold mb-0" id="importProductModalLabel">
                        <i class="fas fa-file-import me-2"></i>Nhap du lieu san pham
                    </h5>
                    <small class="text-white-50">Tu file CSV (.csv)</small>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ url('admin/san-pham/import') }}" method="POST" enctype="multipart/form-data" id="importProductForm">
                @csrf
                <input type="hidden" name="_action" value="import">
                <div class="modal-body">
                    <div class="text-center mb-4">
                        <i class="fas fa-file-csv fa-4x text-success mb-3"></i>
                        <h5>Chon file CSV de import</h5>
                        <p class="text-muted small mb-2">File phai co dinh dang <strong>.csv</strong> (UTF-8).</p>
                        <a href="{{ url('admin/san-pham/export-template') }}" class="btn btn-outline-success btn-sm">
                            <i class="fas fa-download me-1"></i>Tai mau import
                        </a>
                    </div>

                    <div class="mb-3">
                        <label for="importFileInput" class="form-label fw-semibold">Chon file CSV</label>
                        <input type="file" class="form-control" id="importFileInput" name="excel_file" accept=".csv,text/csv">
                        <div class="form-text">Dung luong toi da: 5MB</div>
                    </div>

                    <!-- Preview section -->
                    <div id="importPreviewSection" class="d-none">
                        <hr>
                        <h6 class="mb-2"><i class="fas fa-eye me-1"></i>Xem truoc du lieu (5 dong dau tien)</h6>
                        <div class="table-responsive border rounded">
                            <table class="table table-sm table-bordered table-hover mb-0" id="importPreviewTable">
                                <thead class="table-light"></thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Huy bo</button>
                    <button type="submit" class="btn btn-success" id="btnImportSubmit">
                        <i class="fas fa-upload me-1"></i>Import san pham
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
