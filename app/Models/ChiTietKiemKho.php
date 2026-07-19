<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChiTietKiemKho extends Model
{
    protected $table = 'chi_tiet_kiem_kho';

    protected $fillable = [
        'id_phieu_kiem_kho',
        'variant_id',
        'id_chi_tiet_lo_hang',
        'ma_vach',
        'ten_san_pham',
        'ten_bien_the',
        'ten_don_vi',
        'han_su_dung',
        'ma_lo',
        'so_luong_ton',
        'so_luong_thuc_te',
        'so_luong_lech',
        'gia_von',
        'gia_tri_lech',
    ];

    protected $casts = [
        'so_luong_ton' => 'integer',
        'so_luong_thuc_te' => 'integer',
        'so_luong_lech' => 'integer',
        'gia_von' => 'decimal:2',
        'gia_tri_lech' => 'decimal:2',
        'han_su_dung' => 'date',
    ];

    public function phieuKiemKho()
    {
        return $this->belongsTo(PhieuKiemKho::class, 'id_phieu_kiem_kho');
    }

    public function variant()
    {
        return $this->belongsTo(BienTheSanPham::class, 'variant_id');
    }

    public function chiTietLoHang()
    {
        return $this->belongsTo(ChiTietLoHang::class, 'id_chi_tiet_lo_hang');
    }

    /**
     * Tính lại so_luong_lech + gia_tri_lech từ so_luong_thuc_te và so_luong_ton.
     * Gọi trước khi save.
     */
    public function recomputeLech(): void
    {
        if ($this->so_luong_thuc_te === null) {
            $this->so_luong_lech = 0;
            $this->gia_tri_lech = 0;
            return;
        }
        $this->so_luong_lech = (int) $this->so_luong_thuc_te - (int) $this->so_luong_ton;
        $this->gia_tri_lech = (float) $this->so_luong_lech * (float) $this->gia_von;
    }

    /**
     * Đã nhân viên đếm xong chưa (có số thực tế)?
     */
    public function getDaKiemAttribute(): bool
    {
        return $this->so_luong_thuc_te !== null;
    }

    /**
     * Khớp (lech == 0) hay lệch (lech != 0).
     */
    public function getKhopAttribute(): bool
    {
        return $this->so_luong_lech == 0 && $this->da_kiem;
    }
}
