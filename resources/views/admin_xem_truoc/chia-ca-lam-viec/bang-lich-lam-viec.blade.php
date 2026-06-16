<style>
    .lich-cell {
        min-width: 170px;
        vertical-align: top;
        position: relative;
    }

    .ca-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 7px 12px;
        border-radius: 999px;
        background: #eef4ff;
        color: #1746a2;
        font-weight: 600;
        font-size: 14px;
        border: 1px solid #cddcff;
    }

    .ca-time {
        margin-top: 8px;
        font-size: 13px;
        color: #667085;
    }

    .ca-role {
        margin-top: 6px;
        font-size: 12px;
        font-weight: 600;
        color: #475467;
    }

    .ca-single-card {
        position: relative;
        border: 1px solid #d9e2f1;
        border-radius: 14px;
        background: #f8fbff;
        padding: 12px;
    }

    .ca-single-actions {
        display: flex;
        gap: 8px;
        margin-top: 10px;
    }

    .ca-overlay {
        position: relative;
    }

    .ca-overlay summary {
        list-style: none;
        cursor: pointer;
    }

    .ca-overlay summary::-webkit-details-marker {
        display: none;
    }

    .ca-overlay-panel {
        position: absolute;
        top: calc(100% + 10px);
        left: 0;
        z-index: 20;
        width: 280px;
        max-height: 320px;
        overflow-y: auto;
        padding: 12px;
        border: 1px solid #dbe2ea;
        border-radius: 14px;
        background: #ffffff;
        box-shadow: 0 18px 40px rgba(15, 23, 42, 0.14);
    }

    .ca-overlay-toolbar {
        display: flex;
        justify-content: flex-end;
        margin-bottom: 10px;
        padding-bottom: 10px;
        border-bottom: 1px solid #e5e7eb;
    }

    .ca-overlay-card + .ca-overlay-card {
        margin-top: 10px;
    }

    .ca-overlay-card {
        position: relative;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        background: #f8fafc;
        padding: 12px;
    }

    .ca-warning-card {
        border-color: #ef4444;
        background: #fff5f5;
        box-shadow: inset 0 0 0 1px rgba(239, 68, 68, 0.12);
    }

    .ca-warning-toggle {
        position: absolute;
        top: 10px;
        right: 10px;
        z-index: 5;
    }

    .ca-warning-toggle summary {
        list-style: none;
        cursor: pointer;
    }

    .ca-warning-toggle summary::-webkit-details-marker {
        display: none;
    }

    .ca-warning-icon {
        width: 28px;
        height: 28px;
        border-radius: 999px;
        border: 1px solid #fca5a5;
        background: #fff;
        color: #dc2626;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 13px;
    }

    .ca-warning-menu {
        position: absolute;
        top: calc(100% + 8px);
        right: 0;
        width: 220px;
        padding: 10px;
        border: 1px solid #fecaca;
        border-radius: 12px;
        background: #fff;
        box-shadow: 0 16px 32px rgba(15, 23, 42, 0.14);
    }

    .ca-warning-item + .ca-warning-item {
        margin-top: 8px;
    }

    .ca-warning-item {
        display: flex;
        align-items: flex-start;
        gap: 8px;
        font-size: 12px;
        font-weight: 600;
        color: #b42318;
        line-height: 1.4;
    }

    .ca-overlay-actions {
        display: flex;
        gap: 8px;
        margin-top: 10px;
    }

    .ca-empty {
        font-size: 14px;
        color: #6b7280;
    }
</style>

<div class="table-responsive">
    <table class="table table-bordered align-middle mb-0">
        <thead class="table-light">
            <tr>
                <th style="min-width: 220px;">Nhân viên</th>
                @foreach($weekDates as $date)
                    <th style="min-width: 170px;">
                        @switch($date->dayOfWeekIso)
                            @case(1)
                                Thứ 2
                                @break
                            @case(2)
                                Thứ 3
                                @break
                            @case(3)
                                Thứ 4
                                @break
                            @case(4)
                                Thứ 5
                                @break
                            @case(5)
                                Thứ 6
                                @break
                            @case(6)
                                Thứ 7
                                @break
                            @default
                                Chủ nhật
                        @endswitch
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
                        <div class="small text-muted">{{ $nguoiDung->vai_tro }}</div>
                    </td>
                    @foreach($weekDates as $date)
                        @php
                            $lichTrongNgay = $maTranLich[$nguoiDung->id][$date->toDateString()] ?? [];
                        @endphp
                        <td class="lich-cell">
                            @if(count($lichTrongNgay) === 1)
                                @php
                                    $lich = $lichTrongNgay[0];
                                    $canhBaoCa = $chiTietCanhBaoTheoCa[$date->toDateString() . '|' . $lich->id_ca_lam_viec] ?? null;
                                    $coCanhBao = !empty($canhBaoCa) && (($canhBaoCa['thieu_truong_ca'] ?? false) || ($canhBaoCa['nhieu_truong_ca'] ?? false) || ($canhBaoCa['thieu_nhan_su'] ?? false));
                                @endphp
                                <div class="ca-single-card {{ $coCanhBao ? 'ca-warning-card' : '' }}">
                                    @if($coCanhBao)
                                        <details class="ca-warning-toggle">
                                            <summary>
                                                <span class="ca-warning-icon">
                                                    <i class="fas fa-exclamation"></i>
                                                </span>
                                            </summary>
                                            <div class="ca-warning-menu">
                                                @if($canhBaoCa['nhieu_truong_ca'] ?? false)
                                                    <div class="ca-warning-item">
                                                        <i class="fas fa-user-shield"></i>
                                                        <span>Có {{ $canhBaoCa['so_truong_ca'] }} trưởng ca</span>
                                                    </div>
                                                @endif
                                                @if($canhBaoCa['thieu_truong_ca'] ?? false)
                                                    <div class="ca-warning-item">
                                                        <i class="fas fa-user-shield"></i>
                                                        <span>Chưa có trưởng ca</span>
                                                    </div>
                                                @endif
                                                @if($canhBaoCa['thieu_nhan_su'] ?? false)
                                                    <div class="ca-warning-item">
                                                        <i class="fas fa-users"></i>
                                                        <span>Thiếu nhân sự ({{ $canhBaoCa['so_nhan_vien'] }}/{{ $canhBaoCa['so_toi_thieu'] }})</span>
                                                    </div>
                                                @endif
                                            </div>
                                        </details>
                                    @endif

                                    <div class="ca-badge">
                                        {{ $lich->caLamViec?->ten_ca ?? '-' }}
                                    </div>
                                    @if($lich->caLamViec)
                                        <div class="ca-time">
                                            {{ \Illuminate\Support\Carbon::parse($lich->caLamViec->gio_bat_dau)->format('H:i') }}
                                            -
                                            {{ \Illuminate\Support\Carbon::parse($lich->caLamViec->gio_ket_thuc)->format('H:i') }}
                                        </div>
                                    @endif
                                    <div class="ca-role">
                                        {{ ($lich->vai_tro_trong_ca ?? 'nhan_vien') === 'truong_ca' ? 'Trưởng ca' : 'Nhân viên' }}
                                    </div>
                                    <div class="ca-single-actions">
                                        <a href="{{ route('chia-ca-lam-viec.edit', $lich) }}" class="btn btn-sm btn-outline-primary">
                                            Sửa
                                        </a>
                                        <form action="{{ route('chia-ca-lam-viec.destroy', $lich) }}" method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn xóa lịch này?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">Xóa</button>
                                        </form>
                                    </div>
                                </div>
                            @elseif(count($lichTrongNgay) >= 2)
                                <details class="ca-overlay">
                                    <summary>
                                        <span class="ca-badge">+{{ count($lichTrongNgay) }} ca</span>
                                    </summary>
                                    <div class="ca-overlay-panel">
                                        <div class="ca-overlay-toolbar">
                                            <form
                                                action="{{ route('chia-ca-lam-viec.destroy-cell') }}"
                                                method="POST"
                                                onsubmit="return confirm('Bạn có chắc chắn muốn xóa tất cả ca trong ô lịch này?')"
                                            >
                                                @csrf
                                                @method('DELETE')
                                                <input type="hidden" name="id_nguoi_dung" value="{{ $nguoiDung->id }}">
                                                <input type="hidden" name="ngay" value="{{ $date->toDateString() }}">
                                                <input type="hidden" name="week_start" value="{{ request('week_start', $weekDates[0]->format('Y-m-d')) }}">
                                                <input type="hidden" name="keyword" value="{{ request('keyword') }}">
                                                <input type="hidden" name="id_ca_lam_viec" value="{{ request('id_ca_lam_viec') }}">
                                                <input type="hidden" name="ngay_loc" value="{{ request('ngay') }}">
                                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                                    Xóa tất cả
                                                </button>
                                            </form>
                                        </div>
                                        @foreach($lichTrongNgay as $lich)
                                            @php
                                                $canhBaoCa = $chiTietCanhBaoTheoCa[$date->toDateString() . '|' . $lich->id_ca_lam_viec] ?? null;
                                                $coCanhBao = !empty($canhBaoCa) && (($canhBaoCa['thieu_truong_ca'] ?? false) || ($canhBaoCa['nhieu_truong_ca'] ?? false) || ($canhBaoCa['thieu_nhan_su'] ?? false));
                                            @endphp
                                            <div class="ca-overlay-card {{ $coCanhBao ? 'ca-warning-card' : '' }}">
                                                @if($coCanhBao)
                                                    <details class="ca-warning-toggle">
                                                        <summary>
                                                            <span class="ca-warning-icon">
                                                                <i class="fas fa-exclamation"></i>
                                                            </span>
                                                        </summary>
                                                        <div class="ca-warning-menu">
                                                            @if($canhBaoCa['nhieu_truong_ca'] ?? false)
                                                                <div class="ca-warning-item">
                                                                    <i class="fas fa-user-shield"></i>
                                                                    <span>Có {{ $canhBaoCa['so_truong_ca'] }} trưởng ca</span>
                                                                </div>
                                                            @endif
                                                            @if($canhBaoCa['thieu_truong_ca'] ?? false)
                                                                <div class="ca-warning-item">
                                                                    <i class="fas fa-user-shield"></i>
                                                                    <span>Chưa có trưởng ca</span>
                                                                </div>
                                                            @endif
                                                            @if($canhBaoCa['thieu_nhan_su'] ?? false)
                                                                <div class="ca-warning-item">
                                                                    <i class="fas fa-users"></i>
                                                                    <span>Thiếu nhân sự ({{ $canhBaoCa['so_nhan_vien'] }}/{{ $canhBaoCa['so_toi_thieu'] }})</span>
                                                                </div>
                                                            @endif
                                                        </div>
                                                    </details>
                                                @endif

                                                <div class="fw-semibold">{{ $lich->caLamViec?->ten_ca ?? '-' }}</div>
                                                @if($lich->caLamViec)
                                                    <div class="small text-muted">
                                                        {{ \Illuminate\Support\Carbon::parse($lich->caLamViec->gio_bat_dau)->format('H:i') }}
                                                        -
                                                        {{ \Illuminate\Support\Carbon::parse($lich->caLamViec->gio_ket_thuc)->format('H:i') }}
                                                    </div>
                                                @endif
                                                <div class="ca-role">
                                                    {{ ($lich->vai_tro_trong_ca ?? 'nhan_vien') === 'truong_ca' ? 'Trưởng ca' : 'Nhân viên' }}
                                                </div>
                                                <div class="ca-overlay-actions">
                                                    <a href="{{ route('chia-ca-lam-viec.edit', $lich) }}" class="btn btn-sm btn-outline-primary">
                                                        Sửa
                                                    </a>
                                                    <form action="{{ route('chia-ca-lam-viec.destroy', $lich) }}" method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn xóa lịch này?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-outline-danger">Xóa</button>
                                                    </form>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </details>
                            @else
                                <span class="ca-empty">Chưa phân ca</span>
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
