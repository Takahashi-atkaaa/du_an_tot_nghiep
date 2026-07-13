<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    protected $table = 'bang_audit_logs';
    public $timestamps = false;
    protected $fillable = [
        'id_nguoi_dung',
        'hanh_dong',
        'bang_bi_tac_dong',
        'id_ban_ghi',
        'mo_ta',
        'du_lieu_cu',
        'du_lieu_moi',
        'muc_do',
        'ip_address',
        'user_agent',
        'created_at',
    ];
    protected $casts = [
        'du_lieu_cu' => 'array',
        'du_lieu_moi' => 'array',
        'created_at' => 'datetime',
    ];

    public function nguoiDung(): BelongsTo
    {
        return $this->belongsTo(NguoiDung::class, 'id_nguoi_dung');
    }
}