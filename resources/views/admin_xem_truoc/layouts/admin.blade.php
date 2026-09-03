<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'SmartMart - Admin')</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@300;400;500;600;700&family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet"
    href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
    
        :root {
            --primary-color: #0d6efd;
            --sidebar-bg: #1a1a2e;
            --sidebar-hover: #16213e;
            --sidebar-active: #0f3460;
        }
        body {
            font-family: 'Nunito', 'Roboto', -apple-system, BlinkMacSystemFont, sans-serif;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
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
            overflow-y: auto;
            overflow-x: hidden;
            scrollbar-width: thin;
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
            color: #00d2d3;
        }
        .sidebar .nav-item {
            margin: 5px 10px;
        }
        .sidebar .nav-link {
            color: #b8b9be;
            padding: 12px 15px;
            border-radius: 8px;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 10px;
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
        .topbar .search-box {
            position: relative;
        }
        .topbar .search-box input {
            padding-left: 40px;
            border-radius: 25px;
            border: 1px solid #e0e0e0;
            width: 300px;
        }
        .topbar .search-box i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #aaa;
        }
        .topbar .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        .topbar .user-info .notify {
            position: relative;
            cursor: pointer;
        }
        .topbar .user-info .notify .badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background: #ff4757;
            font-size: 10px;
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
        .card-stat.blue .icon { background: rgba(13,110,253,0.1); color: #0d6efd; }
        .card-stat.green .icon { background: rgba(25,135,84,0.1); color: #198754; }
        .card-stat.orange .icon { background: rgba(253,126,20,0.1); color: #fd7e14; }
        .card-stat.red .icon { background: rgba(220,53,69,0.1); color: #dc3545; }
        .breadcrumb {
            background: transparent;
            padding: 0;
            margin-bottom: 20px;
        }
        .breadcrumb-item a {
            color: #6c757d;
            text-decoration: none;
        }
        .table-admin {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.05);
        }
        .table-admin thead {
            background: #f8f9fa;
        }
        .table-admin th {
            font-weight: 600;
            text-transform: uppercase;
            font-size: 12px;
            color: #6c757d;
            border: none;
            padding: 15px;
        }
        .table-admin td {
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
        .status-active { background: #d1e7dd; color: #0f5132; }
        .status-inactive { background: #f8d7da; color: #842029; }
        .status-pending { background: #fff3cd; color: #664d03; }
        .btn-action {
            width: 32px;
            height: 32px;
            border-radius: 6px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin: 0 3px;
        }
        .pagination {
            justify-content: end;
        }
        .page-link {
            border-radius: 8px;
            margin: 0 3px;
        }
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
        .dropdown-toggle-custom .arrow{
            transition:.3s;
        }
        .dropdown-toggle-custom.collapsed .arrow{
            transform:rotate(0);
        }
        .dropdown-toggle-custom:not(.collapsed) .arrow{
            transform:rotate(-90deg);
        }
    </style>
    @stack('styles')
    @yield('styles')
</head>
<body>
    <!-- Sidebar -->
    <nav class="sidebar" id="sidebar">
        <div class="logo">
            <h4>Smart<span>Mart</span></h4>
        </div>
        <ul class="nav flex-column mt-3">
            {{-- 1. Thống kê - Chỉ hiển thị nếu có quyền xem_dashboard --}}
            @if(userHasPermissionGroup('dashboard'))
            <li class="nav-item">
                <a href="{{ url('admin/dashboard') }}" class="nav-link {{ request()->is('admin/dashboard') ? 'active' : '' }}">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Thống kê</span>
                </a>
            </li>
            @endif

            {{-- 2. Bán hàng - Chỉ hiển thị nếu có quyền ban_hang --}}
            @if(userHasPermissionGroup('ban_hang'))
            <li class="nav-item">
                <a href="{{ url('/ban-hang') }}" class="nav-link">
                    <i class="fas fa-cash-register"></i>
                    <span>Bán hàng</span>
                </a>
            </li>
            @endif

            {{-- 3. Hóa đơn - Chỉ hiển thị nếu có quyền quan_ly_hoa_don --}}
            @if(userHasPermissionGroup('hoa_don'))
            <li class="nav-item">
                <a href="{{ url('admin/hoa-don') }}" class="nav-link {{ request()->is('admin/hoa-don*') ? 'active' : '' }}">
                    <i class="fas fa-file-invoice"></i>
                    <span>Hóa đơn</span>
                </a>
            </li>
            @endif

            {{-- 4. Sản phẩm - Hiển thị nếu có bất kỳ quyền nào về sản phẩm --}}
            @if(userHasPermissionGroup('san_pham'))
            <li class="nav-item">
                <a href="{{ url('admin/san-pham') }}" class="nav-link {{ request()->is('admin/san-pham*') ? 'active' : '' }}">
                    <i class="fas fa-box"></i>
                    <span>Sản phẩm</span>
                </a>
            </li>
            @endif

            {{-- 5. Danh mục - Chỉ hiển thị nếu có quyền quan_ly_danh_muc --}}
            @if(userHasPermissionGroup('danh_muc'))
            <li class="nav-item">
                <a href="{{ route('danh_muc.index') }}" class="nav-link {{ request()->is('admin/danh-muc*') ? 'active' : '' }}">
                    <i class="fas fa-tags"></i>
                    <span>Danh mục</span>
                </a>
            </li>
            @endif

            {{-- 6. Kho hàng - Hiển thị nếu có quyền quan_ly_kho_hang hoặc quyền kiểm kho --}}
            @if(userHasPermissionGroup('kho_hang'))
            <li class="nav-item">
                <a href="{{ url('admin/kho-hang') }}" class="nav-link {{ request()->is('admin/kho-hang*') ? 'active' : '' }}">
                    <i class="fas fa-warehouse"></i>
                    <span>Kho hàng</span>
                </a>
            </li>
            @endif

            {{-- 7. Hàng lỗi - Chỉ hiển thị nếu có quyền quan_ly_hang_loi --}}
            @if(userHasPermissionGroup('hang_loi'))
            <li class="nav-item">
                <a href="{{ route('admin.hang-loi.index') }}" class="nav-link {{ request()->is('admin/hang-loi*') ? 'active' : '' }}">
                    <i class="fas fa-triangle-exclamation"></i>
                    <span>Hàng lỗi</span>
                </a>
            </li>
            @endif

            {{-- 8. Khách hàng - Chỉ hiển thị nếu có quyền quan_ly_khach_hang --}}
            @if(userHasPermissionGroup('khach_hang'))
            <li class="nav-item">
                <a href="{{ url('admin/khach-hang') }}" class="nav-link {{ request()->is('admin/khach-hang*') ? 'active' : '' }}">
                    <i class="fas fa-users"></i>
                    <span>Khách hàng</span>
                </a>
            </li>
            @endif

            {{-- 9. Khuyến mãi - Chỉ hiển thị nếu có quyền quan_ly_khuyen_mai --}}
            @if(userHasPermissionGroup('khuyen_mai'))
            <li class="nav-item">
                <a href="{{ url('admin/khuyen-mai') }}" class="nav-link {{ request()->is('admin/khuyen-mai*') ? 'active' : '' }}">
                    <i class="fas fa-gift"></i>
                    <span>Khuyến mãi</span>
                </a>
            </li>
            @endif

            {{-- Nhân sự - CHỈ Admin và người có quyền quan_ly_nhan_su --}}
            @if(userHasPermissionGroup('nhan_su'))
            <li class="nav-item">
                <a href="{{ url('nguoi-dung') }}" class="nav-link {{ request()->is('nguoi-dung') || request()->is('nguoi-dung/*') ? 'active' : '' }}">
                    <i class="fas fa-user-tie"></i>
                    <span>Nhân sự</span>
                </a>
            </li>
            @endif

            {{-- 10. Quản lý ca làm - Chỉ hiển thị nếu có quyền quan_ly_ca_lam --}}
            @if(userHasPermissionGroup('ca_lam'))
            <li class="nav-item">
                <a class="nav-link collapsed dropdown-toggle-custom"
                data-bs-toggle="collapse"
                href="#caLamMenu"
                role="button"
                aria-expanded="false"
                aria-controls="caLamMenu">
                    <i class="fas fa-business-time"></i>
                    <span>Quản lý ca làm</span>
                    <i class="fas fa-angle-left arrow"></i>
                </a>

                <div class="collapse" id="caLamMenu">
                    <ul class="nav flex-column ms-3">
                        <li class="nav-item">
                            <a href="{{ route('ca-lam.index') }}"
                            class="nav-link {{ request()->is('admin/ca-lam-viec*') ? 'active' : '' }}">
                                Ca làm hiên tại
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="{{ route('ca-lam-viec.index') }}"
                            class="nav-link {{ request()->is('admin/lich-lam-viec*') ? 'active' : '' }}">
                                Lịch làm việc
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="{{ route('lich-su-ca-lam-viec.index') }}"
                            class="nav-link {{ request()->is('admin/lich-su-ca-lam-viec*') ? 'active' : '' }}">
                                Lịch sử ca làm
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="{{ route('chia-ca-lam-viec.index') }}"
                            class="nav-link {{ request()->is('admin/chia-ca-lam-viec*') ? 'active' : '' }}">
                                Chia ca làm việc
                            </a>
                        </li>
                    </ul>
                </div>
            </li>
            @endif

            {{-- Phân quyền - CHỈ Admin và người có quyền phan_quyen --}}
            @if(userHasPermissionGroup('phan_quyen'))
            <li class="nav-item">
                <a class="nav-link collapsed dropdown-toggle-custom"
                data-bs-toggle="collapse"
                href="#phanQuyenMenu"
                role="button"
                aria-expanded="false"
                aria-controls="phanQuyenMenu">
                    <i class="fas fa-user-shield"></i>
                    <span>Phân quyền</span>
                    <i class="fas fa-angle-left arrow"></i>
                </a>

                <div class="collapse" id="phanQuyenMenu">
                    <ul class="nav flex-column ms-3">
                        <li class="nav-item">
                            <a href="{{ route('nguoi-dung.phan-quyen', ['id_vai_tro' => 2]) }}"
                            class="nav-link {{ request()->is('nguoi-dung/phan-quyen/2') ? 'active' : '' }}">
                                Trưởng ca
                            </a>
                        </li>

                        {{-- <li class="nav-item">
                            <a href="{{ route('nguoi-dung.phan-quyen', ['id_vai_tro' => 4]) }}"
                            class="nav-link {{ request()->is('nguoi-dung/phan-quyen/4') ? 'active' : '' }}">
                                Bán hàng
                            </a>
                        </li> --}}
                    </ul>
                </div>
            </li>
            @endif

            {{-- Cài đặt - quyền riêng, mặc định chỉ Admin được cấp --}}
            @if(userHasPermissionGroup('cai_dat'))
            <li class="nav-item mt-3">
                <a href="{{ url('admin/cai-dat') }}" class="nav-link {{ request()->is('admin/cai-dat*') ? 'active' : '' }}">
                    <i class="fas fa-cog"></i>
                    <span>Cài đặt</span>
                </a>
            </li>
            @endif
        </ul>
    </nav>

    <!-- Main Content -->
    <main class="main-content">
        <!-- Topbar -->
        <header class="topbar">
            <div class="d-flex align-items-center">
                <button class="btn d-lg-none me-3" id="sidebar-toggle">
                    <i class="fas fa-bars"></i>
                </button>
               
            </div>
            <div class="user-info">
                <div class="notify" style="cursor:pointer" onclick="document.getElementById('tab-canh-bao').click();">
                    <i class="fas fa-bell fs-5 text-secondary"></i>
                    <span class="badge" id="topbar-notify-badge" style="display:none">0</span>
                </div>
                <div class="dropdown">
                    <a href="#" class="dropdown-toggle" data-bs-toggle="dropdown">
                        <div class="user-avatar">AD</div>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="#"><i class="fas fa-user me-2"></i>Hồ sơ</a></li>
                        <li><a class="dropdown-item" href="{{ url('admin/doi-mat-khau') }}"><i class="fas fa-key me-2"></i>Đổi mật khẩu</a></li>
                        <li><a class="dropdown-item" href="#"><i class="fas fa-cog me-2"></i>Cài đặt</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="{{ url('admin/logout') }}"><i class="fas fa-sign-out-alt me-2"></i>Đăng xuất</a></li>
                    </ul>
                </div>
            </div>
        </header>

        <!-- Content -->
        <div class="content-wrapper">
            {{-- Flash messages (success / error / validation) --}}
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert" id="flash-message">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert" id="flash-message">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            @if(session('warning'))
                <div class="alert alert-warning alert-dismissible fade show" role="alert" id="flash-message-warning">
                    {{ session('warning') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert" id="flash-message">
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            @yield('content')
        </div>
    </main>

    <!-- jQuery (required by kho-hang page) -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Font Awesome JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/js/all.min.js"></script>
    <!-- SheetJS for Excel import preview -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
    <!-- Axios (HTTP client for admin pages) -->
    <script src="https://cdn.jsdelivr.net/npm/axios@1.6.7/dist/axios.min.js"></script>
    <!-- Toastr (notifications) -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/2.1.4/toastr.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/2.1.4/toastr.min.js"></script>
    <!-- SweetAlert2 (confirmation dialogs) -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.1/dist/sweetalert2.all.min.js"></script>
    <!-- Money Input Utility (format tiền tệ VNĐ toàn site) -->
    <script src="{{ asset('js/admin/money-input.js') }}?v={{ filemtime(public_path('js/admin/money-input.js')) }}"></script>

    <script>
        // Cấu hình toastr mặc định
        if (typeof toastr !== 'undefined') {
            toastr.options = {
                closeButton: true,
                progressBar: true,
                positionClass: 'toast-top-right',
                timeOut: 3500,
                extendedTimeOut: 1500,
            };
        }
    </script>
    <script>
        // Sidebar toggle for mobile
        document.getElementById('sidebar-toggle')?.addEventListener('click', function() {
            document.getElementById('sidebar').classList.toggle('active');
        });
        // Auto-dismiss flash messages after 4s
        setTimeout(function(){
            var flash = document.getElementById('flash-message');
            if(flash){
                var bsAlert = new bootstrap.Alert(flash);
                bsAlert.close();
            }
            var flashWarning = document.getElementById('flash-message-warning');
            if(flashWarning){
                var bsWarningAlert = new bootstrap.Alert(flashWarning);
                bsWarningAlert.close();
            }
        }, 4000);
    </script>
    @yield('modals')
    @yield('scripts')
    @yield('page_scripts')
</body>
</html>
