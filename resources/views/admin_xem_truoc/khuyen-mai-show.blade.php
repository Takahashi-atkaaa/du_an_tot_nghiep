@extends('admin_xem_truoc.layouts.admin')

@section('title', 'Chi tiết khuyến mãi')

@section('content')

<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>
            <h4 class="fw-bold mb-1">
                Chi tiết khuyến mãi
            </h4>

            <div class="text-muted">
                Admin / Khuyến mãi / Chi tiết
            </div>
        </div>

        <div class="d-flex gap-2">

            <a href="{{ route('khuyen-mai.index') }}"
               class="btn btn-outline-secondary">

                <i class="fas fa-arrow-left me-1"></i>
                Quay lại

            </a>

            <a href="{{ route('khuyen-mai.edit', $khuyenMai->id) }}"
               class="btn btn-primary">

                <i class="fas fa-edit me-1"></i>
                Chỉnh sửa

            </a>

        </div>

    </div>


    <div class="row">

        {{-- THÔNG TIN CHÍNH --}}
        <div class="col-lg-8">

            <div class="card mb-4">

                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-gift me-2"></i>
                        Thông tin chương trình
                    </h5>
                </div>

                <div class="card-body">

                    <div class="row">

                        <div class="col-md-6 mb-3">
                            <label class="text-muted">
                                Tên chương trình
                            </label>

                            <div class="fw-bold">
                                {{ $khuyenMai->ten_chuong_trinh }}
                            </div>
                        </div>


                        <div class="col-md-6 mb-3">
                            <label class="text-muted">
                                Trạng thái
                            </label>

                            <div>
                                @if($khuyenMai->trang_thai)
                                    <span class="badge bg-success">
                                        Đang hoạt động
                                    </span>
                                @else
                                    <span class="badge bg-secondary">
                                        Ngừng hoạt động
                                    </span>
                                @endif
                            </div>
                        </div>


                        <div class="col-md-6 mb-3">
                            <label class="text-muted">
                                Loại khuyến mãi
                            </label>

                            <div class="fw-semibold">

                                @if(in_array($khuyenMai->loai_giam_gia, [
                                    'percent',
                                    'phan_tram',
                                    'percentage'
                                ]))

                                    Giảm theo phần trăm

                                @elseif(in_array($khuyenMai->loai_giam_gia, [
                                    'amount',
                                    'fixed',
                                    'tien_mat',
                                    'so_tien',
                                    'giam_tien'
                                ]))

                                    Giảm số tiền

                                @elseif(in_array($khuyenMai->loai_giam_gia, [
                                    'bogo',
                                    'mua_1_tang_1'
                                ]))

                                    Mua 1 tặng 1

                                @else

                                    {{ $khuyenMai->loai_giam_gia }}

                                @endif

                            </div>

                        </div>


                        <div class="col-md-6 mb-3">

                            <label class="text-muted">
                                Giá trị giảm
                            </label>

                            <div class="fw-bold text-danger">

                                @if(in_array($khuyenMai->loai_giam_gia, [
                                    'percent',
                                    'phan_tram',
                                    'percentage'
                                ]))

                                    {{ number_format($khuyenMai->gia_tri_giam, 0) }}%

                                @else

                                    {{ number_format(
                                        $khuyenMai->gia_tri_giam,
                                        0,
                                        ',',
                                        '.'
                                    ) }}đ

                                @endif

                            </div>

                        </div>


                        <div class="col-md-6 mb-3">

                            <label class="text-muted">
                                Giảm tối đa
                            </label>

                            <div>

                                @if($khuyenMai->giam_toi_da)

                                    {{ number_format(
                                        $khuyenMai->giam_toi_da,
                                        0,
                                        ',',
                                        '.'
                                    ) }}đ

                                @else
                                    Không giới hạn
                                @endif

                            </div>

                        </div>


                        <div class="col-md-6 mb-3">

                            <label class="text-muted">
                                Đơn hàng tối thiểu
                            </label>

                            <div>

                                {{ number_format(
                                    $khuyenMai->don_hang_toi_thieu ?? 0,
                                    0,
                                    ',',
                                    '.'
                                ) }}đ

                            </div>

                        </div>


                        <div class="col-md-6 mb-3">

                            <label class="text-muted">
                                Số lượng tối thiểu
                            </label>

                            <div>
                                {{ $khuyenMai->so_luong_sp_toi_thieu ?? 0 }}
                            </div>

                        </div>


                        <div class="col-md-6 mb-3">

                            <label class="text-muted">
                                Phạm vi áp dụng
                            </label>

                            <div>

                                @if($laKhuyenMaiHoaDon)

                                    <span class="badge bg-primary">
                                        Toàn hóa đơn
                                    </span>

                                @else

                                    <span class="badge bg-warning text-dark">
                                        Sản phẩm / biến thể
                                    </span>

                                @endif

                            </div>

                        </div>

                    </div>

                </div>

            </div>


            {{-- SẢN PHẨM ÁP DỤNG --}}
            @if(!$laKhuyenMaiHoaDon)

                <div class="card mb-4">

                    <div class="card-header">

                        <h5 class="mb-0">
                            <i class="fas fa-box me-2"></i>
                            Sản phẩm áp dụng
                        </h5>

                    </div>


                    <div class="card-body">

                        @foreach($sanPhamApDung as $item)

                            <div class="border rounded p-3 mb-2">

                                <div class="d-flex justify-content-between">

                                    <div>

                                        <div class="fw-bold">
                                            {{ $item->ten_san_pham }}
                                        </div>


                                        @if($item->id_bien_the_san_pham)

                                            <small class="text-muted">

                                                Biến thể:
                                                {{ $item->ten_bien_the }}

                                                @if($item->ma_hang)
                                                    -
                                                    {{ $item->ma_hang }}
                                                @endif

                                            </small>

                                        @else

                                            <small class="text-primary">
                                                Áp dụng tất cả biến thể
                                            </small>

                                        @endif

                                    </div>


                                    @if($item->id_bien_the_san_pham)

                                        <div class="text-success fw-bold">

                                            {{ number_format(
                                                $item->gia_ban ?? 0,
                                                0,
                                                ',',
                                                '.'
                                            ) }}đ

                                        </div>

                                    @endif

                                </div>

                            </div>

                        @endforeach

                    </div>

                </div>

            @endif


            {{-- GHI CHÚ --}}
            <div class="card">

                <div class="card-header">
                    <h5 class="mb-0">
                        Ghi chú
                    </h5>
                </div>

                <div class="card-body">

                    {{ $khuyenMai->ghi_chu ?: 'Không có ghi chú.' }}

                </div>

            </div>

        </div>


        {{-- THỜI GIAN --}}
        <div class="col-lg-4">

            <div class="card">

                <div class="card-header">

                    <h5 class="mb-0">
                        <i class="fas fa-calendar me-2"></i>
                        Thời gian áp dụng
                    </h5>

                </div>

                <div class="card-body">

                    <div class="mb-3">

                        <small class="text-muted">
                            Ngày bắt đầu
                        </small>

                        <div class="fw-semibold">

                            {{ $khuyenMai->ngay_bat_dau
                                ? \Carbon\Carbon::parse(
                                    $khuyenMai->ngay_bat_dau
                                )->format('d/m/Y H:i')
                                : 'Không giới hạn'
                            }}

                        </div>

                    </div>


                    <div>

                        <small class="text-muted">
                            Ngày kết thúc
                        </small>

                        <div class="fw-semibold">

                            {{ $khuyenMai->ngay_ket_thuc
                                ? \Carbon\Carbon::parse(
                                    $khuyenMai->ngay_ket_thuc
                                )->format('d/m/Y H:i')
                                : 'Không giới hạn'
                            }}

                        </div>

                    </div>

                </div>

            </div>

                </div>

    </div>


    {{-- ======================================== --}}
    {{-- THỐNG KÊ SỬ DỤNG KHUYẾN MÃI --}}
    {{-- ======================================== --}}

    <div class="card mb-4">

        <div class="card-header bg-white">
            <h5 class="mb-0">
                <i class="fas fa-chart-bar me-2 text-primary"></i>
                Thống kê sử dụng
            </h5>
        </div>

        <div class="card-body">

            <div class="row g-3">

                {{-- SỐ HÓA ĐƠN --}}
                <div class="col-md-3">

                    <div class="border rounded p-3 h-100">

                        <div class="text-muted small mb-1">
                            Hóa đơn đã áp dụng
                        </div>

                        <div class="fs-4 fw-bold">
                            {{ number_format(
                                $thongKe->so_hoa_don ?? 0
                            ) }}
                        </div>

                    </div>

                </div>


                {{-- TỔNG TIỀN GIẢM --}}
                <div class="col-md-3">

                    <div class="border rounded p-3 h-100">

                        <div class="text-muted small mb-1">
                            Tổng tiền đã giảm
                        </div>

                        <div class="fs-4 fw-bold text-danger">

                            {{ number_format(
                                $thongKe->tong_tien_giam ?? 0,
                                0,
                                ',',
                                '.'
                            ) }}đ

                        </div>

                    </div>

                </div>


                {{-- DOANH THU --}}
                <div class="col-md-3">

                    <div class="border rounded p-3 h-100">

                        <div class="text-muted small mb-1">
                            Doanh thu
                        </div>

                        <div class="fs-4 fw-bold text-success">

                            {{ number_format(
                                $thongKe->doanh_thu ?? 0,
                                0,
                                ',',
                                '.'
                            ) }}đ

                        </div>

                    </div>

                </div>


                {{-- GIÁ TRỊ ĐƠN TRUNG BÌNH --}}
                <div class="col-md-3">

                    <div class="border rounded p-3 h-100">

                        <div class="text-muted small mb-1">
                 Doanh thu trung bình / hóa đơn
                    </div>

                        <div class="fs-4 fw-bold">

                            {{ number_format(
                                $thongKe->gia_tri_trung_binh ?? 0,
                                0,
                                ',',
                                '.'
                            ) }}đ

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>


    {{-- ======================================== --}}
    {{-- HÓA ĐƠN GẦN ĐÂY --}}
    {{-- ======================================== --}}

    <div class="card mb-4">

        <div class="card-header bg-white">

            <h5 class="mb-0">

                <i class="fas fa-receipt me-2 text-primary"></i>

                Hóa đơn gần đây sử dụng khuyến mãi

            </h5>

        </div>


       <div class="card-body">

    {{-- BỘ LỌC: PHẢI ĐẶT Ở NGOÀI IF --}}
    <form method="GET" class="row g-2 mb-3">

        <div class="col-md-3">
            <label class="form-label">Từ ngày</label>

            <input type="date"
                   name="tu_ngay"
                   class="form-control"
                   value="{{ request('tu_ngay') }}">
        </div>

        <div class="col-md-3">
            <label class="form-label">Đến ngày</label>

            <input type="date"
                   name="den_ngay"
                   class="form-control"
                   value="{{ request('den_ngay') }}">
        </div>

        <div class="col-md-3">
            <label class="form-label">Loại áp dụng</label>

            <select name="loai_ap_dung"
                    class="form-select">

                <option value="">
                    Tất cả
                </option>

                <option value="san_pham"
                    {{ request('loai_ap_dung') === 'san_pham'
                        ? 'selected'
                        : '' }}>
                    Sản phẩm
                </option>

                <option value="hoa_don"
                    {{ request('loai_ap_dung') === 'hoa_don'
                        ? 'selected'
                        : '' }}>
                    Toàn hóa đơn
                </option>

            </select>
        </div>

        <div class="col-md-3 d-flex align-items-end gap-2">

            <button type="submit"
                    class="btn btn-primary">

                <i class="fas fa-filter me-1"></i>
                Lọc

            </button>

            <a href="{{ route(
                'khuyen-mai.show',
                $khuyenMai->id
            ) }}"
               class="btn btn-outline-secondary">

                Xóa lọc

            </a>

        </div>

    </form>


    {{-- SAU FORM MỚI KIỂM TRA CÓ DỮ LIỆU KHÔNG --}}
    @if($hoaDonGanDay->isEmpty())

        <div class="text-center text-muted py-4">

            <i class="fas fa-receipt fa-2x mb-2"></i>

            <div>
                Không tìm thấy hóa đơn phù hợp.
            </div>

        </div>

    @else

        <div class="table-responsive">

            <table class="table table-hover align-middle mb-0">

                <thead>
                    <tr>
                        <th>Hóa đơn</th>
                        <th>Loại áp dụng</th>
                        <th>Tiền giảm</th>
                        <th>Khách cần trả</th>
                        <th>Thời gian</th>
                    </tr>
                </thead>

                <tbody>

                    @foreach($hoaDonGanDay as $hd)

                        <tr>

                            <td>
                                <strong>
                                    #HD{{ str_pad(
                                        $hd->id,
                                        5,
                                        '0',
                                        STR_PAD_LEFT
                                    ) }}
                                </strong>
                            </td>

                            <td>

                                @if($hd->loai_ap_dung === 'san_pham')

                                    <span class="badge bg-warning text-dark">
                                        Sản phẩm
                                    </span>

                                @else

                                    <span class="badge bg-primary">
                                        Toàn hóa đơn
                                    </span>

                                @endif

                            </td>

                            <td class="text-danger fw-semibold">

                                -{{ number_format(
                                    $hd->tien_giam,
                                    0,
                                    ',',
                                    '.'
                                ) }}đ

                            </td>

                            <td class="fw-semibold">

                                {{ number_format(
                                    $hd->khach_can_tra,
                                    0,
                                    ',',
                                    '.'
                                ) }}đ

                            </td>

                            <td>

                                {{ \Carbon\Carbon::parse(
                                    $hd->created_at
                                )->format('d/m/Y H:i') }}

                            </td>

                        </tr>

                    @endforeach

                </tbody>

            </table>


            @if($hoaDonGanDay->hasPages())

                <div class="mt-3">
                    {{ $hoaDonGanDay->links() }}
                </div>

            @endif

        </div>

    @endif

</div>
    </div> {{-- đóng card Hóa đơn gần đây --}}

</div> {{-- đóng container-fluid --}}

@endsection