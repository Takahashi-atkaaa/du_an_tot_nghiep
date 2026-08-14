<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChiTietHoaDon extends Model
{
    use HasFactory;

    protected $appends = [
        'ten_hien_thi_san_pham',
    ];

    protected $table = 'chi_tiet_hoa_don';

    protected $fillable = [
        'id_hoa_don',
        'id_bien_the_san_pham',
        'so_luong',
        'gia_ban',
        'thanh_tien',
    ];

    public function hoaDon()
    {
        return $this->belongsTo(HoaDon::class, 'id_hoa_don');
    }

    public function bienTheSanPham()
    {
        return $this->belongsTo(BienTheSanPham::class, 'id_bien_the_san_pham');
    }

    public function sanPham()
    {
        return $this->hasOneThrough(
            SanPham::class,
            BienTheSanPham::class,
            'id',
            'id',
            'id_bien_the_san_pham',
            'product_id'
        );
    }

    public function getTenHienThiSanPhamAttribute(): string
    {
        $bienThe = $this->bienTheSanPham;

        if (!$bienThe) {
            return '';
        }

        return $bienThe->ten_hien_thi
            ?? $bienThe->product?->ten_san_pham
            ?? '';
    }
}
