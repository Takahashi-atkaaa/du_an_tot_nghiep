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
        .sidebar{
            position: fixed;
            top:0;
            left:0;
            width:260px;
            height:100vh;
            background:var(--sidebar-bg);
            z-index:1050;
            transition:all .3s ease;
        }
.topbar{
    background:#fff;
    padding:15px 25px;
    box-shadow:0 2px 10px rgba(0,0,0,.1);
    display:flex;
    justify-content:space-between;
    align-items:center;
}
.logo{
    padding:20px;
    text-align:center;
    border-bottom:1px solid rgba(255,255,255,.1);
}

.logo h4{
    color:#fff;
    margin:0;
}

.logo span{
    color:#81c784;
}
.sidebar .nav-item{
    margin:6px 10px;
}

.sidebar .nav-link{
    color:#c8e6c9;
    padding:12px 15px;
    display:flex;
    align-items:center;
    gap:10px;
    border-radius:8px;
    transition:.3s;
    text-decoration:none;
}

.sidebar .nav-link:hover{
    background:#2e7d32;
    color:#fff;
}

.sidebar .nav-link.active{
    background:#4caf50;
    color:#fff;
}
.user-info{
    display:flex;
    align-items:center;
}

.user-avatar{
    width:40px;
    height:40px;
    border-radius:50%;
    background:#2e7d32;
    color:white;
    display:flex;
    justify-content:center;
    align-items:center;
    font-weight:bold;
}

        .main-content{
            margin-left:260px;
            min-height:100vh;
            transition:.3s;
        }

        .overlay{
            position:fixed;
            inset:0;
            background:rgba(0,0,0,.4);
            z-index:1049;
            display:none;
        }

        .overlay.show{
            display:block;
        }
        @media(max-width:768px){

            .sidebar{
                transform:translateX(-100%);
            }

            .sidebar.show{
                transform:translateX(0);
            }

            .main-content{
                margin-left:0;
            }

            .topbar{
                padding:12px 15px;
            }

            .content-wrapper{
                padding:15px;
            }
        }
    </style>
    @yield('styles')
</head>
<body>
    <div class="overlay" id="overlay"></div>
    <!-- Sidebar -->
    <nav class="sidebar" id="sidebar">
<div class="logo d-flex justify-content-between align-items-center px-3">

    <div>
    <h4>Smart<span>Mart</span></h4>
    <small class="text-light opacity-75">
    Nhân viên
    </small>
    </div>

    <button
    id="closeSidebar"
    class="btn btn-sm btn-light d-md-none">

    <i class="fas fa-times"></i>

    </button>

    </div>
        <ul class="nav flex-column mt-3">
            <li class="nav-item">
                <a href="{{ url('trang-chu/') }}" class="nav-link {{ request()->is('nhan-vien') || request()->is('nhan-vien/') ? 'active' : '' }}">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Trang chủ</span>
                </a>
            </li>
            @if(auth()->user()->id_vai_tro == 4)
                <li class="nav-item">
                    <a href="{{ url('/ban-hang') }}" class="nav-link {{ request()->is('/ban-hang') ? 'active' : '' }}">
                        <i class="fas fa-cash-register"></i>
                        <span>Bán hàng</span>
                    </a>
                </li>
            @endif

            <li class="nav-item">
                <a href="{{ url('/lich-lam-viec/tuan') }}" class="nav-link {{ request()->is('lich-lam-viec*') ? 'active' : '' }}">
                    <i class="fas fa-calendar-alt"></i>
                    <span>Lịch làm việc</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ url('ho-so') }}" class="nav-link {{ request()->is('nhan-vien/ho-so') ? 'active' : '' }}">
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

                <button
                    id="sidebar-toggle"
                    class="btn btn-success me-3 d-md-none">

                    <i class="fas fa-bars"></i>

                </button>

                <h5 class="mb-0">
                    SmartMart
                </h5>

            </div>

            <div class="user-info">

                <div class="dropdown">

                    <a href="#" class="dropdown-toggle" data-bs-toggle="dropdown">

                        <div class="user-avatar">
                            NV
                        </div>

                    </a>

                    <ul class="dropdown-menu dropdown-menu-end">

                        <li>
                            <a class="dropdown-item" href="{{ url('nhan-vien/ho-so') }}">
                                <i class="fas fa-user me-2"></i>
                                Hồ sơ
                            </a>
                        </li>

                        <li><hr class="dropdown-divider"></li>

                        <li>
                            <a class="dropdown-item" href="{{ url('admin/logout') }}">
                                <i class="fas fa-sign-out-alt me-2"></i>
                                Đăng xuất
                            </a>
                        </li>

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



    <script>
        const sidebar = document.getElementById("sidebar");

        const overlay = document.getElementById("overlay");

        const toggle = document.getElementById("sidebar-toggle");

        const closeSidebar = document.getElementById("closeSidebar");

        toggle?.addEventListener("click",()=>{

            sidebar.classList.add("show");

            overlay.classList.add("show");

        });

        closeSidebar?.addEventListener("click",()=>{

            sidebar.classList.remove("show");

            overlay.classList.remove("show");

        });

        overlay.addEventListener("click",()=>{

            sidebar.classList.remove("show");

            overlay.classList.remove("show");

        });
    </script>
</body>
</html>
