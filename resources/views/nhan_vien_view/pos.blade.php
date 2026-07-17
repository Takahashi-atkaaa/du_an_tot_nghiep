<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SmartMart POS</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <style>
       :root {
    --pos-primary: #16803a;
    --pos-primary-dark: #0f5f2a;
    --pos-primary-light: #22a652;
    --pos-bg: #eef8e9;
    --pos-card: #ffffff;
    --pos-text: #1f2937;
    --pos-muted: #6b7280;
    --pos-border: #e5e7eb;
    --pos-danger: #dc2626;
    --pos-warning: #f59e0b;
    --pos-shadow: 0 8px 24px rgba(15, 95, 42, .10);
}

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Roboto', sans-serif;
    height: 100vh;
    overflow: hidden;
    background: var(--pos-bg);
    color: var(--pos-text);
}

/* HEADER */
.pos-header {
    height: 58px;
    background: linear-gradient(90deg, #0f5f2a, #16803a);
    color: #fff;
    padding: 0 20px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    position: fixed;
    inset: 0 0 auto 0;
    z-index: 1000;
    box-shadow: 0 3px 14px rgba(0,0,0,.14);
}

.logo {
    font-size: 21px;
    font-weight: 800;
    display: flex;
    align-items: center;
    gap: 8px;
}

.logo i {
    color: #bbf7d0;
}

.pos-datetime {
    font-size: 13px;
    color: #dcfce7;
    text-align: center;
    line-height: 1.25;
}

.pos-user {
    display: flex;
    align-items: center;
    gap: 12px;
}

.user-avatar {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: #bbf7d0;
    color: #0f5f2a;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 800;
}

.user-info {
    font-size: 13px;
}

.user-info strong {
    display: block;
    line-height: 1.1;
}

.user-info small {
    opacity: .8;
}

.btn-exit {
    color: #fff;
    text-decoration: none;
    border: 1px solid rgba(255,255,255,.25);
    background: rgba(255,255,255,.12);
    padding: 7px 13px;
    border-radius: 10px;
    font-size: 13px;
}

.btn-exit:hover {
    background: rgba(255,255,255,.22);
    color: #fff;
}

/* BODY */
.pos-body {
    display: flex;
    height: 100vh;
    padding-top: 58px;
    padding-bottom: 28px;
}

/* SIDEBAR */
.pos-sidebar {
    width: 62px;
    background: #0f5f2a;
    padding: 14px 7px;
    display: flex;
    flex-direction: column;
    gap: 8px;
    flex-shrink: 0;
}

.nav-btn {
    width: 48px;
    height: 48px;
    border-radius: 14px;
    border: none;
    background: rgba(255,255,255,.08);
    color: rgba(255,255,255,.75);
    text-decoration: none;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 3px;
    font-size: 10px;
    transition: .18s;
}

.nav-btn i {
    font-size: 16px;
}

.nav-btn span {
    font-size: 9px;
    font-weight: 600;
}

.nav-btn:hover,
.nav-btn.active {
    background: #22a652;
    color: #fff;
    transform: translateY(-1px);
}

.nav-divider {
    height: 1px;
    width: 34px;
    background: rgba(255,255,255,.18);
    margin: 3px auto;
}

/* LEFT PRODUCT AREA */
.pos-products {
    flex: 1;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    background: #f0f8eb;
}

.pos-category-bar {
    background: #fff;
    padding: 10px 14px;
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
    border-bottom: 1px solid var(--pos-border);
}

.cat-btn {
    border: 1px solid var(--pos-border);
    background: #fff;
    color: #4b5563;
    border-radius: 999px;
    padding: 7px 14px;
    font-size: 12px;
    font-weight: 700;
    display: flex;
    align-items: center;
    gap: 6px;
    transition: .18s;
}

.cat-btn:hover {
    border-color: var(--pos-primary);
    color: var(--pos-primary);
}

.cat-btn.active {
    background: var(--pos-primary);
    border-color: var(--pos-primary);
    color: #fff;
}

/* CUSTOMER SEARCH */
.pos-products > .mb-2.position-relative {
    margin: 0 !important;
    padding: 10px 14px;
    background: #fff;
    border-bottom: 1px solid var(--pos-border);
}

#customerSearchInput,
#searchInput {
    height: 42px;
    border-radius: 12px;
    border: 1px solid var(--pos-border);
    font-size: 14px;
}

#customerSearchInput:focus,
#searchInput:focus {
    border-color: var(--pos-primary);
    box-shadow: 0 0 0 3px rgba(22,128,58,.12);
}

#selectedCustomerBox {
    padding: 0 14px;
    background: #fff;
}

/* PRODUCT SEARCH */
.pos-search-bar {
    background: #fff;
    padding: 10px 14px;
    border-bottom: 1px solid var(--pos-border);
}

.search-wrapper {
    position: relative;
}

.search-wrapper i {
    position: absolute;
    top: 50%;
    left: 14px;
    transform: translateY(-50%);
    color: var(--pos-muted);
}

.search-wrapper input {
    width: 100%;
    padding-left: 42px;
}

/* PRODUCT GRID */
.pos-product-grid {
    flex: 1;
    overflow-y: auto;
    padding: 16px;
    display: grid;
    grid-template-columns: repeat(5, minmax(0, 1fr));
    gap: 14px;
    align-content: start;
}

.pos-product-card {
    background: #fff;
    border: 1px solid #dfe7dd;
    border-radius: 16px;
    overflow: hidden;
    cursor: pointer;
    transition: .18s ease;
    box-shadow: 0 2px 8px rgba(0,0,0,.04);
}

.pos-product-card:hover {
    transform: translateY(-3px);
    border-color: var(--pos-primary);
    box-shadow: var(--pos-shadow);
}

.product-img {
    height: 120px;
    background: #fafafa;
    border-bottom: 1px solid #f0f0f0;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 10px;
}

.product-img img {
    max-width: 100%;
    max-height: 100%;
    object-fit: contain;
    display: block;
}

.product-info {
    padding: 10px 8px 12px;
    text-align: center;
}

.product-name {
    font-size: 13px;
    font-weight: 800;
    line-height: 18px;
    height: 36px;
    overflow: hidden;
    color: #111827;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
}

.product-price {
    margin-top: 7px;
    font-size: 17px;
    font-weight: 900;
    color: var(--pos-primary);
}

.product-stock {
    display: inline-block;
    margin-top: 6px;
    padding: 3px 9px;
    border-radius: 999px;
    background: #ecfdf3;
    color: #16803a;
    font-size: 11px;
    font-weight: 700;
}

.product-stock.low {
    background: #fff7ed;
    color: #c2410c;
}

/* CART */
.pos-cart {
    width: 330px;
    background: #fff;
    border-left: 1px solid var(--pos-border);
    display: flex;
    flex-direction: column;
    flex-shrink: 0;
    box-shadow: -4px 0 18px rgba(0,0,0,.06);
}

.cart-header {
    padding: 13px 14px;
    border-bottom: 1px solid var(--pos-border);
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.cart-header h5 {
    margin: 0;
    color: var(--pos-primary);
    font-size: 15px;
    font-weight: 900;
    display: flex;
    align-items: center;
    gap: 7px;
}

.cart-count {
    min-width: 22px;
    height: 22px;
    padding: 0 7px;
    border-radius: 999px;
    background: var(--pos-primary);
    color: #fff;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
}

.btn-clear {
    border: none;
    background: transparent;
    color: var(--pos-muted);
    font-size: 12px;
    padding: 5px 8px;
    border-radius: 8px;
}

.btn-clear:hover {
    background: #fee2e2;
    color: var(--pos-danger);
}

.cart-items {
    flex: 1;
    overflow-y: auto;
}

.cart-empty {
    height: 100%;
    min-height: 180px;
    color: var(--pos-muted);
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 8px;
}

.cart-empty i {
    font-size: 42px;
    opacity: .28;
}

.cart-item {
    padding: 10px 12px;
    border-bottom: 1px solid #f1f1f1;
    display: grid;
    grid-template-columns: 42px 1fr auto;
    gap: 8px;
    align-items: center;
}

.item-img {
    width: 42px;
    height: 42px;
    border-radius: 10px;
    background: #f7f7f7;
    overflow: hidden;
}

.item-img img {
    width: 100%;
    height: 100%;
    object-fit: contain;
}

.item-name {
    font-size: 12px;
    font-weight: 800;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.item-price {
    font-size: 11px;
    color: var(--pos-muted);
}

.item-qty {
    grid-column: 2 / 4;
    display: flex;
    justify-content: flex-start;
    align-items: center;
    gap: 6px;
}

.qty-btn {
    width: 26px;
    height: 26px;
    border-radius: 8px;
    border: 1px solid var(--pos-border);
    background: #fff;
    color: var(--pos-primary);
}

.qty-btn:hover {
    background: var(--pos-primary);
    color: #fff;
}

.qty-num {
    min-width: 22px;
    text-align: center;
    font-weight: 800;
}

.item-total {
    color: var(--pos-primary);
    font-size: 13px;
    font-weight: 900;
}

.btn-remove {
    border: none;
    background: transparent;
    color: var(--pos-muted);
}

.btn-remove:hover {
    color: var(--pos-danger);
}

/* SUMMARY + PAYMENT */
.cart-summary {
    padding: 12px 14px;
    background: #fafafa;
    border-top: 1px solid var(--pos-border);
}

.summary-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 13px;
    margin-bottom: 7px;
}

.summary-row.total {
    margin-top: 8px;
    padding-top: 8px;
    border-top: 1px dashed #cfcfcf;
    font-size: 17px;
    font-weight: 900;
    color: var(--pos-primary);
}

.discount-row,
.text-danger {
    color: var(--pos-danger) !important;
}

.pos-cart .p-2.border-top {
    padding: 12px 14px !important;
    background: #fff;
}

.form-label {
    font-size: 12px;
    font-weight: 700;
    color: #4b5563;
}

.form-select,
.form-control {
    border-radius: 11px;
    border-color: var(--pos-border);
}

.pos-payment {
    padding: 13px 14px 14px;
    border-top: 1px solid var(--pos-border);
    background: #fff;
}

.money-input-group {
    margin-bottom: 10px;
}

.money-input-group label {
    font-size: 12px;
    font-weight: 800;
    color: #4b5563;
    margin-bottom: 5px;
}

.money-input-group input {
    width: 100%;
    height: 42px;
    border: 1px solid var(--pos-border);
    border-radius: 12px;
    padding: 8px 12px;
    text-align: right;
    font-weight: 900;
    color: var(--pos-primary);
}

.change-display {
    background: #e8f7ee;
    border: 1px solid #d1f0dc;
    border-radius: 12px;
    padding: 9px 12px;
    margin-bottom: 10px;
    display: flex;
    justify-content: space-between;
}

.change-label {
    color: var(--pos-muted);
    font-size: 12px;
    font-weight: 700;
}

.change-amount {
    color: var(--pos-primary);
    font-weight: 900;
}

.payment-methods {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 8px;
    margin-bottom: 10px;
}

.pay-btn {
    border: 1px solid var(--pos-border);
    background: #fff;
    border-radius: 13px;
    padding: 9px 4px;
    font-size: 11px;
    font-weight: 800;
    color: #6b7280;
    display: flex;
    flex-direction: column;
    gap: 4px;
    align-items: center;
}

.pay-btn i {
    font-size: 15px;
}

.pay-btn:hover {
    border-color: var(--pos-primary);
    color: var(--pos-primary);
}

.pay-btn.active {
    background: var(--pos-primary);
    border-color: var(--pos-primary);
    color: #fff;
}

.btn-checkout {
    width: 100%;
    height: 48px;
    border: none;
    border-radius: 14px;
    background: linear-gradient(90deg, #16803a, #22a652);
    color: #fff;
    font-size: 15px;
    font-weight: 900;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    box-shadow: 0 8px 18px rgba(22,128,58,.25);
}

.btn-checkout:hover {
    background: linear-gradient(90deg, #0f5f2a, #16803a);
}

.shortcut-hint {
    background: rgba(255,255,255,.22);
    padding: 2px 7px;
    border-radius: 6px;
    font-size: 11px;
}

/* FOOTER */
.pos-footer {
    height: 28px;
    background: #0f5f2a;
    color: rgba(255,255,255,.75);
    font-size: 11px;
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 18px;
    position: fixed;
    inset: auto 0 0 0;
    z-index: 1000;
}

.pos-footer kbd {
    background: rgba(255,255,255,.18);
    color: #fff;
    border: none;
    padding: 2px 6px;
    border-radius: 5px;
    font-size: 10px;
}

/* TOAST */
.pos-toast {
    position: fixed;
    top: 74px;
    right: 20px;
    z-index: 3000;
    display: none;
    color: #fff;
    background: var(--pos-primary);
    padding: 12px 16px;
    border-radius: 12px;
    font-size: 13px;
    font-weight: 800;
    box-shadow: 0 8px 24px rgba(0,0,0,.18);
}

.pos-toast.show {
    display: flex;
    align-items: center;
    gap: 8px;
    animation: slideIn .25s ease;
}

@keyframes slideIn {
    from { transform: translateX(80px); opacity: 0; }
    to { transform: translateX(0); opacity: 1; }
}

/* MODAL */
.modal-content {
    border-radius: 18px;
    overflow: hidden;
}

.btn {
    border-radius: 11px;
}


/* SCROLLBAR */
::-webkit-scrollbar {
    width: 6px;
}

::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 99px;
}

::-webkit-scrollbar-track {
    background: transparent;
}

/* RESPONSIVE */
@media (max-width: 1400px) {
    .pos-product-grid {
        grid-template-columns: repeat(4, minmax(0, 1fr));
    }
}

@media (max-width: 1100px) {
    .pos-cart {
        width: 300px;
    }

    .pos-product-grid {
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }
}

@media (max-width: 800px) {
    .pos-product-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .pos-datetime,
    .user-info {
        display: none;
    }
}
.pos-product-card{
    min-height:260px;
}
.invoice-tabs {
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 10px;
    border-bottom: 1px solid #e5e7eb;
    background: #fff;
}

#invoiceTabs {
    display: flex;
    gap: 6px;
    flex: 1;
    overflow-x: auto;
}

.invoice-tab {
    padding: 7px 12px;
    border-radius: 10px;
    background: #f1f5f9;
    color: #334155;
    font-size: 12px;
    font-weight: 700;
    cursor: pointer;
    white-space: nowrap;
}

.invoice-tab.active {
    background: #16803a;
    color: #fff;
}

.invoice-tab .close-tab {
    margin-left: 8px;
    color: #ef4444;
    font-weight: 900;
}

.btn-new-invoice {
    width: 34px;
    height: 34px;
    border: none;
    border-radius: 10px;
    background: #16803a;
    color: #fff;
    font-size: 20px;
    font-weight: 900;
}

    </style>
</head>
<body>

<!-- ── POS Header ── -->
<header class="pos-header">
    <div class="logo">
        <i class="fas fa-leaf"></i>
        Smart<span style="color:var(--pos-accent)">Mart</span> POS
    </div>
    <div class="pos-datetime" id="posDatetime">
        <div id="posDate">16/06/2026</div>
        <div id="posTime">00:56:00</div>
    </div>
    <div class="pos-user">
        <div class="user-avatar">NV</div>
        <div class="user-info">
            <strong>Nhân Viên Test</strong>
            <small>Nhân viên bán hàng</small>
        </div>
        <a href="{{ url('nhan-vien/') }}" class="btn-exit">
            <i class="fas fa-sign-out-alt"></i> Thoát
        </a>
    </div>
</header>

<div class="modal fade" id="addCustomerModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg">

            <!-- Header -->
            <div class="modal-header bg-success text-white border-0">
                <h5 class="modal-title fw-bold">
                    <i class="fas fa-user-plus me-2"></i>
                    Thêm khách hàng mới
                </h5>

                <button type="button"
                        class="btn-close btn-close-white"
                        data-bs-dismiss="modal"></button>
            </div>

            <!-- Body -->
            <div class="modal-body p-4">

                <div class="alert alert-light border mb-4">
                    <i class="fas fa-info-circle text-primary me-2"></i>
                    Thông tin khách hàng sẽ được lưu và tự động chọn cho hóa đơn hiện tại.
                </div>

                <div class="row g-3">

                    <!-- Tên -->
                    <div class="col-12">
                        <div class="form-floating">
                            <input type="text"
                                   class="form-control"
                                   id="kh_ten"
                                   placeholder="Tên khách hàng">
                            <label>
                                <i class="fas fa-user me-1"></i>
                                Tên khách hàng
                            </label>
                        </div>
                    </div>

                    <!-- SĐT -->
                    <div class="col-md-6">
                        <div class="form-floating">
                            <input type="text"
                                   class="form-control"
                                   id="kh_sdt"
                                   placeholder="Số điện thoại">
                            <label>
                                <i class="fas fa-phone me-1"></i>
                                Số điện thoại
                            </label>
                        </div>
                    </div>

                    <!-- Email -->
                    <div class="col-md-6">
                        <div class="form-floating">
                            <input type="email"
                                   class="form-control"
                                   id="kh_email"
                                   placeholder="Email">
                            <label>
                                <i class="fas fa-envelope me-1"></i>
                                Email
                            </label>
                        </div>
                    </div>

                    <!-- Địa chỉ -->
                    <div class="col-12">
                        <div class="form-floating">
                            <textarea class="form-control"
                                      id="kh_dia_chi"
                                      placeholder="Địa chỉ"
                                      style="height:100px"></textarea>

                            <label>
                                <i class="fas fa-map-marker-alt me-1"></i>
                                Địa chỉ
                            </label>
                        </div>
                    </div>

                </div>

            </div>

            <!-- Footer -->
            <div class="modal-footer border-0 px-4 pb-4">

                <button type="button"
                        class="btn btn-light border px-4"
                        data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>
                    Hủy
                </button>

                <button type="button"
                        class="btn btn-success px-4"
                        onclick="saveCustomerQuick()">
                    <i class="fas fa-save me-1"></i>
                    Lưu khách hàng
                </button>

            </div>

        </div>
    </div>
</div>
<!-- ── POS Body ── -->
<div class="pos-body">

    <!-- Sidebar -->
    <nav class="pos-sidebar">
        @if (auth()->user()->id_vai_tro == 1)
            <a href="{{ url('/admin/dashboard') }}" class="nav-btn" title="Dashboard">
                <i class="bi bi-arrow-left-circle"></i>
                <span>Quản trị</span>
            </a>
        @endif
        
        <a href="{{ url('nhan-vien/') }}" class="nav-btn" title="Dashboard">
            <i class="fas fa-home"></i>
            <span>Home</span>
        </a>
        <div class="nav-divider"></div>
        <button class="nav-btn active" title="Bán hàng" onclick="switchCategory('all')">
            <i class="fas fa-shopping-cart"></i>
            <span>Bán</span>
        </button>
        <button class="nav-btn" title="Hóa đơn" onclick="window.location='{{ url('nhan-vien/hoa-don') }}'">
            <i class="fas fa-file-invoice"></i>
            <span>HD</span>
        </button>
        <button class="nav-btn" title="Sản phẩm" onclick="window.location='{{ url('nhan-vien/san-pham') }}'">
            <i class="fas fa-box"></i>
            <span>SP</span>
        </button>
        <button class="nav-btn" title="Khách hàng" onclick="window.location='{{ url('nhan-vien/khach-hang') }}'">
            <i class="fas fa-users"></i>
            <span>KH</span>
        </button>
        <div class="nav-divider"></div>
        <button class="nav-btn" title="Lịch làm việc" onclick="window.location='{{ url('nhan-vien/lich-lam-viec') }}'">
            <i class="fas fa-calendar"></i>
            <span>Lịch</span>
        </button>
        <button class="nav-btn" title="Hồ sơ" onclick="window.location='{{ url('nhan-vien/ho-so') }}'">
            <i class="fas fa-user"></i>
            <span>Hồ sơ</span>
        </button>
    </nav>

    <!-- Product Panel -->
    <div class="pos-products">

        <!-- Category Bar -->
        <div class="pos-category-bar" id="categoryBar">
    <button class="cat-btn active" data-category="all" onclick="switchCategory('all')">
        <i class="fas fa-border-all"></i>
        Tất cả
    </button>
</div>

        <!-- Search -->
        <div class="mb-2 position-relative">

    <div class="d-flex gap-2">
        <input type="text"
               id="customerSearchInput"
               class="form-control"
               placeholder="Tìm khách hàng theo tên / SĐT..."
               oninput="searchCustomers()">

     <button type="button"
        class="btn btn-success"
        data-bs-toggle="modal"
        data-bs-target="#addCustomerModal">
    <i class="fas fa-user-plus"></i>
    
</button>
    </div>

    <input type="hidden" id="selectedCustomerId">

    <div id="customerSearchResult"
         class="bg-white border rounded shadow-sm position-absolute w-100"
         style="z-index:3000; display:none; max-height:220px; overflow-y:auto;">
    </div>
</div>

<div id="selectedCustomerBox" class="mb-2" style="display:none;">
    <div class="alert alert-success py-2 mb-0 d-flex justify-content-between align-items-center">
        <span id="selectedCustomerText"></span>
        <button type="button" class="btn btn-sm btn-outline-danger" onclick="clearSelectedCustomer()">
            Bỏ chọn
        </button>
    </div>
</div>
        <div class="pos-search-bar">
    <div class="search-wrapper">
        <i class="fas fa-search"></i>

        <input
            type="text"
            id="searchInput"
            placeholder="Tìm sản phẩm hoặc quét mã vạch..."
            oninput="filterProducts()"
            onkeydown="handleSearchEnter(event)">
    </div>
</div>

        <!-- Product Grid -->
        <div class="pos-product-grid" id="productGrid">
            <!-- Products will be rendered by JS -->
        </div>
    </div>

    <!-- Cart Panel -->
    <div class="pos-cart">
        <div class="invoice-tabs">
    <div id="invoiceTabs"></div>

    <button type="button" class="btn-new-invoice" onclick="createInvoice()">
        +
    </button>
</div>
        <div class="cart-header">
            <h5>
                <i class="fas fa-shopping-basket"></i>
                Giỏ hàng
                <span class="cart-count" id="cartCount">0</span>
            </h5>
            <button class="btn-clear" onclick="clearCart()">
                <i class="fas fa-trash-alt"></i> Xóa giỏ
            </button>
        </div>

        <div class="cart-items" id="cartItems">
            <div class="cart-empty" id="cartEmpty">
                <i class="fas fa-shopping-cart"></i>
                <p>Giỏ hàng trống</p>
                <small>Click sản phẩm để thêm vào</small>
            </div>
        </div>
        <div class="p-2 border-top">
            <label class="form-label mb-1">Khuyến mãi</label>
            <select id="promotionSelect" class="form-select form-select-sm" onchange="applyPromotion()">
               <option value="">Không áp dụng</option>
            </select>
        </div>
        <div class="cart-summary" id="cartSummary" style="display:none;">
            @if(isset($khachHang))
            <div class="summary-row">
                <span>Điểm hiện có:</span>
                <strong class="text-primary">
                    {{ number_format($khachHang->diem_tich_luy ?? 0) }}
                </strong>
            </div>
            @endif

            <div class="summary-row">
                <span>Điểm nhận thêm:</span>
                <strong class="text-success" id="diemThuDuoc">
                    +0
                </strong>
            </div>

            <div class="summary-row">
                <span>Điểm hiện có</span>
               <strong id="customerPoint">0</strong>
            </div>

            <div class="money-input-group mt-2">
                <label>Sử dụng điểm</label>
                <input
                type="number"
                id="usePoint"
                class="form-control"
                value="0"
                min="0"
                oninput="calculateTotal(); calculateChange();">
            </div>

            <div class="summary-row text-danger">
                <span>Giảm từ điểm</span>
                <span id="pointDiscount">0đ</span>
            </div>
            <div class="summary-row">
                <span>Tạm tính</span>
                <span id="subtotal">0đ</span>
            </div>
            <div class="summary-row discount-row">
                <span>Giảm giá</span>
                <span id="discount">-0đ</span>
            </div>
            <div class="summary-row total">
                <span>Tổng cộng</span>
                <span id="totalAmount">0đ</span>
            </div>
        </div>

        <div class="pos-payment">
            
            <div class="money-input-group">
                <label>Khách đưa tiền</label>
                <input type="number" id="customerMoney" placeholder="0" oninput="calculateChange()">
            </div>
            <div class="change-display">
                <span class="change-label">Tiền thừa</span>
                <span class="change-amount" id="changeAmount">0đ</span>
            </div>

            <div class="payment-methods">
                <button class="pay-btn active" data-method="cash" onclick="selectPayment('cash')">
                    <i class="fas fa-money-bill-wave"></i>
                    Tiền mặt
                </button>
                <button class="pay-btn" data-method="transfer" onclick="selectPayment('transfer'); ">
                    <i class="fas fa-university"></i>
                    Chuyển khoản
                </button>
                <button class="pay-btn" data-method="card" onclick="selectPayment('card')">
                    <i class="fas fa-credit-card"></i>
                    Quẹt thẻ
                </button>
            </div>

            <button class="btn-checkout" onclick="processPayment()">
                <i class="fas fa-check-circle"></i>
                Thanh toán
                <span class="shortcut-hint">F9</span>
            </button>
        </div>
    </div>
</div>

<!-- ── POS Footer ── -->
<footer class="pos-footer">
    <span class="shortcut-hint"><kbd>F1</kbd> Thực phẩm</span>
    <span class="shortcut-hint"><kbd>F4</kbd> Đồ uống</span>
    <span class="shortcut-hint"><kbd>F8</kbd> Điện tử</span>
    <span class="shortcut-hint"><kbd>F9</kbd> Thanh toán</span>
    <span class="shortcut-hint"><kbd>Esc</kbd> Xóa giỏ</span>
</footer>

<!-- Toast -->
<div class="pos-toast" id="posToast">
    <i class="fas fa-check-circle"></i>
    <span id="toastMessage">Đã thêm vào giỏ hàng</span>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/js/all.min.js"></script>

<script>
// ─────────────────────────────────────────────
// Product Data (Mock)
// ─────────────────────────────────────────────
let products = [];

let promotions = [];
let selectedPromotion = null;
let discountAmount = 0;
async function loadProducts() {
    try {
        let url = '/nhan-vien/ban-hang/san-pham';
        const params = new URLSearchParams();

        const search = document.getElementById('searchInput').value;

        if (search) {
            params.append('q', search);
        }

        if (currentCategory !== 'all') {
            params.append('id_danh_muc', currentCategory);
        }

        if (params.toString()) {
            url += '?' + params.toString();
        }

        const response = await fetch(url);
        products = await response.json();

        renderProducts();
    } catch (error) {
        console.error('Lỗi tải sản phẩm:', error);
    }
}
// ─────────────────────────────────────────────
// Cart State
// ─────────────────────────────────────────────
let cart = [];
let currentCategory = 'all';
let selectedPayment = 'cash';
let invoiceTabs = [
    {
        id: 1,
        name: 'HD1',
        cart: [],
        customer: null,
        promotion: null,
        payment: 'cash',
        usePoint: 0,
        customerMoney: ''
    }
];

function getCurrentInvoice() {
    return invoiceTabs[currentTab];
}
function closePaidInvoiceTab() {
    // Xóa tab vừa thanh toán
    invoiceTabs.splice(currentTab, 1);

    // Nếu vẫn còn tab khác thì tự chuyển về tab gần nhất
    if (invoiceTabs.length > 0) {
        if (currentTab >= invoiceTabs.length) {
            currentTab = invoiceTabs.length - 1;
        }
    } else {
        // Nếu hết tab thì tạo hóa đơn mới trống
        tabIndex++;

        invoiceTabs.push({
            id: tabIndex,
            name: 'HD' + tabIndex,
            cart: [],
            customer: null,
            promotion: null,
            payment: 'cash',
            usePoint: 0,
            customerMoney: ''
        });

        currentTab = 0;
    }

    loadCurrentInvoiceForm();
    renderInvoiceTabs();
    renderCart();
}

function getCurrentCart() {
    return getCurrentInvoice().cart;
}

let currentTab = 0;
let tabIndex = 1;

function renderInvoiceTabs() {
    const box = document.getElementById('invoiceTabs');

    box.innerHTML = invoiceTabs.map((tab, index) => `
        <div class="invoice-tab ${index === currentTab ? 'active' : ''}"
             onclick="switchInvoiceTab(${index})">
            ${tab.name}
            ${invoiceTabs.length > 1 ? `
                <span class="close-tab"
                      onclick="event.stopPropagation(); closeInvoiceTab(${index})">×</span>
            ` : ''}
        </div>
    `).join('');
}

function switchInvoiceTab(index) {
    saveCurrentInvoiceForm();

    currentTab = index;

    loadCurrentInvoiceForm();
    renderInvoiceTabs();
    renderCart();
}
function saveCurrentInvoiceForm() {
    const invoice = getCurrentInvoice();

    invoice.customer = selectedCustomer;
    invoice.promotion = selectedPromotion;
    invoice.payment = selectedPayment;
    invoice.usePoint = parseInt(document.getElementById('usePoint')?.value || 0);
    invoice.customerMoney = document.getElementById('customerMoney')?.value || '';
}

function loadCurrentInvoiceForm() {
    const invoice = getCurrentInvoice();

    selectedCustomer = invoice.customer;
    selectedPromotion = invoice.promotion;
    selectedPayment = invoice.payment || 'cash';

    document.getElementById('usePoint').value = invoice.usePoint || 0;
    document.getElementById('customerMoney').value = invoice.customerMoney || '';

    if (selectedCustomer) {
        document.getElementById('selectedCustomerId').value = selectedCustomer.id;
        document.getElementById('customerPoint').innerText = selectedCustomer.diem_tich_luy ?? 0;
        document.getElementById('selectedCustomerText').innerHTML =
            `<strong>${selectedCustomer.ten_khach_hang}</strong> - ${selectedCustomer.so_dien_thoai ?? ''} - Điểm: ${selectedCustomer.diem_tich_luy ?? 0}`;
        document.getElementById('selectedCustomerBox').style.display = 'block';
    } else {
        document.getElementById('selectedCustomerId').value = '';
        document.getElementById('customerPoint').innerText = '0';
        document.getElementById('selectedCustomerBox').style.display = 'none';
    }

    document.getElementById('promotionSelect').value = selectedPromotion ? selectedPromotion.id : '';

    document.querySelectorAll('.pay-btn').forEach(btn => {
        btn.classList.toggle('active', btn.dataset.method === selectedPayment);
    });

    calculateTotal();
    calculateChange();
}

function createInvoice() {
    if (invoiceTabs.length >= 5) {
        showToast('Chỉ được mở tối đa 5 hóa đơn!', 'error');
        return;
    }

    tabIndex++;

    invoiceTabs.push({
    id: tabIndex,
    name: 'HD' + tabIndex,
    cart: [],
    customer: null,
    promotion: null,
    payment: 'cash',
    usePoint: 0,
    customerMoney: ''
});

    currentTab = invoiceTabs.length - 1;
    renderInvoiceTabs();
}

function closeInvoiceTab(index) {
    if (invoiceTabs.length <= 1) return;

    if (!confirm('Đóng hóa đơn này?')) return;

    invoiceTabs.splice(index, 1);

    if (currentTab >= invoiceTabs.length) {
        currentTab = invoiceTabs.length - 1;
    }

    renderInvoiceTabs();
    renderCart();
}

// ─────────────────────────────────────────────
// Clock
// ─────────────────────────────────────────────
function updateClock() {
    const now = new Date();
    const date = now.toLocaleDateString('vi-VN');
    const time = now.toLocaleTimeString('vi-VN');
    document.getElementById('posDate').textContent = date;
    document.getElementById('posTime').textContent = time;
}
updateClock();
setInterval(updateClock, 1000);

// ─────────────────────────────────────────────
// Format Currency
// ─────────────────────────────────────────────
function formatCurrency(num) {
    return new Intl.NumberFormat('vi-VN').format(num) + 'đ';
}
function getCurrentTotal() {
    const cart = getCurrentCart();
    const subtotal = cart.reduce(
        (sum, item) => sum + Number(item.gia_ban) * item.qty,
        0
    );

    const promotionDiscount = tinhTienGiam(subtotal);

    const customerPoint = selectedCustomer
        ? Number(selectedCustomer.diem_tich_luy)
        : 0;

    let usePoint = parseInt(document.getElementById("usePoint").value) || 0;
    usePoint = Math.min(usePoint, customerPoint);

    const maxUsePoint = Math.floor(
        Math.max(0, subtotal - promotionDiscount) / 100
    );

    usePoint = Math.min(usePoint, maxUsePoint);

    const pointDiscount = usePoint * 100;

    return Math.max(0, subtotal - promotionDiscount - pointDiscount);
}

function showQrPayment() {
    const cart = getCurrentCart();

if (cart.length === 0) {
    showToast('Giỏ hàng trống!', 'error');
    return;
}

    const total = getCurrentTotal();
    const content = 'SMARTMART' + Date.now();

    const bankId = 'MB';
    const accountNo = '0345511263';
    const accountName = 'NGUYEN KHAC HUY';

    const qrUrl =
        `https://img.vietqr.io/image/${bankId}-${accountNo}-compact2.png` +
        `?amount=${total}` +
        `&addInfo=${encodeURIComponent(content)}` +
        `&accountName=${encodeURIComponent(accountName)}`;

    document.getElementById('qrPaymentImg').src = qrUrl;
    document.getElementById('qrPaymentAmount').innerText = formatCurrency(total);
    document.getElementById('qrPaymentContent').innerText = content;

    new bootstrap.Modal(document.getElementById('qrPaymentModal')).show();
}
function confirmTransferPaid() {

    const modal = bootstrap.Modal.getInstance(
        document.getElementById('qrPaymentModal')
    );

    if(modal){
        modal.hide();
    }

    processPayment(true);

}



// ─────────────────────────────────────────────
// Render Products
// ─────────────────────────────────────────────
function renderProducts(filter = '') {
    const grid = document.getElementById('productGrid');
    let filtered = [...products];

    if (filter) {
        const q = filter.toLowerCase();
        filtered = filtered.filter(p =>
            (p.ten_san_pham && p.ten_san_pham.toLowerCase().includes(q)) ||
            (p.ma_hang && String(p.ma_hang).toLowerCase().includes(q)) ||
            (p.ma_vach && String(p.ma_vach).toLowerCase().includes(q))
        );
    }

    if (filtered.length === 0) {
        grid.innerHTML = `
            <div style="grid-column:1/-1;text-align:center;color:#999;padding:40px 0;">
                <i class="fas fa-search" style="font-size:40px;margin-bottom:12px;display:block;"></i>
                <p>Không tìm thấy sản phẩm</p>
            </div>
        `;
        return;
    }

    grid.innerHTML = filtered.map(p => {
        const ten = p.ten_san_pham ?? 'Chưa có tên';

        const gia = Number(
            p.gia_ban ??
            p.gia ??
            p.don_gia ??
            p.gia_ban_le ??
            0
        );

        const ton = Number(p.so_luong_ton_kho ?? 0);

        const hinh = p.hinh_anh
            ? '/' + p.hinh_anh.replace(/^\/+/, '')
            : 'https://via.placeholder.com/300x300?text=No+Image';

        return `
            <div class="pos-product-card" onclick="addToCart(${p.id})">
                <div class="product-img">
                    <img src="${hinh}" alt="${ten}">
                </div>

                <div class="product-info">
                    <div class="product-name">${ten}</div>

                    <div class="product-price">
                        ${formatCurrency(gia)}
                    </div>

                    <div class="product-stock ${ton < 5 ? 'low' : ''}">
                        ${ton < 5 ? '⚠ Sắp hết' : 'Còn ' + ton}
                    </div>
                </div>
            </div>
        `;
    }).join('');
}

// ─────────────────────────────────────────────
// Category Switch
// ─────────────────────────────────────────────
function switchCategory(cat) {
    currentCategory = cat;

    document.querySelectorAll('.cat-btn').forEach(btn => {
        btn.classList.toggle('active', btn.dataset.category == cat);
    });

    loadProducts();
}

// ─────────────────────────────────────────────
// Filter Products
// ─────────────────────────────────────────────
function filterProducts() {
    loadProducts();
}

// ─────────────────────────────────────────────
// Add to Cart
// ─────────────────────────────────────────────
function addToCart(id) {
    const product = products.find(p => p.id === id);
    if (!product) return;

    const cart = getCurrentCart();
    const existing = cart.find(item => item.id === id);

    if (existing) {
        if (existing.qty + 1 > Number(product.so_luong_ton_kho)) {
            showToast('Số lượng vượt quá tồn kho!', 'error');
            return;
        }
        existing.qty += 1;
    } else {
        cart.push({
            id: product.id,
            ten_san_pham: product.ten_san_pham,
            gia_ban: Number(product.gia_ban),
            so_luong_ton_kho: Number(product.so_luong_ton_kho),
            hinh_anh: product.hinh_anh,
            qty: 1
        });
    }

    renderCart();
    showToast(`Đã thêm "${product.ten_san_pham}" vào giỏ hàng`);
}

// ─────────────────────────────────────────────
// Render Cart
// ─────────────────────────────────────────────
function renderCart() {
    const cart = getCurrentCart();
    const container = document.getElementById('cartItems');
    const count = document.getElementById('cartCount');
    const summary = document.getElementById('cartSummary');

    count.textContent = cart.reduce((s, i) => s + i.qty, 0);

    if (cart.length === 0) {
        container.innerHTML = `
            <div class="cart-empty" id="cartEmpty">
                <i class="fas fa-shopping-cart"></i>
                <p>Giỏ hàng trống</p>
                <small>Click sản phẩm để thêm vào</small>
            </div>
        `;
        summary.style.display = 'none';
        return;
    }

    summary.style.display = 'block';

    container.innerHTML = cart.map(item => {
        const ten = item.ten_san_pham ?? 'Chưa có tên';
        const gia = Number(item.gia_ban ?? 0);
        const hinh = item.hinh_anh ? item.hinh_anh : 'https://via.placeholder.com/80';

        return `
            <div class="cart-item">
                <div class="item-img">
                    <img src="${hinh}" alt="${ten}" style="width:100%;height:100%;object-fit:cover;border-radius:6px;">
                </div>
                <div class="item-details">
                    <div class="item-name">${ten}</div>
                    <div class="item-price">${formatCurrency(gia)}</div>
                </div>
                <div class="item-qty">
                    <button class="qty-btn" onclick="updateQuantity(${item.id}, -1)">
                        <i class="fas fa-minus"></i>
                    </button>
                    <span class="qty-num">${item.qty}</span>
                    <button class="qty-btn" onclick="updateQuantity(${item.id}, 1)">
                        <i class="fas fa-plus"></i>
                    </button>
                </div>
                <div class="item-total">${formatCurrency(gia * item.qty)}</div>
                <button class="btn-remove" onclick="removeFromCart(${item.id})">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        `;
    }).join('');

   calculateTotal();
   calculateChange();
}


function calculateTotal() {
    const cart = getCurrentCart();
    const subtotal = cart.reduce(
        (sum, item) => sum + Number(item.gia_ban) * item.qty,
        0
    );

    // Tiền giảm từ khuyến mãi
    const promotionDiscount = tinhTienGiam(subtotal);

    // Điểm hiện có của khách
    const customerPoint = selectedCustomer
        ? Number(selectedCustomer.diem_tich_luy)
        : 0;

    // Điểm khách nhập
    let usePoint =
        parseInt(document.getElementById("usePoint").value) || 0;

    // Không được vượt quá số điểm khách đang có
    usePoint = Math.min(usePoint, customerPoint);

    // Không được vượt quá số tiền còn lại sau khuyến mãi
    const maxUsePoint = Math.floor(
        Math.max(0, subtotal - promotionDiscount) / 100
    );

    usePoint = Math.min(usePoint, maxUsePoint);

    // Cập nhật lại ô nhập nếu người dùng nhập quá nhiều
    document.getElementById("usePoint").value = usePoint;

    // Tiền giảm từ điểm
    const pointDiscount = usePoint * 100;

    // Tổng thanh toán
    const total = Math.max(
        0,
        subtotal - promotionDiscount - pointDiscount
    );

    // Điểm được cộng sau khi thanh toán
    const diemThuDuoc = Math.floor(total / 10000);

    // Hiển thị
    document.getElementById("subtotal").innerText =
        formatCurrency(subtotal);

    document.getElementById("discount").innerText =
        "-" + formatCurrency(promotionDiscount);

    document.getElementById("pointDiscount").innerText =
        "-" + formatCurrency(pointDiscount);

    document.getElementById("totalAmount").innerText =
        formatCurrency(total);

    document.getElementById("diemThuDuoc").innerText =
        "+" + diemThuDuoc;

    calculateChange();
}

// ─────────────────────────────────────────────
// Update Quantity
// ─────────────────────────────────────────────
function updateQuantity(id, change) {
    const cart = getCurrentCart();
    const item = cart.find(i => i.id === id);
    if (!item) return;
    item.qty += change;
    if (item.qty <= 0) {
        removeFromCart(id);
        return;
    }
    renderCart();
}

// ─────────────────────────────────────────────
// Remove from Cart
// ─────────────────────────────────────────────
function removeFromCart(id) {
    invoiceTabs[currentTab].cart = getCurrentCart().filter(i => i.id !== id);
    renderCart();
}

// ─────────────────────────────────────────────
// Clear Cart
// ─────────────────────────────────────────────
function clearCart() {
    if (getCurrentCart().length === 0) return;
    invoiceTabs[currentTab].cart = [];

// reset khách hàng
selectedCustomer = null;
document.getElementById("selectedCustomerId").value = "";
document.getElementById("selectedCustomerBox").style.display = "none";
document.getElementById("customerPoint").innerText = "0";

// reset điểm
document.getElementById("usePoint").value = 0;

// reset khuyến mãi
selectedPromotion = null;
document.getElementById("promotionSelect").value = "";

// reset tiền
document.getElementById("customerMoney").value = "";
document.getElementById("changeAmount").innerText = "0đ";

renderCart();
calculateTotal();
calculateChange();

showToast("Đã xóa giỏ hàng");
}

// ─────────────────────────────────────────────
// Calculate Change
// ─────────────────────────────────────────────
function calculateChange() {
    const cart = getCurrentCart();
    const subtotal = cart.reduce(
        (sum, item) => sum + Number(item.gia_ban) * item.qty,
        0
    );

    const promotionDiscount =
        tinhTienGiam(subtotal);

    const customerPoint = selectedCustomer
    ? Number(selectedCustomer.diem_tich_luy)
    : 0;

let usePoint =
    parseInt(document.getElementById("usePoint").value) || 0;

usePoint = Math.min(usePoint, customerPoint);

const maxUsePoint = Math.floor(
    Math.max(0, subtotal - promotionDiscount) / 100
);

usePoint = Math.min(usePoint, maxUsePoint);

document.getElementById("usePoint").value = usePoint;

const pointDiscount = usePoint * 100;

    const total = Math.max(
    0,
    subtotal - promotionDiscount - pointDiscount
    );

    const customer =
        parseFloat(document.getElementById("customerMoney").value) || 0;

    const change =
        Math.max(0, customer - total);

    document.getElementById("changeAmount").innerText =
        formatCurrency(change);
}

// ─────────────────────────────────────────────
// Select Payment Method
// ─────────────────────────────────────────────
function selectPayment(method) {

    selectedPayment = method;
    getCurrentInvoice().payment = method;

    document.querySelectorAll('.pay-btn').forEach(btn => {
        btn.classList.toggle('active', btn.dataset.method === method);
    });

}

// ─────────────────────────────────────────────
// Process Payment
// ─────────────────────────────────────────────
async function processPayment(isTransferConfirmed = false) {
    const cart = getCurrentCart();
    if (cart.length === 0) {
        showToast('Giỏ hàng trống!', 'error');
        return;
    }
    if (selectedPayment === 'transfer' && !isTransferConfirmed) {

    showQrPayment();

    return;
}

  
const subtotal = cart.reduce(
    (sum, item) => sum + Number(item.gia_ban) * item.qty,
    0
);

const promotionDiscount =
    tinhTienGiam(subtotal);

const customerPoint = selectedCustomer
    ? Number(selectedCustomer.diem_tich_luy)
    : 0;

let usePoint =
    parseInt(document.getElementById("usePoint").value) || 0;

// Không được vượt quá điểm hiện có
usePoint = Math.min(usePoint, customerPoint);

// Không được vượt quá số tiền còn phải trả
const maxUsePoint = Math.floor(
    Math.max(0, subtotal - promotionDiscount) / 100
);

usePoint = Math.min(usePoint, maxUsePoint);

// cập nhật lại input
document.getElementById("usePoint").value = usePoint;

const pointDiscount = usePoint * 100;

const total = Math.max(
    0,
    subtotal - promotionDiscount - pointDiscount
);
let customer =
    parseFloat(document.getElementById("customerMoney").value) || 0;

if (selectedPayment === 'cash') {
    if (customer < total) {
        showToast('Tiền khách đưa chưa đủ.', 'error');
        return;
    }
} else {
    customer = total;
}

    // 

    try {
       const diemThuDuoc = Math.floor(total / 10000);

const response = await fetch('/nhan-vien/ban-hang/thanh-toan', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
    },
    body: JSON.stringify({
    cart: cart.map(item => ({
        id: item.id,
        qty: item.qty
    })),
    id_khach_hang: selectedCustomer ? selectedCustomer.id : null,
    id_khuyen_mai: selectedPromotion ? selectedPromotion.id : null,
    tien_khach_dua: customer,
    phuong_thuc_thanh_toan: selectedPayment,
    diem_su_dung: usePoint
})
});

        const data = await response.json();

        if (!response.ok || !data.success) {
            showToast(data.message || 'Thanh toán thất bại!', 'error');
            return;
        }

        alert(
            'Thanh toán thành công!\n' +
            'Mã hóa đơn: #' + data.hoa_don_id + '\n' +
            'Tổng tiền: ' + formatCurrency(total)
        );
        const hoaDonId = data.hoa_don_id;

// Đóng tab vừa thanh toán
closePaidInvoiceTab();

// Cập nhật lại sản phẩm
loadProducts();

// Hỏi có muốn in không
showPrintInvoiceDialog(hoaDonId);
    } catch (error) {
        console.error(error);
        showToast('Lỗi kết nối máy chủ!', 'error');
    }
}

// ─────────────────────────────────────────────
// Toast Notification
// ─────────────────────────────────────────────
function showToast(message, type = 'success') {
    const toast = document.getElementById('posToast');
    const msg = document.getElementById('toastMessage');
    msg.textContent = message;
    toast.style.background = type === 'error' ? '#d32f2f' : '#2e7d32';
    toast.classList.add('show');
    setTimeout(() => toast.classList.remove('show'), 2000);
}

// ─────────────────────────────────────────────
// Keyboard Shortcuts
// ─────────────────────────────────────────────
document.addEventListener('keydown', function(e) {
    // Ignore if user is typing in input
    if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA') return;

    switch (e.key) {
        case 'F1':
            e.preventDefault();
            switchCategory('thuc-pham');
            break;
        case 'F4':
            e.preventDefault();
            switchCategory('do-uong');
            break;
        case 'F8':
            e.preventDefault();
            switchCategory('dien-tu');
            break;
        case 'F9':
            e.preventDefault();
            processPayment();
            break;
        case 'Escape':
            clearCart();
            break;
        case 'Enter':
            if (e.target.id !== 'searchInput') {
                e.preventDefault();
                processPayment();
            }
            break;
    }
});
async function loadCategories() {
    try {
        const response = await fetch('/nhan-vien/ban-hang/danh-muc');
        const categories = await response.json();

        const bar = document.getElementById('categoryBar');

        categories.forEach(dm => {
            bar.innerHTML += `
                <button class="cat-btn" data-category="${dm.id}" onclick="switchCategory('${dm.id}')">
                    <i class="fas fa-tag"></i>
                    ${dm.ten_danh_muc}
                </button>
            `;
        });
    } catch (error) {
        console.error('Lỗi tải danh mục:', error);
    }
}
let selectedCustomer = null;
let customerSearchTimer = null;

function searchCustomers() {
    clearTimeout(customerSearchTimer);

    const keyword = document.getElementById('customerSearchInput').value.trim();
    const resultBox = document.getElementById('customerSearchResult');

    if (keyword.length < 2) {
        resultBox.style.display = 'none';
        resultBox.innerHTML = '';
        return;
    }

    customerSearchTimer = setTimeout(async () => {
        const response = await fetch('/nhan-vien/ban-hang/khach-hang?q=' + encodeURIComponent(keyword));
        const customers = await response.json();

        if (customers.length === 0) {
            resultBox.innerHTML = `<div class="p-2 text-muted">Không tìm thấy khách hàng</div>`;
            resultBox.style.display = 'block';
            return;
        }

        resultBox.innerHTML = customers.map(kh => `
            <div class="p-2 border-bottom" style="cursor:pointer"
                 onclick='selectCustomer(${JSON.stringify(kh)})'>
                <strong>${kh.ten_khach_hang}</strong>
                <br>
                <small>SĐT: ${kh.so_dien_thoai ?? '---'} | Điểm: ${kh.diem_tich_luy ?? 0}</small>
            </div>
        `).join('');

        resultBox.style.display = 'block';
    }, 300);
}

function selectCustomer(customer) {
    selectedCustomer = customer;
    getCurrentInvoice().customer = customer;
document.getElementById("customerPoint").innerText =
    customer.diem_tich_luy;

document.getElementById("usePoint").value = 0;


    document.getElementById('selectedCustomerId').value = customer.id;
    document.getElementById('customerSearchInput').value = '';
    document.getElementById('customerSearchResult').style.display = 'none';

    document.getElementById('selectedCustomerText').innerHTML =
        `<strong>${customer.ten_khach_hang}</strong> - ${customer.so_dien_thoai ?? ''} - Điểm: ${customer.diem_tich_luy ?? 0}`;

    document.getElementById('selectedCustomerBox').style.display = 'block';

    calculateTotal();
    calculateChange();
}

function clearSelectedCustomer() {
    selectedCustomer = null;
    getCurrentInvoice().customer = null;
    document.getElementById('selectedCustomerId').value = '';
    document.getElementById('selectedCustomerBox').style.display = 'none';
    document.getElementById("customerPoint").innerText = "0";
    document.getElementById("usePoint").value = 0;
    calculateTotal();
    calculateChange();
}
async function loadPromotions() {
    const response = await fetch('/nhan-vien/ban-hang/khuyen-mai');
    promotions = await response.json();

    const select = document.getElementById('promotionSelect');
    select.innerHTML = '<option value="">Không áp dụng</option>';

    promotions.forEach(km => {
        select.innerHTML += `
            <option value="${km.id}">
                ${km.ten_chuong_trinh}
            </option>
        `;
    });
}
function tinhTienGiam(subtotal) {
    const cart = getCurrentCart();
    if (!selectedPromotion) return 0;

    const type = String(selectedPromotion.loai_giam_gia || '')
        .trim()
        .toLowerCase();

    const minOrder = Number(selectedPromotion.don_hang_toi_thieu || 0) * 1000;
    const minQty = Number(selectedPromotion.so_luong_sp_toi_thieu || 0);
    const totalQty = cart.reduce((s, i) => s + Number(i.qty || 0), 0);

    if (subtotal < minOrder) return 0;
    if (minQty > 0 && totalQty < minQty) return 0;

    // Mua 1 tặng 1 / BOGO
    if (type === 'bogo') {
        let discount = 0;

        cart.forEach(item => {
            const qty = Number(item.qty || 0);
            const price = Number(item.gia_ban || 0);

            const freeQty = Math.floor(qty / 2);
            discount += freeQty * price;
        });

        return Math.min(discount, subtotal);
    }

    // Giảm phần trăm
    if (type === 'phan_tram' || type === 'percent') {

    let discount =
        subtotal * Number(selectedPromotion.gia_tri_giam || 0) / 100;

    if (selectedPromotion.giam_toi_da) {
        discount = Math.min(
            discount,
            Number(selectedPromotion.giam_toi_da)
        );
    }

    return Math.min(discount, subtotal);
}

    // Giảm tiền trực tiếp
    // Giảm tiền trực tiếp
const discount = Number(selectedPromotion.gia_tri_giam || 0) * 1000;
return Math.min(discount, subtotal);
}
function applyPromotion() {
    const id = document.getElementById('promotionSelect').value;

    selectedPromotion = promotions.find(km => String(km.id) === String(id)) || null;
    getCurrentInvoice().promotion = selectedPromotion;

    renderCart();
    calculateTotal();
    calculateChange();
} 

// hàm tìm kiếm sản phẩm theo mã vạch khi nhấn Enter
async function handleSearchEnter(event) {
    if (event.key !== 'Enter') return;

    event.preventDefault();

    const keyword = event.target.value.trim();
    if (!keyword) return;

    try {
        const response = await fetch('/nhan-vien/ban-hang/san-pham?q=' + encodeURIComponent(keyword));
        const data = await response.json();

        const product = data.find(p =>
            String(p.ma_vach || '').toLowerCase() === keyword.toLowerCase()
        );

        if (!product) {
            showToast('Không tìm thấy mã vạch này!', 'error');
            return;
        }

        products = data;
        addToCart(product.id);

        event.target.value = '';
        loadProducts();

        setTimeout(() => {
            event.target.focus();
        }, 100);

    } catch (error) {
        console.error(error);
        showToast('Lỗi quét mã vạch!', 'error');
    }
}

function capNhatDiem() {
    const tongTien = parseInt(document.getElementById('tongTien').value || 0);
    const diem = Math.floor(tongTien / 10000);

    const el = document.getElementById('diemThuDuoc');
    if (el) {
        el.innerText = '+' + diem;
    }
}

async function saveCustomerQuick() {
    try {
        const response = await fetch(
            "{{ route('nhan-vien.khach-hang.them-nhanh') }}",
            {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": document.querySelector(
                        'meta[name="csrf-token"]'
                    ).content,
                    "Accept": "application/json"
                },
                body: JSON.stringify({
                    ten_khach_hang: document.getElementById('kh_ten').value,
                    so_dien_thoai: document.getElementById('kh_sdt').value,
                    email: document.getElementById('kh_email').value,
                    dia_chi: document.getElementById('kh_dia_chi').value
                })
            }
        );

       if (!response.ok) {
    const data = await response.json();

    if (response.status === 422) {
        let errors = [];

        Object.values(data.errors).forEach(arr => {
            errors.push(...arr);
        });

        showToast(errors.join(' | '), 'error');
        return;
    }

    throw new Error(data.message || "Có lỗi xảy ra");
}

        const data = await response.json();

        if (data.success) {
            document.getElementById('selectedCustomerId').value =
                data.khach_hang.id;

            document.getElementById('customerSearchInput').value =
                data.khach_hang.ten_khach_hang + ' - ' +
                data.khach_hang.so_dien_thoai;

            bootstrap.Modal.getInstance(
                document.getElementById('addCustomerModal')
            ).hide();

            showToast('Thêm khách hàng thành công!', 'success');
        } else {
            showToast(data.message || 'Có lỗi xảy ra', 'danger');
        }

    } catch (error) {
        console.error(error);
        showToast('Không thể thêm khách hàng', 'danger');
    }
}
function showPrintInvoiceDialog(hoaDonId) {

    document.getElementById('btnPrintInvoice').onclick = function () {

        window.open(
            '/nhan-vien/hoa-don/' + hoaDonId + '/in',
            '_blank'
        );

        bootstrap.Modal
            .getInstance(document.getElementById('printInvoiceModal'))
            .hide();

    };

    new bootstrap.Modal(
        document.getElementById('printInvoiceModal')
    ).show();

}
// ─────────────────────────────────────────────
// Init
// ─────────────────────────────────────────────
loadCategories();
loadProducts();
loadPromotions();
renderInvoiceTabs();
</script>
<div class="modal fade" id="qrPaymentModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">
                    <i class="fas fa-qrcode me-2"></i>QR thanh toán
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body text-center">
                <img id="qrPaymentImg" src="" style="width:280px;max-width:100%;" class="mb-3">

                <h5 class="fw-bold text-success" id="qrPaymentAmount">0đ</h5>
                <p class="text-muted mb-1">Ngân hàng: MB Bank</p>
                <p class="text-muted mb-1">STK: 0123456789</p>
                <p class="text-muted">Nội dung: <strong id="qrPaymentContent">SMARTMART</strong></p>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                <button type="button" class="btn btn-success" onclick="confirmTransferPaid()">
    Đã nhận tiền
</button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="printInvoiceModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">
                    <i class="fas fa-check-circle me-2"></i>
                    Thanh toán thành công
                </h5>
            </div>

            <div class="modal-body text-center">

                <i class="fas fa-receipt text-success"
                   style="font-size:60px;margin-bottom:15px;"></i>

                <h5>Thanh toán thành công!</h5>

                <p class="text-muted">
                    Bạn có muốn in hóa đơn không?
                </p>

            </div>

            <div class="modal-footer">

                <button
                    class="btn btn-secondary"
                    data-bs-dismiss="modal">

                    Không

                </button>

                <button
                    id="btnPrintInvoice"
                    class="btn btn-success">

                    <i class="fas fa-print me-1"></i>

                    In hóa đơn

                </button>

            </div>

        </div>
    </div>
</div>
</body>
</html>
