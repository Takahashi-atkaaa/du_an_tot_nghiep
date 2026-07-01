@extends('nhan_vien_view.layouts.nhan_vien')

@section('title', 'Hóa đơn')

@section('content')
<div class="d-flex justify-content-between align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Danh sách Hóa đơn</h1>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

@if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif

<div class="card">
    <div class="card-header bg-white">
        <form method="GET" action="{{ route('nhan-vien.hoa-don') }}">
            <div class="row g-2">
                <div class="col-md-4">
                    <input type="text" name="q" class="form-control"
                           placeholder="Tìm theo mã hóa đơn..."
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
                    <button class="btn btn-outline-success w-100">
                        <i class="fas fa-search me-2"></i>Tìm
                    </button>
                </div>
            </div>
        </form>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-nv mb-0 align-middle">
                <thead>
                    <tr>
                        <th>Mã HD</th>
                        <th>Ngày tạo</th>
                        <th>Khách hàng</th>
                        <th>Nhân viên</th>
                        <th>Tổng tiền</th>
                        <th>Điểm nhận</th>
                        <th>Thanh toán</th>
                        <th>Trạng thái</th>
                        <th class="text-center">Thao tác</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($hoaDons as $hoaDon)
                        <tr>
                            <td><strong>#HD{{ str_pad($hoaDon->id, 4, '0', STR_PAD_LEFT) }}</strong></td>

                            <td>{{ \Carbon\Carbon::parse($hoaDon->created_at)->format('d/m/Y H:i') }}</td>

                            <td>{{ $hoaDon->ten_khach_hang ?? 'Khách lẻ' }}</td>

                            <td>{{ $hoaDon->ten_nhan_vien ?? 'Nhân viên' }}</td>

                            <td>
                                <strong>
                                    {{ number_format($hoaDon->khach_can_tra, 0, ',', '.') }}đ
                                </strong>
                            </td>

                            <td>
                                <span class="badge bg-info">
                                    +{{ $hoaDon->diem_thu_duoc ?? 0 }}
                                </span>
                            </td>


                            <td>@php
                                 $pttt = [
                                          'cash' => 'Tiền mặt',
                                         'transfer' => 'Chuyển khoản',
                                         'card' => 'Quẹt thẻ',
                                        'tien_mat' => 'Tiền mặt',
                                         'chuyen_khoan' => 'Chuyển khoản',
                                    ];
                            @endphp

                            {{ $pttt[$hoaDon->phuong_thuc_thanh_toan] ?? $hoaDon->phuong_thuc_thanh_toan }}</td>

                            <td>
                                @if($hoaDon->trang_thai === 'Đã hủy')
                                    <span class="badge bg-danger">Đã hủy</span>
                                @else
                                    <span class="badge bg-success">{{ $hoaDon->trang_thai }}</span>
                                @endif
                            </td>

                            <td class="text-center">
                                <a href="{{ route('nhan-vien.hoa-don.chi-tiet', $hoaDon->id) }}"
                                   class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-eye"></i>
                                </a>

                                <a href="{{ route('nhan-vien.hoa-don.in', $hoaDon->id) }}"
                                   target="_blank"
                                   class="btn btn-sm btn-outline-success">
                                    <i class="fas fa-print"></i>
                                </a>

                                @if($hoaDon->trang_thai !== 'Đã hủy')
                                    <form action="{{ route('nhan-vien.hoa-don.huy', $hoaDon->id) }}"
                                          method="POST"
                                          class="d-inline"
                                          onsubmit="return confirm('Bạn có chắc muốn hủy hóa đơn này không? Tồn kho sẽ được hoàn lại.')">
                                        @csrf
                                        <button class="btn btn-sm btn-outline-danger">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-4 text-muted">
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