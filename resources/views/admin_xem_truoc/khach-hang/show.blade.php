@extends('admin_xem_truoc.layouts.admin')

@section('title', 'Chi tiết khách hàng - SmartMart')

@section('content')

<style>
    /* ================================
       CUSTOMER DETAIL
    ================================= */

    .customer-detail-page {
        --customer-primary: #0d6efd;
        --customer-primary-light: #eef5ff;
        --customer-success: #198754;
        --customer-success-light: #edf9f3;
        --customer-warning: #f59f00;
        --customer-danger: #dc3545;
        --customer-text: #1e293b;
        --customer-muted: #64748b;
        --customer-border: #e9eef5;
        --customer-bg: #f8fafc;
    }

    /* Header */
    .customer-page-header {
        margin-bottom: 1.5rem;
    }

    .customer-page-header h4 {
        color: var(--customer-text);
        font-size: 1.35rem;
        letter-spacing: -0.02em;
    }

    .customer-page-header .breadcrumb {
        font-size: 0.875rem;
    }

    .customer-page-header .breadcrumb-item a {
        color: var(--customer-muted);
        text-decoration: none;
    }

    .customer-page-header .breadcrumb-item a:hover {
        color: var(--customer-primary);
    }

    /* Button */
    .customer-action-btn {
        border-radius: 0.65rem;
        padding: 0.6rem 1rem;
        font-weight: 600;
        font-size: 0.875rem;
    }

    /* Common card */
    .customer-card {
        border: 1px solid var(--customer-border);
        border-radius: 1rem;
        background: #fff;
        box-shadow: 0 4px 18px rgba(15, 23, 42, 0.05);
        overflow: hidden;
    }

    .customer-card-header {
        padding: 1rem 1.25rem;
        background: #fff;
        border-bottom: 1px solid var(--customer-border);
    }

    .customer-card-header h5 {
        margin: 0;
        font-size: 1rem;
        font-weight: 700;
        color: var(--customer-text);
    }

    /* Customer profile */
    .customer-profile {
        position: relative;
        height: 100%;
    }

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
        background: linear-gradient(135deg, #0d6efd, #20c997);
        box-shadow: 0 10px 25px rgba(13, 110, 253, 0.2);
    }

    .customer-name {
        font-size: 1.2rem;
        font-weight: 700;
        color: var(--customer-text);
        margin-bottom: 0.25rem;
    }

    .customer-code {
        color: var(--customer-muted);
        font-size: 0.85rem;
        margin-bottom: 1rem;
    }

    .customer-status {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        padding: 0.4rem 0.8rem;
        border-radius: 999px;
        font-size: 0.78rem;
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
        border-top: 1px solid var(--customer-border);
        margin-top: 1.5rem;
        padding-top: 1.25rem;
    }

    .customer-info-item {
        display: flex;
        align-items: flex-start;
        gap: 0.75rem;
        padding: 0.7rem 0;
        color: var(--customer-text);
        font-size: 0.9rem;
    }

    .customer-info-icon {
        width: 32px;
        height: 32px;
        flex: 0 0 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 0.55rem;
        background: var(--customer-primary-light);
        color: var(--customer-primary);
        font-size: 0.8rem;
    }

    .customer-info-content {
        min-width: 0;
        flex: 1;
        word-break: break-word;
    }

    .customer-info-label {
        display: block;
        color: var(--customer-muted);
        font-size: 0.72rem;
        margin-bottom: 0.15rem;
    }

    .customer-info-value {
        color: var(--customer-text);
        font-weight: 500;
    }

    /* Statistics */
    .customer-stat-card {
        position: relative;
        height: 100%;
        padding: 1.25rem;
        border: 1px solid var(--customer-border);
        border-radius: 1rem;
        background: #fff;
        box-shadow: 0 4px 18px rgba(15, 23, 42, 0.05);
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
        opacity: 0.35;
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
        border-radius: 0.8rem;
        font-size: 1.15rem;
    }

    .customer-stat-card.blue .customer-stat-icon {
        background: #eaf2ff;
        color: #0d6efd;
    }

    .customer-stat-card.green .customer-stat-icon {
        background: #eaf8f0;
        color: #198754;
    }

    .customer-stat-card.purple .customer-stat-icon {
        background: #f2ebff;
        color: #6f42c1;
    }

    .customer-stat-label {
        margin-bottom: 0.25rem;
        color: var(--customer-muted);
        font-size: 0.78rem;
        font-weight: 500;
    }

    .customer-stat-value {
        margin: 0;
        color: var(--customer-text);
        font-size: 1.55rem;
        font-weight: 750;
        line-height: 1.2;
    }

    /* Section */
    .customer-section {
        margin-top: 1.25rem;
    }

    /* Transaction summary */
    .transaction-summary {
        padding: 1.25rem;
    }

    .transaction-box {
        height: 100%;
        padding: 1rem 1.1rem;
        border: 1px solid var(--customer-border);
        border-radius: 0.8rem;
        background: var(--customer-bg);
    }

    .transaction-label {
        color: var(--customer-muted);
        font-size: 0.78rem;
        margin-bottom: 0.3rem;
    }

    .transaction-value {
        color: var(--customer-text);
        font-size: 1.45rem;
        font-weight: 750;
    }

    /* Points history */
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
        padding: 0.4rem 0.75rem;
        border-radius: 999px;
        background: #f1f5f9;
        color: #475569;
        font-size: 0.75rem;
        font-weight: 600;
    }

    .points-table {
        margin: 0;
    }

    .points-table thead th {
        padding: 0.85rem 1rem;
        background: #f8fafc;
        border-bottom: 1px solid var(--customer-border);
        color: #64748b;
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.025em;
        white-space: nowrap;
    }

    .points-table tbody td {
        padding: 0.9rem 1rem;
        border-color: #f0f3f7;
        color: var(--customer-text);
        font-size: 0.86rem;
        vertical-align: middle;
    }

    .points-table tbody tr {
        transition: background 0.15s ease;
    }

    .points-table tbody tr:hover {
        background: #f8fbff;
    }

    .invoice-link {
        color: var(--customer-primary);
        text-decoration: none;
        font-weight: 700;
    }

    .invoice-link:hover {
        text-decoration: underline;
    }

    .point-badge {
        display: inline-flex;
        align-items: center;
        padding: 0.35rem 0.7rem;
        border-radius: 999px;
        font-size: 0.74rem;
        font-weight: 700;
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
        color: var(--customer-text);
        font-weight: 700;
        margin-bottom: 0.35rem;
    }

    .empty-history p {
        color: var(--customer-muted);
        margin: 0;
        font-size: 0.85rem;
    }

    /* Pagination */
    .points-pagination {
        padding: 1rem 1.25rem;
        border-top: 1px solid var(--customer-border);
        background: #fff;
    }

    .points-pagination nav {
        display: flex;
        justify-content: flex-end;
    }

    /* Modal */
    #hoaDonModal .modal-content {
        border: 0;
        border-radius: 1rem;
        overflow: hidden;
        box-shadow: 0 1rem 3rem rgba(15, 23, 42, 0.2);
    }

    /* Responsive */
    @media (max-width: 991.98px) {
        .customer-profile-body {
            padding: 1.5rem;
        }

        .customer-stat-value {
            font-size: 1.35rem;
        }
    }

    @media (max-width: 767.98px) {
        .customer-page-header {
            align-items: flex-start !important;
        }

        .customer-page-header .d-flex {
            width: 100%;
        }

        .customer-action-btn {
            flex: 1;
        }

        .points-table {
            min-width: 700px;
        }

        .points-table-wrapper {
            overflow-x: auto;
        }
    }

    @media (max-width: 575.98px) {
        .customer-profile-body {
            padding: 1.25rem;
        }

        .customer-stat-card {
            padding: 1rem;
        }

        .customer-stat-icon {
            width: 44px;
            height: 44px;
            flex-basis: 44px;
        }

        .customer-stat-value {
            font-size: 1.2rem;
        }

        .transaction-summary {
            padding: 1rem;
        }
    }
</style>

@php
    $initials = collect(explode(' ', trim($khachHang->ten_khach_hang)))
        ->filter()
        ->map(fn ($part) => mb_substr($part, 0, 1))
        ->take(2)
        ->implode('');
@endphp

<div class="customer-detail-page">

    {{-- =========================
         HEADER
    ========================== --}}
    <div class="customer-page-header d-flex justify-content-between align-items-start flex-wrap gap-3">

        <div>
            <h4 class="fw-bold mb-1">Chi tiết khách hàng</h4>

            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item">
                        <a href="{{ url('admin/dashboard') }}">Dashboard</a>
                    </li>

                    <li class="breadcrumb-item">
                        <a href="{{ route('khach-hang.index') }}">Khách hàng</a>
                    </li>

                    <li class="breadcrumb-item active">
                        Chi tiết
                    </li>
                </ol>
            </nav>
        </div>

        <div class="d-flex gap-2">
            <a href="{{ route('khach-hang.edit', $khachHang) }}"
               class="btn btn-primary customer-action-btn">
                <i class="fas fa-pen me-2"></i>
                Chỉnh sửa
            </a>

            <a href="{{ route('khach-hang.index') }}"
               class="btn btn-outline-secondary customer-action-btn">
                <i class="fas fa-arrow-left me-2"></i>
                Quay lại
            </a>
        </div>

    </div>


    {{-- =========================
         MAIN CONTENT
    ========================== --}}
    <div class="row g-4">

        {{-- =========================
             CUSTOMER INFORMATION
        ========================== --}}
        <div class="col-lg-4">

            <div class="customer-card customer-profile">

                <div class="customer-profile-body">

                    <div class="customer-avatar">
                        {{ $initials ?: 'KH' }}
                    </div>

                    <div class="text-center">

                        <div class="customer-name">
                            {{ $khachHang->ten_khach_hang }}
                        </div>

                        <div class="customer-code">
                            Mã KH{{ str_pad((string) $khachHang->id, 4, '0', STR_PAD_LEFT) }}
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


                    {{-- Customer information --}}
                    <div class="customer-info">

                        {{-- Phone --}}
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


                        {{-- Email --}}
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


                        {{-- Address --}}
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


                        {{-- Note --}}
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


        {{-- =========================
             RIGHT CONTENT
        ========================== --}}
        <div class="col-lg-8">

            {{-- =========================
                 STATISTICS
            ========================== --}}
            <div class="row g-3">

                {{-- Points --}}
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
                                    {{ number_format($khachHang->diem_tich_luy ?? 0) }}
                                </h3>

                            </div>

                        </div>

                    </div>

                </div>


                {{-- Spending --}}
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
                                    {{ number_format($khachHang->tong_chi_tieu ?? 0, 0, ',', '.') }}
                                    <small class="fs-6">đ</small>
                                </h3>

                            </div>

                        </div>

                    </div>

                </div>


                {{-- Invoices --}}
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
                                    {{ number_format($tongSoHoaDon) }}
                                </h3>

                            </div>

                        </div>

                    </div>

                </div>

            </div>


            {{-- =========================
                 POINT HISTORY
            ========================== --}}
            <div class="customer-card points-history-card"
                 id="lich-su-tich-diem">

                <div class="customer-card-header">

                    <div class="points-history-header">

                        <h5>
                            <i class="fas fa-clock-rotate-left text-primary me-2"></i>
                            Lịch sử tích điểm
                        </h5>

                        <span class="points-count">
                            {{ $lichSuTichDiems->total() }} giao dịch
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
                                        trim($lichSu->loai_bien_dong ?? '')
                                    );

                                    $isCong =
                                        in_array(
                                            $loaiBienDong,
                                            ['cong', 'tang', 'tăng']
                                        )
                                        ||
                                        (
                                            $loaiBienDong === ''
                                            &&
                                            $lichSu->so_diem >= 0
                                        );

                                    if (
                                        in_array(
                                            $loaiBienDong,
                                            ['tru', 'thu']
                                        )
                                    ) {
                                        $isCong = false;
                                    }

                                    $labelBienDong =
                                        $isCong
                                            ? 'Tăng điểm'
                                            : 'Giảm điểm';

                                @endphp


                                <tr>

                                    {{-- Date --}}
                                    <td>
                                        <span class="text-nowrap">
                                            {{ optional($lichSu->created_at)->format('d/m/Y H:i') }}
                                        </span>
                                    </td>


                                    {{-- Invoice --}}
                                    <td>

                                        @if($lichSu->hoaDon)

                                            <button
                                                type="button"
                                                class="btn btn-link p-0 invoice-link hoa-don-modal-button"
                                                data-id="{{ $lichSu->hoaDon->id }}"
                                            >
                                                #HD{{ str_pad(
                                                    (string) $lichSu->hoaDon->id,
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


                                    {{-- Type --}}
                                    <td>

                                        <span class="point-badge {{ $isCong ? 'up' : 'down' }}">

                                            <i class="fas {{ $isCong ? 'fa-arrow-up' : 'fa-arrow-down' }} me-1"></i>

                                            {{ $labelBienDong }}

                                        </span>

                                    </td>


                                    {{-- Points --}}
                                    <td>

                                        <span class="{{ $isCong ? 'point-value-up' : 'point-value-down' }}">

                                            {{ $isCong ? '+' : '-' }}{{ number_format(abs($lichSu->so_diem)) }}

                                        </span>

                                    </td>


                                    {{-- Reason --}}
                                    <td>

                                        <span class="text-muted">
                                            {{ $lichSu->ly_do ?: '-' }}
                                        </span>

                                    </td>

                                </tr>


                            @empty

                                {{-- Không có dữ liệu mẫu --}}
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


                {{-- Pagination --}}
                @if($lichSuTichDiems->hasPages())

                    <div class="points-pagination">

                        {{ $lichSuTichDiems->links() }}

                    </div>

                @endif

            </div>

        </div>

    </div>

</div>


{{-- =========================
     INVOICE MODAL
========================== --}}
<div
    class="modal fade"
    id="hoaDonModal"
    tabindex="-1"
    aria-labelledby="hoaDonModalLabel"
    aria-hidden="true"
>

    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">

        <div
            class="modal-content"
            id="hoa-don-modal-content"
        >

            <div class="modal-body text-center py-5">

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

    const modalEl =
        document.getElementById('hoaDonModal');

    const modalBody =
        document.getElementById('hoa-don-modal-content');

    if (!modalEl || !modalBody) {
        return;
    }


    const hoaDonModal =
        new bootstrap.Modal(modalEl, {
            backdrop: 'static',
            keyboard: false
        });


    document
        .querySelectorAll('.hoa-don-modal-button')
        .forEach(function (button) {

            button.addEventListener('click', function () {

                const hoaDonId =
                    this.dataset.id;

                if (!hoaDonId) {
                    return;
                }


                /* Loading */
                modalBody.innerHTML = `
                    <div class="modal-body text-center py-5">
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


                hoaDonModal.show();


                /* Load invoice */
                fetch(
                    `{{ url('admin/hoa-don') }}/${hoaDonId}/modal`
                )

                .then(function (response) {

                    if (!response.ok) {
                        throw new Error(
                            'Lỗi khi tải chi tiết hóa đơn.'
                        );
                    }

                    return response.text();

                })

                .then(function (html) {

                    modalBody.innerHTML = html;

                })

                .catch(function (error) {

                    modalBody.innerHTML = `
                        <div class="modal-body">
                            <div
                                class="alert alert-danger mb-0"
                                role="alert"
                            >
                                <i class="fas fa-circle-exclamation me-2"></i>
                                Không thể tải chi tiết hóa đơn.
                                Vui lòng thử lại.
                            </div>
                        </div>
                    `;

                    console.error(error);

                });

            });

        });

});

</script>

@endsection