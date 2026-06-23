@extends('admin_xem_truoc.layouts.admin')

@section('title', 'Quản lý Kho hàng - SmartMart')

@section('content')
{{-- Page Header --}}
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1"><i class="fas fa-warehouse me-2 text-primary"></i>Quản lý Kho hàng</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('admin/dashboard') }}">Admin</a></li>
                <li class="breadcrumb-item active">Kho hàng</li>
            </ol>
        </nav>
    </div>
</div>

{{-- Stats Row --}}
<div class="row g-3 mb-4" id="stats-row">
    <div class="col-xl-3 col-md-6">
        <div class="card card-stat blue h-100">
            <div class="card-body d-flex align-items-center">
                <div class="icon me-3"><i class="fas fa-layer-group"></i></div>
                <div>
                    <h6 class="text-muted mb-1">Tổng lô hàng</h6>
                    <h3 class="mb-0" id="stat-tong-lo">--</h3>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card card-stat green h-100">
            <div class="card-body d-flex align-items-center">
                <div class="icon me-3"><i class="fas fa-check-circle"></i></div>
                <div>
                    <h6 class="text-muted mb-1">Lô còn hàng</h6>
                    <h3 class="mb-0" id="stat-lo-con-hang">--</h3>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card card-stat orange h-100">
            <div class="card-body d-flex align-items-center">
                <div class="icon me-3"><i class="fas fa-clock"></i></div>
                <div>
                    <h6 class="text-muted mb-1">Sắp hết HSD (&lt;30 ngày)</h6>
                    <h3 class="mb-0" id="stat-sap-het-hsd">--</h3>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card card-stat red h-100">
            <div class="card-body d-flex align-items-center">
                <div class="icon me-3"><i class="fas fa-exclamation-triangle"></i></div>
                <div>
                    <h6 class="text-muted mb-1">Dưới định mức</h6>
                    <h3 class="mb-0" id="stat-duoi-dinh-muc">--</h3>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Alert container --}}
<div id="kh-alert-container"></div>

{{-- Tabs Navigation --}}
<ul class="nav nav-tabs mb-3" id="kho-hang-tabs" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active" id="tab-ton-kho" data-bs-toggle="tab" data-bs-target="#panel-ton-kho" type="button" role="tab">
            <i class="fas fa-boxes-stacked me-1"></i>Tồn kho
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="tab-lo-hang" data-bs-toggle="tab" data-bs-target="#panel-lo-hang" type="button" role="tab">
            <i class="fas fa-layer-group me-1"></i>Lô hàng
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="tab-phieu-nhap" data-bs-toggle="tab" data-bs-target="#panel-phieu-nhap" type="button" role="tab">
            <i class="fas fa-arrow-down me-1"></i>Phiếu nhập
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="tab-phieu-xuat" data-bs-toggle="tab" data-bs-target="#panel-phieu-xuat" type="button" role="tab">
            <i class="fas fa-arrow-up me-1"></i>Phiếu xuất
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="tab-canh-bao" data-bs-toggle="tab" data-bs-target="#panel-canh-bao" type="button" role="tab">
            <i class="fas fa-triangle-exclamation me-1"></i>Cảnh báo
            <span class="badge bg-danger ms-1" id="badge-canh-bao" style="display:none">0</span>
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="tab-ncc" data-bs-toggle="tab" data-bs-target="#panel-ncc" type="button" role="tab">
            <i class="fas fa-truck me-1"></i>Nhà cung cấp
        </button>
    </li>
</ul>

{{-- Tab Content --}}
<div class="tab-content" id="kho-hang-tab-content">

    {{-- =========================== TAB 1: TỒN KHO =========================== --}}
    <div class="tab-pane fade show active" id="panel-ton-kho" role="tabpanel">
        {{-- Filter --}}
        <div class="card table-admin mb-3">
            <div class="card-body py-2">
                <div class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label small text-muted mb-1">Tìm sản phẩm</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                            <input type="text" class="form-control" id="tk-filter-q" placeholder="Tên, mã vạch...">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small text-muted mb-1">Trạng thái</label>
                        <select class="form-select form-select-sm" id="tk-filter-status">
                            <option value="">Tất cả</option>
                            <option value="binh-thuong">Bình thường</option>
                            <option value="duoi-dinh-muc">Dưới định mức</option>
                            <option value="het-hang">Hết hàng</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button class="btn btn-primary btn-sm w-100" id="tk-btn-loc">
                            <i class="fas fa-filter me-1"></i>Lọc
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Table --}}
        <div class="card table-admin">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th style="width:40px"></th>
                                <th>Mã SP</th>
                                <th>Tên sản phẩm</th>
                                <th class="text-center">Tồn kho</th>
                                <th class="text-center">Định mức</th>
                                <th class="text-center">Trạng thái</th>
                                <th class="text-center" style="width:100px">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody id="tk-bang">
                            <tr><td colspan="7" class="text-center text-muted py-4">Đang tải...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer bg-white">
                <div class="d-flex justify-content-between align-items-center">
                    <span class="text-muted small" id="tk-thong-tin"></span>
                    <nav id="tk-phan-trang"></nav>
                </div>
            </div>
        </div>
    </div>

    {{-- =========================== TAB 2: LÔ HÀNG =========================== --}}
    <div class="tab-pane fade" id="panel-lo-hang" role="tabpanel">
        <div class="d-flex justify-content-end mb-3">
            <button class="btn btn-success btn-sm" id="lh-btn-tao">
                <i class="fas fa-plus me-1"></i>Tạo lô hàng
            </button>
        </div>
        <div class="card table-admin mb-3">
            <div class="card-body py-2">
                <div class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label small text-muted mb-1">Tìm kiếm</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                            <input type="text" class="form-control" id="lh-filter-q" placeholder="Mã lô, ghi chú...">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small text-muted mb-1">Nhà cung cấp</label>
                        <select class="form-select form-select-sm" id="lh-filter-ncc">
                            <option value="">Tất cả</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button class="btn btn-primary btn-sm w-100" id="lh-btn-loc">
                            <i class="fas fa-filter me-1"></i>Lọc
                        </button>
                        <a href="{{ url('admin/kho-hang/lo-hang') }}" class="btn btn-light btn-sm w-100 mt-1">
                            <i class="fas fa-external-link-alt me-1"></i>Trang riêng
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <div class="card table-admin">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Mã lô</th>
                                <th>NCC</th>
                                <th>Ngày nhập</th>
                                <th class="text-center">Tổng nhập</th>
                                <th class="text-center">Tổng tồn</th>
                                <th class="text-center">HSD sớm nhất</th>
                                <th class="text-center">Trạng thái</th>
                                <th class="text-center" style="width:100px">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody id="lh-bang">
                            <tr><td colspan="9" class="text-center text-muted py-4">Đang tải...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer bg-white">
                <div class="d-flex justify-content-between align-items-center">
                    <span class="text-muted small" id="lh-thong-tin"></span>
                    <nav id="lh-phan-trang"></nav>
                </div>
            </div>
        </div>
    </div>

    {{-- =========================== TAB 3: PHIẾU NHẬP =========================== --}}
    <div class="tab-pane fade" id="panel-phieu-nhap" role="tabpanel">
        <div class="d-flex justify-content-end mb-3 gap-2">
            <a href="{{ url('admin/kho-hang/phieu-nhap') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-external-link-alt me-1"></i>Trang riêng
            </a>
            <button class="btn btn-success btn-sm" id="pn-btn-tao">
                <i class="fas fa-plus me-1"></i>Tạo phiếu nhập
            </button>
        </div>
        <div class="card table-admin mb-3">
            <div class="card-body py-2">
                <div class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label small text-muted mb-1">Loại</label>
                        <select class="form-select form-select-sm" id="pn-filter-loai">
                            <option value="">Tất cả loại</option>
                            <option value="mua_hang">Nhập mua hàng</option>
                            <option value="tra_lai_tu_khach">Trả lại từ khách</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small text-muted mb-1">Từ ngày</label>
                        <input type="date" class="form-control form-control-sm" id="pn-filter-tu">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small text-muted mb-1">Đến ngày</label>
                        <input type="date" class="form-control form-control-sm" id="pn-filter-den">
                    </div>
                    <div class="col-md-2">
                        <button class="btn btn-primary btn-sm w-100" id="pn-btn-loc">
                            <i class="fas fa-filter me-1"></i>Lọc
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div class="card table-admin">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Mã phiếu</th>
                                <th>Loại</th>
                                <th>Nhà cung cấp</th>
                                <th>Người tạo</th>
                                <th class="text-center">Ngày tạo</th>
                                <th class="text-center">Tổng giá trị</th>
                                <th>Ghi chú</th>
                                <th class="text-center" style="width:110px">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody id="pn-bang">
                            <tr><td colspan="9" class="text-center text-muted py-4">Đang tải...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer bg-white">
                <div class="d-flex justify-content-between align-items-center">
                    <span class="text-muted small" id="pn-thong-tin"></span>
                    <nav id="pn-phan-trang"></nav>
                </div>
            </div>
        </div>
    </div>

    {{-- =========================== TAB 4: PHIẾU XUẤT =========================== --}}
    <div class="tab-pane fade" id="panel-phieu-xuat" role="tabpanel">
        <div class="d-flex justify-content-end mb-3 gap-2">
            <a href="{{ url('admin/kho-hang/phieu-xuat') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-external-link-alt me-1"></i>Trang riêng
            </a>
            <button class="btn btn-danger btn-sm" id="px-btn-tao">
                <i class="fas fa-plus me-1"></i>Tạo phiếu xuất
            </button>
        </div>
        <div class="card table-admin mb-3">
            <div class="card-body py-2">
                <div class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label small text-muted mb-1">Loại</label>
                        <select class="form-select form-select-sm" id="px-filter-loai">
                            <option value="">Tất cả loại</option>
                            <option value="tra_hang_nha_cung_cap">Trả hàng NCC</option>
                            <option value="tieu_huy">Tiêu hủy</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small text-muted mb-1">Từ ngày</label>
                        <input type="date" class="form-control form-control-sm" id="px-filter-tu">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small text-muted mb-1">Đến ngày</label>
                        <input type="date" class="form-control form-control-sm" id="px-filter-den">
                    </div>
                    <div class="col-md-2">
                        <button class="btn btn-primary btn-sm w-100" id="px-btn-loc">
                            <i class="fas fa-filter me-1"></i>Lọc
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div class="card table-admin">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Mã phiếu</th>
                                <th>Loại</th>
                                <th>NCC</th>
                                <th>Lý do</th>
                                <th>Người tạo</th>
                                <th class="text-center">Ngày tạo</th>
                                <th class="text-center">Tổng SL</th>
                                <th class="text-center" style="width:110px">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody id="px-bang">
                            <tr><td colspan="9" class="text-center text-muted py-4">Đang tải...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer bg-white">
                <div class="d-flex justify-content-between align-items-center">
                    <span class="text-muted small" id="px-thong-tin"></span>
                    <nav id="px-phan-trang"></nav>
                </div>
            </div>
        </div>
    </div>

    {{-- =========================== TAB 5: CẢNH BÁO =========================== --}}
    <div class="tab-pane fade" id="panel-canh-bao" role="tabpanel">
        <div class="row g-4" id="canh-bao-content">
            <div class="text-center text-muted py-5 col-12">Đang tải cảnh báo...</div>
        </div>
    </div>

    {{-- =========================== TAB 6: NHÀ CUNG CẤP =========================== --}}
    <div class="tab-pane fade" id="panel-ncc" role="tabpanel">

        {{-- Header & Actions --}}
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div class="input-group" style="max-width: 400px;">
                <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                <input type="text" class="form-control" id="ncc-search" placeholder="Tìm tên, email, SĐT...">
                <button class="btn btn-primary" id="ncc-btn-search"><i class="fas fa-search"></i></button>
                <button class="btn btn-light border" id="ncc-btn-refresh"><i class="fas fa-sync-alt"></i></button>
            </div>
            <div>
                <a href="{{ url('/admin/kho-hang/nha-cung-cap/thung-rac') }}" class="btn btn-outline-danger btn-sm me-2" target="_blank">
                    <i class="fas fa-trash-alt me-1"></i>Thùng rác
                </a>
                <button class="btn btn-primary btn-sm" id="ncc-btn-them">
                    <i class="fas fa-plus me-1"></i>Thêm NCC
                </button>
            </div>
        </div>

        {{-- Alert container --}}
        <div id="ncc-alert-container"></div>

        {{-- Table --}}
        <div class="card table-admin">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Tên NCC</th>
                                <th>Email</th>
                                <th>SĐT</th>
                                <th>Người đại diện</th>
                                <th>Ngày tạo</th>
                                <th style="width: 130px;">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody id="ncc-tbody">
                            <tr><td colspan="7" class="text-center text-muted py-4">Đang tải...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer bg-white">
                <div class="d-flex justify-content-between align-items-center">
                    <span class="text-muted small" id="ncc-thong-tin"></span>
                    <nav id="ncc-phan-trang"></nav>
                </div>
            </div>
        </div>
    </div>

</div>{{-- end tab-content --}}

{{-- ===================== MODAL: TẠO PHIẾU NHẬP ===================== --}}
<div class="modal fade" id="modal-tao-pn" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <form id="form-tao-pn">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title"><i class="fas fa-arrow-down me-2"></i>Tạo phiếu nhập</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label">Loại nhập <span class="text-danger">*</span></label>
                            <select name="loai_nhap" class="form-select" id="pn-loai" required>
                                <option value="mua_hang">Nhập mua hàng</option>
                                <option value="tra_lai_tu_khach">Trả lại từ khách</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Nhà cung cấp</label>
                            <select name="id_nha_cung_cap" id="pn-ncc" class="form-select">
                                <option value="">-- Chọn NCC --</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Ghi chú</label>
                            <textarea name="ghi_chu" class="form-control" rows="2" id="pn-ghi-chu" placeholder="Ghi chú..."></textarea>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="mb-0 small">Chi tiết sản phẩm</h6>
                    </div>

                    {{-- Product Search Panel --}}
                    <div class="card mb-3" id="pn-product-search-panel">
                        <div class="card-header bg-light py-2">
                            <div class="row g-2 align-items-end">
                                <div class="col-md-6">
                                    <label class="form-label small mb-1">Tìm sản phẩm (tên / mã vạch)</label>
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text bg-white"><i class="fas fa-search"></i></span>
                                        <input type="text" class="form-control" id="pn-sp-search" placeholder="Nhập tên hoặc mã vạch..." autocomplete="off">
                                        <button class="btn btn-outline-secondary" type="button" id="pn-sp-clear"><i class="fas fa-times"></i></button>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small mb-1">Danh mục</label>
                                    <select class="form-select form-select-sm" id="pn-sp-danh-muc">
                                        <option value="">Tất cả danh mục</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="card-body p-0" style="max-height: 280px; overflow-y: auto;" id="pn-sp-results">
                            <div class="text-center text-muted py-4">
                                <i class="fas fa-search fs-4 mb-2 d-block"></i>
                                Nhập tên hoặc mã vạch để tìm sản phẩm
                            </div>
                        </div>
                    </div>

                    {{-- Selected items table --}}
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="mb-0 small">Sản phẩm đã chọn</h6>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="width:35%">Sản phẩm</th>
                                    <th style="width:90px">SL nhập</th>
                                    <th style="width:110px">Giá nhập</th>
                                    <th style="width:130px">Hạn sử dụng</th>
                                    <th style="width:40px"></th>
                                </tr>
                            </thead>
                            <tbody id="pn-ds-sp">
                                <tr id="pn-empty-row"><td colspan="5" class="text-center text-muted py-3">Chưa chọn sản phẩm nào.</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-success"><i class="fas fa-save me-1"></i>Lưu phiếu nhập</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ===================== MODAL: TẠO PHIẾU XUẤT ===================== --}}
<div class="modal fade" id="modal-tao-px" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <form id="form-tao-px">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title"><i class="fas fa-arrow-up me-2"></i>Tạo phiếu xuất</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning mb-3 py-2 small">
                        <i class="fas fa-info-circle me-1"></i>
                        Hệ thống sẽ tự động xuất hàng theo nguyên tắc <strong>FEFO</strong> — ưu tiên lô có HSD gần nhất trước.
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label">Loại xuất <span class="text-danger">*</span></label>
                            <select name="loai_xuat" class="form-select" id="px-loai" required>
                                <option value="tra_hang_nha_cung_cap">Trả hàng NCC</option>
                                <option value="tieu_huy">Tiêu hủy</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Nhà cung cấp</label>
                            <select name="id_nha_cung_cap" id="px-ncc" class="form-select">
                                <option value="">-- Chọn NCC --</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Lý do</label>
                            <input type="text" name="ly_do" class="form-control" id="px-ly-do" placeholder="Lý do xuất hàng...">
                        </div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="mb-0 small">Chi tiết sản phẩm</h6>
                        <button type="button" class="btn btn-sm btn-outline-primary" id="px-btn-them-sp">
                            <i class="fas fa-plus me-1"></i>Thêm sản phẩm
                        </button>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered mb-2">
                            <thead class="table-light">
                                <tr>
                                    <th>Sản phẩm</th>
                                    <th class="text-center" style="width:100px">Tồn kho</th>
                                    <th class="text-center" style="width:90px">SL xuất</th>
                                    <th style="width:40px"></th>
                                </tr>
                            </thead>
                            <tbody id="px-ds-sp"></tbody>
                        </table>
                    </div>
                    <div id="px-fefo-preview"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-danger"><i class="fas fa-save me-1"></i>Lưu phiếu xuất</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ===================== MODAL: TẠO LÔ HÀNG ===================== --}}
<div class="modal fade" id="modal-tao-lo" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <form id="form-tao-lo">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="fas fa-layer-group me-2"></i>Tạo lô hàng mới</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label">Nhà cung cấp</label>
                            <select name="id_nha_cung_cap" id="lo-ncc" class="form-select">
                                <option value="">-- Chọn NCC --</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Mã lô (tùy chọn)</label>
                            <input type="text" name="ma_lo" class="form-control" id="lo-ma" placeholder="Tự động nếu để trống">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Ngày nhập <span class="text-danger">*</span></label>
                            <input type="date" name="ngay_nhap" class="form-control" id="lo-ngay" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Ghi chú</label>
                            <textarea name="ghi_chu" class="form-control" rows="2" id="lo-ghi-chu" placeholder="Ghi chú lô hàng..."></textarea>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="mb-0 small">Chi tiết sản phẩm trong lô</h6>
                        <button type="button" class="btn btn-sm btn-outline-primary" id="lo-btn-them-sp">
                            <i class="fas fa-plus me-1"></i>Thêm sản phẩm
                        </button>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="width:35%">Sản phẩm</th>
                                    <th style="width:90px">SL nhập</th>
                                    <th style="width:110px">Giá nhập</th>
                                    <th style="width:130px">Hạn sử dụng</th>
                                    <th style="width:40px"></th>
                                </tr>
                            </thead>
                            <tbody id="lo-ds-sp"></tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Lưu lô hàng</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ===================== MODAL: XEM CHI TIẾT PHIẾU NHẬP ===================== --}}
<div class="modal fade" id="modal-xem-pn" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-file-alt me-2 text-success"></i>Chi tiết phiếu nhập</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="modal-xem-pn-body"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
            </div>
        </div>
    </div>
</div>

{{-- ===================== MODAL: XEM CHI TIẾT PHIẾU XUẤT ===================== --}}
<div class="modal fade" id="modal-xem-px" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-file-alt me-2 text-danger"></i>Chi tiết phiếu xuất</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="modal-xem-px-body"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
            </div>
        </div>
    </div>
</div>

{{-- ===================== MODAL: THÊM NHÀ CUNG CẤP ===================== --}}
<div class="modal fade" id="modal-them-ncc" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="form-them-ncc" autocomplete="off">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-plus me-2"></i>Thêm nhà cung cấp</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Tên nhà cung cấp <span class="text-danger">*</span></label>
                        <input type="text" name="ten_nha_cung_cap" id="ncc_them_ten" class="form-control" required>
                        <div class="invalid-feedback" id="ncc_them_ten_err"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" id="ncc_them_email" class="form-control">
                        <div class="invalid-feedback" id="ncc_them_email_err"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Số điện thoại <span class="text-danger">*</span></label>
                        <input type="text" name="so_dien_thoai" id="ncc_them_sdt" class="form-control" required>
                        <div class="invalid-feedback" id="ncc_them_sdt_err"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Người đại diện</label>
                        <input type="text" name="nguoi_dai_dien" id="ncc_them_nguoi" class="form-control">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary" id="ncc_btn_luu"><i class="fas fa-save me-1"></i>Lưu</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ===================== MODAL: SỬA NHÀ CUNG CẤP ===================== --}}
<div class="modal fade" id="modal-sua-ncc" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="form-sua-ncc" autocomplete="off">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-edit me-2"></i>Sửa nhà cung cấp</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="ncc_sua_id">
                    <div class="mb-3">
                        <label class="form-label">Tên nhà cung cấp <span class="text-danger">*</span></label>
                        <input type="text" name="ten_nha_cung_cap" id="ncc_sua_ten" class="form-control" required>
                        <div class="invalid-feedback" id="ncc_sua_ten_err"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" id="ncc_sua_email" class="form-control">
                        <div class="invalid-feedback" id="ncc_sua_email_err"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Số điện thoại <span class="text-danger">*</span></label>
                        <input type="text" name="so_dien_thoai" id="ncc_sua_sdt" class="form-control" required>
                        <div class="invalid-feedback" id="ncc_sua_sdt_err"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Người đại diện</label>
                        <input type="text" name="nguoi_dai_dien" id="ncc_sua_nguoi" class="form-control">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary" id="ncc_btn_cap_nhat"><i class="fas fa-save me-1"></i>Cập nhật</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ===================== MODAL: XEM CHI TIẾT LÔ HÀNG ===================== --}}
<div class="modal fade" id="modal-xem-lo" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-layer-group me-2 text-primary"></i>Chi tiết lô hàng</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="modal-xem-lo-body"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
let sanPhamAll = [];
let pnIdx = 0, pxIdx = 0, loIdx = 0;
let tkPage = 1, lhPage = 1, pnPage = 1, pxPage = 1;
let selectedPnProducts = new Set();

// ─── INIT ───────────────────────────────────────────────
$(function () {
    loadStats();
    loadTonKho();
    loadLoHang();
    loadPhieuNhap();
    loadPhieuXuat();
    loadNhaCungCap(1);
    loadNccDropdown();
    loadSanPhamAll();

    // Modal events
    $('#modal-tao-pn').on('shown.bs.modal', function () {
        loadDanhMucNhap();
        $('#pn-sp-search').focus();
    });
    $('#modal-tao-pn').on('hidden.bs.modal', function () {
        selectedPnProducts.clear();
        pnIdx = 0;
        $('#pn-ds-sp').html('<tr id="pn-empty-row"><td colspan="5" class="text-center text-muted py-3">Chưa chọn sản phẩm nào.</td></tr>');
        $('#pn-sp-results').html(`<div class="text-center text-muted py-4"><i class="fas fa-search fs-4 mb-2 d-block"></i>Nhập tên hoặc mã vạch để tìm sản phẩm</div>`);
        $('#pn-sp-search').val('');
        $('#pn-sp-danh-muc').val('');
    });

    // Modal triggers
    $('#pn-btn-tao').click(() => new bootstrap.Modal(document.getElementById('modal-tao-pn')).show());
    $('#px-btn-tao').click(() => { pxIdx = 0; $('#px-ds-sp').html(''); $('#form-tao-px')[0].reset(); new bootstrap.Modal(document.getElementById('modal-tao-px')).show(); });
    $('#lh-btn-tao').click(() => { loIdx = 0; $('#lo-ds-sp').html(''); $('#form-tao-lo')[0].reset(); new bootstrap.Modal(document.getElementById('modal-tao-lo')).show(); });

    // Tab events
    $('#kho-hang-tabs button[data-bs-toggle="tab"]').on('shown.bs.tab', function () {
        const id = $(this).attr('id');
        if (id === 'tab-lo-hang') loadLoHang(lhPage);
        if (id === 'tab-phieu-nhap') loadPhieuNhap(pnPage);
        if (id === 'tab-phieu-xuat') loadPhieuXuat(pxPage);
        if (id === 'tab-canh-bao') loadCanhBao();
    });

    // Filter buttons
    $('#tk-btn-loc').click(() => loadTonKho(1));
    $('#lh-btn-loc').click(() => loadLoHang(1));
    $('#pn-btn-loc').click(() => loadPhieuNhap(1));
    $('#px-btn-loc').click(() => loadPhieuXuat(1));

    // Enter key filters
    $('#tk-filter-q').on('keypress', e => { if (e.which === 13) loadTonKho(1); });
    $('#lh-filter-q').on('keypress', e => { if (e.which === 13) loadLoHang(1); });

    $('#px-btn-them-sp').click(() => addPxRow());
    $('#lo-btn-them-sp').click(() => addLoRow());

    // PX product change → show FEFO preview
    $(document).on('change', '.px-sp-select', function () {
        updateFefoPreview();
    });
    $(document).on('input', '.px-sl-input', updateFefoPreview);

    // Submit forms
    $('#form-tao-pn').submit(e => { e.preventDefault(); submitPhieuNhap(); });
    $('#form-tao-px').submit(e => { e.preventDefault(); submitPhieuXuat(); });
    $('#form-tao-lo').submit(e => { e.preventDefault(); submitLoHang(); });
});

// ─── LOADERS ─────────────────────────────────────────────
function loadStats() {
    $.get('/admin/api/lo-hang/thong-ke', res => {
        if (!res.success) return;
        $('#stat-tong-lo').text(res.data.tong_lo);
        $('#stat-lo-con-hang').text(res.data.lo_con_hang);
        $('#stat-sap-het-hsd').text(res.data.sap_het_hsd);
        $('#stat-duoi-dinh-muc').text(res.data.duoi_dinh_muc);
        const total = (res.data.sap_het_hsd || 0) + (res.data.duoi_dinh_muc || 0);
        if (total > 0) {
            $('#badge-canh-bao').text(total).show();
        }
    });
}

function loadDanhMucNhap() {
    if ($('#pn-sp-danh-muc option').length > 1) return;
    $.get('/admin/api/san-pham', { q: '' }, res => {
        if (!res.danh_muc_list) return;
        const opts = res.danh_muc_list.map(d =>
            `<option value="${d.id}">${d.ten_danh_muc}</option>`
        ).join('');
        $('#pn-sp-danh-muc').append(opts);
    });
}

let nccPage = 1;

function loadNhaCungCap(page = 1, q = '') {
    nccPage = page;
    const searchQ = q !== '' ? q : ($('#ncc-search').val() || '');
    $('#ncc-tbody').html('<tr><td colspan="7" class="text-center text-muted py-4"><i class="fas fa-spinner fa-spin me-1"></i> Đang tải...</td></tr>');
    $.get('/admin/api/nha-cung-cap', { page, q: searchQ }, res => {
        if (!res.success) {
            $('#ncc-tbody').html('<tr><td colspan="7" class="text-center text-danger py-4">Lỗi tải dữ liệu.</td></tr>');
            return;
        }
        const items = res.data.data;
        if (items.length === 0) {
            $('#ncc-tbody').html('<tr><td colspan="7" class="text-center text-muted py-4">Chưa có nhà cung cấp nào.</td></tr>');
        } else {
            const rows = items.map(ncc => `
                <tr>
                    <td>${ncc.id}</td>
                    <td>${ncc.ten_nha_cung_cap}</td>
                    <td>${ncc.email || '<span class="text-muted">—</span>'}</td>
                    <td>${ncc.so_dien_thoai}</td>
                    <td>${ncc.nguoi_dai_dien || '<span class="text-muted">—</span>'}</td>
                    <td>${ncc.created_at ? ncc.created_at.split(' ')[0] : '—'}</td>
                    <td>
                        <button class="btn btn-sm btn-outline-secondary btn-sua-ncc" data-id="${ncc.id}"><i class="fas fa-edit"></i></button>
                        <button class="btn btn-sm btn-outline-danger btn-xoa-ncc" data-id="${ncc.id}" data-ten="${ncc.ten_nha_cung_cap}"><i class="fas fa-trash"></i></button>
                    </td>
                </tr>`).join('');
            $('#ncc-tbody').html(rows);
        }
        const total = res.data.total || 0;
        $('#ncc-thong-tin').text(`Hiển thị ${res.data.data.length} / ${total} nhà cung cấp`);
        renderNccPagination(page, res.data.last_page || 1);
    }).fail(() => {
        $('#ncc-tbody').html('<tr><td colspan="7" class="text-center text-danger py-4">Lỗi kết nối server.</td></tr>');
    });
}

function renderNccPagination(page, lastPage) {
    if (lastPage <= 1) { $('#ncc-phan-trang').html(''); return; }
    let html = '';
    const max = 5;
    let start = Math.max(1, page - 2);
    let end = Math.min(lastPage, start + max - 1);
    if (end - start < max - 1) start = Math.max(1, end - max + 1);
    if (page > 1) html += `<button class="btn btn-sm btn-light border me-1" onclick="loadNhaCungCap(${page - 1})"><i class="fas fa-chevron-left"></i></button>`;
    for (let i = start; i <= end; i++) {
        html += `<button class="btn btn-sm ${i === page ? 'btn-primary' : 'btn-light border'} me-1" onclick="loadNhaCungCap(${i})">${i}</button>`;
    }
    if (page < lastPage) html += `<button class="btn btn-sm btn-light border" onclick="loadNhaCungCap(${page + 1})"><i class="fas fa-chevron-right"></i></button>`;
    $('#ncc-phan-trang').html(html);
}

// Load dropdown NCC cho các tab khác (dùng endpoint riêng)
function loadNccDropdown() {
    $.get('/admin/api/nha-cung-cap/dropdown', res => {
        if (!res.success) return;
        const opts = res.data.map(n => `<option value="${n.id}">${n.ten_nha_cung_cap}</option>`).join('');
        $('#pn-ncc').html('<option value="">-- Chọn NCC --</option>' + opts);
        $('#px-ncc').html('<option value="">-- Chọn NCC --</option>' + opts);
        $('#lo-ncc').html('<option value="">-- Chọn NCC --</option>' + opts);
        $('#lh-filter-ncc').html('<option value="">Tất cả NCC</option>' + opts);
    });
}

// ─── PRODUCT SEARCH (PN Modal) ─────────────────────────────────────

function loadDanhMucNhap() {
    $.get('/admin/api/san-pham', { q: '', danh_muc: '' }, res => {
        if (!res.danh_muc_list) return;
        $('#pn-sp-danh-muc').html('<option value="">Tất cả danh mục</option>' +
            res.danh_muc_list.map(dm => `<option value="${dm.id}">${dm.ten_danh_muc}</option>`).join(''));
    });
}

let pnSearchTimer;
$('#pn-sp-search').on('input', function () {
    clearTimeout(pnSearchTimer);
    pnSearchTimer = setTimeout(() => {
        searchProductsNhap($('#pn-sp-search').val(), $('#pn-sp-danh-muc').val());
    }, 300);
});

$('#pn-sp-danh-muc').on('change', () => {
    searchProductsNhap($('#pn-sp-search').val(), $('#pn-sp-danh-muc').val());
});

$('#pn-sp-clear').click(() => {
    $('#pn-sp-search').val('');
    $('#pn-sp-results').html(`<div class="text-center text-muted py-4"><i class="fas fa-search fs-4 mb-2 d-block"></i>Nhập tên hoặc mã vạch để tìm sản phẩm</div>`);
});

function searchProductsNhap(q, danhMuc) {
    if (!q && !danhMuc) {
        $('#pn-sp-results').html(`<div class="text-center text-muted py-4"><i class="fas fa-search fs-4 mb-2 d-block"></i>Nhập tên hoặc mã vạch để tìm sản phẩm</div>`);
        return;
    }
    $('#pn-sp-results').html(`<div class="text-center py-4"><i class="fas fa-spinner fa-spin text-muted fs-4"></i></div>`);
    $.get('/admin/api/san-pham', { q: q || '', danh_muc: danhMuc || '' }, res => {
        if (!res.success || !res.data.length) {
            $('#pn-sp-results').html(`<div class="text-center text-muted py-4"><i class="fas fa-box-open fs-4 mb-2 d-block text-secondary"></i>Không tìm thấy sản phẩm nào.</div>`);
            return;
        }
        const html = `<table class="table table-sm table-hover mb-0">
            <thead class="table-light"><tr>
                <th style="width:50px"></th>
                <th>Sản phẩm</th>
                <th style="width:110px">Mã vạch</th>
                <th style="width:110px" class="text-end">Giá bán</th>
                <th style="width:80px" class="text-center">Tồn kho</th>
                <th style="width:90px" class="text-center">Chọn</th>
            </tr></thead>
            <tbody>${res.data.map(sp => {
                const img = sp.hinh_anh
                    ? `<img src="/${sp.hinh_anh}" width="38" height="38" class="rounded" style="object-fit:cover" onerror="this.style.display='none';this.nextElementSibling.style.display='flex'">
                       <div class="bg-light rounded d-flex align-items-center justify-content-center" style="width:38px;height:38px;display:none"><i class="fas fa-box text-secondary"></i></div>`
                    : `<div class="bg-light rounded d-flex align-items-center justify-content-center" style="width:38px;height:38px"><i class="fas fa-box text-secondary"></i></div>`;
                const danhMuc = sp.danh_muc?.ten_danh_muc || '';
                const tonKho = sp.chi_tiet_lo_hang_ton_sum_so_luong_ton || 0;
                const tonClass = tonKho === 0 ? 'text-danger' : tonKho < 10 ? 'text-warning' : 'text-success';
                const isSelected = selectedPnProducts.has(sp.id);
                const btnClass = isSelected ? 'btn-secondary' : 'btn-primary';
                const btnIcon = isSelected ? 'fa-check' : 'fa-plus';
                const btnText = isSelected ? 'Đã chọn' : 'Chọn';
                const btnDisabled = isSelected ? 'disabled' : '';
                return `<tr>
                    <td class="text-center align-middle">${img}</td>
                    <td class="align-middle"><div class="fw-semibold small">${sp.ten_san_pham}</div><div class="small text-muted">${danhMuc}</div></td>
                    <td class="align-middle"><code class="small">${sp.ma_vach || '--'}</code></td>
                    <td class="text-end align-middle">${Number(sp.gia_ban || 0).toLocaleString()} đ</td>
                    <td class="text-center align-middle"><span class="fw-semibold ${tonClass}">${tonKho.toLocaleString()}</span></td>
                    <td class="text-center align-middle">
                        <button class="btn btn-sm ${btnClass} btn-chon-sp-nhap" data-id="${sp.id}" data-ten="${sp.ten_san_pham}" data-gia="${sp.gia_ban || 0}" ${btnDisabled}>
                            <i class="fas ${btnIcon}"></i> ${btnText}
                        </button>
                    </td>
                </tr>`;
            }).join('')}</tbody></table>`;
        $('#pn-sp-results').html(html);
    });
}

$(document).on('click', '.btn-chon-sp-nhap', function () {
    const id = $(this).data('id');
    if (selectedPnProducts.has(id)) return;
    selectedPnProducts.add(id);
    const ten = $(this).data('ten');
    const gia = $(this).data('gia');
    const idx = pnIdx++;
    $('#pn-empty-row').remove();
    $('#pn-ds-sp').append(`<tr data-sp-id="${id}">
        <td>
            <div class="fw-semibold small">${ten}</div>
            <input type="hidden" name="chi_tiet[${idx}][id_san_pham]" value="${id}">
        </td>
        <td><input type="number" class="form-control form-control-sm" name="chi_tiet[${idx}][so_luong_nhap]" value="1" min="1"></td>
        <td><input type="number" class="form-control form-control-sm" name="chi_tiet[${idx}][gia_nhap]" value="${gia}" min="0" step="100"></td>
        <td><input type="date" class="form-control form-control-sm" name="chi_tiet[${idx}][han_su_dung]" value=""></td>
        <td><button type="button" class="btn btn-sm btn-outline-danger btn-remove-pn-row" data-id="${id}"><i class="fas fa-times"></i></button></td>
    </tr>`);
    $(this).prop('disabled', true).removeClass('btn-primary').addClass('btn-secondary').html('<i class="fas fa-check"></i> Đã chọn');
});

$(document).on('click', '.btn-remove-pn-row', function () {
    const tr = $(this).closest('tr');
    const spId = parseInt(tr.data('sp-id'));
    selectedPnProducts.delete(spId);
    tr.remove();
    if (!$('#pn-ds-sp tr').length) {
        $('#pn-ds-sp').html('<tr id="pn-empty-row"><td colspan="5" class="text-center text-muted py-3">Chưa chọn sản phẩm nào.</td></tr>');
    }
    $(`.btn-chon-sp-nhap[data-id="${spId}"]`).prop('disabled', false).removeClass('btn-secondary').addClass('btn-primary').html('<i class="fas fa-plus"></i> Chọn');
});

function loadTonKho(page = 1) {
    tkPage = page;
    const q = $('#tk-filter-q').val();
    const status = $('#tk-filter-status').val();
    $.get('/admin/api/lo-hang/ton-kho-tong')
    .done(res => {
        if (!res.success) {
            $('#tk-bang').html('<tr><td colspan="7" class="text-center text-danger py-4">Lỗi tải dữ liệu tồn kho.</td></tr>');
            return;
        }
        let items = res.data;
        if (q) items = items.filter(i => ((i.ten_san_pham || '') + '').toLowerCase().includes(q.toLowerCase()) || ((i.ma_vach || '') + '').toLowerCase().includes(q.toLowerCase()));
        if (status === 'het-hang') items = items.filter(i => !i.tong_ton || i.tong_ton == 0);
        if (status === 'duoi-dinh-muc') items = items.filter(i => i.tong_ton > 0 && i.tong_ton <= (i.dinh_muc_toi_thieu || 0));
        if (status === 'binh-thuong') items = items.filter(i => i.tong_ton > (i.dinh_muc_toi_thieu || 0));

        const perPage = 15, total = items.length;
        const paged = items.slice((page - 1) * perPage, page * perPage);

        if (!paged.length) {
            $('#tk-bang').html('<tr><td colspan="7" class="text-center text-muted py-4">Không có sản phẩm nào.</td></tr>');
            $('#tk-thong-tin').text('Hiển thị 0 sản phẩm');
            $('#tk-phan-trang').html('');
            return;
        }

        const html = paged.map(sp => {
            const tongTon = sp.tong_ton || 0;
            const dinhMuc = sp.dinh_muc_toi_thieu || 0;
            let badge, statusClass;
            if (tongTon === 0) { badge = '<span class="badge bg-secondary">Hết hàng</span>'; statusClass = 'table-secondary'; }
            else if (tongTon <= dinhMuc) { badge = '<span class="badge bg-warning text-dark">Dưới định mức</span>'; statusClass = 'table-warning'; }
            else { badge = '<span class="badge bg-success">Bình thường</span>'; statusClass = ''; }
            return `
            <tr class="clickable-row ${statusClass}" data-id="${sp.id}" style="cursor:pointer">
                <td class="text-center"><i class="fas fa-chevron-down text-muted small"></i></td>
                <td><code>${sp.ma_vach || sp.id}</code></td>
                <td><strong>${sp.ten_san_pham}</strong></td>
                <td class="text-center"><strong>${Number(tongTon).toLocaleString()}</strong></td>
                <td class="text-center text-muted">${Number(dinhMuc).toLocaleString()}</td>
                <td class="text-center">${badge}</td>
                <td class="text-center">
                    <button class="btn btn-sm btn-outline-primary btn-xem-ton" data-id="${sp.id}"><i class="fas fa-eye"></i></button>
                </td>
            </tr>
            <tr class="fefo-detail-row" id="fefo-detail-${sp.id}" style="display:none">
                <td colspan="7" class="p-0">
                    <div class="p-3" id="fefo-detail-content-${sp.id}">
                        <div class="text-center text-muted"><i class="fas fa-spinner fa-spin"></i> Đang tải...</div>
                    </div>
                </td>
            </tr>`;
        }).join('');
        $('#tk-bang').html(html);
        $('#tk-thong-tin').text(`Hiển thị ${paged.length} / ${total} sản phẩm`);
        renderPagination('tk', page, Math.ceil(total / perPage));
    })
    .fail(function(xhr, status, error) {
        console.error('loadTonKho error:', status, error, xhr.responseText);
        $('#tk-bang').html(`<tr><td colspan="7" class="text-center text-danger py-4">Lỗi: ${error || status}<br><small class="text-muted">${xhr.responseText || ''}</small></td></tr>`);
    });
}

function loadLoHang(page = 1) {
    lhPage = page;
    const q = $('#lh-filter-q').val();
    const ncc = $('#lh-filter-ncc').val();
    const params = new URLSearchParams({ page, q: q || '', id_nha_cung_cap: ncc || '' });
    $.get('/admin/api/lo-hang?' + params.toString(), res => {
        const items = res.data?.data || [];
        if (!items.length) {
            $('#lh-bang').html('<tr><td colspan="9" class="text-center text-muted py-4">Chưa có lô hàng nào.</td></tr>');
            $('#lh-thong-tin').text('');
            $('#lh-phan-trang').html('');
            return;
        }
        const html = items.map(item => {
            const tongNhap = item.chi_tiet_lo_hang_sum_so_luong_nhap || 0;
            const tongTon = item.chi_tiet_lo_hang_sum_so_luong_ton || 0;
            const ncc = item.nha_cung_cap?.ten_nha_cung_cap || '<span class="text-muted">--</span>';
            const ngay = item.ngay_nhap || '';
            const maLo = item.ma_lo || 'L-' + item.id;
            let hsds = (item.chi_tiet_lo_hang || []).map(c => (c.han_su_dung || '').split('T')[0]).filter(d => d).sort();
            const hsdMin = hsds[0] || null;
            let hsdBadge = '', hsdClass = '';
            if (hsdMin) {
                const ngayHsdNghiem = new Date(hsdMin);
                const ngayHomNay = new Date();
                ngayHsdNghiem.setHours(0,0,0,0); ngayHomNay.setHours(0,0,0,0);
                const diff = Math.ceil((ngayHsdNghiem - ngayHomNay) / 86400000);
                if (diff < 0) { hsdBadge = `<span class="badge bg-danger badge-hsd">Hết HSD</span>`; hsdClass = 'table-danger'; }
                else if (diff <= 30) { hsdBadge = `<span class="badge bg-warning text-dark badge-hsd">${hsdMin} (${diff} ngày)</span>`; hsdClass = 'table-warning'; }
                else { hsdBadge = `<span class="badge bg-success badge-hsd">${hsdMin}</span>`; }
            }
            let loBadge = tongTon > 0 ? '<span class="badge bg-success">Còn hàng</span>' : '<span class="badge bg-secondary">Hết</span>';
            return `
            <tr class="${hsdClass}">
                <td>${item.id}</td>
                <td><span class="badge bg-dark lo-badge">${maLo}</span></td>
                <td>${ncc}</td>
                <td>${ngay}</td>
                <td class="text-center">${tongNhap.toLocaleString()}</td>
                <td class="text-center"><strong>${tongTon.toLocaleString()}</strong></td>
                <td class="text-center">${hsdBadge}</td>
                <td class="text-center">${loBadge}</td>
                <td class="text-center">
                    <button class="btn btn-sm btn-outline-primary btn-xem-lo" data-id="${item.id}"><i class="fas fa-eye"></i></button>
                    <button class="btn btn-sm btn-outline-danger btn-xoa-lo" data-id="${item.id}"><i class="fas fa-trash"></i></button>
                </td>
            </tr>`;
        }).join('');
        $('#lh-bang').html(html);
        const total = res.data.total || items.length;
        $('#lh-thong-tin').text(`Hiển thị ${items.length} / ${total} lô hàng`);
        renderPagination('lh', page, res.data.last_page || 1);
    })
    .fail(function(xhr, status, error) {
        console.error('loadLoHang error:', status, error, xhr.responseText);
        $('#lh-bang').html(`<tr><td colspan="9" class="text-center text-danger py-4">Lỗi: ${error || status}<br><small class="text-muted">${xhr.responseText || ''}</small></td></tr>`);
    });
}

function loadPhieuNhap(page = 1) {
    pnPage = page;
    const loai = $('#pn-filter-loai').val();
    const tu = $('#pn-filter-tu').val();
    const den = $('#pn-filter-den').val();
    const params = new URLSearchParams({ page, loai_nhap: loai || '', tu_ngay: tu || '', den_ngay: den || '' });
    $.get('/admin/api/phieu-nhap?' + params.toString(), res => {
        const items = res.data?.data || [];
        if (!items.length) {
            $('#pn-bang').html('<tr><td colspan="9" class="text-center text-muted py-4">Chưa có phiếu nhập nào.</td></tr>');
            $('#pn-thong-tin').text('');
            $('#pn-phan-trang').html('');
            return;
        }
        const html = items.map(item => {
            const loaiLabel = item.loai_nhap === 'mua_hang'
                ? '<span class="badge bg-success">Nhập mua hàng</span>'
                : '<span class="badge bg-info">Trả lại từ khách</span>';
            const ncc = item.phieu?.nha_cung_cap?.ten_nha_cung_cap || '--';
            const nguoi = item.phieu?.nguoi_dung?.ho_ten || item.phieu?.id_nguoi_dung || '--';
            const ngay = item.created_at?.slice(0, 10) || '';
            const ghiChu = item.ghi_chu || '<span class="text-muted">--</span>';
            return `
            <tr>
                <td>${item.id}</td>
                <td><span class="badge bg-primary">PN-${item.id_phieu}</span></td>
                <td>${loaiLabel}</td>
                <td>${ncc}</td>
                <td>${nguoi}</td>
                <td class="text-center">${ngay}</td>
                <td>${ghiChu}</td>
                <td class="text-center">
                    <button class="btn btn-sm btn-outline-primary btn-xem-pn" data-id="${item.id}"><i class="fas fa-eye"></i></button>
                    <button class="btn btn-sm btn-outline-danger btn-xoa-pn" data-id="${item.id}"><i class="fas fa-trash"></i></button>
                </td>
            </tr>`;
        }).join('');
        $('#pn-bang').html(html);
        const total = res.data.total || items.length;
        $('#pn-thong-tin').text(`Hiển thị ${items.length} / ${total} phiếu nhập`);
        renderPagination('pn', page, res.data.last_page || 1);
    })
    .fail(function(xhr, status, error) {
        console.error('loadPhieuNhap error:', status, error, xhr.responseText);
        $('#pn-bang').html(`<tr><td colspan="9" class="text-center text-danger py-4">Lỗi: ${error || status}<br><small class="text-muted">${xhr.responseText || ''}</small></td></tr>`);
    });
}

function loadPhieuXuat(page = 1) {
    pxPage = page;
    const loai = $('#px-filter-loai').val();
    const tu = $('#px-filter-tu').val();
    const den = $('#px-filter-den').val();
    const params = new URLSearchParams({ page, loai_xuat: loai || '', tu_ngay: tu || '', den_ngay: den || '' });
    $.get('/admin/api/phieu-xuat?' + params.toString(), res => {
        const items = res.data?.data || [];
        if (!items.length) {
            $('#px-bang').html('<tr><td colspan="9" class="text-center text-muted py-4">Chưa có phiếu xuất nào.</td></tr>');
            $('#px-thong-tin').text('');
            $('#px-phan-trang').html('');
            return;
        }
        const html = items.map(item => {
            const loaiLabel = item.loai_xuat === 'tra_hang_nha_cung_cap'
                ? '<span class="badge bg-warning text-dark">Trả hàng NCC</span>'
                : '<span class="badge bg-danger">Tiêu hủy</span>';
            const ncc = item.phieu?.nha_cung_cap?.ten_nha_cung_cap || '--';
            const nguoi = item.phieu?.nguoi_dung?.ho_ten || item.phieu?.id_nguoi_dung || '--';
            const ngay = item.created_at?.slice(0, 10) || '';
            const lyDo = item.ly_do || '<span class="text-muted">--</span>';
            const tongSl = '--';
            return `
            <tr>
                <td>${item.id}</td>
                <td><span class="badge bg-danger">PX-${item.id_phieu}</span></td>
                <td>${loaiLabel}</td>
                <td>${ncc}</td>
                <td>${lyDo}</td>
                <td>${nguoi}</td>
                <td class="text-center">${ngay}</td>
                <td class="text-center fw-bold text-danger">${tongSl.toLocaleString()}</td>
                <td class="text-center">
                    <button class="btn btn-sm btn-outline-primary btn-xem-px" data-id="${item.id}"><i class="fas fa-eye"></i></button>
                    <button class="btn btn-sm btn-outline-danger btn-xoa-px" data-id="${item.id}"><i class="fas fa-trash"></i></button>
                </td>
            </tr>`;
        }).join('');
        $('#px-bang').html(html);
        const total = res.data.total || items.length;
        $('#px-thong-tin').text(`Hiển thị ${items.length} / ${total} phiếu xuất`);
        renderPagination('px', page, res.data.last_page || 1);
    });
}

function loadCanhBao() {
    $.get('/admin/api/lo-hang/canh-bao', res => {
        if (!res.success) return;
        const d = res.data;
        const totalAlert = (d.het_han?.length || 0) + (d.sap_het_han?.length || 0) + (d.duoi_dinh_muc?.length || 0) + (d.het_hang?.length || 0);
        if (totalAlert === 0) {
            $('#canh-bao-content').html('<div class="col-12 text-center py-5"><i class="fas fa-check-circle text-success fs-1 mb-3 d-block"></i><h5 class="text-success">Tất cả bình thường!</h5><p class="text-muted">Không có cảnh báo nào lúc này.</p></div>');
            return;
        }
        let html = '';
        if (d.het_han?.length) {
            html += renderAlertGroup('het-han', 'Đã hết HSD', 'het-han', d.het_han, 'bg-danger');
        }
        if (d.sap_het_han?.length) {
            html += renderAlertGroup('sap-het-han', 'Sắp hết HSD (≤30 ngày)', 'sap-het-han', d.sap_het_han, 'bg-warning text-dark');
        }
        if (d.duoi_dinh_muc?.length) {
            html += renderAlertGroup('duoi-dinh-muc', 'Dưới định mức tồn kho', 'duoi-dinh-muc', d.duoi_dinh_muc, 'bg-warning text-dark');
        }
        if (d.het_hang?.length) {
            html += renderAlertGroup('het-hang', 'Hết hàng', 'het-hang', d.het_hang, 'bg-secondary');
        }
        $('#canh-bao-content').html(`<div class="row g-3">${html}</div>`);
    });
}

function renderAlertGroup(type, title, cls, items, badgeCls) {
    const rows = items.map(item => {
        if (type === 'het-han' || type === 'sap-het-han') {
            const sp = item.san_pham || {};
            const lo = item.lo_hang || {};
            const hsdRaw = item.han_su_dung || '';
            const hsdDate = new Date(hsdRaw);
            const diff = Math.ceil((hsdDate - new Date()) / 86400000);
            return `
            <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                <div>
                    <div class="fw-semibold">${sp.ten_san_pham || item.id_san_pham}</div>
                    <div class="small text-muted">
                        Lô: ${lo.ma_lo || item.id_lo_hang} | Tồn: ${item.so_luong_ton} | HSD: ${hsdRaw.split('T')[0]}
                        ${diff < 0 ? '<span class="text-danger ms-1">Đã hết HSD</span>' : `<span class="text-warning ms-1">Còn ${diff} ngày</span>`}
                    </div>
                </div>
                <span class="badge ${badgeCls}">${item.so_luong_ton}</span>
            </div>`;
        } else {
            return `
            <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                <div>
                    <div class="fw-semibold">${item.ten_san_pham || item.id}</div>
                    <div class="small text-muted">Mã: ${item.ma_vach || '--'} | Định mức: ${item.dinh_muc_toi_thieu || 0}</div>
                </div>
                <span class="badge ${badgeCls}">${item.so_luong_ton_kho || 0}</span>
            </div>`;
        }
    }).join('');
    return `
    <div class="col-md-6">
        <div class="card canh-bao-card ${cls}">
            <div class="card-header bg-white py-2">
                <div class="d-flex justify-content-between align-items-center">
                    <h6 class="mb-0"><i class="fas fa-triangle-exclamation me-1"></i>${title}</h6>
                    <span class="badge ${badgeCls}">${items.length}</span>
                </div>
            </div>
            <div class="card-body py-2">${rows}</div>
        </div>
    </div>`;
}

// ─── CLICK EVENTS ────────────────────────────────────────
$(document).on('click', '.fefo-detail-row', e => e.stopPropagation());
$(document).on('click', '.clickable-row', function () {
    const id = $(this).data('id');
    const detail = $(`#fefo-detail-${id}`);
    const icon = $(this).find('.fa-chevron-down, .fa-chevron-right');
    if (detail.is(':visible')) {
        detail.hide();
        icon.removeClass('fa-chevron-down').addClass('fa-chevron-right');
        $(this).find('td').removeClass('fw-semibold');
    } else {
        $('.fefo-detail-row').hide();
        $('.fefo-detail-row').prev().find('.fa-chevron-down').removeClass('fa-chevron-down').addClass('fa-chevron-right');
        detail.show();
        icon.removeClass('fa-chevron-right').addClass('fa-chevron-down');
        $(this).find('td').addClass('fw-semibold');
        $.get('/admin/api/lo-hang/ton-kho?id_san_pham=' + id, res => {
            if (!res.success) return;
            const chiTiet = res.data.chi_tiet || [];
            if (!chiTiet.length) {
                $(`#fefo-detail-content-${id}`).html('<div class="text-muted small">Không có dữ liệu tồn kho.</div>');
                return;
            }
            const rows = chiTiet.map(ct => {
                const lo = ct.lo_hang || {};
                const ncc = lo.nha_cung_cap?.ten_nha_cung_cap || '--';
                const hsdRaw = ct.han_su_dung || '';
                const hsd = hsdRaw.split('T')[0];
                const diff = Math.ceil((new Date(hsdRaw) - new Date()) / 86400000);
                let hsdClass = 'text-success';
                if (diff < 0) hsdClass = 'text-danger';
                else if (diff <= 30) hsdClass = 'text-warning';
                return `
                <div class="d-flex justify-content-between align-items-center py-1 border-bottom">
                    <div class="small">
                        <span class="badge bg-dark me-1">${lo.ma_lo || 'L-' + lo.id}</span>
                        <span class="me-2">NCC: ${ncc}</span>
                        <span class="me-2">HSD: <span class="${hsdClass} fw-semibold">${hsd}</span> (${diff > 0 ? diff + ' ngày' : 'Hết HSD'})</span>
                    </div>
                    <div class="text-end">
                        <span class="fw-bold text-primary">${ct.so_luong_ton?.toLocaleString()}</span>
                        <span class="text-muted small ms-1">/ ${ct.so_luong_nhap?.toLocaleString()}</span>
                    </div>
                </div>`;
            }).join('');
            $(`#fefo-detail-content-${id}`).html(`<div class="small">${rows}</div>`);
        });
    }
});

$(document).on('click', '.btn-xem-ton', function (e) {
    e.stopPropagation();
    const id = $(this).data('id');
    $('#tab-ton-kho').click();
    setTimeout(() => {
        $(`.clickable-row[data-id="${id}"]`).click();
    }, 100);
});

$(document).on('click', '.btn-xem-lo', function () {
    const id = $(this).data('id');
    $.get('/admin/api/lo-hang/' + id, res => {
        if (!res.success) return;
        const lo = res.data;
        const tongNhap = (lo.chi_tiet_lo_hang || []).reduce((s, c) => s + (c.so_luong_nhap || 0), 0);
        const tongTon = (lo.chi_tiet_lo_hang || []).reduce((s, c) => s + (c.so_luong_ton || 0), 0);
        const rows = (lo.chi_tiet_lo_hang || []).map(ct => {
            const sp = ct.san_pham || {};
            return `
            <tr>
                <td>${sp.ten_san_pham || ct.id_san_pham}</td>
                <td class="text-center">${(ct.so_luong_nhap || 0).toLocaleString()}</td>
                <td class="text-center">${(ct.so_luong_ton || 0).toLocaleString()}</td>
                <td class="text-center">${Number(ct.gia_nhap || 0).toLocaleString()} đ</td>
                <td class="text-center">${(ct.han_su_dung || '').split('T')[0]}</td>
                <td class="text-center">
                    ${ct.so_luong_ton > 0 ? '<span class="badge bg-success">Còn hàng</span>' : '<span class="badge bg-secondary">Hết</span>'}
                </td>
            </tr>`;
        }).join('') || '<tr><td colspan="6" class="text-center text-muted">Không có chi tiết</td></tr>';
        $('#modal-xem-lo-body').html(`
            <div class="row mb-3">
                <div class="col-md-3"><strong>Mã lô:</strong> ${lo.ma_lo || 'L-' + lo.id}</div>
                <div class="col-md-3"><strong>NCC:</strong> ${lo.nha_cung_cap?.ten_nha_cung_cap || '--'}</div>
                <div class="col-md-3"><strong>Ngày nhập:</strong> ${lo.ngay_nhap || ''}</div>
                <div class="col-md-3"><strong>Ghi chú:</strong> ${lo.ghi_chu || '--'}</div>
            </div>
            <div class="row mb-3">
                <div class="col-md-3"><strong>Tổng nhập:</strong> ${tongNhap.toLocaleString()}</div>
                <div class="col-md-3"><strong>Tổng tồn:</strong> ${tongTon.toLocaleString()}</div>
            </div>
            <table class="table table-sm table-bordered">
                <thead class="table-light"><tr><th>Sản phẩm</th><th class="text-center">SL nhập</th><th class="text-center">SL tồn</th><th class="text-center">Giá nhập</th><th class="text-center">HSD</th><th class="text-center">Trạng thái</th></tr></thead>
                <tbody>${rows}</tbody>
            </table>
        `);
        new bootstrap.Modal(document.getElementById('modal-xem-lo')).show();
    });
});

$(document).on('click', '.btn-xoa-lo', function () {
    if (!confirm('Xóa lô hàng này?')) return;
    const id = $(this).data('id');
    $.ajax({ url: '/admin/api/lo-hang/' + id, method: 'DELETE', success: res => { hienBao('success', res.message); loadLoHang(lhPage); loadStats(); }, error: x => hienBao('danger', x.responseJSON?.message || 'Lỗi.') });
});

$(document).on('click', '.btn-xem-pn', function () {
    const id = $(this).data('id');
    $.get('/admin/api/phieu-nhap/' + id, res => {
        if (!res.success) return;
        const pn = res.data;
        const loaiLabel = pn.loai_nhap === 'mua_hang' ? 'Nhập mua hàng' : 'Trả lại từ khách';
        const rows = (pn.chi_tiet_phieu || []).map(ct => {
            const sp = ct.san_pham || {};
            const lo = ct.lo_hang || {};
            return `<tr>
                <td>${sp.ten_san_pham || ct.id_san_pham}</td>
                <td class="text-center">${(ct.so_luong || 0).toLocaleString()}</td>
                <td class="text-center">${Number(ct.gia_nhap || 0).toLocaleString()} đ</td>
                <td class="text-center">${(ct.han_su_dung || '').split('T')[0]}</td>
                <td class="text-center">${lo.ma_lo || 'L-' + (ct.id_lo_hang || '')}</td>
                <td class="text-end fw-bold">${((ct.so_luong || 0) * (ct.gia_nhap || 0)).toLocaleString()} đ</td>
            </tr>`;
        }).join('') || '<tr><td colspan="6" class="text-center text-muted">Không có chi tiết</td></tr>';
        const tongGt = (pn.chi_tiet_phieu || []).reduce((s, ct) => s + (ct.so_luong || 0) * (ct.gia_nhap || 0), 0);
        $('#modal-xem-pn-body').html(`
            <div class="row mb-3">
                <div class="col-md-3"><strong>Mã phiếu:</strong> PN-${pn.id_phieu}</div>
                <div class="col-md-3"><strong>Loại:</strong> ${loaiLabel}</div>
                <div class="col-md-3"><strong>NCC:</strong> ${pn.phieu?.nha_cung_cap?.ten_nha_cung_cap || '--'}</div>
                <div class="col-md-3"><strong>Ngày:</strong> ${pn.created_at?.slice(0, 10) || ''}</div>
            </div>
            <p><strong>Ghi chú:</strong> ${pn.ghi_chu || '--'}</p>
            <table class="table table-sm table-bordered">
                <thead class="table-light"><tr><th>Sản phẩm</th><th class="text-center">SL nhập</th><th class="text-center">Giá nhập</th><th class="text-center">HSD</th><th class="text-center">Lô</th><th class="text-end">Thành tiền</th></tr></thead>
                <tbody>${rows}</tbody>
                <tfoot><tr><td colspan="5" class="text-end fw-bold">Tổng cộng:</td><td class="text-end fw-bold text-danger">${tongGt.toLocaleString()} đ</td></tr></tfoot>
            </table>
        `);
        new bootstrap.Modal(document.getElementById('modal-xem-pn')).show();
    });
});

$(document).on('click', '.btn-xoa-pn', function () {
    if (!confirm('Xóa phiếu nhập này?')) return;
    const id = $(this).data('id');
    $.ajax({ url: '/admin/api/phieu-nhap/' + id, method: 'DELETE', success: res => { hienBao('success', res.message); loadPhieuNhap(pnPage); loadStats(); }, error: x => hienBao('danger', x.responseJSON?.message || 'Lỗi.') });
});

$(document).on('click', '.btn-xem-px', function () {
    const id = $(this).data('id');
    $.get('/admin/api/phieu-xuat/' + id, res => {
        if (!res.success) return;
        const px = res.data;
        const loaiLabel = px.loai_xuat === 'tra_hang_nha_cung_cap' ? 'Trả hàng NCC' : 'Tiêu hủy';
        const rows = (px.chi_tiet_phieu || []).map(ct => {
            const sp = ct.san_pham || {};
            const hsd = (ct.chi_tiet_lo_hang?.han_su_dung || ct.han_su_dung || '').split('T')[0];
            return `<tr>
                <td>${sp.ten_san_pham || ct.id_san_pham}</td>
                <td class="text-center text-danger fw-bold">${(ct.so_luong || 0).toLocaleString()}</td>
                <td class="text-center">${hsd || '--'}</td>
                <td class="text-center">${ct.lo_hang?.ma_lo || 'L-' + (ct.id_lo_hang || '')}</td>
            </tr>`;
        }).join('') || '<tr><td colspan="4" class="text-center text-muted">Không có chi tiết</td></tr>';
        const tongSl = (px.chi_tiet_phieu || []).reduce((s, ct) => s + (ct.so_luong || 0), 0);
        $('#modal-xem-px-body').html(`
            <div class="row mb-3">
                <div class="col-md-3"><strong>Mã phiếu:</strong> PX-${px.id_phieu}</div>
                <div class="col-md-3"><strong>Loại:</strong> ${loaiLabel}</div>
                <div class="col-md-3"><strong>NCC:</strong> ${px.phieu?.nha_cung_cap?.ten_nha_cung_cap || '--'}</div>
                <div class="col-md-3"><strong>Ngày:</strong> ${px.created_at?.slice(0, 10) || ''}</div>
            </div>
            <p><strong>Lý do:</strong> ${px.ly_do || '--'}</p>
            <table class="table table-sm table-bordered">
                <thead class="table-light"><tr><th>Sản phẩm</th><th class="text-center">SL xuất</th><th class="text-center">HSD lô</th><th class="text-center">Lô</th></tr></thead>
                <tbody>${rows}</tbody>
                <tfoot><tr><td colspan="3" class="text-end fw-bold">Tổng SL xuất:</td><td class="text-center fw-bold text-danger">${tongSl.toLocaleString()}</td></tr></tfoot>
            </table>
        `);
        new bootstrap.Modal(document.getElementById('modal-xem-px')).show();
    });
});

$(document).on('click', '.btn-xoa-px', function () {
    if (!confirm('Xóa phiếu xuất này và hoàn tăng tồn kho?')) return;
    const id = $(this).data('id');
    $.ajax({ url: '/admin/api/phieu-xuat/' + id, method: 'DELETE', success: res => { hienBao('success', res.message); loadPhieuXuat(pxPage); loadStats(); }, error: x => hienBao('danger', x.responseJSON?.message || 'Lỗi.') });
});

// ─── NCC TAB EVENTS ───────────────────────────────────────
$('#tab-ncc').on('shown.bs.tab', () => loadNhaCungCap(nccPage));

$(document).on('click', '#ncc-btn-them', () => {
    $('#form-them-ncc')[0].reset();
    $('#form-them-ncc .form-control').removeClass('is-invalid');
    new bootstrap.Modal(document.getElementById('modal-them-ncc')).show();
});

$(document).on('click', '#ncc-btn-search', () => loadNhaCungCap(1, $('#ncc-search').val()));
$(document).on('click', '#ncc-btn-refresh', () => { $('#ncc-search').val(''); loadNhaCungCap(1); });

$(document).on('keypress', '#ncc-search', e => { if (e.which === 13) loadNhaCungCap(1, $('#ncc-search').val()); });

$(document).on('click', '.btn-sua-ncc', function () {
    const id = $(this).data('id');
    $.get('/admin/api/nha-cung-cap/' + id, res => {
        if (!res.success) { hienBao('danger', 'Không tải được dữ liệu.'); return; }
        const n = res.item;
        $('#ncc_sua_id').val(n.id);
        $('#ncc_sua_ten').val(n.ten_nha_cung_cap);
        $('#ncc_sua_email').val(n.email || '');
        $('#ncc_sua_sdt').val(n.so_dien_thoai);
        $('#ncc_sua_nguoi').val(n.nguoi_dai_dien || '');
        $('#form-sua-ncc .form-control').removeClass('is-invalid');
        new bootstrap.Modal(document.getElementById('modal-sua-ncc')).show();
    }).fail(() => hienBao('danger', 'Lỗi kết nối.'));
});

$(document).on('click', '.btn-xoa-ncc', function () {
    const id = $(this).data('id');
    const ten = $(this).data('ten');
    if (!confirm('Xóa nhà cung cấp "' + ten + '"?')) return;
    $.ajax({ url: '/admin/api/nha-cung-cap/' + id, method: 'DELETE', success: res => {
        hienBao('success', res.message);
        loadNhaCungCap(nccPage);
    }, error: x => hienBao('danger', x.responseJSON?.message || 'Lỗi xóa.') });
});

// ─── DYNAMIC ROWS ────────────────────────────────────────
function spOptions(existingId) {
    return sanPhamAll.map(sp =>
        `<option value="${sp.id}" ${sp.id == existingId ? 'selected' : ''}>${sp.ten_san_pham} (${sp.ma_vach || sp.id})</option>`
    ).join('');
}

function addPxRow(id, sl) {
    const idx = pxIdx++;
    const opts = sanPhamAll.map(sp =>
        `<option value="${sp.id}" data-ton="${sp.chi_tiet_lo_hang_ton_sum_so_luong_ton || 0}" ${sp.id == id ? 'selected' : ''}>${sp.ten_san_pham} (${sp.ma_vach || sp.id})</option>`
    ).join('');
    $('#px-ds-sp').append(`<tr>
        <td><select class="form-select form-select-sm px-sp-select" name="chi_tiet[${idx}][id_san_pham]">${opts || '<option value="">-- Chọn --</option>'}</select></td>
        <td class="text-center text-muted ton-cell small">--</td>
        <td><input type="number" class="form-control form-control-sm px-sl-input" name="chi_tiet[${idx}][so_luong]" value="${sl || 1}" min="1"></td>
        <td><button type="button" class="btn btn-sm btn-outline-danger" onclick="removePxRow(this)"><i class="fas fa-times"></i></button></td>
    </tr>`);
    updateTonCell($('#px-ds-sp tr:last .px-sp-select'));
}

function addLoRow(id, sl, gia, hsd) {
    const idx = loIdx++;
    const opts = spOptions(id);
    $('#lo-ds-sp').append(`<tr>
        <td><select class="form-select form-select-sm" name="chi_tiet[${idx}][id_san_pham]">${opts || '<option value="">-- Chọn --</option>'}</select></td>
        <td><input type="number" class="form-control form-control-sm" name="chi_tiet[${idx}][so_luong_nhap]" value="${sl || 1}" min="1"></td>
        <td><input type="number" class="form-control form-control-sm" name="chi_tiet[${idx}][gia_nhap]" value="${gia || 0}" min="0" step="100"></td>
        <td><input type="date" class="form-control form-control-sm" name="chi_tiet[${idx}][han_su_dung]" value="${hsd || ''}"></td>
        <td><button type="button" class="btn btn-sm btn-outline-danger" onclick="removeLoRow(this)"><i class="fas fa-times"></i></button></td>
    </tr>`);
}

function removePxRow(btn) { if ($('#px-ds-sp tr').length > 1) $(btn).closest('tr').remove(); updateFefoPreview(); }
function removeLoRow(btn) { if ($('#lo-ds-sp tr').length > 1) $(btn).closest('tr').remove(); }

$(document).on('change', '.px-sp-select', function () { updateTonCell(this); });
function updateTonCell(el) {
    const val = $(el).val();
    const row = $(el).closest('tr');
    const cell = row.find('.ton-cell');
    if (val) {
        const opt = $(el).find('option:selected');
        cell.text('Tồn: ' + (opt.data('ton') || 0).toLocaleString());
    } else { cell.text('--'); }
}

function updateFefoPreview() {
    let html = '<div class="fefo-preview small"><h6 class="mb-2"><i class="fas fa-list-check me-1"></i>Preview FEFO khi xuất:</h6>';
    let hasItem = false;
    $('#px-ds-sp tr').each(function () {
        const spSelect = $(this).find('.px-sp-select');
        const spName = spSelect.find('option:selected').text() || '--';
        const sl = parseInt($(this).find('.px-sl-input').val()) || 0;
        if (spSelect.val() && sl > 0) {
            hasItem = true;
            html += `<div class="mb-1"><strong>${spName}</strong> × ${sl.toLocaleString()}</div>`;
            $.ajax({ url: '/admin/api/lo-hang/ton-kho?id_san_pham=' + spSelect.val(), async: false, success: res => {
                if (!res.success) return;
                let con = sl;
                const loList = (res.data.chi_tiet || []).map(ct => {
                    const lo = ct.lo_hang || {};
                    const lay = Math.min(con, ct.so_luong_ton || 0);
                    con -= lay;
                    return { ma_lo: lo.ma_lo || 'L-' + lo.id, hsd: (ct.han_su_dung || '').split('T')[0], lay };
                }).filter(l => l.lay > 0);
                loList.forEach(l => { html += `<div class="ms-3 text-muted">→ Lô ${l.ma_lo} (HSD ${l.hsd}): xuất <span class="text-danger">${l.lay}</span></div>`; });
                if (con > 0) html += `<div class="ms-3 text-danger">⚠ Không đủ tồn kho (thiếu ${con})</div>`;
            }});
        }
    });
    html += '</div>';
    $('#px-fefo-preview').html(hasItem ? html : '');
}

// ─── FORM SUBMITS ────────────────────────────────────────
// NCC: Thêm
$(document).on('submit', '#form-them-ncc', function (e) {
    e.preventDefault();
    const btn = $('#ncc_btn_luu');
    btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> Đang lưu...');
    $.post('/admin/api/nha-cung-cap', {
        _token: $('meta[name="csrf-token"]').attr('content'),
        ten_nha_cung_cap: $('#ncc_them_ten').val(),
        email: $('#ncc_them_email').val(),
        so_dien_thoai: $('#ncc_them_sdt').val(),
        nguoi_dai_dien: $('#ncc_them_nguoi').val(),
    }, res => {
        if (res.success) {
            bootstrap.Modal.getInstance(document.getElementById('modal-them-ncc')).hide();
            hienBao('success', res.message);
            loadNhaCungCap(1);
        } else {
            hienBao('danger', res.message);
        }
    }).fail(x => {
        const err = x.responseJSON;
        if (err && err.errors) {
            const e = err.errors;
            $('#ncc_them_ten').toggleClass('is-invalid', !!e.ten_nha_cung_cap)
                .next('.invalid-feedback').text(e.ten_nha_cung_cap?.[0] || '');
            $('#ncc_them_email').toggleClass('is-invalid', !!e.email)
                .next('.invalid-feedback').text(e.email?.[0] || '');
            $('#ncc_them_sdt').toggleClass('is-invalid', !!e.so_dien_thoai)
                .next('.invalid-feedback').text(e.so_dien_thoai?.[0] || '');
        } else {
            hienBao('danger', err?.message || 'Lỗi server.');
        }
    }).always(() => btn.prop('disabled', false).html('<i class="fas fa-save me-1"></i>Lưu'));
});

// NCC: Sửa
$(document).on('submit', '#form-sua-ncc', function (e) {
    e.preventDefault();
    const id = $('#ncc_sua_id').val();
    const btn = $('#ncc_btn_cap_nhat');
    btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> Đang cập nhật...');
    $.ajax({ url: '/admin/api/nha-cung-cap/' + id, method: 'PUT', headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }, data: {
        ten_nha_cung_cap: $('#ncc_sua_ten').val(),
        email: $('#ncc_sua_email').val(),
        so_dien_thoai: $('#ncc_sua_sdt').val(),
        nguoi_dai_dien: $('#ncc_sua_nguoi').val(),
    }, success: res => {
        if (res.success) {
            bootstrap.Modal.getInstance(document.getElementById('modal-sua-ncc')).hide();
            hienBao('success', res.message);
            loadNhaCungCap(nccPage);
        } else {
            hienBao('danger', res.message);
        }
    }, error: x => {
        const err = x.responseJSON;
        if (err && err.errors) {
            const e = err.errors;
            $('#ncc_sua_ten').toggleClass('is-invalid', !!e.ten_nha_cung_cap)
                .next('.invalid-feedback').text(e.ten_nha_cung_cap?.[0] || '');
            $('#ncc_sua_email').toggleClass('is-invalid', !!e.email)
                .next('.invalid-feedback').text(e.email?.[0] || '');
            $('#ncc_sua_sdt').toggleClass('is-invalid', !!e.so_dien_thoai)
                .next('.invalid-feedback').text(e.so_dien_thoai?.[0] || '');
        } else {
            hienBao('danger', err?.message || 'Lỗi server.');
        }
    }, complete: () => btn.prop('disabled', false).html('<i class="fas fa-save me-1"></i>Cập nhật') });
});

function buildChiTiet(tableId, prefix) {
    const rows = [];
    $('#' + tableId + ' tr').each(function () {
        const row = {};
        $(this).find('input, select').each(function () {
            const name = $(this).attr('name');
            if (!name) return;
            const m = name.match(new RegExp('chi_tiet\\[\\d+\\]\\[(.+)\\]'));
            if (m) row[m[1]] = $(this).val();
        });
        if (Object.keys(row).length) rows.push(row);
    });
    return rows;
}

function submitPhieuNhap() {
    const chiTiet = buildChiTiet('pn-ds-sp', 'pn');
    if (!chiTiet.length) { hienBao('warning', 'Vui lòng thêm ít nhất một sản phẩm.'); return; }
    const data = {
        loai_nhap: $('#pn-loai').val(),
        id_nha_cung_cap: $('#pn-ncc').val() || null,
        ghi_chu: $('#pn-ghi-chu').val(),
        tao_lo_moi: '1',
        id_lo_hang: '',
        chi_tiet: chiTiet,
    };
    $.ajax({ url: '/admin/api/phieu-nhap', method: 'POST', contentType: 'application/json', data: JSON.stringify(data),
        success: res => {
            bootstrap.Modal.getInstance(document.getElementById('modal-tao-pn')).hide();
            hienBao('success', res.message);
            loadPhieuNhap(1); loadLoHang(1); loadStats();
        },
        error: x => hienBao('danger', x.responseJSON?.message || 'Có lỗi xảy ra.')
    });
}

function submitPhieuXuat() {
    const chiTiet = [];
    $('#px-ds-sp tr').each(function () {
        const sp = $(this).find('.px-sp-select').val();
        const sl = $(this).find('.px-sl-input').val();
        if (sp && sl) chiTiet.push({ id_san_pham: sp, so_luong: parseInt(sl) });
    });
    if (!chiTiet.length) { hienBao('warning', 'Vui lòng thêm ít nhất một sản phẩm.'); return; }
    const data = {
        loai_xuat: $('#px-loai').val(),
        id_nha_cung_cap: $('#px-ncc').val() || null,
        ly_do: $('#px-ly-do').val(),
        chi_tiet,
    };
    $.ajax({ url: '/admin/api/phieu-xuat', method: 'POST', contentType: 'application/json', data: JSON.stringify(data),
        success: res => {
            bootstrap.Modal.getInstance(document.getElementById('modal-tao-px')).hide();
            hienBao('success', res.message);
            loadPhieuXuat(1); loadTonKho(tkPage); loadStats();
        },
        error: x => hienBao('danger', x.responseJSON?.message || 'Có lỗi xảy ra.')
    });
}

function submitLoHang() {
    const chiTiet = buildChiTiet('lo-ds-sp', 'lo');
    if (!chiTiet.length) { hienBao('warning', 'Vui lòng thêm ít nhất một sản phẩm.'); return; }
    const data = {
        id_nha_cung_cap: $('#lo-ncc').val() || null,
        ma_lo: $('#lo-ma').val(),
        ngay_nhap: $('#lo-ngay').val(),
        ghi_chu: $('#lo-ghi-chu').val(),
        chi_tiet,
    };
    $.ajax({ url: '/admin/api/lo-hang', method: 'POST', contentType: 'application/json', data: JSON.stringify(data),
        success: res => {
            bootstrap.Modal.getInstance(document.getElementById('modal-tao-lo')).hide();
            hienBao('success', res.message);
            loadLoHang(1); loadStats();
        },
        error: x => hienBao('danger', x.responseJSON?.message || 'Có lỗi xảy ra.')
    });
}

// ─── PAGINATION ───────────────────────────────────────────
function renderPagination(prefix, current, total) {
    if (total <= 1) { $('#' + prefix + '-phan-trang').html(''); return; }
    let html = '<ul class="pagination mb-0">';
    if (current > 1) html += `<li class="page-item"><a class="page-link" href="javascript:;" onclick="go${prefix}(${current - 1})">‹</a></li>`;
    for (let i = 1; i <= total; i++) {
        if (i === 1 || i === total || Math.abs(i - current) <= 2) {
            html += `<li class="page-item ${i === current ? 'active' : ''}"><a class="page-link" href="javascript:;" onclick="go${prefix}(${i})">${i}</a></li>`;
        } else if (Math.abs(i - current) === 3) {
            html += '<li class="page-item disabled"><span class="page-link">...</span></li>';
        }
    }
    if (current < total) html += `<li class="page-item"><a class="page-link" href="javascript:;" onclick="go${prefix}(${current + 1})">›</a></li>`;
    html += '</ul>';
    $('#' + prefix + '-phan-trang').html(html);
}

function gotk(p) { loadTonKho(p); }
function golh(p) { loadLoHang(p); }
function gopn(p) { loadPhieuNhap(p); }
function gopx(p) { loadPhieuXuat(p); }

// ─── ALERTS ──────────────────────────────────────────────
function hienBao(type, message) {
    $('#kh-alert-container').html(`<div class="alert alert-${type} alert-dismissible fade show" role="alert">${message}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>`);
    setTimeout(() => $('.alert').fadeOut(), 4000);
}

// ─── LOAD ALL PRODUCTS ────────────────────────────────────
function loadSanPhamAll() {
    $.get('/admin/api/san-pham', { q: '' }, res => {
        sanPhamAll = res.data?.data || [];
    });
}
</script>
@endsection
