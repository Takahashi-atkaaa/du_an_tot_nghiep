@extends('admin_xem_truoc.layouts.admin')

@section('title', 'Tạo phiếu nhập - SmartMart')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1"><i class="fas fa-arrow-down me-2 text-success"></i>Tạo phiếu nhập</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('admin/dashboard') }}">Admin</a></li>
                <li class="breadcrumb-item"><a href="{{ url('admin/kho-hang') }}">Kho hàng</a></li>
                <li class="breadcrumb-item active">Tạo phiếu nhập</li>
            </ol>
        </nav>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ url('admin/kho-hang') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>Quay lại danh sách
        </a>
    </div>
</div>

<div id="pn-alert-container-page"></div>

{{-- ==================== KHỐI 1: THÔNG TIN CHUNG ==================== --}}
<div class="card table-admin mb-4">
    <div class="card-header bg-white">
        <h5 class="mb-0"><i class="fas fa-info-circle me-2 text-primary"></i>Thông tin chung</h5>
    </div>
    <div class="card-body">
        <form id="form-tao-pn-page" autocomplete="off">
            <div class="row g-3">
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
                    <textarea name="ghi_chu" class="form-control" rows="1" id="pn-ghi-chu" placeholder="Ghi chú phiếu nhập..."></textarea>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- ==================== KHỐI 2: CHI TIẾT SẢN PHẨM ==================== --}}
<div class="card table-admin mb-5">
    <div class="card-header bg-white">
        <h5 class="mb-0"><i class="fas fa-boxes me-2 text-success"></i>Chi tiết sản phẩm</h5>
    </div>
    <div class="card-body">

        {{-- Thanh tìm kiếm nổi bật --}}
        <div class="row g-2 mb-3">
            <div class="col-md-6">
                <div class="input-group input-group-lg">
                    <span class="input-group-text bg-success text-white">
                        <i class="fas fa-search"></i>
                    </span>
                    <input type="text" id="pn-sp-search" class="form-control"
                        placeholder="Tìm mã vạch, tên sản phẩm..." autocomplete="off">
                </div>
            </div>
            <div class="col-md-4">
                <select id="pn-sp-danh-muc" class="form-select form-select-lg">
                    <option value="">-- Tất cả danh mục --</option>
                </select>
            </div>
            <div class="col-md-2 text-end">
                <button type="button" class="btn btn-outline-secondary btn-lg w-100" id="pn-sp-clear">
                    <i class="fas fa-times me-1"></i>Xóa
                </button>
            </div>
        </div>

        {{-- Kết quả tìm kiếm sản phẩm --}}
        <div id="pn-sp-results" class="mb-4"
            style="max-height:280px;overflow-y:auto;border:1px solid #dee2e6;border-radius:6px;padding:8px;">
            <div class="text-center text-muted py-4">
                <i class="fas fa-search fs-3 mb-2 d-block"></i>
                Nhập tên hoặc mã vạch để tìm sản phẩm
            </div>
        </div>

        <hr>

        {{-- Bảng sản phẩm đã chọn --}}
        <div class="d-flex justify-content-between align-items-center mb-2">
            <h6 class="mb-0 small text-uppercase text-muted fw-semibold">Danh sách sản phẩm nhập</h6>
            <span class="badge bg-success fs-6">
                Tổng: <span id="pn-tong-sl">0</span> sản phẩm
            </span>
        </div>
        <div class="table-responsive">
            <table class="table table-bordered align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width:35%">Sản phẩm</th>
                        <th style="width:120px">SL nhập</th>
                        <th style="width:140px">Giá nhập</th>
                        <th style="width:150px">Hạn sử dụng</th>
                        <th style="width:60px"></th>
                    </tr>
                </thead>
                <tbody id="pn-ds-sp">
                    <tr id="pn-empty-row">
                        <td colspan="5" class="text-center text-muted py-3">
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
                Vui lòng kiểm tra kỹ số lượng, đơn giá và hạn sử dụng trước khi lưu
            </span>
            <div class="d-flex gap-2">
                <a href="{{ url('admin/kho-hang') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-times me-1"></i>Hủy
                </a>
                <button type="button" class="btn btn-success px-4" id="pn-btn-save">
                    <i class="fas fa-save me-1"></i>Lưu phiếu nhập
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('page_scripts')
<script src="{{ asset('js/admin/phieu-nhap-create.js') }}?v={{ filemtime(public_path('js/admin/phieu-nhap-create.js')) }}"></script>
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
    #pn-sp-search:focus {
        border-color: #198754;
        box-shadow: 0 0 0 0.2rem rgba(25, 135, 84, 0.15);
    }

    /* Chip thuộc tính biến thể */
    .variant-chip {
        font-size: 0.7rem;
        padding: 0.25em 0.55em;
        margin-right: 2px;
    }
</style>
@endsection
