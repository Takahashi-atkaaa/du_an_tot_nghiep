@extends('admin_xem_truoc.layouts.admin')

@section('title', 'Quản lý Phiếu nhập - SmartMart')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1"><i class="fas fa-arrow-down me-2 text-success"></i>Quản lý Phiếu nhập</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('admin/dashboard') }}">Admin</a></li>
                <li class="breadcrumb-item"><a href="{{ url('admin/kho-hang') }}">Kho hàng</a></li>
                <li class="breadcrumb-item active">Phiếu nhập</li>
            </ol>
        </nav>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ url('admin/kho-hang') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-th-large me-1"></i>Hub kho hàng
        </a>
        <button class="btn btn-outline-success btn-sm" id="btn-xuat-excel-nhap" title="Xuất Excel">
            <i class="fas fa-file-excel me-1"></i>Xuất Excel
        </button>
        <button class="btn btn-success btn-sm" id="btn-tao-phieu-nhap">
            <i class="fas fa-plus me-1"></i>Tạo phiếu nhập
        </button>
        <button class="btn btn-success btn-sm" id="btn-open-import-nhap" title="Import từ Excel">
            <i class="fas fa-file-import me-1"></i>Import Excel
        </button>
    </div>
</div>

<div id="alert-container"></div>

{{-- Filter --}}
<div class="card table-admin mb-4">
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-3">
                <select class="form-select" id="filter-loai">
                    <option value="">Tất cả loại</option>
                    <option value="mua_hang">Nhập mua hàng</option>
                    <option value="tra_lai_tu_khach">Trả lại từ khách</option>
                </select>
            </div>
            <div class="col-md-2">
                <input type="date" class="form-control" id="filter-tu">
            </div>
            <div class="col-md-2">
                <input type="date" class="form-control" id="filter-den">
            </div>
            <div class="col-md-3">
                <button class="btn btn-primary" id="btn-loc-phieu-nhap"><i class="fas fa-filter me-1"></i>Lọc</button>
                <a href="{{ url('admin/kho-hang/phieu-nhap') }}" class="btn btn-light border"><i class="fas fa-redo"></i></a>
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
                        <th>ID</th>
                        <th>Mã phiếu</th>
                        <th>Loại</th>
                        <th>Nhà cung cấp</th>
                        <th>Người tạo</th>
                        <th class="text-center">Ngày</th>
                        <th class="text-center">Tổng giá trị</th>
                        <th>Ghi chú</th>
                        <th class="text-center" style="width:130px">Thao tác</th>
                    </tr>
                </thead>
                <tbody id="bang-phieu-nhap">
                    <tr><td colspan="9" class="text-center text-muted py-4">Đang tải...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer bg-white">
        <div class="d-flex justify-content-between align-items-center">
            <span class="text-muted small" id="thong-tin-hien-thi"></span>
            <nav id="phan-trang-nhap"></nav>
        </div>
    </div>
</div>
@endsection

{{-- MODALS --}}
@section('modals')
{{-- Modal Tạo --}}
<div class="modal fade" id="modal-tao-phieu-nhap" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <form id="form-tao-phieu-nhap">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title"><i class="fas fa-arrow-down me-2"></i>Tạo phiếu nhập</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label">Loại nhập <span class="text-danger">*</span></label>
                            <select name="loai_nhap" class="form-select" id="pn-loai-nhap" required>
                                <option value="mua_hang">Nhập mua hàng</option>
                                <option value="tra_lai_tu_khach">Trả lại từ khách</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Nhà cung cấp</label>
                            <select name="id_nha_cung_cap" id="pn-id-ncc" class="form-select">
                                <option value="">-- Chọn NCC --</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Ghi chú</label>
                            <textarea name="ghi_chu" class="form-control" rows="2" id="pn-ghi-chu" placeholder="Ghi chú..."></textarea>
                        </div>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label d-block">Cách tạo lô <span class="text-danger">*</span></label>
                            <div class="btn-group btn-group-sm" role="group" aria-label="Chon cach tao lo">
                                <input type="radio" class="btn-check" name="tao_lo_moi_ui" id="pn-tao-lo-moi" value="1" checked>
                                <label class="btn btn-outline-success" for="pn-tao-lo-moi"><i class="fas fa-plus-circle me-1"></i>Tạo lô mới</label>

                                <input type="radio" class="btn-check" name="tao_lo_moi_ui" id="pn-tao-lo-cu" value="0">
                                <label class="btn btn-outline-primary" for="pn-tao-lo-cu"><i class="fas fa-boxes-stacked me-1"></i>Thêm vào lô có sẵn</label>
                            </div>
                        </div>
                        <div class="col-md-8" id="pn-chon-lo-cu-wrapper" style="display:none;">
                            <label class="form-label">Chọn lô có sẵn <span class="text-danger">*</span></label>
                            <select id="pn-id-lo-hang" class="form-select">
                                <option value="">-- Đang tải --</option>
                            </select>
                            <small class="text-muted">Hệ thống sẽ cộng dồn số lượng nếu cùng (biến thể + HSD).</small>
                        </div>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="mb-0 small">Chi tiết sản phẩm</h6>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-sm btn-outline-success" id="btn-open-import-nhap-modal">
                                <i class="fas fa-file-import me-1"></i>Import Excel
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-primary" id="btn-them-sp-nhap"><i class="fas fa-plus me-1"></i>Thêm</button>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="width:30%">Sản phẩm / Biến thể</th>
                                    <th style="width:18%">Đơn vị nhập</th>
                                    <th style="width:80px">SL nhập</th>
                                    <th style="width:120px">Giá nhập</th>
                                    <th style="width:130px">Hạn sử dụng</th>
                                    <th style="width:40px"></th>
                                </tr>
                            </thead>
                            <tbody id="danh-sach-sp-nhap"></tbody>
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

{{-- Modal Sửa --}}
<div class="modal fade" id="modal-sua-phieu-nhap" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="form-sua-phieu-nhap">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title"><i class="fas fa-edit me-2"></i>Sửa phiếu nhập</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="sua-pn-id">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Loại nhập <span class="text-danger">*</span></label>
                            <select name="loai_nhap" class="form-select" id="sua-pn-loai" required>
                                <option value="mua_hang">Nhập mua hàng</option>
                                <option value="tra_lai_tu_khach">Trả lại từ khách</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Nhà cung cấp</label>
                            <select name="id_nha_cung_cap" id="sua-pn-ncc" class="form-select"></select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Ghi chú</label>
                            <textarea name="ghi_chu" class="form-control" rows="2" id="sua-pn-ghi-chu"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-warning text-dark"><i class="fas fa-save me-1"></i>Lưu thay đổi</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal Xem chi tiết --}}
<div class="modal fade" id="modal-xem-phieu-nhap" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-file-alt me-2 text-success"></i>Chi tiết phiếu nhập</h5>
                <button type="button" class="btn btn-sm btn-success" id="btn-export-chi-tiet-pn">
                    <i class="fas fa-download me-1"></i>Xuất Excel
                </button>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="modal-xem-phieu-nhap-body"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
            </div>
        </div>
    </div>
</div>

{{-- Modal Import Excel --}}
<div class="modal fade" id="modal-import-phieu-nhap" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="fas fa-file-import me-2"></i>Import Phiếu Nhập từ Excel</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="form-import-phieu-nhap">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Loại nhập <span class="text-danger">*</span></label>
                        <select name="loai_nhap" class="form-select" id="import-loai-nhap" required>
                            <option value="mua_hang">Nhập mua hàng</option>
                            <option value="tra_lai_tu_khach">Trả lại từ khách</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nhà cung cấp</label>
                        <select name="id_nha_cung_cap" id="import-id-ncc" class="form-select">
                            <option value="">-- Chọn NCC --</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Ghi chú</label>
                        <input type="text" name="ghi_chu" id="import-ghi-chu" class="form-control" placeholder="Ghi chú phiếu nhập...">
                    </div>
                    <hr>
                    <div class="mb-3">
                        <label class="form-label">Chọn file Excel <span class="text-danger">*</span></label>
                        <input type="file" id="import-file-nhap" accept=".xlsx,.xls,.csv" class="form-control" required>
                        <small class="text-muted">Hỗ trợ định dạng .xlsx, .xls, .csv</small>
                    </div>
                    <div class="drop-zone-nhap border rounded p-4 text-center" id="drop-zone-nhap" style="border-style: dashed; background: #f8f9fa;">
                        <i class="fas fa-cloud-upload-alt fa-2x text-muted mb-2"></i>
                        <p class="mb-0 text-muted">Kéo thả file Excel vào đây</p>
                        <small class="text-muted">hoặc nhấn "Chọn file" ở trên</small>
                    </div>
                    <div id="import-file-preview-nhap" class="mt-2 d-none">
                        <div class="alert alert-info py-2 mb-0 d-flex align-items-center">
                            <i class="fas fa-file-excel me-2 text-success"></i>
                            <span id="import-file-name-nhap"></span>
                            <button type="button" class="btn-close ms-auto" onclick="xoaFileImportNhap()"></button>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="button" class="btn btn-outline-primary" id="btn-download-template-nhap">
                        <i class="fas fa-download me-1"></i>Tải file mẫu
                    </button>
                    <button type="submit" class="btn btn-success" id="btn-submit-import-nhap" disabled>
                        <i class="fas fa-upload me-1"></i>Import
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
let sanPhamTreeNhap = [];
let sanPhamFlatNhap = [];
let chiTietNhapIndex = 0;
let currentPageNhap = 1;
let pnProductMap = {};

$(function () {
    taiSanPhamNhap();
    taiPhieuNhap();
    taiNhaCungCapNhap();

    $('#btn-tao-phieu-nhap').click(function () {
        chiTietNhapIndex = 0;
        $('#form-tao-phieu-nhap')[0].reset();
        $('input[name="tao_lo_moi_ui"][value="1"]').prop('checked', true);
        $('#pn-chon-lo-cu-wrapper').hide();
        $('#pn-id-lo-hang').html('<option value="">-- Đang tải --</option>');
        $('#danh-sach-sp-nhap').html('');
        addPnRow();
        new bootstrap.Modal(document.getElementById('modal-tao-phieu-nhap')).show();
    });

    $(document).on('click', '#btn-open-import-nhap-modal', function () {
        bootstrap.Modal.getInstance(document.getElementById('modal-tao-phieu-nhap')).hide();
        taiNhaCungCapNhap();
        $('#modal-import-phieu-nhap').modal('show');
    });

    $('#btn-them-sp-nhap').click(() => addPnRow());

    $(document).on('click', '.btn-remove-sp-nhap', function () {
        if ($('#danh-sach-sp-nhap tr').length > 1) $(this).closest('tr').remove();
    });

    // Đổi variant → cập nhật dropdown đơn vị + gợi ý đơn giá
    $(document).on('change', '.pn-sp-select', function () {
        const $row = $(this).closest('tr');
        rebuildDonViSelect($row);
        capNhatXemTruocQuyDoi($row);
    });

    // Đổi đơn vị / số lượng / giá → cập nhật xem trước
    $(document).on('input change', '.pn-sl-input, .pn-gia-input, .pn-dv-select', function () {
        capNhatXemTruocQuyDoi($(this).closest('tr'));
    });

    $('#form-tao-phieu-nhap').submit(function (e) {
        e.preventDefault();
        const data = layDuLieuFormNhap();
        if (!data.chi_tiet.length) { hienThongBao('warning', 'Thêm ít nhất một sản phẩm.'); return; }
        if (data.tao_lo_moi === '0' && !data.id_lo_hang) {
            hienThongBao('warning', 'Vui lòng chọn lô có sẵn để thêm vào.');
            return;
        }
        $.ajax({ url: '/admin/api/phieu-nhap', method: 'POST', contentType: 'application/json', data: JSON.stringify(data),
            success: res => {
                bootstrap.Modal.getInstance(document.getElementById('modal-tao-phieu-nhap')).hide();
                hienThongBao('success', res.message);
                taiPhieuNhap(currentPageNhap);
            },
            error: x => hienThongBao('danger', x.responseJSON?.message || 'Lỗi.')
        });
    });

    $('#form-sua-phieu-nhap').submit(function (e) {
        e.preventDefault();
        const id = $('#sua-pn-id').val();
        const data = {
            loai_nhap: $('#sua-pn-loai').val(),
            id_nha_cung_cap: $('#sua-pn-ncc').val() || null,
            ghi_chu: $('#sua-pn-ghi-chu').val(),
        };
        $.ajax({ url: '/admin/api/phieu-nhap/' + id, method: 'PUT', contentType: 'application/json', data: JSON.stringify(data),
            success: res => {
                bootstrap.Modal.getInstance(document.getElementById('modal-sua-phieu-nhap')).hide();
                hienThongBao('success', res.message);
                taiPhieuNhap(currentPageNhap);
            },
            error: x => hienThongBao('danger', x.responseJSON?.message || 'Lỗi.')
        });
    });

    $('#btn-loc-phieu-nhap').click(() => taiPhieuNhap(1));

    // ========== IMPORT EXCEL ==========
    let importFileNhap = null;
    let currentExportPnId = null;

    $('#btn-xuat-excel-nhap').click(function () {
        const loai = $('#filter-loai').val();
        const tuNgay = $('#filter-tu').val();
        const denNgay = $('#filter-den').val();
        let url = '/admin/api/phieu-nhap/export';
        const params = [];
        if (loai) params.push('loai_nhap=' + loai);
        if (tuNgay) params.push('tu_ngay=' + tuNgay);
        if (denNgay) params.push('den_ngay=' + denNgay);
        if (params.length) url += '?' + params.join('&');
        window.open(url, '_blank');
    });

    $('#btn-tao-phieu-nhap').click(function () {
        $('#modal-tao-phieu-nhap .btn-outline-success').hide();
    });

    $(document).on('click', '#btn-export-chi-tiet-pn', function () {
        if (currentExportPnId) {
            window.open('/admin/api/phieu-nhap/' + currentExportPnId + '/export', '_blank');
        }
    });

    $('#btn-download-template-nhap').click(function () {
        window.open('/admin/api/phieu-nhap/download-template', '_blank');
    });

    $('#import-file-nhap').change(function () {
        const file = this.files[0];
        if (file) {
            importFileNhap = file;
            $('#import-file-name-nhap').text(file.name);
            $('#import-file-preview-nhap').removeClass('d-none');
            $('#btn-submit-import-nhap').prop('disabled', false);
        }
    });

    $('#drop-zone-nhap').click(function () {
        $('#import-file-nhap').click();
    });

    $('#drop-zone-nhap').on('dragover', function (e) {
        e.preventDefault();
        $(this).css('background-color', '#e7f1ff');
    });

    $('#drop-zone-nhap').on('dragleave', function () {
        $(this).css('background-color', '#f8f9fa');
    });

    $('#drop-zone-nhap').on('drop', function (e) {
        e.preventDefault();
        $(this).css('background-color', '#f8f9fa');
        const file = e.originalEvent.dataTransfer.files[0];
        if (file && (file.name.endsWith('.xlsx') || file.name.endsWith('.xls') || file.name.endsWith('.csv'))) {
            importFileNhap = file;
            $('#import-file-nhap')[0].files = e.originalEvent.dataTransfer.files;
            $('#import-file-name-nhap').text(file.name);
            $('#import-file-preview-nhap').removeClass('d-none');
            $('#btn-submit-import-nhap').prop('disabled', false);
        } else {
            hienThongBaoNhap('danger', 'Vui lòng chọn file Excel (.xlsx, .xls, .csv)');
        }
    });

    $('#form-import-phieu-nhap').submit(function (e) {
        e.preventDefault();
        if (!importFileNhap) {
            hienThongBaoNhap('danger', 'Vui lòng chọn file Excel.');
            return;
        }

        const formData = new FormData();
        formData.append('file', importFileNhap);
        formData.append('loai_nhap', $('#import-loai-nhap').val());
        formData.append('id_nha_cung_cap', $('#import-id-ncc').val() || '');
        formData.append('ghi_chu', $('#import-ghi-chu').val() || '');

        $('#btn-submit-import-nhap').prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Đang import...');

        $.ajax({
            url: '/admin/api/phieu-nhap/import',
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            data: formData,
            processData: false,
            contentType: false,
            success: function (res) {
                if (res.success) {
                    bootstrap.Modal.getInstance(document.getElementById('modal-import-phieu-nhap')).hide();
                    hienThongBaoNhap('success', res.message);
                    taiPhieuNhap(1);
                    importFileNhap = null;
                    $('#form-import-phieu-nhap')[0].reset();
                    $('#import-file-preview-nhap').addClass('d-none');
                } else {
                    hienThongBaoNhap('danger', res.message);
                    if (res.errors && res.errors.length) {
                        console.error('Import errors:', res.errors);
                    }
                }
            },
            error: function (x) {
                const msg = x.responseJSON?.message || 'Import thất bại.';
                hienThongBaoNhap('danger', msg);
            },
            complete: function () {
                $('#btn-submit-import-nhap').prop('disabled', false).html('<i class="fas fa-upload me-1"></i>Import');
            }
        });
    });

    // Nút mở modal import trong modal tạo phiếu
    $(document).on('click', '#btn-open-import-nhap', function () {
        taiNhaCungCapNhap();
        $('#modal-import-phieu-nhap').modal('show');
    });
});

function taiSanPhamNhap() {
    $.get('/admin/api/san-pham', res => {
        sanPhamTreeNhap = res.data?.data || [];
        pnProductMap = {};
        sanPhamFlatNhap = [];
        sanPhamTreeNhap.forEach(sp => {
            (sp.bien_the || []).forEach(bt => {
                pnProductMap[bt.id] = {
                    variant: bt,
                    product: sp,
                    units: bt.units || [],
                };
                sanPhamFlatNhap.push(bt);
            });
        });
    });
}

function taiNhaCungCapNhap() {
    $.get('/admin/api/lo-hang/nha-cung-cap', res => {
        // API /admin/api/lo-hang/nha-cung-cap trả về {success, data: [...]};
        // các nơi khác đôi khi chỉ trả mảng — chuẩn hoá về mảng trước khi .map()
        const list = Array.isArray(res) ? res : (res?.data || []);
        const opts = list.map(n => `<option value="${n.id}">${n.ten_nha_cung_cap}</option>`).join('');
        $('#pn-id-ncc').html('<option value="">-- Chọn NCC --</option>' + opts);
        $('#sua-pn-ncc').html('<option value="">-- Chọn NCC --</option>' + opts);
    });
}

function variantOptionsHtml(selectedId) {
    if (!sanPhamTreeNhap.length) {
        return '<option value="">-- Đang tải sản phẩm --</option>';
    }
    const opts = [];
    sanPhamTreeNhap.forEach(sp => {
        const variants = sp.bien_the || [];
        variants.forEach(bt => {
            const tenBt = bt.ten_bien_the ? ` - ${bt.ten_bien_the}` : '';
            const code = bt.ma_vach || bt.id;
            const label = `${sp.ten_san_pham}${tenBt} (${code})`;
            opts.push(`<option value="${bt.id}" ${String(bt.id) === String(selectedId) ? 'selected' : ''}>${escapeHtml(label)}</option>`);
        });
    });
    return opts.join('');
}

function escapeHtml(s) {
    if (s === null || s === undefined) return '';
    return String(s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}

function addPnRow(id, sl, gia, hsd) {
    const idx = chiTietNhapIndex++;
    const variants = variantOptionsHtml(id);
    $('#danh-sach-sp-nhap').append(`<tr data-idx="${idx}">
        <td>
            <select class="form-select form-select-sm pn-sp-select" name="chi_tiet[${idx}][variant_id]">${variants}</select>
            <input type="hidden" class="pn-he-so-hidden" name="chi_tiet[${idx}][so_luong_san_pham_trong_don_vi]" value="1">
        </td>
        <td>
            <select class="form-select form-select-sm pn-dv-select" name="chi_tiet[${idx}][don_vi_id]">
                <option value="">-- Đang tải --</option>
            </select>
            <small class="text-muted pn-dv-hint d-block mt-1">Đơn vị cơ bản</small>
        </td>
        <td>
            <input type="number" class="form-control form-control-sm pn-sl-input" name="chi_tiet[${idx}][so_luong_nhap]" value="${sl || 1}" min="0.0001" step="0.0001">
            <small class="text-muted pn-sl-hint d-block mt-1">SL theo đơn vị đã chọn</small>
        </td>
        <td>
            <input type="text" class="form-control form-control-sm pn-gia-input money-input" name="chi_tiet[${idx}][gia_nhap]" value="${gia ? Number(gia).toLocaleString('vi-VN') : ''}" inputmode="numeric">
            <small class="text-muted pn-gia-hint d-block mt-1">đơn giá / đơn vị đã chọn</small>
        </td>
        <td><input type="date" class="form-control form-control-sm" name="chi_tiet[${idx}][han_su_dung]" value="${hsd || ''}"></td>
        <td><button type="button" class="btn btn-sm btn-outline-danger btn-remove-sp-nhap"><i class="fas fa-times"></i></button></td>
    </tr>`);
    if (id) {
        const $row = $('#danh-sach-sp-nhap tr').last();
        rebuildDonViSelect($row);
        capNhatXemTruocQuyDoi($row);
    } else {
        const $row = $('#danh-sach-sp-nhap tr').last();
        rebuildDonViSelect($row);
        capNhatXemTruocQuyDoi($row);
    }
}

function rebuildDonViSelect($row) {
    const variantId = $row.find('.pn-sp-select').val();
    const $dv = $row.find('.pn-dv-select');
    const $heSo = $row.find('.pn-he-so-hidden');
    const $gia = $row.find('.pn-gia-input');

    if (!variantId || !pnProductMap[variantId]) {
        $dv.html('<option value="">-- Chọn sản phẩm trước --</option>');
        $heSo.val(1);
        return;
    }
    const data = pnProductMap[variantId];
    const variant = data.variant;
    const baseUnitLabel = variant.ten_don_vi || 'đơn vị cơ bản';

    const opts = [`<option value="" data-he-so="1" data-ten="${escapeHtml(baseUnitLabel)}" selected>${escapeHtml(baseUnitLabel)} (đơn vị cơ bản)</option>`];
    (data.units || []).forEach(u => {
        const heSo = u.so_luong_san_pham_trong_don_vi || 1;
        opts.push(`<option value="${u.id}" data-he-so="${heSo}" data-ten="${escapeHtml(u.ten_don_vi)}">${escapeHtml(u.ten_don_vi)} (×${heSo})</option>`);
    });
    $dv.html(opts.join(''));
    $heSo.val(1);

    if (!$gia.val() || Number($gia.val()) === 0) {
        $gia.val(variant.gia_ban || 0);
    }
}

function capNhatXemTruocQuyDoi($row) {
    const sl = parseFloat($row.find('.pn-sl-input').val()) || 0;
    const gia = parseFloat($row.find('.pn-gia-input').val()) || 0;
    const $dv = $row.find('.pn-dv-select');
    const $heSo = $row.find('.pn-he-so-hidden');
    const opt = $dv.find('option:selected');
    const heSo = parseFloat(opt.data('he-so')) || 1;
    const tenDonVi = opt.data('ten') || 'đơn vị cơ bản';

    const variantId = $row.find('.pn-sp-select').val();
    const data = variantId && pnProductMap[variantId] ? pnProductMap[variantId].variant : null;
    const tenCoBan = data ? (data.ten_don_vi || 'đơn vị cơ bản') : 'đơn vị cơ bản';

    $heSo.val(heSo);

    if (heSo === 1) {
        $row.find('.pn-dv-hint').text('Đơn vị cơ bản');
        $row.find('.pn-sl-hint').html(`= <strong>${sl.toLocaleString()}</strong> ${escapeHtml(tenCoBan)}`);
        $row.find('.pn-gia-hint').html(`đơn giá / ${escapeHtml(tenCoBan)}`);
    } else {
        const tong = sl * heSo;
        const donGiaCoBan = gia / heSo;
        $row.find('.pn-dv-hint').html(`1 ${escapeHtml(tenDonVi)} = ${heSo} ${escapeHtml(tenCoBan)}`);
        $row.find('.pn-sl-hint').html(`= <strong class="text-primary">${tong.toLocaleString()}</strong> ${escapeHtml(tenCoBan)}`);
        $row.find('.pn-gia-hint').html(`đơn giá / ${escapeHtml(tenDonVi)} → <strong class="text-info">${donGiaCoBan.toLocaleString(undefined, {maximumFractionDigits: 2})}</strong> / ${escapeHtml(tenCoBan)}`);
    }
}

function layDuLieuFormNhap() {
    const chi_tiet = [];
    $('#danh-sach-sp-nhap tr').each(function () {
        const variant_id = $(this).find('.pn-sp-select').val();
        const don_vi_id = $(this).find('.pn-dv-select').val();
        const so_luong_nhap = $(this).find('.pn-sl-input').val();
        const gia_nhap = $(this).find('.pn-gia-input').val();
        const han_su_dung = $(this).find('input[type="date"]').val();
        if (variant_id) {
            chi_tiet.push({
                variant_id,
                don_vi_id: don_vi_id || null,
                so_luong_nhap,
                gia_nhap,
                han_su_dung,
            });
        }
    });

    const taoLoMoi = $('input[name="tao_lo_moi_ui"]:checked').val() || '1';
    const idLoHang = taoLoMoi === '0' ? ($('#pn-id-lo-hang').val() || '') : '';

    return {
        loai_nhap: $('#pn-loai-nhap').val(),
        id_nha_cung_cap: $('#pn-id-ncc').val() || null,
        ghi_chu: $('#pn-ghi-chu').val(),
        tao_lo_moi: taoLoMoi,
        id_lo_hang: idLoHang,
        chi_tiet,
    };
}

function taiDanhSachLoHangNhap() {
    $.get('/admin/api/phieu-nhap/danh-sach-lo-hang', res => {
        const items = (res && res.data) || [];
        if (!items.length) {
            $('#pn-id-lo-hang').html('<option value="">-- Chưa có lô nào --</option>');
            return;
        }
        const opts = ['<option value="">-- Chọn lô --</option>'].concat(items.map(l => {
            const ma = l.ma_lo || ('L-' + l.id);
            const ngay = l.ngay_nhap ? String(l.ngay_nhap).slice(0, 10) : '';
            const ncc = l.nha_cung_cap?.ten_nha_cung_cap || '';
            const slBienThe = (l.chi_tiet_lo_hang || []).length;
            return `<option value="${l.id}">${ma} | ${ngay} | ${ncc} | ${slBienThe} biến thể</option>`;
        }));
        $('#pn-id-lo-hang').html(opts.join(''));
    }).fail(() => {
        $('#pn-id-lo-hang').html('<option value="">-- Lỗi tải lô --</option>');
    });
}

function taiPhieuNhap(page = 1) {
    currentPageNhap = page;
    const loai = $('#filter-loai').val();
    const tuNgay = $('#filter-tu').val();
    const denNgay = $('#filter-den').val();
    const params = new URLSearchParams({ page, loai_nhap: loai || '', tu_ngay: tuNgay || '', den_ngay: denNgay || '' });

    $.get('/admin/api/phieu-nhap?' + params.toString(), res => {
        const items = res.data?.data || [];
        if (!items.length) {
            $('#bang-phieu-nhap').html('<tr><td colspan="9" class="text-center text-muted py-4">Chưa có phiếu nhập nào.</td></tr>');
            $('#phan-trang-nhap').html('');
            $('#thong-tin-hien-thi').text('Hiển thị 0 phiếu nhập');
            return;
        }
        const html = items.map(item => {
            const loaiLabel = item.loai_nhap === 'mua_hang'
                ? '<span class="badge bg-success">Nhập mua hàng</span>'
                : '<span class="badge bg-info">Trả lại từ khách</span>';
            const ncc = item.phieu?.nha_cung_cap?.ten_nha_cung_cap || '--';
            const nguoiTao = item.phieu?.nguoi_dung?.ho_ten || item.phieu?.id_nguoi_dung || '--';
            const ngay = item.created_at?.slice(0, 10) || '';
            const ghiChu = item.ghi_chu || '<span class="text-muted">--</span>';
            const tongGt = (item.chi_tiet_phieu || []).reduce((s, ct) => s + (ct.so_luong || 0) * (ct.gia_nhap || 0), 0);
            return `<tr>
                <td>${item.id}</td>
                <td><span class="badge bg-primary">PN-${item.id_phieu}</span></td>
                <td>${loaiLabel}</td>
                <td>${ncc}</td>
                <td>${nguoiTao}</td>
                <td class="text-center">${ngay}</td>
                <td class="text-center fw-bold">${tongGt > 0 ? tongGt.toLocaleString() + ' đ' : '--'}</td>
                <td>${ghiChu}</td>
                <td class="text-center">
                    <button class="btn btn-sm btn-outline-primary btn-xem-pn" data-id="${item.id}"><i class="fas fa-eye"></i></button>
                    <button class="btn btn-sm btn-outline-warning btn-sua-pn" data-id="${item.id}"><i class="fas fa-edit"></i></button>
                    <button class="btn btn-sm btn-outline-danger btn-xoa-pn" data-id="${item.id}"><i class="fas fa-trash"></i></button>
                </td>
            </tr>`;
        }).join('');
        $('#bang-phieu-nhap').html(html);
        $('#thong-tin-hien-thi').text(`Hiển thị ${items.length} / ${res.data.total || items.length} phiếu nhập`);
        renderPagination(page, res.data.last_page || 1);
    });
}

$(document).on('click', '.btn-xem-pn', function () {
    const id = $(this).data('id');
    currentExportPnId = id; // Lưu để export chi tiết
    $.get('/admin/api/phieu-nhap/' + id, res => {
        if (!res.success) return;
        const pn = res.data;
        const loaiLabel = pn.loai_nhap === 'mua_hang' ? 'Nhập mua hàng' : 'Trả lại từ khách';
        const tongGt = (pn.chi_tiet_phieu || []).reduce((s, ct) => s + (ct.so_luong || 0) * (ct.gia_nhap || 0), 0);
        const rows = (pn.chi_tiet_phieu || []).map(ct => {
            const sp = ct.san_pham || {};
            const lo = ct.lo_hang || {};
            return `<tr>
                <td>${sp.ten_san_pham || ct.id_san_pham}</td>
                <td class="text-center">${(ct.so_luong || 0).toLocaleString()}</td>
                <td class="text-center">${Number(ct.gia_nhap || 0).toLocaleString()} đ</td>
                <td class="text-center">${ct.han_su_dung?.slice(0, 10) || ''}</td>
                <td class="text-center">${lo.ma_lo || 'L-' + lo.id}</td>
                <td class="text-end fw-bold">${((ct.so_luong || 0) * (ct.gia_nhap || 0)).toLocaleString()} đ</td>
            </tr>`;
        }).join('') || '<tr><td colspan="6" class="text-center text-muted">Không có chi tiết</td></tr>';
        $('#modal-xem-phieu-nhap-body').html(`
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
            </table>`);
        new bootstrap.Modal(document.getElementById('modal-xem-phieu-nhap')).show();
    });
});

$(document).on('click', '.btn-sua-pn', function () {
    const id = $(this).data('id');
    $.get('/admin/api/phieu-nhap/' + id, res => {
        if (!res.success) return;
        const pn = res.data;
        $('#sua-pn-id').val(pn.id);
        $('#sua-pn-loai').val(pn.loai_nhap);
        $('#sua-pn-ncc').val(pn.phieu?.id_nha_cung_cap || '');
        $('#sua-pn-ghi-chu').val(pn.ghi_chu || '');
        new bootstrap.Modal(document.getElementById('modal-sua-phieu-nhap')).show();
    });
});

$(document).on('click', '.btn-xoa-pn', function () {
    if (!confirm('Xóa phiếu nhập này?')) return;
    const id = $(this).data('id');
    $.ajax({ url: '/admin/api/phieu-nhap/' + id, method: 'DELETE',
        success: res => { hienThongBao('success', res.message); taiPhieuNhap(currentPageNhap); },
        error: x => hienThongBao('danger', x.responseJSON?.message || 'Lỗi.')
    });
});

function renderPagination(current, total) {
    if (total <= 1) { $('#phan-trang-nhap').html(''); return; }
    let html = '<ul class="pagination mb-0">';
    if (current > 1) html += `<li class="page-item"><a class="page-link" href="javascript:;" onclick="taiPhieuNhap(${current - 1})">‹</a></li>`;
    for (let i = 1; i <= total; i++) {
        if (i === 1 || i === total || Math.abs(i - current) <= 2)
            html += `<li class="page-item ${i === current ? 'active' : ''}"><a class="page-link" href="javascript:;" onclick="taiPhieuNhap(${i})">${i}</a></li>`;
        else if (Math.abs(i - current) === 3) html += '<li class="page-item disabled"><span class="page-link">...</span></li>';
    }
    if (current < total) html += `<li class="page-item"><a class="page-link" href="javascript:;" onclick="taiPhieuNhap(${current + 1})">›</a></li>`;
    html += '</ul>';
    $('#phan-trang-nhap').html(html);
}

function hienThongBao(type, message) {
    $('#alert-container').html(`<div class="alert alert-${type} alert-dismissible fade show" role="alert">${message}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>`);
    setTimeout(() => $('.alert').fadeOut(), 4000);
}

function hienThongBaoNhap(type, message) {
    $('#alert-container').html(`<div class="alert alert-${type} alert-dismissible fade show" role="alert">${message}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>`);
    setTimeout(() => $('.alert').fadeOut(), 5000);
}

function xoaFileImportNhap() {
    importFileNhap = null;
    $('#import-file-nhap').val('');
    $('#import-file-preview-nhap').addClass('d-none');
    $('#btn-submit-import-nhap').prop('disabled', true);
}
</script>
@endsection
