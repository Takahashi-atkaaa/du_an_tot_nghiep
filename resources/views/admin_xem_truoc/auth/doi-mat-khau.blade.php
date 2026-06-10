@extends('admin_xem_truoc.layouts.admin')

@section('title', 'Đổi mật khẩu - SmartMart')

@push('styles')
<style>
    .change-password-card {
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.05);
        overflow: hidden;
    }
    .change-password-header {
        background: linear-gradient(135deg, #0d6efd 0%, #0f3460 100%);
        color: #fff;
        padding: 25px 30px;
        border-radius: 12px 12px 0 0;
    }
    .change-password-header h4 {
        margin: 0;
        font-weight: 700;
    }
    .change-password-header p {
        margin: 5px 0 0;
        opacity: 0.8;
        font-size: 14px;
    }
    .change-password-body {
        padding: 30px;
    }
    .form-floating {
        margin-bottom: 20px;
    }
    .form-floating .form-control {
        border-radius: 10px;
        border: 1px solid #e0e0e0;
    }
    .form-floating .form-control:focus {
        border-color: #0d6efd;
        box-shadow: 0 0 0 0.2rem rgba(13,110,253,0.15);
    }
    .input-group-text {
        border-radius: 10px 0 0 10px;
        border: 1px solid #e0e0e0;
        border-right: none;
        background: #f8f9fa;
    }
    .form-control {
        border-radius: 0 10px 10px 0;
        border-left: none;
    }
    .form-control:focus {
        border-color: #e0e0e0;
        box-shadow: none;
    }
    .password-toggle {
        cursor: pointer;
        color: #aaa;
        transition: color 0.3s;
        background: #fff;
        border: 1px solid #e0e0e0;
        border-left: none;
        padding-right: 15px;
    }
    .password-toggle:hover {
        color: #0d6efd;
    }
    .btn-submit {
        padding: 12px 40px;
        border-radius: 10px;
        font-weight: 600;
        background: #0d6efd;
        border: none;
        transition: all 0.3s;
    }
    .btn-submit:hover {
        background: #0b5ed7;
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(13,110,253,0.3);
    }
    .btn-cancel {
        padding: 12px 30px;
        border-radius: 10px;
        font-weight: 600;
        border: 2px solid #e0e0e0;
        color: #6c757d;
        background: #fff;
        transition: all 0.3s;
    }
    .btn-cancel:hover {
        border-color: #0d6efd;
        color: #0d6efd;
    }
    .strength-bar {
        height: 4px;
        border-radius: 2px;
        margin-top: -5px;
        margin-bottom: 15px;
        background: #e9ecef;
        overflow: hidden;
    }
    .strength-bar .progress-bar {
        height: 100%;
        transition: width 0.3s, background 0.3s;
    }
</style>
@endpush

@section('content')
<div class="change-password-card">
    <div class="change-password-header">
        <h4><i class="fas fa-key me-2"></i>Đổi mật khẩu</h4>
        <p>Cập nhật mật khẩu để bảo vệ tài khoản của bạn</p>
    </div>
    <div class="change-password-body">
        @if(session('success'))
            <div class="alert alert-success py-2 mb-4">
                <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            </div>
        @endif

        <form action="{{ route('admin.doi-mat-khau.submit') }}" method="POST" id="changePasswordForm">
            @csrf

            {{-- Mat khau cu --}}
            <div class="mb-4">
                <label class="form-label fw-semibold">Mật khẩu cũ</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                    <input type="password"
                           class="form-control @error('mat_khau_cu') is-invalid @enderror"
                           name="mat_khau_cu"
                           id="mat_khau_cu"
                           placeholder="Nhập mật khẩu cũ"
                           required>
                    <span class="input-group-text password-toggle" onclick="togglePassword('mat_khau_cu', 'icon_cu')">
                        <i class="fas fa-eye" id="icon_cu"></i>
                    </span>
                </div>
                @error('mat_khau_cu')
                    <div class="text-danger mt-1" style="font-size: 13px;">
                        <i class="fas fa-exclamation-circle me-1"></i>{{ $message }}
                    </div>
                @enderror
            </div>

            {{-- Mat khau moi --}}
            <div class="mb-4">
                <label class="form-label fw-semibold">Mật khẩu mới</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-key"></i></span>
                    <input type="password"
                           class="form-control @error('mat_khau_moi') is-invalid @enderror"
                           name="mat_khau_moi"
                           id="mat_khau_moi"
                           placeholder="Nhập mật khẩu mới (ít nhất 6 ký tự)"
                           required
                           oninput="checkStrength()">
                    <span class="input-group-text password-toggle" onclick="togglePassword('mat_khau_moi', 'icon_moi')">
                        <i class="fas fa-eye" id="icon_moi"></i>
                    </span>
                </div>
                <div class="strength-bar">
                    <div class="progress-bar" id="strengthBar" style="width: 0%; background: #e9ecef;"></div>
                </div>
                @error('mat_khau_moi')
                    <div class="text-danger mt-1" style="font-size: 13px;">
                        <i class="fas fa-exclamation-circle me-1"></i>{{ $message }}
                    </div>
                @enderror
            </div>

            {{-- Xac nhan mat khau moi --}}
            <div class="mb-4">
                <label class="form-label fw-semibold">Xác nhận mật khẩu mới</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-key"></i></span>
                    <input type="password"
                           class="form-control @error('xac_nhan_mat_khau_moi') is-invalid @enderror"
                           name="xac_nhan_mat_khau_moi"
                           id="xac_nhan_mat_khau_moi"
                           placeholder="Nhập lại mật khẩu mới"
                           required>
                    <span class="input-group-text password-toggle" onclick="togglePassword('xac_nhan_mat_khau_moi', 'icon_xn')">
                        <i class="fas fa-eye" id="icon_xn"></i>
                    </span>
                </div>
                @error('xac_nhan_mat_khau_moi')
                    <div class="text-danger mt-1" style="font-size: 13px;">
                        <i class="fas fa-exclamation-circle me-1"></i>{{ $message }}
                    </div>
                @enderror
            </div>

            {{-- Buttons --}}
            <div class="d-flex gap-3 mt-4">
                <button type="submit" class="btn btn-primary btn-submit">
                    <i class="fas fa-save me-2"></i>Lưu thay đổi
                </button>
                <a href="{{ url('admin/dashboard') }}" class="btn btn-cancel">
                    <i class="fas fa-times me-2"></i>Hủy
                </a>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function togglePassword(inputId, iconId) {
        const input = document.getElementById(inputId);
        const icon = document.getElementById(iconId);
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    }

    function checkStrength() {
        const val = document.getElementById('mat_khau_moi').value;
        const bar = document.getElementById('strengthBar');
        let strength = 0;
        if (val.length >= 6)  strength += 25;
        if (val.length >= 8)  strength += 25;
        if (/[A-Z]/.test(val)) strength += 25;
        if (/[0-9]/.test(val) || /[^A-Za-z0-9]/.test(val)) strength += 25;
        bar.style.width = strength + '%';
        if (strength <= 25)       bar.style.background = '#dc3545';
        else if (strength <= 50)  bar.style.background = '#fd7e14';
        else if (strength <= 75)  bar.style.background = '#ffc107';
        else                      bar.style.background = '#198754';
    }
</script>
@endpush
