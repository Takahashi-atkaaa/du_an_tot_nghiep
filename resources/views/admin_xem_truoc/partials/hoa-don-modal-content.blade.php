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
                    <th class="text-center">Đổi / Trả</th>
                </tr>
            </thead>
            <tbody>
                @foreach($chiTiet as $item)
                    <tr>
                        <td>
                            {{ $item->ten_hien_thi_san_pham ?: $item->ten_san_pham }}
                            @if(!empty($item->thuoc_tinh_hien_thi))
                                <br><small class="text-muted">
                                    <i class="fas fa-tags"></i> {{ implode(' • ', $item->thuoc_tinh_hien_thi) }}
                                </small>
                            @endif
                        </td>
                        <td class="text-center">{{ $item->so_luong }}</td>
                        <td class="text-end">{{ number_format($item->gia_ban, 0, ',', '.') }}đ</td>
                        <td class="text-end">{{ number_format($item->thanh_tien, 0, ',', '.') }}đ</td>
                        <td class="text-center">
                            @if(($item->tong_da_tra ?? 0) > 0)
                                <span class="badge bg-danger">Đã trả {{ $item->tong_da_tra }}/{{ $item->so_luong }}</span>
                            @endif
                            @if(($item->tong_da_doi ?? 0) > 0)
                                <span class="badge bg-warning text-dark">Đã đổi {{ $item->tong_da_doi }}/{{ $item->so_luong }}</span>
                            @endif
                            @if(($item->tong_da_doi_tra ?? 0) === 0)
                                <span class="text-muted">Chưa phát sinh</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @if(isset($lichSuDoiTra) && $lichSuDoiTra->count() && (userHasPermission('quan_ly_hoa_don') || userHasPermission('xem_hoa_don') || userHasPermission('doi_tra_hoa_don')))
        <div class="mb-3">
            <a class="btn btn-outline-warning btn-sm" href="{{ route('admin.hoa-don.chi-tiet-doi-tra', $hoaDon->id) }}">
                <i class="fas fa-rotate-left me-1"></i> Chi tiết đổi/trả
            </a>
        </div>

        <div class="collapse d-none" id="collapseDoiTra">
            @foreach($lichSuDoiTra as $doiTra)
                <div class="border rounded p-3 mb-3">
                    <div class="d-flex justify-content-between flex-wrap gap-2 mb-3">
                        <div>
                            <div class="fw-bold">{{ $doiTra->Loai === 'tra_hang' ? 'Trả hàng' : 'Đổi hàng' }} #DT{{ str_pad($doiTra->id, 4, '0', STR_PAD_LEFT) }}</div>
                            <div class="small text-muted">Ngày thực hiện: {{ optional($doiTra->ngay)->format('d/m/Y H:i') }}</div>
                            <div class="small text-muted">Người thực hiện đổi/trả: {{ $doiTra->nguoi_thuc_hien_doi_tra_hien_thi ?? ($doiTra->nguoiDung->ho_ten_kem_vai_tro ?? 'N/A') }}</div>
                        </div>
                        <div class="text-end">
                            <span class="badge {{ $doiTra->Loai === 'tra_hang' ? 'bg-danger' : 'bg-warning text-dark' }}">
                                {{ $doiTra->Loai === 'tra_hang' ? 'Trả hàng' : 'Đổi hàng' }}
                            </span>
                            <div class="small mt-2">
                                Trừ điểm khách:
                                <strong>{{ $doiTra->tru_diem_cua_khach ? 'Có' : 'Không' }}</strong>
                            </div>
                            <div class="small mt-1">
                                Hàng lỗi:
                                <strong>{{ $doiTra->hang_loi ? 'Có' : 'Không' }}</strong>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3"><strong>Lý do:</strong> {{ $doiTra->ly_do ?: 'Không có' }}</div>

                    <div class="table-responsive">
                        <table class="table table-sm table-bordered mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Sản phẩm / biến thể</th>
                                    <th class="text-center">Số lượng</th>
                                    <th class="text-end">Đơn giá ghi nhận</th>
                                    <th class="text-end">Thành tiền</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($doiTra->chiTietDoiTras as $chiTietDoiTra)
                                    <tr>
                                        <td>
                                            {{ $chiTietDoiTra->bienTheSanPham->ten_hien_thi ?? ($chiTietDoiTra->bienTheSanPham->product->ten_san_pham ?? 'Sản phẩm') }}
                                        </td>
                                        <td class="text-center">{{ $chiTietDoiTra->so_luong }}</td>
                                        <td class="text-end">{{ number_format($chiTietDoiTra->gia_ban, 0, ',', '.') }}đ</td>
                                        <td class="text-end">{{ number_format($chiTietDoiTra->thanh_tien, 0, ',', '.') }}đ</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
</div>
