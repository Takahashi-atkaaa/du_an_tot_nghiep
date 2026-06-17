@extends('admin_xem_truoc.layouts.admin')

@section('title', 'Cap nhat phan chia ca lam viec - SmartMart')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1">Cập nhật lịch phân chia ca làm việc</h4>
    </div>
    <a href="{{ route('chia-ca-lam-viec.index', ['week_start' => $selectedWeekDate ?? request('week_start')]) }}" class="btn btn-outline-secondary">Quay lại</a>
</div>

<div class="card table-admin">
    <div class="card-body">
        <form method="POST" action="{{ route('chia-ca-lam-viec.update', $chiaCaLamViec) }}">
            @csrf
            @method('PUT')
            <input type="hidden" name="week_start" value="{{ $selectedWeekDate ?? request('week_start') }}">
            @include('admin_xem_truoc.chia-ca-lam-viec.bieu_mau', [
                'chiaCaLamViec' => $chiaCaLamViec,
                'nguoiDungs' => $nguoiDungs,
                'caLamViecs' => $caLamViecs,
            ])
            <div class="mt-4 d-flex justify-content-end gap-2">
                <a href="{{ route('chia-ca-lam-viec.index', ['week_start' => $selectedWeekDate ?? request('week_start')]) }}" class="btn btn-light">Hủy</a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-2"></i>Cập nhật
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
