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
use App\Models\DonViSanPham;
use App\Models\ThuocTinhSanPham;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class SanPhamController extends Controller
{
    public function __construct(private AuditLogger $audit) {}

    public function index(Request $request): View
    {
        $keyword = $request->input('keyword');
        $danhMucId = $request->input('danh_muc');
        $trangThai = $request->filled('trang_thai') ? $request->boolean('trang_thai') : null;

        $danhMucs = DanhMucSanPham::query()->orderBy('ten_danh_muc')->get();
        $thuocTinhChas = ThuocTinhSanPham::whereNull('thuoc_tinh_cha_id')
            ->where('trang_thai', true)
            ->orderBy('ten_thuoc_tinh')
            ->with(['thuocTinhCons' => fn($q) => $q->where('trang_thai', true)->orderBy('ten_thuoc_tinh')])
            ->get();

        $sanPhams = Product::with(['danhMuc', 'variants.units'])
            ->whereNull('deleted_at')
            ->when($keyword, fn($q) => $q
                ->where(fn($inner) => $inner
                    ->whereRaw('LOWER(ten_san_pham) LIKE ?', ["%".mb_strtolower($keyword)."%"])
                    ->orWhereHas('variants', fn($v) => $v
                        ->whereRaw('LOWER(ma_vach) LIKE ?', ["%".mb_strtolower($keyword)."%"])
                        ->orWhereRaw('LOWER(ma_hang) LIKE ?', ["%".mb_strtolower($keyword)."%"])
                        ->orWhereRaw('LOWER(ten_bien_the) LIKE ?', ["%".mb_strtolower($keyword)."%"])
                    )
                )
            )
            ->when($danhMucId, fn($q) => $q->where('id_danh_muc', $danhMucId))
            ->when(!is_null($trangThai), fn($q) => $q->where('trang_thai', $trangThai))
            ->orderBy('id')
            ->paginate(10)
            ->withQueryString();

        $donViSanPhams = DonViSanPham::where('trang_thai', true)->orderBy('ten_don_vi')->get();

        return view('admin_xem_truoc.san-pham.index', [
            'sanPhams' => $sanPhams,
            'danhMucs' => $danhMucs,
            'thuocTinhChas' => $thuocTinhChas,
            'donViSanPhams' => $donViSanPhams,
            'keyword' => $keyword,
            'danhMucId' => $danhMucId,
            'trangThai' => $request->input('trang_thai'),
        ]);
    }

    public function store(StoreSanPhamRequest $request): RedirectResponse
    {
        \Log::info('Data store:', $request->all());

        try {
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

            // Xử lý thuộc tính MỚI: tạo vào DB trước, trả về map {label => id}
            $newAttrMap = $this->processNewAttributes($data['new_attributes'] ?? []);
            $data['bien_the'] = $this->resolveThuocTinhIdsWithNew($data['bien_the'] ?? [], $newAttrMap);

            // return bên trong DB::transaction() sẽ throw, thoát closure và return từ store luôn.
            // Nếu closure throw exception → Laravel tự động rollBack + propagate exception ra ngoài catch.
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

            // Đảm bảo tất cả đơn vị tính từ form đều tồn tại trong DB (tạo mới nếu chưa có)
            $unitNames = collect($bienThe)
                ->pluck('ten_bien_the')
                ->filter()
                ->unique()
                ->values();
            foreach ($unitNames as $name) {
                $this->ensureDonViSanPham($name);
            }

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
                    // Chỉ lưu đơn vị có tỷ lệ > 1 (đơn vị quy đổi) vào bảng don_vi_quy_doi.
                    // Đơn vị cơ bản (ty_le = 1) đã được lưu vào bien_the_san_pham rồi → bỏ qua.
                    if (!empty($variant['units'])) {
                        foreach ($variant['units'] as $unit) {
                            if ((int)($unit['ty_le_quy_doi'] ?? 1) <= 1) {
                                continue; // Bỏ qua đơn vị cơ bản
                            }
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

            $this->audit->ghi(
                'tao_san_pham',
                'Tạo sản phẩm: ' . $data['ten_san_pham'],
                [
                    'bang' => 'san_pham',
                    'id_ban_ghi' => $product->id,
                    'muc_do' => 'info',
                ]
            );

            return redirect()->back()->with('success', 'Đã thêm sản phẩm mới.');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Lỗi store sản phẩm: ' . $e->getMessage());
            return redirect()->back()->withInput()->withErrors(['general' => 'Lỗi: ' . $e->getMessage()]);
        }
    }

    public function edit($id): View
    {
        $product = Product::with(['variants.units', 'variants'])->findOrFail($id);
        $danhMucs = DanhMucSanPham::orderBy('ten_danh_muc')->get();
        $thuocTinhChas = ThuocTinhSanPham::whereNull('thuoc_tinh_cha_id')
            ->where('trang_thai', true)
            ->orderBy('ten_thuoc_tinh')
            ->with(['thuocTinhCons' => fn($q) => $q->where('trang_thai', true)->orderBy('ten_thuoc_tinh')])
            ->get();

        $donViSanPhams = DonViSanPham::where('trang_thai', true)->orderBy('ten_don_vi')->get();

        return view('admin_xem_truoc.san-pham.sua', [
            'product' => $product,
            'danhMucs' => $danhMucs,
            'thuocTinhChas' => $thuocTinhChas,
            'donViSanPhams' => $donViSanPhams,
        ]);
    }

    public function update(UpdateSanPhamRequest $request, int $id): RedirectResponse
    {
        file_put_contents(
            storage_path('logs/debug_update.txt'),
            "[" . now()->toDateTimeString() . "] CONTROLLER HIT\nall=" . json_encode($request->all()) . "\n\n",
            FILE_APPEND
        );

        $product = Product::with('variants.units')->findOrFail($id);
        $data = $request->validated();

        // Lưu giá cũ của variants để so sánh
        $giaCuCacVariant = $product->variants->pluck('gia_ban', 'id')->toArray();

        // Xử lý thuộc tính MỚI: tạo vào DB trước, trả về map {label => id}
        $newAttrMap = $this->processNewAttributes($data['new_attributes'] ?? []);
        $data['bien_the'] = $this->resolveThuocTinhIdsWithNew($data['bien_the'] ?? [], $newAttrMap);

        // Đảm bảo tất cả đơn vị tính từ form đều tồn tại trong DB (tạo mới nếu chưa có)
        $unitNames = collect($data['bien_the'] ?? [])
            ->pluck('ten_bien_the')
            ->filter()
            ->unique()
            ->values();
        foreach ($unitNames as $name) {
            $this->ensureDonViSanPham($name);
        }

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

        // Kiem tra thay doi gia > 20%
        $bienDongGiaLon = [];
        foreach ($data['bien_the'] ?? [] as $variant) {
            $vid = $variant['id'] ?? null;
            if (!$vid || !isset($giaCuCacVariant[$vid])) {
                continue;
            }
            $giaCu = (float) $giaCuCacVariant[$vid];
            $giaMoi = (float) ($variant['gia_ban'] ?? 0);
            if ($giaCu > 0 && abs(($giaMoi - $giaCu) / $giaCu) > 0.20) {
                $bienDongGiaLon[] = [
                    'variant_id' => $vid,
                    'gia_cu' => $giaCu,
                    'gia_moi' => $giaMoi,
                    'phan_tram' => round((($giaMoi - $giaCu) / $giaCu) * 100, 1),
                ];
            }
        }

        $mucDo = !empty($bienDongGiaLon) ? 'danger' : 'info';
        $this->audit->ghi(
            'cap_nhat_san_pham',
            'Cập nhật sản phẩm: ' . $product->ten_san_pham . (!empty($bienDongGiaLon)
                ? ' (giá biến động lớn: ' . count($bienDongGiaLon) . ' biến thể)'
                : ''),
            [
                'bang' => 'san_pham',
                'id_ban_ghi' => $product->id,
                'muc_do' => $mucDo,
                'tao_canh_bao' => !empty($bienDongGiaLon),
                'tieu_de_cb' => 'Thay đổi giá sản phẩm',
                'noi_dung_cb' => $product->ten_san_pham . ' - biến động giá ' . count($bienDongGiaLon) . ' biến thể (>20%)',
                'url_lien_ket' => '/san-pham/' . $product->id . '/sua',
            ]
        );

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

        $this->audit->ghi(
            'xoa_san_pham',
            'Xóa sản phẩm: ' . $product->ten_san_pham,
            [
                'bang' => 'san_pham',
                'id_ban_ghi' => $product->id,
                'muc_do' => 'danger',
                'tao_canh_bao' => true,
                'tieu_de_cb' => 'Xóa sản phẩm',
                'noi_dung_cb' => 'Sản phẩm ' . $product->ten_san_pham . ' đã bị xóa',
                'url_lien_ket' => '/san-pham',
            ]
        );

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
            $code = 'MV' . strtoupper(Str::random(8));
        } while (
            BienTheSanPham::where('ma_vach', $code)->exists()
            || DonViQuyDoi::where('ma_vach', $code)->exists()
        );
        return $code;
    }

    /**
     * Tạo đơn vị tính mới vào DB nếu chưa tồn tại.
     * Được gọi khi người dùng gõ đơn vị không có trong danh sách.
     */
    protected function ensureDonViSanPham(string $tenDonVi): \App\Models\DonViSanPham
    {
        return DonViSanPham::firstOrCreate(
            ['ten_don_vi' => $tenDonVi],
            ['trang_thai' => true, 'so_luong_san_pham_trong_don_vi' => 1]
        );
    }

    /**
     * Xử lý thuộc tính MỚI: tạo vào DB rồi trả về map label => id.
     * Input: [['group_name' => 'Màu sắc', 'parent_id' => 5, 'label' => 'Xanh Mint'], ...]
     * Output: ['Màu sắc|Xanh Mint' => 42, ...]
     */
    protected function processNewAttributes(array $newAttributes): array
    {
        $map = [];
        foreach ($newAttributes as $item) {
            $groupName = trim($item['group_name'] ?? '');
            $label = trim($item['label'] ?? '');
            if ($groupName === '' || $label === '') continue;

            $key = $groupName . '|' . $label;
            if (isset($map[$key])) continue;

            $created = ThuocTinhSanPham::firstOrCreate(
                [
                    'ten_thuoc_tinh' => $label,
                    'thuoc_tinh_cha_id' => $item['parent_id'] ?? null,
                ],
                ['trang_thai' => true]
            );
            $map[$key] = $created->id;
        }
        return $map;
    }

    /**
     * Resolve thuoc_tinh_ids trong bien_the: chuyển id=null thành id thực từ newAttrMap.
     *
     * Flow:
     * 1. Với mỗi variant, duyệt tất cả giá trị thuộc tính trong DB cùng nhóm cha
     * 2. Tìm id của giá trị có label khớp → dùng id đó
     * 3. Nếu không tìm thấy, dùng newAttrMap (đã tạo ở processNewAttributes)
     *
     * @param  array  $bienThe   Mảng variants
     * @param  array  $newAttrMap  Map ['group|label' => id]
     * @return array  Mảng variants đã được resolve thuoc_tinh_ids
     */
    protected function resolveThuocTinhIdsWithNew(array $bienThe, array $newAttrMap): array
    {
        foreach ($bienThe as &$variant) {
            $rawIds = $variant['thuoc_tinh_ids'] ?? null;
            if (blank($rawIds)) continue;

            $idStrings = is_array($rawIds) ? $rawIds : explode(',', $rawIds);
            $resolved = [];

            foreach ($idStrings as $idStr) {
                $idStr = trim($idStr);
                if ($idStr === '' || $idStr === 'null') continue;

                if (is_numeric($idStr)) {
                    $resolved[] = (int) $idStr;
                }
            }

            $variant['thuoc_tinh_ids'] = !empty($resolved) ? implode(',', $resolved) : null;
        }
        unset($variant);

        return $bienThe;
    }

    /**
     * Đồng bộ đơn vị quy đổi cho một variant (update).
     * Chỉ lưu đơn vị có tỷ lệ > 1. Đơn vị cơ bản (ty_le = 1) KHÔNG được lưu vào bảng don_vi_quy_doi.
     */
    protected function syncUnits(int $variantId, array $units, ?string $image = null): void
    {
        // Neu khong co image upload, lay tu variant hien tai
        $variantImage = $image;
        if ($variantImage === null) {
            $variant = BienTheSanPham::find($variantId);
            if ($variant) {
                $variantImage = $variant->hinh_anh;
            }
        }

        if (empty($units)) {
            // Xóa hết units cũ
            $oldUnits = DonViQuyDoi::where('variant_id', $variantId)->get();
            foreach ($oldUnits as $u) {
                if ($u->hinh_anh && !str_starts_with($u->hinh_anh, 'http')) {
                    $this->deleteImageIfUnused($u->hinh_anh);
                }
                $u->delete();
            }
            return;
        }

        $incomingIds = [];
        $existingIds = DonViQuyDoi::where('variant_id', $variantId)->pluck('id')->toArray();

        foreach ($units as $unit) {
            // Bỏ qua đơn vị cơ bản (tỷ lệ = 1) — nó đã nằm trong bien_the_san_pham rồi
            if ((int)($unit['ty_le_quy_doi'] ?? 1) <= 1) {
                continue;
            }

            $unitId = $unit['id'] ?? null;

            if ($unitId && in_array($unitId, $existingIds)) {
                // Cập nhật đơn vị hiện có
                $existingUnit = DonViQuyDoi::find($unitId);
                if ($existingUnit) {
                    $existingUnit->update([
                        'ten_don_vi' => $unit['ten_don_vi'],
                        'ty_le_quy_doi' => (int)($unit['ty_le_quy_doi'] ?? 1),
                        'ma_hang' => $unit['ma_hang'] ?? null,
                        'ma_vach' => $unit['ma_vach'] ?? null,
                        'gia_von_quy_doi' => $unit['gia_von_quy_doi'] ?? 0,
                        'gia_ban_quy_doi' => $unit['gia_ban_quy_doi'] ?? 0,
                        'gia_ban_si' => $unit['gia_ban_si'] ?? null,
                        'hinh_anh' => $image !== null ? $image : $existingUnit->hinh_anh,
                        'la_don_vi_mac_dinh' => false,
                    ]);
                    $incomingIds[] = $unitId;
                }
            } else {
                // Tạo mới — thừa hưởng ảnh từ variant
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
                $incomingIds[] = $newUnit->id;
            }
        }

        // Xóa các đơn vị cũ không còn trong payload
        $toDelete = array_diff($existingIds, $incomingIds);
        if (!empty($toDelete)) {
            $oldUnits = DonViQuyDoi::where('variant_id', $variantId)->whereIn('id', $toDelete)->get();
            foreach ($oldUnits as $u) {
                if ($u->hinh_anh && !str_starts_with($u->hinh_anh, 'http')) {
                    $this->deleteImageIfUnused($u->hinh_anh);
                }
                $u->delete();
            }
        }
    }

}
