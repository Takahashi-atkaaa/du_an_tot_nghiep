<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

class DonViQuyDoi extends BaseModel
{
    use SoftDeletes;

    protected $table = 'don_vi_quy_doi';

    protected $fillable = [
        'variant_id',
        'ten_don_vi',
        'ty_le_quy_doi',
        'ma_hang',
        'ma_vach',
        'gia_von_quy_doi',
        'gia_ban_quy_doi',
        'gia_ban_si',
        'hinh_anh',
        'la_don_vi_mac_dinh',
    ];

    protected $casts = [
        'ty_le_quy_doi' => 'integer',
        'gia_von_quy_doi' => 'decimal:2',
        'gia_ban_quy_doi' => 'decimal:2',
        'gia_ban_si' => 'decimal:2',
        'la_don_vi_mac_dinh' => 'boolean',
    ];

    public function variant()
    {
        return $this->belongsTo(BienTheSanPham::class, 'variant_id');
    }

    public function getProductAttribute()
    {
        return $this->variant?->product;
    }

    public function getTenSanPhamAttribute()
    {
        return $this->variant?->ten_hien_thi . ' - ' . $this->ten_don_vi;
    }
}
