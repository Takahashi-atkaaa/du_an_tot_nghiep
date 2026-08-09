<?php

namespace App\Http\Controllers\Admin\Api;

use App\Http\Controllers\Controller;
use App\Exports\PhieuXuatDanhSachExport;
use App\Models\Phieu;
use App\Models\PhieuXuat;
use App\Models\ChiTietLoHang;
use App\Models\ChiTietPhieu;
use App\Models\BienTheSanPham;
use App\Models\DonViQuyDoi;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PhieuXuatApiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $q = $request->query('q');
        $loai = $request->query('loai_xuat');
        $tuNgay = $request->query('tu_ngay');
        $denNgay = $request->query('den_ngay');

        $query = PhieuXuat::with([
            'phieu',
            'phieuNhapLienQuan',
            'chiTietPhieu',
        ])
            ->whereHas('phieu', fn($p) => $p->where('loai_phieu_enum', 'like', 'xuat%'))
            ->orderByDesc('id');

        if (!empty($loai)) {
            $query->where('loai_xuat', $loai);
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
        $phieuXuat = PhieuXuat::with([
            'phieu',
            'phieuNhapLienQuan',
            'chiTietPhieu' => fn($ct) => $ct->with('variant.product', 'chiTietLoHang'),
        ])->find($id);

        if (!$phieuXuat) {
            return response()->json(['success' => false, 'message' => 'Phiếu xuất không tồn tại.'], 404);
        }

        $data = $phieuXuat->toArray();
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
            'loai_xuat' => 'required|in:tra_hang_nha_cung_cap,tieu_huy',
            'id_phieu_nhap_lien_quan' => 'nullable|integer|exists:phieu,id',
            'id_nha_cung_cap' => 'nullable|integer|exists:nha_cung_cap,id',
            'ly_do' => 'nullable|string|max:500',
            'ghi_chu' => 'nullable|string',
            'chi_tiet' => 'required|array|min:1',
            'chi_tiet.*.variant_id' => 'required|integer|exists:bien_the_san_pham,id',
            'chi_tiet.*.id_chi_tiet_lo_hang' => 'required|integer|exists:chi_tiet_lo_hang,id',
            'chi_tiet.*.so_luong' => 'required|integer|min:1',
        ], [
            'chi_tiet.required' => 'Phải có ít nhất một sản phẩm.',
            'chi_tiet.*.so_luong.min' => 'Số lượng xuất phải lớn hơn 0.',
            'chi_tiet.*.id_chi_tiet_lo_hang.required' => 'Phải chọn lô hàng cho từng sản phẩm.',
        ]);

        $loaiPhieuEnum = $data['loai_xuat'] === 'tra_hang_nha_cung_cap'
            ? 'xuat_tra_hang_nha_cung_cap'
            : 'xuat_tieu_huy';

        $loaiPhieuLabel = $data['loai_xuat'] === 'tra_hang_nha_cung_cap'
            ? 'Trả hàng NCC'
            : 'Tiêu hủy';

        try {
            $result = DB::transaction(function () use ($data, $loaiPhieuEnum, $loaiPhieuLabel) {
                $idNguoiDung = auth()->id();

                $phieu = Phieu::create([
                    'loai_phieu' => $loaiPhieuLabel,
                    'loai_phieu_enum' => $loaiPhieuEnum,
                    'id_nguoi_dung' => $idNguoiDung,
                    'id_nha_cung_cap' => $data['id_nha_cung_cap'] ?? null,
                    'ly_do' => $data['ly_do'] ?? null,
                    'ghi_chu' => $data['ghi_chu'] ?? null,
                ]);

                $phieuXuat = PhieuXuat::create([
                    'id_phieu' => $phieu->id,
                    'loai_xuat' => $data['loai_xuat'],
                    'id_phieu_nhap_lien_quan' => $data['id_phieu_nhap_lien_quan'] ?? null,
                    'ly_do' => $data['ly_do'] ?? null,
                    'ghi_chu' => $data['ghi_chu'] ?? null,
                ]);

                $variantIds = collect($data['chi_tiet'])->pluck('variant_id')->unique()->all();
                $variantMap = BienTheSanPham::whereIn('id', $variantIds)
                    ->pluck('product_id', 'id')
                    ->toArray();

                foreach ($data['chi_tiet'] as $ct) {
                    $soLuongCanXuat = (int) $ct['so_luong'];

                    $ctLo = ChiTietLoHang::where('id', $ct['id_chi_tiet_lo_hang'])
                        ->where('variant_id', $ct['variant_id'])
                        ->lockForUpdate()
                        ->first();

                    if (!$ctLo) {
                        throw new \Exception("Lô hàng đã chọn không tồn tại hoặc không thuộc variant ID {$ct['variant_id']}.");
                    }

                    if ($ctLo->so_luong_ton < $soLuongCanXuat) {
                        throw new \Exception("Variant ID {$ct['variant_id']}: lô đã chọn chỉ tồ {$ctLo->so_luong_ton}, không đủ để xuất {$soLuongCanXuat}.");
                    }

                    $soLuongTonTruocKhiXuat = (int) $ctLo->so_luong_ton;
                    $soLuongTonSauKhiXuat = $soLuongTonTruocKhiXuat - $soLuongCanXuat;

                    $ctLo->decrement('so_luong_ton', $soLuongCanXuat);

                    ChiTietPhieu::create([
                        'id_phieu' => $phieu->id,
                        'id_san_pham' => $variantMap[$ct['variant_id']] ?? null,
                        'variant_id' => $ct['variant_id'],
                        'id_lo_hang' => $ctLo->id_lo_hang,
                        'id_chi_tiet_lo_hang' => $ctLo->id,
                        'so_luong' => $soLuongCanXuat,
                        'gia_nhap' => $ctLo->gia_nhap ?? 0,
                        'han_su_dung' => $ctLo->han_su_dung,
                        'so_luong_con_lai' => $soLuongTonSauKhiXuat,
                    ]);
                }

                return $phieuXuat->load('phieu', 'chiTietPhieu.variant', 'chiTietPhieu.chiTietLoHang');
            });
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'Tạo phiếu xuất thành công.',
            'data' => $result,
        ], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $phieuXuat = PhieuXuat::with('phieu')->find($id);
        if (!$phieuXuat) {
            return response()->json(['success' => false, 'message' => 'Phiếu xuất không tồn tại.'], 404);
        }

        $data = $request->validate([
            'loai_xuat' => 'required|in:tra_hang_nha_cung_cap,tieu_huy',
            'id_nha_cung_cap' => 'nullable|integer|exists:nha_cung_cap,id',
            'ly_do' => 'nullable|string|max:500',
            'ghi_chu' => 'nullable|string',
        ]);

        $loaiLabel = $data['loai_xuat'] === 'tra_hang_nha_cung_cap' ? 'Trả hàng NCC' : 'Tiêu hủy';
        $loaiEnum = $data['loai_xuat'] === 'tra_hang_nha_cung_cap'
            ? 'xuat_tra_hang_nha_cung_cap'
            : 'xuat_tieu_huy';

        $phieuXuat->phieu->update([
            'loai_phieu' => $loaiLabel,
            'loai_phieu_enum' => $loaiEnum,
            'id_nha_cung_cap' => $data['id_nha_cung_cap'] ?? null,
            'ly_do' => $data['ly_do'] ?? null,
            'ghi_chu' => $data['ghi_chu'] ?? null,
        ]);
        $phieuXuat->update([
            'loai_xuat' => $data['loai_xuat'],
            'ly_do' => $data['ly_do'] ?? null,
            'ghi_chu' => $data['ghi_chu'] ?? null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật phiếu xuất thành công.',
            'data' => $phieuXuat->load('phieu'),
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $phieuXuat = PhieuXuat::with('phieu')->find($id);
        if (!$phieuXuat) {
            return response()->json(['success' => false, 'message' => 'Phiếu xuất không tồn tại.'], 404);
        }

        DB::transaction(function () use ($phieuXuat) {
            ChiTietPhieu::where('id_phieu', $phieuXuat->id_phieu)->delete();
            $phieuXuat->phieu->delete();
            $phieuXuat->delete();
        });

        return response()->json([
            'success' => true,
            'message' => 'Đã xóa phiếu xuất.',
        ]);
    }

    public function downloadTemplate(): StreamedResponse
    {
        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="phieu-xuat-template.csv"',
        ];

        $output = fopen('php://output', 'w');
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF)); // BOM for Excel UTF-8

        // Header
        fputcsv($output, ['Ma_vach', 'So_luong'], ';');

        // Sample row
        fputcsv($output, ['SP001', '5'], ';');

        // Hướng dẫn
        fputcsv($output, ['# Huong dan:'], ';');
        fputcsv($output, ['# Ma_vach: Ma vach san pham (bat buoc)'], ';');
        fputcsv($output, ['# So_luong: So luong xuat, so nguyen lon hon 0 (bat buoc)'], ';');
        fputcsv($output, ['# He thong tu dong tru kho theo nguyen tac FEFO (uu tien lo gan HSD nhat)'], ';');

        fclose($output);

        return response()->stream(function () {}, 200, $headers);
    }

    public function importExcel(Request $request): JsonResponse
    {
        $data = $request->validate([
            'file' => 'required|file|mimes:csv,txt,xlsx,xls|max:10240',
            'loai_xuat' => 'nullable|in:tra_hang_nha_cung_cap,tieu_huy',
            'id_nha_cung_cap' => 'nullable|integer|exists:nha_cung_cap,id',
            'ly_do' => 'nullable|string|max:500',
            'ghi_chu' => 'nullable|string|max:1000',
        ]);

        try {
            $file = $data['file'];
            $path = $file->getRealPath();

            $rows = [];
            $handle = fopen($path, 'r');
            if ($handle === false) {
                return response()->json(['success' => false, 'message' => 'Không thể đọc file.'], 422);
            }

            // Read first line to detect delimiter
            $firstLine = fgets($handle);
            rewind($handle);

            // Remove BOM if exists
            $firstLine = preg_replace('/^\xEF\xBB\xBF/', '', $firstLine);

            // Detect delimiter: comma or semicolon
            $commaCount = substr_count($firstLine, ',');
            $semicolonCount = substr_count($firstLine, ';');
            $delimiter = ($commaCount >= $semicolonCount) ? ',' : ';';

            $errors = [];
            $lineNumber = 0;
            $isFirstRow = true;

            while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
                $lineNumber++;

                // Remove BOM from first column if exists
                if (!empty($row[0])) {
                    $row[0] = preg_replace('/^\xEF\xBB\xBF/', '', $row[0]);
                }

                $line = trim(implode('', $row));

                // Skip empty lines
                if (empty($line)) {
                    continue;
                }

                // Skip header row
                if ($isFirstRow) {
                    $isFirstRow = false;
                    $firstCell = strtolower(trim($row[0] ?? ''));
                    if (in_array($firstCell, ['ma_vach', 'sku', 'masp', 'product_code', 'bar_code', 'barcode'])) {
                        continue;
                    }
                }

                // Skip comment lines
                if (str_starts_with(trim($row[0] ?? ''), '#')) {
                    continue;
                }

                if (count($row) < 2) {
                    $errors[] = "Dòng $lineNumber: Không đủ cột (cần: Ma_vach, So_luong)";
                    continue;
                }

                $maVach = trim($row[0] ?? '');
                $soLuong = trim($row[1] ?? '');

                if (empty($maVach)) {
                    $errors[] = "Dòng $lineNumber: Mã vạch trống";
                    continue;
                }

                if (!is_numeric($soLuong) || (int)$soLuong <= 0) {
                    $errors[] = "Dòng $lineNumber: Số lượng phải là số nguyên > 0 (hiện tại: '$soLuong')";
                    continue;
                }

                $variant = BienTheSanPham::where('ma_vach', $maVach)->first();
                if (!$variant) {
                    $errors[] = "Dòng $lineNumber: Không tìm thấy sản phẩm với mã vạch '$maVach'";
                    continue;
                }

                $rows[] = [
                    'variant_id' => $variant->id,
                    'so_luong' => (int)$soLuong,
                ];
            }
            fclose($handle);

            if (!empty($errors) && empty($rows)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Import thất bại: ' . implode(' | ', array_slice($errors, 0, 5)),
                    'errors' => $errors,
                ], 422);
            }

            if (empty($rows)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không có dữ liệu hợp lệ để import. ' . (!empty($errors) ? implode('; ', array_slice($errors, 0, 3)) : ''),
                    'errors' => $errors,
                ], 422);
            }

            $idNguoiDung = auth()->id() ?? 0;
            $idNhaCungCap = $data['id_nha_cung_cap'] ?? null;
            $loaiXuat = $data['loai_xuat'] ?? 'tieu_huy';
            $lyDo = $data['ly_do'] ?? null;
            $ghiChu = $data['ghi_chu'] ?? null;

            $loaiPhieuEnum = $loaiXuat === 'tra_hang_nha_cung_cap'
                ? 'xuat_tra_hang_nha_cung_cap'
                : 'xuat_tieu_huy';
            $loaiPhieuLabel = $loaiXuat === 'tra_hang_nha_cung_cap'
                ? 'Trả hàng NCC'
                : 'Tiêu hủy';

            DB::transaction(function () use ($idNguoiDung, $idNhaCungCap, $loaiPhieuEnum, $loaiPhieuLabel, $loaiXuat, $lyDo, $ghiChu, $rows, &$successCount) {
                $phieu = Phieu::create([
                    'loai_phieu' => $loaiPhieuLabel,
                    'loai_phieu_enum' => $loaiPhieuEnum,
                    'id_nguoi_dung' => $idNguoiDung,
                    'id_nha_cung_cap' => $idNhaCungCap,
                    'ly_do' => $lyDo,
                    'ghi_chu' => $ghiChu,
                ]);

                $phieuXuat = PhieuXuat::create([
                    'id_phieu' => $phieu->id,
                    'loai_xuat' => $loaiXuat,
                    'ly_do' => $lyDo,
                    'ghi_chu' => $ghiChu,
                ]);

                $variantMap = BienTheSanPham::whereIn('id', collect($rows)->pluck('variant_id'))
                    ->pluck('product_id', 'id')
                    ->toArray();

                foreach ($rows as $ct) {
                    $soLuongCanXuat = (int) $ct['so_luong'];

                    // FEFO: lấy lô có HSD gần nhất trước
                    $chiTietLohang = ChiTietLoHang::where('variant_id', $ct['variant_id'])
                        ->where('so_luong_ton', '>', 0)
                        ->orderBy('han_su_dung', 'asc')
                        ->lockForUpdate()
                        ->first();

                    if (!$chiTietLohang) {
                        throw new \Exception("Sản phẩm mã vạch '{$ct['variant_id']}' không có đủ tồn kho để xuất.");
                    }

                    if ($chiTietLohang->so_luong_ton < $soLuongCanXuat) {
                        throw new \Exception("Sản phẩm mã vạch '{$ct['variant_id']}': lô " . ($chiTietLohang->id_lo_hang) . " chỉ tồn {$chiTietLohang->so_luong_ton}, không đủ để xuất {$soLuongCanXuat}.");
                    }

                    $soLuongTonSauKhiXuat = $chiTietLohang->so_luong_ton - $soLuongCanXuat;
                    $chiTietLohang->decrement('so_luong_ton', $soLuongCanXuat);

                    ChiTietPhieu::create([
                        'id_phieu' => $phieu->id,
                        'id_san_pham' => $variantMap[$ct['variant_id']] ?? null,
                        'variant_id' => $ct['variant_id'],
                        'id_lo_hang' => $chiTietLohang->id_lo_hang,
                        'id_chi_tiet_lo_hang' => $chiTietLohang->id,
                        'so_luong' => $soLuongCanXuat,
                        'gia_nhap' => $chiTietLohang->gia_nhap ?? 0,
                        'han_su_dung' => $chiTietLohang->han_su_dung,
                        'so_luong_con_lai' => $soLuongTonSauKhiXuat,
                    ]);

                    // ChiTietLoHangObserver đã tự đồng bộ tổng tồn
                    // bien_the_san_pham.so_luong_ton từ dòng decrement() phía trên.

                    $successCount++;
                }
            });

            $msg = "Import thành công $successCount dòng.";
            if (!empty($errors)) {
                $msg .= ' Một số dòng bị lỗi: ' . implode('; ', array_slice($errors, 0, 3));
                if (count($errors) > 3) $msg .= '...';
            }

            return response()->json([
                'success' => true,
                'message' => $msg,
                'row_count' => $successCount,
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

        if (!empty($filters['loai_xuat'])
            && !in_array($filters['loai_xuat'], ['tieu_huy', 'tra_hang_nha_cung_cap'], true)
        ) {
            return response()->json(['success' => false, 'message' => 'loai_xuat khong hop le.'], 422);
        }

        $fileName = 'phieu-xuat-danh-sach-' . now()->format('Ymd_His') . '.xlsx';

        return Excel::download(new PhieuXuatDanhSachExport($filters), $fileName);
    }

    private function parseDateRange(Request $request): array
    {
        $filters = [
            'loai_xuat' => $request->query('loai_xuat'),
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
            // bo qua, validator phia sau xu ly
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
        $phieuXuat = PhieuXuat::with([
            'phieu',
            'chiTietPhieu.variant.product',
        ])->find($id);

        if (!$phieuXuat) {
            abort(404, 'Phiếu xuất không tồn tại.');
        }

        $fileName = 'phieu-xuat-chi-tiet-' . $id . '-' . now()->format('Ymd_His') . '.csv';

        return response()->stream(function () use ($phieuXuat) {
            $output = fopen('php://output', 'w');
            fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF)); // BOM for Excel UTF-8

            fputcsv($output, ['PHIEU XUAT HANG'], ';');
            fputcsv($output, ['Ma phieu:', 'PX' . str_pad($phieuXuat->id, 5, '0', STR_PAD_LEFT)], ';');
            fputcsv($output, ['Ngay:', $phieuXuat->created_at->format('d/m/Y H:i')], ';');
            fputcsv($output, ['Loai:', $phieuXuat->loai_xuat === 'tra_hang_nha_cung_cap' ? 'Tra hang NCC' : 'Tieu huy'], ';');
            fputcsv($output, ['Ly do:', $phieuXuat->ly_do ?? ''], ';');
            fputcsv($output, ['Nha cung cap:', $phieuXuat->phieu->nha_cung_cap ?? 'N/A'], ';');
            fputcsv($output, ['Nguoi tao:', $phieuXuat->phieu->nguoiDung->ho_ten ?? 'N/A'], ';');
            fputcsv($output, ['Ghi chu:', $phieuXuat->ghi_chu ?? ''], ';');
            fputcsv($output, [], ';');

            fputcsv($output, ['STT', 'San_pham', 'Ma_vach', 'So_luong', 'Gia_nhap', 'Han_su_dung'], ';');

            $stt = 1;
            $tongSl = 0;
            foreach ($phieuXuat->chiTietPhieu as $ct) {
                $tongSl += $ct->so_luong;
                $tenSp = $ct->variant?->product?->ten_san_pham ?? '';
                $tenBt = $ct->variant?->ten_bien_the ?? '';
                $tenFull = $tenSp . ($tenBt ? ' - ' . $tenBt : '');

                fputcsv($output, [
                    $stt++,
                    $tenFull,
                    $ct->variant?->ma_vach ?? '',
                    $ct->so_luong,
                    number_format($ct->gia_nhap ?? 0, 0, ',', '.'),
                    $ct->han_su_dung ? date('d/m/Y', strtotime($ct->han_su_dung)) : '',
                ], ';');
            }

            fputcsv($output, [], ';');
            fputcsv($output, ['', '', '', 'TONG:', $tongSl, ''], ';');

            fclose($output);
        }, 200, [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => "attachment; filename=\"$fileName\"",
        ]);
    }
}
