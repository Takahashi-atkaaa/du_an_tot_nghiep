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

        // Lấy variants kèm thông tin cha, phẳng hóa để hiển thị
        $query = BienTheSanPham::with(['product.danhMuc', 'units'])
            ->whereHas('product', fn($q) => $q->whereNull('deleted_at'))
            ->when($keyword, fn($q) => $q
                ->whereHas('product', fn($inner) => $inner
                    ->whereRaw('LOWER(ten_san_pham) LIKE ?', ["%".mb_strtolower($keyword)."%"]))
                ->orWhereRaw('LOWER(ten_bien_the) LIKE ?', ["%".mb_strtolower($keyword)."%"])
                ->orWhereRaw('LOWER(ma_vach) LIKE ?', ["%".mb_strtolower($keyword)."%"])
                ->orWhereRaw('LOWER(ma_hang) LIKE ?', ["%".mb_strtolower($keyword)."%"]))
            ->when($danhMucId, fn($q) => $q->whereHas('product', fn($inner) => $inner->where('id_danh_muc', $danhMucId)))
            ->when(!is_null($trangThai), fn($q) => $q->where('trang_thai', $trangThai))
            ->orderBy('product_id')
            ->orderBy('ten_bien_the');

        $paginated = $query->paginate(10)->withQueryString();
        $variants = $paginated->getCollection();

        // Flat list với units
        $flatItems = $this->buildFlatList($variants);

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

    protected function buildFlatList($variants)
    {
        $flat = [];
        foreach ($variants as $variant) {
            $thuocTinhs = $variant->thuocTinhs();
            $thuocTinhLabels = $thuocTinhs->pluck('ten_thuoc_tinh')->toArray();

            // Row chính cho variant (không có unit)
            $flat[] = [
                'type' => 'variant',
                'id' => $variant->id,
                'product_id' => $variant->product_id,
                'ten_san_pham' => $variant->product->ten_san_pham ?? '',
                'danh_muc' => $variant->product->danhMuc->ten_danh_muc ?? '',
                'ten_hien_thi' => $variant->ten_bien_the
                    ? ($variant->product->ten_san_pham . ' - ' . $variant->ten_bien_the)
                    : $variant->product->ten_san_pham,
                'thuoc_tinh_labels' => $thuocTinhLabels,
                'ma_hang' => $variant->ma_hang,
                'ma_vach' => $variant->ma_vach,
                'gia_von' => $variant->gia_von,
                'gia_ban' => $variant->gia_ban,
                'so_luong_ton' => $variant->so_luong_ton,
                'trang_thai' => $variant->trang_thai,
                'hinh_anh' => $variant->hinh_anh,
                'units' => [],
            ];

            // Mỗi unit là 1 dòng riêng
            foreach ($variant->units as $unit) {
                $flat[] = [
                    'type' => 'unit',
                    'id' => $unit->id,
                    'variant_id' => $variant->id,
                    'product_id' => $variant->product_id,
                    'ten_san_pham' => $variant->product->ten_san_pham ?? '',
                    'danh_muc' => '',
                    'ten_hien_thi' => ($variant->ten_bien_the
                        ? ($variant->product->ten_san_pham . ' - ' . $variant->ten_bien_the)
                        : $variant->product->ten_san_pham) . ' - ' . $unit->ten_don_vi,
                    'thuoc_tinh_labels' => $thuocTinhLabels,
                    'ma_hang' => $unit->ma_hang,
                    'ma_vach' => $unit->ma_vach,
                    'gia_von' => $unit->gia_von_quy_doi,
                    'gia_ban' => $unit->gia_ban_quy_doi,
                    'ty_le_quy_doi' => $unit->ty_le_quy_doi,
                    'so_luong_ton' => $variant->so_luong_ton,
                    'trang_thai' => $variant->trang_thai,
                    'hinh_anh' => $unit->hinh_anh ?: $variant->hinh_anh,
                ];
            }
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

                foreach ($bienThe as $idx => $variant) {
                    $variantImage = $variantImages[$idx] ?? $imagePath;

                    // Parse thuoc_tinh_ids
                    $thuocTinhIds = null;
                    if (!empty($variant['thuoc_tinh_ids'])) {
                        $ids = array_map('intval', explode(',', $variant['thuoc_tinh_ids']));
                        $thuocTinhIds = array_filter($ids);
                    }

                    $createdVariant = BienTheSanPham::create([
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

                    // 3. Tạo units cho variant
                    if (!empty($variant['units'])) {
                        foreach ($variant['units'] as $unit) {
                            DonViQuyDoi::create([
                                'variant_id' => $createdVariant->id,
                                'ten_don_vi' => $unit['ten_don_vi'],
                                'ty_le_quy_doi' => (int)($unit['ty_le_quy_doi'] ?? 1),
                                'ma_hang' => $unit['ma_hang'] ?? $this->generateUniqueMaHang(),
                                'ma_vach' => $unit['ma_vach'] ?? null,
                                'gia_von_quy_doi' => $unit['gia_von_quy_doi'] ?? 0,
                                'gia_ban_quy_doi' => $unit['gia_ban_quy_doi'] ?? 0,
                                'gia_ban_si' => $unit['gia_ban_si'] ?? null,
                                'hinh_anh' => $variantImage,
                                'la_don_vi_mac_dinh' => false,
                            ]);
                        }
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

        // Nếu là biến thể → load thêm sản phẩm cha
        $sanPhamCha = $sanPham->sanPhamCha;
        $isParent = (bool) $sanPham->la_san_pham_cha;

        return view('admin_xem_truoc.san-pham-sua', compact(
            'sanPham',
            'danhMucs',
            'donVis',
            'thuocTinhs',
            'sanPhamCha',
            'isParent'
        ));
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

                // 4. CRUD units cho variant - truyen anh da xac dinh
                $lastVariantId = $existingId ?? $incomingVariantIds[count($incomingVariantIds) - 1];
                $this->syncUnits($lastVariantId, $variant['units'] ?? [], $variantImage);
            }

            // 5. Xoa variants bi loai bo
            $deletedIds = $request->input('deleted_variant_ids', []);

            foreach ($product->variants as $variant) {
                if (!in_array($variant->id, $incomingVariantIds) || in_array($variant->id, $deletedIds)) {
                    // Thu thap anh de kiem tra sau khi xoa
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

                    // Don anh khong con duoc su dung sau khi xoa
                    foreach (array_unique($imagesToCheck) as $imgPath) {
                        $this->deleteImageIfUnused($imgPath);
                    }
                }
            }

        } else {
            // === BIẾN THỂ ===
            $sanPham->update([
                'id_danh_muc' => $data['id_danh_muc'],
                'ten_san_pham' => $data['ten_san_pham'],
                'thuong_hieu' => $data['thuong_hieu'] ?? null,
                'gia_von' => $data['gia_von'] ?? 0,
                'gia_ban' => $data['gia_ban'] ?? 0,
                'so_luong_ton_kho' => $data['so_luong_ton_kho'] ?? 0,
                'dinh_muc_toi_thieu' => $data['dinh_muc_toi_thieu'] ?? 0,
                'mo_ta' => $data['mo_ta'] ?? null,
                'id_don_vi' => $baseUnit->id,
                'hinh_anh' => $imagePath,
                'trang_thai' => $data['trang_thai'] ?? true,
            ]);

            // Sync thuộc tính
            if (!empty($data['thuoc_tinh_ids'])) {
                $ids = array_map('intval', explode(',', $data['thuoc_tinh_ids']));
                $sanPham->thuocTinhs()->sync(array_filter($ids));
            } else {
                // Tao moi
                $newUnit = DonViQuyDoi::create([
                    'variant_id' => $variantId,
                    'ten_don_vi' => $unit['ten_don_vi'],
                    'ty_le_quy_doi' => (int)($unit['ty_le_quy_doi'] ?? 1),
                    'ma_hang' => $unit['ma_hang'] ?? $this->generateUniqueMaHang(),
                    'ma_vach' => $unit['ma_vach'] ?? null,
                    'gia_von_quy_doi' => $unit['gia_von_quy_doi'] ?? 0,
                    'gia_ban_quy_doi' => $unit['gia_ban_quy_doi'] ?? 0,
                    'gia_ban_si' => $unit['gia_ban_si'] ?? null,
                    'hinh_anh' => $variantImage,
                    'la_don_vi_mac_dinh' => false,
                ]);
                $incomingUnitIds[] = $newUnit->id;
            }
        }

        // Xoa units khong con trong form
        DonViQuyDoi::where('variant_id', $variantId)
            ->whereNotIn('id', $incomingUnitIds)
            ->delete();
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

        switch ($action) {
            case 'delete':
                $variants = BienTheSanPham::with('units')->whereIn('id', $ids)->get();
                foreach ($variants as $variant) {
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
                $message = 'Đã xóa ' . count($ids) . ' biến thể.';
                break;

            case 'activate':
                BienTheSanPham::whereIn('id', $ids)->update(['trang_thai' => true]);
                $message = 'Đã bật trạng thái cho ' . count($ids) . ' biến thể.';
                break;

            case 'deactivate':
                BienTheSanPham::whereIn('id', $ids)->update(['trang_thai' => false]);
                $message = 'Đã tắt trạng thái cho ' . count($ids) . ' biến thể.';
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

    public function export()
    {
        return new \App\Exports\SanPhamExport;
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

    public function show($id)
    {
        $sanPham = SanPham::with(['danhMuc', 'donVi', 'thuocTinhs', 'bienThe.thuocTinhs', 'bienThe.donVi'])->findOrFail($id);

        $theKho = DB::table('chi_tiet_phieu')
            ->join('phieu', 'chi_tiet_phieu.id_phieu', '=', 'phieu.id')
            ->leftJoin('nha_cung_cap', 'phieu.id_nha_cung_cap', '=', 'nha_cung_cap.id')
            ->where('chi_tiet_phieu.id_san_pham', $id)
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
            ->where('id_san_pham', $id)
            ->whereNotNull('ma_lo')
            ->select(
                'ma_lo',
                'han_su_dung',
                DB::raw('COALESCE(SUM(so_luong_con_lai), SUM(so_luong)) as so_luong')
            )
            ->groupBy('ma_lo', 'han_su_dung')
            ->orderBy('han_su_dung')
            ->get();

        return view('admin_xem_truoc.san-pham-chi-tiet', compact('sanPham', 'theKho', 'loHang'));
    }

    // ======= EXPORT =======
    public function export(Request $request)
    {
        $query = SanPham::with('danhMuc')
            ->orderBy('created_at', 'desc');

        if ($request->has('search') && $request->search) {
            $query->where('ten_san_pham', 'like', '%' . $request->search . '%');
        }
        if ($request->has('id_danh_muc') && $request->id_danh_muc) {
            $query->where('id_danh_muc', $request->id_danh_muc);
        }
        if ($request->has('trang_thai') && $request->trang_thai !== '') {
            $query->where('trang_thai', $request->trang_thai);
        }

        $sanPhams = $query->get();

        $columns = [
            'Mã SP', 'Tên sản phẩm', 'Danh mục', 'Thương hiệu', 'Mã vạch',
            'Đơn vị', 'Giá vốn', 'Giá bán', 'Tồn kho', 'Định mức tối thiểu',
            'Mô tả', 'Trạng thái', 'Ngày tạo'
        ];

        $rows = [];
        foreach ($sanPhams as $sp) {
            $rows[] = [
                $sp->id,
                $sp->ten_san_pham,
                $sp->danhMuc->ten_danh_muc ?? '',
                $sp->thuong_hieu ?? '',
                $sp->ma_vach ?? '',
                $sp->donVi->ten_don_vi ?? $sp->don_vi ?? '',
                $sp->gia_von ?? 0,
                $sp->gia_ban ?? 0,
                $sp->so_luong_ton_kho ?? 0,
                $sp->dinh_muc_toi_thieu ?? 0,
                $sp->mo_ta ?? '',
                $sp->trang_thai == 1 ? 'Đang bán' : 'Ngừng bán',
                $sp->created_at ? $sp->created_at->format('d/m/Y H:i') : '',
            ];
        }

        $type = $request->get('type', 'csv');

        if ($type === 'csv') {
            return $this->exportCsv($columns, $rows, 'danh_sach_san_pham');
        }

        // Excel-like xlsx using XML (compatible without PhpSpreadsheet)
        return $this->exportXmlExcel($columns, $rows, 'danh_sach_san_pham');
    }

    private function exportCsv(array $columns, array $rows, string $filename)
    {
        $handle = fopen('php://temp', 'r+');
        fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF)); // BOM for UTF-8

        fputcsv($handle, $columns);
        foreach ($rows as $row) {
            fputcsv($handle, $row);
        }

        return response()->streamDownload(function() use ($handle) {
            rewind($handle);
            echo stream_get_contents($handle);
            fclose($handle);
        }, $filename . '.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    private function exportXmlExcel(array $columns, array $rows, string $filename)
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>
<?mso-application progid="Excel.Sheet"?>
<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet"
 xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet">
 <Worksheet ss:Name="SanPham">
  <Table>';

        // Header row
        $xml .= '<Row>';
        foreach ($columns as $col) {
            $xml .= '<Cell><Data ss:Type="String">' . htmlspecialchars($col) . '</Data></Cell>';
        }
        $xml .= '</Row>';

        // Data rows
        foreach ($rows as $row) {
            $xml .= '<Row>';
            foreach ($row as $cell) {
                $val = is_numeric($cell) && !str_starts_with((string)$cell, '0') ? $cell : htmlspecialchars((string)$cell);
                $type = is_numeric($cell) ? 'Number' : 'String';
                $xml .= "<Cell><Data ss:Type=\"{$type}\">{$val}</Data></Cell>";
            }
            $xml .= '</Row>';
        }

        $xml .= '  </Table>
 </Worksheet>
</Workbook>';

        return response($xml, 200, [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '.xls"',
        ]);
    }

    public function exportTemplate(Request $request)
    {
        $type = $request->get('type', 'csv');

        $columns = [
            'Tên sản phẩm *', 'Danh mục *', 'Thương hiệu', 'Mã vạch',
            'Giá vốn', 'Giá bán', 'Tồn kho', 'Định mức tối thiểu', 'Mô tả', 'Đơn vị'
        ];

        $sample = [
            ['Áo thun nam basic', 'Thời trang', 'Nike', '8934567890123', '150000', '250000', '50', '5', 'Áo thun nam chất liệu cotton 100%', 'Cái'],
            ['Quần jeans nữ', 'Thời trang', 'Levis', '8934567890124', '300000', '550000', '20', '3', 'Quần jeans nữ form regular', 'Cái'],
        ];

        if ($type === 'csv') {
            $output = fopen('php://output', 'w');
            fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($output, $columns);
            foreach ($sample as $row) {
                fputcsv($output, $row);
            }
            return response()->streamDownload(function() use ($output) {
                rewind($output);
                fpassthru($output);
                fclose($output);
            }, 'mau_import_san_pham.csv', [
                'Content-Type' => 'text/csv; charset=UTF-8',
            ]);
        }

        $xml = '<?xml version="1.0" encoding="UTF-8"?>
<?mso-application progid="Excel.Sheet"?>
<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet"
 xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet">
 <Worksheet ss:Name="MauImport">
  <Table>';

        $xml .= '<Row>';
        foreach ($columns as $col) {
            $xml .= '<Cell><Data ss:Type="String">' . htmlspecialchars($col) . '</Data></Cell>';
        }
        $xml .= '</Row>';

        foreach ($sample as $row) {
            $xml .= '<Row>';
            foreach ($row as $cell) {
                $xml .= '<Cell><Data ss:Type="String">' . htmlspecialchars($cell) . '</Data></Cell>';
            }
            $xml .= '</Row>';
        }

        $xml .= '  </Table>
 </Worksheet>
</Workbook>';

        return response($xml, 200, [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="mau_import_san_pham.xls"',
        ]);
    }

    // ======= IMPORT =======
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv,txt|max:10240',
        ]);

        $file = $request->file('file');
        $action = $request->input('import_action', 'skip');

        $extension = strtolower($file->getClientOriginalExtension());
        $path = $file->getRealPath();

        if ($extension === 'csv') {
            $data = $this->parseCsv($path);
        } else {
            // xlsx/xls - use XML-based reading
            $data = $this->parseExcelXml($path);
        }

        if (empty($data)) {
            return redirect()->back()->with('error', 'Không đọc được dữ liệu từ file. Vui lòng kiểm tra định dạng file.');
        }

        $headers = array_map('trim', $data[0]);
        $rows = array_slice($data, 1);

        $colMap = $this->mapImportColumns($headers);

        if (!$colMap['ten_san_pham'] && !$colMap['danh_muc']) {
            return redirect()->back()->with('error', 'File thiếu cột bắt buộc: Tên sản phẩm hoặc Danh mục.');
        }

        $errors = [];
        $imported = 0;
        $updated = 0;
        $skipped = 0;

        DB::beginTransaction();
        try {
            foreach ($rows as $index => $row) {
                $lineNum = $index + 2; // row 1 is header

                $tenSp = isset($colMap['ten_san_pham']) ? trim($row[$colMap['ten_san_pham']] ?? '') : '';
                $danhMuc = isset($colMap['danh_muc']) ? trim($row[$colMap['danh_muc']] ?? '') : '';

                if (empty($tenSp)) {
                    $errors[] = "Dòng {$lineNum}: Thiếu tên sản phẩm.";
                    continue;
                }

                // Find or create category
                $idDanhMuc = null;
                if (!empty($danhMuc)) {
                    $dm = DanhMucSanPham::firstOrCreate(
                        ['ten_danh_muc' => $danhMuc],
                        ['slug_danh_muc' => Str::slug($danhMuc), 'trang_thai' => 1]
                    );
                    $idDanhMuc = $dm->id;
                }

                // Find existing product by name + category
                $existing = SanPham::where('ten_san_pham', $tenSp)
                    ->when($idDanhMuc, fn($q) => $q->where('id_danh_muc', $idDanhMuc))
                    ->first();

                $data = [
                    'ten_san_pham' => $tenSp,
                    'id_danh_muc' => $idDanhMuc,
                    'thuong_hieu' => isset($colMap['thuong_hieu']) ? trim($row[$colMap['thuong_hieu']] ?? '') : null,
                    'ma_vach' => isset($colMap['ma_vach']) ? trim($row[$colMap['ma_vach']] ?? '') : null,
                    'gia_von' => isset($colMap['gia_von']) ? (float)($row[$colMap['gia_von']] ?? 0) : null,
                    'gia_ban' => isset($colMap['gia_ban']) ? (float)($row[$colMap['gia_ban']] ?? 0) : null,
                    'so_luong_ton_kho' => isset($colMap['so_luong_ton_kho']) ? (int)($row[$colMap['so_luong_ton_kho']] ?? 0) : 0,
                    'dinh_muc_toi_thieu' => isset($colMap['dinh_muc_toi_thieu']) ? (int)($row[$colMap['dinh_muc_toi_thieu']] ?? 0) : 0,
                    'mo_ta' => isset($colMap['mo_ta']) ? trim($row[$colMap['mo_ta']] ?? '') : null,
                    'don_vi' => isset($colMap['don_vi']) ? trim($row[$colMap['don_vi']] ?? '') : 'Cái',
                    'trang_thai' => 1,
                ];

                if ($existing) {
                    if ($action === 'update') {
                        $existing->update($data);
                        $updated++;
                    } else {
                        $skipped++;
                    }
                } else {
                    SanPham::create($data);
                    $imported++;
                }
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Lỗi khi import: ' . $e->getMessage());
        }

        $msg = "Import hoàn tất: {$imported} sản phẩm mới, {$updated} cập nhật, {$skipped} bỏ qua.";
        if (!empty($errors)) {
            $msg .= ' Các lỗi: ' . implode('; ', array_slice($errors, 0, 5));
            if (count($errors) > 5) $msg .= '...';
        }

        return redirect()->back()->with('success', $msg);
    }

    private function parseCsv(string $path): array
    {
        $data = [];
        if (($handle = fopen($path, 'r')) !== false) {
            // Detect delimiter
            $firstLine = fgets($handle);
            rewind($handle);
            $delimiter = str_contains($firstLine, ';') ? ';' : ',';

            while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
                if (count($row) === 1 && empty(trim($row[0]))) continue;
                $data[] = $row;
            }
            fclose($handle);
        }
        return $data;
    }

    private function parseExcelXml(string $path): array
    {
        $zip = new \ZipArchive();
        if ($zip->open($path) !== true) return [];

        $data = [];
        $sharedStrings = [];

        // Read shared strings
        if ($zip->locateName('xl/sharedStrings.xml') !== false) {
            $xml = $zip->getFromName('xl/sharedStrings.xml');
            $sx = simplexml_load_string($xml);
            foreach ($sx->si as $si) {
                $sharedStrings[] = (string) $si->t;
            }
        }

        // Find first sheet
        $sheetFiles = [];
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $name = $zip->getNameIndex($i);
            if (preg_match('#xl/worksheets/sheet\d+\.xml#', $name)) {
                $sheetFiles[] = $name;
            }
        }

        if (empty($sheetFiles)) {
            $zip->close();
            return [];
        }

        $xml = $zip->getFromName($sheetFiles[0]);
        $zip->close();

        $sx = simplexml_load_string($xml);
        $ns = $sx->getNamespaces(true);

        foreach ($sx->sheetData->row as $row) {
            $rowData = [];
            foreach ($row->c as $cell) {
                $r = (string) $cell['r'];
                $t = (string) ($cell['t'] ?? '');
                $v = (string) $cell->v;

                // Column index from cell ref (A=0, B=1, ...)
                preg_match('/[A-Z]+/', $r, $matches);
                $colStr = $matches[0];
                $colIdx = $this->excelColToIndex($colStr);

                if ($t === 's') {
                    $rowData[$colIdx] = $sharedStrings[(int)$v] ?? '';
                } else {
                    $rowData[$colIdx] = $v;
                }
            }
            if (!empty($rowData)) {
                ksort($rowData);
                $data[] = array_values($rowData);
            }
        }

        return $data;
    }

    private function excelColToIndex(string $col): int
    {
        $index = 0;
        for ($i = 0; $i < strlen($col); $i++) {
            $index = $index * 26 + (ord($col[$i]) - ord('A') + 1);
        }
        return $index - 1;
    }

    private function mapImportColumns(array $headers): array
    {
        $map = [];
        $search = [
            'ten_san_pham' => ['ten san pham', 'tên sản phẩm', 'name'],
            'danh_muc' => ['danh muc', 'danh mục', 'category'],
            'thuong_hieu' => ['thuong hieu', 'thương hiệu', 'brand'],
            'ma_vach' => ['ma vach', 'mã vạch', 'barcode', 'sku'],
            'gia_von' => ['gia von', 'giá vốn', 'cost'],
            'gia_ban' => ['gia ban', 'giá bán', 'price'],
            'so_luong_ton_kho' => ['ton kho', 'tồn kho', 'stock', 'quantity'],
            'dinh_muc_toi_thieu' => ['dinh muc', 'định mức', 'min stock'],
            'mo_ta' => ['mo ta', 'mô tả', 'description'],
            'don_vi' => ['don vi', 'đơn vị', 'unit'],
        ];

        foreach ($headers as $i => $header) {
            $h = mb_strtolower(trim($header));
            foreach ($search as $field => $keywords) {
                if (!isset($map[$field])) {
                    foreach ($keywords as $kw) {
                        if (str_contains($h, $kw)) {
                            $map[$field] = $i;
                            break;
                        }
                    }
                }
            }
        }

        return $map;
    }
}
