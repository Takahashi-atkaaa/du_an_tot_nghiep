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
    padding-bottom: 0;
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
.pos-product-card {
    position: relative;
}

.pos-product-card.has-promotion {
    border: 2px solid #dc3545;
}

.product-promotion-badge {
    position: absolute;
    top: 8px;
    left: 8px;
    z-index: 10;

    background: #dc3545;
    color: #fff;

    font-size: 11px;
    font-weight: 700;

    padding: 5px 9px;

    border-radius: 20px;

    box-shadow:
        0 2px 6px rgba(0, 0, 0, 0.18);
}

.product-old-price {
    color: #999;
    font-size: 12px;

    text-decoration: line-through;

    margin-bottom: 2px;
}

.product-sale-price {
    color: #dc3545 !important;
    font-weight: 700;
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



/* ===== POS CART REDESIGN: compact list + fixed checkout ===== */
.pos-cart {
    width: 390px;
    min-width: 390px;
    height: calc(100vh - 86px);
    overflow: hidden;
}

.invoice-tabs,
.cart-header {
    flex: 0 0 auto;
}

.cart-items {
    flex: 1 1 auto;
    min-height: 120px;
    overflow-y: auto;
    overscroll-behavior: contain;
    background: #fff;
    padding: 4px 8px 8px;
}

.cart-bottom {
    flex: 0 0 auto;
    background: #fff;
    border-top: 1px solid var(--pos-border);
    box-shadow: 0 -8px 24px rgba(15, 95, 42, .08);
    z-index: 5;
}

.cart-promotion {
    padding: 8px 12px;
    background: #f8faf9;
    border-bottom: 1px solid #edf1ee;
}

.cart-promotion .form-label {
    margin-bottom: 3px !important;
}

.cart-promotion .form-select {
    height: 34px;
    font-size: 12px;
    padding-top: 4px;
    padding-bottom: 4px;
}

.cart-item {
    position: relative;
    min-height: 62px;
    margin-top: 5px;
    padding: 7px 34px 7px 7px;
    border: 1px solid #edf1ee;
    border-radius: 11px;
    background: #fff;
    display: grid;
    grid-template-columns: 36px minmax(0, 1fr) auto;
    grid-template-areas:
        "image info total"
        "image controls total";
    column-gap: 8px;
    row-gap: 3px;
    align-items: center;
}

.cart-item:hover {
    border-color: #b8ddc3;
    background: #fbfefc;
}

.item-img {
    grid-area: image;
    width: 36px;
    height: 36px;
    border-radius: 8px;
}

.item-details {
    grid-area: info;
    min-width: 0;
}

.item-name {
    font-size: 12px;
    line-height: 15px;
    font-weight: 800;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.item-price {
    margin-top: 1px;
    font-size: 10px;
}

.item-qty {
    grid-area: controls;
    display: flex;
    align-items: center;
    gap: 5px;
    min-width: 0;
}

.qty-btn {
    width: 22px;
    height: 22px;
    padding: 0;
    border-radius: 7px;
    font-size: 10px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}

.qty-num {
    min-width: 18px;
    font-size: 12px;
}

.item-total {
    grid-area: total;
    align-self: center;
    white-space: nowrap;
    font-size: 12px;
}

.btn-remove {
    position: absolute;
    top: 50%;
    right: 7px;
    transform: translateY(-50%);
    width: 24px;
    height: 24px;
    padding: 0;
    border-radius: 7px;
}

.btn-remove:hover {
    background: #fee2e2;
}

.cart-summary {
    padding: 8px 12px;
    max-height: 205px;
    overflow-y: auto;
    background: #fbfcfb;
}

.summary-row {
    font-size: 12px;
    margin-bottom: 4px;
}

.summary-row.total {
    margin-top: 5px;
    padding-top: 6px;
    font-size: 16px;
}

.cart-summary .money-input-group {
    margin: 5px 0 6px !important;
}

.cart-summary .money-input-group label {
    margin-bottom: 3px;
    font-size: 11px;
}

.cart-summary .money-input-group input {
    height: 34px;
    padding: 5px 9px;
}

.pos-payment {
    padding: 8px 12px 10px;
}

.pos-payment .money-input-group {
    margin-bottom: 6px;
}

.pos-payment .money-input-group label {
    margin-bottom: 3px;
    font-size: 11px;
}

.pos-payment .money-input-group input {
    height: 36px;
    padding: 5px 10px;
}

.change-display {
    padding: 6px 10px;
    margin-bottom: 6px;
    border-radius: 10px;
}

.payment-methods {
    grid-template-columns: repeat(2, 1fr);
    gap: 6px;
    margin-bottom: 7px;
}

.pay-btn {
    min-height: 38px;
    padding: 6px 4px;
    flex-direction: row;
    justify-content: center;
    gap: 6px;
    border-radius: 10px;
}

.pay-btn i {
    font-size: 13px;
}

.btn-checkout {
    height: 42px;
    border-radius: 11px;
    font-size: 14px;
}

@media (max-width: 1200px) {
    .pos-cart {
        width: 360px;
        min-width: 360px;
    }
}

@media (max-height: 760px) {
    .cart-summary {
        max-height: 155px;
    }

    .cart-item {
        min-height: 56px;
        padding-top: 5px;
        padding-bottom: 5px;
    }
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
    <strong>
        {{ auth()->user()->ho_ten ?? 'Nhân viên' }}
    </strong>

    <small>
        @if(isset($caHienTai))
            {{ $caHienTai->ten_ca }}

            (
            {{ \Carbon\Carbon::parse($caHienTai->gio_bat_dau)->format('H:i') }}
            -
            {{ \Carbon\Carbon::parse($caHienTai->gio_ket_thuc)->format('H:i') }}
            )
        @else
            Chưa xác định ca
        @endif
    </small>
</div>
        <a href="{{ url('nhan-vien/') }}" class="btn-exit">
            <i class="fas fa-sign-out-alt"></i> Thoát
        </a>
        <button type="button" class="btn-exit" onclick="openDonChoPayOS()" title="Đơn chờ PayOS">
            <i class="fas fa-qrcode"></i> Đơn chờ
        </button>
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

<!-- Modal: Đơn chờ thanh toán PayOS -->
<div class="modal fade" id="donChoPayOSModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-warning text-dark border-0">
                <h5 class="modal-title fw-bold">
                    <i class="fas fa-qrcode me-2"></i>Đơn đang chờ thanh toán PayOS
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0">
                <div id="donChoPayOSList" style="max-height: 60vh; overflow-y: auto;">
                    <div class="text-center text-muted py-5">
                        <i class="fas fa-spinner fa-spin"></i> Đang tải...
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Đóng</button>
                <button type="button" class="btn btn-warning" onclick="loadDonChoPayOS()">
                    <i class="fas fa-rotate"></i> Tải lại
                </button>
            </div>
        </div>
    </div>
</div>
<!-- ── POS Body ── -->
<div class="pos-body">

    <!-- Sidebar -->
    <nav class="pos-sidebar">
        @if (auth()->user()->id_vai_tro == 1 || auth()->user()->id_vai_tro == 2)
            <a href="{{ url('/admin/dashboard') }}" class="nav-btn" title="Dashboard">
                <i class="bi bi-arrow-left-circle"></i>
                <span>Quản trị</span>
            </a>
        @else
            <a href="{{ url('/dashboard') }}" class="nav-btn" title="Dashboard">
                <i class="bi bi-arrow-left-circle"></i>
                <span>Home</span>
            </a>
        @endif

        @if (auth()->user()->id_vai_tro == 3 || auth()->user()->id_vai_tro == 4)
            <a href="{{ url('/trang-chu') }}" class="nav-btn" title="trang chủ">
                <i class="fas fa-user-tie"></i>
                <span>NV</span>
            </a>
        @endif

        <div class="nav-divider"></div>
        <button class="nav-btn active" title="Bán hàng" onclick="switchCategory('all')">
            <i class="fas fa-shopping-cart"></i>
            <span>Bán</span>
        </button>
        <button class="nav-btn" title="Hóa đơn" onclick="window.location='{{ url('/hoa-don') }}'">
            <i class="fas fa-file-invoice"></i>
            <span>HD</span>
        </button>
        <button class="nav-btn" title="Sản phẩm" onclick="window.location='{{ url('/san-pham') }}'">
            <i class="fas fa-box"></i>
            <span>SP</span>
        </button>
        <button class="nav-btn" title="Khách hàng" onclick="window.location='{{ url('/khach-hang') }}'">
            <i class="fas fa-users"></i>
            <span>KH</span>
        </button>
        <div class="nav-divider"></div>
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
            placeholder="Quét mã vạch hoặc tìm sản phẩm..."
            autocomplete="off"
            inputmode="text"
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
        <div class="cart-bottom">
        <div class="cart-promotion">
            <label class="form-label mb-1">
                <i class="fas fa-user-tie me-1"></i>Người bán
            </label>
            <select id="sellerSelect" class="form-select form-select-sm" onchange="applySeller()">
                <option value="">-- Chọn người bán --</option>
            </select>
        </div>
        <div class="cart-promotion">
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
                <input
    type="text"
    id="customerMoney"
    placeholder="0"
    inputmode="numeric"
    oninput="formatMoneyInput(this)">
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
            
                <button class="pay-btn" data-method="payos" onclick="selectPayment('payos')">
                    <i class="fas fa-qrcode"></i>
                    PayOS
                </button>
            </div>

            <button class="btn-checkout" onclick="processPayment()">
                <i class="fas fa-check-circle"></i>
                Thanh toán
                <span class="shortcut-hint">F9</span>
            </button>
        </div>
        </div><!-- /.cart-bottom -->
    </div>
</div>

<!-- ── POS Footer ── -->


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
const productListUrl = '{{ route('nhan-vien.ban-hang.san-pham') }}';
const categoryListUrl = '{{ route('nhan-vien.ban-hang.danh-muc') }}';

const customerListUrl = '{{ route('nhan-vien.ban-hang.khach-hang') }}';
const promotionListUrl = '{{ route('nhan-vien.ban-hang.khuyen-mai') }}';
const checkoutUrl = '{{ route('nhan-vien.ban-hang.thanh-toan') }}';
const sellerListUrl = '{{ route('nhan-vien.ban-hang.nhan-vien') }}';
const invoiceListUrl = '{{ url('/hoa-don') }}';

function resolveImageUrl(path) {
    if (!path) {
        return 'https://via.placeholder.com/300x300?text=No+Image';
    }

    const value = String(path).trim();

    if (/^https?:\/\//i.test(value)) {
        return value;
    }

    const normalized = value.replace(/^\/+/, '');

    if (!normalized) {
        return 'https://via.placeholder.com/300x300?text=No+Image';
    }

    if (normalized.startsWith('storage/')) {
        return '/' + normalized;
    }

    if (normalized.startsWith('public/')) {
        return '/' + normalized.replace(/^public\//, '');
    }

    if (normalized.startsWith('uploads/')) {
        return '/' + normalized;
    }

    return '/' + normalized;
}

async function loadProducts() {
    try {
        let url = productListUrl;
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

        const response = await fetch(url, {
            headers: { 'Accept': 'application/json' }
        });

        if (!response.ok) {
            throw new Error('Không thể tải sản phẩm từ server.');
        }

        products = await response.json();
        renderProducts();
    } catch (error) {
        console.error('Lỗi tải sản phẩm:', error);
        showToast('Không thể tải sản phẩm. Vui lòng kiểm tra đăng nhập hoặc route.', 'error');
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
        customerMoney: '',
        sellerId: null,
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

        // Tab mới KHÔNG reset sellerId — copy từ lựa chọn phiên hiện tại
        // (đã lưu trong localStorage khi người dùng chọn người bán).
        const rememberedSellerId = getRememberedSellerId();
        invoiceTabs.push({
            id: tabIndex,
            name: 'HD' + tabIndex,
            cart: [],
            customer: null,
            promotion: null,
            payment: 'cash',
            usePoint: 0,
            customerMoney: '',
            sellerId: rememberedSellerId,
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
    document.getElementById('sellerSelect').value = invoice.sellerId ? String(invoice.sellerId) : '';

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

    // Tab mới sẽ lấy sellerId từ phiên hiện tại (đã được lưu trong localStorage).
    // Ưu tiên: sellerId của tab hiện tại → fallback rememberedSellerId.
    const prevSellerId = invoiceTabs[currentTab] ? invoiceTabs[currentTab].sellerId : null;
    const rememberedSellerId = getRememberedSellerId();
    const newInvoiceSellerId = prevSellerId ?? rememberedSellerId;

    invoiceTabs.push({
        id: tabIndex,
        name: 'HD' + tabIndex,
        cart: [],
        customer: null,
        promotion: null,
        payment: 'cash',
        usePoint: 0,
        customerMoney: '',
        sellerId: newInvoiceSellerId,
    });

    currentTab = invoiceTabs.length - 1;
    renderInvoiceTabs();
    // Đồng bộ dropdown người bán với sellerId của tab mới
    const select = document.getElementById('sellerSelect');
    if (select && newInvoiceSellerId) {
        select.value = String(newInvoiceSellerId);
    }
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

    const promotionDiscount = tinhTienGiam();

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
function renderProducts(source = products, filter = '') {
    const grid = document.getElementById('productGrid');
    let filtered = [...source];

    if (filter) {
        const q = filter.toLowerCase();

        filtered = filtered.filter(p => {
            const name =
                String(p.ten_san_pham || '').toLowerCase();

            const code =
                String(p.ma_hang || '').toLowerCase();

            const barcode =
                String(p.ma_vach || '').toLowerCase();

            return (
                name.includes(q) ||
                code.includes(q) ||
                barcode.includes(q)
            );
        });
    }

    if (filtered.length === 0) {
        grid.innerHTML = `
            <div
                style="
                    grid-column:1/-1;
                    text-align:center;
                    color:#999;
                    padding:40px 0;
                "
            >
                <i
                    class="fas fa-search"
                    style="
                        font-size:40px;
                        margin-bottom:12px;
                        display:block;
                    "
                ></i>

                <p>Không tìm thấy sản phẩm</p>
            </div>
        `;

        return;
    }

    grid.innerHTML = filtered.map(p => {

        const ten =
            String(
                p.ten_san_pham || 'Chưa có tên'
            ).trim();

        const gia = Number(
            p.gia_ban ??
            p.gia ??
            p.don_gia ??
            p.gia_ban_le ??
            0
        );

        const ton =
            Number(p.so_luong_ton_kho ?? 0);

        const hinh =
            resolveImageUrl(p.hinh_anh);

        /*
         * ID sản phẩm cha.
         */
        const productId = Number(
            p.product_id ??
            p.id_san_pham ??
            0
        );

        /*
         * p.id hiện tại là ID biến thể.
         */
        const variantId =
            Number(p.id);

        /*
         * Tìm khuyến mãi đang áp dụng
         * cho sản phẩm hoặc biến thể này.
         */
        const promotion = promotions.find(km => {

            const productIds = (
                km.id_san_phams || []
            ).map(Number);

            const variantIds = (
                km.id_bien_thes || []
            ).map(Number);

            const applyProduct =
                productIds.includes(productId);

            const applyVariant =
                variantIds.includes(variantId);

            return (
                applyProduct ||
                applyVariant
            );

        }) || null;

        /*
         * Badge khuyến mãi.
         */
        let promotionBadge = '';

        /*
         * Phần hiển thị giá.
         */
        let priceHtml = `
            <div class="product-price">
                ${formatCurrency(gia)}
            </div>
        `;

        if (promotion) {

            const type = String(
                promotion.loai_giam_gia || ''
            )
                .trim()
                .toLowerCase();

            const value = Number(
                promotion.gia_tri_giam || 0
            );

            /*
             * Khuyến mãi giảm phần trăm.
             */
            if (
                [
                    'percent',
                    'phan_tram',
                    'percentage'
                ].includes(type)
            ) {
                let giaSauGiam =
                    gia - (gia * value / 100);

                const giamToiDa = Number(
                    promotion.giam_toi_da || 0
                );

                if (giamToiDa > 0) {
                    const soTienGiam =
                        Math.min(
                            gia * value / 100,
                            giamToiDa
                        );

                    giaSauGiam =
                        gia - soTienGiam;
                }

                promotionBadge = `
                    <div class="product-promotion-badge">
                        <i class="fas fa-tags"></i>
                        GIẢM ${value}%
                    </div>
                `;

                priceHtml = `
                    <div class="product-old-price">
                        ${formatCurrency(gia)}
                    </div>

                    <div class="product-price product-sale-price">
                        ${formatCurrency(giaSauGiam)}
                    </div>
                `;
            }

            /*
             * Khuyến mãi giảm số tiền.
             */
            else if (
                [
                    'amount',
                    'fixed',
                    'tien_mat',
                    'so_tien',
                    'giam_tien'
                ].includes(type)
            ) {

                const giaSauGiam =
                    Math.max(
                        0,
                        gia - value
                    );

                promotionBadge = `
                    <div class="product-promotion-badge">
                        <i class="fas fa-tags"></i>
                        GIẢM ${formatCurrency(value)}
                    </div>
                `;

                priceHtml = `
                    <div class="product-old-price">
                        ${formatCurrency(gia)}
                    </div>

                    <div class="product-price product-sale-price">
                        ${formatCurrency(giaSauGiam)}
                    </div>
                `;
            }

            /*
             * Mua 1 tặng 1.
             */
            else if (
                type === 'bogo' ||
                type === 'mua_1_tang_1'
            ) {
                promotionBadge = `
                    <div class="product-promotion-badge">
                        <i class="fas fa-gift"></i>
                        MUA 1 TẶNG 1
                    </div>
                `;
            }

            /*
             * Các loại còn lại.
             */
            else {
                promotionBadge = `
                    <div class="product-promotion-badge">
                        <i class="fas fa-tags"></i>
                        ĐANG KHUYẾN MÃI
                    </div>
                `;
            }
        }

        return `
            <div
                class="
                    pos-product-card
                    ${promotion ? 'has-promotion' : ''}
                "
                onclick="addToCart(${p.id})"
            >

                ${promotionBadge}

                <div class="product-img">
                    <img
                        src="${hinh}"
                        alt="${ten}"
                        onerror="
                            this.onerror=null;
                            this.src='https://via.placeholder.com/300x300?text=No+Image';
                        "
                    >
                </div>

                <div class="product-info">

                    <div class="product-name">
                        ${ten}
                    </div>

                    ${priceHtml}

                    <div
                        class="
                            product-stock
                            ${ton < 5 ? 'low' : ''}
                        "
                    >
                        ${
                            ton < 5
                                ? '⚠ Sắp hết'
                                : 'Còn ' + ton
                        }
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
function formatMoneyInput(input) {

    // Lấy toàn bộ số
    let value = input.value.replace(/\D/g, '');

    if (value === '') {
        input.value = '';
        calculateChange();
        return;
    }

    // Format 100000 -> 100.000
    input.value = Number(value).toLocaleString('vi-VN');

    calculateChange();
}

// ─────────────────────────────────────────────
// Filter Products
// ─────────────────────────────────────────────
function filterProducts() {
    const keyword = document.getElementById('searchInput').value.trim();
    renderProducts(products, keyword);
}

// ─────────────────────────────────────────────
// Add to Cart
// ─────────────────────────────────────────────
function buildCartItemFromProduct(product) {
    const variantId = Number(product.id ?? product.bien_the_id ?? 0);

    return {
        row_key: 'variant_' + variantId,
        id: variantId,
        unit_id: null,
        product_id: product.id_san_pham || product.product_id || null,
        ten_san_pham: product.ten_san_pham || product.ten_bien_the || '',
        ten_don_vi: product.ten_don_vi || product.don_vi_tinh_hien_thi || '',
        gia_ban: Number(product.gia_ban || product.gia_ban_hien_thi || 0),
        gia_von: Number(product.gia_von || 0),
        hinh_anh: product.hinh_anh,
        available_qty: Number(product.so_luong_ton_kho || product.so_luong_ton || 0),
        ty_le_quy_doi: 1,
    };
}

function addToCart(id) {
    const product = products.find(
        p => String(p.id) === String(id)
    );

    if (!product) return;

    const cart = getCurrentCart();

    const existing = cart.find(
        item => String(item.id) === String(id)
    );

    if (existing) {
        if (
            existing.qty + 1 >
            Number(product.so_luong_ton_kho)
        ) {
            showToast(
                'Số lượng vượt quá tồn kho!',
                'error'
            );
            return;
        }

        existing.qty += 1;

    } else {
        cart.push({
            // ID biến thể
            id: Number(product.id),

            // ID sản phẩm cha
            product_id: Number(
                product.product_id ??
                product.id_san_pham ??
                product.id
            ),

            ten_san_pham: product.ten_san_pham,
            gia_ban: Number(product.gia_ban),
            so_luong_ton_kho:
                Number(product.so_luong_ton_kho),
            hinh_anh: product.hinh_anh,
            qty: 1
        });
    }

    renderCart();

    showToast(
        `Đã thêm "${product.ten_san_pham}" vào giỏ hàng`
    );
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
        const hinh = resolveImageUrl(item.hinh_anh);
        const donVi = item.ten_don_vi ? ` <span class="item-unit">(${item.ten_don_vi})</span>` : '';

        return `
            <div class="cart-item">
                <div class="item-img">
                    <img src="${hinh}" alt="${ten}" onerror="this.onerror=null;this.src='https://via.placeholder.com/80?text=No+Image';">
                </div>
                <div class="item-details" title="${ten}">
                    <div class="item-name">${ten}</div>
                    <div class="item-price">${formatCurrency(gia)} / sản phẩm</div>
                </div>
                <div class="item-qty">
                    <button type="button" class="qty-btn" aria-label="Giảm số lượng" onclick="updateQuantity(${item.id}, -1)">
                        <i class="fas fa-minus"></i>
                    </button>
                    <span class="qty-num">${item.qty}</span>
                    <button type="button" class="qty-btn" aria-label="Tăng số lượng" onclick="updateQuantity(${item.id}, 1)">
                        <i class="fas fa-plus"></i>
                    </button>
                </div>
                <div class="item-total">${formatCurrency(gia * item.qty)}</div>
                <button type="button" class="btn-remove" aria-label="Xóa sản phẩm" onclick="removeFromCart(${item.id})">
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
    const promotionDiscount = tinhTienGiam();

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

    const item = cart.find(
        i => String(i.id) === String(id)
    );

    if (!item) return;

    item.qty += change;

    if (item.qty <= 0) {
        removeFromCart(id);
        return;
    }

    // Không cho tăng vượt tồn kho
    if (item.qty > Number(item.so_luong_ton_kho)) {
        item.qty = Number(item.so_luong_ton_kho);

        showToast(
            'Số lượng vượt quá tồn kho!',
            'error'
        );

        return;
    }

    renderCart();
}

// ─────────────────────────────────────────────
// Remove from Cart
// ─────────────────────────────────────────────
function removeFromCart(id) {
    const cart = getCurrentCart();

    invoiceTabs[currentTab].cart = cart.filter(
        item => String(item.id) !== String(id)
    );

    renderCart();
    calculateTotal();
    calculateChange();
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

    const promotionDiscount = tinhTienGiam();

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

    const customerMoneyValue =
        document.getElementById("customerMoney").value;

    const customer = parseInt(
        String(customerMoneyValue).replace(/\D/g, ''),
        10
    ) || 0;

    const change = Math.max(0, customer - total);

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
    tinhTienGiam();

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
let customer = parseInt(
    String(
        document.getElementById("customerMoney").value || ''
    ).replace(/\D/g, ''),
    10
) || 0;

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

const response = await fetch(checkoutUrl, {
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
    id_nguoi_ban: (getCurrentInvoice().sellerId ?? null),
    id_khuyen_mai: selectedPromotion ? selectedPromotion.id : null,
    tien_khach_dua: parseInt(
        document.getElementById('customerMoney')
        .value
        .replace(/\D/g, '')
) || 0,
    phuong_thuc_thanh_toan: selectedPayment,
    diem_su_dung: usePoint
})
});

        const data = await response.json();

        if (!response.ok || !data.success) {
            showToast(data.message || 'Thanh toán thất bại!', 'error');
            return;
        }

        const hoaDonId = data.hoa_don_id;

        if (data.redirect_to_payos) {
            showToast('Đang tạo link thanh toán PayOS...', 'success');
            await redirectToPayOS(hoaDonId);
            return;
        }

        showToast(
            'Thanh toán thành công! Mã hóa đơn #' + hoaDonId + ' đang mở bản in.',
            'success'
        );

// Đóng tab vừa thanh toán
closePaidInvoiceTab();

// Cập nhật lại sản phẩm
loadProducts();

// Mở trang in hóa đơn ngay lập tức
printInvoiceImmediately(hoaDonId);
    } catch (error) {
        console.error(error);
        showToast('Lỗi kết nối máy chủ!', 'error');
    }
}
function parseMoneyInput(value) {
    return parseInt(String(value || '').replace(/\D/g, ''), 10) || 0;
}

async function redirectToPayOS(hoaDonId) {
    try {
        const res = await fetch('{{ route('payos.create') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ hoa_don_id: hoaDonId })
        });

        const data = await res.json();

        if (!res.ok || !data.success || !data.checkout_url) {
            showToast(data.message || 'Không tạo được link PayOS!', 'error');
            return;
        }

        closePaidInvoiceTab();
        loadProducts();
        window.open(data.checkout_url, '_blank');
    } catch (error) {
        console.error(error);
        showToast('Lỗi khi tạo link PayOS!', 'error');
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
        const response = await fetch(categoryListUrl, {
            headers: { 'Accept': 'application/json' }
        });

        if (!response.ok) {
            throw new Error('Không thể tải danh mục.');
        }

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
        const response = await fetch(customerListUrl + '?q=' + encodeURIComponent(keyword), {
            headers: { 'Accept': 'application/json' }
        });

        if (!response.ok) {
            throw new Error('Không thể tải khách hàng.');
        }

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
    const response = await fetch(promotionListUrl, {
        headers: { 'Accept': 'application/json' }
    });

    if (!response.ok) {
        throw new Error('Không thể tải khuyến mãi.');
    }

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
    // Render lại sản phẩm để hiện badge khuyến mãi
renderProducts(products);
}

/**
 * Lấy danh sách nhân viên/người bán hàng từ API và đổ vào dropdown Người bán.
 * - Vai trò bất kỳ (Admin, Nhân viên, Trưởng ca, ...) — Admin lúc nào cũng có.
 */
let sellers = [];
const SELLER_STORAGE_KEY = 'pos_last_seller_id_user_' + ({{ Auth::id() ?? 'null' }});

/**
 * Đọc sellerId đã lưu trong localStorage (theo user đang đăng nhập).
 * Trả về null nếu chưa có / giá trị cũ không còn hợp lệ.
 */
function getRememberedSellerId() {
    try {
        const raw = localStorage.getItem(SELLER_STORAGE_KEY);
        if (!raw) return null;
        const id = Number(raw);
        if (!Number.isFinite(id) || id <= 0) return null;
        return id;
    } catch (e) {
        return null;
    }
}

function setRememberedSellerId(id) {
    try {
        if (id && Number.isFinite(Number(id)) && Number(id) > 0) {
            localStorage.setItem(SELLER_STORAGE_KEY, String(Number(id)));
        }
    } catch (e) {
        /* localStorage không khả dụng - bỏ qua */
    }
}

function clearRememberedSellerId() {
    try {
        localStorage.removeItem(SELLER_STORAGE_KEY);
    } catch (e) {
        /* bỏ qua */
    }
}

async function loadSellers() {
    try {
        const response = await fetch(sellerListUrl, {
            headers: { 'Accept': 'application/json' }
        });

        if (!response.ok) {
            throw new Error('Không thể tải danh sách người bán.');
        }

        sellers = await response.json();

        const select = document.getElementById('sellerSelect');
        select.innerHTML = '<option value="">-- Chọn người bán --</option>';

        sellers.forEach(nv => {
            const tenVaiTro = nv.ten_vai_tro ? ` (${nv.ten_vai_tro})` : '';
            select.innerHTML += `
                <option value="${nv.id}">
                    ${nv.ho_ten}${tenVaiTro}
                </option>
            `;
        });

        // Ưu tiên lựa chọn người bán cho hóa đơn hiện tại:
        // 1. Nếu tab hiện tại đã có sellerId hợp lệ (do người dùng chọn trước đó) → giữ nguyên
        // 2. Nếu chưa có → dùng sellerId đã lưu trong localStorage (phiên đăng nhập này)
        // 3. Cuối cùng mới fallback về người đang đăng nhập
        const currentInvoice = getCurrentInvoice();
        const currentUserId = {{ Auth::id() ?? 'null' }};
        let sellerToApply = null;

        if (currentInvoice.sellerId && sellers.some(nv => Number(nv.id) === Number(currentInvoice.sellerId))) {
            sellerToApply = currentInvoice.sellerId;
        } else {
            const remembered = getRememberedSellerId();
            if (remembered && sellers.some(nv => Number(nv.id) === Number(remembered))) {
                sellerToApply = remembered;
                currentInvoice.sellerId = remembered;
            } else if (currentUserId && sellers.some(nv => Number(nv.id) === Number(currentUserId))) {
                sellerToApply = currentUserId;
                currentInvoice.sellerId = currentUserId;
                setRememberedSellerId(currentUserId);
            }
        }

        if (sellerToApply) {
            select.value = String(sellerToApply);
            // Đồng bộ dropdown cho tất cả tab đang mở để tránh hiển thị trống khi switch
            invoiceTabs.forEach(tab => {
                if (!tab.sellerId && sellers.some(nv => Number(nv.id) === Number(sellerToApply))) {
                    tab.sellerId = sellerToApply;
                }
            });
        }
    } catch (err) {
        console.error('Lỗi loadSellers:', err);
    }
}

function applySeller() {
    const select = document.getElementById('sellerSelect');
    const id = select && select.value ? Number(select.value) : null;
    const invoice = getCurrentInvoice();
    invoice.sellerId = id;

    // Lưu lựa chọn vào localStorage để giữ cho các tab sau / lần mở POS tiếp theo.
    // Khi id === null (chọn "-- Chọn người bán --") → KHÔNG xóa lưu nhớ,
    // vì đây chỉ là chưa chọn trong tab hiện tại, không phải ý muốn reset phiên.
    if (id !== null) {
        setRememberedSellerId(id);
    }
}
function tinhTienGiam() {
    const cart = getCurrentCart();

    if (!selectedPromotion || cart.length === 0) {
        return 0;
    }

    // ID sản phẩm áp dụng toàn bộ biến thể
    const applicableProductIds = (
        selectedPromotion.id_san_phams || []
    ).map(Number);

    // ID biến thể áp dụng riêng
    const applicableVariantIds = (
        selectedPromotion.id_bien_thes || []
    ).map(Number);

    /*
     * Nếu không có sản phẩm và cũng không có biến thể
     * thì không áp dụng khuyến mãi.
     */
    if (
        applicableProductIds.length === 0 &&
        applicableVariantIds.length === 0
    ) {
        return 0;
    }

    /*
     * Chỉ lấy item thuộc phạm vi khuyến mãi.
     */
    const applicableItems = cart.filter(item => {

        // ID sản phẩm cha
        const productId = Number(
            item.product_id ??
            item.id_san_pham ??
            0
        );

        // item.id chính là ID biến thể
        const variantId = Number(item.id);

        // Nếu chọn toàn bộ sản phẩm
        const applyWholeProduct =
            applicableProductIds.includes(productId);

        // Nếu chỉ chọn riêng biến thể
        const applySpecificVariant =
            applicableVariantIds.includes(variantId);

        return (
            applyWholeProduct ||
            applySpecificVariant
        );
    });

    if (applicableItems.length === 0) {
        return 0;
    }

    /*
     * Tổng tiền chỉ của các sản phẩm / biến thể
     * được áp dụng khuyến mãi.
     */
    const applicableSubtotal = applicableItems.reduce(
        (sum, item) =>
            sum +
            Number(item.gia_ban) *
            Number(item.qty),
        0
    );

    /*
     * Tổng số lượng được áp dụng.
     */
    const applicableQuantity = applicableItems.reduce(
        (sum, item) =>
            sum + Number(item.qty),
        0
    );

    const minOrder = Number(
        selectedPromotion.don_hang_toi_thieu || 0
    );

    const minQty = Number(
        selectedPromotion.so_luong_sp_toi_thieu || 0
    );

    /*
     * Tổng toàn bộ giỏ hàng.
     * Dùng để xét đơn hàng tối thiểu.
     */
    const wholeCartSubtotal = cart.reduce(
        (sum, item) =>
            sum +
            Number(item.gia_ban) *
            Number(item.qty),
        0
    );

    if (wholeCartSubtotal < minOrder) {
        return 0;
    }

    /*
     * Số lượng tối thiểu chỉ xét những item
     * thuộc chương trình khuyến mãi.
     */
    if (
        minQty > 0 &&
        applicableQuantity < minQty
    ) {
        return 0;
    }

    const type = String(
        selectedPromotion.loai_giam_gia || ''
    )
        .trim()
        .toLowerCase();

    const discountValue = Number(
        selectedPromotion.gia_tri_giam || 0
    );

    let discount = 0;

    // Giảm %
    if (
        [
            'percent',
            'phan_tram',
            'percentage'
        ].includes(type)
    ) {
        discount =
            applicableSubtotal *
            discountValue /
            100;
    }

    // Giảm tiền
    else if (
        [
            'amount',
            'fixed',
            'tien_mat',
            'so_tien',
            'giam_tien'
        ].includes(type)
    ) {
        discount = Math.min(
            discountValue,
            applicableSubtotal
        );
    }

    // Mua 1 tặng 1
    else if (
        type === 'bogo' ||
        type === 'mua_1_tang_1'
    ) {
        applicableItems.forEach(item => {

            const freeQuantity = Math.floor(
                Number(item.qty) / 2
            );

            discount +=
                freeQuantity *
                Number(item.gia_ban);
        });
    }

    /*
     * Giảm tối đa.
     */
    const maxDiscount = Number(
        selectedPromotion.giam_toi_da || 0
    );

    if (maxDiscount > 0) {
        discount = Math.min(
            discount,
            maxDiscount
        );
    }

    /*
     * Không được giảm vượt quá tổng tiền
     * của sản phẩm được áp dụng.
     */
    return Math.min(
        Math.max(0, discount),
        applicableSubtotal
    );
}
function applyPromotion() {
    const id = document.getElementById('promotionSelect').value;

    selectedPromotion = promotions.find(km => String(km.id) === String(id)) || null;
    getCurrentInvoice().promotion = selectedPromotion;

    renderCart();
    calculateTotal();
    calculateChange();
} 

async function handleSearchEnter(event) {
    if (event.key !== 'Enter') return;

    event.preventDefault();

    const keyword = event.target.value.trim();
    if (!keyword) return;

    try {
        const response = await fetch(scanBarcodeUrl, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
            body: JSON.stringify({ barcode: keyword })
        });

        const data = await response.json();

        if (response.ok && data.success) {
            addOrUpdateCartItem(data.item);
            event.target.value = '';
            renderCart();
            setTimeout(() => event.target.focus(), 100);
            return;
        }

        const localProduct = Array.isArray(products)
            ? products.find(p => {
                const barcode = String(p.ma_vach || '').toLowerCase();
                const code = String(p.ma_hang || '').toLowerCase();
                const name = String(p.ten_san_pham || '').toLowerCase();
                const search = keyword.toLowerCase();
                return barcode === search || code === search || name.includes(search);
            })
            : null;

        if (localProduct) {
            addOrUpdateCartItem(buildCartItemFromProduct(localProduct));
            event.target.value = '';
            renderCart();
            setTimeout(() => event.target.focus(), 100);
            return;
        }

        const searchResponse = await fetch(productListUrl + '?q=' + encodeURIComponent(keyword), {
            headers: { 'Accept': 'application/json' }
        });

        if (!searchResponse.ok) {
            throw new Error(data.message || 'Không tìm thấy sản phẩm.');
        }

        const searchData = await searchResponse.json();
        const product = Array.isArray(searchData)
            ? (searchData.find(p => String(p.ma_vach || '').toLowerCase() === keyword.toLowerCase()) || searchData[0])
            : null;

        if (!product) {
            showToast(data.message || 'Không tìm thấy sản phẩm.', 'error');
            event.target.value = '';
            event.target.focus();
            return;
        }

        products = Array.isArray(searchData) ? searchData : products;
        addOrUpdateCartItem(buildCartItemFromProduct(product));

        event.target.value = '';
        renderProducts(products, '');
        renderCart();
        setTimeout(() => event.target.focus(), 100);
    } catch (error) {
        console.error(error);
        showToast('Lỗi xử lý tìm sản phẩm!', 'error');
    }
}

function addOrUpdateCartItem(item) {
    const cart = getCurrentCart();
    const existing = cart.find(i => i.row_key === item.row_key);

    if (existing) {
        if (existing.qty + 1 > Number(item.available_qty || 0)) {
            showToast('Số lượng vượt quá tồn kho!', 'error');
            return;
        }
        existing.qty += 1;
        existing.thanh_tien = existing.gia_ban * existing.qty;
    } else {
        cart.push({
            row_key: item.row_key,
            id: item.id,
            unit_id: item.unit_id || null,
            product_id: item.product_id,
            ten_san_pham: item.ten_san_pham,
            ten_don_vi: item.ten_don_vi,
            gia_ban: item.gia_ban,
            gia_von: item.gia_von,
            hinh_anh: item.hinh_anh,
            qty: 1,
            available_qty: item.available_qty,
            ty_le_quy_doi: item.ty_le_quy_doi,
            thanh_tien: item.gia_ban,
        });
    }

    renderCart();
    calculateTotal();
    calculateChange();
    showToast(`Đã thêm "${item.ten_san_pham}" vào giỏ hàng`);
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
function printInvoiceImmediately(hoaDonId) {
    const detailUrl = invoiceListUrl + '/' + hoaDonId + '?print=1';

    const printWindow = window.open(detailUrl, '_blank', 'width=900,height=700,noopener,noreferrer');

    if (!printWindow) {
        const tempLink = document.createElement('a');
        tempLink.href = detailUrl;
        tempLink.target = '_blank';
        tempLink.rel = 'noopener noreferrer';
        document.body.appendChild(tempLink);
        tempLink.click();
        tempLink.remove();
    }
}
// ─────────────────────────────────────────────
// Init
// ─────────────────────────────────────────────
const donChoPayOSUrl = '{{ route('nhan-vien.ban-hang.don-cho-thanh-toan') }}';
const payOSCreateUrl = '{{ route('payos.create') }}';

async function openDonChoPayOS() {
    const modalEl = document.getElementById('donChoPayOSModal');
    if (!modalEl) return;
    bootstrap.Modal.getOrCreateInstance(modalEl).show();
    await loadDonChoPayOS();
}

async function loadDonChoPayOS() {
    const box = document.getElementById('donChoPayOSList');
    box.innerHTML = `<div class="text-center text-muted py-5"><i class="fas fa-spinner fa-spin"></i> Đang tải...</div>`;

    try {
        const res = await fetch(donChoPayOSUrl, {
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
        });
        const json = await res.json();
        if (!res.ok || !json.success) {
            box.innerHTML = `<div class="text-center text-danger py-4">Không tải được danh sách.</div>`;
            return;
        }

        const items = json.data || [];
        if (items.length === 0) {
            box.innerHTML = `<div class="text-center text-muted py-5"><i class="fas fa-inbox"></i><p class="mt-2 mb-0">Không có đơn nào đang chờ thanh toán.</p></div>`;
            return;
        }

        const fmt = (n) => new Intl.NumberFormat('vi-VN').format(Number(n || 0)) + ' đ';

        box.innerHTML = items.map((it) => {
            const ten = it.ten_khach_hang ? it.ten_khach_hang : 'Khách lẻ';
            const sdt = it.so_dien_thoai ? ' - ' + it.so_dien_thoai : '';
            const reopenBtn = it.has_payos
                ? `<button class="btn btn-warning btn-sm" onclick="reopenPayOSQR(${it.hoa_don_id}, '${(it.ma_hoa_don || '#'+it.hoa_don_id).replace(/'/g,"\\'")}')">
                       <i class="fas fa-qrcode"></i> Mở lại QR
                   </button>`
                : `<span class="badge bg-secondary">Chưa có QR PayOS</span>`;
            return `
                <div class="d-flex justify-content-between align-items-center border-bottom px-3 py-2">
                    <div>
                        <div class="fw-bold">#${it.hoa_don_id} - ${ten}${sdt}</div>
                        <small class="text-muted">${it.ma_hoa_don || ''}</small>
                        <div class="text-success fw-bold">${fmt(it.khach_can_tra)}</div>
                    </div>
                    <div class="text-end">
                        ${reopenBtn}
                    </div>
                </div>
            `;
        }).join('');
    } catch (err) {
        console.error(err);
        box.innerHTML = `<div class="text-center text-danger py-4">Lỗi kết nối máy chủ.</div>`;
    }
}

async function reopenPayOSQR(hoaDonId, maHoaDon) {
    try {
        const res = await fetch(payOSCreateUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ hoa_don_id: hoaDonId })
        });

        const data = await res.json();

        if (!res.ok || !data.success || !data.checkout_url) {
            showToast(data.message || 'Không mở được QR PayOS!', 'error');
            return;
        }

        window.open(data.checkout_url, '_blank');
        showToast('Đã mở lại QR PayOS cho ' + maHoaDon, 'success');
    } catch (err) {
        console.error(err);
        showToast('Lỗi khi mở lại QR PayOS!', 'error');
    }
}

loadCategories();
loadProducts();
loadPromotions();
loadSellers();
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