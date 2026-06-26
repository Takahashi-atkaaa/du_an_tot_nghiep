<?php

namespace App\Observers;

use App\Models\ChiTietLoHang;
use App\Models\SanPham;

class ChiTietLoHangObserver
{
    public function created(ChiTietLoHang $model): void
    {
        $this->syncTonKho($model->id_san_pham);
    }

    public function updated(ChiTietLoHang $model): void
    {
        if ($model->wasChanged('id_san_pham') || $model->wasChanged('so_luong_ton')) {
            $this->syncTonKho($model->getOriginal('id_san_pham'));
            $this->syncTonKho($model->id_san_pham);
        } elseif ($model->isDirty('so_luong_ton')) {
            $this->syncTonKho($model->id_san_pham);
        }
    }

    public function deleted(ChiTietLoHang $model): void
    {
        $this->syncTonKho($model->id_san_pham);
    }

    private function syncTonKho(int $idSanPham): void
    {
        $tongTon = ChiTietLoHang::where('id_san_pham', $idSanPham)->sum('so_luong_ton');
        SanPham::where('id', $idSanPham)->update(['so_luong_ton_kho' => $tongTon]);
    }
}
