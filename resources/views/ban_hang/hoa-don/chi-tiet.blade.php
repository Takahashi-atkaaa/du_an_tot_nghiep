@extends('ban_hang.layouts.ban_hang')

@section('title', 'Chi tiết hóa đơn')

@section('content')
@if(($auto_print ?? false))
    <script>
        window.addEventListener('load', function () {
            setTimeout(function () {
                window.print();
            }, 300);
        });
    </script>
@endif

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<div class="d-flex justify-content-between align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Chi tiết hóa đơn #HD{{ str_pad($hoaDon->id, 4, '0', STR_PAD_LEFT) }}</h1>

    <div>
        <a href="{{ route('nhan-vien.hoa-don') }}" class="btn btn-secondary">Quay lại</a>
        <button onclick="window.print()" class="btn btn-success">
            <i class="fas fa-print me-1"></i> In hóa đơn
        </button>
        <a href="{{ route('nhan-vien.hoa-don.doi-tra', $hoaDon->id) }}" class="btn btn-warning">
            <i class="fas fa-undo me-1"></i> Đổi / Trả hàng
        </a>
        @if(isset($lichSuDoiTra) && $lichSuDoiTra->count())
            <button type="button" class="btn btn-outline-warning" data-bs-toggle="modal" data-bs-target="#chiTietDoiTraModal">
                <i class="fas fa-rotate-left me-1"></i> Chi tiết đổi/trả
            </button>
        @endif
    </div>
</div>

<div class="card" id="invoiceArea">
    <div class="card-body">
        <div class="text-center mb-4">
            <h3 class="fw-bold text-success">SMART MART</h3>
            <p class="mb-1">HÓA ĐƠN BÁN HÀNG</p>
            <strong>#HD{{ str_pad($hoaDon->id, 4, '0', STR_PAD_LEFT) }}</strong>
        </div>

        <hr>

        <div class="row mb-3">
            <div class="col-md-6">
                <p><strong>Ngày tạo:</strong> {{ \Carbon\Carbon::parse($hoaDon->created_at)->format('d/m/Y H:i') }}</p>
                <p><strong>Nhân viên:</strong> {{ $hoaDon->ten_nhan_vien ?? 'Nhân viên' }}</p>
            </div>
            <div class="col-md-6">
                <p><strong>Khách hàng:</strong> {{ $hoaDon->ten_khach_hang ?? 'Khách lẻ' }}</p>
                <p><strong>Thanh toán:</strong> {{ $hoaDon->phuong_thuc_thanh_toan }}</p>
                <p><strong>Trạng thái:</strong> {{ $hoaDon->trang_thai }}</p>
            </div>
        </div>

        <table class="table table-bordered align-middle">
            <thead class="table-success">
                <tr>
                    <th>Sản phẩm</th>
                    <th>Mã vạch</th>
                    <th class="text-center">SL</th>
                    <th class="text-end">Giá bán</th>
                    <th class="text-end">Thành tiền</th>
                    <th class="text-center">Đổi / Trả</th>
                </tr>
            </thead>
            <tbody>
                @foreach($chiTiet as $item)
                    <tr>
                        <td>

    <div class="fw-bold">
        {{ $item->ten_san_pham }}
    </div>

    @if(!empty($item->ten_bien_the))

        <div class="small text-muted mt-1">
            <i class="fas fa-layer-group me-1"></i>

            Biến thể:
            {{ $item->ten_bien_the }}
        </div>

    @endif

    @if(!empty($item->thuoc_tinh_hien_thi))

        <div class="small text-muted mt-1">

            <i class="fas fa-tags me-1"></i>

            {{ implode(
                ' • ',
                $item->thuoc_tinh_hien_thi
            ) }}

        </div>

    @endif

</td>
                        <td>{{ $item->ma_vach }}</td>
                        <td class="text-center">{{ $item->so_luong }}</td>
                        <td class="text-end">{{ number_format($item->gia_ban, 0, ',', '.') }}đ</td>
                        <td class="text-end">{{ number_format($item->thanh_tien, 0, ',', '.') }}đ</td>
                        <td class="text-center">
                            @if(($item->tong_da_tra ?? 0) > 0)
                                <span class="badge bg-danger">Đã trả {{ $item->tong_da_tra }}/{{ $item->so_luong }}</span>
                            @endif
                            @if(($item->tong_da_doi ?? 0) > 0)
                                <span class="badge bg-warning text-dark">Đã đổi {{ $item->tong_da_doi }}/{{ $item->so_luong }}</span>
                            @endif
                            @if(($item->tong_da_doi_tra ?? 0) === 0)
                                <span class="text-muted">Chưa phát sinh</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        {{-- ====================================== --}}
{{-- TỔNG KẾT THANH TOÁN --}}
{{-- ====================================== --}}

<div class="row mt-4">

    <div class="col-md-7">

        {{-- Khuyến mãi đã áp dụng --}}
        @if(isset($khuyenMaiDaApDung) && $khuyenMaiDaApDung->count())

            <div class="border rounded p-3">

                <div class="fw-bold mb-2">
                    <i class="fas fa-tags text-danger me-1"></i>
                    Khuyến mãi đã áp dụng
                </div>

                @foreach($khuyenMaiDaApDung as $km)

                    <div class="d-flex justify-content-between mb-1">

                        <span>
                            {{ $km->ten_chuong_trinh ?? 'Khuyến mãi' }}

                            @if(($km->loai_ap_dung ?? '') === 'san_pham')
                                <span class="badge bg-warning text-dark ms-1">
                                    Sản phẩm
                                </span>
                            @else
                                <span class="badge bg-primary ms-1">
                                    Hóa đơn
                                </span>
                            @endif
                        </span>

                        <strong class="text-danger">
                            -{{ number_format(
                                $km->tien_giam ?? 0,
                                0,
                                ',',
                                '.'
                            ) }}đ
                        </strong>

                    </div>

                @endforeach

            </div>

        @endif

    </div>


    <div class="col-md-5">

        <table class="table table-borderless mb-0">

            <tr>
                <td>Tạm tính:</td>

                <td class="text-end fw-semibold">
                    {{ number_format(
                        $hoaDon->tong_tien_hang ?? 0,
                        0,
                        ',',
                        '.'
                    ) }}đ
                </td>
            </tr>

            <tr>
                <td>Giảm giá:</td>

                <td class="text-end text-danger fw-semibold">
                    -{{ number_format(
                        $hoaDon->tien_giam_gia ?? 0,
                        0,
                        ',',
                        '.'
                    ) }}đ
                </td>
            </tr>

            @if(($hoaDon->diem_su_dung ?? 0) > 0)

                <tr>
                    <td>Điểm đã sử dụng:</td>

                    <td class="text-end">
                        {{ number_format(
                            $hoaDon->diem_su_dung,
                            0,
                            ',',
                            '.'
                        ) }} điểm
                    </td>
                </tr>

            @endif

            <tr class="border-top">

                <td class="fw-bold fs-5">
                    Khách cần trả:
                </td>

                <td class="text-end fw-bold fs-5 text-primary">
                    {{ number_format(
                        $hoaDon->khach_can_tra ?? 0,
                        0,
                        ',',
                        '.'
                    ) }}đ
                </td>

            </tr>

            <tr>
                <td>Tiền khách đưa:</td>

                <td class="text-end">
                    {{ number_format(
                        $hoaDon->tien_khach_dua ?? 0,
                        0,
                        ',',
                        '.'
                    ) }}đ
                </td>
            </tr>

            <tr>
                <td>Tiền thừa:</td>

                <td class="text-end text-success fw-semibold">
                    {{ number_format(
                        $hoaDon->tien_thua ?? 0,
                        0,
                        ',',
                        '.'
                    ) }}đ
                </td>
            </tr>

        </table>

    </div>

</div>
    </div>
</div>

@if(isset($lichSuDoiTra) && $lichSuDoiTra->count())
    <div class="modal fade" id="chiTietDoiTraModal" tabindex="-1" aria-labelledby="chiTietDoiTraModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content" id="chi-tiet-doi-tra">
                <div class="modal-header">
                    <h5 class="modal-title" id="chiTietDoiTraModalLabel">Chi tiết đổi/trả của hóa đơn #HD{{ str_pad($hoaDon->id, 4, '0', STR_PAD_LEFT) }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
                </div>
                <div class="modal-body">
                    @foreach($lichSuDoiTra as $doiTra)
                        <div class="border rounded p-3 mb-3">
                            <div class="d-flex justify-content-between flex-wrap gap-2 mb-3">
                                <div>
                                    <div class="fw-bold">
                                        {{ $doiTra->Loai === 'tra_hang' ? 'Trả hàng' : 'Đổi hàng' }}
                                        #DT{{ str_pad($doiTra->id, 4, '0', STR_PAD_LEFT) }}
                                    </div>
                                    <div class="small text-muted">
                                        Ngày thực hiện: {{ optional($doiTra->ngay)->format('d/m/Y H:i') }}
                                    </div>
                                    <div class="small text-muted">
                                        Nhân viên: {{ $doiTra->nguoiDung->ho_ten ?? 'N/A' }}
                                    </div>
                                </div>
                                <div class="text-end">
                                    <span class="badge {{ $doiTra->Loai === 'tra_hang' ? 'bg-danger' : 'bg-warning text-dark' }}">
                                        {{ $doiTra->Loai === 'tra_hang' ? 'Trả hàng' : 'Đổi hàng' }}
                                    </span>
                                    <div class="small mt-2">
                                        Trừ điểm khách:
                                        <strong>{{ $doiTra->tru_diem_cua_khach ? 'Có' : 'Không' }}</strong>
                                    </div>
                                    <div class="small mt-1">
                                        Hàng lỗi:
                                        <strong>{{ $doiTra->hang_loi ? 'Có' : 'Không' }}</strong>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <strong>Lý do:</strong> {{ $doiTra->ly_do ?: 'Không có' }}
                            </div>

                            <div class="table-responsive">
                                <table class="table table-sm table-bordered mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Sản phẩm / biến thể</th>
                                            <th class="text-center">Số lượng</th>
                                            <th class="text-end">Giá lúc mua</th>
                                            <th class="text-end">Thành tiền</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($doiTra->chiTietDoiTras as $chiTietDoiTra)
                                            <tr>
                                                <td>
                                                    {{ $chiTietDoiTra->bienTheSanPham->ten_hien_thi ?? ($chiTietDoiTra->bienTheSanPham->product->ten_san_pham ?? 'Sản phẩm') }}
                                                </td>
                                                <td class="text-center">{{ $chiTietDoiTra->so_luong }}</td>
                                                <td class="text-end">{{ number_format($chiTietDoiTra->gia_ban, 0, ',', '.') }}đ</td>
                                                <td class="text-end">{{ number_format($chiTietDoiTra->thanh_tien, 0, ',', '.') }}đ</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                                
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
@endif

<style>
@media print {
    body * {
        visibility: hidden;
    }

    #invoiceArea, #invoiceArea * {
        visibility: visible;
    }

    #invoiceArea {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
        border: none;
    }

    .btn, .border-bottom {
        display: none !important;
    }
}
</style>
@endsection
