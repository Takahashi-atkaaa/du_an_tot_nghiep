@extends('admin_xem_truoc.layouts.admin')

@section('title', 'Phân chia ca làm việc - SmartMart')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
    <div>
        <h4 class="fw-bold mb-1">Lịch làm việc theo tuần</h4>
        <div class="text-muted">
            Từ {{ $weekStart->format('d/m/Y') }} đến {{ $weekStart->copy()->addDays(6)->format('d/m/Y') }}
        </div>
    </div>

    <div class="d-flex flex-wrap align-items-center gap-2">
        <form method="GET" action="{{ route('chia-ca-lam-viec.index') }}" class="d-flex align-items-center gap-2">
            <input
                type="date"
                name="week_start"
                value="{{ $selectedWeekDate }}"
                class="form-control form-control-sm"
                style="width: 185px;"
            >

            <button type="submit" class="btn btn-sm btn-outline-secondary text-nowrap px-3">
                Xem tuần
            </button>
        </form>

        <a href="{{ route('chia-ca-lam-viec.export', ['week_start' => $selectedWeekDate]) }}"
           class="btn btn-sm btn-success text-nowrap px-3">
            <i class="fas fa-file-export me-1"></i>Export
        </a>

        <a href="{{ route('chia-ca-lam-viec.create', ['week_start' => $selectedWeekDate]) }}"
           class="btn btn-sm btn-primary text-nowrap px-3">
            <i class="fas fa-file-import me-1"></i>Import
        </a>

        <a href="{{ route('ca-lam-viec.index') }}" class="btn btn-sm btn-outline-secondary text-nowrap">
            <i class="fas fa-arrow-left me-1"></i>Quay lại
        </a>
    </div>
</div>

<div class="card table-admin mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('chia-ca-lam-viec.index') }}" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label">Tên nhân viên</label>
                <input
                    type="text"
                    name="keyword"
                    value="{{ $keyword }}"
                    class="form-control"
                    placeholder="Nhập tên nhân viên"
                >
            </div>

            <div class="col-md-3">
                <label class="form-label">Ca làm</label>
                <select name="id_ca_lam_viec" class="form-select">
                    <option value="">Tất cả ca làm</option>
                    @foreach($caLamViecs as $caLamViec)
                        <option value="{{ $caLamViec->id }}" @selected((string) $caLamViecId === (string) $caLamViec->id)>
                            {{ $caLamViec->ten_ca }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label">Ngày làm</label>
                <input
                    type="date"
                    name="ngay"
                    value="{{ $ngay }}"
                    class="form-control"
                >
            </div>

            <div class="col-md-3">
                <label class="form-label">Tuần hiển thị</label>
                <input
                    type="date"
                    name="week_start"
                    value="{{ $selectedWeekDate }}"
                    class="form-control"
                >
            </div>

            <div class="col-12 d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-filter me-2"></i>Lọc lịch làm việc
                </button>
                <a href="{{ route('chia-ca-lam-viec.index') }}" class="btn btn-outline-secondary">
                    Xóa bộ lọc
                </a>
            </div>
        </form>
    </div>
</div>

<div class="card table-admin">
    <div class="card-body p-0">
        @include('admin_xem_truoc.chia-ca-lam-viec.bang-lich-lam-viec', [
            'nguoiDungs' => $nguoiDungs,
            'selectedWeekDate' => $selectedWeekDate,
            'weekDates' => $weekDates,
            'maTranLich' => $maTranLich,
            'chiTietCanhBaoTheoCa' => $chiTietCanhBaoTheoCa ?? [],
        ])
    </div>
</div>
@endsection
