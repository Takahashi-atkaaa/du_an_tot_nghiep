<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đặt lại mật khẩu - SmartMart Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #0d6efd;
        }
        body {
            font-family: 'Roboto', sans-serif;
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-card {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            overflow: hidden;
            width: 100%;
            max-width: 420px;
        }
        .login-header {
            background: var(--primary-color);
            color: #fff;
            padding: 30px;
            text-align: center;
        }
        .login-header h3 {
            margin: 0;
            font-weight: 700;
        }
        .login-header p {
            margin: 5px 0 0;
            opacity: 0.8;
            font-size: 14px;
        }
        .login-body {
            padding: 40px 30px;
        }
        .form-floating {
            margin-bottom: 20px;
        }
        .form-floating .form-control {
            border-radius: 10px;
            border: 1px solid #e0e0e0;
        }
        .form-floating .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(13,110,253,0.15);
        }
        .btn-submit {
            width: 100%;
            padding: 12px;
            border-radius: 10px;
            font-weight: 600;
            background: var(--primary-color);
            border: none;
        }
        .btn-submit:hover {
            background: #0b5ed7;
        }
        .login-footer {
            text-align: center;
            padding: 20px;
            background: #f8f9fa;
            border-top: 1px solid #eee;
        }
        .login-footer a {
            color: var(--primary-color);
            text-decoration: none;
        }
        .password-toggle {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #aaa;
        }
        .input-group {
            position: relative;
        }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="login-header">
            <h3><i class="fas fa-lock me-2"></i>SmartMart</h3>
            <p>Đặt lại mật khẩu mới</p>
        </div>
        <div class="login-body">
            <h5 class="text-center mb-3 fw-bold">Nhập mật khẩu mới</h5>

            @if(session('success'))
                <div class="alert alert-success py-2">
                    <i class="fas fa-check-circle me-2"></i>
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger py-2">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    {{ session('error') }}
                </div>
            @enderror

            <form action="{{ url('admin/dat-lai-mat-khau') }}" method="POST">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">

                <div class="mb-3">
                    <label class="form-label">Mật khẩu mới</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0">
                            <i class="fas fa-lock text-muted"></i>
                        </span>
                        <input type="password" class="form-control border-start-0 border-end-0"
                               name="mat_khau_moi" id="mat_khau_moi" placeholder="Nhập mật khẩu mới" required>
                        <span class="input-group-text bg-white border-start-0 password-toggle"
                              onclick="togglePassword('mat_khau_moi', 'toggleIcon1')">
                            <i class="fas fa-eye" id="toggleIcon1"></i>
                        </span>
                    </div>
                    @error('mat_khau_moi')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">Xác nhận mật khẩu mới</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0">
                            <i class="fas fa-lock text-muted"></i>
                        </span>
                        <input type="password" class="form-control border-start-0 border-end-0"
                               name="mat_khau_moi_confirmation" id="mat_khau_moi_confirmation"
                               placeholder="Nhập lại mật khẩu mới" required>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary btn-submit">
                    <i class="fas fa-check me-2"></i>Đặt lại mật khẩu
                </button>
            </form>
        </div>
        <div class="login-footer">
            <a href="{{ url('/admin/login') }}">
                <i class="fas fa-arrow-left me-2"></i>Quay lại đăng nhập
            </a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function togglePassword(inputId, iconId) {
            const password = document.getElementById(inputId);
            const icon = document.getElementById(iconId);
            if (password.type === 'password') {
                password.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                password.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }
    </script>
</body>
</html>
