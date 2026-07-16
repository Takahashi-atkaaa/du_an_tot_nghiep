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
        <button class="btn btn-danger btn-sm" id="btn-tao-phieu-xuat">
            <i class="fas fa-plus me-1"></i>Tạo phiếu xuất
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
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="modal-xem-phieu-xuat-body"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
            </div>
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
    setTimeout(() => $('.alert').fadeOut(), 4000);
}
</script>
@endsection
