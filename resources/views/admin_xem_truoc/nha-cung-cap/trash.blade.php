@extends('admin_xem_truoc.layouts.admin')

@section('title', 'Thùng rác Nhà cung cấp')

@section('content')

<style>
    .trash-table th,
    .trash-table td {
        vertical-align: middle;
        padding: 12px 10px;
        white-space: nowrap;
    }
    .trash-table th { font-size:12px; text-transform:uppercase; color:#6b7280; font-weight:700 }
    .trash-table th:nth-child(1), .trash-table td:nth-child(1){ width:40px; text-align:center }
    .trash-table th:nth-child(2), .trash-table td:nth-child(2){ width:80px }
    .trash-table th:nth-child(3), .trash-table td:nth-child(3){ width:35%; white-space:normal }
    .action-buttons { display:flex; gap:8px; align-items:center }
    .action-buttons form{ margin:0 }
</style>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-1">Thùng rác - Nhà cung cấp</h4>

    <a href="{{ url('/admin/kho-hang/nha-cung-cap') }}" class="btn btn-secondary">Quay lại danh sách</a>
</div>

<div class="card table-admin">
    <div class="card-body">
        <form action="{{ url('/admin/kho-hang/nha-cung-cap/bulk-restore') }}" method="POST" id="bulkRestoreForm">
            @csrf
            <input type="hidden" name="ids" id="bulkRestoreIds">
        </form>

        <form action="{{ url('/admin/kho-hang/nha-cung-cap/bulk-force') }}" method="POST" id="bulkForceForm">
            @csrf
            @method('DELETE')
            <input type="hidden" name="ids" id="bulkForceIds">
        </form>

        <div class="mb-3 d-flex gap-2">
            <button type="button" class="btn btn-sm btn-outline-success" id="btnBulkRestore">Khôi phục đã chọn</button>
            <button type="button" class="btn btn-sm btn-outline-danger" id="btnBulkForce">Xóa vĩnh viễn đã chọn</button>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle trash-table">
                <thead>
                    <tr>
                        <th><input type="checkbox" id="selectAll"></th>
                        <th>#</th>
                        <th>Tên</th>
                        <th>Email / SĐT</th>
                        <th>Đã xóa lúc</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($items as $sup)
                        <tr>
                            <td><input type="checkbox" class="selectItem" value="{{ $sup->id }}"></td>
                            <td>{{ $sup->id }}</td>
                            <td>{{ $sup->ten_nha_cung_cap }}</td>
                            <td>
                                <div>{{ $sup->email ?? '-' }}</div>
                                <div class="text-muted">{{ $sup->so_dien_thoai ?? '-' }}</div>
                            </td>
                            <td>{{ $sup->deleted_at ? $sup->deleted_at->format('d/m/Y H:i') : '-' }}</td>
                            <td>
                                <div class="action-buttons">
                                    <form action="{{ url('/admin/kho-hang/nha-cung-cap/'.$sup->id.'/restore') }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-success">Khôi phục</button>
                                    </form>

                                    <form action="{{ url('/admin/kho-hang/nha-cung-cap/'.$sup->id.'/force') }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Xóa vĩnh viễn nhà cung cấp này?')">Xóa vĩnh viễn</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-4">Thùng rác trống.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="d-flex justify-content-end mt-3">{{ $items->links() }}</div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function(){
        const selectAll = document.getElementById('selectAll');
        const btnBulkRestore = document.getElementById('btnBulkRestore');
        const btnBulkForce = document.getElementById('btnBulkForce');

        if (selectAll) {
            selectAll.addEventListener('change', function(){
                document.querySelectorAll('.selectItem').forEach(function(it){ it.checked = selectAll.checked; });
            });
        }

        function collectIds(){
            return Array.from(document.querySelectorAll('.selectItem:checked')).map(it => it.value);
        }

        if (btnBulkRestore) {
            btnBulkRestore.addEventListener('click', function(e){
                e.preventDefault();
                const ids = collectIds();
                if (!ids.length) { alert('Vui lòng chọn ít nhất 1 mục'); return; }
                document.getElementById('bulkRestoreIds').value = JSON.stringify(ids);
                document.getElementById('bulkRestoreForm').submit();
            });
        }

        if (btnBulkForce) {
            btnBulkForce.addEventListener('click', function(e){
                e.preventDefault();
                const ids = collectIds();
                if (!ids.length) { alert('Vui lòng chọn ít nhất 1 mục'); return; }
                if (!confirm('Xác nhận xóa vĩnh viễn các nhà cung cấp đã chọn?')) return;
                document.getElementById('bulkForceIds').value = JSON.stringify(ids);
                document.getElementById('bulkForceForm').submit();
            });
        }
    });
</script>

@endsection
