<table>
    <thead>
        <tr>
            <th style="background-color: #4472C4; color: white; font-weight: bold; text-align: center; border: 1px solid black;">STT</th>
            <th style="background-color: #4472C4; color: white; font-weight: bold; text-align: center; border: 1px solid black;">Mã phiếu</th>
            <th style="background-color: #4472C4; color: white; font-weight: bold; text-align: center; border: 1px solid black;">Mã hàng</th>
            <th style="background-color: #4472C4; color: white; font-weight: bold; text-align: center; border: 1px solid black;">Mã vạch</th>
            <th style="background-color: #4472C4; color: white; font-weight: bold; text-align: center; border: 1px solid black;">Tên sản phẩm</th>
            <th style="background-color: #4472C4; color: white; font-weight: bold; text-align: center; border: 1px solid black;">Tên biến thể</th>
            <th style="background-color: #4472C4; color: white; font-weight: bold; text-align: center; border: 1px solid black;">Số lượng</th>
            <th style="background-color: #4472C4; color: white; font-weight: bold; text-align: center; border: 1px solid black;">Đơn giá</th>
            <th style="background-color: #4472C4; color: white; font-weight: bold; text-align: center; border: 1px solid black;">Thành tiền</th>
            <th style="background-color: #4472C4; color: white; font-weight: bold; text-align: center; border: 1px solid black;">Hạn SD</th>
            <th style="background-color: #4472C4; color: white; font-weight: bold; text-align: center; border: 1px solid black;">Mã lô</th>
            <th style="background-color: #4472C4; color: white; font-weight: bold; text-align: center; border: 1px solid black;">Ghi chú</th>
        </tr>
    </thead>
    <tbody>
        @forelse($data as $item)
        <tr>
            <td style="text-align: center; border: 1px solid black;">{{ $item['stt'] }}</td>
            <td style="text-align: left; border: 1px solid black;">{{ $item['ma_phieu'] }}</td>
            <td style="text-align: left; border: 1px solid black;">{{ $item['ma_hang'] }}</td>
            <td style="text-align: left; border: 1px solid black;">{{ $item['ma_vach'] }}</td>
            <td style="text-align: left; border: 1px solid black;">{{ $item['ten_san_pham'] }}</td>
            <td style="text-align: left; border: 1px solid black;">{{ $item['ten_bien_the'] }}</td>
            <td style="text-align: center; border: 1px solid black;">{{ $item['so_luong'] }}</td>
            <td style="text-align: right; border: 1px solid black;">{{ $item['don_gia'] }}</td>
            <td style="text-align: right; border: 1px solid black;">{{ $item['thanh_tien'] }}</td>
            <td style="text-align: center; border: 1px solid black;">{{ $item['han_su_dung'] }}</td>
            <td style="text-align: center; border: 1px solid black;">{{ $item['ma_lo'] }}</td>
            <td style="text-align: left; border: 1px solid black;">{{ $item['ghi_chu'] }}</td>
        </tr>
        @empty
        <tr>
            <td colspan="12" style="text-align: center; border: 1px solid black;">Không có dữ liệu</td>
        </tr>
        @endforelse
        <tr style="font-weight: bold; background-color: #f0f0f0;">
            <td colspan="8" style="text-align: right; border: 1px solid black;">TỔNG CỘNG:</td>
            <td style="text-align: right; border: 1px solid black;">{{ $tongTien }}</td>
            <td colspan="3" style="border: 1px solid black;"></td>
        </tr>
    </tbody>
</table>
<p style="margin-top: 10px; font-size: 11px; color: #666;">
    Xuất ngày: {{ $exportDate }}
</p>
@if($phieuNhap && $phieuNhap->phieu)
<p style="font-size: 11px; color: #666;">
    Mã phiếu: {{ 'PN' . str_pad($phieuNhap->id, 5, '0', STR_PAD_LEFT) }} |
    Ngày: {{ $phieuNhap->created_at->format('d/m/Y H:i') }} |
    Loại: {{ $phieuNhap->loai_nhap === 'mua_hang' ? 'Mua hàng' : 'Trả lại từ khách' }}
</p>
@endif
