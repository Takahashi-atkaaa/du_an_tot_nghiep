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
        <form id="searchProductForm" action="{{ url('admin/san-pham') }}" method="GET">
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

<form id="bulkActionForm" action="{{ url('admin/san-pham/bulk-action') }}" method="POST" style="display:none;">
    @csrf
    <input type="hidden" name="action" id="bulkActionInput">
    <div id="selectedIdsContainer"></div>
</form>

{{-- ============================================================
DROPDOWN ĐƠN VỊ QUY ĐỔI - 1 instance duy nhất, fixed position
Tuyệt đối KHÔNG nằm trong bảng để không phá vỡ layout
============================================================ --}}
<div id="unitConversionDropdown" class="unit-conversion-popover" style="display:none;">
    <div class="unit-popover-header">
        <i class="fas fa-balance-scale me-1"></i>
        <span>Đơn vị quy đổi</span>
    </div>
    <ul id="unitConversionList" class="unit-popover-list"></ul>
</div>

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
            <table class="table table-hover mb-0 align-middle w-100 product-list-table" style="table-layout: fixed;">
                <colgroup>
                    <col style="width:3%">
                    <col style="width:7%">
                    <col style="width:30%">
                    <col style="width:15%">
                    <col style="width:10%">
                    <col style="width:10%">
                    <col style="width:10%">
                    <col style="width:7%">
                    <col style="width:8%">
                </colgroup>
                <thead>
                    <tr>
                        <th class="text-center"></th>
                        <th class="text-center">Ảnh</th>
                        <th>Tên sản phẩm</th>
                        <th>Danh mục</th>
                        <th class="text-end">Giá vốn</th>
                        <th class="text-end">Giá bán</th>
                        <th class="text-center">Đã bán</th>
                        <th class="text-center">Tồn kho</th>
                        <th class="text-center">Trạng thái</th>
                    </tr>
                </thead>
                <tbody id="productTableBody">
                    @foreach($sanPhams as $sp)
                        @php
                            // Bỏ qua sản phẩm không có variant / đơn vị quy đổi nào
                            // (flatenedRows rỗng). Tránh truy cập $firstRow->variant khi null.
                            $rows = $sp->flattenedRows;
                        @endphp
                        @if($rows->isEmpty())
                            @continue
                        @endif
                        @php
                            $hasMoreThanOneRow = $rows->count() > 1;
                            $firstRow = $rows->first();
                        @endphp

                        @continue(!$rows->isNotEmpty())

                        @php
                            // ============================================================
                            // FIX: Chọn variant đại diện cho đơn vị cơ bản (dòng chính).
                            // Trước đây: $firstRow = $rows->first() → có thể là variant
                            // không phải đơn vị cơ bản (variant có ten_don_vi trùng với
                            // đơn vị quy đổi, do dữ liệu cũ bị ghi đè nhầm).
                            // Sau khi lưu, variant mới "Cái" có ID lớn hơn được sort
                            // xuống dưới, khiến $firstRow luôn lấy variant cũ "Lon"
                            // nhưng variant cũ có ten_don_vi trùng quy đổi → dropdown trống.
                            // ============================================================
                            $masterVariant = $sp->firstMasterVariant;
                            $firstVariant = $masterVariant ?? $firstRow?->variant ?? null;
                            $firstVariantId = $firstVariant?->id;

                            // Phân loại cho UI mới:
                            //   - $variantRows (loai_dong='goc' AND có thuộc tính) -> hiển thị thành <tr> riêng
                            //   - $conversionRows (loai_dong='quy_doi') -> gộp vào Dropdown tại dòng chính
                            $variantAttrRows = $rows->where('loai_dong', 'goc')
                                ->filter(fn($r) => !empty($r->ten_bien_the_display));
                            // Lọc conversion rows theo variant CHA (chỉ conversion thuộc về dòng chính)
                            $conversionRows = $rows->where('loai_dong', 'quy_doi')
                                ->filter(fn($r) => $r->variant?->id === $firstVariantId);
                            $countQuyDoi = $conversionRows->count();
                            $isDonViOnly = $variantAttrRows->count() === 0 && $countQuyDoi > 0;

                            // Trạng thái tổng hợp cho sản phẩm
                            $trangThaiSP = $sp->trang_thai;
                            $tongTonKho = $sp->variants->sum('so_luong_ton');
                            $hasManyVariants = $variantAttrRows->count() > 0 || $countQuyDoi > 0;

                            // Đơn vị cơ bản (CHA): lấy từ master variant
                            $baseUnitName = $masterVariant?->ten_don_vi ?: '—';
                            // Biến thể CHA + data gốc (để JS tính toán khi chọn đơn vị)
                            $baseVariant   = $masterVariant ?? $firstRow?->variant ?? null;
                            $baseUnitId    = $baseVariant?->id ?? '';
                            $baseGiaBanGoc = $baseVariant?->gia_ban ?? 0;
                            $baseTonKhoGoc = $baseVariant?->so_luong_ton ?? 0;
                            $baseMaHang    = $baseVariant?->ma_hang ?? '';
                            $baseMaVach    = $baseVariant?->ma_vach ?? '';

                            // ============================================================
                            // GIÁ VỐN (khoảng giá nếu nhiều biến thể/đơn vị quy đổi)
                            // Lấy từ flattenedRows để bao gồm cả:
                            //   - variant.gia_von  (dòng goc)
                            //   - unit.gia_von_quy_doi (dòng quy_doi)
                            // Bỏ qua giá trị <= 0 (chưa set) để tránh range bị lệch về 0
                            // ============================================================
                            $giaVonList = $rows->pluck('gia_von')
                                ->map(fn($v) => (float) $v)
                                ->filter(fn($v) => $v > 0);
                            $giaVonMin = (float) ($giaVonList->min() ?? 0);
                            $giaVonMax = (float) ($giaVonList->max() ?? 0);
                            $hasGiaVon = $giaVonList->isNotEmpty();
                        @endphp

                        {{-- DÒNG CHÍNH (dòng đầu tiên) --}}
                        <tr class="product-parent-row {{ !$firstRow->trang_thai ? 'table-secondary opacity-50' : '' }}"
                            style="cursor:pointer;"
                            data-id="{{ $sp->id }}"
                            data-variant-id="{{ $firstVariant?->id ?? '' }}"
                            data-unit-id=""
                            data-product-id="{{ $sp->id }}"
                            data-target-id="{{ $sp->id }}"
                            data-row-type="goc"
                            data-is-master="{{ $variantAttrRows->count() > 0 ? '1' : '0' }}"
                            data-gia-ban-goc="{{ $baseGiaBanGoc }}"
                            data-ton-kho-goc="{{ $baseTonKhoGoc }}"
                            onclick="window.toggleVariants && window.toggleVariants({{ $sp->id }})">

                            {{-- Toggle expand --}}
                            <td onclick="event.stopPropagation();">
                                <div class="d-flex align-items-center gap-1">
                                    @if($variantAttrRows->count() > 0)
                                        <button class="btn btn-sm btn-light p-0 border-0 expand-btn"
                                                id="expandBtn{{ $sp->id }}"
                                                onclick="event.stopPropagation(); window.toggleVariants && window.toggleVariants({{ $sp->id }})"
                                                title="Mở rộng">
                                            <i class="fas fa-chevron-down" style="font-size:0.7rem; transition:transform 0.2s;"></i>
                                        </button>
                                    @endif
                                    <input type="checkbox" class="form-check-input product-checkbox"
                                           value="{{ $sp->id }}"
                                           data-product-id="{{ $sp->id }}"
                                           data-type="goc"
                                           onclick="event.stopPropagation();">
                                </div>
                            </td>

                            {{-- Hình ảnh (lấy ảnh variant đầu) --}}
                            <td class="text-center">
                                @php $firstImg = $firstVariant?->hinh_anh ?? $sp->hinh_anh; @endphp
                                @if(!empty($firstImg))
                                    @php
                                        $firstImgSrc = \App\Models\BienTheSanPham::resolveImageUrl($firstImg);
                                    @endphp
                                    <img src="{{ $firstImgSrc }}" alt="{{ $sp->ten_san_pham }}"
                                         style="width:48px;height:48px;object-fit:cover;border-radius:6px;">
                                @else
                                    <div style="width:48px;height:48px;border-radius:6px;background:#f0f0f0;display:flex;align-items:center;justify-content:center;">
                                        <i class="fas fa-image text-muted"></i>
                                    </div>
                                @endif
                            </td>

                            {{-- Tên sản phẩm + Mã hàng (SKU) + Thương hiệu --}}
                            <td class="align-middle">
                                <div>
                                    <span class="fw-semibold js-tensp" style="font-size:0.9rem; line-height:1.2;">{{ $sp->ten_san_pham }}</span>

                                    {{-- YÊU CẦU 1: Thêm Mã hàng (SKU) màu xám, size nhỏ ngay dưới tên sản phẩm --}}
                                    @if(!empty($baseMaHang) || !empty($baseMaVach))
                                        <div class="text-sm text-gray-500 mt-1 js-mahang"
                                             style="font-size:0.72rem; color:#6b7280; line-height:1.1;">
                                            <i class="fas fa-barcode me-1" style="font-size:0.65rem;"></i>MH: <span class="js-mahang-val">{{ $baseMaHang ?: '—' }}</span>
                                        </div>
                                    @endif

                                    @if(!empty($sp->thuong_hieu))
                                        <div class="small text-muted" style="font-size:0.7rem;">{{ $sp->thuong_hieu }}</div>
                                    @endif
                                </div>
                            </td>

                            {{-- Danh mục --}}
                            <td class="align-middle">
                                <span class="text-muted small">{{ $sp->danhMuc?->ten_danh_muc ?? '-' }}</span>
                            </td>

                            {{-- Giá vốn (khoảng giá nếu có nhiều biến thể/đơn vị quy đổi) --}}
                            <td class="text-end align-middle">
                                @if(!$hasGiaVon)
                                    <span class="text-gray-400">—</span>
                                @elseif($giaVonMin == $giaVonMax)
                                    <span class="text-gray-500 js-giavon" style="font-size:0.85rem;"
                                          data-gia-von="{{ $giaVonMin }}">
                                        {{ number_format($giaVonMin, 0, ',', '.') }} đ
                                    </span>
                                @else
                                    <span class="text-gray-500 js-giavon" style="font-size:0.85rem;"
                                          data-gia-von-min="{{ $giaVonMin }}"
                                          data-gia-von-max="{{ $giaVonMax }}">
                                        {{ number_format($giaVonMin, 0, ',', '.') }} đ - {{ number_format($giaVonMax, 0, ',', '.') }} đ
                                    </span>
                                @endif
                            </td>

                            {{-- Giá bán --}}
                            <td class="text-end align-middle">
                                <span class="js-giaban fw-bold text-primary" style="font-size:0.88rem;"
                                      data-gia-ban="{{ (float)$baseGiaBanGoc }}">
                                    {{ number_format((float)$baseGiaBanGoc, 0, ',', '.') }} d
                                </span>
                            </td>

                            {{-- Đã bán (tổng số lượng từ chi_tiet_hoa_don của hóa đơn Hoàn thành) --}}
                            <td class="text-center align-middle">
                                <span class="js-daban text-muted small fw-medium" data-daban="{{ (int)($sp->tong_da_ban ?? 0) }}">{{ (int)($sp->tong_da_ban ?? 0) }}</span>
                            </td>

                            {{-- Tồn kho --}}
                            <td class="text-center align-middle">
                                <span class="js-tonkho {{ $baseTonKhoGoc <= 0 ? 'text-danger' : ($baseTonKhoGoc <= 10 ? 'text-warning' : 'text-muted') }} small fw-medium"
                                      data-base-tonkho="{{ (int)$baseTonKhoGoc }}">
                                    {{ (int)$baseTonKhoGoc }}
                                </span>
                            </td>

                            {{-- Trạng thái --}}
                            <td class="text-center align-middle">
                                @if(!$firstRow->trang_thai)
                                    <span class="badge bg-danger">Ngừng bán</span>
                                @elseif($hasManyVariants)
                                    @if($tongTonKho <= 0)
                                        <span class="badge bg-secondary">Hết hàng</span>
                                    @elseif($tongTonKho <= 10)
                                        <span class="badge bg-warning text-dark">Sắp hết</span>
                                    @else
                                        <span class="badge bg-success">Còn hàng</span>
                                    @endif
                                @else
                                    @if($baseTonKhoGoc <= 0)
                                        <span class="badge bg-secondary">Hết hàng</span>
                                    @elseif($baseTonKhoGoc <= 10)
                                        <span class="badge bg-warning text-dark">Sắp hết</span>
                                    @else
                                        <span class="badge bg-success">Còn hàng</span>
                                    @endif
                                @endif
                            </td>
                        </tr>
                        <tr id="productDetailRow{{ $sp->id }}" class="product-detail-row" style="display:none; background:#f8f9fa;">
                            <td colspan="9" class="p-0">
                                <div id="productDetailPanel{{ $sp->id }}" class="product-detail-panel p-3">
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <div class="flex-grow-1">
                                            <h6 class="mb-1 fw-semibold" style="font-size:0.95rem;">Chi tiết sản phẩm</h6>
                                            <div class="text-muted small">{{ $sp->ten_san_pham }} · {{ $sp->danhMuc?->ten_danh_muc ?? 'Không xác định' }}</div>
                                        </div>
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="badge bg-secondary me-2">{{ $sp->variants->count() }} biến thể</span>
                                            <a href="{{ route('san-pham.edit', $sp->id) }}"
                                               class="btn btn-sm btn-outline-primary d-flex align-items-center gap-1"
                                               title="Sửa sản phẩm"
                                               onclick="event.stopPropagation();">
                                                <i class="fas fa-edit"></i>
                                                <span>Sửa</span>
                                            </a>
                                            <button type="button"
                                                    class="btn btn-sm btn-outline-danger d-flex align-items-center gap-1"
                                                    title="Xóa sản phẩm"
                                                    onclick="event.stopPropagation(); window.deleteProductByUrl('{{ route('san-pham.destroy', $sp->id) }}', {{ $sp->id }}, '{{ addslashes($sp->ten_san_pham) }}');">
                                                <i class="fas fa-trash"></i>
                                                <span>Xóa</span>
                                            </button>
                                        </div>
                                    </div>

                                    <ul class="nav nav-tabs nav-tabs-sm mb-3" role="tablist">
                                        <li class="nav-item">
                                            <button type="button" class="nav-link active" data-tab-key="summary" onclick="event.stopPropagation(); window.switchProductTab({{ $sp->id }}, 'summary')">Tổng quan</button>
                                        </li>
                                        <li class="nav-item">
                                            <button type="button" class="nav-link" data-tab-key="variants" onclick="event.stopPropagation(); window.switchProductTab({{ $sp->id }}, 'variants')">Biến thể</button>
                                        </li>
                                        <li class="nav-item">
                                            <button type="button" class="nav-link" data-tab-key="stock" onclick="event.stopPropagation(); window.switchProductTab({{ $sp->id }}, 'stock')">Kho</button>
                                        </li>
                                    </ul>

                                    <div class="product-detail-tabs">
                                        <div id="productSummaryTab{{ $sp->id }}" class="product-tab-content product-summary-tab text-center py-4 text-muted">
                                            <div class="spinner-border spinner-border-sm me-2" role="status"></div>
                                            Đang tải thống kê...
                                        </div>
                                        <div id="productVariantsTab{{ $sp->id }}" class="product-tab-content product-variants-tab d-none"></div>
                                        <div id="productStockTab{{ $sp->id }}" class="product-tab-content product-stock-tab d-none"></div>
                                    </div>
                                </div>
                            </td>
                        </tr>

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

{{-- ===================== INLINE JS: DROPDOWN ĐƠN VỊ TÍNH ===================== --}}
<script>
(function () {
    // Format tiền VNĐ: 336000 -> "336.000"
    function formatMoneyVND(amount) {
        var n = parseFloat(amount) || 0;
        return n.toLocaleString('vi-VN') + ' đ';
    }

    // Toggle ẩn/hiện popover các đơn vị quy đổi
    window.toggleUnitDropdown = function (triggerEl) {
        if (!triggerEl) return;
        var container = triggerEl.closest('.unit-dropdown-container');
        if (!container) return;
        var popover = container.querySelector('.unit-popover-list');
        if (!popover) return;

        // Đóng tất cả các popover khác trước
        document.querySelectorAll('.unit-popover-list').forEach(function (p) {
            if (p !== popover) p.classList.add('hidden');
        });

        popover.classList.toggle('hidden');
    };

    // Click bên ngoài sẽ đóng popover
    document.addEventListener('click', function (e) {
        if (!e.target.closest('.unit-dropdown-container')) {
            document.querySelectorAll('.unit-popover-list').forEach(function (p) {
                p.classList.add('hidden');
            });
        }
    });

    // ------------------------------------------------------------
    // Hàm recalcRow: cập nhật DOM (Tên ĐVT, Giá bán, Tồn kho)
    // ------------------------------------------------------------
    function recalcRow(row, unitObj) {
        if (!row || !unitObj) return;

        // 1. Cập nhật Tên Đơn vị (giữ lại dấu ▾ nếu có)
        var elDonVi = row.querySelector('.js-donvi');
        if (elDonVi && unitObj.ten_don_vi) {
            var hasArrow = elDonVi.textContent.indexOf('▾') >= 0;
            elDonVi.textContent = unitObj.ten_don_vi + (hasArrow ? ' ▾' : '');
        }

        // 2. Cập nhật Giá bán (Format tiền VNĐ)
        var elGiaBan = row.querySelector('.js-giaban');
        if (elGiaBan) {
            var gia = parseFloat(unitObj.gia_ban) || 0;
            elGiaBan.textContent = formatMoneyVND(gia);
            elGiaBan.setAttribute('data-gia-ban', gia);
        }

        // 3. Cập nhật Tồn kho (Chia theo tỷ lệ so với tồn kho gốc)
        var elTonKho = row.querySelector('.js-tonkho');
        if (elTonKho) {
            var baseTonKho = parseFloat(elTonKho.getAttribute('data-base-tonkho') || 0);
            var tyLe = parseFloat(unitObj.ty_le) || 1;
            var tonKhoMoi = tyLe > 0 ? Math.floor(baseTonKho / tyLe) : 0;
            elTonKho.textContent = tonKhoMoi;

            // Cập nhật class màu theo ngưỡng
            elTonKho.classList.remove('text-danger', 'text-warning', 'text-muted');
            if (tonKhoMoi <= 0) {
                elTonKho.classList.add('text-danger');
            } else if (tonKhoMoi <= 3) {
                elTonKho.classList.add('text-warning');
            } else {
                elTonKho.classList.add('text-muted');
            }
        }

        // 4. Highlight dòng đang xem đơn vị quy đổi
        if (unitObj.type !== 'base') {
            row.classList.add('table-warning');
        } else {
            row.classList.remove('table-warning');
        }
    }
    // Expose để file JS khác (nếu cần) cũng có thể gọi
    window.recalcRow = recalcRow;

    // ------------------------------------------------------------
    // Chọn 1 đơn vị (base hoặc quy đổi):
    //   - Cập nhật data-* cho row để mở drawer đúng biến thể
    //   - Gọi recalcRow để cập nhật DOM ngay trên lưới
    // ------------------------------------------------------------
    window.selectUnitView = function (rowEl, unitData) {
        if (!rowEl || !unitData) return;

        // Chuẩn hóa: chấp nhận cả key cũ (name/qty/isBase) và key mới (ten_don_vi/ty_le/type)
        var normalized = {
            id:         unitData.id || '',
            type:       unitData.type || (unitData.isBase ? 'base' : 'unit'),
            ten_don_vi: unitData.ten_don_vi || unitData.name || '',
            ty_le:      unitData.ty_le || unitData.qty || 1,
            gia_ban:    unitData.gia_ban || 0,
            ma_hang:    unitData.ma_hang || '',
            ma_vach:    unitData.ma_vach || ''
        };

        // 1. Cập nhật data-* cho row để drawer mở đúng biến thể
        if (normalized.id) {
            rowEl.setAttribute('data-unit-id', normalized.id);
        }
        rowEl.setAttribute('data-row-type', normalized.type === 'base' ? 'goc' : 'quy_doi');
        rowEl.setAttribute('data-target-id', rowEl.getAttribute('data-variant-id') || rowEl.getAttribute('data-id'));

        // 2. Cập nhật DOM trên lưới (Tên ĐVT + Giá bán + Tồn kho)
        recalcRow(rowEl, normalized);
    };
})();
</script>

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

    /* =========================================================
       BẢNG SẢN PHẨM - LAYOUT FIXED ĐỂ CỘT KHÔNG BỊ DỒN VỀ TRÁI
       ========================================================= */
    .table-responsive {
        overflow-x: auto;
    }
    .product-list-table {
        width: 100% !important;
        table-layout: fixed !important;
    }
    .product-list-table th,
    .product-list-table td {
        overflow: hidden;
        text-overflow: ellipsis;
        vertical-align: middle;
    }
    /* Cột Tên sản phẩm: cho phép xuống dòng nhưng giấu tràn */
    .product-list-table td:nth-child(3) {
        white-space: normal;
        word-break: break-word;
    }
    /* Cột Giá vốn, Giá bán: căn lề phải theo header */
    .product-list-table td.text-end {
        text-align: right !important;
    }
    .product-list-table td.text-center {
        text-align: center !important;
    }
    /* Bo góc + làm đẹp header */
    .product-list-table thead th {
        background: #f8f9fa;
        border-bottom: 2px solid #e9ecef;
        font-weight: 600;
        font-size: 0.82rem;
        color: #495057;
    }
    /* Tăng chiều cao hàng cho dễ đọc */
    .product-list-table tbody tr {
        height: auto;
    }
    .product-list-table tbody td {
        padding: 0.55rem 0.6rem;
        font-size: 0.85rem;
    }

    /* =========================================================
       YÊU CẦU 1: STYLE MÃ HÀNG (SKU) DƯỚI TÊN SẢN PHẨM
       ========================================================= */
    .product-list-table .js-mahang {
        font-size: 0.72rem !important;
        color: #6b7280 !important;
        letter-spacing: 0.01em;
        line-height: 1.15;
    }
    .product-list-table .js-mahang i {
        color: #9ca3af;
    }

    /* =========================================================
       YÊU CẦU 2: 4 THẺ THỐNG KÊ (STAT CARDS) - TEXT-LEFT
       ========================================================= */
    .stats-cards-row { margin-top: 0.25rem; }

    .stat-card {
        position: relative;
        background: #ffffff;
        border: 1px solid #f1f3f5;
        border-radius: 12px;
        padding: 16px 18px;
        text-align: left;
        transition: all 0.18s ease;
        overflow: hidden;
        box-shadow: 0 1px 2px rgba(15,23,42,0.04);
    }
    .stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 18px rgba(15,23,42,0.08);
        border-color: #e9ecef;
    }

    /* Icon SVG ở góc trên bên phải - màu nhạt */
    .stat-card-icon {
        position: absolute;
        top: 14px;
        right: 14px;
        width: 38px;
        height: 38px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0.85;
    }
    .stat-card-icon svg { display: block; }

    .stat-icon-blue   { background: rgba(59,130,246,0.10); color: #3b82f6; }
    .stat-icon-orange { background: rgba(245,158,11,0.10); color: #f59e0b; }
    .stat-icon-green  { background: rgba(16,185,129,0.10); color: #10b981; }
    .stat-icon-purple { background: rgba(139,92,246,0.10); color: #8b5cf6; }

    /* Style con số chính: text-2xl font-bold text-gray-800 */
    .stat-card-value {
        font-size: 1.55rem;
        line-height: 1.2;
        font-weight: 700;
        color: #1f2937;
        margin-top: 18px;
        margin-bottom: 4px;
        letter-spacing: -0.01em;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .stat-card-label {
        font-size: 0.78rem;
        color: #6b7280;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.03em;
    }

    /* Sub-text giả lập tỷ lệ tăng trưởng */
    .stat-card-growth {
        font-size: 0.72rem;
        line-height: 1.3;
        margin-top: 2px;
    }
    .stat-card-growth .text-green-500 { color: #10b981 !important; font-weight: 600; }
    .stat-card-growth .text-gray-400  { color: #9ca3af !important; }
    .stat-card-growth .text-gray-500  { color: #6b7280 !important; font-weight: 500; }

    /* =========================================================
       YÊU CẦU 4: PANEL BLOCK (Top biến thể + Đơn hàng gần nhất)
       ========================================================= */
    .panel-block {
        border: 1px solid #f1f3f5;
        border-radius: 12px;
        box-shadow: 0 1px 2px rgba(15,23,42,0.04);
    }
    .panel-title {
        font-size: 0.92rem;
        letter-spacing: -0.01em;
    }

    /* ---- Top biến thể: list + progress bar ---- */
    .top-variants-list {
        margin: 0;
        padding: 0;
    }
    .top-variant-item {
        padding: 8px 0;
        border-bottom: 1px dashed #f1f3f5;
    }
    .top-variant-item:last-child { border-bottom: none; padding-bottom: 0; }
    .top-variant-item:first-child { padding-top: 0; }

    .variant-name {
        font-size: 0.82rem;
        color: #1f2937;
        line-height: 1.25;
        display: flex;
        align-items: center;
        gap: 6px;
    }
    .rank-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 18px;
        height: 18px;
        padding: 0 5px;
        font-size: 0.65rem;
        font-weight: 700;
        color: #6b7280;
        background: #f3f4f6;
        border-radius: 9px;
        flex-shrink: 0;
    }
    .rank-badge.rank-1 {
        color: #fff;
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        box-shadow: 0 1px 3px rgba(217,119,6,0.3);
    }

    .variant-progress {
        background-color: #f3f4f6 !important;
        border-radius: 999px;
        overflow: hidden;
    }
    .variant-progress .progress-bar {
        border-radius: 999px;
        transition: width 0.6s ease;
    }

    /* ---- Đơn hàng gần nhất: mini table ---- */
    .recent-orders-table {
        margin: 0 -3px;
    }
    .recent-orders-table table {
        font-size: 0.82rem;
    }
    .recent-orders-table thead th {
        background: transparent;
        border-bottom: 1px solid #f1f3f5;
        padding: 6px 8px;
        text-transform: uppercase;
        letter-spacing: 0.03em;
    }
    .recent-orders-table tbody td {
        border: none;
        border-bottom: 1px dashed #f1f3f5;
        padding: 10px 8px;
        vertical-align: middle;
    }
    .recent-order-row {
        transition: background-color 0.15s ease, transform 0.15s ease;
    }
    .recent-order-row:hover td {
        background-color: #eff6ff;
    }
    .recent-order-row:hover td:first-child {
        box-shadow: inset 3px 0 0 #3b82f6;
    }
    .recent-order-row:hover {
        transform: translateX(2px);
    }
    .recent-order-row:focus {
        outline: none;
        background-color: #eff6ff;
    }
    .recent-order-row:focus td:first-child {
        box-shadow: inset 3px 0 0 #3b82f6;
    }
    .recent-order-row:last-child td {
        border-bottom: none;
    }

    .qty-badge {
        display: inline-block;
        min-width: 36px;
        padding: 2px 8px;
        font-size: 0.72rem;
        font-weight: 600;
        color: #475569;
        background: #f1f5f9;
        border-radius: 10px;
    }

    .badge-status {
        display: inline-block;
        padding: 3px 10px;
        font-size: 0.7rem;
        font-weight: 600;
        border-radius: 999px;
        line-height: 1.2;
    }
    .badge-status.badge-success {
        color: #047857;
        background: #d1fae5;
    }
    .badge-status.badge-warning {
        color: #92400e;
        background: #fef3c7;
    }
    .badge-status.badge-danger {
        color: #b91c1c;
        background: #fee2e2;
    }
    .badge-status.badge-secondary {
        color: #475569;
        background: #f1f5f9;
    }

    /* Responsive: stack panels trên mobile */
    @media (max-width: 767.98px) {
        .stats-cards-row > [class*="col-"] { margin-bottom: 0.75rem; }
        .stats-bottom-row > [class*="col-"] { margin-bottom: 0.75rem; }
    }
</style>
@endsection
