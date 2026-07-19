<?php

namespace App\Http\Controllers\Admin\Api;

use App\Http\Controllers\Controller;
use App\Imports\PhieuNhapImport;
use App\Models\Phieu;
use App\Models\PhieuNhap;
use App\Models\LoHang;
use App\Models\ChiTietLoHang;
use App\Models\ChiTietPhieu;
use App\Models\BienTheSanPham;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PhieuNhapApiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $q = $request->query('q');
        $loai = $request->query('loai_nhap');
        $tuNgay = $request->query('tu_ngay');
        $denNgay = $request->query('den_ngay');

        $query = PhieuNhap::with([
            'phieu' => fn($p) => $p->with('nhaCungCap', 'nguoiDung'),
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
            'phieu',
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
            'chi_tiet.*.don_vi_id' => 'nullable|string|max:50',
            'chi_tiet.*.so_luong_san_pham_trong_don_vi' => 'nullable|integer|min:1',
            'chi_tiet.*.so_luong_nhap' => 'required|integer|min:1',
            'chi_tiet.*.so_luong_thuc' => 'nullable|integer|min:0',
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

            if ($data['tao_lo_moi'] == '1') {
                $variantIds = collect($data['chi_tiet'])->pluck('variant_id')->unique()->all();
                $variantMap = BienTheSanPham::whereIn('id', $variantIds)
                    ->pluck('product_id', 'id')
                    ->toArray();

                $loHang = LoHang::create([
                    'id_phieu' => $phieu->id,
                    'id_nha_cung_cap' => $data['id_nha_cung_cap'] ?? null,
                    'ngay_nhap' => now()->toDateString(),
                ]);

                foreach ($data['chi_tiet'] as $ct) {
                    // Tính số lượng thực (đã quy đổi về đơn vị cơ bản nếu user nhập theo đơn vị quy đổi)
                    $soLuongTrongDonVi = (int)($ct['so_luong_san_pham_trong_don_vi'] ?? 1);
                    $slNhap = (int)$ct['so_luong_nhap'];
                    $slThuc = $soLuongTrongDonVi > 1
                        ? (int)($ct['so_luong_thuc'] ?? ($slNhap * $soLuongTrongDonVi))
                        : $slNhap;

                    // Tính ghi chú cho chi_tiet_phieu (lưu thông tin đơn vị nhập)
                    $donViNhap = $soLuongTrongDonVi > 1
                        ? "Nhập {$slNhap} đơn vị quy đổi × {$soLuongTrongDonVi}"
                        : null;

                    $chiTietLoHang = ChiTietLoHang::create([
                        'id_lo_hang' => $loHang->id,
                        'id_san_pham' => $variantMap[$ct['variant_id']] ?? null,
                        'variant_id' => $ct['variant_id'],
                        'so_luong_nhap' => $slThuc,
                        'so_luong_ton' => $slThuc,
                        'gia_nhap' => $ct['gia_nhap'],
                        'han_su_dung' => $ct['han_su_dung'],
                    ]);

                    ChiTietPhieu::create([
                        'id_phieu' => $phieu->id,
                        'id_san_pham' => $variantMap[$ct['variant_id']] ?? null,
                        'variant_id' => $ct['variant_id'],
                        'id_lo_hang' => $loHang->id,
                        'id_chi_tiet_lo_hang' => $chiTietLoHang->id,
                        'so_luong' => $slThuc,
                        'gia_nhap' => $ct['gia_nhap'],
                        'han_su_dung' => $ct['han_su_dung'],
                        'so_luong_con_lai' => $slThuc,
                        'ghi_chu' => $donViNhap,
                    ]);
                }
            } else {
                $idLoHang = $data['id_lo_hang'];
                $variantIds = collect($data['chi_tiet'])->pluck('variant_id')->unique()->all();
                $variantMap = BienTheSanPham::whereIn('id', $variantIds)
                    ->pluck('product_id', 'id')
                    ->toArray();

                foreach ($data['chi_tiet'] as $ct) {
                    $soLuongTrongDonVi = (int)($ct['so_luong_san_pham_trong_don_vi'] ?? 1);
                    $slNhap = (int)$ct['so_luong_nhap'];
                    $slThuc = $soLuongTrongDonVi > 1
                        ? (int)($ct['so_luong_thuc'] ?? ($slNhap * $soLuongTrongDonVi))
                        : $slNhap;

                    $donViNhap = $soLuongTrongDonVi > 1
                        ? "Nhập {$slNhap} đơn vị quy đổi × {$soLuongTrongDonVi}"
                        : null;

                    $chiTietLoHang = ChiTietLoHang::where('id_lo_hang', $idLoHang)
                        ->where('variant_id', $ct['variant_id'])
                        ->whereDate('han_su_dung', $ct['han_su_dung'])
                        ->first();

                    ChiTietLoHang::create([
                        'id_lo_hang' => $idLoHang,
                        'id_san_pham' => $variantMap[$ct['variant_id']] ?? null,
                        'variant_id' => $ct['variant_id'],
                        'so_luong_nhap' => $slThuc,
                        'so_luong_ton' => $slThuc,
                        'gia_nhap' => $ct['gia_nhap'],
                        'han_su_dung' => $ct['han_su_dung'],
                    ]);

                    ChiTietPhieu::create([
                        'id_phieu' => $phieu->id,
                        'id_san_pham' => $variantMap[$ct['variant_id']] ?? null,
                        'variant_id' => $ct['variant_id'],
                        'id_lo_hang' => $idLoHang,
                        'id_chi_tiet_lo_hang' => $chiTietLoHang?->id,
                        'so_luong' => $slThuc,
                        'gia_nhap' => $ct['gia_nhap'],
                        'han_su_dung' => $ct['han_su_dung'],
                        'so_luong_con_lai' => $slThuc,
                        'ghi_chu' => $donViNhap,
                    ]);
                }
            }

            // Cộng tồn kho cho bien_the_san_pham.so_luong_ton theo số lượng THỰC (đã quy đổi)
            foreach ($data['chi_tiet'] as $ct) {
                $soLuongTrongDonVi = (int)($ct['so_luong_san_pham_trong_don_vi'] ?? 1);
                $slNhap = (int)$ct['so_luong_nhap'];
                $slThuc = $soLuongTrongDonVi > 1
                    ? (int)($ct['so_luong_thuc'] ?? ($slNhap * $soLuongTrongDonVi))
                    : $slNhap;
                BienTheSanPham::where('id', $ct['variant_id'])
                    ->increment('so_luong_ton', $slThuc);
            }

            return $phieuNhap->load('phieu', 'chiTietPhieu.variant', 'chiTietPhieu.chiTietLoHang');
        });

        return response()->json([
            'success' => true,
            'message' => 'Tạo phiếu nhập thành công.',
            'data' => $result,
        ], 201);
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
        $phieuNhap = PhieuNhap::with('phieu')->find($id);
        if (!$phieuNhap) {
            return response()->json(['success' => false, 'message' => 'Phiếu nhập không tồn tại.'], 404);
        }

        DB::transaction(function () use ($phieuNhap) {
            ChiTietPhieu::where('id_phieu', $phieuNhap->id_phieu)->delete();
            $phieuNhap->phieu->delete();
            $phieuNhap->delete();
        });

        return response()->json([
            'success' => true,
            'message' => 'Đã xóa phiếu nhập.',
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

    public function downloadTemplate(): StreamedResponse
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

    public function importExcel(Request $request): JsonResponse
    {
        $data = $request->validate([
            'file' => 'required|file|mimes:csv,txt,xlsx,xls,max:10240',
            'id_nha_cung_cap' => 'nullable|integer|exists:nha_cung_cap,id',
            'loai_nhap' => 'nullable|in:mua_hang,tra_lai_tu_khach',
            'ghi_chu' => 'nullable|string|max:1000',
        ]);

        try {
            $file = $data['file'];
            $idNhaCungCap = $data['id_nha_cung_cap'] ?? null;
            $loaiNhap = $data['loai_nhap'] ?? 'mua_hang';
            $ghiChu = $data['ghi_chu'] ?? null;
            $idNguoiDung = auth()->id() ?? 0;

            // Use maatwebsite/excel to import
            $import = new PhieuNhapImport(
                idNhaCungCap: $idNhaCungCap,
                loaiNhap: $loaiNhap,
                ghiChu: $ghiChu,
                idNguoiDung: $idNguoiDung
            );

            Excel::import($import, $file);

            $errors = $import->getErrors();
            $rowCount = $import->getRowCount();

            if (!empty($errors) && $rowCount === 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Import thất bại: ' . implode(' | ', array_slice($errors, 0, 5)),
                    'errors' => $errors,
                ], 422);
            }

            $msg = "Import thành công $rowCount dòng.";
            if (!empty($errors)) {
                $msg .= ' Một số dòng bị lỗi: ' . implode('; ', array_slice($errors, 0, 3));
            }

            return response()->json([
                'success' => true,
                'message' => $msg,
                'row_count' => $rowCount,
                'errors' => $errors,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Import thất bại: ' . $e->getMessage(),
            ], 422);
        }
    }

    public function exportDanhSach(Request $request): StreamedResponse
    {
        $filters = [
            'loai_nhap' => $request->query('loai_nhap'),
            'tu_ngay' => $request->query('tu_ngay'),
            'den_ngay' => $request->query('den_ngay'),
        ];

        $query = PhieuNhap::with([
            'phieu' => fn($p) => $p->with('nhaCungCap', 'nguoiDung'),
            'chiTietPhieu',
        ])
            ->whereHas('phieu', fn($p) => $p->where('loai_phieu_enum', 'like', 'nhap%'))
            ->orderByDesc('id');

        if (!empty($filters['loai_nhap'])) {
            $query->where('loai_nhap', $filters['loai_nhap']);
        }
        if (!empty($filters['tu_ngay'])) {
            $query->whereDate('created_at', '>=', $filters['tu_ngay']);
        }
        if (!empty($filters['den_ngay'])) {
            $query->whereDate('created_at', '<=', $filters['den_ngay']);
        }

        $phieuNhaps = $query->get();

        $fileName = 'phieu-nhap-danh-sach-' . now()->format('Ymd_His') . '.csv';

        return response()->stream(function () use ($phieuNhaps) {
            $output = fopen('php://output', 'w');
            fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF)); // BOM for Excel UTF-8

            fputcsv($output, [
                'ID', 'Ma_phieu', 'Ngay_tao', 'Loai_nhap', 'Nha_cung_cap',
                'Nguoi_tao', 'Tong_SP', 'Tong_so_luong', 'Tong_tien', 'Ghi_chu'
            ], ';');

            foreach ($phieuNhaps as $item) {
                $chiTiet = $item->chiTietPhieu ?? collect();
                $tongTien = $chiTiet->sum(fn($ct) => $ct->so_luong * $ct->gia_nhap);

                fputcsv($output, [
                    $item->id,
                    'PN' . str_pad($item->id, 5, '0', STR_PAD_LEFT),
                    $item->created_at->format('d/m/Y H:i'),
                    $item->loai_nhap === 'mua_hang' ? 'Mua hang' : 'Tra lai tu khach',
                    $item->phieu->nhaCungCap->ten_nha_cung_cap ?? 'Khong co',
                    $item->phieu->nguoiDung->ho_ten ?? 'N/A',
                    $chiTiet->count(),
                    $chiTiet->sum('so_luong'),
                    number_format($tongTien, 0, ',', '.'),
                    $item->ghi_chu ?? '',
                ], ';');
            }

            fclose($output);
        }, 200, [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => "attachment; filename=\"$fileName\"",
        ]);
    }

    public function exportChiTiet(int $id): StreamedResponse
    {
        $phieuNhap = PhieuNhap::with([
            'phieu' => fn($p) => $p->with('nhaCungCap', 'nguoiDung'),
            'chiTietPhieu.variant.product',
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
                'STT', 'San_pham', 'Ma_vach', 'So_luong', 'Gia_nhap', 'Thanh_tien', 'Han_su_dung'
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

                fputcsv($output, [
                    $stt++,
                    $tenFull,
                    $ct->variant?->ma_vach ?? '',
                    $ct->so_luong,
                    number_format($ct->gia_nhap, 0, ',', '.'),
                    number_format($thanhTien, 0, ',', '.'),
                    $ct->han_su_dung ? date('d/m/Y', strtotime($ct->han_su_dung)) : '',
                ], ';');
            }

            // Total
            fputcsv($output, [], ';');
            fputcsv($output, ['', '', '', '', 'TONG CONG:', number_format($tongTien, 0, ',', '.')], ';');

            fclose($output);
        }, 200, [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => "attachment; filename=\"$fileName\"",
        ]);
    }
}
