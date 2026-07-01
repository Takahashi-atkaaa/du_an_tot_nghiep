@extends('admin_xem_truoc.layouts.admin')

@section('title', 'Quản lý Hóa đơn - SmartMart')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1">Quản lý Hóa đơn</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('admin/dashboard') }}">Admin</a></li>
                <li class="breadcrumb-item active">Hóa đơn</li>
            </ol>
        </nav>
    </div>
</div>

<div class="card table-admin mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('admin.hoa-don.index') }}">
            <div class="row g-3">
                <div class="col-md-3">
                    <input type="text" name="q" class="form-control"
                           placeholder="Mã HD / khách hàng / nhân viên"
                           value="{{ request('q') }}">
                </div>

                <div class="col-md-3">
                    <input type="date" name="ngay" class="form-control"
                           value="{{ request('ngay') }}">
                </div>

                <div class="col-md-3">
                    <select name="trang_thai" class="form-select">
                        <option value="">Tất cả trạng thái</option>
                        <option value="Hoàn thành" {{ request('trang_thai') == 'Hoàn thành' ? 'selected' : '' }}>Hoàn thành</option>
                        <option value="Đã hủy" {{ request('trang_thai') == 'Đã hủy' ? 'selected' : '' }}>Đã hủy</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <button class="btn btn-primary w-100">
                        <i class="fas fa-filter me-2"></i>Lọc
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="card table-admin">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead>
                    <tr>
                        <th>Mã HD</th>
                        <th>Khách hàng</th>
                        <th>Nhân viên</th>
                        <th>Ngày tạo</th>
                        <th>Tổng tiền</th>
                        <th>Giảm giá</th>
                        <th>Thanh toán</th>
                        <th>PTTT</th>
                        <th>Trạng thái</th>
                        <th style="width: 120px;">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($hoaDons as $hoaDon)
                        <tr>
                            <td>
                                <strong class="text-primary">
                                    #HD{{ str_pad($hoaDon->id, 4, '0', STR_PAD_LEFT) }}
                                </strong>
                            </td>

                            <td>{{ $hoaDon->ten_khach_hang ?? 'Khách lẻ' }}</td>
                            <td>{{ $hoaDon->ten_nhan_vien ?? '---' }}</td>

                            <td>
                                {{ \Carbon\Carbon::parse($hoaDon->created_at)->format('d/m/Y H:i') }}
                            </td>

                            <td>
                                <strong>{{ number_format($hoaDon->tong_tien_hang, 0, ',', '.') }} đ</strong>
                            </td>

                            <td>
                                @if($hoaDon->tien_giam_gia > 0)
                                    <span class="text-danger">-{{ number_format($hoaDon->tien_giam_gia, 0, ',', '.') }} đ</span>
                                @else
                                    <span class="text-muted">-0 đ</span>
                                @endif
                            </td>

                            <td>
                                <strong class="text-success">
                                    {{ number_format($hoaDon->khach_can_tra, 0, ',', '.') }} đ
                                </strong>
                            </td>

                            <td>
    @php
        $pttt = [
            'cash' => 'Tiền mặt',
            'tien_mat' => 'Tiền mặt',
            'Tiền mặt' => 'Tiền mặt',

            'transfer' => 'Chuyển khoản',
            'chuyen_khoan' => 'Chuyển khoản',
            'Chuyển khoản' => 'Chuyển khoản',

            'card' => 'Quẹt thẻ',
            'quet_the' => 'Quẹt thẻ',
            'Quẹt thẻ' => 'Quẹt thẻ',
        ];

        $tenPttt = $pttt[$hoaDon->phuong_thuc_thanh_toan] ?? $hoaDon->phuong_thuc_thanh_toan;
    @endphp

    <span class="badge bg-light text-dark border">
        {{ $tenPttt }}
    </span>
</td>

                            <td>
                                @if($hoaDon->trang_thai === 'Đã hủy')
                                    <span class="badge bg-danger">Đã hủy</span>
                                @else
                                    <span class="badge bg-success">{{ $hoaDon->trang_thai }}</span>
                                @endif
                            </td>

                            <td>
                                <a href="{{ route('admin.hoa-don.show', $hoaDon->id) }}"
                                   class="btn btn-sm btn-outline-primary btn-action"
                                   title="Xem">
                                    <i class="fas fa-eye"></i>
                                </a>

                                <a href="{{ route('admin.hoa-don.show', $hoaDon->id) }}"
                                   class="btn btn-sm btn-outline-secondary btn-action"
                                   title="In">
                                    <i class="fas fa-print"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center py-4 text-muted">
                                Chưa có hóa đơn nào
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="card-footer bg-white">
        {{ $hoaDons->links() }}
    </div>
</div>
@endsection