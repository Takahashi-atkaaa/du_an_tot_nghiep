<?php

namespace App\Http\Controllers\Admin\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\PhieuNhap\ImportPhieuNhapRequest;
use App\Imports\PhieuNhapImport;
use App\Exports\PhieuNhapDanhSachExport;
use App\Exports\PhieuNhapTemplateExport;
use App\Models\Phieu;
use App\Models\PhieuNhap;
use App\Models\LoHang;
use App\Models\ChiTietLoHang;
use App\Models\ChiTietPhieu;
use App\Models\BienTheSanPham;
use App\Models\DonViQuyDoi;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class PhieuNhapApiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $q = $request->query('q');
        $loai = $request->query('loai_nhap');
        $tuNgay = $request->query('tu_ngay');
        $denNgay = $request->query('den_ngay');

        $query = PhieuNhap::with([
            'phieu.nhaCungCap',
            'phieu.nguoiDung',
            'hoaDon',
            'phieuXuatGoc',
            'chiTietPhieu',
        ])
            ->whereHas('phieu', fn($p) => $p->where('loai_phieu_enum', 'like', 'nhap%'))
            ->orderByDesc('id');

        if (!empty($loai)) {
            $query->where('loai_nhap', $loai);
        }
        if (!empty($tuNgay)) {
            $query->whereDate('created_at', '>=', $tuNgay);
        }
        if (!empty($denNgay)) {
            $query->whereDate('created_at', '<=', $denNgay);
        }

        $items = $query->paginate(15)->withQueryString();

        return response()->json([
            'success' => true,
            'data' => $items,
        ]);
    }

    public function show(int $id): JsonResponse
    {
        $phieuNhap = PhieuNhap::with([
            'phieu.nhaCungCap',
            'phieu.nguoiDung',
            'hoaDon',
            'chiTietPhieu' => fn($ct) => $ct->with('variant.product', 'chiTietLoHang'),
        ])->find($id);

        if (!$phieuNhap) {
            return response()->json(['success' => false, 'message' => 'Phiếu nhập không tồn tại.'], 404);
        }

        $data = $phieuNhap->toArray();
        foreach ($data['chi_tiet_phieu'] ?? [] as &$ct) {
            if (!empty($ct['variant'])) {
                $ct['thuoc_tinh_labels'] = $ct['variant']['thuoc_tinh_labels'] ?? [];
            }
        }

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'loai_nhap' => 'required|in:mua_hang,tra_lai_tu_khach',
            'id_nha_cung_cap' => 'nullable|integer|exists:nha_cung_cap,id',
            'id_hoa_don' => 'nullable|integer|exists:hoa_don,id',
            'id_phieu_xuat_goc' => 'nullable|integer|exists:phieu,id',
            'ghi_chu' => 'nullable|string',
            'tao_lo_moi' => 'required|in:0,1',
            'id_lo_hang' => 'required_if:tao_lo_moi,0|nullable|integer|exists:lo_hang,id',
            'chi_tiet' => 'required|array|min:1',
            'chi_tiet.*.variant_id' => 'required|integer|exists:bien_the_san_pham,id',
            'chi_tiet.*.don_vi_id' => 'nullable',
            'chi_tiet.*.so_luong_san_pham_trong_don_vi' => 'nullable|integer|min:1',
            'chi_tiet.*.so_luong_nhap' => 'required|numeric|min:0.0001',
            'chi_tiet.*.so_luong_thuc' => 'nullable|numeric|min:0',
            'chi_tiet.*.gia_nhap' => 'required|numeric|min:0',
            'chi_tiet.*.han_su_dung' => 'required|date',
        ], [
            'chi_tiet.required' => 'Phải có ít nhất một sản phẩm.',
            'chi_tiet.*.variant_id.required' => 'Mỗi sản phẩm phải có variant_id.',
            'chi_tiet.*.so_luong_nhap.min' => 'Số lượng nhập phải lớn hơn 0.',
            'id_lo_hang.required_if' => 'Vui lòng chọn lô hàng khi không tạo lô mới.',
        ]);

            $loaiPhieuEnum = $data['loai_nhap'] === 'mua_hang' ? 'nhap_mua_hang' : 'nhap_tra_lai_tu_khach';

        $result = DB::transaction(function () use ($data, $loaiPhieuEnum) {
            $idNguoiDung = auth()->id();

            $phieu = Phieu::create([
                'loai_phieu' => $data['loai_nhap'] === 'mua_hang' ? 'Nhập hàng' : 'Trả hàng từ khách',
                'loai_phieu_enum' => $loaiPhieuEnum,
                'id_nguoi_dung' => $idNguoiDung,
                'id_nha_cung_cap' => $data['id_nha_cung_cap'] ?? null,
                'id_hoa_don' => $data['id_hoa_don'] ?? null,
                'ghi_chu' => $data['ghi_chu'] ?? null,
            ]);

            $phieuNhap = PhieuNhap::create([
                'id_phieu' => $phieu->id,
                'loai_nhap' => $data['loai_nhap'],
                'id_hoa_don' => $data['id_hoa_don'] ?? null,
                'id_phieu_xuat_goc' => $data['id_phieu_xuat_goc'] ?? null,
                'ghi_chu' => $data['ghi_chu'] ?? null,
            ]);

            $variantIds = collect($data['chi_tiet'])->pluck('variant_id')->unique()->all();
            $variants = BienTheSanPham::whereIn('id', $variantIds)->get()->keyBy('id');

            $unitLookup = $this->resolveDonViLookup($data['chi_tiet'], $variants);

            if ($data['tao_lo_moi'] == '1') {
                $loHang = LoHang::create([
                    'id_phieu' => $phieu->id,
                    'id_nha_cung_cap' => $data['id_nha_cung_cap'] ?? null,
                    'ma_lo' => 'PN-' . $phieu->id,
                    'ngay_nhap' => now()->toDateString(),
                ]);
                $maLo = $loHang->ma_lo;
            } else {
                $loHang = LoHang::findOrFail($data['id_lo_hang']);
                $maLo = $loHang->ma_lo ?: ('L-' . $loHang->id);
            }

            foreach ($data['chi_tiet'] as $ct) {
                $normalized = $this->normalizeChiTiet($ct, $variants, $unitLookup);

                // UPSERT chi_tiet_lo_hang theo (id_lo_hang, variant_id, han_su_dung)
                // Nếu đã có: cộng dồn số lượng + giá bình quân gia quyền
                // Nếu chưa: tạo mới với số lượng + giá chuẩn hóa về đơn vị cơ bản
                $chiTietLoHang = ChiTietLoHang::firstOrNew([
                    'id_lo_hang' => $loHang->id,
                    'variant_id' => $normalized['variant_id'],
                    'han_su_dung' => $ct['han_su_dung'],
                ]);

                $isExisting = $chiTietLoHang->exists;

                $chiTietLoHang->id_san_pham = $normalized['product_id'];

                if ($isExisting) {
                    $oldQty = (int)$chiTietLoHang->so_luong_nhap;
                    $oldPrice = (float)$chiTietLoHang->gia_nhap;
                    $newQty = $oldQty + (int)$normalized['so_luong_co_ban'];
                    $chiTietLoHang->so_luong_nhap = $newQty;
                    $chiTietLoHang->so_luong_ton += (int)$normalized['so_luong_co_ban'];
                    $chiTietLoHang->gia_nhap = $newQty > 0
                        ? round((($oldPrice * $oldQty) + ($normalized['gia_nhap_co_ban'] * $normalized['so_luong_co_ban'])) / $newQty, 2)
                        : $normalized['gia_nhap_co_ban'];
                } else {
                    $chiTietLoHang->so_luong_nhap = (int)$normalized['so_luong_co_ban'];
                    $chiTietLoHang->so_luong_ton = (int)$normalized['so_luong_co_ban'];
                    $chiTietLoHang->gia_nhap = $normalized['gia_nhap_co_ban'];
                }

                $chiTietLoHang->save();

                ChiTietPhieu::create([
                    'id_phieu' => $phieu->id,
                    'id_san_pham' => $normalized['product_id'],
                    'variant_id' => $normalized['variant_id'],
                    'id_lo_hang' => $loHang->id,
                    'id_chi_tiet_lo_hang' => $chiTietLoHang->id,
                    'so_luong' => $normalized['so_luong_co_ban'],
                    'gia_nhap' => $normalized['gia_nhap_co_ban'],
                    'ma_lo' => $maLo,
                    'han_su_dung' => $ct['han_su_dung'],
                    'so_luong_con_lai' => $normalized['so_luong_co_ban'],
                    'ghi_chu' => $normalized['ghi_chu'],
                ]);
            }

            // ChiTietLoHangObserver đã tự động đồng bộ tổng tồn
            // trên bien_the_san_pham.so_luong_ton sau khi ChiTietLoHang::save()
            // (gọi INSERT/UPDATE qua firstOrNew ở trên).
            // Không cần increment thủ công ở đây nữa (trước đây gây double-counting).

            return $phieuNhap->load('phieu', 'chiTietPhieu.variant', 'chiTietPhieu.chiTietLoHang');
        });

        return response()->json([
            'success' => true,
            'message' => 'Tạo phiếu nhập thành công.',
            'data' => $result,
        ], 201);
    }

    /**
     * Xác thực và chuẩn hóa một dòng chi tiết phiếu nhập:
     *  - Kiểm tra don_vi_id (nếu có) thuộc đúng variant_id
     *  - Lấy hệ số quy đổi từ hệ thống, BỎ QUA giá trị client gửi lên
     *  - Quy đổi số lượng & giá về đơn vị cơ bản
     *  - Trả về cấu trúc dùng chung cho cả nhánh tạo lô mới và lô có sẵn
     */
    private function normalizeChiTiet(array $ct, $variants, array $unitLookup): array
    {
        $variantId = (int)$ct['variant_id'];
        $variant = $variants->get($variantId);

        if (!$variant) {
            throw ValidationException::withMessages([
                "chi_tiet.{$variantId}.variant_id" => 'Variant không tồn tại.',
            ]);
        }

        $donViIdRaw = $ct['don_vi_id'] ?? null;
        $donViId = ($donViIdRaw === '' || $donViIdRaw === null || $donViIdRaw === '__base__') ? null : (int)$donViIdRaw;

        $heSoQuyDoi = 1;
        $tenDonViNhap = null;

        if ($donViId !== null) {
            $unit = $unitLookup[$variantId][$donViId] ?? null;
            if (!$unit) {
                throw ValidationException::withMessages([
                    "chi_tiet.{$variantId}.don_vi_id" => 'Đơn vị quy đổi không thuộc biến thể đã chọn.',
                ]);
            }
            $heSoQuyDoi = (float)$unit->so_luong_san_pham_trong_don_vi;
            if ($heSoQuyDoi < 1) {
                throw ValidationException::withMessages([
                    "chi_tiet.{$variantId}.don_vi_id" => 'Hệ số đơn vị không hợp lệ.',
                ]);
            }
            $tenDonViNhap = $unit->ten_don_vi;
        }

        $slNhap = (float)$ct['so_luong_nhap'];
        if ($slNhap <= 0) {
            throw ValidationException::withMessages([
                "chi_tiet.{$variantId}.so_luong_nhap" => 'Số lượng nhập phải lớn hơn 0.',
            ]);
        }

        $soLuongCoBan = round($slNhap * $heSoQuyDoi, 4);

        $giaNhapNhap = (float)$ct['gia_nhap'];
        if ($giaNhapNhap < 0) {
            throw ValidationException::withMessages([
                "chi_tiet.{$variantId}.gia_nhap" => 'Giá nhập không được âm.',
            ]);
        }
        
        // Giá nhập luôn lưu theo đơn vị cơ bản; nếu user nhập theo đơn vị quy đổi thì chia cho hệ số
        $giaNhapCoBan = $heSoQuyDoi > 1
            ? round($giaNhapNhap / $heSoQuyDoi, 2)
            : round($giaNhapNhap, 2);

        $ghiChu = null;
        if ($heSoQuyDoi > 1) {
            $ghiChu = sprintf(
                'Nhập %s %s × %s (hệ số) = %s %s',
                rtrim(rtrim(number_format($slNhap, 2, '.', ''), '0'), '.'),
                $tenDonViNhap ?: 'đơn vị quy đổi',
                rtrim(rtrim(number_format($heSoQuyDoi, 2, '.', ''), '0'), '.'),
                rtrim(rtrim(number_format($soLuongCoBan, 2, '.', ''), '0'), '.'),
                $variant->ten_don_vi ?: 'đơn vị cơ bản'
            );
        }

        return [
            'variant_id' => $variantId,
            'product_id' => $variant->product_id,
            'so_luong_co_ban' => $soLuongCoBan,
            'gia_nhap_co_ban' => $giaNhapCoBan,
            'ghi_chu' => $ghiChu,
        ];
    }

    /**
     * Chuẩn bị map: variant_id => don_vi_quy_doi.id => DonViQuyDoi (đã eager load sẵn)
     * để tra cứu nhanh khi normalize từng dòng.
     */
    private function resolveDonViLookup(array $chiTiet, $variants): array
    {
        $donViIds = [];
        foreach ($chiTiet as $ct) {
            $donViIdRaw = $ct['don_vi_id'] ?? null;
            if ($donViIdRaw === '' || $donViIdRaw === null || $donViIdRaw === '__base__') {
                continue;
            }
            $donViIds[] = (int)$donViIdRaw;
        }
        $donViIds = array_values(array_unique(array_filter($donViIds)));

        $lookup = [];
        if (!empty($donViIds)) {
            $units = DonViQuyDoi::whereIn('id', $donViIds)->get();
            foreach ($units as $unit) {
                $lookup[$unit->variant_id][$unit->id] = $unit;
            }
        }
        return $lookup;
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $phieuNhap = PhieuNhap::with('phieu')->find($id);
        if (!$phieuNhap) {
            return response()->json(['success' => false, 'message' => 'Phiếu nhập không tồn tại.'], 404);
        }

        $data = $request->validate([
            'loai_nhap' => 'required|in:mua_hang,tra_lai_tu_khach',
            'id_nha_cung_cap' => 'nullable|integer|exists:nha_cung_cap,id',
            'ghi_chu' => 'nullable|string',
        ]);

        $phieuNhap->phieu->update([
            'id_nha_cung_cap' => $data['id_nha_cung_cap'] ?? null,
            'ghi_chu' => $data['ghi_chu'] ?? null,
        ]);
        $phieuNhap->update([
            'loai_nhap' => $data['loai_nhap'],
            'ghi_chu' => $data['ghi_chu'] ?? null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật phiếu nhập thành công.',
            'data' => $phieuNhap->load('phieu'),
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        abort_unless(\userHasPermission('xoa_phieu_nhap'), 403, 'Bạn không có quyền xóa phiếu nhập.');
        $phieuNhap = PhieuNhap::with('phieu.chiTietPhieu.chiTietLoHang')->find($id);

        if (!$phieuNhap) {
            return response()->json(['success' => false, 'message' => 'Phiếu nhập không tồn tại.'], 404);
        }

        $idPhieu = $phieuNhap->id_phieu;

        // Gom tất cả chi_tiet_lo_hang do phiếu này tạo (theo id_chi_tiet_lo_hang đã track trên chi_tiet_phieu)
        $dsChiTietLoHang = ChiTietLoHang::whereHas('chiTietPhieu', fn($q) => $q->where('id_phieu', $idPhieu))->get();

        // Neu bat ky chi_tiet_lo_hang nao con ton > 0 -> KHONG cho xoa
        $conTon = $dsChiTietLoHang->where('so_luong_ton', '>', 0);
        if ($conTon->isNotEmpty()) {
            $maLoTon = $conTon->map(fn($ct) => $ct->loHang?->ma_lo ?: ('L-' . $ct->id_lo_hang))->unique()->implode(', ');
            return response()->json([
                'success' => false,
                'message' => "Khong the xoa phieu nhap vi cac lo sau dang co ton: {$maLoTon}. Vui long xuat het hang truoc.",
            ], 422);
        }

        DB::transaction(function () use ($phieuNhap, $idPhieu, $dsChiTietLoHang) {
            // 1. Xoa chi_tiet_phieu (ChiTietPhieuObserver da la no-op sau khi sua)
            ChiTietPhieu::where('id_phieu', $idPhieu)->delete();

            // 2. Xoa chi_tiet_lo_hang (ChiTietLoHangObserver::deleted() se sync tong ton bien_the_san_pham)
            $chiTietLoIds = $dsChiTietLoHang->pluck('id');
            if ($chiTietLoIds->isNotEmpty()) {
                ChiTietLoHang::whereIn('id', $chiTietLoIds)->delete();
            }

            // 3. Xoa lo_hang (chi xoa neu khong con chi_tiet_lo_hang nao tham chieu)
            $idLoHang = $dsChiTietLoHang->pluck('id_lo_hang')->unique();
            foreach ($idLoHang as $idLo) {
                if (!ChiTietLoHang::where('id_lo_hang', $idLo)->exists()) {
                    LoHang::where('id', $idLo)->delete();
                }
            }

            // 4. Cuoi cung xoa phieu + phieu_nhap
            $phieuNhap->phieu->delete();
            $phieuNhap->delete();
        });

        return response()->json([
            'success' => true,
            'message' => 'Da xoa phieu nhap.',
        ]);
    }

    public function danhSachLoHang(Request $request): JsonResponse
    {
        $variantId = $request->query('variant_id');
        $query = LoHang::with('nhaCungCap', 'chiTietLoHang')
            ->whereHas('chiTietLoHang', fn($q) => $q->where('so_luong_ton', '>', 0));

        if ($variantId) {
            $query->whereHas('chiTietLoHang', fn($q) => $q->where('variant_id', $variantId));
        }

        $items = $query->orderByDesc('id')->limit(50)->get();

        return response()->json([
            'success' => true,
            'data' => $items,
        ]);
    }

    public function downloadTemplate(): BinaryFileResponse
    {
        $fileName = 'mau-import-phieu-nhap-' . now()->format('Ymd') . '.xlsx';
        
        return Excel::download(new PhieuNhapTemplateExport(), $fileName);
    }

    public function downloadTemplateCsvLegacy(): StreamedResponse
    {
        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="phieu-nhap-template.csv"',
        ];

        $callback = function () {
            $output = fopen('php://output', 'w');
            fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF)); // BOM for Excel UTF-8

            // Header 8 cột: Ma_vach (key tra cứu) + các cột tham khảo + So_luong/Gia_nhap/Han_su_dung
            fputcsv($output, [
                'Ma_vach',
                'Ten_san_pham',
                'Ten_bien_the',
                'Thuoc_tinh',
                'Ten_don_vi',
                'So_luong',
                'Gia_nhap',
                'Han_su_dung',
            ], ';');

            // Lấy danh sách biến thể từ database, eager load product + don_vi_quy_doi để dựng dòng tham khảo
            $variants = BienTheSanPham::with(['product', 'units'])
                ->whereNotNull('ma_vach')
                ->where('ma_vach', '!=', '')
                ->orderBy('product_id')
                ->orderBy('ma_vach')
                ->limit(100)
                ->get();

            foreach ($variants as $variant) {
                $tenSanPham = $variant->product->ten_san_pham ?? '';
                $tenBienThe = $variant->ten_bien_the ?? '';
                $thuocTinhArr = method_exists($variant, 'getThuocTinhLabelsAttribute')
                    ? $variant->thuoc_tinh_labels
                    : [];
                // Accessor trả về danh sách tên thuộc tính (cả nhóm cha và giá trị con)
                // Ví dụ: ["M","Đen"] (size) hoặc ["Size","XL","Màu","Đen"] đa nhóm
                // Lấy toàn bộ, gộp thành chuỗi "Size XL, Màu Đen" hoặc "M, Đen"
                if (is_array($thuocTinhArr) && count($thuocTinhArr) > 0) {
                    // Nếu cặp (nhóm, giá trị) liên tiếp thì ghép lại để gọn
                    $pairs = [];
                    $arr = array_values($thuocTinhArr);
                    $n = count($arr);
                    $i = 0;
                    while ($i < $n) {
                        $a = $arr[$i] ?? '';
                        $b = $arr[$i + 1] ?? null;
                        // Ghép nhóm-giá-trị chỉ khi $b tồn tại và không rỗng
                        if ($b !== null && $b !== '') {
                            $pairs[] = trim($a . ' ' . $b);
                            $i += 2;
                        } else {
                            if ($a !== '') {
                                $pairs[] = $a;
                            }
                            $i += 1;
                        }
                    }
                    $thuocTinhText = implode(', ', $pairs);
                } else {
                    $thuocTinhText = '';
                }

                // Dòng 1: nhập theo đơn vị cơ bản (biến thể)
                if (!empty($variant->ma_vach)) {
                    fputcsv($output, [
                        $variant->ma_vach,
                        $tenSanPham,
                        $tenBienThe,
                        $thuocTinhText,
                        '', // Ten_don_vi - đơn vị cơ bản, bỏ trống
                        '', // So_luong - người dùng tự điền
                        $variant->gia_von ?? '', // Gia_nhap - gợi ý
                        '', // Han_su_dung - người dùng tự điền
                    ], ';');
                }

                // Mỗi đơn vị quy đổi có mã vạch -> 1 dòng tham khảo riêng
                foreach ($variant->units ?? [] as $unit) {
                    if (empty($unit->ma_vach)) {
                        continue;
                    }
                    $tenDonVi = trim(($unit->ten_don_vi ?? '') . ' (x' . ($unit->so_luong_san_pham_trong_don_vi ?? 1) . ')');
                    fputcsv($output, [
                        $unit->ma_vach,
                        $tenSanPham,
                        $tenBienThe,
                        $thuocTinhText,
                        $tenDonVi,
                        '',
                        $unit->gia_von_quy_doi ?? '',
                        '',
                    ], ';');
                }
            }

            fputcsv($output, [], ';');
            fputcsv($output, ['# Huong dan:'], ';');
            fputcsv($output, ['# - Ma_vach (BAT BUOC): Ma vach bien the HOAC ma vach don vi quy doi (Thung/ Hop...). He thong tu tim.'], ';');
            fputcsv($output, ['# - Ten_san_pham / Ten_bien_the / Thuoc_tinh / Ten_don_vi: Chi tham khao, KHONG anh huong import. Co the de trong.'], ';');
            fputcsv($output, ['#   + Ten_san_pham: ten cha (vi du: Ao thun nam co tron)'], ';');
            fputcsv($output, ['#   + Ten_bien_the: ten bien the (vi du: XL, L, ...)'], ';');
            fputcsv($output, ['#   + Thuoc_tinh: gia tri thuoc tinh (vi du: Den, Trang, ...)'], ';');
            fputcsv($output, ['#   + Ten_don_vi: don vi quy doi (vi du: Thung (x20), Hop 10 vien). De trong neu nhap theo don vi co ban.'], ';');
            fputcsv($output, ['# - So_luong (BAT BUOC): So nguyen duong, la so luong theo don vi cua Ma_vach.'], ';');
            fputcsv($output, ['#   Vi du: Ma_vach la Thung (x20), So_luong = 5 nghia la 5 thung = 100 san pham co ban.'], ';');
            fputcsv($output, ['# - Gia_nhap (BAT BUOC): Gia theo don vi cua Ma_vach (vi du: gia/thung neu Ma_vach la thung).'], ';');
            fputcsv($output, ['# - Han_su_dung: Dinh dang YYYY-MM-DD hoac DD/MM/YYYY. Bo trong = mac dinh 2099-12-31.'], ';');
            fputcsv($output, ['# - Cac dong trung Ma_vach + Han_su_dung se tu dong gop (cong don so luong, tinh lai gia binh quan gia quyen).'], ';');
            fputcsv($output, ['# - He thong tu quy doi ve don vi co ban khi luu kho (nhan So_luong voi so_luong_san_pham_trong_don_vi).'], ';');
            fputcsv($output, ['# - Cac dong trong se bi bo qua khi import.'], ';');
            fputcsv($output, ['# - De biet san pham co nhung bien the/ma vach nao, hay vao Quan ly san pham xem truoc khi nhap.'], ';');

            fclose($output);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function importExcel(ImportPhieuNhapRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();
            
            $file = $request->file('file');
            $idNhaCungCap = $validated['id_nha_cung_cap'] ?? null;
            $loaiNhap = $validated['loai_nhap'];
            $ghiChu = $validated['ghi_chu'] ?? null;
            $idNguoiDung = auth()->id();

            $import = new PhieuNhapImport(
                idNhaCungCap: $idNhaCungCap,
                loaiNhap: $loaiNhap,
                ghiChu: $ghiChu,
                idNguoiDung: $idNguoiDung
            );

            Excel::import($import, $file);

            $errors = $import->getErrors();
            $rowCount = $import->getRowCount();
            $insertedCount = $import->getInsertedCount();

            if ($insertedCount === 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Import thất bại: Không có dòng hợp lệ nào.',
                    'errors' => $errors,
                    'summary' => [
                        'phieu_created' => 0,
                        'chi_tiet_created' => 0,
                        'row_total' => $rowCount,
                        'row_skipped' => $rowCount,
                    ],
                ], 422);
            }

            $msg = "Import thành công: Tạo 1 phiếu nhập với {$insertedCount} chi tiết.";
            if (!empty($errors)) {
                $msg .= " Bỏ qua " . ($rowCount - $insertedCount) . " dòng lỗi.";
            }

            return response()->json([
                'success' => true,
                'message' => $msg,
                'summary' => [
                    'phieu_created' => 1,
                    'chi_tiet_created' => $insertedCount,
                    'row_total' => $rowCount,
                    'row_skipped' => $rowCount - $insertedCount,
                ],
                'errors' => $errors,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Import thất bại: ' . $e->getMessage(),
            ], 422);
        }
    }

    public function exportDanhSach(Request $request)
    {
        $filters = $this->parseDateRange($request);

        // Validate loai_nhap neu co
        if (!empty($filters['loai_nhap'])
            && !in_array($filters['loai_nhap'], ['mua_hang', 'tra_lai_tu_khach'], true)
        ) {
            return response()->json(['success' => false, 'message' => 'loai_nhap khong hop le.'], 422);
        }

        $fileName = 'phieu-nhap-danh-sach-' . now()->format('Ymd_His') . '.xlsx';

        return Excel::download(new PhieuNhapDanhSachExport($filters), $fileName);
    }

    /**
     * Chuan hoa start_date/end_date tu Request.
     *
     * - start_date (YYYY-MM-DD) -> 'YYYY-MM-DD 00:00:00' (Carbon::startOfDay)
     * - end_date   (YYYY-MM-DD) -> 'YYYY-MM-DD 23:59:59' (Carbon::endOfDay)
     * - Neu end_date < start_date thi tra loi 422.
     *
     * Tra ve mang ['tu_ngay' => Carbon|null, 'den_ngay' => Carbon|null, 'loai_nhap' => ...].
     */
    private function parseDateRange(Request $request): array
    {
        $filters = [
            'loai_nhap' => $request->query('loai_nhap'),
            'tu_ngay' => null,
            'den_ngay' => null,
        ];

        $tu = $request->query('tu_ngay') ?? $request->query('start_date');
        $den = $request->query('den_ngay') ?? $request->query('end_date');

        try {
            if (!empty($tu)) {
                $filters['tu_ngay'] = \Carbon\Carbon::createFromFormat('Y-m-d', $tu)->startOfDay();
            }
            if (!empty($den)) {
                $filters['den_ngay'] = \Carbon\Carbon::createFromFormat('Y-m-d', $den)->endOfDay();
            }
        } catch (\Exception $e) {
            // bo qua, de validator phia xu ly request xu ly neu can
        }

        if ($filters['tu_ngay'] && $filters['den_ngay']
            && $filters['tu_ngay']->gt($filters['den_ngay'])
        ) {
            abort(422, 'Tu ngay phai nho hon hoac bang den ngay.');
        }

        return $filters;
    }

    public function exportChiTiet(int $id): StreamedResponse
    {
        $phieuNhap = PhieuNhap::with([
            'phieu' => fn($p) => $p->with('nhaCungCap', 'nguoiDung'),
            'chiTietPhieu.variant.product',
            'chiTietPhieu.chiTietLoHang.loHang',
        ])->find($id);

        if (!$phieuNhap) {
            abort(404, 'Phiếu nhập không tồn tại.');
        }

        $fileName = 'phieu-nhap-chi-tiet-' . $id . '-' . now()->format('Ymd_His') . '.csv';

        return response()->stream(function () use ($phieuNhap) {
            $output = fopen('php://output', 'w');
            fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF)); // BOM for Excel UTF-8

            // Header info
            fputcsv($output, ['PHIEU NHAP HANG'], ';');
            fputcsv($output, ['Ma phieu:', 'PN' . str_pad($phieuNhap->id, 5, '0', STR_PAD_LEFT)], ';');
            fputcsv($output, ['Ngay:', $phieuNhap->created_at->format('d/m/Y H:i')], ';');
            fputcsv($output, ['Loai:', $phieuNhap->loai_nhap === 'mua_hang' ? 'Mua hang' : 'Tra lai tu khach'], ';');
            fputcsv($output, ['Nha cung cap:', $phieuNhap->phieu->nhaCungCap->ten_nha_cung_cap ?? 'Khong co'], ';');
            fputcsv($output, ['Nguoi tao:', $phieuNhap->phieu->nguoiDung->ho_ten ?? 'N/A'], ';');
            fputcsv($output, ['Ghi chu:', $phieuNhap->ghi_chu ?? ''], ';');
            fputcsv($output, [], ';');

            // Table header
            fputcsv($output, [
                'STT', 'San_pham', 'Bien_the', 'Ma_vach', 'Lo', 'So_luong', 'Gia_nhap', 'Thanh_tien', 'Han_su_dung'
            ], ';');

            // Data rows
            $stt = 1;
            $tongTien = 0;
            foreach ($phieuNhap->chiTietPhieu as $ct) {
                $thanhTien = $ct->so_luong * $ct->gia_nhap;
                $tongTien += $thanhTien;
                $tenSp = $ct->variant?->product?->ten_san_pham ?? '';
                $tenBt = $ct->variant?->ten_bien_the ?? '';
                $tenFull = $tenSp . ($tenBt ? ' - ' . $tenBt : '');
                $maLo = $ct->chiTietLoHang?->loHang?->ma_lo ?: ($ct->ma_lo ?: ('L-' . ($ct->id_lo_hang ?? '')));

                fputcsv($output, [
                    $stt++,
                    $tenSp,
                    $tenBt,
                    $ct->variant?->ma_vach ?? '',
                    $maLo,
                    $ct->so_luong,
                    number_format($ct->gia_nhap, 0, ',', '.'),
                    number_format($thanhTien, 0, ',', '.'),
                    $ct->han_su_dung ? date('d/m/Y', strtotime($ct->han_su_dung)) : '',
                ], ';');
            }

            // Total
            fputcsv($output, [], ';');
            fputcsv($output, ['', '', '', '', '', 'TONG CONG:', number_format($tongTien, 0, ',', '.')], ';');

            fclose($output);
        }, 200, [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => "attachment; filename=\"$fileName\"",
        ]);
    }
}
