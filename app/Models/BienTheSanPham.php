<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BienTheSanPham extends Model
{
    use HasFactory;

    protected $table = 'bien_the_san_pham';

    protected $fillable = [
        'ten_bien_the',
        'trang_thai',
        'id_san_pham',
        'he_so_quy_doi',
    ];

    public function sanPham()
    {
        return $this->belongsTo(SanPham::class, 'id_san_pham');
    }
}