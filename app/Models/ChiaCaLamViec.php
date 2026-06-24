<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ChiaCaLamViec extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'chia_ca_lam_viec';

    protected $fillable = [
        'id_ca_lam_viec',
        'id_nguoi_dung',
        'ngay',
        'vai_tro_trong_ca',
    ];

    protected $casts = [
        'id_ca_lam_viec' => 'integer',
        'id_nguoi_dung' => 'integer',
        'ngay' => 'date',
    ];

    public function caLamViec(): BelongsTo
    {
        return $this->belongsTo(CaLamViec::class, 'id_ca_lam_viec');
    }

    public function nguoiDung(): BelongsTo
    {
        return $this->belongsTo(NguoiDung::class, 'id_nguoi_dung');
    }

    public function diemDanh(){
        return $this->HasOne(DiemDanh::class,'id_chia_ca_lam_viec', 'id');
    }
}
