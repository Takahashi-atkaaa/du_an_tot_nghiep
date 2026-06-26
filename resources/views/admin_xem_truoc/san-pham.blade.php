@extends('admin_xem_truoc.layouts.admin')

@section('title', 'Quản lý Sản phẩm - SmartMart')

@section('content')
<!-- Page Header -->
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
    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    <!-- Filter & Search -->
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
                            <option value="{{ $danhMuc->id }}" {{ (string) $danhMuc->id === (string) ($danhMucId ?? '') ? 'selected' : '' }}>{{ $danhMuc->ten_danh_muc }}</option>
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

<!-- QR Scanner Modal -->
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

<!-- Bulk Action Bar -->
<form id="bulkActionForm" action="{{ url('admin/san-pham/bulk-action') }}" method="POST" style="display:none;">
    @csrf
    <input type="hidden" name="action" id="bulkActionInput">
    <div id="selectedIdsContainer"></div>
</form>

<!-- Products Table -->
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
                        <th style="width: 40px;"></th>
                        <th style="width: 60px;">Ảnh</th>
                        <th>Mã SP</th>
                        <th style="width:130px;">Mã vạch</th>
                        <th>Tên sản phẩm</th>
                        <th>Danh mục</th>
                        <th>Đơn vị</th>
                        <th style="width: 80px;">Định mức</th>
                        <th style="width: 130px;">Giá bán</th>
                        <th style="width: 80px;">Tồn kho</th>
                        <th style="width: 100px;">Trạng thái</th>
                    </tr>
                </thead>
                <tbody id="productTableBody">
                    @foreach($sanPhams as $sanPham)
                    <?php
                        $soBienThe = $sanPham->bienThe->count();
                        $bienTheThuocTinh = $sanPham->bienThe->filter(fn($b) => $b->loai_bien_the === 'thuoc_tinh');
                        $bienTheDonVi = $sanPham->bienThe->filter(fn($b) => $b->loai_bien_the === 'don_vi');
                        $soBienThe = $bienTheThuocTinh->count();
                        $hasVariants = $soBienThe > 0;
                        $tongTonKho = $sanPham->so_luong_ton_kho + $bienTheThuocTinh->sum('so_luong_ton_kho');
                        $giaMin = $bienTheThuocTinh->min('gia_ban');
                        $giaMax = $bienTheThuocTinh->max('gia_ban');
                        if ($bienTheThuocTinh->isEmpty()) {
                            $giaHienThi = $sanPham->gia_ban;
                        } elseif ($giaMin == $giaMax) {
                            $giaHienThi = $giaMin;
                        } else {
                            $giaHienThi = "{$giaMin} - {$giaMax}";
                        }
                    ?>
                    <tr class="product-row" style="cursor:pointer;" data-product-id="{{ $sanPham->id }}" data-has-variants="{{ $hasVariants ? '1' : '0' }}">
                        <td onclick="event.stopPropagation();">
                            <input type="checkbox" class="form-check-input product-checkbox" value="{{ $sanPham->id }}">
                        </td>
                        <td onclick="event.stopPropagation(); openProductDrawer({{ $sanPham->id }});">
                            @if($sanPham->hinh_anh)
                                <img src="{{ asset($sanPham->hinh_anh) }}"
                                     alt="{{ $sanPham->ten_san_pham }}"
                                     style="width:48px; height:48px; object-fit:cover; border-radius:6px;">
                            @else
                                <div style="width:48px; height:48px; border-radius:6px; background:#f0f0f0; display:flex; align-items:center; justify-content:center;">
                                    <i class="fas fa-image text-muted"></i>
                                </div>
                            @endif
                        </td> 
                        <td onclick="event.stopPropagation(); openProductDrawer({{ $sanPham->id }});">
                            <span class="text-muted small">#{{ $sanPham->ma_hang }}</span>
                        </td>
                        <td onclick="event.stopPropagation(); openProductDrawer({{ $sanPham->id }});">
                            <span class="text-muted small">{{ $sanPham->ma_vach ?? '-' }}</span>
                        </td>
                        <td onclick="event.stopPropagation(); openProductDrawer({{ $sanPham->id }});">
                            <div class="d-flex align-items-center gap-2">
                                @if($hasVariants)
                                    <a href="javascript:void(0)" onclick="event.stopPropagation(); toggleVariants({{ $sanPham->id }})" class="text-decoration-none expand-btn" id="expandBtn{{ $sanPham->id }}" title="Xem biến thể">
                                        <i class="fas fa-chevron-right text-muted" style="transition:transform 0.2s;"></i>
                                    </a>
                                @else
                                    <span style="width:16px;display:inline-block;"></span>
                                @endif
                                <span class="fw-semibold" style="font-size:0.88rem;">{{ $sanPham->ten_san_pham }}</span>
                                @if($hasVariants)
                                    <span class="badge bg-secondary" style="font-size:0.7rem;">{{ $soBienThe }} biến thể</span>
                                @endif
                            </div>
                        </td>
                        <td onclick="event.stopPropagation(); openProductDrawer({{ $sanPham->id }});">
                            <span class="text-muted small">{{ $sanPham->danhMuc->ten_danh_muc ?? '-' }}</span>
                        </td>
                        <td onclick="event.stopPropagation(); openProductDrawer({{ $sanPham->id }});">
                            <span class="text-muted small">{{ $sanPham->donVi->ten_don_vi ?? '-' }}</span>
                        </td>
                        <td onclick="event.stopPropagation(); openProductDrawer({{ $sanPham->id }});">
                            <span class="text-muted small text-center d-block">{{ $sanPham->dinh_muc_toi_thieu ?? 0 }}</span>
                        </td>
                        <td onclick="event.stopPropagation(); openProductDrawer({{ $sanPham->id }});">
                            @if(is_numeric($giaHienThi))
                                <span class="fw-bold text-primary" style="font-size:0.88rem;">{{ number_format($giaHienThi, 0, ',', '.') }} đ</span>
                            @else
                                <span class="fw-bold text-primary" style="font-size:0.8rem;">{{ $giaHienThi }} đ</span>
                            @endif
                        </td>
                        <td onclick="event.stopPropagation(); openProductDrawer({{ $sanPham->id }});">
                            <span class="{{ $tongTonKho <= ($sanPham->dinh_muc_toi_thieu ?? 0) ? 'text-warning' : 'text-muted' }} small">
                                {{ $tongTonKho }}
                            </span>
                        </td>
                        <td onclick="event.stopPropagation(); openProductDrawer({{ $sanPham->id }});">
                            @php
                                $displayStock = $tongTonKho;
                                $minStock = $sanPham->dinh_muc_toi_thieu ?? 0;
                                $trangThaiSanPham = $sanPham->trang_thai;
                            @endphp
                            @if(!$trangThaiSanPham)
                                <span class="badge bg-danger">Ngừng bán</span>
                            @elseif($displayStock <= 0)
                                <span class="badge bg-secondary">Hết hàng</span>
                            @elseif($displayStock <= $minStock)
                                <span class="badge bg-warning text-dark">Sắp hết</span>
                            @else
                                <span class="badge bg-success">Còn hàng</span>
                            @endif
                        </td>
                    </tr>
                    {{-- Biến thể thuộc tính rows (hidden by default) --}}
                    @foreach($bienTheThuocTinh as $bienThe)
                    <tr class="variant-row" id="variantRow{{ $sanPham->id }}_{{ $bienThe->id }}" style="display:none; background:#fafafa;">
                        <td></td>
                        <td onclick="event.stopPropagation();">
                            @if($bienThe->hinh_anh)
                                <img src="{{ asset($bienThe->hinh_anh) }}" alt="" style="width:36px; height:36px; object-fit:cover; border-radius:4px; border:1px solid #eee;">
                            @else
                                <div style="width:36px; height:36px; border-radius:4px; background:#eee; display:flex; align-items:center; justify-content:center;">
                                    <i class="fas fa-image text-muted" style="font-size:0.7rem;"></i>
                                </div>
                            @endif
                        </td>
                        <td onclick="event.stopPropagation(); openProductDrawer({{ $bienThe->id }});">
                            <span class="text-muted small">#{{ $bienThe->ma_hang }}</span>
                        </td>
                        <td onclick="event.stopPropagation(); openProductDrawer({{ $bienThe->id }});">
                            <span class="text-muted small">{{ $bienThe->ma_vach ?? '-' }}</span>
                        </td>
                        <td onclick="event.stopPropagation(); openProductDrawer({{ $bienThe->id }});">
                            <div class="d-flex align-items-center gap-2" style="padding-left:20px;">
                                <i class="fas fa-arrow-turn-down-right text-muted" style="font-size:0.6rem;"></i>
                                <span class="text-muted" style="font-size:0.82rem;">
                                    @foreach($bienThe->thuocTinhs as $tt)
                                        <span class="badge bg-light text-dark border me-1" style="font-size:0.7rem;">{{ $tt->ten_thuoc_tinh }}</span>
                                    @endforeach
                                    @if($bienThe->thuocTinhs->isEmpty())
                                        <em class="small text-muted">Bien the</em>
                                    @endif
                                </span>
                            </div>
                        </td>
                        <td></td>
                        <td onclick="event.stopPropagation(); openProductDrawer({{ $bienThe->id }});">
                            <span class="text-muted small">{{ $bienThe->donVi->ten_don_vi ?? '-' }}</span>
                        </td>
                        <td></td>
                        <td onclick="event.stopPropagation(); openProductDrawer({{ $bienThe->id }});">
                            <span class="fw-bold text-primary" style="font-size:0.82rem;">{{ number_format($bienThe->gia_ban, 0, ',', '.') }} đ</span>
                        </td>
                        <td onclick="event.stopPropagation(); openProductDrawer({{ $bienThe->id }});">
                            <span class="{{ ($bienThe->so_luong_ton_kho ?? 0) <= ($bienThe->dinh_muc_toi_thieu ?? 0) ? 'text-warning' : 'text-muted' }} small">{{ $bienThe->so_luong_ton_kho ?? 0 }}</span>
                        </td>
                        <td onclick="event.stopPropagation(); openProductDrawer({{ $bienThe->id }});">
                            @if(!$bienThe->trang_thai)
                                <span class="badge bg-danger">Ngừng</span>
                            @elseif(($bienThe->so_luong_ton_kho ?? 0) <= 0)
                                <span class="badge bg-secondary">Hết</span>
                            @elseif(($bienThe->so_luong_ton_kho ?? 0) <= ($bienThe->dinh_muc_toi_thieu ?? 0))
                                <span class="badge bg-warning text-dark">Sắp hết</span>
                            @else
                                <span class="badge bg-success">Còn</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach

                    {{-- Biến thể đơn vị (hiển thị như rows độc lập) --}}
                    @foreach($bienTheDonVi as $bienThe)
                    <tr class="product-row" style="cursor:pointer;" data-product-id="{{ $bienThe->id }}" data-has-variants="0">
                        <td onclick="event.stopPropagation();">
                            <input type="checkbox" class="form-check-input product-checkbox" value="{{ $bienThe->id }}">
                        </td>
                        <td onclick="event.stopPropagation(); openProductDrawer({{ $bienThe->id }});">
                            @if($bienThe->hinh_anh)
                                <img src="{{ asset($bienThe->hinh_anh) }}" alt="" style="width:36px; height:36px; object-fit:cover; border-radius:4px; border:1px solid #eee;">
                            @else
                                <div style="width:36px; height:36px; border-radius:4px; background:#eee; display:flex; align-items:center; justify-content:center;">
                                    <i class="fas fa-image text-muted" style="font-size:0.7rem;"></i>
                                </div>
                            @endif
                        </td>
                        <td onclick="event.stopPropagation(); openProductDrawer({{ $bienThe->id }});">
                            <span class="text-muted small">#{{ $bienThe->ma_hang }}</span>
                        </td>
                        <td onclick="event.stopPropagation(); openProductDrawer({{ $bienThe->id }});">
                            <span class="text-muted small">{{ $bienThe->ma_vach ?? '-' }}</span>
                        </td>
                        <td onclick="event.stopPropagation(); openProductDrawer({{ $bienThe->id }});">
                            <div class="d-flex align-items-center gap-2" style="padding-left:20px;">
                                <i class="fas fa-arrow-turn-down-right text-muted" style="font-size:0.6rem;"></i>
                                <span class="text-muted" style="font-size:0.82rem;">
                                    <span class="badge bg-info text-white me-1" style="font-size:0.68rem;">Don vi</span>
                                    <em class="small text-muted">{{ $bienThe->ten_san_pham }}</em>
                                </span>
                            </div>
                        </td>
                        <td onclick="event.stopPropagation(); openProductDrawer({{ $bienThe->id }});">
                            <span class="text-muted small">{{ $sanPham->danhMuc->ten_danh_muc ?? '-' }}</span>
                        </td>
                        <td onclick="event.stopPropagation(); openProductDrawer({{ $bienThe->id }});">
                            <span class="text-muted small">{{ $bienThe->donVi->ten_don_vi ?? '-' }}</span>
                        </td>
                        <td></td>
                        <td onclick="event.stopPropagation(); openProductDrawer({{ $bienThe->id }});">
                            <span class="fw-bold text-primary" style="font-size:0.82rem;">{{ number_format($bienThe->gia_ban, 0, ',', '.') }} đ</span>
                        </td>
                        <td onclick="event.stopPropagation(); openProductDrawer({{ $bienThe->id }});">
                            <span class="{{ ($bienThe->so_luong_ton_kho ?? 0) <= ($bienThe->dinh_muc_toi_thieu ?? 0) ? 'text-warning' : 'text-muted' }} small">{{ $bienThe->so_luong_ton_kho ?? 0 }}</span>
                        </td>
                        <td onclick="event.stopPropagation(); openProductDrawer({{ $bienThe->id }});">
                            @if(!$bienThe->trang_thai)
                                <span class="badge bg-danger">Ngừng</span>
                            @elseif(($bienThe->so_luong_ton_kho ?? 0) <= 0)
                                <span class="badge bg-secondary">Hết</span>
                            @elseif(($bienThe->so_luong_ton_kho ?? 0) <= ($bienThe->dinh_muc_toi_thieu ?? 0))
                                <span class="badge bg-warning text-dark">Sắp hết</span>
                            @else
                                <span class="badge bg-success">Còn</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="text-center text-muted py-5">
            <i class="fas fa-box-open fa-3x mb-3"></i>
            <p class="mb-0">Hiện chưa có sản phẩm nào. Vui lòng thêm sản phẩm mới.</p>
        </div>
        @endif
    </div>
    <div class="card-footer bg-white">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <span class="text-muted">
                    Hiển thị {{ $sanPhams->firstItem() ?? 0 }} - {{ $sanPhams->lastItem() ?? 0 }} trên {{ $sanPhams->total() }} sản phẩm
                </span>
            </div>
            <nav>
                {{ $sanPhams->links('pagination::bootstrap-5') }}
            </nav>
        </div>
    </div>
</div>

<!-- Product Detail Drawer -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="productDetailDrawer" style="width:680px;">
    <div class="offcanvas-header border-bottom">
        <h5 class="offcanvas-title fw-bold"><i class="fas fa-box-open me-2 text-primary"></i>Chi tiết sản phẩm</h5>
        <div class="d-flex gap-2">
            <a href="#" id="drawerEditBtn" class="btn btn-sm btn-primary">
                <i class="fas fa-edit me-1"></i>Sửa
            </a>
            <button type="button" class="btn btn-sm btn-danger" id="drawerDeleteBtn" onclick="confirmDeleteFromDrawer()">
                <i class="fas fa-trash me-1"></i>Xóa
            </button>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
        </div>
    </div>
    <div class="offcanvas-body p-0" id="drawerBody" style="overflow-y:auto;">
        <!-- Loading -->
        <div class="d-flex justify-content-center align-items-center" style="min-height:300px;">
            <div class="text-center">
                <div class="spinner-border text-primary mb-3" role="status"></div>
                <p class="text-muted mb-0">Đang tải...</p>
            </div>
        </div>
    </div>
</div>

<!-- Add Product Modal -->
<div class="modal fade" id="addProductModal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-fullheight">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #0d6efd 0%, #0b5ed7 100%); color:white;">
                <div>
                    <h5 class="modal-title fw-bold mb-0"><i class="fas fa-box-open me-2"></i>THÊM SẢN PHẨM</h5>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ url('admin/san-pham') }}" method="POST" enctype="multipart/form-data" id="productForm">
                @csrf
                <div class="modal-body p-3" id="addProductModalBody">
                    {{-- ======== 1. Thông tin chung ======== --}}
                    <div class="section-card mb-2">
                        <div class="section-header" onclick="toggleSection(this)">
                            <span><i class="fas fa-info-circle me-2 text-primary"></i>Thông tin chung</span>
                            <i class="fas fa-chevron-down section-arrow"></i>
                        </div>
                        <div class="section-body open">
                            <div class="row g-3">
                                <div class="col-6">
                                    <label class="form-label">Tên sản phẩm <span class="text-danger">*</span></label>
                                    <input type="text" name="ten_san_pham" class="form-control" placeholder="VD: Áo thun Doremon" required>
                                </div>
                                <div class="col-3">
                                    <label class="form-label">Danh mục <span class="text-danger">*</span></label>
                                    <select class="form-select" name="id_danh_muc" required>
                                        <option selected disabled value="">-- Chọn --</option>
                                        @foreach($danhMucs as $danhMuc)
                                            <option value="{{ $danhMuc->id }}">{{ $danhMuc->ten_danh_muc }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-3">
                                    <label class="form-label">Thương hiệu</label>
                                    <input type="text" name="thuong_hieu" class="form-control" placeholder="VD: Nike">
                                </div>
                                <div class="col-4">
                                    <label class="form-label">Barcode / Mã vạch</label>
                                    <input type="text" name="ma_vach" id="maVachChung" class="form-control" placeholder="8934567890123">
                                </div>
                                <div class="col-8">
                                    <label class="form-label">Mô tả</label>
                                    <textarea name="mo_ta" class="form-control" rows="1" placeholder="Mô tả sản phẩm..."></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ======== 2. Giá & Tồn kho ======== --}}
                    <div class="section-card mb-2">
                        <div class="section-header" onclick="toggleSection(this)">
                            <span><i class="fas fa-coins me-2 text-warning"></i>Giá &amp; Tồn kho</span>
                            <i class="fas fa-chevron-down section-arrow"></i>
                        </div>
                        <div class="section-body open">
                            <div class="row g-3 align-items-end">
                                <div class="col-3">
                                    <label class="form-label">Giá vốn</label>
                                    <div class="input-group">
                                        <input type="number" name="gia_von" class="form-control" placeholder="0" min="0">
                                        <span class="input-group-text">đ</span>
                                    </div>
                                </div>
                                <div class="col-3">
                                    <label class="form-label">Giá bán <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="number" name="gia_ban" class="form-control" placeholder="0" min="0" required>
                                        <span class="input-group-text">đ</span>
                                    </div>
                                </div>
                                <div class="col-3">
                                    <label class="form-label">Tồn kho</label>
                                    <input type="number" name="so_luong_ton_kho" class="form-control" placeholder="0" min="0" value="0">
                                </div>
                                <div class="col-3">
                                    <label class="form-label">Định mức tối thiểu</label>
                                    <input type="number" name="dinh_muc_toi_thieu" class="form-control" placeholder="0" min="0" value="0">
                                </div>
                                <div class="col-12">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="trang_thai" id="trangThaiSwitch" checked value="1">
                                        <label class="form-check-label fw-medium" for="trangThaiSwitch">Sản phẩm đang được bán</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ======== 3. Hình ảnh ======== --}}
                    <div class="section-card mb-2">
                        <div class="section-header" onclick="toggleSection(this)">
                            <span><i class="fas fa-image me-2 text-success"></i>Hình ảnh</span>
                            <i class="fas fa-chevron-down section-arrow"></i>
                        </div>
                        <div class="section-body open">
                            <div class="d-flex gap-3 align-items-start">
                                <div class="border rounded d-flex align-items-center justify-content-center"
                                     id="imageUploadArea"
                                     style="width:180px; height:180px; border-style:dashed !important; cursor:pointer; background:#f8f9fa; flex-shrink:0;"
                                     onclick="document.getElementById('hinhAnhInput').click()">
                                    <div id="imagePlaceholder" class="text-center">
                                        <i class="fas fa-cloud-upload-alt fa-2x text-muted mb-2 d-block"></i>
                                        <p class="text-muted small mb-0">Chọn ảnh</p>
                                    </div>
                                    <img id="imagePreview" src="" class="img-fluid rounded d-none" style="object-fit:cover; width:100%; height:100%;">
                                </div>
                                <input type="file" name="hinh_anh" id="hinhAnhInput" class="d-none" accept="image/*">
                                <div class="flex-grow-1">
                                    <p class="text-muted small mb-1">Hỗ trợ định dạng: JPEG, PNG, JPG, GIF, WEBP</p>
                                    <p class="text-muted small mb-2">Dung lượng tối đa: 5MB</p>
                                    <button type="button" class="btn btn-sm btn-outline-danger d-none" id="removeImageBtn">
                                        <i class="fas fa-trash me-1"></i>Xóa ảnh
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ======== 4. Đơn vị bán hàng ======== --}}
                    <div class="section-card mb-2">
                        <div class="section-header" onclick="toggleSection(this)">
                            <span><i class="fas fa-ruler-combined me-2 text-info"></i>Đơn vị bán hàng</span>
                            <div class="d-flex align-items-center gap-2">
                                <span class="text-muted small">Đơn vị cơ bản:</span>
                                <input type="text" name="don_vi_text" class="form-control form-control-sm" style="width:120px;" id="donViTextInput" placeholder="VD: Lon" value="Cái">
                                <button type="button" class="btn btn-sm btn-primary" id="btnThemDonVi"><i class="fas fa-plus me-1"></i>Thêm</button>
                                <i class="fas fa-chevron-down section-arrow"></i>
                            </div>
                        </div>
                        <div class="section-body open">
                            <div class="table-scroll-wrap">
                                <table class="table table-bordered table-sm mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="text-center" style="width:40px;">STT</th>
                                            <th style="min-width:130px;">Tên đơn vị <span class="text-danger">*</span></th>
                                            <th style="width:130px;">Quy đổi</th>
                                            <th style="width:130px;">Giá lẻ (đ) <span class="text-danger">*</span></th>
                                            <th style="width:130px;">Giá sỉ (đ)</th>
                                            <th style="width:140px;">Mã vạch</th>
                                            <th style="width:90px;">Tồn kho</th>
                                            <th style="width:72px;">Ảnh</th>
                                            <th class="text-center" style="width:40px;">
                                                <input type="checkbox" id="selectAllUnits" title="Chọn tất cả">
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody id="donViTableBody">
                                        <tr data-unit-row>
                                            <td class="text-center align-middle small fw-medium">1</td>
                                            <td>
                                                <input type="text" name="hang_cung_loai[0][ten_don_vi]" class="form-control unit-ten" placeholder="VD: Lon" required>
                                            </td>
                                            <td>
                                                <div class="input-group input-group-sm">
                                                    <input type="number" name="hang_cung_loai[0][so_luong_quy_doi]" class="form-control unit-qty" value="1" min="1">
                                                    <span class="input-group-text">/ ĐV</span>
                                                </div>
                                            </td>
                                            <td>
                                                <input type="number" name="hang_cung_loai[0][gia_ban_le]" class="form-control unit-gia-le" placeholder="0" min="0">
                                            </td>
                                            <td>
                                                <input type="number" name="hang_cung_loai[0][gia_ban_si]" class="form-control unit-gia-si" placeholder="0" min="0">
                                            </td>
                                            <td>
                                                <input type="text" name="hang_cung_loai[0][ma_vach]" class="form-control unit-mavach" placeholder="Mã vạch">
                                            </td>
                                            <td>
                                                <input type="number" name="hang_cung_loai[0][so_luong]" class="form-control unit-tonkho" value="0" min="0">
                                            </td>
                                            <td style="width:72px;">
                                                <div class="unit-img-cell" data-idx="0" style="position:relative;">
                                                    <div class="unit-img-placeholder" style="width:52px;height:52px;border:2px dashed #dee2e6;border-radius:6px;display:flex;align-items:center;justify-content:center;cursor:pointer;background:#f8f9fa;">
                                                        <i class="fas fa-image text-muted"></i>
                                                    </div>
                                                    <img class="unit-img-preview d-none" style="width:52px;height:52px;object-fit:cover;border-radius:6px;border:1px solid #dee2e6;" data-preview-for="0">
                                                    <input type="file" class="unit-img-input d-none" accept="image/*" data-file-for="0" name="hang_cung_loai[0][hinh_anh]">
                                                    <button type="button" class="unit-img-remove btn btn-sm btn-danger rounded-circle d-none" data-remove-for="0" style="position:absolute;top:-6px;right:-6px;width:18px;height:18px;padding:0;font-size:9px;line-height:18px;text-align:center;">×</button>
                                                </div>
                                            </td>
                                            <td class="text-center align-middle">
                                                <input type="checkbox" class="unit-check form-check-input">
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mt-2">
                                <span class="badge bg-secondary" id="unitCountLabel">1 đơn vị</span>
                                <div class="d-flex gap-2">
                                    <button type="button" class="btn btn-sm btn-outline-secondary" id="btnApDungGiaMacDinh">
                                        <i class="fas fa-sync-alt me-1"></i>Áp dụng giá mặc định
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-danger" id="btnXoaDonViDaChon">
                                        <i class="fas fa-trash me-1"></i>Xóa đã chọn
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ======== 5. Thuộc tính & Tổ hợp ======== --}}
                    <div class="section-card mb-2">
                        <div class="section-header" onclick="toggleSection(this)">
                            <span><i class="fas fa-tags me-2 text-danger"></i>Thuộc tính &amp; Tổ hợp</span>
                            <i class="fas fa-chevron-down section-arrow"></i>
                        </div>
                        <div class="section-body open">
                            <div class="row g-3">
                                {{-- Left: Thuộc tính --}}
                                <div class="col-6">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <h6 class="mb-0 fw-semibold"><i class="fas fa-layer-group me-1 text-danger"></i> Thuộc tính</h6>
                                        <button type="button" class="btn btn-sm btn-danger" id="btnThemThuocTinh">
                                            <i class="fas fa-plus me-1"></i>Thêm nhóm
                                        </button>
                                    </div>
                                    <div id="thuocTinhContainer"></div>
                                    <div id="thuocTinhEmptyHint" class="text-center text-muted py-4 border border-dashed rounded">
                                        <p class="mb-1 small">Bấm <b>"Thêm nhóm"</b> để thêm thuộc tính</p>
                                        <p class="mb-0 text-secondary" style="font-size:0.72rem;">VD: Màu sắc, Size, Chất liệu...</p>
                                    </div>
                                    <div id="taoBangContainer" class="mt-2 text-center" style="display:none;">
                                        <span class="badge bg-info me-2 mb-1" id="tongBienTheText"></span>
                                        <button type="button" class="btn btn-sm btn-success" id="btnTaoBangBienThe">
                                            <i class="fas fa-table me-1"></i>Tạo bảng tổ hợp
                                        </button>
                                    </div>
                                </div>
                                {{-- Right: Tổ hợp --}}
                                <div class="col-6">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <h6 class="mb-0 fw-semibold"><i class="fas fa-swatchbook me-1 text-primary"></i> Tổ hợp</h6>
                                        <span class="badge bg-primary" id="soLuongBienTheBadge" style="display:none;">0</span>
                                    </div>
                                    <div class="border rounded p-2 tong-hop-wrap" id="tongHopHienThi" style="max-height:220px; overflow-y:auto; background:#fafafa; min-height:80px;">
                                        <p class="text-muted small mb-0 text-center py-3" id="khongCoTongHop">Chưa có tổ hợp nào</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ======== 6. Bảng hàng cùng loại ======== --}}
                    <div class="section-card mb-2" id="bangHangCungLoaiSection">
                        <div class="section-header" onclick="toggleSection(this)">
                            <span><i class="fas fa-table me-2 text-primary"></i>Bảng hàng cùng loại</span>
                            <div class="d-flex align-items-center gap-2">
                                <button type="button" class="btn btn-sm btn-success" id="btnCapNhatBangHCL">
                                    <i class="fas fa-sync-alt me-1"></i>Cập nhật bảng
                                </button>
                                <i class="fas fa-chevron-down section-arrow"></i>
                            </div>
                        </div>
                        <div class="section-body open">
                            <div class="table-scroll-wrap bang-hcl-table-wrap">
                                <table class="table table-bordered table-sm mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="text-center" style="width:44px;">STT</th>
                                            <th style="min-width:200px;">Tên sản phẩm</th>
                                            <th style="width:100px;">Tổ hợp</th>
                                            <th style="width:90px;">Quy đổi</th>
                                            <th style="width:120px;">Giá lẻ (đ)</th>
                                            <th style="width:120px;">Giá sỉ (đ)</th>
                                            <th style="width:130px;">Mã vạch</th>
                                            <th style="width:80px;">Tồn kho</th>
                                            <th style="width:72px;">Ảnh</th>
                                            <th style="width:80px;">Thao tác</th>
                                        </tr>
                                    </thead>
                                    <tbody id="bangHangCungLoaiBody">
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer bg-light">
                    <div class="d-flex justify-content-between w-100 align-items-center">
                        <span class="text-muted small"><i class="fas fa-info-circle me-1"></i> Điền đầy đủ thông tin trước khi lưu</span>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Hủy bỏ</button>
                            <button type="submit" class="btn btn-success px-4">
                                <i class="fas fa-save me-1"></i>Lưu sản phẩm
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    /* ======= Modal Full Height ======= */
    #addProductModal .modal-fullheight {
        max-width: 1120px;
        margin: 1.5rem auto;
    }
    #addProductModal .modal-fullheight .modal-content {
        height: 100%;
        max-height: calc(100vh - 3rem);
        display: flex;
        flex-direction: column;
    }
    #addProductModal .modal-fullheight .modal-body {
        overflow-y: auto;
        flex: 1;
        padding: 1.25rem 1.5rem;
    }
    #addProductModal .modal-fullheight .modal-header {
        flex-shrink: 0;
        padding: 1rem 1.5rem;
    }
    #addProductModal .modal-fullheight .modal-footer {
        flex-shrink: 0;
        padding: 0.75rem 1.5rem;
    }

    /* ======= Card headings ======= */
    #addProductModal .card-header h6 {
        font-size: 0.88rem;
        letter-spacing: 0.02em;
    }

    /* ======= Section Card (KiotViet-style collapsible) ======= */
    #addProductModal .section-card {
        border: 1px solid #e0e4e8;
        border-radius: 8px;
        margin-bottom: 10px;
        overflow: hidden;
        background: white;
        box-shadow: 0 1px 3px rgba(0,0,0,0.06);
    }
    #addProductModal .section-header {
        background: #f8f9fa;
        padding: 10px 14px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        cursor: pointer;
        user-select: none;
        font-weight: 600;
        font-size: 0.84rem;
        color: #212529;
        border-bottom: 1px solid #e9ecef;
    }
    #addProductModal .section-header:hover { background: #e9ecef; }
    #addProductModal .section-header > span {
        display: flex;
        align-items: center;
        gap: 6px;
    }
    #addProductModal .section-header > span i {
        opacity: 0.75;
    }
    #addProductModal .section-body { display: none; padding: 14px; overflow: hidden; }
    #addProductModal .section-body.open { display: block; }
    #addProductModal .section-arrow {
        font-size: 0.68rem;
        color: #6c757d;
        transition: transform 0.2s ease;
    }
    #addProductModal .section-header.open .section-arrow { transform: rotate(180deg); }

    /* ======= Form fields ======= */
    #addProductModal .form-label {
        font-size: 0.8rem;
        margin-bottom: 0.2rem;
        color: #495057;
    }
    #addProductModal .form-control,
    #addProductModal .form-select {
        font-size: 0.85rem;
    }
    #addProductModal .input-group-text {
        font-size: 0.82rem;
    }

    /* ======= Table ======= */
    #addProductModal table {
        font-size: 0.82rem;
    }
    #addProductModal table th {
        font-size: 0.78rem;
        white-space: nowrap;
        vertical-align: middle;
    }
    #addProductModal table td {
        vertical-align: middle;
        padding: 0.4rem 0.5rem;
    }
    #addProductModal table tbody tr:hover {
        background-color: #f8faff;
    }

    /* ======= Table scroll wrap (sticky header + horizontal scroll) ======= */
    #addProductModal .table-scroll-wrap {
        overflow-x: auto;
        overflow-y: auto;
        height: 240px;
    }
    #addProductModal .table-scroll-wrap table {
        table-layout: fixed;
        min-width: 900px;
    }
    #addProductModal .table-scroll-wrap table thead th {
        position: sticky;
        top: 0;
        z-index: 2;
    }

    #addProductModal .bang-hcl-table-wrap {
        height: 300px;
        overflow-x: auto;
    }
    #addProductModal .bang-hcl-table-wrap table {
        table-layout: fixed;
        min-width: 600px;
    }

    /* ======= Tổ hợp wrap ======= */
    #addProductModal .tong-hop-wrap {
        overflow-x: auto;
    }

    /* ======= Attrib chip ======= */
    #addProductModal .attr-chip {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 4px 10px;
        border-radius: 15px;
        border: 1px solid #dee2e6;
        background: #f8f9fa;
        font-size: 0.8rem;
        cursor: pointer;
        user-select: none;
        transition: all 0.15s;
    }
    #addProductModal .attr-chip:hover { border-color: #0d6efd; background: #e7f1ff; }
    #addProductModal .attr-chip.selected { background: #0d6efd; color: white; border-color: #0d6efd; }
    #addProductModal .attr-chip .chip-check {
        width: 14px; height: 14px;
        border: 1.5px solid #adb5bd;
        border-radius: 3px;
        display: flex; align-items: center; justify-content: center;
        background: white;
        transition: all 0.15s;
        flex-shrink: 0;
    }
    #addProductModal .attr-chip.selected .chip-check {
        background: white; border-color: white;
    }
    #addProductModal .attr-chip.selected .chip-check::after {
        content: '✓'; font-size: 9px; color: #0d6efd; font-weight: bold;
    }
    #addProductModal .don-vi-chip {
        display: inline-block;
        padding: 3px 10px;
        border-radius: 12px;
        border: 1px solid #dee2e6;
        background: white;
        font-size: 0.78rem;
        cursor: pointer;
        margin: 2px;
    }
    #addProductModal .don-vi-chip:hover { border-color: #0d6efd; }
    #addProductModal .don-vi-chip.active { background: #0d6efd; color: white; border-color: #0d6efd; }
    #addProductModal .product-row:hover > td { background-color: #f0f7ff !important; }
    #addProductModal .unit-table tr:hover > td { background-color: #f8f9fa; }
    #addProductModal .hcl-preview-row:hover > td { background-color: #f0f7ff !important; }
</style>

<script>
    // ---- Section collapse/expand (KiotViet-style) ----
    function toggleSection(headerEl) {
        const card = headerEl.closest('.section-card');
        const body = card.querySelector('.section-body');
        const isOpen = body.classList.contains('open');
        body.classList.toggle('open', !isOpen);
        headerEl.classList.toggle('open', !isOpen);
    }

(function() {
    // ---- State ----
    let unitIndex = 0;  // next index for file inputs
    let unitRowCount = 0;

    // ---- Auto-fill Mã vạch from Thông tin chung into first unit row ----
    const maVachChungInput = document.getElementById('maVachChung');
    maVachChungInput?.addEventListener('input', function() {
        const barcode = this.value.trim();
        const firstRow = donViTableBody.querySelector('tr[data-unit-row]');
        if (!firstRow) return;
        const firstMavachInput = firstRow.querySelector('.unit-mavach');
        if (firstMavachInput) {
            firstMavachInput.value = barcode;
            firstMavachInput.dispatchEvent(new Event('input', { bubbles: true }));
        }
    });

    // ---- Barcode Scanner: Search product by barcode and auto-fill unit rows ----
    window.searchBarcodeHCL = async function(input) {
        const barcode = input.value.trim();
        if (!barcode) return;

        // Check if already in list
        const existing = donViTableBody.querySelectorAll('.unit-mavach');
        for (const inp of existing) {
            if (inp.value === barcode) {
                if (typeof Toast !== 'undefined') {
                    Toast.fire({ icon: 'warning', title: 'Ma vach da co trong danh sach' });
                }
                return;
            }
        }

        try {
            const res = await fetch(`/admin/api/san-pham/search-barcode?barcode=${encodeURIComponent(barcode)}`);
            const json = await res.json();

            if (!json.success || !json.data || !json.data.bien_the_don_vi?.length) {
                if (typeof Toast !== 'undefined') {
                    Toast.fire({ icon: 'error', title: 'Khong tim thay san pham' });
                }
                return;
            }

            const data = json.data;

            // Auto-fill product name if empty
            const tenSanPhamInput = document.getElementById('tenSanPham');
            if (tenSanPhamInput && !tenSanPhamInput.value.trim()) {
                tenSanPhamInput.value = data.ten_san_pham;
            }

            // Add each unit as a new row
            for (const unit of data.bien_the_don_vi) {
                window.addUnitRow(
                    unit.ten_don_vi,
                    unit.gia_ban || '',
                    '',
                    unit.ma_vach || '',
                    unit.so_luong_quy_doi || 1,
                    unit.so_luong_ton_kho || 0,
                    null,
                    null
                );
            }

            if (typeof Toast !== 'undefined') {
                Toast.fire({ icon: 'success', title: `Da them ${data.bien_the_don_vi.length} don vi` });
            }
        } catch (e) {
            if (typeof Toast !== 'undefined') {
                Toast.fire({ icon: 'error', title: 'Loi khi tim ma vach' });
            }
        }
    };

    // ---- Image Preview (main product image) ----
    const imageUploadArea = document.getElementById('imageUploadArea');
    const hinhAnhInput = document.getElementById('hinhAnhInput');
    const imagePreview = document.getElementById('imagePreview');
    const imagePlaceholder = document.getElementById('imagePlaceholder');
    const removeImageBtn = document.getElementById('removeImageBtn');

    imageUploadArea.addEventListener('click', () => hinhAnhInput.click());
    hinhAnhInput.addEventListener('change', function() {
        const file = this.files[0];
        if (!file) return;
        const reader = new FileReader();
        reader.onload = e => {
            imagePreview.src = e.target.result;
            imagePreview.classList.remove('d-none');
            imagePlaceholder.classList.add('d-none');
            removeImageBtn.classList.remove('d-none');
        };
        reader.readAsDataURL(file);
    });
    removeImageBtn.addEventListener('click', function(e) {
        e.stopPropagation();
        hinhAnhInput.value = '';
        imagePreview.src = '';
        imagePreview.classList.add('d-none');
        imagePlaceholder.classList.remove('d-none');
        removeImageBtn.classList.add('d-none');
    });

    // ---- Don Vi Table Management ----
    const donViTableBody = document.getElementById('donViTableBody');
    const unitCountLabel = document.getElementById('unitCountLabel');
    const btnThemDonVi = document.getElementById('btnThemDonVi');
    const btnXoaDonViDaChon = document.getElementById('btnXoaDonViDaChon');
    const btnApDungGiaMacDinh = document.getElementById('btnApDungGiaMacDinh');
    const selectAllUnits = document.getElementById('selectAllUnits');
    const donViTextInput = document.getElementById('donViTextInput');
    const btnCapNhatBangHCL = document.getElementById('btnCapNhatBangHCL');

    // Render STT for unit table
    function renderUnitSTT() {
        donViTableBody.querySelectorAll('tr').forEach((tr, i) => {
            const firstTd = tr.querySelector('td');
            if (firstTd) firstTd.textContent = i + 1;
        });
        const count = donViTableBody.querySelectorAll('tr').length;
        unitRowCount = count;
        unitCountLabel.textContent = count + ' đơn vị';
    }

    // Add a new unit row
    window.addUnitRow = function(tenDonVi, giaLe, giaSi, maVach, qty, tonKho, imgData, imgFileFor) {
        unitIndex++;
        const idx = unitIndex;
        const currentCount = donViTableBody.querySelectorAll('tr').length;

        const html = `
        <tr data-unit-row>
            <td class="text-center align-middle small fw-medium">${currentCount + 1}</td>
            <td>
                <input type="text" name="hang_cung_loai[${currentCount}][ten_don_vi]" class="form-control form-control-sm unit-ten" placeholder="VD: Lon" value="${tenDonVi || ''}" required>
            </td>
            <td>
                <div class="input-group input-group-sm">
                    <input type="number" name="hang_cung_loai[${currentCount}][so_luong_quy_doi]" class="form-control unit-qty" value="${qty || 1}" min="1">
                    <span class="input-group-text">/ ĐV</span>
                </div>
            </td>
            <td>
                <input type="number" name="hang_cung_loai[${currentCount}][gia_ban_le]" class="form-control form-control-sm unit-gia-le" placeholder="0" min="0" value="${giaLe || ''}">
            </td>
            <td>
                <input type="number" name="hang_cung_loai[${currentCount}][gia_ban_si]" class="form-control form-control-sm unit-gia-si" placeholder="0" min="0" value="${giaSi || ''}">
            </td>
            <td>
                <input type="text" name="hang_cung_loai[${currentCount}][ma_vach]" class="form-control form-control-sm unit-mavach" placeholder="Mã vạch" value="${maVach || ''}">
            </td>
            <td>
                <input type="number" name="hang_cung_loai[${currentCount}][so_luong]" class="form-control form-control-sm unit-tonkho" value="${tonKho || 0}" min="0">
            </td>
            <td style="width:80px;">
                <div class="unit-img-cell" data-idx="${idx}" style="position:relative;">
                    <div class="unit-img-placeholder" style="width:56px;height:56px;border:2px dashed #dee2e6;border-radius:6px;display:flex;align-items:center;justify-content:center;cursor:pointer;background:#f8f9fa;">
                        <i class="fas fa-image text-muted"></i>
                    </div>
                    ${imgData ? `<img class="unit-img-preview" style="width:56px;height:56px;object-fit:cover;border-radius:6px;border:1px solid #dee2e6;" src="${imgData}" data-preview-for="${idx}">` : `<img class="unit-img-preview d-none" style="width:56px;height:56px;object-fit:cover;border-radius:6px;border:1px solid #dee2e6;" data-preview-for="${idx}">`}
                    <input type="file" class="unit-img-input d-none" accept="image/*" data-file-for="${idx}" name="hang_cung_loai[${currentCount}][hinh_anh]" ${imgFileFor ? `data-original-for="${imgFileFor}"` : ''}>
                    <button type="button" class="unit-img-remove btn btn-sm btn-danger rounded-circle d-none" data-remove-for="${idx}" style="position:absolute;top:-6px;right:-6px;width:18px;height:18px;padding:0;font-size:9px;line-height:18px;text-align:center;">×</button>
                </div>
            </td>
            <td class="text-center align-middle">
                <input type="checkbox" class="unit-check form-check-input">
            </td>
        </tr>`;

        donViTableBody.insertAdjacentHTML('beforeend', html);
        renderUnitSTT();

        if (imgData) {
            const row = donViTableBody.lastElementChild;
            const placeholder = row.querySelector('.unit-img-placeholder');
            const preview = row.querySelector('.unit-img-preview');
            const removeBtn = row.querySelector('.unit-img-remove');
            if (placeholder) placeholder.classList.add('d-none');
            if (preview) preview.classList.remove('d-none');
            if (removeBtn) removeBtn.classList.remove('d-none');
        }

        attachUnitImageEvents(donViTableBody.lastElementChild);

        // Attach barcode scan listener to the new row's mavach input
        const newRow = donViTableBody.lastElementChild;
        const maVachInput = newRow.querySelector('.unit-mavach');
        if (maVachInput) {
            maVachInput.addEventListener('keydown', async function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    await window.searchBarcodeHCL(this);
                }
            });
        }
    };

    btnThemDonVi.addEventListener('click', function() {
        window.addUnitRow();
    });

    // Delete selected unit rows
    btnXoaDonViDaChon.addEventListener('click', function() {
        donViTableBody.querySelectorAll('.unit-check:checked').forEach(cb => {
            cb.closest('tr').remove();
        });
        renderUnitSTT();
        updateBangHangCungLoai();
    });

    // Select all unit checkboxes
    selectAllUnits.addEventListener('change', function() {
        donViTableBody.querySelectorAll('.unit-check').forEach(cb => cb.checked = this.checked);
    });

    // Apply default retail price to all unit rows
    btnApDungGiaMacDinh.addEventListener('click', function() {
        const giaLe = document.querySelector('input[name="gia_ban"]').value;
        donViTableBody.querySelectorAll('.unit-gia-le').forEach(inp => {
            if (!inp.value) inp.value = giaLe;
        });
    });

    // Attach image events to unit rows
    function attachUnitImageEvents(row) {
        row.querySelectorAll('.unit-img-placeholder').forEach(placeholder => {
            placeholder.addEventListener('click', function() {
                const cell = this.closest('.unit-img-cell');
                const idx = cell.dataset.idx;
                const fileInput = document.querySelector(`.unit-img-input[data-file-for="${idx}"]`);
                if (fileInput) fileInput.click();
            });
        });

        row.querySelectorAll('.unit-img-input').forEach(fileInput => {
            fileInput.addEventListener('change', function() {
                const file = this.files[0];
                if (!file) return;
                const idx = this.dataset.fileFor;
                const preview = document.querySelector(`.unit-img-preview[data-preview-for="${idx}"]`);
                const placeholder = document.querySelector(`.unit-img-cell[data-idx="${idx}"] .unit-img-placeholder`);
                const removeBtn = document.querySelector(`.unit-img-remove[data-remove-for="${idx}"]`);

                const reader = new FileReader();
                reader.onload = e => {
                    if (preview) { preview.src = e.target.result; preview.classList.remove('d-none'); }
                    if (placeholder) placeholder.classList.add('d-none');
                    if (removeBtn) removeBtn.classList.remove('d-none');
                };
                reader.readAsDataURL(file);
            });
        });

        row.querySelectorAll('.unit-img-remove').forEach(btn => {
            btn.addEventListener('click', function() {
                const idx = this.dataset.removeFor;
                const preview = document.querySelector(`.unit-img-preview[data-preview-for="${idx}"]`);
                const placeholder = document.querySelector(`.unit-img-cell[data-idx="${idx}"] .unit-img-placeholder`);
                const fileInput = document.querySelector(`.unit-img-input[data-file-for="${idx}"]`);
                const removeBtn = document.querySelector(`.unit-img-remove[data-remove-for="${idx}"]`);

                if (preview) { preview.src = ''; preview.classList.add('d-none'); }
                if (placeholder) placeholder.classList.remove('d-none');
                if (fileInput) { fileInput.value = ''; }
                if (removeBtn) removeBtn.classList.add('d-none');
            });
        });
    }

    // Attach image events to the initial row
    attachUnitImageEvents(donViTableBody.firstElementChild);

    // ---- Thuộc tính ----
    let thuocTinhIndex = 0;
    let bienTheIndex = 0;

    const thuocTinhContainer = document.getElementById('thuocTinhContainer');
    const thuocTinhEmptyHint = document.getElementById('thuocTinhEmptyHint');
    const taoBangContainer = document.getElementById('taoBangContainer');
    const tongBienTheText = document.getElementById('tongBienTheText');
    const btnThemThuocTinh = document.getElementById('btnThemThuocTinh');
    const btnTaoBangBienThe = document.getElementById('btnTaoBangBienThe');
    const soLuongBienTheBadge = document.getElementById('soLuongBienTheBadge');
    const khongCoTongHop = document.getElementById('khongCoTongHop');
    const tongHopHienThi = document.getElementById('tongHopHienThi');

    function kiemTraHienThiTaoBang() {
        const rows = document.querySelectorAll('.attr-group-row');
        let tong = 1;
        rows.forEach(row => {
            const checked = row.querySelectorAll('.attr-chip.selected');
            if (checked.length > 0) tong *= checked.length;
        });
        thuocTinhEmptyHint.style.display = rows.length === 0 ? 'block' : 'none';
        if (tong > 1) {
            taoBangContainer.style.display = 'block';
            tongBienTheText.textContent = `${tong} tổ hợp`;
        } else {
            taoBangContainer.style.display = 'none';
        }
    }

    btnThemThuocTinh.addEventListener('click', async function() {
        const id = 'tt-' + thuocTinhIndex++;
        const thuocTinhChas = @json($thuocTinhChas ?? []);

        const html = `
        <div class="attr-group-row" id="${id}" style="background:#f8f9fa;border-radius:8px;padding:10px 12px;margin-bottom:8px;border:1px solid #e9ecef;">
            <div class="attr-group-title" style="font-weight:600;font-size:0.82rem;color:#495057;margin-bottom:8px;display:flex;align-items:center;gap:6px;">
                <i class="fas fa-tag text-muted"></i>
                <select class="form-select form-select-sm d-inline-block" style="width:auto;max-width:200px;" data-thuoc-tinh-select>
                    <option value="">-- Chọn thuộc tính --</option>
                    ${thuocTinhChas.map(tt => `<option value="${tt.id}">${tt.ten_thuoc_tinh}</option>`).join('')}
                </select>
                <button type="button" class="btn btn-sm btn-outline-success py-0 px-2" data-tao-moi-cha-btn title="Tạo thuộc tính cha mới">
                    <i class="fas fa-plus"></i>
                </button>
                <div style="margin-left:auto;">
                    <button type="button" class="btn btn-sm btn-outline-danger py-0 px-2" data-xoa-nhom-btn title="Xóa nhóm">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
            <div class="d-flex flex-wrap gap-2 thuoc-tinh-con-list"></div>
            <div class="mt-2">
                <button type="button" class="btn btn-sm btn-outline-primary py-0 px-2" data-them-gia-tri-btn>
                    <i class="fas fa-plus me-1"></i>Thêm giá trị
                </button>
            </div>
        </div>`;

        thuocTinhContainer.insertAdjacentHTML('beforeend', html);
        attachThuocTinhEvents(document.getElementById(id));
        kiemTraHienThiTaoBang();
    });

    function attachThuocTinhEvents(row) {
        const selectEl = row.querySelector('[data-thuoc-tinh-select]');
        const conList = row.querySelector('.thuoc-tinh-con-list');

        selectEl.addEventListener('change', async function() {
            const chaId = this.value;
            conList.innerHTML = '';
            if (!chaId) { kiemTraHienThiTaoBang(); return; }
            try {
                const res = await fetch(`/admin/api/thuoc-tinh/con/${chaId}`);
                const cons = await res.json();
                renderConList(chaId, this.options[this.selectedIndex]?.text || '', cons);
            } catch(e) { console.error(e); }
            kiemTraHienThiTaoBang();
        });

        row.querySelector('[data-tao-moi-cha-btn]').addEventListener('click', async function() {
            const ten = prompt('Nhập tên thuộc tính cha mới (VD: Màu sắc, Size, Chất liệu):');
            if (!ten) return;
            try {
                const res = await fetch('/admin/api/thuoc-tinh', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content || document.querySelector('input[name=_token]')?.value,
                    },
                    body: JSON.stringify({ ten_thuoc_tinh: ten, loai: 'cha' })
                });
                const data = await res.json();
                if (data.success) {
                    document.querySelectorAll('[data-thuoc-tinh-select]').forEach(s => {
                        const opt = document.createElement('option');
                        opt.value = data.data.id;
                        opt.textContent = data.data.ten_thuoc_tinh;
                        s.appendChild(opt);
                    });
                    selectEl.value = data.data.id;
                    selectEl.dispatchEvent(new Event('change'));
                }
            } catch(e) { console.error(e); }
        });

        row.querySelector('[data-them-gia-tri-btn]').addEventListener('click', async function() {
            const ten = prompt('Nhập giá trị (VD: Đen, Trắng, M, L):');
            if (!ten) return;
            const chaId = selectEl.value;
            if (!chaId) { alert('Vui lòng chọn thuộc tính cha trước.'); return; }
            try {
                const res = await fetch('/admin/api/thuoc-tinh', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content || document.querySelector('input[name=_token]')?.value,
                    },
                    body: JSON.stringify({ ten_thuoc_tinh: ten, thuoc_tinh_cha_id: chaId, loai: 'con' })
                });
                const data = await res.json();
                if (data.success) {
                    renderConList(chaId, selectEl.options[selectEl.selectedIndex]?.text || '', [{ id: data.data.id, ten_thuoc_tinh: data.data.ten_thuoc_tinh }], true);
                }
            } catch(e) { console.error(e); }
        });

        row.querySelector('[data-xoa-nhom-btn]').addEventListener('click', function() {
            row.remove();
            kiemTraHienThiTaoBang();
        });
    }

    function renderConList(chaId, chaTen, cons, append) {
        let targetRow = null;
        document.querySelectorAll('.attr-group-row').forEach(r => {
            const sel = r.querySelector('[data-thuoc-tinh-select]');
            if (sel && sel.value == chaId) targetRow = r;
        });
        if (!targetRow) return;
        const conList = targetRow.querySelector('.thuoc-tinh-con-list');
        if (!append) conList.innerHTML = '';
        cons.forEach(c => {
            if (conList.querySelector(`[data-con-id="${c.id}"]`)) return;
            const html = `
            <label class="attr-chip" data-con-id="${c.id}" data-cha-id="${chaId}" data-cha-ten="${chaTen}">
                <span class="chip-check"></span>
                <span class="ms-1">${c.ten_thuoc_tinh}</span>
            </label>`;
            conList.insertAdjacentHTML('beforeend', html);
        });
        conList.querySelectorAll('.attr-chip').forEach(chip => {
            chip.addEventListener('click', function(e) {
                e.preventDefault();
                this.classList.toggle('selected');
                kiemTraHienThiTaoBang();
                capNhatTongHopPanel();
            });
        });
    }

    // ---- Cartesian product ----
    function cartesianProduct(arrays) {
        return arrays.reduce((acc, arr) => {
            if (acc.length === 0) return arr.map(v => [v]);
            return acc.flatMap(a => arr.map(v => [...a, v]));
        }, []);
    }

    // ---- Tổ hợp panel ----
    function capNhatTongHopPanel() {
        const rows = document.querySelectorAll('.attr-group-row');
        if (rows.length === 0) {
            if (khongCoTongHop) khongCoTongHop.style.display = '';
            tongHopHienThi.querySelectorAll('.tong-hop-item').forEach(el => el.remove());
            return;
        }
        const combos = [];
        document.querySelectorAll('.attr-group-row').forEach(row => {
            const checked = row.querySelectorAll('.attr-chip.selected');
            if (checked.length === 0) return;
            combos.push(Array.from(checked).map(chip => ({
                id: chip.dataset.conId,
                ten: chip.querySelector('span:last-child').textContent.trim(),
                chaTen: chip.dataset.chaTen
            })));
        });

        if (combos.length === 0) {
            if (khongCoTongHop) khongCoTongHop.style.display = '';
            tongHopHienThi.querySelectorAll('.tong-hop-item').forEach(el => el.remove());
            return;
        }

        if (khongCoTongHop) khongCoTongHop.style.display = 'none';
        tongHopHienThi.querySelectorAll('.tong-hop-item').forEach(el => el.remove());

        const toHop = cartesianProduct(combos);
        toHop.forEach(combo => {
            const label = combo.map(c => c.ten).join(' × ');
            const div = document.createElement('div');
            div.className = 'tong-hop-item d-flex align-items-center gap-2 mb-1 p-1 rounded';
            div.style.cssText = 'background:#e7f1ff;font-size:0.75rem;';
            div.innerHTML = `<span class="text-truncate flex-grow-1">${label}</span>`;
            tongHopHienThi.appendChild(div);
        });

        soLuongBienTheBadge.textContent = toHop.length;
        soLuongBienTheBadge.style.display = 'inline';
    }

    btnTaoBangBienThe.addEventListener('click', function() {
        const tenSanPham = tenSanPhamInput?.value.trim() || 'Sản phẩm';
        const giaBanMacDinh = giaBanInput?.value || '';
        bangHangCungLoaiBody.innerHTML = '';
        bienTheIndex = 0;

        const nhomThuocTinh = [];
        document.querySelectorAll('.attr-group-row').forEach(row => {
            const checked = row.querySelectorAll('.attr-chip.selected');
            if (checked.length === 0) return;
            const items = [];
            checked.forEach(chip => {
                items.push({
                    id: chip.dataset.conId,
                    ten: chip.querySelector('span:last-child').textContent.trim(),
                    chaId: chip.dataset.chaId,
                    chaTen: chip.dataset.chaTen
                });
            });
            nhomThuocTinh.push(items);
        });

        if (nhomThuocTinh.length === 0) return;
        const toHop = cartesianProduct(nhomThuocTinh);

        toHop.forEach(combo => {
            const tenDayDu = `${tenSanPham} (${combo.map(c => c.ten).join(' - ')})`;
            const thuocTinhIds = combo.map(c => c.id).join(',');
            const idx = bienTheIndex++;

            const html = `
            <tr data-variant-row>
                <td class="text-center align-middle small fw-medium">${idx + 1}</td>
                <td class="align-middle">
                    <input type="text" class="form-control form-control-sm" value="${tenDayDu}" data-variant-ten>
                    <input type="hidden" name="bien_the[${idx}][ten_day_du]" value="${tenDayDu}">
                </td>
                <td style="width:90px;">
                    <input type="hidden" name="bien_the[${idx}][thuoc_tinh_ids]" value="${thuocTinhIds}">
                    <span class="badge bg-light text-dark border" style="font-size:0.72rem;">${combo.map(c => c.ten).join('<br>')}</span>
                </td>
                <td>
                    <div class="input-group input-group-sm">
                        <input type="number" class="form-control" name="bien_the[${idx}][gia_ban]" value="${giaBanMacDinh || ''}" placeholder="0" required>
                        <span class="input-group-text">đ</span>
                    </div>
                </td>
                <td>
                    <input type="text" class="form-control form-control-sm" name="bien_the[${idx}][ma_vach]" placeholder="Mã vạch">
                </td>
                <td>
                    <input type="number" class="form-control form-control-sm" name="bien_the[${idx}][so_luong]" value="0" min="0">
                </td>
                <td style="width:80px;">
                    <div class="variant-img-cell" data-idx="${idx}" style="position:relative;">
                        <div class="variant-img-placeholder" style="width:56px;height:56px;border:2px dashed #dee2e6;border-radius:6px;display:flex;align-items:center;justify-content:center;cursor:pointer;background:#f8f9fa;">
                            <i class="fas fa-image text-muted"></i>
                        </div>
                        <img class="variant-img-preview d-none" style="width:56px;height:56px;object-fit:cover;border-radius:6px;border:1px solid #dee2e6;" data-preview-for="${idx}">
                        <input type="file" class="variant-img-input d-none" name="bien_the[${idx}][hinh_anh]" accept="image/*">
                        <button type="button" class="variant-img-remove btn btn-sm btn-danger rounded-circle d-none" data-remove-for="${idx}" style="position:absolute;top:-6px;right:-6px;width:18px;height:18px;padding:0;font-size:9px;line-height:18px;text-align:center;">×</button>
                    </div>
                </td>
            </tr>`;
            bangHangCungLoaiBody.insertAdjacentHTML('beforeend', html);
        });

        // Attach variant image events
        bangHangCungLoaiBody.querySelectorAll('.variant-img-placeholder').forEach(p => {
            p.addEventListener('click', function() {
                const cell = this.closest('.variant-img-cell');
                const idx = cell.dataset.idx;
                const fileInput = document.querySelector(`.variant-img-input[data-preview-for="${idx}"]`);
                if (fileInput) fileInput.click();
            });
        });
        bangHangCungLoaiBody.querySelectorAll('.variant-img-input').forEach(fi => {
            fi.addEventListener('change', function() {
                const file = this.files[0];
                if (!file) return;
                const idx = this.dataset.previewFor;
                const preview = document.querySelector(`.variant-img-preview[data-preview-for="${idx}"]`);
                const placeholder = document.querySelector(`.variant-img-cell[data-idx="${idx}"] .variant-img-placeholder`);
                const removeBtn = document.querySelector(`.variant-img-remove[data-remove-for="${idx}"]`);
                const reader = new FileReader();
                reader.onload = ev => {
                    if (preview) { preview.src = ev.target.result; preview.classList.remove('d-none'); }
                    if (placeholder) placeholder.classList.add('d-none');
                    if (removeBtn) removeBtn.classList.remove('d-none');
                };
                reader.readAsDataURL(file);
            });
        });
        bangHangCungLoaiBody.querySelectorAll('.variant-img-remove').forEach(btn => {
            btn.addEventListener('click', function() {
                const idx = this.dataset.removeFor;
                const preview = document.querySelector(`.variant-img-preview[data-preview-for="${idx}"]`);
                const placeholder = document.querySelector(`.variant-img-cell[data-idx="${idx}"] .variant-img-placeholder`);
                const fileInput = document.querySelector(`.variant-img-input[data-preview-for="${idx}"]`);
                const removeBtn2 = document.querySelector(`.variant-img-remove[data-remove-for="${idx}"]`);
                if (preview) { preview.src = ''; preview.classList.add('d-none'); }
                if (placeholder) placeholder.classList.remove('d-none');
                if (fileInput) fileInput.value = '';
                if (removeBtn2) removeBtn2.classList.add('d-none');
            });
        });
    });

    // ---- Bang Hang Cung Loai ----
    const bangHangCungLoaiBody = document.getElementById('bangHangCungLoaiBody');
    const tenSanPhamInput = document.querySelector('input[name="ten_san_pham"]');
    const giaBanInput = document.querySelector('input[name="gia_ban"]');

    function updateBangHangCungLoai() {
        const tenSanPham = tenSanPhamInput?.value.trim() || 'Sản phẩm';
        const giaBanMacDinh = giaBanInput?.value || '';
        const rows = donViTableBody.querySelectorAll('tr[data-unit-row]');

        bangHangCungLoaiBody.innerHTML = '';

        rows.forEach((row, i) => {
            const tenDonVi = row.querySelector('.unit-ten')?.value.trim() || '';
            const giaLe = row.querySelector('.unit-gia-le')?.value || '';
            const giaSi = row.querySelector('.unit-gia-si')?.value || '';
            const maVach = row.querySelector('.unit-mavach')?.value || '';
            const qty = row.querySelector('.unit-qty')?.value || '1';
            const tonKho = row.querySelector('.unit-tonkho')?.value || '0';

            if (!tenDonVi) return;

            const tenDayDu = tenSanPham + ' ' + tenDonVi;
            const idx = row.querySelector('.unit-img-cell')?.dataset.idx || i;

            // Check if this row has a custom image
            const previewImg = document.querySelector(`.unit-img-preview[data-preview-for="${idx}"]`);
            const hasCustomImg = previewImg && !previewImg.classList.contains('d-none') && previewImg.src;
            const previewSrc = hasCustomImg ? previewImg.src : '';

            const html = `
            <tr class="hcl-preview-row" data-hcl-row="${i}">
                <td class="text-center align-middle small fw-medium">${i + 1}</td>
                <td class="align-middle">
                    <input type="hidden" name="hang_cung_loai[${i}][ten_don_vi]" value="${tenDonVi}">
                    <input type="hidden" name="hang_cung_loai[${i}][so_luong_quy_doi]" value="${qty}">
                    <span class="fw-medium" style="font-size:0.85rem;">${tenDayDu}</span>
                </td>
                <td class="align-middle text-center">
                    <span class="badge bg-light text-secondary border" style="font-size:0.72rem;">—</span>
                </td>
                <td class="align-middle text-center">
                    <span class="badge bg-info-subtle text-info border" style="font-size:0.78rem;">${qty}</span>
                </td>
                <td class="align-middle">
                    <input type="hidden" name="hang_cung_loai[${i}][gia_ban_le]" value="${giaLe}">
                    <span class="fw-bold text-primary" style="font-size:0.85rem;">${giaLe ? Number(giaLe).toLocaleString('vi-VN') : '—'}</span>
                </td>
                <td class="align-middle">
                    <input type="hidden" name="hang_cung_loai[${i}][gia_ban_si]" value="${giaSi}">
                    <span class="text-muted" style="font-size:0.85rem;">${giaSi ? Number(giaSi).toLocaleString('vi-VN') : '—'}</span>
                </td>
                <td class="align-middle">
                    <input type="hidden" name="hang_cung_loai[${i}][ma_vach]" value="${maVach}">
                    <span class="small text-muted">${maVach || '—'}</span>
                </td>
                <td class="align-middle text-center">
                    <input type="hidden" name="hang_cung_loai[${i}][so_luong]" value="${tonKho}">
                    <span class="small">${tonKho}</span>
                </td>
                <td class="align-middle text-center">
                    <input type="hidden" name="hang_cung_loai[${i}][has_image]" value="${hasCustomImg ? '1' : '0'}">
                    ${hasCustomImg
                        ? `<img src="${previewSrc}" style="width:40px;height:40px;object-fit:cover;border-radius:4px;border:1px solid #dee2e6;">`
                        : `<div style="width:40px;height:40px;border-radius:4px;background:#f0f0f0;display:flex;align-items:center;justify-content:center;"><i class="fas fa-image text-muted" style="font-size:0.7rem;"></i></div>`
                    }
                </td>
                <td class="align-middle text-center">
                    <button type="button" class="btn btn-sm btn-outline-warning" onclick="suaHangCungLoai(this)" title="Sửa">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="xoaHangCungLoai(this)" title="Xóa">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>`;

            bangHangCungLoaiBody.insertAdjacentHTML('beforeend', html);
        });

        // Hide section if no rows
        const section = document.getElementById('bangHangCungLoaiSection');
        if (bangHangCungLoaiBody.querySelectorAll('tr').length === 0) {
            section.style.display = 'none';
        } else {
            section.style.display = 'block';
        }
    }

    // Edit an HCL row — fills data back to unit table
    window.suaHangCungLoai = function(btn) {
        const tr = btn.closest('tr');
        const hiddenInputs = tr.querySelectorAll('input[type="hidden"]');
        // Just re-focus the first unit row as a simple "edit" UX
        donViTableBody.querySelector('.unit-ten')?.focus();
    };

    // Delete an HCL row
    window.xoaHangCungLoai = function(btn) {
        const tr = btn.closest('tr');
        tr.remove();
        // Re-number remaining rows
        bangHangCungLoaiBody.querySelectorAll('tr').forEach((r, idx) => {
            r.querySelector('td:first-child').textContent = idx + 1;
        });
        const section = document.getElementById('bangHangCungLoaiSection');
        if (bangHangCungLoaiBody.querySelectorAll('tr').length === 0) {
            section.style.display = 'none';
        }
    };

    // Update HCL table on unit row change
    donViTableBody.addEventListener('input', function() {
        clearTimeout(window.hclUpdateTimer);
        window.hclUpdateTimer = setTimeout(updateBangHangCungLoai, 400);
    });
    donViTableBody.addEventListener('change', updateBangHangCungLoai);

    // Manual update button
    btnCapNhatBangHCL.addEventListener('click', updateBangHangCungLoai);

    // Initial render
    updateBangHangCungLoai();

    // ---- Reset modal on close ----
    document.getElementById('addProductModal').addEventListener('hidden.bs.modal', function() {
        donViTableBody.innerHTML = `
            <tr data-unit-row>
                <td class="text-center align-middle small fw-medium">1</td>
                <td><input type="text" name="hang_cung_loai[0][ten_don_vi]" class="form-control form-control-sm unit-ten" placeholder="VD: Lon" required></td>
                <td>
                    <div class="input-group input-group-sm">
                        <input type="number" name="hang_cung_loai[0][so_luong_quy_doi]" class="form-control unit-qty" value="1" min="1">
                        <span class="input-group-text">/ ĐV</span>
                    </div>
                </td>
                <td><input type="number" name="hang_cung_loai[0][gia_ban_le]" class="form-control form-control-sm unit-gia-le" placeholder="0" min="0"></td>
                <td><input type="number" name="hang_cung_loai[0][gia_ban_si]" class="form-control form-control-sm unit-gia-si" placeholder="0" min="0"></td>
                <td><input type="text" name="hang_cung_loai[0][ma_vach]" class="form-control form-control-sm unit-mavach" placeholder="Mã vạch"></td>
                <td><input type="number" name="hang_cung_loai[0][so_luong]" class="form-control form-control-sm unit-tonkho" value="0" min="0"></td>
                <td style="width:80px;">
                    <div class="unit-img-cell" data-idx="0" style="position:relative;">
                        <div class="unit-img-placeholder" style="width:56px;height:56px;border:2px dashed #dee2e6;border-radius:6px;display:flex;align-items:center;justify-content:center;cursor:pointer;background:#f8f9fa;">
                            <i class="fas fa-image text-muted"></i>
                        </div>
                        <img class="unit-img-preview d-none" style="width:56px;height:56px;object-fit:cover;border-radius:6px;border:1px solid #dee2e6;" data-preview-for="0">
                        <input type="file" class="unit-img-input d-none" accept="image/*" data-file-for="0" name="hang_cung_loai[0][hinh_anh]">
                        <button type="button" class="unit-img-remove btn btn-sm btn-danger rounded-circle d-none" data-remove-for="0" style="position:absolute;top:-6px;right:-6px;width:18px;height:18px;padding:0;font-size:9px;line-height:18px;text-align:center;">×</button>
                    </div>
                </td>
                <td class="text-center align-middle"><input type="checkbox" class="unit-check form-check-input"></td>
            </tr>`;
        unitIndex = 0;
        unitRowCount = 1;
        unitCountLabel.textContent = '1 đơn vị';
        imagePreview.src = '';
        imagePreview.classList.add('d-none');
        imagePlaceholder.classList.remove('d-none');
        removeImageBtn.classList.add('d-none');
        hinhAnhInput.value = '';
        selectAllUnits.checked = false;
        updateBangHangCungLoai();
        attachUnitImageEvents(donViTableBody.firstElementChild);
    });
})();
</script>

@endsection

@section('page_scripts')
<script src="https://unpkg.com/html5-qrcode@2.3.7/minified/html5-qrcode.min.js"></script>
<script>
    // Bulk Actions
    const selectAllCheckbox = document.getElementById('selectAllCheckbox');
    const productCheckboxes = document.querySelectorAll('.product-checkbox');
    const bulkActionButtons = document.getElementById('bulkActionButtons');
    const selectedCount = document.getElementById('selectedCount');
    const bulkActionForm = document.getElementById('bulkActionForm');
    const bulkActionInput = document.getElementById('bulkActionInput');
    const selectedIdsContainer = document.getElementById('selectedIdsContainer');

    function updateBulkUI() {
        const checked = Array.from(productCheckboxes).filter(cb => cb.checked);
        if (checked.length > 0) {
            bulkActionButtons.classList.remove('d-none');
            selectedCount.textContent = checked.length + ' đã chọn';
        } else {
            bulkActionButtons.classList.add('d-none');
        }
    }

    productCheckboxes.forEach(cb => cb.addEventListener('change', updateBulkUI));

    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function () {
            productCheckboxes.forEach(cb => cb.checked = this.checked);
            updateBulkUI();
        });
    }

    function submitBulkAction(action) {
        const checked = Array.from(productCheckboxes).filter(cb => cb.checked);
        if (checked.length === 0) return;

        const messages = {
            delete: 'Bạn có chắc muốn xóa ' + checked.length + ' sản phẩm đã chọn?',
            activate: 'Bật trạng thái cho ' + checked.length + ' sản phẩm?',
            deactivate: 'Tắt trạng thái cho ' + checked.length + ' sản phẩm?',
        };

        if (!confirm(messages[action] || 'Xác nhận?')) return;

        selectedIdsContainer.innerHTML = '';
        checked.forEach(cb => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'ids[]';
            input.value = cb.value;
            selectedIdsContainer.appendChild(input);
        });
        bulkActionInput.value = action;
        bulkActionForm.submit();
    }
</script>

<script>
    // QR Scanner
    const startQrScanBtn = document.getElementById('startQrScanBtn');
    const stopQrScanBtn = document.getElementById('stopQrScanBtn');
    const qrScannerModal = document.getElementById('qrScannerModal');
    const searchKeywordInput = document.getElementById('searchKeywordInput');
    const qrScannerElementId = 'qrScanner';
    let html5QrCode = null;
    let qrScannerActive = false;

    function startQrScanner() {
        if (qrScannerActive) return;

        html5QrCode = new Html5Qrcode(qrScannerElementId);
        const config = { fps: 10, qrbox: 250 };

        Html5Qrcode.getCameras().then(cameras => {
            if (cameras && cameras.length) {
                const cameraId = cameras[0].id;
                html5QrCode.start(cameraId, config, qrCodeMessage => {
                    if (searchKeywordInput) {
                        searchKeywordInput.value = qrCodeMessage;
                    }
                    bootstrap.Modal.getInstance(qrScannerModal).hide();
                    stopQrScanner();
                    document.querySelector('form[action="{{ url('admin/san-pham') }}"]').submit();
                }, errorMessage => {
                    console.debug('QR scan error', errorMessage);
                }).then(() => {
                    qrScannerActive = true;
                }).catch(err => {
                    console.error('Không thể khởi động QR scanner', err);
                    alert('Không thể khởi động camera để quét mã vạch. Vui lòng kiểm tra quyền truy cập camera.');
                });
            } else {
                alert('Không tìm thấy camera phù hợp để quét mã vạch.');
            }
        }).catch(err => {
            console.error('Lỗi lấy camera', err);
            alert('Không thể truy cập camera. Vui lòng kiểm tra quyền truy cập thiết bị.');
        });
    }

    function stopQrScanner() {
        if (!qrScannerActive || !html5QrCode) return;
        html5QrCode.stop().then(() => {
            html5QrCode.clear();
            qrScannerActive = false;
        }).catch(err => {
            console.error('Lỗi dừng QR scanner', err);
        });
    }

    if (startQrScanBtn) {
        startQrScanBtn.addEventListener('click', function () {
            const modal = new bootstrap.Modal(qrScannerModal);
            modal.show();
            startQrScanner();
        });
    }

    if (stopQrScanBtn) {
        stopQrScanBtn.addEventListener('click', function () {
            const modal = bootstrap.Modal.getInstance(qrScannerModal);
            if (modal) modal.hide();
            stopQrScanner();
        });
    }

    if (qrScannerModal) {
        qrScannerModal.addEventListener('hidden.bs.modal', function () {
            stopQrScanner();
        });
    }
</script>

<!-- Import Product Modal -->
<div class="modal fade" id="importProductModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #198754 0%, #157347 100%); color:white;">
                <div>
                    <h5 class="modal-title fw-bold mb-0"><i class="fas fa-file-import me-2"></i>IMPORT SẢN PHẨM</h5>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ url('admin/san-pham/import') }}" method="POST" enctype="multipart/form-data" id="importForm">
                @csrf
                <div class="modal-body">

                    <div class="alert alert-info d-flex align-items-start gap-2 mb-3">
                        <i class="fas fa-info-circle mt-1"></i>
                        <div>
                            <strong>Hướng dẫn Import:</strong>
                            <ul class="mb-0 mt-1 small">
                                <li>Tải file mẫu bên dưới để đảm bảo đúng định dạng cột</li>
                                <li>File hỗ trợ: <strong>.xlsx</strong>, <strong>.xls</strong>, <strong>.csv</strong></li>
                                <li>Các cột bắt buộc: <code>Tên sản phẩm</code>, <code>Danh mục</code></li>
                                <li>Các cột tùy chọn: Mã vạch, Thương hiệu, Giá vốn, Giá bán, Tồn kho, Định mức tối thiểu, Mô tả, Đơn vị</li>
                                <li>Nếu <strong>Danh mục</strong> chưa tồn tại, hệ thống sẽ tự tạo mới</li>
                            </ul>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-medium small">Chọn file Import</label>
                        <input type="file" name="file" class="form-control" accept=".xlsx,.xls,.csv" required id="importFileInput">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-medium small">Hành động khi trùng dữ liệu</label>
                        <select name="import_action" class="form-select">
                            <option value="skip">Bỏ qua (giữ nguyên dữ liệu cũ)</option>
                            <option value="update">Cập nhật (ghi đè dữ liệu mới)</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-medium small">Tải file mẫu</label>
                        <div>
                            <a href="{{ url('admin/san-pham/export-template') }}" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-download me-1"></i>Tải file Excel mẫu (.xlsx)
                            </a>
                            <a href="{{ url('admin/san-pham/export-template?type=csv') }}" class="btn btn-sm btn-outline-secondary ms-2">
                                <i class="fas fa-download me-1"></i>Tải file CSV mẫu
                            </a>
                        </div>
                    </div>

                    <div id="importPreviewSection" class="d-none">
                        <label class="form-label fw-medium small">Xem trước dữ liệu (5 dòng đầu tiên)</label>
                        <div class="table-responsive border rounded">
                            <table class="table table-sm table-bordered mb-0" id="importPreviewTable">
                                <thead class="table-light"></thead>
                                <tbody></tbody>
                            </table>
                        </div>
                        <div class="form-check mt-2">
                            <input class="form-check-input" type="checkbox" id="confirmImportCheck" required>
                            <label class="form-check-label small" for="confirmImportCheck">
                                Tôi đã kiểm tra dữ liệu và xác nhận Import
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy bỏ</button>
                    <button type="submit" class="btn btn-success" id="btnConfirmImport">
                        <i class="fas fa-upload me-1"></i>Import sản phẩm
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Export Loading Modal -->
<div class="modal fade" id="exportLoadingModal" tabindex="-1">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content text-center">
            <div class="modal-body py-4">
                <div class="spinner-border text-primary mb-3" role="status"></div>
                <p class="mb-0 fw-medium">Đang xuất dữ liệu...</p>
                <p class="text-muted small mb-0">Vui lòng chờ trong giây lát</p>
            </div>
        </div>
    </div>
</div>

<!-- Import Error Modal -->
<div class="modal fade" id="importErrorModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="fas fa-exclamation-triangle me-2"></i>Lỗi Import</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="importErrorContent">
                <!-- Dynamic error content -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
            </div>
        </div>
    </div>
</div>

<script>
// ---- Product Detail Drawer ----
(function() {
    const drawer = document.getElementById('productDetailDrawer');
    const drawerBody = document.getElementById('drawerBody');
    const drawerEditBtn = document.getElementById('drawerEditBtn');

    // Toggle variants expand/collapse
    window.toggleVariants = function(productId) {
        const btn = document.getElementById('expandBtn' + productId);
        const rows = document.querySelectorAll('[id^="variantRow' + productId + '_"]');
        const isExpanded = btn.classList.contains('expanded');

        if (isExpanded) {
            rows.forEach(row => row.style.display = 'none');
            btn.classList.remove('expanded');
            btn.querySelector('i').style.transform = '';
        } else {
            rows.forEach(row => row.style.display = '');
            btn.classList.add('expanded');
            btn.querySelector('i').style.transform = 'rotate(90deg)';
        }
    };

    // Open drawer when clicking any table row (except checkbox/action columns)
    document.getElementById('productTableBody')?.addEventListener('click', function(e) {
        const row = e.target.closest('.product-row');
        if (!row) return;
        const id = row.dataset.productId;
        if (id) openProductDrawer(id);
    });

    window.openProductDrawer = async function(id) {
        const modal = new bootstrap.Offcanvas(drawer);
        drawerBody.innerHTML = `
            <div class="d-flex justify-content-center align-items-center" style="min-height:300px;">
                <div class="text-center">
                    <div class="spinner-border text-primary mb-3" role="status"></div>
                    <p class="text-muted mb-0">Đang tải...</p>
                </div>
            </div>`;
        drawerEditBtn.href = `{{ url('admin/san-pham/') }}/${id}/edit`;
        currentDrawerProductId = id;
        modal.show();

        try {
            const res = await fetch(`/admin/api/san-pham/${id}`);
            const json = await res.json();
            if (!json.success) {
                drawerBody.innerHTML = `<div class="p-4 text-center text-danger">${json.message || 'Không tìm thấy sản phẩm.'}</div>`;
                return;
            }
            renderDrawerContent(json.data);
        } catch(e) {
            drawerBody.innerHTML = `<div class="p-4 text-center text-danger">Lỗi tải dữ liệu: ${e.message}</div>`;
        }
    };

    let currentDrawerProductId = null;
    window.confirmDeleteFromDrawer = function() {
        if (!currentDrawerProductId) return;
        if (!confirm('Bạn có chắc muốn xóa sản phẩm này?')) return;
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `{{ url('admin/san-pham') }}/${currentDrawerProductId}`;
        form.innerHTML = `@csrf @method('DELETE')`;
        document.body.appendChild(form);
        form.submit();
    }

    function formatMoney(num) {
        if (num === null || num === undefined) return '0';
        return Number(num).toLocaleString('vi-VN');
    }

    function formatDate(dateStr) {
        if (!dateStr) return '-';
        const d = new Date(dateStr);
        return d.toLocaleDateString('vi-VN', { day: '2-digit', month: '2-digit', year: 'numeric' });
    }

    function loaiPhieuLabel(loai) {
        const map = {
            'nhap': '<span class="badge bg-success">Nhập</span>',
            'xuat': '<span class="badge bg-danger">Xuất</span>',
            'ban': '<span class="badge bg-primary">Bán</span>',
            'tra': '<span class="badge bg-warning text-dark">Trả</span>',
        };
        return map[loai?.toLowerCase()] || `<span class="badge bg-secondary">${loai || '-'}</span>`;
    }

    function renderDrawerContent(data) {
        const sp = data.sanPham;
        const theKho = data.theKho || [];
        const loHang = data.loHang || [];

        const trangThaiLabel = !sp.trang_thai
            ? '<span class="badge bg-danger">Ngừng bán</span>'
            : (sp.so_luong_ton_kho <= 0
                ? '<span class="badge bg-secondary">Hết hàng</span>'
                : (sp.so_luong_ton_kho <= (sp.dinh_muc_toi_thieu || 0)
                    ? '<span class="badge bg-warning text-dark">Sắp hết hàng</span>'
                    : '<span class="badge bg-success">Còn hàng</span>'));

        const thuocTinhLabels = (sp.thuoc_tinhs || []).map(tt =>
            `<span class="badge bg-info text-dark me-1">${tt.ten_thuoc_tinh}</span>`
        ).join('');

        const bienTheHtml = (() => {
            const bt = data.bienThe || [];
            if (bt.length === 0) return '';
            return `
            <div class="mb-3">
                <h6 class="fw-bold mb-2"><i class="fas fa-layer-group me-1"></i>Biến thể <span class="fw-normal text-muted small">(${bt.length})</span></h6>
                <table class="table table-sm table-bordered mb-0" style="font-size:0.82rem;">
                    <thead class="table-light">
                        <tr>
                            <th>Ảnh</th>
                            <th>Thuộc tính</th>
                            <th>Mã vạch</th>
                            <th class="text-end">Giá bán</th>
                            <th class="text-end">Tồn kho</th>
                            <th>Trạng thái</th>
                            <th class="text-center">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${bt.map(v => `
                        <tr>
                            <td>
                                ${v.hinh_anh
                                    ? `<img src="/${v.hinh_anh}" style="width:32px;height:32px;object-fit:cover;border-radius:4px;">`
                                    : `<div style="width:32px;height:32px;border-radius:4px;background:#eee;display:flex;align-items:center;justify-content:center;"><i class="fas fa-image text-muted" style="font-size:0.6rem;"></i></div>`
                                }
                            </td>
                            <td>
                                ${(v.thuoc_tinhs || []).length > 0
                                    ? v.thuoc_tinhs.map(tt => `<span class="badge bg-light text-dark border me-1">${tt.ten_thuoc_tinh}</span>`).join('')
                                    : '<em class="text-muted">—</em>'
                                }
                            </td>
                            <td class="small">${v.ma_vach || '-'}</td>
                            <td class="text-end fw-bold text-primary">${formatMoney(v.gia_ban)} đ</td>
                            <td class="text-end ${(v.so_luong_ton_kho ?? 0) <= (v.dinh_muc_toi_thieu || 0) ? 'text-warning' : 'text-muted'}">${v.so_luong_ton_kho ?? 0}</td>
                            <td>
                                ${!v.trang_thai
                                    ? '<span class="badge bg-danger">Ngừng</span>'
                                    : (v.so_luong_ton_kho <= 0
                                        ? '<span class="badge bg-secondary">Hết</span>'
                                        : '<span class="badge bg-success">Còn</span>'
                                    )
                                }
                            </td>
                            <td class="text-center">
                                <button class="btn btn-sm btn-outline-danger" onclick="deleteVariant(${v.id}, ${sp.id})" title="Xóa biến thể">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>`).join('')}
                    </tbody>
                </table>
            </div>`;
        })();

        const hinhAnh = sp.hinh_anh
            ? `<img src="/${sp.hinh_anh}" class="img-fluid rounded" alt="${sp.ten_san_pham}" style="max-height:220px; object-fit:contain; background:#f8f9fa;">`
            : `<div class="text-center text-muted py-5 bg-light rounded"><i class="fas fa-image fa-3x"></i><p class="mt-2 mb-0">Không có ảnh</p></div>`;

        const galleryHtml = '';

        // Thẻ kho
        let theKhoHtml = '';
        if (theKho.length > 0) {
            theKhoHtml = `
            <div class="table-scroll-wrap">
                <table class="table table-sm table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Mã phiếu</th>
                            <th>Thời gian</th>
                            <th>Loại</th>
                            <th>Lô</th>
                            <th>Giá nhập</th>
                            <th>SL</th>
                            <th>SL còn lại</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${theKho.map(item => `
                        <tr>
                            <td class="small">${item.maPhieu || '-'}</td>
                            <td class="small">${formatDate(item.thoiGian)}</td>
                            <td>${loaiPhieuLabel(item.loaiPhieu)}</td>
                            <td class="small">${item.maLo || '-'}</td>
                            <td class="small text-end">${formatMoney(item.gia)} đ</td>
                            <td class="small text-center">${item.soLuong}</td>
                            <td class="small text-center">${item.soLuongConLai ?? '-'}</td>
                        </tr>`).join('')}
                    </tbody>
                </table>
            </div>`;
        } else {
            theKhoHtml = `<p class="text-muted text-center py-3 mb-0 small">Chưa có dữ liệu thẻ kho.</p>`;
        }

        // Lô hàng
        let loHangHtml = '';
        if (loHang.length > 0) {
            loHangHtml = `
            <table class="table table-sm table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Số lô</th>
                        <th>Hạn sử dụng</th>
                        <th class="text-end">SL nhập</th>
                        <th class="text-end">SL còn lại</th>
                    </tr>
                </thead>
                <tbody>
                    ${loHang.map(item => {
                        const isExpired = item.hanSuDung && new Date(item.hanSuDung) < new Date();
                        return `<tr>
                            <td class="small">${item.maLo || '-'}</td>
                            <td class="small ${isExpired ? 'text-danger' : ''}">${formatDate(item.hanSuDung)} ${isExpired ? '<i class="fas fa-exclamation-circle"></i>' : ''}</td>
                            <td class="small text-end">${item.so_luong ?? '-'}</td>
                            <td class="small text-end">${item.soLuongConLai ?? '-'}</td>
                        </tr>`;
                    }).join('')}
                </tbody>
            </table>`;
        } else {
            loHangHtml = `<p class="text-muted text-center py-3 mb-0 small">Chưa có lô hàng.</p>`;
        }

        drawerBody.innerHTML = `
        <div class="p-3">
            <div class="row g-3 mb-3">
                <div class="col-4">
                    ${hinhAnh}
                </div>
                <div class="col-8">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h5 class="fw-bold mb-1">${sp.ten_san_pham}</h5>
                            <p class="text-muted small mb-1">#${sp.ma_hang || sp.ma_vach || sp.id}</p>
                            ${trangThaiLabel}
                        </div>
                        <div class="text-end">
                            <p class="fw-bold text-primary mb-0" style="font-size:1.4rem;">${formatMoney(sp.gia_ban)} đ</p>
                            <p class="text-muted small mb-0">Giá vốn: ${formatMoney(sp.gia_von)} đ</p>
                        </div>
                    </div>
                    <hr>
                    <div class="row g-2 small">
                        <div class="col-6"><strong>Danh mục:</strong> ${sp.danh_muc?.ten_danh_muc || '-'}</div>
                        <div class="col-6"><strong>Thương hiệu:</strong> ${sp.thuong_hieu || '-'}</div>
                        <div class="col-6"><strong>Đơn vị:</strong> ${sp.don_vi?.ten_don_vi || '-'}</div>
                        <div class="col-6"><strong>Mã vạch:</strong> ${sp.ma_vach || '-'}</div>
                        <div class="col-6"><strong>Tồn kho:</strong> ${sp.so_luong_ton_kho ?? 0}</div>
                        <div class="col-6"><strong>Định mức:</strong> ${sp.dinh_muc_toi_thieu ?? 0}</div>
                    </div>
                    ${thuocTinhLabels ? `<div class="mt-2">${thuocTinhLabels}</div>` : ''}
                </div>
            </div>

            ${sp.mo_ta ? `
            <div class="mb-3">
                <h6 class="fw-bold mb-2"><i class="fas fa-align-left me-1"></i>Mô tả</h6>
                <div class="bg-light rounded p-2 small text-muted" style="white-space:pre-line;">${sp.mo_ta}</div>
            </div>` : ''}

            ${bienTheHtml}

            <div class="mb-3">
                <h6 class="fw-bold mb-2"><i class="fas fa-history me-1"></i>Thẻ kho <span class="fw-normal text-muted small">(${theKho.length})</span></h6>
                ${theKhoHtml}
            </div>

            <div class="mb-3">
                <h6 class="fw-bold mb-2"><i class="fas fa-boxes-stacked me-1"></i>Lô - Hạn sử dụng <span class="fw-normal text-muted small">(${loHang.length})</span></h6>
                ${loHangHtml}
            </div>

            <div class="text-muted small border-top pt-2">
                <i class="fas fa-clock me-1"></i>Tạo: ${formatDate(sp.created_at)} | Cập nhật: ${formatDate(sp.updated_at)}
            </div>
        </div>`;
    }
})();

// ---- Export Excel ----
(function() {
    const btn = document.getElementById('btnExportExcel');
    if (!btn) return;
    btn.addEventListener('click', function() {
        const params = new URLSearchParams(window.location.search);
        window.location.href = '{{ url('admin/san-pham/export') }}?' + params.toString();
    });
})();

// ---- Import Preview ----
(function() {
    const input = document.getElementById('importFileInput');
    if (!input) return;
    input.addEventListener('change', function() {
        const file = this.files[0];
        if (!file) return;

        const previewSection = document.getElementById('importPreviewSection');
        const previewTable = document.getElementById('importPreviewTable');
        if (!previewTable) return;
        const thead = previewTable.querySelector('thead');
        const tbody = previewTable.querySelector('tbody');

        const reader = new FileReader();
        reader.onload = function(e) {
            try {
                const data = new Uint8Array(e.target.result);
                const workbook = XLSX.read(data, { type: 'array' });
                const firstSheet = workbook.Sheets[workbook.SheetNames[0]];
                const jsonData = XLSX.utils.sheet_to_json(firstSheet, { header: 1 });

                if (jsonData.length < 2) {
                    alert('File không có dữ liệu hoặc chỉ có header.');
                    return;
                }

                const headers = jsonData[0];
                const rows = jsonData.slice(1, 6);

                thead.innerHTML = '<tr>' + headers.map(h => `<th>${h || ''}</th>`).join('') + '</tr>';
                tbody.innerHTML = rows.map(row => {
                    return '<tr>' + headers.map((_, i) => `<td>${row[i] !== undefined ? row[i] : ''}</td>`).join('') + '</tr>';
                }).join('');

                previewSection.classList.remove('d-none');
            } catch(err) {
                alert('Không thể đọc file. Vui lòng đảm bảo file đúng định dạng.');
                console.error(err);
            }
        };
        reader.readAsArrayBuffer(file);
    });
})();

// ---- Delete Variant (global, accessible from HTML) ----
window.deleteVariant = async function(variantId, productId) {
    if (!confirm('Bạn có chắc muốn xóa biến thể này?')) return;
    try {
        const res = await fetch(`/admin/api/san-pham/variant/${variantId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content,
                'Accept': 'application/json',
            },
        });
        const json = await res.json();
        if (json.success) {
            window.openProductDrawer(productId);
        } else {
            alert(json.message || 'Không thể xóa biến thể.');
        }
    } catch(e) {
        alert('Lỗi: ' + e.message);
    }
};
</script>
@endsection
