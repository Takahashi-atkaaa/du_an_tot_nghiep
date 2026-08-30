<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SmartMart POS — Bán lẻ</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="payos-create-url" content="{{ route('payos.create') }}">
    <meta name="payos-pending-url" content="{{ route('nhan-vien.ban-hang.don-cho-thanh-toan') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --pos-bg: #f4f6fb;
            --pos-card: #ffffff;
            --pos-primary: #2563eb;
            --pos-primary-dark: #1d4ed8;
            --pos-primary-light: #dbeafe;
            --pos-text: #0f172a;
            --pos-muted: #64748b;
            --pos-border: #e5e7eb;
            --pos-success: #16a34a;
            --pos-danger: #ef4444;
            --pos-warning: #f59e0b;
            --pos-radius: 12px;
            --pos-shadow: 0 4px 14px rgba(15, 23, 42, 0.06);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        html, body {
            height: 100%;
            overflow: hidden;
            font-family: 'Inter', sans-serif;
            background: var(--pos-bg);
            color: var(--pos-text);
            font-size: 14px;
        }

        .pos-shell {
            display: grid;
            grid-template-rows: 56px 1fr;
            height: 100vh;
        }

        /* ===== HEADER ===== */
        .pos-header {
            background: linear-gradient(90deg, #1d4ed8, #2563eb);
            color: #fff;
            padding: 0 22px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 2px 8px rgba(29, 78, 216, 0.18);
            z-index: 100;
        }

        .pos-header .logo {
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 800;
            font-size: 18px;
            letter-spacing: 0.3px;
        }

        .pos-header .logo i {
            background: rgba(255,255,255,0.18);
            width: 34px;
            height: 34px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
        }

        .pos-header .meta {
            display: flex;
            align-items: center;
            gap: 18px;
        }

        .pos-header .ca-badge {
            background: rgba(255,255,255,0.18);
            padding: 6px 12px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 600;
        }

        .pos-header .live-clock {
            font-size: 13px;
            color: #dbeafe;
            font-variant-numeric: tabular-nums;
        }

        .pos-header .user-block {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 13px;
        }

        .pos-header .avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: #fff;
            color: #1d4ed8;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            font-size: 15px;
        }

        /* ===== SIDEBAR (toggleable) ===== */
        .pos-sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: 260px;
            background: var(--pos-primary-dark);
            color: #dbeafe;
            padding-top: 0;
            z-index: 1100;
            transform: translateX(-100%);
            transition: transform 0.3s ease;
            display: flex;
            flex-direction: column;
            box-shadow: 0 0 24px rgba(0,0,0,0.18);
        }

        .pos-sidebar.open {
            transform: translateX(0);
        }

        .pos-sidebar .sidebar-logo {
            padding: 22px 20px 16px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            display: flex;
            align-items: center;
            gap: 10px;
            flex-shrink: 0;
        }

        .pos-sidebar .sidebar-logo h4 {
            color: #fff;
            font-weight: 800;
            margin: 0;
            font-size: 18px;
            letter-spacing: 0.3px;
        }

        .pos-sidebar .sidebar-logo h4 span { color: #93c5fd; }

        .pos-sidebar .sidebar-logo small {
            color: rgba(255,255,255,0.6);
            font-size: 11px;
            display: block;
            margin-top: 2px;
        }

        .pos-sidebar .sidebar-logo .close-sidebar {
            margin-left: auto;
            background: rgba(255,255,255,0.1);
            border: 0;
            color: #fff;
            width: 30px;
            height: 30px;
            border-radius: 6px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .pos-sidebar .sidebar-logo .close-sidebar:hover { background: rgba(255,255,255,0.18); }

        .pos-sidebar .sidebar-nav {
            flex: 1;
            overflow-y: auto;
            padding: 12px 10px 20px;
        }

        .pos-sidebar .nav-item {
            margin: 4px 0;
        }

        .pos-sidebar .nav-link {
            color: #dbeafe;
            padding: 11px 14px;
            border-radius: 8px;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
        }

        .pos-sidebar .nav-link:hover {
            background: rgba(255,255,255,0.08);
            color: #fff;
        }

        .pos-sidebar .nav-link.active {
            background: var(--pos-primary);
            color: #fff;
            font-weight: 700;
        }

        .pos-sidebar .nav-link i {
            width: 20px;
            text-align: center;
            font-size: 15px;
        }

        .pos-sidebar .nav-link .badge-pill {
            margin-left: auto;
            background: rgba(255,255,255,0.18);
            color: #fff;
            padding: 2px 8px;
            border-radius: 999px;
            font-size: 11px;
        }

        .pos-sidebar .sidebar-divider {
            height: 1px;
            background: rgba(255,255,255,0.1);
            margin: 14px 10px;
        }

        .pos-sidebar .sidebar-footer {
            padding: 12px 16px;
            border-top: 1px solid rgba(255,255,255,0.1);
            display: flex;
            align-items: center;
            gap: 10px;
            flex-shrink: 0;
            background: rgba(0,0,0,0.15);
        }

        .pos-sidebar .sidebar-footer .avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: var(--pos-primary);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 14px;
            flex-shrink: 0;
        }

        .pos-sidebar .sidebar-footer .info {
            flex: 1;
            min-width: 0;
        }

        .pos-sidebar .sidebar-footer .info .name {
            color: #fff;
            font-weight: 600;
            font-size: 13px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .pos-sidebar .sidebar-footer .info .role {
            color: rgba(255,255,255,0.6);
            font-size: 11px;
        }

        .pos-sidebar .sidebar-footer .logout-btn {
            background: rgba(239,68,68,0.18);
            border: 0;
            color: #fecaca;
            width: 32px;
            height: 32px;
            border-radius: 6px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .pos-sidebar .sidebar-footer .logout-btn:hover {
            background: rgba(239,68,68,0.35);
            color: #fff;
        }

        .sidebar-overlay {
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.5);
            z-index: 1050;
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.3s ease, visibility 0.3s ease;
        }

        .sidebar-overlay.show {
            opacity: 1;
            visibility: visible;
        }

        .btn-toggle-sidebar {
            background: rgba(255,255,255,0.18);
            border: 0;
            color: #fff;
            width: 36px;
            height: 36px;
            border-radius: 8px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            margin-right: 8px;
            transition: background 0.2s;
        }

        .btn-toggle-sidebar:hover { background: rgba(255,255,255,0.3); }

        /* ===== MAIN LAYOUT ===== */
        .pos-main {
            display: grid;
            grid-template-columns: 440px 1fr;
            gap: 0;
            overflow: hidden;
        }

        /* ===== LEFT: INVOICE PANEL ===== */
        .pos-left {
            background: #fff;
            border-right: 1px solid var(--pos-border);
            display: grid;
            grid-template-rows: auto auto 1fr auto;
            overflow: hidden;
        }

        .invoice-tabs {
            display: flex;
            align-items: center;
            gap: 6px;
            padding: 10px 12px;
            border-bottom: 1px solid var(--pos-border);
            overflow-x: auto;
            background: #f9fafb;
        }

        .invoice-tab {
            padding: 7px 14px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            white-space: nowrap;
            border: 1px solid transparent;
            background: #fff;
            color: var(--pos-muted);
            transition: 0.15s;
            position: relative;
        }

        .invoice-tab.active {
            background: var(--pos-primary);
            color: #fff;
            border-color: var(--pos-primary);
        }

        .invoice-tab .close-tab {
            margin-left: 8px;
            opacity: 0.7;
            cursor: pointer;
        }

        .invoice-tab .close-tab:hover { opacity: 1; }

        .invoice-tab-add {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            border: 1px dashed var(--pos-border);
            background: #fff;
            color: var(--pos-muted);
            cursor: pointer;
            flex-shrink: 0;
        }

        .invoice-tab-add:hover { color: var(--pos-primary); border-color: var(--pos-primary); }

        .search-panels {
            padding: 12px;
            display: grid;
            grid-template-columns: 1fr;
            gap: 8px;
            border-bottom: 1px solid var(--pos-border);
            background: #fff;
        }

        .search-panels .input-group-text {
            background: #f1f5f9;
            border-right: 0;
            color: var(--pos-muted);
        }

        .search-panels .form-control {
            border-left: 0;
            font-size: 13px;
        }

        .search-panels .form-control:focus { box-shadow: none; border-color: var(--pos-border); }

        .customer-info {
            margin: 0 12px 8px;
            padding: 10px 12px;
            background: var(--pos-primary-light);
            border-radius: 10px;
            font-size: 13px;
            display: none;
            align-items: center;
            justify-content: space-between;
        }

        .customer-info.show { display: flex; }

        .customer-info .name { font-weight: 700; color: var(--pos-primary-dark); }
        .customer-info .points { color: var(--pos-primary-dark); font-weight: 600; font-size: 12px; }

        .invoice-items {
            overflow-y: auto;
            padding: 8px 12px;
        }

        .invoice-empty {
            text-align: center;
            color: var(--pos-muted);
            padding: 50px 16px;
        }

        .invoice-empty i { font-size: 36px; opacity: 0.4; }

        .invoice-item {
            display: grid;
            grid-template-columns: 56px 1fr auto;
            gap: 10px;
            padding: 10px 6px;
            border-bottom: 1px solid #f1f5f9;
            align-items: center;
        }

        .invoice-item img {
            width: 56px;
            height: 56px;
            border-radius: 8px;
            object-fit: cover;
            background: #f1f5f9;
        }

        .invoice-item .name {
            font-weight: 600;
            font-size: 13px;
            line-height: 1.25;
            margin-bottom: 4px;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .invoice-item .price {
            color: var(--pos-muted);
            font-size: 12px;
        }

        .invoice-item .qty-control {
            display: flex;
            align-items: center;
            gap: 4px;
            margin-top: 4px;
        }

        .qty-btn {
            width: 24px;
            height: 24px;
            border-radius: 6px;
            border: 1px solid var(--pos-border);
            background: #fff;
            cursor: pointer;
            font-weight: 700;
            color: var(--pos-primary);
        }

        .qty-btn:hover { background: var(--pos-primary-light); }

        .qty-input {
            width: 40px;
            text-align: center;
            border: 1px solid var(--pos-border);
            border-radius: 6px;
            height: 24px;
            font-size: 13px;
            font-weight: 600;
        }

        .invoice-item .total {
            font-weight: 700;
            color: var(--pos-primary-dark);
            font-size: 13px;
            text-align: right;
        }

        .invoice-item .remove-btn {
            display: block;
            margin-top: 4px;
            font-size: 11px;
            color: var(--pos-danger);
            cursor: pointer;
        }

        .invoice-footer {
            border-top: 1px solid var(--pos-border);
            padding: 14px 16px;
            background: #fff;
            box-shadow: 0 -2px 8px rgba(15, 23, 42, 0.04);
        }

        .totals { margin-bottom: 12px; }

        .totals .row-line {
            display: flex;
            justify-content: space-between;
            margin-bottom: 6px;
            font-size: 13px;
            color: var(--pos-muted);
        }

        .totals .row-line strong {
            color: var(--pos-text);
            font-weight: 600;
        }

        .totals .row-line.big {
            font-size: 17px;
            color: var(--pos-text);
            font-weight: 700;
            border-top: 1px dashed var(--pos-border);
            padding-top: 10px;
            margin-top: 8px;
        }

        .totals .row-line.big strong {
            color: var(--pos-primary);
            font-size: 20px;
        }

        .btn-checkout {
            width: 100%;
            background: var(--pos-primary);
            color: #fff;
            border: 0;
            padding: 12px;
            border-radius: 10px;
            font-weight: 700;
            font-size: 15px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: 0.15s;
        }

        .btn-checkout:hover { background: var(--pos-primary-dark); }
        .btn-checkout:disabled { opacity: 0.5; cursor: not-allowed; }

        .btn-clear {
            width: 100%;
            background: #fff;
            color: var(--pos-danger);
            border: 1px solid var(--pos-danger);
            padding: 8px;
            border-radius: 10px;
            font-weight: 600;
            font-size: 13px;
            cursor: pointer;
            margin-bottom: 8px;
        }

        /* ===== RIGHT: PRODUCT PANEL ===== */
        .pos-right {
            display: grid;
            grid-template-rows: auto 1fr;
            overflow: hidden;
            background: var(--pos-bg);
        }

        .filter-bar {
            background: #fff;
            padding: 12px 18px;
            border-bottom: 1px solid var(--pos-border);
            display: grid;
            grid-template-rows: auto auto;
            gap: 10px;
        }

        .filter-bar .search-row {
            display: grid;
            grid-template-columns: 1fr auto;
            gap: 10px;
            align-items: center;
        }

        .filter-bar .input-group-text {
            background: #f1f5f9;
            border-right: 0;
            color: var(--pos-muted);
        }

        .filter-bar .form-control {
            border-left: 0;
            font-size: 14px;
            height: 40px;
        }

        .filter-bar .form-control:focus { box-shadow: none; border-color: var(--pos-border); }

        .category-chips {
            display: flex;
            gap: 8px;
            overflow-x: auto;
            padding-bottom: 2px;
        }

        .chip {
            padding: 7px 16px;
            border-radius: 999px;
            border: 1px solid var(--pos-border);
            background: #fff;
            color: var(--pos-text);
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            white-space: nowrap;
            transition: 0.15s;
        }

        .chip:hover { border-color: var(--pos-primary); color: var(--pos-primary); }

        .chip.active {
            background: var(--pos-primary);
            color: #fff;
            border-color: var(--pos-primary);
        }

        .product-grid {
            overflow-y: auto;
            padding: 14px 18px;
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(170px, 1fr));
            gap: 14px;
            align-content: start;
        }

        .product-card {
            background: #fff;
            border: 1px solid var(--pos-border);
            border-radius: var(--pos-radius);
            overflow: hidden;
            cursor: pointer;
            transition: 0.15s;
            display: flex;
            flex-direction: column;
        }

        .product-card:hover {
            transform: translateY(-2px);
            box-shadow: var(--pos-shadow);
            border-color: var(--pos-primary);
        }

        .product-card .img-wrap {
            position: relative;
            width: 100%;
            aspect-ratio: 1 / 1;
            background: #f8fafc;
            overflow: hidden;
        }

        .product-card .img-wrap img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .product-card .img-wrap .no-img {
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #cbd5e1;
            font-size: 32px;
        }

        .product-card .badge-promo {
            position: absolute;
            top: 6px;
            left: 6px;
            background: var(--pos-danger);
            color: #fff;
            padding: 3px 8px;
            border-radius: 6px;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .product-card .badge-stock {
            position: absolute;
            top: 6px;
            right: 6px;
            background: rgba(0,0,0,0.6);
            color: #fff;
            padding: 3px 8px;
            border-radius: 6px;
            font-size: 10px;
            font-weight: 600;
        }

        .product-card .info {
            padding: 10px;
            display: flex;
            flex-direction: column;
            flex: 1;
        }

        .product-card .info .name {
            font-weight: 600;
            font-size: 13px;
            line-height: 1.3;
            margin-bottom: 6px;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            min-height: 34px;
        }

        .product-card .info .price {
            color: var(--pos-primary);
            font-weight: 800;
            font-size: 15px;
        }

        .product-card .info .barcode {
            color: var(--pos-muted);
            font-size: 11px;
            margin-top: 2px;
        }

        .product-card.out-of-stock { opacity: 0.55; cursor: not-allowed; }

        .product-grid-empty {
            grid-column: 1 / -1;
            text-align: center;
            padding: 60px 16px;
            color: var(--pos-muted);
        }

        .product-grid-empty i { font-size: 42px; opacity: 0.4; margin-bottom: 12px; }

        /* ===== TOAST ===== */
        .pos-toast {
            position: fixed;
            top: 80px;
            right: 22px;
            background: #16a34a;
            color: #fff;
            padding: 12px 18px;
            border-radius: 10px;
            font-weight: 600;
            font-size: 13px;
            z-index: 9999;
            box-shadow: 0 6px 20px rgba(0,0,0,0.15);
            opacity: 0;
            transform: translateY(-10px);
            transition: 0.2s;
            pointer-events: none;
        }

        .pos-toast.show { opacity: 1; transform: translateY(0); }
        .pos-toast.error { background: var(--pos-danger); }
        .pos-toast.warn { background: var(--pos-warning); }

        /* ===== SUGGESTION DROPDOWN ===== */
        .suggest-box {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: #fff;
            border: 1px solid var(--pos-border);
            border-top: 0;
            border-radius: 0 0 10px 10px;
            max-height: 240px;
            overflow-y: auto;
            z-index: 50;
            display: none;
            box-shadow: 0 6px 20px rgba(15, 23, 42, 0.08);
        }

        .suggest-box.show { display: block; }

        .suggest-item {
            padding: 9px 12px;
            cursor: pointer;
            border-bottom: 1px solid #f1f5f9;
            font-size: 13px;
        }

        .suggest-item:hover { background: var(--pos-primary-light); }
        .suggest-item:last-child { border-bottom: 0; }

        .suggest-item .name { font-weight: 600; }
        .suggest-item .meta { color: var(--pos-muted); font-size: 11px; }

        .suggest-wrap { position: relative; }

        /* ===== PAYMENT MODAL ===== */
        .payment-methods {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 8px;
            margin-bottom: 16px;
        }

        .pay-method {
            border: 1px solid var(--pos-border);
            border-radius: 10px;
            padding: 12px;
            text-align: center;
            cursor: pointer;
            background: #fff;
            transition: 0.15s;
        }

        .pay-method:hover { border-color: var(--pos-primary); }

        .pay-method.active {
            border-color: var(--pos-primary);
            background: var(--pos-primary-light);
            color: var(--pos-primary-dark);
            font-weight: 700;
        }

        .pay-method i { font-size: 22px; display: block; margin-bottom: 6px; }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 1100px) {
            .pos-main { grid-template-columns: 380px 1fr; }
        }

        @media (max-width: 900px) {
            .pos-main { grid-template-columns: 1fr; grid-template-rows: 1fr 1fr; }
            .pos-left { border-right: 0; border-bottom: 1px solid var(--pos-border); }
        }

        .scroll-thin::-webkit-scrollbar { width: 6px; }
        .scroll-thin::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 999px; }
        .scroll-thin::-webkit-scrollbar-track { background: transparent; }
        /* ================================
   THÊM KHÁCH HÀNG NHANH
================================ */

.customer-search-group {
    position: relative;
}

.btn-add-customer {
    width: 42px;
    height: 38px;
    padding: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 0 8px 8px 0;
    font-size: 14px;
    flex-shrink: 0;
}

.btn-add-customer:hover {
    background: var(--pos-primary-dark);
}

.customer-search-group .form-control {
    min-width: 0;
}

/* Modal thêm khách hàng */

#addCustomerModal .modal-dialog {
    max-width: 480px;
}

#addCustomerModal .modal-content {
    border: 0;
    border-radius: 14px;
    overflow: hidden;
    box-shadow: 0 15px 45px rgba(15, 23, 42, 0.18);
}

#addCustomerModal .modal-header {
    background: linear-gradient(
        90deg,
        var(--pos-primary-dark),
        var(--pos-primary)
    );
    color: #fff;
    border-bottom: 0;
    padding: 16px 20px;
}

#addCustomerModal .modal-title {
    font-size: 16px;
    font-weight: 700;
}

#addCustomerModal .modal-body {
    padding: 20px;
}

#addCustomerModal .form-label {
    font-size: 13px;
    font-weight: 600;
    margin-bottom: 6px;
}

#addCustomerModal .form-control {
    height: 42px;
    border-radius: 8px;
    font-size: 13px;
}

#addCustomerModal .form-control:focus {
    border-color: var(--pos-primary);
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
}

#addCustomerModal .modal-footer {
    padding: 12px 20px;
    background: #f8fafc;
    border-top: 1px solid var(--pos-border);
}

#btnSaveCustomer {
    min-width: 130px;
    font-weight: 600;
}
    </style>
</head>
<body>
<!-- SIDEBAR -->
<aside class="pos-sidebar" id="posSidebar">
    <div class="sidebar-logo">
        <div>
            <h4>Smart<span>Mart</span></h4>
            <small>Bán hàng POS</small>
        </div>
        <button class="close-sidebar" id="btnCloseSidebar" type="button" aria-label="Đóng menu">
            <i class="fa-solid fa-xmark"></i>
        </button>
    </div>

    <nav class="sidebar-nav">
        @if (isset($nguoiDung->id_vai_tro) && in_array($nguoiDung->id_vai_tro, [1, 2]))
        <div class="nav-item">
            <a href="{{ url('/admin/dashboard') }}" class="nav-link">
                <i class="fa-solid fa-gauge-high"></i>
                <span>Trang quản trị</span>
            </a>
        </div>
        @endif

        <div class="sidebar-divider"></div>

        <div class="nav-item">
            <a href="{{ url('/ban-hang') }}" class="nav-link active">
                <i class="fa-solid fa-cash-register"></i>
                <span>Bán hàng</span>
            </a>
        </div>
        <div class="nav-item">
            <a href="javascript:void(0)" class="nav-link" onclick="openDonChoPayOS()">
                <i class="fa-solid fa-qrcode"></i>
                <span>QR đang chờ</span>
            </a>
        </div>
        <div class="nav-item">
            <a href="{{ url('/hoa-don') }}" class="nav-link">
                <i class="fa-solid fa-file-invoice"></i>
                <span>Hóa đơn</span>
            </a>
        </div>
        <div class="nav-item">
            <a href="{{ url('/san-pham') }}" class="nav-link">
                <i class="fa-solid fa-box"></i>
                <span>Sản phẩm</span>
            </a>
        </div>
        <div class="nav-item">
            <a href="{{ url('/khach-hang') }}" class="nav-link">
                <i class="fa-solid fa-users"></i>
                <span>Khách hàng</span>
            </a>
        </div>

        <div class="sidebar-divider"></div>

        @auth
            @if(auth()->user()->id_vai_tro != 1 && auth()->user()->id_vai_tro != 2 && auth()->user()->id_vai_tro != 4)
                <div class="nav-item">
                    <a href="{{ url('/lich-lam-viec') }}" class="nav-link">
                        <i class="fa-solid fa-calendar-days"></i>
                        <span>Lịch làm việc</span>
                    </a>
                </div>
            @endif
        @endauth
    </nav>

    <div class="sidebar-footer">
        <div class="avatar">{{ mb_substr($nguoiDung->ho_ten ?? 'U', 0, 1) }}</div>
        <div class="info">
            <div class="name">{{ $nguoiDung->ho_ten ?? 'Nhân viên' }}</div>
            <div class="role">{{ $nguoiDung->email ?? '' }}</div>
        </div>
        <a class="logout-btn" href="{{ url('admin/logout') }}" title="Đăng xuất">
            <i class="fa-solid fa-right-from-bracket"></i>
        </a>
    </div>
</aside>

<div class="sidebar-overlay" id="sidebarOverlay"></div>

<div class="pos-shell">
    <header class="pos-header">
        <div class="d-flex align-items-center">
            <button class="btn-toggle-sidebar" id="btnOpenSidebar" type="button" aria-label="Mở menu">
                <i class="fa-solid fa-bars"></i>
            </button>
            <div class="logo">
                <i class="fa-solid fa-cash-register"></i>
                <span>SmartMart POS</span>
            </div>
        </div>
        <div class="meta">
            <span class="ca-badge"><i class="fa-regular fa-clock me-1"></i>Ca: {{ $caHienTai->ten_ca ?? '—' }} @if(isset($caHienTai->gio_bat_dau)) ({{ substr($caHienTai->gio_bat_dau,0,5) }} - {{ substr($caHienTai->gio_ket_thuc,0,5) }}) @endif</span>
            <span class="live-clock" id="liveClock"></span>
        </div>
        <div class="user-block">
            <div>
                <div style="font-weight: 600;">{{ $nguoiDung->ho_ten ?? 'Nhân viên' }}</div>
                <div style="font-size: 11px; opacity: 0.85;">{{ $nguoiDung->email ?? '' }}</div>
            </div>
            <div class="avatar">{{ mb_substr($nguoiDung->ho_ten ?? 'U', 0, 1) }}</div>
        </div>
    </header>

    <main class="pos-main">
        <!-- LEFT -->
        <aside class="pos-left">
            <div class="invoice-tabs" id="invoiceTabs"></div>

            <div class="search-panels">
                <div class="suggest-wrap">
    <div class="input-group customer-search-group">
        <span class="input-group-text">
            <i class="fa-solid fa-user"></i>
        </span>

        <input
            id="searchCustomer"
            class="form-control"
            placeholder="Tìm khách hàng (tên / SĐT)..."
            autocomplete="off"
        >

        <button
            type="button"
            class="btn btn-primary btn-add-customer"
            id="btnAddCustomer"
            title="Thêm khách hàng"
        >
            <i class="fa-solid fa-plus"></i>
        </button>
    </div>

    <div class="suggest-box" id="customerSuggest"></div>
</div>
                <div class="input-group">
                    <span class="input-group-text"><i class="fa-solid fa-barcode"></i></span>
                    <input id="barcodeInput" class="form-control" placeholder="Quét mã vạch / Enter để thêm nhanh..." autocomplete="off">
                </div>
            </div>

            <div class="customer-info" id="customerInfo">
                <div>
                    <div class="name" id="customerName">—</div>
                    <div class="points"><i class="fa-solid fa-star me-1"></i><span id="customerPoints">0</span> điểm</div>
                </div>
                <button type="button" class="btn btn-sm btn-outline-danger" id="btnRemoveCustomer">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <div class="invoice-items scroll-thin" id="invoiceItems">
                <div class="invoice-empty">
                    <i class="fa-solid fa-cart-shopping"></i>
                    <p class="mt-2 mb-0">Chưa có sản phẩm trong hóa đơn</p>
                    <small>Bấm vào sản phẩm bên phải để thêm</small>
                </div>
            </div>

            <div class="invoice-footer">
                <div class="totals">
                    <div class="row-line"><span>Tạm tính</span><strong id="subtotal">0 đ</strong></div>
                    <div class="row-line"><span>Giảm giá</span><strong id="discount">0 đ</strong></div>
                    <div class="row-line"><span>Điểm sử dụng</span><strong id="pointUse">0 đ</strong></div>
                    <div class="row-line big"><span>Khách cần trả</span><strong id="total">0 đ</strong></div>
                </div>
                <button class="btn-clear" id="btnClearCart">
                    <i class="fa-solid fa-trash-can me-1"></i> Xóa toàn bộ
                </button>
                <button class="btn-checkout" id="btnCheckout">
                    <i class="fa-solid fa-credit-card"></i> Thanh toán
                </button>
            </div>
        </aside>

        <!-- RIGHT -->
        <section class="pos-right">
            <div class="filter-bar">
                <div class="search-row">
                    <div class="input-group">
                        <span class="input-group-text"><i class="fa-solid fa-search"></i></span>
                        <input id="searchProduct" class="form-control" placeholder="Tìm sản phẩm theo tên / mã vạch...">
                    </div>
                    <div>
                        <select id="sellerSelect" class="form-select" style="height: 40px; font-size: 13px;">
                            <option value="">-- Người bán --</option>
                        </select>
                    </div>
                </div>
                <div class="category-chips" id="categoryChips">
                    <button class="chip active" data-id="">Tất cả</button>
                    @foreach($danhSachDanhMuc as $dm)
                        <button class="chip" data-id="{{ $dm->id }}">{{ $dm->ten_danh_muc }}</button>
                    @endforeach
                </div>
            </div>

            <div class="product-grid scroll-thin" id="productGrid">
                <div class="product-grid-empty">
                    <i class="fa-solid fa-spinner fa-spin"></i>
                    <p class="mt-2 mb-0">Đang tải sản phẩm...</p>
                </div>
            </div>
        </section>
    </main>
</div>

<div class="pos-toast" id="posToast"><i class="fa-solid fa-circle-check me-2"></i><span id="toastMessage">OK</span></div>

<!-- Payment modal -->
<div class="modal fade" id="checkoutModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background: var(--pos-primary); color: #fff;">
                <h5 class="modal-title"><i class="fa-solid fa-cash-register me-2"></i>Thanh toán hóa đơn</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3 mb-3">
                    <div class="col-md-7">
                        <div class="border rounded p-3" style="background: #f8fafc;">
                            <h6 class="fw-bold mb-2">Sản phẩm</h6>
                            <div id="checkoutItems" style="max-height: 200px; overflow-y: auto;"></div>
                        </div>
                    </div>
                    <div class="col-md-5">
                        <div class="border rounded p-3" style="background: #f8fafc;">
                            <div class="d-flex justify-content-between mb-1"><span>Tạm tính</span><strong id="ckSubtotal">0 đ</strong></div>
                            <div class="d-flex justify-content-between mb-1"><span>Giảm giá SP</span><strong id="ckDiscount">0 đ</strong></div>
                            <div class="d-flex justify-content-between mb-1"><span>Voucher KM</span>
                                <select id="ckVoucher" style="width: 140px; font-size: 12px;" class="form-select form-select-sm"></select>
                            </div>
                            <div class="d-flex justify-content-between mb-1"><span>Giảm voucher</span><strong id="ckVoucherDiscount">0 đ</strong></div>
                            <div class="d-flex justify-content-between mb-1"><span>Khách hàng</span>
                                <span class="text-primary fw-bold" id="ckCustomer">Khách lẻ</span>
                            </div>
                            <div class="d-flex justify-content-between mb-1"><span>Điểm dùng (×100đ)</span>
                                <input id="ckUsePoint" type="number" min="0" value="0" style="width: 80px;" class="form-control form-control-sm">
                            </div>
                            <div class="d-flex justify-content-between mb-1"><span>Giảm từ điểm</span><strong id="ckPointDiscount">0 đ</strong></div>
                            <hr>
                            <div class="d-flex justify-content-between"><span class="fw-bold">Khách cần trả</span>
                                <strong class="text-primary" style="font-size: 20px;" id="ckTotal">0 đ</strong>
                            </div>
                        </div>
                    </div>
                </div>

                <h6 class="fw-bold mt-3">Phương thức thanh toán</h6>
                <div class="payment-methods">
                    <div class="pay-method active" data-method="cash">
                        <i class="fa-solid fa-money-bill-wave"></i>
                        <div>Tiền mặt</div>
                    </div>
                    <div class="pay-method" data-method="payos">
                        <i class="fa-solid fa-mobile-screen"></i>
                        <div>PayOS</div>
                    </div>
                </div>

                <div id="cashBox">
                    <label class="form-label fw-bold">Tiền khách đưa</label>
                    <div class="input-group">
                        <input id="customerMoney" type="text" class="form-control money-input" placeholder="0">
                        <span class="input-group-text">đ</span>
                    </div>
                    <div class="d-flex gap-2 mt-2 flex-wrap">
                        <button class="btn btn-outline-secondary btn-sm quick-money" data-amt="50000">50.000</button>
                        <button class="btn btn-outline-secondary btn-sm quick-money" data-amt="100000">100.000</button>
                        <button class="btn btn-outline-secondary btn-sm quick-money" data-amt="200000">200.000</button>
                        <button class="btn btn-outline-secondary btn-sm quick-money" data-amt="500000">500.000</button>
                        <button class="btn btn-outline-secondary btn-sm quick-money" id="quickMoneyExact">Bằng tổng</button>
                    </div>
                    <div class="mt-2">Tiền thừa: <strong id="changeMoney" class="text-success">0 đ</strong></div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                <button class="btn btn-primary" id="btnConfirmPay"><i class="fa-solid fa-check me-1"></i>Xác nhận thanh toán</button>
            </div>
        </div>
    </div>
</div>

<!-- MODAL: Đơn chờ thanh toán PayOS -->
<div class="modal fade" id="donChoPayOSModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background: var(--pos-primary); color: #fff;">
                <h5 class="modal-title">
                    <i class="fa-solid fa-qrcode me-2"></i>Đơn chờ thanh toán PayOS
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Đóng"></button>
            </div>
            <div class="modal-body p-0">
                <div id="donChoPayOSList" class="py-3"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-primary" onclick="loadDonChoPayOS()">
                    <i class="fa-solid fa-rotate me-1"></i>Làm mới
                </button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
            </div>
        </div>
    </div>
</div>
<!-- =========================================================
     MODAL: THÊM KHÁCH HÀNG NHANH
========================================================= -->
<div
    class="modal fade"
    id="addCustomerModal"
    tabindex="-1"
    aria-hidden="true"
>
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fa-solid fa-user-plus me-2"></i>
                    Thêm khách hàng
                </h5>

                <button
                    type="button"
                    class="btn-close btn-close-white"
                    data-bs-dismiss="modal"
                    aria-label="Đóng"
                ></button>
            </div>

            <form id="addCustomerForm">

                <div class="modal-body">

                    <div class="mb-3">
                        <label class="form-label">
                            Tên khách hàng
                            <span class="text-danger">*</span>
                        </label>

                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="fa-solid fa-user"></i>
                            </span>

                            <input
                                type="text"
                                id="newCustomerName"
                                class="form-control"
                                placeholder="Nhập tên khách hàng"
                                maxlength="255"
                                required
                            >
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">
                            Số điện thoại
                             <span class="text-danger">*</span>
                        </label>

                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="fa-solid fa-phone"></i>
                            </span>

                            <input
                                type="text"
                                id="newCustomerPhone"
                                class="form-control"
                                placeholder="Nhập số điện thoại"
                                maxlength="20"
                            >
                        </div>

                        <div
                            id="customerPhoneError"
                            class="text-danger mt-1"
                            style="font-size:12px; display:none;"
                        ></div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">
                            Email
                        </label>

                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="fa-solid fa-envelope"></i>
                            </span>

                            <input
                                type="email"
                                id="newCustomerEmail"
                                class="form-control"
                                placeholder="Nhập email nếu có"
                                maxlength="255"
                            >
                        </div>
                    </div>

                    <div class="mb-1">
                        <label class="form-label">
                            Địa chỉ
                        </label>

                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="fa-solid fa-location-dot"></i>
                            </span>

                            <input
                                type="text"
                                id="newCustomerAddress"
                                class="form-control"
                                placeholder="Nhập địa chỉ nếu có"
                                maxlength="255"
                            >
                        </div>
                    </div>

                </div>

                <div class="modal-footer">

                    <button
                        type="button"
                        class="btn btn-secondary"
                        data-bs-dismiss="modal"
                    >
                        Hủy
                    </button>

                    <button
                        type="submit"
                        class="btn btn-primary"
                        id="btnSaveCustomer"
                    >
                        <i class="fa-solid fa-check me-1"></i>
                        Lưu khách hàng
                    </button>

                </div>

            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="{{ asset('js/admin/money-input.js') }}"></script>
<script src="{{ asset('js/pos/payos.js') }}"></script>
<script>
const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
const productListUrl = '{{ route('nhan-vien.ban-hang.san-pham') }}';
const categoryListUrl = '{{ route('nhan-vien.ban-hang.danh-muc') }}';
const checkoutUrl = '{{ route('nhan-vien.ban-hang.thanh-toan') }}';
const customerListUrl = '{{ route('nhan-vien.ban-hang.khach-hang') }}';
const promotionListUrl = '{{ route('nhan-vien.ban-hang.khuyen-mai') }}';
const sellerListUrl = '{{ route('nhan-vien.ban-hang.nhan-vien') }}';
const payOSCreateUrl = '{{ route('payos.create') }}';
const invoicePrintUrl = '{{ url('/hoa-don') }}';

// Stub cho payos.js (sau thanh toán PayOS, tab đã được reset ngay tại handler xác nhận)
window.closePaidInvoiceTab = function () { /* tab reset tại btnConfirmPay handler */ };

const fmt = (n) => new Intl.NumberFormat('vi-VN').format(Number(n || 0)) + ' đ';
const parseMoney = (v) => parseInt(String(v || '').replace(/\D/g, ''), 10) || 0;
const SELLER_STORAGE_KEY = 'smartmart_pos_seller_id';

// =========================================================
// STATE
// =========================================================
let allProducts = [];
let allCategories = [];
let allPromotions = [];
let allSellers = [];

let currentCategory = '';
let searchKeyword = '';

let invoices = [];           // multi tab
let activeInvoiceIdx = 0;
let selectedCustomer = null; // global mặc định
let selectedPayment = 'cash';
let selectedVoucherId = null;

// =========================================================
// INVOICE TABS
// =========================================================
function newInvoice() {
    return {
        items: [],            // {id, ten_san_pham, gia_ban, qty, hinh_anh, ma_vach, ...}
        customer: null,
        usePoint: 0,
        voucherId: null,
        voucherDiscount: 0,
        sellerId: '',
    };
}

function renderInvoiceTabs() {
    if (invoices.length === 0) invoices.push(newInvoice());

    const box = document.getElementById('invoiceTabs');
    box.innerHTML = '';

    invoices.forEach((inv, idx) => {
        const tab = document.createElement('button');
        tab.className = 'invoice-tab' + (idx === activeInvoiceIdx ? ' active' : '');
        const itemCount = inv.items.length;
        tab.innerHTML = `#${idx + 1} ${itemCount > 0 ? '(' + itemCount + ')' : ''} <span class="close-tab" data-idx="${idx}">×</span>`;
        tab.onclick = (e) => {
            if (e.target.classList.contains('close-tab')) return;
            activeInvoiceIdx = idx;
            applyActiveInvoice();
        };
        const closeBtn = tab.querySelector('.close-tab');
        closeBtn.onclick = (e) => {
            e.stopPropagation();
            if (invoices.length === 1) {
                invoices[0] = newInvoice();
            } else {
                invoices.splice(idx, 1);
                if (activeInvoiceIdx >= invoices.length) activeInvoiceIdx = invoices.length - 1;
            }
            renderInvoiceTabs();
            applyActiveInvoice();
        };
        box.appendChild(tab);
    });

    const addBtn = document.createElement('button');
    addBtn.className = 'invoice-tab-add';
    addBtn.innerHTML = '<i class="fa-solid fa-plus"></i>';
    addBtn.onclick = () => {
        if (invoices.length >= 5) {
            showToast('Tối đa 5 hóa đơn cùng lúc', 'warn');
            return;
        }
        invoices.push(newInvoice());
        activeInvoiceIdx = invoices.length - 1;
        renderInvoiceTabs();
        applyActiveInvoice();
    };
    box.appendChild(addBtn);
}

function getActiveInvoice() {
    if (!invoices[activeInvoiceIdx]) {
        invoices[activeInvoiceIdx] = newInvoice();
    }
    return invoices[activeInvoiceIdx];
}

function applyActiveInvoice() {
    const inv = getActiveInvoice();

    // Đồng bộ khách hàng của hóa đơn hiện tại
    selectedCustomer = inv.customer;

    // Đồng bộ điểm sử dụng
    const ckUsePoint = document.getElementById('ckUsePoint');
    if (ckUsePoint) {
        ckUsePoint.value = inv.usePoint || 0;
    }

    // Đồng bộ voucher
    selectedVoucherId = inv.voucherId || null;

    // =====================================================
    // ĐỒNG BỘ Ô TÌM KIẾM KHÁCH HÀNG
    // =====================================================
    const searchCustomer = document.getElementById('searchCustomer');
    const customerSuggest = document.getElementById('customerSuggest');

    if (searchCustomer) {
        if (selectedCustomer) {
            searchCustomer.value =
                selectedCustomer.ten_khach_hang +
                (
                    selectedCustomer.so_dien_thoai
                        ? ' (' + selectedCustomer.so_dien_thoai + ')'
                        : ''
                );
        } else {
            // Không có khách hàng => xóa ô tìm kiếm
            searchCustomer.value = '';
        }
    }

    // Đóng danh sách gợi ý
    if (customerSuggest) {
        customerSuggest.classList.remove('show');
        customerSuggest.innerHTML = '';
    }

    // Render lại giao diện
    renderCustomerInfo();
    renderInvoiceItems();
    renderTotals();
    renderInvoiceTabs();
}

// =========================================================
// CATEGORIES & PRODUCTS
// =========================================================
async function loadCategories() {
    try {
        const res = await fetch(categoryListUrl, { headers: { 'Accept': 'application/json' } });
        allCategories = await res.json();
        renderCategories();
    } catch (err) {
        console.error(err);
    }
}

function renderCategories() {
    const wrap = document.getElementById('categoryChips');
    wrap.innerHTML = '<button class="chip active" data-id="">Tất cả</button>';
    allCategories.forEach(c => {
        const btn = document.createElement('button');
        btn.className = 'chip';
        btn.dataset.id = c.id;
        btn.textContent = c.ten_danh_muc;
        btn.onclick = () => {
            currentCategory = c.id;
            wrap.querySelectorAll('.chip').forEach(x => x.classList.remove('active'));
            btn.classList.add('active');
            loadProducts();
        };
        wrap.insertBefore(btn, wrap.lastChild.nextSibling);
    });
    wrap.querySelector('.chip[data-id=""]').onclick = (e) => {
        currentCategory = '';
        wrap.querySelectorAll('.chip').forEach(x => x.classList.remove('active'));
        e.target.classList.add('active');
        loadProducts();
    };
}

async function loadProducts() {
    const grid = document.getElementById('productGrid');
    grid.innerHTML = '<div class="product-grid-empty"><i class="fa-solid fa-spinner fa-spin"></i><p class="mt-2 mb-0">Đang tải sản phẩm...</p></div>';

    try {
        const params = new URLSearchParams();
        if (currentCategory) params.set('id_danh_muc', currentCategory);
        if (searchKeyword) params.set('q', searchKeyword);
        const url = productListUrl + (params.toString() ? '?' + params.toString() : '');
        const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
        if (!res.ok) {
            grid.innerHTML = '<div class="product-grid-empty"><i class="fa-solid fa-circle-exclamation"></i><p>Lỗi tải sản phẩm (HTTP ' + res.status + ')</p></div>';
            return;
        }
        allProducts = await res.json();
        renderProducts();
    } catch (err) {
        console.error(err);
        grid.innerHTML = '<div class="product-grid-empty"><i class="fa-solid fa-circle-exclamation"></i><p>Lỗi tải sản phẩm</p></div>';
    }
}
function getBestPromotionForProduct(product, qty = 1) {
    if (!Array.isArray(allPromotions) || allPromotions.length === 0) {
        return {
            promotion: null,
            discount: 0
        };
    }

    const productId = Number(
        product.product_id ??
        product.id_san_pham ??
        0
    );

    const variantId = Number(
        product.id ??
        product.id_bien_the ??
        0
    );

    const price = Number(
        product.gia_ban ??
        product.gia ??
        product.gia_ban_le ??
        0
    );

    const itemTotal = price * Number(qty || 1);

    let bestPromotion = null;
    let bestDiscount = 0;

   allPromotions.forEach(km => {

        // Chỉ xét khuyến mãi sản phẩm
        if (!km.la_khuyen_mai_san_pham) {
            return;
        }

        const productIds = Array.isArray(km.id_san_phams)
            ? km.id_san_phams.map(Number)
            : [];

        const variantIds = Array.isArray(km.id_bien_thes)
            ? km.id_bien_thes.map(Number)
            : [];

        const applies =
            productIds.includes(productId) ||
            variantIds.includes(variantId);

        if (!applies) {
            return;
        }

        // Kiểm tra số lượng tối thiểu
        const minQty = Number(
            km.so_luong_sp_toi_thieu || 0
        );

        if (
            minQty > 0 &&
            Number(qty) < minQty
        ) {
            return;
        }

        const type = String(
            km.loai_giam_gia || ''
        )
            .trim()
            .toLowerCase();

        const value = Number(
            km.gia_tri_giam || 0
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
                itemTotal *
                value /
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
                value * Number(qty),
                itemTotal
            );
        }

        // Mua 1 tặng 1
        else if (
            [
                'bogo',
                'mua_1_tang_1'
            ].includes(type)
        ) {
            const freeQty =
                Math.floor(
                    Number(qty) / 2
                );

            discount =
                freeQty *
                price;
        }

        // Giảm tối đa
        const maxDiscount = Number(
            km.giam_toi_da || 0
        );

        if (maxDiscount > 0) {
            discount = Math.min(
                discount,
                maxDiscount
            );
        }

        // Không được giảm âm
        // và không vượt tổng tiền
        discount = Math.min(
            Math.max(0, discount),
            itemTotal
        );

        // Nếu có nhiều KM,
        // lấy KM giảm nhiều nhất
        if (discount > bestDiscount) {
            bestDiscount = discount;
            bestPromotion = km;
        }
    });

    return {
        promotion: bestPromotion,
        discount: bestDiscount
    };
}

function renderProducts() {
    const grid = document.getElementById('productGrid');

    if (!allProducts || allProducts.length === 0) {
        grid.innerHTML = `
            <div class="product-grid-empty">
                <i class="fa-solid fa-box-open"></i>
                <p>Không có sản phẩm</p>
            </div>
        `;
        return;
    }

    grid.innerHTML = allProducts.map(p => {

        const oos = Number(p.so_luong_ton_kho) <= 0;

        const imgHtml = p.hinh_anh
            ? `
                <img
                    src="${p.hinh_anh}"
                    alt="${p.ten_san_pham}"
                    onerror="this.outerHTML='<div class=\\'no-img\\'><i class=\\'fa-solid fa-box\\'></i></div>'"
                >
            `
            : `
                <div class="no-img">
                    <i class="fa-solid fa-box"></i>
                </div>
            `;


        // =====================================
        // KIỂM TRA KHUYẾN MÃI CỦA SẢN PHẨM
        // =====================================

        const result = getBestPromotionForProduct(p, 1);

        const promotion = result.promotion;
        const promotionDiscount = Number(result.discount || 0);

        const giaGoc = Number(p.gia_ban || 0);

        const giaSauGiam = Math.max(
            0,
            giaGoc - promotionDiscount
        );


        // =====================================
        // NHÃN KHUYẾN MÃI
        // =====================================

        let promotionBadge = '';

        if (promotion && promotionDiscount > 0) {

            const type = String(
                promotion.loai_giam_gia || ''
            )
                .trim()
                .toLowerCase();

            if (
                [
                    'percent',
                    'phan_tram',
                    'percentage'
                ].includes(type)
            ) {

                promotionBadge = `
                    <div
                        class="badge-stock"
                        style="
                            background:#dc3545;
                            left:6px;
                            right:auto;
                        "
                    >
                        Giảm ${Number(
                            promotion.gia_tri_giam || 0
                        )}%
                    </div>
                `;

            } else {

                promotionBadge = `
                    <div
                        class="badge-stock"
                        style="
                            background:#dc3545;
                            left:6px;
                            right:auto;
                        "
                    >
                        Giảm ${fmt(promotionDiscount)}
                    </div>
                `;

            }
        }


        // =====================================
        // HIỂN THỊ GIÁ
        // =====================================

        let priceHtml = '';

        if (promotion && promotionDiscount > 0) {

            priceHtml = `
                <div class="price">

                    <span
                        style="
                            text-decoration: line-through;
                            color:#8b95a5;
                            font-size:12px;
                            margin-right:5px;
                        "
                    >
                        ${fmt(giaGoc)}
                    </span>

                    <span>
                        ${fmt(giaSauGiam)}
                    </span>

                </div>
            `;

        } else {

            priceHtml = `
                <div class="price">
                    ${fmt(giaGoc)}
                </div>
            `;
        }


        return `
            <div
                class="product-card ${oos ? 'out-of-stock' : ''}"
                data-id="${p.id}"
            >

                <div class="img-wrap">

                    ${imgHtml}

                    ${promotionBadge}

                    ${
                        oos
                            ? `
                                <div
                                    class="badge-stock"
                                    style="background:var(--pos-danger);"
                                >
                                    Hết hàng
                                </div>
                            `
                            : `
                                <div class="badge-stock">
                                    Kho: ${p.so_luong_ton_kho}
                                </div>
                            `
                    }

                </div>

                <div class="info">

                    <div class="name">
                        ${p.ten_san_pham || ''}
                    </div>

                    ${priceHtml}

                    ${
                        p.ma_vach
                            ? `
                                <div class="barcode">
                                    <i class="fa-solid fa-barcode me-1"></i>
                                    ${p.ma_vach}
                                </div>
                            `
                            : ''
                    }

                </div>

            </div>
        `;
    }).join('');


    // =====================================
    // CLICK SẢN PHẨM
    // =====================================

    grid.querySelectorAll('.product-card').forEach(card => {

        card.onclick = () => {

            if (card.classList.contains('out-of-stock')) {
                showToast(
                    'Sản phẩm đã hết hàng',
                    'warn'
                );
                return;
            }

            const id = parseInt(
                card.dataset.id,
                10
            );

            addToCart(id);
        };

    });
}

// =========================================================
// CART
// =========================================================
function addToCart(productId) {
    const p = allProducts.find(x => x.id === productId);
    if (!p) return;

    const inv = getActiveInvoice();
    const existing = inv.items.find(x => x.id === productId);
    if (existing) {
        existing.qty += 1;
    } else {
        inv.items.push({
            id: p.id,
            id_san_pham: p.id_san_pham,
            ten_san_pham: p.ten_san_pham,
            hinh_anh: p.hinh_anh,
            gia_ban: p.gia_ban,
            ma_vach: p.ma_vach,
            qty: 1,
        });
    }
    showToast('Đã thêm: ' + p.ten_san_pham);
    renderInvoiceItems();
    renderTotals();
    renderInvoiceTabs();
}

function removeFromCart(productId) {
    const inv = getActiveInvoice();
    inv.items = inv.items.filter(x => x.id !== productId);
    renderInvoiceItems();
    renderTotals();
    renderInvoiceTabs();
}

function updateQty(productId, newQty) {
    const inv = getActiveInvoice();
    const it = inv.items.find(x => x.id === productId);
    if (!it) return;
    it.qty = Math.max(1, newQty);
    renderInvoiceItems();
    renderTotals();
}

function renderInvoiceItems() {
    const box = document.getElementById('invoiceItems');
    const inv = getActiveInvoice();
    if (inv.items.length === 0) {
        box.innerHTML = `<div class="invoice-empty">
            <i class="fa-solid fa-cart-shopping"></i>
            <p class="mt-2 mb-0">Chưa có sản phẩm trong hóa đơn</p>
            <small>Bấm vào sản phẩm bên phải để thêm</small>
        </div>`;
        return;
    }
    box.innerHTML = inv.items.map(it => {
        const img = it.hinh_anh
            ? `<img src="${it.hinh_anh}" onerror="this.outerHTML='<div class=\\'no-img\\' style=\\'background:#e2e8f0;width:56px;height:56px;display:flex;align-items:center;justify-content:center;border-radius:8px;color:#94a3b8;\\'><i class=\\'fa-solid fa-box\\'></i></div>'">`
            : `<div style="background:#e2e8f0;width:56px;height:56px;display:flex;align-items:center;justify-content:center;border-radius:8px;color:#94a3b8;"><i class="fa-solid fa-box"></i></div>`;
        return `
        <div class="invoice-item" data-id="${it.id}">
            ${img}
            <div>
                <div class="name">${it.ten_san_pham}</div>
                <div class="price">${fmt(it.gia_ban)}</div>
                <div class="qty-control">
                    <button class="qty-btn" onclick="updateQty(${it.id}, ${it.qty - 1})">−</button>
                    <input class="qty-input" type="number" min="1" value="${it.qty}" onchange="updateQty(${it.id}, parseInt(this.value||1))">
                    <button class="qty-btn" onclick="updateQty(${it.id}, ${it.qty + 1})">+</button>
                </div>
            </div>
            <div>
                <div class="total">${fmt(it.gia_ban * it.qty)}</div>
                <span class="remove-btn" onclick="removeFromCart(${it.id})"><i class="fa-solid fa-trash-can me-1"></i>Xóa</span>
            </div>
        </div>`;
    }).join('');
}

// =========================================================
// TOTALS
// =========================================================
function calcTotals() {
    const inv = getActiveInvoice();
    const subtotal = inv.items.reduce((s, x) => s + x.gia_ban * x.qty, 0);
    const productDiscount = calcProductDiscount(inv.items);
    const pointDiscount = (Number(inv.usePoint) || 0) * 100;
    const voucherDiscount = Number(inv.voucherDiscount) || 0;
    const total = Math.max(0, subtotal - productDiscount - voucherDiscount - pointDiscount);
    return { subtotal, productDiscount, pointDiscount, voucherDiscount, total };
}

function calcProductDiscount(items) {
    let totalDiscount = 0;

    items.forEach(it => {

        const result = getBestPromotionForProduct(
            it,
            Number(it.qty || 1)
        );

        totalDiscount += Number(
            result.discount || 0
        );

    });

    return totalDiscount;
}

function renderTotals() {
    const t = calcTotals();
    document.getElementById('subtotal').textContent = fmt(t.subtotal);
    document.getElementById('discount').textContent = fmt(t.productDiscount + t.voucherDiscount);
    document.getElementById('pointUse').textContent = fmt(t.pointDiscount);
    document.getElementById('total').textContent = fmt(t.total);
    document.getElementById('btnCheckout').disabled = (getActiveInvoice().items.length === 0);
}

// =========================================================
// CUSTOMER
// =========================================================

// ================================
// TÌM KIẾM KHÁCH HÀNG
// ================================
document.getElementById('searchCustomer').addEventListener('input', async function() {

    const kw = this.value.trim();
    const box = document.getElementById('customerSuggest');

    // Không nhập gì
    if (kw.length < 1) {
        box.classList.remove('show');
        box.innerHTML = '';
        return;
    }

    try {

        const res = await fetch(
            customerListUrl + '?q=' + encodeURIComponent(kw),
            {
                headers: {
                    'Accept': 'application/json'
                }
            }
        );

        const list = await res.json();

        // Không tìm thấy
        if (!list || list.length === 0) {

            box.innerHTML = `
                <div class="suggest-item text-muted">
                    <i class="fa-solid fa-user-slash me-1"></i>
                    Không tìm thấy khách hàng
                </div>
            `;

            box.classList.add('show');

            return;
        }

        // Hiển thị danh sách khách hàng
        box.innerHTML = list.map(c => `
            <div
                class="suggest-item"
                data-id="${c.id}"
            >
                <div class="name">
                    <i class="fa-solid fa-user me-1 text-primary"></i>
                    ${c.ten_khach_hang}

                    ${
                        c.so_dien_thoai
                            ? `(${c.so_dien_thoai})`
                            : ''
                    }
                </div>

                <div class="meta">
                    <i class="fa-solid fa-star me-1"></i>
                    ${c.diem_tich_luy || 0} điểm
                    ·
                    Chi tiêu: ${fmt(c.tong_chi_tieu || 0)}
                </div>
            </div>
        `).join('');

        box.classList.add('show');


        // ================================
        // CLICK CHỌN KHÁCH HÀNG
        // ================================
        box.querySelectorAll('.suggest-item').forEach((item, index) => {

            const customer = list[index];

            if (!customer) return;

            item.addEventListener('click', function() {

                selectCustomer(customer);

                box.classList.remove('show');
                box.innerHTML = '';

            });

        });

    } catch (err) {

        console.error(
            'Lỗi tìm kiếm khách hàng:',
            err
        );

    }

});


// ================================
// CHỌN KHÁCH HÀNG
// ================================
function selectCustomer(c) {

    if (!c) return;

    selectedCustomer = c;

    // Lưu khách hàng vào hóa đơn hiện tại
    getActiveInvoice().customer = c;

    // Hiển thị thông tin khách hàng
    renderCustomerInfo();

    // Hiển thị tên trong ô tìm kiếm
    document.getElementById('searchCustomer').value =
        c.ten_khach_hang +
        (
            c.so_dien_thoai
                ? ' (' + c.so_dien_thoai + ')'
                : ''
        );

    // Đóng danh sách gợi ý
    const box = document.getElementById('customerSuggest');

    box.classList.remove('show');
    box.innerHTML = '';
}


// ================================
// HIỂN THỊ KHÁCH HÀNG ĐÃ CHỌN
// ================================
function renderCustomerInfo() {

    const info = document.getElementById('customerInfo');

    if (selectedCustomer) {

        info.classList.add('show');

        document.getElementById('customerName').textContent =
            selectedCustomer.ten_khach_hang +
            (
                selectedCustomer.so_dien_thoai
                    ? ' - ' + selectedCustomer.so_dien_thoai
                    : ''
            );

        document.getElementById('customerPoints').textContent =
            selectedCustomer.diem_tich_luy || 0;

    } else {

        info.classList.remove('show');

    }
}


// ================================
// XÓA KHÁCH HÀNG KHỎI HÓA ĐƠN
// ================================
document.getElementById('btnRemoveCustomer').onclick = () => {

    selectedCustomer = null;

    getActiveInvoice().customer = null;

    document.getElementById('searchCustomer').value = '';

    const box = document.getElementById('customerSuggest');

    box.classList.remove('show');
    box.innerHTML = '';

    renderCustomerInfo();
};


// =========================================================
// QUICK ADD CUSTOMER
// =========================================================

// Route tạo khách hàng
const createCustomerUrl =
    '{{ route('nhan-vien.khach-hang.them-nhanh') }}';


// Bootstrap Modal
const addCustomerModalElement =
    document.getElementById('addCustomerModal');

const addCustomerModal =
    bootstrap.Modal.getOrCreateInstance(
        addCustomerModalElement
    );


// ================================
// MỞ MODAL THÊM KHÁCH HÀNG
// ================================
document.getElementById('btnAddCustomer').addEventListener(
    'click',
    function() {

        // Reset form
        document.getElementById('addCustomerForm').reset();

        // Xóa lỗi SĐT
        const phoneError =
            document.getElementById('customerPhoneError');

        phoneError.textContent = '';
        phoneError.style.display = 'none';

        // Đóng suggestion
        const box =
            document.getElementById('customerSuggest');

        box.classList.remove('show');
        box.innerHTML = '';

        // Mở modal
        addCustomerModal.show();

        // Focus tên khách hàng
        setTimeout(() => {

            document
                .getElementById('newCustomerName')
                .focus();

        }, 300);

    }
);


// ================================
// SUBMIT THÊM KHÁCH HÀNG
// ================================
document.getElementById('addCustomerForm').addEventListener(
    'submit',
    async function(e) {

        e.preventDefault();

        const name =
            document
                .getElementById('newCustomerName')
                .value
                .trim();

        const phone =
            document
                .getElementById('newCustomerPhone')
                .value
                .trim();

        const email =
            document
                .getElementById('newCustomerEmail')
                .value
                .trim();

        const address =
            document
                .getElementById('newCustomerAddress')
                .value
                .trim();

        const btn =
            document.getElementById('btnSaveCustomer');

        const phoneError =
            document.getElementById('customerPhoneError');


        // ================================
        // VALIDATE TÊN
        // ================================
        if (!name) {

            showToast(
                'Vui lòng nhập tên khách hàng',
                'error'
            );

            document
                .getElementById('newCustomerName')
                .focus();

            return;
        }


        // ================================
        // VALIDATE SỐ ĐIỆN THOẠI
        // ================================
        if (phone) {

            const phoneRegex =
                /^(0|\+84)[0-9]{8,10}$/;

            if (!phoneRegex.test(phone)) {

                phoneError.textContent =
                    'Số điện thoại không đúng định dạng';

                phoneError.style.display = 'block';

                document
                    .getElementById('newCustomerPhone')
                    .focus();

                return;
            }

        }

        phoneError.textContent = '';
        phoneError.style.display = 'none';


        // ================================
        // LOADING
        // ================================
        btn.disabled = true;

        btn.innerHTML = `
            <span
                class="spinner-border spinner-border-sm me-1"
            ></span>
            Đang lưu...
        `;


        try {

            const res = await fetch(
                createCustomerUrl,
                {
                    method: 'POST',

                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },

                    body: JSON.stringify({

                        ten_khach_hang: name,
                        so_dien_thoai: phone || null,
                        email: email || null,
                        dia_chi: address || null

                    })
                }
            );


            const data = await res.json();


            // ================================
            // XỬ LÝ LỖI
            // ================================
            if (!res.ok || !data.success) {

                // Lỗi trùng SĐT
                if (
                    data.errors &&
                    data.errors.so_dien_thoai
                ) {

                    phoneError.textContent =
                        data.errors.so_dien_thoai[0];

                    phoneError.style.display = 'block';

                } else {

                    showToast(
                        data.message ||
                        'Không thể thêm khách hàng',
                        'error'
                    );

                }

                return;
            }


            // ================================
            // KHÁCH HÀNG TẠO THÀNH CÔNG
            // ================================
            const customer = data.customer;


            if (!customer) {

                showToast(
                    'Server không trả về thông tin khách hàng',
                    'error'
                );

                return;
            }


            // Gán ngay vào hóa đơn hiện tại
            selectCustomer(customer);


            // Đóng modal
            addCustomerModal.hide();


            // Thông báo
            showToast(
                'Đã thêm khách hàng: ' +
                customer.ten_khach_hang
            );


        } catch (err) {

            console.error(
                'Lỗi thêm khách hàng:',
                err
            );

            showToast(
                'Lỗi kết nối máy chủ!',
                'error'
            );

        } finally {

            btn.disabled = false;

            btn.innerHTML = `
                <i class="fa-solid fa-check me-1"></i>
                Lưu khách hàng
            `;

        }

    }
);
// =========================================================
// BARCODE SCAN
// =========================================================
document.getElementById('barcodeInput').addEventListener('keydown', async function(e) {
    if (e.key !== 'Enter') return;
    const code = this.value.trim();
    if (!code) return;
    try {
        const res = await fetch(productListUrl + '?q=' + encodeURIComponent(code), { headers: { 'Accept': 'application/json' } });
        const list = await res.json();
        if (list && list.length > 0) {
            addToCart(list[0].id);
            this.value = '';
        } else {
            showToast('Không tìm thấy sản phẩm với mã: ' + code, 'error');
        }
    } catch (err) { console.error(err); }
});

// =========================================================
// PRODUCT SEARCH
// =========================================================
let searchTimer;
document.getElementById('searchProduct').addEventListener('input', function() {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(() => {
        searchKeyword = this.value.trim();
        loadProducts();
    }, 300);
});

// =========================================================
// PROMOTIONS
// =========================================================
async function loadPromotions() {
    try {
        const res = await fetch(
            promotionListUrl,
            {
                headers: {
                    'Accept': 'application/json'
                }
            }
        );

        if (!res.ok) {
            throw new Error(
                'Không tải được khuyến mãi: HTTP ' + res.status
            );
        }

        allPromotions = await res.json();

        console.log(
            'Khuyến mãi POS:',
            allPromotions
        );

        // Nếu sản phẩm đã tải rồi
        // thì render lại để hiện giá khuyến mãi
        if (
            Array.isArray(allProducts) &&
            allProducts.length > 0
        ) {
            renderProducts();
        }

    } catch (err) {
        console.error(
            'Lỗi tải khuyến mãi:',
            err
        );
    }
}

// =========================================================
// SELLERS
// =========================================================
async function loadSellers() {
    try {
        const res = await fetch(sellerListUrl, { headers: { 'Accept': 'application/json' } });
        allSellers = await res.json();
        const sel = document.getElementById('sellerSelect');
        sel.innerHTML = '<option value="">-- Người bán --</option>';
        allSellers.forEach(s => {
            const opt = document.createElement('option');
            opt.value = s.id;
            opt.textContent = s.ho_ten + (s.ten_vai_tro ? ' (' + s.ten_vai_tro + ')' : '');
            sel.appendChild(opt);
        });
        // Chọn giá trị theo thứ tự ưu tiên:
        //  1) Lựa chọn đã ghi nhớ trong localStorage (nếu còn hợp lệ).
        //  2) Người đang đăng nhập.
        //  3) Để trống.
        const me = '{{ $nguoiDung->id ?? '' }}';
        const remembered = localStorage.getItem(SELLER_STORAGE_KEY);
        let chosen = '';
        if (remembered && allSellers.some(s => String(s.id) === String(remembered))) {
            chosen = remembered;
        } else if (me && allSellers.some(s => String(s.id) === String(me))) {
            chosen = me;
            // id cũ không còn hợp lệ -> đồng bộ lại localStorage
            if (remembered && remembered !== me) {
                localStorage.setItem(SELLER_STORAGE_KEY, me);
            }
        } else if (remembered) {
            // id trong localStorage không còn trong danh sách -> xóa đi
            localStorage.removeItem(SELLER_STORAGE_KEY);
        }
        sel.value = chosen;
        getActiveInvoice().sellerId = sel.value;
    } catch (err) { console.error(err); }
}

document.getElementById('sellerSelect').addEventListener('change', function() {
    getActiveInvoice().sellerId = this.value;
    if (this.value) {
        localStorage.setItem(SELLER_STORAGE_KEY, this.value);
    } else {
        localStorage.removeItem(SELLER_STORAGE_KEY);
    }
});

// =========================================================
// CHECKOUT MODAL
// =========================================================
document.getElementById('btnCheckout').onclick = () => {
    const inv = getActiveInvoice();
    if (inv.items.length === 0) {
        showToast('Chưa có sản phẩm trong hóa đơn', 'warn');
        return;
    }
    populateCheckout();
    bootstrap.Modal.getOrCreateInstance(document.getElementById('checkoutModal')).show();
};

function populateCheckout() {
    const inv = getActiveInvoice();
    const itemsBox = document.getElementById('checkoutItems');
    itemsBox.innerHTML = inv.items.map(it => `
        <div class="d-flex justify-content-between border-bottom py-1">
            <div>${it.ten_san_pham} <small class="text-muted">×${it.qty}</small></div>
            <strong>${fmt(it.gia_ban * it.qty)}</strong>
        </div>
    `).join('');

    // Voucher dropdown
    const voucherSel = document.getElementById('ckVoucher');
    voucherSel.innerHTML = '<option value="">-- Không áp dụng --</option>';
    (allPromotions || []).filter(km => !km.la_khuyen_mai_san_pham).forEach(km => {
        const opt = document.createElement('option');
        opt.value = km.id;
        let label = km.ten_chuong_trinh;
        if (km.loai_giam_gia === 'phan_tram') label += ` (-${km.gia_tri_giam}%)`;
        else if (km.loai_giam_gia === 'amount') label += ` (-${fmt(km.gia_tri_giam)})`;
        opt.textContent = label;
        voucherSel.appendChild(opt);
    });
    if (inv.voucherId) voucherSel.value = inv.voucherId;

    document.getElementById('ckUsePoint').value = inv.usePoint || 0;
    document.getElementById('ckCustomer').textContent = (inv.customer && inv.customer.ten_khach_hang) || 'Khách lẻ';

    recomputeCheckout();

    document.getElementById('customerMoney').value = '';
    updateChange();
}

function recomputeCheckout() {
    const inv = getActiveInvoice();
    const t = calcTotals();
    document.getElementById('ckSubtotal').textContent = fmt(t.subtotal);
    document.getElementById('ckDiscount').textContent = fmt(t.productDiscount);
    document.getElementById('ckVoucherDiscount').textContent = fmt(t.voucherDiscount);
    document.getElementById('ckPointDiscount').textContent = fmt(t.pointDiscount);
    document.getElementById('ckTotal').textContent = fmt(t.total);
    updateChange();
}

document.getElementById('ckVoucher').addEventListener('change', function() {
    const inv = getActiveInvoice();
    inv.voucherId = this.value || null;
    const km = (allPromotions || []).find(x => String(x.id) === String(this.value));
    let vd = 0;
    if (km) {
        const t = calcTotals();
        if (km.loai_giam_gia === 'phan_tram') {
            vd = Math.max(0, (t.subtotal - t.productDiscount) * Number(km.gia_tri_giam) / 100);
            if (km.giam_toi_da) vd = Math.min(vd, Number(km.giam_toi_da));
        } else if (km.loai_giam_gia === 'amount') {
            vd = Math.min(t.subtotal - t.productDiscount, Number(km.gia_tri_giam));
        }
    }
    inv.voucherDiscount = vd;
    recomputeCheckout();
});

document.getElementById('ckUsePoint').addEventListener('input', function() {
    const inv = getActiveInvoice();
    let val = parseInt(this.value || 0, 10);
    if (val < 0) val = 0;
    if (selectedCustomer) val = Math.min(val, Number(selectedCustomer.diem_tich_luy || 0));
    inv.usePoint = val;
    this.value = val;
    recomputeCheckout();
});

document.querySelectorAll('.pay-method').forEach(el => {
    el.onclick = () => {
        document.querySelectorAll('.pay-method').forEach(x => x.classList.remove('active'));
        el.classList.add('active');
        selectedPayment = el.dataset.method;
        document.getElementById('cashBox').style.display = (selectedPayment === 'cash') ? '' : 'none';
    };
});

document.querySelectorAll('.quick-money').forEach(btn => {
    btn.onclick = () => {
        const amt = btn.dataset.amt;
        const inputEl = document.getElementById('customerMoney');
        if (amt) {
            inputEl.value = amt;
        } else {
            const t = calcTotals().total;
            inputEl.value = String(t);
        }
        // Kích hoạt format tiền tệ: money-input utility lắng nghe event 'input' để format.
        // Set value thẳng không trigger event -> phải dispatch thủ công.
        inputEl.dispatchEvent(new Event('input', { bubbles: true }));
        updateChange();
    };
});

document.getElementById('customerMoney').addEventListener('input', updateChange);

function updateChange() {
    const t = calcTotals().total;
    const given = parseMoney(document.getElementById('customerMoney').value);
    const change = Math.max(0, given - t);
    document.getElementById('changeMoney').textContent = fmt(change);
}

// =========================================================
// CONFIRM PAYMENT
// =========================================================
document.getElementById('btnConfirmPay').onclick = async () => {
    const inv = getActiveInvoice();
    if (inv.items.length === 0) {
        showToast('Giỏ hàng trống', 'error');
        return;
    }

    const t = calcTotals();
    let customerMoney = parseMoney(document.getElementById('customerMoney').value);
    if (selectedPayment === 'cash') {
        if (customerMoney < t.total) {
            showToast('Tiền khách đưa chưa đủ', 'error');
            return;
        }
    } else {
        customerMoney = t.total;
    }

    const idNguoiBan = document.getElementById('sellerSelect').value || null;

    const body = {
        cart: inv.items.map(it => ({ id: it.id, qty: it.qty })),
        id_khach_hang: inv.customer ? inv.customer.id : null,
        id_nguoi_ban: idNguoiBan,
        id_khuyen_mai: inv.voucherId || null,
        tien_khach_dua: customerMoney,
        phuong_thuc_thanh_toan: selectedPayment,
        diem_su_dung: Number(inv.usePoint) || 0,
    };

    try {
        document.getElementById('btnConfirmPay').disabled = true;
        const res = await fetch(checkoutUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
            },
            body: JSON.stringify(body),
        });

        let data;
        try {
            const rawText = await res.text();
            data = JSON.parse(rawText);
        } catch (parseErr) {
            showToast('Phản hồi server không hợp lệ. Vui lòng thử lại.', 'error');
            document.getElementById('btnConfirmPay').disabled = false;
            return;
        }

        if (!res.ok || !data.success) {
            showToast(data.message || 'Thanh toán thất bại!', 'error');
            document.getElementById('btnConfirmPay').disabled = false;
            return;
        }

        const hoaDonId = data.hoa_don_id;
        bootstrap.Modal.getOrCreateInstance(document.getElementById('checkoutModal')).hide();
        document.getElementById('btnConfirmPay').disabled = false;

        // Reset tab
        invoices[activeInvoiceIdx] = newInvoice();
        renderInvoiceTabs();
        applyActiveInvoice();

        showToast('Thanh toán thành công! #' + hoaDonId);

        if (data.redirect_to_payos) {
            await redirectToPayOS(hoaDonId);
            return;
        }

        // In hóa đơn
        setTimeout(() => {
            window.open(invoicePrintUrl + '/' + hoaDonId + '?print=1', '_blank');
        }, 400);

        loadProducts();
    } catch (err) {
        console.error(err);
        showToast('Lỗi kết nối máy chủ!', 'error');
        document.getElementById('btnConfirmPay').disabled = false;
    }
};

async function redirectToPayOS(hoaDonId) {
    try {
        const res = await fetch(payOSCreateUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
            },
            body: JSON.stringify({ hoa_don_id: hoaDonId }),
        });
        const data = await res.json();
        if (!res.ok || !data.success || !data.checkout_url) {
            showToast(data.message || 'Không tạo được link PayOS', 'error');
            return;
        }
        window.open(data.checkout_url, '_blank');
    } catch (err) {
        console.error(err);
        showToast('Lỗi khi tạo link PayOS', 'error');
    }
}

// =========================================================
// CLEAR CART
// =========================================================
document.getElementById('btnClearCart').onclick = () => {
    const inv = getActiveInvoice();
    if (inv.items.length === 0) return;
    if (!confirm('Xóa toàn bộ sản phẩm trong hóa đơn này?')) return;
    inv.items = [];
    inv.customer = null;
    inv.usePoint = 0;
    inv.voucherId = null;
    inv.voucherDiscount = 0;
    selectedCustomer = null;
    document.getElementById('searchCustomer').value = '';
    renderCustomerInfo();
    renderInvoiceItems();
    renderTotals();
};

// =========================================================
// SIDEBAR TOGGLE
// =========================================================
const sidebarEl = document.getElementById('posSidebar');
const sidebarOverlay = document.getElementById('sidebarOverlay');

function openSidebar() {
    sidebarEl.classList.add('open');
    sidebarOverlay.classList.add('show');
}

function closeSidebar() {
    sidebarEl.classList.remove('open');
    sidebarOverlay.classList.remove('show');
}

document.getElementById('btnOpenSidebar').addEventListener('click', openSidebar);
document.getElementById('btnCloseSidebar').addEventListener('click', closeSidebar);
sidebarOverlay.addEventListener('click', closeSidebar);

// Đóng sidebar khi bấm phím Escape
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape' && sidebarEl.classList.contains('open')) {
        closeSidebar();
    }
});

// =========================================================
// LIVE CLOCK
// =========================================================
function tickClock() {
    const now = new Date();
    const pad = (n) => String(n).padStart(2, '0');
    const days = ['CN','T2','T3','T4','T5','T6','T7'];
    document.getElementById('liveClock').textContent =
        `${days[now.getDay()]} ${pad(now.getHours())}:${pad(now.getMinutes())}:${pad(now.getSeconds())} - ${pad(now.getDate())}/${pad(now.getMonth()+1)}/${now.getFullYear()}`;
}
setInterval(tickClock, 1000);
tickClock();

// =========================================================
// TOAST
// =========================================================
function showToast(message, type = 'success') {
    const t = document.getElementById('posToast');
    const msg = document.getElementById('toastMessage');
    msg.textContent = message;
    t.classList.remove('error', 'warn');
    if (type === 'error') t.classList.add('error');
    else if (type === 'warn') t.classList.add('warn');
    t.classList.add('show');
    setTimeout(() => t.classList.remove('show'), 2200);
}

// =========================================================
// INIT
// =========================================================
renderInvoiceTabs();
applyActiveInvoice();
loadCategories();
loadProducts();
loadPromotions();
loadSellers();
</script>
</body>
</html>