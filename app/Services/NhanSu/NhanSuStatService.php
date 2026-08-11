<?php

namespace App\Services\NhanSu;

use App\Models\ChiaCaLamViec;
use App\Models\HoaDon;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

class NhanSuStatService
{
    /**
     * Lấy ca làm việc gần nhất của nhân viên (sắp xếp theo ngày, rồi id).
     */
    public static function getCaGanNhat(int $idNguoiDung): ?ChiaCaLamViec
    {
        return ChiaCaLamViec::query()
            ->with('caLamViec')
            ->where('id_nguoi_dung', $idNguoiDung)
            ->orderByDesc('ngay')
            ->orderByDesc('id')
            ->first();
    }

    /**
     * Lấy các ca làm việc trong ngày hôm nay.
     */
    public static function getCaHomNay(int $idNguoiDung): Collection
    {
        return ChiaCaLamViec::query()
            ->with('caLamViec')
            ->where('id_nguoi_dung', $idNguoiDung)
            ->whereDate('ngay', Carbon::today()->toDateString())
            ->orderBy('id_ca_lam_viec')
            ->get();
    }

    /**
     * Tổng số phút làm thực tế trong tháng.
     * Tính theo gio_bat_dau, gio_ket_thuc của ca làm việc được phân công.
     */
    public static function getTongGioLamThang(int $idNguoiDung, ?Carbon $thang = null): int
    {
        $thang = $thang ?: Carbon::now();

        $phut = ChiaCaLamViec::query()
            ->join('ca_lam_viec', 'chia_ca_lam_viec.id_ca_lam_viec', '=', 'ca_lam_viec.id')
            ->where('chia_ca_lam_viec.id_nguoi_dung', $idNguoiDung)
            ->whereMonth('chia_ca_lam_viec.ngay', $thang->month)
            ->whereYear('chia_ca_lam_viec.ngay', $thang->year)
            ->get(['ca_lam_viec.gio_bat_dau', 'ca_lam_viec.gio_ket_thuc'])
            ->sum(function ($row) {
                return self::calculateShiftMinutes(
                    (string) $row->gio_bat_dau,
                    (string) $row->gio_ket_thuc
                );
            });

        return (int) $phut;
    }

    /**
     * Thống kê hóa đơn trong tháng hiện tại của nhân viên.
     * Theo yêu cầu: thống kê cả hóa đơn Hoàn thành + Đã hủy + các trạng thái khác.
     */
    public static function getThongKeHoaDonThang(int $idNguoiDung): array
    {
        $query = HoaDon::query()
            ->where('id_nguoi_dung', $idNguoiDung)
            ->whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year);

        $tongHoaDon = (clone $query)->count();

        $tongDoanhThu = (clone $query)->sum('khach_can_tra');

        return [
            'tong_hoa_don' => (int) $tongHoaDon,
            'tong_doanh_thu' => (float) $tongDoanhThu,
        ];
    }

    /**
     * Helper: Tính phút làm của 1 ca (xoay qua đêm nếu gio_ket_thuc <= gio_bat_dau).
     */
    public static function calculateShiftMinutes(string $gioBatDau, string $gioKetThuc): int
    {
        $batDau = Carbon::createFromFormat('H:i:s', substr($gioBatDau, 0, 8));
        $ketThuc = Carbon::createFromFormat('H:i:s', substr($gioKetThuc, 0, 8));

        if ($ketThuc->lessThanOrEqualTo($batDau)) {
            $ketThuc->addDay();
        }

        return $batDau->diffInMinutes($ketThuc);
    }

    /**
     * Helper: Format phút thành chuỗi "X giờ Y phút".
     */
    public static function formatGioLam(int $minutes): string
    {
        $hours = intdiv($minutes, 60);
        $remainingMinutes = $minutes % 60;

        if ($remainingMinutes === 0) {
            return $hours . ' giờ';
        }

        return $hours . ' giờ ' . $remainingMinutes . ' phút';
    }
}
