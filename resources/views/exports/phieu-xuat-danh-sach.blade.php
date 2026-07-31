<table>
    <thead>
        <tr>
            <th style="background-color: #C00000; color: white; font-weight: bold; text-align: center; border: 1px solid black;">STT</th>
            <th style="background-color: #C00000; color: white; font-weight: bold; text-align: center; border: 1px solid black;">Mã phiếu</th>
            <th style="background-color: #C00000; color: white; font-weight: bold; text-align: center; border: 1px solid black;">Ngày tạo</th>
            <th style="background-color: #C00000; color: white; font-weight: bold; text-align: center; border: 1px solid black;">Loại xuất</th>
            <th style="background-color: #C00000; color: white; font-weight: bold; text-align: center; border: 1px solid black;">Người tạo</th>
            <th style="background-color: #C00000; color: white; font-weight: bold; text-align: center; border: 1px solid black;">Số SP</th>
            <th style="background-color: #C00000; color: white; font-weight: bold; text-align: center; border: 1px solid black;">Tổng SL</th>
            <th style="background-color: #C00000; color: white; font-weight: bold; text-align: center; border: 1px solid black;">Giá trị</th>
            <th style="background-color: #C00000; color: white; font-weight: bold; text-align: center; border: 1px solid black;">Lý do</th>
            <th style="background-color: #C00000; color: white; font-weight: bold; text-align: center; border: 1px solid black;">Ghi chú</th>
        </tr>
    </thead>
    <tbody>
        @forelse($data as $item)
        <tr>
            <td style="text-align: center; border: 1px solid black;">{{ $loop->iteration }}</td>
            <td style="text-align: left; border: 1px solid black;">{{ $item['ma_phieu'] }}</td>
            <td style="text-align: center; border: 1px solid black;">{{ $item['ngay_tao'] }}</td>
            <td style="text-align: left; border: 1px solid black;">{{ $item['loai_xuat'] }}</td>
            <td style="text-align: left; border: 1px solid black;">{{ $item['nguoi_tao'] }}</td>
            <td style="text-align: center; border: 1px solid black;">{{ $item['tong_san_pham'] }}</td>
            <td style="text-align: center; border: 1px solid black;">{{ $item['tong_so_luong'] }}</td>
            <td style="text-align: right; border: 1px solid black;">{{ $item['tong_tien'] }}</td>
            <td style="text-align: left; border: 1px solid black;">{{ $item['ly_do'] }}</td>
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
