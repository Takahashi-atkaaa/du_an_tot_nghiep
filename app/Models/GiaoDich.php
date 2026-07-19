<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GiaoDich extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'giao_dich';

    public const PHUONG_THUC_TIEN_MAT = 'tien_mat';
    public const PHUONG_THUC_VNPAY = 'vnpay';

    public const TRANG_THAI_CHO_XAC_NHAN = 'cho_xac_nhan';
    public const TRANG_THAI_THANH_CONG = 'thanh_cong';
    public const TRANG_THAI_THAT_BAI = 'that_bai';
    public const TRANG_THAI_HOAN_TIEN = 'hoan_tien';

    protected $fillable = [
        'id_hoa_don',
        'phuong_thuc',
        'so_tien',
        'trang_thai',
        'ma_tham_chieu',
        'ma_giao_dich_doi_tac',
        'ma_phan_hoi',
        'trang_thai_doi_tac',
        'du_lieu_phan_hoi',
        'ma_ngan_hang',
        'ngay_gio_thanh_toan',
    ];

    protected $casts = [
        'so_tien' => 'decimal:2',
        'ngay_gio_thanh_toan' => 'datetime',
        'du_lieu_phan_hoi' => 'array',
    ];

    public function hoaDon(): BelongsTo
    {
        return $this->belongsTo(HoaDon::class, 'id_hoa_don');
    }
}
