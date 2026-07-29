<?php

namespace App\Http\Controllers\admin\SanPham;

use App\Http\Controllers\Controller;
use App\Http\Requests\SanPham\StoreSanPhamRequest;
use App\Http\Requests\SanPham\UpdateSanPhamRequest;
use App\Http\Requests\SanPham\ImportSanPhamRequest;
use App\Models\DanhMucDonVi;
use App\Models\DanhMucSanPham;
use App\Models\Product;
use App\Models\BienTheSanPham;
use App\Models\DonViQuyDoi;
use App\Models\ThuocTinhSanPham;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
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

        // Lấy đơn vị chung (bảng danh_muc_don_vi)
        $danhMucDonVis = DanhMucDonVi::where('trang_thai', true)
            ->orderBy('ten_don_vi')
            ->orderBy('so_luong_san_pham_trong_don_vi')
            ->get();

        // Payload cho Vue (đơn vị chuẩn - dùng trong form)
        $unitsPayload = $danhMucDonVis->map(fn($u) => [
            'id'   => $u->id,
            'name' => $u->ten_hien_thi,  // "Thùng 24"
            'qty'  => $u->so_luong_san_pham_trong_don_vi,
        ])->values()->all();

        return view('admin_xem_truoc.san-pham.index', [
            'sanPhams' => $sanPhams,
            'danhMucs' => $danhMucs,
            'thuocTinhChas' => $thuocTinhChas,
            'donViMacDinhs' => $danhMucDonVis,
            'unitsPayload' => $unitsPayload,
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

            // ============================================================
            // YÊU CẦU 1: KIỂM TRA TRÙNG LẶP BIẾN THỂ (BACKEND)
            // ============================================================
            // 1a. Kiểm tra trùng lặp TRONG CHÍNH payload gửi lên
            $this->checkVariantDuplicatesInPayload($data['bien_the'] ?? [], 'create');
            // 1b. Kiểm tra trùng lặp với các biến thể ĐÃ TỒN TẠI trong DB cho sản phẩm này
            // (Trường hợp update, cần truyền product_id; trong store thì là sản phẩm mới nên bỏ qua)

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

                // ============================================================
                // FIX Gom-nhom: thay vòng lặp cũ bằng logic Gom nhóm theo thuoc_tinh_ids.
                // Trước đây: mỗi dòng trong gridData được tạo thành 1 BienTheSanPham
                // riêng biệt. Khi user tạo sản phẩm có ma trận (VD: Màu × Size × Đơn vị),
                // 2 dòng cùng thuộc tính nhưng khác đơn vị (Đen-38 "cái" vs Đen-38 "Bao")
                // không tạo ra conversion unit vì backend không biết chúng thuộc cùng variant.
                //
                // Logic mới: gom các dòng cùng thuoc_tinh_ids thành 1 nhóm.
                // Trong nhóm, dòng ty_le=1 → base variant; các dòng ty_le>1 → DonViQuyDoi
                // gắn vào variant đó.
                // ============================================================
                $groupedVariants = [];
                foreach ($bienThe as $idx => $item) {
                    // Gắn ảnh upload (nếu có) cho mỗi item
                    if (!isset($item['_variantImage'])) {
                        $item['_variantImage'] = $variantImages[$idx] ?? $imagePath;
                    }
                    // Dùng thuoc_tinh_ids làm key gom nhóm; 'no_attr' cho sp đơn giản
                    $attrKey = !empty($item['thuoc_tinh_ids']) ? $item['thuoc_tinh_ids'] : 'no_attr';
                    if (!isset($groupedVariants[$attrKey])) {
                        $groupedVariants[$attrKey] = [];
                    }
                    $groupedVariants[$attrKey][] = $item;
                }

                foreach ($groupedVariants as $attrKey => $groupItems) {
                    $baseVariantData = null;
                    $conversionUnitsData = [];

                    // Tách base (ty_le=1) và conversion (ty_le>1) trong cùng nhóm
                    foreach ($groupItems as $item) {
                        $tyLe = isset($item['ty_le'])
                            ? (int) $item['ty_le']
                            : (isset($item['so_luong_san_pham_trong_don_vi'])
                                ? (int) $item['so_luong_san_pham_trong_don_vi']
                                : 1);

                        if ($tyLe === 1) {
                            $baseVariantData = $item;
                        } else {
                            $conversionUnitsData[] = $item;
                        }
                    }

                    // Fallback: nếu không tìm được base (tất cả ty_le>1), lấy dòng đầu
                    if (!$baseVariantData && count($groupItems) > 0) {
                        $baseVariantData = $groupItems[0];
                        $conversionUnitsData = array_slice($groupItems, 1);
                    }

                    if (!$baseVariantData) {
                        continue;
                    }

                    // Chuẩn bị mảng ID thuộc tính
                    $thuocTinhIds = null;
                    if (!empty($baseVariantData['thuoc_tinh_ids'])) {
                        $ids = array_map('intval', explode(',', $baseVariantData['thuoc_tinh_ids']));
                        $thuocTinhIds = array_values(array_filter($ids));
                    }

                    // 3. Lưu Biến thể CHA (Base Variant)
                    $createdVariant = BienTheSanPham::create([
                        'product_id' => $product->id,
                        'ten_bien_the' => $baseVariantData['ten_bien_the'] ?? null,
                        // Nếu nhóm không có thuộc tính → biến thể là đơn vị gốc thuần túy
                        'la_don_vi' => ($attrKey === 'no_attr') ? 1 : 0,
                        'ten_don_vi' => $baseVariantData['ten_don_vi'] ?? null,
                        'ma_hang' => !empty($baseVariantData['ma_hang'])
                            ? $baseVariantData['ma_hang']
                            : $this->generateUniqueMaHang(),
                        'ma_vach' => !empty($baseVariantData['ma_vach'])
                            ? $baseVariantData['ma_vach']
                            : $this->generateUniqueMaVach(),
                        'gia_von' => $baseVariantData['gia_von'] ?? 0,
                        'gia_ban' => $baseVariantData['gia_ban'] ?? 0,
                        'so_luong_ton' => $baseVariantData['so_luong_ton'] ?? 0,
                        'dinh_muc_toi_thieu' => $baseVariantData['dinh_muc_toi_thieu'] ?? 0,
                        'hinh_anh' => $baseVariantData['_variantImage'] ?? $imagePath,
                        'thuoc_tinh_ids' => $thuocTinhIds,
                        'trang_thai' => $data['trang_thai'] ?? true,
                    ]);

                    // 4. Lưu các Đơn vị quy đổi (Gắn Khóa ngoại vào Biến thể CHA)
                    // a) Các dòng cùng nhóm thuộc tính có ty_le > 1
                    foreach ($conversionUnitsData as $unitItem) {
                        $tyLeQuyDoi = isset($unitItem['ty_le'])
                            ? (int) $unitItem['ty_le']
                            : (isset($unitItem['so_luong_san_pham_trong_don_vi'])
                                ? (int) $unitItem['so_luong_san_pham_trong_don_vi']
                                : 2);

                        DonViQuyDoi::create([
                            'variant_id' => $createdVariant->id, // BẮT BUỘC KHÓA NGOẠI
                            'product_id' => $product->id,
                            'don_vi_chuan_id' => null,
                            'ten_don_vi' => $unitItem['ten_don_vi_bien_the'] ?? ($unitItem['ten_don_vi'] ?? ''),
                            'so_luong_san_pham_trong_don_vi' => $tyLeQuyDoi,
                            'ma_hang' => !empty($unitItem['ma_hang'])
                                ? $unitItem['ma_hang']
                                : $this->generateUniqueMaHang(),
                            'ma_vach' => $unitItem['ma_vach'] ?? null,
                            'gia_von_quy_doi' => $unitItem['gia_von'] ?? ($unitItem['gia_von_quy_doi'] ?? 0),
                            'gia_ban_quy_doi' => $unitItem['gia_ban'] ?? ($unitItem['gia_ban_quy_doi'] ?? 0),
                            'gia_ban_si' => $unitItem['gia_ban_si'] ?? null,
                            'hinh_anh' => $unitItem['_variantImage'] ?? $imagePath,
                            'la_don_vi_mac_dinh' => false,
                        ]);
                    }

                    // b) Các đơn vị quy đổi khai báo trong units[] của base row (giữ logic cũ)
                    if (!empty($baseVariantData['units'])) {
                        foreach ($baseVariantData['units'] as $unit) {
                            if ((int) ($unit['so_luong_san_pham_trong_don_vi'] ?? 1) <= 1) {
                                continue;
                            }
                            DonViQuyDoi::create([
                                'variant_id' => $createdVariant->id,
                                'product_id' => $product->id,
                                'don_vi_chuan_id' => $unit['don_vi_chuan_id'] ?? null,
                                'ten_don_vi' => $unit['ten_don_vi'],
                                'so_luong_san_pham_trong_don_vi' => (int) ($unit['so_luong_san_pham_trong_don_vi'] ?? 1),
                                'ma_hang' => $unit['ma_hang'] ?? $this->generateUniqueMaHang(),
                                'ma_vach' => $unit['ma_vach'] ?? null,
                                'gia_von_quy_doi' => $unit['gia_von_quy_doi'] ?? 0,
                                'gia_ban_quy_doi' => $unit['gia_ban_quy_doi'] ?? 0,
                                'gia_ban_si' => $unit['gia_ban_si'] ?? null,
                                'hinh_anh' => $baseVariantData['_variantImage'] ?? $imagePath,
                                'la_don_vi_mac_dinh' => false,
                            ]);
                        }
                    }
                }
            }

            return redirect()->back()->with('success', 'Đã thêm sản phẩm mới.');
            });
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Lỗi store sản phẩm: ' . $e->getMessage());
            return redirect()->back()->withInput()->withErrors(['general' => 'Lỗi: ' . $e->getMessage()]);
        }
    }

    public function edit($id): View
    {
        $product = Product::with('variants')->findOrFail($id);

        // Lấy đơn vị quy đổi theo product_id (tái sử dụng cho tất cả variant)
        $productUnits = DonViQuyDoi::where('product_id', $id)
            ->orderBy('so_luong_san_pham_trong_don_vi')
            ->get();

        $danhMucs = DanhMucSanPham::orderBy('ten_danh_muc')->get();
        $thuocTinhChas = ThuocTinhSanPham::whereNull('thuoc_tinh_cha_id')
            ->where('trang_thai', true)
            ->orderBy('ten_thuoc_tinh')
            ->with(['thuocTinhCons' => fn($q) => $q->where('trang_thai', true)->orderBy('ten_thuoc_tinh')])
            ->get();

        // Lấy đơn vị chung (bảng danh_muc_don_vi)
        $danhMucDonVis = DanhMucDonVi::where('trang_thai', true)
            ->orderBy('ten_don_vi')
            ->orderBy('so_luong_san_pham_trong_don_vi')
            ->get();

        // Payload cho Vue (đơn vị chuẩn - dùng trong form)
        $unitsPayload = $danhMucDonVis->map(fn($u) => [
            'id'   => $u->id,
            'name' => $u->ten_hien_thi,
            'qty'  => $u->so_luong_san_pham_trong_don_vi,
        ])->values()->all();

        return view('admin_xem_truoc.san-pham.sua', [
            'product' => $product,
            'danhMucs' => $danhMucs,
            'thuocTinhChas' => $thuocTinhChas,
            'danhMucDonVis' => $danhMucDonVis,
            'productUnits' => $productUnits,
            'unitsPayload' => $unitsPayload,
        ]);
    }

    public function update(UpdateSanPhamRequest $request, int $id): RedirectResponse
    {
        $product = Product::with('variants.units')->findOrFail($id);
        $data = $request->validated();

        // ============================================================
        // YÊU CẦU 1: KIỂM TRA TRÙNG LẶP BIẾN THỂ (BACKEND)
        // ============================================================
        // 1a. Kiểm tra trùng lặp TRONG CHÍNH payload gửi lên
        $this->checkVariantDuplicatesInPayload($data['bien_the'] ?? [], 'update', $product->id);
        // 1b. Kiểm tra trùng lặp với các biến thể ĐÃ TỒN TẠI trong DB cho product này (loại trừ biến thể hiện tại đang sửa)

        // Xử lý thuộc tính MỚI: tạo vào DB trước, trả về map {label => id}
        $newAttrMap = $this->processNewAttributes($data['new_attributes'] ?? []);
        $data['bien_the'] = $this->resolveThuocTinhIdsWithNew($data['bien_the'] ?? [], $newAttrMap);

        // 1. Upload anh bien the (ngoai transaction de tranh rollback file)
        $variantImages = [];
        if ($request->hasFile('bien_the')) {
            foreach ($request->file('bien_the') as $idx => $files) {
                if (isset($files['hinh_anh']) && $files['hinh_anh']) {
                    $variantImages[$idx] = $this->uploadImage($files['hinh_anh']);
                }
            }
        }

        // Upload anh chinh cua san pham (truong hinh_anh tren form)
        $mainImage = null;
        if ($request->hasFile('hinh_anh')) {
            $mainImage = $this->uploadImage($request->file('hinh_anh'));
        }

        // Safety check: If bien_the is empty or not sent, preserve existing variants
        if (empty($data['bien_the']) && $product->variants()->exists()) {
            // No variants sent means user didn't change variants - preserve them
            // Just update product info and redirect
            $product->update([
                'id_danh_muc' => $data['id_danh_muc'],
                'ten_san_pham' => $data['ten_san_pham'],
                'thuong_hieu' => $data['thuong_hieu'] ?? null,
                'mo_ta' => $data['mo_ta'] ?? null,
                'trang_thai' => $data['trang_thai'] ?? true,
            ]);
            return redirect()->route('san-pham.index')->with('success', 'Đã cập nhật sản phẩm.');
        }

        $deletedVariantsList = [];
        $updatedVariantsList = [];
        $createdVariantsList = [];
 
        return DB::transaction(function () use ($product, $data, $request, $variantImages, $mainImage, &$deletedVariantsList, &$updatedVariantsList, &$createdVariantsList) {
            // 2. Cap nhat thong tin chung
            $product->update([
                'id_danh_muc' => $data['id_danh_muc'],
                'ten_san_pham' => $data['ten_san_pham'],
                'thuong_hieu' => $data['thuong_hieu'] ?? null,
                'mo_ta' => $data['mo_ta'] ?? null,
                'trang_thai' => $data['trang_thai'] ?? true,
            ]);

            $incomingVariantIds = [];

            // ============================================================
            // FIX Gom-nhom: gom các dòng theo thuoc_tinh_ids trước khi CRUD.
            // Mỗi nhóm có 1 base (ty_le=1) + N conversion (ty_le>1).
            // - Nếu base có existingId → update variant đó + xóa/sync đơn vị quy đổi
            // - Nếu base không có existingId → tạo mới + tạo các conversion gắn vào
            // ============================================================
            $groupedVariants = [];
            foreach ($data['bien_the'] ?? [] as $idx => $variant) {
                $variant['_variantImage'] = $variantImages[$idx] ?? null;
                $attrKey = !empty($variant['thuoc_tinh_ids']) ? $variant['thuoc_tinh_ids'] : 'no_attr';
                if (!isset($groupedVariants[$attrKey])) {
                    $groupedVariants[$attrKey] = [];
                }
                $groupedVariants[$attrKey][] = $variant;
            }

            foreach ($groupedVariants as $attrKey => $groupItems) {
                $baseVariantData = null;
                $conversionUnitsData = [];

                // BỌC THÉP: Chỉ nhận Base Variant 1 LẦN DUY NHẤT.
                // Nếu đã có Base rồi thì tất cả các dòng sau đều bị đẩy vào Quy đổi.
                foreach ($groupItems as $item) {
                    $isBase = isset($item['is_base']) ? (int)$item['is_base'] === 1 : false;
                    $tyLe = isset($item['ty_le']) ? (int)$item['ty_le'] : (isset($item['so_luong_san_pham_trong_don_vi']) ? (int)$item['so_luong_san_pham_trong_don_vi'] : 1);

                    if (($isBase || $tyLe === 1) && $baseVariantData === null) {
                        $baseVariantData = $item;
                    } else {
                        $conversionUnitsData[] = $item;
                    }
                }

                // Fallback: nếu không có base, lấy dòng đầu làm base
                if (!$baseVariantData && count($groupItems) > 0) {
                    $baseVariantData = $groupItems[0];
                    $conversionUnitsData = array_slice($groupItems, 1);
                }

                if (!$baseVariantData) {
                    continue;
                }

                $existingId = $baseVariantData['id'] ?? null;
                $variantImage = $baseVariantData['_variantImage'] ?? null;

                if ($existingId && $product->variants->contains('id', $existingId)) {
                    // Cập nhật variant hiện có
                    $existingVariant = BienTheSanPham::find($existingId);
                    // Ưu tiên: ảnh variant > ảnh chính > ảnh cũ của variant
                    $finalImage = $variantImage ?? $mainImage ?? $existingVariant->hinh_anh;

                    $thuocTinhIds = null;
                    if (!empty($baseVariantData['thuoc_tinh_ids'])) {
                        $ids = array_map('intval', explode(',', $baseVariantData['thuoc_tinh_ids']));
                        $thuocTinhIds = array_values(array_filter($ids));
                    }

                    // SAFETY: giữ nguyên la_don_vi theo DB
                    $laDonVi = $existingVariant->la_don_vi ? true : false;
                    // Khi la_don_vi=true: lấy ten_don_vi từ payload, fallback về giá trị cũ
                    // Khi la_don_vi=false: ten_don_vi phải là null
                    $tenDonVi = $laDonVi
                        ? (!empty($baseVariantData['ten_don_vi']) ? $baseVariantData['ten_don_vi'] : $existingVariant->ten_don_vi)
                        : null;
                    $tenBienThe = $laDonVi
                        ? null
                        : (!empty($baseVariantData['ten_bien_the']) ? $baseVariantData['ten_bien_the'] : $existingVariant->ten_bien_the);

                    $existingVariant->update([
                        'ten_bien_the' => $tenBienThe,
                        'la_don_vi' => $laDonVi,
                        'ten_don_vi' => $tenDonVi,
                        'ma_hang' => !empty($baseVariantData['ma_hang'])
                            ? $baseVariantData['ma_hang']
                            : $existingVariant->ma_hang,
                        'ma_vach' => !empty($baseVariantData['ma_vach'])
                            ? $baseVariantData['ma_vach']
                            : $existingVariant->ma_vach,
                        'gia_von' => isset($baseVariantData['gia_von'])
                            ? $baseVariantData['gia_von']
                            : $existingVariant->gia_von,
                        'gia_ban' => isset($baseVariantData['gia_ban'])
                            ? $baseVariantData['gia_ban']
                            : $existingVariant->gia_ban,
                        // BẮT BUỘC: Giữ nguyên tồn kho cũ khi update.
                        // Tồn kho chỉ thay đổi qua Phiếu Nhập/Xuất/Kiểm kho, không qua form sản phẩm.
                        'so_luong_ton' => $existingVariant->so_luong_ton,
                        'dinh_muc_toi_thieu' => isset($baseVariantData['dinh_muc_toi_thieu'])
                            ? $baseVariantData['dinh_muc_toi_thieu']
                            : $existingVariant->dinh_muc_toi_thieu,
                        'hinh_anh' => $finalImage,
                        'thuoc_tinh_ids' => $thuocTinhIds,
                        'trang_thai' => $baseVariantData['trang_thai'] ?? $existingVariant->trang_thai,
                    ]);

                    $updatedVariantsList[] = [
                        'id' => $existingId,
                        'ten_bien_the' => $tenBienThe,
                    ];
                    $incomingVariantIds[] = $existingId;
                } else {
                    // Tạo variant mới (base)
                    $thuocTinhIds = null;
                    if (!empty($baseVariantData['thuoc_tinh_ids'])) {
                        $ids = array_map('intval', explode(',', $baseVariantData['thuoc_tinh_ids']));
                        $thuocTinhIds = array_values(array_filter($ids));
                    }

                    $laDonVi = ($attrKey === 'no_attr') ? true : false;
                    $tenDonVi = $laDonVi
                        ? ($baseVariantData['ten_don_vi'] ?? null)
                        : null;
                    $tenBienThe = $laDonVi ? null : ($baseVariantData['ten_bien_the'] ?? null);
                    // Ưu tiên: ảnh variant > ảnh chính
                    $finalImage = $variantImage ?? $mainImage;

                    $newVariant = BienTheSanPham::create([
                        'product_id' => $product->id,
                        'ten_bien_the' => $tenBienThe,
                        'la_don_vi' => $laDonVi,
                        'ten_don_vi' => $tenDonVi,
                        'ma_hang' => !empty($baseVariantData['ma_hang'])
                            ? $baseVariantData['ma_hang']
                            : $this->generateUniqueMaHang(),
                        'ma_vach' => !empty($baseVariantData['ma_vach'])
                            ? $baseVariantData['ma_vach']
                            : $this->generateUniqueMaVach(),
                        'gia_von' => $baseVariantData['gia_von'] ?? 0,
                        'gia_ban' => $baseVariantData['gia_ban'] ?? 0,
                        'so_luong_ton' => $baseVariantData['so_luong_ton'] ?? 0,
                        'dinh_muc_toi_thieu' => $baseVariantData['dinh_muc_toi_thieu'] ?? 0,
                        'hinh_anh' => $finalImage,
                        'thuoc_tinh_ids' => $thuocTinhIds,
                        'trang_thai' => $baseVariantData['trang_thai'] ?? true,
                    ]);

                    $createdVariantsList[] = [
                        'id' => $newVariant->id,
                        'ten_bien_the' => $tenBienThe,
                        'thuoc_tinh_ids' => $thuocTinhIds,
                    ];
                    $incomingVariantIds[] = $newVariant->id;
                    $existingId = $newVariant->id;
                    // Cap nhat lai variantImage de truyen vao cac don vi quy doi
                    $variantImage = $finalImage;
                }

                // 4. Xử lý các đơn vị quy đổi (conversion) trong cùng nhóm thuộc tính
                // Kiểm tra xem unit có id hay không để quyết định update hay create
                $variantId = $existingId;

                foreach ($conversionUnitsData as $unitItem) {
                    $tyLeQuyDoi = isset($unitItem['ty_le'])
                        ? (int) $unitItem['ty_le']
                        : 2;
                    $unitId = $unitItem['id'] ?? null;

                    if ($unitId) {
                        // Cập nhật đơn vị quy đổi hiện có
                        $existingUnit = DonViQuyDoi::find($unitId);
                        if ($existingUnit) {
                            $existingUnit->update([
                                'ten_don_vi' => !empty($unitItem['ten_don_vi_bien_the'])
                                    ? $unitItem['ten_don_vi_bien_the']
                                    : (!empty($unitItem['ten_don_vi'])
                                        ? $unitItem['ten_don_vi']
                                        : $existingUnit->ten_don_vi),
                                'so_luong_san_pham_trong_don_vi' => $tyLeQuyDoi,
                                'ma_hang' => !empty($unitItem['ma_hang'])
                                    ? $unitItem['ma_hang']
                                    : $existingUnit->ma_hang,
                                'ma_vach' => !empty($unitItem['ma_vach'])
                                    ? $unitItem['ma_vach']
                                    : $existingUnit->ma_vach,
                                'gia_von_quy_doi' => isset($unitItem['gia_von'])
                                    ? $unitItem['gia_von']
                                    : $existingUnit->gia_von_quy_doi,
                                'gia_ban_quy_doi' => isset($unitItem['gia_ban'])
                                    ? $unitItem['gia_ban']
                                    : $existingUnit->gia_ban_quy_doi,
                                'hinh_anh' => $variantImage ?? $existingUnit->hinh_anh,
                            ]);
                        }
                    } else {
                        // Tạo đơn vị quy đổi mới
                        DonViQuyDoi::create([
                            'variant_id' => $variantId,
                            'product_id' => $product->id,
                            'don_vi_chuan_id' => null,
                            'ten_don_vi' => $unitItem['ten_don_vi_bien_the']
                                ?? ($unitItem['ten_don_vi'] ?? ''),
                            'so_luong_san_pham_trong_don_vi' => $tyLeQuyDoi,
                            'ma_hang' => !empty($unitItem['ma_hang'])
                                ? $unitItem['ma_hang']
                                : $this->generateUniqueMaHang(),
                            'ma_vach' => $unitItem['ma_vach'] ?? null,
                            'gia_von_quy_doi' => $unitItem['gia_von']
                                ?? ($unitItem['gia_von_quy_doi'] ?? 0),
                            'gia_ban_quy_doi' => $unitItem['gia_ban']
                                ?? ($unitItem['gia_ban_quy_doi'] ?? 0),
                            'hinh_anh' => $variantImage,
                            'la_don_vi_mac_dinh' => false,
                        ]);
                    }
                }

                // 4b. Sync các đơn vị khai báo trong units[] của base row (giữ logic cũ)
                if (!empty($baseVariantData['units'])) {
                    $this->syncUnits($variantId, $baseVariantData['units'], $variantImage, $product->id);
                }
            }

            // 5. Xoa variants bi loai bo
            $deletedIds = $request->input('deleted_variant_ids', []);

            $existingIdsInPayload = array_filter(array_map(
                fn($v) => $v['id'] ?? null,
                $data['bien_the'] ?? []
            ));

            // Safety: Nếu payload không có id variant nào (toàn bộ là tạo mới)
            // + DB đang có variant → KHÔNG xóa variant cũ
            // + không có deleted_variant_ids → fail-safe, return sớm
            if (empty($existingIdsInPayload) && empty($deletedIds) && $product->variants->isNotEmpty()) {
                \Log::warning('[Update SanPham] Safety: payload thiếu id variant, bỏ qua xóa', [
                    'product_id' => $product->id,
                    'existing_in_db' => $product->variants->pluck('id')->toArray(),
                ]);
                return redirect()->route('san-pham.index')->with('success', 'Đã cập nhật sản phẩm.');
            }

            // Chỉ xóa các variant DB không có trong incomingVariantIds khi:
            // 1) Có deleted_variant_ids (user chủ động xóa), HOẶC
            // 2) Số id hợp lệ trong payload KHỚP SỐ variant DB (an toàn để xóa các variant không có id)
            // Nếu số id < số variant DB → KHÔNG xóa (fallback theo index trong JS có thể gây nhân đôi)
            $safeToDelete = empty($deletedIds)
                ? count($existingIdsInPayload) === $product->variants->count()
                : true;

            \Log::info('[Update SanPham] id=' . $product->id, [
                'incoming_variant_ids' => $incomingVariantIds,
                'deleted_variant_ids' => $deletedIds,
                'existing_in_db' => $product->variants->pluck('id')->toArray(),
                'safe_to_delete' => $safeToDelete,
                'bien_the_count' => count($data['bien_the'] ?? []),
            ]);

            foreach ($product->variants as $variant) {
                $shouldDelete = in_array($variant->id, $deletedIds)
                    || ($safeToDelete && !empty($incomingVariantIds) && !in_array($variant->id, $incomingVariantIds));
                if ($shouldDelete) {
                    $deletedVariantsList[] = ['id' => $variant->id, 'ten' => $variant->ten_bien_the];
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

        // ============================================================
        // SELF-HEALING: Sync lại tồn kho từ chi_tiet_lo_hang lên bien_the_san_pham
        // (Fix lỗi hiển thị tồn kho = 0 do update trước đây ghi đè)
        // ============================================================
        try {
            $product->load('variants');
            foreach ($product->variants as $variant) {
                $realStock = \DB::table('chi_tiet_lo_hang')
                    ->where('variant_id', $variant->id)
                    ->sum('so_luong_ton');
                $variant->updateQuietly(['so_luong_ton' => (int) $realStock]);
            }
            \Log::info('[Update SanPham] Sync tồn kho thành công', ['product_id' => $id]);
        } catch (\Throwable $e) {
            \Log::warning('[Update SanPham] Lỗi sync tồn kho: ' . $e->getMessage(), ['product_id' => $id]);
        }

        \Log::info('[Update SanPham Result] product_id=' . $id, [
            'deleted_count' => count($deletedVariantsList),
            'updated_count' => count($updatedVariantsList),
            'created_count' => count($createdVariantsList),
            'deleted_variants' => $deletedVariantsList,
            'updated_variants' => $updatedVariantsList,
            'created_variants' => $createdVariantsList,
        ]);

        // Don anh cu cua san pham (neu co upload moi - nam ngoai transaction)
        if ($request->hasFile('hinh_anh') && $request->file('hinh_anh')->isValid() && $product->getOriginal('hinh_anh')
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

    /**
     * Khôi phục nhiều biến thể đã xóa (Bulk Restore)
     */
    public function bulkRestore(Request $request): RedirectResponse
    {
        $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer',
        ]);

        $ids = $request->input('ids', []);
        $restoredCount = 0;

        $variants = BienTheSanPham::onlyTrashed()->whereIn('id', $ids)->get();

        foreach ($variants as $variant) {
            $variant->restore();
            $restoredCount++;
        }

        return redirect()->route('san-pham.trash')
            ->with('success', "Đã khôi phục {$restoredCount} biến thể thành công.");
    }

    /**
     * Xóa vĩnh viễn nhiều biến thể (Bulk Force Delete)
     */
    public function bulkForceDelete(Request $request): RedirectResponse
    {
        $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer',
        ]);

        $ids = $request->input('ids', []);
        $deletedCount = 0;

        $variants = BienTheSanPham::onlyTrashed()->with('units')->whereIn('id', $ids)->get();

        foreach ($variants as $variant) {
            // Xóa ảnh đơn vị quy đổi
            foreach ($variant->units as $unit) {
                if ($unit->hinh_anh && !str_starts_with($unit->hinh_anh, 'http')) {
                    $fullPath = public_path($unit->hinh_anh);
                    if (is_file($fullPath)) {
                        unlink($fullPath);
                    }
                }
                $unit->forceDelete();
            }

            // Xóa ảnh biến thể
            if ($variant->hinh_anh && !str_starts_with($variant->hinh_anh, 'http')) {
                $fullPath = public_path($variant->hinh_anh);
                if (is_file($fullPath)) {
                    unlink($fullPath);
                }
            }

            $variant->forceDelete();
            $deletedCount++;
        }

        return redirect()->route('san-pham.trash')
            ->with('success', "Đã xóa vĩnh viễn {$deletedCount} biến thể.");
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
     * ============================================================
     * YÊU CẦU 1: KIỂM TRA TRÙNG LẶP BIẾN THỂ (BACKEND LARAVEL)
     * ============================================================
     * Logic:
     * 1. Trích xuất "chữ ký thuộc tính" (Attribute Signature) từ mỗi biến thể
     *    - Ghép các ID thuộc tính lại thành chuỗi đã sort (VD: "1,2,3")
     * 2. Kiểm tra độ trùng lặp trong chính payload gửi lên
     * 3. Kiểm tra trùng lặp với Database (nếu có productId)
     *
     * @param array $variants Mảng biến thể từ request
     * @param string $mode 'create' | 'update'
     * @param int|null $productId ID sản phẩm (chỉ dùng trong mode update)
     * @throws ValidationException Nếu phát hiện trùng lặp
     */
    protected function checkVariantDuplicatesInPayload(array $variants, string $mode = 'create', ?int $productId = null): void
    {
        if (empty($variants)) {
            return;
        }

        // Bước 1: Trích xuất Attribute Signature từ mỗi biến thể
        // Signature = chuỗi các thuộc tính đã sort + '__' + (ty_le hoặc ten_don_vi)
        // FIX: trước đây chỉ gom thuoc_tinh_ids, làm 2 dòng cùng thuộc tính
        // nhưng khác đơn vị (VD: Cam-hộp vs Cam-thùng) bị tính là trùng.
        // Giờ thêm ty_le vào signature để phân biệt đơn vị cơ bản vs quy đổi.
        $signatures = [];
        $variantIndices = []; // Lưu chỉ số dòng để thông báo lỗi

        foreach ($variants as $idx => $variant) {
            $rawIds = $variant['thuoc_tinh_ids'] ?? null;

            if (blank($rawIds)) {
                // Biến thể không có thuộc tính = signature rỗng
                $signature = '';
            } else {
                // Parse và sort các ID để đảm bảo "Size M - Color Đỏ" = "Color Đỏ - Size M"
                $ids = is_array($rawIds) ? $rawIds : explode(',', $rawIds);
                $ids = array_map('intval', array_filter(array_map('trim', $ids)));
                sort($ids, SORT_NUMERIC);
                $attrPart = implode(',', $ids);

                // Phân biệt đơn vị: ưu tiên ty_le, fallback ten_don_vi_bien_the / ten_don_vi
                $tyLe = isset($variant['ty_le']) && $variant['ty_le'] !== ''
                    ? (int) $variant['ty_le']
                    : 1;
                $signature = "{$attrPart}__ty_le={$tyLe}";
            }

            $signatures[] = $signature;
            $variantIndices[] = $idx + 1; // 1-indexed cho human-readable
        }

        // Bước 2: Kiểm tra trùng lặp TRONG payload gửi lên
        $uniqueSignatures = array_unique($signatures);

        if (count($uniqueSignatures) < count($signatures)) {
            // Có trùng lặp! Tìm các dòng bị trùng
            $seen = [];
            $duplicates = [];

            foreach ($signatures as $idx => $sig) {
                if ($sig === '') {
                    // Bỏ qua signature rỗng (biến thể không có thuộc tính)
                    continue;
                }
                if (isset($seen[$sig])) {
                    $duplicates[] = "Dòng {$variantIndices[$idx]}";
                } else {
                    $seen[$sig] = true;
                }
            }

            if (!empty($duplicates)) {
                $dupList = implode(', ', $duplicates);
                throw ValidationException::withMessages([
                    'variants' => ["Cảnh báo: Có biến thể bị trùng lặp thuộc tính tại {$dupList}. Vui lòng kiểm tra lại!"]
                ]);
            }
        }

        // Bước 3: Kiểm tra trùng lặp với DATABASE (nâng cao)
        // Chỉ kiểm tra khi có productId (mode update).
        // Sau logic Gom-nhom, mỗi "biến thể" trong DB = 1 base variant (ty_le=1) cho 1 nhóm thuộc tính.
        // Vậy ta chỉ cần check trùng base variant = (product_id, sorted thuoc_tinh_ids, ty_le=1).
        if ($productId !== null) {
            foreach ($variants as $idx => $variant) {
                $rawIds = $variant['thuoc_tinh_ids'] ?? null;
                if (blank($rawIds)) continue;

                // Lấy sorted attrIds
                $ids = is_array($rawIds) ? $rawIds : explode(',', $rawIds);
                $attrIds = array_map('intval', array_filter(array_map('trim', $ids)));
                sort($attrIds);
                if (empty($attrIds)) continue;

                // Lấy ty_le hiện tại của row trong payload
                $rowTyLe = isset($variant['ty_le']) && $variant['ty_le'] !== ''
                    ? (int) $variant['ty_le']
                    : 1;

                // Trong DB, base variant cho mỗi nhóm thuộc tính có ty_le ngầm = 1
                // (không lưu ty_le trên bien_the_san_pham). Vậy nếu row trong payload
                // là base (ty_le=1) → check trùng với BienTheSanPham cùng product + thuoc_tinh_ids
                // Nếu là conversion (ty_le>1) → KHÔNG check vì conversion được lưu vào DonViQuyDoi riêng.
                if ($rowTyLe > 1) {
                    continue;
                }

                // Tìm biến thể có cùng bộ thuộc tính trong DB
                $existingVariant = BienTheSanPham::where('product_id', $productId)
                    ->whereNotNull('thuoc_tinh_ids')
                    ->get()
                    ->first(function ($dbVariant) use ($attrIds) {
                        $dbIds = array_filter(array_map('intval', explode(',', $dbVariant->thuoc_tinh_ids)));
                        sort($dbIds);
                        return $dbIds === $attrIds;
                    });

                if ($existingVariant) {
                    // Loại trừ chính variant đang sửa: nếu payload row có id = $existingVariant->id thì OK
                    $existingRowId = $variant['id'] ?? null;
                    if ($existingRowId && (int) $existingRowId === (int) $existingVariant->id) {
                        continue;
                    }

                    $msg = "Dòng " . ($idx + 1) . ": Bộ thuộc tính này đã tồn tại trong sản phẩm (ID: {$existingVariant->id}). Vui lòng chọn bộ thuộc tính khác!";
                    throw ValidationException::withMessages([
                        'variants' => [$msg]
                    ]);
                }
            }
        }
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
     * Dùng product_id để có thể tái sử dụng cho tất cả variant cùng sản phẩm.
     *
     * Logic xóa an toàn: chỉ xóa các unit thuộc về variant hiện tại (variant_id = $variantId).
     * KHÔNG xóa các unit được share từ variant khác (variant_id khác).
     */
    protected function syncUnits(int $variantId, array $units, ?string $image = null, ?int $productId = null): void
    {
        // Neu khong co productId, lay tu variant hien tai
        if ($productId === null) {
            $variant = BienTheSanPham::find($variantId);
            $productId = $variant?->product_id;
        }

        // Neu khong co image upload, lay tu variant hien tai
        $variantImage = $image;
        if ($variantImage === null) {
            $variant = BienTheSanPham::find($variantId);
            if ($variant) {
                $variantImage = $variant->hinh_anh;
            }
        }

        // Query các unit thuộc VỀ variant hiện tại (không bao gồm unit được share từ variant khác)
        $existingIds = DonViQuyDoi::where('variant_id', $variantId)
            ->pluck('id')->toArray();

        if (empty($units)) {
            // Xóa các unit thuộc về variant hiện tại (KHÔNG động đến unit được share)
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

        foreach ($units as $unit) {
            // Bỏ qua đơn vị cơ bản (tỷ lệ = 1) — nó đã nằm trong bien_the_san_pham rồi
            if ((int)($unit['so_luong_san_pham_trong_don_vi'] ?? 1) <= 1) {
                continue;
            }

            $unitId = $unit['id'] ?? null;

            if ($unitId && in_array($unitId, $existingIds)) {
                // Cập nhật đơn vị hiện có
                $existingUnit = DonViQuyDoi::find($unitId);
                if ($existingUnit) {
                    $existingUnit->update([
                        'don_vi_chuan_id' => $unit['don_vi_chuan_id'] ?? null,
                        'ten_don_vi' => $unit['ten_don_vi'],
                        'so_luong_san_pham_trong_don_vi' => (int)($unit['so_luong_san_pham_trong_don_vi'] ?? 1),
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
                    'product_id' => $productId,
                    'don_vi_chuan_id' => $unit['don_vi_chuan_id'] ?? null,
                    'ten_don_vi' => $unit['ten_don_vi'],
                    'so_luong_san_pham_trong_don_vi' => (int)($unit['so_luong_san_pham_trong_don_vi'] ?? 1),
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

        // Xóa các đơn vị cũ KHÔNG còn trong payload VÀ thuộc về variant hiện tại
        // (KHÔNG xóa các unit được share từ variant khác)
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
