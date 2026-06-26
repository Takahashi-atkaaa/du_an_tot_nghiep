<?php

namespace App\Http\Controllers\admin\SanPham;

use App\Http\Controllers\Controller;
use App\Http\Requests\SanPham\StoreSanPhamRequest;
use App\Http\Requests\SanPham\UpdateSanPhamRequest;
use App\Models\DanhMucSanPham;
use App\Models\DonViSanPham;
use App\Models\DonViSanPhamSanPham;
use App\Models\ThuocTinhSanPham;
use App\Models\SanPham;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SanPhamController extends Controller
{
    public function index(Request $request): View
    {
        $keyword = $request->input('keyword');
        $danhMucId = $request->input('danh_muc');
        $trangThai = $request->filled('trang_thai') ? $request->boolean('trang_thai') : null;

        $danhMucs = DanhMucSanPham::query()
            ->orderBy('ten_danh_muc')
            ->get();

        $donVis = DonViSanPham::where('trang_thai', true)
            ->orderBy('ten_don_vi')
            ->get();

        $thuocTinhChas = ThuocTinhSanPham::whereNull('thuoc_tinh_cha_id')
            ->where('trang_thai', true)
            ->orderBy('ten_thuoc_tinh')
            ->get();

        $sanPhams = SanPham::query()
            ->with(['danhMuc', 'donVi', 'bienThe.thuocTinhs', 'bienThe.donVi'])
            ->sanPhamCha()
            ->when($keyword, function ($query, $keyword) {
                $query->searchByFields($keyword, ['ten_san_pham', 'ma_vach', 'ma_hang', 'thuong_hieu']);
            })
            ->when($danhMucId, function ($query, $danhMucId) {
                $query->where('id_danh_muc', $danhMucId);
            })
            ->when(! is_null($trangThai), function ($query) use ($trangThai) {
                $query->where('trang_thai', $trangThai);
            })
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        return view('admin_xem_truoc.san-pham', [
            'sanPhams' => $sanPhams,
            'danhMucs' => $danhMucs,
            'donVis' => $donVis,
            'thuocTinhChas' => $thuocTinhChas,
            'keyword' => $keyword,
            'danhMucId' => $danhMucId,
            'trangThai' => $request->input('trang_thai'),
        ]);
    }

    public function store(StoreSanPhamRequest $request)
    {
        $data = $request->validated();

        // Upload ảnh chung
        $imagePath = null;
        if ($request->hasFile('hinh_anh')) {
            $imagePath = $this->uploadImage($request->file('hinh_anh'));
        }

        // Upload ảnh biến thể
        $variantImages = [];
        if ($request->hasFile('bien_the')) {
            foreach ($request->file('bien_the') as $idx => $files) {
                if (isset($files['hinh_anh']) && $files['hinh_anh']) {
                    $variantImages[$idx] = $this->uploadImage($files['hinh_anh']);
                }
            }
        }

        // Validate mã vạch biến thể không trùng
        $barcodes = array_filter(array_column($data['bien_the'] ?? [], 'ma_vach'));
        if (collect($barcodes)->duplicates()->isNotEmpty()) {
            return redirect()->back()->withInput()->withErrors(['bien_the' => 'Mã vạch biến thể không được trùng nhau.']);
        }

        if (!empty($barcodes) && SanPham::whereIn('ma_vach', $barcodes)->exists()) {
            return redirect()->back()->withInput()->withErrors(['bien_the' => 'Một hoặc nhiều mã vạch biến thể đã tồn tại trong hệ thống.']);
        }

        // Tạo/cập nhật đơn vị cơ bản
        $baseUnit = $this->findOrCreateDonVi($data['don_vi_text'] ?? 'Cái', 1);

        $bienThe = $data['bien_the'] ?? [];

        return DB::transaction(function () use ($data, $bienThe, $baseUnit, $request, $imagePath, $variantImages) {
            if (empty($bienThe)) {
                // Không có biến thể → tạo 1 sản phẩm duy nhất
                $sanPham = SanPham::create([
                    'id_danh_muc'       => $data['id_danh_muc'],
                    'ten_san_pham'      => $data['ten_san_pham'],
                    'ma_hang'           => $this->generateUniqueMaHang(),
                    'ma_vach'           => !empty($data['ma_vach']) ? $data['ma_vach'] : $this->generateUniqueMaVach(),
                    'thuong_hieu'       => $data['thuong_hieu'] ?? null,
                    'gia_von'           => $data['gia_von'] ?? 0,
                    'gia_ban'           => 0,
                    'so_luong_ton_kho'  => 0,
                    'dinh_muc_toi_thieu' => $data['dinh_muc_toi_thieu'] ?? 0,
                    'mo_ta'             => $data['mo_ta'] ?? null,
                    'id_don_vi'         => $baseUnit->id,
                    'hinh_anh'          => $imagePath,
                    'trang_thai'        => $data['trang_thai'] ?? true,
                    'la_san_pham_cha'   => true,
                ]);
            } else {
                // Có biến thể → tạo 1 sản phẩm CHA, rồi tạo biến thể gán cha
                $sanPhamCha = SanPham::create([
                    'id_danh_muc'       => $data['id_danh_muc'],
                    'ten_san_pham'      => $data['ten_san_pham'],
                    'ma_hang'           => $this->generateUniqueMaHang(),
                    'ma_vach'           => !empty($data['ma_vach']) ? $data['ma_vach'] : $this->generateUniqueMaVach(),
                    'thuong_hieu'       => $data['thuong_hieu'] ?? null,
                    'gia_von'           => $data['gia_von'] ?? 0,
                    'gia_ban'           => 0,
                    'so_luong_ton_kho'  => 0,
                    'dinh_muc_toi_thieu' => $data['dinh_muc_toi_thieu'] ?? 0,
                    'mo_ta'             => $data['mo_ta'] ?? null,
                    'id_don_vi'         => $baseUnit->id,
                    'hinh_anh'          => $imagePath,
                    'trang_thai'        => $data['trang_thai'] ?? true,
                    'la_san_pham_cha'   => true,
                ]);

                foreach ($bienThe as $idx => $variant) {
                    $variantImage = $variantImages[$idx] ?? $imagePath;

                    $bienTheSp = SanPham::create([
                        'id_danh_muc'       => $data['id_danh_muc'],
                        'ten_san_pham'      => $variant['ten_day_du'] ?? $data['ten_san_pham'],
                        'ma_hang'           => $this->generateUniqueMaHang(),
                        'ma_vach'           => !empty($variant['ma_vach']) ? $variant['ma_vach'] : $this->generateUniqueMaVach(),
                        'thuong_hieu'       => $data['thuong_hieu'] ?? null,
                        'gia_von'           => $data['gia_von'] ?? 0,
                        'gia_ban'           => $variant['gia_ban'] ?? 0,
                        'so_luong_ton_kho'  => $variant['so_luong'] ?? 0,
                        'dinh_muc_toi_thieu' => $data['dinh_muc_toi_thieu'] ?? 0,
                        'mo_ta'             => $data['mo_ta'] ?? null,
                        'id_don_vi'         => $baseUnit->id,
                        'hinh_anh'          => $variantImage,
                        'trang_thai'        => $data['trang_thai'] ?? true,
                        'san_pham_cha_id'   => $sanPhamCha->id,
                        'la_san_pham_cha'   => false,
                    ]);

                    // Attach thuộc tính vào pivot
                    if (!empty($variant['thuoc_tinh_ids'])) {
                        $ids = array_map('intval', explode(',', $variant['thuoc_tinh_ids']));
                        $bienTheSp->thuocTinhs()->attach(array_filter($ids));
                    }
                }
            }

            // Lưu đơn vị bán hàng (hang_cung_loai) — mỗi đơn vị tạo 1 SanPham riêng
            if (!empty($data['hang_cung_loai'])) {
                foreach ($data['hang_cung_loai'] as $idx => $unit) {
                    $tenDonVi = trim($unit['ten_don_vi'] ?? '');
                    $soLuongQuyDoi = (int)($unit['so_luong_quy_doi'] ?? 1);

                    $donVi = DonViSanPham::firstOrCreate(
                        ['ten_don_vi' => $tenDonVi],
                        ['so_luong_san_pham_trong_don_vi' => $soLuongQuyDoi, 'trang_thai' => true]
                    );

                    $unitImgPath = null;
                    if ($request->hasFile("hang_cung_loai.{$idx}.hinh_anh")) {
                        $unitImgPath = $this->uploadImage($request->file("hang_cung_loai.{$idx}.hinh_anh"), 'uploads/don-vi');
                    }

                    SanPham::create([
                        'id_danh_muc'       => $data['id_danh_muc'],
                        'ten_san_pham'      => $data['ten_san_pham'] . ' - ' . $tenDonVi,
                        'ma_hang'           => $this->generateUniqueMaHang(),
                        'ma_vach'           => !empty($unit['ma_vach']) ? trim($unit['ma_vach']) : $this->generateUniqueMaVach(),
                        'thuong_hieu'       => $data['thuong_hieu'] ?? null,
                        'gia_von'           => $data['gia_von'] ?? 0,
                        'gia_ban'           => (float)($unit['gia_ban_le'] ?? 0),
                        'so_luong_ton_kho'  => 0,
                        'dinh_muc_toi_thieu' => $data['dinh_muc_toi_thieu'] ?? 0,
                        'mo_ta'             => $data['mo_ta'] ?? null,
                        'id_don_vi'         => $donVi->id,
                        'hinh_anh'          => $unitImgPath ?? $imagePath,
                        'trang_thai'        => $data['trang_thai'] ?? true,
                        'san_pham_cha_id'   => $sanPham->id,
                        'la_san_pham_cha'   => false,
                    ]);
                }
            }

            return redirect()->back()->with('success', 'Đã thêm sản phẩm mới.');
        });
    }

    public function edit($id)
    {
        $sanPham = SanPham::with(['danhMuc', 'donVi', 'bienThe.thuocTinhs', 'bienThe.donVi'])->findOrFail($id);
        $danhMucs = DanhMucSanPham::orderBy('ten_danh_muc')->get();
        $donVis = DonViSanPham::where('trang_thai', true)->orderBy('ten_don_vi')->get();
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

    public function update(UpdateSanPhamRequest $request, $id)
    {
        $sanPham = SanPham::with('thuocTinhs')->findOrFail($id);
        $data = $request->validated();
        $isParent = (bool) $sanPham->la_san_pham_cha;

        // Upload ảnh mới
        $oldImagePath = $sanPham->hinh_anh;
        $imagePath = $oldImagePath;
        if ($request->hasFile('hinh_anh')) {
            $imagePath = $this->uploadImage($request->file('hinh_anh'));
            if ($oldImagePath && !str_starts_with($oldImagePath, 'http')) {
                $this->deleteProductImageIfUnused($oldImagePath, $sanPham->id);
            }
        }

        // Xử lý đơn vị
        $baseUnit = $this->findOrCreateDonVi($data['don_vi_text'] ?? $sanPham->donVi->ten_don_vi ?? 'Cái', 1);

        return DB::transaction(function () use ($sanPham, $data, $request, $imagePath, $baseUnit) {
            $isParent = (bool) $sanPham->la_san_pham_cha;

            if ($isParent) {
            // === SẢN PHẨM CHA ===
            $sanPham->update([
                'id_danh_muc' => $data['id_danh_muc'],
                'ten_san_pham' => $data['ten_san_pham'],
                'thuong_hieu' => $data['thuong_hieu'] ?? null,
                'gia_von' => $data['gia_von'] ?? 0,
                'dinh_muc_toi_thieu' => $data['dinh_muc_toi_thieu'] ?? 0,
                'mo_ta' => $data['mo_ta'] ?? null,
                'id_don_vi' => $baseUnit->id,
                'hinh_anh' => $imagePath,
                'trang_thai' => $data['trang_thai'] ?? true,
            ]);

            // === CRUD BIẾN THỂ ===
            $incomingIds = [];
            $variantImages = [];

            // Upload ảnh biến thể
            if ($request->hasFile('bien_the')) {
                foreach ($request->file('bien_the') as $idx => $files) {
                    if (isset($files['hinh_anh']) && $files['hinh_anh']) {
                        $variantImages[$idx] = $this->uploadImage($files['hinh_anh']);
                    }
                }
            }

            foreach ($data['bien_the'] ?? [] as $idx => $variant) {
                $existingId = $variant['id'] ?? null;

                if ($existingId && $sanPham->bienThe->contains($existingId)) {
                    // Cập nhật biến thể hiện có
                    $bienThe = SanPham::find($existingId);
                    $oldVariantImg = $bienThe->hinh_anh;
                    $newVariantImg = $variantImages[$idx] ?? $imagePath;

                    $bienThe->update([
                        'ten_san_pham' => $variant['ten_day_du'] ?? $data['ten_san_pham'],
                        'ma_vach' => !empty($variant['ma_vach']) ? $variant['ma_vach'] : null,
                        'gia_von' => $data['gia_von'] ?? 0,
                        'gia_ban' => $variant['gia_ban'] ?? 0,
                        'so_luong_ton_kho' => $variant['so_luong'] ?? 0,
                        'dinh_muc_toi_thieu' => $data['dinh_muc_toi_thieu'] ?? 0,
                        'mo_ta' => $data['mo_ta'] ?? null,
                        'id_don_vi' => $baseUnit->id,
                        'hinh_anh' => $newVariantImg,
                        'trang_thai' => $data['trang_thai'] ?? true,
                    ]);

                    if ($oldVariantImg && $oldVariantImg !== $newVariantImg && !str_starts_with($oldVariantImg, 'http')) {
                        $this->deleteProductImageIfUnused($oldVariantImg, $bienThe->id);
                    }

                    $incomingIds[] = $existingId;
                } else {
                    // Tạo biến thể mới
                    $bienThe = SanPham::create([
                        'id_danh_muc' => $data['id_danh_muc'],
                        'ten_san_pham' => $variant['ten_day_du'] ?? $data['ten_san_pham'],
                        'ma_hang' => $this->generateUniqueMaHang(),
                        'ma_vach' => !empty($variant['ma_vach']) ? $variant['ma_vach'] : $this->generateUniqueMaVach(),
                        'thuong_hieu' => $data['thuong_hieu'] ?? null,
                        'gia_von' => $data['gia_von'] ?? 0,
                        'gia_ban' => $variant['gia_ban'] ?? 0,
                        'so_luong_ton_kho' => $variant['so_luong'] ?? 0,
                        'dinh_muc_toi_thieu' => $data['dinh_muc_toi_thieu'] ?? 0,
                        'mo_ta' => $data['mo_ta'] ?? null,
                        'id_don_vi' => $baseUnit->id,
                        'hinh_anh' => $variantImages[$idx] ?? $imagePath,
                        'trang_thai' => $data['trang_thai'] ?? true,
                        'san_pham_cha_id' => $sanPham->id,
                        'la_san_pham_cha' => false,
                    ]);

                    $incomingIds[] = $bienThe->id;
                }

                // Sync thuộc tính
                $lastId = $existingId ?? ($bienThe->id ?? null);
                if ($lastId && !empty($variant['thuoc_tinh_ids'])) {
                    $ids = array_map('intval', explode(',', $variant['thuoc_tinh_ids']));
                    SanPham::find($lastId)->thuocTinhs()->sync(array_filter($ids));
                }
            }

            // Refresh để lấy lại danh sách biến thể (có thể đã tạo mới ở trên)
            $sanPham->refresh();
            $sanPham->load('bienThe.thuocTinhs');

            // Xóa biến thể bị loại bỏ (bị xóa khỏi form)
            foreach ($sanPham->bienThe as $bienThe) {
                if (!in_array($bienThe->id, $incomingIds)) {
                    $variantImg = $bienThe->hinh_anh;
                    $bienThe->thuocTinhs()->detach();
                    $bienThe->delete();
                    if ($variantImg && !str_starts_with($variantImg, 'http')) {
                        $this->deleteProductImageIfUnused($variantImg, $bienThe->id);
                    }
                }
            }

            // Xóa biến thể bị đánh dấu xóa từ form
            $deletedIds = $request->input('deleted_variant_ids', []);
            foreach ($deletedIds as $delId) {
                $bienThe = SanPham::find($delId);
                if ($bienThe && $bienThe->san_pham_cha_id === $sanPham->id) {
                    $variantImg = $bienThe->hinh_anh;
                    $bienThe->thuocTinhs()->detach();
                    $bienThe->delete();
                    if ($variantImg && !str_starts_with($variantImg, 'http')) {
                        $this->deleteProductImageIfUnused($variantImg, $bienThe->id);
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
                $sanPham->thuocTinhs()->detach();
            }
        }

        return redirect(url('admin/san-pham'))->with('success', 'Cập nhật sản phẩm thành công.');
    });
}

    public function destroy(int $id): RedirectResponse
    {
        $sanPham = SanPham::findOrFail($id);
        $imagePath = $sanPham->hinh_anh;

        // Xóa biến thể trước (nếu là sản phẩm cha)
        foreach ($sanPham->bienThe as $bienThe) {
            if ($bienThe->hinh_anh && !str_starts_with($bienThe->hinh_anh, 'http')) {
                $this->deleteProductImageIfUnused($bienThe->hinh_anh, $bienThe->id);
            }
            $bienThe->delete();
        }

        $sanPham->delete();

        if ($imagePath) {
            $this->deleteProductImageIfUnused($imagePath);
        }

        return redirect()->route('san-pham.index')
            ->with('success', 'Đã xóa sản phẩm.');
    }

    public function bulkAction(Request $request): RedirectResponse
    {
        $request->validate([
            'action' => 'required|in:delete,activate,deactivate',
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer|exists:san_pham,id',
        ]);

        $ids = $request->input('ids');
        $action = $request->input('action');

        switch ($action) {
            case 'delete':
                $sanPhams = SanPham::whereIn('id', $ids)->get();
                foreach ($sanPhams as $sanPham) {
                    // Xóa biến thể nếu là sản phẩm cha
                    if ($sanPham->la_san_pham_cha) {
                        foreach ($sanPham->bienThe as $bienThe) {
                            if ($bienThe->hinh_anh && !str_starts_with($bienThe->hinh_anh, 'http')) {
                                $this->deleteProductImageIfUnused($bienThe->hinh_anh, $bienThe->id);
                            }
                            $bienThe->delete();
                        }
                    }
                    if ($sanPham->hinh_anh && !str_starts_with($sanPham->hinh_anh, 'http')) {
                        $this->deleteProductImageIfUnused($sanPham->hinh_anh);
                    }
                    $sanPham->delete();
                }
                $message = 'Đã xóa ' . count($ids) . ' sản phẩm.';
                break;

            case 'activate':
                SanPham::whereIn('id', $ids)->update(['trang_thai' => true]);
                $message = 'Đã bật trạng thái cho ' . count($ids) . ' sản phẩm.';
                break;

            case 'deactivate':
                SanPham::whereIn('id', $ids)->update(['trang_thai' => false]);
                $message = 'Đã tắt trạng thái cho ' . count($ids) . ' sản phẩm.';
                break;
        }

        return redirect()->route('san-pham.index')->with('success', $message);
    }

    public function trash(Request $request): View
    {
        $keyword = $request->input('keyword');

        $trashed = SanPham::onlyTrashed()
            ->with(['danhMuc', 'donVi'])
            ->when($keyword, function ($query, $keyword) {
                $query->searchByFields($keyword, ['ten_san_pham', 'ma_vach', 'ma_hang', 'thuong_hieu']);
            })
            ->orderByDesc('deleted_at')
            ->paginate(10)
            ->withQueryString();

        return view('admin_xem_truoc.san-pham-thung-rac', [
            'trashed' => $trashed,
            'keyword' => $keyword,
        ]);
    }

    public function restore(int $id): RedirectResponse
    {
        $sanPham = SanPham::onlyTrashed()->findOrFail($id);
        $sanPham->restore();

        return redirect()->route('san-pham.trash')->with('success', 'Đã khôi phục sản phẩm "' . $sanPham->ten_san_pham . '".');
    }

    public function forceDelete(int $id): RedirectResponse
    {
        $sanPham = SanPham::onlyTrashed()->findOrFail($id);
        $imagePath = $sanPham->hinh_anh;

        $sanPham->forceDelete();

        if ($imagePath && !str_starts_with($imagePath, 'http')) {
            $fullPath = public_path($imagePath);
            if (is_file($fullPath)) {
                unlink($fullPath);
            }
        }

        return redirect()->route('san-pham.trash')->with('success', 'Đã xóa vĩnh viễn sản phẩm.');
    }

    protected function uploadDirectory(): string
    {
        $path = public_path('uploads/san-pham');

        if (! is_dir($path)) {
            mkdir($path, 0755, true);
        }

        return $path;
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

    protected function deleteProductImageIfUnused(?string $imagePath, ?int $excludeId = null): void
    {
        if (blank($imagePath) || str_starts_with($imagePath, 'http')) {
            return;
        }

        $query = SanPham::query()->where('hinh_anh', $imagePath);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        if ($query->exists()) {
            return;
        }

        $fullPath = public_path($imagePath);

        if (is_file($fullPath)) {
            unlink($fullPath);
        }
    }

    protected function findOrCreateDonVi(string $tenDonVi, int $soLuong): DonViSanPham
    {
        return DonViSanPham::firstOrCreate([
            'ten_don_vi' => trim($tenDonVi),
        ], [
            'so_luong_san_pham_trong_don_vi' => $soLuong,
            'trang_thai' => true,
        ]);
    }
    protected function generateUniqueMaHang(): string
    {
        do {
            $code = 'MH' . strtoupper(Str::random(6));
        } while (SanPham::where('ma_hang', $code)->exists());

        return $code;
    }

    protected function generateUniqueMaVach(): string
    {
        do {
            $code = 'BV' . strtoupper(Str::random(8));
        } while (SanPham::where('ma_vach', $code)->exists());

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
            'Mã SP',
            'Tên sản phẩm',
            'Danh mục',
            'Thương hiệu',
            'Mã vạch',
            'Đơn vị',
            'Giá vốn',
            'Giá bán',
            'Tồn kho',
            'Định mức tối thiểu',
            'Mô tả',
            'Trạng thái',
            'Ngày tạo'
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
        fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF)); // BOM for UTF-8

        fputcsv($handle, $columns);
        foreach ($rows as $row) {
            fputcsv($handle, $row);
        }

        return response()->streamDownload(function () use ($handle) {
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
            'Tên sản phẩm *',
            'Danh mục *',
            'Thương hiệu',
            'Mã vạch',
            'Giá vốn',
            'Giá bán',
            'Tồn kho',
            'Định mức tối thiểu',
            'Mô tả',
            'Đơn vị'
        ];

        $sample = [
            ['Áo thun nam basic', 'Thời trang', 'Nike', '8934567890123', '150000', '250000', '50', '5', 'Áo thun nam chất liệu cotton 100%', 'Cái'],
            ['Quần jeans nữ', 'Thời trang', 'Levis', '8934567890124', '300000', '550000', '20', '3', 'Quần jeans nữ form regular', 'Cái'],
        ];

        if ($type === 'csv') {
            $output = fopen('php://output', 'w');
            fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($output, $columns);
            foreach ($sample as $row) {
                fputcsv($output, $row);
            }
            return response()->streamDownload(function () use ($output) {
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
