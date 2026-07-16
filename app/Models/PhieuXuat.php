<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PhieuXuat extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'phieu_xuat';

    protected $fillable = [
        'id_phieu',
        'loai_xuat',
        'id_phieu_nhap_lien_quan',
        'ly_do',
        'ghi_chu',
    ];

    public function phieu()
    {
        return $this->belongsTo(Phieu::class, 'id_phieu');
    }

    public function phieuNhapLienQuan()
    {
        return $this->belongsTo(Phieu::class, 'id_phieu_nhap_lien_quan');
    }

    public function chiTietPhieu()
    {
        return $this->hasMany(ChiTietPhieu::class, 'id_phieu', 'id_phieu');
    }
}
