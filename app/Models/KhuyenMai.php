<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class KhuyenMai extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'khuyen_mai';

    protected $fillable = [
        'ten_chuong_trinh',
        'loai_giam_gia',
        'gia_tri_giam',
        'giam_toi_da',
        'so_luong_sp_toi_thieu',
        'don_hang_toi_thieu',
        'ngay_bat_dau',
        'ngay_ket_thuc',
        'trang_thai',
        'ghi_chu',
    ];

    protected $casts = [
        'ngay_bat_dau' => 'datetime',
        'ngay_ket_thuc' => 'datetime',
        'gia_tri_giam' => 'decimal:2',
        'giam_toi_da' => 'decimal:2',
        'don_hang_toi_thieu' => 'decimal:2',
        'trang_thai' => 'boolean',
    ];

    /**
     * Scope to get promotions that are currently active (flag true and now between dates)
     */
    public function scopeCurrentlyActive($query)
    {
        $now = Carbon::now();
        return $query->where('trang_thai', true)
                     ->whereNotNull('ngay_bat_dau')
                     ->whereNotNull('ngay_ket_thuc')
                     ->where('ngay_bat_dau', '<=', $now)
                     ->where('ngay_ket_thuc', '>=', $now);
    }

    /**
     * Check if this promotion is currently active (usable)
     */
    public function isCurrentlyActive(): bool
    {
        if (! $this->trang_thai) return false;
        if (! $this->ngay_bat_dau || ! $this->ngay_ket_thuc) return false;
        $now = Carbon::now();
        return $now->between($this->ngay_bat_dau, $this->ngay_ket_thuc);
    }

    /**
     * Check if promotion is applicable to an order by amount and/or product quantity
     * (pass null to ignore a check)
     */
    public function applicableToOrder(?float $orderAmount = null, ?int $productCount = null): bool
    {
        if (! $this->isCurrentlyActive()) return false;

        if (! is_null($this->don_hang_toi_thieu) && ! is_null($orderAmount)) {
            if (floatval($orderAmount) < floatval($this->don_hang_toi_thieu)) return false;
        }

        if (! is_null($this->so_luong_sp_toi_thieu) && ! is_null($productCount)) {
            if (intval($productCount) < intval($this->so_luong_sp_toi_thieu)) return false;
        }

        return true;
    }
}
