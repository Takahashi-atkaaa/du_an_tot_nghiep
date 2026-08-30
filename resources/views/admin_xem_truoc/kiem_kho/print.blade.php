<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Phiếu kiểm kho {{ $phieu->ma_kiem_kho }}</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; font-size: 13px; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h1 { margin: 0; font-size: 22px; text-transform: uppercase; }
        .header h2 { margin: 5px 0; font-size: 18px; }
        .info { margin-bottom: 20px; }
        .info table { width: 100%; }
        .info td { padding: 3px 8px; }
        .info td:first-child { font-weight: bold; width: 130px; }
        table.detail { width: 100%; border-collapse: collapse; margin-top: 10px; }
        table.detail th, table.detail td { border: 1px solid #333; padding: 6px 8px; text-align: center; font-size: 12px; }
        table.detail th { background: #f0f0f0; }
        .text-start { text-align: left !important; }
        .footer { margin-top: 40px; }
        .footer table { width: 100%; }
        .footer td { width: 33%; text-align: center; padding: 10px; }
        .signature { border-top: 1px solid #333; padding-top: 5px; }
        @media print {
            body { padding: 10px; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <div class="no-print" style="text-align: right; margin-bottom: 10px;">
        <button onclick="window.print()" style="padding: 6px 14px; background: #4f46e5; color: white; border: none; border-radius: 6px; cursor: pointer;">
            <i class="fas fa-print"></i> In phiếu
        </button>
        <button onclick="window.close()" style="padding: 6px 14px; background: #64748b; color: white; border: none; border-radius: 6px; cursor: pointer;">
            Đóng
        </button>
    </div>

    <div class="header">
        <h1>Cửa hàng tạp hóa SmartMart</h1>
        <h2>PHIẾU KIỂM KHO</h2>
    </div>

    <div class="info">
        <table>
            <tr><td>Mã phiếu:</td><td>{{ $phieu->ma_kiem_kho }}</td><td>Ngày kiểm:</td><td>{{ $phieu->ngay_kiem?->format('d/m/Y') }}</td></tr>
            <tr><td>Người kiểm:</td><td>{{ $phieu->nguoiKiem?->ho_ten ?? '-' }}</td><td>Người duyệt:</td><td>{{ $phieu->nguoiDuyet?->ho_ten ?? '-' }}</td></tr>
            <tr><td>Phạm vi:</td><td colspan="3">{{ $phieu->pham_vi_label }}</td></tr>
            <tr><td>Trạng thái:</td><td colspan="3">{{ $phieu->trang_thai_label }}</td></tr>
        </table>
    </div>

    <table class="detail">
        <thead>
            <tr>
                <th style="width: 40px;">STT</th>
                <th>Mã SP</th>
                <th>Tên sản phẩm</th>
                <th style="width: 60px;">HSD</th>
                <th style="width: 60px;">Tồn HT</th>
                <th style="width: 60px;">Thực tế</th>
                <th style="width: 60px;">Chênh lệch</th>
                <th>Lý do</th>
            </tr>
        </thead>
        <tbody>
            @foreach($phieu->chiTietKiemKho as $i => $ct)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $ct->ma_hang ?? $ct->ma_vach ?? '' }}</td>
                <td class="text-start">{{ $ct->ten_san_pham }} {{ $ct->ten_bien_the ?? $ct->ten_don_vi ?? '' }}</td>
                <td>{{ $ct->han_su_dung_gan_nhat?->format('d/m/Y') ?? '-' }}</td>
                <td>{{ $ct->so_luong_he_thong }}</td>
                <td>{{ $ct->so_luong_thuc_te ?? '-' }}</td>
                <td>{{ $ct->so_luong_lech != 0 ? ($ct->so_luong_lech > 0 ? '+' : '') . $ct->so_luong_lech : '0' }}</td>
                <td class="text-start">{{ $ct->ly_do ?? '' }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <th colspan="4" class="text-start" style="text-align: right;">Tổng:</th>
                <th>{{ $phieu->tong_sl_he_thong }}</th>
                <th>{{ $phieu->tong_sl_thuc_te }}</th>
                <th>{{ $phieu->tong_sl_lech }}</th>
                <th></th>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        <table>
            <tr>
                <td>
                    <div class="signature">Người kiểm</div>
                    <small>(Ký, ghi rõ họ tên)</small>
                </td>
                <td>
                    <div class="signature">Người duyệt</div>
                    <small>(Ký, ghi rõ họ tên)</small>
                </td>
                <td>
                    <div class="signature">Người tạo phiếu</div>
                    <small>(Ký, ghi rõ họ tên)</small>
                </td>
            </tr>
        </table>
    </div>

    <div style="margin-top: 20px; text-align: center; font-size: 11px; color: #666;">
        In lúc: {{ now()->format('d/m/Y H:i') }} - SmartMart
    </div>
</body>
</html>