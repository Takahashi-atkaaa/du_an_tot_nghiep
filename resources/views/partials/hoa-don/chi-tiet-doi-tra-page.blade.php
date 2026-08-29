@php
    $nguoiBanHoaDon = $hoaDon->nguoiDung->ho_ten_kem_vai_tro ?? 'N/A';
@endphp

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 pt-3 pb-2 mb-3 border-bottom">
    <div>
        <h1 class="h2 mb-1">Chi tiết đổi/trả hóa đơn #HD{{ str_pad($hoaDon->id, 4, '0', STR_PAD_LEFT) }}</h1>
        <div class="text-muted">Lịch sử đổi/trả được quản lý riêng theo từng lần xử lý.</div>
    </div>

    <div class="d-flex flex-wrap gap-2">
        <a href="{{ $backUrl }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-1"></i> {{ $backLabel }}
        </a>
    </div>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-3">
                <div class="text-muted small">Mã hóa đơn gốc</div>
                <div class="fw-semibold">#HD{{ str_pad($hoaDon->id, 4, '0', STR_PAD_LEFT) }}</div>
            </div>
            <div class="col-md-3">
                <div class="text-muted small">Người bán</div>
                <div class="fw-semibold">{{ $nguoiBanHoaDon }}</div>
            </div>
            <div class="col-md-3">
                <div class="text-muted small">Khách hàng</div>
                <div class="fw-semibold">{{ $hoaDon->khachHang->ten_khach_hang ?? 'Khách lẻ' }}</div>
            </div>
            <div class="col-md-3">
                <div class="text-muted small">Số lần đổi/trả</div>
                <div class="fw-semibold">{{ $lichSuDoiTra->count() }}</div>
            </div>
        </div>
    </div>
</div>

@if($lichSuDoiTra->isEmpty())
    <div class="alert alert-light border">
        Hóa đơn này chưa phát sinh đổi/trả.
    </div>
@else
    @foreach($lichSuDoiTra as $doiTra)
        @php
            $tongGiaTri = (float) $doiTra->chiTietDoiTras->sum('thanh_tien');
            $loaiHienThi = $doiTra->loai_hien_thi ?? (($doiTra->Loai ?? null) === 'tra_hang' ? 'Trả hàng' : 'Đổi hàng');
            $coTraHang = method_exists($doiTra, 'hasTraHang') ? $doiTra->hasTraHang() : (($doiTra->Loai ?? null) === 'tra_hang');
            $coDoiHang = method_exists($doiTra, 'hasDoiHang') ? $doiTra->hasDoiHang() : (($doiTra->Loai ?? null) === 'doi_tra');
            $badgeClass = $coTraHang && $coDoiHang
                ? 'bg-info text-dark'
                : ($coTraHang ? 'bg-danger' : 'bg-warning text-dark');
        @endphp

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white">
                <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
                    <div>
                        <div class="fw-bold fs-5">{{ $loaiHienThi }} #DT{{ str_pad($doiTra->id, 4, '0', STR_PAD_LEFT) }}</div>
                        <div class="small text-muted mt-1">Ngày thực hiện: {{ optional($doiTra->ngay)->format('d/m/Y H:i') ?? 'N/A' }}</div>
                        <div class="small text-muted mt-1">Người thực hiện đổi/trả: {{ $doiTra->nguoi_thuc_hien_doi_tra_hien_thi ?? ($doiTra->nguoiDung->ho_ten_kem_vai_tro ?? 'N/A') }}</div>
                    </div>

                    <div class="text-md-end">
                        <span class="badge {{ $badgeClass }} px-3 py-2">{{ $loaiHienThi }}</span>
                        <div class="small mt-2">Trừ điểm khách hàng: <strong>{{ $doiTra->tru_diem_cua_khach ? 'Có' : 'Không' }}</strong></div>
                        <div class="small mt-1">Hàng lỗi: <strong>{{ $doiTra->hang_loi ? 'Có' : 'Không' }}</strong></div>
                    </div>
                </div>
            </div>

            <div class="card-body">
                <div class="row g-3 mb-3">
                    <div class="col-md-8">
                        <div class="p-3 bg-light rounded border h-100">
                            <div class="fw-semibold mb-1">Lý do</div>
                            <div>{{ $doiTra->ly_do ?: 'Không có' }}</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-3 bg-light rounded border h-100">
                            <div class="text-muted small">Tổng giá trị đổi/trả</div>
                            <div class="fw-bold fs-5 text-primary">{{ number_format($tongGiaTri, 0, ',', '.') }}đ</div>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Loại dòng</th>
                                <th>Sản phẩm trả</th>
                                <th>Sản phẩm nhận đổi</th>
                                <th>Mã vạch</th>
                                <th class="text-center">Số lượng</th>
                                <th class="text-end">Giá lúc mua</th>
                                <th class="text-end">Thành tiền</th>
                                <th>Hàng lỗi / tiêu hủy</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($doiTra->chiTietDoiTras as $chiTietDoiTra)
                                @php
                                    $bienTheTra = $chiTietDoiTra->bienTheSanPham;
                                    $bienTheNhan = $chiTietDoiTra->bienTheThayThe;
                                    $hangLoiChiTiet = $chiTietDoiTra->hangLois;
                                    $loaiDong = $chiTietDoiTra->loai === 'doi_hang' ? 'Đổi hàng' : 'Trả hàng';
                                @endphp
                                <tr>
                                    <td>
                                        <span class="badge {{ $chiTietDoiTra->loai === 'doi_hang' ? 'bg-warning text-dark' : 'bg-danger' }}">
                                            {{ $loaiDong }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="fw-semibold">{{ $bienTheTra->ten_hien_thi ?? ($bienTheTra->product->ten_san_pham ?? 'Sản phẩm') }}</div>
                                        @if(!empty($bienTheTra?->ten_hien_thi_don_vi))
                                            <div class="small text-muted">Biến thể: {{ $bienTheTra->ten_hien_thi_don_vi }}</div>
                                        @elseif(!empty($bienTheTra?->ten_bien_the))
                                            <div class="small text-muted">Biến thể: {{ $bienTheTra->ten_bien_the }}</div>
                                        @endif
                                    </td>
                                    <td>
                                        @if($chiTietDoiTra->loai === 'doi_hang' && $bienTheNhan)
                                            <div class="fw-semibold">{{ $bienTheNhan->ten_hien_thi ?? ($bienTheNhan->product->ten_san_pham ?? 'Sản phẩm') }}</div>
                                            @if(!empty($bienTheNhan?->ten_hien_thi_don_vi))
                                                <div class="small text-muted">Biến thể: {{ $bienTheNhan->ten_hien_thi_don_vi }}</div>
                                            @elseif(!empty($bienTheNhan?->ten_bien_the))
                                                <div class="small text-muted">Biến thể: {{ $bienTheNhan->ten_bien_the }}</div>
                                            @endif
                                        @else
                                            <span class="text-muted">Không áp dụng</span>
                                        @endif
                                    </td>
                                    <td>{{ $bienTheTra->ma_vach ?? ($bienTheNhan->ma_vach ?? 'N/A') }}</td>
                                    <td class="text-center">{{ $chiTietDoiTra->so_luong }}</td>
                                    <td class="text-end">{{ number_format((float) ($chiTietDoiTra->gia_ban ?? 0), 0, ',', '.') }}đ</td>
                                    <td class="text-end fw-semibold">{{ number_format((float) ($chiTietDoiTra->thanh_tien ?? 0), 0, ',', '.') }}đ</td>
                                    <td>
                                        @if($hangLoiChiTiet->isEmpty())
                                            <span class="text-muted">Không có</span>
                                        @else
                                            @foreach($hangLoiChiTiet as $hangLoi)
                                                <div class="mb-2">
                                                    <div class="fw-semibold">
                                                        {{ $hangLoi->trang_thai === 'da_tieu_huy' ? 'Đã tiêu hủy' : 'Chờ tiêu hủy' }}
                                                    </div>
                                                    <div class="small text-muted">Số lượng: {{ $hangLoi->so_luong }}</div>
                                                    @if($hangLoi->ngay_tieu_huy)
                                                        <div class="small text-muted">Ngày tiêu hủy: {{ optional($hangLoi->ngay_tieu_huy)->format('d/m/Y H:i') }}</div>
                                                    @endif
                                                    @if(!empty($hangLoi->nguoiDungTieuHuy?->ho_ten_kem_vai_tro))
                                                        <div class="small text-muted">Người xác nhận tiêu hủy: {{ $hangLoi->nguoiDungTieuHuy->ho_ten_kem_vai_tro }}</div>
                                                    @endif
                                                </div>
                                            @endforeach
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endforeach
@endif
