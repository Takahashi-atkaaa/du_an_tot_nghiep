<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DoiTra extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'doi_tra';

    protected $fillable = [
        'id_nguoi_dung',
        'id_hoa_don',
        'Loai',
        'hang_loi',
        'ngay',
        'tru_diem_cua_khach',
        'ly_do',
    ];

    protected $casts = [
        'ngay' => 'datetime',
        'hang_loi' => 'boolean',
        'tru_diem_cua_khach' => 'boolean',
    ];

    public function nguoiDung()
    {
        return $this->belongsTo(NguoiDung::class, 'id_nguoi_dung');
    }

    public function hoaDon()
    {
        return $this->belongsTo(HoaDon::class, 'id_hoa_don');
    }

    public function chiTietDoiTras()
    {
        return $this->hasMany(ChiTietDoiTra::class, 'id_doi_tra');
    }

    public function hangLois()
    {
        return $this->hasMany(HangLoi::class, 'id_doi_tra');
    }
}
