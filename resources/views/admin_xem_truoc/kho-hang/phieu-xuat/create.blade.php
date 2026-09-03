@extends('admin_xem_truoc.layouts.admin')

@section('title', 'Tạo phiếu xuất - SmartMart')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1"><i class="fas fa-arrow-up me-2 text-danger"></i>Tạo phiếu xuất</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('admin/dashboard') }}">Admin</a></li>
                <li class="breadcrumb-item"><a href="{{ url('admin/kho-hang') }}">Kho hàng</a></li>
                <li class="breadcrumb-item"><a href="{{ url('admin/kho-hang/phieu-xuat') }}">Phiếu xuất</a></li>
                <li class="breadcrumb-item active">Tạo phiếu xuất</li>
            </ol>
        </nav>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ url('admin/kho-hang/phieu-xuat') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>Quay lại danh sách
        </a>
    </div>
</div>

<div id="px-alert-container-page"></div>

{{-- ==================== KHỐI 1: THÔNG TIN CHUNG ==================== --}}
<div class="card table-admin mb-4">
    <div class="card-header bg-white">
        <h5 class="mb-0"><i class="fas fa-info-circle me-2 text-primary"></i>Thông tin chung</h5>
    </div>
    <div class="card-body">
        <form id="form-tao-px-page" autocomplete="off">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Loại xuất <span class="text-danger">*</span></label>
                    <select name="loai_xuat" class="form-select" id="px-loai" required>
                        <option value="tieu_huy">Tiêu hủy</option>
                        <option value="tra_hang_nha_cung_cap">Trả hàng nhà cung cấp</option>
                    </select>
                </div>
                <div class="col-md-4" id="px-ncc-wrap">
                    <label class="form-label">Nhà cung cấp <span class="text-danger" id="px-ncc-required">*</span></label>
                    <select name="id_nha_cung_cap" id="px-ncc" class="form-select">
                        <option value="">-- Chọn NCC --</option>
                        @foreach($nhaCungCaps as $ncc)
                            <option value="{{ $ncc->id }}">{{ $ncc->ten_nha_cung_cap }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Lý do xuất <span class="text-danger" id="px-ly-do-required">*</span></label>
                    <textarea name="ly_do" class="form-control" rows="1" id="px-ly-do" placeholder="Lý do xuất kho..." required></textarea>
                </div>
                <div class="col-12">
                    <label class="form-label">Ghi chú</label>
                    <textarea name="ghi_chu" class="form-control" rows="1" id="px-ghi-chu" placeholder="Ghi chú phiếu xuất..."></textarea>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- ==================== KHỐI 2: CHI TIẾT SẢN PHẨM ==================== --}}
<div class="card table-admin mb-5">
    <div class="card-header bg-white">
        <h5 class="mb-0"><i class="fas fa-boxes me-2 text-danger"></i>Chi tiết sản phẩm</h5>
    </div>
    <div class="card-body">

        {{-- Thanh tìm kiếm nổi bật --}}
        <div class="row g-2 mb-3">
            <div class="col-md-4">
                <div class="input-group input-group-lg">
                    <span class="input-group-text bg-danger text-white">
                        <i class="fas fa-search"></i>
                    </span>
                    <input type="text" id="px-sp-search" class="form-control"
                        placeholder="Tìm mã vạch, tên sản phẩm..." autocomplete="off">
                </div>
            </div>
            <div class="col-md-3">
                <select id="px-sp-danh-muc" class="form-select form-select-lg">
                    <option value="">-- Tất cả danh mục --</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="button" class="btn btn-outline-primary btn-lg w-100" id="px-btn-chon-lo">
                    <i class="fas fa-layer-group me-1"></i>Chọn lô
                </button>
            </div>
            <div class="col-md-3 text-end">
                <button type="button" class="btn btn-outline-secondary btn-lg w-100" id="px-sp-clear">
                    <i class="fas fa-times me-1"></i>Xóa tìm kiếm
                </button>
            </div>
        </div>

        {{-- Kết quả tìm kiếm sản phẩm --}}
        <div id="px-sp-results" class="mb-4"
            style="max-height:280px;overflow-y:auto;border:1px solid #dee2e6;border-radius:6px;padding:8px;">
            <div class="text-center text-muted py-4">
                <i class="fas fa-search fs-3 mb-2 d-block"></i>
                Nhập tên hoặc mã vạch để tìm sản phẩm
            </div>
        </div>

        <hr>

        {{-- Bảng sản phẩm đã chọn --}}
        <div class="d-flex justify-content-between align-items-center mb-2">
            <h6 class="mb-0 small text-uppercase text-muted fw-semibold">Danh sách sản phẩm xuất</h6>
            <span class="badge bg-danger fs-6">
                Tổng: <span id="px-tong-sl">0</span> sản phẩm
            </span>
        </div>
        <div class="table-responsive">
            <table class="table table-bordered align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width:30%">Sản phẩm</th>
                        <th style="width:80px">Tồn</th>
                        <th style="width:100px">SL xuất</th>
                        <th style="width:200px">Lô hàng</th>
                        <th style="width:130px">Hạn sử dụng</th>
                        <th style="width:60px"></th>
                    </tr>
                </thead>
                <tbody id="px-ds-sp">
                    <tr id="px-empty-row">
                        <td colspan="6" class="text-center text-muted py-3">
                            Chưa chọn sản phẩm nào. Vui lòng tìm kiếm và bấm "Chọn" phía trên.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- ==================== STICKY FOOTER ==================== --}}
<div class="card mt-4 shadow-sm border-0 sticky-bottom-action">
    <div class="card-body py-3 px-4">
        <div class="d-flex justify-content-between align-items-center">
            <span class="text-muted small">
                <i class="fas fa-info-circle me-1"></i>
                Vui lòng kiểm tra kỹ số lượng, lô hàng và hạn sử dụng trước khi lưu
            </span>
            <div class="d-flex gap-2">
                <a href="{{ url('admin/kho-hang/phieu-xuat') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-times me-1"></i>Hủy
                </a>
                <button type="button" class="btn btn-danger px-4" id="px-btn-save">
                    <i class="fas fa-save me-1"></i>Lưu phiếu xuất
                </button>
            </div>
        </div>
    </div>
</div>

{{-- ==================== MODAL CHỌN LÔ ==================== --}}
<div class="modal fade" id="modal-chon-lo" tabindex="-1" aria-labelledby="modalChonLoLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalChonLoLabel">
                    <i class="fas fa-layer-group me-2"></i>Chọn lô hàng để xuất
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row g-2 mb-3">
                    <div class="col-md-8">
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                            <input type="text" class="form-control" id="chon-lo-search" 
                                   placeholder="Tìm theo mã lô, tên nhà cung cấp...">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <select id="chon-lo-ncc-filter" class="form-select">
                            <option value="">-- Tất cả NCC --</option>
                            @foreach($nhaCungCaps as $ncc)
                                <option value="{{ $ncc->id }}">{{ $ncc->ten_nha_cung_cap }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="table-responsive" style="max-height: 500px;">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light sticky-top">
                            <tr>
                                <th style="width: 150px">Mã lô</th>
                                <th>Nhà cung cấp</th>
                                <th style="width: 120px">Ngày nhập</th>
                                <th style="width: 80px" class="text-center">Số SP</th>
                                <th style="width: 100px" class="text-center">Tổng tồn</th>
                                <th style="width: 100px" class="text-center">Hành động</th>
                            </tr>
                        </thead>
                        <tbody id="chon-lo-body">
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">
                                    <i class="fas fa-spinner fa-spin me-1"></i>Đang tải danh sách lô hàng...
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>Đóng
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('page_scripts')
<script src="{{ asset('js/admin/phieu-xuat-create.js') }}?v={{ filemtime(public_path('js/admin/phieu-xuat-create.js')) }}"></script>
@endsection

@section('styles')
<style>
    /* Sticky action bar ở footer (tương tự san-pham-create) */
    .sticky-bottom-action {
        position: sticky;
        bottom: 0;
        z-index: 100;
        border-radius: 8px;
        background: #fff;
        box-shadow: 0 -2px 8px rgba(0,0,0,0.06);
    }

    /* Làm nổi bật thanh tìm kiếm */
    #px-sp-search:focus {
        border-color: #dc3545;
        box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.15);
    }

    /* Chip thuộc tính biến thể */
    .variant-chip {
        font-size: 0.7rem;
        padding: 0.25em 0.55em;
        margin-right: 2px;
    }
</style>
@endsection
