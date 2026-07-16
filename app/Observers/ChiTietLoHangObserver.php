<?php

namespace App\Observers;

use App\Models\ChiTietLoHang;
use App\Models\BienTheSanPham;

class ChiTietLoHangObserver
{
    public function created(ChiTietLoHang $model): void
    {
        if ($model->variant_id) {
            $this->syncTonKhoVariant($model->variant_id);
        }
    }

    public function updated(ChiTietLoHang $model): void
    {
        if ($model->wasChanged('variant_id')) {
            $this->syncTonKhoVariant($model->getOriginal('variant_id'));
            $this->syncTonKhoVariant($model->variant_id);
        } elseif ($model->wasChanged('so_luong_ton')) {
            $this->syncTonKhoVariant($model->variant_id);
        }
    }

    public function deleted(ChiTietLoHang $model): void
    {
        if ($model->variant_id) {
            $this->syncTonKhoVariant($model->variant_id);
        }
    }

    private function syncTonKhoVariant(?int $variantId): void
    {
        if (!$variantId) {
            return;
        }

        $tongTon = ChiTietLoHang::where('variant_id', $variantId)->sum('so_luong_ton');
        BienTheSanPham::where('id', $variantId)->update(['so_luong_ton' => $tongTon]);
    }
}
