<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

class PhieuLuong extends BaseModel
{
    use SoftDeletes;

    protected $table = 'phieu_luong';

    protected $fillable = [
        'id_nguoi_dung',
        'id_bang_luong',
        'luong_tam_tinh',
        'trang_thai',
        'tong_gio_lam_thuc_te',
        'tong_luong',
    ];

    protected $casts = [
        'luong_tam_tinh' => 'decimal:2',
        'tong_gio_lam_thuc_te' => 'decimal:2',
        'tong_luong' => 'decimal:2',
    ];

    public function nguoiDung(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(NguoiDung::class, 'id_nguoi_dung');
    }

    public function bangLuong(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(BangLuong::class, 'id_bang_luong');
    }
}
