@extends('admin_xem_truoc.layouts.admin')

@section('title', 'Quản lý Phiếu xuất - SmartMart')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1"><i class="fas fa-arrow-up me-2 text-danger"></i>Quản lý Phiếu xuất</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('admin/dashboard') }}">Admin</a></li>
                <li class="breadcrumb-item"><a href="{{ url('admin/kho-hang') }}">Kho hàng</a></li>
                <li class="breadcrumb-item active">Phiếu xuất</li>
            </ol>
        </nav>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ url('admin/kho-hang') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-th-large me-1"></i>Hub kho hàng
        </a>
        <button class="btn btn-outline-danger btn-sm" id="btn-xuat-excel-xuat" title="Xuất Excel">
            <i class="fas fa-file-excel me-1"></i>Xuất Excel
        </button>
        <button class="btn btn-danger btn-sm" id="btn-tao-phieu-xuat">
            <i class="fas fa-plus me-1"></i>Tạo phiếu xuất
        </button>
        <button class="btn btn-danger btn-sm" id="btn-open-import-xuat" title="Import từ Excel">
            <i class="fas fa-file-import me-1"></i>Import Excel
        </button>
    </div>
</div>

<div id="alert-container-xuat"></div>

{{-- Filter --}}
<div class="card table-admin mb-4">
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-3">
                <select class="form-select" id="filter-loai-xuat">
                    <option value="">Tất cả loại</option>
                    <option value="tra_hang_nha_cung_cap">Trả hàng NCC</option>
                    <option value="tieu_huy">Tiêu hủy</option>
                </select>
            </div>
            <div class="col-md-2">
                <input type="date" class="form-control" id="filter-tu-xuat">
            </div>
            <div class="col-md-2">
                <input type="date" class="form-control" id="filter-den-xuat">
            </div>
            <div class="col-md-3">
                <button class="btn btn-primary" id="btn-loc-phieu-xuat"><i class="fas fa-filter me-1"></i>Lọc</button>
                <a href="{{ url('admin/kho-hang/phieu-xuat') }}" class="btn btn-light border"><i class="fas fa-redo"></i></a>
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
                        <th>Lý do</th>
                        <th>Người tạo</th>
                        <th class="text-center">Ngày</th>
                        <th class="text-center">Tổng SL</th>
                        <th class="text-center" style="width:130px">Thao tác</th>
                    </tr>
                </thead>
                <tbody id="bang-phieu-xuat">
                    <tr><td colspan="9" class="text-center text-muted py-4">Đang tải...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer bg-white">
        <div class="d-flex justify-content-between align-items-center">
            <span class="text-muted small" id="thong-tin-hien-thi-xuat"></span>
            <nav id="phan-trang-xuat"></nav>
        </div>
    </div>
</div>
@endsection

{{-- MODALS --}}
@section('modals')
{{-- Modal Tạo --}}
<div class="modal fade" id="modal-tao-phieu-xuat" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <form id="form-tao-phieu-xuat">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title"><i class="fas fa-arrow-up me-2"></i>Tạo phiếu xuất</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning mb-3 py-2 small">
                        <i class="fas fa-info-circle me-1"></i>
                        Hệ thống tự động xuất hàng theo nguyên tắc <strong>FEFO</strong> — ưu tiên lô có HSD gần nhất.
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label">Loại xuất <span class="text-danger">*</span></label>
                            <select name="loai_xuat" class="form-select" id="px-loai-xuat" required>
                                <option value="tra_hang_nha_cung_cap">Trả hàng NCC</option>
                                <option value="tieu_huy">Tiêu hủy</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Nhà cung cấp</label>
                            <select name="id_nha_cung_cap" id="px-id-ncc" class="form-select">
                                <option value="">-- Chọn NCC --</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Lý do</label>
                            <input type="text" name="ly_do" class="form-control" id="px-ly-do" placeholder="Lý do xuất hàng...">
                        </div>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="mb-0 small">Chi tiết sản phẩm xuất</h6>
                        <button type="button" class="btn btn-sm btn-outline-primary" id="btn-them-sp-xuat"><i class="fas fa-plus me-1"></i>Thêm</button>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered mb-2">
                            <thead class="table-light">
                                <tr>
                                    <th style="width:35%">Sản phẩm</th>
                                    <th class="text-center" style="width:100px">Tồn kho</th>
                                    <th class="text-center" style="width:90px">SL xuất</th>
                                    <th style="width:40px"></th>
                                </tr>
                            </thead>
                            <tbody id="danh-sach-sp-xuat"></tbody>
                        </table>
                    </div>
                    <div id="px-fefo-preview"></div>
                    <div class="mt-2 text-end">
                        <h5>Tổng SL: <span id="tong-sl-xuat" class="text-danger">0</span></h5>
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

{{-- Modal Sửa --}}
<div class="modal fade" id="modal-sua-phieu-xuat" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="form-sua-phieu-xuat">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title"><i class="fas fa-edit me-2"></i>Sửa phiếu xuất</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="sua-px-id">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Loại xuất <span class="text-danger">*</span></label>
                            <select name="loai_xuat" class="form-select" id="sua-px-loai" required>
                                <option value="tra_hang_nha_cung_cap">Trả hàng NCC</option>
                                <option value="tieu_huy">Tiêu hủy</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Nhà cung cấp</label>
                            <select name="id_nha_cung_cap" id="sua-px-ncc" class="form-select">
                                <option value="">-- Chọn NCC --</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Lý do</label>
                            <input type="text" name="ly_do" class="form-control" id="sua-px-ly-do">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Ghi chú</label>
                            <textarea name="ghi_chu" class="form-control" rows="2" id="sua-px-ghi-chu"></textarea>
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
<div class="modal fade" id="modal-xem-phieu-xuat" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-file-alt me-2 text-danger"></i>Chi tiết phiếu xuất</h5>
                <button type="button" class="btn btn-sm btn-danger" id="btn-export-chi-tiet-px">
                    <i class="fas fa-download me-1"></i>Xuất Excel
                </button>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="modal-xem-phieu-xuat-body"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
            </div>
        </div>
    </div>
</div>

{{-- Modal Import Excel --}}
<div class="modal fade" id="modal-import-phieu-xuat" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="fas fa-file-import me-2"></i>Import Phiếu Xuất từ Excel</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="form-import-phieu-xuat">
                <div class="modal-body">
                    <div class="alert alert-warning mb-3 py-2 small">
                        <i class="fas fa-info-circle me-1"></i>
                        Hệ thống sẽ tự động trừ kho theo nguyên tắc <strong>FEFO</strong> — ưu tiên lô có HSD gần nhất.
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Loại xuất <span class="text-danger">*</span></label>
                        <select name="loai_xuat" class="form-select" id="import-loai-xuat" required>
                            <option value="tieu_huy">Tiêu hủy</option>
                            <option value="tra_hang_nha_cung_cap">Trả hàng NCC</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nhà cung cấp</label>
                        <select name="id_nha_cung_cap" id="import-xuat-id-ncc" class="form-select">
                            <option value="">-- Chọn NCC --</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Lý do</label>
                        <input type="text" name="ly_do" id="import-ly-do" class="form-control" placeholder="Lý do xuất hàng...">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Ghi chú</label>
                        <input type="text" name="ghi_chu" id="import-xuat-ghi-chu" class="form-control" placeholder="Ghi chú...">
                    </div>
                    <hr>
                    <div class="mb-3">
                        <label class="form-label">Chọn file Excel <span class="text-danger">*</span></label>
                        <input type="file" id="import-file-xuat" accept=".xlsx,.xls,.csv" class="form-control" required>
                        <small class="text-muted">Hỗ trợ định dạng .xlsx, .xls, .csv</small>
                    </div>
                    <div class="drop-zone-xuat border rounded p-4 text-center" id="drop-zone-xuat" style="border-style: dashed; background: #f8f9fa;">
                        <i class="fas fa-cloud-upload-alt fa-2x text-muted mb-2"></i>
                        <p class="mb-0 text-muted">Kéo thả file Excel vào đây</p>
                        <small class="text-muted">hoặc nhấn "Chọn file" ở trên</small>
                    </div>
                    <div id="import-file-preview-xuat" class="mt-2 d-none">
                        <div class="alert alert-info py-2 mb-0 d-flex align-items-center">
                            <i class="fas fa-file-excel me-2 text-success"></i>
                            <span id="import-file-name-xuat"></span>
                            <button type="button" class="btn-close ms-auto" onclick="xoaFileImportXuat()"></button>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="button" class="btn btn-outline-primary" id="btn-download-template-xuat">
                        <i class="fas fa-download me-1"></i>Tải file mẫu
                    </button>
                    <button type="submit" class="btn btn-danger" id="btn-submit-import-xuat" disabled>
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
let sanPhamListXuat = [];
let chiTietXuatIndex = 0;
let currentPageXuat = 1;

$(function () {
    taiSanPhamXuat();
    taiPhieuXuat();
    taiNhaCungCapXuat();

    $('#btn-tao-phieu-xuat').click(function () {
        chiTietXuatIndex = 0;
        $('#form-tao-phieu-xuat')[0].reset();
        $('#danh-sach-sp-xuat').html('');
        $('#px-fefo-preview').html('');
        addPxRow();
        new bootstrap.Modal(document.getElementById('modal-tao-phieu-xuat')).show();
    });

    $(document).on('click', '#btn-open-import-xuat', function () {
        taiNhaCungCapXuat();
        $('#modal-import-phieu-xuat').modal('show');
    });

    $('#btn-them-sp-xuat').click(() => addPxRow());

    $(document).on('click', '.btn-remove-sp-xuat', function () {
        if ($('#danh-sach-sp-xuat tr').length > 1) {
            $(this).closest('tr').remove();
            tinhTongXuat();
        }
    });

    $(document).on('change', '.px-sp-select', function () {
        const row = $(this).closest('tr');
        const val = $(this).val();
        const tonCell = row.find('.ton-kho-cell');
        if (val) {
            const opt = $(this).find('option:selected');
            tonCell.text('Tồn: ' + (opt.data('ton') || 0).toLocaleString());
        } else {
            tonCell.text('--');
        }
        updateFefoPreview();
    });

    $(document).on('input', '.px-sl-input', function () {
        tinhTongXuat();
        updateFefoPreview();
    });

    $('#form-tao-phieu-xuat').submit(function (e) {
        e.preventDefault();
        const chi_tiet = [];
        $('#danh-sach-sp-xuat tr').each(function () {
            const sp = $(this).find('.px-sp-select').val();
            const sl = $(this).find('.px-sl-input').val();
            if (sp && sl) chi_tiet.push({ id_san_pham: sp, so_luong: parseInt(sl) });
        });
        if (!chi_tiet.length) { hienThongBaoXuat('warning', 'Thêm ít nhất một sản phẩm.'); return; }
        const data = {
            loai_xuat: $('#px-loai-xuat').val(),
            id_nha_cung_cap: $('#px-id-ncc').val() || null,
            ly_do: $('#px-ly-do').val(),
            chi_tiet,
        };
        $.ajax({ url: '/admin/api/phieu-xuat', method: 'POST', contentType: 'application/json', data: JSON.stringify(data),
            success: res => {
                bootstrap.Modal.getInstance(document.getElementById('modal-tao-phieu-xuat')).hide();
                hienThongBaoXuat('success', res.message);
                taiPhieuXuat(currentPageXuat);
            },
            error: x => hienThongBaoXuat('danger', x.responseJSON?.message || 'Lỗi.')
        });
    });

    $('#form-sua-phieu-xuat').submit(function (e) {
        e.preventDefault();
        const id = $('#sua-px-id').val();
        const data = {
            loai_xuat: $('#sua-px-loai').val(),
            id_nha_cung_cap: $('#sua-px-ncc').val() || null,
            ly_do: $('#sua-px-ly-do').val(),
            ghi_chu: $('#sua-px-ghi-chu').val(),
        };
        $.ajax({ url: '/admin/api/phieu-xuat/' + id, method: 'PUT', contentType: 'application/json', data: JSON.stringify(data),
            success: res => {
                bootstrap.Modal.getInstance(document.getElementById('modal-sua-phieu-xuat')).hide();
                hienThongBaoXuat('success', res.message);
                taiPhieuXuat(currentPageXuat);
            },
            error: x => hienThongBaoXuat('danger', x.responseJSON?.message || 'Lỗi.')
        });
    });

    $('#btn-loc-phieu-xuat').click(() => taiPhieuXuat(1));

    // ========== IMPORT EXCEL ==========
    let importFileXuat = null;
    let currentExportPxId = null;

    $('#btn-xuat-excel-xuat').click(function () {
        const loai = $('#filter-loai-xuat').val();
        const tuNgay = $('#filter-tu-xuat').val();
        const denNgay = $('#filter-den-xuat').val();
        let url = '/admin/api/phieu-xuat/export';
        const params = [];
        if (loai) params.push('loai_xuat=' + loai);
        if (tuNgay) params.push('tu_ngay=' + tuNgay);
        if (denNgay) params.push('den_ngay=' + denNgay);
        if (params.length) url += '?' + params.join('&');
        window.open(url, '_blank');
    });

    $(document).on('click', '#btn-export-chi-tiet-px', function () {
        if (currentExportPxId) {
            window.open('/admin/api/phieu-xuat/' + currentExportPxId + '/export', '_blank');
        }
    });

    $('#btn-download-template-xuat').click(function () {
        window.open('/admin/api/phieu-xuat/download-template', '_blank');
    });

    $('#import-file-xuat').change(function () {
        const file = this.files[0];
        if (file) {
            importFileXuat = file;
            $('#import-file-name-xuat').text(file.name);
            $('#import-file-preview-xuat').removeClass('d-none');
            $('#btn-submit-import-xuat').prop('disabled', false);
        }
    });

    $('#drop-zone-xuat').click(function () {
        $('#import-file-xuat').click();
    });

    $('#drop-zone-xuat').on('dragover', function (e) {
        e.preventDefault();
        $(this).css('background-color', '#fff5f5');
    });

    $('#drop-zone-xuat').on('dragleave', function () {
        $(this).css('background-color', '#f8f9fa');
    });

    $('#drop-zone-xuat').on('drop', function (e) {
        e.preventDefault();
        $(this).css('background-color', '#f8f9fa');
        const file = e.originalEvent.dataTransfer.files[0];
        if (file && (file.name.endsWith('.xlsx') || file.name.endsWith('.xls') || file.name.endsWith('.csv'))) {
            importFileXuat = file;
            $('#import-file-xuat')[0].files = e.originalEvent.dataTransfer.files;
            $('#import-file-name-xuat').text(file.name);
            $('#import-file-preview-xuat').removeClass('d-none');
            $('#btn-submit-import-xuat').prop('disabled', false);
        } else {
            hienThongBaoXuat('danger', 'Vui lòng chọn file Excel (.xlsx, .xls, .csv)');
        }
    });

    $('#form-import-phieu-xuat').submit(function (e) {
        e.preventDefault();
        if (!importFileXuat) {
            hienThongBaoXuat('danger', 'Vui lòng chọn file Excel.');
            return;
        }

        const formData = new FormData();
        formData.append('file', importFileXuat);
        formData.append('loai_xuat', $('#import-loai-xuat').val());
        formData.append('id_nha_cung_cap', $('#import-xuat-id-ncc').val() || '');
        formData.append('ly_do', $('#import-ly-do').val() || '');
        formData.append('ghi_chu', $('#import-xuat-ghi-chu').val() || '');

        $('#btn-submit-import-xuat').prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Đang import...');

        $.ajax({
            url: '/admin/api/phieu-xuat/import',
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            data: formData,
            processData: false,
            contentType: false,
            success: function (res) {
                if (res.success) {
                    bootstrap.Modal.getInstance(document.getElementById('modal-import-phieu-xuat')).hide();
                    hienThongBaoXuat('success', res.message);
                    taiPhieuXuat(1);
                    importFileXuat = null;
                    $('#form-import-phieu-xuat')[0].reset();
                    $('#import-file-preview-xuat').addClass('d-none');
                } else {
                    hienThongBaoXuat('danger', res.message);
                    if (res.errors && res.errors.length) {
                        console.error('Import errors:', res.errors);
                    }
                }
            },
            error: function (x) {
                const msg = x.responseJSON?.message || 'Import thất bại.';
                hienThongBaoXuat('danger', msg);
            },
            complete: function () {
                $('#btn-submit-import-xuat').prop('disabled', false).html('<i class="fas fa-upload me-1"></i>Import');
            }
        });
    });
});

function taiSanPhamXuat() {
    $.get('/admin/api/san-pham', res => {
        sanPhamListXuat = res.data?.data || [];
    });
}

function taiNhaCungCapXuat() {
    $.get('/admin/api/lo-hang/nha-cung-cap', res => {
        const opts = res.map(n => `<option value="${n.id}">${n.ten_nha_cung_cap}</option>`).join('');
        $('#px-id-ncc').html('<option value="">-- Chọn NCC --</option>' + opts);
        $('#sua-px-ncc').html('<option value="">-- Chọn NCC --</option>' + opts);
        $('#import-xuat-id-ncc').html('<option value="">-- Chọn NCC --</option>' + opts);
    });
}

function addPxRow(id, sl) {
    const idx = chiTietXuatIndex++;
    const opts = sanPhamListXuat.map(sp =>
        `<option value="${sp.id}" data-ton="${sp.tong_ton || 0}" ${sp.id == id ? 'selected' : ''}>${sp.ten_san_pham} (${sp.ma_vach || sp.id})</option>`
    ).join('');
    $('#danh-sach-sp-xuat').append(`<tr>
        <td><select class="form-select form-select-sm px-sp-select">${opts || '<option value="">-- Chọn --</option>'}</select></td>
        <td class="text-center ton-kho-cell text-muted small">--</td>
        <td><input type="number" class="form-control form-control-sm px-sl-input" value="${sl || 1}" min="1"></td>
        <td><button type="button" class="btn btn-sm btn-outline-danger btn-remove-sp-xuat"><i class="fas fa-times"></i></button></td>
    </tr>`);
}

function tinhTongXuat() {
    let tong = 0;
    $('.px-sl-input').each(function () { tong += parseInt($(this).val()) || 0; });
    $('#tong-sl-xuat').text(tong.toLocaleString());
}

function updateFefoPreview() {
    let html = '<div class="fefo-preview small"><h6 class="mb-2"><i class="fas fa-list-check me-1"></i>Preview FEFO — lô xuất:</h6>';
    let hasItem = false;
    $('#danh-sach-sp-xuat tr').each(function () {
        const spSelect = $(this).find('.px-sp-select');
        const slInput = $(this).find('.px-sl-input');
        const spName = spSelect.find('option:selected').text() || '--';
        const sl = parseInt(slInput.val()) || 0;
        if (spSelect.val() && sl > 0) {
            hasItem = true;
            html += `<div class="mb-2"><strong>${spName}</strong> × ${sl.toLocaleString()}</div>`;
            $.ajax({ url: '/admin/api/lo-hang/ton-kho?id_san_pham=' + spSelect.val(), async: false, success: res => {
                if (!res.success) return;
                let con = sl;
                (res.data.chi_tiet || []).forEach(ct => {
                    const lo = ct.lo_hang || {};
                    const lay = Math.min(con, ct.so_luong_ton || 0);
                    con -= lay;
                    if (lay > 0) {
                        html += `<div class="ms-3 text-muted">→ Lô ${lo.ma_lo || 'L-' + lo.id} (HSD ${ct.han_su_dung?.slice(0,10)}): xuất <span class="text-danger">${lay}</span></div>`;
                    }
                });
                if (con > 0) html += `<div class="ms-3 text-danger">⚠ Thiếu ${con} cái trong kho</div>`;
            }});
        }
    });
    html += '</div>';
    $('#px-fefo-preview').html(hasItem ? html : '');
}

function taiPhieuXuat(page = 1) {
    currentPageXuat = page;
    const loai = $('#filter-loai-xuat').val();
    const tuNgay = $('#filter-tu-xuat').val();
    const denNgay = $('#filter-den-xuat').val();
    const params = new URLSearchParams({ page, loai_xuat: loai || '', tu_ngay: tuNgay || '', den_ngay: denNgay || '' });

    $.get('/admin/api/phieu-xuat?' + params.toString(), res => {
        const items = res.data?.data || [];
        if (!items.length) {
            $('#bang-phieu-xuat').html('<tr><td colspan="9" class="text-center text-muted py-4">Chưa có phiếu xuất nào.</td></tr>');
            $('#phan-trang-xuat').html('');
            $('#thong-tin-hien-thi-xuat').text('Hiển thị 0 phiếu xuất');
            return;
        }
        const html = items.map(item => {
            const loaiLabel = item.loai_xuat === 'tra_hang_nha_cung_cap'
                ? '<span class="badge bg-warning text-dark">Trả hàng NCC</span>'
                : '<span class="badge bg-danger">Tiêu hủy</span>';
            const ncc = item.phieu?.nha_cung_cap?.ten_nha_cung_cap || '--';
            const nguoiTao = item.phieu?.nguoi_dung?.ho_ten || item.phieu?.id_nguoi_dung || '--';
            const ngay = item.created_at?.slice(0, 10) || '';
            const lyDo = item.ly_do || '<span class="text-muted">--</span>';
            const tongSl = (item.chi_tiet_phieu || []).reduce((s, ct) => s + (ct.so_luong || 0), 0);
            return `<tr>
                <td>${item.id}</td>
                <td><span class="badge bg-danger">PX-${item.id_phieu}</span></td>
                <td>${loaiLabel}</td>
                <td>${ncc}</td>
                <td>${lyDo}</td>
                <td>${nguoiTao}</td>
                <td class="text-center">${ngay}</td>
                <td class="text-center fw-bold text-danger">${tongSl.toLocaleString()}</td>
                <td class="text-center">
                    <button class="btn btn-sm btn-outline-primary btn-xem-px" data-id="${item.id}"><i class="fas fa-eye"></i></button>
                    <button class="btn btn-sm btn-outline-warning btn-sua-px" data-id="${item.id}"><i class="fas fa-edit"></i></button>
                    <button class="btn btn-sm btn-outline-danger btn-xoa-px" data-id="${item.id}"><i class="fas fa-trash"></i></button>
                </td>
            </tr>`;
        }).join('');
        $('#bang-phieu-xuat').html(html);
        $('#thong-tin-hien-thi-xuat').text(`Hiển thị ${items.length} / ${res.data.total || items.length} phiếu xuất`);
        renderPagination(page, res.data.last_page || 1);
    });
}

$(document).on('click', '.btn-xem-px', function () {
    const id = $(this).data('id');
    currentExportPxId = id;
    $.get('/admin/api/phieu-xuat/' + id, res => {
        if (!res.success) return;
        const px = res.data;
        const loaiLabel = px.loai_xuat === 'tra_hang_nha_cung_cap' ? 'Trả hàng NCC' : 'Tiêu hủy';
        const tongSl = (px.chi_tiet_phieu || []).reduce((s, ct) => s + (ct.so_luong || 0), 0);
        const rows = (px.chi_tiet_phieu || []).map(ct => {
            const sp = ct.san_pham || {};
            const lo = ct.chi_tiet_lo_hang?.lo_hang || {};
            return `<tr>
                <td>${sp.ten_san_pham || ct.id_san_pham}</td>
                <td class="text-center text-danger fw-bold">${(ct.so_luong || 0).toLocaleString()}</td>
                <td class="text-center">${ct.chi_tiet_lo_hang?.han_su_dung?.slice(0, 10) || '--'}</td>
                <td class="text-center">${lo.ma_lo || 'L-' + lo.id}</td>
            </tr>`;
        }).join('') || '<tr><td colspan="4" class="text-center text-muted">Không có chi tiết</td></tr>';
        $('#modal-xem-phieu-xuat-body').html(`
            <div class="row mb-3">
                <div class="col-md-3"><strong>Mã phiếu:</strong> PX-${px.id_phieu}</div>
                <div class="col-md-3"><strong>Loại:</strong> ${loaiLabel}</div>
                <div class="col-md-3"><strong>NCC:</strong> ${px.phieu?.nha_cung_cap?.ten_nha_cung_cap || '--'}</div>
                <div class="col-md-3"><strong>Ngày:</strong> ${px.created_at?.slice(0, 10) || ''}</div>
            </div>
            <p><strong>Lý do:</strong> ${px.ly_do || '--'}</p>
            <table class="table table-sm table-bordered">
                <thead class="table-light"><tr><th>Sản phẩm</th><th class="text-center">SL xuất</th><th class="text-center">HSD lô</th><th class="text-center">Lô xuất (FEFO)</th></tr></thead>
                <tbody>${rows}</tbody>
                <tfoot><tr><td colspan="3" class="text-end fw-bold">Tổng SL xuất:</td><td class="text-center fw-bold text-danger">${tongSl.toLocaleString()}</td></tr></tfoot>
            </table>`);
        new bootstrap.Modal(document.getElementById('modal-xem-phieu-xuat')).show();
    });
});

$(document).on('click', '.btn-sua-px', function () {
    const id = $(this).data('id');
    $.get('/admin/api/phieu-xuat/' + id, res => {
        if (!res.success) return;
        const px = res.data;
        $('#sua-px-id').val(px.id);
        $('#sua-px-loai').val(px.loai_xuat);
        $('#sua-px-ncc').val(px.phieu?.id_nha_cung_cap || '');
        $('#sua-px-ly-do').val(px.ly_do || '');
        $('#sua-px-ghi-chu').val(px.ghi_chu || '');
        new bootstrap.Modal(document.getElementById('modal-sua-phieu-xuat')).show();
    });
});

$(document).on('click', '.btn-xoa-px', function () {
    if (!confirm('Xóa phiếu xuất và hoàn tăng tồn kho?')) return;
    const id = $(this).data('id');
    $.ajax({ url: '/admin/api/phieu-xuat/' + id, method: 'DELETE',
        success: res => { hienThongBaoXuat('success', res.message); taiPhieuXuat(currentPageXuat); },
        error: x => hienThongBaoXuat('danger', x.responseJSON?.message || 'Lỗi.')
    });
});

function renderPagination(current, total) {
    if (total <= 1) { $('#phan-trang-xuat').html(''); return; }
    let html = '<ul class="pagination mb-0">';
    if (current > 1) html += `<li class="page-item"><a class="page-link" href="javascript:;" onclick="taiPhieuXuat(${current - 1})">‹</a></li>`;
    for (let i = 1; i <= total; i++) {
        if (i === 1 || i === total || Math.abs(i - current) <= 2)
            html += `<li class="page-item ${i === current ? 'active' : ''}"><a class="page-link" href="javascript:;" onclick="taiPhieuXuat(${i})">${i}</a></li>`;
        else if (Math.abs(i - current) === 3) html += '<li class="page-item disabled"><span class="page-link">...</span></li>';
    }
    if (current < total) html += `<li class="page-item"><a class="page-link" href="javascript:;" onclick="taiPhieuXuat(${current + 1})">›</a></li>`;
    html += '</ul>';
    $('#phan-trang-xuat').html(html);
}

function hienThongBaoXuat(type, message) {
    $('#alert-container-xuat').html(`<div class="alert alert-${type} alert-dismissible fade show" role="alert">${message}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>`);
    setTimeout(() => $('.alert').fadeOut(), 5000);
}

function xoaFileImportXuat() {
    importFileXuat = null;
    $('#import-file-xuat').val('');
    $('#import-file-preview-xuat').addClass('d-none');
    $('#btn-submit-import-xuat').prop('disabled', true);
}
</script>
@endsection
