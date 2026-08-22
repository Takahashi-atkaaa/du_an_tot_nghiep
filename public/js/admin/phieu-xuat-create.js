// ============================================================
// PHIEU XUAT CREATE PAGE
// Toàn bộ logic tìm kiếm sản phẩm, chọn lô hàng có tồn và submit
// cho trang "Tạo phiếu xuất" (trang riêng, không dùng modal).
// ============================================================

let pxIdx = 0;
let selectedPxProducts = new Set();

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

function hienBaoPage(level, msg) {
    if (typeof toastr !== 'undefined') {
        toastr[level](msg);
    } else {
        console[level === 'danger' ? 'error' : 'log']('[PX-CREATE]', msg);
    }
}

// ─── INIT ───────────────────────────────────────────────
$(function () {
    console.log('[PHIEU-XUAT-CREATE] Init start');

    loadDanhMucXuatPage();
    toggleNccRequired();

    $('#px-loai').on('change', toggleNccRequired);

    $('#form-tao-px-page').submit(function (e) {
        e.preventDefault();
        submitPhieuXuatPage();
    });
    $('#px-btn-save').click(function (e) {
        e.preventDefault();
        submitPhieuXuatPage();
    });

    $('#px-sp-search').focus();
    console.log('[PHIEU-XUAT-CREATE] Init end');
});

// Bắt buộc NCC khi loại xuất = trả hàng NCC
function toggleNccRequired() {
    const loai = $('#px-loai').val();
    if (loai === 'tra_hang_nha_cung_cap') {
        $('#px-ncc').prop('required', true);
        $('#px-ncc-required').show();
    } else {
        $('#px-ncc').prop('required', false);
        $('#px-ncc-required').hide();
    }
}

// ─── LOADERS ─────────────────────────────────────────────
function loadDanhMucXuatPage() {
    $.get('/admin/api/san-pham', { q: '', danh_muc: '' }, function (res) {
        if (!res.danh_muc_list) return;
        $('#px-sp-danh-muc').html('<option value="">Tất cả danh mục</option>' +
            res.danh_muc_list.map(function (dm) {
                return '<option value="' + dm.id + '">' + escapeHtml(dm.ten_danh_muc) + '</option>';
            }).join(''));
    });
}

// ─── PRODUCT SEARCH ─────────────────────────────────────
let pxSearchTimer = 0;
$('#px-sp-search').on('input', function () {
    clearTimeout(pxSearchTimer);
    pxSearchTimer = setTimeout(function () {
        searchProductsXuat($('#px-sp-search').val(), $('#px-sp-danh-muc').val());
    }, 300);
});

$('#px-sp-danh-muc').on('change', function () {
    searchProductsXuat($('#px-sp-search').val(), $('#px-sp-danh-muc').val());
});

$('#px-sp-clear').click(function () {
    $('#px-sp-search').val('');
    $('#px-sp-results').html(
        '<div class="text-center text-muted py-4">' +
        '<i class="fas fa-search fs-3 mb-2 d-block"></i>' +
        'Nhập tên hoặc mã vạch để tìm sản phẩm</div>'
    );
});

function searchProductsXuat(q, danhMuc) {
    if (!q && !danhMuc) {
        $('#px-sp-results').html(
            '<div class="text-center text-muted py-4">' +
            '<i class="fas fa-search fs-3 mb-2 d-block"></i>' +
            'Nhập tên hoặc mã vạch để tìm sản phẩm</div>'
        );
        return;
    }
    $('#px-sp-results').html('<div class="text-center py-4"><i class="fas fa-spinner fa-spin text-muted fs-4"></i></div>');
    $.get('/admin/api/san-pham', { q: q || '', danh_muc: danhMuc || '' }, function (res) {
        if (!res.success || !res.data.length) {
            $('#px-sp-results').html('<div class="text-center text-muted py-4"><i class="fas fa-box-open fs-3 mb-2 d-block text-secondary"></i>Không tìm thấy sản phẩm nào.</div>');
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
            const isParentSelected = selectedPxProducts.has(parentVariantId);
            const expandBtn = hasVariants
                ? '<button class="btn btn-sm btn-light border btn-toggle-variants-px" data-parent="' + sp.id + '" title="Mở rộng biến thể"><i class="fas fa-chevron-down"></i></button>'
                : '';
            const parentBtnClass = isParentSelected ? 'btn-secondary' : 'btn-primary';
            const parentBtnIcon = isParentSelected ? 'fa-check' : 'fa-plus';
            const parentBtnText = isParentSelected ? 'Đã chọn' : 'Chọn';
            const parentDisabled = (isParentSelected || totalTon === 0) ? 'disabled' : '';

            const variantRows = variants.map(function (bt) {
                const btTon = bt.chi_tiet_lo_hang_ton || 0;
                const btTonClass = btTon === 0 ? 'text-danger' : btTon < 10 ? 'text-warning' : 'text-success';
                const isBtSelected = selectedPxProducts.has(bt.id);
                const btBtnClass = isBtSelected ? 'btn-secondary' : 'btn-danger';
                const btBtnIcon = isBtSelected ? 'fa-check' : 'fa-plus';
                const btBtnText = isBtSelected ? 'Đã chọn' : 'Chọn';
                const btDisabled = (isBtSelected || btTon === 0) ? 'disabled' : '';
                const attrs = (bt.thuoc_tinh_labels || []).map(function (tt) {
                    return '<span class="badge variant-chip" style="background:#e9ecef;color:#495057;border:1px solid #dee2e6">' + escapeHtml(tt) + '</span>';
                }).join(' ');
                const tenBt = sp.ten_san_pham + (attrs ? ' - ' + (bt.ten_bien_the || '') : '');
                return '' +
                '<tr class="variant-sub-row-px" data-parent="' + sp.id + '" style="display:none">' +
                    '<td class="text-center align-middle"><div class="vr" style="width:2px;height:30px;opacity:0.4"></div></td>' +
                    '<td class="align-middle ps-1">' +
                        '<div class="small"><span class="text-muted">\\</span> ' + (attrs || '<span class="text-muted small">Không có thuộc tính</span>') + '</div>' +
                    '</td>' +
                    '<td class="align-middle"><code class="small">' + (bt.ma_vach || '--') + '</code></td>' +
                    '<td class="text-center align-middle"><span class="fw-semibold ' + btTonClass + '">' + btTon.toLocaleString() + '</span></td>' +
                    '<td class="text-center align-middle">' +
                        '<button class="btn btn-sm ' + btBtnClass + ' btn-chon-sp-xuat"' +
                            ' data-id="' + bt.id + '"' +
                            ' data-ten="' + escapeAttr(tenBt) + '"' +
                            ' data-ton="' + btTon + '"' +
                            ' data-variant="1"' +
                            ' ' + btDisabled + '>' +
                            '<i class="fas ' + btBtnIcon + '"></i> ' + btBtnText +
                        '</button>' +
                    '</td>' +
                '</tr>';
            });

            return [
                '<tr class="parent-row-px" data-id="' + sp.id + '">' +
                    '<td class="text-center align-middle">' + img + '</td>' +
                    '<td class="align-middle">' +
                        '<div class="fw-semibold small">' + escapeHtml(sp.ten_san_pham) + '</div>' +
                        '<div class="small text-muted">' + escapeHtml(danhMucName) + '</div>' +
                        (hasVariants ? '<div class="mt-1"><span class="badge bg-secondary" style="font-size:0.68rem">' + variants.length + ' biến thể</span></div>' : '') +
                    '</td>' +
                    '<td class="align-middle"><code class="small">' + (variants[0] && variants[0].ma_vach ? variants[0].ma_vach : '--') + '</code></td>' +
                    '<td class="text-center align-middle"><span class="fw-semibold ' + tonClass + '">' + totalTon.toLocaleString() + '</span></td>' +
                    '<td class="text-center align-middle">' +
                        expandBtn +
                        (!hasVariants ? (
                            '<button class="btn btn-sm ' + parentBtnClass + ' btn-chon-sp-xuat"' +
                                ' data-id="' + parentVariantId + '"' +
                                ' data-ten="' + escapeAttr(sp.ten_san_pham) + '"' +
                                ' data-ton="' + totalTon + '"' +
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
                    '<th style="width:80px" class="text-center">Tồn kho</th>' +
                    '<th style="width:120px" class="text-center">Chọn</th>' +
                '</tr></thead>' +
                '<tbody>' + rows.join('') + '</tbody>' +
            '</table>';
        $('#px-sp-results').html(tableHtml);
    });
}

// ─── TOGGLE VARIANTS & CHỌN SẢN PHẨM ───────────────────
$(document).on('click', '.btn-toggle-variants-px', function () {
    const parentId = $(this).data('parent');
    const icon = $(this).find('i');
    const isExpanded = icon.hasClass('fa-chevron-down');
    if (isExpanded) {
        $('.variant-sub-row-px[data-parent="' + parentId + '"]').hide();
        icon.removeClass('fa-chevron-down').addClass('fa-chevron-right');
    } else {
        $('.variant-sub-row-px[data-parent="' + parentId + '"]').show();
        icon.removeClass('fa-chevron-right').addClass('fa-chevron-down');
    }
});

$(document).on('click', '.btn-chon-sp-xuat', function () {
    const id = $(this).data('id');
    if (selectedPxProducts.has(id)) return;
    selectedPxProducts.add(id);

    const ten = $(this).data('ten');
    const ton = $(this).data('ton') || 0;
    const idx = pxIdx++;
    $('#px-empty-row').remove();

    const row = '' +
    '<tr data-sp-id="' + id + '">' +
        '<td>' +
            '<div class="fw-semibold small">' + escapeHtml(ten) + '</div>' +
            '<input type="hidden" name="chi_tiet[' + idx + '][variant_id]" value="' + id + '">' +
            '<div class="px-lo-hang-list mt-1" data-idx="' + idx + '"><small class="text-muted"><i class="fas fa-spinner fa-spin"></i> Đang tải lô...</small></div>' +
        '</td>' +
        '<td class="text-center align-middle">' +
            '<span class="badge bg-light text-dark px-ton-hien-thi" data-idx="' + idx + '">' + ton.toLocaleString() + '</span>' +
        '</td>' +
        '<td>' +
            '<input type="number" class="form-control form-control-sm px-sl-input" name="chi_tiet[' + idx + '][so_luong]" value="1" min="1" step="1" data-idx="' + idx + '" data-ton="' + ton + '">' +
        '</td>' +
        '<td>' +
            '<select class="form-select form-select-sm px-lo-select" name="chi_tiet[' + idx + '][id_chi_tiet_lo_hang]" data-idx="' + idx + '" required>' +
                '<option value="">-- Chọn lô --</option>' +
            '</select>' +
        '</td>' +
        '<td>' +
            '<input type="text" class="form-control form-control-sm px-hsd-input" data-idx="' + idx + '" readonly placeholder="HSD từ lô">' +
        '</td>' +
        '<td><button type="button" class="btn btn-sm btn-outline-danger btn-remove-px-row" data-id="' + id + '"><i class="fas fa-times"></i></button></td>' +
    '</tr>';
    $('#px-ds-sp').append(row);

    $(this).prop('disabled', true).removeClass('btn-primary btn-danger').addClass('btn-secondary').html('<i class="fas fa-check"></i> Đã chọn');

    loadLoHangForVariant(id, idx);
    updateTongSL();
});

// Lấy danh sách lô hàng có tồn cho variant
function loadLoHangForVariant(variantId, rowIdx) {
    $.get('/admin/api/phieu-xuat/lo-hang', { variant_id: variantId }, function (res) {
        const $select = $('#px-ds-sp').find('.px-lo-select[data-idx="' + rowIdx + '"]');
        const $list = $('#px-ds-sp').find('.px-lo-hang-list[data-idx="' + rowIdx + '"]');
        if (!res.success || !res.data || !res.data.length) {
            $select.html('<option value="">-- Không có lô tồn --</option>').prop('disabled', true);
            $list.html('<small class="text-danger">Sản phẩm này không có lô hàng tồn kho.</small>');
            $('#px-ds-sp tr[data-sp-id="' + variantId + '"] .px-sl-input').prop('disabled', true);
            return;
        }
        // Chỉ lấy các chi_tiet_lo_hang match variant_id
        const opts = [];
        res.data.forEach(function (lo) {
            (lo.chi_tiet_lo_hang || []).forEach(function (ct) {
                if (ct.variant_id !== variantId) return;
                if (ct.so_luong_ton <= 0) return;
                const maLo = (lo.ma_lo || ('L-' + lo.id));
                const hsd = ct.han_su_dung ? ct.han_su_dung : '';
                const label = maLo + ' | HSD: ' + (hsd || '—') + ' | Tồn: ' + Number(ct.so_luong_ton).toLocaleString();
                opts.push(
                    '<option value="' + ct.id + '"' +
                    ' data-hsd="' + escapeAttr(hsd) + '"' +
                    ' data-ton="' + ct.so_luong_ton + '"' +
                    ' >' + escapeHtml(label) + '</option>'
                );
            });
        });

        if (!opts.length) {
            $select.html('<option value="">-- Không có lô tồn --</option>').prop('disabled', true);
            $list.html('<small class="text-danger">Sản phẩm này không có lô hàng tồn kho.</small>');
            $('#px-ds-sp tr[data-sp-id="' + variantId + '"] .px-sl-input').prop('disabled', true);
            return;
        }

        $select.html('<option value="">-- Chọn lô --</option>' + opts.join(''));
        // Auto chọn lô đầu tiên (FEFO) nếu có
        $select.find('option').eq(1).prop('selected', true).trigger('change');
        $list.html('<small class="text-success"><i class="fas fa-check"></i> Đã tải ' + opts.length + ' lô có tồn</small>');
    });
}

// Auto-fill HSD khi chọn lô
$(document).on('change', '.px-lo-select', function () {
    const idx = $(this).data('idx');
    const opt = $(this).find('option:selected');
    const hsd = opt.data('hsd') || '';
    $('.px-hsd-input[data-idx="' + idx + '"]').val(hsd);
    // Cập nhật tồn hiển thị theo lô
    const tonLo = opt.data('ton') || 0;
    $('#px-ds-sp tr').find('.px-sl-input[data-idx="' + idx + '"]').attr('data-ton-lo', tonLo);
});

// Validate SL xuất <= tồn
$(document).on('input', '.px-sl-input', function () {
    const sl = parseInt($(this).val()) || 0;
    const ton = parseInt($(this).data('ton')) || 0;
    const tonLo = parseInt($(this).attr('data-ton-lo')) || ton;
    const limit = tonLo || ton;
    const $row = $(this).closest('tr');
    if (sl > limit) {
        $(this).addClass('is-invalid');
        if (!$row.find('.px-sl-warning').length) {
            $(this).after('<small class="text-danger px-sl-warning d-block mt-1">Vượt tồn kho (tối đa: ' + limit.toLocaleString() + ')</small>');
        } else {
            $row.find('.px-sl-warning').text('Vượt tồn kho (tối đa: ' + limit.toLocaleString() + ')');
        }
    } else {
        $(this).removeClass('is-invalid');
        $row.find('.px-sl-warning').remove();
    }
    updateTongSL();
});

// Xóa 1 dòng sản phẩm
$(document).on('click', '.btn-remove-px-row', function () {
    const tr = $(this).closest('tr');
    const spId = parseInt(tr.data('sp-id'));
    selectedPxProducts.delete(spId);
    tr.remove();
    if (!$('#px-ds-sp tr').length) {
        $('#px-ds-sp').html(
            '<tr id="px-empty-row">' +
                '<td colspan="6" class="text-center text-muted py-3">' +
                    'Chưa chọn sản phẩm nào. Vui lòng tìm kiếm và bấm "Chọn" phía trên.' +
                '</td>' +
            '</tr>'
        );
    }
    const btn = $('.btn-chon-sp-xuat[data-id="' + spId + '"]');
    if (btn.length) {
        const isVariant = btn.data('variant') == 1;
        btn.prop('disabled', false).removeClass('btn-secondary').addClass(isVariant ? 'btn-danger' : 'btn-primary').html('<i class="fas fa-plus"></i> Chọn');
    }
    updateTongSL();
});

function updateTongSL() {
    const rows = $('#px-ds-sp tr:not(#px-empty-row)').length;
    $('#px-tong-sl').text(rows);
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
        if (Object.keys(row).length) rows.push(row);
    });
    return rows;
}

// ─── SUBMIT PHIẾU XUẤT ───────────────────────────────────
function submitPhieuXuatPage() {
    const chiTiet = buildChiTietPage('px-ds-sp');
    if (!chiTiet.length) {
        $('#px-alert-container-page').html('<div class="alert alert-warning"><i class="fas fa-exclamation-triangle me-1"></i>Vui lòng thêm ít nhất một sản phẩm trước khi lưu.</div>');
        hienBaoPage('warning', 'Vui lòng thêm ít nhất một sản phẩm trước khi lưu.');
        return;
    }

    // Validate: bắt buộc chọn lô, SL > 0, SL <= tồn
    let invalid = 0;
    let invalidMsg = '';
    chiTiet.forEach(function (r) {
        if (!r.id_chi_tiet_lo_hang) {
            invalid++;
            invalidMsg = 'Vui lòng chọn lô hàng cho tất cả sản phẩm.';
        }
        if (!r.so_luong || parseInt(r.so_luong) <= 0) {
            invalid++;
            invalidMsg = 'Số lượng xuất phải lớn hơn 0.';
        }
    });
    if (invalid > 0) {
        $('#px-alert-container-page').html('<div class="alert alert-danger">' + invalidMsg + '</div>');
        hienBaoPage('danger', invalidMsg);
        return;
    }

    const loaiXuat = $('#px-loai').val();
    const idNcc = $('#px-ncc').val() || null;
    if (loaiXuat === 'tra_hang_nha_cung_cap' && !idNcc) {
        const msg = 'Vui lòng chọn nhà cung cấp cho loại xuất "Trả hàng NCC".';
        $('#px-alert-container-page').html('<div class="alert alert-danger">' + msg + '</div>');
        hienBaoPage('danger', msg);
        return;
    }

    const lyDo = ($('#px-ly-do').val() || '').trim();
    if (!lyDo) {
        const msg = 'Vui lòng nhập lý do xuất.';
        $('#px-alert-container-page').html('<div class="alert alert-danger">' + msg + '</div>');
        hienBaoPage('danger', msg);
        return;
    }

    $('#px-alert-container-page').html('');

    const btn = $('#px-btn-save');
    btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i>Đang lưu...');

    const data = {
        loai_xuat: loaiXuat,
        id_nha_cung_cap: idNcc,
        ly_do: lyDo,
        ghi_chu: $('#px-ghi-chu').val(),
        chi_tiet: chiTiet.map(function (r) {
            return {
                variant_id: parseInt(r.variant_id),
                id_chi_tiet_lo_hang: parseInt(r.id_chi_tiet_lo_hang),
                so_luong: parseInt(r.so_luong),
            };
        }),
    };

    $.ajax({
        url: '/admin/api/phieu-xuat',
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
        contentType: 'application/json',
        data: JSON.stringify(data),
        success: function (res) {
            btn.prop('disabled', false).html('<i class="fas fa-save me-1"></i>Lưu phiếu xuất');
            hienBaoPage('success', res.message || 'Tạo phiếu xuất thành công.');
            setTimeout(function () {
                window.location.href = '/admin/kho-hang/phieu-xuat';
            }, 800);
        },
        error: function (x) {
            btn.prop('disabled', false).html('<i class="fas fa-save me-1"></i>Lưu phiếu xuất');
            let msg = 'Có lỗi xảy ra khi lưu phiếu xuất.';
            if (x.responseJSON) {
                if (x.responseJSON.message) {
                    msg = x.responseJSON.message;
                }
                if (x.responseJSON.errors) {
                    const errs = Object.values(x.responseJSON.errors).flat();
                    if (errs.length) msg += '<br>' + errs.join('<br>');
                }
            }
            $('#px-alert-container-page').html('<div class="alert alert-danger">' + msg + '</div>');
            hienBaoPage('danger', msg);
        }
    });
}
