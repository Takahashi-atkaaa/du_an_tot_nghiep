<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChiTietHoaDon extends Model
{
    use HasFactory;

    protected $table = 'chi_tiet_hoa_don';

    /**
     * Cấu trúc bảng mới: cột `id_san_pham` đã được thay thế bằng
     * `id_bien_the_san_pham` (tham chiếu tới bảng bien_the_san_pham).
     */
    protected $fillable = [
        'id_hoa_don',
        'id_bien_the_san_pham',
        'id_chi_tiet_phieu',
        'so_luong',
        'gia_ban',
        'thanh_tien',
    ];

    public function chiTietPhieu(){
        return $this->belongsTo(ChiTietPhieu::class, 'id_chi_tiet_phieu');
    }

    public function hoaDon(){
        return $this->belongsTo(HoaDon::class, 'id_hoa_don');
    }

    /**
     * Mỗi chi tiết hóa đơn giờ trỏ về biến thể (bien_the_san_pham)
     * thay vì trỏ thẳng về sản phẩm cha.
     */
    public function bienThe(){
        return $this->belongsTo(BienTheSanPham::class, 'id_bien_the_san_pham');
    }
}
