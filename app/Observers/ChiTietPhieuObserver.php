<?php

namespace App\Observers;

use App\Models\ChiTietPhieu;

class ChiTietPhieuObserver
{
    /**
     * KHONG can thiep so_luong_ton cua chi_tiet_lo_hang khi xoa chi_tiet_phieu.
     *
     * Ly do:
     * - Khi xoa phieu nhap, controller destroy() se xoa luon chi_tiet_lo_hang
     *   (thong qua model cascade / xoa theo id_chi_tiet_lo_hang).
     * - ChiTietLoHangObserver::deleted() da dong bo tong ton bien_the_san_pham.so_luong_ton
     *   mot lan khi chi_tiet_lo_hang bi xoa.
     *
     * Viec cu observer cu "increment so_luong_ton" khi xoa chi_tiet_phieu la sai ve
     * nghiep vu: no lam tang ton ao vinh vien khi destroy phieu (vi luc do chi_tiet_lo_hang
     * khong bi xoa theo, nen tong ton bien_the bi cong ngược).
     */
    public function deleted(ChiTietPhieu $model): void
    {
        // No-op.
    }
}
