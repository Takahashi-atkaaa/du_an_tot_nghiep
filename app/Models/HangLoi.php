<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class HangLoi extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'hang_loi';

    protected $fillable = [
        'id_doi_tra',
        'id_chi_tiet_doi_tra',
        'id_bien_the',
        'so_luong',
        'trang_thai',
        'ly_do',
        'ngay_tieu_huy',
        'id_nguoi_dung_tieu_huy',
    ];

    protected $casts = [
        'so_luong' => 'integer',
        'ngay_tieu_huy' => 'datetime',
    ];

    public function doiTra()
    {
        return $this->belongsTo(DoiTra::class, 'id_doi_tra');
    }

    public function chiTietDoiTra()
    {
        return $this->belongsTo(ChiTietDoiTra::class, 'id_chi_tiet_doi_tra');
    }

    public function bienTheSanPham()
    {
        return $this->belongsTo(BienTheSanPham::class, 'id_bien_the');
    }

    public function nguoiDungTieuHuy()
    {
        return $this->belongsTo(NguoiDung::class, 'id_nguoi_dung_tieu_huy');
    }
}
