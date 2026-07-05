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
            </ol></nav>
</div>
</div>

{{-- Stats Row --}}

<div class="row g-3 mb-4" id="stats-row">

<div class="col-xl-3 col-md-6">

<div class="card card-stat blue h-100">

<div class="card-body d-flex align-items-center">

<div class="icon me-3">

<i class="fas fa-layer-group"></i>
</div>

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

<div class="icon me-3">

<i class="fas fa-check-circle"></i>
</div>

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

<div class="icon me-3">

<i class="fas fa-clock"></i>
</div>

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

<div class="icon me-3">

<i class="fas fa-exclamation-triangle"></i>
</div>

<div>
                    
<h6 class="text-muted mb-1">Dưới định mức</h6>
                    
<h3 class="mb-0" id="stat-duoi-dinh-muc">--</h3>
</div>
</div>
</div>
</div>
</div>

{{-- Alert container --}}

<div id="kh-alert-container">
</div>

{{-- Tabs Navigation --}}

<ul class="nav nav-tabs mb-3" id="kho-hang-tabs" role="tablist">    
<li class="nav-item" role="presentation">        
<button class="nav-link active" id="tab-ton-kho" data-bs-toggle="tab" data-bs-target="#panel-ton-kho" type="button" role="tab">            
<i class="fas fa-boxes-stacked me-1"></i>Tồn kho</button>
</li>
    
<li class="nav-item" role="presentation">        
<button class="nav-link" id="tab-lo-hang" data-bs-toggle="tab" data-bs-target="#panel-lo-hang" type="button" role="tab">            
<i class="fas fa-layer-group me-1"></i>Nhập hàng</button>
</li>
    
<li class="nav-item" role="presentation">        
<button class="nav-link" id="tab-phieu-xuat" data-bs-toggle="tab" data-bs-target="#panel-phieu-xuat" type="button" role="tab">            
<i class="fas fa-arrow-up me-1"></i>Xuất hàng</button>
</li>
    
<li class="nav-item" role="presentation">        
<button class="nav-link" id="tab-canh-bao" data-bs-toggle="tab" data-bs-target="#panel-canh-bao" type="button" role="tab">            
<i class="fas fa-triangle-exclamation me-1"></i>Cảnh báo            
<span class="badge bg-danger ms-1" id="badge-canh-bao" style="display:none">0</span>
</button>
</li>
    
<li class="nav-item" role="presentation">        
<button class="nav-link" id="tab-ncc" data-bs-toggle="tab" data-bs-target="#panel-ncc" type="button" role="tab">            
<i class="fas fa-truck me-1"></i>Nhà cung cấp</button>
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
<option value=">Tất cả</option>
                            
<option value="binh-thuong">Bình thường</option>
                            
<option value="duoi-dinh-muc">Dưới định mức</option>
                            
<option value="het-hang">Hết hàng</option>
</select>
</div>

<div class="col-md-2">
                        
<button class="btn btn-primary btn-sm w-100" id="tk-btn-loc">                            
<i class="fas fa-filter me-1"></i>Lọc</button>
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
<th style="width:40px">
</th>
                                
<th>Mã SP</th>
                                
<th>Tên sản phẩm</th>
                                
<th class="text-center">Tồn kho</th>
                                
<th class="text-center">Định mức</th>
                                
<th class="text-center">Trạng thái</th>
                                
<th class="text-center" style="width:100px">Thao tác</th>
</tr>
</thead>
                        
<tbody id="tk-bang">                            
<tr><td colspan="7" class="text-center text-muted py-4">Đang tải...</td>
</tr>
</tbody>
</table>
</div>
</div>

<div class="card-footer bg-white">

<div class="d-flex justify-content-between align-items-center">
                    
<span class="text-muted small" id="tk-thong-tin">
</span>
                    
<nav id="tk-phan-trang">
</nav>
</div>
</div>
</div>
</div>
    
{{-- =========================== TAB 2: NHẬP HÀNG =========================== --}}

<div class="tab-pane fade" id="panel-lo-hang" role="tabpanel">

<div class="d-flex justify-content-end mb-3 gap-2">
            
<a href="{{ url('admin/kho-hang/phieu-nhap') }}" class="btn btn-outline-secondary btn-sm">                
<i class="fas fa-external-link-alt me-1"></i>Trang riêng            </a>            
<button class="btn btn-success btn-sm" id="pn-btn-tao">                
<i class="fas fa-plus me-1"></i>Tạo nhập hàng</button>
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
<i class="fas fa-filter me-1"></i>Lọc</button>
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
<tr><td colspan="9" class="text-center text-muted py-4">Đang tải...</td>
</tr>
</tbody>
</table>
</div>
</div>

<div class="card-footer bg-white">

<div class="d-flex justify-content-between align-items-center">
                    
<span class="text-muted small" id="pn-thong-tin">
</span>
                    
<nav id="pn-phan-trang">
</nav>
</div>
</div>
</div>
</div>
    
{{-- =========================== TAB 4: PHIẾU XUẤT =========================== --}}

<div class="tab-pane fade" id="panel-phieu-xuat" role="tabpanel">

<div class="d-flex justify-content-end mb-3 gap-2">
            
<a href="{{ url('admin/kho-hang/phieu-xuat') }}" class="btn btn-outline-secondary btn-sm">                
<i class="fas fa-external-link-alt me-1"></i>Trang riêng            </a>            
<button class="btn btn-danger btn-sm" id="px-btn-tao">                
<i class="fas fa-plus me-1"></i>Tạo phiếu xuất</button>
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
<i class="fas fa-filter me-1"></i>Lọc</button>
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
<tr><td colspan="9" class="text-center text-muted py-4">Đang tải...</td>
</tr>
</tbody>
</table>
</div>
</div>

<div class="card-footer bg-white">

<div class="d-flex justify-content-between align-items-center">
                    
<span class="text-muted small" id="px-thong-tin">
</span>
                    
<nav id="px-phan-trang">
</nav>
</div>
</div>
</div>
</div>
    
{{-- =========================== TAB 5: CẢNH BÁO =========================== --}}

<div class="tab-pane fade" id="panel-canh-bao" role="tabpanel">

<div class="row g-4" id="canh-bao-content">

<div class="text-center text-muted py-5 col-12">
Đang tải cảnh báo...</div>
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
<i class="fas fa-trash-alt me-1"></i>Thùng rác                </a>                
<button class="btn btn-primary btn-sm" id="ncc-btn-them">                    
<i class="fas fa-plus me-1"></i>Thêm NCC</button>
</div>
</div>
        
{{-- Alert container --}}

<div id="ncc-alert-container">
</div>
        
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
<tr><td colspan="7" class="text-center text-muted py-4">Đang tải...</td>
</tr>
</tbody>
</table>
</div>
</div>

<div class="card-footer bg-white">

<div class="d-flex justify-content-between align-items-center">
                    
<span class="text-muted small" id="ncc-thong-tin">
</span>
                    
<nav id="ncc-phan-trang">
</nav>
</div>
</div>
</div>
</div>
</div>

{{-- end tab-content --}}

{{-- ===================== MODAL: TẠO NHẬP HÀNG ===================== --}}

<div class="modal fade" id="modal-tao-pn" tabindex="-1">

<div class="modal-dialog modal-xl">

<div class="modal-content">
            
<form id="form-tao-pn">

<div class="modal-header bg-success text-white">
                    
<h5 class="modal-title"><i class="fas fa-arrow-down me-2"></i>Tạo nhập hàng</h5>
                    
<button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal">
</button>
</div>

<div class="modal-body">

<div id="pn-alert-container"></div>

<div class="row g-3 mb-3">

<div class="col-md-4">
                            
<label class="form-label">Loại nhập hàng 
<span class="text-danger">*</span>
</label>
                            
<select name="loai_nhap" class="form-select" id="pn-loai" required>                                
<option value="mua_hang">Nhập hàng NCC</option>
                                
<option value="tra_lai_tu_khach">Nhập hàng khách hoàn trả</option>
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
                                
<i class="fas fa-search fs-4 mb-2 d-block"></i>                                Nhập tên hoặc mã vạch để tìm sản phẩm</div>
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
<th style="width:30%">Sản phẩm</th>
                                    
<th style="width:22%">Đơn vị nhập <span class="text-danger">*</span></th>

<th style="width:90px">SL nhập</th>
                                    
<th style="width:110px">Giá nhập</th>
                                    
<th style="width:130px">Hạn sử dụng</th>
                                    
<th style="width:40px">
</th>
</tr>
</thead>
                            
<tbody id="pn-ds-sp">                                <tr id="pn-empty-row"><td colspan="6" class="text-center text-muted py-3">Chưa chọn sản phẩm nào.</td>
</tr>
</tbody>
</table>
</div>
</div>

<div class="modal-footer">
                    
<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    
<button type="button" id="pn-btn-save" class="btn btn-success"><i class="fas fa-save me-1"></i>Lưu nhập hàng</button>
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
                    
<button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal">
</button>
</div>

<div class="modal-body">

<div class="alert alert-info mb-3 py-2 small">
                        
<i class="fas fa-info-circle me-1"></i>                        Chọn <strong>sản phẩm</strong>, sau đó chọn <strong>lô hàng cụ thể</strong> (mỗi lô có HSD và tồn kho riêng).</div>

<div class="row g-3 mb-3">

<div class="col-md-4">
                            
<label class="form-label">Loại xuất 
<span class="text-danger">*</span>
</label>
                            
<select name="loai_xuat" class="form-select" id="px-loai" required>                                
<option value="tra_hang_nha_cung_cap">Trả hàng NCC</option>
                                
<option value="tieu_huy">Tiêu hủy</option>
</select>
</div>

<div class="col-md-4">
                            
<label class="form-label">Nhà cung cấp</label>
                            
<select name="id_nha_cung_cap" id="px-ncc" class="form-select">                                
<option value=">-- Chọn NCC --</option>
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
<i class="fas fa-plus me-1"></i>Thêm sản phẩm</button>
</div>

<div class="table-responsive">
                        
<table class="table table-sm table-bordered mb-2">                            

<thead class="table-light">                                
<tr>                                    
<th>Sản phẩm</th>
                                    
<th class="text-center" style="width:100px">Tồn kho</th>
                                    
<th style="width:240px">Lô hàng (HSD - Tồn)</th>
                                    
<th class="text-center" style="width:90px">SL xuất</th>
                                    
<th style="width:40px">
</th>
</tr>
</thead>
                            
<tbody id="px-ds-sp">
</tbody>
</table>
</div>

<div id="px-fefo-preview">
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

{{-- ===================== MODAL: TẠO LÔ HÀNG ===================== --}}

<div class="modal fade" id="modal-tao-lo" tabindex="-1">

<div class="modal-dialog modal-xl">

<div class="modal-content">
            
<form id="form-tao-lo">

<div class="modal-header bg-primary text-white">
                    
<h5 class="modal-title"><i class="fas fa-layer-group me-2"></i>Tạo lô hàng mới</h5>
                    
<button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal">
</button>
</div>

<div class="modal-body">

<div class="row g-3 mb-3">

<div class="col-md-4">
                            
<label class="form-label">Nhà cung cấp</label>
                            
<select name="id_nha_cung_cap" id="lo-ncc" class="form-select">                                
<option value=">-- Chọn NCC --</option>
</select>
</div>

<div class="col-md-4">
                            
<label class="form-label">Mã lô (tùy chọn)</label>

<input type="text" name="ma_lo" class="form-control" id="lo-ma" placeholder="Tự động nếu để trống">
</div>

<div class="col-md-4">
                            
<label class="form-label">Ngày nhập 
<span class="text-danger">*</span>
</label>

<input type="date" name="ngay_nhap" class="form-control" id="lo-ngay" value="{{ date('Y-m-d') }}" required>
</div>

<div class="col-12">
                            
<label class="form-label">Ghi chú</label>
                            
<textarea name="ghi_chu" class="form-control" rows="2" id="lo-ghi-chu" placeholder="Ghi chú lô hàng...">
</textarea>
</div>
</div>

<div class="d-flex justify-content-between align-items-center mb-2">
                        
<h6 class="mb-0 small">Chi tiết sản phẩm trong lô</h6>
                        
<button type="button" class="btn btn-sm btn-outline-primary" id="lo-btn-them-sp">                            
<i class="fas fa-plus me-1"></i>Thêm sản phẩm</button>
</div>

<div class="table-responsive">
                        
<table class="table table-sm table-bordered mb-0">                            

<thead class="table-light">                                
<tr>                                    
<th style="width:35%">Sản phẩm</th>
                                    
<th style="width:90px">SL nhập</th>
                                    
<th style="width:110px">Giá nhập</th>
                                    
<th style="width:130px">Hạn sử dụng</th>
                                    
<th style="width:40px">
</th>
</tr>
</thead>
                            
<tbody id="lo-ds-sp">
</tbody>
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
                
<button type="button" class="btn-close" data-bs-dismiss="modal">
</button>
</div>

<div class="modal-body" id="modal-xem-pn-body">
</div>

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
                
<button type="button" class="btn-close" data-bs-dismiss="modal">
</button>
</div>

<div class="modal-body" id="modal-xem-px-body">
</div>

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
            
<form id="form-them-ncc" autocomplete="off">@csrf
<div class="modal-header">
                    
<h5 class="modal-title"><i class="fas fa-plus me-2"></i>Thêm nhà cung cấp</h5>
                    
<button type="button" class="btn-close" data-bs-dismiss="modal">
</button>
</div>

<div class="modal-body">

<div class="mb-3">
                        
<label class="form-label">Tên nhà cung cấp 
<span class="text-danger">*</span>
</label>

<input type="text" name="ten_nha_cung_cap" id="ncc_them_ten" class="form-control" required>

<div class="invalid-feedback" id="ncc_them_ten_err">
</div>
</div>

<div class="mb-3">
                        
<label class="form-label">Email</label>

<input type="email" name="email" id="ncc_them_email" class="form-control">

<div class="invalid-feedback" id="ncc_them_email_err">
</div>
</div>

<div class="mb-3">
                        
<label class="form-label">Số điện thoại 
<span class="text-danger">*</span>
</label>

<input type="text" name="so_dien_thoai" id="ncc_them_sdt" class="form-control" required>

<div class="invalid-feedback" id="ncc_them_sdt_err">
</div>
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
            
<form id="form-sua-ncc" autocomplete="off">@csrf
@method('PUT')
<div class="modal-header">
                    
<h5 class="modal-title"><i class="fas fa-edit me-2"></i>Sửa nhà cung cấp</h5>
                    
<button type="button" class="btn-close" data-bs-dismiss="modal">
</button>
</div>

<div class="modal-body">

<input type="hidden" id="ncc_sua_id">

<div class="mb-3">
                        
<label class="form-label">Tên nhà cung cấp 
<span class="text-danger">*</span>
</label>

<input type="text" name="ten_nha_cung_cap" id="ncc_sua_ten" class="form-control" required>

<div class="invalid-feedback" id="ncc_sua_ten_err">
</div>
</div>

<div class="mb-3">
                        
<label class="form-label">Email</label>

<input type="email" name="email" id="ncc_sua_email" class="form-control">

<div class="invalid-feedback" id="ncc_sua_email_err">
</div>
</div>

<div class="mb-3">
                        
<label class="form-label">Số điện thoại 
<span class="text-danger">*</span>
</label>

<input type="text" name="so_dien_thoai" id="ncc_sua_sdt" class="form-control" required>

<div class="invalid-feedback" id="ncc_sua_sdt_err">
</div>
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
                
<button type="button" class="btn-close" data-bs-dismiss="modal">
</button>
</div>

<div class="modal-body" id="modal-xem-lo-body">
</div>

<div class="modal-footer">
                
<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
</div>
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