<?php

namespace App\Observers;

use App\Models\ChiTietPhieu;
use App\Models\ChiTietLoHang;

class ChiTietPhieuObserver
{
    public function deleted(ChiTietPhieu $model): void
    {
        if ($model->id_chi_tiet_lo_hang && $model->variant_id) {
            ChiTietLoHang::where('id', $model->id_chi_tiet_lo_hang)
                ->increment('so_luong_ton', $model->so_luong);
        }
    }
}
