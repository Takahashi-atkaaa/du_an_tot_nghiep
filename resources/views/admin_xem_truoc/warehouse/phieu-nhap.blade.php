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
        <button class="btn btn-success btn-sm" id="btn-tao-phieu-nhap">
            <i class="fas fa-plus me-1"></i>Tạo phiếu nhập
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
                    <hr>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="mb-0 small">Chi tiết sản phẩm</h6>
                        <button type="button" class="btn btn-sm btn-outline-primary" id="btn-them-sp-nhap"><i class="fas fa-plus me-1"></i>Thêm</button>
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
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="modal-xem-phieu-nhap-body"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
let sanPhamListNhap = [];
let chiTietNhapIndex = 0;
let currentPageNhap = 1;

$(function () {
    taiSanPhamNhap();
    taiPhieuNhap();
    taiNhaCungCapNhap();

    $('#btn-tao-phieu-nhap').click(function () {
        chiTietNhapIndex = 0;
        $('#form-tao-phieu-nhap')[0].reset();
        $('#danh-sach-sp-nhap').html('');
        addPnRow();
        new bootstrap.Modal(document.getElementById('modal-tao-phieu-nhap')).show();
    });

    $('#btn-them-sp-nhap').click(() => addPnRow());

    $(document).on('click', '.btn-remove-sp-nhap', function () {
        if ($('#danh-sach-sp-nhap tr').length > 1) $(this).closest('tr').remove();
    });

    $('#form-tao-phieu-nhap').submit(function (e) {
        e.preventDefault();
        const data = layDuLieuFormNhap();
        if (!data.chi_tiet.length) { hienThongBao('warning', 'Thêm ít nhất một sản phẩm.'); return; }
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
});

function taiSanPhamNhap() {
    $.get('/admin/api/san-pham', res => { sanPhamListNhap = res.data?.data || []; });
}

function taiNhaCungCapNhap() {
    $.get('/admin/api/lo-hang/nha-cung-cap', res => {
        const opts = res.map(n => `<option value="${n.id}">${n.ten_nha_cung_cap}</option>`).join('');
        $('#pn-id-ncc').html('<option value="">-- Chọn NCC --</option>' + opts);
        $('#sua-pn-ncc').html('<option value="">-- Chọn NCC --</option>' + opts);
    });
}

function addPnRow(id, sl, gia, hsd) {
    const idx = chiTietNhapIndex++;
    const opts = sanPhamListNhap.map(sp => `<option value="${sp.id}" ${sp.id == id ? 'selected' : ''}>${sp.ten_san_pham} (${sp.ma_vach || sp.id})</option>`).join('');
    $('#danh-sach-sp-nhap').append(`<tr>
        <td><select class="form-select form-select-sm" name="chi_tiet[${idx}][id_san_pham]">${opts || '<option value="">-- Chọn --</option>'}</select></td>
        <td><input type="number" class="form-control form-control-sm" name="chi_tiet[${idx}][so_luong_nhap]" value="${sl || 1}" min="1"></td>
        <td><input type="number" class="form-control form-control-sm" name="chi_tiet[${idx}][gia_nhap]" value="${gia || 0}" min="0" step="100"></td>
        <td><input type="date" class="form-control form-control-sm" name="chi_tiet[${idx}][han_su_dung]" value="${hsd || ''}"></td>
        <td><button type="button" class="btn btn-sm btn-outline-danger btn-remove-sp-nhap"><i class="fas fa-times"></i></button></td>
    </tr>`);
}

function layDuLieuFormNhap() {
    const chi_tiet = [];
    $('#danh-sach-sp-nhap tr').each(function () {
        const sp = $(this).find('select').val();
        const sl = $(this).find('input[name*="so_luong_nhap"]').val();
        const gia = $(this).find('input[name*="gia_nhap"]').val();
        const hsd = $(this).find('input[name*="han_su_dung"]').val();
        if (sp) chi_tiet.push({ id_san_pham: sp, so_luong: sl, so_luong_nhap: sl, gia_nhap: gia, han_su_dung: hsd });
    });
    return {
        loai_nhap: $('#pn-loai-nhap').val(),
        id_nha_cung_cap: $('#pn-id-ncc').val() || null,
        ghi_chu: $('#pn-ghi-chu').val(),
        tao_lo_moi: '1',
        id_lo_hang: '',
        chi_tiet,
    };
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
</script>
@endsection
