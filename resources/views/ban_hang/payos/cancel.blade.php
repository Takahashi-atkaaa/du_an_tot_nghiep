@extends('ban_hang.payos.layouts.payos')

@section('title', 'PayOS - Đã hủy thanh toán')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card shadow-sm">
                <div class="card-body text-center p-5">
                    <div class="mb-4">
                        <i class="fas fa-times-circle text-danger" style="font-size: 64px;"></i>
                    </div>
                    <h3 class="mb-3">Đã hủy thanh toán</h3>
                    <p class="text-muted mb-4">
                        Giao dịch PayOS đã được hủy. Hóa đơn đã được đánh dấu "Đã hủy" trong hệ thống.
                    </p>

                    @if ($giaoDich)
                        <table class="table table-sm table-borderless mx-auto" style="max-width: 360px;">
                            <tr>
                                <td class="text-muted">Mã hóa đơn:</td>
                                <td class="text-end fw-bold">#{{ $giaoDich->id_hoa_don }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Số tiền:</td>
                                <td class="text-end fw-bold">
                                    {{ number_format($giaoDich->so_tien, 0, ',', '.') }}đ
                                </td>
                            </tr>
                            <tr>
                                <td class="text-muted">Trạng thái:</td>
                                <td class="text-end">
                                    <span class="badge bg-danger">{{ $giaoDich->trang_thai }}</span>
                                </td>
                            </tr>
                        </table>
                    @endif

                    <div class="d-grid gap-2 mt-4">
                        <a href="{{ route('nhan-vien.ban-hang') }}" class="btn btn-primary">
                            <i class="fas fa-arrow-left"></i> Quay lại POS
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
