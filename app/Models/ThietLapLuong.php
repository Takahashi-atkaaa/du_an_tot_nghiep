<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

class ThietLapLuong extends BaseModel
{
    use SoftDeletes;

    protected $table = 'thiet_lap_luong';

    protected $fillable = [
        'id_nguoi_dung',
        'luong_theo_gio',
        'phu_cap',
        'phat_di_muon',
        'luong_tang_ca',
        'thuong',
        'phat_bo_ca',
    ];

    protected $casts = [
        'luong_theo_gio' => 'decimal:2',
        'phu_cap' => 'decimal:2',
        'phat_di_muon' => 'decimal:2',
        'luong_tang_ca' => 'decimal:2',
        'thuong' => 'decimal:2',
        'phat_bo_ca' => 'decimal:2',
    ];

    public function nguoiDung(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(NguoiDung::class, 'id_nguoi_dung');
    }
}
