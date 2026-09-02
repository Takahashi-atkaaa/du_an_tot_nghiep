@extends('ban_hang.layouts.ban_hang')

@section('title', 'QR đang chờ')

@section('content')
<div class="container-fluid py-3">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <div>
            <h1 class="h2 mb-1">QR đang chờ</h1>
            <div class="text-muted">Danh sách hóa đơn PayOS đang chờ thanh toán.</div>
        </div>

        <div class="d-flex gap-2">
            <a href="{{ route('nhan-vien.ban-hang.don-cho-thanh-toan') }}" class="btn btn-outline-primary">
                <i class="fa-solid fa-rotate me-1"></i> Làm mới
            </a>
            <a href="{{ route('nhan-vien.ban-hang') }}" class="btn btn-secondary">
                <i class="fa-solid fa-arrow-left me-1"></i> Quay lại POS
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Đóng"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Đóng"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ $errors->first() }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Đóng"></button>
        </div>
    @endif

    <div class="alert alert-info border-0 shadow-sm">
        Đổi phương thức thanh toán sẽ hủy QR PayOS hiện tại (nếu còn hiệu lực) và hoàn tất hóa đơn ngay.
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <div class="text-muted small">Tổng đơn chờ</div>
                    <div class="fs-3 fw-bold">{{ number_format($tongHoaDon ?? 0) }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <div class="text-muted small">Có QR PayOS</div>
                    <div class="fs-3 fw-bold text-success">{{ number_format($soHoaDonCoQR ?? 0) }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <div class="text-muted small">Chưa có QR</div>
                    <div class="fs-3 fw-bold text-warning">{{ number_format($soHoaDonChuaQR ?? 0) }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <div class="text-muted small">Tổng tiền chờ</div>
                    <div class="fs-3 fw-bold text-primary">{{ number_format($tongTien ?? 0, 0, ',', '.') }}đ</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-header bg-white">
            <div class="fw-semibold">Danh sách hóa đơn đang chờ thanh toán</div>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Mã HD</th>
                            <th>Khách hàng</th>
                            <th>SĐT</th>
                            <th>Ngày tạo</th>
                            <th class="text-end">Số tiền</th>
                            <th>QR</th>
                            <th class="text-end">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($hoaDons as $item)
                            <tr>
                                <td class="fw-semibold">#{{ $item['hoa_don_id'] }}</td>
                                <td>{{ $item['ten_khach_hang'] ?: 'Khách lẻ' }}</td>
                                <td>{{ $item['so_dien_thoai'] ?: '—' }}</td>
                                <td>{{ \Carbon\Carbon::parse($item['created_at'])->format('d/m/Y H:i') }}</td>
                                <td class="text-end fw-semibold">{{ number_format($item['khach_can_tra'], 0, ',', '.') }}đ</td>
                                <td>
                                    @if($item['has_payos'])
                                        <span class="badge bg-success">Có QR</span>
                                        @if($item['is_expired'])
                                            <span class="badge bg-warning text-dark ms-1">Hết hạn</span>
                                        @endif
                                    @else
                                        <span class="badge bg-secondary">Chưa có QR</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <div class="d-flex flex-wrap justify-content-end gap-2">
                                        @if(!empty($item['checkout_url']) && !$item['is_expired'])
                                            <a href="{{ $item['checkout_url'] }}" target="_blank" class="btn btn-sm btn-success">
                                                <i class="fa-solid fa-qrcode me-1"></i> Mở QR
                                            </a>
                                        @elseif(!empty($item['checkout_url']))
                                            <span class="badge bg-warning text-dark align-self-center">QR đã hết hạn</span>
                                        @else
                                            <span class="text-muted small align-self-center">Không có link QR</span>
                                        @endif

                                        <form method="POST" action="{{ route('nhan-vien.ban-hang.don-cho-thanh-toan.doi-phuong-thuc', $item['hoa_don_id']) }}" class="d-flex flex-column align-items-end gap-1">
                                            @csrf
                                            <input type="hidden" name="phuong_thuc_thanh_toan" value="cash">
                                            <div class="input-group input-group-sm" style="width: 170px;">
                                                <span class="input-group-text">Khách đưa</span>
                                                <input type="number"
                                                       name="tien_khach_dua"
                                                       class="form-control text-end @error('tien_khach_dua') is-invalid @enderror"
                                                       min="{{ (int) $item['khach_can_tra'] }}"
                                                       step="1"
                                                       value="{{ old('tien_khach_dua', (int) $item['khach_can_tra']) }}">
                                            </div>
                                            <button type="submit"
                                                    class="btn btn-sm btn-outline-secondary"
                                                    onclick="return confirm(@json('Đổi hóa đơn '.$item['ma_hoa_don'].' sang Tiền mặt và hoàn tất ngay?'))">
                                                Đổi sang tiền mặt
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    Chưa có hóa đơn nào đang chờ thanh toán.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
