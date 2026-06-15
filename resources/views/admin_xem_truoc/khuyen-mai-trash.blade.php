@extends('admin_xem_truoc.layouts.admin')

@section('title', 'Thùng rác Khuyến mãi')

@section('content')

@php
    $loaiMap = [
        'percent' => 'Phần trăm',
        'phan_tram' => 'Phần trăm',
        'percentage' => 'Phần trăm',
        'fixed' => 'Giảm tiền',
        'tien_mat' => 'Giảm tiền',
        'so_tien' => 'Giảm tiền',
        'qua_tang' => 'Quà tặng',
        'gift' => 'Quà tặng',
    ];
@endphp

<style>
    .trash-table th,
    .trash-table td {
        vertical-align: middle;
        padding: 14px 12px;
        white-space: nowrap;
    }

    .trash-table th {
        font-size: 12px;
        text-transform: uppercase;
        color: #6b7280;
        font-weight: 700;
    }

    .trash-table th:nth-child(1),
    .trash-table td:nth-child(1) {
        width: 40px;
        text-align: center;
    }

    .trash-table th:nth-child(2),
    .trash-table td:nth-child(2) {
        width: 70px;
        text-align: center;
    }

    .trash-table th:nth-child(3),
    .trash-table td:nth-child(3) {
        width: 45%;
        white-space: normal;
        word-break: break-word;
    }

    .trash-table th:nth-child(4),
    .trash-table td:nth-child(4) {
        width: 160px;
    }

    .trash-table th:nth-child(5),
    .trash-table td:nth-child(5) {
        width: 180px;
    }

    .trash-table th:nth-child(6),
    .trash-table td:nth-child(6) {
        width: 220px;
    }

    .action-buttons {
        display: flex;
        gap: 8px;
        align-items: center;
        flex-wrap: nowrap;
    }

    .action-buttons form {
        margin: 0;
    }
</style>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-1">Thùng rác - Khuyến mãi</h4>

    <a href="{{ url('/admin/khuyen-mai') }}" class="btn btn-secondary">
        Quay lại danh sách
    </a>
</div>

<div class="card table-admin">
    <div class="card-body">
        <form action="{{ url('/admin/khuyen-mai/bulk-restore') }}" method="POST" id="bulkRestoreForm">
            @csrf
            <input type="hidden" name="ids" id="bulkRestoreIds">
        </form>

        <form action="{{ url('/admin/khuyen-mai/bulk-force') }}" method="POST" id="bulkForceForm">
            @csrf
            @method('DELETE')
            <input type="hidden" name="ids" id="bulkForceIds">
        </form>

        <div class="mb-3 d-flex gap-2">
            <button type="button" class="btn btn-sm btn-outline-success" id="btnBulkRestore">
                Khôi phục đã chọn
            </button>

            <button type="button" class="btn btn-sm btn-outline-danger" id="btnBulkForce">
                Xóa vĩnh viễn đã chọn
            </button>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle trash-table">
                <thead>
                    <tr>
                        <th>
                            <input type="checkbox" id="selectAll">
                        </th>
                        <th>#</th>
                        <th>Tên</th>
                        <th>Loại</th>
                        <th>Đã xóa lúc</th>
                        <th>Hành động</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($items as $promo)
                        <tr>
                            <td>
                                <input type="checkbox" class="selectItem" value="{{ $promo->id }}">
                            </td>

                            <td>{{ $promo->id }}</td>

                            <td>{{ $promo->ten_chuong_trinh }}</td>

                            <td>
                                {{ $loaiMap[$promo->loai_giam_gia] ?? $promo->loai_giam_gia }}
                            </td>

                            <td>
                                {{ $promo->deleted_at ? $promo->deleted_at->format('d/m/Y H:i') : '-' }}
                            </td>

                            <td>
                                <div class="action-buttons">
                                    <form action="{{ route('khuyen-mai.restore', $promo->id) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-success">
                                            Khôi phục
                                        </button>
                                    </form>

                                    <form action="{{ route('khuyen-mai.forceDelete', $promo->id) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Xóa vĩnh viễn chương trình này?')">
                                            Xóa vĩnh viễn
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-4">
                                Thùng rác trống.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="d-flex justify-content-end mt-3">
            {{ $items->links() }}
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const selectAll = document.getElementById('selectAll');
        const btnBulkRestore = document.getElementById('btnBulkRestore');
        const btnBulkForce = document.getElementById('btnBulkForce');

        if (selectAll) {
            selectAll.addEventListener('change', function () {
                document.querySelectorAll('.selectItem').forEach(function (item) {
                    item.checked = selectAll.checked;
                });
            });
        }

        function collectIds() {
            return Array.from(document.querySelectorAll('.selectItem:checked')).map(function (item) {
                return item.value;
            });
        }

        if (btnBulkRestore) {
            btnBulkRestore.addEventListener('click', function (e) {
                e.preventDefault();

                const ids = collectIds();

                if (!ids.length) {
                    alert('Vui lòng chọn ít nhất 1 mục');
                    return;
                }

                document.getElementById('bulkRestoreIds').value = JSON.stringify(ids);
                document.getElementById('bulkRestoreForm').submit();
            });
        }

        if (btnBulkForce) {
            btnBulkForce.addEventListener('click', function (e) {
                e.preventDefault();

                const ids = collectIds();

                if (!ids.length) {
                    alert('Vui lòng chọn ít nhất 1 mục');
                    return;
                }

                if (!confirm('Xác nhận xóa vĩnh viễn các chương trình đã chọn?')) {
                    return;
                }

                document.getElementById('bulkForceIds').value = JSON.stringify(ids);
                document.getElementById('bulkForceForm').submit();
            });
        }
    });
</script>

@endsection