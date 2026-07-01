@extends('admin_xem_truoc.layouts.admin')

@section('title', 'Trả hàng hóa đơn')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0">
        Trả hàng hóa đơn #HD{{ str_pad($hoaDon->id, 4, '0', STR_PAD_LEFT) }}
    </h4>

    <a href="{{ route('admin.hoa-don.show', $hoaDon->id) }}" class="btn btn-secondary">
        Quay lại
    </a>
</div>

@if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif

<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ route('admin.hoa-don.xu-ly-tra-hang', $hoaDon->id) }}">
            @csrf

            <table class="table table-bordered align-middle">
                <thead class="table-warning">
                    <tr>
                        <th>Sản phẩm</th>
                        <th>Mã vạch</th>
                        <th class="text-center">Đã mua</th>
                        <th class="text-center">Đã trả</th>
                        <th class="text-center">Còn được trả</th>
                        <th class="text-end">Giá bán</th>
                        <th class="text-center">Số lượng trả</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach($chiTiet as $item)
                        @php
                            $soLuongDaTra = (int) ($daTra[$item->id_san_pham] ?? 0);
                            $soLuongConDuocTra = max(0, $item->so_luong - $soLuongDaTra);
                        @endphp

                        <tr>
                            <td>{{ $item->ten_san_pham }}</td>
                            <td>{{ $item->ma_vach }}</td>
                            <td class="text-center">{{ $item->so_luong }}</td>
                            <td class="text-center text-danger">{{ $soLuongDaTra }}</td>
                            <td class="text-center text-success fw-bold">{{ $soLuongConDuocTra }}</td>
                            <td class="text-end">{{ number_format($item->gia_ban, 0, ',', '.') }}đ</td>
                            <td class="text-center">
                                <input type="number"
                                       name="so_luong_tra[{{ $item->id }}]"
                                       class="form-control text-center"
                                       value="0"
                                       min="0"
                                       max="{{ $soLuongConDuocTra }}"
                                       {{ $soLuongConDuocTra <= 0 ? 'disabled' : '' }}>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="mb-3">
                <label class="form-label">Lý do trả hàng</label>
                <textarea name="ly_do" class="form-control" rows="3"
                          placeholder="Ví dụ: khách đổi ý, sản phẩm lỗi..."></textarea>
            </div>

            <button class="btn btn-warning"
                    onclick="return confirm('Xác nhận xử lý trả hàng? Tồn kho sẽ được cộng lại.')">
                <i class="fas fa-undo me-1"></i> Xác nhận trả hàng
            </button>
        </form>
    </div>
</div>
@endsection