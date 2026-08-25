// ============================================================
// PHIEU NHAP CREATE PAGE
// Toàn bộ logic tìm kiếm sản phẩm, thêm vào bảng và submit
// cho trang "Tạo phiếu nhập" (không còn là modal).
// ============================================================

let pnIdx = 0;
let selectedPnProducts = new Set();

// Helpers for HTML escaping (tránh lỗi quote khi tên có dấu nháy)
function escapeHtml(s) {
    if (s === null || s === undefined) return '';
    return String(s)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
}
function escapeAttr(s) {
    if (s === null || s === undefined) return '';
    return String(s).replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/'/g, '&#39;');
}

// Hiển thị thông báo nhỏ trong container riêng của trang
function hienBaoPage(level, msg) {
    if (typeof toastr !== 'undefined') {
        toastr[level](msg);
    } else {
        console[level === 'danger' ? 'error' : 'log']('[PN-CREATE]', msg);
    }
}

// ─── INIT ───────────────────────────────────────────────
$(function () {
    console.log('[PHIEU-NHAP-CREATE] Init start');

    loadDanhMucNhapPage();

    // Submit form (Lưu phiếu nhập hoặc Enter trong form)
    $('#form-tao-pn-page').submit(function (e) {
        e.preventDefault();
        submitPhieuNhapPage();
    });
    $('#pn-btn-save').click(function (e) {
        e.preventDefault();
        submitPhieuNhapPage();
    });

    // Auto-focus vào ô tìm kiếm
    $('#pn-sp-search').focus();
    console.log('[PHIEU-NHAP-CREATE] Init end');
});

// ─── LOADERS ─────────────────────────────────────────────
function loadDanhMucNhapPage() {
    $.get('/admin/api/san-pham', { q: '', danh_muc: '' }, function (res) {
        if (!res.danh_muc_list) return;
        $('#pn-sp-danh-muc').html('<option value="">Tất cả danh mục</option>' +
            res.danh_muc_list.map(function (dm) {
                return '<option value="' + dm.id + '">' + escapeHtml(dm.ten_danh_muc) + '</option>';
            }).join(''));
    });
}

// ─── PRODUCT SEARCH ─────────────────────────────────────
let pnSearchTimer = 0;
$('#pn-sp-search').on('input', function () {
    clearTimeout(pnSearchTimer);
    pnSearchTimer = setTimeout(function () {
        searchProductsNhap($('#pn-sp-search').val(), $('#pn-sp-danh-muc').val());
    }, 300);
});

$('#pn-sp-danh-muc').on('change', function () {
    searchProductsNhap($('#pn-sp-search').val(), $('#pn-sp-danh-muc').val());
});

$('#pn-sp-clear').click(function () {
    $('#pn-sp-search').val('');
    $('#pn-sp-results').html(
        '<div class="text-center text-muted py-4">' +
        '<i class="fas fa-search fs-3 mb-2 d-block"></i>' +
        'Nhập tên hoặc mã vạch để tìm sản phẩm</div>'
    );
});

function searchProductsNhap(q, danhMuc) {
    if (!q && !danhMuc) {
        $('#pn-sp-results').html(
            '<div class="text-center text-muted py-4">' +
            '<i class="fas fa-search fs-3 mb-2 d-block"></i>' +
            'Nhập tên hoặc mã vạch để tìm sản phẩm</div>'
        );
        return;
    }
    $('#pn-sp-results').html('<div class="text-center py-4"><i class="fas fa-spinner fa-spin text-muted fs-4"></i></div>');
    $.get('/admin/api/san-pham', { q: q || '', danh_muc: danhMuc || '' }, function (res) {
        if (!res.success || !res.data.length) {
            $('#pn-sp-results').html('<div class="text-center text-muted py-4"><i class="fas fa-box-open fs-3 mb-2 d-block text-secondary"></i>Không tìm thấy sản phẩm nào.</div>');
            return;
        }
        const rows = res.data.map(function (sp) {
            const variants = sp.bien_the || [];
            const hasVariants = variants.length > 1;
            const totalTon = variants.reduce(function (sum, bt) { return sum + (bt.chi_tiet_lo_hang_ton || 0); }, 0);
            const tonClass = totalTon === 0 ? 'text-danger' : totalTon < 10 ? 'text-warning' : 'text-success';
            const img = sp.hinh_anh
                ? '<img src="/' + sp.hinh_anh + '" width="40" height="40" class="rounded" style="object-fit:cover" onerror="this.style.display=\'none\';this.nextElementSibling.style.display=\'flex\'">' +
                  '<div class="bg-light rounded d-flex align-items-center justify-content-center" style="width:40px;height:40px;display:none"><i class="fas fa-box text-secondary"></i></div>'
                : '<div class="bg-light rounded d-flex align-items-center justify-content-center" style="width:40px;height:40px"><i class="fas fa-box text-secondary"></i></div>';
            const danhMucName = (sp.danh_muc && sp.danh_muc.ten_danh_muc) || '';
            const parentVariantId = variants[0] ? variants[0].id : undefined;
            const isParentSelected = selectedPnProducts.has(parentVariantId);
            const expandBtn = hasVariants
                ? '<button class="btn btn-sm btn-light border btn-toggle-variants" data-parent="' + sp.id + '" title="Mở rộng biến thể"><i class="fas fa-chevron-down"></i></button>'
                : '';
            const parentBtnClass = isParentSelected ? 'btn-secondary' : 'btn-primary';
            const parentBtnIcon = isParentSelected ? 'fa-check' : 'fa-plus';
            const parentBtnText = isParentSelected ? 'Đã chọn' : 'Chọn';
            const parentDisabled = isParentSelected ? 'disabled' : '';

            const variantRows = variants.map(function (bt) {
                const btTon = bt.chi_tiet_lo_hang_ton || 0;
                const btTonClass = btTon === 0 ? 'text-danger' : btTon < 10 ? 'text-warning' : 'text-success';
                const isBtSelected = selectedPnProducts.has(bt.id);
                const btBtnClass = isBtSelected ? 'btn-secondary' : 'btn-success';
                const btBtnIcon = isBtSelected ? 'fa-check' : 'fa-plus';
                const btBtnText = isBtSelected ? 'Đã chọn' : 'Chọn';
                const btDisabled = isBtSelected ? 'disabled' : '';
                const attrs = (bt.thuoc_tinh_labels || []).map(function (tt) {
                    return '<span class="badge variant-chip" style="background:#e9ecef;color:#495057;border:1px solid #dee2e6">' + escapeHtml(tt) + '</span>';
                }).join(' ');
                const unitsJson = JSON.stringify(bt.units || []).replace(/'/g, '&#39;');
                const tenBt = sp.ten_san_pham + (attrs ? ' - ' + (bt.ten_bien_the || '') : '');
                return '' +
                '<tr class="variant-sub-row" data-parent="' + sp.id + '" style="display:none">' +
                    '<td class="text-center align-middle"><div class="vr" style="width:2px;height:30px;opacity:0.4"></div></td>' +
                    '<td class="align-middle ps-1">' +
                        '<div class="small"><span class="text-muted">\\</span> ' + (attrs || '<span class="text-muted small">Không có thuộc tính</span>') + '</div>' +
                    '</td>' +
                    '<td class="align-middle"><code class="small">' + (bt.ma_vach || '--') + '</code></td>' +
                    '<td class="text-end align-middle small">' + Number(bt.gia_ban || 0).toLocaleString() + ' d</td>' +
                    '<td class="text-center align-middle"><span class="fw-semibold ' + btTonClass + '">' + btTon.toLocaleString() + '</span></td>' +
                    '<td class="text-center align-middle">' +
                        '<button class="btn btn-sm ' + btBtnClass + ' btn-chon-sp-nhap"' +
                            ' data-id="' + bt.id + '"' +
                            ' data-ten="' + escapeAttr(tenBt) + '"' +
                            ' data-gia="' + (bt.gia_ban || 0) + '"' +
                            ' data-variant="1"' +
                            ' data-base-name="' + escapeAttr(bt.ten_bien_the || bt.ten_don_vi || 'Cơ bản') + '"' +
                            ' data-base-unit="' + escapeAttr(bt.ten_don_vi || '') + '"' +
                            " data-units='" + unitsJson + "'" +
                            ' ' + btDisabled + '>' +
                            '<i class="fas ' + btBtnIcon + '"></i> ' + btBtnText +
                        '</button>' +
                    '</td>' +
                '</tr>';
            });

            return [
                '<tr class="parent-row" data-id="' + sp.id + '">' +
                    '<td class="text-center align-middle">' + img + '</td>' +
                    '<td class="align-middle">' +
                        '<div class="fw-semibold small">' + escapeHtml(sp.ten_san_pham) + '</div>' +
                        '<div class="small text-muted">' + escapeHtml(danhMucName) + '</div>' +
                        (hasVariants ? '<div class="mt-1"><span class="badge bg-secondary" style="font-size:0.68rem">' + variants.length + ' biến thể</span></div>' : '') +
                    '</td>' +
                    '<td class="align-middle"><code class="small">' + (variants[0] && variants[0].ma_vach ? variants[0].ma_vach : '--') + '</code></td>' +
                    '<td class="text-end align-middle small">' + Number((variants[0] && variants[0].gia_ban) || 0).toLocaleString() + ' d</td>' +
                    '<td class="text-center align-middle"><span class="fw-semibold ' + tonClass + '">' + totalTon.toLocaleString() + '</span></td>' +
                    '<td class="text-center align-middle">' +
                        expandBtn +
                        (!hasVariants ? (
                            '<button class="btn btn-sm ' + parentBtnClass + ' btn-chon-sp-nhap"' +
                                ' data-id="' + parentVariantId + '"' +
                                ' data-ten="' + escapeAttr(sp.ten_san_pham) + '"' +
                                ' data-gia="' + ((variants[0] && variants[0].gia_ban) || 0) + '"' +
                                ' data-base-name="' + escapeAttr(((variants[0] && variants[0].ten_bien_the) || (variants[0] && variants[0].ten_don_vi) || 'Cơ bản')) + '"' +
                                ' data-base-unit="' + escapeAttr(((variants[0] && variants[0].ten_don_vi) || '')) + '"' +
                                " data-units='" + JSON.stringify((variants[0] && variants[0].units) || []).replace(/'/g, '&#39;') + "'" +
                                ' ' + parentDisabled + '>' +
                                '<i class="fas ' + parentBtnIcon + '"></i> ' + parentBtnText +
                            '</button>'
                        ) : '') +
                    '</td>' +
                '</tr>',
                ...variantRows
            ];
        });
        const tableHtml = '' +
            '<table class="table table-sm table-hover mb-0">' +
                '<thead class="table-light"><tr>' +
                    '<th style="width:50px"></th>' +
                    '<th>Sản phẩm</th>' +
                    '<th style="width:110px">Mã vạch</th>' +
                    '<th style="width:110px" class="text-end">Giá bán</th>' +
                    '<th style="width:80px" class="text-center">Tồn kho</th>' +
                    '<th style="width:120px" class="text-center">Chọn</th>' +
                '</tr></thead>' +
                '<tbody>' + rows.join('') + '</tbody>' +
            '</table>';
        $('#pn-sp-results').html(tableHtml);
    });
}

// ─── TOGGLE VARIANTS & CHỌN SẢN PHẨM ───────────────────
$(document).on('click', '.btn-toggle-variants', function () {
    const parentId = $(this).data('parent');
    const icon = $(this).find('i');
    const isExpanded = icon.hasClass('fa-chevron-down');
    if (isExpanded) {
        $('.variant-sub-row[data-parent="' + parentId + '"]').hide();
        icon.removeClass('fa-chevron-down').addClass('fa-chevron-right');
    } else {
        $('.variant-sub-row[data-parent="' + parentId + '"]').show();
        icon.removeClass('fa-chevron-right').addClass('fa-chevron-down');
    }
});

$(document).on('click', '.btn-chon-sp-nhap', function () {
    const id = $(this).data('id');
    if (selectedPnProducts.has(id)) return;
    selectedPnProducts.add(id);

    const ten = $(this).data('ten');
    const gia = $(this).data('gia');
    const units = $(this).data('units') || [];
    const variantBaseName = $(this).data('base-name') || 'Cơ bản';
    const baseUnitLabel = $(this).data('base-unit') || variantBaseName || 'đơn vị cơ bản';
    const idx = pnIdx++;
    $('#pn-empty-row').remove();

    // Build option đơn vị: đơn vị cơ bản (he_so=1) + đơn vị quy đổi
    const baseOpt = '<option value="__base__" data-he-so="1" data-ten="' + escapeAttr(baseUnitLabel) + '" data-don-vi-id="" selected>' + escapeHtml(variantBaseName) + ' (đơn vị cơ bản)</option>';
    const unitOpts = units.map(function (u) {
        return '<option value="' + u.id + '" data-he-so="' + (u.so_luong_san_pham_trong_don_vi || 1) + '" data-ten="' + escapeAttr(u.ten_don_vi) + '" data-don-vi-id="' + u.id + '">' + escapeHtml(u.ten_don_vi) + ' (×' + (u.so_luong_san_pham_trong_don_vi || 1) + ')</option>';
    }).join('');
    const donViSelect = '<select class="form-select form-select-sm pn-don-vi-select" name="chi_tiet[' + idx + '][don_vi_id]" data-idx="' + idx + '">' + baseOpt + unitOpts + '</select>';
    const quyDoiHint = units.length
        ? '<small class="text-muted pn-quy-doi-hint d-block mt-1">Mặc định nhập theo đơn vị cơ bản. Đổi sang "Thùng", "Hộp"... nếu mua theo đơn vị quy đổi.</small>'
        : '';

    const row = '' +
    '<tr data-sp-id="' + id + '">' +
        '<td>' +
            '<div class="fw-semibold small">' + escapeHtml(ten) + '</div>' +
            '<input type="hidden" name="chi_tiet[' + idx + '][variant_id]" value="' + id + '">' +
            '<input type="hidden" name="chi_tiet[' + idx + '][so_luong_san_pham_trong_don_vi]" value="1" class="pn-he-so-hidden">' +
            (units.length ? '<div class="mt-1 d-flex align-items-center gap-1">' + donViSelect + quyDoiHint + '</div>' : '') +
        '</td>' +
        '<td>' +
            '<input type="number" class="form-control form-control-sm pn-sl-input" name="chi_tiet[' + idx + '][so_luong_nhap]" value="1" min="0.0001" step="0.0001" data-idx="' + idx + '">' +
            (units.length ? '<small class="text-muted pn-sl-display d-block mt-1" data-idx="' + idx + '">= 1 ' + escapeHtml(baseUnitLabel) + '</small>' : '') +
        '</td>' +
        '<td>' +
            '<input type="text" class="form-control form-control-sm pn-gia-input money-input" name="chi_tiet[' + idx + '][gia_nhap]" value="' + (gia ? Number(gia).toLocaleString('vi-VN') : '') + '" inputmode="numeric">' +
            (units.length
                ? '<small class="text-muted pn-gia-display d-block mt-1" data-idx="' + idx + '">đơn giá / ' + escapeHtml(baseUnitLabel) + '</small>'
                : '<small class="text-muted d-block mt-1">đơn giá / ' + escapeHtml(baseUnitLabel) + '</small>') +
        '</td>' +
        '<td><input type="date" class="form-control form-control-sm" name="chi_tiet[' + idx + '][han_su_dung]" value=""></td>' +
        '<td><button type="button" class="btn btn-sm btn-outline-danger btn-remove-pn-row" data-id="' + id + '"><i class="fas fa-times"></i></button></td>' +
    '</tr>';
    $('#pn-ds-sp').append(row);
    $(this).prop('disabled', true).removeClass('btn-primary btn-success').addClass('btn-secondary').html('<i class="fas fa-check"></i> Đã chọn');

    updateTongSL();
});

// Tính số lượng quy đổi realtime khi user đổi đơn vị hoặc số lượng / giá
$(document).on('input change', '.pn-sl-input, .pn-gia-input, .pn-don-vi-select', function () {
    const $row = $(this).closest('tr');
    const $slInput = $row.find('.pn-sl-input');
    const $giaInput = $row.find('.pn-gia-input');
    const $donViSelect = $row.find('.pn-don-vi-select');
    const $heSoHidden = $row.find('.pn-he-so-hidden');
    const $display = $row.find('.pn-sl-display');
    const $giaDisplay = $row.find('.pn-gia-display');

    const sl = parseFloat($slInput.val()) || 0;
    // $giaInput có thể chứa dấu chấm do money-input format -> phải bỏ trước khi parseFloat
    const gia = parseFloat(String($giaInput.val()).replace(/\./g, '')) || 0;
    let heSo = 1;
    let tenDonViNhap = '';
    let tenDonViCoBan = '';
    if ($donViSelect.length) {
        const opt = $donViSelect.find('option:selected');
        heSo = parseFloat(opt.data('he-so')) || 1;
        tenDonViNhap = opt.data('ten') || opt.text();
        const baseOpt = $donViSelect.find('option[value="__base__"]');
        tenDonViCoBan = baseOpt.data('ten') || '';
    }
    $heSoHidden.val(heSo);

    if ($display.length) {
        if (heSo === 1) {
            $display.html('= <strong>' + sl.toLocaleString() + '</strong> ' + escapeHtml(tenDonViCoBan || 'đơn vị cơ bản'));
        } else {
            const tong = sl * heSo;
            const donGiaCoBan = gia / heSo;
            $display.html(
                '= <strong class="text-primary">' + tong.toLocaleString() + '</strong> ' + escapeHtml(tenDonViCoBan || 'đơn vị cơ bản') +
                ' <span class="text-muted">(1 ' + escapeHtml(tenDonViNhap) + ' = ' + heSo + ' ' + escapeHtml(tenDonViCoBan || 'đơn vị cơ bản') + ')</span>'
            );
            if ($giaDisplay.length) {
                $giaDisplay.html(
                    'đơn giá / ' + escapeHtml(tenDonViNhap) +
                    ' → <strong class="text-info">' + donGiaCoBan.toLocaleString(undefined, { maximumFractionDigits: 2 }) + '</strong>' +
                    ' / ' + escapeHtml(tenDonViCoBan || 'đơn vị cơ bản')
                );
            }
        }
    }

    updateTongSL();
});

$(document).on('click', '.btn-remove-pn-row', function () {
    const tr = $(this).closest('tr');
    const spId = parseInt(tr.data('sp-id'));
    selectedPnProducts.delete(spId);
    tr.remove();
    if (!$('#pn-ds-sp tr').length) {
        $('#pn-ds-sp').html(
            '<tr id="pn-empty-row">' +
                '<td colspan="5" class="text-center text-muted py-3">' +
                    'Chưa chọn sản phẩm nào. Vui lòng tìm kiếm và bấm "Chọn" phía trên.' +
                '</td>' +
            '</tr>'
        );
    }
    // Re-enable nút chọn sản phẩm (nếu còn)
    const btn = $('.btn-chon-sp-nhap[data-id="' + spId + '"]');
    if (btn.length) {
        const isVariant = btn.data('variant') == 1;
        btn.prop('disabled', false).removeClass('btn-secondary').addClass(isVariant ? 'btn-success' : 'btn-primary').html('<i class="fas fa-plus"></i> Chọn');
    }
    updateTongSL();
});

// Cập nhật tổng số dòng sản phẩm đã chọn
function updateTongSL() {
    const rows = $('#pn-ds-sp tr:not(#pn-empty-row)').length;
    $('#pn-tong-sl').text(rows);
}

// ─── BUILD CHI TIET (mapping input -> payload API) ──────
function buildChiTietPage(tableId) {
    const rows = [];
    $('#' + tableId + ' tr').each(function () {
        const row = {};
        $(this).find('input, select').each(function () {
            const name = $(this).attr('name');
            if (!name) return;
            const m = name.match(new RegExp('chi_tiet\\[\\d+\\]\\[(.+)\\]'));
            if (m) row[m[1]] = $(this).val();
        });
        // Đổi '__base__' về rỗng để backend hiểu là đơn vị cơ bản
        if (row.don_vi_id === '__base__') row.don_vi_id = '';
        if (Object.keys(row).length) rows.push(row);
    });
    return rows;
}

// ─── SUBMIT PHIẾU NHẬP ───────────────────────────────────
function submitPhieuNhapPage() {
    const chiTiet = buildChiTietPage('pn-ds-sp');
    if (!chiTiet.length) {
        $('#pn-alert-container-page').html('<div class="alert alert-warning"><i class="fas fa-exclamation-triangle me-1"></i>Vui lòng thêm ít nhất một sản phẩm trước khi lưu.</div>');
        hienBaoPage('warning', 'Vui lòng thêm ít nhất một sản phẩm trước khi lưu.');
        return;
    }
    $('#pn-alert-container-page').html('');

    // Validate các field bắt buộc (han_su_dung)
    let invalid = 0;
    chiTiet.forEach(function (r, i) {
        if (!r.han_su_dung) {
            invalid++;
        }
    });
    if (invalid > 0) {
        const msg = 'Vui lòng nhập Hạn sử dụng cho tất cả sản phẩm.';
        $('#pn-alert-container-page').html('<div class="alert alert-danger">' + msg + '</div>');
        hienBaoPage('danger', msg);
        return;
    }

    const btn = $('#pn-btn-save');
    btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i>Đang lưu...');

    const data = {
        loai_nhap: $('#pn-loai').val(),
        id_nha_cung_cap: $('#pn-ncc').val() || null,
        ghi_chu: $('#pn-ghi-chu').val(),
        tao_lo_moi: '1',
        id_lo_hang: '',
        chi_tiet: chiTiet,
    };

    $.ajax({
        url: '/admin/api/phieu-nhap',
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
        contentType: 'application/json',
        data: JSON.stringify(data),
        success: function (res) {
            btn.prop('disabled', false).html('<i class="fas fa-save me-1"></i>Lưu phiếu nhập');
            hienBaoPage('success', res.message || 'Tạo phiếu nhập thành công.');
            // Chuyển về trang danh sách kho hàng sau khi lưu thành công
            setTimeout(function () {
                window.location.href = '/admin/kho-hang';
            }, 800);
        },
        error: function (x) {
            btn.prop('disabled', false).html('<i class="fas fa-save me-1"></i>Lưu phiếu nhập');
            const msg = (x.responseJSON && x.responseJSON.message) || 'Có lỗi xảy ra khi lưu nhập hàng.';
            $('#pn-alert-container-page').html('<div class="alert alert-danger">' + msg + '</div>');
            hienBaoPage('danger', msg);
        }
    });
}
