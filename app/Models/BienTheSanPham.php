<?php

namespace App\Models;

use App\Models\ThuocTinhSanPham;
use Illuminate\Database\Eloquent\SoftDeletes;

class BienTheSanPham extends BaseModel
{
    use SoftDeletes;

    protected $table = 'bien_the_san_pham';

    protected $fillable = [
        'product_id',
        'ten_bien_the',
        'ma_hang',
        'ma_vach',
        'gia_von',
        'gia_ban',
        'so_luong_ton',
        'dinh_muc_toi_thieu',
        'hinh_anh',
        'thuoc_tinh_ids',
        'trang_thai',
    ];

    protected $casts = [
        'thuoc_tinh_ids' => 'array',
        'gia_von' => 'decimal:2',
        'gia_ban' => 'decimal:2',
        'so_luong_ton' => 'integer',
        'dinh_muc_toi_thieu' => 'integer',
        'trang_thai' => 'boolean',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function units()
    {
        return $this->hasMany(DonViQuyDoi::class, 'variant_id');
    }

    public function chiTietLoHang()
    {
        return $this->hasMany(ChiTietLoHang::class, 'variant_id');
    }

    public function chiTietLoHangTon()
    {
        return $this->hasMany(ChiTietLoHang::class, 'variant_id')
            ->where('so_luong_ton', '>', 0);
    }

    public function thuocTinhs()
    {
        if (empty($this->thuoc_tinh_ids)) {
            return collect();
        }
        return ThuocTinhSanPham::whereIn('id', $this->thuoc_tinh_ids)->get();
    }

    public function getTenHienThiAttribute()
    {
        if ($this->ten_bien_the) {
            return ($this->product->ten_san_pham ?? '') . ' - ' . $this->ten_bien_the;
        }
        return $this->product->ten_san_pham ?? '';
    }
}
