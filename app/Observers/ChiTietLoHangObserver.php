<?php

namespace App\Observers;

use App\Models\ChiTietLoHang;
use App\Models\BienTheSanPham;
use Illuminate\Support\Facades\Log;

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
            $oldVariantId = $model->getOriginal('variant_id');
            $newVariantId = $model->variant_id;

            $this->syncTonKhoVariant($oldVariantId);
            $this->syncTonKhoVariant($newVariantId);
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

        try {
            $tongTon = ChiTietLoHang::where('variant_id', $variantId)->sum('so_luong_ton');
            BienTheSanPham::where('id', $variantId)->update(['so_luong_ton' => (int) $tongTon]);
        } catch (\Throwable $e) {
            // Không để observer làm fail cả request CRUD.
            // Ghi log để debug nếu cần thiết.
            Log::warning('ChiTietLoHangObserver syncTonKhoVariant failed', [
                'variant_id' => $variantId,
                'error' => $e->getMessage(),
            ]);
        }
    }
}