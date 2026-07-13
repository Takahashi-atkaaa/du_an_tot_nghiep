<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CanhBao extends Model
{
    protected $table = 'bang_canh_bao';
    public $timestamps = false;
    protected $fillable = [
        'id_audit_log',
        'id_nguoi_dung_thuc_hien',
        'tieu_de',
        'noi_dung',
        'muc_do',
        'hanh_dong',
        'url_lien_ket',
        'da_doc',
        'id_nguoi_dung_da_doc',
        'thoi_gian_doc',
        'created_at',
    ];
    protected $casts = [
        'da_doc' => 'boolean',
        'thoi_gian_doc' => 'datetime',
        'created_at' => 'datetime',
    ];

    public function nguoiDungThucHien(): BelongsTo
    {
        return $this->belongsTo(NguoiDung::class, 'id_nguoi_dung_thuc_hien');
    }

    public function auditLog(): BelongsTo
    {
        return $this->belongsTo(AuditLog::class, 'id_audit_log');
    }
}