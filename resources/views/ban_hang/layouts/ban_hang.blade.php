<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'SmartMart - Nhân viên')</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #2e7d32;
            --sidebar-bg: #1b5e20;
            --sidebar-hover: #2e7d32;
            --sidebar-active: #4caf50;
            --pos-primary: #2563eb;
            --pos-primary-dark: #1d4ed8;
        }

        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f5f6fa;
        }

        /* ===== POS SIDEBAR (always visible on desktop, toggle on mobile) ===== */
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
            display: flex;
            flex-direction: column;
            box-shadow: 0 0 24px rgba(0,0,0,0.18);
            transition: transform 0.3s ease;
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
            display: none;
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
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
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
            text-decoration: none;
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

        .main-content {
            margin-left: 260px;
            min-height: 100vh;
        }

        .topbar {
            background: #fff;
            padding: 15px 25px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .topbar .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .topbar .user-info .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--pos-primary);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
        }

        .topbar .user-info .dropdown-toggle::after {
            display: none;
        }

        .content-wrapper {
            padding: 25px;
        }

        .card-stat {
            border: none;
            border-radius: 12px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
            transition: transform 0.3s ease;
        }

        .card-stat:hover {
            transform: translateY(-5px);
        }

        .card-stat .icon {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
        }

        .card-stat.green .icon {
            background: rgba(46, 125, 50, 0.1);
            color: #2e7d32;
        }

        .card-stat.blue .icon {
            background: rgba(25, 118, 210, 0.1);
            color: #1976d2;
        }

        .card-stat.orange .icon {
            background: rgba(230, 81, 0, 0.1);
            color: #e65100;
        }

        .card-stat.purple .icon {
            background: rgba(106, 27, 154, 0.1);
            color: #7b1fa2;
        }

        .table-nv {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
        }

        .table-nv thead {
            background: #e8f5e9;
        }

        .table-nv th {
            font-weight: 600;
            text-transform: uppercase;
            font-size: 12px;
            color: #2e7d32;
            border: none;
            padding: 15px;
        }

        .table-nv td {
            border-color: #f0f0f0;
            padding: 15px;
            vertical-align: middle;
        }

        .status-badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
        }

        .status-success {
            background: #c8e6c9;
            color: #1b5e20;
        }

        .status-warning {
            background: #fff3e0;
            color: #e65100;
        }

        .status-info {
            background: #e3f2fd;
            color: #1565c0;
        }

        @media (max-width: 768px) {
            .pos-sidebar {
                transform: translateX(-100%);
            }
            .pos-sidebar.open {
                transform: translateX(0);
            }
            .pos-sidebar .sidebar-logo .close-sidebar {
                display: flex;
            }
            .main-content {
                margin-left: 0;
            }
        }
    </style>
    @yield('styles')
</head>

@auth
@php
    $sidebarUser = auth()->user();
    $sidebarHoTen = $sidebarUser->ho_ten ?? 'Nhân viên';
    $sidebarEmail = $sidebarUser->email ?? '';
    $sidebarAvatar = mb_substr($sidebarHoTen, 0, 1);
    $sidebarVaiTro = $sidebarUser->id_vai_tro ?? null;
    $isQuanLy = in_array($sidebarVaiTro, [1, 2]);
    $currentPath = request()->path();
@endphp
<body>
    <!-- Sidebar -->
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
            @if ($isQuanLy)
                <div class="nav-item">
                    <a href="{{ url('/admin/dashboard') }}" class="nav-link">
                        <i class="fa-solid fa-gauge-high"></i>
                        <span>Trang quản trị</span>
                    </a>
                </div>
            @endif

            <div class="sidebar-divider"></div>

            <div class="nav-item">
                <a href="{{ url('/ban-hang') }}"
                    class="nav-link {{ $currentPath === 'ban-hang' ? 'active' : '' }}">
                    <i class="fa-solid fa-cash-register"></i>
                    <span>Bán hàng</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="{{ url('/admin/hoa-don') }}"
                    class="nav-link {{ str_starts_with($currentPath, 'admin/hoa-don') ? 'active' : '' }}">
                    <i class="fa-solid fa-file-invoice"></i>
                    <span>Hóa đơn</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="{{ url('/admin/san-pham') }}"
                    class="nav-link {{ str_starts_with($currentPath, 'admin/san-pham') ? 'active' : '' }}">
                    <i class="fa-solid fa-box"></i>
                    <span>Sản phẩm</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="{{ url('/khach-hang') }}"
                    class="nav-link {{ str_starts_with($currentPath, 'khach-hang') ? 'active' : '' }}">
                    <i class="fa-solid fa-users"></i>
                    <span>Khách hàng</span>
                </a>
            </div>

            <div class="sidebar-divider"></div>

            @if ($sidebarVaiTro != 1 && $sidebarVaiTro != 2 && $sidebarVaiTro != 4)
                <div class="nav-item">
                    <a href="{{ url('/lich-lam-viec') }}"
                        class="nav-link {{ str_starts_with($currentPath, 'lich-lam-viec') ? 'active' : '' }}">
                        <i class="fa-solid fa-calendar-days"></i>
                        <span>Lịch làm việc</span>
                    </a>
                </div>
            @endif
        </nav>

        <div class="sidebar-footer">
            <div class="avatar">{{ $sidebarAvatar }}</div>
            <div class="info">
                <div class="name">{{ $sidebarHoTen }}</div>
                <div class="role">{{ $sidebarEmail }}</div>
            </div>
            <a class="logout-btn" href="{{ url('admin/logout') }}" title="Đăng xuất">
                <i class="fa-solid fa-right-from-bracket"></i>
            </a>
        </div>
    </aside>

    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <!-- Main Content -->
    <main class="main-content">
        <!-- Topbar -->
        <header class="topbar">
            <div class="d-flex align-items-center gap-2">
                <button class="btn btn-sm btn-outline-secondary d-md-none" id="btnOpenSidebar" type="button" aria-label="Mở menu">
                    <i class="fa-solid fa-bars"></i>
                </button>
                <h5 class="mb-0 text-secondary"> Giao diện bán hàng </h5>
            </div>
            <div class="user-info">
                <div class="dropdown">
                    <a href="#" class="dropdown-toggle" data-bs-toggle="dropdown">
                        <div class="user-avatar">{{ $sidebarAvatar }}</div>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="{{ url('nhan-vien/ho-so') }}"><i
                                    class="fas fa-user me-2"></i>Hồ sơ</a></li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li><a class="dropdown-item" href="{{ url('admin/logout') }}"><i
                                    class="fas fa-sign-out-alt me-2"></i>Đăng xuất</a></li>
                    </ul>
                </div>
            </div>
        </header>

        <!-- Content -->
        <div class="content-wrapper">
            @yield('content')
        </div>
    </main>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Font Awesome JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/js/all.min.js"></script>

    <script>
        // Sidebar toggle for mobile
        document.getElementById('btnOpenSidebar')?.addEventListener('click', function () {
            document.getElementById('posSidebar').classList.add('open');
            document.getElementById('sidebarOverlay').classList.add('show');
        });
        document.getElementById('btnCloseSidebar')?.addEventListener('click', function () {
            document.getElementById('posSidebar').classList.remove('open');
            document.getElementById('sidebarOverlay').classList.remove('show');
        });
        document.getElementById('sidebarOverlay')?.addEventListener('click', function () {
            document.getElementById('posSidebar').classList.remove('open');
            document.getElementById('sidebarOverlay').classList.remove('show');
        });
    </script>
    @yield('scripts')
</body>
@else
<body>
    <div class="container py-5">
        <p class="text-center text-muted">Vui lòng đăng nhập để tiếp tục.</p>
    </div>
</body>
@endauth

</html>
