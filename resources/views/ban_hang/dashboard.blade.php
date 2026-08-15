@extends('ban_hang.layouts.ban_hang')

@section('title', 'Dashboard - Nhân viên')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Dashboard</h1>
    <span class="text-muted">Xin chào, {{ Auth::user()->ho_ten }}</span>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="fas fa-store fa-3x text-muted mb-3"></i>
                <h4 class="text-muted">Chào mừng đến với SmartMart POS</h4>
                <p class="text-muted">Chọn chức năng từ menu bên trái để bắt đầu.</p>
                <a href="{{ route('nhan-vien.ban-hang') }}" class="btn btn-primary mt-3">
                    <i class="fas fa-cash-register me-1"></i> Bắt đầu bán hàng
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
