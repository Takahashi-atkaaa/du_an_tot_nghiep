@extends('admin_xem_truoc.layouts.admin')

@section('title', 'Chi tiết khách hàng - SmartMart')

@section('content')

<style>
    .customer-detail-page {
        --primary: #0d6efd;
        --primary-light: #eef5ff;
        --success: #198754;
        --success-light: #e7f7ee;
        --warning: #f59f00;
        --warning-light: #fff4d6;
        --danger: #dc3545;
        --danger-light: #fdebec;
        --text: #1e293b;
        --muted: #64748b;
        --border: #e9eef5;
        --bg: #f8fafc;
    }

    .customer-page-header {
        margin-bottom: 1.5rem;
    }

    .customer-page-header h4 {
        color: var(--text);
        font-size: 1.35rem;
        letter-spacing: -0.02em;
    }

    .customer-page-header .breadcrumb {
        font-size: .875rem;
    }

    .customer-page-header .breadcrumb-item a {
        color: var(--muted);
        text-decoration: none;
    }

    .customer-page-header .breadcrumb-item a:hover {
        color: var(--primary);
    }

    .customer-action-btn {
        border-radius: .65rem;
        padding: .6rem 1rem;
        font-weight: 600;
        font-size: .875rem;
    }

    .customer-card {
        border: 1px solid var(--border);
        border-radius: 1rem;
        background: #fff;
        box-shadow: 0 4px 18px rgba(15,23,42,.05);
        overflow: hidden;
    }

    .customer-card-header {
        padding: 1rem 1.25rem;
        background: #fff;
        border-bottom: 1px solid var(--border);
    }

    .customer-card-header h5 {
        margin: 0;
        font-size: 1rem;
        font-weight: 700;
        color: var(--text);
    }

    /* PROFILE */

    .customer-profile-body {
        padding: 2rem 1.5rem;
    }

    .customer-avatar {
        width: 92px;
        height: 92px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 1rem;
        color: #fff;
        font-size: 1.75rem;
        font-weight: 800;
        background: linear-gradient(135deg,#0d6efd,#20c997);
        box-shadow: 0 10px 25px rgba(13,110,253,.2);
    }

    .customer-name {
        font-size: 1.2rem;
        font-weight: 700;
        color: var(--text);
        margin-bottom: .25rem;
    }

    .customer-code {
        color: var(--muted);
        font-size: .85rem;
        margin-bottom: 1rem;
    }

    .customer-status {
        display: inline-flex;
        align-items: center;
        gap: .4rem;
        padding: .4rem .8rem;
        border-radius: 999px;
        font-size: .78rem;
        font-weight: 600;
    }

    .customer-status.active {
        color: #157347;
        background: #dff5e8;
    }

    .customer-status.inactive {
        color: #b02a37;
        background: #fce4e6;
    }

    .customer-info {
        border-top: 1px solid var(--border);
        margin-top: 1.5rem;
        padding-top: 1.25rem;
    }

    .customer-info-item {
        display: flex;
        align-items: flex-start;
        gap: .75rem;
        padding: .7rem 0;
        font-size: .9rem;
        color: var(--text);
    }

    .customer-info-icon {
        width: 32px;
        height: 32px;
        flex: 0 0 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: .55rem;
        background: var(--primary-light);
        color: var(--primary);
        font-size: .8rem;
    }

    .customer-info-content {
        min-width: 0;
        flex: 1;
        word-break: break-word;
    }

    .customer-info-label {
        display: block;
        color: var(--muted);
        font-size: .72rem;
        margin-bottom: .15rem;
    }

    .customer-info-value {
        color: var(--text);
        font-weight: 500;
    }

    /* STAT */

    .customer-stat-card {
        position: relative;
        height: 100%;
        padding: 1.25rem;
        border: 1px solid var(--border);
        border-radius: 1rem;
        background: #fff;
        box-shadow: 0 4px 18px rgba(15,23,42,.05);
        overflow: hidden;
    }

    .customer-stat-card::after {
        content: "";
        position: absolute;
        width: 90px;
        height: 90px;
        right: -35px;
        top: -35px;
        border-radius: 50%;
        opacity: .35;
    }

    .customer-stat-card.blue::after {
        background: #dceaff;
    }

    .customer-stat-card.green::after {
        background: #dff5e8;
    }

    .customer-stat-card.purple::after {
        background: #eee5ff;
    }

    .customer-stat-content {
        display: flex;
        align-items: center;
        gap: 1rem;
        position: relative;
        z-index: 1;
    }

    .customer-stat-icon {
        width: 50px;
        height: 50px;
        flex: 0 0 50px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: .8rem;
        font-size: 1.15rem;
    }

    .blue .customer-stat-icon {
        background: #eaf2ff;
        color: #0d6efd;
    }

    .green .customer-stat-icon {
        background: #eaf8f0;
        color: #198754;
    }

    .purple .customer-stat-icon {
        background: #f2ebff;
        color: #6f42c1;
    }

    .customer-stat-label {
        margin-bottom: .25rem;
        color: var(--muted);
        font-size: .78rem;
        font-weight: 500;
    }

    .customer-stat-value {
        margin: 0;
        color: var(--text);
        font-size: 1.5rem;
        font-weight: 750;
        line-height: 1.2;
    }

    /* TABLE */

    .customer-section {
        margin-top: 1.25rem;
    }

    .points-history-card {
        margin-top: 1.25rem;
    }

    .points-history-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 1rem;
        flex-wrap: wrap;
    }

    .points-count {
        padding: .4rem .75rem;
        border-radius: 999px;
        background: #f1f5f9;
        color: #475569;
        font-size: .75rem;
        font-weight: 600;
    }

    .points-table {
        margin: 0;
    }

    .points-table thead th {
        padding: .85rem 1rem;
        background: #f8fafc;
        border-bottom: 1px solid var(--border);
        color: #64748b;
        font-size: .75rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .025em;
        white-space: nowrap;
    }

    .points-table tbody td {
        padding: .9rem 1rem;
        border-color: #f0f3f7;
        color: var(--text);
        font-size: .86rem;
        vertical-align: middle;
    }

    .points-table tbody tr:hover {
        background: #f8fbff;
    }

    .points-table-wrapper {
        overflow-x: auto;
    }

    /* INVOICE */

    .invoice-link {
        color: var(--primary);
        text-decoration: none;
        font-weight: 700;
    }

    .invoice-link:hover {
        text-decoration: underline;
    }

    .payment-badge {
        display: inline-flex;
        align-items: center;
        gap: .35rem;
        padding: .35rem .65rem;
        border-radius: 999px;
        background: #f1f5f9;
        color: #475569;
        font-size: .74rem;
        font-weight: 600;
        white-space: nowrap;
    }

    .invoice-status {
        display: inline-flex;
        align-items: center;
        gap: .35rem;
        padding: .35rem .7rem;
        border-radius: 999px;
        font-size: .74rem;
        font-weight: 700;
        white-space: nowrap;
    }

    .invoice-status.success {
        color: #157347;
        background: var(--success-light);
    }

    .invoice-status.warning {
        color: #9a6700;
        background: var(--warning-light);
    }

    .invoice-status.danger {
        color: #b02a37;
        background: var(--danger-light);
    }

    .invoice-status.secondary {
        color: #475569;
        background: #f1f5f9;
    }

    /* POINT */

    .point-badge {
        display: inline-flex;
        align-items: center;
        padding: .35rem .7rem;
        border-radius: 999px;
        font-size: .74rem;
        font-weight: 700;
        white-space: nowrap;
    }

    .point-badge.up {
        color: #157347;
        background: #e7f7ee;
    }

    .point-badge.down {
        color: #b02a37;
        background: #fdebec;
    }

    .point-value-up {
        color: #198754;
        font-weight: 750;
    }

    .point-value-down {
        color: #dc3545;
        font-weight: 750;
    }

    /* EMPTY */

    .empty-history {
        padding: 3rem 1rem;
        text-align: center;
    }

    .empty-history-icon {
        width: 54px;
        height: 54px;
        margin: 0 auto 1rem;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        background: #f1f5f9;
        color: #94a3b8;
        font-size: 1.25rem;
    }

    .empty-history h6 {
        color: var(--text);
        font-weight: 700;
        margin-bottom: .35rem;
    }

    .empty-history p {
        color: var(--muted);
        margin: 0;
        font-size: .85rem;
    }

    .points-pagination {
        padding: 1rem 1.25rem;
        border-top: 1px solid var(--border);
        background: #fff;
    }

    .points-pagination nav {
        display: flex;
        justify-content: flex-end;
    }

    /* MODAL */

    #hoaDonModal .modal-dialog {
        max-width: 1100px;
    }

    #hoaDonModal .modal-content {
        border: 0;
        border-radius: 1rem;
        overflow: hidden;
        box-shadow: 0 1rem 3rem rgba(15,23,42,.2);
    }

    #hoaDonModal .modal-body {
        background: #fff;
    }

    .modal-loading {
        padding: 4rem 1rem;
        text-align: center;
    }

    @media(max-width:767.98px) {
        .customer-action-btn {
            flex: 1;
        }

        .points-table {
            min-width: 900px;
        }
    }
</style>

@php
    $initials = collect(
        preg_split('/\s+/', trim($khachHang->ten_khach_hang))
    )
    ->filter()
    ->map(fn($part) => mb_substr($part, 0, 1))
    ->take(2)
    ->implode('');
@endphp

<div class="customer-detail-page">

    {{-- HEADER --}}
    <div class="customer-page-header d-flex justify-content-between align-items-start flex-wrap gap-3">

        <div>
            <h4 class="fw-bold mb-1">
                Chi tiết khách hàng
            </h4>

            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">

                    <li class="breadcrumb-item">
                        <a href="{{ url('admin/dashboard') }}">
                            Dashboard
                        </a>
                    </li>

                    <li class="breadcrumb-item">
                        <a href="{{ route('khach-hang.index') }}">
                            Khách hàng
                        </a>
                    </li>

                    <li class="breadcrumb-item active">
                        Chi tiết
                    </li>

                </ol>
            </nav>
        </div>

        <div class="d-flex gap-2">

            @if(userHasPermission('sua_khach_hang'))
                <a
                    href="{{ route('khach-hang.edit', $khachHang) }}"
                    class="btn btn-primary customer-action-btn"
                >
                    <i class="fas fa-pen me-2"></i>
                    Chỉnh sửa
                </a>
            @endif

            <a
                href="{{ route('khach-hang.index') }}"
                class="btn btn-outline-secondary customer-action-btn"
            >
                <i class="fas fa-arrow-left me-2"></i>
                Quay lại
            </a>

        </div>
    </div>


    <div class="row g-4">

        {{-- ================= CUSTOMER ================= --}}
        <div class="col-lg-4">

            <div class="customer-card h-100">

                <div class="customer-profile-body">

                    <div class="customer-avatar">
                        {{ $initials ?: 'KH' }}
                    </div>

                    <div class="text-center">

                        <div class="customer-name">
                            {{ $khachHang->ten_khach_hang }}
                        </div>

                        <div class="customer-code">
                            Mã KH{{ str_pad(
                                $khachHang->id,
                                4,
                                '0',
                                STR_PAD_LEFT
                            ) }}
                        </div>

                        @if($khachHang->trang_thai)

                            <span class="customer-status active">
                                <i class="fas fa-circle fa-xs"></i>
                                Hoạt động
                            </span>

                        @else

                            <span class="customer-status inactive">
                                <i class="fas fa-circle fa-xs"></i>
                                Ngừng hoạt động
                            </span>

                        @endif

                    </div>


                    <div class="customer-info">

                        {{-- PHONE --}}
                        <div class="customer-info-item">

                            <div class="customer-info-icon">
                                <i class="fas fa-phone"></i>
                            </div>

                            <div class="customer-info-content">

                                <span class="customer-info-label">
                                    Số điện thoại
                                </span>

                                <span class="customer-info-value">
                                    {{ $khachHang->so_dien_thoai ?: '-' }}
                                </span>

                            </div>
                        </div>


                        {{-- EMAIL --}}
                        <div class="customer-info-item">

                            <div class="customer-info-icon">
                                <i class="fas fa-envelope"></i>
                            </div>

                            <div class="customer-info-content">

                                <span class="customer-info-label">
                                    Email
                                </span>

                                <span class="customer-info-value">
                                    {{ $khachHang->email ?: '-' }}
                                </span>

                            </div>
                        </div>


                        {{-- ADDRESS --}}
                        <div class="customer-info-item">

                            <div class="customer-info-icon">
                                <i class="fas fa-location-dot"></i>
                            </div>

                            <div class="customer-info-content">

                                <span class="customer-info-label">
                                    Địa chỉ
                                </span>

                                <span class="customer-info-value">
                                    {{ $khachHang->dia_chi ?: '-' }}
                                </span>

                            </div>
                        </div>


                        {{-- NOTE --}}
                        <div class="customer-info-item">

                            <div class="customer-info-icon">
                                <i class="fas fa-note-sticky"></i>
                            </div>

                            <div class="customer-info-content">

                                <span class="customer-info-label">
                                    Ghi chú
                                </span>

                                <span class="customer-info-value">
                                    {{ $khachHang->phi_chu ?: 'Không có ghi chú' }}
                                </span>

                            </div>
                        </div>

                    </div>

                </div>

            </div>

        </div>


        {{-- ================= RIGHT ================= --}}
        <div class="col-lg-8">

            {{-- STATISTICS --}}
            <div class="row g-3">

                {{-- POINT --}}
                <div class="col-md-4">

                    <div class="customer-stat-card blue">

                        <div class="customer-stat-content">

                            <div class="customer-stat-icon">
                                <i class="fas fa-gem"></i>
                            </div>

                            <div>

                                <div class="customer-stat-label">
                                    Điểm tích lũy
                                </div>

                                <h3 class="customer-stat-value">
                                    {{ number_format(
                                        $khachHang->diem_tich_luy ?? 0
                                    ) }}
                                </h3>

                            </div>

                        </div>

                    </div>

                </div>


                {{-- SPENDING --}}
                <div class="col-md-4">

                    <div class="customer-stat-card green">

                        <div class="customer-stat-content">

                            <div class="customer-stat-icon">
                                <i class="fas fa-wallet"></i>
                            </div>

                            <div>

                                <div class="customer-stat-label">
                                    Tổng chi tiêu
                                </div>

                                <h3 class="customer-stat-value">

                                    {{ number_format(
                                        $tongTienDaMua ?? 0,
                                        0,
                                        ',',
                                        '.'
                                    ) }}

                                    <small class="fs-6">
                                        đ
                                    </small>

                                </h3>

                            </div>

                        </div>

                    </div>

                </div>


                {{-- INVOICE --}}
                <div class="col-md-4">

                    <div class="customer-stat-card purple">

                        <div class="customer-stat-content">

                            <div class="customer-stat-icon">
                                <i class="fas fa-receipt"></i>
                            </div>

                            <div>

                                <div class="customer-stat-label">
                                    Tổng số hóa đơn
                                </div>

                                <h3 class="customer-stat-value">
                                    {{ number_format(
                                        $tongSoHoaDon ?? 0
                                    ) }}
                                </h3>

                            </div>

                        </div>

                    </div>

                </div>

            </div>


            {{-- ================= INVOICE HISTORY ================= --}}
            <div class="customer-card points-history-card">

                <div class="customer-card-header">

                    <div class="points-history-header">

                        <h5>
                            <i class="fas fa-file-invoice text-primary me-2"></i>
                            Lịch sử mua hàng
                        </h5>

                        <span class="points-count">
                            {{ $tongSoHoaDon }} hóa đơn
                        </span>

                    </div>

                </div>


                <div class="points-table-wrapper">

                    <table class="table points-table align-middle">

                        <thead>
                            <tr>
                                <th>Ngày</th>
                                <th>Hóa đơn</th>
                                <th>Tổng tiền</th>
                                <th>Thanh toán</th>
                                <th>Trạng thái</th>
                                <th class="text-center">Xem</th>
                            </tr>
                        </thead>


                        <tbody>

                        @forelse($hoaDons as $hoaDon)

                            <tr>

                                {{-- DATE --}}
                                <td>
                                    <span class="text-nowrap">
                                        {{ optional($hoaDon->created_at)
                                            ->format('d/m/Y H:i') }}
                                    </span>
                                </td>


                                {{-- INVOICE --}}
                                <td>

                                    <button
                                        type="button"
                                        class="btn btn-link p-0 invoice-link hoa-don-modal-button"
                                        data-id="{{ $hoaDon->id }}"
                                    >
                                        #HD{{ str_pad(
                                            $hoaDon->id,
                                            4,
                                            '0',
                                            STR_PAD_LEFT
                                        ) }}
                                    </button>

                                </td>


                                {{-- TOTAL --}}
                                <td>

                                    <strong>
                                        {{ number_format(
                                            $hoaDon->khach_can_tra ?? 0,
                                            0,
                                            ',',
                                            '.'
                                        ) }}
                                        đ
                                    </strong>

                                </td>


                                {{-- PAYMENT --}}
                                <td>

                                    @php
                                        $phuongThuc = mb_strtolower(
                                            trim(
                                                $hoaDon->phuong_thuc_thanh_toan
                                                ?? ''
                                            )
                                        );
                                    @endphp

                                    @if(
                                        str_contains($phuongThuc, 'tiền mặt')
                                        ||
                                        str_contains($phuongThuc, 'tien mat')
                                    )

                                        <span class="payment-badge">
                                            <i class="fas fa-money-bill-wave"></i>
                                            Tiền mặt
                                        </span>

                                    @elseif(
                                        str_contains($phuongThuc, 'tài khoản')
                                        ||
                                        str_contains($phuongThuc, 'tai khoan')
                                        ||
                                        str_contains($phuongThuc, 'chuyển khoản')
                                        ||
                                        str_contains($phuongThuc, 'chuyen khoan')
                                        ||
                                        str_contains($phuongThuc, 'payos')
                                    )

                                        <span class="payment-badge">
                                            <i class="fas fa-building-columns"></i>
                                            Tài khoản
                                        </span>

                                    @else

                                        <span class="payment-badge">
                                            <i class="fas fa-credit-card"></i>
                                            {{ $hoaDon->phuong_thuc_thanh_toan ?: '-' }}
                                        </span>

                                    @endif

                                </td>


                                {{-- STATUS --}}
                                <td>

                                    @php
                                        $trangThai = trim(
                                            $hoaDon->trang_thai ?? ''
                                        );
                                    @endphp


                                    @if($trangThai === 'Hoàn thành')

                                        <span class="invoice-status success">
                                            <i class="fas fa-check"></i>
                                            Hoàn thành
                                        </span>


                                    @elseif($trangThai === 'Đã đổi/trả hàng')

                                        <span class="invoice-status warning">
                                            <i class="fas fa-rotate-left"></i>
                                            Đã đổi/trả hàng
                                        </span>


                                    @elseif($trangThai === 'Đã trả toàn bộ')

                                        <span class="invoice-status danger">
                                            <i class="fas fa-rotate-left"></i>
                                            Đã trả toàn bộ
                                        </span>


                                    @elseif($trangThai === 'Đã hủy')

                                        <span class="invoice-status danger">
                                            <i class="fas fa-xmark"></i>
                                            Đã hủy
                                        </span>


                                    @else

                                        <span class="invoice-status secondary">
                                            {{ $trangThai ?: '-' }}
                                        </span>

                                    @endif

                                </td>


                                {{-- VIEW --}}
                                <td class="text-center">

                                    <button
                                        type="button"
                                        class="btn btn-sm btn-outline-primary hoa-don-modal-button"
                                        data-id="{{ $hoaDon->id }}"
                                        title="Xem chi tiết hóa đơn"
                                    >
                                        <i class="fas fa-eye"></i>
                                    </button>

                                </td>

                            </tr>

                        @empty

                            <tr>

                                <td colspan="6">

                                    <div class="empty-history">

                                        <div class="empty-history-icon">
                                            <i class="fas fa-receipt"></i>
                                        </div>

                                        <h6>
                                            Chưa có hóa đơn
                                        </h6>

                                        <p>
                                            Khách hàng chưa phát sinh hóa đơn mua hàng.
                                        </p>

                                    </div>

                                </td>

                            </tr>

                        @endforelse

                        </tbody>

                    </table>

                </div>


                @if($hoaDons->hasPages())

                    <div class="points-pagination">

                        {{ $hoaDons->appends([
                            'diem_page' => request('diem_page')
                        ])->links() }}

                    </div>

                @endif

            </div>


            {{-- ================= POINT HISTORY ================= --}}
            <div
                class="customer-card points-history-card"
                id="lich-su-tich-diem"
            >

                <div class="customer-card-header">

                    <div class="points-history-header">

                        <h5>
                            <i class="fas fa-clock-rotate-left text-primary me-2"></i>
                            Lịch sử tích điểm
                        </h5>

                        <span class="points-count">
                            {{ $lichSuTichDiems->total() }}
                            giao dịch
                        </span>

                    </div>

                </div>


                <div class="points-table-wrapper">

                    <table class="table points-table align-middle">

                        <thead>

                            <tr>
                                <th>Ngày</th>
                                <th>Hóa đơn</th>
                                <th>Loại biến động</th>
                                <th>Số điểm</th>
                                <th>Lý do</th>
                            </tr>

                        </thead>


                        <tbody>

                        @forelse($lichSuTichDiems as $lichSu)

                            @php

                                $loaiBienDong = mb_strtolower(
                                    trim(
                                        $lichSu->loai_bien_dong ?? ''
                                    )
                                );

                                $soDiem = (float) (
                                    $lichSu->so_diem ?? 0
                                );

                                $isCong =
                                    in_array(
                                        $loaiBienDong,
                                        [
                                            'cong',
                                            'tang',
                                            'tăng'
                                        ],
                                        true
                                    );

                                if (
                                    in_array(
                                        $loaiBienDong,
                                        [
                                            'tru',
                                            'thu',
                                            'giảm',
                                            'giam'
                                        ],
                                        true
                                    )
                                ) {
                                    $isCong = false;
                                }

                                if (
                                    $loaiBienDong === ''
                                    && $soDiem >= 0
                                ) {
                                    $isCong = true;
                                }

                            @endphp


                            <tr>

                                <td>

                                    <span class="text-nowrap">
                                        {{ optional($lichSu->created_at)
                                            ->format('d/m/Y H:i') }}
                                    </span>

                                </td>


                                <td>

                                    @if($lichSu->hoaDon)

                                        <button
                                            type="button"
                                            class="btn btn-link p-0 invoice-link hoa-don-modal-button"
                                            data-id="{{ $lichSu->hoaDon->id }}"
                                        >
                                            #HD{{ str_pad(
                                                $lichSu->hoaDon->id,
                                                4,
                                                '0',
                                                STR_PAD_LEFT
                                            ) }}
                                        </button>

                                    @else

                                        <span class="text-muted">
                                            -
                                        </span>

                                    @endif

                                </td>


                                <td>

                                    <span
                                        class="point-badge {{ $isCong ? 'up' : 'down' }}"
                                    >

                                        <i
                                            class="fas {{
                                                $isCong
                                                ? 'fa-arrow-up'
                                                : 'fa-arrow-down'
                                            }} me-1"
                                        ></i>

                                        {{ $isCong
                                            ? 'Tăng điểm'
                                            : 'Giảm điểm' }}

                                    </span>

                                </td>


                                <td>

                                    <span
                                        class="{{
                                            $isCong
                                            ? 'point-value-up'
                                            : 'point-value-down'
                                        }}"
                                    >

                                        {{ $isCong ? '+' : '-' }}{{
                                            number_format(
                                                abs($soDiem)
                                            )
                                        }}

                                    </span>

                                </td>


                                <td>

                                    <span class="text-muted">
                                        {{ $lichSu->ly_do ?: '-' }}
                                    </span>

                                </td>

                            </tr>

                        @empty

                            <tr>

                                <td colspan="5">

                                    <div class="empty-history">

                                        <div class="empty-history-icon">
                                            <i class="fas fa-star"></i>
                                        </div>

                                        <h6>
                                            Chưa có lịch sử tích điểm
                                        </h6>

                                        <p>
                                            Khách hàng chưa phát sinh giao dịch tích điểm.
                                        </p>

                                    </div>

                                </td>

                            </tr>

                        @endforelse

                        </tbody>

                    </table>

                </div>


                @if($lichSuTichDiems->hasPages())

                    <div class="points-pagination">

                        {{ $lichSuTichDiems->appends([
                            'hoa_don_page' => request('hoa_don_page')
                        ])->links() }}

                    </div>

                @endif

            </div>

        </div>

    </div>

</div>


{{-- =========================================================
     POPUP CHI TIẾT HÓA ĐƠN
========================================================= --}}

<div
    class="modal fade"
    id="hoaDonModal"
    tabindex="-1"
    aria-labelledby="hoaDonModalLabel"
    aria-hidden="true"
>

    <div
        class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable"
    >

        <div
            class="modal-content"
            id="hoa-don-modal-content"
        >

            <div class="modal-loading">

                <div
                    class="spinner-border text-primary"
                    role="status"
                >
                    <span class="visually-hidden">
                        Đang tải...
                    </span>
                </div>

                <p class="mt-3 mb-0 text-muted">
                    Đang tải chi tiết hóa đơn...
                </p>

            </div>

        </div>

    </div>

</div>

@endsection


@section('scripts')

<script>
document.addEventListener('DOMContentLoaded', function () {

    const modalElement =
        document.getElementById('hoaDonModal');

    const modalContent =
        document.getElementById(
            'hoa-don-modal-content'
        );

    if (!modalElement || !modalContent) {
        return;
    }


    const invoiceModal =
        bootstrap.Modal.getOrCreateInstance(
            modalElement,
            {
                backdrop: true,
                keyboard: true
            }
        );


    /*
    |--------------------------------------------------------------------------
    | URL POPUP
    |--------------------------------------------------------------------------
    |
    | Route:
    | admin.hoa-don.modal
    |
    */
    const modalUrlTemplate =
        @json(
            route(
                'admin.hoa-don.modal',
                ['id' => '__ID__']
            )
        );


    /*
    |--------------------------------------------------------------------------
    | LOADING
    |--------------------------------------------------------------------------
    */
    function showLoading() {

        modalContent.innerHTML = `
            <div class="modal-loading">
                <div
                    class="spinner-border text-primary"
                    role="status"
                >
                    <span class="visually-hidden">
                        Đang tải...
                    </span>
                </div>

                <p class="mt-3 mb-0 text-muted">
                    Đang tải chi tiết hóa đơn...
                </p>
            </div>
        `;
    }


    /*
    |--------------------------------------------------------------------------
    | ERROR
    |--------------------------------------------------------------------------
    */
    function showError(message) {

        modalContent.innerHTML = `
            <div class="modal-header">
                <h5
                    class="modal-title text-danger"
                    id="hoaDonModalLabel"
                >
                    <i class="fas fa-circle-exclamation me-2"></i>
                    Không thể tải hóa đơn
                </h5>

                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="modal"
                    aria-label="Đóng"
                ></button>
            </div>

            <div class="modal-body">

                <div class="alert alert-danger mb-0">
                    ${message}
                </div>

            </div>
        `;
    }


    /*
    |--------------------------------------------------------------------------
    | CLICK XEM HÓA ĐƠN
    |--------------------------------------------------------------------------
    */
    document.addEventListener(
        'click',
        function (event) {

            const button =
                event.target.closest(
                    '.hoa-don-modal-button'
                );

            if (!button) {
                return;
            }


            event.preventDefault();


            const hoaDonId =
                button.dataset.id;


            if (!hoaDonId) {
                showError(
                    'Không xác định được mã hóa đơn.'
                );

                invoiceModal.show();

                return;
            }


            showLoading();

            invoiceModal.show();


            const url =
                modalUrlTemplate.replace(
                    '__ID__',
                    encodeURIComponent(
                        hoaDonId
                    )
                );


            fetch(url, {
                method: 'GET',

                headers: {
                    'X-Requested-With':
                        'XMLHttpRequest',

                    'Accept':
                        'text/html'
                },

                credentials: 'same-origin'
            })

            .then(function (response) {

                if (!response.ok) {

                    throw new Error(
                        'HTTP ' +
                        response.status
                    );
                }

                return response.text();
            })

            .then(function (html) {

                if (!html.trim()) {

                    throw new Error(
                        'Máy chủ không trả về dữ liệu.'
                    );
                }


                modalContent.innerHTML =
                    html;


                /*
                |--------------------------------------------------------------------------
                | FIX NÚT ĐÓNG SAU KHI LOAD AJAX
                |--------------------------------------------------------------------------
                */
                const closeButtons =
                    modalContent.querySelectorAll(
                        '[data-bs-dismiss="modal"]'
                    );

                closeButtons.forEach(
                    function (closeButton) {

                        closeButton.addEventListener(
                            'click',
                            function () {

                                invoiceModal.hide();

                            }
                        );

                    }
                );

            })

            .catch(function (error) {

                console.error(
                    'Lỗi tải chi tiết hóa đơn:',
                    error
                );

                showError(
                    'Không thể tải chi tiết hóa đơn #' +
                    hoaDonId +
                    '. Vui lòng thử lại.'
                );

            });

        }
    );


    /*
    |--------------------------------------------------------------------------
    | RESET MODAL KHI ĐÓNG
    |--------------------------------------------------------------------------
    */
    modalElement.addEventListener(
        'hidden.bs.modal',
        function () {

            modalContent.innerHTML = `
                <div class="modal-loading">

                    <div
                        class="spinner-border text-primary"
                        role="status"
                    >
                        <span class="visually-hidden">
                            Đang tải...
                        </span>
                    </div>

                    <p class="mt-3 mb-0 text-muted">
                        Đang tải chi tiết hóa đơn...
                    </p>

                </div>
            `;

        }
    );

});
</script>

@endsection
