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
            $this->syncGiaVonVariant($model->variant_id);
        }
    }

    public function updated(ChiTietLoHang $model): void
    {
        if ($model->wasChanged('variant_id')) {
            $this->syncTonKhoVariant($model->getOriginal('variant_id'));
            $this->syncGiaVonVariant($model->getOriginal('variant_id'));
            $this->syncTonKhoVariant($model->variant_id);
            $this->syncGiaVonVariant($model->variant_id);
        } elseif ($model->wasChanged('so_luong_ton') && $model->wasChanged('gia_nhap')) {
            // Cả số lượng tồn VÀ giá nhập thay đổi → cần tính lại giá vốn
            $this->syncTonKhoVariant($model->variant_id);
            $this->syncGiaVonVariant($model->variant_id);
        } elseif ($model->wasChanged('gia_nhap')) {
            // Chỉ giá nhập thay đổi (hiếm khi xảy ra) → vẫn cần tính lại giá vốn
            $this->syncGiaVonVariant($model->variant_id);
        } elseif ($model->wasChanged('so_luong_ton')) {
            // Chỉ số lượng tồn thay đổi (bán hàng) → KHÔNG tính lại giá vốn
            $this->syncTonKhoVariant($model->variant_id);
        }
    }

    public function deleted(ChiTietLoHang $model): void
    {
        if ($model->variant_id) {
            $this->syncTonKhoVariant($model->variant_id);
            $this->syncGiaVonVariant($model->variant_id);
        }
    }

    /**
     * Đồng bộ tổng tồn kho: bien_the_san_pham.so_luong_ton = SUM(chi_tiet_lo_hang.so_luong_ton)
     */
    private function syncTonKhoVariant(?int $variantId): void
    {
        if (!$variantId) {
            return;
        }

        $tongTon = ChiTietLoHang::where('variant_id', $variantId)->sum('so_luong_ton');
        BienTheSanPham::where('id', $variantId)->update(['so_luong_ton' => $tongTon]);
    }

    /**
     * Đồng bộ giá vốn biến thể = bình quân gia quyền từ các lô còn hàng:
     * gia_von = SUM(chi_tiet_lo_hang.gia_nhap * chi_tiet_lo_hang.so_luong_ton) / SUM(chi_tiet_lo_hang.so_luong_ton)
     * Chỉ tính với các lô có so_luong_ton > 0.
     * Khi xóa lô hoặc lô hết hàng, giá vốn được tính lại từ các lô còn lại.
     */
    private function syncGiaVonVariant(?int $variantId): void
    {
        if (!$variantId) {
            return;
        }

        $chiTietConTon = ChiTietLoHang::where('variant_id', $variantId)
            ->where('so_luong_ton', '>', 0)
            ->get(['gia_nhap', 'so_luong_ton']);

        if ($chiTietConTon->isEmpty()) {
            BienTheSanPham::where('id', $variantId)->update(['gia_von' => 0]);
            return;
        }

        $tongGiaTri = $chiTietConTon->sum(fn($ct) => (float) $ct->gia_nhap * (int) $ct->so_luong_ton);
        $tongSoLuongTon = $chiTietConTon->sum('so_luong_ton');

        $giaVonBq = $tongSoLuongTon > 0
            ? round($tongGiaTri / $tongSoLuongTon, 2)
            : 0;

        BienTheSanPham::where('id', $variantId)->update(['gia_von' => $giaVonBq]);
    }
}
