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

    public function hasTraHang(): bool
    {
        return $this->chiTietDoiTras->contains(function (ChiTietDoiTra $chiTiet) {
            return ($chiTiet->loai ?? null) === 'tra_hang'
                || (($chiTiet->loai ?? null) === null && $this->Loai === 'tra_hang');
        });
    }

    public function hasDoiHang(): bool
    {
        return $this->chiTietDoiTras->contains(function (ChiTietDoiTra $chiTiet) {
            return ($chiTiet->loai ?? null) === 'doi_hang'
                || (($chiTiet->loai ?? null) === null && $this->Loai === 'doi_tra');
        });
    }

    public function getLoaiHienThiAttribute(): string
    {
        $hasTraHang = $this->relationLoaded('chiTietDoiTras') ? $this->hasTraHang() : $this->Loai === 'tra_hang';
        $hasDoiHang = $this->relationLoaded('chiTietDoiTras') ? $this->hasDoiHang() : $this->Loai === 'doi_tra';

        if ($hasTraHang && $hasDoiHang) {
            return 'Đổi / Trả hàng';
        }

        return $hasTraHang ? 'Trả hàng' : 'Đổi hàng';
    }
}
