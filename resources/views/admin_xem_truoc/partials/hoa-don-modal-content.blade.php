<div class="modal-header">
    <h5 class="modal-title">Chi tiết hóa đơn #HD{{ str_pad($hoaDon->id, 4, '0', STR_PAD_LEFT) }}</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
</div>
<div class="modal-body">
    <div class="row mb-3">
        <div class="col-6">
            <p class="mb-1"><strong>Ngày tạo:</strong> {{ \Carbon\Carbon::parse($hoaDon->created_at)->format('d/m/Y H:i') }}</p>
            <p class="mb-1"><strong>Nhân viên:</strong> {{ $hoaDon->ten_nhan_vien ?? 'Nhân viên' }}</p>
            <p class="mb-1"><strong>Khách hàng:</strong> {{ $hoaDon->ten_khach_hang ?? 'Khách lẻ' }}</p>
        </div>
        <div class="col-6">
            <p class="mb-1"><strong>SĐT:</strong> {{ $hoaDon->so_dien_thoai ?? '---' }}</p>
            <p class="mb-1"><strong>Thanh toán:</strong> {{ $hoaDon->phuong_thuc_thanh_toan }}</p>
            <p class="mb-1"><strong>Khuyến mãi:</strong> {{ $hoaDon->ten_khuyen_mai ?? 'Không áp dụng' }}</p>
            <p class="mb-1"><strong>Trạng thái:</strong> {{ $hoaDon->trang_thai }}</p>
        </div>
    </div>

    <div class="table-responsive mb-3">
        <table class="table table-sm table-bordered align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Sản phẩm</th>
                    <th class="text-center">SL</th>
                    <th class="text-end">Giá bán</th>
                    <th class="text-end">Thành tiền</th>
                </tr>
            </thead>
            <tbody>
                @foreach($chiTiet as $item)
                <tr>
                    <td>
                        {{ $item->ten_san_pham }}
                        @php
                            $variantName = $item->ten_don_vi ?: $item->ten_bien_the;
                        @endphp
                        @if($variantName)
                            <br><small class="text-muted">({{ $variantName }})</small>
                        @endif
                    </td>
                    <td class="text-center">{{ $item->so_luong }}</td>
                    <td class="text-end">{{ number_format($item->gia_ban, 0, ',', '.') }}đ</td>
                    <td class="text-end">{{ number_format($item->thanh_tien, 0, ',', '.') }}đ</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="row justify-content-end mb-3">
        <div class="col-6">
            <div class="d-flex justify-content-between mb-2">
                <span>Tổng tiền hàng:</span>
                <strong>{{ number_format($hoaDon->tong_tien_hang, 0, ',', '.') }}đ</strong>
            </div>
            <div class="d-flex justify-content-between mb-2">
                <span>Giảm giá:</span>
                <strong>{{ number_format($hoaDon->tien_giam_gia, 0, ',', '.') }}đ</strong>
            </div>
            <div class="d-flex justify-content-between mb-2 text-success">
                <span>Khách cần trả:</span>
                <strong>{{ number_format($hoaDon->khach_can_tra, 0, ',', '.') }}đ</strong>
            </div>
            <div class="d-flex justify-content-between mb-2">
                <span>Khách đưa:</span>
                <strong>{{ number_format($hoaDon->tien_khach_dua, 0, ',', '.') }}đ</strong>
            </div>
            <div class="d-flex justify-content-between mb-2">
                <span>Tiền thừa:</span>
                <strong>{{ number_format($hoaDon->tien_thua, 0, ',', '.') }}đ</strong>
            </div>
            <div class="d-flex justify-content-between mb-2 text-danger">
                <span>Điểm sử dụng:</span>
                <strong>-{{ number_format($hoaDon->diem_su_dung ?? 0, 0, ',', '.') }}</strong>
            </div>
            <div class="d-flex justify-content-between text-success">
                <span>Điểm thu được:</span>
                <strong>+{{ number_format($hoaDon->diem_thu_duoc ?? 0, 0, ',', '.') }}</strong>
            </div>
        </div>
    </div>

    <div>
        <h6 class="fw-semibold mb-3">Chi tiết điểm tích lũy</h6>
        @if($diemTichDiems->count())
            <div class="table-responsive">
                <table class="table table-sm table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Thời gian</th>
                            <th>Loại</th>
                            <th>Số điểm</th>
                            <th>Lý do</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($diemTichDiems as $diem)
                            @php
                                $loai = mb_strtolower(trim($diem->loai_bien_dong ?? ''));
                                $isCong = in_array($loai, ['cong', 'tang', 'tăng']) || ($loai === '' && $diem->so_diem >= 0);
                            @endphp
                            <tr>
                                <td>{{ optional($diem->created_at)->format('d/m/Y H:i') }}</td>
                                <td>
                                    <span class="badge {{ $isCong ? 'bg-success' : 'bg-danger' }} px-3 py-2">
                                        {{ $isCong ? 'Tăng điểm' : 'Giảm điểm' }}
                                    </span>
                                </td>
                                <td class="fw-bold {{ $isCong ? 'text-success' : 'text-danger' }}">
                                    {{ $isCong ? '+' : '-' }}{{ number_format(abs($diem->so_diem), 0, ',', '.') }}
                                </td>
                                <td>{{ $diem->ly_do ?: '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="text-muted">Hóa đơn này không có thay đổi điểm tích lũy.</p>
        @endif
    </div>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
</div>
