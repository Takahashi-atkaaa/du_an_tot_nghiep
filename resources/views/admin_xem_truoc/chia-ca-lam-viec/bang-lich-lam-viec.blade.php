<style>
    .table-responsive {
        overflow-x: auto;
        overflow-y: visible;
    }

    .lich-cell {
        min-width: 180px;
        vertical-align: top;
        position: relative;
        overflow: visible;
    }

    .shift-card {
        position: relative;
        border: 1px solid #e7eef8;
        border-radius: 18px;
        overflow: visible;
        box-shadow: 0 10px 24px rgba(15, 23, 42, 0.08);
    }

    .shift-card--warning {
        border-color: #ff6b6b;
        box-shadow: 0 10px 24px rgba(15, 23, 42, 0.08), inset 0 0 0 1px rgba(239, 68, 68, 0.12);
    }

    .shift-card-inner {
        border-radius: inherit;
        overflow: hidden;
        background: #fff;
    }

    .shift-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        padding: 16px 16px 14px;
        padding-right: 58px;
        background: radial-gradient(circle at top left, rgba(255, 235, 229, 0.95) 0%, rgba(255, 246, 246, 0.82) 34%, rgba(249, 251, 255, 0.95) 100%);
    }

    .shift-header-left,
    .shift-header-right {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .shift-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 48px;
        padding: 8px 14px;
        border-radius: 999px;
        background: linear-gradient(180deg, #edf4ff 0%, #dfeeff 100%);
        border: 1px solid #cfe0ff;
        color: #2f63d8;
        font-weight: 700;
        font-size: 14px;
        line-height: 1;
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.85);
    }

    .shift-badge--more {
        min-width: 46px;
        padding: 8px 10px;
        font-size: 12px;
    }

    .shift-body {
        padding: 14px 16px 16px;
    }

    .shift-meta {
        display: flex;
        align-items: center;
        gap: 8px;
        color: #475467;
        font-size: 13px;
        font-weight: 600;
    }

    .shift-meta + .shift-meta {
        margin-top: 10px;
    }

    .shift-meta-icon {
        width: 18px;
        text-align: center;
        color: #667085;
    }

    .shift-meta--role {
        color: #1f2937;
        font-weight: 700;
    }

    .shift-actions,
    .shift-overlay-actions {
        display: flex;
        gap: 12px;
        padding: 14px 16px 16px;
        border-top: 1px solid #eef2f7;
    }

    .shift-actions .btn,
    .shift-overlay-actions .btn {
        flex: 1;
        border-radius: 12px;
        font-weight: 700;
        padding: 9px 12px;
    }

    .shift-btn-edit {
        border-color: #d8e5ff;
        background: linear-gradient(180deg, #f7faff 0%, #edf4ff 100%);
        color: #2f63d8;
    }

    .shift-btn-delete {
        border-color: #ffd2d2;
        background: linear-gradient(180deg, #fff8f8 0%, #ffefef 100%);
        color: #ef4444;
    }

    .shift-overlay {
        position: relative;
    }

    .shift-overlay-trigger {
        cursor: pointer;
    }

    .shift-overlay-panel {
        display: none;
        position: absolute;
        top: calc(100% + 10px);
        left: 0;
        z-index: 20;
        width: 300px;
        max-height: 340px;
        overflow-y: auto;
        padding: 12px;
        border: 1px solid #dbe2ea;
        border-radius: 16px;
        background: #ffffff;
        box-shadow: 0 18px 40px rgba(15, 23, 42, 0.14);
    }

    .shift-overlay.is-open .shift-overlay-panel {
        display: block;
    }

    .shift-overlay-toolbar {
        display: flex;
        justify-content: flex-end;
        margin-bottom: 10px;
        padding-bottom: 10px;
        border-bottom: 1px solid #e5e7eb;
    }

    .shift-overlay-item + .shift-overlay-item {
        margin-top: 12px;
    }

    .warning-wrapper {
        position: absolute;
        top: 14px;
        right: 12px;
        z-index: 40;
    }

    .warning-btn {
        width: 34px;
        height: 34px;
        border-radius: 999px;
        border: 1px solid #fcb3b3;
        background: #fff;
        color: #dc2626;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 15px;
        font-weight: 700;
        box-shadow: 0 4px 14px rgba(239, 68, 68, 0.14);
        cursor: pointer;
    }

    .warning-btn:focus {
        outline: none;
    }

    .warning-dropdown {
        display: none;
        position: absolute;
        top: calc(100% + 8px);
        right: 0;
        z-index: 60;
        width: 220px;
        padding: 10px;
        border: 1px solid #fecaca;
        border-radius: 12px;
        background: #fff;
        box-shadow: 0 16px 32px rgba(15, 23, 42, 0.14);
    }

    .warning-wrapper:hover .warning-dropdown,
    .warning-wrapper:focus-within .warning-dropdown,
    .warning-wrapper.is-open .warning-dropdown {
        display: block;
    }

    .warning-item + .warning-item {
        margin-top: 8px;
    }

    .warning-item {
        display: flex;
        align-items: flex-start;
        gap: 8px;
        font-size: 12px;
        font-weight: 600;
        color: #b42318;
        line-height: 1.4;
    }

    .shift-empty {
        font-size: 14px;
        color: #6b7280;
    }
</style>

@php
    $weekdayLabels = [
        1 => 'Thứ 2',
        2 => 'Thứ 3',
        3 => 'Thứ 4',
        4 => 'Thứ 5',
        5 => 'Thứ 6',
        6 => 'Thứ 7',
        7 => 'Chủ nhật',
    ];
@endphp

@once
    @php
        function renderShiftWarning($canhBaoCa)
        {
            if (empty($canhBaoCa)) {
                return;
            }

            $coCanhBao = ($canhBaoCa['thieu_truong_ca'] ?? false)
                || ($canhBaoCa['nhieu_truong_ca'] ?? false)
                || ($canhBaoCa['thieu_nhan_su'] ?? false);

            if (! $coCanhBao) {
                return;
            }

            echo '<div class="warning-wrapper">';
            echo '<button type="button" class="warning-btn" aria-label="Xem cảnh báo"><i class="fas fa-exclamation"></i></button>';
            echo '<div class="warning-dropdown">';

            if ($canhBaoCa['nhieu_truong_ca'] ?? false) {
                echo '<div class="warning-item"><i class="fas fa-user-shield"></i><span>Có ' . e($canhBaoCa['so_truong_ca']) . ' trưởng ca</span></div>';
            }

            if ($canhBaoCa['thieu_truong_ca'] ?? false) {
                echo '<div class="warning-item"><i class="fas fa-user-shield"></i><span>Chưa có trưởng ca</span></div>';
            }

            if ($canhBaoCa['thieu_nhan_su'] ?? false) {
                echo '<div class="warning-item"><i class="fas fa-users"></i><span>Thiếu nhân sự (' . e($canhBaoCa['so_nhan_vien']) . '/' . e($canhBaoCa['so_toi_thieu']) . ')</span></div>';
            }

            echo '</div></div>';
        }
    @endphp
@endonce

<div class="table-responsive">
    <table class="table table-bordered align-middle mb-0">
        <thead class="table-light">
            <tr>
                <th style="min-width: 220px;">Nhân viên</th>
                @foreach($weekDates as $date)
                    <th style="min-width: 180px;">
                        {{ $weekdayLabels[$date->dayOfWeekIso] ?? 'Ngày' }}
                        <div class="small text-muted">{{ $date->format('d/m/Y') }}</div>
                    </th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @forelse($nguoiDungs as $nguoiDung)
                <tr>
                    <td>
                        <div class="fw-semibold">{{ $nguoiDung->ho_ten }}</div>
                        <div class="small text-muted">{{ $nguoiDung->vaiTro->ten_vai_tro ?? 'Chưa có vai trò' }}</div>
                    </td>
                    @foreach($weekDates as $date)
                        @php
                            $lichTrongNgay = $maTranLich[$nguoiDung->id][$date->toDateString()] ?? [];
                        @endphp
                        <td class="lich-cell">
                            @if(count($lichTrongNgay) === 0)
                                <span class="shift-empty">Chưa phân ca</span>
                            @elseif(count($lichTrongNgay) === 1)
                                @php
                                    $lich = $lichTrongNgay[0];
                                    $canhBaoCa = $chiTietCanhBaoTheoCa[$date->toDateString() . '|' . $lich->id_ca_lam_viec] ?? null;
                                    $coCanhBao = ! empty($canhBaoCa) && (($canhBaoCa['thieu_truong_ca'] ?? false) || ($canhBaoCa['nhieu_truong_ca'] ?? false) || ($canhBaoCa['thieu_nhan_su'] ?? false));
                                @endphp
                                <div class="shift-card {{ $coCanhBao ? 'shift-card--warning' : '' }}">
                                    {!! renderShiftWarning($canhBaoCa) !!}
                                    <div class="shift-card-inner">
                                        <div class="shift-header">
                                            <div class="shift-header-left">
                                                <span class="shift-badge">{{ $lich->caLamViec?->ten_ca ?? '-' }}</span>
                                            </div>
                                        </div>
                                        <div class="shift-body">
                                            @if($lich->caLamViec)
                                                <div class="shift-meta">
                                                    <span class="shift-meta-icon"><i class="far fa-clock"></i></span>
                                                    <span>
                                                        {{ \Illuminate\Support\Carbon::parse($lich->caLamViec->gio_bat_dau)->format('H:i') }}
                                                        -
                                                        {{ \Illuminate\Support\Carbon::parse($lich->caLamViec->gio_ket_thuc)->format('H:i') }}
                                                    </span>
                                                </div>
                                            @endif
                                            <div class="shift-meta shift-meta--role">
                                                <span class="shift-meta-icon"><i class="fas fa-user"></i></span>
                                                <span>{{ ($lich->vai_tro_trong_ca ?? 'nhan_vien') === 'truong_ca' ? 'Trưởng ca' : 'Nhân viên' }}</span>
                                            </div>
                                        </div>
                                        <div class="shift-actions">
                                            <a href="{{ route('chia-ca-lam-viec.edit', ['chiaCaLamViec' => $lich, 'week_start' => $selectedWeekDate ?? request('week_start')]) }}" class="btn btn-sm shift-btn-edit">
                                                <i class="fas fa-pen me-1"></i>Sửa
                                            </a>
                                            <form action="{{ route('chia-ca-lam-viec.destroy', $lich) }}" method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn xóa lịch này?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm shift-btn-delete">
                                                    <i class="fas fa-trash me-1"></i>Xóa
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @else
                                @php
                                    $lichDaiDien = $lichTrongNgay[0];
                                    $canhBaoCaDaiDien = $chiTietCanhBaoTheoCa[$date->toDateString() . '|' . $lichDaiDien->id_ca_lam_viec] ?? null;
                                    $coCanhBaoDaiDien = ! empty($canhBaoCaDaiDien) && (($canhBaoCaDaiDien['thieu_truong_ca'] ?? false) || ($canhBaoCaDaiDien['nhieu_truong_ca'] ?? false) || ($canhBaoCaDaiDien['thieu_nhan_su'] ?? false));
                                @endphp
                                <div class="shift-overlay">
                                    <div class="shift-overlay-trigger">
                                        <div class="shift-card {{ $coCanhBaoDaiDien ? 'shift-card--warning' : '' }}">
                                            {!! renderShiftWarning($canhBaoCaDaiDien) !!}
                                            <div class="shift-card-inner">
                                                <div class="shift-header">
                                                    <div class="shift-header-left">
                                                        <span class="shift-badge">{{ $lichDaiDien->caLamViec?->ten_ca ?? '-' }}</span>
                                                    </div>
                                                    <div class="shift-header-right">
                                                        <span class="shift-badge shift-badge--more">+{{ count($lichTrongNgay) - 1 }}</span>
                                                    </div>
                                                </div>
                                                <div class="shift-body">
                                                    @if($lichDaiDien->caLamViec)
                                                        <div class="shift-meta">
                                                            <span class="shift-meta-icon"><i class="far fa-clock"></i></span>
                                                            <span>
                                                                {{ \Illuminate\Support\Carbon::parse($lichDaiDien->caLamViec->gio_bat_dau)->format('H:i') }}
                                                                -
                                                                {{ \Illuminate\Support\Carbon::parse($lichDaiDien->caLamViec->gio_ket_thuc)->format('H:i') }}
                                                            </span>
                                                        </div>
                                                    @endif
                                                    <div class="shift-meta shift-meta--role">
                                                        <span class="shift-meta-icon"><i class="fas fa-user"></i></span>
                                                        <span>{{ ($lichDaiDien->vai_tro_trong_ca ?? 'nhan_vien') === 'truong_ca' ? 'Trưởng ca' : 'Nhân viên' }}</span>
                                                    </div>
                                                </div>
                                                <div class="shift-actions">
                                                    <a href="{{ route('chia-ca-lam-viec.edit', ['chiaCaLamViec' => $lichDaiDien, 'week_start' => $selectedWeekDate ?? request('week_start')]) }}" class="btn btn-sm shift-btn-edit">
                                                        <i class="fas fa-pen me-1"></i>Sửa
                                                    </a>
                                                    <form action="{{ route('chia-ca-lam-viec.destroy', $lichDaiDien) }}" method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn xóa lịch này?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm shift-btn-delete">
                                                            <i class="fas fa-trash me-1"></i>Xóa
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="shift-overlay-panel">
                                        <div class="shift-overlay-toolbar">
                                            <form action="{{ route('chia-ca-lam-viec.destroy-cell') }}" method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn xóa tất cả ca trong ô lịch này?')">
                                                @csrf
                                                @method('DELETE')
                                                <input type="hidden" name="id_nguoi_dung" value="{{ $nguoiDung->id }}">
                                                <input type="hidden" name="ngay" value="{{ $date->toDateString() }}">
                                                <input type="hidden" name="week_start" value="{{ $selectedWeekDate ?? request('week_start', $weekDates[0]->format('Y-m-d')) }}">
                                                <input type="hidden" name="keyword" value="{{ request('keyword') }}">
                                                <input type="hidden" name="id_ca_lam_viec" value="{{ request('id_ca_lam_viec') }}">
                                                <input type="hidden" name="ngay_loc" value="{{ request('ngay') }}">
                                                <button type="submit" class="btn btn-sm btn-outline-danger">Xóa tất cả</button>
                                            </form>
                                        </div>
                                        @foreach($lichTrongNgay as $lich)
                                            @php
                                                $canhBaoCa = $chiTietCanhBaoTheoCa[$date->toDateString() . '|' . $lich->id_ca_lam_viec] ?? null;
                                                $coCanhBao = ! empty($canhBaoCa) && (($canhBaoCa['thieu_truong_ca'] ?? false) || ($canhBaoCa['nhieu_truong_ca'] ?? false) || ($canhBaoCa['thieu_nhan_su'] ?? false));
                                            @endphp
                                            <div class="shift-overlay-item">
                                                <div class="shift-card {{ $coCanhBao ? 'shift-card--warning' : '' }}">
                                                    {!! renderShiftWarning($canhBaoCa) !!}
                                                    <div class="shift-card-inner">
                                                        <div class="shift-header">
                                                            <div class="shift-header-left">
                                                                <span class="shift-badge">{{ $lich->caLamViec?->ten_ca ?? '-' }}</span>
                                                            </div>
                                                        </div>
                                                        <div class="shift-body">
                                                            @if($lich->caLamViec)
                                                                <div class="shift-meta">
                                                                    <span class="shift-meta-icon"><i class="far fa-clock"></i></span>
                                                                    <span>
                                                                        {{ \Illuminate\Support\Carbon::parse($lich->caLamViec->gio_bat_dau)->format('H:i') }}
                                                                        -
                                                                        {{ \Illuminate\Support\Carbon::parse($lich->caLamViec->gio_ket_thuc)->format('H:i') }}
                                                                    </span>
                                                                </div>
                                                            @endif
                                                            <div class="shift-meta shift-meta--role">
                                                                <span class="shift-meta-icon"><i class="fas fa-user"></i></span>
                                                                <span>{{ ($lich->vai_tro_trong_ca ?? 'nhan_vien') === 'truong_ca' ? 'Trưởng ca' : 'Nhân viên' }}</span>
                                                            </div>
                                                        </div>
                                                        <div class="shift-overlay-actions">
                                                            <a href="{{ route('chia-ca-lam-viec.edit', ['chiaCaLamViec' => $lich, 'week_start' => $selectedWeekDate ?? request('week_start')]) }}" class="btn btn-sm shift-btn-edit">
                                                                <i class="fas fa-pen me-1"></i>Sửa
                                                            </a>
                                                            <form action="{{ route('chia-ca-lam-viec.destroy', $lich) }}" method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn xóa lịch này?')">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="btn btn-sm shift-btn-delete">
                                                                    <i class="fas fa-trash me-1"></i>Xóa
                                                                </button>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </td>
                    @endforeach
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center text-muted py-5">Chưa có nhân viên để lập lịch làm việc.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const warningWrappers = Array.from(document.querySelectorAll('.warning-wrapper'));
        const shiftOverlays = Array.from(document.querySelectorAll('.shift-overlay'));

        function closeAllWarnings(exceptWrapper = null) {
            warningWrappers.forEach(function (wrapper) {
                if (wrapper !== exceptWrapper) {
                    wrapper.classList.remove('is-open');
                }
            });
        }

        function closeAllOverlays(exceptOverlay = null) {
            shiftOverlays.forEach(function (overlay) {
                if (overlay !== exceptOverlay) {
                    overlay.classList.remove('is-open');
                }
            });
        }

        warningWrappers.forEach(function (wrapper) {
            const button = wrapper.querySelector('.warning-btn');

            if (!button) {
                return;
            }

            button.addEventListener('click', function (event) {
                event.preventDefault();
                event.stopPropagation();

                const isOpen = wrapper.classList.contains('is-open');
                closeAllWarnings(wrapper);
                wrapper.classList.toggle('is-open', !isOpen);
            });

            wrapper.addEventListener('click', function (event) {
                event.stopPropagation();
            });
        });

        shiftOverlays.forEach(function (overlay) {
            const trigger = overlay.querySelector('.shift-overlay-trigger');
            const panel = overlay.querySelector('.shift-overlay-panel');

            if (!trigger || !panel) {
                return;
            }

            trigger.addEventListener('click', function (event) {
                if (event.target.closest('.warning-wrapper')) {
                    return;
                }

                event.preventDefault();
                event.stopPropagation();

                const isOpen = overlay.classList.contains('is-open');
                closeAllOverlays(overlay);
                overlay.classList.toggle('is-open', !isOpen);
            });

            panel.addEventListener('click', function (event) {
                event.stopPropagation();
            });
        });

        document.addEventListener('click', function () {
            closeAllWarnings();
            closeAllOverlays();
        });
    });
</script>
