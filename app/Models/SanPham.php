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
        'thuong_hieu',
        'mo_ta',
        'trang_thai',
    ];

    protected $casts = [
        'trang_thai' => 'boolean',
    ];

    public function danhMuc()
    {
        return $this->belongsTo(DanhMucSanPham::class, 'id_danh_muc');
    }

    public function bienTheSanPhams()
    {
        return $this->hasMany(BienTheSanPham::class, 'product_id');
    }

    /**
     * Lấy tất cả chi tiết hóa đơn của sản phẩm này (thông qua các biến thể).
     * - san_pham.id              → bien_the_san_pham.product_id
     * - bien_the_san_pham.id     → chi_tiet_hoa_don.id_bien_the_san_pham
     */
    public function chiTietHoaDons()
    {
        return $this->hasManyThrough(
            ChiTietHoaDon::class,
            BienTheSanPham::class,
            'product_id',            // FK trên bien_the_san_pham
            'id_bien_the_san_pham',  // FK trên chi_tiet_hoa_don
            'id',                    // Local key trên san_pham
            'id'                     // Local key trên bien_the_san_pham
        );
    }
}