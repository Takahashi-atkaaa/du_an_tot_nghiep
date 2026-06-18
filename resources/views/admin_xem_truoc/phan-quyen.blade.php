@extends('admin_xem_truoc.layouts.admin')

@section('title', 'Cập nhật nhân sự - SmartMart')

@section('content')

<style>
    .quyen-grid {
        display: grid;
        grid-template-columns: repeat(5, 1fr);
        gap: 12px;
    }

    .quyen-item {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 8px 10px;
        border: 1px solid #e5e5e5;
        border-radius: 6px;
        background: #f9f9f9;
        font-size: 14px;
    }
</style>

<h2>Phân quyền {{$vaiTro->ten_vai_tro}}</h2>

    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

<form action="{{ route('admin.quyen.update', $vaiTro->id) }}" method="POST">
    @csrf

    <div class="quyen-grid">
        @foreach($quyens as $quyen)
            <label class="quyen-item" for="quyen-{{ $quyen->id }}">
                <input class="form-check-input"
                       type="checkbox"
                       id="quyen-{{ $quyen->id }}"
                       name="quyens[]"
                       value="{{ $quyen->id }}"
                       {{ in_array($quyen->id, $quyen_thuoc_vai_tro) ? 'checked' : '' }}>

                <span>{{ $quyen->ten_quyen }}</span>
            </label>
        @endforeach
    </div>

    <div class="d-flex justify-content-end mt-3">
        <button type="submit" class="btn btn-primary" onclick="return confirm('Bạn có chắc muốn lưu thay đổi phân quyền cho nhân viên này không?')">
            Lưu quyền
        </button>
    </div>
</form>

@endsection