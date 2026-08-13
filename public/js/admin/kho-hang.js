// ============================================================
// KHO HANG: All warehouse management JS
// ============================================================

let sanPhamAll = [];
let pnIdx = 0, pxIdx = 0, loIdx = 0;
let tkPage = 1, lhPage = 1, pnPage = 1, pxPage = 1, nccPage = 1;
let selectedPnProducts = new Set();
let selectedPxProducts = new Set();

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

// ─── INIT ───────────────────────────────────────────────
$(function () {
    console.log('[KHO-HANG] Init start');
    try { loadStats(); } catch(e) { console.error('[KHO-HANG] loadStats error:', e); hienBao('danger', 'Lỗi loadStats: ' + e.message); }
    try { loadTonKho(); } catch(e) { console.error('[KHO-HANG] loadTonKho error:', e); hienBao('danger', 'Lỗi loadTonKho: ' + e.message); }
    try { loadLoHang(); } catch(e) { console.error('[KHO-HANG] loadLoHang error:', e); hienBao('danger', 'Lỗi loadLoHang: ' + e.message); }
    try { loadPhieuNhap(); } catch(e) { console.error('[KHO-HANG] loadPhieuNhap error:', e); hienBao('danger', 'Lỗi loadPhieuNhap: ' + e.message); }
    try { loadPhieuXuat(); } catch(e) { console.error('[KHO-HANG] loadPhieuXuat error:', e); hienBao('danger', 'Lỗi loadPhieuXuat: ' + e.message); }
    try { loadNhaCungCap(1); } catch(e) { console.error('[KHO-HANG] loadNhaCungCap error:', e); hienBao('danger', 'Lỗi loadNhaCungCap: ' + e.message); }
    try { loadNccDropdown(); } catch(e) { console.error('[KHO-HANG] loadNccDropdown error:', e); hienBao('danger', 'Lỗi loadNccDropdown: ' + e.message); }
    try { loadSanPhamAll(); } catch(e) { console.error('[KHO-HANG] loadSanPhamAll error:', e); hienBao('danger', 'Lỗi loadSanPhamAll: ' + e.message); }
    console.log('[KHO-HANG] Init end');

    // Modal events
    $('#modal-tao-pn').on('shown.bs.modal', function () {
        loadDanhMucNhap();
        $('#pn-sp-search').focus();
    });
    $('#modal-tao-pn').on('hidden.bs.modal', function () {
        selectedPnProducts.clear();
        pnIdx = 0;
        $('#form-tao-pn')[0].reset();
        $('#pn-ds-sp').html('<tr id="pn-empty-row"><td colspan="5" class="text-center text-muted py-3">Chưa chọn sản phẩm nào.</td></tr>');
        $('#pn-sp-results').html('<div class="text-center text-muted py-4"><i class="fas fa-search fs-4 mb-2 d-block"></i>Nhập tên hoặc mã vạch để tìm sản phẩm</div>');
        $('#pn-sp-search').val('');
        $('#pn-sp-danh-muc').val('');
    });

    $('#modal-tao-px').on('shown.bs.modal', function () {
        loadDanhMucXuat();
        $('#px-sp-search').focus();
    });
    $('#modal-tao-px').on('hidden.bs.modal', function () {
        pxIdx = 0;
        selectedPxProducts.clear();
        $('#form-tao-px')[0].reset();
        $('#px-ds-sp').html('<tr id="px-empty-row"><td colspan="5" class="text-center text-muted py-3">Chưa chọn sản phẩm nào.</td></tr>');
        $('#px-sp-results').html('<div class="text-center text-muted py-4"><i class="fas fa-search fs-4 mb-2 d-block"></i>Nhập tên hoặc mã vạch để tìm sản phẩm</div>');
        $('#px-sp-search').val('');
        $('#px-sp-danh-muc').val('');
    });

    $('#modal-tao-lo').on('hidden.bs.modal', function () {
        loIdx = 0;
        $('#form-tao-lo')[0].reset();
        $('#lo-ds-sp').html('');
    });

    // Modal triggers
    $('#pn-btn-tao').click(() => new bootstrap.Modal(document.getElementById('modal-tao-pn')).show());
    $('#px-btn-tao').click(() => {
        pxIdx = 0;
        selectedPxProducts.clear();
        $('#px-ds-sp').html('<tr id="px-empty-row"><td colspan="5" class="text-center text-muted py-3">Chưa chọn sản phẩm nào.</td></tr>');
        $('#px-sp-results').html('<div class="text-center text-muted py-4"><i class="fas fa-search fs-4 mb-2 d-block"></i>Nhập tên hoặc mã vạch để tìm sản phẩm</div>');
        $('#px-sp-search').val('');
        $('#px-sp-danh-muc').val('');
        $('#form-tao-px')[0].reset();
        new bootstrap.Modal(document.getElementById('modal-tao-px')).show();
    });
    $('#lh-btn-tao').click(() => {
        pnIdx = 0;
        selectedPnProducts.clear();
        $('#pn-ds-sp').html('<tr id="pn-empty-row"><td colspan="5" class="text-center text-muted py-3">Chưa chọn sản phẩm nào.</td></tr>');
        $('#form-tao-pn')[0].reset();
        $('#pn-sp-results').html('<div class="text-center text-muted py-4"><i class="fas fa-search fs-4 mb-2 d-block"></i>Nhập tên hoặc mã vạch để tìm sản phẩm</div>');
        $('#pn-sp-search').val('');
        $('#pn-sp-danh-muc').val('');
        new bootstrap.Modal(document.getElementById('modal-tao-pn')).show();
    });

    // Tab events
    $('#khoHangTabs button[data-bs-toggle="tab"]').on('shown.bs.tab', function () {
        const id = $(this).attr('id');
        if (id === 'tab-ton-kho') loadTonKho(tkPage);
        if (id === 'tab-lo-hang') loadLoHang(lhPage);
        if (id === 'tab-nhap-hang') loadPhieuNhap(pnPage);
        if (id === 'tab-xuat-hang') loadPhieuXuat(pxPage);
        if (id === 'tab-canh-bao') loadCanhBao();
        if (id === 'tab-ncc') loadNhaCungCap(nccPage);
    });

    // Filter buttons
    $('#tk-btn-loc').click(() => loadTonKho(1));
    $('#lh-btn-loc').click(() => loadLoHang(1));
    $('#pn-btn-loc').click(() => loadPhieuNhap(1));
    $('#px-btn-loc').click(() => loadPhieuXuat(1));

    // Enter key filters
    $('#tk-filter-ten').on('keypress', e => { if (e.which === 13) loadTonKho(1); });
    $('#lh-filter-ma').on('keypress', e => { if (e.which === 13) loadLoHang(1); });

    $('#px-btn-them-sp').click(() => addPxRow());
    $('#lo-btn-them-sp').click(() => addLoRow());

    // Submit forms
    $('#form-tao-pn').submit(e => { e.preventDefault(); submitPhieuNhap(); });
    $('#pn-btn-save').click(e => { e.preventDefault(); submitPhieuNhap(); });
    $('#form-tao-px').submit(e => { e.preventDefault(); submitPhieuXuat(); });
    $('#form-tao-lo').submit(e => { e.preventDefault(); submitLoHang(); });
});

// ─── LOADERS ─────────────────────────────────────────────
function loadStats() {
    console.log('[KHO-HANG] loadStats called');
    $.get('/admin/api/lo-hang/thong-ke', res => {
        console.log('[KHO-HANG] loadStats response:', res);
        if (!res.success) { console.warn('[KHO-HANG] loadStats: response success=false'); return; }
        $('#stat-tong-lo').text(res.data.tong_lo);
        $('#stat-lo-con-hang').text(res.data.lo_con_hang);
        $('#stat-sap-het-hsd').text(res.data.sap_het_hsd);
        $('#stat-duoi-dinh-muc').text(res.data.duoi_dinh_muc);
        const total = (res.data.sap_het_hsd || 0) + (res.data.duoi_dinh_muc || 0);
        if (total > 0) {
            $('#badge-canh-bao').text(total).show();
        }
        // Update topbar notification badge
        if (total > 0) {
            $('#topbar-notify-badge').text(total).show();
        } else {
            $('#topbar-notify-badge').hide();
        }
    }).fail(function(xhr, status, error) {
        console.error('[KHO-HANG] loadStats FAIL:', status, error, xhr.responseText);
        hienBao('danger', 'Lỗi loadStats (' + status + '): ' + error);
    });
}

function loadDanhMucNhap() {
    $.get('/admin/api/san-pham', { q: '', danh_muc: '' }, res => {
        if (!res.danh_muc_list) return;
        $('#pn-sp-danh-muc').html('<option value="">Tất cả danh mục</option>' +
            res.danh_muc_list.map(dm => `<option value="${dm.id}">${dm.ten_danh_muc}</option>`).join(''));
    });
}

function loadNhaCungCap(page = 1, q = '') {
    nccPage = page;
    const $tbody = $('#ncc-bang');
    $tbody.html('<tr><td colspan="6" class="text-center text-muted py-4"><i class="fas fa-spinner fa-spin me-1"></i> Đang tải...</td></tr>');
    $.get('/admin/api/nha-cung-cap', { page, q }, res => {
        if (!res.success) {
            $tbody.html('<tr><td colspan="6" class="text-center text-danger py-4">Lỗi tải dữ liệu.</td></tr>');
            return;
        }
        const items = res.data?.data || [];
        if (!items.length) {
            $tbody.html('<tr><td colspan="6" class="text-center text-muted py-4">Chưa có nhà cung cấp nào.</td></tr>');
        } else {
            const rows = items.map(ncc => `
                <tr>
                    <td>${ncc.id}</td>
                    <td>${ncc.ten_nha_cung_cap}</td>
                    <td>${ncc.email || '<span class="text-muted">--</span>'}</td>
                    <td>${ncc.so_dien_thoai || ''}</td>
                    <td>${ncc.nguoi_dai_dien || '<span class="text-muted">--</span>'}</td>
                    <td class="text-center">
                        <button class="btn btn-sm btn-outline-secondary btn-sua-ncc" data-id="${ncc.id}"><i class="fas fa-edit"></i></button>
                        <button class="btn btn-sm btn-outline-danger btn-xoa-ncc" data-id="${ncc.id}" data-ten="${escapeAttr(ncc.ten_nha_cung_cap)}"><i class="fas fa-trash"></i></button>
                    </td>
                </tr>`).join('');
            $tbody.html(rows);
        }
        const total = res.data?.total ?? items.length;
        $('#ncc-thong-tin').text(`Hiển thị ${items.length} / ${total} nhà cung cấp`);
        renderNccPagination(page, res.data?.last_page || 1);
    }).fail(function(xhr, status, error) {
        console.error('[KHO-HANG] loadNhaCungCap FAIL:', status, error, xhr.responseText);
        $tbody.html('<tr><td colspan="6" class="text-center text-danger py-4">Lỗi: ' + (error || status) + '</td></tr>');
    });
}

function renderNccPagination(page, lastPage) {
    if (lastPage <= 1) { $('#ncc-phan-trang').html(''); return; }
    let html = '<ul class="pagination mb-0">';
    if (page > 1) html += `<li class="page-item"><a class="page-link" href="javascript:;" onclick="loadNhaCungCap(${page - 1})">‹</a></li>`;
    const max = 5;
    let start = Math.max(1, page - 2);
    let end = Math.min(lastPage, start + max - 1);
    if (end - start < max - 1) start = Math.max(1, end - max + 1);
    for (let i = start; i <= end; i++) {
        html += `<li class="page-item ${i === page ? 'active' : ''}"><a class="page-link" href="javascript:;" onclick="loadNhaCungCap(${i})">${i}</a></li>`;
    }
    if (page < lastPage) html += `<li class="page-item"><a class="page-link" href="javascript:;" onclick="loadNhaCungCap(${page + 1})">›</a></li>`;
    html += '</ul>';
    $('#ncc-phan-trang').html(html);
}

function loadNccDropdown() {
    $.get('/admin/api/nha-cung-cap/dropdown', res => {
        if (!res.success) return;
        const opts = res.data.map(n => `<option value="${n.id}">${n.ten_nha_cung_cap}</option>`).join('');
        $('#pn-ncc').html('<option value="">-- Chon NCC --</option>' + opts);
        $('#px-ncc').html('<option value="">-- Chon NCC --</option>' + opts);
        $('#lo-ncc').html('<option value="">-- Chon NCC --</option>' + opts);
        $('#lh-filter-ncc').html('<option value="">Tất cả NCC</option>' + opts);
    });
}

// ─── PRODUCT SEARCH (PN Modal) ─────────────────────────────────────

let pnSearchTimer = 0;
$('#pn-sp-search').on('input', function () {
    clearTimeout(pnSearchTimer);
    pnSearchTimer = setTimeout(() => {
        searchProductsNhap($('#pn-sp-search').val(), $('#pn-sp-danh-muc').val());
    }, 300);
});

$('#pn-sp-danh-muc').on('change', () => {
    searchProductsNhap($('#pn-sp-search').val(), $('#pn-sp-danh-muc').val());
});

$('#pn-sp-clear').click(() => {
    $('#pn-sp-search').val('');
    $('#pn-sp-results').html('<div class="text-center text-muted py-4"><i class="fas fa-search fs-4 mb-2 d-block"></i>Nhập tên hoặc mã vạch để tìm sản phẩm</div>');
});

function searchProductsNhap(q, danhMuc) {
    if (!q && !danhMuc) {
        $('#pn-sp-results').html('<div class="text-center text-muted py-4"><i class="fas fa-search fs-4 mb-2 d-block"></i>Nhập tên hoặc mã vạch để tìm sản phẩm</div>');
        return;
    }
    $('#pn-sp-results').html('<div class="text-center py-4"><i class="fas fa-spinner fa-spin text-muted fs-4"></i></div>');
    $.get('/admin/api/san-pham', { q: q || '', danh_muc: danhMuc || '' }, res => {
        if (!res.success || !res.data.length) {
            $('#pn-sp-results').html('<div class="text-center text-muted py-4"><i class="fas fa-box-open fs-4 mb-2 d-block text-secondary"></i>Không tìm thấy sản phẩm nào.</div>');
            return;
        }
        // res.data: mang nested theo product, moi product co bien_the[]
        const rows = res.data.map(sp => {
            const variants = sp.bien_the || [];
            const hasVariants = variants.length > 1;
            // Tổng tồn = tổng tồn của tất cả biến thể
            const totalTon = variants.reduce((sum, bt) => sum + (bt.chi_tiet_lo_hang_ton || 0), 0);
            const tonClass = totalTon === 0 ? 'text-danger' : totalTon < 10 ? 'text-warning' : 'text-success';
            const img = sp.hinh_anh
                ? `<img src="/${sp.hinh_anh}" width="38" height="38" class="rounded" style="object-fit:cover" onerror="this.style.display='none';this.nextElementSibling.style.display='flex'">
                   <div class="bg-light rounded d-flex align-items-center justify-content-center" style="width:38px;height:38px;display:none"><i class="fas fa-box text-secondary"></i></div>`
                : `<div class="bg-light rounded d-flex align-items-center justify-content-center" style="width:38px;height:38px"><i class="fas fa-box text-secondary"></i></div>`;
            const danhMucName = sp.danh_muc?.ten_danh_muc || '';
            // ID cua parent row = product_id (de expand/collapse), nhung btn-chon gui variant_id = first variant
            const parentVariantId = variants[0]?.id;
            const isParentSelected = selectedPnProducts.has(parentVariantId);
            const expandBtn = hasVariants ? `<button class="btn btn-sm btn-light border btn-toggle-variants" data-parent="${sp.id}" title="Mở rộng biến thể"><i class="fas fa-chevron-down"></i></button>` : '';
            const parentBtnClass = isParentSelected ? 'btn-secondary' : 'btn-primary';
            const parentBtnIcon = isParentSelected ? 'fa-check' : 'fa-plus';
            const parentBtnText = isParentSelected ? 'Đã chọn' : 'Chọn';
            const parentDisabled = isParentSelected ? 'disabled' : '';
            const variantRows = variants.map(bt => {
                const btTon = bt.chi_tiet_lo_hang_ton || 0;
                const btTonClass = btTon === 0 ? 'text-danger' : btTon < 10 ? 'text-warning' : 'text-success';
                const isBtSelected = selectedPnProducts.has(bt.id);
                const btBtnClass = isBtSelected ? 'btn-secondary' : 'btn-success';
                const btBtnIcon = isBtSelected ? 'fa-check' : 'fa-plus';
                const btBtnText = isBtSelected ? 'Đã chọn' : 'Chọn';
                const btDisabled = isBtSelected ? 'disabled' : '';
                const attrs = (bt.thuoc_tinh_labels || []).map(tt =>
                    `<span class="badge variant-chip" style="background:#e9ecef;color:#495057;border:1px solid #dee2e6">${tt}</span>`
                ).join(' ');
                // Gắn units vào data-units (JSON) để click "Chọn" có thể build dropdown đơn vị
                const unitsJson = JSON.stringify(bt.units || []).replace(/'/g, '&#39;');
                return `<tr class="variant-sub-row" data-parent="${sp.id}" style="display:none">
                    <td class="text-center align-middle"><div class="vr" style="width:2px;height:30px;opacity:0.4"></div></td>
                    <td class="align-middle ps-1">
                        <div class="small"><span class="text-muted">\\</span> ${attrs || '<span class="text-muted small">Không có thuộc tính</span>'}</div>
                    </td>
                    <td class="align-middle"><code class="small">${bt.ma_vach || '--'}</code></td>
                    <td class="text-end align-middle small">${Number(bt.gia_ban || 0).toLocaleString()} d</td>
                    <td class="text-center align-middle"><span class="fw-semibold ${btTonClass}">${btTon.toLocaleString()}</span></td>
                    <td class="text-center align-middle">
                        <button class="btn btn-sm ${btBtnClass} btn-chon-sp-nhap"
                            data-id="${bt.id}"
                            data-ten="${sp.ten_san_pham}${attrs ? ' - ' + bt.ten_bien_the : ''}"
                            data-gia="${bt.gia_ban || 0}"
                            data-variant="1"
                            data-base-name="${escapeAttr(bt.ten_bien_the || bt.ten_don_vi || 'Cơ bản')}"
                            data-base-unit="${escapeAttr(bt.ten_don_vi || '')}"
                            data-units='${unitsJson}'
                            ${btDisabled}>
                            <i class="fas ${btBtnIcon}"></i> ${btBtnText}
                        </button>
                    </td>
                </tr>`;
            });
            return [
                `<tr class="parent-row" data-id="${sp.id}">
                    <td class="text-center align-middle">${img}</td>
                    <td class="align-middle">
                        <div class="fw-semibold small">${sp.ten_san_pham}</div>
                        <div class="small text-muted">${danhMucName}</div>
                        ${hasVariants ? `<div class="mt-1"><span class="badge bg-secondary" style="font-size:0.68rem">${variants.length} bien the</span></div>` : ''}
                    </td>
                    <td class="align-middle"><code class="small">${variants[0]?.ma_vach || '--'}</code></td>
                    <td class="text-end align-middle small">${Number(variants[0]?.gia_ban || 0).toLocaleString()} d</td>
                    <td class="text-center align-middle"><span class="fw-semibold ${tonClass}">${totalTon.toLocaleString()}</span></td>
                    <td class="text-center align-middle">
                        ${expandBtn}
                        ${!hasVariants ? `
                        <button class="btn btn-sm ${parentBtnClass} btn-chon-sp-nhap"
                            data-id="${parentVariantId}"
                            data-ten="${sp.ten_san_pham}"
                            data-gia="${variants[0]?.gia_ban || 0}"
                            data-base-name="${escapeAttr(variants[0]?.ten_bien_the || variants[0]?.ten_don_vi || 'Cơ bản')}"
                            data-base-unit="${escapeAttr(variants[0]?.ten_don_vi || '')}"
                            data-units='${JSON.stringify(variants[0]?.units || []).replace(/'/g, '&#39;')}'
                            ${parentDisabled}>
                            <i class="fas ${parentBtnIcon}"></i> ${parentBtnText}
                        </button>` : ''}
                    </td>
                </tr>`,
                ...variantRows
            ];
        });
        const tableHtml = `<table class="table table-sm table-hover mb-0">
            <thead class="table-light"><tr>
                <th style="width:50px"></th>
                <th>Sản phẩm</th>
                <th style="width:110px">Mã vạch</th>
                <th style="width:110px" class="text-end">Giá bán</th>
                <th style="width:80px" class="text-center">Tồn kho</th>
                <th style="width:120px" class="text-center">Chon</th>
            </tr></thead>
            <tbody>${rows.join('')}</tbody>
        </table>`;
        $('#pn-sp-results').html(tableHtml);
    });
}

$(document).on('click', '.btn-toggle-variants', function () {
    const parentId = $(this).data('parent');
    const icon = $(this).find('i');
    const isExpanded = icon.hasClass('fa-chevron-down');
    if (isExpanded) {
        $(`.variant-sub-row[data-parent="${parentId}"]`).hide();
        icon.removeClass('fa-chevron-down').addClass('fa-chevron-right');
    } else {
        $(`.variant-sub-row[data-parent="${parentId}"]`).show();
        icon.removeClass('fa-chevron-right').addClass('fa-chevron-down');
    }
});

$(document).on('click', '.btn-chon-sp-nhap', function () {
    const id = $(this).data('id');
    if (selectedPnProducts.has(id)) return;
    selectedPnProducts.add(id);
    const ten = $(this).data('ten');
    const gia = $(this).data('gia');
    // Lấy thêm danh sách đơn vị quy đổi (nếu có) từ nút bấm
    const units = $(this).data('units') || [];
    const variantBaseName = $(this).data('base-name') || 'Cơ bản';
    const baseUnitLabel = $(this).data('base-unit') || variantBaseName || 'đơn vị cơ bản';
    const idx = pnIdx++;
    $('#pn-empty-row').remove();

    // Build option đơn vị: đơn vị cơ bản (he_so=1) + đơn vị quy đổi
    const baseOpt = `<option value="__base__" data-he-so="1" data-ten="${escapeAttr(baseUnitLabel)}" data-don-vi-id="" selected>${escapeHtml(variantBaseName)} (đơn vị cơ bản)</option>`;
    const unitOpts = units.map(u => `<option value="${u.id}" data-he-so="${u.so_luong_san_pham_trong_don_vi || 1}" data-ten="${escapeAttr(u.ten_don_vi)}" data-don-vi-id="${u.id}">${escapeHtml(u.ten_don_vi)} (×${u.so_luong_san_pham_trong_don_vi || 1})</option>`).join('');
    const donViSelect = `<select class="form-select form-select-sm pn-don-vi-select" name="chi_tiet[${idx}][don_vi_id]" data-idx="${idx}">${baseOpt}${unitOpts}</select>`;

    // Ghi chú nhỏ hiển thị quy đổi khi user chọn đơn vị
    const quyDoiHint = units.length
        ? `<small class="text-muted pn-quy-doi-hint d-block mt-1">Mặc định nhập theo đơn vị cơ bản. Đổi sang "Thùng", "Hộp"... nếu mua theo đơn vị quy đổi.</small>`
        : '';

    $('#pn-ds-sp').append(`<tr data-sp-id="${id}">
        <td>
            <div class="fw-semibold small">${ten}</div>
            <input type="hidden" name="chi_tiet[${idx}][variant_id]" value="${id}">
            <input type="hidden" name="chi_tiet[${idx}][so_luong_san_pham_trong_don_vi]" value="1" class="pn-he-so-hidden">
            ${units.length ? `<div class="mt-1 d-flex align-items-center gap-1">${donViSelect}${quyDoiHint}</div>` : ''}
        </td>
        <td>
            <input type="number" class="form-control form-control-sm pn-sl-input" name="chi_tiet[${idx}][so_luong_nhap]" value="1" min="0.0001" step="0.0001" data-idx="${idx}">
            ${units.length ? `<small class="text-muted pn-sl-display d-block mt-1" data-idx="${idx}">= 1 ${escapeHtml(baseUnitLabel)}</small>` : ''}
        </td>
        <td>
            <input type="number" class="form-control form-control-sm pn-gia-input" name="chi_tiet[${idx}][gia_nhap]" value="${gia}" min="0" step="100">
            ${units.length ? `<small class="text-muted pn-gia-display d-block mt-1" data-idx="${idx}">đơn giá / ${escapeHtml(baseUnitLabel)}</small>` : `<small class="text-muted d-block mt-1">đơn giá / ${escapeHtml(baseUnitLabel)}</small>`}
        </td>
        <td><input type="date" class="form-control form-control-sm" name="chi_tiet[${idx}][han_su_dung]" value=""></td>
        <td><button type="button" class="btn btn-sm btn-outline-danger btn-remove-pn-row" data-id="${id}"><i class="fas fa-times"></i></button></td>
    </tr>`);
    $(this).prop('disabled', true).removeClass('btn-primary btn-success').addClass('btn-secondary').html('<i class="fas fa-check"></i> Đã chọn');
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
    const gia = parseFloat($giaInput.val()) || 0;
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
            $display.html(`= <strong>${sl.toLocaleString()}</strong> ${escapeHtml(tenDonViCoBan || 'đơn vị cơ bản')}`);
        } else {
            const tong = sl * heSo;
            const donGiaCoBan = gia / heSo;
            $display.html(`= <strong class="text-primary">${tong.toLocaleString()}</strong> ${escapeHtml(tenDonViCoBan || 'đơn vị cơ bản')} <span class="text-muted">(1 ${escapeHtml(tenDonViNhap)} = ${heSo} ${escapeHtml(tenDonViCoBan || 'đơn vị cơ bản')})</span>`);
            if ($giaDisplay.length) {
                $giaDisplay.html(`đơn giá / ${escapeHtml(tenDonViNhap)} → <strong class="text-info">${donGiaCoBan.toLocaleString(undefined, {maximumFractionDigits: 2})}</strong> / ${escapeHtml(tenDonViCoBan || 'đơn vị cơ bản')}`);
            }
        }
    }
});

$(document).on('click', '.btn-remove-pn-row', function () {
    const tr = $(this).closest('tr');
    const spId = parseInt(tr.data('sp-id'));
    selectedPnProducts.delete(spId);
    tr.remove();
    if (!$('#pn-ds-sp tr').length) {
        $('#pn-ds-sp').html('<tr id="pn-empty-row"><td colspan="5" class="text-center text-muted py-3">Chưa chọn sản phẩm nào.</td></tr>');
    }
    // Chỉ re-enable nút nếu nó tồn tại (sản phẩm không có biến thể)
    const btn = $(`.btn-chon-sp-nhap[data-id="${spId}"]`);
    if (btn.length) {
        const isVariant = btn.data('variant') == 1;
        btn.prop('disabled', false).removeClass('btn-secondary').addClass(isVariant ? 'btn-success' : 'btn-primary').html(`<i class="fas fa-plus"></i> Chọn`);
    }
});

// ─── PRODUCT SEARCH (PX Modal) ─────────────────────────────────────

let pxSearchTimer = 0;
$('#px-sp-search').on('input', function () {
    clearTimeout(pxSearchTimer);
    pxSearchTimer = setTimeout(() => {
        searchProductsXuat($('#px-sp-search').val(), $('#px-sp-danh-muc').val());
    }, 300);
});

$('#px-sp-danh-muc').on('change', () => {
    searchProductsXuat($('#px-sp-search').val(), $('#px-sp-danh-muc').val());
});

$('#px-sp-clear').click(() => {
    $('#px-sp-search').val('');
    $('#px-sp-results').html('<div class="text-center text-muted py-4"><i class="fas fa-search fs-4 mb-2 d-block"></i>Nhập tên hoặc mã vạch để tìm sản phẩm</div>');
});

function loadDanhMucXuat() {
    // Tận dụng API đã có (đã được gọi ở loadDanhMucNhap); chỉ cần clone options.
    const opts = $('#pn-sp-danh-muc option').clone();
    if (!opts.length) {
        $.get('/admin/api/san-pham', { q: '', danh_muc: '' }, res => {
            if (!res.danh_muc_list) return;
            $('#px-sp-danh-muc').html('<option value="">Tất cả danh mục</option>' +
                res.danh_muc_list.map(dm => `<option value="${dm.id}">${dm.ten_danh_muc}</option>`).join(''));
        });
    } else {
        $('#px-sp-danh-muc').html('<option value="">Tất cả danh mục</option>' + opts.not('[value=""]').clone().get().map(o => o.outerHTML).join(''));
    }
}

function searchProductsXuat(q, danhMuc) {
    // #region agent log
    fetch('http://127.0.0.1:7359/ingest/002bc91b-88fd-46aa-85b0-ce56b4017dd2',{method:'POST',headers:{'Content-Type':'application/json','X-Debug-Session-Id':'b085c7'},body:JSON.stringify({sessionId:'b085c7',location:'kho-hang.js:searchProductsXuat',message:'PX search fired',data:{q,danhMuc,emptyQ:!q,emptyDm:!danhMuc},timestamp:Date.now(),hypothesisId:'A'})}).catch(()=>{});
    // #endregion
    if (!q && !danhMuc) {
        $('#px-sp-results').html('<div class="text-center text-muted py-4"><i class="fas fa-search fs-4 mb-2 d-block"></i>Nhập tên hoặc mã vạch để tìm sản phẩm</div>');
        return;
    }
    $('#px-sp-results').html('<div class="text-center py-4"><i class="fas fa-spinner fa-spin text-muted fs-4"></i></div>');
    $.get('/admin/api/san-pham', { q: q || '', danh_muc: danhMuc || '' }, res => {
        // #region agent log
        fetch('http://127.0.0.1:7359/ingest/002bc91b-88fd-46aa-85b0-ce56b4017dd2',{method:'POST',headers:{'Content-Type':'application/json','X-Debug-Session-Id':'b085c7'},body:JSON.stringify({sessionId:'b085c7',location:'kho-hang.js:searchProductsXuat.onSuccess',message:'PX search response',data:{success:res.success,productsLen:res.data?.length,hasDanhMucList:!!res.danh_muc_list},timestamp:Date.now(),hypothesisId:'A'})}).catch(()=>{});
        // #endregion
        if (!res.success || !res.data.length) {
            $('#px-sp-results').html('<div class="text-center text-muted py-4"><i class="fas fa-box-open fs-4 mb-2 d-block text-secondary"></i>Không tìm thấy sản phẩm nào.</div>');
            return;
        }
        const rows = res.data.map(sp => {
            const variants = sp.bien_the || [];
            const hasVariants = variants.length > 1;
            const totalTon = variants.reduce((sum, bt) => sum + (bt.chi_tiet_lo_hang_ton || 0), 0);
            const tonClass = totalTon === 0 ? 'text-danger' : totalTon < 10 ? 'text-warning' : 'text-success';
            const img = sp.hinh_anh
                ? `<img src="/${sp.hinh_anh}" width="38" height="38" class="rounded" style="object-fit:cover" onerror="this.style.display='none';this.nextElementSibling.style.display='flex'">
                   <div class="bg-light rounded d-flex align-items-center justify-content-center" style="width:38px;height:38px;display:none"><i class="fas fa-box text-secondary"></i></div>`
                : `<div class="bg-light rounded d-flex align-items-center justify-content-center" style="width:38px;height:38px"><i class="fas fa-box text-secondary"></i></div>`;
            const danhMucName = sp.danh_muc?.ten_danh_muc || '';
            const parentVariantId = variants[0]?.id;
            const isParentSelected = parentVariantId ? selectedPxProducts.has(parentVariantId) : false;
            const expandBtn = hasVariants ? `<button class="btn btn-sm btn-light border btn-px-toggle-variants" data-parent="${sp.id}" title="Mở rộng biến thể"><i class="fas fa-chevron-down"></i></button>` : '';
            const parentBtnClass = isParentSelected ? 'btn-secondary' : 'btn-primary';
            const parentBtnIcon = isParentSelected ? 'fa-check' : 'fa-plus';
            const parentBtnText = isParentSelected ? 'Đã chọn' : 'Chọn';
            const parentDisabled = isParentSelected ? 'disabled' : '';
            const variantRows = variants.map(bt => {
                const btTon = bt.chi_tiet_lo_hang_ton || 0;
                const btTonClass = btTon === 0 ? 'text-danger' : btTon < 10 ? 'text-warning' : 'text-success';
                const isBtSelected = selectedPxProducts.has(bt.id);
                const btBtnClass = isBtSelected ? 'btn-secondary' : 'btn-success';
                const btBtnIcon = isBtSelected ? 'fa-check' : 'fa-plus';
                const btBtnText = isBtSelected ? 'Đã chọn' : 'Chọn';
                const btDisabled = isBtSelected ? 'disabled' : '';
                const attrs = (bt.thuoc_tinh_labels || []).map(tt =>
                    `<span class="badge variant-chip" style="background:#e9ecef;color:#495057;border:1px solid #dee2e6">${tt}</span>`
                ).join(' ');
                return `<tr class="variant-sub-row-px" data-parent="${sp.id}" style="display:none">
                    <td class="text-center align-middle"><div class="vr" style="width:2px;height:30px;opacity:0.4"></div></td>
                    <td class="align-middle ps-1">
                        <div class="small"><span class="text-muted">\\</span> ${attrs || '<span class="text-muted small">Không có thuộc tính</span>'}</div>
                    </td>
                    <td class="align-middle"><code class="small">${bt.ma_vach || '--'}</code></td>
                    <td class="text-end align-middle small">${Number(bt.gia_ban || 0).toLocaleString()} d</td>
                    <td class="text-center align-middle"><span class="fw-semibold ${btTonClass}">${btTon.toLocaleString()}</span></td>
                    <td class="text-center align-middle">
                        <button class="btn btn-sm ${btBtnClass} btn-chon-sp-xuat"
                            data-id="${bt.id}"
                            data-ten-sp="${escapeAttr(sp.ten_san_pham)}"
                            data-ten-bt="${escapeAttr(bt.ten_bien_the || '')}"
                            data-vach="${escapeAttr(bt.ma_vach || '')}"
                            data-attrs="${escapeAttr((bt.thuoc_tinh_labels || []).join(', '))}"
                            data-ton="${bt.chi_tiet_lo_hang_ton || 0}"
                            data-variant="1"
                            ${btDisabled}>
                            <i class="fas ${btBtnIcon}"></i> ${btBtnText}
                        </button>
                    </td>
                </tr>`;
            });
            return [
                `<tr class="parent-row-px" data-id="${sp.id}">
                    <td class="text-center align-middle">${img}</td>
                    <td class="align-middle">
                        <div class="fw-semibold small">${sp.ten_san_pham}</div>
                        <div class="small text-muted">${danhMucName}</div>
                        ${hasVariants ? `<div class="mt-1"><span class="badge bg-secondary" style="font-size:0.68rem">${variants.length} bien the</span></div>` : ''}
                    </td>
                    <td class="align-middle"><code class="small">${variants[0]?.ma_vach || '--'}</code></td>
                    <td class="text-end align-middle small">${Number(variants[0]?.gia_ban || 0).toLocaleString()} d</td>
                    <td class="text-center align-middle"><span class="fw-semibold ${tonClass}">${totalTon.toLocaleString()}</span></td>
                    <td class="text-center align-middle">
                        ${expandBtn}
                        ${!hasVariants ? `
                        <button class="btn btn-sm ${parentBtnClass} btn-chon-sp-xuat"
                            data-id="${parentVariantId}"
                            data-ten-sp="${escapeAttr(sp.ten_san_pham)}"
                            data-ten-bt="${escapeAttr(variants[0]?.ten_bien_the || '')}"
                            data-vach="${escapeAttr(variants[0]?.ma_vach || '')}"
                            data-attrs="${escapeAttr((variants[0]?.thuoc_tinh_labels || []).join(', '))}"
                            data-ton="${variants[0]?.chi_tiet_lo_hang_ton || 0}"
                            data-variant="0"
                            ${parentDisabled}>
                            <i class="fas ${parentBtnIcon}"></i> ${parentBtnText}
                        </button>` : ''}
                    </td>
                </tr>`,
                ...variantRows
            ];
        });
        const tableHtml = `<table class="table table-sm table-hover mb-0">
            <thead class="table-light"><tr>
                <th style="width:50px"></th>
                <th>Sản phẩm</th>
                <th style="width:110px">Mã vạch</th>
                <th style="width:110px" class="text-end">Giá bán</th>
                <th style="width:80px" class="text-center">Tồn kho</th>
                <th style="width:120px" class="text-center">Chon</th>
            </tr></thead>
            <tbody>${rows.join('')}</tbody>
        </table>`;
        $('#px-sp-results').html(tableHtml);
    });
}

$(document).on('click', '.btn-px-toggle-variants', function () {
    const parentId = $(this).data('parent');
    const icon = $(this).find('i');
    const isExpanded = icon.hasClass('fa-chevron-down');
    if (isExpanded) {
        $(`.variant-sub-row-px[data-parent="${parentId}"]`).hide();
        icon.removeClass('fa-chevron-down').addClass('fa-chevron-right');
    } else {
        $(`.variant-sub-row-px[data-parent="${parentId}"]`).show();
        icon.removeClass('fa-chevron-right').addClass('fa-chevron-down');
    }
});

$(document).on('click', '.btn-chon-sp-xuat', function () {
    const $btn = $(this);
    // Dùng .attr() thay vì .data() để tránh jQuery tự động parse chuỗi số ("333" -> 333).
    const id = parseInt($btn.attr('data-id'));
    const readStr = name => $btn.attr(name) || '';
    const displayInfo = {
        tenSp: readStr('data-ten-sp'),
        tenBt: readStr('data-ten-bt'),
        attrs: readStr('data-attrs'),
        vach: readStr('data-vach'),
        ton: parseInt($btn.attr('data-ton')) || 0,
    };
    // #region agent log
    fetch('http://127.0.0.1:7359/ingest/002bc91b-88fd-46aa-85b0-ce56b4017dd2',{method:'POST',headers:{'Content-Type':'application/json','X-Debug-Session-Id':'b085c7'},body:JSON.stringify({sessionId:'b085c7',location:'kho-hang.js:btn-chon-sp-xuat.click',message:'PX select click',data:{id,alreadyInSet:selectedPxProducts.has(id),displayInfo,displayInfoTypes:{tenSp:typeof displayInfo.tenSp,tenBt:typeof displayInfo.tenBt,attrs:typeof displayInfo.attrs,vach:typeof displayInfo.vach,ton:typeof displayInfo.ton},sanPhamAllIncludes:sanPhamAll.find(x=>x.id==id)?.id||null},timestamp:Date.now(),hypothesisId:'F'})}).catch(()=>{});
    // #endregion
    if (!id) return;
    if (selectedPxProducts.has(id)) return;
    selectedPxProducts.add(id);
    $btn.prop('disabled', true).removeClass('btn-primary btn-success').addClass('btn-secondary').html('<i class="fas fa-check"></i> Đã chọn');
    addPxRow(id, 1, displayInfo);
});

$(document).on('click', '.btn-remove-px-row', function () {
    removePxRow(this);
});

// Cập nhật tổng SL khi đổi số lượng xuất
$(document).on('input change', '.px-sl-input', function () {
    updatePxTongSl();
});

// Cập nhật tổng SL khi thêm row thủ công (gọi từ addPxRow)
// (Đã được tích hợp vào addPxRow)

function loadTonKho(page = 1) {
    tkPage = page;
    const q = $('#tk-filter-ten').val();
    const status = $('#tk-filter-status').val();
    $.get('/admin/api/lo-hang/ton-kho-tong')
    .done(res => {
        if (!res.success) {
            $('#tk-bang').html('<tr><td colspan="7" class="text-center text-danger py-4">Lỗi tải dữ liệu tồn kho.</td></tr>');
            return;
        }
        let items = res.data;
        if (q) items = items.filter(i => {
            const name = (i.ten_san_pham || i.product?.ten_san_pham || '') + '';
            const code = (i.ma_vach || '') + '';
            return name.toLowerCase().includes(q.toLowerCase()) || code.toLowerCase().includes(q.toLowerCase());
        });
        if (status === 'het-hang') items = items.filter(i => !(i.tong_ton || i.product?.tong_ton) || (i.tong_ton || i.product?.tong_ton) == 0);
        if (status === 'duoi-dinh-muc') items = items.filter(i => {
            const qty = i.tong_ton ?? i.product?.tong_ton ?? 0;
            const min = i.dinh_muc_toi_thieu ?? i.product?.dinh_muc_toi_thieu ?? 0;
            return qty > 0 && qty <= min;
        });
        if (status === 'binh-thuong') items = items.filter(i => {
            const qty = i.tong_ton ?? i.product?.tong_ton ?? 0;
            const min = i.dinh_muc_toi_thieu ?? i.product?.dinh_muc_toi_thieu ?? 0;
            return qty > min;
        });

        const perPage = 15, total = items.length;
        const paged = items.slice((page - 1) * perPage, page * perPage);

        if (!paged.length) {
            $('#tk-bang').html('<tr><td colspan="7" class="text-center text-muted py-4">Không có sản phẩm nào.</td></tr>');
            $('#tk-thong-tin').text('Hiển thị 0 sản phẩm');
            $('#tk-phan-trang').html('');
            return;
        }

        const html = paged.map(sp => {
            const tongTon = sp.tong_ton || 0;
            const dinhMuc = sp.dinh_muc_toi_thieu || 0;
            let badge, statusClass;
            if (tongTon === 0) { badge = '<span class="badge bg-secondary">Hết hàng</span>'; statusClass = 'table-secondary'; }
            else if (tongTon <= dinhMuc) { badge = '<span class="badge bg-warning text-dark">Dưới định mức</span>'; statusClass = 'table-warning'; }
            else { badge = '<span class="badge bg-success">Bình thường</span>'; statusClass = ''; }
            const productName = sp.ten_san_pham || sp.product?.ten_san_pham || '';
            const variantName = sp.ten_bien_the || '';
            const baseName = variantName && variantName !== productName
                ? `${productName} - ${variantName}`
                : productName;
            const attrs = (sp.thuoc_tinh_labels && sp.thuoc_tinh_labels.length)
                ? ` - ${sp.thuoc_tinh_labels.join(', ')}`
                : '';
            const displayName = `${baseName}${attrs}`;
            return `
            <tr class="clickable-row ${statusClass}" data-id="${sp.id}" data-variant-id="${sp.id}" style="cursor:pointer">
                <td class="text-center toggle-expand"><i class="fas fa-chevron-down text-muted small"></i></td>
                <td><code>${sp.ma_vach || sp.id}</code></td>
                <td><strong>${displayName}</strong></td>
                <td class="text-center"><strong>${Number(tongTon).toLocaleString()}</strong></td>
                <td class="text-center text-muted">${Number(dinhMuc).toLocaleString()}</td>
                <td class="text-center">${badge}</td>
                <td class="text-center">
                    <button class="btn btn-sm btn-outline-primary btn-xem-ton" data-id="${sp.id}" data-variant-id="${sp.id}"><i class="fas fa-eye"></i></button>
                </td>
            </tr>
            <tr class="fefo-detail-row" id="fefo-detail-${sp.id}" style="display:none">
                <td colspan="7" class="p-0">
                    <div class="p-3" id="fefo-detail-content-${sp.id}">
                        <div class="text-center text-muted"><i class="fas fa-spinner fa-spin"></i> Dang tai...</div>
                    </div>
                </td>
            </tr>`;
        }).join('');
        $('#tk-bang').html(html);
        $('#tk-thong-tin').text(`Hien thi ${paged.length} / ${total} san pham`);
        renderPagination('tk', page, Math.ceil(total / perPage));
    })
    .fail(function(xhr, status, error) {
        console.error('loadTonKho error:', status, error, xhr.responseText);
        $('#tk-bang').html(`<tr><td colspan="7" class="text-center text-danger py-4">Loi: ${error || status}<br><small class="text-muted">${xhr.responseText || ''}</small></td></tr>`);
    });
}

function loadLoHang(page = 1) {
    lhPage = page;
    const q = $('#lh-filter-ma').val();
    const ncc = $('#lh-filter-ncc').val();
    const params = new URLSearchParams({ page, q: q || '', id_nha_cung_cap: ncc || '' });
    $.get('/admin/api/lo-hang?' + params.toString(), res => {
        const items = res.data?.data || [];
        if (!items.length) {
            $('#lh-bang').html('<tr><td colspan="9" class="text-center text-muted py-4">Chưa có lô hàng nào.</td></tr>');
            $('#lh-thong-tin').text('');
            $('#lh-phan-trang').html('');
            return;
        }
        const html = items.map(item => {
            const tongNhap = item.chi_tiet_lo_hang_sum_so_luong_nhap || 0;
            const tongTon = item.chi_tiet_lo_hang_sum_so_luong_ton || 0;
            const ncc = item.nha_cung_cap?.ten_nha_cung_cap || '<span class="text-muted">--</span>';
            const ngay = item.ngay_nhap || '';
            const maLo = item.ma_lo || 'L-' + item.id;
            let hsds = (item.chi_tiet_lo_hang || []).map(c => (c.han_su_dung || '').split('T')[0]).filter(d => d).sort();
            const hsdMin = hsds[0] || null;
            let hsdBadge = '', hsdClass = '';
            if (hsdMin) {
                const ngayHsdNghiem = new Date(hsdMin);
                const ngayHomNay = new Date();
                ngayHsdNghiem.setHours(0,0,0,0); ngayHomNay.setHours(0,0,0,0);
                const diff = Math.ceil((ngayHsdNghiem - ngayHomNay) / 86400000);
                if (diff < 0) { hsdBadge = `<span class="badge bg-danger badge-hsd">Hết HSD</span>`; hsdClass = 'table-danger'; }
                else if (diff <= 30) { hsdBadge = `<span class="badge bg-warning text-dark badge-hsd">${hsdMin} (${diff} ngay)</span>`; hsdClass = 'table-warning'; }
                else { hsdBadge = `<span class="badge bg-success badge-hsd">${hsdMin}</span>`; }
            }
            let loBadge = tongTon > 0 ? '<span class="badge bg-success">Còn hàng</span>' : '<span class="badge bg-secondary">Hết</span>';
            return `
            <tr class="${hsdClass}">
                <td>${item.id}</td>
                <td><span class="badge bg-dark lo-badge">${maLo}</span></td>
                <td>${ncc}</td>
                <td>${ngay}</td>
                <td class="text-center">${tongNhap.toLocaleString()}</td>
                <td class="text-center"><strong>${tongTon.toLocaleString()}</strong></td>
                <td class="text-center">${hsdBadge}</td>
                <td class="text-center">${loBadge}</td>
                <td class="text-center">
                    <button class="btn btn-sm btn-outline-primary btn-xem-lo" data-id="${item.id}"><i class="fas fa-eye"></i></button>
                    <button class="btn btn-sm btn-outline-danger btn-xoa-lo" data-id="${item.id}"><i class="fas fa-trash"></i></button>
                </td>
            </tr>`;
        }).join('');
        $('#lh-bang').html(html);
        const total = res.data.total || items.length;
        $('#lh-thong-tin').text(`Hien thi ${items.length} / ${total} lo hang`);
        renderPagination('lh', page, res.data.last_page || 1);
    })
    .fail(function(xhr, status, error) {
        console.error('loadLoHang error:', status, error, xhr.responseText);
        $('#lh-bang').html(`<tr><td colspan="9" class="text-center text-danger py-4">Loi: ${error || status}<br><small class="text-muted">${xhr.responseText || ''}</small></td></tr>`);
    });
}

function loadPhieuNhap(page = 1) {
    pnPage = page;
    const loai = $('#pn-filter-loai').val();
    const tu = $('#pn-filter-tu').val();
    const den = $('#pn-filter-den').val();
    const params = new URLSearchParams({ page, loai_nhap: loai || '', tu_ngay: tu || '', den_ngay: den || '' });
    $.get('/admin/api/phieu-nhap?' + params.toString(), res => {
        const items = res.data?.data || [];
        if (!items.length) {
            $('#pn-bang').html('<tr><td colspan="9" class="text-center text-muted py-4">Chưa có phiếu nhập nào.</td></tr>');
            $('#pn-thong-tin').text('');
            $('#pn-phan-trang').html('');
            return;
        }
        const html = items.map(item => {
            const loaiLabel = item.loai_nhap === 'mua_hang'
                ? '<span class="badge bg-success">Nhập hàng NCC</span>'
                : '<span class="badge bg-info">Nhập hàng khách hoàn trả</span>';
            const ncc = item.phieu?.nha_cung_cap?.ten_nha_cung_cap || '--';
            const nguoi = item.phieu?.nguoi_dung?.ho_ten || item.phieu?.id_nguoi_dung || '--';
            const ngay = item.created_at?.slice(0, 10) || '';
            const tongGiaTri = (item.chi_tiet_phieu || []).reduce((s, ct) =>
                s + ((ct.so_luong || 0) * (ct.gia_nhap || 0)), 0);
            const ghiChu = item.ghi_chu || '<span class="text-muted">--</span>';
            return `
            <tr>
                <td>${item.id}</td>
                <td><span class="badge bg-primary">PN-${item.id_phieu}</span></td>
                <td>${loaiLabel}</td>
                <td>${ncc}</td>
                <td>${nguoi}</td>
                <td class="text-center">${ngay}</td>
                <td class="text-end fw-bold text-danger">${tongGiaTri.toLocaleString()} d</td>
                <td>${ghiChu}</td>
                <td class="text-center">
                    <button class="btn btn-sm btn-outline-primary btn-xem-pn" data-id="${item.id}"><i class="fas fa-eye"></i></button>
                    <button class="btn btn-sm btn-outline-danger btn-xoa-pn" data-id="${item.id}"><i class="fas fa-trash"></i></button>
                </td>
            </tr>`;
        }).join('');
        $('#pn-bang').html(html);
        const total = res.data.total || items.length;
        $('#pn-thong-tin').text(`Hien thi ${items.length} / ${total} phieu nhap`);
        renderPagination('pn', page, res.data.last_page || 1);
    })
    .fail(function(xhr, status, error) {
        console.error('loadPhieuNhap error:', status, error, xhr.responseText);
        $('#pn-bang').html(`<tr><td colspan="9" class="text-center text-danger py-4">Loi: ${error || status}<br><small class="text-muted">${xhr.responseText || ''}</small></td></tr>`);
    });
}

function loadPhieuXuat(page = 1) {
    pxPage = page;
    const loai = $('#px-filter-loai').val();
    const tu = $('#px-filter-tu').val();
    const den = $('#px-filter-den').val();
    const params = new URLSearchParams({ page, loai_xuat: loai || '', tu_ngay: tu || '', den_ngay: den || '' });
    $.get('/admin/api/phieu-xuat?' + params.toString(), res => {
        const items = res.data?.data || [];
        if (!items.length) {
            $('#px-bang').html('<tr><td colspan="9" class="text-center text-muted py-4">Chưa có phiếu xuất nào.</td></tr>');
            $('#px-thong-tin').text('');
            $('#px-phan-trang').html('');
            return;
        }
        const html = items.map(item => {
            const loaiLabel = item.loai_xuat === 'tra_hang_nha_cung_cap'
                ? '<span class="badge bg-warning text-dark">Trả hàng NCC</span>'
                : '<span class="badge bg-danger">Tiêu hủy</span>';
            const ncc = item.phieu?.nha_cung_cap?.ten_nha_cung_cap || '--';
            const nguoi = item.phieu?.nguoi_dung?.ho_ten || item.phieu?.id_nguoi_dung || '--';
            const ngay = item.created_at?.slice(0, 10) || '';
            const lyDo = item.ly_do || '<span class="text-muted">--</span>';
            const tongSl = (item.chi_tiet_phieu || []).reduce((s, ct) => s + (ct.so_luong || 0), 0);
            return `
            <tr>
                <td>${item.id}</td>
                <td><span class="badge bg-danger">PX-${item.id_phieu}</span></td>
                <td>${loaiLabel}</td>
                <td>${ncc}</td>
                <td>${lyDo}</td>
                <td>${nguoi}</td>
                <td class="text-center">${ngay}</td>
                <td class="text-center fw-bold text-danger">${tongSl.toLocaleString()}</td>
                <td class="text-center">
                    <button class="btn btn-sm btn-outline-primary btn-xem-px" data-id="${item.id}"><i class="fas fa-eye"></i></button>
                    <button class="btn btn-sm btn-outline-danger btn-xoa-px" data-id="${item.id}"><i class="fas fa-trash"></i></button>
                </td>
            </tr>`;
        }).join('');
        $('#px-bang').html(html);
        const total = res.data.total || items.length;
        $('#px-thong-tin').text(`Hien thi ${items.length} / ${total} phieu xuat`);
        renderPagination('px', page, res.data.last_page || 1);
    }).fail(function(xhr, status, error) {
        console.error('[KHO-HANG] loadPhieuXuat FAIL:', status, error, xhr.responseText);
        $('#px-bang').html('<tr><td colspan="9" class="text-center text-danger py-4">Loi: ' + error + '</td></tr>');
    });
}

function loadCanhBao() {
    $.get('/admin/api/lo-hang/canh-bao', res => {
        if (!res.success) return;
        const d = res.data;
        const totalAlert = (d.het_han?.length || 0) + (d.sap_het_han?.length || 0) + (d.duoi_dinh_muc?.length || 0) + (d.het_hang?.length || 0);
        if (totalAlert === 0) {
            $('#canh-bao-content').html('<div class="col-12 text-center py-5"><i class="fas fa-check-circle text-success fs-1 mb-3 d-block"></i><h5 class="text-success">Tất cả bình thường!</h5><p class="text-muted">Không có cảnh báo nào lúc này.</p></div>');
            return;
        }
        let html = '';
        if (d.het_han?.length) {
            html += renderAlertGroup('het-han', 'Đã hết HSD', 'het-han', d.het_han, 'bg-danger');
        }
        if (d.sap_het_han?.length) {
            html += renderAlertGroup('sap-het-han', 'Sắp hết HSD (<=30 ngày)', 'sap-het-han', d.sap_het_han, 'bg-warning text-dark');
        }
        if (d.duoi_dinh_muc?.length) {
            html += renderAlertGroup('duoi-dinh-muc', 'Dưới định mức tồn kho', 'duoi-dinh-muc', d.duoi_dinh_muc, 'bg-warning text-dark');
        }
        if (d.het_hang?.length) {
            html += renderAlertGroup('het-hang', 'Hết hàng', 'het-hang', d.het_hang, 'bg-secondary');
        }
        $('#canh-bao-content').html(`<div class="row g-3">${html}</div>`);
    });
}

function renderAlertGroup(type, title, cls, items, badgeCls) {
    const rows = items.map(item => {
        if (type === 'het-han' || type === 'sap-het-han') {
            // ChiTietLoHang items: use item.variant. product
            const variant = item.variant || {};
            const product = item.product || variant.product || {};
            const lo = item.lo_hang || item.lo_hang_nha_cung_cap || {};
            const hsdRaw = item.han_su_dung || '';
            const hsdDate = new Date(hsdRaw);
            const diff = Math.ceil((hsdDate - new Date()) / 86400000);
            return `
            <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                <div>
                    <div class="fw-semibold">${product.ten_san_pham || variant.ten_bien_the || item.id_san_pham || item.variant_id || 'Không xác định'}</div>
                    <div class="small text-muted">
                        Lô: ${lo.ma_lo || (item.id_lo_hang ? 'L-' + item.id_lo_hang : '--')} | Tồn: ${item.so_luong_ton} | HSD: ${hsdRaw.split('T')[0]}
                        ${diff < 0 ? '<span class="text-danger ms-1">Đã hết HSD</span>' : `<span class="text-warning ms-1">Còn ${diff} ngày</span>`}
                    </div>
                </div>
                <span class="badge ${badgeCls}">${item.so_luong_ton}</span>
            </div>`;
        } else {
            // ProductVariant items: use item.product or item.ten_hien_thi
            const product = item.product || {};
            const tenSp = item.ten_hien_thi || product.ten_san_pham || item.id;
            return `
            <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                <div>
                    <div class="fw-semibold">${tenSp}</div>
                    <div class="small text-muted">Mã: ${item.ma_vach || '--'} | Định mức: ${item.dinh_muc_toi_thieu || 0}</div>
                </div>
                <span class="badge ${badgeCls}">${item.so_luong_ton || 0}</span>
            </div>`;
        }
    }).join('');
    return `
    <div class="col-md-6">
        <div class="card canh-bao-card ${cls}">
            <div class="card-header bg-white py-2">
                <div class="d-flex justify-content-between align-items-center">
                    <h6 class="mb-0"><i class="fas fa-triangle-exclamation me-1"></i>${title}</h6>
                    <span class="badge ${badgeCls}">${items.length}</span>
                </div>
            </div>
            <div class="card-body py-2">${rows}</div>
        </div>
    </div>`;
}

// ─── CLICK EVENTS ────────────────────────────────────────
$(document).on('click', '.fefo-detail-row', e => e.stopPropagation());
// Stop propagation on child elements inside clickable rows
$(document).on('click', '.clickable-row td > *', e => e.stopPropagation());
$(document).on('click', '.clickable-row button', e => e.stopPropagation());

$(document).on('click', '.clickable-row', function (e) {
    // Only respond to direct clicks on the toggle column (td:first-child), not other cells
    const firstTd = this.querySelector('td:first-child');
    if (e.target.closest('td') !== firstTd && e.target !== this) {
        return;
    }
    const $row = $(this);
    const id = $row.data('variant-id') || $row.data('id');
    const detail = $(`#fefo-detail-${id}`);
    const icon = $row.find('.fa-chevron-down, .fa-chevron-right');
    if (detail.is(':visible')) {
        detail.hide();
        icon.removeClass('fa-chevron-down').addClass('fa-chevron-right');
        $row.find('td').removeClass('fw-semibold');
    } else {
        $('.fefo-detail-row').hide();
        $('.fefo-detail-row').prev().find('.fa-chevron-down').removeClass('fa-chevron-down').addClass('fa-chevron-right');
        detail.show();
        icon.removeClass('fa-chevron-right').addClass('fa-chevron-down');
        $row.find('td').addClass('fw-semibold');
        $.get('/admin/api/lo-hang/ton-kho?variant_id=' + id, res => {
            if (!res.success) return;
            const chiTiet = res.data.chi_tiet || [];
            const variantUnits = res.data.variant_units || [];

            if (!chiTiet.length && !variantUnits.length) {
                $(`#fefo-detail-content-${id}`).html('<div class="text-muted small">Khong co du lieu ton kho.</div>');
                return;
            }

            // Tinh tong ton (don vi co ban)
            const tongTonBase = chiTiet.reduce((sum, ct) => sum + (parseInt(ct.so_luong_ton) || 0), 0);

            // Hien thi tong ton theo tung don vi quy doi
            let unitStockHtml = '';
            if (variantUnits.length > 0) {
                const unitRows = variantUnits.map(u => {
                    const qty = parseInt(u.so_luong) || 1;
                    const ton = Math.floor(tongTonBase / qty);
                    const tenDonVi = u.ten_don_vi_chuan || u.ten_don_vi;
                    const isEmpty = ton === 0;
                    return `
                    <div class="d-flex justify-content-between align-items-center py-1 px-2 ${isEmpty ? 'text-muted' : ''}">
                        <span>${escapeHtml(tenDonVi)}</span>
                        <span class="${isEmpty ? 'text-muted' : 'fw-bold text-primary'}">${ton.toLocaleString()}</span>
                    </div>`;
                }).join('');
                unitStockHtml = `
                    <div class="small fw-bold text-muted text-uppercase mb-1 mt-2">Ton kho theo don vi</div>
                    <div class="border rounded bg-light mb-2">${unitRows}</div>
                `;
            }

            const rows = chiTiet.map(ct => {
                const lo = ct.lo_hang || {};
                const ncc = lo.nha_cung_cap?.ten_nha_cung_cap || '--';
                const hsdRaw = ct.han_su_dung || '';
                const hsd = hsdRaw.split('T')[0];
                const diff = Math.ceil((new Date(hsdRaw) - new Date()) / 86400000);
                let hsdClass = 'text-success';
                if (diff < 0) hsdClass = 'text-danger';
                else if (diff <= 30) hsdClass = 'text-warning';
                return `
                <div class="d-flex justify-content-between align-items-center py-1 border-bottom">
                    <div class="small">
                        <span class="badge bg-dark me-1">${lo.ma_lo || 'L-' + lo.id}</span>
                        <span class="me-2">NCC: ${escapeHtml(ncc)}</span>
                        <span class="me-2">HSD: <span class="${hsdClass} fw-semibold">${hsd}</span> (${diff > 0 ? diff + ' ngay' : 'Het HSD'})</span>
                    </div>
                    <div class="text-end">
                        <span class="fw-bold text-primary">${(parseInt(ct.so_luong_ton) || 0).toLocaleString()}</span>
                        <span class="text-muted small ms-1">/ ${(parseInt(ct.so_luong_nhap) || 0).toLocaleString()}</span>
                    </div>
                </div>`;
            }).join('');

            // Header: tong ton don vi co ban
            const baseUnitLabel = chiTiet.length
                ? `<div class="d-flex justify-content-between align-items-center py-1 px-2 border-bottom">
                        <span class="text-muted small">Don vi co ban (Lon)</span>
                        <span class="fw-bold text-primary">${tongTonBase.toLocaleString()}</span>
                   </div>`
                : '';

            $(`#fefo-detail-content-${id}`).html(`
                <div class="small fw-bold text-muted text-uppercase mb-1">Tong ton kho</div>
                <div class="border rounded bg-light">${baseUnitLabel}${unitStockHtml}</div>
                <div class="small fw-bold text-muted text-uppercase mt-3 mb-1">Chi tiet theo lo (FEFO)</div>
                <div class="small">${rows}</div>
            `);
        });
    }
});

$(document).on('click', '.btn-xem-ton', function (e) {
    e.stopPropagation();
    const id = $(this).data('variant-id') || $(this).data('id');
    $('#tab-ton-kho').click();
    setTimeout(() => {
        const $row = $(`.clickable-row[data-variant-id="${id}"], .clickable-row[data-id="${id}"]`).first();
        if ($row.length) {
            const firstTd = $row[0].querySelector('td:first-child');
            const fakeEvent = { target: firstTd, stopPropagation: () => {} };
            $row.trigger('click', [fakeEvent]);
        }
    }, 100);
});

$(document).on('click', '.btn-xem-lo', function () {
    const id = $(this).data('id');
    $.get('/admin/api/lo-hang/' + id, res => {
        if (!res.success) return;
        const lo = res.data;
        const tongNhap = (lo.chi_tiet_lo_hang || []).reduce((s, c) => s + (c.so_luong_nhap || 0), 0);
        const tongTon = (lo.chi_tiet_lo_hang || []).reduce((s, c) => s + (c.so_luong_ton || 0), 0);
        const rows = (lo.chi_tiet_lo_hang || []).map(ct => {
            const variant = ct.variant || {};
            const product = variant.product || {};
            const baseName = variant.ten_bien_the && variant.ten_bien_the !== product.ten_san_pham
                ? `${product.ten_san_pham || ''} - ${variant.ten_bien_the}`
                : (product.ten_san_pham || ct.variant_id);
            const attrs = (ct.thuoc_tinh_labels && ct.thuoc_tinh_labels.length)
                ? ` - ${ct.thuoc_tinh_labels.join(', ')}`
                : '';
            const code = variant.ma_vach || ct.variant_id;
            const displayName = `${baseName}${attrs} (${code})`;
            return `
            <tr>
                <td>${displayName}</td>
                <td class="text-center">${(ct.so_luong_nhap || 0).toLocaleString()}</td>
                <td class="text-center">${(ct.so_luong_ton || 0).toLocaleString()}</td>
                <td class="text-center">${Number(ct.gia_nhap || 0).toLocaleString()} d</td>
                <td class="text-center">${(ct.han_su_dung || '').split('T')[0]}</td>
                <td class="text-center">
                    ${ct.so_luong_ton > 0 ? '<span class="badge bg-success">Còn hàng</span>' : '<span class="badge bg-secondary">Hết</span>'}
                </td>
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
            </table>
        `);
        new bootstrap.Modal(document.getElementById('modal-xem-lo')).show();
    });
});

$(document).on('click', '.btn-xoa-lo', function () {
    if (!confirm('Xóa lô hàng này?')) return;
    const id = $(this).data('id');
    $.ajax({ url: '/admin/api/lo-hang/' + id, method: 'DELETE', success: res => { hienBao('success', res.message); loadLoHang(lhPage); loadStats(); }, error: x => hienBao('danger', x.responseJSON?.message || 'Lỗi.') });
});

$(document).on('click', '.btn-xem-pn', function () {
    const id = $(this).data('id');
    $.get('/admin/api/phieu-nhap/' + id, res => {
        if (!res.success) return;
        const pn = res.data;
        const loaiLabel = pn.loai_nhap === 'mua_hang' ? 'Nhập mua hàng' : 'Trả lại từ khách';
        const rows = (pn.chi_tiet_phieu || []).map(ct => {
            const variant = ct.variant || {};
            const product = variant.product || {};
            const baseName = variant.ten_bien_the && variant.ten_bien_the !== product.ten_san_pham
                ? `${product.ten_san_pham || ''} - ${variant.ten_bien_the}`
                : (product.ten_san_pham || variant.ten_bien_the || ct.id_san_pham || ct.variant_id || '');
            const attrs = (ct.thuoc_tinh_labels && ct.thuoc_tinh_labels.length)
                ? ` - ${ct.thuoc_tinh_labels.join(', ')}`
                : '';
            const code = variant.ma_vach || ct.variant_id || '';
            const displayName = `${baseName}${attrs} (${code})`;
            const lo = ct.lo_hang || {};
            const ghiChuCt = ct.ghi_chu
                ? `<small class="text-info d-block">${ct.ghi_chu}</small>`
                : '';
            return `<tr>
                <td>${displayName}${ghiChuCt}</td>
                <td class="text-center">${(ct.so_luong || 0).toLocaleString()}</td>
                <td class="text-center">${Number(ct.gia_nhap || 0).toLocaleString()} d</td>
                <td class="text-center">${(ct.han_su_dung || '').split('T')[0]}</td>
                <td class="text-center">${lo.ma_lo || 'L-' + (ct.id_lo_hang || '')}</td>
                <td class="text-end fw-bold">${((ct.so_luong || 0) * (ct.gia_nhap || 0)).toLocaleString()} d</td>
            </tr>`;
        }).join('') || '<tr><td colspan="6" class="text-center text-muted">Không có chi tiết</td></tr>';
        const tongGt = (pn.chi_tiet_phieu || []).reduce((s, ct) => s + (ct.so_luong || 0) * (ct.gia_nhap || 0), 0);
        $('#modal-xem-pn-body').html(`
            <div class="row mb-3">
                <div class="col-md-3"><strong>Mã phiếu:</strong> PN-${pn.id_phieu}</div>
                <div class="col-md-3"><strong>Loai:</strong> ${loaiLabel}</div>
                <div class="col-md-3"><strong>NCC:</strong> ${pn.phieu?.nha_cung_cap?.ten_nha_cung_cap || '--'}</div>
                <div class="col-md-3"><strong>Ngay:</strong> ${pn.created_at?.slice(0, 10) || ''}</div>
            </div>
            <p><strong>Ghi chú:</strong> ${pn.ghi_chu || '--'}</p>
            <table class="table table-sm table-bordered">
                <thead class="table-light"><tr><th>Sản phẩm</th><th class="text-center">SL nhập</th><th class="text-center">Giá nhập</th><th class="text-center">HSD</th><th class="text-center">Lô</th><th class="text-end">Thành tiền</th></tr></thead>
                <tbody>${rows}</tbody>
                <tfoot><tr><td colspan="5" class="text-end fw-bold">Tổng cộng:</td><td class="text-end fw-bold text-danger">${tongGt.toLocaleString()} d</td></tr></tfoot>
            </table>
        `);
        new bootstrap.Modal(document.getElementById('modal-xem-pn')).show();
    });
});

$(document).on('click', '.btn-xoa-pn', function () {
    if (!confirm('Xóa phiếu nhập này?')) return;
    const id = $(this).data('id');
    $.ajax({ url: '/admin/api/phieu-nhap/' + id, method: 'DELETE', success: res => { hienBao('success', res.message); loadPhieuNhap(pnPage); loadStats(); }, error: x => hienBao('danger', x.responseJSON?.message || 'Lỗi.') });
});

$(document).on('click', '.btn-xem-px', function () {
    const id = $(this).data('id');
    $.get('/admin/api/phieu-xuat/' + id, res => {
        if (!res.success) return;
        const px = res.data;
        const loaiLabel = px.loai_xuat === 'tra_hang_nha_cung_cap' ? 'Trả hàng NCC' : 'Tiêu hủy';
        const rows = (px.chi_tiet_phieu || []).map(ct => {
            const variant = ct.variant || {};
            const product = variant.product || ct.san_pham || {};
            const baseName = variant.ten_bien_the && variant.ten_bien_the !== product.ten_san_pham
                ? `${product.ten_san_pham || ''} - ${variant.ten_bien_the}`
                : (product.ten_san_pham || variant.ten_bien_the || ct.variant_id || '---');
            const attrs = (ct.thuoc_tinh_labels && ct.thuoc_tinh_labels.length)
                ? ` - ${ct.thuoc_tinh_labels.join(', ')}`
                : '';
            const code = variant.ma_vach || ct.variant_id || '';
            const displayName = `${baseName}${attrs} (${code})`;
            const hsd = (ct.chi_tiet_lo_hang?.han_su_dung || ct.han_su_dung || '').split('T')[0];
            return `<tr>
                <td>${displayName}</td>
                <td class="text-center text-danger fw-bold">${(ct.so_luong || 0).toLocaleString()}</td>
                <td class="text-center">${hsd || '--'}</td>
                <td class="text-center">${ct.lo_hang?.ma_lo || 'L-' + (ct.id_lo_hang || '')}</td>
            </tr>`;
        }).join('') || '<tr><td colspan="4" class="text-center text-muted">Không có chi tiết</td></tr>';
        const tongSl = (px.chi_tiet_phieu || []).reduce((s, ct) => s + (ct.so_luong || 0), 0);
        $('#modal-xem-px-body').html(`
            <div class="row mb-3">
                <div class="col-md-3"><strong>Mã phiếu:</strong> PX-${px.id_phieu}</div>
                <div class="col-md-3"><strong>Loai:</strong> ${loaiLabel}</div>
                <div class="col-md-3"><strong>NCC:</strong> ${px.phieu?.nha_cung_cap?.ten_nha_cung_cap || '--'}</div>
                <div class="col-md-3"><strong>Ngay:</strong> ${px.created_at?.slice(0, 10) || ''}</div>
            </div>
            <p><strong>Lý do:</strong> ${px.ly_do || '--'}</p>
            <table class="table table-sm table-bordered">
                <thead class="table-light"><tr><th>Sản phẩm</th><th class="text-center">SL xuất</th><th class="text-center">HSD lô</th><th class="text-center">Lô</th></tr></thead>
                <tbody>${rows}</tbody>
                <tfoot><tr><td colspan="3" class="text-end fw-bold">Tổng SL xuất:</td><td class="text-center fw-bold text-danger">${tongSl.toLocaleString()}</td></tr></tfoot>
            </table>
        `);
        new bootstrap.Modal(document.getElementById('modal-xem-px')).show();
    });
});

$(document).on('click', '.btn-xoa-px', function () {
    if (!confirm('Xóa phiếu xuất này và hoàn tăng tồn kho?')) return;
    const id = $(this).data('id');
    $.ajax({ url: '/admin/api/phieu-xuat/' + id, method: 'DELETE', success: res => { hienBao('success', res.message); loadPhieuXuat(pxPage); loadStats(); }, error: x => hienBao('danger', x.responseJSON?.message || 'Lỗi.') });
});

// ─── NCC TAB EVENTS ───────────────────────────────────────
$('#tab-ncc').on('shown.bs.tab', () => loadNhaCungCap(nccPage));

$(document).on('click', '#ncc-btn-them', () => {
    $('#form-them-ncc')[0].reset();
    $('#form-them-ncc .form-control').removeClass('is-invalid');
    new bootstrap.Modal(document.getElementById('modal-them-ncc')).show();
});

$(document).on('click', '#ncc-btn-search', () => loadNhaCungCap(1, $('#ncc-search').val()));
$(document).on('click', '#ncc-btn-refresh', () => { $('#ncc-search').val(''); loadNhaCungCap(1); });

$(document).on('keypress', '#ncc-search', e => { if (e.which === 13) loadNhaCungCap(1, $('#ncc-search').val()); });

$(document).on('click', '.btn-sua-ncc', function () {
    const id = $(this).data('id');
    $.get('/admin/api/nha-cung-cap/' + id, res => {
        if (!res.success) { hienBao('danger', 'Khong tai duoc du lieu.'); return; }
        const n = res.item;
        $('#ncc_sua_id').val(n.id);
        $('#ncc_sua_ten').val(n.ten_nha_cung_cap);
        $('#ncc_sua_email').val(n.email || '');
        $('#ncc_sua_sdt').val(n.so_dien_thoai);
        $('#ncc_sua_nguoi').val(n.nguoi_dai_dien || '');
        $('#form-sua-ncc .form-control').removeClass('is-invalid');
        new bootstrap.Modal(document.getElementById('modal-sua-ncc')).show();
    }).fail(() => hienBao('danger', 'Lỗi ket noi.'));
});

$(document).on('click', '.btn-xoa-ncc', function () {
    const id = $(this).data('id');
    const ten = $(this).data('ten');
    if (!confirm('Xóa nhà cung cấp "' + ten + '"?')) return;
    $.ajax({ url: '/admin/api/nha-cung-cap/' + id, method: 'DELETE', success: res => {
        hienBao('success', res.message);
        loadNhaCungCap(nccPage);
    }, error: x => hienBao('danger', x.responseJSON?.message || 'Lỗi xoa.') });
});

// ─── DYNAMIC ROWS ────────────────────────────────────────
function spDisplayText(sp) {
    const baseName = sp.ten_bien_the && sp.ten_bien_the !== sp.ten_san_pham
        ? `${sp.ten_san_pham} - ${sp.ten_bien_the}`
        : sp.ten_san_pham;
    const attrs = (sp.thuoc_tinh_labels && sp.thuoc_tinh_labels.length)
        ? ` - ${sp.thuoc_tinh_labels.join(', ')}`
        : '';
    const code = sp.ma_vach || sp.id;
    return `${baseName}${attrs} (${code})`;
}

function spOptions(existingId) {
    return sanPhamAll.map(sp =>
        `<option value="${sp.id}" ${sp.id == existingId ? 'selected' : ''}>${spDisplayText(sp)}</option>`
    ).join('');
}

function addPxRow(id, sl, displayInfo) {
    // #region agent log
    fetch('http://127.0.0.1:7359/ingest/002bc91b-88fd-46aa-85b0-ce56b4017dd2',{method:'POST',headers:{'Content-Type':'application/json','X-Debug-Session-Id':'b085c7'},body:JSON.stringify({sessionId:'b085c7',location:'kho-hang.js:addPxRow',message:'addPxRow start',data:{id,sl,hasDisplayInfo:!!displayInfo,sanPhamAllLen:sanPhamAll.length,foundSp:!!sanPhamAll.find(x=>x.id==id),displayInfo},timestamp:Date.now(),hypothesisId:'E'})}).catch(()=>{});
    // #endregion
    const idx = pxIdx++;
    let opts;
    let selectTon = 0;
    if (displayInfo && id) {
        // Được gọi từ search: dựng option đúng với sản phẩm user vừa chọn (tránh dùng sanPhamAll có LIMIT 50)
        const label = buildPxLabel(displayInfo.tenSp, displayInfo.tenBt, displayInfo.attrs, displayInfo.vach);
        selectTon = displayInfo.ton || 0;
        opts = `<option value="${id}" data-ton="${selectTon}" selected>${label}</option>`;
    } else {
        opts = sanPhamAll.map(sp =>
            `<option value="${sp.id}" data-ton="${sp.chi_tiet_lo_hang_ton_sum_so_luong_ton || 0}" ${sp.id == id ? 'selected' : ''}>${spDisplayText(sp)}</option>`
        ).join('');
    }
    $('#px-empty-row').remove();
    $('#px-ds-sp').append(`<tr data-sp-id="${id || ''}">
        <td><select class="form-select form-select-sm px-sp-select" name="chi_tiet[${idx}][variant_id]">${opts || '<option value="">-- Chon --</option>'}</select></td>
        <td class="text-center text-muted ton-cell small">${selectTon ? 'Tồn: ' + selectTon.toLocaleString() : '--'}</td>
        <td>
            <select class="form-select form-select-sm px-lo-select" name="chi_tiet[${idx}][id_chi_tiet_lo_hang]" disabled>
                <option value="">-- Chon lo --</option>
            </select>
            <small class="text-muted px-lo-info d-block mt-1">--</small>
        </td>
        <td><input type="number" class="form-control form-control-sm px-sl-input" name="chi_tiet[${idx}][so_luong]" value="${sl || 1}" min="1"></td>
        <td><button type="button" class="btn btn-sm btn-outline-danger btn-remove-px-row" data-id="${id || ''}"><i class="fas fa-times"></i></button></td>
    </tr>`);
    if (id) {
        if (!displayInfo) updateTonCell($('#px-ds-sp tr:last .px-sp-select'));
        loadLoOptions($('#px-ds-sp tr:last'), id);
    } else {
        updateTonCell($('#px-ds-sp tr:last .px-sp-select'));
    }
    updatePxTongSl();
}

function buildPxLabel(tenSp, tenBt, attrs, vach) {
    let label = tenSp || '';
    const variantPart = (tenBt && tenBt !== tenSp) ? tenBt : '';
    const attrsPart = attrs ? attrs : '';
    const tail = [variantPart, attrsPart].filter(Boolean).join(' - ');
    if (tail) label += ' - ' + tail;
    const code = vach || '';
    if (code) label += ` (${code})`;
    return label;
}

function removePxRow(btn) {
    const $tr = $(btn).closest('tr');
    const spId = parseInt($tr.attr('data-sp-id'));
    if (spId) selectedPxProducts.delete(spId);
    $tr.remove();
    if (!$('#px-ds-sp tr').length || $('#px-ds-sp tr').length === 1 && $('#px-ds-sp tr').attr('id') === 'px-empty-row') {
        $('#px-ds-sp').html('<tr id="px-empty-row"><td colspan="5" class="text-center text-muted py-3">Chưa chọn sản phẩm nào.</td></tr>');
    } else {
        // Nếu không còn row thật nào thì thêm empty
        const hasReal = $('#px-ds-sp tr').toArray().some(r => r.id !== 'px-empty-row');
        if (!hasReal) {
            $('#px-ds-sp').html('<tr id="px-empty-row"><td colspan="5" class="text-center text-muted py-3">Chưa chọn sản phẩm nào.</td></tr>');
        }
    }
    // Bật lại nút "Chọn" trong bảng kết quả search (chỉ nếu nút tồn tại)
    if (spId) {
        const $btnSel = $(`.btn-chon-sp-xuat[data-id="${spId}"]`);
        if ($btnSel.length) {
            const isVariant = $btnSel.data('variant') == 1;
            $btnSel.prop('disabled', false).removeClass('btn-secondary').addClass(isVariant ? 'btn-success' : 'btn-primary').html('<i class="fas fa-plus"></i> Chọn');
        }
    }
    updatePxTongSl();
}

function updatePxTongSl() {
    let total = 0;
    $('#px-ds-sp tr').each(function () {
        if (this.id === 'px-empty-row') return;
        const v = parseInt($(this).find('.px-sl-input').val());
        if (!isNaN(v)) total += v;
    });
    $('#px-tong-sl').text(total.toLocaleString());
}

function addLoRow(id, sl, gia, hsd) {
    const idx = loIdx++;
    const opts = spOptions(id);
    $('#lo-ds-sp').append(`<tr>
        <td><select class="form-select form-select-sm" name="chi_tiet[${idx}][variant_id]">${opts || '<option value="">-- Chon --</option>'}</select></td>
        <td><input type="number" class="form-control form-control-sm" name="chi_tiet[${idx}][so_luong_nhap]" value="${sl || 1}" min="1"></td>
        <td><input type="number" class="form-control form-control-sm" name="chi_tiet[${idx}][gia_nhap]" value="${gia || 0}" min="0" step="100"></td>
        <td><input type="date" class="form-control form-control-sm" name="chi_tiet[${idx}][han_su_dung]" value="${hsd || ''}"></td>
        <td><button type="button" class="btn btn-sm btn-outline-danger" onclick="removeLoRow(this)"><i class="fas fa-times"></i></button></td>
    </tr>`);
}

function removeLoRow(btn) { if ($('#lo-ds-sp tr').length > 1) $(btn).closest('tr').remove(); }

$(document).on('change', '.px-sp-select', function () {
    updateTonCell(this);
    loadLoOptions($(this).closest('tr'), $(this).val());
});

$(document).on('change', '.px-lo-select', function () {
    const opt = $(this).find('option:selected');
    const info = $(this).closest('tr').find('.px-lo-info');
    info.text(opt.val() ? `HSD ${opt.data('hsd') || '--'}, tồn lô ${opt.data('ton') || 0}` : '--');
});

function loadLoOptions(row, idSp) {
    // #region agent log
    fetch('http://127.0.0.1:7359/ingest/002bc91b-88fd-46aa-85b0-ce56b4017dd2',{method:'POST',headers:{'Content-Type':'application/json','X-Debug-Session-Id':'b085c7'},body:JSON.stringify({sessionId:'b085c7',location:'kho-hang.js:loadLoOptions',message:'loadLoOptions called from PX',data:{idSp},timestamp:Date.now(),hypothesisId:'C'})}).catch(()=>{});
    // #endregion
    const loSelect = row.find('.px-lo-select');
    const info = row.find('.px-lo-info');
    if (!idSp) {
        loSelect.html('<option value="">-- Chon lo --</option>').prop('disabled', true);
        info.text('--');
        return;
    }
    loSelect.html('<option value="">-- Dang tai --</option>').prop('disabled', true);
    info.text('Đang tải danh sách lô...');
    $.get('/admin/api/lo-hang/ton-kho?variant_id=' + idSp, res => {
        if (!res.success || !res.data.chi_tiet || !res.data.chi_tiet.length) {
            loSelect.html('<option value="">-- Hết hàng --</option>').prop('disabled', true);
            info.html('<span class="text-danger">Sản phẩm đã hết tồn kho</span>');
            return;
        }
        const opts = res.data.chi_tiet.map(ct => {
            const lo = ct.lo_hang || {};
            const hsd = (ct.han_su_dung || '').split('T')[0];
            const maLo = lo.ma_lo || ('L-' + lo.id);
            return `<option value="${ct.id}" data-ton="${ct.so_luong_ton}" data-hsd="${hsd}" data-malo="${maLo}">${maLo} | HSD ${hsd} | tồn ${ct.so_luong_ton}</option>`;
        }).join('');
        loSelect.html('<option value="">-- Chon lo --</option>' + opts).prop('disabled', false);
        info.text('');
    }).fail(() => {
        loSelect.html('<option value="">-- Lỗi tải lô --</option>').prop('disabled', true);
        info.html('<span class="text-danger">Không tải được danh sách lô</span>');
    });
}

function updateTonCell(el) {
    const val = $(el).val();
    const row = $(el).closest('tr');
    const cell = row.find('.ton-cell');
    if (val) {
        const opt = $(el).find('option:selected');
        cell.text('Ton: ' + (opt.data('ton') || 0).toLocaleString());
    } else { cell.text('--'); }
}

// ─── FORM SUBMITS ────────────────────────────────────────
$(document).on('submit', '#form-them-ncc', function (e) {
    e.preventDefault();
    const btn = $('#ncc_btn_luu');
    btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> Dang luu...');
    $.post('/admin/api/nha-cung-cap', {
        _token: $('meta[name="csrf-token"]').attr('content'),
        ten_nha_cung_cap: $('#ncc_them_ten').val(),
        email: $('#ncc_them_email').val(),
        so_dien_thoai: $('#ncc_them_sdt').val(),
        nguoi_dai_dien: $('#ncc_them_nguoi').val(),
    }, res => {
        if (res.success) {
            bootstrap.Modal.getInstance(document.getElementById('modal-them-ncc')).hide();
            hienBao('success', res.message);
            loadNhaCungCap(1);
        } else {
            hienBao('danger', res.message);
        }
    }).fail(x => {
        const err = x.responseJSON;
        if (err && err.errors) {
            const e = err.errors;
            $('#ncc_them_ten').toggleClass('is-invalid', !!e.ten_nha_cung_cap)
                .next('.invalid-feedback').text(e.ten_nha_cung_cap?.[0] || '');
            $('#ncc_them_email').toggleClass('is-invalid', !!e.email)
                .next('.invalid-feedback').text(e.email?.[0] || '');
            $('#ncc_them_sdt').toggleClass('is-invalid', !!e.so_dien_thoai)
                .next('.invalid-feedback').text(e.so_dien_thoai?.[0] || '');
        } else {
            hienBao('danger', err?.message || 'Lỗi server.');
        }
    }).always(() => btn.prop('disabled', false).html('<i class="fas fa-save me-1"></i>Lưu'));
});

$(document).on('submit', '#form-sua-ncc', function (e) {
    e.preventDefault();
    const id = $('#ncc_sua_id').val();
    const btn = $('#ncc_btn_cap_nhat');
    btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> Dang cap nhat...');
    $.ajax({ url: '/admin/api/nha-cung-cap/' + id, method: 'PUT', headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }, data: {
        ten_nha_cung_cap: $('#ncc_sua_ten').val(),
        email: $('#ncc_sua_email').val(),
        so_dien_thoai: $('#ncc_sua_sdt').val(),
        nguoi_dai_dien: $('#ncc_sua_nguoi').val(),
    }, success: res => {
        if (res.success) {
            bootstrap.Modal.getInstance(document.getElementById('modal-sua-ncc')).hide();
            hienBao('success', res.message);
            loadNhaCungCap(nccPage);
        } else {
            hienBao('danger', res.message);
        }
    }, error: x => {
        const err = x.responseJSON;
        if (err && err.errors) {
            const e = err.errors;
            $('#ncc_sua_ten').toggleClass('is-invalid', !!e.ten_nha_cung_cap)
                .next('.invalid-feedback').text(e.ten_nha_cung_cap?.[0] || '');
            $('#ncc_sua_email').toggleClass('is-invalid', !!e.email)
                .next('.invalid-feedback').text(e.email?.[0] || '');
            $('#ncc_sua_sdt').toggleClass('is-invalid', !!e.so_dien_thoai)
                .next('.invalid-feedback').text(e.so_dien_thoai?.[0] || '');
        } else {
            hienBao('danger', err?.message || 'Lỗi server.');
        }
    }, complete: () => btn.prop('disabled', false).html('<i class="fas fa-save me-1"></i>Cập nhật') });
});

function buildChiTiet(tableId, prefix) {
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

function submitPhieuNhap() {
    const chiTiet = buildChiTiet('pn-ds-sp', 'pn');
    if (!chiTiet.length) {
        $('#pn-alert-container').html('<div class="alert alert-warning">Vui lòng thêm ít nhất một sản phẩm trước khi lưu.</div>');
        return;
    }
    $('#pn-alert-container').html('');
    const data = {
        loai_nhap: $('#pn-loai').val(),
        id_nha_cung_cap: $('#pn-ncc').val() || null,
        ghi_chu: $('#pn-ghi-chu').val(),
        tao_lo_moi: '1',
        id_lo_hang: '',
        chi_tiet: chiTiet,
    };
    $.ajax({ url: '/admin/api/phieu-nhap', method: 'POST', headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }, contentType: 'application/json', data: JSON.stringify(data),
        success: res => {
            bootstrap.Modal.getInstance(document.getElementById('modal-tao-pn')).hide();
            hienBao('success', res.message);
            loadPhieuNhap(1); loadLoHang(1); loadTonKho(tkPage); loadStats();
        },
        error: x => {
            const msg = x.responseJSON?.message || 'Có lỗi xảy ra khi lưu nhập hàng.';
            $('#pn-alert-container').html(`<div class="alert alert-danger">${msg}</div>`);
            hienBao('danger', msg);
        }
    });
}

function submitPhieuXuat() {
    // #region agent log
    const rowsLog = $('#px-ds-sp tr').toArray().map(r => ({id:r.id,dataset:r.dataset.spId||null,isEmpty:r.id==='px-empty-row'}));
    fetch('http://127.0.0.1:7359/ingest/002bc91b-88fd-46aa-85b0-ce56b4017dd2',{method:'POST',headers:{'Content-Type':'application/json','X-Debug-Session-Id':'b085c7'},body:JSON.stringify({sessionId:'b085c7',location:'kho-hang.js:submitPhieuXuat',message:'PX submit start',data:{rowsLog},timestamp:Date.now(),hypothesisId:'D'})}).catch(()=>{});
    // #endregion
    const chiTiet = [];
    let hasError = false;
    $('#px-ds-sp tr').each(function () {
        const sp = $(this).find('.px-sp-select').val();
        const loSel = $(this).find('.px-lo-select');
        const lo = loSel.val();
        const sl = parseInt($(this).find('.px-sl-input').val());
        const ton = parseInt(loSel.find('option:selected').data('ton') || 0);
        if (sp && sl) {
            if (!lo) {
                hienBao('warning', 'Vui lòng chọn lô hàng cho từng sản phẩm.');
                hasError = true;
                return false;
            }
            if (sl > ton) {
                hienBao('warning', `Lo da chon chi ton ${ton}, khong du de xuat ${sl}.`);
                hasError = true;
                return false;
            }
            chiTiet.push({ variant_id: sp, id_chi_tiet_lo_hang: lo, so_luong: sl });
        }
    });
    if (hasError || !chiTiet.length) {
        if (!chiTiet.length && !hasError) hienBao('warning', 'Vui lòng thêm ít nhất một sản phẩm.');
        return;
    }
    const data = {
        loai_xuat: $('#px-loai').val(),
        id_nha_cung_cap: $('#px-ncc').val() || null,
        ly_do: $('#px-ly-do').val(),
        chi_tiet: chiTiet,
    };
    $.ajax({ url: '/admin/api/phieu-xuat', method: 'POST', headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }, contentType: 'application/json', data: JSON.stringify(data),
        success: res => {
            bootstrap.Modal.getInstance(document.getElementById('modal-tao-px')).hide();
            hienBao('success', res.message);
            loadPhieuXuat(1); loadTonKho(tkPage); loadStats();
        },
        error: x => {
            console.error('PhieuXuat error:', x);
            let msg = x.responseJSON?.message;
            if (!msg && x.responseText) {
                try { const j = JSON.parse(x.responseText); msg = j.message; } catch(e) { msg = x.responseText.substring(0, 200); }
            }
            if (!msg) msg = 'Có lỗi xảy ra. Mở console để xem chi tiết.';
            hienBao('danger', 'Lỗi ' + x.status + ': ' + msg);
        }
    });
}

function submitLoHang() {
    const chiTiet = buildChiTiet('lo-ds-sp', 'lo');
    if (!chiTiet.length) { hienBao('warning', 'Vui lòng thêm ít nhất một sản phẩm.'); return; }
    const data = {
        id_nha_cung_cap: $('#lo-ncc').val() || null,
        ma_lo: $('#lo-ma').val(),
        ngay_nhap: $('#lo-ngay').val(),
        ghi_chu: $('#lo-ghi-chu').val(),
        chi_tiet,
    };
    $.ajax({ url: '/admin/api/lo-hang', method: 'POST', headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }, contentType: 'application/json', data: JSON.stringify(data),
        success: res => {
            bootstrap.Modal.getInstance(document.getElementById('modal-tao-lo')).hide();
            hienBao('success', res.message);
            loadLoHang(1); loadTonKho(tkPage); loadStats();
        },
        error: x => hienBao('danger', x.responseJSON?.message || 'Có lỗi xảy ra.')
    });
}

// ─── PAGINATION ───────────────────────────────────────────
function renderPagination(prefix, current, total) {
    if (total <= 1) { $('#' + prefix + '-phan-trang').html(''); return; }
    let html = '<ul class="pagination mb-0">';
    if (current > 1) html += `<li class="page-item"><a class="page-link" href="javascript:;" onclick="go${prefix}(${current - 1})">|</a></li>`;
    for (let i = 1; i <= total; i++) {
        if (i === 1 || i === total || Math.abs(i - current) <= 2) {
            html += `<li class="page-item ${i === current ? 'active' : ''}"><a class="page-link" href="javascript:;" onclick="go${prefix}(${i})">${i}</a></li>`;
        } else if (Math.abs(i - current) === 3) {
            html += '<li class="page-item disabled"><span class="page-link">...</span></li>';
        }
    }
    if (current < total) html += `<li class="page-item"><a class="page-link" href="javascript:;" onclick="go${prefix}(${current + 1})">></a></li>`;
    html += '</ul>';
    $('#' + prefix + '-phan-trang').html(html);
}

function gotk(p) { loadTonKho(p); }
function golh(p) { loadLoHang(p); }
function gopn(p) { loadPhieuNhap(p); }
function gopx(p) { loadPhieuXuat(p); }

// ─── IMPORT/EXPORT EXCEL - PHIẾU NHẬP ─────────────────────────────────
let importPnFile = null;

// Nút Export Excel - Phiếu nhập: mở modal để chọn khoảng thời gian
$('#pn-btn-export').click(function () {
    const tu = $('#pn-filter-tu').val();
    const den = $('#pn-filter-den').val();
    if (tu) $('#xuat-pn-tu').val(tu);
    if (den) $('#xuat-pn-den').val(den);
    new bootstrap.Modal(document.getElementById('modal-xuat-pn')).show();
});

// Helper preset ngày (dùng chung cho cả pn & px)
function fillDateRange(tuSel, denSel, preset) {
    const today = new Date();
    const fmt = (d) => d.toISOString().slice(0, 10);
    let tu = '', den = '';
    if (preset === 'today') {
        tu = den = fmt(today);
    } else if (preset === '7days') {
        const start = new Date(today);
        start.setDate(start.getDate() - 6);
        tu = fmt(start); den = fmt(today);
    } else if (preset === 'this_month') {
        const first = new Date(today.getFullYear(), today.getMonth(), 1);
        tu = fmt(first); den = fmt(today);
    } else if (preset === 'last_month') {
        const first = new Date(today.getFullYear(), today.getMonth() - 1, 1);
        const last = new Date(today.getFullYear(), today.getMonth(), 0);
        tu = fmt(first); den = fmt(last);
    }
    $(tuSel).val(tu);
    $(denSel).val(den);
}

$(document).on('click', '.pn-preset', function () {
    fillDateRange('#xuat-pn-tu', '#xuat-pn-den', $(this).data('preset'));
});

$(document).on('click', '.px-preset', function () {
    fillDateRange('#xuat-px-tu', '#xuat-px-den', $(this).data('preset'));
});

$('#form-xuat-pn').submit(function (e) {
    e.preventDefault();
    const loai = $('#xuat-pn-loai').val();
    const tu = $('#xuat-pn-tu').val();
    const den = $('#xuat-pn-den').val();
    if (tu && den && tu > den) {
        hienBao('warning', 'Từ ngày phải nhỏ hơn hoặc bằng đến ngày.');
        return;
    }
    const params = new URLSearchParams();
    if (loai) params.set('loai_nhap', loai);
    if (tu) params.set('tu_ngay', tu);
    if (den) params.set('den_ngay', den);
    let url = '/admin/api/phieu-nhap/export';
    const qs = params.toString();
    if (qs) url += '?' + qs;
    bootstrap.Modal.getInstance(document.getElementById('modal-xuat-pn')).hide();
    window.location.href = url;
});

// Nút Import Excel - Phiếu nhập
$('#pn-btn-import').click(function () {
    importPnFile = null;
    $('#form-import-pn')[0].reset();
    $('#import-pn-preview').addClass('d-none');
    $('#import-pn-submit').prop('disabled', true);
    // Load NCC dropdown
    $.get('/admin/api/nha-cung-cap/dropdown', res => {
        if (res.success) {
            const opts = res.data.map(n => `<option value="${n.id}">${n.ten_nha_cung_cap}</option>`).join('');
            $('#import-pn-ncc').html('<option value="">-- Chọn NCC --</option>' + opts);
        }
    });
    new bootstrap.Modal(document.getElementById('modal-import-pn')).show();
});

// Download template
$('#import-pn-download-template').click(function () {
    window.open('/admin/api/phieu-nhap/download-template', '_blank');
});

// File input change
$('#import-pn-file').change(function () {
    const file = this.files[0];
    if (file) {
        importPnFile = file;
        $('#import-pn-filename').text(file.name);
        $('#import-pn-preview').removeClass('d-none');
        $('#import-pn-submit').prop('disabled', false);
    }
});

// Drop zone
$('#drop-zone-pn').click(function () { $('#import-pn-file').click(); });
$('#drop-zone-pn').on('dragover', function (e) { e.preventDefault(); $(this).css('background-color', '#e7f1ff'); });
$('#drop-zone-pn').on('dragleave', function () { $(this).css('background-color', '#f8f9fa'); });
$('#drop-zone-pn').on('drop', function (e) {
    e.preventDefault();
    $(this).css('background-color', '#f8f9fa');
    const file = e.originalEvent.dataTransfer.files[0];
    if (file && (file.name.endsWith('.xlsx') || file.name.endsWith('.xls') || file.name.endsWith('.csv'))) {
        importPnFile = file;
        $('#import-pn-file')[0].files = e.originalEvent.dataTransfer.files;
        $('#import-pn-filename').text(file.name);
        $('#import-pn-preview').removeClass('d-none');
        $('#import-pn-submit').prop('disabled', false);
    } else {
        hienBao('danger', 'Vui lòng chọn file Excel (.xlsx, .xls, .csv)');
    }
});

// Remove file
$('#import-pn-remove').click(function () {
    importPnFile = null;
    $('#import-pn-file').val('');
    $('#import-pn-preview').addClass('d-none');
    $('#import-pn-submit').prop('disabled', true);
});

// Submit import
$('#form-import-pn').submit(function (e) {
    e.preventDefault();
    if (!importPnFile) { hienBao('danger', 'Vui lòng chọn file Excel.'); return; }

    const formData = new FormData();
    formData.append('file', importPnFile);
    formData.append('loai_nhap', $('#import-pn-loai').val());
    formData.append('id_nha_cung_cap', $('#import-pn-ncc').val() || '');
    formData.append('ghi_chu', $('#import-pn-ghi-chu').val() || '');

    $('#import-pn-submit').prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Đang import...');

    $.ajax({
        url: '/admin/api/phieu-nhap/import',
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
        data: formData,
        processData: false,
        contentType: false,
        success: function (res) {
            if (res.success) {
                bootstrap.Modal.getInstance(document.getElementById('modal-import-pn')).hide();

                // Neu co dong loi (partial success) -> hien thi modal warning
                if (res.errors && res.errors.length > 0) {
                    showImportPnResultModal(res);
                } else {
                    hienBao('success', res.message);
                }
                loadPhieuNhap(1);
                importPnFile = null;
            } else {
                // That bai hoan toan
                showImportPnResultModal(res);
            }
        },
        error: function (x) {
            hienBao('danger', x.responseJSON?.message || 'Import thất bại.');
        },
        complete: function () {
            $('#import-pn-submit').prop('disabled', false).html('<i class="fas fa-upload me-1"></i>Import');
        }
    });
});

// Hien thi modal ket qua import (ca thanh cong co loi lan that bai)
function showImportPnResultModal(res) {
    // Tao modal neu chua co
    let $modal = $('#modal-import-pn-result');
    if ($modal.length === 0) {
        const html = `
            <div class="modal fade" id="modal-import-pn-result" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Kết quả Import Phiếu Nhập</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body" id="import-pn-result-body"></div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                        </div>
                    </div>
                </div>
            </div>
        `;
        $('body').append(html);
        $modal = $('#modal-import-pn-result');
    }

    const $body = $('#import-pn-result-body');
    const success = res.success;
    const insertedCount = res.inserted_count ?? res.row_count ?? 0;
    const rowCount = res.row_count ?? 0;
    const errors = res.errors ?? [];

    let html = '';
    if (success) {
        html += `<div class="alert alert-success">
            <i class="fas fa-check-circle me-2"></i><strong>${res.message}</strong>
        </div>`;
        if (insertedCount < rowCount) {
            html += `<div class="alert alert-warning mt-2">
                <i class="fas fa-info-circle me-2"></i>
                Đã import <strong>${insertedCount}/${rowCount}</strong> dòng. Bỏ qua <strong>${rowCount - insertedCount}</strong> dòng bị lỗi.
            </div>`;
        }
    } else {
        html += `<div class="alert alert-danger">
            <i class="fas fa-exclamation-triangle me-2"></i><strong>${res.message}</strong>
        </div>`;
    }

    if (errors.length > 0) {
        html += '<h6 class="mt-3">Chi tiết các dòng bị lỗi:</h6>';
        html += '<div class="table-responsive"><table class="table table-sm table-bordered">';
        html += '<thead><tr><th style="width:80px">Dòng</th><th>Lỗi</th></tr></thead><tbody>';
        errors.slice(0, 50).forEach(err => {
            html += `<tr><td class="text-danger">${err.replace(/^Dong (\d+): /, '$1')}</td><td>${err.replace(/^Dong \d+: /, '')}</td></tr>`;
        });
        html += '</tbody></table></div>';
        if (errors.length > 50) {
            html += `<p class="text-muted small">... và ${errors.length - 50} lỗi khác</p>`;
        }
    }

    $body.html(html);
    new bootstrap.Modal($modal[0]).show();
}

// ─── IMPORT/EXPORT EXCEL - PHIẾU XUẤT ─────────────────────────────────
let importPxFile = null;

// Nút Export Excel - Phiếu xuất: mở modal
$('#px-btn-export').click(function () {
    const tu = $('#px-filter-tu').val();
    const den = $('#px-filter-den').val();
    if (tu) $('#xuat-px-tu').val(tu);
    if (den) $('#xuat-px-den').val(den);
    new bootstrap.Modal(document.getElementById('modal-xuat-px')).show();
});

$('#form-xuat-px').submit(function (e) {
    e.preventDefault();
    const loai = $('#xuat-px-loai').val();
    const tu = $('#xuat-px-tu').val();
    const den = $('#xuat-px-den').val();
    if (tu && den && tu > den) {
        hienBao('warning', 'Từ ngày phải nhỏ hơn hoặc bằng đến ngày.');
        return;
    }
    const params = new URLSearchParams();
    if (loai) params.set('loai_xuat', loai);
    if (tu) params.set('tu_ngay', tu);
    if (den) params.set('den_ngay', den);
    let url = '/admin/api/phieu-xuat/export';
    const qs = params.toString();
    if (qs) url += '?' + qs;
    bootstrap.Modal.getInstance(document.getElementById('modal-xuat-px')).hide();
    window.location.href = url;
});

// Nút Import Excel - Phiếu xuất
$('#px-btn-import').click(function () {
    importPxFile = null;
    $('#form-import-px')[0].reset();
    $('#import-px-preview').addClass('d-none');
    $('#import-px-submit').prop('disabled', true);
    // Load NCC dropdown
    $.get('/admin/api/nha-cung-cap/dropdown', res => {
        if (res.success) {
            const opts = res.data.map(n => `<option value="${n.id}">${n.ten_nha_cung_cap}</option>`).join('');
            $('#import-px-ncc').html('<option value="">-- Chọn NCC --</option>' + opts);
        }
    });
    new bootstrap.Modal(document.getElementById('modal-import-px')).show();
});

// Download template
$('#import-px-download-template').click(function () {
    window.open('/admin/api/phieu-xuat/download-template', '_blank');
});

// File input change
$('#import-px-file').change(function () {
    const file = this.files[0];
    if (file) {
        importPxFile = file;
        $('#import-px-filename').text(file.name);
        $('#import-px-preview').removeClass('d-none');
        $('#import-px-submit').prop('disabled', false);
    }
});

// Drop zone
$('#drop-zone-px').click(function () { $('#import-px-file').click(); });
$('#drop-zone-px').on('dragover', function (e) { e.preventDefault(); $(this).css('background-color', '#fff5f5'); });
$('#drop-zone-px').on('dragleave', function () { $(this).css('background-color', '#f8f9fa'); });
$('#drop-zone-px').on('drop', function (e) {
    e.preventDefault();
    $(this).css('background-color', '#f8f9fa');
    const file = e.originalEvent.dataTransfer.files[0];
    if (file && (file.name.endsWith('.xlsx') || file.name.endsWith('.xls') || file.name.endsWith('.csv'))) {
        importPxFile = file;
        $('#import-px-file')[0].files = e.originalEvent.dataTransfer.files;
        $('#import-px-filename').text(file.name);
        $('#import-px-preview').removeClass('d-none');
        $('#import-px-submit').prop('disabled', false);
    } else {
        hienBao('danger', 'Vui lòng chọn file Excel (.xlsx, .xls, .csv)');
    }
});

// Remove file
$('#import-px-remove').click(function () {
    importPxFile = null;
    $('#import-px-file').val('');
    $('#import-px-preview').addClass('d-none');
    $('#import-px-submit').prop('disabled', true);
});

// Submit import
$('#form-import-px').submit(function (e) {
    e.preventDefault();
    if (!importPxFile) { hienBao('danger', 'Vui lòng chọn file Excel.'); return; }

    const formData = new FormData();
    formData.append('file', importPxFile);
    formData.append('loai_xuat', $('#import-px-loai').val());
    formData.append('id_nha_cung_cap', $('#import-px-ncc').val() || '');
    formData.append('ly_do', $('#import-px-ly-do').val() || '');
    formData.append('ghi_chu', $('#import-px-ghi-chu').val() || '');

    $('#import-px-submit').prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Đang import...');

    $.ajax({
        url: '/admin/api/phieu-xuat/import',
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
        data: formData,
        processData: false,
        contentType: false,
        success: function (res) {
            if (res.success) {
                bootstrap.Modal.getInstance(document.getElementById('modal-import-px')).hide();

                if (res.errors && res.errors.length > 0) {
                    showImportPxResultModal(res);
                } else {
                    hienBao('success', res.message);
                }
                loadPhieuXuat(1);
                importPxFile = null;
            } else {
                showImportPxResultModal(res);
            }
        },
        error: function (x) {
            hienBao('danger', x.responseJSON?.message || 'Import thất bại.');
        },
        complete: function () {
            $('#import-px-submit').prop('disabled', false).html('<i class="fas fa-upload me-1"></i>Import');
        }
    });
});

// Hien thi modal ket qua import PX
function showImportPxResultModal(res) {
    let $modal = $('#modal-import-px-result');
    if ($modal.length === 0) {
        const html = `
            <div class="modal fade" id="modal-import-px-result" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Kết quả Import Phiếu Xuất</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body" id="import-px-result-body"></div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                        </div>
                    </div>
                </div>
            </div>
        `;
        $('body').append(html);
        $modal = $('#modal-import-px-result');
    }

    const $body = $('#import-px-result-body');
    const success = res.success;
    const insertedCount = res.inserted_count ?? res.row_count ?? 0;
    const rowCount = res.row_count ?? 0;
    const errors = res.errors ?? [];

    let html = '';
    if (success) {
        html += `<div class="alert alert-success">
            <i class="fas fa-check-circle me-2"></i><strong>${res.message}</strong>
        </div>`;
        if (insertedCount < rowCount) {
            html += `<div class="alert alert-warning mt-2">
                <i class="fas fa-info-circle me-2"></i>
                Đã import <strong>${insertedCount}/${rowCount}</strong> dòng. Bỏ qua <strong>${rowCount - insertedCount}</strong> dòng bị lỗi.
            </div>`;
        }
    } else {
        html += `<div class="alert alert-danger">
            <i class="fas fa-exclamation-triangle me-2"></i><strong>${res.message}</strong>
        </div>`;
    }

    if (errors.length > 0) {
        html += '<h6 class="mt-3">Chi tiết các dòng bị lỗi:</h6>';
        html += '<div class="table-responsive"><table class="table table-sm table-bordered">';
        html += '<thead><tr><th style="width:80px">Dòng</th><th>Lỗi</th></tr></thead><tbody>';
        errors.slice(0, 50).forEach(err => {
            html += `<tr><td class="text-danger">${err.replace(/^Dong (\d+): /, '$1')}</td><td>${err.replace(/^Dong \d+: /, '')}</td></tr>`;
        });
        html += '</tbody></table></div>';
        if (errors.length > 50) {
            html += `<p class="text-muted small">... và ${errors.length - 50} lỗi khác</p>`;
        }
    }

    $body.html(html);
    new bootstrap.Modal($modal[0]).show();
}

// ─── ALERTS ──────────────────────────────────────────────
function hienBao(type, message) {
    $('#kh-alert-container').html(`<div class="alert alert-${type} alert-dismissible fade show" role="alert">${message}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>`);
    setTimeout(() => $('.alert').fadeOut(), 4000);
}

// ─── LOAD ALL PRODUCTS ────────────────────────────────────
function loadSanPhamAll() {
    console.log('[KHO-HANG] loadSanPhamAll called');
    $.get('/admin/api/san-pham', { q: '' }, res => {
        console.log('[KHO-HANG] loadSanPhamAll response:', res);
        if (!res.data) { sanPhamAll = []; return; }
        sanPhamAll = [];
        // New API returns nested by product with bien_the[] inside.
        // Flatten to one option per bien_the for dropdown selects.
        res.data.forEach(product => {
            (product.bien_the || []).forEach(bt => {
                sanPhamAll.push({
                    id: bt.id,
                    product_id: product.product_id,
                    ten_san_pham: product.ten_san_pham,
                    ten_bien_the: bt.ten_bien_the,
                    ma_vach: bt.ma_vach,
                    thuoc_tinh_labels: bt.thuoc_tinh_labels || [],
                    chi_tiet_lo_hang_ton_sum_so_luong_ton: bt.chi_tiet_lo_hang_ton || 0,
                });
            });
        });
    }).fail(function(xhr, status, error) {
        console.error('[KHO-HANG] loadSanPhamAll FAIL:', status, error, xhr.responseText);
    });
}
