<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GiaoCa extends Model
{
 protected $table = 'giao_ca';

    protected $fillable = [
        'id_truong_ca_ban_giao',
        'id_truong_ca_nhan_ca',
        'id_ca_lam_viec',
        'tien_mat_dau_ca',
        'tien_mat_cuoi_ca',
        'chenh_lech',
        'thoi_gian_bat_dau_ca',
        'thoi_gian_ket_thuc_ca',
        'trang_thai',
        'ghi_chu',
    ];

    protected $casts = [
        'tien_dau_ca' => 'decimal:2',
        'tien_cuoi_ca' => 'decimal:2',
        'chenh_lech' => 'decimal:2',
        'thoi_gian_bat_dau_ca' => 'datetime',
        'thoi_gian_ket_thuc_ca' => 'datetime',
    ];

     /**
     * Trưởng ca bàn giao
     */
    public function truongCaBanGiao(): BelongsTo
    {
        return $this->belongsTo(NguoiDung::class, 'id_truong_ca_ban_giao');
    }

    /**
     * Trưởng ca nhận ca
     */
    public function truongCaNhanCa(): BelongsTo
    {
        return $this->belongsTo(NguoiDung::class, 'id_truong_ca_nhan_ca');
    }

    /**
     * Ca làm việc
     */
    public function caLamViec(): BelongsTo
    {
        return $this->belongsTo(CaLamViec::class, 'id_ca_lam_viec');
    }
}
