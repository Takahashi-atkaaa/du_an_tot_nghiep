@extends('admin_xem_truoc.layouts.admin')

@section('title', 'Thêm nhân sự - SmartMart')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1">Thêm mới nhân sự</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ url('admin/dashboard') }}">Admin</a></li>
                <li class="breadcrumb-item"><a href="{{ route('nguoi-dung.index') }}">Nhân sự</a></li>
                <li class="breadcrumb-item active">Thêm mới</li>
            </ol>
        </nav>
    </div>
    <a href="{{ route('nguoi-dung.index') }}" class="btn btn-outline-secondary">Quay lại</a>
</div>

<div class="card table-admin">
    <div class="card-body">
        <form method="POST" action="{{ route('nguoi-dung.store') }}">
            @csrf
            @include('admin_xem_truoc.nhan-su._form', ['isEdit' => false])
            <div class="mt-4 d-flex justify-content-end gap-2">
                <a href="{{ route('nguoi-dung.index') }}" class="btn btn-light">Hủy</a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-2"></i>Lưu nhân sự
                </button>
            </div>
        </form>
    </div>
</div>
@endsection