// ============================================================
// KHO HANG: All warehouse management JS
// ============================================================

let sanPhamAll = [];
let loIdx = 0;
let tkPage = 1, lhPage = 1, pnPage = 1, pxPage = 1, nccPage = 1;

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

function pad2(n) {
    return String(n).padStart(2, '0');
}

function parseDateValue(value) {
    if (value === null || value === undefined || value === '') return null;
    const raw = String(value).trim();
    if (!raw) return null;

    const dateOnly = raw.match(/^(\d{4})-(\d{2})-(\d{2})$/);
    if (dateOnly) {
        const d = new Date(Number(dateOnly[1]), Number(dateOnly[2]) - 1, Number(dateOnly[3]));
        return Number.isNaN(d.getTime()) ? null : d;
    }

    const d = new Date(raw);
    return Number.isNaN(d.getTime()) ? null : d;
}

function formatDateDisplay(value, fallback = '--') {
    const d = parseDateValue(value);
    if (!d) return fallback;
    return `${pad2(d.getDate())}/${pad2(d.getMonth() + 1)}/${d.getFullYear()}`;
}

function formatDateInput(value) {
    const d = parseDateValue(value);
    if (!d) return '';
    return `${d.getFullYear()}-${pad2(d.getMonth() + 1)}-${pad2(d.getDate())}`;
}

function daysUntil(value) {
    const d = parseDateValue(value);
    if (!d) return null;
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    d.setHours(0, 0, 0, 0);
    return Math.ceil((d - today) / 86400000);
}

// ─── INIT ───────────────────────────────────────────────
$(function () {
    console.log('[KHO-HANG] Init start');
    try { loadStats(); } catch(e) { console.error('[KHO-HANG] loadStats error:', e); hienBao('danger', 'Lỗi loadStats: ' + e.message); }
    // loadTonKho() không gọi nữa: tab Tồn kho đã được render server-side (Blade)
    // bằng KhoHangController với paginate($sanPhams) và Alpine.js expand row.
    try { loadLoHang(); } catch(e) { console.error('[KHO-HANG] loadLoHang error:', e); hienBao('danger', 'Lỗi loadLoHang: ' + e.message); }
    try { loadPhieuNhap(); } catch(e) { console.error('[KHO-HANG] loadPhieuNhap error:', e); hienBao('danger', 'Lỗi loadPhieuNhap: ' + e.message); }
    try { loadPhieuXuat(); } catch(e) { console.error('[KHO-HANG] loadPhieuXuat error:', e); hienBao('danger', 'Lỗi loadPhieuXuat: ' + e.message); }
    try { loadNhaCungCap(1); } catch(e) { console.error('[KHO-HANG] loadNhaCungCap error:', e); hienBao('danger', 'Lỗi loadNhaCungCap: ' + e.message); }
    try { loadNccDropdown(); } catch(e) { console.error('[KHO-HANG] loadNccDropdown error:', e); hienBao('danger', 'Lỗi loadNccDropdown: ' + e.message); }
    try { loadSanPhamAll(); } catch(e) { console.error('[KHO-HANG] loadSanPhamAll error:', e); hienBao('danger', 'Lỗi loadSanPhamAll: ' + e.message); }
    try { initKhoFilterPanel(); } catch(e) { console.error('[KHO-HANG] initKhoFilterPanel error:', e); }
    console.log('[KHO-HANG] Init end');

    $('#modal-tao-lo').on('hidden.bs.modal', function () {
        loIdx = 0;
        $('#form-tao-lo')[0].reset();
        $('#lo-ds-sp').html('');
    });

    // Tab events
    $('#khoHangTabs button[data-bs-toggle="tab"]').on('shown.bs.tab', function () {
        const id = $(this).attr('id');
        // Tab Tồn kho đã render server-side, không cần load lại qua AJAX.
        if (id === 'tab-lo-hang') loadLoHang(lhPage);
        if (id === 'tab-nhap-hang') loadPhieuNhap(pnPage);
        if (id === 'tab-xuat-hang') loadPhieuXuat(pxPage);
        if (id === 'tab-canh-bao') loadCanhBao();
        if (id === 'tab-ncc') loadNhaCungCap(nccPage);
    });

    // Filter buttons (Tồn kho dùng GET form submit, không qua JS)
    $('#lh-btn-loc').click(() => loadLoHang(1));
    $('#pn-btn-loc').click(() => loadPhieuNhap(1));
    $('#px-btn-loc').click(() => loadPhieuXuat(1));

    // Enter key filters
    $('#lh-filter-ma').on('keypress', e => { if (e.which === 13) loadLoHang(1); });
    $('#px-filter-ma-lo').on('keypress', e => { if (e.which === 13) loadPhieuXuat(1); });

    // Submit forms
    // Lưu ý: #form-tao-px đã được chuyển sang trang riêng (file: phieu-xuat-create.js),
    // không còn submit qua kho-hang.js nữa.
    $('#lo-btn-them-sp').click(() => addLoRow());
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

// ============================================================
// KHỐI LỌC MỚI — đồng bộ trang Sản phẩm
// ============================================================

// Cache toàn bộ TonKho server trả về để filter client-side
let tkAllItems = [];
let tkCurrentPage = 1;
const TK_PER_PAGE = 10;

/**
 * Load dropdown Danh mục cho bộ lọc Tồn kho.
 * Tận dụng API /admin/api/san-pham (đã include danh_muc_list)
 * value = ten_danh_muc để so sánh với field `danh_muc` trong data ton-kho-tong.
 */
function loadDanhMucDropdownForTk() {
    const $sel = $('#tk-filter-danh-muc');
    if (!$sel.length) return;
    const params = new URLSearchParams(window.location.search);
    const currentVal = params.get('danh_muc_id') || '';
    $.get('/admin/api/san-pham', { q: '' }, res => {
        const list = (res && res.danh_muc_list) || [];
        if (!Array.isArray(list) || list.length === 0) {
            console.warn('[KHO-HANG] danh_muc_list not found in /admin/api/san-pham response');
            return;
        }
        const opts = list.map(dm =>
            `<option value="${dm.id}">${escapeHtml(dm.ten_danh_muc || '')}</option>`
        ).join('');
        $sel.html('<option value="">Tất cả danh mục</option>' + opts).val(currentVal);
    }).fail(function() {
        console.warn('[KHO-HANG] loadDanhMucDropdownForTk failed');
    });
}

/**
 * Dropdown Nhà cung cấp cho bộ lọc Tồn kho
 * - value = ten_nha_cung_cap (so sánh với mảng nha_cung_caps trả về từ API)
 */
function loadNccDropdownForTk() {
    const $sel = $('#tk-filter-ncc');
    if (!$sel.length) return;
    const currentVal = new URLSearchParams(window.location.search).get('nha_cung_cap') || '';
    $.get('/admin/api/nha-cung-cap/dropdown', res => {
        if (!res.success || !Array.isArray(res.data)) return;
        const opts = res.data.map(n =>
            `<option value="${escapeAttr(n.ten_nha_cung_cap || '')}">${escapeHtml(n.ten_nha_cung_cap || '')}</option>`
        ).join('');
        $sel.html('<option value="">Tất cả nhà cung cấp</option>' + opts).val(currentVal);
    }).fail(function() {
        console.warn('[KHO-HANG] loadNccDropdownForTk failed');
    });
}

/**
 * Đếm số filter đang active để hiển thị badge + auto-open panel khi có filter
 */
function updateKhoFilterCount() {
    const params = new URLSearchParams(window.location.search);
    let count = 0;
    if (params.get('keyword')) count++;
    if (params.get('danh_muc')) count++;
    if (params.get('trang_thai_ton')) count++;
    if (params.get('nha_cung_cap')) count++;
    const $badge = $('.kho-filter-count');
    if (count > 0) {
        $badge.text(count).show();
    } else {
        $badge.hide();
    }
    return count;
}

function initKhoFilterPanel() {
    loadDanhMucDropdownForTk();
    loadNccDropdownForTk();
    updateKhoFilterCount();

    // Re-count khi thay đổi filter
    $('#tk-filter-danh-muc, #tk-filter-status, #tk-filter-ncc').on('change', function () {
        updateKhoFilterCount();
    });

    // Reset filter → quay về URL gốc
    $('#tk-btn-reset').on('click', function () {
        window.location.href = window.location.pathname;
    });

    // Auto-open panel khi Alpine.js đã init (nếu có filter đang active)
    const tryAutoOpenFilter = () => {
        const card = document.querySelector('.search-filter-card[x-data]');
        if (!card || !window.Alpine) return;
        try {
            const data = window.Alpine.$data(card);
            if (updateKhoFilterCount() > 0) {
                data.showFilter = true;
            }
        } catch (e) { /* ignore */ }
    };
    if (window.Alpine) {
        setTimeout(tryAutoOpenFilter, 0);
    } else {
        document.addEventListener('alpine:init', tryAutoOpenFilter, { once: true });
    }
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
        // #px-ncc đã chuyển sang trang tạo phiếu xuất riêng (file: phieu-xuat-create.js)
        $('#lo-ncc').html('<option value="">-- Chon NCC --</option>' + opts);
        $('#lh-filter-ncc').html('<option value="">Tất cả NCC</option>' + opts);
    });
}

// ─── PRODUCT SEARCH (PX Modal) ─────────────────────────────────────
// Lưu ý: Logic search & chọn sản phẩm cho phiếu xuất đã được chuyển sang
// file riêng: public/js/admin/phieu-xuat-create.js (trang tạo riêng, không modal).

function loadTonKho(page = 1) {
    tkPage = page;
    const q = $('#tk-filter-ten').val() || '';
    const status = $('#tk-filter-status').val() || '';
    const danhMuc = $('#tk-filter-danh-muc').val() || '';
    const ncc = $('#tk-filter-ncc').val() || '';

    $('#tk-bang').html(
        '<tr><td colspan="6" class="text-center text-muted py-4">' +
        '<i class="fas fa-spinner fa-spin me-1"></i> Đang tải...</td></tr>'
    );

    $.get('/admin/api/lo-hang/ton-kho-tong')
    .done(res => {
        if (!res.success) {
            $('#tk-bang').html(
                '<tr><td colspan="6" class="text-center text-danger py-4">' +
                'Lỗi tải dữ liệu tồn kho.</td></tr>'
            );
            return;
        }

        // Lưu cache toàn bộ để filter client-side (chỉ áp dụng với danh mục + NCC;
        // status tính ngay từ dữ liệu gốc; search theo tên/mã vạch)
        tkAllItems = Array.isArray(res.data) ? res.data : [];

        // ---- LỌC ----
        let items = tkAllItems.slice();

        if (q) {
            const qLower = q.toLowerCase();
            items = items.filter(i => {
                const name = (i.ten_san_pham || i.product?.ten_san_pham || '') + '';
                const code = (i.ma_vach || '') + '';
                return name.toLowerCase().includes(qLower) ||
                       code.toLowerCase().includes(qLower);
            });
        }

        if (status === 'het-hang') {
            items = items.filter(i => {
                const qty = i.tong_ton ?? i.product?.tong_ton ?? 0;
                return !qty || qty == 0;
            });
        } else if (status === 'duoi-dinh-muc') {
            items = items.filter(i => {
                const qty = i.tong_ton ?? i.product?.tong_ton ?? 0;
                const min = i.dinh_muc_toi_thieu ?? i.product?.dinh_muc_toi_thieu ?? 0;
                return qty > 0 && qty <= min;
            });
        } else if (status === 'binh-thuong') {
            items = items.filter(i => {
                const qty = i.tong_ton ?? i.product?.tong_ton ?? 0;
                const min = i.dinh_muc_toi_thieu ?? i.product?.dinh_muc_toi_thieu ?? 0;
                return qty > min;
            });
        }

        if (danhMuc) {
            items = items.filter(i =>
                (i.danh_muc || i.product?.danhMuc?.ten_danh_muc) == danhMuc
            );
        }

        if (ncc) {
            items = items.filter(i => {
                const list = i.nha_cung_caps || [];
                return Array.isArray(list) && list.includes(ncc);
            });
        }

        // ---- PHÂN TRANG (client-side) ----
        const total = items.length;
        const totalPages = Math.max(1, Math.ceil(total / TK_PER_PAGE));
        if (page < 1) page = 1;
        if (page > totalPages) page = totalPages;
        tkCurrentPage = page;
        const paged = items.slice((page - 1) * TK_PER_PAGE, page * TK_PER_PAGE);

        if (!paged.length) {
            $('#tk-bang').html(
                '<tr><td colspan="6" class="text-center text-muted py-4">' +
                '<i class="fas fa-inbox me-1"></i> Không có sản phẩm nào.</td></tr>'
            );
            $('#tk-thong-tin').text('Hiển thị 0 / 0 sản phẩm');
            $('#tk-phan-trang').html('');
            return;
        }

        const html = paged.map(sp => buildTonKhoRow(sp)).join('');
        $('#tk-bang').html(html);
        $('#tk-thong-tin').text(`Hiển thị ${paged.length} / ${total} sản phẩm`);
        renderTkPagination(page, totalPages);
    })
    .fail(function(xhr, status, error) {
        console.error('loadTonKho error:', status, error, xhr.responseText);
        $('#tk-bang').html(
            `<tr><td colspan="6" class="text-center text-danger py-4">` +
            `Lỗi: ${escapeHtml(error || status)}<br>` +
            `<small class="text-muted">${escapeHtml(xhr.responseText || '')}</small></td></tr>`
        );
    });
}

function buildTonKhoRow(sp) {
    const tongTon = sp.tong_ton ?? sp.product?.tong_ton ?? 0;
    const dinhMuc = sp.dinh_muc_toi_thieu ?? sp.product?.dinh_muc_toi_thieu ?? 0;

    // Cảnh báo tinh tế
    let warnClass = '';
    let tonBadgeHtml = '';
    if (tongTon === 0) {
        tonBadgeHtml = '<span class="status-pill muted"><i class="fas fa-times-circle"></i> Hết hàng</span>';
    } else if (tongTon <= dinhMuc) {
        warnClass = 'is-warn';   // đỏ đậm
        tonBadgeHtml = '<span class="status-pill warn"><i class="fas fa-exclamation-circle"></i> Dưới định mức</span>';
    } else {
        tonBadgeHtml = '<span class="status-pill ok"><i class="fas fa-check-circle"></i> Bình thường</span>';
    }

    const productName = sp.ten_san_pham || sp.product?.ten_san_pham || 'Sản phẩm';
    const variantName = sp.ten_bien_the || '';
    const displayName = (variantName && variantName !== productName)
        ? `${productName} - ${variantName}`
        : productName;
    const maVach = sp.ma_vach || sp.id;

    return `
    <tr class="clickable-row" data-id="${sp.id}" data-variant-id="${sp.id}" data-ton="${tongTon}" data-dinh-muc="${dinhMuc}">
        <td class="text-center toggle-expand-cell">
            <i class="fas fa-chevron-right toggle-icon"></i>
        </td>
        <td class="product-cell">
            <span class="product-name">${escapeHtml(displayName)}</span>
            <span class="product-meta">
                <i class="fas fa-barcode"></i>MH: ${escapeHtml(maVach)}
            </span>
        </td>
        <td class="num-cell ${warnClass}">
            <span class="num-value">${Number(tongTon).toLocaleString('vi-VN')}</span>
        </td>
        <td class="num-cell">
            <span class="num-value">${Number(dinhMuc).toLocaleString('vi-VN')}</span>
        </td>
        <td class="text-center">${tonBadgeHtml}</td>
        <td class="text-center">
            <button class="btn btn-sm btn-outline-secondary btn-xem-ton"
                    data-id="${sp.id}" data-variant-id="${sp.id}"
                    title="Xem chi tiết">
                <i class="fas fa-eye"></i>
            </button>
        </td>
    </tr>
    <tr class="kho-expand-row" id="kho-expand-${sp.id}" style="display:none;">
        <td colspan="6">
            <div id="kho-expand-content-${sp.id}" class="kho-expand-panel">
                <div class="text-center text-muted py-3" style="grid-column: 1 / -1;">
                    <i class="fas fa-spinner fa-spin me-1"></i>Đang tải chi tiết...
                </div>
            </div>
        </td>
    </tr>`;
}

function renderTkPagination(current, total) {
    if (total <= 1) { $('#tk-phan-trang').html(''); return; }
    let html = '<ul class="pagination mb-0 justify-content-end">';
    const prevDisabled = current === 1 ? ' disabled' : '';
    html += `<li class="page-item${prevDisabled}">
                <a class="page-link" href="javascript:;" onclick="loadTonKho(${current - 1})">&laquo;</a>
             </li>`;
    const max = 5;
    let start = Math.max(1, current - Math.floor(max / 2));
    let end = Math.min(total, start + max - 1);
    if (end - start < max - 1) start = Math.max(1, end - max + 1);
    if (start > 1) {
        html += `<li class="page-item"><a class="page-link" href="javascript:;" onclick="loadTonKho(1)">1</a></li>`;
        if (start > 2) html += `<li class="page-item disabled"><span class="page-link">…</span></li>`;
    }
    for (let i = start; i <= end; i++) {
        html += `<li class="page-item ${i === current ? 'active' : ''}">
                    <a class="page-link" href="javascript:;" onclick="loadTonKho(${i})">${i}</a>
                 </li>`;
    }
    if (end < total) {
        if (end < total - 1) html += `<li class="page-item disabled"><span class="page-link">…</span></li>`;
        html += `<li class="page-item"><a class="page-link" href="javascript:;" onclick="loadTonKho(${total})">${total}</a></li>`;
    }
    const nextDisabled = current === total ? ' disabled' : '';
    html += `<li class="page-item${nextDisabled}">
                <a class="page-link" href="javascript:;" onclick="loadTonKho(${current + 1})">&raquo;</a>
             </li>`;
    html += '</ul>';
    $('#tk-phan-trang').html(html);
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
            const ncc = item.nha_cung_cap?.ten_nha_cung_cap || '<span class="text-muted fst-italic">(Không có)</span>';
            const ngay = formatDateDisplay(item.ngay_nhap);
            const maLo = item.ma_lo || 'L-' + item.id;
            let hsds = (item.chi_tiet_lo_hang || [])
                .map(c => formatDateInput(c.han_su_dung))
                .filter(d => d)
                .sort();
            const hsdMin = hsds[0] || null;
            let hsdBadge = '', hsdClass = '';
            if (hsdMin) {
                const diff = daysUntil(hsdMin);
                const hsdText = formatDateDisplay(hsdMin);
                if (diff < 0) { hsdBadge = `<span class="badge bg-danger badge-hsd">Hết HSD</span>`; hsdClass = 'table-danger'; }
                else if (diff <= 30) { hsdBadge = `<span class="badge bg-warning text-dark badge-hsd">${hsdText} (${diff} ngày)</span>`; hsdClass = 'table-warning'; }
                else { hsdBadge = `<span class="badge bg-success badge-hsd">${hsdText}</span>`; }
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
                    <a href="/admin/kho-hang/lo-hang/${item.id}" class="btn btn-sm btn-outline-primary" title="Xem chi tiết"><i class="fas fa-eye"></i></a>
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
            const ngay = formatDateDisplay(item.created_at);
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
    const maLo = $('#px-filter-ma-lo').val();
    const params = new URLSearchParams({ 
        page, 
        loai_xuat: loai || '', 
        tu_ngay: tu || '', 
        den_ngay: den || '',
        ma_lo: maLo || ''
    });
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
            const ngay = formatDateDisplay(item.created_at);
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
                    <a href="/admin/kho-hang/phieu-xuat/${item.id}" class="btn btn-sm btn-outline-primary" title="Xem chi tiết phiếu xuất">
                        <i class="fas fa-eye"></i>
                    </a>
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
            const diff = daysUntil(hsdRaw);
            const hsdText = formatDateDisplay(hsdRaw);
            return `
            <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                <div>
                    <div class="fw-semibold">${product.ten_san_pham || variant.ten_bien_the || item.id_san_pham || item.variant_id || 'Không xác định'}</div>
                    <div class="small text-muted">
                        Lô: ${lo.ma_lo || (item.id_lo_hang ? 'L-' + item.id_lo_hang : '--')} | Tồn: ${item.so_luong_ton} | HSD: ${hsdText}
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

// ============================================================
// DÒNG MỞ RỘNG — Tab Tồn kho (Mini-Dashboard)
// Từng sản phẩm cha được bọc trong <tbody x-data="{ expanded: false }">
// của Alpine.js. Click row cha do Blade xử lý qua @click="expanded = !expanded".
// KHÔNG bind .clickable-row ở đây để tránh xung đột với Alpine.
// ============================================================
// (handler cũ đã được vô hiệu hoá)

/**
 * Render bảng Lô hàng (FEFO) — nested table gọn gàng
 */
function buildFefoTable(chiTiet) {
    if (!chiTiet.length) {
        return `
            <div class="kho-fefo">
                <h6 class="kho-fefo-title">Chi tiết theo lô (FEFO)</h6>
                <div class="kho-fefo-empty">Không có lô hàng tồn kho.</div>
            </div>`;
    }

    const rowsHtml = chiTiet.map(ct => {
        const lo = ct.lo_hang || {};
        const ncc = lo.nha_cung_cap?.ten_nha_cung_cap || '--';
        const hsdRaw = ct.han_su_dung || '';
        const hsd = formatDateDisplay(hsdRaw);
        const diff = daysUntil(hsdRaw);

        let hsdClass = 'hsd-ok';
        let hsdLabel = hsd || '--';
        if (diff === null) {
            hsdClass = 'hsd-ok';
            hsdLabel = hsd || '--';
        } else if (diff < 0) {
            hsdClass = 'hsd-danger';
            hsdLabel = `${hsd} (Hết HSD)`;
        } else if (diff <= 30) {
            hsdClass = 'hsd-warn';
            hsdLabel = `${hsd} (còn ${diff} ngày)`;
        }

        const maLo = lo.ma_lo || ('L-' + lo.id);
        const ton = parseInt(ct.so_luong_ton) || 0;
        const slNhap = parseInt(ct.so_luong_nhap) || 0;

        return `
        <tr>
            <td><span class="lo-badge-mini">${escapeHtml(maLo)}</span></td>
            <td>${escapeHtml(ncc)}</td>
            <td class="text-right"><span class="${hsdClass}">${escapeHtml(hsdLabel)}</span></td>
            <td class="text-right">
                <span class="fw-semibold text-primary">${ton.toLocaleString('vi-VN')}</span>
                <span class="text-muted small ms-1">/ ${slNhap.toLocaleString('vi-VN')}</span>
            </td>
        </tr>`;
    }).join('');

    return `
        <div class="kho-fefo">
            <h6 class="kho-fefo-title">
                <i class="fas fa-layer-group me-1"></i>Chi tiết theo lô (FEFO)
            </h6>
            <table>
                <thead>
                    <tr>
                        <th>Mã lô</th>
                        <th>Nhà cung cấp</th>
                        <th class="text-right">HSD</th>
                        <th class="text-right">Tồn / Nhập</th>
                    </tr>
                </thead>
                <tbody>${rowsHtml}</tbody>
            </table>
        </div>
    `;
}

// .btn-xem-ton không còn dùng (tab Tồn kho chuyển sang Alpine + Mini-Dashboard).
// Button này nếu tồn tại sẽ không có tác dụng để tránh xung đột với Alpine.
// (handler cũ đã được vô hiệu hoá)

// Xem chi tiết Lô hàng (FEFO) của một biến thể — mở modal riêng
$(document).on('click', '.btn-xem-lo-bt', function (e) {
    e.stopPropagation();
    const $btn = $(this);
    const variantId = $btn.attr('data-variant-id');
    const spName = $btn.attr('data-sp-name') || '';
    const btName = $btn.attr('data-bt-name') || '';

    const $modal = $('#modal-fefo-bt');
    const $body = $('#fefo-bt-body');
    const $title = $('#fefo-bt-title');
    $title.text(`${spName} — ${btName}`);
    $body.html('<div class="text-center text-muted py-4"><i class="fas fa-spinner fa-spin me-1"></i>Đang tải chi tiết lô hàng...</div>');

    const modal = bootstrap.Modal.getOrCreateInstance($modal[0]);
    modal.show();

    $.get('/admin/api/lo-hang/ton-kho?variant_id=' + variantId, res => {
        if (!res.success || !res.data) {
            $body.html('<div class="text-danger small">Lỗi tải dữ liệu lô hàng.</div>');
            return;
        }
        const data = res.data || {};
        const chiTiet = data.chi_tiet || [];
        const variantUnits = data.variant_units || [];
        const tongTon = data.tong_ton || 0;

        if (!chiTiet.length && !variantUnits.length) {
            $body.html('<div class="text-muted text-center py-4">Biến thể này hiện không có lô hàng tồn kho.</div>');
            return;
        }

        const summaryHtml = `
            <div class="kho-summary mb-3">
                <div class="kho-summary-title"><i class="fas fa-info-circle me-1"></i>Tổng quan</div>
                <div class="kho-info-row">
                    <span class="kho-info-label">Tổng tồn (đơn vị cơ bản)</span>
                    <span class="kho-info-value">${Number(tongTon).toLocaleString('vi-VN')}</span>
                </div>
                ${variantUnits.length ? `
                <div class="kho-info-row">
                    <span class="kho-info-label">Quy đổi đơn vị</span>
                    <span class="kho-info-value">${variantUnits.map(u =>
                        `${u.so_luong} ${escapeHtml(u.ten_don_vi)} = ${u.so_luong_chuan} ${escapeHtml(u.ten_don_vi_chuan || '')}`
                    ).join(' · ')}</span>
                </div>` : ''}
            </div>
        `;

        $body.html(summaryHtml + buildFefoTable(chiTiet));
    }).fail(function (xhr) {
        $body.html(`<div class="text-danger small">Lỗi: ${xhr.status} ${xhr.statusText}</div>`);
    });
});

// Removed: Old modal-based detail view - now using dedicated page
// Users click the link in loadLoHang() which redirects to /admin/kho-hang/lo-hang/{id}

$(document).on('click', '.btn-xoa-lo', function () {
    if (!confirm('Xóa lô hàng này?')) return;
    const id = $(this).data('id');
    $.ajax({ url: '/admin/api/lo-hang/' + id, method: 'DELETE', success: res => { hienBao('success', res.message); loadLoHang(lhPage); loadStats(); }, error: x => hienBao('danger', x.responseJSON?.message || 'Lỗi.') });
});

$(document).on('click', '.btn-xem-pn', function () {
    const id = $(this).data('id');
    window.location.href = '/admin/kho-hang/phieu-nhap/' + id;
});

$(document).on('click', '.btn-xoa-pn', function () {
    if (!confirm('Xóa phiếu nhập này?')) return;
    const id = $(this).data('id');
    $.ajax({ url: '/admin/api/phieu-nhap/' + id, method: 'DELETE', success: res => { hienBao('success', res.message); loadPhieuNhap(pnPage); loadStats(); }, error: x => hienBao('danger', x.responseJSON?.message || 'Lỗi.') });
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

function addLoRow(id, sl, gia, hsd) {
    const idx = loIdx++;
    const opts = spOptions(id);
    $('#lo-ds-sp').append(`<tr>
        <td><select class="form-select form-select-sm" name="chi_tiet[${idx}][variant_id]">${opts || '<option value="">-- Chon --</option>'}</select></td>
        <td><input type="number" class="form-control form-control-sm" name="chi_tiet[${idx}][so_luong_nhap]" value="${sl || 1}" min="1"></td>
        <td><input type="text" class="form-control form-control-sm money-input" name="chi_tiet[${idx}][gia_nhap]" value="${gia ? Number(gia).toLocaleString('vi-VN') : ''}" inputmode="numeric"></td>
        <td><input type="date" class="form-control form-control-sm" name="chi_tiet[${idx}][han_su_dung]" value="${hsd || ''}"></td>
        <td><button type="button" class="btn btn-sm btn-outline-danger" onclick="removeLoRow(this)"><i class="fas fa-times"></i></button></td>
    </tr>`);
}

function removeLoRow(btn) { if ($('#lo-ds-sp tr').length > 1) $(btn).closest('tr').remove(); }

// Lưu ý: Các hàm addPxRow / buildPxLabel / removePxRow / updatePxTongSl /
// loadLoOptions / updateTonCell và handler .px-sp-select / .px-lo-select
// đã được chuyển sang file: public/js/admin/phieu-xuat-create.js
// (trang tạo phiếu xuất riêng, không dùng modal).

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

// Hàm submitPhieuNhap() đã được chuyển sang file riêng: public/js/admin/phieu-nhap-create.js
// (tính năng tạo phiếu nhập giờ là trang riêng thay vì modal trong kho-hang).

// submitPhieuXuat() đã được chuyển sang file riêng: public/js/admin/phieu-xuat-create.js
// (tính năng tạo phiếu xuất giờ là trang riêng thay vì modal trong kho-hang).

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
            loadLoHang(1); loadStats();
            // Reload trang nếu đang ở tab Tồn kho (server-side render)
            if ($('#content-ton-kho').hasClass('active')) window.location.reload();
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

function gotk(p) { /* Tab Tồn kho đã chuyển sang server-side, không cần AJAX */ }
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
    const fmt = (d) => `${d.getFullYear()}-${pad2(d.getMonth() + 1)}-${pad2(d.getDate())}`;
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

// ─── IMPORT/EXPORT EXCEL - PHIẾU XUẤT ─────────────────────────────────
let importPxFile = null;

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
