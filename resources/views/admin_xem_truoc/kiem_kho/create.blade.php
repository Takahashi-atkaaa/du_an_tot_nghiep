@extends('admin_xem_truoc.layouts.admin')

@section('title', 'Tạo phiếu kiểm kho - SmartMart')

{{-- Tailwind CDN (chỉ load cho trang này) --}}
@push('styles')
<script src="https://cdn.tailwindcss.com"></script>
<style>
    /* ===== Tailwind mở rộng dùng cho kiểm kho ===== */
    body { font-family: 'Nunito', sans-serif; }

    /* Tab pills */
    .kk-tab {
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
    .kk-tab .badge {
        background: #e2e8f0; color: #475569;
        font-size: 10.5px; padding: 2px 7px;
        border-radius: 999px; font-weight: 700;
    }
    .kk-tab:hover { color: #1f2937; background: #f8fafc; }
    .kk-tab.active {
        background: linear-gradient(135deg, #4f46e5, #6366f1);
        color: #fff;
        box-shadow: 0 4px 10px rgba(79, 70, 229, .25);
    }
    .kk-tab.active .badge { background: rgba(255,255,255,.25); color: #fff; }

    /* ===== Yêu cầu 1: TOOLBAR FLEXBOX NGANG ===== */
    .kk-toolbar-flex {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
        background: #fff;
        padding: 12px 14px;
        border-radius: 12px;
        box-shadow: 0 2px 10px rgba(0,0,0,.04);
    }
    .kk-search-big {
        position: relative;
        flex: 1 1 360px;
        min-width: 280px;
    }
    .kk-search-big > i {
        position: absolute;
        left: 16px; top: 50%;
        transform: translateY(-50%);
        color: #94a3b8;
        font-size: 16px;
        pointer-events: none;
    }
    .kk-search-big > input {
        width: 100%;
        height: 48px;
        font-size: 16px;
        font-weight: 500;
        padding: 0 16px 0 46px;
        border-radius: 12px;
        border: 1px solid #e2e8f0;
        background: #fafbfc;
        color: #1f2937;
        transition: border-color .15s, box-shadow .15s;
    }
    .kk-search-big > input:focus {
        outline: none;
        border-color: #6366f1;
        background: #fff;
        box-shadow: 0 0 0 4px rgba(99, 102, 241, .15);
    }

    .kk-action-mini {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        height: 48px;
        padding: 0 16px;
        border-radius: 12px;
        font-weight: 600;
        font-size: 13px;
        border: 1px solid #e2e8f0;
        background: #fff;
        color: #1f2937;
        cursor: pointer;
        transition: all .15s;
        white-space: nowrap;
    }
    .kk-action-mini:hover { background: #f8fafc; border-color: #cbd5e1; }
    .kk-action-mini.success {
        background: linear-gradient(135deg, #10b981, #059669);
        color: #fff;
        border-color: transparent;
    }
    .kk-action-mini.success:hover { transform: translateY(-1px); box-shadow: 0 6px 14px rgba(16, 185, 129, .35); }
    .kk-action-mini.danger {
        background: #fff;
        color: #b91c1c;
        border-color: #fecaca;
    }
    .kk-action-mini.danger:hover { background: #fef2f2; }

    /* ===== Yêu cầu 2: BẢNG + ROW COLORING ===== */
    .kk-table-card {
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 2px 12px rgba(0,0,0,.05);
        overflow: hidden;
    }
    .kk-table { width: 100%; border-collapse: separate; border-spacing: 0; }
    .kk-table thead th {
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
    .kk-table tbody td {
        padding: 14px;
        border-bottom: 1px solid #f1f5f9;
        vertical-align: middle;
        font-size: 13.5px;
        color: #1f2937;
    }
    .kk-table tbody tr:last-child td { border-bottom: none; }
    .kk-table tbody tr:hover { background: #fafbff; }

    /* Hết hạn (đỏ nhạt) và Cận date (cam nhạt) — yêu cầu 2 */
    .kk-row-expired { background-color: #fef2f2 !important; }
    .kk-row-expired:hover { background-color: #fee2e2 !important; }
    .kk-row-near     { background-color: #fff7ed !important; }
    .kk-row-near:hover     { background-color: #ffedd5 !important; }

    .kk-code {
        font-family: 'JetBrains Mono', monospace;
        font-size: 11.5px;
        background: #f1f5f9;
        color: #475569;
        padding: 2px 6px;
        border-radius: 4px;
    }
    .kk-tname { font-weight: 600; color: #1f2937; margin-bottom: 2px; }
    .kk-tmeta { font-size: 11.5px; color: #94a3b8; }

    .kk-thucte-input {
        width: 110px;
        text-align: right;
        font-weight: 700;
        font-size: 15px;
        height: 40px;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 6px 12px;
        color: #1f2937;
    }
    .kk-thucte-input:focus {
        border-color: #4f46e5;
        outline: none;
        box-shadow: 0 0 0 3px rgba(79, 70, 229, .15);
    }

    .kk-hsd-pill {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 3px 9px;
        border-radius: 999px;
        font-size: 11.5px;
        font-weight: 600;
    }
    .kk-hsd-pill.red    { background: #fee2e2; color: #991b1b; }
    .kk-hsd-pill.orange { background: #ffedd5; color: #9a3412; }
    .kk-hsd-pill.gray   { background: #f1f5f9; color: #475569; }

    /* ===== Layout: dùng Tailwind thuần (wrapper flex flex-col lg:flex-row gap-6 items-start) ===== */
    /* Main: w-full lg:w-3/4 flex-1 */
    /* Sidebar: w-full lg:w:1/4 sticky top-4 bg-white rounded-lg shadow-md p-4 */

    /* Bảng cho phép cuộn ngang khi main bị hẹp */
    .kk-table-wrap {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        max-width: 100%;
    }
    .kk-table-wrap .kk-table { min-width: 880px; }

    /* Sidebar internals (giữ lại những class còn dùng) */
    .kk-scard {
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 2px 12px rgba(0,0,0,.05);
        overflow: hidden;
    }
    .kk-suserbar {
        display: flex; align-items: center; gap: 12px;
        padding: 14px 18px;
        background: linear-gradient(135deg, #eef2ff, #f5f3ff);
        border-bottom: 1px solid #eef2f7;
    }
    .kk-savatar {
        width: 44px; height: 44px;
        border-radius: 50%;
        background: linear-gradient(135deg, #4f46e5, #ec4899);
        color: #fff;
        font-weight: 700; font-size: 18px;
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0;
        text-transform: uppercase;
    }
    .kk-suser-name { font-weight: 700; color: #1f2937; font-size: 14px; }
    .kk-suser-date { font-size: 11.5px; color: #64748b; margin-top: 2px; }

    .kk-scode {
        padding: 14px 18px;
        background: #fff;
        border-bottom: 1px solid #f1f5f9;
    }
    .kk-scode-label {
        font-size: 11px; text-transform: uppercase;
        color: #6b7280; letter-spacing: .5px;
        font-weight: 600; margin-bottom: 4px;
    }
    .kk-scode-value {
        font-family: 'JetBrains Mono', monospace;
        font-size: 18px; font-weight: 700; color: #4338ca;
    }
    .kk-scard-b { padding: 14px 18px; }
    .kk-stats-row { display: flex; gap: 8px; margin-bottom: 12px; }
    .kk-stat-mini {
        flex: 1;
        background: #f8fbff;
        border-radius: 8px;
        padding: 10px 12px;
        border: 1px solid #eef2f7;
    }
    .kk-stat-mini .lbl {
        font-size: 10.5px; color: #6b7280;
        text-transform: uppercase; font-weight: 600;
        letter-spacing: .4px; margin-bottom: 4px;
    }
    .kk-stat-mini .val { font-size: 16px; font-weight: 700; color: #1f2937; }

    .kk-sfooter {
        padding: 12px;
        background: #fff;
        border-top: 1px solid #eee;
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 8px;
    }
    .kk-btn-primary {
        background: linear-gradient(135deg, #2563eb, #1d4ed8);
        color: #fff; border: none; padding: 10px;
        border-radius: 8px; font-weight: 600; font-size: 13.5px;
        cursor: pointer; width: 100%;
        display: inline-flex; align-items: center; justify-content: center; gap: 6px;
    }
    .kk-btn-success {
        background: linear-gradient(135deg, #16a34a, #15803d);
        color: #fff; border: none; padding: 10px;
        border-radius: 8px; font-weight: 600; font-size: 13.5px;
        cursor: pointer; width: 100%;
        display: inline-flex; align-items: center; justify-content: center; gap: 6px;
    }
    .kk-btn-primary:disabled, .kk-btn-success:disabled { opacity: .45; cursor: not-allowed; }

    .kk-recent { list-style: none; margin: 0; padding: 0; }
    .kk-recent-item {
        display: flex; align-items: center; gap: 8px;
        padding: 9px 6px; margin: 0 -6px;
        border-radius: 6px; cursor: pointer;
        transition: background .15s;
    }
    .kk-recent-item:hover { background: #f1f5ff; }
    .kk-recent-icon {
        width: 28px; height: 28px;
        border-radius: 6px;
        background: #dbeafe; color: #1d4ed8;
        display: flex; align-items: center; justify-content: center;
        font-size: 12px; flex-shrink: 0;
    }
    .kk-recent-body { min-width: 0; flex: 1; }
    .kk-recent-code {
        font-size: 12.5px; font-weight: 600; color: #1f2937;
        margin-bottom: 2px;
        overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
    }
    .kk-recent-meta {
        font-size: 11px; color: #94a3b8;
        display: flex; align-items: center; gap: 6px;
    }
    .kk-recent-meta .kk-recent-count {
        background: #e0e7ff; color: #4338ca;
        padding: 1px 7px; border-radius: 999px;
        font-weight: 600; font-size: 10.5px;
    }
    .kk-recent-empty {
        list-style: none; padding: 14px;
        text-align: center; color: #94a3b8;
        font-size: 12.5px; background: #fafbfc;
        border-radius: 8px; line-height: 1.5;
    }

    .kk-empty {
        text-align: center; padding: 60px 20px; color: #94a3b8;
    }
    .kk-empty i { font-size: 48px; margin-bottom: 12px; color: #cbd5e1; }

    .kk-edit-banner {
        background: #fffbeb;
        border: 1px solid #fcd34d;
        border-left: 4px solid #f59e0b;
        color: #78350f;
        padding: 10px 14px;
        border-radius: 10px;
        font-size: 13px;
        display: flex; align-items: center; gap: 8px;
    }

    /* Page head */
    .kk-page-head {
        display: flex; align-items: center; justify-content: space-between;
        gap: 12px;
    }
    .kk-page-title {
        font-size: 20px; font-weight: 700; margin: 0;
        display: flex; align-items: center; gap: 10px;
    }
    .kk-page-title i { color: #4f46e5; }
    .kk-breadcrumb .breadcrumb {
        font-size: 12.5px; padding: 0; background: transparent; margin-bottom: 4px;
    }
    .kk-cta {
        background: linear-gradient(135deg, #4f46e5, #6366f1);
        color: #fff; border: none;
        padding: 10px 20px;
        border-radius: 10px;
        font-weight: 600; font-size: 13.5px;
        display: inline-flex; align-items: center; gap: 6px;
        text-decoration: none;
        transition: transform .15s, box-shadow .15s;
    }
    .kk-cta:hover:not(:disabled) {
        transform: translateY(-1px);
        box-shadow: 0 6px 16px rgba(79, 70, 229, .35);
        color: #fff;
    }
    .kk-cta:disabled { opacity: .5; cursor: not-allowed; }

    .kk-page-foot {
        padding: 12px 16px;
        font-size: 12px;
        color: #94a3b8;
        background: #fafbfc;
        border-top: 1px solid #f1f5f9;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
</style>
@endpush

@section('content')
<div class="p-4 sm:p-6">

    {{-- 1. PHẦN TIÊU ĐỀ TRANG --}}
    <div class="mb-4 flex items-start justify-between gap-3 flex-wrap">
        <div>
            <nav aria-label="breadcrumb" class="kk-breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ url('admin/dashboard') }}">Admin</a></li>
                    <li class="breadcrumb-item"><a href="{{ url('admin/kho-hang') }}">Kho hàng</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('kiem-kho.history') }}">Kiểm kho</a></li>
                    <li class="breadcrumb-item active" id="kk-breadcrumb-active">Tạo phiếu</li>
                </ol>
            </nav>
            <h2 class="text-2xl font-bold text-slate-800 flex items-center gap-2 m-0">
                <i class="fas fa-clipboard-check text-indigo-500"></i>
                <span id="kk-page-title-text">Tạo phiếu kiểm kho</span>
            </h2>
            {{-- Banner trạng thái draft (nếu đang chỉnh sửa) --}}
            <div class="kk-edit-banner mt-2" id="kk-edit-banner" style="display:none">
                <i class="fas fa-info-circle"></i>
                Đang chỉnh sửa phiếu <b id="kk-edit-banner-ma">KK…</b>
                — đã lưu lúc <span id="kk-edit-banner-time">—</span>.
            </div>
        </div>
        <button class="kk-cta" id="btn-luu-phieu" disabled>
            <i class="fas fa-save"></i>
            <span id="kk-cta-text">Lưu phiếu</span>
        </button>
    </div>

    {{-- 2. WRAPPER CHIA 2 CỘT --}}
    <div class="flex flex-col lg:flex-row gap-6 items-start w-full">

        {{-- ================= CỘT TRÁI (75%) ================= --}}
        <div class="w-full lg:w-3/4 flex-1 bg-white rounded-lg shadow p-4 flex flex-col gap-4 min-w-0">

            {{-- Toolbar tìm kiếm + 3 action mini --}}
            <div class="kk-toolbar-flex">
                <div class="kk-search-big">
                    <i class="fas fa-search"></i>
                    <input
                        type="text"
                        id="search-input"
                        autocomplete="off"
                        placeholder="Quét mã vạch hoặc tìm tên sản phẩm..."
                        autofocus
                    >
                </div>
                <button class="kk-action-mini success" id="btn-import-excel" title="Thêm hàng loạt từ file Excel">
                    <i class="fas fa-file-excel"></i>
                    <span>Import</span>
                </button>
                <button class="kk-action-mini" id="btn-scan-device" title="Quét mã vạch bằng thiết bị">
                    <i class="fas fa-barcode"></i>
                    <span>Quét mã</span>
                </button>
                <button class="kk-action-mini" id="btn-choose-from-list" title="Mở danh sách hàng hóa">
                    <i class="fas fa-list"></i>
                    <span>Thêm sản phẩm</span>
                </button>
                <button class="kk-action-mini danger" id="btn-clear-all" title="Xóa tất cả">
                    <i class="fas fa-trash-alt"></i>
                </button>
            </div>

            {{-- Dải Tabs + Tự động kiểm --}}
            <div class="kk-main-head flex items-center flex-wrap gap-2">
                <div class="kk-pill-tabs flex bg-white rounded-xl p-1 gap-1 shadow-sm" id="kk-pill-tabs">
                    <a class="kk-tab active" data-tab="all">
                        Tất cả <span class="badge" id="cnt-all">0</span>
                    </a>
                    <a class="kk-tab" data-tab="chua-kiem">
                        Chưa kiểm <span class="badge" id="cnt-chua-kiem">0</span>
                    </a>
                    <a class="kk-tab" data-tab="khop">
                        Khớp <span class="badge" id="cnt-khop">0</span>
                    </a>
                    <a class="kk-tab" data-tab="lech">
                        Lệch <span class="badge" id="cnt-lech">0</span>
                    </a>
                    <a class="kk-tab" data-tab="can-date">
                        Cận date <span class="badge" id="cnt-can-date">0</span>
                    </a>
                    <a class="kk-tab" data-tab="het-han">
                        Hết hạn <span class="badge" id="cnt-het-han">0</span>
                    </a>
                </div>
                <button class="kk-action-mini" id="btn-tu-dong-kiem" title="Tự động kiểm tất cả lô tồn">
                    <i class="fas fa-magic text-warning"></i>
                    <span>Tự động kiểm</span>
                </button>
            </div>

            {{-- Bảng kiểm --}}
            <div class="kk-table-card">
                <div class="kk-table-wrap">
                    <table class="kk-table">
                        <thead>
                            <tr>
                                <th style="width:44px" class="text-center">#</th>
                                <th style="width:130px">Mã hàng</th>
                                <th>Tên hàng / Biến thể</th>
                                <th style="width:130px" class="text-center">HSD</th>
                                <th style="width:80px" class="text-end">Tồn kho</th>
                                <th style="width:130px" class="text-center">Thực tế</th>
                                <th style="width:90px" class="text-end">SL lệch</th>
                                <th style="width:140px" class="text-end">Giá trị lệch</th>
                                <th style="width:50px"></th>
                            </tr>
                        </thead>
                        <tbody id="kk-tbody">
                            <tr>
                                <td colspan="9">
                                    <div class="kk-empty">
                                        <i class="fas fa-box-open"></i>
                                        <div class="mt-2">Bấm <b>Thêm sản phẩm</b> hoặc quét mã vạch để bắt đầu.</div>
                                        <div class="small mt-1">Mẹo: nhấn <kbd>F3</kbd> để focus ô tìm kiếm.</div>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="kk-page-foot">
                    <span><i class="fas fa-circle-check text-success me-1"></i> Tự động <b>Lưu tạm</b> sau 5s không thao tác</span>
                    <span>Mã phiếu: <b id="sb-ma-kk-bottom" class="text-primary">KK—</b></span>
                </div>
            </div>

        </div>
        {{-- ================= KẾT THÚC CỘT TRÁI ================= --}}


        {{-- ================= CỘT PHẢI - SIDEBAR (25%) ================= --}}
        <div class="w-full lg:w-1/4 sticky top-4 flex flex-col gap-4 bg-white p-4 rounded-lg shadow-md border border-gray-100 self-start">

            {{-- Thông tin người tạo, Mã phiếu, Giờ tạo --}}
            <div class="flex items-center gap-3 p-3 rounded-md bg-gradient-to-r from-indigo-50 to-fuchsia-50">
                <div class="kk-savatar">{{ mb_strtoupper(mb_substr(auth()->user()?->ho_ten ?? 'U', 0, 1, 'UTF-8')) }}</div>
                <div class="flex-1 min-w-0">
                    <div class="text-sm font-bold text-slate-800 truncate">{{ auth()->user()?->ho_ten ?? 'Người dùng' }}</div>
                    <div class="text-xs text-slate-500 mt-0.5"><i class="far fa-clock mr-1"></i><span id="sb-ngay-tao">—</span></div>
                </div>
            </div>

            <div>
                <div class="text-[11px] uppercase text-slate-400 font-semibold tracking-wider mb-1">Mã phiếu kiểm kho</div>
                <div class="kk-scode-value" id="sb-ma-kk">KK00000</div>
            </div>

            {{-- Ô input Tổng dòng, Đã kiểm, Tổng lệch --}}
            <div class="flex gap-2">
                <div class="flex-1 bg-slate-50 border border-slate-200 rounded-md p-2">
                    <div class="text-[10.5px] text-slate-500 uppercase font-semibold tracking-wider mb-0.5">Tổng dòng</div>
                    <div class="text-base font-bold text-slate-800" id="sb-tong-dong">0</div>
                </div>
                <div class="flex-1 bg-slate-50 border border-slate-200 rounded-md p-2">
                    <div class="text-[10.5px] text-slate-500 uppercase font-semibold tracking-wider mb-0.5">Đã kiểm</div>
                    <div class="text-base font-bold text-slate-800" id="sb-da-kiem">0</div>
                </div>
            </div>

            <div>
                <label class="block text-[11px] uppercase text-slate-400 font-semibold tracking-wider mb-1">Tổng SL lệch</label>
                <input type="text" id="sb-sl-lech" value="0" readonly
                       class="w-full border border-slate-200 rounded p-2 bg-slate-50 text-sm font-semibold text-slate-700">
            </div>
            <div>
                <label class="block text-[11px] uppercase text-slate-400 font-semibold tracking-wider mb-1">Tổng GT lệch</label>
                <input type="text" id="sb-gia-tri-lech" value="0 ₫" readonly
                       class="w-full border border-slate-200 rounded p-2 bg-slate-50 text-sm font-semibold text-slate-700">
            </div>

            {{-- Textarea Ghi chú --}}
            <div>
                <label class="block text-[11px] uppercase text-slate-400 font-semibold tracking-wider mb-1">Ghi chú phiếu</label>
                <textarea
                    id="sb-ghi-chu"
                    rows="2"
                    placeholder="Ghi chú (không bắt buộc)"
                    class="w-full border border-slate-200 rounded p-2 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 outline-none"
                ></textarea>
            </div>

            {{-- Hai nút Lưu tạm + Hoàn thành (flex justify-between) --}}
            <div class="flex justify-between gap-2">
                <button class="kk-btn-primary flex-1" id="btn-luu-tam" disabled>
                    <i class="fas fa-save"></i> Lưu tạm
                </button>
                <button class="kk-btn-success flex-1" id="btn-hoan-thanh" disabled>
                    <i class="fas fa-check"></i> Hoàn thành
                </button>
            </div>

            {{-- Danh sách "Kiểm gần đây" --}}
            <div class="border-t border-slate-200 pt-3">
                <div class="flex items-center gap-2 px-1 py-1 text-[12px] font-bold uppercase tracking-wider text-slate-700 border-b border-slate-200 pb-2 mb-2">
                    <i class="fas fa-history text-indigo-500"></i> Kiểm gần đây
                </div>
                <ul class="kk-recent" id="kk-recent-list">
                    <li class="kk-recent-empty">
                        Chưa có hàng hóa được chọn.<br>
                        Thêm hàng hóa vào phiếu kiểm kho sẽ hiển thị ở đây.
                    </li>
                </ul>
            </div>

        </div>
        {{-- ================= KẾT THÚC CỘT PHẢI ================= --}}

    </div>
    {{-- /Kết thúc Wrapper 2 cột --}}
</div>
{{-- /Kết thúc p-4 sm:p-6 wrapper --}}

{{-- Modal xác nhận --}}
<div class="modal fade" id="modal-xac-nhan" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Xác nhận cân bằng kho</h5>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="mb-2">Hệ thống sẽ tự động tạo phiếu nhập/xuất dựa trên lệch:</p>
                <ul class="mb-2">
                    <li>Lệch <b class="text-danger">âm</b> (mất hàng) → tạo <b>phiếu xuất hủy</b></li>
                    <li>Lệch <b class="text-success">dương</b> (thừa hàng) → tạo <b>phiếu nhập kiểm kê</b></li>
                </ul>
                <p class="mb-0 small text-muted">Sau khi hoàn thành, phiếu <b>không thể chỉnh sửa</b>.</p>
            </div>
            <div class="modal-footer">
                <button class="btn btn-light" data-bs-dismiss="modal">Hủy</button>
                <button class="btn btn-success" id="btn-xac-nhan-hoan-thanh">
                    <i class="fas fa-check me-1"></i>Xác nhận
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
const state = {
    phieu: null,
    items: [],
    activeTab: 'all',
    mode: 'new',
    autoSaveTimer: null,
};

const url = {
    search:    '/admin/api/kiem-kho/search',
    draftGet:  '/admin/api/kiem-kho/draft',
    draftPost: '/admin/api/kiem-kho/draft',
    show:      id => `/admin/api/kiem-kho/${id}`,
    balance:   id => `/admin/api/kiem-kho/${id}/balance`,
    listStock: '/admin/api/lo-hang',
};

const fmtVND = n => (Number(n)||0).toLocaleString('vi-VN') + ' ₫';
const fmtNum  = n => (Number(n)||0).toLocaleString('vi-VN');

function escHtml(s) {
    return (s ?? '').toString()
        .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;').replace(/'/g, '&#39;');
}

// ===== Helpers cho cận date / hết hạn (yêu cầu 2) =====
function getHsdStatus(hsdStr) {
    if (!hsdStr) return { state: 'unknown', days: null };
    const hsd = new Date(hsdStr);
    if (isNaN(hsd.getTime())) return { state: 'unknown', days: null };
    const now = new Date();
    now.setHours(0, 0, 0, 0);
    hsd.setHours(0, 0, 0, 0);
    const diff = Math.round((hsd - now) / 86400000);
    if (diff < 0)  return { state: 'expired', days: diff };
    if (diff <= 7) return { state: 'near',    days: diff };
    return { state: 'ok', days: diff };
}

function recomputeItem(i) {
    const it = state.items[i];
    if (!it) return;
    const ton = Number(it.so_luong_ton) || 0;
    const tt = it.so_luong_thuc_te;
    if (tt === null || tt === undefined || tt === '') {
        it.so_luong_lech = 0;
        it.gia_tri_lech = 0;
    } else {
        const tn = Number(tt) || 0;
        it.so_luong_lech = tn - ton;
        it.gia_tri_lech = it.so_luong_lech * (Number(it.gia_von) || 0);
    }
}

function isChecked(it) {
    return it.so_luong_thuc_te !== null && it.so_luong_thuc_te !== undefined && it.so_luong_thuc_te !== '';
}

function applyFilters() {
    return state.items.filter(it => {
        const ck = isChecked(it);
        const l  = Number(it.so_luong_lech) || 0;
        const hsdStatus = getHsdStatus(it.han_su_dung);

        switch (state.activeTab) {
            case 'all':         return true;
            case 'chua-kiem':   return !ck;
            case 'khop':        return ck && l === 0;
            case 'lech':        return ck && l !== 0;
            case 'can-date':    return hsdStatus.state === 'near';
            case 'het-han':     return hsdStatus.state === 'expired';
            default:            return true;
        }
    });
}

function renderTable() {
    const tbody = document.getElementById('kk-tbody');
    const filtered = applyFilters();
    if (filtered.length === 0) {
        tbody.innerHTML = `<tr><td colspan="9"><div class="kk-empty">
            <i class="fas fa-box-open"></i>
            <div class="mt-2">${
                state.items.length === 0
                    ? 'Bấm "Thêm sản phẩm" hoặc quét mã vạch để bắt đầu.'
                    : 'Không có dòng nào thuộc tab này.'
            }</div>
        </div></td></tr>`;
        return;
    }

    tbody.innerHTML = filtered.map(it => {
        const idx = state.items.indexOf(it);
        const tt = isChecked(it) ? it.so_luong_thuc_te : '';
        const l  = Number(it.so_luong_lech) || 0;
        const gv = Number(it.gia_tri_lech) || 0;
        const ck = isChecked(it);

        // SL Lệch class — đỏ đậm + font-bold nếu khác 0
        const lClass = !ck ? 'kk-diff-zero'
            : (l > 0 ? 'kk-diff-positive' : (l < 0 ? 'kk-diff-negative' : 'kk-diff-zero'));
        const lText = !ck ? '—'
            : (l === 0 ? '0' : (l > 0 ? '+' + fmtNum(l) : fmtNum(l)));
        const gvText = !ck ? '—'
            : (gv === 0 ? fmtVND(0) : (gv < 0 ? '-' : '+') + fmtVND(Math.abs(gv)));

        // HSD pill — yêu cầu 2
        const hsdStatus = getHsdStatus(it.han_su_dung);
        let hsdPillHtml = '';
        if (it.han_su_dung) {
            const hsdDate = it.han_su_dung.substring(0, 10);
            let pillCls = 'gray', iconCls = '', txt = hsdDate;
            if (hsdStatus.state === 'expired') {
                pillCls = 'red';
                txt = `Hết hạn ${Math.abs(hsdStatus.days)} ngày`;
            } else if (hsdStatus.state === 'near') {
                pillCls = 'orange';
                txt = `Cận date (${hsdStatus.days} ngày)`;
            }
            hsdPillHtml = `<span class="kk-hsd-pill ${pillCls}">${txt}</span>`;
        } else {
            hsdPillHtml = '<span class="text-muted">—</span>';
        }

        // ===== YÊU CẦU 2: row bg classes =====
        let rowBg = '';
        if (hsdStatus.state === 'expired') rowBg = 'kk-row-expired';
        else if (hsdStatus.state === 'near') rowBg = 'kk-row-near';

        const maLo = it.ma_lo ? `Lô ${escHtml(it.ma_lo)}` : '';
        const lTextClass = (ck && l !== 0) ? 'text-red-600 font-bold' : '';

        return `
        <tr data-idx="${idx}" class="${rowBg}">
            <td class="text-center text-muted">${idx + 1}</td>
            <td><span class="kk-code">${escHtml(it.ma_vach || '')}</span></td>
            <td>
                <div class="kk-tname">${escHtml(it.ten_san_pham || '')}</div>
                <div class="kk-tmeta">${escHtml(it.ten_bien_the || '')} • ${escHtml(it.ten_don_vi || 'cái')}</div>
            </td>
            <td class="text-center small">
                ${maLo ? '<div class="mb-1 text-muted">' + maLo + '</div>' : ''}
                ${hsdPillHtml}
            </td>
            <td class="text-end kk-value-strong">${fmtNum(it.so_luong_ton)}</td>
            <td class="text-center">
                <input type="number" min="0" class="kk-thucte-input"
                    data-idx="${idx}" value="${tt}" placeholder="—"
                    onfocus="this.select()">
            </td>
            <td class="text-end ${lTextClass}">${lText}</td>
            <td class="text-end ${lClass}">${gvText}</td>
            <td class="text-center">
                <span class="kk-trash" data-remove="${idx}" title="Xóa dòng">
                    <i class="fas fa-trash"></i>
                </span>
            </td>
        </tr>`;
    }).join('');
}

function updateCounts() {
    let all = state.items.length;
    let khop = 0, lech = 0, chuaKiem = 0, canDate = 0, hetHan = 0;
    for (const it of state.items) {
        const ck = isChecked(it);
        const l = Number(it.so_luong_lech) || 0;
        if (!ck) chuaKiem++;
        else if (l === 0) khop++;
        else lech++;

        const s = getHsdStatus(it.han_su_dung);
        if (s.state === 'near')    canDate++;
        else if (s.state === 'expired') hetHan++;
    }
    const set = (id, n) => { const el = document.getElementById(id); if (el) el.textContent = n; };
    set('cnt-all', all);
    set('cnt-chua-kiem', chuaKiem);
    set('cnt-khop', khop);
    set('cnt-lech', lech);
    set('cnt-can-date', canDate);
    set('cnt-het-han', hetHan);
}

function updateSidebarStats() {
    let slTt = 0, slLe = 0, gtLe = 0, daKiem = 0;
    for (const it of state.items) {
        if (isChecked(it)) {
            slTt += Number(it.so_luong_thuc_te) || 0;
            daKiem++;
        }
        slLe += Number(it.so_luong_lech) || 0;
        gtLe += Number(it.gia_tri_lech) || 0;
    }

    document.getElementById('sb-tong-dong').textContent = state.items.length;
    document.getElementById('sb-da-kiem').textContent = daKiem;

    const slLeEl = document.getElementById('sb-sl-lech');
    slLeEl.value = (slLe > 0 ? '+' : '') + fmtNum(slLe);
    slLeEl.className = 'form-control ' + (slLe > 0 ? 'text-success' : (slLe < 0 ? 'text-danger' : ''));

    const gtLeEl = document.getElementById('sb-gia-tri-lech');
    gtLeEl.value = fmtVND(gtLe);
    gtLeEl.className = 'form-control ' + (gtLe > 0 ? 'text-success' : (gtLe < 0 ? 'text-danger' : ''));

    const has = state.items.length > 0;
    const allChecked = has && state.items.every(isChecked);
    document.getElementById('btn-luu-phieu').disabled = !has;
    document.getElementById('btn-luu-tam').disabled = !has;
    document.getElementById('btn-hoan-thanh').disabled = !allChecked;
}

function rerender() {
    state.items.forEach((_, i) => recomputeItem(i));
    renderTable();
    updateCounts();
    updateSidebarStats();
    renderRecent();
}

async function searchAndAdd(q) {
    try {
        const res = await axios.get(url.search, {params: {q}});
        const data = res.data.data || [];
        if (data.length === 0) {
            Swal.fire({icon:'warning', title:'Không tìm thấy', text:`Không có lô tồn cho "${q}".`, timer: 2000});
            return;
        }
        const existing = new Set(state.items.map(x => x.id_chi_tiet_lo_hang));
        let added = 0;
        for (const row of data) {
            if (existing.has(row.id_chi_tiet_lo_hang)) continue;
            state.items.push({
                id_chi_tiet_lo_hang: row.id_chi_tiet_lo_hang,
                variant_id: row.variant_id,
                ma_vach: row.ma_vach,
                ten_san_pham: row.ten_san_pham,
                ten_bien_the: row.ten_bien_the,
                ten_don_vi: row.don_vi || 'cái',
                han_su_dung: row.han_su_dung,
                ma_lo: row.ma_lo,
                so_luong_ton: row.so_luong_ton,
                so_luong_thuc_te: null,
                so_luong_lech: 0,
                gia_von: row.gia_von,
                gia_tri_lech: 0,
            });
            added++;
        }
        if (added === 0) {
            Swal.fire({icon:'info', title:'Đã có', text:'Tất cả lô của mã này đã có trong phiếu.', timer: 1800});
        } else {
            rerender();
            scheduleAutoSave();
        }
    } catch (e) {
        console.error(e);
        Swal.fire({icon:'error', title:'Lỗi', text:e.message});
    }
}

async function autoCheckAll() {
    try {
        const res = await axios.get(url.listStock, {params: {per_page: 500}});
        const list = res.data?.data?.data || res.data?.data || [];
        if (!list.length) {
            Swal.fire({icon:'info', title:'Không có hàng tồn', text:'Không có lô hàng tồn kho.'});
            return;
        }
        const existing = new Set(state.items.map(x => x.id_chi_tiet_lo_hang));
        let added = 0;
        for (const row of list) {
            if (!row.so_luong_ton || Number(row.so_luong_ton) <= 0) continue;
            if (existing.has(row.id)) continue;
            state.items.push({
                id_chi_tiet_lo_hang: row.id,
                variant_id: row.variant_id,
                ma_vach: row.variant?.ma_vach || '',
                ten_san_pham: row.variant?.product?.ten_san_pham || '',
                ten_bien_the: row.variant?.ten_bien_the || '',
                ten_don_vi: row.variant?.ten_bien_the || 'cái',
                han_su_dung: row.han_su_dung,
                ma_lo: row.lo_hang?.ma_lo || null,
                so_luong_ton: row.so_luong_ton,
                so_luong_thuc_te: row.so_luong_ton,
                so_luong_lech: 0,
                gia_von: row.gia_nhap || 0,
                gia_tri_lech: 0,
            });
            added++;
        }
        if (!added) {
            Swal.fire({icon:'info', title:'Đã có', text:'Tất cả lô tồn đã có trong phiếu.'});
            return;
        }
        rerender();
        scheduleAutoSave();
        toastr.success(`Đã thêm ${added} dòng`);
    } catch (e) {
        toastr.error('Lỗi: ' + e.message);
    }
}

async function saveDraft(silent = false) {
    if (!state.items.length) {
        if (!silent) toastr.warning('Chưa có sản phẩm nào.');
        return;
    }
    const payload = {
        ghi_chu: document.getElementById('sb-ghi-chu').value,
        items: state.items.map(it => ({
            id_chi_tiet_lo_hang: it.id_chi_tiet_lo_hang,
            variant_id: it.variant_id,
            ma_vach: it.ma_vach,
            ten_san_pham: it.ten_san_pham,
            ten_bien_the: it.ten_bien_the,
            ten_don_vi: it.ten_don_vi,
            han_su_dung: it.han_su_dung,
            ma_lo: it.ma_lo,
            so_luong_ton: it.so_luong_ton,
            so_luong_thuc_te: it.so_luong_thuc_te,
            gia_von: it.gia_von,
        })),
    };
    try {
        const res = await axios.post(url.draftPost, payload);
        if (res.data.success) {
            const wasNew = state.mode === 'new';
            state.phieu = res.data.data;
            if (state.phieu) {
                if (wasNew) switchMode('edit', state.phieu);
                else refreshEditDisplay(state.phieu);
            }
            if (!silent) {
                toastr.success(wasNew ? 'Đã tạo phiếu kiểm kho' : 'Đã lưu nháp');
            }
        }
    } catch (e) {
        console.error(e);
        if (!silent) toastr.error('Lỗi: ' + (e.response?.data?.message || e.message));
    }
}

function scheduleAutoSave() {
    clearTimeout(state.autoSaveTimer);
    state.autoSaveTimer = setTimeout(() => saveDraft(true), 5000);
}

function switchMode(mode, draftData = null) {
    state.mode = mode;
    const banner = document.getElementById('kk-edit-banner');
    if (mode === 'edit') {
        document.getElementById('kk-page-title-text').textContent = 'Chỉnh sửa phiếu kiểm kho';
        document.getElementById('kk-breadcrumb-active').textContent = 'Chỉnh sửa';
        document.getElementById('kk-cta-text').textContent = 'Lưu tạm';
        if (draftData) {
            document.getElementById('kk-edit-banner-ma').textContent = draftData.ma_kiem_kho || '';
            document.getElementById('kk-edit-banner-time').textContent = draftData.created_at || '';
            document.getElementById('sb-ma-kk').textContent = draftData.ma_kiem_kho || '—';
            document.getElementById('sb-ngay-tao').textContent = draftData.created_at || '—';
            document.getElementById('sb-ma-kk-bottom').textContent = draftData.ma_kiem_kho || '—';
        }
        banner.style.display = 'flex';
    } else {
        document.getElementById('kk-page-title-text').textContent = 'Tạo phiếu kiểm kho';
        document.getElementById('kk-breadcrumb-active').textContent = 'Tạo phiếu';
        document.getElementById('kk-cta-text').textContent = 'Lưu phiếu';
        banner.style.display = 'none';
    }
}

function refreshEditDisplay(phieu) {
    document.getElementById('kk-edit-banner-ma').textContent = phieu.ma_kiem_kho || '';
    document.getElementById('kk-edit-banner-time').textContent = phieu.created_at || '';
    document.getElementById('sb-ma-kk').textContent = phieu.ma_kiem_kho || '—';
    document.getElementById('sb-ma-kk-bottom').textContent = phieu.ma_kiem_kho || '—';
}

async function loadDraft() {
    const params = new URLSearchParams(window.location.search);

    // Phan biet 2 luong vao:
    //   ?edit={id}  -> click "Chinh sua" tu trang show (footer), load phiếu cu the
    //   ?mode=new   -> click "Tao phieu kiem kho" o history, luon tao moi
    //   khong co QS  -> tuong thich nguoc: load draft tam cua user (cũ)
    const editId = params.get('edit');
    const mode = params.get('mode');

    // 1) ?edit={id}: load phiếu cu the (luong tu trang show)
    if (editId) {
        try {
            const res = await axios.get(url.show(editId));
            if (res.data.success && res.data.data) {
                state.phieu = res.data.data;
                state.items = res.data.data.items || [];
                switchMode('edit', state.phieu);
                document.getElementById('sb-ghi-chu').value = state.phieu.ghi_chu || '';
            } else {
                toastr.error(res.data.message || 'Không tải được phiếu.');
                switchMode('new');
            }
        } catch (e) {
            console.error(e);
            toastr.error('Lỗi khi tải phiếu: ' + (e.message || ''));
            switchMode('new');
        }
        rerender();
        setTimeout(() => {
            const s = document.getElementById('search-input');
            if (s) s.focus();
        }, 50);
        return;
    }

    // 2) ?mode=new: luon tao moi, KHONG load draft cu
    if (mode === 'new') {
        switchMode('new');
        try {
            const res = await axios.get(url.draftGet);
            const preview = res.data.ma_kiem_kho_preview || 'KK—';
            document.getElementById('sb-ma-kk-bottom').textContent = preview;
            document.getElementById('sb-ngay-tao').textContent = new Date().toLocaleString('vi-VN');
        } catch (e) {
            console.error(e);
        }
        rerender();
        setTimeout(() => {
            const s = document.getElementById('search-input');
            if (s) s.focus();
        }, 50);
        return;
    }

    // 3) Khong co query string: giu nguyen logic cu - load draft tam cua user
    try {
        const res = await axios.get(url.draftGet);
        if (res.data.success && res.data.data) {
            state.phieu = res.data.data;
            state.items = res.data.data.items || [];
            switchMode('edit', state.phieu);
            document.getElementById('sb-ghi-chu').value = state.phieu.ghi_chu || '';
        } else {
            switchMode('new');
            const preview = res.data.ma_kiem_kho_preview || 'KK—';
            document.getElementById('sb-ma-kk-bottom').textContent = preview;
            document.getElementById('sb-ngay-tao').textContent = new Date().toLocaleString('vi-VN');
        }
        rerender();
        // Yêu cầu 1: focus ô search ngay khi vào trang
        setTimeout(() => {
            const s = document.getElementById('search-input');
            if (s) s.focus();
        }, 50);
    } catch (e) {
        console.error(e);
        switchMode('new');
    }
}

function renderRecent() {
    const list = document.getElementById('kk-recent-list');
    if (!list) return;
    const items = state.items.slice(-5).reverse();
    if (items.length === 0) {
        list.innerHTML = '<li class="kk-recent-empty">'
            + 'Chưa có hàng hóa được chọn.<br>'
            + 'Thêm hàng hóa vào phiếu kiểm kho sẽ hiển thị ở đây.'
            + '</li>';
        return;
    }
    list.innerHTML = items.map((it, i) => {
        const idx = state.items.indexOf(it);
        const ten = it.ten_san_pham || it.ten_bien_the || '(chưa đặt tên)';
        const ma = it.ma_vach || it.id_chi_tiet_lo_hang;
        const count = i + 1;
        const ck = isChecked(it);
        return `<li class="kk-recent-item" data-idx="${idx}">
            <div class="kk-recent-icon"><i class="fas fa-pen"></i></div>
            <div class="kk-recent-body">
                <div class="kk-recent-code">${escHtml(ten)}</div>
                <div class="kk-recent-meta">
                    <span class="kk-recent-count">(${count})</span>
                    <span>${escHtml(ma || '')}</span>
                    ${ck ? '<i class="fas fa-check-circle text-success"></i>' : '<i class="far fa-circle text-muted"></i>'}
                </div>
            </div>
        </li>`;
    }).join('');
    list.querySelectorAll('.kk-recent-item').forEach(li => {
        li.addEventListener('click', () => focusItemRow(Number(li.dataset.idx)));
    });
}

function focusItemRow(idx) {
    const input = document.querySelector(`#kk-tbody input.kk-thucte-input[data-idx="${idx}"]`);
    if (!input) return;
    const row = input.closest('tr');
    row?.scrollIntoView({ behavior: 'smooth', block: 'center' });
    setTimeout(() => input.focus({ preventScroll: true }), 350);
}

async function balanceNow() {
    if (!state.phieu) {
        await saveDraft(true);
        if (!state.phieu) { toastr.error('Phiếu rỗng.'); return; }
    }
    const modal = new bootstrap.Modal(document.getElementById('modal-xac-nhan'));
    modal.show();
}

document.addEventListener('DOMContentLoaded', () => {
    const searchInput = document.getElementById('search-input');
    searchInput.addEventListener('keydown', e => {
        if (e.key === 'Enter') {
            e.preventDefault();
            const q = searchInput.value.trim();
            if (q) { searchAndAdd(q); searchInput.value = ''; }
        }
    });
    document.addEventListener('keydown', e => {
        if (e.key === 'F3') { e.preventDefault(); searchInput.focus(); }
    });

    document.querySelectorAll('.kk-tab').forEach(tab => {
        tab.addEventListener('click', e => {
            e.preventDefault();
            document.querySelectorAll('.kk-tab').forEach(t => t.classList.remove('active'));
            tab.classList.add('active');
            state.activeTab = tab.dataset.tab;
            renderTable();
        });
    });

    document.getElementById('kk-tbody').addEventListener('input', e => {
        const t = e.target;
        if (t.classList.contains('kk-thucte-input')) {
            const i = Number(t.dataset.idx);
            const v = t.value;
            state.items[i].so_luong_thuc_te = (v === '' ? null : Number(v));
            recomputeItem(i);
            const tr = t.closest('tr');
            const it = state.items[i];
            const ck = isChecked(it);
            const l  = Number(it.so_luong_lech) || 0;
            const gv = Number(it.gia_tri_lech) || 0;
            const lClass = !ck ? 'kk-diff-zero'
                : (l > 0 ? 'kk-diff-positive' : (l < 0 ? 'kk-diff-negative' : 'kk-diff-zero'));
            const lText = !ck ? '—' : (l === 0 ? '0' : (l > 0 ? '+' + fmtNum(l) : fmtNum(l)));
            const gvText = !ck ? '—' : (gv === 0 ? fmtVND(0) : (gv < 0 ? '-' : '+') + fmtVND(Math.abs(gv)));

            const lTextClass = (ck && l !== 0) ? 'text-red-600 font-bold' : '';

            tr.children[6].textContent = lText;
            tr.children[6].className = 'text-end ' + lTextClass;
            tr.children[7].textContent = gvText;
            tr.children[7].className = 'text-end ' + lClass;
            updateCounts();
            updateSidebarStats();
            scheduleAutoSave();
        }
    });

    document.getElementById('kk-tbody').addEventListener('click', e => {
        const rm = e.target.closest('[data-remove]');
        if (!rm) return;
        const i = Number(rm.dataset.remove);
        Swal.fire({
            icon: 'question',
            title: 'Xóa dòng này?',
            text: state.items[i]?.ten_san_pham,
            showCancelButton: true,
            confirmButtonText: 'Xóa',
            cancelButtonText: 'Hủy',
        }).then(r => {
            if (r.isConfirmed) {
                state.items.splice(i, 1);
                rerender();
                scheduleAutoSave();
            }
        });
    });

    // Top action bar (Yêu cầu 1)
    document.getElementById('btn-import-excel')?.addEventListener('click', () => {
        toastr.info('Chức năng nhập Excel sẽ được phát triển ở phiên bản sau.');
    });
    document.getElementById('btn-scan-device')?.addEventListener('click', () => {
        toastr.info('Chức năng quét thiết bị sẽ được phát triển ở phiên bản sau.');
    });
    document.getElementById('btn-choose-from-list')?.addEventListener('click', () => {
        searchInput.focus();
        searchInput.scrollIntoView({ behavior: 'smooth', block: 'center' });
    });
    document.getElementById('btn-clear-all').addEventListener('click', () => {
        if (!state.items.length) return;
        Swal.fire({
            icon:'warning',
            title:'Xóa tất cả?',
            text:'Hành động này sẽ xóa tất cả các dòng đang kiểm.',
            showCancelButton: true,
            confirmButtonText:'Xóa hết',
            cancelButtonText:'Hủy',
            confirmButtonColor:'#dc3545',
        }).then(r => {
            if (r.isConfirmed) {
                state.items = [];
                rerender();
                scheduleAutoSave();
            }
        });
    });

    document.getElementById('btn-luu-tam')?.addEventListener('click', () => saveDraft(false));
    document.getElementById('btn-luu-phieu')?.addEventListener('click', () => saveDraft(false));
    document.getElementById('btn-hoan-thanh')?.addEventListener('click', () => balanceNow());
    document.getElementById('btn-xac-nhan-hoan-thanh')?.addEventListener('click', async () => {
        bootstrap.Modal.getInstance(document.getElementById('modal-xac-nhan')).hide();
        try {
            const res = await axios.post(url.balance(state.phieu.id));
            if (res.data.success) {
                toastr.success(res.data.message || 'Cân bằng thành công');
                setTimeout(() => location.href = '/admin/kho-hang/kiem-kho/lich-su', 800);
            } else {
                toastr.error(res.data.message || 'Cân bằng thất bại');
            }
        } catch (e) {
            toastr.error(e.response?.data?.message || e.message);
        }
    });

    document.getElementById('btn-tu-dong-kiem').addEventListener('click', () => {
        if (state.items.length > 0) {
            Swal.fire({
                icon:'warning',
                title:'Đã có hàng trong phiếu',
                text:'Tự động kiểm sẽ bổ sung các lô chưa có. Tiếp tục?',
                showCancelButton: true,
                confirmButtonText:'Tiếp tục',
                cancelButtonText:'Hủy',
            }).then(r => { if (r.isConfirmed) autoCheckAll(); });
        } else {
            autoCheckAll();
        }
    });

    loadDraft();
});
</script>
@endsection