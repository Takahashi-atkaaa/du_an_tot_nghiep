@extends('nhan_vien.layouts.nhan_vien')

@section('title', 'Chấm công')

@section('styles')
<style>

.card{
    border:none;
    border-radius:16px;
    box-shadow:0 4px 15px rgba(0,0,0,.08);
}

.btn{
    border-radius:12px;
}

.stat-card{
    border-radius:15px;
    color:white;
}

.stat-card h3{
    margin:0;
    font-weight:bold;
}

.stat-card i{
    font-size:30px;
    opacity:.8;
}

.bg-green{
    background:#28a745;
}

.bg-blue{
    background:#0d6efd;
}

.bg-orange{
    background:#fd7e14;
}

.bg-purple{
    background:#6f42c1;
}

.attendance-card{

    text-align:center;

    padding:25px;

}

.attendance-card h4{

    font-weight:bold;

}

.attendance-card i{

    font-size:60px;

}

@media(max-width:768px){

    h1{

        font-size:24px;

    }

    .btn-lg{

        width:100%;

    }

}

</style>
@endsection


@section('content')
@if(session('warning'))
    <div class="alert alert-warning alert-dismissible fade show">
        {{ session('warning') }}

        <button
            type="button"
            class="btn-close"
            data-bs-dismiss="alert">
        </button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show">
        {{ session('error') }}

        <button
            type="button"
            class="btn-close"
            data-bs-dismiss="alert">
        </button>
    </div>
@endif

@if(session('success'))

<div class="alert alert-success alert-dismissible fade show">

    {{ session('success') }}

    <button
        class="btn-close"
        data-bs-dismiss="alert">
    </button>

</div>

@endif


<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap">

    <div>

        <h3 class="fw-bold mb-1">

            <i class="fas fa-clock text-success"></i>

            Chấm công

        </h3>

        <small class="text-muted">

            Tháng {{ $currentMonth }}/{{ $currentYear }}

        </small>

    </div>

</div>


@if($caHomNay)

<div class="card mb-4">

<div class="card-body attendance-card">

@if($caHomNay->diemDanh && $caHomNay->diemDanh->gio_tan_ca)

<i class="fas fa-check-circle text-success mb-3"></i>

<h4>

Hoàn thành ca làm

</h4>

<p class="text-muted">

<b>{{ $caHomNay->caLamViec->ten_ca }}</b>

<br>

{{ $caHomNay->diemDanh->gio_vao->format('H:i') }}

-

{{ $caHomNay->diemDanh->gio_tan_ca->format('H:i') }}

</p>

<div class="alert alert-success">

Bạn đã hoàn thành ca làm hôm nay.

</div>

@elseif($caHomNay->diemDanh)

<i class="fas fa-business-time text-primary mb-3"></i>

<h4>

Đang trong ca làm

</h4>

<p class="text-muted">

<b>{{ $caHomNay->caLamViec->ten_ca }}</b>

<br>

Đã vào lúc

{{ $caHomNay->diemDanh->gio_vao->format('H:i') }}

</p>

<form
action="{{ route('nhan-vien.diem-danh.ket-thuc-ca') }}"
method="POST">

@csrf

<input
type="hidden"
name="id_diem_danh"
value="{{ $caHomNay->diemDanh->id }}">

<button
class="btn btn-danger btn-lg mt-2">

<i class="fas fa-sign-out-alt me-2"></i>

Kết thúc ca

</button>

</form>

@else

<i class="fas fa-play-circle text-success mb-3"></i>

<h4>

{{ $caHomNay->caLamViec->ten_ca }}

</h4>

<p class="text-muted">

{{ $caHomNay->caLamViec->gio_bat_dau }}

-

{{ $caHomNay->caLamViec->gio_ket_thuc }}

</p>

<form
action="{{ route('nhan-vien.diem-danh.vao-ca') }}"
method="POST">

@csrf

<input
type="hidden"
name="id_chia_ca"
value="{{ $caHomNay->id }}">

<button
class="btn btn-success btn-lg mt-2">

<i class="fas fa-sign-in-alt me-2"></i>

Bắt đầu ca làm

</button>

</form>

@endif

</div>

</div>

@else

<div class="alert alert-info">

<i class="fas fa-info-circle me-2"></i>

Hôm nay bạn không có ca làm việc.

</div>

@endif

{{-- Thống kê --}}
<div class="row g-3 mb-4">

    <div class="col-6 col-md-3">
        <div class="card stat-card bg-green">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <small>Tổng buổi</small>
                    <h3>{{ $tongBuoi }}</h3>
                </div>
                <i class="fas fa-calendar-check"></i>
            </div>
        </div>
    </div>

    <div class="col-6 col-md-3">
        <div class="card stat-card bg-blue">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <small>Đi muộn</small>
                    <h3>{{ $diMuon }}</h3>
                </div>
                <i class="fas fa-clock"></i>
            </div>
        </div>
    </div>

    <div class="col-6 col-md-3">
        <div class="card stat-card bg-orange">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <small>Về sớm</small>
                    <h3>{{ $veSom }}</h3>
                </div>
                <i class="fas fa-person-walking-arrow-right"></i>
            </div>
        </div>
    </div>

    <div class="col-6 col-md-3">
        <div class="card stat-card bg-purple">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <small>Tăng ca</small>
                    <h3>{{ $tangCa }}</h3>
                </div>
                <i class="fas fa-business-time"></i>
            </div>
        </div>
    </div>

</div>

{{-- Lịch sử --}}
<div class="card">

    <div class="card-header bg-white">

        <h5 class="mb-0">

            <i class="fas fa-history text-success"></i>

            Lịch sử chấm công

        </h5>

    </div>

    <div class="card-body">

        {{-- Mobile --}}
        <div class="d-block d-md-none">

            @forelse($lichSuDiemDanh as $dd)

                @php

                    $tenCa = $dd->chiaCaLamViec?->caLamViec?->ten_ca ?? '-';

                    $gioVao = $dd->gio_vao
                        ? \Carbon\Carbon::parse($dd->gio_vao)->format('H:i')
                        : '-';

                    $gioRa = $dd->gio_tan_ca
                        ? \Carbon\Carbon::parse($dd->gio_tan_ca)->format('H:i')
                        : '-';

                    $ngay = $dd->gio_vao
                        ? \Carbon\Carbon::parse($dd->gio_vao)->format('d/m/Y')
                        : '-';

                @endphp

                <div class="card mb-3 shadow-sm">

                    <div class="card-body">

                        <div class="d-flex justify-content-between">

                            <h6 class="fw-bold">

                                {{ $ngay }}

                            </h6>

                            @if($dd->trang_thai == 'Đúng giờ')

                                <span class="badge bg-success">

                                    Đúng giờ

                                </span>

                            @elseif($dd->trang_thai == 'Đi muộn')

                                <span class="badge bg-warning">

                                    Đi muộn

                                </span>

                            @else

                                <span class="badge bg-secondary">

                                    {{ $dd->trang_thai }}

                                </span>

                            @endif

                        </div>

                        <hr>

                        <p class="mb-2">

                            <strong>Ca:</strong>

                            {{ $tenCa }}

                        </p>

                        <p class="mb-2">

                            <strong>Giờ vào:</strong>

                            {{ $gioVao }}

                        </p>

                        <p class="mb-2">

                            <strong>Giờ ra:</strong>

                            {{ $gioRa }}

                        </p>

                    </div>

                </div>

            @empty

                <div class="alert alert-info">

                    Chưa có dữ liệu chấm công.

                </div>

            @endforelse

        </div>

        {{-- Desktop --}}
        <div class="table-responsive d-none d-md-block">

            <table class="table table-hover align-middle">

                <thead class="table-success">

                    <tr>

                        <th>Ngày</th>

                        <th>Ca</th>

                        <th>Giờ vào</th>

                        <th>Giờ ra</th>

                        <th>Trạng thái</th>

                    </tr>

                </thead>

                <tbody>

                @forelse($lichSuDiemDanh as $dd)

                    <tr>

                        <td>

                            {{ $dd->gio_vao ? \Carbon\Carbon::parse($dd->gio_vao)->format('d/m/Y') : '-' }}

                        </td>

                        <td>

                            {{ $dd->chiaCaLamViec?->caLamViec?->ten_ca ?? '-' }}

                        </td>

                        <td>

                            {{ $dd->gio_vao ? \Carbon\Carbon::parse($dd->gio_vao)->format('H:i') : '-' }}

                        </td>

                        <td>

                            {{ $dd->gio_tan_ca ? \Carbon\Carbon::parse($dd->gio_tan_ca)->format('H:i') : '-' }}

                        </td>

                        <td>

                            @if($dd->trang_thai=='Đúng giờ')

                                <span class="badge bg-success">

                                    Đúng giờ

                                </span>

                            @elseif($dd->trang_thai=='Đi muộn')

                                <span class="badge bg-warning">

                                    Đi muộn

                                </span>

                            @else

                                <span class="badge bg-secondary">

                                    {{ $dd->trang_thai }}

                                </span>

                            @endif

                        </td>

                    </tr>

                @empty

                    <tr>

                        <td colspan="5" class="text-center">

                            Chưa có dữ liệu.

                        </td>

                    </tr>

                @endforelse

                </tbody>

            </table>

        </div>

    </div>

</div>

{{-- Phân trang --}}
@if(method_exists($lichSuDiemDanh, 'links'))
    <div class="mt-4 d-flex justify-content-center">
        {{ $lichSuDiemDanh->links() }}
    </div>
@endif

@endsection

@section('scripts')
<script>
document.addEventListener("DOMContentLoaded", function () {

    const btnVaoCa = document.querySelector(".btn-success.btn-lg");
    const btnRaCa = document.querySelector(".btn-danger.btn-lg");

    if (btnVaoCa) {
        const formVaoCa = btnVaoCa.closest("form");

        if (formVaoCa) {
            formVaoCa.addEventListener("submit", function () {
                setTimeout(function () {
                    btnVaoCa.disabled = true;
                    btnVaoCa.innerHTML =
                        '<span class="spinner-border spinner-border-sm me-2"></span>Đang xử lý...';
                }, 0);
            });
        }
    }

    if (btnRaCa) {
        const formRaCa = btnRaCa.closest("form");

        if (formRaCa) {
            formRaCa.addEventListener("submit", function () {
                setTimeout(function () {
                    btnRaCa.disabled = true;
                    btnRaCa.innerHTML =
                        '<span class="spinner-border spinner-border-sm me-2"></span>Đang xử lý...';
                }, 0);
            });
        }
    }

});
</script>
@endsection