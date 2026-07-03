<?php

namespace App\Http\Controllers\admin\SanPham;

use App\Http\Controllers\Controller;
use App\Http\Requests\SanPham\StoreSanPhamRequest;
use App\Http\Requests\SanPham\UpdateSanPhamRequest;
use App\Http\Requests\SanPham\ImportSanPhamRequest;
use App\Models\DanhMucSanPham;
use App\Models\Product;
use App\Models\BienTheSanPham;
use App\Models\DonViQuyDoi;
use App\Models\ThuocTinhSanPham;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class SanPhamController extends Controller
{
    public function index(Request $request): View
    {
        $keyword = $request->input('keyword');
        $danhMucId = $request->input('danh_muc');
        $trangThai = $request->filled('trang_thai') ? $request->boolean('trang_thai') : null;

        $danhMucs = DanhMucSanPham::query()->orderBy('ten_danh_muc')->get();
        $thuocTinhChas = ThuocTinhSanPham::whereNull('thuoc_tinh_cha_id')
            ->where('trang_thai', true)
            ->orderBy('ten_thuoc_tinh')
            ->get();

        // Query ở mức PRODUCT (không phải variant) để mỗi sản phẩm hiển thị 1 dòng duy nhất.
        // Variants + units được eager-load để hiển thị chi tiết khi click vào sản phẩm.
        $query = Product::with(['danhMuc', 'variants.units'])
            ->whereNull('deleted_at')
            ->when($keyword, fn($q) => $q
                ->whereRaw('LOWER(ten_san_pham) LIKE ?', ["%".mb_strtolower($keyword)."%"])
                ->orWhereHas('variants', fn($inner) => $inner
                    ->whereRaw('LOWER(ten_bien_the) LIKE ?', ["%".mb_strtolower($keyword)."%"])
                    ->orWhereRaw('LOWER(ma_vach) LIKE ?', ["%".mb_strtolower($keyword)."%"])
                    ->orWhereRaw('LOWER(ma_hang) LIKE ?', ["%".mb_strtolower($keyword)."%"])))
            ->when($danhMucId, fn($q) => $q->where('id_danh_muc', $danhMucId))
            ->when(!is_null($trangThai), fn($q) => $q
                ->where(function ($inner) use ($trangThai) {
                    $inner->where('trang_thai', $trangThai)
                        ->orWhereHas('variants', fn($v) => $v->where('trang_thai', $trangThai));
                }))
            ->orderByDesc('id');

        $paginated = $query->paginate(10)->withQueryString();
        $products = $paginated->getCollection();

        // Flat list (mỗi product = 1 row, variants lồng bên trong)
        $flatItems = $this->buildFlatList($products);

        return view('admin_xem_truoc.san-pham.index', [
            'variants' => $paginated,
            'flatItems' => $flatItems,
            'danhMucs' => $danhMucs,
            'thuocTinhChas' => $thuocTinhChas,
            'keyword' => $keyword,
            'danhMucId' => $danhMucId,
            'trangThai' => $request->input('trang_thai'),
        ]);
    }

    protected function buildFlatList($products)
    {
        $flat = [];
        foreach ($products as $product) {
            // Lấy variant đầu tiên (nếu có) để lấy ảnh đại diện
            $firstVariant = $product->variants->first();
            $representativeImage = $firstVariant?->hinh_anh;

            $totalTonKho = 0;
            $minGiaBan = null;
            $maxGiaBan = null;
            $variantsPayload = [];

            foreach ($product->variants as $variant) {
                $totalTonKho += (int)($variant->so_luong_ton ?? 0);
                $giaBan = (float)($variant->gia_ban ?? 0);
                if ($minGiaBan === null || $giaBan < $minGiaBan) {
                    $minGiaBan = $giaBan;
                }
                if ($maxGiaBan === null || $giaBan > $maxGiaBan) {
                    $maxGiaBan = $giaBan;
                }

                // Thuộc tính của variant
                $thuocTinhLabels = [];
                if (method_exists($variant, 'thuocTinhs')) {
                    $thuocTinhLabels = $variant->thuocTinhs()->pluck('ten_thuoc_tinh')->toArray();
                }

                $unitsPayload = $variant->units->map(fn($u) => [
                    'id' => $u->id,
                    'ten_don_vi' => $u->ten_don_vi,
                    'ty_le_quy_doi' => (int)$u->ty_le_quy_doi,
                    'gia_von_quy_doi' => (float)$u->gia_von_quy_doi,
                    'gia_ban_quy_doi' => (float)$u->gia_ban_quy_doi,
                    'ma_vach' => $u->ma_vach,
                    'ma_hang' => $u->ma_hang,
                    'hinh_anh' => $u->hinh_anh,
                    'la_don_vi_mac_dinh' => (bool)$u->la_don_vi_mac_dinh,
                ])->toArray();

                $variantsPayload[] = [
                    'id' => $variant->id,
                    'ten_bien_the' => $variant->ten_bien_the,
                    'ma_hang' => $variant->ma_hang,
                    'ma_vach' => $variant->ma_vach,
                    'gia_von' => (float)$variant->gia_von,
                    'gia_ban' => $giaBan,
                    'so_luong_ton' => (int)$variant->so_luong_ton,
                    'dinh_muc_toi_thieu' => (int)$variant->dinh_muc_toi_thieu,
                    'trang_thai' => (bool)$variant->trang_thai,
                    'hinh_anh' => $variant->hinh_anh,
                    'thuoc_tinh_labels' => $thuocTinhLabels,
                    'units' => $unitsPayload,
                ];
            }

            $flat[] = [
                'type' => 'product',
                'product_id' => $product->id,
                'ten_san_pham' => $product->ten_san_pham ?? '',
                'danh_muc' => $product->danhMuc->ten_danh_muc ?? '',
                'thuong_hieu' => $product->thuong_hieu,
                'trang_thai' => (bool)$product->trang_thai,
                'hinh_anh' => $representativeImage,
                'so_bien_the' => $product->variants->count(),
                'total_ton_kho' => $totalTonKho,
                'min_gia_ban' => $minGiaBan,
                'max_gia_ban' => $maxGiaBan,
                'variants' => $variantsPayload,
            ];
        }
        return $flat;
    }

    public function store(StoreSanPhamRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $imagePath = null;
        if ($request->hasFile('hinh_anh')) {
            $imagePath = $this->uploadImage($request->file('hinh_anh'));
        }

        // Validate ma_vach khong trung
        $barcodes = array_filter(array_column($data['bien_the'] ?? [], 'ma_vach'));
        if (!empty($barcodes) && BienTheSanPham::whereIn('ma_vach', $barcodes)->exists()) {
            return redirect()->back()->withInput()->withErrors(['bien_the' => 'Một hoặc nhiều mã vạch biến thể đã tồn tại.']);
        }

        return DB::transaction(function () use ($data, $request, $imagePath) {
            // 1. Tạo Product (thông tin chung)
            $product = Product::create([
                'id_danh_muc' => $data['id_danh_muc'],
                'ten_san_pham' => $data['ten_san_pham'],
                'thuong_hieu' => $data['thuong_hieu'] ?? null,
                'mo_ta' => $data['mo_ta'] ?? null,
                'trang_thai' => $data['trang_thai'] ?? true,
            ]);

            $bienThe = $data['bien_the'] ?? [];

            if (empty($bienThe)) {
                // 2a. Không có biến thể → tạo 1 variant mặc định
                $variantImages = [];
                if ($request->hasFile('bien_the')) {
                    foreach ($request->file('bien_the') as $idx => $files) {
                        if (isset($files['hinh_anh']) && $files['hinh_anh']) {
                            $variantImages[$idx] = $this->uploadImage($files['hinh_anh']);
                        }
                    }
                }

                BienTheSanPham::create([
                    'product_id' => $product->id,
                    'ten_bien_the' => null,
                    'ma_hang' => $this->generateUniqueMaHang(),
                    'ma_vach' => $this->generateUniqueMaVach(),
                    'gia_von' => 0,
                    'gia_ban' => 0,
                    'so_luong_ton' => 0,
                    'hinh_anh' => $imagePath,
                    'thuoc_tinh_ids' => null,
                    'trang_thai' => $data['trang_thai'] ?? true,
                ]);
            } else {
                // 2b. Có biến thể → upload ảnh biến thể
                $variantImages = [];
                if ($request->hasFile('bien_the')) {
                    foreach ($request->file('bien_the') as $idx => $files) {
                        if (isset($files['hinh_anh']) && $files['hinh_anh']) {
                            $variantImages[$idx] = $this->uploadImage($files['hinh_anh']);
                        }
                    }
                }

                $createdVariants = [];
                foreach ($bienThe as $idx => $variant) {
                    $variantImage = $variantImages[$idx] ?? $imagePath;

                    // Parse thuoc_tinh_ids
                    $thuocTinhIds = null;
                    if (!empty($variant['thuoc_tinh_ids'])) {
                        $ids = array_map('intval', explode(',', $variant['thuoc_tinh_ids']));
                        $thuocTinhIds = array_filter($ids);
                    }

                    $createdVariants[] = BienTheSanPham::create([
                        'product_id' => $product->id,
                        'ten_bien_the' => $variant['ten_bien_the'] ?? $variant['ten_day_du'] ?? null,
                        'ma_hang' => $variant['ma_hang'] ?? $this->generateUniqueMaHang(),
                        'ma_vach' => !empty($variant['ma_vach']) ? $variant['ma_vach'] : $this->generateUniqueMaVach(),
                        'gia_von' => $variant['gia_von'] ?? 0,
                        'gia_ban' => $variant['gia_ban'] ?? 0,
                        'so_luong_ton' => $variant['so_luong_ton'] ?? 0,
                        'dinh_muc_toi_thieu' => $variant['dinh_muc_toi_thieu'] ?? 0,
                        'hinh_anh' => $variantImage,
                        'thuoc_tinh_ids' => $thuocTinhIds,
                        'trang_thai' => $data['trang_thai'] ?? true,
                    ]);
                }

                // Gom units từ các variant vào danh sách duy nhất và dedupe
                // Vì form Blade mặc định gửi cùng bộ units cho mọi variant,
                // chỉ lấy bộ units đầu tiên và gắn vào variant đầu tiên.
                $firstVariantUnits = $bienThe[0]['units'] ?? [];
                $seenUnitKeys = [];
                $uniqueUnits = [];
                foreach ($firstVariantUnits as $unit) {
                    $tenDonVi = trim($unit['ten_don_vi'] ?? '');
                    $tyLe = (int)($unit['ty_le_quy_doi'] ?? 1);
                    $key = mb_strtolower($tenDonVi) . '|' . $tyLe;
                    if ($tenDonVi === '' || isset($seenUnitKeys[$key])) {
                        continue;
                    }
                    $seenUnitKeys[$key] = true;
                    $uniqueUnits[] = $unit;
                }

                if (!empty($createdVariants) && !empty($uniqueUnits)) {
                    $primaryVariant = $createdVariants[0];
                    $primaryImage = $primaryVariant->hinh_anh;
                    foreach ($uniqueUnits as $unit) {
                        DonViQuyDoi::create([
                            'variant_id' => $primaryVariant->id,
                            'ten_don_vi' => $unit['ten_don_vi'],
                            'ty_le_quy_doi' => (int)($unit['ty_le_quy_doi'] ?? 1),
                            'ma_hang' => $unit['ma_hang'] ?? $this->generateUniqueMaHang(),
                            'ma_vach' => $unit['ma_vach'] ?? null,
                            'gia_von_quy_doi' => $unit['gia_von_quy_doi'] ?? 0,
                            'gia_ban_quy_doi' => $unit['gia_ban_quy_doi'] ?? 0,
                            'gia_ban_si' => $unit['gia_ban_si'] ?? null,
                            'hinh_anh' => $primaryImage,
                            'la_don_vi_mac_dinh' => ((int)($unit['ty_le_quy_doi'] ?? 1) === 1),
                        ]);
                    }
                }
            }

            return redirect()->back()->with('success', 'Đã thêm sản phẩm mới.');
        });
    }

    public function edit($id): View
    {
        $product = Product::with(['variants.units', 'variants'])->findOrFail($id);
        $danhMucs = DanhMucSanPham::orderBy('ten_danh_muc')->get();
        $thuocTinhs = ThuocTinhSanPham::whereNull('thuoc_tinh_cha_id')
            ->where('trang_thai', true)
            ->orderBy('ten_thuoc_tinh')
            ->get();

        return view('admin_xem_truoc.san-pham.sua', [
            'product' => $product,
            'danhMucs' => $danhMucs,
            'thuocTinhs' => $thuocTinhs,
        ]);
    }

    public function update(UpdateSanPhamRequest $request, int $id): RedirectResponse
    {
        $product = Product::with('variants.units')->findOrFail($id);
        $data = $request->validated();

        // 1. Upload anh bien the (ngoai transaction de tranh rollback file)
        $variantImages = [];
        if ($request->hasFile('bien_the')) {
            foreach ($request->file('bien_the') as $idx => $files) {
                if (isset($files['hinh_anh']) && $files['hinh_anh']) {
                    $variantImages[$idx] = $this->uploadImage($files['hinh_anh']);
                }
            }
        }

        return DB::transaction(function () use ($product, $data, $request, $variantImages) {
            // 2. Cap nhat thong tin chung
            $product->update([
                'id_danh_muc' => $data['id_danh_muc'],
                'ten_san_pham' => $data['ten_san_pham'],
                'thuong_hieu' => $data['thuong_hieu'] ?? null,
                'mo_ta' => $data['mo_ta'] ?? null,
                'trang_thai' => $data['trang_thai'] ?? true,
            ]);

            $incomingVariantIds = [];

            // 3. CRUD variants
            foreach ($data['bien_the'] ?? [] as $idx => $variant) {
                $existingId = $variant['id'] ?? null;
                // Xac dinh anh: upload moi > anh cu cua variant
                $variantImage = $variantImages[$idx] ?? null;

                if ($existingId && $product->variants->contains('id', $existingId)) {
                    // Cap nhat variant hien co
                    $existingVariant = BienTheSanPham::find($existingId);
                    $variantImage = $variantImage ?? $existingVariant->hinh_anh;

                    $thuocTinhIds = null;
                    if (!empty($variant['thuoc_tinh_ids'])) {
                        $ids = array_map('intval', explode(',', $variant['thuoc_tinh_ids']));
                        $thuocTinhIds = array_filter($ids);
                    }

                    $existingVariant->update([
                        'ten_bien_the' => $variant['ten_bien_the'] ?? null,
                        'ma_vach' => !empty($variant['ma_vach']) ? $variant['ma_vach'] : null,
                        'gia_von' => $variant['gia_von'] ?? 0,
                        'gia_ban' => $variant['gia_ban'] ?? 0,
                        'hinh_anh' => $variantImage,
                        'thuoc_tinh_ids' => $thuocTinhIds,
                        'trang_thai' => $variant['trang_thai'] ?? $existingVariant->trang_thai,
                    ]);

                    $incomingVariantIds[] = $existingId;
                } else {
                    // Tao variant moi
                    $thuocTinhIds = null;
                    if (!empty($variant['thuoc_tinh_ids'])) {
                        $ids = array_map('intval', explode(',', $variant['thuoc_tinh_ids']));
                        $thuocTinhIds = array_filter($ids);
                    }

                    $newVariant = BienTheSanPham::create([
                        'product_id' => $product->id,
                        'ten_bien_the' => $variant['ten_bien_the'] ?? null,
                        'ma_hang' => $this->generateUniqueMaHang(),
                        'ma_vach' => !empty($variant['ma_vach']) ? $variant['ma_vach'] : $this->generateUniqueMaVach(),
                        'gia_von' => $variant['gia_von'] ?? 0,
                        'gia_ban' => $variant['gia_ban'] ?? 0,
                        'so_luong_ton' => 0,
                        'hinh_anh' => $variantImage,
                        'thuoc_tinh_ids' => $thuocTinhIds,
                        'trang_thai' => $variant['trang_thai'] ?? true,
                    ]);

                    $incomingVariantIds[] = $newVariant->id;
                }

                // 4. Sync units CHỈ một lần cho variant đầu tiên (nếu có)
                // Lý do: form Blade dùng chung bảng "Đơn vị bán hàng" cho mọi variant,
                // nên units từ input bien_the[*].units[] là lặp lại giống nhau.
                // Để tránh nhân bản, chỉ xử lý bộ units của variant đầu tiên và gắn
                // vào variant đầu tiên, các variant khác để trống units.
                $firstVariant = $data['bien_the'][0] ?? null;
                if ($firstVariant && !empty($incomingVariantIds)) {
                    $primaryVariantId = $incomingVariantIds[0];
                    $primaryVariant = BienTheSanPham::find($primaryVariantId);
                    $primaryImage = $primaryVariant?->hinh_anh;

                    // Xóa hết unit cũ của variant đầu để rebuild sạch
                    DonViQuyDoi::where('variant_id', $primaryVariantId)->delete();

                    // Dedupe units theo ten_don_vi + ty_le_quy_doi
                    $seenUnitKeys = [];
                    $uniqueUnits = [];
                    foreach (($firstVariant['units'] ?? []) as $unit) {
                        $tenDonVi = trim($unit['ten_don_vi'] ?? '');
                        $tyLe = (int)($unit['ty_le_quy_doi'] ?? 1);
                        $key = mb_strtolower($tenDonVi) . '|' . $tyLe;
                        if ($tenDonVi === '' || isset($seenUnitKeys[$key])) {
                            continue;
                        }
                        $seenUnitKeys[$key] = true;
                        $uniqueUnits[] = $unit;
                    }

                    foreach ($uniqueUnits as $unit) {
                        DonViQuyDoi::create([
                            'variant_id' => $primaryVariantId,
                            'ten_don_vi' => $unit['ten_don_vi'],
                            'ty_le_quy_doi' => (int)($unit['ty_le_quy_doi'] ?? 1),
                            'ma_hang' => $unit['ma_hang'] ?? $this->generateUniqueMaHang(),
                            'ma_vach' => $unit['ma_vach'] ?? null,
                            'gia_von_quy_doi' => $unit['gia_von_quy_doi'] ?? 0,
                            'gia_ban_quy_doi' => $unit['gia_ban_quy_doi'] ?? 0,
                            'gia_ban_si' => $unit['gia_ban_si'] ?? null,
                            'hinh_anh' => $unit['hinh_anh'] ?? $primaryImage,
                            'la_don_vi_mac_dinh' => ((int)($unit['ty_le_quy_doi'] ?? 1) === 1),
                        ]);
                    }

                    // Đồng thời xóa unit của tất cả variant khác để tránh dữ liệu cũ nhân bản
                    if (count($incomingVariantIds) > 1) {
                        $otherVariantIds = array_slice($incomingVariantIds, 1);
                        DonViQuyDoi::whereIn('variant_id', $otherVariantIds)->delete();
                    }
                }
            }

            // 5. Xoa variants bi loai bo
            $deletedIds = $request->input('deleted_variant_ids', []);

            foreach ($product->variants as $variant) {
                if (!in_array($variant->id, $incomingVariantIds) || in_array($variant->id, $deletedIds)) {
                    $imagesToCheck = [];
                    if ($variant->hinh_anh && !str_starts_with($variant->hinh_anh, 'http')) {
                        $imagesToCheck[] = $variant->hinh_anh;
                    }
                    foreach ($variant->units as $unit) {
                        if ($unit->hinh_anh && !str_starts_with($unit->hinh_anh, 'http')) {
                            $imagesToCheck[] = $unit->hinh_anh;
                        }
                        $unit->delete();
                    }
                    $variant->delete();

                    foreach (array_unique($imagesToCheck) as $imgPath) {
                        $this->deleteImageIfUnused($imgPath);
                    }
                }
            }

            // Phan biet: co bien the hay khong de cap nhat dung truong
            if ($product->variants()->exists()) {
                // San pham co bien the - chi can redirect
                return redirect()->route('san-pham.index')->with('success', 'Đã cập nhật sản phẩm.');
            } else {
                // San pham khong co bien the - cap nhat them cac truong gia_von, gia_ban, so_luong_ton_kho
                $product->update([
                    'gia_von' => $data['gia_von'] ?? 0,
                    'gia_ban' => $data['gia_ban'] ?? 0,
                    'so_luong_ton_kho' => $data['so_luong_ton_kho'] ?? 0,
                ]);
            }
        }); // dong DB::transaction

        // Don anh cu cua san pham (neu co upload moi - nam ngoai transaction)
        if ($request->hasFile('hinh_anh') && $product->getOriginal('hinh_anh')
            && !str_starts_with($product->getOriginal('hinh_anh'), 'http')) {
            $this->deleteImageIfUnused($product->getOriginal('hinh_anh'));
        }

        return redirect()->route('san-pham.index')->with('success', 'Đã cập nhật sản phẩm.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $product = Product::with('variants.units')->findOrFail($id);

        foreach ($product->variants as $variant) {
            foreach ($variant->units as $unit) {
                if ($unit->hinh_anh && !str_starts_with($unit->hinh_anh, 'http')) {
                    $this->deleteImageIfUnused($unit->hinh_anh);
                }
                $unit->delete();
            }
            if ($variant->hinh_anh && !str_starts_with($variant->hinh_anh, 'http')) {
                $this->deleteImageIfUnused($variant->hinh_anh);
            }
            $variant->delete();
        }

        $product->delete();

        return redirect()->route('san-pham.index')->with('success', 'Đã xóa sản phẩm.');
    }

    public function bulkAction(Request $request): RedirectResponse
    {
        $request->validate([
            'action' => 'required|in:delete,activate,deactivate',
            'ids' => 'required|array|min:1',
        ]);

        $ids = $request->input('ids', []);
        $action = $request->input('action');
        $count = count($ids);

        switch ($action) {
            case 'delete':
                // Soft-delete toàn bộ product; cascade variants + units
                $products = Product::with(['variants.units'])->whereIn('id', $ids)->get();
                foreach ($products as $product) {
                    foreach ($product->variants as $variant) {
                        foreach ($variant->units as $unit) {
                            if ($unit->hinh_anh && !str_starts_with($unit->hinh_anh, 'http')) {
                                $this->deleteImageIfUnused($unit->hinh_anh);
                            }
                            $unit->delete();
                        }
                        if ($variant->hinh_anh && !str_starts_with($variant->hinh_anh, 'http')) {
                            $this->deleteImageIfUnused($variant->hinh_anh);
                        }
                        $variant->delete();
                    }
                    if ($product->hinh_anh && !str_starts_with($product->hinh_anh, 'http')) {
                        $this->deleteImageIfUnused($product->hinh_anh);
                    }
                    $product->delete();
                }
                $message = 'Đã xóa ' . $count . ' sản phẩm.';
                break;

            case 'activate':
                Product::whereIn('id', $ids)->update(['trang_thai' => true]);
                $message = 'Đã bật trạng thái cho ' . $count . ' sản phẩm.';
                break;

            case 'deactivate':
                Product::whereIn('id', $ids)->update(['trang_thai' => false]);
                $message = 'Đã tắt trạng thái cho ' . $count . ' sản phẩm.';
                break;
        }

        return redirect()->route('san-pham.index')->with('success', $message);
    }

    public function trash(Request $request): View
    {
        $keyword = $request->input('keyword');

        $trashed = BienTheSanPham::with(['product.danhMuc', 'units'])
            ->onlyTrashed()
            ->when($keyword, fn($q) => $q
                ->whereHas('product', fn($inner) => $inner
                    ->whereRaw('LOWER(ten_san_pham) LIKE ?', ["%".mb_strtolower($keyword)."%"])))
            ->orderByDesc('deleted_at')
            ->paginate(10)
            ->withQueryString();

        return view('admin_xem_truoc.san-pham.thung-rac', [
            'trashed' => $trashed,
            'keyword' => $keyword,
        ]);
    }

    public function exportTemplate()
    {
        return new \App\Exports\SanPhamImportTemplateExport;
    }

    public function import(ImportSanPhamRequest $request): RedirectResponse
    {
        $file = $request->file('excel_file');
        $handle = fopen($file->getRealPath(), 'r');
        if (!$handle) {
            return redirect()->route('san-pham.index')->with('error', 'Không thể đọc file.');
        }

        $rows = [];
        while (($data = fgetcsv($handle, 0, ',')) !== false) {
            $rows[] = $data;
        }
        fclose($handle);

        $importer = new \App\Imports\SanPhamImport;
        $importer->collection(collect($rows));
        $summary = $importer->getSummary();

        $errors = $summary['errors'] ?? [];
        $total = $summary['created'] + $summary['updated'] + $summary['skipped'];

        if (!empty($errors)) {
            return redirect()->route('san-pham.index')
                ->with('error', 'Import hoàn tất với lỗi: ' . implode('; ', $errors))
                ->with('import_summary', $summary);
        }

        $message = "Import thành công {$summary['created']} sản phẩm mới, cập nhật {$summary['updated']} sản phẩm.";
        if ($summary['skipped'] > 0) {
            $message .= " Bỏ qua {$summary['skipped']} dòng.";
        }

        return redirect()->route('san-pham.index')->with('success', $message);
    }

    public function restore(int $id): RedirectResponse
    {
        $variant = BienTheSanPham::onlyTrashed()->findOrFail($id);
        $variant->restore();

        return redirect()->route('san-pham.trash')->with('success', 'Đã khôi phục biến thể.');
    }

    public function forceDelete(int $id): RedirectResponse
    {
        $variant = BienTheSanPham::onlyTrashed()->with('units')->findOrFail($id);

        foreach ($variant->units as $unit) {
            if ($unit->hinh_anh && !str_starts_with($unit->hinh_anh, 'http')) {
                $fullPath = public_path($unit->hinh_anh);
                if (is_file($fullPath)) {
                    unlink($fullPath);
                }
            }
            $unit->forceDelete();
        }

        if ($variant->hinh_anh && !str_starts_with($variant->hinh_anh, 'http')) {
            $fullPath = public_path($variant->hinh_anh);
            if (is_file($fullPath)) {
                unlink($fullPath);
            }
        }

        $variant->forceDelete();

        return redirect()->route('san-pham.trash')->with('success', 'Đã xóa vĩnh viễn biến thể.');
    }

    public function show($id)
    {
        $product = Product::with(['variants.units', 'danhMuc'])->findOrFail($id);
        $variant = $product->variants->first();

        $theKho = [];
        $loHang = [];

        if ($variant) {
            $theKho = DB::table('chi_tiet_phieu')
                ->join('phieu', 'chi_tiet_phieu.id_phieu', '=', 'phieu.id')
                ->leftJoin('nha_cung_cap', 'phieu.id_nha_cung_cap', '=', 'nha_cung_cap.id')
                ->where('chi_tiet_phieu.variant_id', $variant->id)
                ->select(
                    'phieu.id as ma_phieu',
                    'phieu.created_at as thoi_gian',
                    'phieu.loai_phieu',
                    'nha_cung_cap.ten_nha_cung_cap as nha_cung_cap',
                    'chi_tiet_phieu.gia_nhap as gia',
                    'chi_tiet_phieu.so_luong as so_luong'
                )
                ->orderByDesc('phieu.created_at')
                ->get();

            $loHang = DB::table('chi_tiet_phieu')
                ->where('variant_id', $variant->id)
                ->whereNotNull('ma_lo')
                ->select(
                    'ma_lo',
                    'han_su_dung',
                    DB::raw('COALESCE(SUM(so_luong_con_lai), SUM(so_luong)) as so_luong')
                )
                ->groupBy('ma_lo', 'han_su_dung')
                ->orderBy('han_su_dung')
                ->get();
        }

        return view('admin_xem_truoc.san-pham.chi-tiet', compact('product', 'theKho', 'loHang'));
    }

    protected function uploadImage($file, string $subDir = 'san-pham'): string
    {
        $dir = public_path("uploads/{$subDir}");
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        $filename = time() . '_' . preg_replace('/[^a-zA-Z0-9\.\-_]/', '_', $file->getClientOriginalName());
        $file->move($dir, $filename);
        return "uploads/{$subDir}/" . $filename;
    }

    protected function deleteImageIfUnused(?string $imagePath, ?int $excludeId = null): void
    {
        if (blank($imagePath) || str_starts_with($imagePath, 'http')) {
            return;
        }

        $variantQuery = BienTheSanPham::query()->where('hinh_anh', $imagePath);
        if ($excludeId) {
            $variantQuery->where('id', '!=', $excludeId);
        }
        if ($variantQuery->exists()) {
            return;
        }

        // Kiem tra xem don vi quy doi nao khac con dung anh nay khong
        if (DonViQuyDoi::where('hinh_anh', $imagePath)->exists()) {
            return;
        }

        $fullPath = public_path($imagePath);
        if (is_file($fullPath)) {
            unlink($fullPath);
        }
    }

    protected function generateUniqueMaHang(): string
    {
        do {
            $code = 'MH' . strtoupper(Str::random(6));
        } while (
            BienTheSanPham::where('ma_hang', $code)->exists()
            || DonViQuyDoi::where('ma_hang', $code)->exists()
        );
        return $code;
    }

    protected function generateUniqueMaVach(): string
    {
        do {
            $code = 'BV' . strtoupper(Str::random(8));
        } while (
            BienTheSanPham::where('ma_vach', $code)->exists()
            || DonViQuyDoi::where('ma_vach', $code)->exists()
        );
        return $code;
    }

}
