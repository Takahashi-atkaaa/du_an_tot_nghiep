@extends('admin_xem_truoc.layouts.admin')

@section('title', 'Thùng rác khách hàng')

@section('content')

<style>
    /* =========================================
       CUSTOMER TRASH PAGE
    ========================================= */

    .customer-trash-page {
        --trash-primary: #0d6efd;
        --trash-primary-light: #eef5ff;
        --trash-danger: #dc3545;
        --trash-danger-light: #fdebec;
        --trash-success: #198754;
        --trash-success-light: #eaf8f0;
        --trash-dark: #212529;
        --trash-text: #1e293b;
        --trash-muted: #64748b;
        --trash-border: #e9eef5;
        --trash-bg: #f8fafc;
    }

    /* =========================
       PAGE HEADER
    ========================== */

    .trash-page-header {
        margin-bottom: 1.5rem;
    }

    .trash-page-title {
        margin: 0;
        color: var(--trash-text);
        font-size: 1.35rem;
        font-weight: 700;
        letter-spacing: -0.02em;
    }

    .trash-page-subtitle {
        margin-top: 0.35rem;
        color: var(--trash-muted);
        font-size: 0.85rem;
    }

    .trash-back-btn {
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        padding: 0.6rem 1rem;
        border-radius: 0.65rem;
        font-size: 0.875rem;
        font-weight: 600;
    }

    /* =========================
       ALERT
    ========================== */

    .trash-alert {
        border: 0;
        border-radius: 0.75rem;
        padding: 0.85rem 1rem;
        margin-bottom: 1.25rem;
        box-shadow: 0 3px 12px rgba(15, 23, 42, 0.04);
    }

    /* =========================
       MAIN CARD
    ========================== */

    .trash-card {
        border: 1px solid var(--trash-border);
        border-radius: 1rem;
        background: #fff;
        box-shadow: 0 4px 18px rgba(15, 23, 42, 0.05);
        overflow: hidden;
    }

    .trash-card-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        flex-wrap: wrap;
        padding: 1rem 1.25rem;
        border-bottom: 1px solid var(--trash-border);
        background: #fff;
    }

    .trash-card-title {
        display: flex;
        align-items: center;
        gap: 0.6rem;
        margin: 0;
        color: var(--trash-text);
        font-size: 1rem;
        font-weight: 700;
    }

    .trash-card-title-icon {
        width: 34px;
        height: 34px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 0.6rem;
        background: var(--trash-danger-light);
        color: var(--trash-danger);
        font-size: 0.9rem;
    }

    .trash-count {
        padding: 0.4rem 0.75rem;
        border-radius: 999px;
        background: #f1f5f9;
        color: #475569;
        font-size: 0.75rem;
        font-weight: 600;
    }

    /* =========================
       TABLE
    ========================== */

    .trash-table-wrapper {
        overflow-x: auto;
    }

    .trash-table {
        min-width: 850px;
        margin: 0;
    }

    .trash-table thead th {
        padding: 0.85rem 1rem;
        background: #f8fafc;
        border-bottom: 1px solid var(--trash-border);
        color: #64748b;
        font-size: 0.74rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.025em;
        white-space: nowrap;
        vertical-align: middle;
    }

    .trash-table tbody td {
        padding: 0.9rem 1rem;
        border-color: #f0f3f7;
        color: var(--trash-text);
        font-size: 0.86rem;
        vertical-align: middle;
    }

    .trash-table tbody tr {
        transition: background-color 0.15s ease;
    }

    .trash-table tbody tr:hover {
        background: #fafcff;
    }

    /* =========================
       CUSTOMER
    ========================== */

    .customer-id {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 42px;
        height: 30px;
        padding: 0 0.6rem;
        border-radius: 0.5rem;
        background: #f1f5f9;
        color: #475569;
        font-size: 0.78rem;
        font-weight: 700;
    }

    .customer-name-wrapper {
        display: flex;
        align-items: center;
        gap: 0.7rem;
        min-width: 190px;
    }

    .customer-avatar-small {
        width: 38px;
        height: 38px;
        flex: 0 0 38px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        background: #eef2f7;
        color: #64748b;
        font-size: 0.78rem;
        font-weight: 800;
    }

    .customer-name {
        color: var(--trash-text);
        font-weight: 650;
    }

    .customer-deleted-label {
        display: block;
        margin-top: 0.15rem;
        color: #94a3b8;
        font-size: 0.72rem;
    }

    /* =========================
       CONTACT
    ========================== */

    .customer-phone {
        color: var(--trash-text);
        font-weight: 500;
        white-space: nowrap;
    }

    .customer-email {
        max-width: 220px;
        color: var(--trash-muted);
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    /* =========================
       DELETED DATE
    ========================== */

    .deleted-date {
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        color: var(--trash-muted);
        white-space: nowrap;
    }

    .deleted-date i {
        color: #94a3b8;
    }

    /* =========================
       ACTIONS
    ========================== */

    .trash-actions {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 0.5rem;
        white-space: nowrap;
    }

    .trash-action-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.4rem;
        min-height: 34px;
        padding: 0.4rem 0.7rem;
        border-radius: 0.55rem;
        font-size: 0.76rem;
        font-weight: 600;
        transition: all 0.15s ease;
    }

    .trash-action-btn:hover {
        transform: translateY(-1px);
    }

    .restore-btn {
        color: var(--trash-success);
        background: var(--trash-success-light);
        border: 1px solid #d7f0e2;
    }

    .restore-btn:hover {
        color: #146c43;
        background: #dff5e8;
        border-color: #c8e9d6;
    }

    .delete-btn {
        color: #fff;
        background: var(--trash-dark);
        border: 1px solid var(--trash-dark);
    }

    .delete-btn:hover {
        color: #fff;
        background: #343a40;
        border-color: #343a40;
    }

    /* =========================
       EMPTY STATE
    ========================== */

    .trash-empty {
        padding: 4rem 1rem;
        text-align: center;
    }

    .trash-empty-icon {
        width: 70px;
        height: 70px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 1rem;
        border-radius: 50%;
        background: #f1f5f9;
        color: #94a3b8;
        font-size: 1.6rem;
    }

    .trash-empty h5 {
        margin-bottom: 0.4rem;
        color: var(--trash-text);
        font-size: 1rem;
        font-weight: 700;
    }

    .trash-empty p {
        margin: 0;
        color: var(--trash-muted);
        font-size: 0.85rem;
    }

    /* =========================
       PAGINATION
    ========================== */

    .trash-pagination {
        padding: 1rem 1.25rem;
        border-top: 1px solid var(--trash-border);
        background: #fff;
    }

    .trash-pagination nav {
        display: flex;
        justify-content: flex-end;
    }

    /* =========================
       RESPONSIVE
    ========================== */

    @media (max-width: 767.98px) {

        .trash-page-header {
            align-items: flex-start !important;
        }

        .trash-page-header .trash-back-btn {
            width: 100%;
            justify-content: center;
        }

        .trash-card-header {
            align-items: flex-start;
        }

        .trash-actions {
            justify-content: flex-start;
        }
    }
</style>


<div class="customer-trash-page">

    {{-- =========================
         HEADER
    ========================== --}}
    <div class="trash-page-header d-flex justify-content-between align-items-start flex-wrap gap-3">

        <div>
            <h4 class="trash-page-title">
                <i class="fas fa-trash-can text-danger me-2"></i>
                Thùng rác khách hàng
            </h4>

            <div class="trash-page-subtitle">
                Quản lý các khách hàng đã được xóa mềm
            </div>
        </div>

        <a href="{{ route('khach-hang.index') }}"
           class="btn btn-outline-secondary trash-back-btn">

            <i class="fas fa-arrow-left"></i>

            Quay lại

        </a>

    </div>


    {{-- =========================
         SUCCESS MESSAGE
    ========================== --}}
    @if(session('success'))

        <div class="alert alert-success trash-alert">

            <i class="fas fa-circle-check me-2"></i>

            {{ session('success') }}

        </div>

    @endif


    {{-- =========================
         TRASH CARD
    ========================== --}}
    <div class="trash-card">

        {{-- Card header --}}
        <div class="trash-card-header">

            <h5 class="trash-card-title">

                <span class="trash-card-title-icon">
                    <i class="fas fa-trash-can"></i>
                </span>

                Khách hàng đã xóa

            </h5>

            <span class="trash-count">
                {{ $khachHangs->total() }} khách hàng
            </span>

        </div>


        {{-- =========================
             TABLE
        ========================== --}}
        <div class="trash-table-wrapper">

            <table class="table trash-table align-middle">

                <thead>

                    <tr>

                        <th width="80">
                            ID
                        </th>

                        <th>
                            Khách hàng
                        </th>

                        <th>
                            Số điện thoại
                        </th>

                        <th>
                            Email
                        </th>

                        <th>
                            Ngày xóa
                        </th>

                        <th width="230" class="text-end">
                            Thao tác
                        </th>

                    </tr>

                </thead>


                <tbody>

                    @forelse($khachHangs as $khachHang)

                        @php
                            $initials = collect(
                                explode(
                                    ' ',
                                    trim($khachHang->ten_khach_hang)
                                )
                            )
                            ->filter()
                            ->map(
                                fn ($part) =>
                                    mb_substr($part, 0, 1)
                            )
                            ->take(2)
                            ->implode('');
                        @endphp


                        <tr>

                            {{-- ID --}}
                            <td>

                                <span class="customer-id">
                                    #{{ $khachHang->id }}
                                </span>

                            </td>


                            {{-- Customer --}}
                            <td>

                                <div class="customer-name-wrapper">

                                    <div class="customer-avatar-small">
                                        {{ $initials ?: 'KH' }}
                                    </div>

                                    <div>

                                        <div class="customer-name">
                                            {{ $khachHang->ten_khach_hang }}
                                        </div>

                                        <span class="customer-deleted-label">
                                            Đã xóa khỏi danh sách
                                        </span>

                                    </div>

                                </div>

                            </td>


                            {{-- Phone --}}
                            <td>

                                <span class="customer-phone">
                                    {{ $khachHang->so_dien_thoai ?: '-' }}
                                </span>

                            </td>


                            {{-- Email --}}
                            <td>

                                <div class="customer-email"
                                     title="{{ $khachHang->email }}">

                                    {{ $khachHang->email ?: '-' }}

                                </div>

                            </td>


                            {{-- Deleted date --}}
                            <td>

                                <span class="deleted-date">

                                    <i class="far fa-clock"></i>

                                    {{ optional($khachHang->deleted_at)->format('d/m/Y H:i') }}

                                </span>

                            </td>


                            {{-- Actions --}}
                            <td>

                                <div class="trash-actions">

                                    {{-- Restore --}}
                                    <form
                                        action="{{ route('khach-hang.restore', $khachHang->id) }}"
                                        method="POST"
                                        class="d-inline"
                                    >

                                        @csrf
                                        @method('PUT')

                                        <button
                                            type="submit"
                                            class="btn trash-action-btn restore-btn"
                                        >

                                            <i class="fas fa-rotate-left"></i>

                                            Khôi phục

                                        </button>

                                    </form>


                                    {{-- Permanent delete --}}
                                    <form
                                        action="{{ route('khach-hang.force-delete', $khachHang->id) }}"
                                        method="POST"
                                        class="d-inline"
                                        onsubmit="return confirm('Bạn có chắc chắn muốn xóa vĩnh viễn khách hàng này? Dữ liệu sẽ không thể khôi phục.')"
                                    >

                                        @csrf
                                        @method('DELETE')

                                        <button
                                            type="submit"
                                            class="btn trash-action-btn delete-btn"
                                        >

                                            <i class="fas fa-trash-can"></i>

                                            Xóa vĩnh viễn

                                        </button>

                                    </form>

                                </div>

                            </td>

                        </tr>


                    @empty

                        <tr>

                            <td colspan="6">

                                <div class="trash-empty">

                                    <div class="trash-empty-icon">
                                        <i class="fas fa-trash-can"></i>
                                    </div>

                                    <h5>
                                        Thùng rác đang trống
                                    </h5>

                                    <p>
                                        Không có khách hàng nào đã bị xóa.
                                    </p>

                                </div>

                            </td>

                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>


        {{-- =========================
             PAGINATION
        ========================== --}}
        @if($khachHangs->hasPages())

            <div class="trash-pagination">

                {{ $khachHangs->links() }}

            </div>

        @endif

    </div>

</div>

@endsection