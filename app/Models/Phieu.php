<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Phieu extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'phieu';

    protected $fillable = [
        'loai_phieu',
        'loai_phieu_enum',
        'id_nguoi_dung',
        'id_nha_cung_cap',
        'id_hoa_don',
        'ly_do',
        'ghi_chu',
    ];

    public function nguoiDung()
    {
        return $this->belongsTo(NguoiDung::class, 'id_nguoi_dung');
    }

    public function nhaCungCap()
    {
        return $this->belongsTo(NhaCungCap::class, 'id_nha_cung_cap');
    }

    public function hoaDon()
    {
        return $this->belongsTo(HoaDon::class, 'id_hoa_don');
    }

    public function chiTietPhieu()
    {
        return $this->hasMany(ChiTietPhieu::class, 'id_phieu');
    }

    public function phieuNhap()
    {
        return $this->hasOne(PhieuNhap::class, 'id_phieu');
    }

    public function phieuXuat()
    {
        return $this->hasOne(PhieuXuat::class, 'id_phieu');
    }

    public function loHang()
    {
        return $this->hasMany(LoHang::class, 'id_phieu');
    }
}
