<?php

namespace App\Imports;

use App\Models\BienTheSanPham;
use App\Models\DanhMucSanPham;
use App\Models\DonViQuyDoi;
use App\Models\Product;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SanPhamImport
{
    protected $errors = [];
    protected $created = 0;
    protected $updated = 0;
    protected $skipped = 0;

    public function collection(Collection $rows)
    {
        $rows = $rows->skip(1); // skip header
        $rows = $rows->filter(fn($row) => !empty($row[0]) && trim((string)$row[0]) !== '');

        if ($rows->isEmpty()) {
            return;
        }

        $existingBarcodes = BienTheSanPham::pluck('ma_vach')->filter()->map(fn($v) => (string)$v)->toArray();
        $existingMaHang = BienTheSanPham::pluck('ma_hang')->filter()->map(fn($v) => (string)$v)->toArray();
        $donViQuyDoiMaHangs = DonViQuyDoi::pluck('ma_hang')->filter()->map(fn($v) => (string)$v)->toArray();
        $allMaHang = array_unique(array_merge($existingMaHang, $donViQuyDoiMaHangs));

        $productsCache = DanhMucSanPham::all()->pluck('id', 'ten_danh_muc')->toArray();

        DB::transaction(function () use ($rows, $existingBarcodes, $allMaHang, $productsCache) {
            foreach ($rows as $idx => $row) {
                $lineNumber = $idx + 2;
                $tenSanPham = trim((string)($row[0] ?? ''));
                $tenDanhMuc = trim((string)($row[1] ?? ''));
                $tenBienThe = trim((string)($row[2] ?? ''));
                $maHang = trim((string)($row[3] ?? ''));
                $maVach = trim((string)($row[4] ?? ''));
                $giaVon = (float)($row[5] ?? 0);
                $giaBan = (float)($row[6] ?? 0);
                $soLuongTon = (int)($row[7] ?? 0);
                $dinhMucToiThieu = (int)($row[8] ?? 0);
                $trangThai = (int)($row[9] ?? 1) === 1;

                if ($tenSanPham === '') {
                    $this->errors[] = "Dòng {$lineNumber}: Thiếu tên sản phẩm.";
                    $this->skipped++;
                    continue;
                }

                // Validate barcodes/mahang unique
                if ($maVach !== '' && in_array($maVach, $existingBarcodes, true)) {
                    $this->errors[] = "Dòng {$lineNumber}: Mã vạch '{$maVach}' đã tồn tại.";
                    $this->skipped++;
                    continue;
                }
                if ($maHang !== '' && in_array($maHang, $allMaHang, true)) {
                    $this->errors[] = "Dòng {$lineNumber}: Mã hàng '{$maHang}' đã tồn tại.";
                    $this->skipped++;
                    continue;
                }

                // Find or create product by name
                $product = Product::where('ten_san_pham', $tenSanPham)->whereNull('deleted_at')->first();
                if (!$product) {
                    $idDanhMuc = null;
                    if ($tenDanhMuc !== '' && isset($productsCache[$tenDanhMuc])) {
                        $idDanhMuc = $productsCache[$tenDanhMuc];
                    } elseif ($tenDanhMuc !== '') {
                        $dm = DanhMucSanPham::create(['ten_danh_muc' => $tenDanhMuc]);
                        $idDanhMuc = $dm->id;
                        $productsCache[$tenDanhMuc] = $dm->id;
                    }

                    $product = Product::create([
                        'id_danh_muc' => $idDanhMuc,
                        'ten_san_pham' => $tenSanPham,
                        'trang_thai' => true,
                    ]);
                    $this->created++;
                } else {
                    $this->updated++;
                }

                // Generate ma_hang if empty
                if ($maHang === '') {
                    $maHang = 'MH' . strtoupper(Str::random(6));
                    while (BienTheSanPham::where('ma_hang', $maHang)->exists() || DonViQuyDoi::where('ma_hang', $maHang)->exists()) {
                        $maHang = 'MH' . strtoupper(Str::random(6));
                    }
                }

                // Generate ma_vach if empty
                if ($maVach === '') {
                    $maVach = 'BV' . strtoupper(Str::random(8));
                    while (BienTheSanPham::where('ma_vach', $maVach)->exists()) {
                        $maVach = 'BV' . strtoupper(Str::random(8));
                    }
                }

                // Create variant
                $variant = BienTheSanPham::create([
                    'product_id' => $product->id,
                    'ten_bien_the' => $tenBienThe !== '' ? $tenBienThe : null,
                    'ma_hang' => $maHang,
                    'ma_vach' => $maVach,
                    'gia_von' => $giaVon,
                    'gia_ban' => $giaBan,
                    'so_luong_ton' => $soLuongTon,
                    'dinh_muc_toi_thieu' => $dinhMucToiThieu,
                    'trang_thai' => $trangThai,
                ]);

                $existingBarcodes[] = $maVach;
                $allMaHang[] = $maHang;
            }
        });
    }

    public function getSummary(): array
    {
        return [
            'created' => $this->created,
            'updated' => $this->updated,
            'skipped' => $this->skipped,
            'errors' => $this->errors,
        ];
    }
}
