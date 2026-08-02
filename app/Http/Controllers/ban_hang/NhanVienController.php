<?php

namespace App\Http\Controllers\ban_hang;

use App\Http\Controllers\Controller;
use App\Http\Requests\DoiMatKhauRequest;
use App\Models\ChiaCaLamViec;
use App\Models\NguoiDung;
use App\Models\SanPham;
use App\Models\DanhMucSanPham;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\KhachHang;
use App\Http\Requests\Pos\ScanBarcodeRequest;
use App\Services\Pos\BarcodeScanService;

class NhanVienController extends Controller
{
    public function index()
    {
        return view('ban_hang.dashboard');
    }

    public function banHang()
    {
        return view('ban_hang.pos');
    }

    public function hoaDon(Request $request)
    {
        $query = DB::table('hoa_don')
            ->leftJoin('khach_hang', 'hoa_don.id_khach_hang', '=', 'khach_hang.id')
            ->leftJoin('nguoi_dung', 'hoa_don.id_nguoi_dung', '=', 'nguoi_dung.id')
            ->leftJoin('ca_lam_viec', 'hoa_don.id_ca_lam_viec', '=', 'ca_lam_viec.id')
            ->select(
                'hoa_don.*',
                'khach_hang.ten_khach_hang',
                'nguoi_dung.ho_ten as ten_nhan_vien',
                'ca_lam_viec.ten_ca'
            )
            ->orderByDesc('hoa_don.id');

        if ($request->filled('q')) {
            $query->where('hoa_don.id', preg_replace('/[^0-9]/', '', $request->q));
        }
        if ($request->filled('id_ca_lam_viec')) {
            $query->where('hoa_don.id_ca_lam_viec', $request->id_ca_lam_viec);
        }

        if ($request->filled('ngay')) {
            $query->whereDate('hoa_don.created_at', $request->ngay);
        }

        if ($request->filled('trang_thai')) {
            $query->where('hoa_don.trang_thai', $request->trang_thai);
        }

        $hoaDons = $query->paginate(10)->withQueryString();

        $caLamViecs = DB::table('ca_lam_viec')
            ->orderBy('gio_bat_dau')
            ->get();
        return view('ban_hang.hoa-don.index', compact('hoaDons', 'caLamViecs'));
    }

    public function sanPham(Request $request)
    {
        $tuKhoa = trim((string) $request->query('tu_khoa', ''));

        $sanPhams = SanPham::query()
            ->with([
                'danhMuc',
                'bienTheSanPhams' => function ($query) {
                    $query->where('trang_thai', true)
                        ->orderBy('id')
                        ->with([
                            'units' => function ($unitQuery) {
                                $unitQuery->orderByDesc('la_don_vi_mac_dinh')
                                    ->orderBy('id');
                            },
                        ]);
                },
            ])
            ->where('trang_thai', true)
            ->when($tuKhoa !== '', function ($query) use ($tuKhoa) {
                $keyword = mb_strtolower($tuKhoa, 'UTF-8');

                $query->where(function ($subQuery) use ($keyword) {
                    $subQuery->whereRaw('LOWER(ten_san_pham) LIKE ?', ["%{$keyword}%"])
                        ->orWhereRaw("LOWER(COALESCE(thuong_hieu, '')) LIKE ?", ["%{$keyword}%"]);
                });
            })
            ->orderBy('ten_san_pham')
            ->paginate(10)
            ->withQueryString();

        $sanPhams->getCollection()->transform(function (SanPham $sanPham) {
            $bienThes = $sanPham->bienTheSanPhams ?? collect();
            $bienTheDauTien = $bienThes->first();
            $tatCaDonVi = $bienThes->flatMap(fn($bienThe) => $bienThe->units);
            $donViMacDinh = $tatCaDonVi->firstWhere('la_don_vi_mac_dinh', true);
            $donViDauTien = $donViMacDinh ?? $tatCaDonVi->first();
            $tongTonKho = (int) $bienThes->sum('so_luong_ton');

            $sanPham->setAttribute(
                'hinh_anh_hien_thi',
                $donViDauTien?->hinh_anh ?: $bienTheDauTien?->hinh_anh
            );
            $sanPham->setAttribute(
                'don_vi_tinh_hien_thi',
                $donViDauTien?->ten_don_vi ?: $bienTheDauTien?->ten_bien_the
            );
            $sanPham->setAttribute(
                'gia_ban_hien_thi',
                $donViDauTien?->gia_ban_quy_doi ?? $bienTheDauTien?->gia_ban
            );
            $sanPham->setAttribute('tong_ton_kho_hien_thi', $tongTonKho);
            $sanPham->setAttribute('trang_thai_kho_hien_thi', $tongTonKho > 0 ? 'Còn hàng' : 'Hết hàng');

            return $sanPham;
        });

        return view('ban_hang.san-pham.index', [
            'sanPhams' => $sanPhams,
            'tuKhoa' => $tuKhoa,
        ]);
    }
    public function lichLamViec(Request $request): View
    {
        return $this->lichSuCaLam($request);
    }

    public function lichSuCaLam(Request $request): View
    {
        $nguoiDung = $this->resolvePreviewEmployee($request);

        $query = ChiaCaLamViec::query()
            ->with('caLamViec')
            ->where('id_nguoi_dung', $nguoiDung->id);

        if ($request->filled('tu_ngay')) {
            $query->whereDate('ngay', '>=', $request->input('tu_ngay'));
        }

        if ($request->filled('den_ngay')) {
            $query->whereDate('ngay', '<=', $request->input('den_ngay'));
        }

        $lichSuCaLam = (clone $query)
            ->orderByDesc('ngay')
            ->orderByDesc('id')
            ->paginate(12)
            ->withQueryString();

        $tongSoCa = (clone $query)->count();
        $tongSoNgayLam = (clone $query)
            ->distinct('ngay')
            ->count('ngay');
        $caGanNhat = (clone $query)
            ->orderByDesc('ngay')
            ->orderByDesc('id')
            ->first();
        $caDauTien = (clone $query)
            ->orderBy('ngay')
            ->orderBy('id')
            ->first();

        return view('nhan_vien_view.lich-lam-viec.lich-su', [
            'nguoiDung' => $nguoiDung,
            'lichSuCaLam' => $lichSuCaLam,
            'tongSoCa' => $tongSoCa,
            'tongSoNgayLam' => $tongSoNgayLam,
            'caGanNhat' => $caGanNhat,
            'caDauTien' => $caDauTien,
            'boLoc' => [
                'tu_ngay' => $request->input('tu_ngay'),
                'den_ngay' => $request->input('den_ngay'),
            ],
        ]);
    }


    private function calculateShiftMinutes(string $gioBatDau, string $gioKetThuc): int
    {
        $batDau = Carbon::createFromFormat('H:i:s', substr($gioBatDau, 0, 8));
        $ketThuc = Carbon::createFromFormat('H:i:s', substr($gioKetThuc, 0, 8));

        if ($ketThuc->lessThanOrEqualTo($batDau)) {
            $ketThuc->addDay();
        }

        return $batDau->diffInMinutes($ketThuc);
    }

    private function formatHoursFromMinutes(int $minutes): string
    {
        $hours = intdiv($minutes, 60);
        $remainingMinutes = $minutes % 60;

        if ($remainingMinutes === 0) {
            return $hours . ' giờ';
        }

        return $hours . ' giờ ' . $remainingMinutes . ' phút';
    }

    private function displayRole(?string $vaiTro): string
    {
        $normalized = Str::of((string) $vaiTro)->lower()->ascii()->value();

        return match ($normalized) {
            'truong ca' => 'Trưởng ca',
            'admin' => 'Admin',
            default => 'Nhân viên',
        };
    }

    private function isAdminRole(?string $vaiTro): bool
    {
        return Str::of((string) $vaiTro)->lower()->ascii()->value() === 'admin';
    }
 public function laySanPham(Request $request)
{
    $query = DB::table('bien_the_san_pham')
        ->join('san_pham', 'bien_the_san_pham.product_id', '=', 'san_pham.id')
        ->where('san_pham.trang_thai', 1)
        ->where('bien_the_san_pham.trang_thai', 1)
        ->whereNull('san_pham.deleted_at')
        ->whereNull('bien_the_san_pham.deleted_at')
        ->where('bien_the_san_pham.so_luong_ton', '>', 0);

        if ($request->filled('id_danh_muc') && $request->id_danh_muc !== 'all') {
            $query->where('san_pham.id_danh_muc', $request->id_danh_muc);
        }

        if ($request->filled('q')) {
            $keyword = $request->q;

            $query->where(function ($q) use ($keyword) {
                $q->where('san_pham.ten_san_pham', 'like', "%{$keyword}%")
                    ->orWhere('bien_the_san_pham.ma_hang', 'like', "%{$keyword}%")
                    ->orWhere('bien_the_san_pham.ma_vach', 'like', "%{$keyword}%")
                    ->orWhere('bien_the_san_pham.ten_bien_the', 'like', "%{$keyword}%");
            });
        }

        $products = $query->select(
            'bien_the_san_pham.id',
            'san_pham.id as id_san_pham',
            'san_pham.id_danh_muc',
            'san_pham.ten_san_pham',
            'bien_the_san_pham.ten_bien_the',
            'bien_the_san_pham.ma_hang',
            'bien_the_san_pham.ma_vach',
            'bien_the_san_pham.gia_ban',
            'bien_the_san_pham.so_luong_ton as so_luong_ton_kho',
            'bien_the_san_pham.hinh_anh'
        )
            ->orderByDesc('bien_the_san_pham.id')
            ->get();

        $products = $products->map(function ($product) {
            $product->ten_san_pham = trim(($product->ten_san_pham ?? '') . ' ' . ($product->ten_bien_the ?? ''));
            $product->ten_san_pham = preg_replace('/\s+/', ' ', $product->ten_san_pham);
            $product->ten_san_pham = trim($product->ten_san_pham);

            $product->ma_hang = $product->ma_hang ?? null;
            $product->ma_vach = $product->ma_vach ?? null;
            $product->gia_ban = (float) ($product->gia_ban ?? 0);
            $product->so_luong_ton_kho = (int) ($product->so_luong_ton_kho ?? 0);
            $product->hinh_anh = $product->hinh_anh ?? null;

            return $product;
        });

    return response()->json($products);
}
public function layDanhMuc()
{
    return response()->json(
        DanhMucSanPham::query()
            ->where('trang_thai', 1)
            ->select('id', 'ten_danh_muc')
            ->orderBy('id', 'asc')
            ->get()
    );
}
public function thanhToan(Request $request)
{
    $request->validate([
        'cart' => 'required|array|min:1',
        'cart.*.id' => 'required|integer|exists:bien_the_san_pham,id',
        'cart.*.qty' => 'required|integer|min:1',
        'tien_khach_dua' => 'required|numeric|min:0',
        'phuong_thuc_thanh_toan' => 'required|string',
        'id_khach_hang' => 'nullable|integer|exists:khach_hang,id',
        'id_khuyen_mai' => 'nullable|integer|exists:khuyen_mai,id',
        'diem_su_dung' => 'nullable|integer|min:0',
    ]);

        return DB::transaction(function () use ($request) {
            $tongTienHang = 0;
            $items = [];

        foreach ($request->cart as $item) {
            $bienThe = DB::table('bien_the_san_pham')
                ->join('san_pham', 'bien_the_san_pham.product_id', '=', 'san_pham.id')
                ->where('bien_the_san_pham.id', $item['id'])
                ->whereNull('bien_the_san_pham.deleted_at')
                ->whereNull('san_pham.deleted_at')
                ->select(
                    'bien_the_san_pham.*',
                    'san_pham.ten_san_pham'
                )
                ->lockForUpdate()
                ->first();

            if (!$bienThe) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy sản phẩm.'
                ], 422);
            }

            if ((int)$bienThe->so_luong_ton < (int)$item['qty']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sản phẩm "' . $bienThe->ten_san_pham . '" không đủ tồn kho.'
                ], 422);
            }

            $thanhTien = $bienThe->gia_ban * $item['qty'];
            $tongTienHang += $thanhTien;

            $items[] = [
                'bien_the' => $bienThe,
                'so_luong' => (int)$item['qty'],
                'gia_ban' => $bienThe->gia_ban,
                'thanh_tien' => $thanhTien,
            ];
        }

            $tienGiamGia = 0;
            $diemSuDung = (int)($request->diem_su_dung ?? 0);

            if ($request->id_khuyen_mai) {
                $khuyenMai = DB::table('khuyen_mai')
                    ->where('id', $request->id_khuyen_mai)
                    ->where('trang_thai', 1)
                    ->first();

                if ($khuyenMai) {
                    $tongSoLuong = collect($items)->sum('so_luong');

                    if (
                        $tongTienHang >= ($khuyenMai->don_hang_toi_thieu ?? 0) &&
                        $tongSoLuong >= ($khuyenMai->so_luong_sp_toi_thieu ?? 0)
                    ) {
                        $loaiGiamGia = Str::of((string) $khuyenMai->loai_giam_gia)
                            ->trim()
                            ->lower()
                            ->ascii()
                            ->replace([' ', '-'], '_')
                            ->value();

                        $giaTriGiam = (float) ($khuyenMai->gia_tri_giam ?? 0);
                        $giamToiDa = (float) ($khuyenMai->giam_toi_da ?? 0);

                        switch ($loaiGiamGia) {
                            case 'phan_tram':
                            case 'percent':
                                $tienGiamGia = $tongTienHang * $giaTriGiam / 100;

                                if ($giamToiDa > 0) {
                                    $tienGiamGia = min($tienGiamGia, $giamToiDa);
                                }
                                break;

                            case 'bogo':
                            case 'mua_1_tang_1':
                                foreach ($items as $item) {
                                    $freeQty = floor($item['so_luong'] / 2);
                                    $tienGiamGia += $freeQty * $item['gia_ban'];
                                }
                                break;

                            case 'tien_mat':
                            case 'so_tien':
                            case 'giam_tien':
                            case 'fixed':
                                $tienGiamGia = $giaTriGiam;
                                break;

                            default:
                                return response()->json([
                                    'success' => false,
                                    'message' => 'Loại giảm giá không hợp lệ: '
                                        . $khuyenMai->loai_giam_gia
                                ], 422);
                        }

                        $tienGiamGia = min(
                            max($tienGiamGia, 0),
                            $tongTienHang
                        );
                    }
                }
            }

            $khachHang = null;

            if ($request->id_khach_hang) {
                $khachHang = KhachHang::lockForUpdate()->find($request->id_khach_hang);

                if (!$khachHang) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Không tìm thấy khách hàng.'
                    ], 422);
                }

                $maxUsePoint = floor(max(0, $tongTienHang - $tienGiamGia) / 100);

                $diemSuDung = min(
                    $diemSuDung,
                    (int)$khachHang->diem_tich_luy,
                    (int)$maxUsePoint
                );

                $tienGiamGia += $diemSuDung * 100;
            }

            $khachCanTra = max(0, $tongTienHang - $tienGiamGia);
            $tienKhachDua = (float)$request->tien_khach_dua;
            $tienThua = max(0, $tienKhachDua - $khachCanTra);
            $diemThuDuoc = floor($khachCanTra / 10000);

            $phuongThucMap = [
                'cash' => 'Tiền mặt',
                'transfer' => 'Chuyển khoản',
                'tien_mat' => 'Tiền mặt',
                'chuyen_khoan' => 'Chuyển khoản',
                'payos' => 'PayOS',
            ];

            $phuongThucThanhToan = $phuongThucMap[$request->phuong_thuc_thanh_toan]
                ?? $request->phuong_thuc_thanh_toan;

            if (
                in_array($request->phuong_thuc_thanh_toan, ['cash', 'tien_mat']) &&
                $tienKhachDua < $khachCanTra
            ) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tiền khách đưa chưa đủ.'
                ], 422);
            }

            $isPayOS = $request->phuong_thuc_thanh_toan === 'payos';

            $hoaDonId = DB::table('hoa_don')->insertGetId([
                'id_nguoi_dung' => Auth::id(),
                'id_khach_hang' => $request->id_khach_hang,
                'id_ca_lam_viec' => session('id_ca_lam_viec') ?? null,
                'id_khuyen_mai' => $request->id_khuyen_mai,
                'tong_tien_hang' => $tongTienHang,
                'tien_giam_gia' => $tienGiamGia,
                'khach_can_tra' => $khachCanTra,
                'tien_khach_dua' => $isPayOS ? $khachCanTra : $tienKhachDua,
                'tien_thua' => $isPayOS ? 0 : $tienThua,
                'phuong_thuc_thanh_toan' => $phuongThucThanhToan,
                'trang_thai' => $isPayOS ? 'Chờ thanh toán' : 'Hoàn thành',
                'diem_su_dung' => $isPayOS ? 0 : $diemSuDung,
                'diem_thu_duoc' => $isPayOS ? 0 : $diemThuDuoc,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            if ($khachHang) {
                if ($isPayOS) {
                    if ($diemSuDung > 0) {
                        DB::table('khach_hang')
                            ->where('id', $khachHang->id)
                            ->update([
                                'diem_tich_luy' => $khachHang->diem_tich_luy - $diemSuDung,
                                'updated_at' => now(),
                            ]);

                        DB::table('lich_su_tich_diem')->insert([
                            'id_khach_hang' => $khachHang->id,
                            'id_hoa_don' => $hoaDonId,
                            'loai_bien_dong' => 'tru',
                            'so_diem' => $diemSuDung,
                            'ly_do' => 'Sử dụng điểm thanh toán (PayOS - đang chờ)',
                            'created_at' => now(),
                        ]);
                    }
                } else {
                    $diemMoi = $khachHang->diem_tich_luy - $diemSuDung + $diemThuDuoc;

                    DB::table('khach_hang')
                        ->where('id', $khachHang->id)
                        ->update([
                            'diem_tich_luy' => $diemMoi,
                            'tong_chi_tieu' => $khachHang->tong_chi_tieu + $khachCanTra,
                            'updated_at' => now(),
                        ]);

                    if ($diemSuDung > 0) {
                        DB::table('lich_su_tich_diem')->insert([
                            'id_khach_hang' => $khachHang->id,
                            'id_hoa_don' => $hoaDonId,
                            'loai_bien_dong' => 'tru',
                            'so_diem' => $diemSuDung,
                            'ly_do' => 'Sử dụng điểm thanh toán',
                            'created_at' => now(),
                        ]);
                    }

                    if ($diemThuDuoc > 0) {
                        DB::table('lich_su_tich_diem')->insert([
                            'id_khach_hang' => $khachHang->id,
                            'id_hoa_don' => $hoaDonId,
                            'loai_bien_dong' => 'cong',
                            'so_diem' => $diemThuDuoc,
                            'ly_do' => 'Tích điểm từ hóa đơn',
                            'created_at' => now(),
                        ]);
                    }
                }
            }

            foreach ($items as $item) {
                DB::table('chi_tiet_hoa_don')->insert([
                    'id_hoa_don' => $hoaDonId,
                    'id_san_pham' => $item['bien_the']->product_id,
                    'id_chi_tiet_phieu' => $item['bien_the']->id,
                    'so_luong' => $item['so_luong'],
                    'gia_ban' => $item['gia_ban'],
                    'thanh_tien' => $item['thanh_tien'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                if (!$isPayOS) {
                    DB::table('bien_the_san_pham')
                        ->where('id', $item['bien_the']->id)
                        ->decrement('so_luong_ton', $item['so_luong']);
                }
            }

            return response()->json([
                'success' => true,
                'message' => $isPayOS ? 'Đang tạo link thanh toán PayOS.' : 'Thanh toán thành công.',
                'hoa_don_id' => $hoaDonId,
                'redirect_to_payos' => $isPayOS,
            ]);
        });
    }
    public function chiTietHoaDon($id)
    {
        $hoaDon = DB::table('hoa_don')
            ->leftJoin('khach_hang', 'hoa_don.id_khach_hang', '=', 'khach_hang.id')
            ->leftJoin('nguoi_dung', 'hoa_don.id_nguoi_dung', '=', 'nguoi_dung.id')
            ->leftJoin('ca_lam_viec', 'hoa_don.id_ca_lam_viec', '=', 'ca_lam_viec.id')
            ->select(
                'hoa_don.*',
                'khach_hang.ten_khach_hang',
                'nguoi_dung.ho_ten as ten_nhan_vien',
                'ca_lam_viec.ten_ca',
                'ca_lam_viec.gio_bat_dau',
                'ca_lam_viec.gio_ket_thuc'
            )
            ->where('hoa_don.id', $id)
            ->first();

        abort_if(!$hoaDon, 404);

        $chiTiet = DB::table('chi_tiet_hoa_don')
            ->join('san_pham', 'chi_tiet_hoa_don.id_san_pham', '=', 'san_pham.id')
            ->leftJoin('bien_the_san_pham', 'chi_tiet_hoa_don.id_chi_tiet_phieu', '=', 'bien_the_san_pham.id')
            ->select(
                'chi_tiet_hoa_don.*',
                'san_pham.ten_san_pham',
                'bien_the_san_pham.ten_bien_the',
                'bien_the_san_pham.ten_don_vi',
                'bien_the_san_pham.ma_vach'
            )
            ->where('chi_tiet_hoa_don.id_hoa_don', $id)
            ->get();

        return view('ban_hang.hoa-don.chi-tiet', compact('hoaDon', 'chiTiet'))
            ->with('auto_print', request()->boolean('print'));
    }

    public function formDoiTra($id)
    {
        $hoaDon = DB::table('hoa_don')
            ->leftJoin('khach_hang', 'hoa_don.id_khach_hang', '=', 'khach_hang.id')
            ->select('hoa_don.*', 'khach_hang.ten_khach_hang')
            ->where('hoa_don.id', $id)
            ->first();

        abort_if(!$hoaDon, 404);

        if (in_array($hoaDon->trang_thai, ['Đã hủy', 'Đã trả toàn bộ'])) {
            return back()->with('error', 'Hóa đơn này không thể đổi/trả hàng.');
        }

        $chiTiet = DB::table('chi_tiet_hoa_don')
            ->join('san_pham', 'chi_tiet_hoa_don.id_san_pham', '=', 'san_pham.id')
            ->leftJoin('bien_the_san_pham', 'chi_tiet_hoa_don.id_chi_tiet_phieu', '=', 'bien_the_san_pham.id')
            ->select(
                'chi_tiet_hoa_don.*',
                'san_pham.ten_san_pham',
                'bien_the_san_pham.ten_bien_the',
                'bien_the_san_pham.ten_don_vi',
                'bien_the_san_pham.ma_vach',
                DB::raw('COALESCE(bien_the_san_pham.gia_ban, chi_tiet_hoa_don.gia_ban) as gia_ban')
            )
            ->where('chi_tiet_hoa_don.id_hoa_don', $id)
            ->get();

        $daTra = DB::table('phieu')
            ->join('chi_tiet_phieu', 'phieu.id', '=', 'chi_tiet_phieu.id_phieu')
            ->where('phieu.id_hoa_don', $id)
            ->where('phieu.loai_phieu', 'Đổi / Trả hàng')
            ->where('chi_tiet_phieu.ghi_chu', 'like', 'Hàng khách trả%')
            ->select(
                DB::raw('COALESCE(chi_tiet_phieu.variant_id, chi_tiet_phieu.id_san_pham) as key_id'),
                DB::raw('SUM(chi_tiet_phieu.so_luong) as tong_da_tra')
            )
            ->groupBy('key_id')
            ->pluck('tong_da_tra', 'key_id');

        return view('ban_hang.hoa-don.doi-tra', compact('hoaDon', 'chiTiet', 'daTra'));
    }

    public function xuLyDoiTraNhanVien(Request $request, $id)
    {
        $adminController = app()->make(\App\Http\Controllers\admin\BanHang\HoaDonController::class);
        $response = app()->call([$adminController, 'xuLyDoiTra'], ['request' => $request, 'id' => $id]);

        // If admin logic returned a redirect, map to staff routes and preserve flash messages
        if ($response instanceof \Illuminate\Http\RedirectResponse) {
            $redirect = redirect()->route('nhan-vien.hoa-don.chi-tiet', $id);
            if (session()->has('success')) {
                return $redirect->with('success', session('success'));
            }
            if (session()->has('error')) {
                return $redirect->with('error', session('error'));
            }
            if (session()->has('message')) {
                return $redirect->with('message', session('message'));
            }
            return $redirect;
        }

        return $response;
    }

    public function inHoaDon($id)
    {
        return redirect()->route('nhan-vien.hoa-don.chi-tiet', ['id' => $id, 'print' => 1]);
    }
    public function huyHoaDon($id)
    {
        return DB::transaction(function () use ($id) {
            $hoaDon = DB::table('hoa_don')
                ->where('id', $id)
                ->lockForUpdate()
                ->first();

            if (!$hoaDon) {
                return back()->with('error', 'Không tìm thấy hóa đơn.');
            }

            if ($hoaDon->trang_thai === 'Đã hủy') {
                return back()->with('error', 'Hóa đơn này đã bị hủy trước đó.');
            }

            $chiTiet = DB::table('chi_tiet_hoa_don')
                ->where('id_hoa_don', $id)
                ->get();

            foreach ($chiTiet as $item) {
                if ($item->id_chi_tiet_phieu) {
                    DB::table('bien_the_san_pham')
                        ->where('id', $item->id_chi_tiet_phieu)
                        ->increment('so_luong_ton', $item->so_luong);
                } else {
                    DB::table('san_pham')
                        ->where('id', $item->id_san_pham)
                        ->increment('so_luong_ton_kho', $item->so_luong);
                }
            }
            if ($hoaDon->id_khach_hang && $hoaDon->diem_thu_duoc > 0) {

                DB::table('khach_hang')
                    ->where('id', $hoaDon->id_khach_hang)
                    ->decrement('diem_tich_luy', $hoaDon->diem_thu_duoc);

                DB::table('lich_su_tich_diem')->insert([
                    'id_khach_hang' => $hoaDon->id_khach_hang,
                    'id_hoa_don' => $hoaDon->id,
                    'loai_bien_dong' => 'tru',
                    'so_diem' => $hoaDon->diem_thu_duoc,
                    'ly_do' => 'Hủy hóa đơn',
                    'ngay_tao' => now(),
                ]);
            }
            DB::table('hoa_don')
                ->where('id', $id)
                ->update([
                    'trang_thai' => 'Đã hủy',
                    'updated_at' => now(),
                ]);

            return back()->with('success', 'Đã hủy hóa đơn và hoàn lại tồn kho.');
        });
    }
    public function layKhachHang(Request $request)
    {
        $query = KhachHang::query()
            ->where('trang_thai', 1);

        if ($request->filled('q')) {
            $query->where(function ($q) use ($request) {
                $q->where('ten_khach_hang', 'like', '%' . $request->q . '%')
                    ->orWhere('so_dien_thoai', 'like', '%' . $request->q . '%')
                    ->orWhere('email', 'like', '%' . $request->q . '%');
            });
        }

        return response()->json(
            $query->select(
                'id',
                'ten_khach_hang',
                'so_dien_thoai',
                'email',
                'diem_tich_luy',
                'tong_chi_tieu'
            )
                ->orderBy('ten_khach_hang')
                ->limit(10)
                ->get()
        );
    }
    public function layKhuyenMai()
    {
        return response()->json(
            DB::table('khuyen_mai')
                ->where('trang_thai', 1)
                ->where('ngay_bat_dau', '<=', now())
                ->where('ngay_ket_thuc', '>=', now())
                ->select(
                    'id',
                    'ten_chuong_trinh',
                    'loai_giam_gia',
                    'gia_tri_giam',
                    'giam_toi_da',
                    'so_luong_sp_toi_thieu',
                    'don_hang_toi_thieu'
                )
                ->orderByDesc('id')
                ->get()
        );
    }
}
