@extends('admin_xem_truoc.layouts.admin')

@section('title', 'Sửa Phiếu xuất - SmartMart')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1"><i class="fas fa-edit me-2 text-warning"></i>Sửa Phiếu xuất</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ url('admin/dashboard') }}">Admin</a></li>
                    <li class="breadcrumb-item"><a href="{{ url('admin/kho-hang/phieu-xuat') }}">Phiếu xuất</a></li>
                    <li class="breadcrumb-item active">Sửa #{{ $phieuXuat->id }}</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ url('admin/kho-hang/phieu-xuat') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-arrow-left me-1"></i> Quay lại
            </a>
        </div>
    </div>

    <div id="alert-container-xuat-edit"></div>

    {{-- Form sửa phiếu xuất --}}
    <div class="row">
        <div class="col-lg-8">
            <div class="card table-admin mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0"><i class="fas fa-info-circle me-2 text-warning"></i>Thông tin phiếu xuất</h5>
                </div>
                <div class="card-body">
                    <form id="form-sua-phieu-xuat-full">
                        <input type="hidden" id="edit-px-id" value="{{ $phieuXuat->id }}">

                        <div class="row g-3 mb-4">
                            <div class="col-md-4">
                                <label class="form-label">Mã phiếu</label>
                                <input type="text" class="form-control" value="PX-{{ $phieuXuat->id_phieu }}" readonly>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Loại xuất <span class="text-danger">*</span></label>
                                <select name="loai_xuat" class="form-select" id="edit-px-loai" required>
                                    <option value="tra_hang_nha_cung_cap"
                                        {{ $phieuXuat->loai_xuat === 'tra_hang_nha_cung_cap' ? 'selected' : '' }}>Trả hàng
                                        NCC</option>
                                    <option value="tieu_huy" {{ $phieuXuat->loai_xuat === 'tieu_huy' ? 'selected' : '' }}>
                                        Tiêu hủy</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Nhà cung cấp</label>
                                <select name="id_nha_cung_cap" id="edit-px-ncc" class="form-select">
                                    <option value="">-- Chọn NCC --</option>
                                </select>
                            </div>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label">Lý do</label>
                                <input type="text" name="ly_do" class="form-control" id="edit-px-ly-do"
                                    value="{{ $phieuXuat->ly_do ?? '' }}" placeholder="Lý do xuất hàng...">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Ghi chú</label>
                                <input type="text" name="ghi_chu" class="form-control" id="edit-px-ghi-chu"
                                    value="{{ $phieuXuat->ghi_chu ?? '' }}" placeholder="Ghi chú...">
                            </div>
                        </div>

                        <hr>

                        <h6 class="mb-3">Chi tiết sản phẩm đã xuất</h6>

                        <div class="table-responsive">
                            <table class="table table-bordered mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Sản phẩm</th>
                                        <th class="text-center">Lô (FEFO)</th>
                                        <th class="text-center">HSD</th>
                                        <th class="text-center">SL đã xuất</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($phieuXuat->chiTietPhieu as $ct)
                                        <tr>
                                            <td>
                                                {{ $ct->variant?->product?->ten_san_pham ?? 'SP ' . $ct->id_san_pham }}
                                                @if ($ct->variant?->ten_bien_the)
                                                    <small class="text-muted"> - {{ $ct->variant->ten_bien_the }}</small>
                                                @endif
                                                <br><small class="text-muted">Mã:
                                                    {{ $ct->variant?->ma_vach ?? '--' }}</small>
                                            </td>
                                            <td class="text-center">
                                                {{ $ct->chiTietLoHang?->loHang?->ma_lo ?? 'L-' . $ct->chiTietLoHang?->id_lo_hang }}
                                            </td>
                                            <td class="text-center">
                                                @if ($ct->han_su_dung)
                                                    {{ \Carbon\Carbon::parse($ct->han_su_dung)->format('d/m/Y') }}
                                                @else
                                                    --
                                                @endif
                                            </td>
                                            <td class="text-center fw-bold text-danger">
                                                {{ number_format($ct->so_luong) }}
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center text-muted py-3">Không có chi tiết</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="3" class="text-end fw-bold">Tổng SL:</td>
                                        <td class="text-center fw-bold text-danger">
                                            {{ number_format($phieuXuat->chiTietPhieu->sum('so_luong')) }}
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        <div class="alert alert-info py-2 small mt-3">
                            <i class="fas fa-info-circle me-1"></i>
                            <strong>Lưu ý:</strong> Chỉ có thể sửa thông tin cơ bản (loại xuất, NCC, lý do). Chi tiết sản
                            phẩm đã xuất không thể thay đổi sau khi tạo phiếu.
                        </div>
                    </form>
                </div>
                <div class="card-footer bg-white">
                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ url('admin/kho-hang/phieu-xuat') }}" class="btn btn-secondary">Hủy</a>
                        <button type="button" class="btn btn-warning" id="btn-luu-sua-phieu-xuat-full">
                            <i class="fas fa-save me-1"></i>Lưu thay đổi
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            {{-- Thông tin phiếu --}}
            <div class="card table-admin mb-4">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0"><i class="fas fa-file-alt me-2"></i>Thông tin phiếu</h6>
                </div>
                <div class="card-body small">
                    <table class="table table-sm mb-0">
                        <tr>
                            <td class="text-muted">Mã phiếu:</td>
                            <td class="fw-bold">PX-{{ $phieuXuat->id_phieu }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Ngày tạo:</td>
                            <td>{{ $phieuXuat->created_at->format('d/m/Y H:i') }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Người tạo:</td>
                            <td>{{ $phieuXuat->phieu?->nguoiDung?->ho_ten ?? '--' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Loại xuất:</td>
                            <td>
                                @if ($phieuXuat->loai_xuat === 'tra_hang_nha_cung_cap')
                                    <span class="badge bg-warning text-dark">Trả hàng NCC</span>
                                @else
                                    <span class="badge bg-danger">Tiêu hủy</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted">Tổng SL:</td>
                            <td class="fw-bold text-danger">{{ number_format($phieuXuat->chiTietPhieu->sum('so_luong')) }}
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

            {{-- Hướng dẫn --}}
            <div class="card table-admin">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0"><i class="fas fa-question-circle me-2"></i>Hướng dẫn</h6>
                </div>
                <div class="card-body small">
                    <ul class="mb-0 ps-3">
                        <li>Có thể sửa: loại xuất, nhà cung cấp, lý do, ghi chú</li>
                        <li>Không thể sửa chi tiết sản phẩm đã xuất</li>
                        <li>Để xóa phiếu, vui lòng vào trang danh sách</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(function() {
            // Load nhà cung cấp
            $.get('/admin/api/lo-hang/nha-cung-cap', function(res) {
                const nccId = '{{ $phieuXuat->phieu?->id_nha_cung_cap ?? '' }}';
                const opts = res.map(n =>
                    `<option value="${n.id}" ${n.id == nccId ? 'selected' : ''}>${n.ten_nha_cung_cap}</option>`
                    ).join('');
                $('#edit-px-ncc').html('<option value="">-- Chọn NCC --</option>' + opts);
            });

            // Save button
            $('#btn-luu-sua-phieu-xuat-full').click(function() {
                const id = $('#edit-px-id').val();
                const data = {
                    loai_xuat: $('#edit-px-loai').val(),
                    id_nha_cung_cap: $('#edit-px-ncc').val() || null,
                    ly_do: $('#edit-px-ly-do').val(),
                    ghi_chu: $('#edit-px-ghi-chu').val(),
                };

                $.ajax({
                    url: '/admin/api/phieu-xuat/' + id,
                    method: 'PUT',
                    contentType: 'application/json',
                    data: JSON.stringify(data),
                    success: function(res) {
                        hienThongBaoXuatEdit('success', res.message);
                        setTimeout(() => {
                            window.location.href = '/admin/kho-hang/phieu-xuat';
                        }, 1500);
                    },
                    error: function(x) {
                        hienThongBaoXuatEdit('danger', x.responseJSON?.message ||
                            'Đã xảy ra lỗi.');
                    }
                });
            });
        });

        function hienThongBaoXuatEdit(type, message) {
            $('#alert-container-xuat-edit').html(`
        <div class="alert alert-${type} alert-dismissible fade show" role="alert">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `);
            setTimeout(() => $('.alert').fadeOut(), 5000);
        }
    </script>
@endsection
