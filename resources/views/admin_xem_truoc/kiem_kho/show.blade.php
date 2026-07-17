@extends('admin_xem_truoc.layouts.admin')

@section('title', 'Chi tiết phiếu kiểm kho - SmartMart')

@section('styles')
<style>
    /* ===== Page head ===== */
    .kks-page-head {
        display: flex; align-items: center; justify-content: space-between;
        gap: 12px; margin-bottom: 14px;
    }
    .kks-page-title {
        font-size: 20px; font-weight: 700; margin: 0;
        display: flex; align-items: center; gap: 10px;
    }
    .kks-page-title i { color: #4f46e5; }
    .kks-breadcrumb .breadcrumb {
        font-size: 12.5px; padding: 0; background: transparent; margin-bottom: 4px;
    }
    .kks-tool-btn {
        background: #fff;
        border: 1px solid #e2e8f0;
        padding: 9px 14px;
        border-radius: 10px;
        font-weight: 600; font-size: 13px;
        color: #475569; cursor: pointer;
        display: inline-flex; align-items: center; gap: 6px;
        transition: background .15s;
        text-decoration: none;
    }
    .kks-tool-btn:hover { background: #f8fafc; color: #1f2937; }
    .kks-cta {
        background: linear-gradient(135deg, #4f46e5, #6366f1);
        color: #fff;
        border: none;
        padding: 9px 18px;
        border-radius: 10px;
        font-weight: 600; font-size: 13.5px;
        display: inline-flex; align-items: center; gap: 6px;
        text-decoration: none;
        transition: transform .15s, box-shadow .15s;
    }
    .kks-cta:hover {
        transform: translateY(-1px);
        box-shadow: 0 6px 16px rgba(79, 70, 229, .35);
        color: #fff;
    }

    /* ===== Card chính ===== */
    .kks-card {
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 2px 12px rgba(0,0,0,.06);
        overflow: hidden;
    }

    /* Head card gradient tím */
    .kks-card-head {
        padding: 18px 22px;
        background: linear-gradient(135deg, #4f46e5, #6366f1);
        color: #fff;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
    }
    .kks-card-head .kks-ma {
        font-family: 'JetBrains Mono', monospace;
        font-size: 22px;
        font-weight: 700;
        letter-spacing: .5px;
    }
    .kks-card-head .kks-ma i { font-size: 18px; opacity: .85; margin-right: 8px; }
    .kks-card-head .kks-head-right { display: flex; gap: 10px; align-items: center; flex-wrap: wrap; }
    .kks-status-pill {
        display: inline-flex; align-items: center; gap: 5px;
        padding: 6px 12px;
        border-radius: 999px;
        font-size: 12px; font-weight: 600;
        background: rgba(255,255,255,.18);
        color: #fff;
    }
    .kks-status-pill.green { background: #16a34a; }
    .kks-status-pill.yellow { background: #f59e0b; }
    .kks-status-pill.red { background: #ef4444; }
    .kks-status-pill.gray { background: rgba(255,255,255,.25); }

    /* Meta 6 ô */
    .kks-card-meta {
        padding: 16px 22px;
        background: #f8fbff;
        border-bottom: 1px solid #eef2f7;
        display: grid;
        grid-template-columns: repeat(6, 1fr);
        gap: 12px;
    }
    .kks-card-meta .label {
        color: #9ca3af;
        font-size: 11.5px;
        text-transform: uppercase;
        font-weight: 600;
        letter-spacing: .5px;
        margin-bottom: 4px;
    }
    .kks-card-meta .value {
        color: #1f2937;
        font-weight: 600;
        font-size: 13.5px;
    }
    .kks-card-meta .value.green { color: #16a34a; }
    .kks-card-meta .value.red   { color: #ef4444; }

    /* Toolbar bảng */
    .kks-toolbar {
        padding: 12px 22px;
        background: #fff;
        border-bottom: 1px solid #eee;
        display: flex;
        gap: 10px; align-items: center; flex-wrap: wrap;
    }
    .kks-toolbar input {
        height: 34px;
        font-size: 13px;
        max-width: 220px;
    }

    /* Bảng chi tiết */
    .kks-card-table { width: 100%; }
    .kks-card-table thead th {
        background: #f3f4f6;
        color: #4b5563;
        font-size: 11.5px;
        font-weight: 700;
        padding: 10px 12px;
        border-bottom: 1px solid #d1d5db;
        text-align: left;
        text-transform: uppercase;
        letter-spacing: .5px;
        white-space: nowrap;
    }
    .kks-card-table tbody td {
        padding: 12px;
        border-bottom: 1px solid #f3f4f6;
        vertical-align: middle;
        font-size: 13.5px;
    }
    .kks-card-table tbody tr:hover { background: #f8fbff; }
    .kks-card-table tbody tr:last-child td { border-bottom: none; }

    .kks-product-name { font-weight: 500; color: #1f2937; }
    .kks-product-meta { font-size: 12px; color: #9ca3af; margin-top: 2px; }

    .kks-good { color: #16a34a; font-weight: 600; }
    .kks-bad  { color: #ef4444; font-weight: 600; }
    .kks-pending { color: #94a3b8; }

    /* Ghi chú note block */
    .kks-note-block {
        padding: 14px 22px;
        border-top: 1px dashed #f0f0f0;
        background: #fffbe6;
    }
    .kks-note-block .label {
        color: #a16207; font-weight: 600;
        margin-bottom: 6px; display: block;
    }

    /* Summary 4 ô */
    .kks-summary-row {
        padding: 16px 22px;
        background: #f8fbff;
        border-top: 1px solid #eef2f7;
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 14px;
    }
    .kks-summary-row .item .label {
        font-size: 11.5px; color: #6b7280;
        text-transform: uppercase; font-weight: 600;
        letter-spacing: .5px;
    }
    .kks-summary-row .item .label .ct { color: #9ca3af; font-weight: 500; }
    .kks-summary-row .item .value {
        font-size: 20px; font-weight: 700;
        margin-top: 2px; color: #1f2937;
    }
    .kks-summary-row .item .value.good { color: #16a34a; }
    .kks-summary-row .item .value.bad  { color: #ef4444; }

    /* Footer actions */
    .kks-footer-actions {
        padding: 14px 22px;
        border-top: 1px solid #eee;
        background: #fafbfc;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }

    /* Empty state */
    .kks-empty {
        text-align: center;
        padding: 50px 20px;
        color: #94a3b8;
    }
    .kks-empty i { font-size: 44px; margin-bottom: 10px; color: #cbd5e1; }

    @media (max-width: 992px) {
        .kks-card-meta { grid-template-columns: repeat(3, 1fr); }
    }
    @media (max-width: 768px) {
        .kks-card-meta, .kks-summary-row { grid-template-columns: repeat(2, 1fr); }
    }
</style>
@endsection

@section('content')
{{-- ========== PAGE HEAD ========== --}}
<div class="kks-page-head">
    <div>
        <nav aria-label="breadcrumb" class="kks-breadcrumb">
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="{{ url('admin/dashboard') }}">Admin</a></li>
                <li class="breadcrumb-item"><a href="{{ url('admin/kho-hang') }}">Kho hàng</a></li>
                <li class="breadcrumb-item"><a href="{{ route('kiem-kho.history') }}">Kiểm kho</a></li>
                <li class="breadcrumb-item active">Chi tiết</li>
            </ol>
        </nav>
        <h4 class="kks-page-title">
            <i class="fas fa-clipboard-check"></i>
            Chi tiết phiếu kiểm kho
        </h4>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('kiem-kho.history') }}" class="kks-tool-btn">
            <i class="fas fa-arrow-left"></i> Quay lại
        </a>
        <button class="kks-cta" id="btn-in-head">
            <i class="fas fa-print"></i> In phiếu kiểm kho
        </button>
    </div>
</div>

<div id="kks-loading" class="kks-card text-center py-5">
    <i class="fas fa-spinner fa-spin me-2"></i>Đang tải...
</div>

<div id="kks-content" style="display:none">
    <div class="kks-card">
        {{-- Head card tím --}}
        <div class="kks-card-head">
            <div class="kks-ma">
                <i class="fas fa-file-alt"></i>
                <span id="detail-ma">—</span>
            </div>
            <div class="kks-head-right">
                <span class="kks-status-pill" id="detail-trang-thai-pill">—</span>
            </div>
        </div>

        {{-- Meta 6 ô --}}
        <div class="kks-card-meta">
            <div>
                <div class="label">Người tạo</div>
                <div class="value" id="detail-nguoi-tao">—</div>
            </div>
            <div>
                <div class="label">Ngày tạo</div>
                <div class="value" id="detail-ngay-tao">—</div>
            </div>
            <div>
                <div class="label">Người cân bằng</div>
                <div class="value" id="detail-nguoi-can-bang">—</div>
            </div>
            <div>
                <div class="label">Ngày cân bằng</div>
                <div class="value" id="detail-ngay-can-bang">—</div>
            </div>
            <div>
                <div class="label">Tổng SL lệch</div>
                <div class="value" id="detail-tong-sl-lech">—</div>
            </div>
            <div>
                <div class="label">Tổng GT điều chỉnh</div>
                <div class="value" id="detail-tong-gt-lech">—</div>
            </div>
        </div>

        {{-- Toolbar --}}
        <div class="kks-toolbar">
            <input type="text" id="filter-ma" class="form-control form-control-sm" placeholder="Tìm mã hàng">
            <input type="text" id="filter-ten" class="form-control form-control-sm" placeholder="Tìm tên hàng">
            <button class="kks-tool-btn" id="btn-clear-search">
                <i class="fas fa-undo"></i> Đặt lại
            </button>
        </div>

        {{-- Bảng chi tiết --}}
        <div class="table-responsive">
            <table class="kks-card-table">
                <thead>
                    <tr>
                        <th style="width:40px" class="text-center">#</th>
                        <th style="width:130px">Mã hàng</th>
                        <th>Tên hàng</th>
                        <th style="width:80px" class="text-end">SL tồn</th>
                        <th style="width:90px" class="text-end">SL lệch</th>
                        <th style="width:90px" class="text-end">SL thực tế</th>
                        <th style="width:120px" class="text-end">Đơn giá vốn</th>
                        <th style="width:140px" class="text-end">Thành tiền</th>
                        <th>Ghi chú</th>
                    </tr>
                </thead>
                <tbody id="kks-detail-tbody"></tbody>
            </table>
        </div>

        <div class="kks-note-block" id="kks-note-block" style="display:none">
            <span class="label"><i class="fas fa-sticky-note me-1"></i>Ghi chú phiếu</span>
            <textarea class="form-control" rows="3" id="detail-ghi-chu"></textarea>
        </div>

        <div class="kks-summary-row">
            <div class="item">
                <div class="label">Tổng thực tế <span class="ct" id="ct-sl-thuc-te">(0)</span></div>
                <div class="value" id="detail-sl-thuc-te">0</div>
            </div>
            <div class="item">
                <div class="label">Tổng lệch tăng <span class="ct" id="ct-lech-tang">(0)</span></div>
                <div class="value good" id="detail-lech-tang">—</div>
            </div>
            <div class="item">
                <div class="label">Tổng lệch giảm <span class="ct" id="ct-lech-giam">(0)</span></div>
                <div class="value bad" id="detail-lech-giam">—</div>
            </div>
            <div class="item">
                <div class="label">Tổng chênh lệch <span class="ct" id="ct-chenh-lech">(0)</span></div>
                <div class="value" id="detail-gia-tri-lech">0 ₫</div>
            </div>
        </div>

        <div class="kks-footer-actions">
            <div class="d-flex gap-2 flex-wrap">
                <button class="btn btn-outline-danger btn-sm" id="btn-huy" style="display:none">
                    <i class="fas fa-trash me-1"></i>Hủy
                </button>
                <button class="btn btn-outline-secondary btn-sm" id="btn-sao-chep">
                    <i class="fas fa-copy me-1"></i>Sao chép
                </button>
                <a id="btn-chinh-sua" class="btn btn-outline-primary btn-sm" style="display:none"
                   href="{{ route('kiem-kho.create') }}?edit={{ $id }}">
                    <i class="fas fa-edit me-1"></i>Chỉnh sửa
                </a>
                <button class="btn btn-outline-success btn-sm" id="btn-xuat-block">
                    <i class="fas fa-file-excel me-1"></i>Xuất file
                </button>
            </div>
            <div class="d-flex gap-2">
                <button class="btn btn-outline-primary btn-sm" id="btn-luu">
                    <i class="fas fa-save me-1"></i>Lưu
                </button>
                <button class="btn btn-primary btn-sm" id="btn-in">
                    <i class="fas fa-print me-1"></i>In phiếu kiểm kho
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
const phieuId = {{ $id }};
const urls = {
    detail: `/admin/api/kiem-kho/${phieuId}`,
    cancel: `/admin/api/kiem-kho/${phieuId}/cancel`,
};

let allItems = [];

function escapeHtml(s) {
    return (s ?? '').toString()
        .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;').replace(/'/g, '&#39;');
}

function statusPillCls(tt) {
    if (tt === 'hoan_thanh') return { cls: 'green',  label: 'Đã hoàn thành' };
    if (tt === 'phieu_tam')  return { cls: 'yellow', label: 'Đang xử lý' };
    if (tt === 'da_huy')     return { cls: 'red',    label: 'Đã hủy' };
    return { cls: 'gray', label: tt || '—' };
}

function fmtVND(n) { return (Number(n)||0).toLocaleString('vi-VN') + ' ₫'; }

async function loadDetail() {
    try {
        const res = await axios.get(urls.detail);
        const p = res.data.data;
        if (!p) {
            document.getElementById('kks-loading').innerHTML =
                '<div class="kks-empty"><i class="fas fa-exclamation-triangle text-warning"></i><div>Không tìm thấy phiếu</div></div>';
            return;
        }
        document.getElementById('kks-loading').style.display = 'none';
        document.getElementById('kks-content').style.display = 'block';

        // Mã phiếu
        document.getElementById('detail-ma').textContent = p.ma_kiem_kho;

        // Trạng thái pill (gradient head)
        const pill = statusPillCls(p.trang_thai);
        const pillEl = document.getElementById('detail-trang-thai-pill');
        pillEl.textContent = pill.label;
        pillEl.className = 'kks-status-pill ' + pill.cls;

        // Meta 6 ô
        document.getElementById('detail-nguoi-tao').textContent = p.nguoi_tao || '—';
        document.getElementById('detail-ngay-tao').textContent = p.created_at || '—';
        document.getElementById('detail-nguoi-can-bang').textContent = (p.trang_thai === 'hoan_thanh') ? (p.nguoi_tao || '—') : '—';
        document.getElementById('detail-ngay-can-bang').textContent = p.hoan_thanh_luc || '—';

        const slLech = Number(p.tong_sl_lech) || 0;
        const gvLech = Number(p.tong_gia_tri_lech) || 0;

        const slLechEl = document.getElementById('detail-tong-sl-lech');
        slLechEl.textContent = (slLech > 0 ? '+' : slLech < 0 ? '' : '') + slLech.toLocaleString('vi-VN');
        slLechEl.className = 'value ' + (slLech > 0 ? 'green' : (slLech < 0 ? 'red' : ''));

        const gtLechEl = document.getElementById('detail-tong-gt-lech');
        gtLechEl.textContent = (gvLech >= 0 ? '+' : '-') + fmtVND(Math.abs(gvLech));
        gtLechEl.className = 'value ' + (gvLech > 0 ? 'green' : (gvLech < 0 ? 'red' : ''));

        // Ghi chú phiếu
        const ghiChuEl = document.getElementById('kks-note-block');
        const ghiChuText = document.getElementById('detail-ghi-chu');
        if (p.ghi_chu && p.ghi_chu.trim() !== '') {
            ghiChuText.value = p.ghi_chu;
            ghiChuEl.style.display = 'block';
        } else {
            ghiChuEl.style.display = 'none';
        }

        // Items
        allItems = p.items || [];
        renderTable();

        // Summary 4 ô dưới
        const items = p.items || [];
        const slTt = Number(p.tong_sl_thuc_te) || 0;
        const slTang = slLech > 0 ? slLech : 0;
        const slGiam = slLech < 0 ? Math.abs(slLech) : 0;

        document.getElementById('ct-sl-thuc-te').textContent = `(${items.length})`;
        document.getElementById('ct-lech-tang').textContent = `(${slTang > 0 ? '+' + slTang : 0})`;
        document.getElementById('ct-lech-giam').textContent = `(${slGiam > 0 ? '-' + slGiam : 0})`;
        document.getElementById('ct-chenh-lech').textContent = `(${items.length})`;

        document.getElementById('detail-sl-thuc-te').textContent = slTt.toLocaleString('vi-VN');
        document.getElementById('detail-lech-tang').textContent = (slTang > 0 ? '+' + slTang.toLocaleString('vi-VN') : '—');
        document.getElementById('detail-lech-giam').textContent = (slGiam > 0 ? '-' + slGiam.toLocaleString('vi-VN') : '—');
        const gvEl = document.getElementById('detail-gia-tri-lech');
        gvEl.textContent = (gvLech >= 0 ? '+' : '-') + Math.abs(gvLech).toLocaleString('vi-VN') + ' ₫';
        gvEl.className = 'value ' + (gvLech > 0 ? 'good' : (gvLech < 0 ? 'bad' : ''));

        if (p.trang_thai === 'phieu_tam') {
            document.getElementById('btn-huy').style.display = 'inline-block';
            document.getElementById('btn-chinh-sua').style.display = 'inline-block';
        }
    } catch (e) {
        console.error(e);
        document.getElementById('kks-loading').innerHTML =
            '<div class="kks-empty"><i class="fas fa-exclamation-triangle text-danger"></i><div>Lỗi tải: ' + escapeHtml(e.message) + '</div></div>';
    }
}

function renderTable() {
    const maQ = (document.getElementById('filter-ma').value || '').toLowerCase().trim();
    const tenQ = (document.getElementById('filter-ten').value || '').toLowerCase().trim();
    const tbody = document.getElementById('kks-detail-tbody');
    const filtered = allItems.filter(it => {
        if (maQ && !(it.ma_vach || '').toLowerCase().includes(maQ)) return false;
        if (tenQ) {
            const tenFull = (it.ten_san_pham || '') + ' ' + (it.ten_bien_the || '');
            if (!tenFull.toLowerCase().includes(tenQ)) return false;
        }
        return true;
    });

    if (filtered.length === 0) {
        tbody.innerHTML = '<tr><td colspan="9"><div class="kks-empty">'
            + '<i class="fas fa-search"></i>'
            + '<div class="mt-2">Không tìm thấy dòng nào</div>'
            + '</div></td></tr>';
        return;
    }

    tbody.innerHTML = filtered.map((it, idx) => {
        const l = Number(it.so_luong_lech) || 0;
        const lClass = !it.da_kiem ? 'kks-pending' : (l === 0 ? '' : (l > 0 ? 'kks-good' : 'kks-bad'));
        const lText = !it.da_kiem ? '—' : (l > 0 ? '+' + Number(l).toLocaleString('vi-VN') : Number(l).toLocaleString('vi-VN'));
        const gv = Number(it.gia_tri_lech) || 0;
        const gvText = !it.da_kiem ? '—' : (gv !== 0 ? (gv < 0 ? '-' : '+') + Math.abs(gv).toLocaleString('vi-VN') + ' ₫' : '0 ₫');

        const tonKho = Number(it.so_luong_ton) || 0;
        const thanhTien = Number(it.gia_von || 0) * tonKho;
        const tenSp = escapeHtml(it.ten_san_pham || '');
        const tenVariant = escapeHtml(it.ten_bien_the || '');
        const tenDv = escapeHtml(it.ten_don_vi || '');
        const tenFull = tenVariant ? `${tenSp} - ${tenVariant}` : tenSp;
        const tt = it.da_kiem ? Number(it.so_luong_thuc_te).toLocaleString('vi-VN') : '<span class="kks-pending">—</span>';
        const ghiChuItem = escapeHtml(it.ghi_chu || '');

        return `
            <tr>
                <td class="text-center text-muted">${idx + 1}</td>
                <td><code>${escapeHtml(it.ma_vach || '')}</code></td>
                <td>
                    <div class="kks-product-name">${tenFull}</div>
                    ${tenDv ? `<div class="kks-product-meta">ĐVT: ${tenDv}</div>` : ''}
                </td>
                <td class="text-end fw-semibold">${tonKho.toLocaleString('vi-VN')}</td>
                <td class="text-end ${lClass}">${lText}</td>
                <td class="text-end">${tt}</td>
                <td class="text-end">${fmtVND(it.gia_von || 0)}</td>
                <td class="text-end fw-semibold">${thanhTien.toLocaleString('vi-VN')} ₫</td>
                <td class="small text-muted">${ghiChuItem || '<span class="kks-pending">—</span>'}</td>
            </tr>
        `;
    }).join('');
}

document.addEventListener('DOMContentLoaded', () => {
    loadDetail();
    document.getElementById('filter-ma').addEventListener('input', renderTable);
    document.getElementById('filter-ten').addEventListener('input', renderTable);

    document.getElementById('btn-clear-search').addEventListener('click', () => {
        document.getElementById('filter-ma').value = '';
        document.getElementById('filter-ten').value = '';
        renderTable();
    });

    document.getElementById('btn-huy').addEventListener('click', () => {
        Swal.fire({
            icon: 'warning',
            title: 'Hủy phiếu kiểm kho?',
            text: 'Phiếu sẽ chuyển sang trạng thái ĐÃ HỦY và không thể chỉnh sửa.',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            confirmButtonText: 'Hủy phiếu',
            cancelButtonText: 'Đóng',
        }).then(r => {
            if (!r.isConfirmed) return;
            axios.post(urls.cancel).then(res => {
                if (res.data.success) {
                    toastr.success(res.data.message);
                    setTimeout(() => location.reload(), 600);
                } else {
                    toastr.error(res.data.message);
                }
            });
        });
    });

    document.getElementById('btn-sao-chep').addEventListener('click', () => {
        toastr.info('Tính năng sao chép sẽ được cập nhật ở phiên bản sau.');
    });

    document.getElementById('btn-xuat-block').addEventListener('click', () => {
        toastr.info('Chức năng xuất Excel đang được phát triển.');
    });

    document.getElementById('btn-luu').addEventListener('click', () => {
        toastr.info('Phiếu đã hoàn tất.');
    });

    const doPrint = () => {
        toastr.info('In phiếu (sẽ phát triển ở bản sau).');
        setTimeout(() => window.print(), 100);
    };
    document.getElementById('btn-in').addEventListener('click', doPrint);
    document.getElementById('btn-in-head').addEventListener('click', doPrint);
});
</script>
@endsection