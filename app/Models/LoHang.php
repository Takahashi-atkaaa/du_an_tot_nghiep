<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LoHang extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'lo_hang';

    protected $fillable = [
        'id_phieu',
        'id_nha_cung_cap',
        'ma_lo',
        'ngay_nhap',
        'ghi_chu',
    ];

    protected $casts = [
        'ngay_nhap' => 'date',
    ];

    public function phieu()
    {
        return $this->belongsTo(Phieu::class, 'id_phieu');
    }

    public function nhaCungCap()
    {
        return $this->belongsTo(NhaCungCap::class, 'id_nha_cung_cap');
    }

    public function chiTietLoHang()
    {
        return $this->hasMany(ChiTietLoHang::class, 'id_lo_hang');
    }

    public function chiTietPhieu()
    {
        return $this->hasMany(ChiTietPhieu::class, 'id_lo_hang');
    }
}
