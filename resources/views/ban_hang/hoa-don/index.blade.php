@extends('ban_hang.layouts.ban_hang')

@section('title', 'Hóa đơn')

@section('content')
<div class="d-flex justify-content-between align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Danh sách hóa đơn</h1>
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
                        <th>Ca làm việc</th>
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
                            <td>
                                {{ $hoaDon->ten_khach_hang ?? 'Khách lẻ' }}
                                @if(($hoaDon->so_lan_doi_tra ?? 0) > 0)
                                    <div class="small text-warning fw-semibold mt-1">
                                        Có {{ $hoaDon->so_lan_doi_tra }} lần đổi/trả
                                    </div>
                                @endif
                            </td>
                            <td>{{ $hoaDon->ten_nhan_vien ?? 'Nhân viên' }}</td>
                            <td>
                                @if($hoaDon->ten_ca)
                                    <span class="badge bg-success">{{ $hoaDon->ten_ca }}</span>
                                    <div class="small text-muted mt-1">
                                        {{ \Carbon\Carbon::parse($hoaDon->gio_bat_dau)->format('H:i') }}
                                        -
                                        {{ \Carbon\Carbon::parse($hoaDon->gio_ket_thuc)->format('H:i') }}
                                    </div>
                                @else
                                    <span class="text-muted">Chưa có ca</span>
                                @endif
                            </td>
                            <td><strong>{{ number_format($hoaDon->khach_can_tra, 0, ',', '.') }}đ</strong></td>
                            <td>
                                <span class="badge bg-info">+{{ $hoaDon->diem_thu_duoc ?? 0 }}</span>
                            </td>
                            <td>
                                @php
                                    $pttt = [
                                        'cash' => 'Tiền mặt',
                                        'transfer' => 'Chuyển khoản',
                                        'tien_mat' => 'Tiền mặt',
                                        'chuyen_khoan' => 'Chuyển khoản',
                                    ];
                                @endphp
                                {{ $pttt[$hoaDon->phuong_thuc_thanh_toan] ?? $hoaDon->phuong_thuc_thanh_toan }}
                            </td>
                            <td>
                                @if($hoaDon->trang_thai === 'Đã hủy')
                                    <span class="badge bg-danger">Đã hủy</span>
                                @else
                                    <span class="badge bg-success">{{ $hoaDon->trang_thai }}</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <a href="{{ route('nhan-vien.hoa-don.chi-tiet', $hoaDon->id) }}"
                                   class="btn btn-sm btn-outline-primary"
                                   title="Xem hóa đơn">
                                    <i class="fas fa-eye"></i>
                                </a>

                                <a href="{{ route('nhan-vien.hoa-don.in', $hoaDon->id) }}"
                                   target="_blank"
                                   class="btn btn-sm btn-outline-success"
                                   title="In hóa đơn">
                                    <i class="fas fa-print"></i>
                                </a>

                                @if(($hoaDon->so_lan_doi_tra ?? 0) > 0)
                                    <a href="{{ route('nhan-vien.hoa-don.chi-tiet', $hoaDon->id) }}#chi-tiet-doi-tra"
                                       class="btn btn-sm btn-outline-warning"
                                       title="Chi tiết đổi/trả">
                                        <i class="fas fa-rotate-left"></i>
                                    </a>
                                @endif
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
