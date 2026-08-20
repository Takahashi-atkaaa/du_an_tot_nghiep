@extends('admin_xem_truoc.layouts.admin')

@section('title', 'Tạo Phiếu xuất - SmartMart')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1"><i class="fas fa-arrow-up me-2 text-danger"></i>Tạo Phiếu xuất</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('admin/dashboard') }}">Admin</a></li>
                <li class="breadcrumb-item"><a href="{{ url('admin/kho-hang/phieu-xuat') }}">Phiếu xuất</a></li>
                <li class="breadcrumb-item active">Tạo mới</li>
            </ol>
        </nav>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ url('admin/kho-hang/phieu-xuat') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i> Quay lại
        </a>
    </div>
</div>

<div id="alert-container-xuat-tao"></div>

{{-- Form tạo phiếu xuất --}}
<div class="row">
    <div class="col-lg-8">
        <div class="card table-admin mb-4">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0"><i class="fas fa-list me-2 text-danger"></i>Chi tiết sản phẩm xuất</h5>
            </div>
            <div class="card-body">
                <form id="form-tao-phieu-xuat-full">
                    <div class="alert alert-warning py-2 small mb-3">
                        <i class="fas fa-info-circle me-1"></i>
                        Hệ thống tự động xuất hàng theo nguyên tắc <strong>FEFO</strong> — ưu tiên lô có HSD gần nhất.
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <label class="form-label">Loại xuất <span class="text-danger">*</span></label>
                            <select name="loai_xuat" class="form-select" id="px-loai-xuat-full" required>
                                <option value="tra_hang_nha_cung_cap">Trả hàng NCC</option>
                                <option value="tieu_huy">Tiêu hủy</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Nhà cung cấp</label>
                            <select name="id_nha_cung_cap" id="px-id-ncc-full" class="form-select">
                                <option value="">-- Chọn NCC --</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Lý do</label>
                            <input type="text" name="ly_do" class="form-control" id="px-ly-do-full" placeholder="Lý do xuất hàng...">
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="mb-0">Danh sách sản phẩm</h6>
                        <button type="button" class="btn btn-sm btn-outline-primary" id="btn-them-sp-xuat-full">
                            <i class="fas fa-plus me-1"></i>Thêm sản phẩm
                        </button>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered mb-0" id="bang-sp-xuat-full">
                            <thead class="table-light">
                                <tr>
                                    <th style="width:40%">Sản phẩm / Biến thể</th>
                                    <th class="text-center" style="width:15%">Tồn kho</th>
                                    <th class="text-center" style="width:15%">SL xuất</th>
                                    <th class="text-center" style="width:15%">FEFO - Lô</th>
                                    <th style="width:10%"></th>
                                </tr>
                            </thead>
                            <tbody id="danh-sach-sp-xuat-full">
                                {{-- Dynamic rows here --}}
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3 text-end">
                        <h5>Tổng SL: <span id="tong-sl-xuat-full" class="text-danger">0</span></h5>
                    </div>
                </form>
            </div>
            <div class="card-footer bg-white">
                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ url('admin/kho-hang/phieu-xuat') }}" class="btn btn-secondary">Hủy</a>
                    <button type="button" class="btn btn-danger" id="btn-luu-phieu-xuat-full">
                        <i class="fas fa-save me-1"></i>Lưu phiếu xuất
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        {{-- Preview FEFO --}}
        <div class="card table-admin mb-4">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0"><i class="fas fa-list-check me-2"></i>Preview FEFO</h6>
            </div>
            <div class="card-body" id="fefo-preview-full">
                <p class="text-muted mb-0 small">Thêm sản phẩm để xem preview FEFO...</p>
            </div>
        </div>

        {{-- Hướng dẫn --}}
        <div class="card table-admin">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0"><i class="fas fa-question-circle me-2"></i>Hướng dẫn</h6>
            </div>
            <div class="card-body small">
                <ul class="mb-0 ps-3">
                    <li><strong>Trả hàng NCC:</strong> Xuất hàng để trả lại cho nhà cung cấp</li>
                    <li><strong>Tiêu hủy:</strong> Xuất hàng hỏng, hết hạn để tiêu hủy</li>
                    <li><strong>FEFO:</strong> Hệ thống tự động chọn lô có HSD gần nhất</li>
                    <li><strong>SL xuất:</strong> Số lượng muốn xuất cho mỗi biến thể</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
let sanPhamListXuatFull = [];
let chiTietXuatFullIndex = 0;

$(function () {
    taiSanPhamXuatFull();
    taiNhaCungCapXuatFull();
    addPxRowFull(); // Add first row

    $('#btn-them-sp-xuat-full').click(() => addPxRowFull());

    $(document).on('click', '.btn-remove-sp-xuat-full', function () {
        const rows = $('#danh-sach-sp-xuat-full tr').length;
        if (rows > 1) {
            $(this).closest('tr').remove();
            tinhTongXuatFull();
            updateFefoPreviewFull();
        }
    });

    $(document).on('change', '.px-sp-select-full', function () {
        const row = $(this).closest('tr');
        const val = $(this).val();
        const tonCell = row.find('.ton-kho-cell-full');
        const fefoCell = row.find('.fefo-cell-full');
        const slInput = row.find('.px-sl-input-full');

        if (val) {
            const opt = $(this).find('option:selected');
            tonCell.text('Tồn: ' + ((opt.data('ton') || 0)).toLocaleString());
            loadFefoForVariant(row, val, slInput.val() || 1);
        } else {
            tonCell.text('--');
            fefoCell.html('--');
        }
        updateFefoPreviewFull();
    });

    $(document).on('input', '.px-sl-input-full', function () {
        const row = $(this).closest('tr');
        const spSelect = row.find('.px-sp-select-full');
        if (spSelect.val()) {
            loadFefoForVariant(row, spSelect.val(), $(this).val() || 1);
        }
        tinhTongXuatFull();
        updateFefoPreviewFull();
    });

    // Save button
    $('#btn-luu-phieu-xuat-full').click(function () {
        const chiTiet = [];
        let hasError = false;

        $('#danh-sach-sp-xuat-full tr').each(function () {
            const row = $(this);
            const variantId = row.find('.px-sp-select-full').val();
            const sl = parseInt(row.find('.px-sl-input-full').val()) || 0;
            const loSelect = row.find('.fefo-lo-select-full');

            if (!variantId || sl <= 0) return;

            const loId = loSelect.val();
            if (!loId) {
                hasError = true;
                loSelect.addClass('is-invalid');
            } else {
                loSelect.removeClass('is-invalid');
                chiTiet.push({
                    variant_id: parseInt(variantId),
                    id_chi_tiet_lo_hang: parseInt(loId),
                    so_luong: sl
                });
            }
        });

        if (chiTiet.length === 0) {
            hienThongBaoXuatTao('warning', 'Thêm ít nhất một sản phẩm với số lượng > 0.');
            return;
        }

        if (hasError) {
            hienThongBaoXuatTao('warning', 'Vui lòng chọn lô hàng cho tất cả sản phẩm.');
            return;
        }

        const data = {
            loai_xuat: $('#px-loai-xuat-full').val(),
            id_nha_cung_cap: $('#px-id-ncc-full').val() || null,
            ly_do: $('#px-ly-do-full').val(),
            chi_tiet: chiTiet
        };

        $.ajax({
            url: '/admin/api/phieu-xuat',
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify(data),
            success: function (res) {
                hienThongBaoXuatTao('success', res.message);
                setTimeout(() => {
                    window.location.href = '/admin/kho-hang/phieu-xuat';
                }, 1500);
            },
            error: function (x) {
                hienThongBaoXuatTao('danger', x.responseJSON?.message || 'Đã xảy ra lỗi.');
            }
        });
    });
});

function taiSanPhamXuatFull() {
    $.get('/admin/api/san-pham', function (res) {
        sanPhamListXuatFull = res.data?.data || [];
    });
}

function taiNhaCungCapXuatFull() {
    $.get('/admin/api/lo-hang/nha-cung-cap', function (res) {
        const opts = res.map(n => `<option value="${n.id}">${n.ten_nha_cung_cap}</option>`).join('');
        $('#px-id-ncc-full').html('<option value="">-- Chọn NCC --</option>' + opts);
    });
}

function addPxRowFull(id, sl) {
    const idx = chiTietXuatFullIndex++;
    const opts = sanPhamListXuatFull.map(sp => {
        const ten = sp.ten_san_pham || 'SP ' + sp.id;
        const maVach = sp.ma_vach || sp.id;
        const ton = sp.tong_ton || 0;
        return `<option value="${sp.id}" data-ton="${ton}">${ten} (${maVach})</option>`;
    }).join('');

    $('#danh-sach-sp-xuat-full').append(`
        <tr data-index="${idx}">
            <td>
                <select class="form-select form-select-sm px-sp-select-full" required>
                    <option value="">-- Chọn sản phẩm --</option>
                    ${opts}
                </select>
            </td>
            <td class="text-center ton-kho-cell-full text-muted small">--</td>
            <td>
                <input type="number" class="form-control form-control-sm px-sl-input-full"
                    value="${sl || 1}" min="1" placeholder="SL">
            </td>
            <td class="fefo-cell-full text-center small text-muted">--</td>
            <td class="text-center">
                <button type="button" class="btn btn-sm btn-outline-danger btn-remove-sp-xuat-full">
                    <i class="fas fa-times"></i>
                </button>
            </td>
        </tr>
    `);
}

function loadFefoForVariant(row, variantId, soLuong) {
    const fefoCell = row.find('.fefo-cell-full');
    const sl = parseInt(soLuong) || 1;

    if (!variantId || sl <= 0) {
        fefoCell.html('--');
        return;
    }

    $.ajax({
        url: '/admin/api/lo-hang/ton-kho?id_san_pham=' + variantId,
        async: false,
        success: function (res) {
            if (!res.success || !res.data?.chi_tiet?.length) {
                fefoCell.html('<span class="text-danger small">Không có lô</span>');
                return;
            }

            const options = res.data.chi_tiet.map(ct => {
                const lo = ct.lo_hang || {};
                const maLo = lo.ma_lo || 'L-' + lo.id;
                const hsd = ct.han_su_dung ? ct.han_su_dung.slice(0, 10) : '--';
                const ton = ct.so_luong_ton || 0;
                return `<option value="${ct.id}" data-ton="${ton}">${maLo} (HSD: ${hsd}) - Còn: ${ton}</option>`;
            }).join('');

            fefoCell.html(`<select class="form-select form-select-sm fefo-lo-select-full" required>${options}</select>`);
        },
        error: function () {
            fefoCell.html('<span class="text-danger small">Lỗi tải lô</span>');
        }
    });
}

function tinhTongXuatFull() {
    let tong = 0;
    $('.px-sl-input-full').each(function () {
        tong += parseInt($(this).val()) || 0;
    });
    $('#tong-sl-xuat-full').text(tong.toLocaleString());
}

function updateFefoPreviewFull() {
    let html = '';
    let hasItem = false;

    $('#danh-sach-sp-xuat-full tr').each(function () {
        const row = $(this);
        const spSelect = row.find('.px-sp-select-full');
        const slInput = row.find('.px-sl-input-full');
        const loSelect = row.find('.fefo-lo-select-full');

        const spName = spSelect.find('option:selected').text() || '--';
        const sl = parseInt(slInput.val()) || 0;
        const loName = loSelect.find('option:selected').text() || null;

        if (spSelect.val() && sl > 0) {
            hasItem = true;
            html += `<div class="mb-2 p-2 border rounded">
                <strong>${spName}</strong> × ${sl.toLocaleString()}`;
            if (loName) {
                html += `<div class="small text-muted ms-2">→ ${loName}</div>`;
            } else {
                html += `<div class="small text-danger ms-2">⚠ Chưa chọn lô</div>`;
            }
            html += `</div>`;
        }
    });

    if (hasItem) {
        $('#fefo-preview-full').html(html);
    } else {
        $('#fefo-preview-full').html('<p class="text-muted mb-0 small">Thêm sản phẩm để xem preview FEFO...</p>');
    }
}

function hienThongBaoXuatTao(type, message) {
    $('#alert-container-xuat-tao').html(`
        <div class="alert alert-${type} alert-dismissible fade show" role="alert">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `);
    setTimeout(() => $('.alert').fadeOut(), 5000);
}
</script>
@endsection
