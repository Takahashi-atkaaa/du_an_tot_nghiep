<?php

namespace App\Models;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChiTietLoHang extends Model
{
    use HasFactory;

    protected $table = 'chi_tiet_lo_hang';

    protected $fillable = [
        'id_lo_hang',
        'id_san_pham',
        'variant_id',
        'so_luong_nhap',
        'so_luong_ton',
        'gia_nhap',
        'han_su_dung',
    ];

    protected $casts = [
        'so_luong_nhap' => 'integer',
        'so_luong_ton' => 'integer',
        'gia_nhap' => 'decimal:2',
        'han_su_dung' => 'date',
    ];

    public function loHang()
    {
        return $this->belongsTo(LoHang::class, 'id_lo_hang');
    }

    public function variant()
    {
        return $this->belongsTo(BienTheSanPham::class, 'variant_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'id_san_pham');
    }

    public function chiTietPhieu()
    {
        return $this->hasMany(ChiTietPhieu::class, 'id_chi_tiet_lo_hang');
    }

    public function scopeTon($query)
    {
        return $query->where('so_luong_ton', '>', 0);
    }

    public function scopeFefo($query)
    {
        return $query->where('so_luong_ton', '>', 0)
            ->orderBy('han_su_dung', 'asc');
    }
}
