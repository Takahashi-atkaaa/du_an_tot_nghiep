@extends('nhan_vien_view.layouts.nhan_vien')

@section('title', 'Chấm công')

@section('content')
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Chấm công</h1>
    <div>
        <span class="me-3"><strong>Tháng:</strong> {{ $currentMonth }}/{{ $currentYear }}</span>
    </div>
</div>

{{-- Nút điểm danh hôm nay --}}
@if($caHomNay)
    <div class="card mb-4">
        <div class="card-body text-center py-4">
            @if($caHomNay->diemDanh && $caHomNay->diemDanh->gio_tan_ca)
                <div class="text-success">
                    <i class="fas fa-check-circle fa-3x mb-3"></i>
                    <h4>Đã hoàn thành ca làm việc</h4>
                    <p class="text-muted mb-0">
                        Ca {{ $caHomNay->caLamViec->ten_ca }} &bull;
                        {{ $caHomNay->diemDanh->gio_vao->format('H:i') }} -
                        {{ $caHomNay->diemDanh->gio_tan_ca->format('H:i') }}
                    </p>
                </div>
            @elseif($caHomNay->diemDanh)
                <div class="text-info">
                    <i class="fas fa-clock fa-3x mb-3"></i>
                    <h4>Ca đang làm việc</h4>
                    <p class="text-muted mb-2">
                        Ca {{ $caHomNay->caLamViec->ten_ca }} &bull;
                        Đã vào lúc {{ $caHomNay->diemDanh->gio_vao->format('H:i') }}
                    </p>
                    <form action="{{ route('nhan-vien.diem-danh.ket-thuc-ca') }}" method="POST">
                        @csrf
                        <input type="hidden" name="id_diem_danh" value="{{ $caHomNay->diemDanh->id }}">
                        <button type="submit" class="btn btn-danger btn-lg">
                            <i class="fas fa-sign-out-alt me-2"></i>Kết thúc ca
                        </button>
                    </form>
                </div>
            @else
                <div>
                    <i class="fas fa-play-circle fa-3x text-primary mb-3"></i>
                    <h4>Ca {{ $caHomNay->caLamViec->ten_ca }} hôm nay</h4>
                    <p class="text-muted mb-2">
                        {{ $caHomNay->caLamViec->gio_bat_dau }} - {{ $caHomNay->caLamViec->gio_ket_thuc }}
                    </p>
                    <form action="{{ route('nhan-vien.diem-danh.vao-ca') }}" method="POST">
                        @csrf
                        <input type="hidden" name="id_chia_ca" value="{{ $caHomNay->id }}">
                        <button type="submit" class="btn btn-success btn-lg">
                            <i class="fas fa-sign-in-alt me-2"></i>Bắt đầu ca làm việc
                        </button>
                    </form>
                </div>
            @endif
        </div>
    </div>
@else
    <div class="alert alert-info mb-4">
        <i class="fas fa-info-circle me-2"></i>
        Hôm nay bạn không có ca làm việc nào được sắp xếp.
    </div>
@endif

{{-- Thống kê chấm công --}}
<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="card card-stat green">
            <div class="card-body d-flex align-items-center">
                <div class="icon me-3">
                    <i class="fas fa-check"></i>
                </div>
                <div>
                    <h3 class="mb-0">{{ $tongBuoi }}</h3>
                    <small class="text-muted">Ngày đi làm</small>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card card-stat blue">
            <div class="card-body d-flex align-items-center">
                <div class="icon me-3">
                    <i class="fas fa-clock"></i>
                </div>
                <div>
                    <h3 class="mb-0">{{ $diMuon }}</h3>
                    <small class="text-muted">Đi muộn</small>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card card-stat orange">
            <div class="card-body d-flex align-items-center">
                <div class="icon me-3">
                    <i class="fas fa-moon"></i>
                </div>
                <div>
                    <h3 class="mb-0">{{ $veSom }}</h3>
                    <small class="text-muted">Về sớm</small>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card card-stat purple">
            <div class="card-body d-flex align-items-center">
                <div class="icon me-3">
                    <i class="fas fa-business-time"></i>
                </div>
                <div>
                    <h3 class="mb-0">{{ $tangCa }}</h3>
                    <small class="text-muted">Tăng ca</small>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Bảng chấm công --}}
<div class="card">
    <div class="card-header bg-white">
        <h5 class="mb-0">Chi tiết chấm công tháng {{ $currentMonth }}/{{ $currentYear }}</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-nv mb-0">
                <thead>
                    <tr>
                        <th>Ngày</th>
                        <th>Ca</th>
                        <th>Giờ vào</th>
                        <th>Giờ ra</th>
                        <th>Tổng giờ</th>
                        <th>Trạng thái vào</th>
                        <th>Trạng thái ra</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($lichSuDiemDanh as $dd)
                        @php
                            $gioVao = $dd->gio_vao ? $dd->gio_vao->format('H:i') : '-';
                            $gioRa = $dd->gio_tan_ca ? $dd->gio_tan_ca->format('H:i') : '-';
                            $tenCa = $dd->chiaCaLamViec->caLamViec->ten_ca ?? '-';

                            $tongGio = '-';
                            if ($dd->gio_vao && $dd->gio_tan_ca) {
                                $diff = $dd->gio_vao->diffInMinutes($dd->gio_tan_ca);
                                $hours = intdiv($diff, 60);
                                $mins = $diff % 60;
                                $tongGio = $hours . 'h' . ($mins > 0 ? ' ' . $mins . 'p' : '');
                            }

                            $badgeVao = match($dd->trang_thai_vao_lam) {
                                'Đi muộn' => '<span class="status-badge status-warning">Đi muộn</span>',
                                'Đúng giờ' => '<span class="status-badge status-success">Đúng giờ</span>',
                                default => '<span class="badge bg-secondary">-</span>',
                            };

                            $badgeRa = match($dd->trang_thai_tan_ca) {
                                'Về sớm' => '<span class="status-badge status-warning">Về sớm</span>',
                                'Tăng ca' => '<span class="status-badge status-info">Tăng ca</span>',
                                'Đúng giờ' => '<span class="status-badge status-success">Đúng giờ</span>',
                                default => '<span class="badge bg-secondary">-</span>',
                            };
                        @endphp
                        <tr>
                            <td>{{ $dd->gio_vao ? $dd->gio_vao->format('d/m/Y') : '-' }}</td>
                            <td>{{ $tenCa }}</td>
                            <td>{{ $gioVao }}</td>
                            <td>{{ $gioRa }}</td>
                            <td>{{ $tongGio }}</td>
                            <td>{!! $badgeVao !!}</td>
                            <td>{!! $badgeRa !!}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">Chưa có dữ liệu điểm danh.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($lichSuDiemDanh->hasPages())
    <div class="card-footer bg-white">
        <nav>
            <ul class="pagination mb-0 justify-content-end">
                @if($lichSuDiemDanh->onFirstPage())
                    <li class="page-item disabled"><span class="page-link">Trước</span></li>
                @else
                    <li class="page-item"><a class="page-link" href="{{ $lichSuDiemDanh->previousPageUrl() }}">Trước</a></li>
                @endif

                @foreach($lichSuDiemDanh->getUrlRange(1, $lichSuDiemDanh->lastPage()) as $page => $url)
                    @if($page == $lichSuDiemDanh->currentPage())
                        <li class="page-item active"><span class="page-link">{{ $page }}</span></li>
                    @else
                        <li class="page-item"><a class="page-link" href="{{ $url }}">{{ $page }}</a></li>
                    @endif
                @endforeach

                @if($lichSuDiemDanh->hasMorePages())
                    <li class="page-item"><a class="page-link" href="{{ $lichSuDiemDanh->nextPageUrl() }}">Sau</a></li>
                @else
                    <li class="page-item disabled"><span class="page-link">Sau</span></li>
                @endif
            </ul>
        </nav>
    </div>
    @endif
</div>
@if(session('warning'))
<div class="modal fade" id="warningModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-warning">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title">
                    <i class="fas fa-exclamation-triangle me-2"></i>Cảnh báo
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <i class="fas fa-clock fa-4x text-warning mb-3"></i>
                <p class="fs-5">{{ session('warning') }}</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-warning" data-bs-dismiss="modal">Đóng</button>
            </div>
        </div>
    </div>
</div>
@endif
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        @if(session('warning'))
        var warningModal = new bootstrap.Modal(document.getElementById('warningModal'));
        warningModal.show();
        @endif
    });
</script>
@endsection
