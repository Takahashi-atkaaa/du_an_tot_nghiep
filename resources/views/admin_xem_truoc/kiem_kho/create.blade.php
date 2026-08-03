@extends('admin_xem_truoc.layouts.admin')

@section('title', 'Tạo phiếu kiểm kho - SmartMart')

@section('styles')
<style>
    /* ===== Tab pills (Bootstrap-5 friendly) ===== */
    .kk-tab {
        padding: 6px 12px;
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
        background: #f1f5f9;
        border: 1px solid transparent;
    }
    .kk-tab .badge {
        background: #e2e8f0; color: #475569;
        font-size: 10.5px; padding: 2px 7px;
        border-radius: 999px; font-weight: 700;
    }
    .kk-tab:hover { color: #1f2937; background: #e2e8f0; }
    .kk-tab.active {
        background: linear-gradient(135deg, #4f46e5, #6366f1);
        color: #fff;
        box-shadow: 0 4px 10px rgba(79, 70, 229, .25);
        border-color: transparent;
    }
    .kk-tab.active .badge { background: rgba(255,255,255,.25); color: #fff; }

    /* ===== Toolbar tìm kiếm + 4 action mini ===== */
    .kk-toolbar-flex {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
    }
    .kk-search-big {
        position: relative;
        flex: 1 1 320px;
        min-width: 260px;
    }
    .kk-search-big > i.fa-search {
        position: absolute;
        left: 14px; top: 50%;
        transform: translateY(-50%);
        color: #94a3b8;
        font-size: 15px;
        pointer-events: none;
    }
    .kk-search-big > input.form-control {
        height: 42px;
        font-size: 15px;
        font-weight: 500;
        padding-left: 40px;
    }

    .kk-action-mini {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        height: 42px;
        padding: 0 14px;
        border-radius: 8px;
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
    .kk-action-mini.success:hover { transform: translateY(-1px); box-shadow: 0 6px 14px rgba(16, 185, 129, .35); color: #fff; }
    .kk-action-mini.danger {
        background: #fff;
        color: #b91c1c;
        border-color: #fecaca;
    }
    .kk-action-mini.danger:hover { background: #fef2f2; }

    /* ===== Bảng kiểm (Bootstrap native) ===== */
    .kk-table thead th {
        background: #f8fafc;
        color: #64748b;
        font-size: 11.5px;
        text-transform: uppercase;
        letter-spacing: .5px;
        font-weight: 700;
        white-space: nowrap;
    }
    .kk-table tbody td { vertical-align: middle; font-size: 13.5px; color: #1f2937; }

    /* Hết hạn / cận date row coloring */
    .kk-row-expired { background-color: #fef2f2 !important; }
    .kk-row-expired:hover { background-color: #fee2e2 !important; }
    .kk-row-near { background-color: #fff7ed !important; }
    .kk-row-near:hover { background-color: #ffedd5 !important; }

    .kk-code {
        font-family: 'JetBrains Mono', 'Consolas', monospace;
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
    .kk-hsd-pill.red { background: #fee2e2; color: #991b1b; }
    .kk-hsd-pill.orange { background: #ffedd5; color: #9a3412; }
    .kk-hsd-pill.gray { background: #f1f5f9; color: #475569; }

    /* ===== Sidebar phải ===== */
    .kk-scard {
        background: #fff;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0,0,0,.05);
        overflow: hidden;
    }
    .kk-suserbar {
        display: flex; align-items: center; gap: 12px;
        padding: 12px 14px;
        background: linear-gradient(135deg, #eef2ff, #f5f3ff);
        border-bottom: 1px solid #eef2f7;
    }
    .kk-savatar {
        width: 40px; height: 40px;
        border-radius: 50%;
        background: linear-gradient(135deg, #4f46e5, #ec4899);
        color: #fff;
        font-weight: 700; font-size: 16px;
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0;
        text-transform: uppercase;
    }
    .kk-suser-name { font-weight: 700; color: #1f2937; font-size: 13.5px; }
    .kk-suser-date { font-size: 11.5px; color: #64748b; margin-top: 2px; }

    .kk-scode {
        padding: 12px 14px;
        background: #fff;
        border-bottom: 1px solid #f1f5f9;
    }
    .kk-scode-label {
        font-size: 11px; text-transform: uppercase;
        color: #6b7280; letter-spacing: .5px;
        font-weight: 600; margin-bottom: 4px;
    }
    .kk-scode-value {
        font-family: 'JetBrains Mono', 'Consolas', monospace;
        font-size: 17px; font-weight: 700; color: #4338ca;
    }

    .kk-stat-mini {
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

    .kk-trash {
        cursor: pointer;
        color: #ef4444;
        font-size: 14px;
    }
    .kk-trash:hover { color: #b91c1c; }

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

    /* Modal chọn sản phẩm từ danh sách */
    .kk-pick-row.active { background: #eef2ff; }
    .kk-pick-table thead th {
        background: #f8fafc; font-size: 11.5px;
        text-transform: uppercase; letter-spacing: .5px;
        color: #64748b;
    }
</style>
@endsection

@section('content')
<div class="container-fluid py-3">

    {{-- 1. PHẦN TIÊU ĐỀ TRANG --}}
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-3">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1 small">
                    <li class="breadcrumb-item"><a href="{{ url('admin/dashboard') }}">Admin</a></li>
                    <li class="breadcrumb-item"><a href="{{ url('admin/kho-hang') }}">Kho hàng</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('kiem-kho.history') }}">Kiểm kho</a></li>
                    <li class="breadcrumb-item active" id="kk-breadcrumb-active">Tạo phiếu</li>
                </ol>
            </nav>
            <h2 class="h4 fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                <i class="fas fa-clipboard-check text-primary"></i>
                <span id="kk-page-title-text">Tạo phiếu kiểm kho</span>
            </h2>
            {{-- Banner trạng thái draft (nếu đang chỉnh sửa) --}}
            <div class="kk-edit-banner mt-2" id="kk-edit-banner" style="display:none">
                <i class="fas fa-info-circle"></i>
                Đang chỉnh sửa phiếu <b id="kk-edit-banner-ma">KK…</b>
                — đã lưu lúc <span id="kk-edit-banner-time">—</span>.
            </div>
        </div>
        <button class="btn btn-primary px-3" id="btn-luu-phieu" disabled>
            <i class="fas fa-save me-1"></i>
            <span id="kk-cta-text">Lưu phiếu</span>
        </button>
    </div>

    {{-- 2. WRAPPER CHIA 2 CỘT --}}
    <div class="row g-3 align-items-start">

        {{-- ================= CỘT TRÁI (75%) ================= --}}
        <div class="col-12 col-lg-9">
            <div class="bg-white rounded shadow-sm p-3 d-flex flex-column gap-3">

                {{-- Toolbar tìm kiếm + 4 action mini --}}
                <div class="kk-toolbar-flex">
                    <div class="kk-search-big">
                        <i class="fas fa-search"></i>
                        <input
                            type="text"
                            id="search-input"
                            class="form-control"
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
                <div class="d-flex flex-wrap align-items-center gap-2">
                    <div class="d-flex bg-white rounded p-1 gap-1 shadow-sm" id="kk-pill-tabs">
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
                <div class="kk-scard">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 kk-table">
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
                    <div class="px-3 py-2 small text-muted bg-light border-top d-flex justify-content-between align-items-center">
                        <span><i class="fas fa-circle-check text-success me-1"></i> Tự động <b>Lưu tạm</b> sau 5s không thao tác</span>
                        <span>Mã phiếu: <b id="sb-ma-kk-bottom" class="text-primary">KK—</b></span>
                    </div>
                </div>
            </div>
        </div>
        {{-- ================= KẾT THÚC CỘT TRÁI ================= --}}


        {{-- ================= CỘT PHẢI - SIDEBAR (25%) ================= --}}
        <div class="col-12 col-lg-3">
            <div class="kk-scard sticky-top" style="top: 12px;">

                {{-- Thông tin người tạo --}}
                <div class="kk-suserbar">
                    <div class="kk-savatar">{{ mb_strtoupper(mb_substr(auth()->user()?->ho_ten ?? 'U', 0, 1, 'UTF-8')) }}</div>
                    <div class="flex-grow-1 min-w-0">
                        <div class="kk-suser-name text-truncate">{{ auth()->user()?->ho_ten ?? 'Người dùng' }}</div>
                        <div class="kk-suser-date"><i class="far fa-clock me-1"></i><span id="sb-ngay-tao">—</span></div>
                    </div>
                </div>

                {{-- Mã phiếu --}}
                <div class="kk-scode">
                    <div class="kk-scode-label">Mã phiếu kiểm kho</div>
                    <div class="kk-scode-value" id="sb-ma-kk">KK00000</div>
                </div>

                {{-- Tổng dòng / Đã kiểm --}}
                <div class="d-flex gap-2 p-3">
                    <div class="kk-stat-mini flex-fill">
                        <div class="lbl">Tổng dòng</div>
                        <div class="val" id="sb-tong-dong">0</div>
                    </div>
                    <div class="kk-stat-mini flex-fill">
                        <div class="lbl">Đã kiểm</div>
                        <div class="val" id="sb-da-kiem">0</div>
                    </div>
                </div>

                {{-- Tổng SL lệch --}}
                <div class="px-3 pb-2">
                    <label class="kk-scode-label d-block mb-1">Tổng SL lệch</label>
                    <input type="text" id="sb-sl-lech" value="0" readonly
                           class="form-control form-control-sm bg-light fw-semibold">
                </div>
                {{-- Tổng GT lệch --}}
                <div class="px-3 pb-3">
                    <label class="kk-scode-label d-block mb-1">Tổng GT lệch</label>
                    <input type="text" id="sb-gia-tri-lech" value="0 ₫" readonly
                           class="form-control form-control-sm bg-light fw-semibold">
                </div>

                {{-- Ghi chú --}}
                <div class="px-3 pb-3">
                    <label class="kk-scode-label d-block mb-1">Ghi chú phiếu</label>
                    <textarea
                        id="sb-ghi-chu"
                        rows="2"
                        placeholder="Ghi chú (không bắt buộc)"
                        class="form-control form-control-sm"
                    ></textarea>
                </div>

                {{-- Hai nút Lưu tạm + Hoàn thành --}}
                <div class="d-flex gap-2 px-3 pb-3">
                    <button class="btn btn-primary flex-fill" id="btn-luu-tam" disabled>
                        <i class="fas fa-save me-1"></i> Lưu tạm
                    </button>
                    <button class="btn btn-success flex-fill" id="btn-hoan-thanh" disabled>
                        <i class="fas fa-check me-1"></i> Hoàn thành
                    </button>
                </div>

                {{-- Danh sách "Kiểm gần đây" --}}
                <div class="border-top px-3 py-2">
                    <div class="d-flex align-items-center gap-2 small fw-bold text-uppercase text-secondary border-bottom pb-2 mb-2">
                        <i class="fas fa-history text-primary"></i> Kiểm gần đây
                    </div>
                    <ul class="kk-recent" id="kk-recent-list">
                        <li class="kk-recent-empty">
                            Chưa có hàng hóa được chọn.<br>
                            Thêm hàng hóa vào phiếu kiểm kho sẽ hiển thị ở đây.
                        </li>
                    </ul>
                </div>

            </div>
        </div>
        {{-- ================= KẾT THÚC CỘT PHẢI ================= --}}

    </div>
    {{-- /Kết thúc Wrapper 2 cột --}}
</div>

{{-- ===== Modal xác nhận cân bằng kho ===== --}}
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

{{-- ===== Modal QR Scanner (tái sử dụng từ module Sản phẩm) ===== --}}
<div class="modal fade" id="qrScannerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-barcode me-2"></i>Quét mã vạch</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="qrScanner" style="width:100%; min-height:400px;"></div>
                <div class="mt-3 text-center">
                    <button type="button" class="btn btn-secondary" id="stopQrScanBtn">Dừng quét</button>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ===== Modal chọn sản phẩm từ danh sách ===== --}}
<div class="modal fade" id="modal-pick-product" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-list me-2"></i>Chọn sản phẩm đang tồn kho</h5>
                <button class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="d-flex gap-2 mb-2">
                    <div class="position-relative flex-grow-1">
                        <i class="fas fa-search position-absolute" style="left:12px; top:50%; transform:translateY(-50%); color:#94a3b8;"></i>
                        <input type="text" id="pick-search-input" class="form-control" placeholder="Tìm theo tên hoặc mã vạch..." style="padding-left:34px;">
                    </div>
                    <button class="btn btn-outline-secondary" id="pick-refresh-btn"><i class="fas fa-redo"></i> Tải lại</button>
                </div>
                <div class="table-responsive" style="max-height: 420px;">
                    <table class="table table-hover align-middle kk-pick-table">
                        <thead>
                            <tr>
                                <th style="width:40px;" class="text-center">
                                    <input type="checkbox" class="form-check-input" id="pick-check-all">
                                </th>
                                <th>Mã hàng</th>
                                <th>Tên hàng / Biến thể</th>
                                <th class="text-center">HSD</th>
                                <th class="text-end">Tồn kho</th>
                            </tr>
                        </thead>
                        <tbody id="pick-tbody">
                            <tr><td colspan="5" class="text-center text-muted py-4">Đang tải dữ liệu...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <span class="text-muted me-auto small"><span id="pick-selected-count">0</span> đã chọn</span>
                <button class="btn btn-light" data-bs-dismiss="modal">Hủy</button>
                <button class="btn btn-primary" id="pick-add-btn"><i class="fas fa-plus me-1"></i>Thêm vào phiếu</button>
            </div>
        </div>
    </div>
</div>

{{-- ===== Modal Import Excel/CSV ===== --}}
<div class="modal fade" id="modal-import-excel" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-file-excel me-2 text-success"></i>Import Excel/CSV vào phiếu kiểm kho</h5>
                <button class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                {{-- Hướng dẫn --}}
                <div class="alert alert-info py-2 small mb-3" role="alert">
                    <i class="fas fa-info-circle me-1"></i>
                    File phải có <b>2 cột</b> theo thứ tự:
                    <b class="ms-1">ma_vach</b>
                    <b>so_luong_thuc_te</b>
                    (Cột A, Cột B — dòng đầu làm tiêu đề).
                    <a href="javascript:void(0)" id="btn-download-template" class="float-end fw-semibold">
                        <i class="fas fa-download me-1"></i>Tải file mẫu CSV
                    </a>
                </div>

                {{-- Upload --}}
                <div class="mb-3">
                    <label class="form-label fw-semibold">1. Chọn file Excel/CSV</label>
                    <input type="file" id="import-file-input" class="form-control"
                           accept=".xlsx,.xls,.csv">
                </div>

                {{-- Bảng preview --}}
                <label class="form-label fw-semibold">2. Xem trước kết quả</label>
                <div class="table-responsive" style="max-height: 380px;">
                    <table class="table table-bordered table-sm mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width:40px;">#</th>
                                <th>Mã vạch / Mã hàng</th>
                                <th>Tên SP</th>
                                <th class="text-end">SL thực tế</th>
                                <th class="text-end">Tồn kho</th>
                                <th>Trạng thái</th>
                            </tr>
                        </thead>
                        <tbody id="import-preview-tbody">
                            <tr><td colspan="6" class="text-center text-muted py-4">
                                Chọn file để xem trước.
                            </td></tr>
                        </tbody>
                    </table>
                </div>

                {{-- Summary --}}
                <div class="d-flex gap-3 mt-3 small" id="import-summary" style="display:none">
                    <span class="badge bg-success-subtle text-success">
                        <i class="fas fa-check-circle me-1"></i>
                        <span id="import-summary-ok">0</span> OK
                    </span>
                    <span class="badge bg-warning-subtle text-warning">
                        <i class="fas fa-exclamation-triangle me-1"></i>
                        <span id="import-summary-warning">0</span> Cảnh báo
                    </span>
                    <span class="badge bg-danger-subtle text-danger">
                        <i class="fas fa-times-circle me-1"></i>
                        <span id="import-summary-error">0</span> Lỗi
                    </span>
                </div>
            </div>
            <div class="modal-footer">
                <span class="text-muted me-auto small" id="import-help-text">
                    Các dòng <b>OK</b> sẽ được thêm vào phiếu. Dòng lỗi sẽ bị bỏ qua.
                </span>
                <button class="btn btn-light" data-bs-dismiss="modal">Hủy</button>
                <button class="btn btn-success" id="btn-import-execute" disabled>
                    <i class="fas fa-plus me-1"></i>Thêm các dòng OK vào phiếu
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
{{-- html5-qrcode: thư viện quét mã vạch/mã QR bằng camera --}}
<script src="https://unpkg.com/html5-qrcode@2.3.7/minified/html5-qrcode.min.js"></script>
<script>
{{-- =====================================================
     GLOBAL HELPER: quét mã vạch/QR bằng camera
     Cách dùng:
       window.startQrScan('qrScanner', (decoded) => { ... });
       window.stopQrScan();
     ===================================================== --}}
window.__qrInstance = null;
window.__qrActive   = false;

window.startQrScan = function(elementId, onDecoded) {
    if (window.__qrActive) return;
    const el = document.getElementById(elementId);
    if (!el) {
        console.warn('startQrScan: không tìm thấy #' + elementId);
        return;
    }
    if (typeof Html5Qrcode === 'undefined') {
        toastr.error('Thư viện html5-qrcode chưa được tải.');
        return;
    }
    if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
        toastr.error('Trình duyệt không hỗ trợ truy cập camera.');
        return;
    }

    window.__qrInstance = new Html5Qrcode(elementId);
    const config = { fps: 10, qrbox: { width: 240, height: 160 } };

    Html5Qrcode.getCameras().then((cameras) => {
        if (!cameras || !cameras.length) {
            toastr.error('Không tìm thấy camera trên thiết bị.');
            return;
        }
        const cameraId = cameras[0].id;
        window.__qrInstance.start(cameraId, config, (decoded) => {
            try {
                if (typeof onDecoded === 'function') {
                    onDecoded(decoded);
                }
            } catch (err) {
                console.error('onDecoded error:', err);
            }
        }, (err) => {
            // ignore frame parse errors (rất nhiều khi quét ngoài vùng)
        }).then(() => {
            window.__qrActive = true;
        }).catch((err) => {
            console.error('startQrScan error:', err);
            toastr.error('Không thể khởi động camera: ' + (err?.message || err));
        });
    }).catch((err) => {
        console.error('getCameras error:', err);
        toastr.error('Vui lòng cấp quyền truy cập camera trong trình duyệt.');
    });
};

window.stopQrScan = function() {
    if (!window.__qrActive || !window.__qrInstance) {
        if (window.__qrInstance) {
            try { window.__qrInstance.clear(); } catch (_) {}
            window.__qrInstance = null;
        }
        return;
    }
    window.__qrInstance.stop().then(() => {
        try { window.__qrInstance.clear(); } catch (_) {}
        window.__qrInstance = null;
        window.__qrActive = false;
    }).catch((err) => {
        console.error('stopQrScan error:', err);
        window.__qrInstance = null;
        window.__qrActive = false;
    });
};
</script>
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
    listStock: '/admin/api/lo-hang/ton-kho-list',
};

const fmtVND = n => (Number(n)||0).toLocaleString('vi-VN') + ' ₫';
const fmtNum  = n => (Number(n)||0).toLocaleString('vi-VN');

function escHtml(s) {
    return (s ?? '').toString()
        .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;').replace(/'/g, '&#39;');
}

// ===== Helpers cho cận date / hết hạn =====
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

        const lClass = !ck ? 'kk-diff-zero'
            : (l > 0 ? 'kk-diff-positive' : (l < 0 ? 'kk-diff-negative' : 'kk-diff-zero'));
        const lText = !ck ? '—'
            : (l === 0 ? '0' : (l > 0 ? '+' + fmtNum(l) : fmtNum(l)));
        const gvText = !ck ? '—'
            : (gv === 0 ? fmtVND(0) : (gv < 0 ? '-' : '+') + fmtVND(Math.abs(gv)));

        const hsdStatus = getHsdStatus(it.han_su_dung);
        let hsdPillHtml = '';
        if (it.han_su_dung) {
            const hsdDate = it.han_su_dung.substring(0, 10);
            let pillCls = 'gray', txt = hsdDate;
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

        let rowBg = '';
        if (hsdStatus.state === 'expired') rowBg = 'kk-row-expired';
        else if (hsdStatus.state === 'near') rowBg = 'kk-row-near';

        const maLo = it.ma_lo ? `Lô ${escHtml(it.ma_lo)}` : '';
        const lTextClass = (ck && l !== 0) ? 'text-danger fw-bold' : '';

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
            <td class="text-end fw-semibold">${fmtNum(it.so_luong_ton)}</td>
            <td class="text-center">
                <input type="number" min="0" class="form-control form-control-sm kk-thucte-input mx-auto"
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
    slLeEl.className = 'form-control form-control-sm bg-light fw-semibold ' + (slLe > 0 ? 'text-success' : (slLe < 0 ? 'text-danger' : ''));

    const gtLeEl = document.getElementById('sb-gia-tri-lech');
    gtLeEl.value = fmtVND(gtLe);
    gtLeEl.className = 'form-control form-control-sm bg-light fw-semibold ' + (gtLe > 0 ? 'text-success' : (gtLe < 0 ? 'text-danger' : ''));

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
        const list = res.data?.data || [];
        if (!list.length) {
            Swal.fire({icon:'info', title:'Không có hàng tồn', text:'Không có lô hàng tồn kho.'});
            return;
        }
        const existing = new Set(state.items.map(x => x.id_chi_tiet_lo_hang));
        let added = 0;
        for (const row of list) {
            if (!row.so_luong_ton || Number(row.so_luong_ton) <= 0) continue;
            if (existing.has(row.id)) continue;
            // tonKhoList đã trả về flat: dùng thẳng row.*
            state.items.push({
                id_chi_tiet_lo_hang: row.id,
                variant_id: row.variant_id,
                ma_vach: row.ma_vach || '',
                ten_san_pham: row.ten_san_pham || '',
                ten_bien_the: row.ten_bien_the || '',
                ten_don_vi: row.ten_don_vi || 'cái',
                han_su_dung: row.han_su_dung,
                ma_lo: row.ma_lo || null,
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
    const editId = params.get('edit');
    const mode = params.get('mode');

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

// =====================================================================
// MODAL CHỌN SẢN PHẨM TỪ DANH SÁCH (Bootstrap 5)
// =====================================================================
let _pickStockCache = [];

async function loadPickStock(q = '') {
    const tbody = document.getElementById('pick-tbody');
    tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted py-4"><i class="fas fa-spinner fa-spin me-2"></i>Đang tải...</td></tr>';
    try {
        const params = { per_page: 500 };
        if (q) params.q = q;
        const res = await axios.get(url.listStock, { params });
        // API tonKhoList trả về { success, data: [...] }
        const list = res.data?.data || [];
        _pickStockCache = list.filter(r => r.so_luong_ton && Number(r.so_luong_ton) > 0);
        renderPickStock(_pickStockCache);
    } catch (e) {
        tbody.innerHTML = '<tr><td colspan="5" class="text-center text-danger py-4">Lỗi tải dữ liệu: '+escHtml(e.message)+'</td></tr>';
    }
}

function renderPickStock(list) {
    const tbody = document.getElementById('pick-tbody');
    if (!list.length) {
        tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted py-4">Không có lô hàng tồn kho.</td></tr>';
        return;
    }
    tbody.innerHTML = list.map(r => {
        // tonKhoList đã trả về flat: ma_vach, ten_san_pham, ten_bien_the, ma_lo đều ở root
        const maVach = r.ma_vach || '';
        const tenSp = r.ten_san_pham || '';
        const tenBt = r.ten_bien_the || '';
        const dv = r.ten_don_vi || 'cái';
        const maLo = r.ma_lo;
        return `<tr class="kk-pick-row">
            <td class="text-center"><input type="checkbox" class="form-check-input pick-check" data-id="${r.id}"></td>
            <td><span class="kk-code">${escHtml(maVach)}</span></td>
            <td>
                <div class="kk-tname">${escHtml(tenSp)}</div>
                <div class="kk-tmeta">${escHtml(tenBt)} • ${escHtml(dv)}${maLo ? ' • Lô '+escHtml(maLo) : ''}</div>
            </td>
            <td class="text-center small">${r.han_su_dung ? escHtml(r.han_su_dung.substring(0,10)) : '<span class="text-muted">—</span>'}</td>
            <td class="text-end fw-semibold">${fmtNum(r.so_luong_ton)}</td>
        </tr>`;
    }).join('');
    tbody.querySelectorAll('.pick-check').forEach(cb => {
        cb.addEventListener('change', updatePickSelectedCount);
    });
}

function updatePickSelectedCount() {
    const cnt = document.querySelectorAll('.pick-check:checked').length;
    document.getElementById('pick-selected-count').textContent = cnt;
}

function openPickProductModal() {
    loadPickStock();
    const modal = new bootstrap.Modal(document.getElementById('modal-pick-product'));
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

            const lTextClass = (ck && l !== 0) ? 'text-danger fw-bold' : '';

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

    document.getElementById('btn-import-excel')?.addEventListener('click', () => {
        const modal = new bootstrap.Modal(document.getElementById('modal-import-excel'));
        modal.show();
    });
    document.getElementById('btn-scan-device')?.addEventListener('click', () => {
        const modalEl = document.getElementById('qrScannerModal');
        const modal = new bootstrap.Modal(modalEl);
        modal.show();
        // Khởi tạo scanner sau khi modal animation xong
        setTimeout(() => {
            if (typeof window.startQrScan === 'function') {
                window.startQrScan('qrScanner', (decoded) => {
                    searchInput.value = decoded;
                    searchAndAdd(decoded);
                    searchInput.value = '';
                    modal.hide();
                });
            } else {
                toastr.warning('Chức năng quét thiết bị chưa sẵn sàng.');
            }
        }, 400);
    });
    document.getElementById('stopQrScanBtn')?.addEventListener('click', () => {
        const modalEl = document.getElementById('qrScannerModal');
        const modal = bootstrap.Modal.getInstance(modalEl);
        if (modal) modal.hide();
        if (typeof window.stopQrScan === 'function') window.stopQrScan();
    });
    // Tự động stop camera khi modal QR bị đóng (bất kể lý do)
    document.getElementById('qrScannerModal')?.addEventListener('hidden.bs.modal', () => {
        if (typeof window.stopQrScan === 'function') window.stopQrScan();
    });
    document.getElementById('btn-choose-from-list')?.addEventListener('click', () => {
        openPickProductModal();
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

    // ===== Modal chọn sản phẩm từ danh sách =====
    document.getElementById('pick-refresh-btn')?.addEventListener('click', () => loadPickStock());
    document.getElementById('pick-search-input')?.addEventListener('input', e => {
        const q = e.target.value.trim().toLowerCase();
        if (!q) { renderPickStock(_pickStockCache); return; }
        const filtered = _pickStockCache.filter(r => {
            return (r.ma_vach || '').toLowerCase().includes(q)
                || (r.ma_hang || '').toLowerCase().includes(q)
                || (r.ten_san_pham || '').toLowerCase().includes(q)
                || (r.ten_bien_the || '').toLowerCase().includes(q)
                || (r.ma_lo || '').toLowerCase().includes(q);
        });
        renderPickStock(filtered);
    });
    document.getElementById('pick-check-all')?.addEventListener('change', e => {
        document.querySelectorAll('.pick-check').forEach(cb => { cb.checked = e.target.checked; });
        updatePickSelectedCount();
    });
    document.getElementById('pick-add-btn')?.addEventListener('click', () => {
        const ids = Array.from(document.querySelectorAll('.pick-check:checked'))
            .map(cb => Number(cb.dataset.id));
        if (!ids.length) {
            toastr.warning('Vui lòng chọn ít nhất 1 sản phẩm.');
            return;
        }
        const existing = new Set(state.items.map(x => x.id_chi_tiet_lo_hang));
        let added = 0;
        for (const id of ids) {
            if (existing.has(id)) continue;
            const row = _pickStockCache.find(r => r.id === id);
            if (!row) continue;
            // tonKhoList trả về flat: dùng thẳng row.*
            state.items.push({
                id_chi_tiet_lo_hang: row.id,
                variant_id: row.variant_id,
                ma_vach: row.ma_vach || '',
                ten_san_pham: row.ten_san_pham || '',
                ten_bien_the: row.ten_bien_the || '',
                ten_don_vi: row.ten_don_vi || 'cái',
                han_su_dung: row.han_su_dung,
                ma_lo: row.ma_lo || null,
                so_luong_ton: row.so_luong_ton,
                so_luong_thuc_te: null,
                so_luong_lech: 0,
                gia_von: row.gia_nhap || 0,
                gia_tri_lech: 0,
            });
            added++;
        }
        bootstrap.Modal.getInstance(document.getElementById('modal-pick-product')).hide();
        if (added) {
            rerender();
            scheduleAutoSave();
            toastr.success(`Đã thêm ${added} dòng vào phiếu.`);
        } else {
            toastr.info('Tất cả sản phẩm đã chọn đều đã có trong phiếu.');
        }
    });

    loadDraft();
});

// =====================================================================
// MODAL IMPORT EXCEL/CSV
// =====================================================================
let _importRows = []; // các dòng đã được resolve từ server
let _importFileName = '';

/**
 * Parse file CSV / Excel đơn giản (csv + xlsx qua SheetJS nếu có).
 * Trả về mảng {ma_vach, so_luong_thuc_te}.
 */
function parseImportFile(file) {
    return new Promise((resolve, reject) => {
        const ext = (file.name.split('.').pop() || '').toLowerCase();
        if (ext === 'csv') {
            const reader = new FileReader();
            reader.onload = (e) => {
                const text = e.target.result;
                resolve(parseCsvText(text));
            };
            reader.onerror = () => reject(reader.error);
            reader.readAsText(file, 'utf-8');
        } else if (ext === 'xlsx' || ext === 'xls') {
            if (typeof XLSX === 'undefined') {
                // Không có SheetJS → fallback chỉ parse CSV
                reject(new Error('File XLSX cần thư viện SheetJS. Hãy import file CSV hoặc báo admin thêm CDN SheetJS.'));
                return;
            }
            const reader = new FileReader();
            reader.onload = (e) => {
                try {
                    const wb = XLSX.read(e.target.result, { type: 'binary' });
                    const ws = wb.Sheets[wb.SheetNames[0]];
                    const rows = XLSX.utils.sheet_to_json(ws, { header: 1 });
                    resolve(rowsToItems(rows));
                } catch (err) { reject(err); }
            };
            reader.onerror = () => reject(reader.error);
            reader.readAsBinaryString(file);
        } else {
            reject(new Error('Định dạng file không hỗ trợ. Chỉ chấp nhận .csv, .xlsx, .xls.'));
        }
    });
}

function parseCsvText(text) {
    const lines = text.split(/\r?\n/).filter(l => l.trim() !== '');
    return rowsToItems(lines.map(l => l.split(/[,\t;]/).map(c => c.trim())));
}

function rowsToItems(rows) {
    if (!rows.length) return [];
    const result = [];
    // Bỏ header nếu dòng đầu chứa chữ "ma_vach" / "mã"
    let start = 0;
    if (rows[0] && rows[0][0] && /ma[_ ]?vach|ma[ ]?hang|mã/i.test(String(rows[0][0]))) {
        start = 1;
    }
    for (let i = start; i < rows.length; i++) {
        const r = rows[i] || [];
        const maVach = String(r[0] ?? '').trim();
        const sl = parseInt(String(r[1] ?? '').trim(), 10);
        if (!maVach) continue;
        result.push({
            ma_vach: maVach,
            ma_hang: null,
            so_luong_thuc_te: Number.isFinite(sl) ? sl : null,
            ten_san_pham: null,
        });
    }
    return result;
}

function downloadImportTemplate() {
    const csv = 'ma_vach,so_luong_thuc_te\n' +
        '8934673001234,10\n' +
        '8934673005678,5\n' +
        '8935049505551,8\n';
    const blob = new Blob(['\uFEFF' + csv], { type: 'text/csv;charset=utf-8' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'template-kiem-kho.csv';
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    URL.revokeObjectURL(url);
    toastr.success('Đã tải file mẫu CSV.');
}

async function handleImportFile(file) {
    if (!file) return;
    _importFileName = file.name;
    const tbody = document.getElementById('import-preview-tbody');
    tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted py-4"><i class="fas fa-spinner fa-spin me-2"></i>Đang đọc file và tra cứu...</td></tr>';

    try {
        const items = await parseImportFile(file);
        if (!items.length) {
            tbody.innerHTML = '<tr><td colspan="6" class="text-center text-warning py-4">File không có dòng dữ liệu hợp lệ.</td></tr>';
            document.getElementById('import-summary').style.display = 'none';
            return;
        }
        const res = await axios.post('/admin/api/kiem-kho/import-preview', { items });
        if (!res.data.success) {
            tbody.innerHTML = `<tr><td colspan="6" class="text-center text-danger py-4">${escHtml(res.data.message)}</td></tr>`;
            return;
        }
        _importRows = res.data.data;
        const sum = res.data.summary;
        renderImportPreview(_importRows, sum);
    } catch (err) {
        tbody.innerHTML = `<tr><td colspan="6" class="text-center text-danger py-4">${escHtml(err.message)}</td></tr>`;
        document.getElementById('import-summary').style.display = 'none';
    }
}

function renderImportPreview(rows, sum) {
    const tbody = document.getElementById('import-preview-tbody');
    if (!rows.length) {
        tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted py-4">Không có dòng nào.</td></tr>';
        return;
    }

    const statusMap = {
        ok:      { cls: 'bg-success-subtle text-success', icon: 'fa-check-circle' },
        warning: { cls: 'bg-warning-subtle text-warning', icon: 'fa-exclamation-triangle' },
        error:   { cls: 'bg-danger-subtle text-danger', icon: 'fa-times-circle' },
    };

    tbody.innerHTML = rows.map((r, idx) => {
        const s = statusMap[r.status] || statusMap.error;
        const maHienThi = r.ma_vach || r.ma_hang || '';
        const maHienThiHtml = r.status === 'error'
            ? `<s class="text-muted">${escHtml(maHienThi)}</s>`
            : escHtml(maHienThi);
        const ton = r.so_luong_ton != null ? fmtNum(r.so_luong_ton) : '—';
        return `<tr>
            <td class="text-muted">${idx + 1}</td>
            <td><span class="kk-code">${maHienThiHtml}</span></td>
            <td>${escHtml(r.ten_san_pham || r.ten_bien_the || '')}</td>
            <td class="text-end">${r.so_luong_thuc_te != null ? fmtNum(r.so_luong_thuc_te) : '<span class="text-muted">—</span>'}</td>
            <td class="text-end">${ton}</td>
            <td><span class="badge ${s.cls}"><i class="fas ${s.icon} me-1"></i>${escHtml(r.message || r.status)}</span></td>
        </tr>`;
    }).join('');

    const sumEl = document.getElementById('import-summary');
    sumEl.style.display = '';
    document.getElementById('import-summary-ok').textContent = sum.ok;
    document.getElementById('import-summary-warning').textContent = sum.warning;
    document.getElementById('import-summary-error').textContent = sum.error;

    // Enable nút "Thêm các dòng OK" nếu có ít nhất 1 dòng OK hoặc warning
    const okOrWarn = sum.ok + sum.warning;
    document.getElementById('btn-import-execute').disabled = okOrWarn <= 0;
}

async function executeImport() {
    const okOrWarn = _importRows.filter(r => r.status === 'ok' || r.status === 'warning');
    if (!okOrWarn.length) {
        toastr.warning('Không có dòng nào để thêm.');
        return;
    }

    // Convert về đúng shape items[] mà API storeDraft chấp nhận
    const items = okOrWarn.map(r => ({
        id_chi_tiet_lo_hang: r.id_chi_tiet_lo_hang,
        variant_id: r.variant_id,
        ma_vach: r.ma_vach,
        ten_san_pham: r.ten_san_pham,
        ten_bien_the: r.ten_bien_the,
        ten_don_vi: r.ten_don_vi,
        han_su_dung: r.han_su_dung,
        ma_lo: r.ma_lo,
        so_luong_ton: r.so_luong_ton,
        so_luong_thuc_te: r.so_luong_thuc_te,
        gia_von: r.gia_von,
    }));

    const btn = document.getElementById('btn-import-execute');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Đang thêm...';

    try {
        const res = await axios.post('/admin/api/kiem-kho/import-execute', {
            items,
            ghi_chu: 'Import từ ' + _importFileName,
        });
        if (res.data.success) {
            toastr.success(res.data.message);
            // Đóng modal + reset
            bootstrap.Modal.getInstance(document.getElementById('modal-import-excel')).hide();
            document.getElementById('import-file-input').value = '';
            document.getElementById('import-preview-tbody').innerHTML = '<tr><td colspan="6" class="text-center text-muted py-4">Chọn file để xem trước.</td></tr>';
            document.getElementById('import-summary').style.display = 'none';
            _importRows = [];
            // Reload phiếu (server trả về data)
            if (res.data.data) {
                state.phieu = res.data.data;
                state.items = res.data.data.items || [];
                switchMode('edit', state.phieu);
                document.getElementById('sb-ghi-chu').value = res.data.data.ghi_chu || '';
            }
            rerender();
        } else {
            toastr.error(res.data.message);
        }
    } catch (err) {
        toastr.error(err.response?.data?.message || err.message);
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-plus me-1"></i>Thêm các dòng OK vào phiếu';
    }
}

document.addEventListener('DOMContentLoaded', () => {
    const fileInput = document.getElementById('import-file-input');
    fileInput?.addEventListener('change', (e) => handleImportFile(e.target.files[0]));
    document.getElementById('btn-import-execute')?.addEventListener('click', executeImport);
    document.getElementById('btn-download-template')?.addEventListener('click', (e) => {
        e.preventDefault();
        downloadImportTemplate();
    });
});
</script>
@endsection
