<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PhieuNhap extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'phieu_nhap';

    protected $fillable = [
        'id_phieu',
        'loai_nhap',
        'id_hoa_don',
        'id_phieu_xuat_goc',
        'ghi_chu',
    ];

    public function phieu()
    {
        return $this->belongsTo(Phieu::class, 'id_phieu');
    }

    public function hoaDon()
    {
        return $this->belongsTo(HoaDon::class, 'id_hoa_don');
    }

    public function phieuXuatGoc()
    {
        return $this->belongsTo(Phieu::class, 'id_phieu_xuat_goc');
    }

    public function chiTietPhieu()
    {
        return $this->hasMany(ChiTietPhieu::class, 'id_phieu', 'id_phieu');
    }
}
