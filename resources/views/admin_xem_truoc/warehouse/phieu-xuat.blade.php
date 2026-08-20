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
        <a href="{{ url('admin/kho-hang/phieu-xuat/create') }}" class="btn btn-danger btn-sm">
            <i class="fas fa-plus me-1"></i>Tạo phiếu xuất
        </a>
        <a href="{{ url('admin/kho-hang/phieu-xuat/import') }}" class="btn btn-danger btn-sm" title="Import từ Excel">
            <i class="fas fa-file-import me-1"></i>Import Excel
        </a>
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

@section('scripts')
<script>
let sanPhamListXuat = [];
let currentPageXuat = 1;

$(function () {
    taiPhieuXuat();

    $('#btn-loc-phieu-xuat').click(() => taiPhieuXuat(1));

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
});

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
                    <a href="/admin/kho-hang/phieu-xuat/${item.id}" class="btn btn-sm btn-outline-primary" title="Xem chi tiết"><i class="fas fa-eye"></i></a>
                    <a href="/admin/kho-hang/phieu-xuat/${item.id}/edit" class="btn btn-sm btn-outline-warning" title="Sửa"><i class="fas fa-edit"></i></a>
                    <button class="btn btn-sm btn-outline-danger btn-xoa-px" data-id="${item.id}" title="Xóa"><i class="fas fa-trash"></i></button>
                </td>
            </tr>`;
        }).join('');
        $('#bang-phieu-xuat').html(html);
        $('#thong-tin-hien-thi-xuat').text(`Hiển thị ${items.length} / ${res.data.total || items.length} phiếu xuất`);
        renderPagination(page, res.data.last_page || 1);
    });
}

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
</script>
@endsection
