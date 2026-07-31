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
        'id_chia_ca_lam_viec',
        'id_nguoi_dung',
        'trang_thai',
        'tong_sl_thuc_te',
        'tong_sl_lech',
        'tong_gia_tri_lech',
        'ghi_chu',
        'hoan_thanh_luc',
    ];

    protected $casts = [
        'tong_sl_thuc_te' => 'integer',
        'tong_sl_lech' => 'integer',
        'tong_gia_tri_lech' => 'decimal:2',
        'id_chia_ca_lam_viec' => 'integer',
        'id_nguoi_dung' => 'integer',
        'hoan_thanh_luc' => 'datetime',
    ];

    public function nguoiDung()
    {
        return $this->belongsTo(NguoiDung::class, 'id_nguoi_dung');
    }

    public function chiaCaLamViec()
    {
        return $this->belongsTo(ChiaCaLamViec::class, 'id_chia_ca_lam_viec');
    }

    public function chiTietKiemKho()
    {
        return $this->hasMany(ChiTietKiemKho::class, 'id_phieu_kiem_kho')
            ->orderBy('han_su_dung', 'asc');
    }

    /**
     * Sinh mã kiểm kho tiếp theo, định dạng KK00001, KK00002...
     * Lấy max(strip non-digit) để tránh trùng khi xóa giữa chừng.
     */
    public static function generateMaKiemKho(): string
    {
        // Lấy max id + 1, định dạng KK + zero-pad 5 chữ số
        $nextId = (int) (self::max('id') ?? 0) + 1;
        return 'KK' . str_pad((string) $nextId, 5, '0', STR_PAD_LEFT);
    }

    /**
     * Recompute các tổng từ các chi tiết, dùng khi lưu nháp để sidebar update real-time.
     */
    public function recomputeTotals(): void
    {
        $details = $this->chiTietKiemKho()->get();
        $tongThucTe = 0;
        $tongLech = 0;
        $tongGiaTriLech = 0;

        foreach ($details as $d) {
            // Chỉ cộng dòng đã kiểm (so_luong_thuc_te !== null)
            if ($d->so_luong_thuc_te !== null) {
                $tongThucTe += (int) $d->so_luong_thuc_te;
            }
            $tongLech += (int) $d->so_luong_lech;
            $tongGiaTriLech += (float) $d->gia_tri_lech;
        }

        $this->tong_sl_thuc_te = $tongThucTe;
        $this->tong_sl_lech = $tongLech;
        $this->tong_gia_tri_lech = $tongGiaTriLech;
        $this->save();
    }

    public function getTrangThaiLabelAttribute(): string
    {
        return match ($this->trang_thai) {
            'phieu_tam' => 'Phiếu tạm',
            'hoan_thanh' => 'Hoàn thành',
            'da_huy' => 'Đã hủy',
            default => $this->trang_thai,
        };
    }

    public function getTrangThaiBadgeAttribute(): string
    {
        return match ($this->trang_thai) {
            'phieu_tam' => 'bg-warning text-dark',
            'hoan_thanh' => 'bg-success',
            'da_huy' => 'bg-secondary',
            default => 'bg-secondary',
        };
    }
}
