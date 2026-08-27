@extends('admin_xem_truoc.layouts.admin')

@section('title', 'Chi tiết hóa đơn')

@section('content')

@php
    /*
    |--------------------------------------------------------------------------
    | TỔNG HỢP DỮ LIỆU
    |--------------------------------------------------------------------------
    */

    // ---------------------------------------------------------
    // Tổng tiền đổi / trả
    // ---------------------------------------------------------
  $tongTienHoanThucTe = (float) ($tongTienHoanThucTe ?? 0);


    // ---------------------------------------------------------
    // Tổng số lượng trả / đổi
    // ---------------------------------------------------------
    $tongSoLuongTra = 0;
    $tongSoLuongDoi = 0;

    if (isset($tongHopDoiTra)) {

        if (is_array($tongHopDoiTra)) {

            $tongSoLuongTra =
                $tongHopDoiTra['tong_tra_hang']
                ?? $tongHopDoiTra['tong_so_luong_tra']
                ?? $tongHopDoiTra['so_luong_tra']
                ?? 0;

            $tongSoLuongDoi =
                $tongHopDoiTra['tong_doi_hang']
                ?? $tongHopDoiTra['tong_so_luong_doi']
                ?? $tongHopDoiTra['so_luong_doi']
                ?? 0;

        } else {

            $tongSoLuongTra =
                $tongHopDoiTra->tong_tra_hang
                ?? $tongHopDoiTra->tong_so_luong_tra
                ?? $tongHopDoiTra->so_luong_tra
                ?? 0;

            $tongSoLuongDoi =
                $tongHopDoiTra->tong_doi_hang
                ?? $tongHopDoiTra->tong_so_luong_doi
                ?? $tongHopDoiTra->so_luong_doi
                ?? 0;
        }
    }

    $tongSoLuongTra = (int) $tongSoLuongTra;
    $tongSoLuongDoi = (int) $tongSoLuongDoi;


    // ---------------------------------------------------------
    // Tổng số lượng sản phẩm trong hóa đơn
    // ---------------------------------------------------------
    $tongSoLuongSanPham = 0;

    if (isset($chiTiet)) {
        $tongSoLuongSanPham = $chiTiet->sum(function ($item) {
            return (int) ($item->so_luong ?? 0);
        });
    }


    // ---------------------------------------------------------
    // Doanh thu ròng
    // ---------------------------------------------------------
   $khachCanTra = (float) ($hoaDon->khach_can_tra ?? 0);

$doanhThuRong = max(
    0,
    $khachCanTra - $tongTienHoanThucTe
);


    // ---------------------------------------------------------
    // Số lần đổi / trả
    // ---------------------------------------------------------
    $soLanDoiTra = isset($lichSuDoiTra)
        ? $lichSuDoiTra->count()
        : 0;


    // ---------------------------------------------------------
    // Trạng thái
    // ---------------------------------------------------------
    $trangThai = $hoaDon->trang_thai ?? 'Không xác định';


    // ---------------------------------------------------------
    // Tổng tiền sản phẩm
    // ---------------------------------------------------------
    $tamTinh = (float) (
        $hoaDon->tong_tien
        ?? $hoaDon->tam_tinh
        ?? 0
    );


    // ---------------------------------------------------------
    // Giảm giá
    // ---------------------------------------------------------
    $giamGia = (float) (
        $hoaDon->giam_gia
        ?? $hoaDon->tien_giam
        ?? $hoaDon->tong_giam_gia
        ?? 0
    );
@endphp


{{-- ============================================================
     HEADER
============================================================ --}}

<div class="d-flex justify-content-between align-items-center mb-4">

    <div>
        <h4 class="fw-bold mb-1">
            Chi tiết hóa đơn
            #HD{{ str_pad($hoaDon->id, 4, '0', STR_PAD_LEFT) }}
        </h4>

        <div class="text-muted small">
            <i class="far fa-calendar-alt me-1"></i>

            {{ \Carbon\Carbon::parse($hoaDon->created_at)->format('d/m/Y H:i') }}
        </div>
    </div>


    <div class="d-flex flex-wrap gap-2">

        {{-- Quay lại --}}
        <a
            href="{{ route('admin.hoa-don.index') }}"
            class="btn btn-secondary"
        >
            <i class="fas fa-arrow-left me-1"></i>
            Quay lại
        </a>


        {{-- In hóa đơn --}}
        <button
            type="button"
            onclick="window.print()"
            class="btn btn-success"
        >
            <i class="fas fa-print me-1"></i>
            In hóa đơn
        </button>


        {{-- Đổi / trả --}}
        @if(!in_array($trangThai, ['Đã hủy', 'Đã trả toàn bộ']))

            <a
                href="{{ route('admin.hoa-don.doi-tra', $hoaDon->id) }}"
                class="btn btn-warning"
            >
                <i class="fas fa-exchange-alt me-1"></i>
                Đổi / Trả hàng
            </a>

        @endif


        {{-- Modal chi tiết --}}
        @if(isset($lichSuDoiTra) && $lichSuDoiTra->count())

            <button
                type="button"
                class="btn btn-outline-warning"
                data-bs-toggle="modal"
                data-bs-target="#chiTietDoiTraModal"
            >
                <i class="fas fa-history me-1"></i>
                Chi tiết đổi/trả
            </button>

        @endif

    </div>

</div>



{{-- ============================================================
     THÔNG TIN HÓA ĐƠN
============================================================ --}}

<div class="card border-0 shadow-sm mb-4">

    <div class="card-header bg-white border-bottom">

        <div class="d-flex justify-content-between align-items-center">

            <div>
                <h5 class="fw-bold mb-1">
                    <i class="fas fa-file-invoice text-success me-2"></i>
                    Thông tin hóa đơn
                </h5>

                <small class="text-muted">
                    Mã hóa đơn:
                    #HD{{ str_pad($hoaDon->id, 4, '0', STR_PAD_LEFT) }}
                </small>
            </div>


            {{-- Trạng thái --}}
            @if($trangThai === 'Đã hủy')

                <span class="badge bg-danger px-3 py-2">
                    <i class="fas fa-times-circle me-1"></i>
                    {{ $trangThai }}
                </span>

            @elseif($trangThai === 'Đã trả toàn bộ')

                <span class="badge bg-warning text-dark px-3 py-2">
                    <i class="fas fa-rotate-left me-1"></i>
                    {{ $trangThai }}
                </span>

            @elseif($trangThai === 'Đã trả một phần')

                <span class="badge bg-warning text-dark px-3 py-2">
                    <i class="fas fa-exchange-alt me-1"></i>
                    {{ $trangThai }}
                </span>

            @else

                <span class="badge bg-success px-3 py-2">
                    <i class="fas fa-check-circle me-1"></i>
                    {{ $trangThai }}
                </span>

            @endif

        </div>

    </div>


    <div class="card-body">

        <div class="row g-4">

            {{-- Mã hóa đơn --}}
            <div class="col-md-3">

                <div class="info-item">

                    <small class="text-muted d-block mb-1">
                        Mã hóa đơn
                    </small>

                    <div class="fw-bold">
                        #HD{{ str_pad($hoaDon->id, 4, '0', STR_PAD_LEFT) }}
                    </div>

                </div>

            </div>


            {{-- Ngày tạo --}}
            <div class="col-md-3">

                <div class="info-item">

                    <small class="text-muted d-block mb-1">
                        Ngày tạo
                    </small>

                    <div class="fw-semibold">

                        {{ \Carbon\Carbon::parse($hoaDon->created_at)->format('d/m/Y H:i') }}

                    </div>

                </div>

            </div>


            {{-- Nhân viên --}}
            <div class="col-md-3">

                <div class="info-item">

                    <small class="text-muted d-block mb-1">
                        Nhân viên
                    </small>

                    <div class="fw-semibold">

                        <i class="fas fa-user-tie text-primary me-1"></i>

                        {{ $hoaDon->ten_nhan_vien ?? 'Nhân viên' }}

                    </div>

                </div>

            </div>


            {{-- Khách hàng --}}
            <div class="col-md-3">

                <div class="info-item">

                    <small class="text-muted d-block mb-1">
                        Khách hàng
                    </small>

                    <div class="fw-semibold">

                        <i class="fas fa-user text-success me-1"></i>

                        {{ $hoaDon->ten_khach_hang ?? 'Khách lẻ' }}

                    </div>

                    @if(!empty($hoaDon->so_dien_thoai))

                        <small class="text-muted">
                            {{ $hoaDon->so_dien_thoai }}
                        </small>

                    @endif

                </div>

            </div>

        </div>


        <hr class="my-4">


        <div class="row g-4">

            {{-- Thanh toán --}}
            <div class="col-md-4">

                <small class="text-muted d-block mb-1">
                    Phương thức thanh toán
                </small>

                <div class="fw-semibold">

                    <i class="fas fa-credit-card text-primary me-1"></i>

                    {{ $hoaDon->phuong_thuc_thanh_toan ?? '---' }}

                </div>

            </div>


            {{-- Khuyến mãi --}}
            <div class="col-md-4">

                <small class="text-muted d-block mb-1">
                    Khuyến mãi
                </small>

                <div class="fw-semibold">

                    <i class="fas fa-tag text-warning me-1"></i>

                    {{ $hoaDon->ten_khuyen_mai ?? 'Không áp dụng' }}

                </div>

            </div>


            {{-- Trạng thái --}}
            <div class="col-md-4">

                <small class="text-muted d-block mb-1">
                    Trạng thái
                </small>

                <div class="fw-semibold">
                    {{ $trangThai }}
                </div>

            </div>

        </div>

    </div>

</div>



{{-- ============================================================
     4 Ô THỐNG KÊ
============================================================ --}}

<div class="row g-3 mb-4">

    {{-- Khách cần trả --}}
    <div class="col-xl-3 col-md-6">

        <div class="card border-0 shadow-sm h-100 stat-card">

            <div class="card-body">

                <div class="d-flex justify-content-between">

                    <div>

                        <div class="text-muted small mb-2">
                            Khách cần trả
                        </div>

                        <h4 class="fw-bold text-primary mb-1">

                            {{ number_format($khachCanTra, 0, ',', '.') }}đ

                        </h4>

                        <small class="text-muted">
                            Giá trị thanh toán
                        </small>

                    </div>

                    <div class="stat-icon bg-primary-subtle text-primary">
                        <i class="fas fa-money-bill-wave"></i>
                    </div>

                </div>

            </div>

        </div>

    </div>


    {{-- Đã hoàn trả --}}
    <div class="col-xl-3 col-md-6">

        <div class="card border-0 shadow-sm h-100 stat-card">

            <div class="card-body">

                <div class="d-flex justify-content-between">

                    <div>

                        <div class="text-muted small mb-2">
                            Đã hoàn trả
                        </div>

                        <h4 class="fw-bold text-danger mb-1">

                         {{ number_format($tongTienHoanThucTe, 0, ',', '.') }}đ

                        </h4>

                        <small class="text-muted">
                            Tiền hàng đã trả
                        </small>

                    </div>

                    <div class="stat-icon bg-danger-subtle text-danger">
                        <i class="fas fa-rotate-left"></i>
                    </div>

                </div>

            </div>

        </div>

    </div>


    {{-- Doanh thu ròng --}}
    <div class="col-xl-3 col-md-6">

        <div class="card border-0 shadow-sm h-100 stat-card">

            <div class="card-body">

                <div class="d-flex justify-content-between">

                    <div>

                        <div class="text-muted small mb-2">
                            Doanh thu ròng
                        </div>

                        <h4 class="fw-bold text-success mb-1">

                            {{ number_format($doanhThuRong, 0, ',', '.') }}đ

                        </h4>

                        <small class="text-muted">
                            Sau đổi / trả
                        </small>

                    </div>

                    <div class="stat-icon bg-success-subtle text-success">
                        <i class="fas fa-chart-line"></i>
                    </div>

                </div>

            </div>

        </div>

    </div>


    {{-- Sản phẩm --}}
    <div class="col-xl-3 col-md-6">

        <div class="card border-0 shadow-sm h-100 stat-card">

            <div class="card-body">

                <div class="d-flex justify-content-between">

                    <div>

                        <div class="text-muted small mb-2">
                            Sản phẩm
                        </div>

                        <h4 class="fw-bold mb-1">

                            {{ $tongSoLuongSanPham }}

                        </h4>

                        <small class="text-muted">
                            Tổng số lượng trong hóa đơn
                        </small>

                    </div>

                    <div class="stat-icon bg-secondary-subtle text-secondary">
                        <i class="fas fa-box"></i>
                    </div>

                </div>

            </div>

        </div>

    </div>

</div>



{{-- ============================================================
     CHI TIẾT SẢN PHẨM
============================================================ --}}

<div class="card border-0 shadow-sm mb-4">

    <div class="card-header bg-white">

        <div class="d-flex justify-content-between align-items-center">

            <div>

                <h5 class="fw-bold mb-1">
                    <i class="fas fa-shopping-cart text-success me-2"></i>
                    Chi tiết sản phẩm
                </h5>

                <small class="text-muted">
                    Danh sách sản phẩm thuộc hóa đơn
                </small>

            </div>

            <span class="badge bg-light text-dark border">
                {{ $tongSoLuongSanPham }} sản phẩm
            </span>

        </div>

    </div>


    <div class="card-body p-0">

        <div class="table-responsive">

            <table class="table table-hover align-middle mb-0">

                <thead class="table-light">

                    <tr>

                        <th style="min-width: 280px;">
                            Sản phẩm
                        </th>

                        <th style="min-width: 130px;">
                            Mã vạch
                        </th>

                        <th class="text-center">
                            SL mua
                        </th>

                        <th class="text-end">
                            Giá bán
                        </th>

                        <th class="text-end">
                            Thành tiền
                        </th>

                        <th class="text-center">
                            Đã trả
                        </th>

                        <th class="text-center">
                            Đã đổi
                        </th>

                        <th class="text-center">
                            Trạng thái
                        </th>

                    </tr>

                </thead>


                <tbody>

                @forelse($chiTiet as $item)

                    @php

                        $soLuongMua = (int) ($item->so_luong ?? 0);

                        $daTra = (int) ($item->tong_da_tra ?? 0);

                        $daDoi = (int) ($item->tong_da_doi ?? 0);

                        $tongDaDoiTra = (int) (
                            $item->tong_da_doi_tra
                            ?? ($daTra + $daDoi)
                        );

                        $soLuongConLai = max(
                            0,
                            $soLuongMua - $tongDaDoiTra
                        );

                    @endphp


                    <tr>

                        {{-- Sản phẩm --}}
                        <td>

                            <div class="fw-semibold">

                                {{ $item->ten_hien_thi_san_pham ?: $item->ten_san_pham }}

                            </div>


                            @if(!empty($item->thuoc_tinh_hien_thi))

                                <div class="small text-muted mt-1">

                                    <i class="fas fa-tags me-1"></i>

                                    {{ implode(' • ', $item->thuoc_tinh_hien_thi) }}

                                </div>

                            @endif

                        </td>


                        {{-- Mã vạch --}}
                        <td>

                            <span class="font-monospace small">

                                {{ $item->ma_vach ?? 'N/A' }}

                            </span>

                        </td>


                        {{-- SL mua --}}
                        <td class="text-center">

                            <span class="fw-semibold">

                                {{ $soLuongMua }}

                            </span>

                        </td>


                        {{-- Giá bán --}}
                        <td class="text-end">

                            {{ number_format($item->gia_ban ?? 0, 0, ',', '.') }}đ

                        </td>


                        {{-- Thành tiền --}}
                        <td class="text-end">

                            <span class="fw-semibold">

                                {{ number_format($item->thanh_tien ?? 0, 0, ',', '.') }}đ

                            </span>

                        </td>


                        {{-- Đã trả --}}
                        <td class="text-center">

                            @if($daTra > 0)

                                <span class="badge bg-danger">
                                    {{ $daTra }}
                                </span>

                            @else

                                <span class="text-muted">
                                    0
                                </span>

                            @endif

                        </td>


                        {{-- Đã đổi --}}
                        <td class="text-center">

                            @if($daDoi > 0)

                                <span class="badge bg-warning text-dark">
                                    {{ $daDoi }}
                                </span>

                            @else

                                <span class="text-muted">
                                    0
                                </span>

                            @endif

                        </td>


                        {{-- Trạng thái --}}
                        <td class="text-center">

                            @if($tongDaDoiTra <= 0)

                                <span class="badge bg-success-subtle text-success">
                                    Chưa đổi/trả
                                </span>

                            @elseif($soLuongConLai > 0)

                                <span class="badge bg-warning-subtle text-warning-emphasis">
                                    Đã xử lý một phần
                                </span>

                            @else

                                <span class="badge bg-danger-subtle text-danger">
                                    Đã xử lý toàn bộ
                                </span>

                            @endif

                        </td>

                    </tr>


                @empty

                    <tr>

                        <td colspan="8" class="text-center py-5 text-muted">

                            <i class="fas fa-box-open fa-2x mb-2"></i>

                            <div>
                                Không có sản phẩm trong hóa đơn.
                            </div>

                        </td>

                    </tr>

                @endforelse

                </tbody>

            </table>

        </div>

    </div>

</div>



{{-- ============================================================
     TỔNG KẾT THANH TOÁN
============================================================ --}}

<div class="row mb-4">

    <div class="col-lg-7"></div>


    <div class="col-lg-5">

        <div class="card border-0 shadow-sm">

            <div class="card-header bg-white">

                <h5 class="fw-bold mb-0">
                    <i class="fas fa-calculator text-primary me-2"></i>
                    Tổng kết thanh toán
                </h5>

            </div>


            <div class="card-body">

                {{-- Tạm tính --}}
                <div class="d-flex justify-content-between mb-3">

                    <span class="text-muted">
                        Tạm tính
                    </span>

                    <span class="fw-semibold">
                        {{ number_format($tamTinh, 0, ',', '.') }}đ
                    </span>

                </div>


                {{-- Giảm giá --}}
                <div class="d-flex justify-content-between mb-3">

                    <span class="text-muted">
                        Giảm giá
                    </span>

                    <span class="text-success fw-semibold">

                        @if($giamGia > 0)
                            -{{ number_format($giamGia, 0, ',', '.') }}đ
                        @else
                            0đ
                        @endif

                    </span>

                </div>


                <hr>


                {{-- Khách cần trả --}}
                <div class="d-flex justify-content-between mb-3">

                    <span class="fw-bold">
                        Khách cần trả
                    </span>

                    <span class="fw-bold text-primary">

                        {{ number_format($khachCanTra, 0, ',', '.') }}đ

                    </span>

                </div>


                {{-- Đổi trả --}}
                @if($tongTienHoanThucTe > 0)

                    <div class="d-flex justify-content-between mb-3">

                        <span class="text-danger">
                            Hoàn tiền đổi / trả
                        </span>

                        <span class="fw-semibold text-danger">

                            -{{ number_format($tongTienHoanThucTe, 0, ',', '.') }}đ

                        </span>

                    </div>

                @endif


                <hr>


                {{-- Doanh thu ròng --}}
                <div class="p-3 rounded bg-success-subtle">

                    <div class="d-flex justify-content-between align-items-center">

                        <span class="fw-bold">
                            Doanh thu ròng
                        </span>

                        <span class="fw-bold text-success fs-5">

                            {{ number_format($doanhThuRong, 0, ',', '.') }}đ

                        </span>

                    </div>

                   <small class="text-muted d-block mt-1">
                    Khách cần trả - tiền hoàn thực tế
                </small>

                </div>

            </div>

        </div>

    </div>

</div>



{{-- ============================================================
     LỊCH SỬ ĐỔI / TRẢ
============================================================ --}}

@if(isset($lichSuDoiTra) && $lichSuDoiTra->count())

<div class="card border-0 shadow-sm mb-4">

    <div class="card-header bg-white">

        <div class="d-flex justify-content-between align-items-center">

            <div>

                <h5 class="fw-bold mb-1">

                    <i class="fas fa-exchange-alt text-warning me-2"></i>

                    Lịch sử đổi / trả

                </h5>

                <small class="text-muted">
                    Các lần xử lý đổi hoặc trả của hóa đơn
                </small>

            </div>


            <span class="badge bg-warning text-dark">

                {{ $lichSuDoiTra->count() }} lần

            </span>

        </div>

    </div>


    <div class="card-body">

        @foreach($lichSuDoiTra as $doiTra)

            @php

                $loaiDoiTra = $doiTra->Loai ?? $doiTra->loai ?? '';

                $isTraHang = $loaiDoiTra === 'tra_hang';

                $tieuDeDoiTra = $isTraHang
                    ? 'Trả hàng'
                    : 'Đổi hàng';

                $badgeClass = $isTraHang
                    ? 'bg-danger'
                    : 'bg-warning text-dark';

            @endphp


            <div class="return-history-item border rounded p-3 mb-3">

                {{-- Header --}}
                <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-3">

                    <div>

                        <div class="fw-bold">

                            {{ $tieuDeDoiTra }}

                            #DT{{ str_pad($doiTra->id, 4, '0', STR_PAD_LEFT) }}

                        </div>


                        <div class="small text-muted mt-1">

                            <i class="far fa-clock me-1"></i>

                            Ngày thực hiện:

                            {{ optional($doiTra->ngay)->format('d/m/Y H:i') }}

                        </div>


                        <div class="small text-muted mt-1">

                            <i class="fas fa-user me-1"></i>

                            Người thực hiện:

                            {{ $doiTra->nguoi_thuc_hien_doi_tra_hien_thi
                                ?? ($doiTra->nguoiDung->ho_ten_kem_vai_tro ?? 'N/A') }}

                        </div>

                    </div>


                    <div class="text-end">

                        <span class="badge {{ $badgeClass }} px-3 py-2">

                            {{ $tieuDeDoiTra }}

                        </span>


                        @if(isset($doiTra->tru_diem_cua_khach))

                            <div class="small mt-2">

                                Trừ điểm khách:

                                <strong>

                                    {{ $doiTra->tru_diem_cua_khach ? 'Có' : 'Không' }}

                                </strong>

                            </div>

                        @endif


                        @if(isset($doiTra->hang_loi))

                            <div class="small mt-1">

                                Hàng lỗi:

                                <strong>

                                    {{ $doiTra->hang_loi ? 'Có' : 'Không' }}

                                </strong>

                            </div>

                        @endif

                    </div>

                </div>


                {{-- Lý do --}}
                <div class="bg-light rounded p-3 mb-3">

                    <strong>
                        Lý do:
                    </strong>

                    {{ $doiTra->ly_do ?: 'Không có' }}

                </div>


                {{-- Chi tiết sản phẩm đổi trả --}}
                <div class="table-responsive">

                    <table class="table table-sm table-bordered align-middle mb-0">

                        <thead class="table-light">

                            <tr>

                                <th>
                                    Sản phẩm / biến thể
                                </th>

                                <th class="text-center">
                                    Số lượng
                                </th>

                                <th class="text-end">
                                    Giá lúc mua
                                </th>

                                <th class="text-end">
                                    Thành tiền
                                </th>

                            </tr>

                        </thead>


                        <tbody>

                        @forelse($doiTra->chiTietDoiTras as $chiTietDoiTra)

                            <tr>

                                <td>

                                    {{ $chiTietDoiTra->bienTheSanPham->ten_hien_thi
                                        ?? ($chiTietDoiTra->bienTheSanPham->product->ten_san_pham
                                        ?? 'Sản phẩm') }}

                                </td>


                                <td class="text-center">

                                    {{ $chiTietDoiTra->so_luong }}

                                </td>


                                <td class="text-end">

                                    {{ number_format($chiTietDoiTra->gia_ban ?? 0, 0, ',', '.') }}đ

                                </td>


                                <td class="text-end fw-semibold">

                                    {{ number_format($chiTietDoiTra->thanh_tien ?? 0, 0, ',', '.') }}đ

                                </td>

                            </tr>

                        @empty

                            <tr>

                                <td
                                    colspan="4"
                                    class="text-center text-muted py-3"
                                >
                                    Không có chi tiết sản phẩm.
                                </td>

                            </tr>

                        @endforelse

                        </tbody>

                    </table>

                </div>

            </div>

        @endforeach

    </div>

</div>

@endif



{{-- ============================================================
     ĐIỂM KHÁCH HÀNG
============================================================ --}}

@if(isset($diemTichDiems) && $diemTichDiems->count())

<div class="card border-0 shadow-sm mb-4">

    <div class="card-header bg-white">

        <h5 class="fw-bold mb-0">

            <i class="fas fa-star text-warning me-2"></i>

            Lịch sử điểm khách hàng

        </h5>

    </div>


    <div class="card-body">

        <div class="table-responsive">

            <table class="table table-hover align-middle mb-0">

                <thead class="table-light">

                    <tr>

                        <th>
                            Thời gian
                        </th>

                        <th>
                            Nội dung
                        </th>

                        <th class="text-center">
                            Điểm
                        </th>

                    </tr>

                </thead>


                <tbody>

                @foreach($diemTichDiems as $diem)

                    <tr>

                        <td>

                            @if(isset($diem->created_at))

                                {{ \Carbon\Carbon::parse($diem->created_at)->format('d/m/Y H:i') }}

                            @elseif(isset($diem->ngay))

                                {{ \Carbon\Carbon::parse($diem->ngay)->format('d/m/Y H:i') }}

                            @else

                                ---

                            @endif

                        </td>


                        <td>

                            {{ $diem->noi_dung
                                ?? $diem->ly_do
                                ?? $diem->mo_ta
                                ?? 'Thay đổi điểm' }}

                        </td>

                        <td class="text-center">

                            @php

                                // ---------------------------------------------------------
                                // Lấy số điểm
                                // ---------------------------------------------------------
                                $soDiem =
                                    $diem->so_diem
                                    ?? $diem->diem
                                    ?? $diem->so_diem_thay_doi
                                    ?? 0;

                                $soDiem = abs((int) $soDiem);


                                // ---------------------------------------------------------
                                // Lấy nội dung giao dịch
                                // ---------------------------------------------------------
                                $noiDung =
                                    $diem->noi_dung
                                    ?? $diem->ly_do
                                    ?? $diem->mo_ta
                                    ?? '';

                                $noiDungLower = mb_strtolower(
                                    trim($noiDung),
                                    'UTF-8'
                                );


                                // ---------------------------------------------------------
                                // Xác định giao dịch TRỪ điểm
                                // ---------------------------------------------------------
                                $laDiemTru =
                                    str_contains($noiDungLower, 'sử dụng điểm')
                                    || str_contains($noiDungLower, 'trừ điểm')
                                    || str_contains($noiDungLower, 'tru diem')
                                    || str_contains($noiDungLower, 'sử dụng')
                                    || str_contains($noiDungLower, 'trả hàng')
                                    || str_contains($noiDungLower, 'đổi hàng')
                                    || str_contains($noiDungLower, 'đổi/trả')
                                    || str_contains($noiDungLower, 'đổi trả')
                                    || str_contains($noiDungLower, 'đổi/trả hàng')
                                    || str_contains($noiDungLower, 'hoàn điểm');

                            @endphp


                            @if($soDiem == 0)

                                {{-- Không thay đổi điểm --}}
                                <span class="badge bg-secondary px-2 py-1">
                                    0
                                </span>


                            @elseif($laDiemTru)

                                {{-- Điểm bị trừ --}}
                                <span class="badge bg-danger px-2 py-1">

                                    <i class="fas fa-minus me-1"></i>

                                    {{ $soDiem }}

                                </span>


                            @else

                                {{-- Điểm được cộng --}}
                                <span class="badge bg-success px-2 py-1">

                                    <i class="fas fa-plus me-1"></i>

                                    {{ $soDiem }}

                                </span>

                            @endif

                        </td>
                    </tr>

                @endforeach

                </tbody>

            </table>

        </div>

    </div>

</div>

@endif



{{-- ============================================================
     MODAL CHI TIẾT ĐỔI / TRẢ
============================================================ --}}

@if(isset($lichSuDoiTra) && $lichSuDoiTra->count())

<div
    class="modal fade"
    id="chiTietDoiTraModal"
    tabindex="-1"
    aria-labelledby="chiTietDoiTraModalLabel"
    aria-hidden="true"
>

    <div class="modal-dialog modal-xl modal-dialog-scrollable">

        <div class="modal-content">

            <div class="modal-header">

                <h5
                    class="modal-title fw-bold"
                    id="chiTietDoiTraModalLabel"
                >

                    <i class="fas fa-history text-warning me-2"></i>

                    Chi tiết đổi/trả của hóa đơn

                    #HD{{ str_pad($hoaDon->id, 4, '0', STR_PAD_LEFT) }}

                </h5>


                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="modal"
                    aria-label="Đóng"
                ></button>

            </div>


            <div class="modal-body">

                @foreach($lichSuDoiTra as $doiTra)

                    @php

                        $loaiDoiTra = $doiTra->Loai ?? $doiTra->loai ?? '';

                        $isTraHang = $loaiDoiTra === 'tra_hang';

                    @endphp


                    <div class="border rounded p-3 mb-4">

                        <div class="d-flex justify-content-between flex-wrap gap-2 mb-3">

                            <div>

                                <div class="fw-bold">

                                    {{ $isTraHang ? 'Trả hàng' : 'Đổi hàng' }}

                                    #DT{{ str_pad($doiTra->id, 4, '0', STR_PAD_LEFT) }}

                                </div>


                                <div class="small text-muted">

                                    Ngày thực hiện:

                                    {{ optional($doiTra->ngay)->format('d/m/Y H:i') }}

                                </div>


                                <div class="small text-muted">

                                    Người thực hiện:

                                    {{ $doiTra->nguoi_thuc_hien_doi_tra_hien_thi
                                        ?? ($doiTra->nguoiDung->ho_ten_kem_vai_tro ?? 'N/A') }}

                                </div>

                            </div>


                            <div class="text-end">

                                <span
                                    class="badge
                                    {{ $isTraHang
                                        ? 'bg-danger'
                                        : 'bg-warning text-dark' }}"
                                >

                                    {{ $isTraHang ? 'Trả hàng' : 'Đổi hàng' }}

                                </span>


                                @if(isset($doiTra->tru_diem_cua_khach))

                                    <div class="small mt-2">

                                        Trừ điểm khách:

                                        <strong>
                                            {{ $doiTra->tru_diem_cua_khach ? 'Có' : 'Không' }}
                                        </strong>

                                    </div>

                                @endif


                                @if(isset($doiTra->hang_loi))

                                    <div class="small mt-1">

                                        Hàng lỗi:

                                        <strong>
                                            {{ $doiTra->hang_loi ? 'Có' : 'Không' }}
                                        </strong>

                                    </div>

                                @endif

                            </div>

                        </div>


                        <div class="alert alert-light border mb-3">

                            <strong>
                                Lý do:
                            </strong>

                            {{ $doiTra->ly_do ?: 'Không có' }}

                        </div>


                        <div class="table-responsive">

                            <table class="table table-sm table-bordered mb-0">

                                <thead class="table-light">

                                    <tr>

                                        <th>
                                            Sản phẩm / biến thể
                                        </th>

                                        <th class="text-center">
                                            Số lượng
                                        </th>

                                        <th class="text-end">
                                            Giá lúc mua
                                        </th>

                                        <th class="text-end">
                                            Thành tiền
                                        </th>

                                    </tr>

                                </thead>


                                <tbody>

                                @foreach($doiTra->chiTietDoiTras as $chiTietDoiTra)

                                    <tr>

                                        <td>

                                            {{ $chiTietDoiTra->bienTheSanPham->ten_hien_thi
                                                ?? ($chiTietDoiTra->bienTheSanPham->product->ten_san_pham
                                                ?? 'Sản phẩm') }}

                                        </td>


                                        <td class="text-center">

                                            {{ $chiTietDoiTra->so_luong }}

                                        </td>


                                        <td class="text-end">

                                            {{ number_format($chiTietDoiTra->gia_ban ?? 0, 0, ',', '.') }}đ

                                        </td>


                                        <td class="text-end">

                                            {{ number_format($chiTietDoiTra->thanh_tien ?? 0, 0, ',', '.') }}đ

                                        </td>

                                    </tr>

                                @endforeach

                                </tbody>

                            </table>

                        </div>

                    </div>

                @endforeach

            </div>


            <div class="modal-footer">

                <button
                    type="button"
                    class="btn btn-secondary"
                    data-bs-dismiss="modal"
                >
                    Đóng
                </button>

            </div>

        </div>

    </div>

</div>

@endif



{{-- ============================================================
     CSS
============================================================ --}}

<style>

    .stat-card {
        transition: all .2s ease;
    }


    .stat-card:hover {
        transform: translateY(-2px);
    }


    .stat-icon {

        width: 45px;
        height: 45px;

        display: flex;
        align-items: center;
        justify-content: center;

        border-radius: 10px;

        font-size: 18px;

    }


    .info-item {
        min-height: 55px;
    }


    .return-history-item {
        background: #fff;
        transition: all .2s ease;
    }


    .return-history-item:hover {
        box-shadow: 0 3px 10px rgba(0,0,0,.06);
    }


    .table th {
        white-space: nowrap;
        font-size: 13px;
    }


    .table td {
        font-size: 14px;
    }


    .font-monospace {
        font-size: 12px;
    }


    @media print {

        body * {
            visibility: hidden !important;
        }


        #invoiceArea,
        #invoiceArea * {
            visibility: visible !important;
        }


        #invoiceArea {

            position: absolute;

            left: 0;
            top: 0;

            width: 100%;

            border: none !important;

        }


        .btn,
        .sidebar,
        .navbar,
        .header,
        .topbar,
        .modal,
        .modal-backdrop {

            display: none !important;

        }


        .card {

            box-shadow: none !important;
            border: none !important;

        }


        .table {

            font-size: 12px;

        }

    }

</style>

@endsection