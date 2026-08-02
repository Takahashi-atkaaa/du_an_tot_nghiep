@php
    $hasRange = !empty($filters['tu_ngay']) || !empty($filters['den_ngay']);
    $tuLabel = !empty($filters['tu_ngay']) ? \Carbon\Carbon::parse($filters['tu_ngay'])->format('d/m/Y') : '...';
    $denLabel = !empty($filters['den_ngay']) ? \Carbon\Carbon::parse($filters['den_ngay'])->format('d/m/Y') : '...';
    $title = $hasRange
        ? "BÁO CÁO XUẤT HÀNG từ {$tuLabel} đến {$denLabel}"
        : 'BÁO CÁO XUẤT HÀNG (toàn bộ thời gian)';
@endphp

<h2 style="margin: 0 0 6px 0; text-align: center; color: #C00000; font-size: 18px;">
    {{ $title }}
</h2>
<p style="margin: 0 0 12px 0; text-align: center; font-size: 11px; color: #666;">
    @if(!empty($filters['loai_xuat']))
        Loại: {{ $filters['loai_xuat'] === 'tra_hang_nha_cung_cap' ? 'Trả hàng NCC' : 'Tiêu hủy' }}
        |
    @endif
    Xuất ngày: {{ $exportDate }}
</p>

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
            <td style="text-align: right; border: 1px solid black; mso-number-format:&quot;\@&quot;;">&nbsp;{{ $item['tong_tien'] }}</td>
            <td style="text-align: left; border: 1px solid black;">{{ $item['ly_do'] }}</td>
            <td style="text-align: left; border: 1px solid black;">{{ $item['ghi_chu'] }}</td>
        </tr>
        @empty
        <tr>
            <td colspan="10" style="text-align: center; border: 1px solid black;">
                @if($hasRange)
                    Không có phiếu xuất nào trong khoảng từ {{ $tuLabel }} đến {{ $denLabel }}.
                @else
                    Không có dữ liệu
                @endif
            </td>
        </tr>
        @endforelse
    </tbody>
</table>