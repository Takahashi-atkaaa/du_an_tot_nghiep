<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Model SanPham - đồng bộ với Product (cùng trỏ tới bảng san_pham).
 * Bảng san_pham chỉ còn các cột thông tin chung của sản phẩm cha:
 * id, id_danh_muc, ten_san_pham, thuong_hieu, mo_ta, trang_thai.
 * Các dữ liệu về giá, tồn kho, mã vạch, mã hàng, đơn vị, biến thể…
 * đã được chuyển sang bien_the_san_pham + don_vi_quy_doi.
 */
class SanPham extends BaseModel
{
    use HasFactory, SoftDeletes;

    protected $table = 'san_pham';

    protected $fillable = [
        'id_danh_muc',
        'ten_san_pham',
        'hinh_anh',
        'mo_ta',
        'gia_ban',
        'dinh_muc_toi_thieu',
        'trang_thai',
    ];

    public function danhMuc()
    {
        return $this->belongsTo(DanhMucSanPham::class, 'id_danh_muc');
    }

    public function bienTheSanPhams()
    {
        return $this->hasMany(BienTheSanPham::class, 'id_san_pham');
    }

    public function khuyenMaiSanPham(){
        return $this->hasMany(KhuyenMaiSanPham::class, 'id_san_pham');
    }
}