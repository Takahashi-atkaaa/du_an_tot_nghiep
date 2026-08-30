@extends('admin_xem_truoc.layouts.admin')
@section('title', 'Báo cáo kiểm kho')

@section('content')
<div class="container-fluid py-4">
    <div class="kk-page-header">
        <h2><i class="fas fa-chart-bar"></i> Báo cáo kiểm kho</h2>
    </div>

    <!-- Filter -->
    <div class="card mb-3">
        <div class="card-body">
            <form method="GET">
                <div class="row g-3">
                    <div class="col-md-3"><label class="form-label">Từ ngày</label><input type="date" name="tu_ngay" class="form-control" value="{{ $tuNgay }}"></div>
                    <div class="col-md-3"><label class="form-label">Đến ngày</label><input type="date" name="den_ngay" class="form-control" value="{{ $denNgay }}"></div>
                    <div class="col-md-3 d-flex align-items-end"><button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Lọc</button></div>
                </div>
            </form>
        </div>
    </div>

    <!-- Thống kê tổng -->
    <div class="row g-3 mb-3">
        <div class="col-md-2"><div class="kk-stat-card bg-blue"><div class="label">Số phiếu</div><div class="value">{{ $tongHop['tong_phieu'] }}</div></div></div>
        <div class="col-md-2"><div class="kk-stat-card"><div class="label">Tổng SP kiểm</div><div class="value">{{ $tongHop['tong_san_pham_kiem'] }}</div></div></div>
        <div class="col-md-2"><div class="kk-stat-card bg-green"><div class="label">SP đúng</div><div class="value">{{ $tongHop['tong_sp_dung'] }}</div></div></div>
        <div class="col-md-2"><div class="kk-stat-card bg-red"><div class="label">SP thiếu</div><div class="value">{{ $tongHop['tong_sp_thieu'] }}</div></div></div>
        <div class="col-md-2"><div class="kk-stat-card bg-yellow"><div class="label">SP thừa</div><div class="value">{{ $tongHop['tong_sp_thua'] }}</div></div></div>
        <div class="col-md-2"><div class="kk-stat-card"><div class="label">Tổng giá trị lệch</div><div class="value text-danger" style="font-size: 18px;">{{ number_format($tongHop['tong_gia_tri_lech'], 0, ',', '.') }}đ</div></div></div>
    </div>

    <!-- Bảng -->
    <div class="kk-table">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>Mã phiếu</th>
                    <th>Ngày hoàn thành</th>
                    <th>Người kiểm</th>
                    <th class="text-center">Tổng SP</th>
                    <th class="text-center">Đúng</th>
                    <th class="text-center">Thiếu</th>
                    <th class="text-center">Thừa</th>
                    <th class="text-end">Tổng lệch</th>
                    <th class="text-end">Giá trị</th>
                </tr>
            </thead>
            <tbody>
                @forelse($phieus as $p)
                <tr>
                    <td><a href="{{ route('kiem-kho.show', $p->id) }}">{{ $p->ma_kiem_kho }}</a></td>
                    <td>{{ $p->hoan_thanh_luc?->format('d/m/Y H:i') }}</td>
                    <td>{{ $p->nguoiKiem?->ho_ten ?? '-' }}</td>
                    <td class="text-center">{{ $p->tong_so_san_pham }}</td>
                    <td class="text-center text-success">{{ $p->so_sp_dung }}</td>
                    <td class="text-center text-danger">{{ $p->so_sp_thieu }}</td>
                    <td class="text-center text-warning">{{ $p->so_sp_thua }}</td>
                    <td class="text-end">{{ $p->tong_sl_lech }}</td>
                    <td class="text-end">{{ number_format((float)$p->tong_gia_tri_lech, 0, ',', '.') }}đ</td>
                </tr>
                @empty
                <tr><td colspan="9" class="text-center py-5 text-muted">Chưa có dữ liệu báo cáo.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection