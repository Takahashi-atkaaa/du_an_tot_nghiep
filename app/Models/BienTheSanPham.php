<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BienTheSanPham extends Model
{
    use HasFactory;

    protected $table = 'bien_the_san_pham';

    protected $fillable = [
    'id_san_pham',
    'ten_bien_the',
    'he_so_quy_doi',
    'gia_bien_the',
    'trang_thai',
];
    public function sanPham()
    {
        return $this->belongsTo(SanPham::class, 'id_san_pham');
    }
}