<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PhieuKiemKho extends Model
{
    use SoftDeletes;

    protected $table = 'phieu_kiem_kho';

    protected $fillable = [
        'ma_kiem_kho',
        'id_nguoi_tao',
        'id_nguoi_kiem',
        'id_nguoi_duyet',
        'id_chia_ca_lam_viec',
        'pham_vi',
        'id_danh_muc',
        'variant_ids',
        'ngay_kiem',
        'trang_thai',
        'tong_so_san_pham',
        'tong_sl_thuc_te',
        'tong_sl_he_thong',
        'tong_sl_lech',
        'so_sp_thieu',
        'so_sp_thua',
        'so_sp_dung',
        'tong_gia_tri_lech',
        'ghi_chu',
        'ly_do_huy',
        'ly_do_tu_choi',
        'bat_dau_luc',
        'hoan_tat_dem_luc',
        'duyet_luc',
        'hoan_thanh_luc',
        'huy_luc',
    ];

    protected $casts = [
        'variant_ids' => 'array',
        'tong_so_san_pham' => 'integer',
        'tong_sl_thuc_te' => 'integer',
        'tong_sl_he_thong' => 'integer',
        'tong_sl_lech' => 'integer',
        'so_sp_thieu' => 'integer',
        'so_sp_thua' => 'integer',
        'so_sp_dung' => 'integer',
        'tong_gia_tri_lech' => 'decimal:2',
        'ngay_kiem' => 'date',
        'bat_dau_luc' => 'datetime',
        'hoan_tat_dem_luc' => 'datetime',
        'duyet_luc' => 'datetime',
        'hoan_thanh_luc' => 'datetime',
        'huy_luc' => 'datetime',
    ];

    // ====== Relationships ======

    public function nguoiTao()
    {
        return $this->belongsTo(NguoiDung::class, 'id_nguoi_tao');
    }

    public function nguoiKiem()
    {
        return $this->belongsTo(NguoiDung::class, 'id_nguoi_kiem');
    }

    public function nguoiDuyet()
    {
        return $this->belongsTo(NguoiDung::class, 'id_nguoi_duyet');
    }

    public function chiaCaLamViec()
    {
        return $this->belongsTo(ChiaCaLamViec::class, 'id_chia_ca_lam_viec');
    }

    public function chiTietKiemKho()
    {
        return $this->hasMany(ChiTietKiemKho::class, 'id_phieu_kiem_kho');
    }

    public function khoaKiemKho()
    {
        return $this->hasMany(KhoaKiemKho::class, 'id_phieu_kiem_kho');
    }

    public function danhMuc()
    {
        return $this->belongsTo(DanhMucSanPham::class, 'id_danh_muc');
    }

    // ====== Scopes ======

    public function scopeChuaHoanThanh($query)
    {
        return $query->whereNotIn('trang_thai', ['hoan_thanh', 'da_huy']);
    }

    public function scopeDangDem($query)
    {
        return $query->whereIn('trang_thai', ['phieu_tam', 'counting']);
    }

    public function scopeChoDuyet($query)
    {
        return $query->where('trang_thai', 'cho_duyet');
    }

    public function scopeHoanThanh($query)
    {
        return $query->where('trang_thai', 'hoan_thanh');
    }

    public function scopeDaHuy($query)
    {
        return $query->where('trang_thai', 'da_huy');
    }

    // ====== Static helpers ======

    /**
     * Sinh mã kiểm kho tiếp theo, định dạng KK00001, KK00002...
     */
    public static function generateMaKiemKho(): string
    {
        $nextId = (int) (self::max('id') ?? 0) + 1;
        return 'KK' . str_pad((string) $nextId, 5, '0', STR_PAD_LEFT);
    }

    // ====== Recompute totals ======

    /**
     * Tính lại các tổng từ các chi tiết. Dùng khi lưu nháp, hoàn tất đếm.
     */
    public function recomputeTotals(): void
    {
        $details = $this->chiTietKiemKho()->get();

        $tongSlHeThong = 0;
        $tongSlThucTe = 0;
        $tongSlLech = 0;
        $tongGiaTriLech = 0;
        $thieu = 0;
        $thua = 0;
        $dung = 0;

        foreach ($details as $d) {
            $tongSlHeThong += (int) $d->so_luong_he_thong;

            if ($d->so_luong_thuc_te !== null) {
                $tongSlThucTe += (int) $d->so_luong_thuc_te;
                $tongSlLech += (int) $d->so_luong_lech;
                $tongGiaTriLech += (float) $d->gia_tri_lech;

                if ($d->so_luong_lech < 0) {
                    $thieu++;
                } elseif ($d->so_luong_lech > 0) {
                    $thua++;
                } else {
                    $dung++;
                }
            }
        }

        $this->tong_so_san_pham = $details->count();
        $this->tong_sl_he_thong = $tongSlHeThong;
        $this->tong_sl_thuc_te = $tongSlThucTe;
        $this->tong_sl_lech = $tongSlLech;
        $this->so_sp_thieu = $thieu;
        $this->so_sp_thua = $thua;
        $this->so_sp_dung = $dung;
        $this->tong_gia_tri_lech = $tongGiaTriLech;
        $this->save();
    }

    // ====== Accessors ======

    public function getTrangThaiLabelAttribute(): string
    {
        return match ($this->trang_thai) {
            'phieu_tam' => 'Phiếu tạm',
            'counting' => 'Đang đếm',
            'cho_duyet' => 'Chờ duyệt',
            'da_duyet' => 'Đã duyệt',
            'hoan_thanh' => 'Hoàn thành',
            'tu_choi' => 'Từ chối',
            'da_huy' => 'Đã hủy',
            default => $this->trang_thai,
        };
    }

    public function getTrangThaiBadgeAttribute(): string
    {
        return match ($this->trang_thai) {
            'phieu_tam' => 'bg-secondary',
            'counting' => 'bg-info text-dark',
            'cho_duyet' => 'bg-warning text-dark',
            'da_duyet' => 'bg-primary',
            'hoan_thanh' => 'bg-success',
            'tu_choi' => 'bg-danger',
            'da_huy' => 'bg-dark',
            default => 'bg-secondary',
        };
    }

    public function getPhamViLabelAttribute(): string
    {
        return match ($this->pham_vi) {
            'toan_bo' => 'Toàn bộ sản phẩm',
            'theo_danh_muc' => 'Theo danh mục',
            'chon_san_pham' => 'Chọn sản phẩm',
            default => $this->pham_vi,
        };
    }

    public function getCoTheSuaAttribute(): bool
    {
        return $this->trang_thai === 'phieu_tam';
    }

    public function getCoTheBatDauKiemAttribute(): bool
    {
        return $this->trang_thai === 'phieu_tam';
    }

    public function getCoTheDemAttribute(): bool
    {
        return in_array($this->trang_thai, ['counting', 'tu_choi'], true);
    }

    public function getCoTheHoanTatDemAttribute(): bool
    {
        return $this->trang_thai === 'counting';
    }

    public function getCoTheDuyetAttribute(): bool
    {
        return $this->trang_thai === 'cho_duyet';
    }

    public function getCoTheTuChoiAttribute(): bool
    {
        return $this->trang_thai === 'cho_duyet';
    }

    public function getCoTheDemLaiAttribute(): bool
    {
        return $this->trang_thai === 'tu_choi';
    }

    public function getCoTheHoanTatAttribute(): bool
    {
        return $this->trang_thai === 'da_duyet';
    }

    public function getCoTheHuyAttribute(): bool
    {
        return !in_array($this->trang_thai, ['hoan_thanh', 'da_huy'], true);
    }

    /**
     * Trạng thái có khoá biến thể (chặn bán hàng) hay không
     */
    public function getDangKhoaBienTheAttribute(): bool
    {
        return $this->trang_thai === 'counting';
    }

    /**
     * Da dem xong chua - kiem tat ca detail da co so_luong_thuc_te
     */
    public function getDaDemXongAttribute(): bool
    {
        $total = $this->chiTietKiemKho()->count();
        if ($total === 0) {
            return false;
        }
        $daDem = $this->chiTietKiemKho()->whereNotNull('so_luong_thuc_te')->count();
        return $daDem === $total;
    }
}
