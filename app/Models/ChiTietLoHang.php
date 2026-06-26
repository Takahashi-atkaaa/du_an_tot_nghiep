<?php

namespace App\Models;

class ChiTietLoHang extends BaseModel
{
    protected $table = 'chi_tiet_lo_hang';

    protected $fillable = [
        'id_lo_hang',
        'id_san_pham',
        'so_luong_nhap',
        'so_luong_ton',
        'gia_nhap',
        'han_su_dung',
    ];

    protected $casts = [
        'han_su_dung' => 'date',
        'gia_nhap' => 'decimal:2',
        'so_luong_nhap' => 'integer',
        'so_luong_ton' => 'integer',
    ];

    public function sanPham(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(SanPham::class, 'id_san_pham');
    }

    public function loHang(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\LoHang::class, 'id_lo_hang');
    }
}
