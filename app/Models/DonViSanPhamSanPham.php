<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DonViSanPhamSanPham extends BaseModel
{
    protected $table = 'don_vi_san_pham_san_pham';

    protected $fillable = [
        'id_san_pham',
        'id_don_vi',
        'ten_don_vi',
        'so_luong_quy_doi',
        'gia_ban_le',
        'gia_ban_si',
        'ma_vach',
        'hinh_anh',
        'la_don_vi_mac_dinh',
    ];

    protected $casts = [
        'gia_ban_le' => 'decimal:2',
        'gia_ban_si' => 'decimal:2',
        'so_luong_quy_doi' => 'integer',
        'la_don_vi_mac_dinh' => 'boolean',
    ];

    public function sanPham(): BelongsTo
    {
        return $this->belongsTo(SanPham::class, 'id_san_pham');
    }

    public function donVi(): BelongsTo
    {
        return $this->belongsTo(DonViSanPham::class, 'id_don_vi');
    }
}
