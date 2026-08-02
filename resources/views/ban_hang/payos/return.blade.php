@extends('ban_hang.payos.layouts.payos')

@section('title', 'PayOS - Kết quả thanh toán')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card shadow-sm">
                <div class="card-body text-center p-5">
                    @php
                        $trangThai = $giaoDich->trang_thai ?? null;
                        $isSuccess = $trangThai === 'thanh_cong';
                        $isFailed = in_array($trangThai, ['that_bai', 'hoan_tien'], true);
                    @endphp

                    @if ($isSuccess)
                        <div class="mb-4">
                            <i class="fas fa-check-circle text-success" style="font-size: 64px;"></i>
                        </div>
                        <h3 class="mb-3 text-success">Thanh toán thành công</h3>
                        <p class="text-muted mb-4">
                            Giao dịch đã được PayOS xác nhận. Hóa đơn đã được hoàn tất.
                        </p>
                    @elseif ($isFailed)
                        <div class="mb-4">
                            <i class="fas fa-times-circle text-danger" style="font-size: 64px;"></i>
                        </div>
                        <h3 class="mb-3 text-danger">Thanh toán thất bại</h3>
                        <p class="text-muted mb-4">
                            Giao dịch không thành công. Hóa đơn đã được hủy.
                        </p>
                    @else
                        <div class="mb-4">
                            <i class="fas fa-spinner fa-spin text-primary" style="font-size: 64px;"></i>
                        </div>
                        <h3 class="mb-3">Đang xử lý thanh toán</h3>
                        <p class="text-muted mb-4">
                            Hệ thống đang chờ PayOS xác nhận giao dịch. Vui lòng không đóng tab này.
                            Trang sẽ tự động cập nhật khi webhook được gọi về server.
                        </p>
                    @endif

                    @if ($giaoDich)
                        <table class="table table-sm table-borderless mx-auto" style="max-width: 360px;">
                            <tr>
                                <td class="text-muted">Mã hóa đơn:</td>
                                <td class="text-end fw-bold">#{{ $giaoDich->id_hoa_don }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Order code:</td>
                                <td class="text-end fw-bold">{{ $giaoDich->ma_tham_chieu }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Số tiền:</td>
                                <td class="text-end fw-bold text-primary">
                                    {{ number_format($giaoDich->so_tien, 0, ',', '.') }}đ
                                </td>
                            </tr>
                            <tr>
                                <td class="text-muted">Trạng thái:</td>
                                <td class="text-end">
                                    @if ($isSuccess)
                                        <span class="badge bg-success">{{ $giaoDich->trang_thai_doi_tac ?? 'PAID' }}</span>
                                    @elseif ($isFailed)
                                        <span class="badge bg-danger">{{ $giaoDich->trang_thai_doi_tac ?? 'FAILED' }}</span>
                                    @else
                                        <span class="badge bg-warning text-dark">{{ $giaoDich->trang_thai_doi_tac ?? 'Đang chờ' }}</span>
                                    @endif
                                </td>
                            </tr>
                        </table>
                    @else
                        <p class="text-danger">Không tìm thấy giao dịch tương ứng.</p>
                    @endif

                    <div class="d-grid gap-2 mt-4">
                        @if ($isSuccess && $giaoDich)
                            <a href="{{ route('nhan-vien.hoa-don.chi-tiet', $giaoDich->id_hoa_don) }}" class="btn btn-success">
                                <i class="fas fa-file-invoice"></i> Xem hóa đơn
                            </a>
                        @elseif ($isFailed)
                            <a href="{{ route('nhan-vien.ban-hang') }}" class="btn btn-primary">
                                <i class="fas fa-arrow-left"></i> Quay lại POS
                            </a>
                        @else
                            <a href="{{ route('nhan-vien.ban-hang') }}" class="btn btn-primary">
                                <i class="fas fa-arrow-left"></i> Quay lại POS
                            </a>
                            @if ($giaoDich)
                                <a href="{{ route('nhan-vien.hoa-don.chi-tiet', $giaoDich->id_hoa_don) }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-file-invoice"></i> Xem hóa đơn
                                </a>
                            @endif
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@if (!$isSuccess && !$isFailed)
<script>
    setTimeout(() => location.reload(), 5000);
</script>
@endif
@endsection
