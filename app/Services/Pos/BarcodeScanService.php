<?php

namespace App\Services\Pos;

use App\Models\BienTheSanPham;
use App\Models\DonViQuyDoi;

class BarcodeScanService
{
    public function resolveBarcode(string $barcode): ?array
    {
        $barcode = trim((string) $barcode);
        if ($barcode === '') {
            return null;
        }

        $variant = BienTheSanPham::query()
            ->where('trang_thai', 1)
            ->whereNull('deleted_at')
            ->where('ma_vach', $barcode)
            ->whereHas('product', function ($query) {
                $query->where('trang_thai', 1)->whereNull('deleted_at');
            })
            ->with('product')
            ->first();

        if ($variant) {
            if ((int) ($variant->so_luong_ton ?? 0) <= 0) {
                return [
                    'row_key' => 'variant_' . $variant->id,
                    'id' => $variant->id,
                    'unit_id' => null,
                    'available_qty' => 0,
                    'ten_san_pham' => trim(($variant->product->ten_san_pham ?? '') . ' - ' . ($variant->ten_don_vi ?: $variant->ten_bien_the)),
                    'message' => 'Sản phẩm đã hết hàng.',
                ];
            }

            return $this->mapVariantToScanResult($variant);
        }

        $unit = DonViQuyDoi::query()
            ->where('ma_vach', $barcode)
            ->whereNull('deleted_at')
            ->whereHas('variant', function ($query) {
                $query->where('trang_thai', 1)->whereNull('deleted_at');
            })
            ->whereHas('product', function ($query) {
                $query->where('trang_thai', 1)->whereNull('deleted_at');
            })
            ->with(['variant.product'])
            ->first();

        if (!$unit) {
            return null;
        }

        $variant = $unit->variant;
        if (!$variant) {
            return null;
        }

        $ratio = (float) ($unit->so_luong_san_pham_trong_don_vi ?: 1);
        if ($ratio <= 0) {
            $ratio = 1;
        }

        $availableQty = (int) floor((float) ($variant->so_luong_ton ?? 0) / $ratio);

        if ($availableQty <= 0) {
            return [
                'row_key' => 'unit_' . $unit->id,
                'id' => $variant->id,
                'unit_id' => $unit->id,
                'available_qty' => 0,
                'ten_san_pham' => trim(($variant->product->ten_san_pham ?? '') . ' - ' . $unit->ten_don_vi),
                'message' => 'Sản phẩm đã hết hàng.',
            ];
        }

        return $this->mapUnitToScanResult($unit, $availableQty, $ratio);
    }

    protected function mapVariantToScanResult(BienTheSanPham $variant): array
    {
        return [
            'row_key' => 'variant_' . $variant->id,
            'id' => $variant->id,
            'unit_id' => null,
            'barcode' => $variant->ma_vach,
            'product_id' => $variant->product_id,
            'ten_san_pham' => trim(($variant->product->ten_san_pham ?? '') . ' - ' . ($variant->ten_don_vi ?: $variant->ten_bien_the)),
            'ten_don_vi' => $variant->ten_don_vi ?: $variant->ten_bien_the,
            'gia_ban' => (float) ($variant->gia_ban ?? 0),
            'gia_von' => (float) ($variant->gia_von ?? 0),
            'hinh_anh' => $variant->hinh_anh,
            'available_qty' => max(0, (int) ($variant->so_luong_ton ?? 0)),
            'ty_le_quy_doi' => 1,
            'is_unit' => false,
        ];
    }

    protected function mapUnitToScanResult(DonViQuyDoi $unit, int $availableQty, float $ratio): array
    {
        $variant = $unit->variant;

        return [
            'row_key' => 'unit_' . $unit->id,
            'id' => $variant->id,
            'unit_id' => $unit->id,
            'barcode' => $unit->ma_vach,
            'product_id' => $unit->product_id,
            'ten_san_pham' => trim(($variant->product->ten_san_pham ?? '') . ' - ' . $unit->ten_don_vi),
            'ten_don_vi' => $unit->ten_don_vi,
            'gia_ban' => (float) ($unit->gia_ban_quy_doi ?? $variant->gia_ban ?? 0),
            'gia_von' => (float) ($unit->gia_von_quy_doi ?? $variant->gia_von ?? 0),
            'hinh_anh' => $unit->hinh_anh ?: $variant->hinh_anh,
            'available_qty' => $availableQty,
            'ty_le_quy_doi' => $ratio,
            'is_unit' => true,
        ];
    }
}
