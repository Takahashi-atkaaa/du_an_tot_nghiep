@extends('admin_xem_truoc.layouts.admin')

@section('title', 'Quản lý Lô hàng - SmartMart')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1"><i class="fas fa-layer-group me-2 text-primary"></i>Quản lý Lô hàng</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('admin/dashboard') }}">Admin</a></li>
                <li class="breadcrumb-item"><a href="{{ url('admin/kho-hang') }}">Kho hàng</a></li>
                <li class="breadcrumb-item active">Lô hàng</li>
            </ol>
        </nav>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ url('admin/kho-hang') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-th-large me-1"></i>Hub kho hàng
        </a>
        <button class="btn btn-success btn-sm" id="btn-tao-lo-hang">
            <i class="fas fa-plus me-1"></i>Tạo lô hàng
        </button>
    </div>
</div>

<div id="alert-container"></div>

{{-- Stats --}}
<div class="row g-3 mb-4">
    <div class="col-xl-3 col-md-6">
        <div class="card card-stat blue h-100">
            <div class="card-body d-flex align-items-center">
                <div class="icon me-3"><i class="fas fa-layer-group"></i></div>
                <div><h6 class="text-muted mb-1">Tổng lô</h6><h3 class="mb-0" id="stat-tong">--</h3></div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card card-stat green h-100">
            <div class="card-body d-flex align-items-center">
                <div class="icon me-3"><i class="fas fa-check-circle"></i></div>
                <div><h6 class="text-muted mb-1">Còn hàng</h6><h3 class="mb-0" id="stat-con">--</h3></div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card card-stat orange h-100">
            <div class="card-body d-flex align-items-center">
                <div class="icon me-3"><i class="fas fa-clock"></i></div>
                <div><h6 class="text-muted mb-1">Sắp hết HSD</h6><h3 class="mb-0" id="stat-sap">--</h3></div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card card-stat red h-100">
            <div class="card-body d-flex align-items-center">
                <div class="icon me-3"><i class="fas fa-times-circle"></i></div>
                <div><h6 class="text-muted mb-1">Đã hết</h6><h3 class="mb-0" id="stat-het">--</h3></div>
            </div>
        </div>
    </div>
</div>

{{-- Filter --}}
<div class="card table-admin mb-4">
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-4">
                <div class="input-group">
                    <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                    <input type="text" class="form-control" id="filter-q" placeholder="Tìm mã lô, ghi chú...">
                </div>
            </div>
            <div class="col-md-3">
                <select class="form-select" id="filter-ncc">
                    <option value="">Tất cả nhà cung cấp</option>
                </select>
            </div>
            <div class="col-md-2">
                <button class="btn btn-primary w-100" id="btn-loc"><i class="fas fa-filter me-1"></i>Lọc</button>
            </div>
            <div class="col-md-2">
                <a href="{{ url('admin/kho-hang/lo-hang') }}" class="btn btn-light border w-100"><i class="fas fa-redo"></i></a>
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
                        <th>Mã lô</th>
                        <th>Nhà cung cấp</th>
                        <th>Ngày nhập</th>
                        <th class="text-center">Tổng nhập</th>
                        <th class="text-center">Tổng tồn</th>
                        <th class="text-center">HSD sớm nhất</th>
                        <th class="text-center">Trạng thái</th>
                        <th class="text-center" style="width:130px">Thao tác</th>
                    </tr>
                </thead>
                <tbody id="bang-lo-hang">
                    <tr><td colspan="9" class="text-center text-muted py-4">Đang tải...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer bg-white">
        <div class="d-flex justify-content-between align-items-center">
            <span class="text-muted small" id="thong-tin-hien-thi"></span>
            <nav id="phan-trang"></nav>
        </div>
    </div>
</div>
@endsection

{{-- MODAL TẠO LÔ --}}
@section('modals')
<div class="modal fade" id="modal-tao-lo-hang" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <form id="form-tao-lo-hang">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="fas fa-layer-group me-2"></i>Tạo lô hàng mới</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label">Nhà cung cấp</label>
                            <select name="id_nha_cung_cap" id="lo-id-ncc" class="form-select">
                                <option value="">-- Chọn NCC --</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Mã lô (tùy chọn)</label>
                            <input type="text" name="ma_lo" class="form-control" placeholder="Tự động nếu để trống">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Ngày nhập <span class="text-danger">*</span></label>
                            <input type="date" name="ngay_nhap" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Ghi chú</label>
                            <textarea name="ghi_chu" class="form-control" rows="2" placeholder="Ghi chú..."></textarea>
                        </div>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="mb-0 small">Chi tiết sản phẩm</h6>
                        <button type="button" class="btn btn-sm btn-outline-primary" id="btn-them-sp">
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
                            <tbody id="danh-sach-lo"></tbody>
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

{{-- MODAL SỬA LÔ --}}
<div class="modal fade" id="modal-sua-lo-hang" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="form-sua-lo-hang">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title"><i class="fas fa-edit me-2"></i>Sửa lô hàng</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="sua-lo-id">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Nhà cung cấp</label>
                            <select name="id_nha_cung_cap" id="sua-lo-ncc" class="form-select">
                                <option value="">-- Chọn NCC --</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Mã lô</label>
                            <input type="text" name="ma_lo" id="sua-lo-ma" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Ngày nhập <span class="text-danger">*</span></label>
                            <input type="date" name="ngay_nhap" id="sua-lo-ngay" class="form-control" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Ghi chú</label>
                            <textarea name="ghi_chu" id="sua-lo-ghi-chu" class="form-control" rows="2"></textarea>
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

{{-- MODAL XEM CHI TIẾT --}}
<div class="modal fade" id="modal-xem-lo-hang" tabindex="-1">
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
let sanPhamList = [];
let loIdx = 0;
let currentPage = 1;

$(function () {
    loadStats();
    taiLoHang();
    taiSanPham();
    taiNhaCungCap();

    $('#btn-tao-lo-hang').click(function () {
        loIdx = 0;
        $('#form-tao-lo-hang')[0].reset();
        $('#danh-sach-lo').html('');
        addLoRow();
        new bootstrap.Modal(document.getElementById('modal-tao-lo-hang')).show();
    });

    $('#btn-them-sp').click(() => addLoRow());

    $(document).on('click', '.btn-remove-lo', function () {
        if ($('#danh-sach-lo tr').length > 1) $(this).closest('tr').remove();
    });

    $('#btn-loc').click(() => taiLoHang(1));
    $('#filter-q').on('keypress', e => { if (e.which === 13) taiLoHang(1); });

    $('#form-tao-lo-hang').submit(function (e) {
        e.preventDefault();
        const data = buildLoData();
        if (!data.chi_tiet.length) { hienThongBao('warning', 'Thêm ít nhất một sản phẩm.'); return; }
        $.ajax({ url: '/admin/api/lo-hang', method: 'POST', contentType: 'application/json', data: JSON.stringify(data),
            success: res => {
                bootstrap.Modal.getInstance(document.getElementById('modal-tao-lo-hang')).hide();
                hienThongBao('success', res.message);
                taiLoHang(currentPage); loadStats();
            },
            error: x => hienThongBao('danger', x.responseJSON?.message || 'Lỗi.')
        });
    });

    $('#form-sua-lo-hang').submit(function (e) {
        e.preventDefault();
        const id = $('#sua-lo-id').val();
        const data = {
            id_nha_cung_cap: $('#sua-lo-ncc').val() || null,
            ma_lo: $('#sua-lo-ma').val(),
            ngay_nhap: $('#sua-lo-ngay').val(),
            ghi_chu: $('#sua-lo-ghi-chu').val(),
        };
        $.ajax({ url: '/admin/api/lo-hang/' + id, method: 'PUT', contentType: 'application/json', data: JSON.stringify(data),
            success: res => {
                bootstrap.Modal.getInstance(document.getElementById('modal-sua-lo-hang')).hide();
                hienThongBao('success', res.message);
                taiLoHang(currentPage);
            },
            error: x => hienThongBao('danger', x.responseJSON?.message || 'Lỗi.')
        });
    });
});

function loadStats() {
    $.get('/admin/api/lo-hang/thong-ke', res => {
        if (!res.success) return;
        $('#stat-tong').text(res.data.tong_lo);
        $('#stat-con').text(res.data.lo_con_hang);
        $('#stat-sap').text(res.data.sap_het_hsd);
        $('#stat-het').text(res.data.da_het);
    });
}

function taiSanPham() {
    $.get('/admin/api/san-pham', res => { sanPhamList = res.data?.data || []; });
}

function taiNhaCungCap() {
    $.get('/admin/api/lo-hang/nha-cung-cap', res => {
        const opts = res.map(n => `<option value="${n.id}">${n.ten_nha_cung_cap}</option>`).join('');
        $('#filter-ncc').html('<option value="">Tất cả NCC</option>' + opts);
        $('#lo-id-ncc').html('<option value="">-- Chọn NCC --</option>' + opts);
        $('#sua-lo-ncc').html('<option value="">-- Chọn NCC --</option>' + opts);
    });
}

function spOptionsHtml() {
    return sanPhamList.map(sp => `<option value="${sp.id}">${sp.ten_san_pham} (${sp.ma_vach || sp.id})</option>`).join('');
}

function addLoRow() {
    const idx = loIdx++;
    const opts = spOptionsHtml();
    $('#danh-sach-lo').append(`<tr>
        <td><select class="form-select form-select-sm" name="chi_tiet[${idx}][id_san_pham]">${opts || '<option value="">-- Chọn --</option>'}</select></td>
        <td><input type="number" class="form-control form-control-sm" name="chi_tiet[${idx}][so_luong_nhap]" value="1" min="1"></td>
        <td><input type="number" class="form-control form-control-sm" name="chi_tiet[${idx}][gia_nhap]" value="0" min="0" step="100"></td>
        <td><input type="date" class="form-control form-control-sm" name="chi_tiet[${idx}][han_su_dung]"></td>
        <td><button type="button" class="btn btn-sm btn-outline-danger btn-remove-lo"><i class="fas fa-times"></i></button></td>
    </tr>`);
}

function buildLoData() {
    const data = { chi_tiet: [] };
    $('#danh-sach-lo tr').each(function () {
        const row = {};
        $(this).find('input, select').each(function () {
            const m = $(this).attr('name')?.match(/chi_tiet\[(\d+)\]\[(.+)\]/);
            if (m) { const [, , f] = m; row[f] = $(this).val(); }
        });
        if (Object.keys(row).length) data.chi_tiet.push(row);
    });
    $('#form-tao-lo-hang').serializeArray().forEach(({name, value}) => { if (!name.startsWith('chi_tiet')) data[name] = value; });
    return data;
}

function taiLoHang(page = 1) {
    currentPage = page;
    const q = $('#filter-q').val();
    const ncc = $('#filter-ncc').val();
    const params = new URLSearchParams({ page, q: q || '', id_nha_cung_cap: ncc || '' });

    $.get('/admin/api/lo-hang?' + params.toString(), res => {
        const items = res.data?.data || [];
        if (!items.length) {
            $('#bang-lo-hang').html('<tr><td colspan="9" class="text-center text-muted py-4">Chưa có lô hàng nào.</td></tr>');
            $('#phan-trang').html('');
            $('#thong-tin-hien-thi').text('Hiển thị 0 lô hàng');
            return;
        }
        const html = items.map(item => {
            const tongNhap = item.chi_tiet_lo_hang_sum_so_luong_nhap || 0;
            const tongTon = item.chi_tiet_lo_hang_sum_so_luong_ton || 0;
            const ncc = item.nha_cung_cap?.ten_nha_cung_cap || '<span class="text-muted">--</span>';
            const ngay = item.ngay_nhap || '';
            const maLo = item.ma_lo || 'L-' + item.id;

            const hsds = item.chi_tiet_lo_hang?.map(c => c.han_su_dung?.slice(0, 10)) || [];
            const hsdMin = [...hsds].sort()[0];
            const today = new Date(); today.setHours(0,0,0,0);
            const diff = hsdMin ? Math.ceil((new Date(hsdMin) - today) / 86400000) : null;
            let hsdBadge = '', rowClass = '';
            if (hsdMin) {
                if (diff < 0) { hsdBadge = `<span class="badge bg-danger badge-hsd">Hết HSD</span>`; rowClass = 'table-danger'; }
                else if (diff <= 30) { hsdBadge = `<span class="badge bg-warning text-dark badge-hsd">${hsdMin} (${diff}ngày)</span>`; rowClass = 'table-warning'; }
                else { hsdBadge = `<span class="badge bg-success badge-hsd">${hsdMin}</span>`; }
            }

            const loBadge = tongTon > 0
                ? '<span class="badge bg-success">Còn hàng</span>'
                : '<span class="badge bg-secondary">Hết hàng</span>';

            return `<tr class="${rowClass}">
                <td>${item.id}</td>
                <td><span class="badge bg-dark">${maLo}</span></td>
                <td>${ncc}</td>
                <td>${ngay}</td>
                <td class="text-center">${tongNhap.toLocaleString()}</td>
                <td class="text-center"><strong>${tongTon.toLocaleString()}</strong></td>
                <td class="text-center">${hsdBadge}</td>
                <td class="text-center">${loBadge}</td>
                <td class="text-center">
                    <button class="btn btn-sm btn-outline-primary btn-xem-lo" data-id="${item.id}"><i class="fas fa-eye"></i></button>
                    <button class="btn btn-sm btn-outline-warning btn-sua-lo" data-id="${item.id}"><i class="fas fa-edit"></i></button>
                    <button class="btn btn-sm btn-outline-danger btn-xoa-lo" data-id="${item.id}"><i class="fas fa-trash"></i></button>
                </td>
            </tr>`;
        }).join('');
        $('#bang-lo-hang').html(html);
        $('#thong-tin-hien-thi').text(`Hiển thị ${items.length} / ${res.data.total || items.length} lô hàng`);
        renderPagination(page, res.data.last_page || 1);
    });
}

$(document).on('click', '.btn-xem-lo', function () {
    const id = $(this).data('id');
    $.get('/admin/api/lo-hang/' + id, res => {
        if (!res.success) return;
        const lo = res.data;
        const tongNhap = lo.chi_tiet_lo_hang?.reduce((s, c) => s + (c.so_luong_nhap || 0), 0) || 0;
        const tongTon = lo.chi_tiet_lo_hang?.reduce((s, c) => s + (c.so_luong_ton || 0), 0) || 0;
        const rows = (lo.chi_tiet_lo_hang || []).map(ct => {
            const sp = ct.san_pham || {};
            const diff = Math.ceil((new Date(ct.han_su_dung) - new Date()) / 86400000);
            let hsdClass = 'text-success';
            if (diff < 0) hsdClass = 'text-danger';
            else if (diff <= 30) hsdClass = 'text-warning';
            return `<tr>
                <td>${sp.ten_san_pham || ct.id_san_pham}</td>
                <td class="text-center">${(ct.so_luong_nhap || 0).toLocaleString()}</td>
                <td class="text-center">${(ct.so_luong_ton || 0).toLocaleString()}</td>
                <td class="text-center">${Number(ct.gia_nhap || 0).toLocaleString()} đ</td>
                <td class="text-center ${hsdClass} fw-semibold">${ct.han_su_dung?.slice(0, 10)} (${diff > 0 ? diff + ' ngày' : 'Hết HSD'})</td>
                <td class="text-center">${ct.so_luong_ton > 0 ? '<span class="badge bg-success">Còn</span>' : '<span class="badge bg-secondary">Hết</span>'}</td>
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
            </table>`);
        new bootstrap.Modal(document.getElementById('modal-xem-lo-hang')).show();
    });
});

$(document).on('click', '.btn-sua-lo', function () {
    const id = $(this).data('id');
    $.get('/admin/api/lo-hang/' + id, res => {
        if (!res.success) return;
        const lo = res.data;
        $('#sua-lo-id').val(lo.id);
        $('#sua-lo-ncc').val(lo.id_nha_cung_cap || '');
        $('#sua-lo-ma').val(lo.ma_lo || '');
        $('#sua-lo-ngay').val(lo.ngay_nhap || '');
        $('#sua-lo-ghi-chu').val(lo.ghi_chu || '');
        new bootstrap.Modal(document.getElementById('modal-sua-lo-hang')).show();
    });
});

$(document).on('click', '.btn-xoa-lo', function () {
    if (!confirm('Xóa lô hàng này?')) return;
    const id = $(this).data('id');
    $.ajax({ url: '/admin/api/lo-hang/' + id, method: 'DELETE',
        success: res => { hienThongBao('success', res.message); taiLoHang(currentPage); loadStats(); },
        error: x => hienThongBao('danger', x.responseJSON?.message || 'Lỗi.')
    });
});

function renderPagination(current, total) {
    if (total <= 1) { $('#phan-trang').html(''); return; }
    let html = '<ul class="pagination mb-0">';
    if (current > 1) html += `<li class="page-item"><a class="page-link" href="javascript:;" onclick="taiLoHang(${current - 1})">‹</a></li>`;
    for (let i = 1; i <= total; i++) {
        if (i === 1 || i === total || Math.abs(i - current) <= 2)
            html += `<li class="page-item ${i === current ? 'active' : ''}"><a class="page-link" href="javascript:;" onclick="taiLoHang(${i})">${i}</a></li>`;
        else if (Math.abs(i - current) === 3) html += '<li class="page-item disabled"><span class="page-link">...</span></li>';
    }
    if (current < total) html += `<li class="page-item"><a class="page-link" href="javascript:;" onclick="taiLoHang(${current + 1})">›</a></li>`;
    html += '</ul>';
    $('#phan-trang').html(html);
}

function hienThongBao(type, message) {
    $('#alert-container').html(`<div class="alert alert-${type} alert-dismissible fade show" role="alert">${message}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>`);
    setTimeout(() => $('.alert').fadeOut(), 4000);
}
</script>
@endsection
