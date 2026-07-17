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

{{-- Tabs --}}
<ul class="nav nav-tabs mb-4" id="khoHangTabs" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active" id="tab-ton-kho" data-bs-toggle="tab" data-bs-target="#content-ton-kho" type="button">
            <i class="fas fa-boxes me-1"></i>Tồn kho
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="tab-lo-hang" data-bs-toggle="tab" data-bs-target="#content-lo-hang" type="button">
            <i class="fas fa-layer-group me-1"></i>Lô hàng
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="tab-nhap-hang" data-bs-toggle="tab" data-bs-target="#content-nhap-hang" type="button">
            <i class="fas fa-arrow-down text-success me-1"></i>Nhập hàng
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="tab-xuat-hang" data-bs-toggle="tab" data-bs-target="#content-xuat-hang" type="button">
            <i class="fas fa-arrow-up text-danger me-1"></i>Xuất hàng
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="tab-canh-bao" data-bs-toggle="tab" data-bs-target="#content-canh-bao" type="button">
            <i class="fas fa-triangle-exclamation me-1"></i>Cảnh báo
            <span class="badge bg-danger ms-1" id="badge-canh-bao" style="display:none">0</span>
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="tab-ncc" data-bs-toggle="tab" data-bs-target="#content-ncc" type="button">
            <i class="fas fa-truck me-1"></i>Nhà cung cấp
        </button>
    </li>
</ul>

{{-- Tab Content --}}
<div class="tab-content" id="khoHangTabContent">

    {{-- ============================================ TỒN KHO ============================================ --}}
    <div class="tab-pane fade show active" id="content-ton-kho" role="tabpanel">
        <div class="card table-admin">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-boxes me-2"></i>Danh sách tồn kho</h5>
                <div class="d-flex gap-2">
                    <input type="text" class="form-control form-control-sm" id="tk-filter-ten" placeholder="Tìm sản phẩm..." style="width:200px">
                    <select class="form-select form-select-sm" id="tk-filter-status" style="width:160px">
                        <option value="">Tất cả trạng thái</option>
                        <option value="binh-thuong">Bình thường</option>
                        <option value="duoi-dinh-muc">Dưới định mức</option>
                        <option value="het-hang">Hết hàng</option>
                    </select>
                    <button class="btn btn-sm btn-outline-primary" onclick="loadTonKho(1)"><i class="fas fa-search"></i></button>
                </div>
            </div>
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
                        <tbody id="tk-bang"></tbody>
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

    {{-- ============================================ LÔ HÀNG ============================================ --}}
    <div class="tab-pane fade" id="content-lo-hang" role="tabpanel">
        <div class="d-flex justify-content-end mb-3 gap-2">
            <button class="btn btn-primary btn-sm" id="lo-btn-them"><i class="fas fa-plus me-1"></i>Thêm lô hàng</button>
        </div>
        <div class="card table-admin mb-4">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3">
                        <input type="text" class="form-control form-control-sm" id="lh-filter-ma" placeholder="Mã lô...">
                    </div>
                    <div class="col-md-2">
                        <button class="btn btn-primary btn-sm" onclick="loadLoHang(1)"><i class="fas fa-filter me-1"></i>Lọc</button>
                        <a href="{{ url('admin/kho-hang') }}" class="btn btn-light border btn-sm"><i class="fas fa-redo"></i></a>
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
                                <th class="text-center">Ngày nhập</th>
                                <th class="text-center">Số SP</th>
                                <th class="text-center">Tổng tồn</th>
                                <th class="text-center">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody id="lh-bang"></tbody>
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

    {{-- ============================================ NHẬP HÀNG ============================================ --}}
    <div class="tab-pane fade" id="content-nhap-hang" role="tabpanel">
        <div class="d-flex justify-content-end mb-3 gap-2">
            <button class="btn btn-outline-success btn-sm" id="pn-btn-export"><i class="fas fa-file-excel me-1"></i>Xuất Excel</button>
            <button class="btn btn-outline-success btn-sm" id="pn-btn-import"><i class="fas fa-file-import me-1"></i>Import Excel</button>
            <button class="btn btn-success btn-sm" id="pn-btn-tao"><i class="fas fa-plus me-1"></i>Tạo nhập hàng</button>
        </div>
        <div class="card table-admin mb-4">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3">
                        <select class="form-select form-select-sm" id="pn-filter-loai">
                            <option value="">Tất cả loại</option>
                            <option value="mua_hang">Nhập mua hàng</option>
                            <option value="tra_lai_tu_khach">Trả lại từ khách</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <input type="date" class="form-control form-control-sm" id="pn-filter-tu">
                    </div>
                    <div class="col-md-2">
                        <input type="date" class="form-control form-control-sm" id="pn-filter-den">
                    </div>
                    <div class="col-md-2">
                        <button class="btn btn-primary btn-sm" onclick="loadPhieuNhap(1)"><i class="fas fa-filter me-1"></i>Lọc</button>
                        <a href="{{ url('admin/kho-hang') }}" class="btn btn-light border btn-sm"><i class="fas fa-redo"></i></a>
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
                                <th>Người tạo</th>
                                <th class="text-center">Ngày</th>
                                <th class="text-center">Tổng giá trị</th>
                                <th class="text-center">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody id="pn-bang"></tbody>
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

    {{-- ============================================ XUẤT HÀNG ============================================ --}}
    <div class="tab-pane fade" id="content-xuat-hang" role="tabpanel">
        <div class="d-flex justify-content-end mb-3 gap-2">
            <button class="btn btn-outline-danger btn-sm" id="px-btn-export"><i class="fas fa-file-excel me-1"></i>Xuất Excel</button>
            <button class="btn btn-outline-danger btn-sm" id="px-btn-import"><i class="fas fa-file-import me-1"></i>Import Excel</button>
            <button class="btn btn-danger btn-sm" id="px-btn-tao"><i class="fas fa-plus me-1"></i>Tạo phiếu xuất</button>
        </div>
        <div class="card table-admin mb-4">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3">
                        <select class="form-select form-select-sm" id="px-filter-loai">
                            <option value="">Tất cả loại</option>
                            <option value="tieu_huy">Tiêu hủy</option>
                            <option value="tra_hang_nha_cung_cap">Trả hàng NCC</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <input type="date" class="form-control form-control-sm" id="px-filter-tu">
                    </div>
                    <div class="col-md-2">
                        <input type="date" class="form-control form-control-sm" id="px-filter-den">
                    </div>
                    <div class="col-md-2">
                        <button class="btn btn-primary btn-sm" onclick="loadPhieuXuat(1)"><i class="fas fa-filter me-1"></i>Lọc</button>
                        <a href="{{ url('admin/kho-hang') }}" class="btn btn-light border btn-sm"><i class="fas fa-redo"></i></a>
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
                                <th class="text-center">Ngày</th>
                                <th class="text-center">Tổng SL</th>
                                <th class="text-center">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody id="px-bang"></tbody>
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

    {{-- ============================================ CẢNH BÁO ============================================ --}}
    <div class="tab-pane fade" id="content-canh-bao" role="tabpanel">
        <div class="row g-4" id="canh-bao-content">
            <div class="text-center text-muted py-5 col-12">Đang tải cảnh báo...</div>
        </div>
    </div>

    {{-- ============================================ NHÀ CUNG CẤP ============================================ --}}
    <div class="tab-pane fade" id="content-ncc" role="tabpanel">
        <div class="d-flex justify-content-between mb-3 gap-2">
            <div class="input-group input-group-sm" style="max-width:300px">
                <input type="text" class="form-control" id="ncc-search" placeholder="Tìm tên NCC...">
                <button class="btn btn-outline-primary" id="ncc-btn-search" type="button"><i class="fas fa-search"></i></button>
                <button class="btn btn-outline-secondary" id="ncc-btn-refresh" type="button"><i class="fas fa-redo"></i></button>
            </div>
            <button class="btn btn-primary btn-sm" id="ncc-btn-them"><i class="fas fa-plus me-1"></i>Thêm nhà cung cấp</button>
        </div>
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
                                <th class="text-center">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody id="ncc-bang"></tbody>
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

</div>

@endsection

@section('modals')

{{-- ===================== MODAL: THÊM NHÀ CUNG CẤP ===================== --}}
<div class="modal fade" id="modal-them-ncc" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="form-them-ncc" autocomplete="off">@csrf
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
            <form id="form-sua-ncc" autocomplete="off">@csrf @method('PUT')
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

{{-- ===================== MODAL: TẠO PHIẾU NHẬP ===================== --}}
<div class="modal fade" id="modal-tao-pn" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <form id="form-tao-pn">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title"><i class="fas fa-arrow-down me-2"></i><span id="pn-modal-title">Tạo phiếu nhập</span></h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="pn-id">
                    {{-- Search product form --}}
                    <div class="row g-2 mb-3">
                        <div class="col-md-5">
                            <input type="text" id="pn-sp-search" class="form-control" placeholder="Tìm mã vạch, tên sản phẩm...">
                        </div>
                        <div class="col-md-4">
                            <select id="pn-sp-danh-muc" class="form-select">
                                <option value="">-- Tất cả danh mục --</option>
                            </select>
                        </div>
                        <div class="col-md-3 text-end">
                            <button type="button" class="btn btn-outline-secondary btn-sm" id="pn-sp-clear"><i class="fas fa-times me-1"></i>Xóa</button>
                        </div>
                    </div>
                    <div id="pn-sp-results" class="mb-3" style="max-height:200px;overflow-y:auto;border:1px solid #dee2e6;border-radius:4px;padding:8px;">
                        <div class="text-center text-muted py-4"><i class="fas fa-search fs-4 mb-2 d-block"></i>Nhập tên hoặc mã vạch để tìm sản phẩm</div>
                    </div>
                    <hr>
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
                                @foreach($nhaCungCaps as $ncc)
                                    <option value="{{ $ncc->id }}">{{ $ncc->ten_nha_cung_cap }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Ghi chú</label>
                            <textarea name="ghi_chu" class="form-control" rows="1" id="pn-ghi-chu" placeholder="Ghi chú..."></textarea>
                        </div>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="mb-0 small">Chi tiết sản phẩm</h6>
                        <button type="button" class="btn btn-sm btn-outline-primary" id="pn-btn-them-sp"><i class="fas fa-plus me-1"></i>Thêm</button>
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
                            <tbody id="pn-ds-sp"></tbody>
                        </table>
                    </div>
                    <div id="pn-alert-container"></div>
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
                    <h5 class="modal-title"><i class="fas fa-arrow-up me-2"></i><span id="px-modal-title">Tạo phiếu xuất</span></h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="px-id">
                    <div class="alert alert-warning mb-3 py-2 small">
                        <i class="fas fa-info-circle me-1"></i>
                        Hệ thống sẽ tự động trừ kho theo nguyên tắc <strong>FEFO</strong> — ưu tiên lô có HSD gần nhất.
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label">Loại xuất <span class="text-danger">*</span></label>
                            <select name="loai_xuat" class="form-select" id="px-loai" required>
                                <option value="tieu_huy">Tiêu hủy</option>
                                <option value="tra_hang_nha_cung_cap">Trả hàng NCC</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Nhà cung cấp</label>
                            <select name="id_nha_cung_cap" id="px-ncc" class="form-select">
                                <option value="">-- Chọn NCC --</option>
                                @foreach($nhaCungCaps as $ncc)
                                    <option value="{{ $ncc->id }}">{{ $ncc->ten_nha_cung_cap }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Lý do</label>
                            <input type="text" name="ly_do" class="form-control" id="px-ly-do" placeholder="Lý do xuất hàng...">
                        </div>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="mb-0 small">Chi tiết sản phẩm</h6>
                        <button type="button" class="btn btn-sm btn-outline-primary" id="px-btn-them-sp"><i class="fas fa-plus me-1"></i>Thêm</button>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="width:35%">Sản phẩm</th>
                                    <th class="text-center" style="width:100px">SL tồn</th>
                                    <th style="width:30%">Lô hàng (FEFO)</th>
                                    <th class="text-center" style="width:110px">SL xuất</th>
                                    <th class="text-center" style="width:60px"></th>
                                </tr>
                            </thead>
                            <tbody id="px-ds-sp"></tbody>
                        </table>
                    </div>
                    <div class="mt-2 text-end">
                        <span class="fw-bold">Tổng SL: <span id="px-tong-sl">0</span></span>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-danger"><i class="fas fa-save me-1"></i>Lưu phiếu xuất</button>
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
                <button type="button" class="btn btn-sm btn-success" id="pxn-export-btn"><i class="fas fa-download me-1"></i>Xuất Excel</button>
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
                <button type="button" class="btn btn-sm btn-danger" id="pxx-export-btn"><i class="fas fa-download me-1"></i>Xuất Excel</button>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="modal-xem-px-body"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
            </div>
        </div>
    </div>
</div>

{{-- ===================== MODAL: THÊM LÔ HÀNG ===================== --}}
<div class="modal fade" id="modal-them-lo" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="form-them-lo">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="fas fa-layer-group me-2"></i>Thêm lô hàng</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nhà cung cấp</label>
                        <select name="id_nha_cung_cap" id="lo-ncc" class="form-select">
                            <option value="">-- Chọn NCC --</option>
                            @foreach($nhaCungCaps as $ncc)
                                <option value="{{ $ncc->id }}">{{ $ncc->ten_nha_cung_cap }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Ngày nhập</label>
                        <input type="date" name="ngay_nhap" id="lo-ngay-nhap" class="form-control" value="{{ date('Y-m-d') }}">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Lưu</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ===================== MODAL: IMPORT PHIẾU NHẬP ===================== --}}
<div class="modal fade" id="modal-import-pn" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="fas fa-file-import me-2"></i>Import Phiếu Nhập từ Excel</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="form-import-pn">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Loại nhập <span class="text-danger">*</span></label>
                        <select name="loai_nhap" class="form-select" id="import-pn-loai" required>
                            <option value="mua_hang">Nhập mua hàng</option>
                            <option value="tra_lai_tu_khach">Trả lại từ khách</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nhà cung cấp</label>
                        <select name="id_nha_cung_cap" id="import-pn-ncc" class="form-select">
                            <option value="">-- Chọn NCC --</option>
                            @foreach($nhaCungCaps as $ncc)
                                <option value="{{ $ncc->id }}">{{ $ncc->ten_nha_cung_cap }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Ghi chú</label>
                        <input type="text" name="ghi_chu" id="import-pn-ghi-chu" class="form-control" placeholder="Ghi chú phiếu nhập...">
                    </div>
                    <hr>
                    <div class="mb-3">
                        <label class="form-label">Chọn file Excel <span class="text-danger">*</span></label>
                        <input type="file" id="import-pn-file" accept=".xlsx,.xls,.csv" class="form-control" required>
                        <small class="text-muted">Hỗ trợ định dạng .xlsx, .xls, .csv</small>
                    </div>
                    <div class="drop-zone border rounded p-4 text-center" id="drop-zone-pn" style="border-style: dashed; background: #f8f9fa;">
                        <i class="fas fa-cloud-upload-alt fa-2x text-muted mb-2"></i>
                        <p class="mb-0 text-muted">Kéo thả file Excel vào đây</p>
                        <small class="text-muted">hoặc nhấn "Chọn file" ở trên</small>
                    </div>
                    <div id="import-pn-preview" class="mt-2 d-none">
                        <div class="alert alert-info py-2 mb-0 d-flex align-items-center">
                            <i class="fas fa-file-excel me-2 text-success"></i>
                            <span id="import-pn-filename"></span>
                            <button type="button" class="btn-close ms-auto" id="import-pn-remove"></button>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="button" class="btn btn-outline-primary" id="import-pn-download-template"><i class="fas fa-download me-1"></i>Tải file mẫu</button>
                    <button type="submit" class="btn btn-success" id="import-pn-submit" disabled><i class="fas fa-upload me-1"></i>Import</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ===================== MODAL: IMPORT PHIẾU XUẤT ===================== --}}
<div class="modal fade" id="modal-import-px" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="fas fa-file-import me-2"></i>Import Phiếu Xuất từ Excel</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="form-import-px">
                <div class="modal-body">
                    <div class="alert alert-warning mb-3 py-2 small">
                        <i class="fas fa-info-circle me-1"></i>
                        Hệ thống sẽ tự động trừ kho theo nguyên tắc <strong>FEFO</strong> — ưu tiên lô có HSD gần nhất.
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Loại xuất <span class="text-danger">*</span></label>
                        <select name="loai_xuat" class="form-select" id="import-px-loai" required>
                            <option value="tieu_huy">Tiêu hủy</option>
                            <option value="tra_hang_nha_cung_cap">Trả hàng NCC</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nhà cung cấp</label>
                        <select name="id_nha_cung_cap" id="import-px-ncc" class="form-select">
                            <option value="">-- Chọn NCC --</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Lý do</label>
                        <input type="text" name="ly_do" id="import-px-ly-do" class="form-control" placeholder="Lý do xuất hàng...">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Ghi chú</label>
                        <input type="text" name="ghi_chu" id="import-px-ghi-chu" class="form-control" placeholder="Ghi chú...">
                    </div>
                    <hr>
                    <div class="mb-3">
                        <label class="form-label">Chọn file Excel <span class="text-danger">*</span></label>
                        <input type="file" id="import-px-file" accept=".xlsx,.xls,.csv" class="form-control" required>
                        <small class="text-muted">Hỗ trợ định dạng .xlsx, .xls, .csv</small>
                    </div>
                    <div class="drop-zone border rounded p-4 text-center" id="drop-zone-px" style="border-style: dashed; background: #f8f9fa;">
                        <i class="fas fa-cloud-upload-alt fa-2x text-muted mb-2"></i>
                        <p class="mb-0 text-muted">Kéo thả file Excel vào đây</p>
                        <small class="text-muted">hoặc nhấn "Chọn file" ở trên</small>
                    </div>
                    <div id="import-px-preview" class="mt-2 d-none">
                        <div class="alert alert-info py-2 mb-0 d-flex align-items-center">
                            <i class="fas fa-file-excel me-2 text-success"></i>
                            <span id="import-px-filename"></span>
                            <button type="button" class="btn-close ms-auto" id="import-px-remove"></button>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="button" class="btn btn-outline-primary" id="import-px-download-template"><i class="fas fa-download me-1"></i>Tải file mẫu</button>
                    <button type="submit" class="btn btn-danger" id="import-px-submit" disabled><i class="fas fa-upload me-1"></i>Import</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script src="{{ asset('js/admin/kho-hang.js') }}"></script>
@endsection

@section('styles')
<link rel="stylesheet" href="{{ asset('css/admin/kho-hang.css') }}">
@endsection
