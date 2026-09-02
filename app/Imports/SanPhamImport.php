<?php

namespace App\Imports;

use App\Models\BienTheSanPham;
use App\Models\DanhMucDonVi;
use App\Models\DanhMucSanPham;
use App\Models\DonViQuyDoi;
use App\Models\Product;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;

/**
 * Import danh mục sản phẩm từ Excel/CSV.
 *
 * Import này chỉ ghi Product, BienTheSanPham và DonViQuyDoi. Không tạo phiếu
 * nhập, lô hàng hoặc bất kỳ giao dịch kho nào.
 */
class SanPhamImport
{
    public const MAX_ROWS = 1000;

    protected array $errors = [];

    protected int $created = 0;

    protected int $updated = 0;

    protected int $createdVariants = 0;

    protected int $updatedVariants = 0;

    protected int $createdUnits = 0;

    protected int $updatedUnits = 0;

    protected int $skipped = 0;

    /**
     * Nhận mảng dòng do Excel::toArray() trả về. Dòng đầu tiên phải là header.
     */
    public function collection(Collection $rows): void
    {
        $this->resetSummary();

        $rows = array_values($rows->toArray());
        if (empty($rows)) {
            $this->errors[] = 'File không có dữ liệu.';

            return;
        }

        $headerMap = $this->buildHeaderMap($rows[0] ?? []);
        if ($headerMap === null) {
            return;
        }

        $dataRows = [];
        foreach (array_slice($rows, 1) as $offset => $row) {
            if (! is_array($row) || $this->isIgnorableRow($row, $headerMap)) {
                continue;
            }

            $line = $offset + 2;
            $normalized = $this->normalizeRow($row, $headerMap, $line);
            if ($normalized === null) {
                $this->skipped++;

                continue;
            }

            $dataRows[] = $normalized;
        }

        if (count($dataRows) > self::MAX_ROWS) {
            $this->errors[] = sprintf(
                'File có %d dòng dữ liệu, vượt quá giới hạn %d dòng mỗi lần import.',
                count($dataRows),
                self::MAX_ROWS
            );
            $this->skipped = count($dataRows);

            return;
        }

        if (empty($dataRows)) {
            if (empty($this->errors)) {
                $this->errors[] = 'File không có dòng sản phẩm hợp lệ.';
            }

            return;
        }

        $this->validateExistingCodes($dataRows);
        $groups = $this->prepareGroups($dataRows);
        $this->validateExistingProducts($groups);
        if (! empty($this->errors)) {
            $this->skipped = max($this->skipped, count($dataRows));

            return;
        }

        try {
            DB::transaction(function () use ($groups): void {
                $this->persistGroups($groups);
            });
        } catch (Throwable $e) {
            $this->errors[] = 'Không thể lưu dữ liệu: '.$e->getMessage();
            $this->created = 0;
            $this->updated = 0;
            $this->createdVariants = 0;
            $this->updatedVariants = 0;
            $this->createdUnits = 0;
            $this->updatedUnits = 0;
        }
    }

    public function getSummary(): array
    {
        return [
            'created' => $this->created,
            'updated' => $this->updated,
            'created_variants' => $this->createdVariants,
            'updated_variants' => $this->updatedVariants,
            'created_units' => $this->createdUnits,
            'updated_units' => $this->updatedUnits,
            'skipped' => $this->skipped,
            'errors' => $this->errors,
        ];
    }

    protected function resetSummary(): void
    {
        $this->errors = [];
        $this->created = 0;
        $this->updated = 0;
        $this->createdVariants = 0;
        $this->updatedVariants = 0;
        $this->createdUnits = 0;
        $this->updatedUnits = 0;
        $this->skipped = 0;
    }

    protected function buildHeaderMap(array $header): ?array
    {
        $aliases = [
            'stt' => 'stt',
            'ten_san_pham' => 'ten_san_pham',
            'san_pham' => 'ten_san_pham',
            'danh_muc' => 'danh_muc',
            'bien_the' => 'bien_the',
            'ten_bien_the' => 'bien_the',
            'ma_hang' => 'ma_hang',
            'ma_vach' => 'ma_vach',
            'don_vi' => 'don_vi',
            'ten_don_vi' => 'don_vi',
            'gia_von' => 'gia_von',
            'gia_ban' => 'gia_ban',
            'ton_kho' => 'ton_kho',
            'so_luong_ton' => 'ton_kho',
            'dinh_muc_tt' => 'dinh_muc_tt',
            'dinh_muc_toi_thieu' => 'dinh_muc_tt',
            'trang_thai' => 'trang_thai',
            'thuong_hieu' => 'thuong_hieu',
            'url_anh' => 'url_anh',
            'hinh_anh' => 'url_anh',
            'loai_dong' => 'loai_dong',
            'loai_row' => 'loai_dong',
            'ma_bien_the_cha' => 'ma_bien_the_cha',
            'ty_le_quy_doi' => 'ty_le_quy_doi',
            'ty_le' => 'ty_le_quy_doi',
        ];

        $map = [];
        foreach (array_values($header) as $index => $value) {
            $normalized = $this->normalizeHeader((string) $value);
            if ($normalized === '' || ! isset($aliases[$normalized])) {
                continue;
            }

            $key = $aliases[$normalized];
            if (isset($map[$key])) {
                $this->errors[] = "Header bị trùng cột '{$value}'.";

                return null;
            }
            $map[$key] = $index;
        }

        foreach (['ten_san_pham', 'danh_muc', 'gia_ban'] as $required) {
            if (! isset($map[$required])) {
                $this->errors[] = "Thiếu cột bắt buộc '{$required}'.";
            }
        }

        return empty($this->errors) ? $map : null;
    }

    protected function normalizeHeader(string $value): string
    {
        $value = Str::ascii(trim($value));
        $value = mb_strtolower($value);
        $value = preg_replace('/[^a-z0-9]+/', '_', $value) ?? '';

        return trim($value, '_');
    }

    protected function isIgnorableRow(array $row, array $headerMap): bool
    {
        $values = array_map(static fn ($value) => trim((string) $value), $row);
        if (empty(array_filter($values, static fn ($value) => $value !== ''))) {
            return true;
        }

        $name = trim((string) ($row[$headerMap['ten_san_pham']] ?? ''));
        $first = Str::ascii(mb_strtolower(trim((string) ($row[0] ?? ''))));
        $nonEmptyIndexes = array_keys(array_filter($values, static fn ($value) => $value !== ''));

        return $name === ''
            && count($nonEmptyIndexes) === 1
            && $nonEmptyIndexes[0] === 0
            && (str_starts_with($first, 'ghi chu')
                || str_starts_with($first, 'huong dan')
                || str_starts_with($first, '-')
                || str_starts_with($first, '#'));
    }

    protected function normalizeRow(array $row, array $headerMap, int $line): ?array
    {
        $get = static function (string $key) use ($row, $headerMap) {
            return isset($headerMap[$key]) ? ($row[$headerMap[$key]] ?? null) : null;
        };

        $name = trim((string) $get('ten_san_pham'));
        $category = trim((string) $get('danh_muc'));
        $variantName = trim((string) $get('bien_the'));
        $unitName = trim((string) $get('don_vi'));
        $sku = $this->normalizeCode($get('ma_hang'));
        $barcode = $this->normalizeCode($get('ma_vach'));
        $brand = trim((string) $get('thuong_hieu'));
        $image = trim((string) $get('url_anh'));
        $type = $this->normalizeType($get('loai_dong'));
        $parentCode = $this->normalizeCode($get('ma_bien_the_cha'));

        $valid = true;
        if ($type !== null && ! in_array($type, ['base', 'variant', 'unit'], true)) {
            $this->addRowError($line, 'Loại dòng chỉ nhận base, variant hoặc unit.');
            $valid = false;
        }
        if ($name === '') {
            $this->addRowError($line, 'Thiếu tên sản phẩm.');
            $valid = false;
        } elseif (mb_strlen($name) > 255) {
            $this->addRowError($line, 'Tên sản phẩm không được vượt quá 255 ký tự.');
            $valid = false;
        }

        if ($category === '') {
            $this->addRowError($line, 'Thiếu danh mục.');
            $valid = false;
        }

        $salePrice = $this->parseNumber($get('gia_ban'));
        if ($salePrice === null || $salePrice < 0) {
            $this->addRowError($line, 'Giá bán phải là số không âm.');
            $valid = false;
        }

        $minimum = $this->parseInteger($get('dinh_muc_tt'));
        if ($minimum === null || $minimum < 0) {
            if ($this->hasValue($get('dinh_muc_tt'))) {
                $this->addRowError($line, 'Định mức TT phải là số nguyên không âm.');
                $valid = false;
            }
            $minimum = 0;
        }

        $status = $this->parseStatus($get('trang_thai'));
        if ($status === null) {
            $this->addRowError($line, 'Trạng thái chỉ nhận "Hoạt động" hoặc "Tạm ngưng".');
            $valid = false;
            $status = true;
        }

        if ($sku !== null && mb_strlen($sku) > 255) {
            $this->addRowError($line, 'Mã hàng không được vượt quá 255 ký tự.');
            $valid = false;
        }
        if ($barcode !== null && mb_strlen($barcode) > 255) {
            $this->addRowError($line, 'Mã vạch không được vượt quá 255 ký tự.');
            $valid = false;
        }

        if ($image !== '') {
            $parts = parse_url($image);
            $scheme = strtolower((string) ($parts['scheme'] ?? ''));
            if (! filter_var($image, FILTER_VALIDATE_URL) || ! in_array($scheme, ['http', 'https'], true)) {
                $this->addRowError($line, 'URL ảnh phải bắt đầu bằng http:// hoặc https://.');
                $valid = false;
            } elseif (mb_strlen($image) > 255) {
                $this->addRowError($line, 'URL ảnh không được vượt quá 255 ký tự.');
                $valid = false;
            }
        }

        $ratio = $this->parseNumber($get('ty_le_quy_doi'));
        if ($this->hasValue($get('ty_le_quy_doi')) && ($ratio === null || $ratio <= 1)) {
            $this->addRowError($line, 'Tỷ lệ quy đổi phải là số lớn hơn 1.');
            $valid = false;
        }

        $inferredRatio = $this->extractRatio($variantName.' '.$unitName);
        $looksLikePackagingUnit = preg_match(
            '/\b(thung|hop|loc|bao|tui|bich|ket|set|box|pack)\b/i',
            Str::ascii($variantName.' '.$unitName)
        ) === 1;
        $isUnit = $type === 'unit'
            || $parentCode !== null
            || ($ratio !== null && $ratio > 1)
            || ($type === null && $inferredRatio !== null && $looksLikePackagingUnit);
        if (in_array($type, ['base', 'variant'], true) && $isUnit) {
            $this->addRowError($line, "Loại dòng {$type} không được có tỷ lệ quy đổi.");
            $valid = false;
        }

        if ($isUnit && $ratio === null) {
            $ratio = $inferredRatio;
        }
        if ($isUnit && ($ratio === null || $ratio <= 1)) {
            $this->addRowError($line, 'Dòng đơn vị quy đổi cần tỷ lệ lớn hơn 1.');
            $valid = false;
        }

        if (! $isUnit && $unitName === '') {
            $unitName = 'Cái';
        }
        if ($isUnit && $unitName === '') {
            $this->addRowError($line, 'Dòng đơn vị quy đổi cần có tên đơn vị.');
            $valid = false;
        }

        if (! $valid) {
            return null;
        }

        return [
            'line' => $line,
            'name' => $name,
            'category' => $category,
            'variant_name' => $variantName,
            'unit_name' => $unitName,
            'sku' => $sku,
            'barcode' => $barcode,
            'brand' => $brand !== '' ? $brand : null,
            'image' => $image !== '' ? $image : null,
            'sale_price' => $salePrice,
            'minimum' => $minimum,
            'status' => $status,
            'type' => $type,
            'parent_code' => $parentCode,
            'ratio' => $isUnit ? $ratio : null,
            'is_unit' => $isUnit,
        ];
    }

    protected function prepareGroups(array $rows): array
    {
        $groups = [];
        $seenCodes = [];

        foreach ($rows as $row) {
            $productKey = $this->key($row['name']);
            $groups[$productKey]['product'] ??= $row;
            $groups[$productKey]['rows'][] = $row;

            foreach (['sku', 'barcode'] as $codeField) {
                $code = $row[$codeField];
                if ($code === null) {
                    continue;
                }
                $codeKey = $codeField.':'.$this->key($code);
                if (isset($seenCodes[$codeKey])) {
                    $this->addRowError($row['line'], "{$codeField} '{$code}' bị trùng với dòng {$seenCodes[$codeKey]}.");
                } else {
                    $seenCodes[$codeKey] = $row['line'];
                }
            }
        }

        foreach ($groups as &$group) {
            $first = $group['product'];
            foreach ($group['rows'] as $row) {
                if ($this->key($row['category']) !== $this->key($first['category'])) {
                    $this->addRowError($row['line'], 'Các dòng cùng tên sản phẩm phải dùng cùng danh mục.');
                }
                if ($first['brand'] === null && $row['brand'] !== null) {
                    $first['brand'] = $row['brand'];
                }
                if ($row['brand'] !== null && $first['brand'] !== null && $this->key($row['brand']) !== $this->key($first['brand'])) {
                    $this->addRowError($row['line'], 'Các dòng cùng sản phẩm không được khác thương hiệu.');
                }
                if ($row['status'] !== $first['status']) {
                    $this->addRowError($row['line'], 'Các dòng cùng sản phẩm không được khác trạng thái.');
                }
            }
            $group['product'] = $first;

            $variants = [];
            $units = [];
            foreach ($group['rows'] as $row) {
                if ($row['is_unit']) {
                    $units[] = $row;

                    continue;
                }

                $variantKey = $row['sku'] ?? $row['barcode'] ?? $this->key($row['variant_name'].'|'.$row['unit_name']);
                if ($variantKey === '') {
                    $variantKey = 'line:'.$row['line'];
                }
                if (isset($variants[$variantKey])) {
                    $this->addRowError($row['line'], 'Biến thể bị trùng trong cùng sản phẩm; hãy dùng mã hàng/mã vạch hoặc gộp thành một dòng.');

                    continue;
                }
                $row['variant_key'] = $variantKey;
                $variants[$variantKey] = $row;
            }

            if (empty($variants) && ! empty($units)) {
                $this->addRowError($units[0]['line'], 'Sản phẩm phải có ít nhất một dòng biến thể/đơn vị cơ bản.');
            }

            $unitPlans = [];
            foreach ($units as $unit) {
                $parentKey = null;
                if ($unit['parent_code'] !== null) {
                    foreach ($variants as $variantKey => $variant) {
                        if (in_array($unit['parent_code'], [$variant['sku'], $variant['barcode']], true)) {
                            $parentKey = $variantKey;
                            break;
                        }
                    }
                    if ($parentKey === null) {
                        $this->addRowError($unit['line'], "Không tìm thấy biến thể cha '{$unit['parent_code']}'.");
                    }
                } elseif (count($variants) === 1) {
                    $parentKey = array_key_first($variants);
                } else {
                    $candidate = $this->key($unit['variant_name']);
                    foreach ($variants as $variantKey => $variant) {
                        if ($candidate !== '' && $candidate === $this->key($variant['variant_name'])) {
                            $parentKey = $variantKey;
                            break;
                        }
                    }
                    if ($parentKey === null) {
                        $this->addRowError($unit['line'], 'Đơn vị quy đổi chưa xác định được biến thể cha; hãy điền Mã biến thể cha.');
                    }
                }

                if ($parentKey !== null) {
                    $unit['parent_key'] = $parentKey;
                    $unitPlans[] = $unit;
                }
            }

            $group['variants'] = array_values($variants);
            $group['units'] = $unitPlans;
            unset($group['rows']);
        }
        unset($group);

        return array_values($groups);
    }

    /**
     * Mã hàng/mã vạch có thể thuộc variant hoặc unit. Không cho một dòng
     * chuyển loại hoặc chiếm mã đang thuộc sản phẩm khác.
     */
    protected function validateExistingCodes(array $rows): void
    {
        $codesByField = [
            'ma_hang' => array_values(array_unique(array_filter(array_column($rows, 'sku')))),
            'ma_vach' => array_values(array_unique(array_filter(array_column($rows, 'barcode')))),
        ];

        foreach ($codesByField as $field => $codes) {
            if (empty($codes)) {
                continue;
            }

            $rowField = $field === 'ma_hang' ? 'sku' : 'barcode';

            $variants = BienTheSanPham::with('product')
                ->whereIn($field, $codes)
                ->get()
                ->mapWithKeys(fn (BienTheSanPham $variant) => [
                    $this->key((string) $variant->{$field}) => [
                        'type' => 'variant',
                        'product' => $variant->product?->ten_san_pham,
                    ],
                ]);
            $units = DonViQuyDoi::with(['product', 'variant.product'])
                ->whereIn($field, $codes)
                ->get()
                ->mapWithKeys(fn (DonViQuyDoi $unit) => [
                    $this->key((string) $unit->{$field}) => [
                        'type' => 'unit',
                        'product' => $unit->product?->ten_san_pham ?? $unit->variant?->product?->ten_san_pham,
                    ],
                ]);

            foreach ($rows as $row) {
                $code = $row[$rowField];
                if ($code === null) {
                    continue;
                }

                $key = $this->key($code);
                $matches = array_filter([$variants->get($key), $units->get($key)]);
                if (count($matches) > 1) {
                    $this->addRowError($row['line'], "{$field} '{$code}' đang bị trùng trong hệ thống.");

                    continue;
                }
                $existing = $matches ? array_values($matches)[0] : null;
                if (! $existing) {
                    continue;
                }

                if ($existing['product'] !== null && $this->key($existing['product']) !== $this->key($row['name'])) {
                    $this->addRowError($row['line'], "{$field} '{$code}' đã thuộc sản phẩm '{$existing['product']}'.");
                } elseif (($row['is_unit'] && $existing['type'] !== 'unit') || (! $row['is_unit'] && $existing['type'] !== 'variant')) {
                    $this->addRowError($row['line'], "{$field} '{$code}' không cùng loại dòng với dữ liệu hiện có.");
                }
            }
        }
    }

    protected function validateExistingProducts(array $groups): void
    {
        $existingProducts = Product::query()
            ->whereNull('deleted_at')
            ->get(['ten_san_pham'])
            ->pluck('ten_san_pham')
            ->filter()
            ->map(fn ($name) => $this->key((string) $name))
            ->flip();

        foreach ($groups as $group) {
            $productName = (string) ($group['product']['name'] ?? '');
            if ($productName === '') {
                continue;
            }

            if ($existingProducts->has($this->key($productName))) {
                $this->addRowError(
                    (int) ($group['product']['line'] ?? 0),
                    "Sản phẩm '{$productName}' đã tồn tại trong hệ thống, không thể import trùng."
                );
            }
        }
    }

    protected function persistGroups(array $groups): void
    {
        $categoryCache = [];

        foreach ($groups as $group) {
            $productRow = $group['product'];
            $categoryKey = $this->key($productRow['category']);
            if (! isset($categoryCache[$categoryKey])) {
                $categoryCache[$categoryKey] = DanhMucSanPham::query()
                    ->whereRaw('LOWER(ten_danh_muc) = ?', [mb_strtolower($productRow['category'])])
                    ->whereNull('deleted_at')
                    ->first();
                if (! $categoryCache[$categoryKey]) {
                    $categoryCache[$categoryKey] = DanhMucSanPham::create([
                        'ten_danh_muc' => $productRow['category'],
                        'trang_thai' => true,
                    ]);
                }
            }

            $product = Product::query()
                ->whereRaw('LOWER(ten_san_pham) = ?', [mb_strtolower($productRow['name'])])
                ->whereNull('deleted_at')
                ->first();

            if (! $product) {
                $product = Product::create([
                    'id_danh_muc' => $categoryCache[$categoryKey]->id,
                    'ten_san_pham' => $productRow['name'],
                    'thuong_hieu' => $productRow['brand'],
                    'trang_thai' => $productRow['status'],
                ]);
                $this->created++;
            } else {
                throw new \RuntimeException("Sản phẩm '{$productRow['name']}' đã tồn tại trong hệ thống, không thể import trùng.");
            }

            $variantMap = [];
            foreach ($group['variants'] as $row) {
                $variant = $this->findVariant($product, $row);
                $attributes = [
                    'product_id' => $product->id,
                    'ten_bien_the' => $row['variant_name'] !== '' ? $row['variant_name'] : null,
                    'ten_don_vi' => $row['unit_name'] ?: 'Cái',
                    'la_don_vi' => $row['variant_name'] === '',
                    'gia_von' => 0,
                    'gia_ban' => $row['sale_price'],
                    'dinh_muc_toi_thieu' => $row['minimum'],
                    'trang_thai' => $row['status'],
                    'hinh_anh' => $row['image'] ?? $variant?->hinh_anh,
                ];

                if (! $variant) {
                    $attributes['ma_hang'] = $row['sku'] ?? $this->generateUniqueSku();
                    $attributes['ma_vach'] = $row['barcode'] ?? $this->generateUniqueBarcode();
                    $attributes['so_luong_ton'] = 0;
                    $variant = BienTheSanPham::create($attributes);
                    $this->createdVariants++;
                } else {
                    $variant->update(array_merge($attributes, [
                        'ma_hang' => $row['sku'] ?? $variant->ma_hang,
                        'ma_vach' => $row['barcode'] ?? $variant->ma_vach,
                    ]));
                    $this->updatedVariants++;
                }

                $variantMap[$row['variant_key']] = $variant;
            }

            foreach ($group['units'] as $row) {
                $variant = $variantMap[$row['parent_key']] ?? null;
                if (! $variant) {
                    continue;
                }

                $unit = $this->findUnit($variant, $row);
                $standardUnit = DanhMucDonVi::query()
                    ->whereRaw('LOWER(ten_don_vi) = ?', [mb_strtolower($row['unit_name'])])
                    ->where('so_luong_san_pham_trong_don_vi', $row['ratio'])
                    ->whereNull('deleted_at')
                    ->first();
                $attributes = [
                    'variant_id' => $variant->id,
                    'product_id' => $product->id,
                    'don_vi_chuan_id' => $standardUnit?->id,
                    'ten_don_vi' => $row['unit_name'],
                    'so_luong_san_pham_trong_don_vi' => $row['ratio'],
                    'ty_le_quy_doi' => $row['ratio'],
                    'gia_von_quy_doi' => 0,
                    'gia_ban_quy_doi' => $row['sale_price'],
                    'hinh_anh' => $row['image'] ?? $unit?->hinh_anh,
                    'la_don_vi_mac_dinh' => false,
                ];

                if (! $unit) {
                    $attributes['ma_hang'] = $row['sku'] ?? $this->generateUniqueSku();
                    $attributes['ma_vach'] = $row['barcode'] ?? $this->generateUniqueBarcode();
                    DonViQuyDoi::create($attributes);
                    $this->createdUnits++;
                } else {
                    $unit->update(array_merge($attributes, [
                        'ma_hang' => $row['sku'] ?? $unit->ma_hang,
                        'ma_vach' => $row['barcode'] ?? $unit->ma_vach,
                    ]));
                    $this->updatedUnits++;
                }
            }
        }
    }

    protected function findVariant(Product $product, array $row): ?BienTheSanPham
    {
        $query = BienTheSanPham::query()->where('product_id', $product->id)->whereNull('deleted_at');
        if ($row['sku'] !== null) {
            $variant = (clone $query)->where('ma_hang', $row['sku'])->first();
            if ($variant) {
                return $variant;
            }
        }
        if ($row['barcode'] !== null) {
            $variant = (clone $query)->where('ma_vach', $row['barcode'])->first();
            if ($variant) {
                return $variant;
            }
        }

        return $query
            ->whereRaw("LOWER(COALESCE(ten_bien_the, '')) = ?", [mb_strtolower($row['variant_name'])])
            ->whereRaw("LOWER(COALESCE(ten_don_vi, '')) = ?", [mb_strtolower($row['unit_name'])])
            ->first();
    }

    protected function findUnit(BienTheSanPham $variant, array $row): ?DonViQuyDoi
    {
        $query = DonViQuyDoi::query()->where('variant_id', $variant->id)->whereNull('deleted_at');
        if ($row['sku'] !== null) {
            $unit = (clone $query)->where('ma_hang', $row['sku'])->first();
            if ($unit) {
                return $unit;
            }
        }
        if ($row['barcode'] !== null) {
            $unit = (clone $query)->where('ma_vach', $row['barcode'])->first();
            if ($unit) {
                return $unit;
            }
        }

        return $query
            ->whereRaw('LOWER(ten_don_vi) = ?', [mb_strtolower($row['unit_name'])])
            ->where('so_luong_san_pham_trong_don_vi', $row['ratio'])
            ->first();
    }

    protected function normalizeCode($value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    protected function normalizeType($value): ?string
    {
        $value = Str::ascii(mb_strtolower(trim((string) $value)));

        return match ($value) {
            'base', 'co ban', 'don vi co ban' => 'base',
            'variant', 'bien the' => 'variant',
            'unit', 'quy doi', 'don vi', 'don vi quy doi' => 'unit',
            '' => null,
            default => $value,
        };
    }

    protected function parseStatus($value): ?bool
    {
        $value = Str::ascii(mb_strtolower(trim((string) $value)));
        if ($value === '') {
            return true;
        }
        if (in_array($value, ['hoat dong', 'active', '1', 'true'], true)) {
            return true;
        }
        if (in_array($value, ['tam ngung', 'inactive', '0', 'false'], true)) {
            return false;
        }

        return null;
    }

    protected function parseInteger($value): ?int
    {
        if (! $this->hasValue($value)) {
            return null;
        }
        $number = $this->parseNumber($value);
        if ($number === null || floor($number) !== $number) {
            return null;
        }

        return (int) $number;
    }

    protected function parseNumber($value): ?float
    {
        if (! $this->hasValue($value)) {
            return null;
        }
        if (is_int($value) || is_float($value)) {
            return is_finite((float) $value) ? (float) $value : null;
        }

        $text = trim((string) $value);
        $text = str_replace(['₫', 'đ', 'Đ', "\u{00A0}"], '', $text);
        $text = preg_replace('/\s+/u', '', $text) ?? '';
        if ($text === '' || ! preg_match('/^\+?\d[\d.,]*$/', $text)) {
            return null;
        }

        $hasComma = str_contains($text, ',');
        $hasDot = str_contains($text, '.');
        if ($hasComma && $hasDot) {
            $lastComma = strrpos($text, ',');
            $lastDot = strrpos($text, '.');
            $decimalSeparator = $lastComma > $lastDot ? ',' : '.';
            $after = substr($text, max($lastComma, $lastDot) + 1);
            if (strlen($after) <= 2) {
                $text = str_replace($decimalSeparator === ',' ? '.' : ',', '', $text);
                $text = str_replace($decimalSeparator, '.', $text);
            } else {
                $text = str_replace([',', '.'], '', $text);
            }
        } elseif ($hasComma || $hasDot) {
            $separator = $hasComma ? ',' : '.';
            if (preg_match('/^\d{1,3}(?:'.preg_quote($separator, '/').'\d{3})+$/', $text)) {
                $text = str_replace($separator, '', $text);
            } else {
                $text = str_replace($separator, '.', $text);
            }
        }

        return is_numeric($text) ? (float) $text : null;
    }

    protected function extractRatio(string $value): ?float
    {
        if (! preg_match('/\b(\d+(?:[.,]\d+)?)\b/u', $value, $matches)) {
            return null;
        }
        $ratio = $this->parseNumber($matches[1]);

        return $ratio !== null && $ratio > 1 ? $ratio : null;
    }

    protected function hasValue($value): bool
    {
        return $value !== null && trim((string) $value) !== '';
    }

    protected function key(string $value): string
    {
        return Str::ascii(mb_strtolower(trim($value)));
    }

    protected function addRowError(int $line, string $message): void
    {
        $this->errors[] = "Dòng {$line}: {$message}";
    }

    protected function generateUniqueSku(): string
    {
        do {
            $code = 'MH'.strtoupper(Str::random(8));
        } while (BienTheSanPham::where('ma_hang', $code)->exists() || DonViQuyDoi::where('ma_hang', $code)->exists());

        return $code;
    }

    protected function generateUniqueBarcode(): string
    {
        do {
            $code = 'BV'.strtoupper(Str::random(10));
        } while (BienTheSanPham::where('ma_vach', $code)->exists() || DonViQuyDoi::where('ma_vach', $code)->exists());

        return $code;
    }
}
