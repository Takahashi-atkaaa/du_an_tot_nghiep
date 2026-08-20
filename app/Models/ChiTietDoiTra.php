<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ChiTietDoiTra extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'chi_tiet_doi_tra';

    protected $fillable = [
        'id_doi_tra',
        'id_bien_the',
        'id_bien_the_thay_the',
        'loai',
        'so_luong',
        'gia_ban',
        'thanh_tien',
    ];

    protected $casts = [
        'so_luong' => 'integer',
        'gia_ban' => 'decimal:2',
        'thanh_tien' => 'decimal:2',
    ];

    public function isTraHang(): bool
    {
        return $this->loai === 'tra_hang';
    }

    public function isDoiHang(): bool
    {
        return $this->loai === 'doi_hang';
    }

    public function doiTra()
    {
        return $this->belongsTo(DoiTra::class, 'id_doi_tra');
    }

    public function bienTheSanPham()
    {
        return $this->belongsTo(BienTheSanPham::class, 'id_bien_the');
    }

    public function bienTheThayThe()
    {
        return $this->belongsTo(BienTheSanPham::class, 'id_bien_the_thay_the');
    }

    public function hangLois()
    {
        return $this->hasMany(HangLoi::class, 'id_chi_tiet_doi_tra');
    }
}
