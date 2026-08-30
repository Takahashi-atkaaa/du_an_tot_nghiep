<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChiTietKiemKho extends Model
{
    protected $table = 'chi_tiet_kiem_kho';

    protected $fillable = [
        'id_phieu_kiem_kho',
        'variant_id',
        'ma_vach',
        'ma_hang',
        'ten_san_pham',
        'ten_bien_the',
        'ten_don_vi',
        'han_su_dung_gan_nhat',
        'so_lo_con_ton',
        'so_luong_he_thong',
        'so_luong_thuc_te',
        'so_luong_lech',
        'gia_von',
        'gia_tri_lech',
        'ly_do',
        'ghi_chu',
        'dem_luc',
        'id_nguoi_dem',
        'lo_hang_snapshot',
    ];

    protected $casts = [
        'so_lo_con_ton' => 'integer',
        'so_luong_he_thong' => 'integer',
        'so_luong_thuc_te' => 'integer',
        'so_luong_lech' => 'integer',
        'gia_von' => 'decimal:2',
        'gia_tri_lech' => 'decimal:2',
        'han_su_dung_gan_nhat' => 'date',
        'dem_luc' => 'datetime',
        'lo_hang_snapshot' => 'array',
    ];

    // ====== Relationships ======

    public function phieuKiemKho()
    {
        return $this->belongsTo(PhieuKiemKho::class, 'id_phieu_kiem_kho');
    }

    public function variant()
    {
        return $this->belongsTo(BienTheSanPham::class, 'variant_id');
    }

    public function nguoiDem()
    {
        return $this->belongsTo(NguoiDung::class, 'id_nguoi_dem');
    }

    // ====== Methods ======

    /**
     * Tính lại so_luong_lech + gia_tri_lech từ so_luong_thuc_te và so_luong_he_thong.
     * Gọi trước khi save.
     */
    public function recomputeLech(): void
    {
        if ($this->so_luong_thuc_te === null) {
            $this->so_luong_lech = 0;
            $this->gia_tri_lech = 0;
            return;
        }
        $this->so_luong_lech = (int) $this->so_luong_thuc_te - (int) $this->so_luong_he_thong;
        $this->gia_tri_lech = (float) $this->so_luong_lech * (float) $this->gia_von;
    }

    // ====== Accessors ======

    public function getDaKiemAttribute(): bool
    {
        return $this->so_luong_thuc_te !== null;
    }

    public function getKhopAttribute(): bool
    {
        return $this->so_luong_lech == 0 && $this->da_kiem;
    }

    public function getLoaiChenhLechAttribute(): string
    {
        if (!$this->da_kiem) {
            return 'chua_dem';
        }
        if ($this->so_luong_lech < 0) {
            return 'thieu';
        }
        if ($this->so_luong_lech > 0) {
            return 'thua';
        }
        return 'du';
    }

    public function getLoaiChenhLechLabelAttribute(): string
    {
        return match ($this->loai_chenh_lech) {
            'thieu' => 'Thiếu',
            'thua' => 'Thừa',
            'du' => 'Đủ',
            'chua_dem' => 'Chưa đếm',
            default => $this->loai_chenh_lech,
        };
    }

    public function getLoaiChenhLechBadgeAttribute(): string
    {
        return match ($this->loai_chenh_lech) {
            'thieu' => 'bg-danger',
            'thua' => 'bg-warning text-dark',
            'du' => 'bg-success',
            'chua_dem' => 'bg-secondary',
            default => 'bg-light text-dark',
        };
    }
}
