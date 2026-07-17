@extends('admin_xem_truoc.layouts.admin')

@section('title', 'Lịch sử kiểm kho - SmartMart')

@section('styles')
<style>
    /* ===== LAYOUT 2 cột: filter trái + main phải ===== */
    .kkhs-shell {
        display: flex;
        gap: 16px;
        align-items: flex-start;
        margin-top: 12px;
    }
    .kkhs-filter {
        flex: 0 0 270px;
        position: sticky;
        top: 12px;
        display: flex;
        flex-direction: column;
        gap: 14px;
    }
    .kkhs-main {
        flex: 1 1 auto;
        min-width: 0;
        display: flex;
        flex-direction: column;
        gap: 14px;
    }

    /* ===== Filter card (KiotViet style) ===== */
    .kkhs-fcard {
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 2px 12px rgba(0,0,0,.05);
        overflow: hidden;
    }
    .kkhs-fcard-h {
        padding: 12px 16px;
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .5px;
        color: #1f2937;
        background: linear-gradient(135deg, #f8fafc, #f1f5f9);
        border-bottom: 1px solid #eef2f7;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .kkhs-fcard-h i { color: #4f46e5; }
    .kkhs-fcard-b { padding: 14px 16px; }

    /* Radio chips "Thời gian" */
    .kkhs-radio-group { display: flex; flex-direction: column; gap: 8px; }
    .kkhs-radio-group label {
        display: flex; align-items: center; gap: 8px;
        font-size: 13px; color: #475569;
        cursor: pointer; padding: 4px 0;
    }
    .kkhs-radio-group input[type="radio"] { accent-color: #4f46e5; }
    .kkhs-radio-group label.checked { color: #1f2937; font-weight: 600; }

    .kkhs-date-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 8px;
        margin-top: 8px;
    }
    .kkhs-date-row input {
        font-size: 12.5px;
        padding: 6px 8px;
        border-radius: 6px;
        border: 1px solid #e2e8f0;
    }

    /* Status chips */
    .kkhs-status-list { display: flex; flex-direction: column; gap: 6px; }
    .kkhs-status-chip {
        display: flex; align-items: center; gap: 10px;
        padding: 8px 12px;
        border-radius: 8px;
        font-size: 13px;
        cursor: pointer;
        transition: background .15s;
        border: 1px solid transparent;
        user-select: none;
    }
    .kkhs-status-chip:hover { background: #f8fafc; }
    .kkhs-status-chip.active {
        background: #f1f5ff;
        border-color: #c7d2fe;
    }
    .kkhs-status-chip .dot {
        width: 8px; height: 8px; border-radius: 50%;
        flex-shrink: 0;
    }
    .kkhs-status-chip .dot.green  { background: #16a34a; }
    .kkhs-status-chip .dot.yellow { background: #f59e0b; }
    .kkhs-status-chip .dot.red    { background: #ef4444; }
    .kkhs-status-chip .dot.gray   { background: #94a3b8; }
    .kkhs-status-chip .name { flex: 1; color: #1f2937; font-weight: 500; }
    .kkhs-status-chip .cnt {
        font-size: 11.5px;
        background: #f1f5f9;
        color: #475569;
        padding: 2px 8px;
        border-radius: 999px;
        font-weight: 600;
    }
    .kkhs-status-chip.active .cnt { background: #e0e7ff; color: #4338ca; }

    /* ===== Page head + CTA ===== */
    .kkhs-page-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
    }
    .kkhs-page-title {
        font-size: 20px;
        font-weight: 700;
        margin: 0;
        display: flex; align-items: center; gap: 10px;
    }
    .kkhs-page-title i { color: #4f46e5; }
    .kkhs-breadcrumb .breadcrumb {
        font-size: 12.5px;
        padding: 0; background: transparent; margin-bottom: 4px;
    }
    .kkhs-cta-group { display: flex; gap: 8px; align-items: center; }
    .kkhs-cta {
        background: linear-gradient(135deg, #4f46e5, #6366f1);
        color: #fff;
        border: none;
        padding: 9px 18px;
        border-radius: 10px;
        font-weight: 600;
        font-size: 13.5px;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        text-decoration: none;
        transition: transform .15s, box-shadow .15s;
    }
    .kkhs-cta:hover {
        transform: translateY(-1px);
        box-shadow: 0 6px 16px rgba(79, 70, 229, .35);
        color: #fff;
    }
    .kkhs-tool-btn {
        background: #fff;
        border: 1px solid #e2e8f0;
        padding: 9px 14px;
        border-radius: 10px;
        font-weight: 600;
        font-size: 13px;
        color: #475569;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition: background .15s;
    }
    .kkhs-tool-btn:hover { background: #f8fafc; color: #1f2937; }
    .kkhs-tool-btn.success {
        color: #166534; border-color: #bbf7d0; background: #f0fdf4;
    }
    .kkhs-tool-btn.success:hover { background: #dcfce7; }

    /* ===== Main: pills + toolbar ===== */
    .kkhs-main-head {
        display: flex; align-items: center; gap: 10px; flex-wrap: wrap;
    }
    .kkhs-pill-tabs {
        display: flex; background: #fff;
        border-radius: 12px; padding: 4px; gap: 4px;
        box-shadow: 0 2px 10px rgba(0,0,0,.04);
    }
    .kkhs-pill-tab {
        padding: 8px 14px;
        font-size: 13px;
        font-weight: 600;
        color: #64748b;
        border-radius: 8px;
        cursor: pointer;
        transition: all .15s;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        text-decoration: none;
    }
    .kkhs-pill-tab .badge {
        background: #e2e8f0; color: #475569;
        font-size: 10.5px; padding: 2px 7px;
        border-radius: 999px; font-weight: 700;
    }
    .kkhs-pill-tab:hover { color: #1f2937; background: #f8fafc; }
    .kkhs-pill-tab.active {
        background: linear-gradient(135deg, #4f46e5, #6366f1);
        color: #fff;
        box-shadow: 0 4px 10px rgba(79, 70, 229, .25);
    }
    .kkhs-pill-tab.active .badge { background: rgba(255,255,255,.25); color: #fff; }

    .kkhs-toolbar {
        display: flex; flex: 1 1 220px;
        gap: 8px; align-items: center; margin-left: auto;
    }
    .kkhs-search {
        position: relative; flex: 1 1 220px;
    }
    .kkhs-search i {
        position: absolute; left: 12px; top: 50%;
        transform: translateY(-50%); color: #94a3b8;
    }
    .kkhs-search input {
        padding-left: 36px !important;
        height: 38px;
        border-radius: 10px !important;
        border: 1px solid #e2e8f0 !important;
        font-size: 13px !important;
    }

    /* ===== Bảng ===== */
    .kkhs-card {
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 2px 12px rgba(0,0,0,.05);
        overflow: hidden;
    }
    .kkhs-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
    }
    .kkhs-table thead th {
        background: #f8fafc;
        color: #64748b;
        font-size: 11.5px;
        text-transform: uppercase;
        letter-spacing: .5px;
        font-weight: 700;
        padding: 14px;
        border-bottom: 1px solid #e2e8f0;
        white-space: nowrap;
    }
    .kkhs-table tbody td {
        padding: 14px;
        border-bottom: 1px solid #f1f5f9;
        vertical-align: middle;
        font-size: 13.5px;
        color: #1f2937;
    }
    .kkhs-table tbody tr.kkhs-row { cursor: pointer; transition: background .15s; }
    .kkhs-table tbody tr.kkhs-row:hover { background: #fafbff; }
    .kkhs-table tbody tr:last-child td { border-bottom: none; }

    .kkhs-ma {
        font-family: 'JetBrains Mono', monospace;
        font-size: 12.5px;
        font-weight: 700;
        color: #4f46e5;
    }
    .kkhs-lech-am { color: #ef4444; font-weight: 600; }
    .kkhs-lech-duong { color: #16a34a; font-weight: 600; }
    .kkhs-lech-khong { color: #94a3b8; }

    .kkhs-user-cell { display: flex; align-items: center; gap: 8px; }
    .kkhs-avatar {
        width: 30px; height: 30px;
        border-radius: 50%;
        background: linear-gradient(135deg, #6366f1, #ec4899);
        color: #fff;
        font-size: 12px;
        font-weight: 700;
        display: flex; align-items: center; justify-content: center;
        text-transform: uppercase;
        flex-shrink: 0;
    }
    .kkhs-user-name { font-weight: 500; color: #1f2937; font-size: 13px; }

    /* Row pill trạng thái */
    .kkhs-row-pill {
        display: inline-flex; align-items: center; gap: 5px;
        padding: 4px 10px;
        border-radius: 999px;
        font-size: 11.5px;
        font-weight: 600;
        white-space: nowrap;
    }
    .kkhs-row-pill.green { background: #dcfce7; color: #166534; }
    .kkhs-row-pill.yellow { background: #fef3c7; color: #854d0e; }
    .kkhs-row-pill.red { background: #fee2e2; color: #991b1b; }
    .kkhs-row-pill.gray { background: #f1f5f9; color: #475569; }

    /* Inline chips lệch trong bảng (Số mặt hàng) */
    .kkhs-delta-chip {
        display: inline-flex;
        align-items: center;
        gap: 3px;
        padding: 3px 8px;
        border-radius: 999px;
        font-size: 11.5px;
        font-weight: 700;
        margin-right: 4px;
    }
    .kkhs-delta-chip.up   { background: #dcfce7; color: #166534; }
    .kkhs-delta-chip.down { background: #fee2e2; color: #991b1b; }

    /* Expand row */
    .kkhs-expand td { padding: 0; border-bottom: none; background: #f8fbff; }
    .kkhs-expand-inner { padding: 18px 22px; }

    .kkhs-detail-card {
        background: #fff;
        border-radius: 10px;
        border: 1px solid #e5e7eb;
        overflow: hidden;
    }
    .kkhs-detail-head {
        padding: 12px 16px;
        background: linear-gradient(135deg, #4f46e5, #6366f1);
        color: #fff;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .kkhs-detail-head .ma { font-size: 16px; font-weight: 700; }

    .kkhs-detail-meta {
        padding: 10px 16px;
        background: #f8fbff;
        border-bottom: 1px solid #eef2f7;
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 10px;
        font-size: 12.5px;
    }
    .kkhs-detail-meta .label { color: #9ca3af; font-size: 11.5px; }
    .kkhs-detail-meta .value { color: #1f2937; font-weight: 500; }

    .kkhs-detail-table { width: 100%; }
    .kkhs-detail-table thead th {
        background: #f3f4f6;
        color: #4b5563;
        font-size: 11.5px;
        font-weight: 600;
        padding: 8px 10px;
        border-bottom: 1px solid #d1d5db;
        text-align: left;
        text-transform: uppercase;
        letter-spacing: .5px;
    }
    .kkhs-detail-table tbody td {
        padding: 8px 10px;
        border-bottom: 1px solid #f3f4f6;
        font-size: 13px;
    }
    .kkhs-detail-table tbody tr:last-child td { border-bottom: none; }

    .kkhs-detail-summary {
        padding: 14px 16px;
        background: #f8fbff;
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 14px;
        border-top: 1px solid #eef2f7;
    }
    .kkhs-summary-item .label {
        font-size: 11.5px; color: #6b7280;
        text-transform: uppercase;
        font-weight: 600; letter-spacing: .5px;
    }
    .kkhs-summary-item .value {
        font-size: 18px;
        font-weight: 700;
        margin-top: 2px;
        color: #1f2937;
    }
    .kkhs-summary-item .value.good { color: #16a34a; }
    .kkhs-summary-item .value.bad  { color: #ef4444; }

    .kkhs-detail-footer {
        padding: 12px 16px;
        border-top: 1px solid #eee;
        background: #fafbfc;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }
    .kkhs-detail-loading { text-align: center; padding: 30px; color: #9ca3af; }

    .kkhs-empty {
        text-align: center;
        padding: 60px 20px;
        color: #94a3b8;
    }
    .kkhs-empty i { font-size: 48px; margin-bottom: 12px; color: #cbd5e1; }

    .kkhs-page-foot {
        padding: 14px 16px;
        font-size: 12px;
        color: #94a3b8;
        background: #fafbfc;
        border-top: 1px solid #f1f5f9;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    @media (max-width: 992px) {
        .kkhs-shell { flex-direction: column; }
        .kkhs-filter { flex: 1 1 auto; position: relative; width: 100%; }
    }
</style>
@endsection

@section('content')
{{-- ========== PAGE HEAD ========== --}}
<div class="kkhs-page-head">
    <div>
        <nav aria-label="breadcrumb" class="kkhs-breadcrumb">
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="{{ url('admin/dashboard') }}">Admin</a></li>
                <li class="breadcrumb-item"><a href="{{ url('admin/kho-hang') }}">Kho hàng</a></li>
                <li class="breadcrumb-item active">Kiểm kho</li>
            </ol>
        </nav>
        <h4 class="kkhs-page-title">
            <i class="fas fa-clipboard-check"></i>
            Phiếu kiểm kho
        </h4>
    </div>
    <div class="kkhs-cta-group">
        <a href="{{ route('kiem-kho.create', ['mode' => 'new']) }}" class="kkhs-cta">
            <i class="fas fa-plus"></i> Tạo phiếu kiểm kho
        </a>
        <button class="kkhs-tool-btn success" id="btn-xuat-file" title="Xuất Excel">
            <i class="fas fa-file-excel"></i> Xuất Excel
        </button>
        <button class="kkhs-tool-btn" id="btn-reload" title="Tải lại">
            <i class="fas fa-redo"></i>
        </button>
    </div>
</div>

<div class="kkhs-shell">

    {{-- ========== SIDEBAR FILTER ========== --}}
    <aside class="kkhs-filter">
        {{-- Thời gian --}}
        <div class="kkhs-fcard">
            <div class="kkhs-fcard-h">
                <i class="far fa-calendar"></i> Thời gian
            </div>
            <div class="kkhs-fcard-b">
                <div class="kkhs-radio-group" id="time-group">
                    <label class="checked"><input type="radio" name="filter-ngay" value="hom_nay" checked> Hôm nay</label>
                    <label><input type="radio" name="filter-ngay" value="7_ngay"> 7 ngày qua</label>
                    <label><input type="radio" name="filter-ngay" value="thang_nay"> Tháng này</label>
                    <label><input type="radio" name="filter-ngay" value="thang_truoc"> Tháng trước</label>
                    <label><input type="radio" name="filter-ngay" value="tuy_chinh"> Tùy chỉnh</label>
                </div>
                <div class="kkhs-date-row" id="ngay-tuy-chinh-range" style="display:none">
                    <input type="date" id="filter-tu-ngay">
                    <input type="date" id="filter-den-ngay">
                </div>
                <button class="btn btn-light btn-sm w-100 mt-2" id="btn-reset-filter">
                    <i class="fas fa-undo me-1"></i>Đặt lại bộ lọc
                </button>
            </div>
        </div>

        {{-- Trạng thái (chips) --}}
        <div class="kkhs-fcard">
            <div class="kkhs-fcard-h">
                <i class="fas fa-tasks"></i> Trạng thái
            </div>
            <div class="kkhs-fcard-b">
                <div class="kkhs-status-list" id="kkhs-status-list">
                    <div class="kkhs-status-chip active" data-status="">
                        <span class="dot gray"></span>
                        <span class="name">Tất cả phiếu</span>
                        <span class="cnt" id="status-cnt-all">—</span>
                    </div>
                    <div class="kkhs-status-chip" data-status="phieu_tam">
                        <span class="dot yellow"></span>
                        <span class="name">Đang kiểm</span>
                        <span class="cnt" id="status-cnt-tam">—</span>
                    </div>
                    <div class="kkhs-status-chip" data-status="hoan_thanh">
                        <span class="dot green"></span>
                        <span class="name">Đã cân bằng</span>
                        <span class="cnt" id="status-cnt-xong">—</span>
                    </div>
                    <div class="kkhs-status-chip" data-status="da_huy">
                        <span class="dot red"></span>
                        <span class="name">Đã hủy</span>
                        <span class="cnt" id="status-cnt-huy">—</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Người tạo --}}
        <div class="kkhs-fcard">
            <div class="kkhs-fcard-h">
                <i class="fas fa-user"></i> Người tạo
            </div>
            <div class="kkhs-fcard-b">
                <select id="filter-nguoi-tao" class="form-select form-select-sm">
                    <option value="">Tất cả</option>
                    <option value="me">Tôi tạo</option>
                </select>
            </div>
        </div>
    </aside>

    {{-- ========== MAIN ========== --}}
    <section class="kkhs-main">

        {{-- Pills + search + actions --}}
        <div class="kkhs-main-head">
            <div class="kkhs-pill-tabs" id="kkhs-pill-tabs">
                <a class="kkhs-pill-tab active" data-tab="all">
                    Tất cả <span class="badge" id="cnt-all">0</span>
                </a>
                <a class="kkhs-pill-tab" data-tab="processing">
                    Đang xử lý <span class="badge" id="cnt-processing">0</span>
                </a>
                <a class="kkhs-pill-tab" data-tab="done">
                    Đã hoàn thành <span class="badge" id="cnt-done">0</span>
                </a>
            </div>

            <div class="kkhs-toolbar">
                <div class="kkhs-search">
                    <i class="fas fa-search"></i>
                    <input type="text" id="filter-q" class="form-control"
                           placeholder="Theo mã phiếu kiểm..." autocomplete="off">
                </div>
                <button class="kkhs-tool-btn" id="btn-toggle-filter">
                    <i class="fas fa-filter"></i> Lọc
                </button>
            </div>
        </div>

        {{-- Bảng --}}
        <div class="kkhs-card">
            <div class="table-responsive">
                <table class="kkhs-table">
                    <thead>
                        <tr>
                            <th style="width:40px" class="text-center">#</th>
                            <th>Mã phiếu kiểm</th>
                            <th>Người tạo</th>
                            <th class="text-center">Ngày tạo</th>
                            <th class="text-center">Trạng thái</th>
                            <th class="text-center">Số mặt hàng</th>
                            <th class="text-center">Đã đối soát</th>
                            <th class="text-end">Giá trị điều chỉnh</th>
                            <th class="text-center" style="width:90px"></th>
                        </tr>
                    </thead>
                    <tbody id="bang-kiem-kho">
                        <tr><td colspan="9"><div class="kkhs-empty">
                            <i class="fas fa-spinner fa-spin"></i>
                            <div class="mt-2">Đang tải...</div>
                        </div></td></tr>
                    </tbody>
                </table>
            </div>
            <div class="kkhs-page-foot">
                <span id="thong-tin-hien-thi">Hiển thị 0 / 0 phiếu</span>
                <nav id="phan-trang"></nav>
            </div>
        </div>

    </section>
</div>
@endsection

@section('scripts')
<script>
const url = {
    history:   '/admin/api/kiem-kho/history',
    detail:    id => `/admin/api/kiem-kho/${id}`,
    cancel:    id => `/admin/api/kiem-kho/${id}/cancel`,
    exportOne: id => `/admin/api/kiem-kho/${id}/export`,
    exportAll:    `/admin/api/kiem-kho/history/export`,
};

let currentPage = 1, lastPage = 1;
let activeStatus = '';
let activeTab = 'all';
const detailCache = new Map();

function escapeHtml(s) {
    return (s ?? '').toString()
        .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;').replace(/'/g, '&#39;');
}

function getAvatar(name) {
    return escapeHtml((name || 'U').trim().charAt(0).toUpperCase());
}

function fmtVND(n) { return (Number(n)||0).toLocaleString('vi-VN') + ' ₫'; }
function fmtNum(n)  { return (Number(n)||0).toLocaleString('vi-VN'); }

function statusPill(tt) {
    if (tt === 'hoan_thanh') return { cls: 'green',  label: 'Đã hoàn thành' };
    if (tt === 'phieu_tam')  return { cls: 'yellow', label: 'Đang xử lý' };
    if (tt === 'da_huy')     return { cls: 'red',    label: 'Đã hủy' };
    return { cls: 'gray', label: tt || '—' };
}

async function load(page = 1) {
    const params = {
        page,
        q: document.getElementById('filter-q').value.trim(),
        trang_thai: activeStatus,
    };
    const ngayMode = document.querySelector('input[name="filter-ngay"]:checked')?.value;
    if (ngayMode === 'tuy_chinh') {
        params.tu_ngay = document.getElementById('filter-tu-ngay').value;
        params.den_ngay = document.getElementById('filter-den-ngay').value;
    } else if (ngayMode) {
        params.thoi_gian = ngayMode;
    }

    try {
        const res = await axios.get(url.history, {params});
        const data = res.data;
        const tbody = document.getElementById('bang-kiem-kho');
        if (!data.data || data.data.length === 0) {
            tbody.innerHTML = `<tr><td colspan="9"><div class="kkhs-empty">
                <i class="fas fa-inbox"></i>
                <div class="mt-2">Chưa có phiếu kiểm kho nào</div>
            </div></td></tr>`;
            document.getElementById('thong-tin-hien-thi').textContent = 'Hiển thị 0 / 0 phiếu';
            document.getElementById('phan-trang').innerHTML = '';
            updateStatusCounts({all: 0, processing: 0, done: 0, cancel: 0});
            updateCounts({data: {data: []}, total: 0});
            return;
        }

        tbody.innerHTML = data.data.map((p, idx) => {
            const slLech = Number(p.tong_sl_lech) || 0;
            const lClass = slLech < 0 ? 'kkhs-lech-am' : (slLech > 0 ? 'kkhs-lech-duong' : 'kkhs-lech-khong');
            const lText = (slLech === 0 ? '0' : (slLech > 0 ? '+' : '') + slLech.toLocaleString('vi-VN'));
            const pill = statusPill(p.trang_thai);

            const slMatHang = Number(p.so_dong ?? 0);
            const slDaDoiSoat = Number(p.tong_sl_thuc_te || 0);
            const gvLech = Number(p.tong_gia_tri_lech || 0);

            // chips lệch tăng / giảm
            const slTang = slLech > 0 ? slLech : 0;
            const slGiam = slLech < 0 ? Math.abs(slLech) : 0;
            const deltaChips = `
                ${slTang > 0 ? `<span class="kkhs-delta-chip up">+${slTang}</span>` : ''}
                ${slGiam > 0 ? `<span class="kkhs-delta-chip down">-${slGiam}</span>` : ''}
                ${slTang === 0 && slGiam === 0 ? '<span class="text-muted small">—</span>' : ''}
            `;

            return `
                <tr class="kkhs-row" data-id="${p.id}" data-expanded="0" data-status="${p.trang_thai}">
                    <td class="text-center text-muted">${idx + 1}</td>
                    <td><span class="kkhs-ma">${escapeHtml(p.ma_kiem_kho)}</span></td>
                    <td>
                        <div class="kkhs-user-cell">
                            <div class="kkhs-avatar">${getAvatar(p.nguoi_tao)}</div>
                            <div class="kkhs-user-name">${escapeHtml(p.nguoi_tao || 'N/A')}</div>
                        </div>
                    </td>
                    <td class="text-center">${escapeHtml(p.created_at || '—')}</td>
                    <td class="text-center"><span class="kkhs-row-pill ${pill.cls}">${pill.label}</span></td>
                    <td class="text-center">${deltaChips}</td>
                    <td class="text-center">${slDaDoiSoat} / ${slMatHang}</td>
                    <td class="text-end ${lClass}">
                        <div>${lText}</div>
                        <div class="small text-muted">${gvLech >= 0 ? '+' : '-'}${fmtVND(Math.abs(gvLech))}</div>
                    </td>
                    <td class="text-center" onclick="event.stopPropagation()">
                        <a href="/admin/kho-hang/kiem-kho/${p.id}" class="btn btn-sm btn-light border" title="Xem chi tiết">
                            <i class="fas fa-eye text-primary"></i>
                        </a>
                        <a href="/admin/kho-hang/kiem-kho/${p.id}" class="btn btn-sm btn-light border" title="In phiếu">
                            <i class="fas fa-print text-secondary"></i>
                        </a>
                        <i class="fas fa-chevron-down text-muted toggle-icon ms-1"></i>
                    </td>
                </tr>
                <tr class="kkhs-expand" data-expand-for="${p.id}" style="display:none">
                    <td colspan="9">
                        <div class="kkhs-expand-inner">
                            <div class="kkhs-detail-loading"><i class="fas fa-spinner fa-spin me-2"></i>Đang tải chi tiết...</div>
                        </div>
                    </td>
                </tr>
            `;
        }).join('');

        currentPage = data.current_page;
        lastPage = data.last_page;
        document.getElementById('thong-tin-hien-thi').textContent =
            `Trang ${currentPage}/${lastPage} - Tổng ${data.total} phiếu`;
        renderPagination();

        // Đếm status để hiển thị chip count
        updateStatusCounts({
            all: data.total,
            processing: data.data.filter(p => p.trang_thai === 'phieu_tam').length,
            done: data.data.filter(p => p.trang_thai === 'hoan_thanh').length,
            cancel: data.data.filter(p => p.trang_thai === 'da_huy').length,
        });
        updateCounts(data);
    } catch (e) {
        console.error(e);
        toastr?.error?.('Lỗi tải lịch sử kiểm kho');
    }
}

function updateCounts(data) {
    const list = (data.data || []).filter(p => !p.trang_thai || p.trang_thai === 'da_huy' || p.trang_thai === 'hoan_thanh' || p.trang_thai === 'phieu_tam');
    document.getElementById('cnt-all').textContent = data.total || data.data.length;
    document.getElementById('cnt-processing').textContent = list.filter(p => p.trang_thai === 'phieu_tam').length;
    document.getElementById('cnt-done').textContent = list.filter(p => p.trang_thai === 'hoan_thanh').length;
}

function updateStatusCounts(c) {
    document.getElementById('status-cnt-all').textContent = c.all ?? '—';
    document.getElementById('status-cnt-tam').textContent = c.processing ?? '—';
    document.getElementById('status-cnt-xong').textContent = c.done ?? '—';
    document.getElementById('status-cnt-huy').textContent  = c.cancel ?? '—';
}

// ============ Render chi tiết inline ============
function renderDetail(p) {
    const slTt = Number(p.tong_sl_thuc_te) || 0;
    const slLech = Number(p.tong_sl_lech) || 0;
    const slTang = slLech > 0 ? slLech : 0;
    const slGiam = slLech < 0 ? Math.abs(slLech) : 0;
    const gvLech = Number(p.tong_gia_tri_lech) || 0;

    const items = p.items || [];
    const itemsCount = items.length;
    const itemRows = items.length === 0
        ? '<tr><td colspan="6" class="text-center text-muted py-3">Chưa có dòng chi tiết</td></tr>'
        : items.map(it => {
            const l = Number(it.so_luong_lech) || 0;
            const lClass = !it.da_kiem ? 'kkhs-lech-khong' : (l === 0 ? 'kkhs-lech-khong' : (l > 0 ? 'kkhs-lech-duong' : 'kkhs-lech-am'));
            const lText = !it.da_kiem ? '—' : (l > 0 ? '+' + Number(l).toLocaleString('vi-VN') : Number(l).toLocaleString('vi-VN'));
            const gv = Number(it.gia_tri_lech) || 0;
            const gvText = !it.da_kiem ? '—' : (gv !== 0 ? (gv < 0 ? '-' : '+') + Math.abs(gv).toLocaleString('vi-VN') + ' ₫' : '0 ₫');
            const tenSp = escapeHtml(it.ten_san_pham || '');
            const tenVariant = escapeHtml(it.ten_bien_the || '');
            const tenDv = escapeHtml(it.ten_don_vi || '');
            const tenFull = tenVariant ? `${tenSp} - ${tenVariant}` : tenSp;
            return `
                <tr>
                    <td><code>${escapeHtml(it.ma_vach || '')}</code></td>
                    <td>
                        <div style="font-weight:500">${tenFull}</div>
                        ${tenDv ? `<div class="small text-muted">ĐVT: ${tenDv}</div>` : ''}
                    </td>
                    <td class="text-end">${Number(it.so_luong_ton).toLocaleString('vi-VN')}</td>
                    <td class="text-end">${it.da_kiem ? Number(it.so_luong_thuc_te).toLocaleString('vi-VN') : '<span class="kkhs-lech-khong">—</span>'}</td>
                    <td class="text-end ${lClass}">${lText}</td>
                    <td class="text-end">${gvText}</td>
                </tr>
            `;
        }).join('');

    return `
        <div class="kkhs-detail-card">
            <div class="kkhs-detail-head">
                <div class="ma"><i class="fas fa-file-alt me-2"></i>${escapeHtml(p.ma_kiem_kho)}</div>
                <span class="badge ${p.trang_thai_badge || 'bg-light text-dark'}">${escapeHtml(p.trang_thai_label || '')}</span>
            </div>
            <div class="kkhs-detail-meta">
                <div>
                    <div class="label">Người tạo</div>
                    <div class="value">${escapeHtml(p.nguoi_tao || '—')}</div>
                </div>
                <div>
                    <div class="label">Ngày tạo</div>
                    <div class="value">${escapeHtml(p.created_at || '—')}</div>
                </div>
                <div>
                    <div class="label">Người cân bằng</div>
                    <div class="value">${escapeHtml(p.nguoi_can_bang || p.nguoi_tao || '—')}</div>
                </div>
                <div>
                    <div class="label">Ngày cân bằng</div>
                    <div class="value">${escapeHtml(p.hoan_thanh_luc || '—')}</div>
                </div>
            </div>

            <div class="table-responsive">
                <table class="kkhs-detail-table">
                    <thead>
                        <tr>
                            <th style="width:130px">Mã hàng</th>
                            <th>Tên hàng</th>
                            <th style="width:80px" class="text-end">Tồn kho</th>
                            <th style="width:80px" class="text-end">Thực tế</th>
                            <th style="width:80px" class="text-end">SL lệch</th>
                            <th style="width:130px" class="text-end">Giá trị lệch</th>
                        </tr>
                    </thead>
                    <tbody>${itemRows}</tbody>
                </table>
            </div>

            <div class="kkhs-detail-summary">
                <div class="kkhs-summary-item">
                    <div class="label">Tổng thực tế <span style="color:#9ca3af;font-weight:500">(${itemsCount})</span></div>
                    <div class="value">${slTt.toLocaleString('vi-VN')}</div>
                </div>
                <div class="kkhs-summary-item">
                    <div class="label">Tổng lệch tăng</div>
                    <div class="value ${slTang > 0 ? 'good' : ''}">${slTang > 0 ? '+' + slTang.toLocaleString('vi-VN') : '—'}</div>
                </div>
                <div class="kkhs-summary-item">
                    <div class="label">Tổng lệch giảm</div>
                    <div class="value ${slGiam > 0 ? 'bad' : ''}">${slGiam > 0 ? '-' + slGiam.toLocaleString('vi-VN') : '—'}</div>
                </div>
                <div class="kkhs-summary-item">
                    <div class="label">Tổng chênh lệch</div>
                    <div class="value ${gvLech > 0 ? 'good' : (gvLech < 0 ? 'bad' : '')}">${(gvLech >= 0 ? '+' : '-') + Math.abs(gvLech).toLocaleString('vi-VN') + ' ₫'}</div>
                </div>
            </div>

            <div class="kkhs-detail-footer">
                <div class="d-flex gap-2 flex-wrap">
                    <button class="btn btn-outline-danger btn-sm btn-huy-inline" data-id="${p.id}" ${p.trang_thai !== 'phieu_tam' ? 'disabled' : ''}>
                        <i class="fas fa-trash me-1"></i>Hủy
                    </button>
                    <button class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-copy me-1"></i>Sao chép
                    </button>
                    <button class="btn btn-outline-success btn-sm btn-xuat-inline" data-id="${p.id}">
                        <i class="fas fa-file-excel me-1"></i>Xuất file
                    </button>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-save me-1"></i>Lưu
                    </button>
                    <button class="btn btn-primary btn-sm">
                        <i class="fas fa-print me-1"></i>In
                    </button>
                </div>
            </div>
        </div>
    `;
}

// ============ Toggle expand inline ============
async function toggleRow(rowEl) {
    const id = rowEl.dataset.id;
    const expandRow = document.querySelector(`tr.kkhs-expand[data-expand-for="${id}"]`);
    const isOpen = rowEl.dataset.expanded === '1';
    const expandInner = expandRow.querySelector('.kkhs-expand-inner');

    document.querySelectorAll('tr.kkhs-row[data-expanded="1"]').forEach(r => {
        if (r === rowEl) return;
        r.dataset.expanded = '0';
        const otherId = r.dataset.id;
        const otherExpand = document.querySelector(`tr.kkhs-expand[data-expand-for="${otherId}"]`);
        if (otherExpand) otherExpand.style.display = 'none';
        r.querySelector('.toggle-icon')?.classList.replace('fa-chevron-up', 'fa-chevron-down');
    });

    if (isOpen) {
        expandRow.style.display = 'none';
        rowEl.dataset.expanded = '0';
        rowEl.querySelector('.toggle-icon')?.classList.replace('fa-chevron-up', 'fa-chevron-down');
        return;
    }

    expandRow.style.display = '';
    rowEl.dataset.expanded = '1';
    rowEl.querySelector('.toggle-icon')?.classList.replace('fa-chevron-down', 'fa-chevron-up');

    if (detailCache.has(id)) {
        expandInner.innerHTML = renderDetail(detailCache.get(id));
        return;
    }

    try {
        const res = await axios.get(url.detail(id));
        const p = res.data.data;
        if (!p) {
            expandInner.innerHTML = '<div class="text-center text-muted py-3">Không tìm thấy phiếu</div>';
            return;
        }
        detailCache.set(id, p);
        expandInner.innerHTML = renderDetail(p);
    } catch (e) {
        expandInner.innerHTML = '<div class="text-center text-danger py-3">Lỗi tải chi tiết: ' + escapeHtml(e.message) + '</div>';
    }
}

// ============ Pagination ============
function renderPagination() {
    const el = document.getElementById('phan-trang');
    if (lastPage <= 1) { el.innerHTML = ''; return; }
    let html = '<ul class="pagination pagination-sm mb-0">';
    html += `<li class="page-item ${currentPage === 1 ? 'disabled' : ''}"><a class="page-link" data-page="${currentPage - 1}" href="#"><i class="fas fa-chevron-left"></i></a></li>`;
    for (let i = 1; i <= lastPage; i++) {
        if (i === 1 || i === lastPage || (i >= currentPage - 1 && i <= currentPage + 1)) {
            html += `<li class="page-item ${i === currentPage ? 'active' : ''}"><a class="page-link" data-page="${i}" href="#">${i}</a></li>`;
        } else if (i === currentPage - 2 || i === currentPage + 2) {
            html += '<li class="page-item disabled"><span class="page-link">…</span></li>';
        }
    }
    html += `<li class="page-item ${currentPage === lastPage ? 'disabled' : ''}"><a class="page-link" data-page="${currentPage + 1}" href="#"><i class="fas fa-chevron-right"></i></a></li>`;
    html += '</ul>';
    el.innerHTML = html;
    el.querySelectorAll('[data-page]').forEach(a => {
        a.addEventListener('click', e => {
            e.preventDefault();
            const p = Number(a.dataset.page);
            if (p >= 1 && p <= lastPage) load(p);
        });
    });
}

// ============ Init events ============
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.kkhs-pill-tab').forEach(tab => {
        tab.addEventListener('click', e => {
            e.preventDefault();
            document.querySelectorAll('.kkhs-pill-tab').forEach(t => t.classList.remove('active'));
            tab.classList.add('active');
            activeTab = tab.dataset.tab;
            const map = { all: '', processing: 'phieu_tam', done: 'hoan_thanh' };
            activeStatus = map[activeTab] ?? '';
            document.querySelectorAll('.kkhs-status-chip').forEach(c => {
                c.classList.toggle('active', c.dataset.status === activeStatus);
            });
            load(1);
        });
    });

    document.querySelectorAll('.kkhs-status-chip').forEach(chip => {
        chip.addEventListener('click', () => {
            document.querySelectorAll('.kkhs-status-chip').forEach(c => c.classList.remove('active'));
            chip.classList.add('active');
            activeStatus = chip.dataset.status;
            const map = { '': 'all', 'phieu_tam': 'processing', 'hoan_thanh': 'done' };
            const newTab = map[activeStatus] ?? 'all';
            document.querySelectorAll('.kkhs-pill-tab').forEach(t => {
                t.classList.toggle('active', t.dataset.tab === newTab);
            });
            load(1);
        });
    });

    document.querySelectorAll('input[name="filter-ngay"]').forEach(r => {
        r.addEventListener('change', () => {
            document.querySelectorAll('#time-group label').forEach(l => l.classList.remove('checked'));
            r.closest('label').classList.add('checked');
            document.getElementById('ngay-tuy-chinh-range').style.display = (r.value === 'tuy_chinh' ? 'grid' : 'none');
            load(1);
        });
    });

    document.getElementById('filter-q').addEventListener('keydown', e => {
        if (e.key === 'Enter') load(1);
    });

    document.getElementById('btn-reload').addEventListener('click', () => {
        detailCache.clear();
        load(currentPage);
    });

    document.getElementById('btn-reset-filter').addEventListener('click', () => {
        document.querySelector('input[name="filter-ngay"][value="hom_nay"]').click();
        document.querySelectorAll('.kkhs-status-chip').forEach(c => {
            c.classList.toggle('active', c.dataset.status === '');
        });
        activeStatus = '';
        document.querySelectorAll('.kkhs-pill-tab').forEach(t => {
            t.classList.toggle('active', t.dataset.tab === 'all');
        });
        document.getElementById('filter-q').value = '';
        document.getElementById('filter-tu-ngay').value = '';
        document.getElementById('filter-den-ngay').value = '';
        load(1);
    });

    document.getElementById('btn-toggle-filter')?.addEventListener('click', () => {
        const f = document.querySelector('.kkhs-filter');
        if (!f) return;
        f.style.display = (f.style.display === 'none') ? '' : 'none';
    });

    // Xuất file (stub)
    document.getElementById('btn-xuat-file').addEventListener('click', () => {
        toastr.info('Chức năng xuất Excel đang được phát triển.');
    });

    document.getElementById('bang-kiem-kho').addEventListener('click', e => {
        if (e.target.closest('input[type=checkbox]') || e.target.closest('button') || e.target.closest('a')) return;
        if (e.target.closest('tr.kkhs-expand')) return;
        const row = e.target.closest('tr.kkhs-row');
        if (row) toggleRow(row);
    });

    document.addEventListener('click', e => {
        const btn = e.target.closest('.btn-huy-inline');
        if (!btn || btn.disabled) return;
        e.stopPropagation();
        const id = btn.dataset.id;
        Swal.fire({
            icon: 'warning', title: 'Hủy phiếu kiểm kho?',
            text: 'Phiếu sẽ chuyển sang trạng thái ĐÃ HỦY.',
            showCancelButton: true, confirmButtonColor: '#dc3545',
            confirmButtonText: 'Hủy phiếu', cancelButtonText: 'Đóng',
        }).then(async r => {
            if (!r.isConfirmed) return;
            try {
                const res = await axios.post(url.cancel(id));
                if (res.data.success) {
                    toastr.success(res.data.message);
                    detailCache.delete(id);
                    load(currentPage);
                } else { toastr.error(res.data.message); }
            } catch (err) { toastr.error(err.response?.data?.message || err.message); }
        });
    });

    document.addEventListener('click', e => {
        const btn = e.target.closest('.btn-xuat-inline');
        if (!btn) return;
        e.stopPropagation();
        toastr.info('Xuất file Excel đang được phát triển.');
    });

    load(1);
});
</script>
@endsection