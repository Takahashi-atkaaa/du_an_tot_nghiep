<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChiTietPhieu extends Model
{
    use HasFactory;

    protected $table = 'chi_tiet_phieu';

    protected $fillable = [
        'id_phieu',
        'id_san_pham',
        'id_lo_hang',
        'id_chi_tiet_lo_hang',
        'so_luong',
        'gia_nhap',
        'ma_lo',
        'han_su_dung',
        'so_luong_con_lai',
        'ghi_chu',
    ];

    public function phieu()
    {
        return $this->belongsTo(Phieu::class, 'id_phieu');
    }

    public function sanPham()
    {
        return $this->belongsTo(SanPham::class, 'id_san_pham');
    }

    public function loHang()
    {
        return $this->belongsTo(LoHang::class, 'id_lo_hang');
    }

    public function chiTietLoHang()
    {
        return $this->belongsTo(ChiTietLoHang::class, 'id_chi_tiet_lo_hang');
    }
}
