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

        $sanPhams = Product::with(['danhMuc', 'bienThe.units'])
            ->withSum('variants', 'so_luong_ton')
            ->withTongDaBan()
            ->whereNull('deleted_at')
            ->whereHas('variants', fn($q) => $q->whereNull('deleted_at'))
            ->when($keyword, fn($q) => $q
                ->where(fn($inner) => $inner
                    ->whereRaw('LOWER(ten_san_pham) LIKE ?', ["%".mb_strtolower($keyword)."%"])
                    ->orWhereHas('variants', fn($v) => $v
                        ->whereNull('deleted_at')
                        ->whereRaw('LOWER(ma_vach) LIKE ?', ["%".mb_strtolower($keyword)."%"])
                        ->orWhereRaw('LOWER(ma_hang) LIKE ?', ["%".mb_strtolower($keyword)."%"])
                        ->orWhereRaw('LOWER(ten_bien_the) LIKE ?', ["%".mb_strtolower($keyword)."%"])
                    )
                )
            )
            ->when($danhMucId, fn($q) => $q->where('id_danh_muc', $danhMucId))
            ->when(!is_null($trangThai), fn($q) => $q->where('trang_thai', $trangThai))
            ->orderByDesc('created_at')
            ->orderByDesc('id')
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

    public function create(): View
    {
        $danhMucs = DanhMucSanPham::query()->orderBy('ten_danh_muc')->get();

        $thuocTinhChas = ThuocTinhSanPham::whereNull('thuoc_tinh_cha_id')
            ->where('trang_thai', true)
            ->orderBy('ten_thuoc_tinh')
            ->with(['thuocTinhCons' => fn($q) => $q->where('trang_thai', true)->orderBy('ten_thuoc_tinh')])
            ->get();

        $danhMucDonVis = DanhMucDonVi::where('trang_thai', true)
            ->orderBy('ten_don_vi')
            ->orderBy('so_luong_san_pham_trong_don_vi')
            ->get();

        return view('admin_xem_truoc.san-pham.create', [
            'danhMucs' => $danhMucs,
            'thuocTinhChas' => $thuocTinhChas,
            'danhMucDonVis' => $danhMucDonVis,
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

            // Validate ma_vach khong trung
            $barcodes = array_filter(array_column($data['bien_the'] ?? [], 'ma_vach'));
            if (!empty($barcodes) && BienTheSanPham::whereIn('ma_vach', $barcodes)->exists()) {
                return redirect()->back()->withInput()->withErrors(['bien_the' => 'Một hoặc nhiều mã vạch biến thể đã tồn tại.']);
            }

            // Xử lý thuộc tính MỚI: tạo vào DB trước, trả về map {label => id}
            $newAttrMap = $this->processNewAttributes($data['new_attributes'] ?? []);
            $data['bien_the'] = $this->resolveThuocTinhIdsWithNew($data['bien_the'] ?? [], $newAttrMap);

            // ============================================================
            // Xử lý Đơn vị mới on-the-fly (don_vi_co_ban + don_vi_quy_doi):
            // - Tạo record trong bảng danh_muc_don_vi cho từng tên mới.
            // - Map tên về don_vi_chuan_id để insert đúng.
            // ============================================================
            $data['bien_the'] = $this->processOnTheFlyDonViForBienThe($data['bien_the'] ?? []);

            // return bên trong DB::transaction() sẽ throw, thoát closure và return từ store luôn.
            // Nếu closure throw exception → Laravel tự động rollBack + propagate exception ra ngoài catch.
            return DB::transaction(function () use ($data, $request) {
            // 1. Tạo Product (thông tin chung)
            // Lưu ý: bảng `san_pham` KHÔNG có cột `hinh_anh`. Ảnh sản phẩm chính được lấy từ variant đầu tiên.
            $product = Product::create([
                'id_danh_muc' => $data['id_danh_muc'],
                'ten_san_pham' => $data['ten_san_pham'],
                'thuong_hieu' => $data['thuong_hieu'] ?? null,
                'mo_ta' => $data['mo_ta'] ?? null,
                'trang_thai' => $data['trang_thai'] ?? true,
            ]);

            $bienThe = $data['bien_the'] ?? [];

            // Xử lý ảnh cho từng biến thể + đơn vị quy đổi (units[]) NGAY TRƯỚC khi gom nhóm
            // để tránh lệch index giữa mảng bien_the và ảnh upload từ FormData.
            // Hỗ trợ cả 2 cơ chế:
            //   - FormData UploadedFile (ưu tiên, đang dùng ở FE)
            //   - Base64 string (fallback, legacy)
            for ($idx = 0, $n = count($bienThe); $idx < $n; $idx++) {
                if (!is_array($bienThe[$idx] ?? null)) {
                    continue;
                }
                $bienThe[$idx]['uploaded_image_path'] = $this->processImageInput(
                    $request->file("bien_the.$idx.hinh_anh"),
                    $bienThe[$idx]['hinh_anh_base64'] ?? null
                );

                // Ảnh cho units[] (đơn vị quy đổi) trong biến thể
                if (!empty($bienThe[$idx]['units']) && is_array($bienThe[$idx]['units'])) {
                    foreach ($bienThe[$idx]['units'] as $uIdx => $unit) {
                        $bienThe[$idx]['units'][$uIdx]['uploaded_image_path'] = $this->processImageInput(
                            $request->file("bien_the.$idx.units.$uIdx.hinh_anh"),
                            $unit['hinh_anh_base64'] ?? null
                        );
                    }
                }
            }

            if (empty($bienThe)) {
                // 2a. Không có biến thể → tạo 1 variant mặc định
                // Mặc định: KHÔNG gán ảnh chính nào. Nếu sau này có ảnh chính qua base64, sẽ thêm sau.
                BienTheSanPham::create([
                    'product_id' => $product->id,
                    'ten_bien_the' => null,
                    'ma_hang' => $this->generateUniqueMaHang(),
                    'ma_vach' => $this->generateUniqueMaVach(),
                    'gia_von' => 0,
                    'gia_ban' => 0,
                    'so_luong_ton' => 0,
                    'hinh_anh' => null,
                    'thuoc_tinh_ids' => null,
                    'trang_thai' => $data['trang_thai'] ?? true,
                ]);
            } else {
                // 2b. Có biến thể → gom nhóm theo thuoc_tinh_ids
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
                //
                // Ảnh: ưu tiên uploaded_image_path (đã inject ở trên). KHÔNG fallback ảnh chính SP.
                // ============================================================
                $groupedVariants = [];
                foreach ($bienThe as $idx => $item) {
                    // Ưu tiên ảnh upload đã inject; nếu không có thì null
                    $item['_variantImage'] = $item['uploaded_image_path'] ?? null;
                    $ids = $this->normalizeThuocTinhIds($item['thuoc_tinh_ids'] ?? null);
                    $attrKey = !empty($ids) ? implode(',', $ids) : 'no_attr';
                    if (!isset($groupedVariants[$attrKey])) {
                        $groupedVariants[$attrKey] = [];
                    }
                    $groupedVariants[$attrKey][] = $item;
                }

                foreach ($groupedVariants as $attrKey => $groupItems) {
                    // ============================================================
                    // BỌC THÉP: Gom nhóm - phân tách Base Variant và Conversion Units
                    // Dựa vào cờ is_base từ Frontend, không chỉ ty_le === 1
                    // ============================================================
                    $baseVariantData = null;
                    $conversionUnitsData = [];

                    foreach ($groupItems as $item) {
                        $isBase = isset($item['is_base']) ? (int)$item['is_base'] === 1 : false;
                        $tyLe = isset($item['ty_le'])
                            ? (float) $item['ty_le']
                            : (isset($item['so_luong_san_pham_trong_don_vi'])
                                ? (float) $item['so_luong_san_pham_trong_don_vi']
                                : 1);

                        // Đơn vị cơ bản = is_base = 1 hoặc ty_le gần bằng 1 (sai số 0.0001)
                        $isBaseUnit = $isBase || abs($tyLe - 1) < 0.0001;

                        // Chỉ nhận Base Variant 1 LẦN DUY NHẤT.
                        if ($isBaseUnit && $baseVariantData === null) {
                            $baseVariantData = $item;
                        } else {
                            $conversionUnitsData[] = $item;
                        }
                    }

                    // Fallback: nếu không tìm được base (tất cả ty_le>1 và không có is_base), lấy dòng đầu
                    if (!$baseVariantData && count($groupItems) > 0) {
                        $baseVariantData = $groupItems[0];
                        $conversionUnitsData = array_slice($groupItems, 1);
                    }

                    if (!$baseVariantData) {
                        continue;
                    }

                    // Chuẩn bị mảng ID thuộc tính
                    $thuocTinhIds = $this->normalizeThuocTinhIds($baseVariantData['thuoc_tinh_ids'] ?? null) ?: null;

                    // 3. Lưu Biến thể CHA (Base Variant)
                    $createdVariant = BienTheSanPham::create([
                        'product_id' => $product->id,
                        'ten_bien_the' => !empty($baseVariantData['ten_bien_the']) ? $baseVariantData['ten_bien_the'] : null,
                        // Nếu nhóm không có thuộc tính → biến thể là đơn vị gốc thuần túy
                        'la_don_vi' => ($attrKey === 'no_attr') ? 1 : 0,
                        // SAFETY: MỌI sản phẩm phải có đơn vị cơ bản (Cái, Chiếc, Hộp, Thùng...),
                        // kể cả khi có thuộc tính. Nếu payload rỗng, fallback về "Cái".
                        'ten_don_vi' => !empty($baseVariantData['ten_don_vi']) ? $baseVariantData['ten_don_vi'] : 'Cái',
                        'ma_hang' => !empty($baseVariantData['ma_hang'])
                            ? $baseVariantData['ma_hang']
                            : $this->generateUniqueMaHang(),
                        'ma_vach' => !empty($baseVariantData['ma_vach'])
                            ? $baseVariantData['ma_vach']
                            : $this->generateUniqueMaVach(),
                        'gia_von' => 0, // Luôn = 0; giá vốn chỉ được sinh tự động từ lần nhập hàng đầu tiên
                        'gia_ban' => $baseVariantData['gia_ban'] ?? 0,
                        'so_luong_ton' => $baseVariantData['so_luong_ton'] ?? 0,
                        'dinh_muc_toi_thieu' => $baseVariantData['dinh_muc_toi_thieu'] ?? 0,
                        // FIX: KHÔNG fallback $imagePath. Nếu không có ảnh base64 → null.
                        'hinh_anh' => $baseVariantData['_variantImage'] ?? null,
                        'thuoc_tinh_ids' => $thuocTinhIds,
                        'trang_thai' => $data['trang_thai'] ?? true,
                    ]);

                    // 4. Lưu các Đơn vị quy đổi (Gắn Khóa ngoại vào Biến thể CHA)
                    // a) Các dòng cùng nhóm thuộc tính có ty_le > 1
                    foreach ($conversionUnitsData as $unitItem) {
                        $tyLeQuyDoi = isset($unitItem['ty_le'])
                            ? (float) $unitItem['ty_le']
                            : (isset($unitItem['so_luong_san_pham_trong_don_vi'])
                                ? (float) $unitItem['so_luong_san_pham_trong_don_vi']
                                : 2);

                        DonViQuyDoi::create([
                            'variant_id' => $createdVariant->id, // BẮT BUỘC KHÓA NGOẠI
                            'product_id' => $product->id,
                            'don_vi_chuan_id' => !empty($unitItem['don_vi_chuan_id'])
                                ? (int) $unitItem['don_vi_chuan_id']
                                : null,
                            // SAFETY: dùng !empty() để tránh tạo đơn vị với tên rỗng
                            'ten_don_vi' => !empty($unitItem['ten_don_vi_bien_the'])
                                ? $unitItem['ten_don_vi_bien_the']
                                : (!empty($unitItem['ten_don_vi']) ? $unitItem['ten_don_vi'] : 'Đơn vị'),
                            'so_luong_san_pham_trong_don_vi' => $tyLeQuyDoi,
                            'ma_hang' => !empty($unitItem['ma_hang'])
                                ? $unitItem['ma_hang']
                                : $this->generateUniqueMaHang(),
                            'ma_vach' => $unitItem['ma_vach'] ?? null,
                            'gia_von_quy_doi' => 0, // Luôn = 0; giá vốn chỉ được sinh tự động từ lần nhập hàng đầu tiên
                            'gia_ban_quy_doi' => $unitItem['gia_ban'] ?? ($unitItem['gia_ban_quy_doi'] ?? 0),
                            'gia_ban_si' => $unitItem['gia_ban_si'] ?? null,
                            // FIX: KHÔNG fallback ảnh chính SP. Dùng ảnh riêng của unit (nếu có).
                            'hinh_anh' => $unitItem['uploaded_image_path'] ?? null,
                            'la_don_vi_mac_dinh' => false,
                        ]);
                    }

                    // b) Các đơn vị quy đổi khai báo trong units[] của base row (giữ logic cũ)
                    if (!empty($baseVariantData['units'])) {
                        foreach ($baseVariantData['units'] as $unit) {
                            // Bỏ qua đơn vị có rate = 0 (không hợp lệ); giữ rate < 1 để hỗ trợ đảo đơn vị cơ bản
                            if ((float) ($unit['so_luong_san_pham_trong_don_vi'] ?? 0) <= 0) {
                                continue;
                            }
                            DonViQuyDoi::create([
                                'variant_id' => $createdVariant->id,
                                'product_id' => $product->id,
                                'don_vi_chuan_id' => !empty($unit['don_vi_chuan_id'])
                                    ? (int) $unit['don_vi_chuan_id']
                                    : null,
                                // SAFETY: bỏ qua tạo nếu thiếu tên hợp lệ
                                'ten_don_vi' => !empty($unit['ten_don_vi']) ? $unit['ten_don_vi'] : 'Đơn vị',
                                'so_luong_san_pham_trong_don_vi' => (float) ($unit['so_luong_san_pham_trong_don_vi'] ?? 1),
                                'ma_hang' => $unit['ma_hang'] ?? $this->generateUniqueMaHang(),
                                'ma_vach' => $unit['ma_vach'] ?? null,
                                'gia_von_quy_doi' => 0, // Luôn = 0; giá vốn chỉ được sinh tự động từ lần nhập hàng đầu tiên
                                'gia_ban_quy_doi' => $unit['gia_ban_quy_doi'] ?? 0,
                                'gia_ban_si' => $unit['gia_ban_si'] ?? null,
                                'hinh_anh' => $unit['uploaded_image_path'] ?? null,
                                'la_don_vi_mac_dinh' => false,
                            ]);
                        }
                    }
                }
            }

            return redirect()->back()->with('success', 'Đã thêm sản phẩm mới.');
        });
        } catch (\Exception $e) {
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

        return view('admin_xem_truoc.san-pham.edit', [
            'product' => $product,
            'danhMucs' => $danhMucs,
            'thuocTinhChas' => $thuocTinhChas,
            'danhMucDonVis' => $danhMucDonVis,
            'productUnits' => $productUnits,
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

        // Xử lý Đơn vị mới on-the-fly (don_vi_co_ban + don_vi_quy_doi) – trước transaction
        $data['bien_the'] = $this->processOnTheFlyDonViForBienThe($data['bien_the'] ?? []);

// FIX: Inject đường dẫn ảnh upload TRƯỚC KHI gom nhóm để tránh lệch index.
// Dùng for + array index (không dùng reference &$item) để tránh PHP reference aliasing bug.
        for ($idx = 0, $n = count($data['bien_the'] ?? []); $idx < $n; $idx++) {
            if (!is_array($data['bien_the'][$idx] ?? null)) {
                continue;
            }
            $item = &$data['bien_the'][$idx];

            // Lấy ảnh cũ từ DB nếu đang sửa variant đã tồn tại (để fallback khi user không upload ảnh mới)
            $existingImage = null;
            if (!empty($item['id'])) {
                $existingImage = BienTheSanPham::find($item['id'])?->hinh_anh;
            }

            // Ảnh variant: ưu tiên FormData UploadedFile > base64 > keep ảnh cũ.
            $item['uploaded_image_path'] = $this->processImageInput(
                $request->file("bien_the.$idx.hinh_anh"),
                $item['hinh_anh_base64'] ?? null,
                $existingImage
            );

            // Ảnh cho units[] (đơn vị quy đổi) trong biến thể
            if (!empty($item['units']) && is_array($item['units'])) {
                foreach ($item['units'] as $uIdx => $unit) {
                    $existingUnitImage = null;
                    if (!empty($unit['id'])) {
                        $existingUnitImage = DonViQuyDoi::find($unit['id'])?->hinh_anh;
                    }
                    $item['units'][$uIdx]['uploaded_image_path'] = $this->processImageInput(
                        $request->file("bien_the.$idx.units.$uIdx.hinh_anh"),
                        $unit['hinh_anh_base64'] ?? null,
                        $existingUnitImage
                    );
                }
            }
            unset($item);
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

        return DB::transaction(function () use ($product, $data, $request, &$deletedVariantsList, &$updatedVariantsList, &$createdVariantsList) {
            // 2. Cap nhat thong tin chung
            $product->update([
                'id_danh_muc' => $data['id_danh_muc'],
                'ten_san_pham' => $data['ten_san_pham'],
                'thuong_hieu' => $data['thuong_hieu'] ?? null,
                'mo_ta' => $data['mo_ta'] ?? null,
                'trang_thai' => $data['trang_thai'] ?? true,
            ]);

            // ============================================================
            // BỌC THÉP DỰA VÀO VỊ TRÍ (INDEX):
            //   - Phần tử ĐẦU TIÊN (không có thuộc tính)  → Đơn vị cơ bản (CHA) → bien_the_san_pham
            //   - Phần tử CÒN LẠI (không có thuộc tính)    → Đơn vị quy đổi       → don_vi_quy_doi
            //   - Có thuoc_tinh_ids                        → variantRows (biến thể thuộc tính)
            // (Tuyệt đối KHÔNG tin tưởng ty_le từ frontend; fallback default = 2 để an toàn DB)
            // ============================================================
            $variantRows = [];
            $conversionRows = [];
            $isFirst = true;
            foreach ($data['bien_the'] ?? [] as $idx => $item) {
                if (!empty($item['thuoc_tinh_ids'])) {
                    $variantRows[$idx] = $item;
                    $isFirst = false; // variant có thuộc tính KHÔNG tính là dòng đầu đơn vị
                    continue;
                }
                if ($isFirst) {
                    $variantRows[$idx] = $item; // Dòng đầu tiên LUÔN là Đơn vị cơ bản
                    $isFirst = false;
                } else {
                    $conversionRows[$idx] = $item; // Từ dòng thứ 2 trở đi LUÔN là Đơn vị quy đổi
                }
            }

            $incomingVariantIds = [];

            // ============================================================
            // FIX Gom-nhom: gom các dòng theo thuoc_tinh_ids trước khi CRUD.
            // Mỗi nhóm có 1 base (ty_le=1) + N conversion (ty_le>1).
            // - Nếu base có existingId → update variant đó + xóa/sync đơn vị quy đổi
            // - Nếu base không có existingId → tạo mới + tạo các conversion gắn vào
            // ============================================================
            $groupedVariants = [];
            foreach ($data['bien_the'] ?? [] as $idx => $variant) {
                // Ưu tiên uploaded_image_path đã inject ở ngoài transaction (khớp index tuyệt đối)
                $variant['_variantImage'] = $variant['uploaded_image_path'] ?? null;
                $attrIds = $this->normalizeThuocTinhIds($variant['thuoc_tinh_ids'] ?? null);
                $attrKey = !empty($attrIds) ? implode(',', $attrIds) : 'no_attr';
                if (!isset($groupedVariants[$attrKey])) {
                    $groupedVariants[$attrKey] = [];
                }
                $groupedVariants[$attrKey][] = $variant;
            }

            foreach ($groupedVariants as $attrKey => $groupItems) {
                $baseVariantData = null;
                $conversionUnitsData = [];

                foreach ($groupItems as $item) {
                    $isBase = isset($item['is_base']) ? (int)$item['is_base'] === 1 : false;
                    $tyLe = isset($item['ty_le']) ? (float)$item['ty_le'] : (isset($item['so_luong_san_pham_trong_don_vi']) ? (float)$item['so_luong_san_pham_trong_don_vi'] : 1);

                    // Đơn vị cơ bản = is_base = 1 hoặc ty_le gần bằng 1 (sai số 0.0001)
                    $isBaseUnit = $isBase || abs($tyLe - 1) < 0.0001;
                    if ($isBaseUnit && $baseVariantData === null) {
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
                    // ẢNH ĐÃ ĐƯỢC RESOLVE TỪ BASE64 Ở NGOÀI TRANSACTION (xem upload block trên).
                    // uploaded_image_path đã tính đúng action (replace/delete/keep) với ảnh cũ.
                    // KHÔNG fallback $mainImage (đã bỏ). Tôn trọng quyết định của user.
                    $finalImage = $variantImage;

                    $thuocTinhIds = $this->normalizeThuocTinhIds($baseVariantData['thuoc_tinh_ids'] ?? null) ?: null;

                    // SAFETY: giữ nguyên la_don_vi theo DB
                    $laDonVi = $existingVariant->la_don_vi ? true : false;

                    // ten_don_vi: MỌI sản phẩm đều có đơn vị cơ bản (Cái, Chiếc, Hộp, Thùng...).
                    // KHÔNG dùng $laDonVi làm điều kiện — nếu payload rỗng, fallback về giá trị đang có trong DB.
                    $tenDonVi = !empty($baseVariantData['ten_don_vi'])
                        ? $baseVariantData['ten_don_vi']
                        : $existingVariant->ten_don_vi;

                    // ten_bien_the: chỉ lưu khi KHÔNG phải đơn vị đơn lẻ.
                    // Nếu payload rỗng, fallback về giá trị đang có trong DB.
                    $tenBienThe = $laDonVi
                        ? null
                        : (!empty($baseVariantData['ten_bien_the'])
                            ? $baseVariantData['ten_bien_the']
                            : $existingVariant->ten_bien_the);

                    // ma_hang: dùng payload nếu có, ngược lại giữ nguyên DB
                    $maHang = !empty($variant['ma_hang']) ? $variant['ma_hang'] : $existingVariant->ma_hang;

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
                        // KHÔNG cập nhật gia_von: giá vốn chỉ được sinh tự động từ lần nhập hàng đầu tiên.
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
                    $thuocTinhIds = $this->normalizeThuocTinhIds($baseVariantData['thuoc_tinh_ids'] ?? null) ?: null;

                    $laDonVi = ($attrKey === 'no_attr') ? true : false;

                    // ten_don_vi: MỌI sản phẩm đều phải có đơn vị cơ bản (Cái, Chiếc, Hộp, Thùng...),
                    // kể cả khi có thuộc tính. Khi tạo mới, nếu payload không có thì fallback về "Cái".
                    $tenDonVi = !empty($baseVariantData['ten_don_vi'])
                        ? $baseVariantData['ten_don_vi']
                        : 'Cái';

                    // ten_bien_the: chỉ lưu khi KHÔNG phải đơn vị đơn lẻ.
                    $tenBienThe = $laDonVi
                        ? null
                        : (!empty($baseVariantData['ten_bien_the']) ? $baseVariantData['ten_bien_the'] : null);
                    // FIX: Ảnh đã được resolve từ Base64. KHÔNG fallback $mainImage.
                    // - Nếu có base64: variant MỚI sẽ có ảnh (replace).
                    // - Nếu không có base64: variant MỚI sẽ có hinh_anh = null (TUYỆT ĐỐI KHÔNG gán $mainImage đè).
                    $finalImage = $variantImage;

                    // Auto-generate ma_hang chỉ khi rỗng
                    $maHang = !empty($variant['ma_hang']) ? $variant['ma_hang'] : $this->generateUniqueMaHang();
                    $maVach = !empty($variant['ma_vach']) ? $variant['ma_vach'] : $this->generateUniqueMaVach();

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
                        'gia_von' => 0, // Luôn = 0; giá vốn chỉ được sinh tự động từ lần nhập hàng đầu tiên
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
                        ? (float) $unitItem['ty_le']
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
                            'ten_don_vi' => !empty($unitItem['ten_don_vi_bien_the'])
                                ? $unitItem['ten_don_vi_bien_the']
                                : (!empty($unitItem['ten_don_vi']) ? $unitItem['ten_don_vi'] : ''),
                            'so_luong_san_pham_trong_don_vi' => $tyLeQuyDoi,
                            'ma_hang' => !empty($unitItem['ma_hang'])
                                ? $unitItem['ma_hang']
                                : $this->generateUniqueMaHang(),
                            'ma_vach' => $unitItem['ma_vach'] ?? null,
                            'gia_von_quy_doi' => 0, // Luôn = 0; giá vốn chỉ được sinh tự động từ lần nhập hàng đầu tiên
                            'gia_ban_quy_doi' => $unitItem['gia_ban']
                                ?? ($unitItem['gia_ban_quy_doi'] ?? 0),
                            'hinh_anh' => $variantImage,
                            'la_don_vi_mac_dinh' => false,
                        ]);
                    }
                }

                // 4b. Sync các đơn vị khai báo trong units[] của base row (giữ logic cũ)
                // SAFETY: Chỉ syncUnits khi payload THỰC SỰ có key "units" VÀ không rỗng.
                // Nếu thiếu key "units" → giữ nguyên DonViQuyDoi (user không can thiệp đơn vị quy đổi).
                // Nếu có key "units" nhưng rỗng → user đã chủ động xóa hết → syncUnits sẽ xóa DB.
                if (array_key_exists('units', $baseVariantData ?? [])) {
                    if (!empty($baseVariantData['units'])) {
                        $this->syncUnits($variantId, $baseVariantData['units'], $variantImage, $product->id);
                    } else {
                        // User gửi units=[] → xóa hết DonViQuyDoi thuộc variant này
                        $this->syncUnits($variantId, [], $variantImage, $product->id);
                    }
                }
                // Nếu không có key "units" trong payload → KHÔNG gọi syncUnits, giữ nguyên DB
            }

            // 4b. Lưu conversionRows (các dòng CÒN LẠI không phải thuộc tính, không phải dòng đầu) vào don_vi_quy_doi
            // Gắn variant_id của biến thể CHA đầu tiên của product
            if (!empty($conversionRows)) {
                $parentVariant = BienTheSanPham::where('product_id', $product->id)
                    ->orderBy('id')->first();
                if ($parentVariant) {
                    foreach ($conversionRows as $cRow) {
                        $tyLeQuyDoi = isset($cRow['ty_le']) ? (int)$cRow['ty_le']
                            : (isset($cRow['so_luong_san_pham_trong_don_vi']) ? (int)$cRow['so_luong_san_pham_trong_don_vi'] : 2);
                        $uMaHang = !empty($cRow['ma_hang']) ? $cRow['ma_hang'] : $this->generateUniqueMaHang();
                        DonViQuyDoi::create([
                            'variant_id'   => $parentVariant->id,
                            'product_id'   => $product->id,
                            'don_vi_chuan_id' => $cRow['don_vi_chuan_id'] ?? null,
                            'ten_don_vi'   => $cRow['ten_don_vi'] ?? '',
                            'so_luong_san_pham_trong_don_vi' => $tyLeQuyDoi,
                            'ma_hang'      => $uMaHang,
                            'ma_vach'      => $cRow['ma_vach'] ?? null,
                            'gia_von_quy_doi' => 0, // Luôn = 0; giá vốn chỉ được sinh tự động từ lần nhập hàng đầu tiên
                            'gia_ban_quy_doi' => $cRow['gia_ban'] ?? ($cRow['gia_ban_quy_doi'] ?? 0),
                            'gia_ban_si'   => $cRow['gia_ban_si'] ?? null,
                            'hinh_anh'     => $cRow['hinh_anh'] ?? null,
                            'la_don_vi_mac_dinh' => false,
                        ]);
                    }
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
            }
            // San pham khong co bien the: khong co gi de cap nhat them
            // Cac cot gia_von, gia_ban, so_luong_ton_kho da bi xoa khoi bang san_pham (migration 03/07/2026)
            return redirect()->route('san-pham.index')->with('success', 'Đã cập nhật sản phẩm.');
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
        } catch (\Throwable $e) {
            \Log::warning('[Update SanPham] Lỗi sync tồn kho: ' . $e->getMessage(), ['product_id' => $id]);
        }

        return redirect()->route('san-pham.index')->with('success', 'Đã cập nhật sản phẩm.');
    }

    public function destroy(Request $request, int $id)
    {
        try {
            // Tìm sản phẩm, bao gồm cả bản ghi đã bị soft delete
            $product = Product::withTrashed()->with('variants.units')->find($id);

            if (!$product) {
                if ($request->expectsJson()) {
                    return response()->json(['success' => false, 'message' => 'Sản phẩm không tồn tại.'], 404);
                }
                return redirect()->route('san-pham.index')->with('error', 'Sản phẩm không tồn tại.');
            }

            // Kiểm tra nếu sản phẩm đã bị xóa mềm
            if ($product->trashed()) {
                if ($request->expectsJson()) {
                    return response()->json(['success' => false, 'message' => 'Sản phẩm đã nằm trong thùng rác.'], 400);
                }
                return redirect()->route('san-pham.index')->with('error', 'Sản phẩm đã nằm trong thùng rác.');
            }

            // Xóa các biến thể và đơn vị quy đổi
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

            // Xóa sản phẩm (soft delete)
            $product->delete();

            if ($request->expectsJson()) {
                return response()->json(['success' => true, 'message' => 'Đã xóa sản phẩm thành công.']);
            }

            return redirect()->route('san-pham.index')->with('success', 'Đã xóa sản phẩm.');
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()], 500);
            }
            return redirect()->route('san-pham.index')->with('error', 'Lỗi xóa sản phẩm: ' . $e->getMessage());
        }
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
                // Lấy Products theo IDs (checkbox gửi Product IDs, không phải Variant IDs)
                // Sau đó cascade xoá variants và xoá mềm Products
                $products = Product::with('variants.units')->whereIn('id', $ids)->get();

                foreach ($products as $product) {
                    // Xoá cascade variants và units (giống destroy())
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
                    // Xoá mềm Product
                    $product->delete();
                }
                $message = 'Đã xóa ' . count($ids) . ' sản phẩm.';
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
        return new \App\Exports\SanPhamExport();
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
        DB::beginTransaction();
        try {
            $variant = BienTheSanPham::onlyTrashed()
                ->with(['product'])
                ->findOrFail($id);

            // 1. Khôi phục các đơn vị quy đổi của biến thể (chỉ những đơn vị đang bị soft-delete)
            $restoredUnits = 0;
            foreach ($variant->units()->onlyTrashed()->get() as $unit) {
                $unit->restore();
                $restoredUnits++;
            }

            // 2. Khôi phục biến thể
            $variant->restore();

            // 3. Cascade khôi phục Product cha nếu cha cũng đang soft-deleted
            $productRestored = false;
            $parentProduct   = Product::withTrashed()->find($variant->product_id);
            if ($parentProduct && $parentProduct->trashed()) {
                $parentProduct->restore();
                $productRestored = true;
            }

            DB::commit();

            $msg = 'Đã khôi phục biến thể.';
            if ($productRestored) {
                $msg .= ' Sản phẩm cha cũng đã được khôi phục.';
            }
            if ($restoredUnits > 0) {
                $msg .= " Đồng thời khôi phục {$restoredUnits} đơn vị quy đổi.";
            }

            return redirect()->route('san-pham.trash')->with('success', $msg);
        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->route('san-pham.trash')
                ->with('error', 'Lỗi khi khôi phục: ' . $e->getMessage());
        }
    }

    public function getConstraints(int $id)
    {
        $variant = BienTheSanPham::onlyTrashed()->findOrFail($id);

        // Đếm các ràng buộc
        $chiTietHoaDon = \DB::table('chi_tiet_hoa_don')->where('id_bien_the_san_pham', $id)->count();
        $chiTietDoiTra = \DB::table('chi_tiet_doi_tra')
            ->where('id_bien_the', $id)
            ->orWhere('id_bien_the_thay_the', $id)
            ->count();
        $hangLoi = \DB::table('hang_loi')->where('id_bien_the', $id)->count();
        
        // chi_tiet_phieu chứa cả phiếu nhập và xuất, cần join với phieu để phân loại
        $chiTietPhieuNhap = \DB::table('chi_tiet_phieu as ctp')
            ->join('phieu as p', 'ctp.id_phieu', '=', 'p.id')
            ->whereIn('p.loai_phieu', ['nhap_hang', 'tra_lai_tu_khach'])
            ->where('ctp.id_san_pham', $id)
            ->count();
            
        $chiTietPhieuXuat = \DB::table('chi_tiet_phieu as ctp')
            ->join('phieu as p', 'ctp.id_phieu', '=', 'p.id')
            ->whereIn('p.loai_phieu', ['xuat_tra_ncc', 'xuat_tieu_huy'])
            ->where('ctp.id_san_pham', $id)
            ->count();

        return response()->json([
            'variant_name' => $variant->ten_bien_the ?: 'Mặc định',
            'product_name' => $variant->product->ten_san_pham ?? 'N/A',
            'constraints' => [
                'chi_tiet_hoa_don' => $chiTietHoaDon,
                'chi_tiet_doi_tra' => $chiTietDoiTra,
                'hang_loi' => $hangLoi,
                'chi_tiet_phieu_nhap' => $chiTietPhieuNhap,
                'chi_tiet_phieu_xuat' => $chiTietPhieuXuat,
            ],
            'total' => $chiTietHoaDon + $chiTietDoiTra + $hangLoi + $chiTietPhieuNhap + $chiTietPhieuXuat,
        ]);
    }

    public function forceDelete(int $id): RedirectResponse
    {
        $variant = BienTheSanPham::onlyTrashed()->with('units')->findOrFail($id);

        // Xóa các bản ghi có khóa ngoại tham chiếu đến variant này
        \DB::table('hang_loi')->where('id_bien_the', $id)->delete();
        \DB::table('chi_tiet_doi_tra')->where('id_bien_the', $id)->delete();
        \DB::table('chi_tiet_doi_tra')->where('id_bien_the_thay_the', $id)->delete();

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

        DB::beginTransaction();
        try {
            $variants = BienTheSanPham::onlyTrashed()
                ->whereIn('id', $ids)
                ->get();

            $restoredVariants   = 0;
            $restoredUnits      = 0;
            $restoredProducts   = 0;
            $productIdsRestored = [];

            foreach ($variants as $variant) {
                // Restore units trước
                foreach ($variant->units()->onlyTrashed()->get() as $unit) {
                    $unit->restore();
                    $restoredUnits++;
                }

                // Restore variant
                $variant->restore();
                $restoredVariants++;

                // Cascade restore product cha nếu đang soft-deleted
                $parentProduct = Product::withTrashed()->find($variant->product_id);
                if ($parentProduct && $parentProduct->trashed()
                    && !in_array($parentProduct->id, $productIdsRestored, true)) {
                    $parentProduct->restore();
                    $restoredProducts++;
                    $productIdsRestored[] = $parentProduct->id;
                }
            }

            DB::commit();

            $msg = "Đã khôi phục {$restoredVariants} biến thể thành công.";
            if ($restoredProducts > 0) {
                $msg .= " Đồng thời khôi phục {$restoredProducts} sản phẩm cha.";
            }
            if ($restoredUnits > 0) {
                $msg .= " Và {$restoredUnits} đơn vị quy đổi.";
            }

            return redirect()->route('san-pham.trash')->with('success', $msg);
        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->route('san-pham.trash')
                ->with('error', 'Lỗi khi khôi phục hàng loạt: ' . $e->getMessage());
        }
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

        // ============================================================
        // KPI #1: Tóm tắt bán hàng 30 ngày gần nhất (doanh thu, lợi
        // nhuận gộp, sản phẩm đã bán) — tính ở cấp SẢN PHẨM (gom
        // tất cả các biến thể của product này).
        // ============================================================
        $ordersSummary = [
            'doanh_thu'      => 0,
            'so_luong_ban'   => 0,
            'so_don'         => 0,
            'gia_von_uoc'    => 0,
            'loi_nhuan_gop'  => 0,
            'bieu_loi_nhuan' => 0,
        ];
        try {
            $from = now()->subDays(29)->startOfDay();
            $to   = now()->endOfDay();

            $row = DB::table('chi_tiet_hoa_don as cth')
                ->join('hoa_don as hd', 'cth.id_hoa_don', '=', 'hd.id')
                ->join('bien_the_san_pham as v', 'cth.variant_id', '=', 'v.id')
                ->where('v.product_id', $product->id)
                ->where('hd.trang_thai', 'Hoàn thành')
                ->whereBetween('hd.created_at', [$from, $to])
                ->selectRaw('COALESCE(SUM(cth.so_luong), 0)            as total_quantity')
                ->selectRaw('COALESCE(SUM(cth.thanh_tien), 0)          as total_revenue')
                ->selectRaw('COUNT(DISTINCT cth.id_hoa_don)            as total_orders')
                ->selectRaw('COALESCE(SUM(cth.so_luong * v.gia_von), 0) as total_cost')
                ->first();

            if ($row) {
                $doanhThu     = (float) $row->total_revenue;
                $giaVonUoc    = (float) $row->total_cost;
                $loiNhuanGop  = $doanhThu - $giaVonUoc;
                $ordersSummary = [
                    'doanh_thu'      => $doanhThu,
                    'so_luong_ban'   => (int) $row->total_quantity,
                    'so_don'         => (int) $row->total_orders,
                    'gia_von_uoc'    => $giaVonUoc,
                    'loi_nhuan_gop'  => $loiNhuanGop,
                    'bieu_loi_nhuan' => $doanhThu > 0 ? round(($loiNhuanGop / $doanhThu) * 100, 1) : 0,
                ];
            }
        } catch (\Throwable $e) {
            // Nếu thiếu cột/table (ví dụ môi trường dev chưa seed) → để = 0, UI vẫn render OK
            \Log::warning('[SanPham show] ordersSummary fail: ' . $e->getMessage());
        }

        // ============================================================
        // KPI #2: Thống kê biến thể (tổng, tồn nhiều nhất, sắp hết)
        // ============================================================
        $variantKpis = [
            'tong_bien_the'      => $product->variants->count(),
            'ton_nhieu_nhat'     => null,   // ['ten_bien_the' => ..., 'so_luong_ton' => ...]
            'sap_het_hang'       => [],     // collection các variant sắp hết (ton <= dinh_muc_toi_thieu)
            'so_bien_the_sap_het'=> 0,
        ];
        if ($product->variants->isNotEmpty()) {
            $tonNhieuNhat = $product->variants->sortByDesc('so_luong_ton')->first();
            $variantKpis['ton_nhieu_nhat'] = $tonNhieuNhat;

            $sapHetHang = $product->variants->filter(function ($v) {
                $ton = (int) ($v->so_luong_ton ?? 0);
                $dinhMuc = (int) ($v->dinh_muc_toi_thieu ?? 0);
                return $ton <= $dinhMuc;
            })->values();
            $variantKpis['sap_het_hang']      = $sapHetHang;
            $variantKpis['so_bien_the_sap_het'] = $sapHetHang->count();
        }

        // ============================================================
        // LỊCH SỬ BÁN HÀNG (phân trang) — dùng HasManyThrough qua bien_the_san_pham
        // Bảng chi_tiet_hoa_don đã thay id_san_pham bằng variant_id.
        // ============================================================
        $lichSuBanHang = $product->chiTietHoaDons()
            ->with(['hoaDon', 'bienThe'])
            ->latest('chi_tiet_hoa_don.created_at')
            ->paginate(10);

        return view('admin_xem_truoc.san-pham.chi-tiet', compact('product', 'theKho', 'loHang', 'ordersSummary', 'variantKpis', 'lichSuBanHang'));
    }

    /**
     * Upload file ảnh từ FormData UploadedFile vào public/uploads/san-pham.
     */
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

    /**
     * Decode chuỗi Base64 (data:image/...;base64,...) thành file vật lý trong public/uploads/$subDir.
     * Trả về đường dẫn tương đối để lưu vào DB (VD: "uploads/san-pham/xxx.jpg"), hoặc null nếu input không hợp lệ.
     */
    protected function decodeBase64ToFile(?string $base64, string $subDir = 'san-pham'): ?string
    {
        if (empty($base64)) {
            return null;
        }
        if (!preg_match('#^data:image/(\w+);base64,(.*)$#s', $base64, $m)) {
            return null;
        }
        $ext = strtolower($m[1]);
        if (!in_array($ext, ['jpeg', 'jpg', 'png', 'gif', 'webp'], true)) {
            $ext = 'jpg';
        }
        $data = base64_decode($m[2], true);
        if ($data === false) {
            return null;
        }
        $dir = public_path("uploads/{$subDir}");
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }
        $filename = time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
        $fullPath = $dir . DIRECTORY_SEPARATOR . $filename;
        if (file_put_contents($fullPath, $data) === false) {
            return null;
        }
        return "uploads/{$subDir}/{$filename}";
    }

    /**
     * Xử lý input ảnh thống nhất (FormData UploadedFile hoặc Base64) cho cả store() và update().
     *
     * Logic ưu tiên:
     *   1) Nếu có FormData UploadedFile mới → upload file mới (trả về path mới).
     *   2) Nếu có Base64 mới → decode thành file mới.
     *   3) Nếu không có gì mới → giữ nguyên $existingImage (cho update). Khi store mới, giữ null.
     *
     * @return string|null  Đường dẫn tương đối lưu vào DB, hoặc null.
     */
    protected function processImageInput(
        ?\Illuminate\Http\UploadedFile $uploadedFile,
        ?string $base64,
        ?string $existingImage = null
    ): ?string {
        if ($uploadedFile instanceof \Illuminate\Http\UploadedFile) {
            return $this->uploadImage($uploadedFile);
        }
        $newPath = $this->decodeBase64ToFile($base64);
        if ($newPath !== null) {
            return $newPath;
        }
        return $existingImage;
    }

    /**
     * Xử lý đơn vị mới on-the-fly trong mảng bien_the + units:
     * - Nếu ten_don_vi là string (chưa tồn tại) → tạo mới trong danh_muc_don_vi, gán don_vi_chuan_id.
     * - Nếu đã có don_vi_chuan_id → giữ nguyên.
     * Trả về mảng bien_the đã được enrich.
     */
    protected function processOnTheFlyDonViForBienThe(array $bienThe): array
    {
        foreach ($bienThe as &$item) {
            // 1. Đơn vị cơ bản của variant (ten_don_vi): nếu không có id trong danh_muc_don_vi, tạo mới.
            $tenDonViBase = trim((string) ($item['ten_don_vi'] ?? ''));
            if ($tenDonViBase !== '' && empty($item['don_vi_chuan_id'])) {
                $item['_don_vi_chuan_id_resolved'] = $this->ensureDanhMucDonVi($tenDonViBase);
            }

            // 2. Units[] (đơn vị quy đổi): xử lý từng unit.
            if (!empty($item['units']) && is_array($item['units'])) {
                foreach ($item['units'] as &$unit) {
                    $tenDonViUnit = trim((string) ($unit['ten_don_vi'] ?? ''));
                    if ($tenDonViUnit !== '' && empty($unit['don_vi_chuan_id'])) {
                        $unit['don_vi_chuan_id'] = $this->ensureDanhMucDonVi($tenDonViUnit);
                    }
                }
                unset($unit);
            }

            // 3. Trường hợp base variant là "don_vi" (không có thuộc tính) + user gõ tên đơn vị
            //    trong ten_don_vi_bien_the → cũng tạo mới đơn vị.
            $tenDonViBienThe = trim((string) ($item['ten_don_vi_bien_the'] ?? ''));
            if ($tenDonViBienThe !== '' && empty($item['don_vi_chuan_id'])) {
                $item['don_vi_chuan_id'] = $this->ensureDanhMucDonVi($tenDonViBienThe);
            }
        }
        unset($item);
        return $bienThe;
    }

    /**
     * Tìm hoặc tạo mới Đơn vị trong bảng danh_muc_don_vi dựa trên tên.
     * Trả về id của đơn vị. Nếu tên rỗng → trả về null.
     */
    protected function ensureDanhMucDonVi(?string $tenDonVi): ?int
    {
        $tenDonVi = trim((string) $tenDonVi);
        if ($tenDonVi === '') {
            return null;
        }
        $row = DanhMucDonVi::firstOrCreate(
            ['ten_don_vi' => $tenDonVi],
            ['so_luong_san_pham_trong_don_vi' => 1, 'trang_thai' => true]
        );
        return $row->id;
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
     * Chuẩn hóa thuoc_tinh_ids từ request (CSV) hoặc model cast (array).
     *
     * @return array<int>
     */
    protected function normalizeThuocTinhIds(mixed $rawIds): array
    {
        if (blank($rawIds)) {
            return [];
        }

        $ids = is_array($rawIds) ? $rawIds : explode(',', (string) $rawIds);
        $ids = array_map(
            static fn($id) => (int) trim((string) $id),
            array_filter($ids, static fn($id) => trim((string) $id) !== '')
        );
        $ids = array_values(array_filter($ids, static fn($id) => $id > 0));
        sort($ids, SORT_NUMERIC);

        return $ids;
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
                $ids = $this->normalizeThuocTinhIds($rawIds);
                $attrPart = implode(',', $ids);

                // Phân biệt đơn vị: ưu tiên ty_le, fallback ten_don_vi_bien_the / ten_don_vi
                $tyLe = isset($variant['ty_le']) && $variant['ty_le'] !== ''
                    ? (float) $variant['ty_le']
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
                $attrIds = $this->normalizeThuocTinhIds($rawIds);
                if (empty($attrIds)) continue;

                // Lấy ty_le hiện tại của row trong payload
                $rowTyLe = isset($variant['ty_le']) && $variant['ty_le'] !== ''
                    ? (float) $variant['ty_le']
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
                        return $this->normalizeThuocTinhIds($dbVariant->thuoc_tinh_ids) === $attrIds;
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
        // Load all parent attributes to build group name -> id lookup
        $allParents = ThuocTinhSanPham::whereNull('thuoc_tinh_cha_id')
            ->pluck('id', 'ten_thuoc_tinh')
            ->toArray();

        foreach ($bienThe as &$variant) {
            $rawIds = $variant['thuoc_tinh_ids'] ?? null;
            if (blank($rawIds)) continue;

            $idStrings = is_array($rawIds) ? $rawIds : explode(',', $rawIds);
            $resolved = [];

            foreach ($idStrings as $idStr) {
                $idStr = trim($idStr);
                if ($idStr === '' || $idStr === 'null') continue;

                // Case 1: numeric ID (existing attribute from DB) - use directly
                if (is_numeric($idStr)) {
                    $resolved[] = (int) $idStr;
                    continue;
                }

                // Case 2: synthetic ID from JS like "id_Màu sắc_Đỏ" (new attribute)
                // Parse to extract group name and label
                if (str_starts_with($idStr, 'id_')) {
                    $parts = explode('_', $idStr, 3); // ["id", "Màu sắc", "Đỏ"]
                    if (count($parts) === 3) {
                        $groupName = $parts[1];
                        $label = $parts[2];
                        $parentId = $allParents[$groupName] ?? null;

                        // Look up in newAttrMap: "groupName|label" => id
                        $key = $groupName . '|' . $label;
                        if (isset($newAttrMap[$key])) {
                            $resolved[] = (int) $newAttrMap[$key];
                        } else {
                            // Fallback: create/find in DB directly
                            $created = ThuocTinhSanPham::firstOrCreate(
                                ['ten_thuoc_tinh' => $label, 'thuoc_tinh_cha_id' => $parentId],
                                ['trang_thai' => true]
                            );
                            $resolved[] = $created->id;
                        }
                    }
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

        // Nếu user upload ảnh mới cho variant cha → propagate xuống TẤT CẢ units
        // của variant (cả units có trong payload lẫn units không có trong payload)
        if ($image !== null) {
            $allUnits = DonViQuyDoi::where('variant_id', $variantId)->get();
            foreach ($allUnits as $u) {
                if ($u->hinh_anh !== $image) {
                    $oldImg = $u->hinh_anh;
                    $u->hinh_anh = $image;
                    $u->save();
                    // Xóa file cũ nếu không còn ai dùng
                    if ($oldImg && !str_starts_with($oldImg, 'http')) {
                        $this->deleteImageIfUnused($oldImg);
                    }
                }
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
            // Bỏ qua đơn vị có rate = 0 (không hợp lệ); giữ rate < 1 để hỗ trợ đảo đơn vị cơ bản
            if ((float)($unit['so_luong_san_pham_trong_don_vi'] ?? 0) <= 0) {
                continue;
            }

            $unitId = $unit['id'] ?? null;

            if ($unitId && in_array($unitId, $existingIds)) {
                // Cập nhật đơn vị hiện có
                $existingUnit = DonViQuyDoi::find($unitId);
                if ($existingUnit) {
                    $unitMaHang = !empty($unit['ma_hang']) ? $unit['ma_hang'] : $existingUnit->ma_hang;
                    $existingUnit->update([
                        'don_vi_chuan_id' => $unit['don_vi_chuan_id'] ?? null,
                        // SAFETY: dùng !empty() để KHÔNG cho phép chuỗi rỗng "" đè lên giá trị DB
                        'ten_don_vi' => !empty($unit['ten_don_vi']) ? $unit['ten_don_vi'] : $existingUnit->ten_don_vi,
                        'so_luong_san_pham_trong_don_vi' => (float)($unit['so_luong_san_pham_trong_don_vi'] ?? 1),
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
                $unitMaHang = !empty($unit['ma_hang']) ? $unit['ma_hang'] : $this->generateUniqueMaHang();
                $newUnit = DonViQuyDoi::create([
                    'variant_id' => $variantId,
                    'product_id' => $productId,
                    'don_vi_chuan_id' => $unit['don_vi_chuan_id'] ?? null,
                    // SAFETY: bỏ qua tạo mới nếu thiếu tên đơn vị hợp lệ
                    'ten_don_vi' => !empty($unit['ten_don_vi']) ? $unit['ten_don_vi'] : 'Đơn vị',
                    'so_luong_san_pham_trong_don_vi' => (float)($unit['so_luong_san_pham_trong_don_vi'] ?? 1),
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
