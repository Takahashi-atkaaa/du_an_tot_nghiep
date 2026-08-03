@extends('admin_xem_truoc.layouts.admin')

@section('title', 'Thùng rác kiểm kho - SmartMart')

@section('styles')
<style>
    .kkhs-page-head {
        display: flex; justify-content: space-between; align-items: flex-end;
        gap: 12px; flex-wrap: wrap; margin-bottom: 16px;
    }
    .kkhs-page-title {
        font-size: 22px; font-weight: 700; color: #1f2937;
        display: flex; align-items: center; gap: 10px; margin: 0;
    }
    .kkhs-page-title i { color: #6b7280; }
    .kkhs-breadcrumb .breadcrumb { background: transparent; padding: 0; margin: 0; }
    .kkhs-cta-group { display: flex; gap: 8px; flex-wrap: wrap; }
    .kkhs-cta {
        background: linear-gradient(135deg, #4f46e5, #6366f1);
        color: #fff; padding: 8px 16px; border-radius: 8px;
        font-weight: 600; font-size: 13.5px;
        display: inline-flex; align-items: center; gap: 6px;
        border: none; text-decoration: none;
        transition: transform .15s, box-shadow .15s;
    }
    .kkhs-cta:hover { transform: translateY(-1px); color: #fff;
        box-shadow: 0 6px 14px rgba(79, 70, 229, .35); }
    .kkhs-tool-btn {
        background: #fff; border: 1px solid #e5e7eb;
        padding: 8px 12px; border-radius: 8px;
        font-weight: 600; font-size: 13px;
        display: inline-flex; align-items: center; gap: 6px;
        color: #374151; cursor: pointer;
    }
    .kkhs-tool-btn:hover { background: #f9fafb; border-color: #d1d5db; }
    .kkhs-tool-btn.success { background: linear-gradient(135deg, #10b981, #059669); color: #fff; border-color: transparent; }
    .kkhs-tool-btn.success:hover { color: #fff; box-shadow: 0 4px 10px rgba(16, 185, 129, .35); }

    .kkhs-shell {
        background: #fff;
        border-radius: 10px;
        box-shadow: 0 1px 4px rgba(0,0,0,.05);
        padding: 14px 16px;
    }
    .kkhs-empty {
        text-align: center; padding: 60px 16px; color: #94a3b8;
    }
    .kkhs-empty i { font-size: 42px; margin-bottom: 10px; color: #cbd5e1; }

    .kkhs-ma { font-family: 'JetBrains Mono', 'Consolas', monospace; font-weight: 700; color: #1f2937; }
    .kkhs-user-cell { display: flex; align-items: center; gap: 8px; }
    .kkhs-avatar {
        width: 28px; height: 28px; border-radius: 50%;
        background: linear-gradient(135deg, #6366f1, #ec4899);
        color: #fff; display: inline-flex; align-items: center; justify-content: center;
        font-weight: 700; font-size: 12px; flex-shrink: 0;
    }
    .kkhs-user-name { color: #1f2937; font-size: 13.5px; font-weight: 500; }

    .kkhs-lech-khong { color: #94a3b8; }
    .kkhs-lech-duong { color: #16a34a; font-weight: 600; }
    .kkhs-lech-am    { color: #dc2626; font-weight: 600; }

    .kkhs-bulk-bar {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 10px 14px;
        margin-bottom: 12px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }

    /* cảnh báo đỏ header thùng rác */
    .kkhs-trash-banner {
        background: linear-gradient(135deg, #fef2f2, #fff7ed);
        border-left: 4px solid #ef4444;
        border-radius: 8px;
        padding: 12px 16px;
        color: #7f1d1d;
        font-size: 13.5px;
        margin-bottom: 14px;
        display: flex; align-items: center; gap: 10px;
    }
    .kkhs-trash-banner i { color: #ef4444; font-size: 18px; }
</style>
@endsection

@section('content')
{{-- ========== PAGE HEAD ========== --}}
<div class="kkhs-page-head">
    <div>
        <nav aria-label="breadcrumb" class="kkhs-breadcrumb">
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="{{ url('admin/dashboard') }}">Admin</a></li>
                <li class="breadcrumb-item"><a href="{{ url('admin/kho-hang') }}">Kho hàng</a></li>
                <li class="breadcrumb-item"><a href="{{ route('kiem-kho.history') }}">Kiểm kho</a></li>
                <li class="breadcrumb-item active">Thùng rác</li>
            </ol>
        </nav>
        <h4 class="kkhs-page-title">
            <i class="fas fa-trash-restore"></i>
            Thùng rác - Phiếu kiểm kho
        </h4>
    </div>
    <div class="kkhs-cta-group">
        <a href="{{ route('kiem-kho.history') }}" class="kkhs-tool-btn">
            <i class="fas fa-arrow-left"></i> Quay lại lịch sử
        </a>
        <button class="kkhs-tool-btn" id="btn-reload">
            <i class="fas fa-redo"></i> Tải lại
        </button>
    </div>
</div>

<div class="kkhs-trash-banner">
    <i class="fas fa-exclamation-triangle"></i>
    <div>
        Đây là danh sách các phiếu kiểm kho đã bị xóa. Bạn có thể
        <b>khôi phục</b> hoặc <b>xóa vĩnh viễn</b>. Thao tác xóa vĩnh viễn không thể hoàn tác.
    </div>
</div>

<div class="kkhs-shell">
    {{-- Thanh bulk action --}}
    <div class="kkhs-bulk-bar">
        <div class="d-flex align-items-center gap-3">
            <div class="form-check">
                <input type="checkbox" class="form-check-input" id="selectAllTrash">
                <label class="form-check-label text-muted" for="selectAllTrash">Chọn tất cả</label>
            </div>
                    <div id="bulkActionTrash" class="d-none">
                        <span class="text-muted me-2 small"><span id="selectedCountTrash">0</span> đã chọn</span>
                        <button type="button" class="btn btn-sm btn-success" onclick="submitTrashBulkAction('restore')">
                            <i class="fas fa-undo me-1"></i>Khôi phục
                        </button>
                        <button type="button" class="btn btn-sm btn-danger" onclick="submitTrashBulkAction('force_delete')">
                            <i class="fas fa-times-circle me-1"></i>Xóa vĩnh viễn
                        </button>
                    </div>
        </div>
        <div class="d-flex align-items-center gap-2">
            <span class="text-muted small">Tổng: <strong id="totalTrash">0</strong> phiếu đã xóa</span>
        </div>
    </div>

    {{-- Bảng --}}
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th style="width:40px;"></th>
                    <th style="width:50px;">#</th>
                    <th style="width:150px;">Mã phiếu</th>
                    <th>Người tạo</th>
                    <th>Trạng thái</th>
                    <th class="text-center">Số dòng</th>
                    <th class="text-end">SL lệch</th>
                    <th class="text-end">Giá trị lệch</th>
                    <th>Ngày xóa</th>
                    <th class="text-end" style="width:220px;">Hành động</th>
                </tr>
            </thead>
            <tbody id="trash-tbody">
                <tr>
                    <td colspan="10">
                        <div class="kkhs-empty">
                            <i class="fas fa-trash"></i>
                            <div class="mt-2">Thùng rác trống.</div>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="px-1 pt-2 d-flex justify-content-between align-items-center">
        <span class="text-muted small" id="thong-tin-hien-thi-trash">—</span>
        <nav id="phan-trang-trash"></nav>
    </div>
</div>

<form id="bulkTrashForm" style="display:none;">
    @csrf
    <input type="hidden" name="action" id="bulkActionInputTrash">
    <div id="selectedIdsTrashContainer"></div>
</form>
@endsection

@section('scripts')
<script>
const urlTrash = '/admin/api/kiem-kho/trash';
const urlBulk  = '/admin/api/kiem-kho/bulk-action';

function escapeHtml(s) {
    return (s ?? '').toString()
        .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;').replace(/'/g, '&#39;');
}
function fmtVND(n) { return (Number(n)||0).toLocaleString('vi-VN') + ' ₫'; }
function fmtNum(n)  { return (Number(n)||0).toLocaleString('vi-VN'); }

function statusLabel(tt) {
    if (tt === 'hoan_thanh') return { cls: 'bg-success',  label: 'Hoàn thành' };
    if (tt === 'phieu_tam')  return { cls: 'bg-warning text-dark', label: 'Phiếu tạm' };
    if (tt === 'da_huy')     return { cls: 'bg-secondary', label: 'Đã hủy' };
    return { cls: 'bg-light text-dark', label: tt || '—' };
}

let currentPageTrash = 1, lastPageTrash = 1;

async function loadTrash(page = 1) {
    try {
        const res = await axios.get(urlTrash, {
            params: { page, q: document.getElementById('search-trash')?.value?.trim() || '' },
        });
        const data = res.data;
        const tbody = document.getElementById('trash-tbody');

        if (!data.data || data.data.length === 0) {
            tbody.innerHTML = `<tr><td colspan="10"><div class="kkhs-empty">
                <i class="fas fa-trash"></i>
                <div class="mt-2">Thùng rác trống.</div>
            </div></td></tr>`;
            document.getElementById('thong-tin-hien-thi-trash').textContent = 'Hiển thị 0 / 0 phiếu';
            document.getElementById('phan-trang-trash').innerHTML = '';
            document.getElementById('totalTrash').textContent = 0;
            document.getElementById('selectAllTrash').checked = false;
            document.getElementById('bulkActionTrash').classList.add('d-none');
            return;
        }

        tbody.innerHTML = data.data.map((p, idx) => {
            const slLech = Number(p.tong_sl_lech) || 0;
            const lClass = slLech < 0 ? 'kkhs-lech-am' : (slLech > 0 ? 'kkhs-lech-duong' : 'kkhs-lech-khong');
            const lText = (slLech === 0 ? '0' : (slLech > 0 ? '+' : '') + slLech);
            const gvLech = Number(p.tong_gia_tri_lech) || 0;
            const pill = statusLabel(p.trang_thai);
            const avatar = (p.nguoi_tao || 'U').trim().charAt(0).toUpperCase();

            return `
            <tr data-id="${p.id}">
                <td><input type="checkbox" class="form-check-input trash-check" data-id="${p.id}"></td>
                <td class="text-center text-muted">${(page - 1) * (data.per_page || 15) + idx + 1}</td>
                <td><span class="kkhs-ma">${escapeHtml(p.ma_kiem_kho)}</span></td>
                <td>
                    <div class="kkhs-user-cell">
                        <div class="kkhs-avatar">${avatar}</div>
                        <div class="kkhs-user-name">${escapeHtml(p.nguoi_tao || 'N/A')}</div>
                    </div>
                </td>
                <td><span class="badge ${pill.cls}">${pill.label}</span></td>
                <td class="text-center">${p.so_dong}</td>
                <td class="text-end ${lClass}">${lText}</td>
                <td class="text-end ${lClass}">${(gvLech >= 0 ? '+' : '-') + fmtVND(Math.abs(gvLech))}</td>
                <td><span class="small text-muted">${escapeHtml(p.deleted_at || '—')}</span></td>
                <td class="text-end">
                    <div class="d-flex gap-1 justify-content-end">
                        <button class="btn btn-sm btn-outline-success" onclick="restoreOne(${p.id})" title="Khôi phục">
                            <i class="fas fa-undo"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-danger" onclick="forceDeleteOne(${p.id}, '${escapeHtml(p.ma_kiem_kho)}')" title="Xóa vĩnh viễn">
                            <i class="fas fa-times-circle"></i>
                        </button>
                    </div>
                </td>
            </tr>`;
        }).join('');

        document.getElementById('totalTrash').textContent = data.total;
        document.getElementById('thong-tin-hien-thi-trash').textContent =
            `Trang ${data.current_page}/${data.last_page} - Tổng ${data.total} phiếu đã xóa`;
        currentPageTrash = data.current_page;
        lastPageTrash = data.last_page;
        renderPaginationTrash();

        document.querySelectorAll('.trash-check').forEach(cb => {
            cb.addEventListener('change', updateTrashSelected);
        });
        document.getElementById('selectAllTrash').checked = false;
        updateTrashSelected();
    } catch (e) {
        console.error(e);
        toastr.error('Lỗi tải thùng rác: ' + e.message);
    }
}

function renderPaginationTrash() {
    const nav = document.getElementById('phan-trang-trash');
    if (lastPageTrash <= 1) { nav.innerHTML = ''; return; }
    let html = '<ul class="pagination pagination-sm mb-0">';
    for (let i = 1; i <= lastPageTrash; i++) {
        const active = i === currentPageTrash ? 'active' : '';
        html += `<li class="page-item ${active}"><a class="page-link" href="#" onclick="loadTrash(${i}); return false;">${i}</a></li>`;
    }
    html += '</ul>';
    nav.innerHTML = html;
}

function updateTrashSelected() {
    const total = document.querySelectorAll('.trash-check').length;
    const checked = document.querySelectorAll('.trash-check:checked').length;
    document.getElementById('selectedCountTrash').textContent = checked;
    const bulk = document.getElementById('bulkActionTrash');
    if (checked > 0) bulk.classList.remove('d-none');
    else bulk.classList.add('d-none');
    const selectAll = document.getElementById('selectAllTrash');
    selectAll.checked = total > 0 && checked === total;
    selectAll.indeterminate = checked > 0 && checked < total;
}

async function restoreOne(id) {
    Swal.fire({
        icon: 'question', title: 'Khôi phục phiếu?',
        text: 'Phiếu sẽ được đưa trở lại danh sách chính.',
        showCancelButton: true, confirmButtonText: 'Khôi phục', cancelButtonText: 'Hủy',
    }).then(async r => {
        if (!r.isConfirmed) return;
        try {
            const res = await axios.post(`/admin/api/kiem-kho/${id}/restore`);
            if (res.data.success) {
                toastr.success(res.data.message);
                loadTrash(currentPageTrash);
            } else {
                toastr.error(res.data.message);
            }
        } catch (e) {
            toastr.error(e.response?.data?.message || e.message);
        }
    });
}

async function forceDeleteOne(id, ma) {
    Swal.fire({
        icon: 'warning', title: 'Xóa vĩnh viễn?',
        html: `Phiếu <b>${ma}</b> sẽ bị xóa vĩnh viễn cùng toàn bộ chi tiết.`,
        showCancelButton: true, confirmButtonText: 'Xóa vĩnh viễn', cancelButtonText: 'Hủy',
        confirmButtonColor: '#dc2626',
    }).then(async r => {
        if (!r.isConfirmed) return;
        try {
            const res = await axios.delete(`/admin/api/kiem-kho/${id}/force`);
            if (res.data.success) {
                toastr.success(res.data.message);
                loadTrash(currentPageTrash);
            } else {
                toastr.error(res.data.message);
            }
        } catch (e) {
            toastr.error(e.response?.data?.message || e.message);
        }
    });
}

async function submitTrashBulkAction(action) {
    const ids = Array.from(document.querySelectorAll('.trash-check:checked')).map(cb => Number(cb.dataset.id));
    if (ids.length === 0) {
        toastr.warning('Vui lòng chọn ít nhất 1 phiếu.');
        return;
    }
    const labels = { restore: 'khôi phục', force_delete: 'XÓA VĨNH VIỄN' };
    const label = labels[action] || action;
    const swalConfig = {
        icon: action === 'force_delete' ? 'warning' : 'question',
        title: `${label} ${ids.length} phiếu?`,
        showCancelButton: true,
        confirmButtonText: `Xác nhận ${label}`,
        cancelButtonText: 'Hủy',
    };
    if (action === 'force_delete') {
        swalConfig.html = 'Thao tác này <b>không thể hoàn tác</b>.';
        swalConfig.confirmButtonColor = '#dc2626';
    }

    Swal.fire(swalConfig).then(async r => {
        if (!r.isConfirmed) return;
        try {
            const res = await axios.post(urlBulk, { action, ids });
            if (res.data.success) {
                toastr.success(res.data.message);
                if (res.data.errors?.length) {
                    toastr.warning(`${res.data.errors.length} lỗi: ${res.data.errors.slice(0,3).join(' | ')}`);
                }
                loadTrash(currentPageTrash);
            } else {
                toastr.error(res.data.message);
            }
        } catch (e) {
            toastr.error(e.response?.data?.message || e.message);
        }
    });
}

document.addEventListener('DOMContentLoaded', () => {
    document.getElementById('btn-reload').addEventListener('click', () => loadTrash(1));
    document.getElementById('selectAllTrash').addEventListener('change', e => {
        document.querySelectorAll('.trash-check').forEach(cb => { cb.checked = e.target.checked; });
        updateTrashSelected();
    });
    loadTrash(1);
});
</script>
@endsection
