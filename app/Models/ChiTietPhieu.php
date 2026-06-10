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
        'so_luong',
        'gia_nhap',
        'ma_lo',
        'han_su_dung',
        'so_luong_con_lai',
        'ghi_chu',
    ];
}
