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
            --pos-primary: #2e7d32;
            --pos-primary-dark: #1b5e20;
            --pos-primary-light: #4caf50;
            --pos-accent: #a5d6a7;
            --pos-bg: #f1f8e9;
            --pos-card: #ffffff;
            --pos-text: #212121;
            --pos-text-muted: #757575;
            --pos-border: #e0e0e0;
            --pos-sidebar: #1b5e20;
            --pos-warning: #f57c00;
            --pos-danger: #d32f2f;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Roboto', sans-serif;
            background: var(--pos-bg);
            height: 100vh;
            overflow: hidden;
            color: var(--pos-text);
        }

        /* ── POS Header ── */
        .pos-header {
            background: var(--pos-primary-dark);
            color: #fff;
            padding: 10px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            height: 56px;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
        }

        .pos-header .logo {
            font-size: 20px;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .pos-header .logo i {
            color: var(--pos-accent);
        }

        .pos-header .pos-datetime {
            font-size: 14px;
            color: var(--pos-accent);
            text-align: center;
        }

        .pos-header .pos-user {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .pos-header .pos-user .user-avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: var(--pos-accent);
            color: var(--pos-primary-dark);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 14px;
        }

        .pos-header .pos-user .user-info {
            font-size: 13px;
        }

        .pos-header .pos-user .user-info strong {
            display: block;
        }

        .pos-header .pos-user .user-info small {
            opacity: 0.7;
        }

        .pos-header .btn-exit {
            background: rgba(255,255,255,0.1);
            border: 1px solid rgba(255,255,255,0.2);
            color: #fff;
            padding: 6px 14px;
            border-radius: 6px;
            font-size: 13px;
            cursor: pointer;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 6px;
            transition: background 0.2s;
        }

        .pos-header .btn-exit:hover {
            background: rgba(255,255,255,0.2);
            color: #fff;
        }

        /* ── POS Body ── */
        .pos-body {
            display: flex;
            height: 100vh;
            padding-top: 56px;
        }

        /* ── Sidebar (Cột 1 - 64px) ── */
        .pos-sidebar {
            width: 64px;
            background: var(--pos-sidebar);
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 16px 0;
            gap: 8px;
            flex-shrink: 0;
        }

        .pos-sidebar .nav-btn {
            width: 48px;
            height: 48px;
            border-radius: 10px;
            background: rgba(255,255,255,0.08);
            border: none;
            color: rgba(255,255,255,0.7);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s;
            font-size: 10px;
            gap: 2px;
            text-decoration: none;
        }

        .pos-sidebar .nav-btn:hover,
        .pos-sidebar .nav-btn.active {
            background: var(--pos-primary);
            color: #fff;
        }

        .pos-sidebar .nav-btn i {
            font-size: 16px;
        }

        .pos-sidebar .nav-btn span {
            font-size: 9px;
            font-weight: 500;
        }

        .pos-sidebar .nav-divider {
            width: 32px;
            height: 1px;
            background: rgba(255,255,255,0.15);
            margin: 4px 0;
        }

        /* ── Product Panel (Cột 2) ── */
        .pos-products {
            flex: 1;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            background: var(--pos-bg);
        }

        /* Category tabs */
        .pos-category-bar {
            background: var(--pos-card);
            padding: 10px 16px;
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            border-bottom: 1px solid var(--pos-border);
            flex-shrink: 0;
        }

        .pos-category-bar .cat-btn {
            padding: 6px 14px;
            border-radius: 20px;
            border: 1px solid var(--pos-border);
            background: #fff;
            color: var(--pos-text-muted);
            font-size: 12px;
            font-weight: 500;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 5px;
            transition: all 0.2s;
        }

        .pos-category-bar .cat-btn:hover {
            border-color: var(--pos-primary);
            color: var(--pos-primary);
        }

        .pos-category-bar .cat-btn.active {
            background: var(--pos-primary);
            border-color: var(--pos-primary);
            color: #fff;
        }

        .pos-category-bar .cat-btn .shortcut {
            background: rgba(255,255,255,0.25);
            padding: 1px 5px;
            border-radius: 3px;
            font-size: 10px;
            font-weight: 700;
        }

        .pos-category-bar .cat-btn:not(.active) .shortcut {
            background: #eee;
            color: var(--pos-text-muted);
        }

        /* Search */
        .pos-search-bar {
            padding: 10px 16px;
            background: var(--pos-card);
            border-bottom: 1px solid var(--pos-border);
            flex-shrink: 0;
        }

        .pos-search-bar .search-wrapper {
            position: relative;
        }

        .pos-search-bar .search-wrapper i {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--pos-text-muted);
            font-size: 14px;
        }

        .pos-search-bar .search-wrapper input {
            width: 100%;
            padding: 8px 12px 8px 36px;
            border: 1px solid var(--pos-border);
            border-radius: 8px;
            font-size: 14px;
            outline: none;
            transition: border-color 0.2s;
        }

        .pos-search-bar .search-wrapper input:focus {
            border-color: var(--pos-primary);
        }

        /* Product grid */
        .pos-product-grid {
            flex: 1;
            overflow-y: auto;
            padding: 12px 16px;
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 10px;
            align-content: start;
        }

        .pos-product-card {
            background: var(--pos-card);
            border-radius: 10px;
            border: 1px solid var(--pos-border);
            cursor: pointer;
            transition: all 0.2s;
            overflow: hidden;
        }

        .pos-product-card:hover {
            border-color: var(--pos-primary);
            box-shadow: 0 4px 12px rgba(46,125,50,0.15);
            transform: translateY(-1px);
        }

        .pos-product-card .product-img {
            height: 80px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 36px;
            background: #f5f5f5;
        }

        .pos-product-card .product-info {
            padding: 8px;
            text-align: center;
        }

        .pos-product-card .product-name {
            font-size: 12px;
            font-weight: 600;
            color: var(--pos-text);
            margin-bottom: 4px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .pos-product-card .product-price {
            font-size: 13px;
            font-weight: 700;
            color: var(--pos-primary);
        }

        .pos-product-card .product-stock {
            font-size: 10px;
            color: var(--pos-text-muted);
            margin-top: 2px;
        }

        .pos-product-card .product-stock.low {
            color: var(--pos-danger);
            font-weight: 600;
        }

        /* ── Cart Panel (Cột 3) ── */
        .pos-cart {
            width: 280px;
            background: var(--pos-card);
            border-left: 1px solid var(--pos-border);
            display: flex;
            flex-direction: column;
            flex-shrink: 0;
        }

        .pos-cart .cart-header {
            padding: 12px 14px;
            border-bottom: 1px solid var(--pos-border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-shrink: 0;
        }

        .pos-cart .cart-header h5 {
            font-size: 14px;
            font-weight: 700;
            color: var(--pos-primary);
            margin: 0;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .pos-cart .cart-header .cart-count {
            background: var(--pos-primary);
            color: #fff;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            font-size: 11px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .pos-cart .cart-header .btn-clear {
            background: none;
            border: none;
            color: var(--pos-text-muted);
            font-size: 12px;
            cursor: pointer;
            padding: 4px 8px;
            border-radius: 4px;
        }

        .pos-cart .cart-header .btn-clear:hover {
            color: var(--pos-danger);
            background: #ffebee;
        }

        /* Cart items */
        .pos-cart .cart-items {
            flex: 1;
            overflow-y: auto;
        }

        .pos-cart .cart-item {
            padding: 10px 14px;
            border-bottom: 1px solid #f0f0f0;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .pos-cart .cart-item .item-img {
            width: 40px;
            height: 40px;
            border-radius: 6px;
            background: #f5f5f5;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            flex-shrink: 0;
        }

        .pos-cart .cart-item .item-details {
            flex: 1;
            min-width: 0;
        }

        .pos-cart .cart-item .item-name {
            font-size: 12px;
            font-weight: 600;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .pos-cart .cart-item .item-price {
            font-size: 11px;
            color: var(--pos-text-muted);
        }

        .pos-cart .cart-item .item-qty {
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .pos-cart .cart-item .qty-btn {
            width: 24px;
            height: 24px;
            border-radius: 4px;
            border: 1px solid var(--pos-border);
            background: #fff;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            color: var(--pos-primary);
            transition: all 0.2s;
        }

        .pos-cart .cart-item .qty-btn:hover {
            background: var(--pos-primary);
            color: #fff;
            border-color: var(--pos-primary);
        }

        .pos-cart .cart-item .qty-num {
            min-width: 20px;
            text-align: center;
            font-weight: 600;
            font-size: 13px;
        }

        .pos-cart .cart-item .item-total {
            font-weight: 700;
            font-size: 13px;
            color: var(--pos-primary);
            text-align: right;
            min-width: 60px;
        }

        .pos-cart .cart-item .btn-remove {
            background: none;
            border: none;
            color: var(--pos-text-muted);
            cursor: pointer;
            padding: 4px;
            font-size: 12px;
        }

        .pos-cart .cart-item .btn-remove:hover {
            color: var(--pos-danger);
        }

        .pos-cart .cart-empty {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: var(--pos-text-muted);
            gap: 8px;
        }

        .pos-cart .cart-empty i {
            font-size: 40px;
            opacity: 0.3;
        }

        .pos-cart .cart-empty p {
            font-size: 13px;
            margin: 0;
        }

        /* Cart summary */
        .pos-cart .cart-summary {
            padding: 12px 14px;
            border-top: 1px solid var(--pos-border);
            background: #fafafa;
            flex-shrink: 0;
        }

        .pos-cart .cart-summary .summary-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 13px;
            margin-bottom: 6px;
        }

        .pos-cart .cart-summary .summary-row.total {
            font-size: 16px;
            font-weight: 700;
            color: var(--pos-primary);
            margin-bottom: 0;
            padding-top: 6px;
            border-top: 1px dashed var(--pos-border);
        }

        .pos-cart .cart-summary .discount-row {
            color: var(--pos-danger);
        }

        /* Payment panel */
        .pos-payment {
            padding: 12px 14px;
            border-top: 1px solid var(--pos-border);
            background: var(--pos-card);
            flex-shrink: 0;
        }

        .pos-payment .money-input-group {
            margin-bottom: 8px;
        }

        .pos-payment .money-input-group label {
            font-size: 12px;
            font-weight: 500;
            color: var(--pos-text-muted);
            margin-bottom: 4px;
            display: block;
        }

        .pos-payment .money-input-group input {
            width: 100%;
            padding: 8px 10px;
            border: 1px solid var(--pos-border);
            border-radius: 6px;
            font-size: 14px;
            font-weight: 600;
            text-align: right;
            outline: none;
            color: var(--pos-primary);
        }

        .pos-payment .money-input-group input:focus {
            border-color: var(--pos-primary);
        }

        .pos-payment .change-display {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 6px 10px;
            background: #e8f5e9;
            border-radius: 6px;
            margin-bottom: 8px;
            font-size: 12px;
        }

        .pos-payment .change-display .change-label {
            color: var(--pos-text-muted);
        }

        .pos-payment .change-display .change-amount {
            font-weight: 700;
            color: var(--pos-primary);
            font-size: 14px;
        }

        /* Payment methods */
        .pos-payment .payment-methods {
            display: flex;
            gap: 6px;
            margin-bottom: 8px;
        }

        .pos-payment .payment-methods .pay-btn {
            flex: 1;
            padding: 7px 4px;
            border-radius: 6px;
            border: 1px solid var(--pos-border);
            background: #fff;
            font-size: 10px;
            font-weight: 600;
            cursor: pointer;
            text-align: center;
            transition: all 0.2s;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 3px;
            color: var(--pos-text-muted);
        }

        .pos-payment .payment-methods .pay-btn i {
            font-size: 14px;
        }

        .pos-payment .payment-methods .pay-btn:hover {
            border-color: var(--pos-primary);
            color: var(--pos-primary);
        }

        .pos-payment .payment-methods .pay-btn.active {
            background: var(--pos-primary);
            border-color: var(--pos-primary);
            color: #fff;
        }

        /* Checkout button */
        .pos-payment .btn-checkout {
            width: 100%;
            padding: 12px;
            background: var(--pos-primary);
            color: #fff;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            transition: background 0.2s;
        }

        .pos-payment .btn-checkout:hover {
            background: var(--pos-primary-dark);
        }

        .pos-payment .btn-checkout .shortcut-hint {
            background: rgba(255,255,255,0.2);
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 11px;
        }

        /* ── POS Footer ── */
        .pos-footer {
            background: var(--pos-primary-dark);
            color: rgba(255,255,255,0.6);
            padding: 6px 20px;
            font-size: 11px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 16px;
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            z-index: 1000;
        }

        .pos-footer .shortcut-hint {
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .pos-footer .shortcut-hint kbd {
            background: rgba(255,255,255,0.15);
            padding: 1px 5px;
            border-radius: 3px;
            font-family: monospace;
            font-size: 10px;
            color: #fff;
        }

        /* Scrollbar */
        ::-webkit-scrollbar {
            width: 4px;
        }

        ::-webkit-scrollbar-track {
            background: transparent;
        }

        ::-webkit-scrollbar-thumb {
            background: #ccc;
            border-radius: 2px;
        }

        /* Alert toast */
        .pos-toast {
            position: fixed;
            top: 70px;
            right: 20px;
            background: var(--pos-primary);
            color: #fff;
            padding: 10px 16px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 500;
            z-index: 2000;
            display: none;
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
        }

        .pos-toast.show {
            display: flex;
            align-items: center;
            gap: 8px;
            animation: slideIn 0.3s ease;
        }

        @keyframes slideIn {
            from { transform: translateX(100px); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }

        /* Responsive */
        @media (max-width: 900px) {
            .pos-product-grid {
                grid-template-columns: repeat(3, 1fr);
            }
            .pos-cart {
                width: 240px;
            }
        }

        @media (max-width: 700px) {
            .pos-sidebar {
                width: 52px;
            }
            .pos-product-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            .pos-cart {
                width: 220px;
            }
            .pos-header .pos-datetime,
            .pos-header .pos-user .user-info {
                display: none;
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
            <strong>Nhân Viên Test</strong>
            <small>Nhân viên bán hàng</small>
        </div>
        <a href="{{ url('nhan-vien/') }}" class="btn-exit">
            <i class="fas fa-sign-out-alt"></i> Thoát
        </a>
    </div>
</header>

<!-- ── POS Body ── -->
<div class="pos-body">

    <!-- Sidebar -->
    <nav class="pos-sidebar">
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
    <input type="text"
           id="customerSearchInput"
           class="form-control"
           placeholder="Tìm khách hàng theo tên / SĐT..."
           oninput="searchCustomers()">

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
                <input type="text" id="searchInput" placeholder="Tìm sản phẩm..." oninput="filterProducts()">
            </div>
        </div>

        <!-- Product Grid -->
        <div class="pos-product-grid" id="productGrid">
            <!-- Products will be rendered by JS -->
        </div>
    </div>

    <!-- Cart Panel -->
    <div class="pos-cart">
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
                <button class="pay-btn" data-method="transfer" onclick="selectPayment('transfer')">
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
        const gia = Number(p.gia_ban ?? 0);
        const ton = Number(p.so_luong_ton_kho ?? 0);
        const hinh = p.hinh_anh ? p.hinh_anh : 'https://via.placeholder.com/150';

        return `
            <div class="pos-product-card" onclick="addToCart(${p.id})">
                <div class="product-img">
                    <img src="${hinh}" alt="${ten}" style="width:100%;height:100%;object-fit:cover;">
                </div>
                <div class="product-info">
                    <div class="product-name">${ten}</div>
                    <div class="product-price">${formatCurrency(gia)}</div>
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

    const subtotal = cart.reduce((s, i) => s + Number(i.gia_ban) * i.qty, 0);
    discountAmount = tinhTienGiam(subtotal);
const total = Math.max(0, subtotal - discountAmount);

    document.getElementById('subtotal').textContent = formatCurrency(subtotal);
    document.getElementById('discount').textContent = '-' + formatCurrency(discountAmount);
    document.getElementById('totalAmount').textContent = formatCurrency(total);
}

    // Update summary
    


// ─────────────────────────────────────────────
// Update Quantity
// ─────────────────────────────────────────────
function updateQuantity(id, change) {
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
    cart = cart.filter(i => i.id !== id);
    renderCart();
}

// ─────────────────────────────────────────────
// Clear Cart
// ─────────────────────────────────────────────
function clearCart() {
    if (cart.length === 0) return;
    cart = [];
    document.getElementById('customerMoney').value = '';
    document.getElementById('changeAmount').textContent = '0đ';
    renderCart();
    showToast('Đã xóa giỏ hàng');
}

// ─────────────────────────────────────────────
// Calculate Change
// ─────────────────────────────────────────────
function calculateChange() {
    const customer = parseFloat(document.getElementById('customerMoney').value) || 0;

    const subtotal = cart.reduce((s, i) => s + Number(i.gia_ban) * i.qty, 0);
    const discount = tinhTienGiam(subtotal);
    const total = Math.max(0, subtotal - discount);

    const change = customer - total;

    document.getElementById('changeAmount').textContent =
        formatCurrency(Math.max(0, change));
}

// ─────────────────────────────────────────────
// Select Payment Method
// ─────────────────────────────────────────────
function selectPayment(method) {
    selectedPayment = method;
    document.querySelectorAll('.pay-btn').forEach(btn => {
        btn.classList.toggle('active', btn.dataset.method === method);
    });
}

// ─────────────────────────────────────────────
// Process Payment
// ─────────────────────────────────────────────
async function processPayment() {
    if (cart.length === 0) {
        showToast('Giỏ hàng trống!', 'error');
        return;
    }

    const subtotal = cart.reduce((s, i) => s + Number(i.gia_ban) * i.qty, 0);
const discount = tinhTienGiam(subtotal);
const total = Math.max(0, subtotal - discount);

let customer = parseFloat(document.getElementById('customerMoney').value) || 0;

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
                tien_khach_dua: customer,
                id_khuyen_mai: selectedPromotion ? selectedPromotion.id : null,
                tien_giam_gia: discount,
                phuong_thuc_thanh_toan: selectedPayment
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
        window.open('/nhan-vien/hoa-don/' + data.hoa_don_id, '_blank');

        cart = [];
        document.getElementById('customerMoney').value = '';
        document.getElementById('changeAmount').textContent = '0đ';

        renderCart();
        loadProducts();

        showToast('Thanh toán thành công!');
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

    document.getElementById('selectedCustomerId').value = customer.id;
    document.getElementById('customerSearchInput').value = '';
    document.getElementById('customerSearchResult').style.display = 'none';

    document.getElementById('selectedCustomerText').innerHTML =
        `<strong>${customer.ten_khach_hang}</strong> - ${customer.so_dien_thoai ?? ''} - Điểm: ${customer.diem_tich_luy ?? 0}`;

    document.getElementById('selectedCustomerBox').style.display = 'block';
}

function clearSelectedCustomer() {
    selectedCustomer = null;
    document.getElementById('selectedCustomerId').value = '';
    document.getElementById('selectedCustomerBox').style.display = 'none';
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
    if (!selectedPromotion) return 0;

    const type = String(selectedPromotion.loai_giam_gia || '')
        .trim()
        .toLowerCase();

    const minOrder = Number(selectedPromotion.don_hang_toi_thieu || 0);
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
    if (type === 'phan_tram') {
        let discount = subtotal * Number(selectedPromotion.gia_tri_giam || 0) / 100;

        if (selectedPromotion.giam_toi_da !== null && selectedPromotion.giam_toi_da !== '') {
            discount = Math.min(discount, Number(selectedPromotion.giam_toi_da));
        }

        return Math.min(discount, subtotal);
    }

    // Giảm tiền trực tiếp
    const discount = Number(selectedPromotion.gia_tri_giam || 0);
    return Math.min(discount, subtotal);
}
function applyPromotion() {
    const id = document.getElementById('promotionSelect').value;

    selectedPromotion = promotions.find(km => String(km.id) === String(id)) || null;

    renderCart();
    calculateChange();
}

// ─────────────────────────────────────────────
// Init
// ─────────────────────────────────────────────
loadCategories();
loadProducts();
loadPromotions();
</script>
</body>
</html>
