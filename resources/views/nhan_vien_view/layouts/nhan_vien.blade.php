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
        }
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f5f6fa;
        }
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: 260px;
            background: var(--sidebar-bg);
            padding-top: 20px;
            z-index: 1000;
            transition: all 0.3s ease;
        }
        .sidebar .logo {
            text-align: center;
            padding: 15px 20px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        .sidebar .logo h4 {
            color: #fff;
            font-weight: 700;
            margin: 0;
        }
        .sidebar .logo span {
            color: #81c784;
        }
        .sidebar .nav-item {
            margin: 5px 10px;
        }
        .sidebar .nav-link {
            color: #c8e6c9;
            padding: 12px 15px;
            border-radius: 8px;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
        }
        .sidebar .nav-link:hover {
            background: var(--sidebar-hover);
            color: #fff;
        }
        .sidebar .nav-link.active {
            background: var(--primary-color);
            color: #fff;
        }
        .sidebar .nav-link i {
            width: 20px;
            text-align: center;
        }
        .main-content {
            margin-left: 260px;
            min-height: 100vh;
        }
        .topbar {
            background: #fff;
            padding: 15px 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
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
            background: var(--primary-color);
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
            box-shadow: 0 5px 20px rgba(0,0,0,0.05);
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
        .card-stat.green .icon { background: rgba(46,125,50,0.1); color: #2e7d32; }
        .card-stat.blue .icon { background: rgba(25,118,210,0.1); color: #1976d2; }
        .card-stat.orange .icon { background: rgba(230,81,0,0.1); color: #e65100; }
        .card-stat.purple .icon { background: rgba(106,27,154,0.1); color: #7b1fa2; }
        .table-nv {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.05);
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
        .status-success { background: #c8e6c9; color: #1b5e20; }
        .status-warning { background: #fff3e0; color: #e65100; }
        .status-info { background: #e3f2fd; color: #1565c0; }
        @media (max-width: 768px) {
            .sidebar {
                margin-left: -260px;
            }
            .main-content {
                margin-left: 0;
            }
            .sidebar.active {
                margin-left: 0;
            }
        }
    </style>
    @yield('styles')
</head>
<body>
    <!-- Sidebar -->
    <nav class="sidebar" id="sidebar">
        <div class="logo">
            <h4>Smart<span>Mart</span></h4>
            <small class="text-light opacity-75">Nhân viên</small>
        </div>
        <ul class="nav flex-column mt-3">
            <li class="nav-item">
                <a href="{{ url('nhan-vien/') }}" class="nav-link {{ request()->is('nhan-vien') || request()->is('nhan-vien/') ? 'active' : '' }}">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ url('nhan-vien/ban-hang') }}" class="nav-link {{ request()->is('nhan-vien/ban-hang') ? 'active' : '' }}">
                    <i class="fas fa-cash-register"></i>
                    <span>Bán hàng</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ url('nhan-vien/hoa-don') }}" class="nav-link {{ request()->is('nhan-vien/hoa-don*') ? 'active' : '' }}">
                    <i class="fas fa-file-invoice"></i>
                    <span>Hóa đơn</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ url('nhan-vien/san-pham') }}" class="nav-link {{ request()->is('nhan-vien/san-pham*') ? 'active' : '' }}">
                    <i class="fas fa-box"></i>
                    <span>Sản phẩm</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ url('nhan-vien/khach-hang') }}" class="nav-link {{ request()->is('nhan-vien/khach-hang*') ? 'active' : '' }}">
                    <i class="fas fa-users"></i>
                    <span>Khách hàng</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ url('nhan-vien/lich-lam-viec/tuan') }}" class="nav-link {{ request()->is('nhan-vien/lich-lam-viec*') ? 'active' : '' }}">
                    <i class="fas fa-calendar-alt"></i>
                    <span>Lịch làm việc</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ url('nhan-vien/cham-cong') }}" class="nav-link {{ request()->is('nhan-vien/cham-cong') ? 'active' : '' }}">
                    <i class="fas fa-clock"></i>
                    <span>Chấm công</span>
                </a>
            </li>
            <li class="nav-item mt-3">
                <a href="{{ url('nhan-vien/ho-so') }}" class="nav-link {{ request()->is('nhan-vien/ho-so') ? 'active' : '' }}">
                    <i class="fas fa-user"></i>
                    <span>Hồ sơ cá nhân</span>
                </a>
            </li>
        </ul>
    </nav>

    <!-- Main Content -->
    <main class="main-content">
        <!-- Topbar -->
        <header class="topbar">
            <div class="d-flex align-items-center">
                <h5 class="mb-0 text-secondary"> giao diện nhân viên</h5>
            </div>
            <div class="user-info">
                <div class="dropdown">
                    <a href="#" class="dropdown-toggle" data-bs-toggle="dropdown">
                        <div class="user-avatar">NV</div>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="{{ url('nhan-vien/ho-so') }}"><i class="fas fa-user me-2"></i>Hồ sơ</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="{{ url('admin/logout') }}"><i class="fas fa-sign-out-alt me-2"></i>Đăng xuất</a></li>
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
        document.getElementById('sidebar-toggle')?.addEventListener('click', function() {
            document.getElementById('sidebar').classList.toggle('active');
        });
    </script>
    @yield('scripts')
</body>
</html>
