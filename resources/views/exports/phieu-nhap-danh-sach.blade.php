<table>
    <thead>
        <tr>
            <th style="background-color: #4472C4; color: white; font-weight: bold; text-align: center; border: 1px solid black;">STT</th>
            <th style="background-color: #4472C4; color: white; font-weight: bold; text-align: center; border: 1px solid black;">Mã phiếu</th>
            <th style="background-color: #4472C4; color: white; font-weight: bold; text-align: center; border: 1px solid black;">Ngày tạo</th>
            <th style="background-color: #4472C4; color: white; font-weight: bold; text-align: center; border: 1px solid black;">Loại nhập</th>
            <th style="background-color: #4472C4; color: white; font-weight: bold; text-align: center; border: 1px solid black;">Nhà cung cấp</th>
            <th style="background-color: #4472C4; color: white; font-weight: bold; text-align: center; border: 1px solid black;">Người tạo</th>
            <th style="background-color: #4472C4; color: white; font-weight: bold; text-align: center; border: 1px solid black;">Số SP</th>
            <th style="background-color: #4472C4; color: white; font-weight: bold; text-align: center; border: 1px solid black;">Tổng SL</th>
            <th style="background-color: #4472C4; color: white; font-weight: bold; text-align: center; border: 1px solid black;">Tổng tiền</th>
            <th style="background-color: #4472C4; color: white; font-weight: bold; text-align: center; border: 1px solid black;">Ghi chú</th>
        </tr>
    </thead>
    <tbody>
        @forelse($data as $item)
        <tr>
            <td style="text-align: center; border: 1px solid black;">{{ $loop->iteration }}</td>
            <td style="text-align: left; border: 1px solid black;">{{ $item['ma_phieu'] }}</td>
            <td style="text-align: center; border: 1px solid black;">{{ $item['ngay_tao'] }}</td>
            <td style="text-align: left; border: 1px solid black;">{{ $item['loai_nhap'] }}</td>
            <td style="text-align: left; border: 1px solid black;">{{ $item['nha_cung_cap'] }}</td>
            <td style="text-align: left; border: 1px solid black;">{{ $item['nguoi_tao'] }}</td>
            <td style="text-align: center; border: 1px solid black;">{{ $item['tong_san_pham'] }}</td>
            <td style="text-align: center; border: 1px solid black;">{{ $item['tong_so_luong'] }}</td>
            <td style="text-align: right; border: 1px solid black;">{{ $item['tong_tien'] }}</td>
            <td style="text-align: left; border: 1px solid black;">{{ $item['ghi_chu'] }}</td>
        </tr>
        @empty
        <tr>
            <td colspan="10" style="text-align: center; border: 1px solid black;">Không có dữ liệu</td>
        </tr>
        @endforelse
    </tbody>
</table>
<p style="margin-top: 10px; font-size: 11px; color: #666;">
    Xuất ngày: {{ $exportDate }}
</p>
