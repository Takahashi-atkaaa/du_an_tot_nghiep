@extends('nhan_vien_view.layouts.nhan_vien')

@section('title', 'Lịch làm việc theo tuần - SmartMart')

@section('styles')
<style>
    .weekly-schedule-page {
        color: #1f2937;
    }

    .weekly-schedule-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 14px;
        margin-bottom: 18px;
        flex-wrap: wrap;
    }

    .weekly-schedule-title {
        margin: 0;
        font-size: 1.7rem;
        font-weight: 700;
        color: #182230;
    }

    .weekly-schedule-subtitle {
        margin: 6px 0 0;
        font-size: 0.9rem;
        color: #667085;
    }

    .week-navigation {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
        justify-content: flex-end;
    }

    .week-navigation .btn {
        border-radius: 12px;
        padding: 8px 14px;
        min-height: 40px;
        font-size: 0.92rem;
        font-weight: 600;
        box-shadow: 0 6px 16px rgba(16, 24, 40, 0.06);
    }

    .week-navigation .btn-outline-success {
        border-color: #cfe8d2;
        color: #1b7a32;
    }

    .week-navigation .btn-success {
        background: #2e7d32;
        border-color: #2e7d32;
    }

    .week-picker {
        min-width: 210px;
        border-radius: 12px;
        border: 1px solid #d0d5dd;
        padding: 8px 12px;
        min-height: 40px;
        font-size: 0.92rem;
        box-shadow: 0 6px 16px rgba(16, 24, 40, 0.06);
    }

    .schedule-summary {
        background: #fff;
        border: 1px solid #dfe6de;
        border-radius: 20px;
        box-shadow: 0 12px 28px rgba(16, 24, 40, 0.05);
        padding: 8px;
        margin-bottom: 18px;
    }

    .schedule-summary-row {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 8px;
    }

    .summary-card {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px 14px;
        border-radius: 16px;
    }

    .summary-card + .summary-card {
        border-left: 1px solid #edf1ed;
    }

    .summary-icon {
        width: 44px;
        height: 44px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(180deg, #edf9ee 0%, #e3f4e5 100%);
        color: #2e7d32;
        font-size: 1.05rem;
        flex-shrink: 0;
    }

    .summary-label {
        margin: 0 0 6px;
        font-size: 0.85rem;
        color: #667085;
    }

    .summary-value {
        margin: 0;
        font-size: 1.35rem;
        line-height: 1;
        font-weight: 700;
        color: #1b7a32;
    }

    .week-grid-wrapper {
        background: #fff;
        border: 1px solid #dfe6de;
        border-radius: 20px;
        box-shadow: 0 12px 28px rgba(16, 24, 40, 0.05);
        padding: 14px;
        margin-bottom: 18px;
        overflow-x: auto;
    }

    .week-scroll {
        overflow-x: auto;
    }

    .week-grid {
        display: grid;
        grid-template-columns: repeat(7, minmax(160px, 1fr));
        gap: 10px;
        min-width: 1180px;
    }

    .day-column {
        background: #fff;
        border-left: 1px solid #edf1ed;
        padding-left: 10px;
    }

    .day-column:first-child {
        border-left: none;
        padding-left: 0;
    }

    .day-header {
        text-align: center;
        padding: 8px 8px 12px;
        border-radius: 14px;
        background: linear-gradient(180deg, #fbfcfd 0%, #f4f7fb 100%);
        margin-bottom: 10px;
    }

    .day-name {
        margin: 0;
        font-size: 1.15rem;
        font-weight: 700;
        color: #182230;
    }

    .day-name.is-sunday {
        color: #e53935;
    }

    .day-date {
        margin-top: 2px;
        color: #667085;
        font-size: 0.9rem;
        font-weight: 500;
    }

    .day-shifts {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .shift-card {
        position: relative;
        min-height: 205px;
    }

    .shift-card-inner {
        border: 1px solid #e4efe5;
        border-radius: 18px;
        background: #fff;
        box-shadow: 0 10px 24px rgba(16, 24, 40, 0.05);
        padding: 12px;
        min-height: 205px;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        overflow: hidden;
    }

    .shift-card.shift-card--empty .shift-card-inner {
        border-color: #e5e7eb;
        background: linear-gradient(180deg, #f9fafb 0%, #f3f4f6 100%);
        color: #6b7280;
        justify-content: center;
        align-items: center;
        text-align: center;
    }

    .shift-card.shift-card--off .shift-card-inner {
        border-color: #f5df98;
        background: linear-gradient(180deg, #fff8dd 0%, #fff3c4 100%);
        color: #b7791f;
        justify-content: center;
        align-items: center;
        text-align: center;
    }

    .shift-card-header {
        display: flex;
        align-items: center;
        gap: 8px;
        padding-right: 52px;
    }

    .shift-code,
    .shift-label,
    .modal-shift-code {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 999px;
        padding: 6px 12px;
        min-width: 74px;
        font-weight: 700;
        font-size: 0.95rem;
        box-shadow: inset 0 1px 0 rgba(255,255,255,0.9);
    }

    .shift-code.shift-morning,
    .shift-label.shift-morning,
    .modal-shift-code.shift-morning {
        background: linear-gradient(180deg, #eaf8ea 0%, #d8efda 100%);
        color: #24753b;
    }

    .shift-code.shift-afternoon,
    .shift-label.shift-afternoon,
    .modal-shift-code.shift-afternoon {
        background: linear-gradient(180deg, #edf4ff 0%, #dbe8ff 100%);
        color: #2563eb;
    }

    .shift-code.shift-evening,
    .shift-label.shift-evening,
    .modal-shift-code.shift-evening {
        background: linear-gradient(180deg, #efe9ff 0%, #e1d5ff 100%);
        color: #6d28d9;
    }

    .shift-code.shift-default,
    .shift-label.shift-default,
    .modal-shift-code.shift-default {
        background: linear-gradient(180deg, #eef2ff 0%, #e5e7ff 100%);
        color: #374151;
    }

    .shift-more-wrapper {
        position: absolute;
        top: 12px;
        right: 12px;
        z-index: 2;
    }

    .shift-more-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 38px;
        height: 34px;
        padding: 0 10px;
        border-radius: 999px;
        border: 1px solid #d7eadb;
        background: linear-gradient(180deg, #f2fbf3 0%, #e5f5e8 100%);
        color: #2e7d32;
        font-weight: 700;
        font-size: 0.9rem;
        box-shadow: 0 6px 16px rgba(46, 125, 50, 0.12);
        cursor: pointer;
        transition: transform 0.18s ease, box-shadow 0.18s ease;
    }

    .shift-more-badge:hover,
    .shift-more-badge:focus {
        transform: translateY(-1px);
        box-shadow: 0 8px 18px rgba(46, 125, 50, 0.18);
    }

    .shift-details {
        margin-top: 14px;
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .shift-info,
    .modal-shift-time,
    .modal-shift-role {
        display: flex;
        align-items: center;
        gap: 8px;
        color: #344054;
        font-size: 0.95rem;
        font-weight: 600;
    }

    .modal-shift-role,
    .shift-info + .shift-info {
        margin-top: 8px;
    }

    .shift-info i,
    .modal-shift-time i,
    .modal-shift-role i {
        color: #667085;
        width: 18px;
        text-align: center;
    }

    .shift-status {
        margin-top: 14px;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 8px 12px;
        border-radius: 12px;
        background: linear-gradient(180deg, #eefaf0 0%, #e2f5e7 100%);
        color: #2c8f45;
        font-weight: 700;
        font-size: 0.95rem;
    }

    .shift-empty-icon {
        font-size: 1.35rem;
        color: #98a2b3;
        margin-bottom: 10px;
    }

    .legend-box {
        background: #fff;
        border: 1px solid #dfe6de;
        border-radius: 18px;
        box-shadow: 0 12px 28px rgba(16, 24, 40, 0.05);
        padding: 16px 18px;
    }

    .legend-title {
        margin: 0 0 14px;
        font-size: 1.2rem;
        font-weight: 700;
        color: #182230;
    }

    .legend-list {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 14px;
    }

    .legend-item {
        display: flex;
        align-items: center;
        gap: 10px;
        color: #475467;
        font-size: 0.92rem;
    }

    .legend-note {
        margin-top: 18px;
        border: 1px solid #bfd5ff;
        background: #eef5ff;
        color: #2a63d2;
        border-radius: 14px;
        padding: 12px 14px;
        font-size: 0.92rem;
        font-weight: 500;
    }

    .day-shifts-modal .modal-dialog {
        max-width: 540px;
    }

    .day-shifts-modal .modal-content {
        border: none;
        border-radius: 22px;
        overflow: hidden;
        box-shadow: 0 22px 60px rgba(15, 23, 42, 0.2);
    }

    .day-shifts-modal .modal-header {
        padding: 18px 20px 12px;
        border-bottom: 1px solid #eef2f7;
        align-items: flex-start;
    }

    .day-shifts-modal .modal-title {
        font-size: 1.15rem;
        font-weight: 700;
        color: #182230;
    }

    .day-shifts-modal .modal-subtitle {
        margin: 4px 0 0;
        font-size: 0.9rem;
        color: #667085;
    }

    .day-shifts-modal .btn-close {
        margin: 0;
        box-shadow: none;
    }

    .day-shifts-modal .modal-body {
        padding: 16px 20px;
    }

    .modal-shift-list {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .modal-shift-item {
        border: 1px solid #e8edf3;
        border-radius: 14px;
        background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
        padding: 12px;
    }

    .modal-shift-item-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        margin-bottom: 8px;
    }

    .shift-more-title {
        margin: 0 0 10px;
        font-size: 0.96rem;
        font-weight: 700;
        color: #344054;
    }

    .day-shifts-modal .modal-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
        padding: 12px 20px 18px;
        border-top: 1px solid #eef2f7;
    }

    .day-shifts-modal .modal-total {
        color: #667085;
        font-weight: 600;
        font-size: 0.9rem;
    }

    .day-shifts-modal .btn-success {
        background: #2e7d32;
        border-color: #2e7d32;
        border-radius: 12px;
        padding: 8px 18px;
        font-weight: 600;
    }

    @media (max-width: 1200px) {
        .schedule-summary-row,
        .legend-list {
            grid-template-columns: 1fr;
        }

        .summary-card + .summary-card {
            border-left: none;
            border-top: 1px solid #edf1ed;
        }
    }

    @media (max-width: 768px) {
        .weekly-schedule-title {
            font-size: 1.65rem;
        }

        .week-navigation {
            justify-content: flex-start;
        }

        .day-shifts-modal .modal-dialog {
            max-width: calc(100vw - 24px);
            margin: 12px auto;
        }
    }
</style>
@endsection

@section('content')
@php
    $todayWeekStart = now()->startOfWeek(\Carbon\Carbon::MONDAY)->toDateString();

    $shiftTypeClass = function (?string $tenCa) {
        $normalized = \Illuminate\Support\Str::of((string) $tenCa)->lower()->ascii()->value();

        if (str_contains($normalized, 'sang')) {
            return 'shift-morning';
        }

        if (str_contains($normalized, 'chieu')) {
            return 'shift-afternoon';
        }

        if (str_contains($normalized, 'toi') || str_contains($normalized, 'dem')) {
            return 'shift-evening';
        }

        return 'shift-default';
    };

    $thuTrongTuan = function ($dayOfWeekIso) {
        return match ($dayOfWeekIso) {
            1 => 'Thứ 2',
            2 => 'Thứ 3',
            3 => 'Thứ 4',
            4 => 'Thứ 5',
            5 => 'Thứ 6',
            6 => 'Thứ 7',
            default => 'Chủ nhật',
        };
    };
@endphp

<div class="weekly-schedule-page">
    <div class="weekly-schedule-header">
        <div>
            <h1 class="weekly-schedule-title">Lịch làm việc theo tuần</h1>
            <p class="weekly-schedule-subtitle">Từ {{ $weekStart->format('d/m/Y') }} đến {{ $weekEnd->format('d/m/Y') }}</p>
        </div>

        <div class="week-navigation">
            <a href="{{ route('nhan-vien.lich-tuan', ['week_start' => $weekStart->copy()->subDays(7)->toDateString(), 'user_id' => $nguoiDung->id]) }}" class="btn btn-outline-success">
                <i class="fas fa-arrow-left me-2"></i>Tuần trước
            </a>
            <a href="{{ route('nhan-vien.lich-tuan', ['week_start' => $todayWeekStart, 'user_id' => $nguoiDung->id]) }}" class="btn {{ $weekStart->toDateString() === $todayWeekStart ? 'btn-success' : 'btn-outline-success' }}">
                <i class="far fa-calendar me-2"></i>Tuần này
            </a>
            <a href="{{ route('nhan-vien.lich-tuan', ['week_start' => $weekStart->copy()->addDays(7)->toDateString(), 'user_id' => $nguoiDung->id]) }}" class="btn btn-outline-success">
                Tuần sau <i class="fas fa-arrow-right ms-2"></i>
            </a>
            <form method="GET" action="{{ route('nhan-vien.lich-tuan') }}">
                <input type="hidden" name="user_id" value="{{ $nguoiDung->id }}">
                <input type="date" name="week_start" value="{{ $selectedWeekDate }}" class="form-control week-picker" onchange="this.form.submit()">
            </form>
        </div>
    </div>

    <div class="schedule-summary">
        <div class="schedule-summary-row">
            <div class="summary-card">
                <span class="summary-icon"><i class="far fa-calendar-check"></i></span>
                <div>
                    <p class="summary-label">Tổng số ca trong tuần</p>
                    <p class="summary-value">{{ $tongSoCa }}</p>
                </div>
            </div>
            <div class="summary-card">
                <span class="summary-icon"><i class="far fa-clock"></i></span>
                <div>
                    <p class="summary-label">Tổng giờ làm dự kiến</p>
                    <p class="summary-value">{{ $tongGioLam }}</p>
                </div>
            </div>
            <div class="summary-card">
                <span class="summary-icon"><i class="far fa-user"></i></span>
                <div>
                    <p class="summary-label">Vai trò chính</p>
                    <p class="summary-value">{{ $vaiTroChinh }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="week-grid-wrapper week-scroll">
        <div class="week-grid">
            @foreach($weekDates as $date)
                @php
                    $dateKey = $date->toDateString();
                    $caTrongNgay = $lichTheoNgay[$dateKey] ?? collect();
                    $isSunday = $date->dayOfWeekIso === 7;
                    $dayStatus = $caTrongNgay->isNotEmpty()
                        ? 'assigned'
                        : ($ngayDaMoLich->has($dateKey) ? 'off' : 'empty');
                @endphp

                <div class="day-column">
                    <div class="day-header">
                        <p class="day-name {{ $isSunday ? 'is-sunday' : '' }}">{{ $thuTrongTuan($date->dayOfWeekIso) }}</p>
                        <div class="day-date">{{ $date->format('d/m/Y') }}</div>
                    </div>

                    <div class="day-shifts">
                        @if($dayStatus === 'assigned')
                            @php
                                $caChinh = $caTrongNgay->first();
                                $cacCaConLai = $caTrongNgay->slice(1)->values();
                                $tenCa = $caChinh->caLamViec?->ten_ca ?? 'Ca làm việc';
                                $gioBatDau = $caChinh->caLamViec ? \Illuminate\Support\Carbon::parse($caChinh->caLamViec->gio_bat_dau)->format('H:i') : '--:--';
                                $gioKetThuc = $caChinh->caLamViec ? \Illuminate\Support\Carbon::parse($caChinh->caLamViec->gio_ket_thuc)->format('H:i') : '--:--';
                                $vaiTroTrongCa = ($caChinh->vai_tro_trong_ca ?? 'nhan_vien') === 'truong_ca' ? 'Trưởng ca' : 'Nhân viên';
                                $modalId = 'modalShiftsDay' . $date->format('Ymd');
                            @endphp

                            <div class="shift-card">
                                @if($cacCaConLai->isNotEmpty())
                                    <div class="shift-more-wrapper">
                                        <button type="button" class="shift-more-badge" data-bs-toggle="modal" data-bs-target="#{{ $modalId }}">
                                            +{{ $cacCaConLai->count() }}
                                        </button>
                                    </div>
                                @endif

                                <div class="shift-card-inner">
                                    <div>
                                        <div class="shift-card-header">
                                            <span class="shift-code {{ $shiftTypeClass($tenCa) }}">{{ $tenCa }}</span>
                                        </div>

                                        <div class="shift-details">
                                            <div class="shift-info">
                                                <i class="far fa-clock"></i>
                                                <span>{{ $gioBatDau }} - {{ $gioKetThuc }}</span>
                                            </div>
                                            <div class="shift-info">
                                                <i class="fas fa-user"></i>
                                                <span>{{ $vaiTroTrongCa }}</span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="shift-status">
                                        <i class="far fa-check-circle"></i>
                                        <span>Đã phân ca</span>
                                    </div>
                                </div>
                            </div>

                            @if($cacCaConLai->isNotEmpty())
                                <div class="modal fade day-shifts-modal" id="{{ $modalId }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <div>
                                                    <h5 class="modal-title">Ca làm việc trong ngày</h5>
                                                    <p class="modal-subtitle">{{ $thuTrongTuan($date->dayOfWeekIso) }} - {{ $date->format('d/m/Y') }}</p>
                                                </div>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <p class="shift-more-title">Danh sách ca làm trong ngày</p>
                                                <div class="modal-shift-list">
                                                    @foreach($caTrongNgay as $caModal)
                                                        @php
                                                            $tenCaModal = $caModal->caLamViec?->ten_ca ?? 'Ca làm việc';
                                                            $gioBatDauModal = $caModal->caLamViec ? \Illuminate\Support\Carbon::parse($caModal->caLamViec->gio_bat_dau)->format('H:i') : '--:--';
                                                            $gioKetThucModal = $caModal->caLamViec ? \Illuminate\Support\Carbon::parse($caModal->caLamViec->gio_ket_thuc)->format('H:i') : '--:--';
                                                            $vaiTroCaModal = ($caModal->vai_tro_trong_ca ?? 'nhan_vien') === 'truong_ca' ? 'Trưởng ca' : 'Nhân viên';
                                                        @endphp
                                                        <div class="modal-shift-item">
                                                            <div class="modal-shift-item-head">
                                                                <span class="modal-shift-code {{ $shiftTypeClass($tenCaModal) }}">{{ $tenCaModal }}</span>
                                                            </div>
                                                            <div class="modal-shift-time">
                                                                <i class="far fa-clock"></i>
                                                                <span>{{ $gioBatDauModal }} - {{ $gioKetThucModal }}</span>
                                                            </div>
                                                            <div class="modal-shift-role">
                                                                <i class="fas fa-user"></i>
                                                                <span>{{ $vaiTroCaModal }}</span>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <div class="modal-total">Tổng cộng {{ $caTrongNgay->count() }} ca trong ngày</div>
                                                <button type="button" class="btn btn-success" data-bs-dismiss="modal">Đóng</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @elseif($dayStatus === 'off')
                            <div class="shift-card shift-card--off">
                                <div class="shift-card-inner">
                                    <div>
                                        <span class="shift-label shift-default" style="background: linear-gradient(180deg, #fff1b8 0%, #ffe08a 100%); color: #b7791f;">Nghỉ</span>
                                    </div>
                                    <div class="mt-4">
                                        <i class="fas fa-umbrella-beach shift-empty-icon" style="color:#c08a1f;"></i>
                                        <div class="fs-5 fw-semibold">Ngày nghỉ</div>
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="shift-card shift-card--empty">
                                <div class="shift-card-inner">
                                    <div>
                                        <span class="shift-label shift-default" style="background: linear-gradient(180deg, #eef0f3 0%, #e4e7ec 100%); color: #667085;">Chưa có ca</span>
                                    </div>
                                    <div class="mt-4">
                                        <i class="far fa-calendar-alt shift-empty-icon"></i>
                                        <div class="fs-5 fw-semibold">Chưa có ca làm việc</div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <div class="legend-note">
        <i class="fas fa-info-circle me-2"></i>
        Bạn đang xem lịch cá nhân trong tuần đã chọn của <strong>{{ $nguoiDung->ho_ten }}</strong>.
    </div>
</div>
@endsection
