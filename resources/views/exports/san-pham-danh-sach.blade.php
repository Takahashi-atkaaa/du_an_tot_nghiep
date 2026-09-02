<h2 style="margin: 0 0 6px 0; text-align: center; color: #1f4e79; font-size: 18px;">
    DANH SÁCH SẢN PHẨM
</h2>
<p style="margin: 0 0 12px 0; text-align: center; font-size: 11px; color: #666;">
    Xuất ngày: {{ $exportDate }}
</p>

<table>
    <thead>
        <tr>
            <th style="background-color: #4472C4; color: white; font-weight: bold; text-align: center; border: 1px solid black;">STT</th>
            <th style="background-color: #4472C4; color: white; font-weight: bold; text-align: center; border: 1px solid black;">Tên sản phẩm</th>
            <th style="background-color: #4472C4; color: white; font-weight: bold; text-align: center; border: 1px solid black;">Danh mục</th>
            <th style="background-color: #4472C4; color: white; font-weight: bold; text-align: center; border: 1px solid black;">Biến thể</th>
            <th style="background-color: #4472C4; color: white; font-weight: bold; text-align: center; border: 1px solid black;">Mã hàng</th>
            <th style="background-color: #4472C4; color: white; font-weight: bold; text-align: center; border: 1px solid black;">Mã vạch</th>
            <th style="background-color: #4472C4; color: white; font-weight: bold; text-align: center; border: 1px solid black;">Đơn vị</th>
            <th style="background-color: #4472C4; color: white; font-weight: bold; text-align: center; border: 1px solid black;">Giá vốn</th>
            <th style="background-color: #4472C4; color: white; font-weight: bold; text-align: center; border: 1px solid black;">Giá bán</th>
            <th style="background-color: #4472C4; color: white; font-weight: bold; text-align: center; border: 1px solid black;">Tồn kho</th>
            <th style="background-color: #4472C4; color: white; font-weight: bold; text-align: center; border: 1px solid black;">Định mức TT</th>
            <th style="background-color: #4472C4; color: white; font-weight: bold; text-align: center; border: 1px solid black;">Trạng thái</th>
            <th style="background-color: #4472C4; color: white; font-weight: bold; text-align: center; border: 1px solid black;">Thương hiệu</th>
        </tr>
    </thead>
    <tbody>
        @forelse($data as $item)
        <tr>
            <td style="text-align: center; border: 1px solid #ddd;">{{ $loop->iteration }}</td>
            <td style="text-align: left; border: 1px solid #ddd;">{{ $item['ten_san_pham'] }}</td>
            <td style="text-align: left; border: 1px solid #ddd;">{{ $item['danh_muc'] }}</td>
            <td style="text-align: left; border: 1px solid #ddd;">{{ $item['ten_bien_the'] }}</td>
            <td style="text-align: left; border: 1px solid #ddd;">{{ $item['ma_hang'] }}</td>
            <td style="text-align: left; border: 1px solid #ddd;">{{ $item['ma_vach'] }}</td>
            <td style="text-align: center; border: 1px solid #ddd;">{{ $item['ten_don_vi'] }}</td>
            <td style="text-align: right; border: 1px solid #ddd; mso-number-format:\@;">&nbsp;{{ $item['gia_von'] }}</td>
            <td style="text-align: right; border: 1px solid #ddd; mso-number-format:\@;">&nbsp;{{ $item['gia_ban'] }}</td>
            <td style="text-align: center; border: 1px solid #ddd;">{{ $item['so_luong_ton'] }}</td>
            <td style="text-align: center; border: 1px solid #ddd;">{{ $item['dinh_muc_toi_thieu'] }}</td>
            <td style="text-align: center; border: 1px solid #ddd;">{{ $item['trang_thai'] }}</td>
            <td style="text-align: left; border: 1px solid #ddd;">{{ $item['thuong_hieu'] }}</td>
        </tr>
        @empty
        <tr>
            <td colspan="13" style="text-align: center; border: 1px solid #ddd; padding: 10px;">Không có dữ liệu</td>
        </tr>
        @endforelse
    </tbody>
</table>
