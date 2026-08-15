<?php

namespace App\Http\Controllers\ban_hang;

use App\Http\Controllers\Controller;
use App\Http\Requests\BanHang\XuLyDoiTraRequest;
use App\Http\Requests\DoiMatKhauRequest;
use App\Models\ChiaCaLamViec;
use App\Models\NguoiDung;
use App\Models\SanPham;
use App\Models\CaLamViec;
use App\Models\ChiTietLoHang;
use App\Models\DanhMucSanPham;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\KhachHang;
use App\Services\DoiTraService;


class NhanVienController extends Controller
{
    private function tenHienThiBienTheSelect(): \Illuminate\Database\Query\Expression
    {
        return DB::raw("
            TRIM(
                CONCAT(
                    COALESCE(san_pham.ten_san_pham, ''),
                    CASE
                        WHEN COALESCE(
                            CASE
                                WHEN bien_the_san_pham.la_don_vi = 1 THEN NULLIF(bien_the_san_pham.ten_don_vi, '')
                                ELSE NULLIF(bien_the_san_pham.ten_bien_the, '')
                            END,
                            NULLIF(bien_the_san_pham.ten_don_vi, '')
                        ) IS NOT NULL
                            THEN CONCAT(
                                ' - ',
                                COALESCE(
                                    CASE
                                        WHEN bien_the_san_pham.la_don_vi = 1 THEN NULLIF(bien_the_san_pham.ten_don_vi, '')
                                        ELSE NULLIF(bien_the_san_pham.ten_bien_the, '')
                                    END,
                                    NULLIF(bien_the_san_pham.ten_don_vi, '')
                                )
                            )
                        ELSE ''
                    END
                )
            ) as ten_hien_thi_san_pham
        ");
    }

    private function timCaLamViecHienTai(): ?CaLamViec
{
    $bayGio = Carbon::now();

    $danhSachCa = CaLamViec::query()
        ->whereNull('deleted_at')
        ->get();

    foreach ($danhSachCa as $ca) {
        $ngayHomNay = $bayGio->toDateString();

        $gioBatDauHomNay = Carbon::parse(
            $ngayHomNay . ' ' . $ca->gio_bat_dau
        );

        $gioKetThucHomNay = Carbon::parse(
            $ngayHomNay . ' ' . $ca->gio_ket_thuc
        );

        /*
         * Ca trong cùng ngày:
         * SA1: 07:00 - 12:00
         * CH1: 12:00 - 19:00
         * TO2: 01:00 - 07:00
         */
        if ($gioKetThucHomNay->greaterThan($gioBatDauHomNay)) {
            if (
                $bayGio->greaterThanOrEqualTo($gioBatDauHomNay)
                && $bayGio->lessThan($gioKetThucHomNay)
            ) {
                return $ca;
            }

            continue;
        }

        /*
         * Ca qua đêm:
         * TO1: 19:00 hôm nay - 01:00 ngày mai
         */

        $batDauHomNay = $gioBatDauHomNay->copy();

        $ketThucNgayMai = $gioKetThucHomNay
            ->copy()
            ->addDay();

        if (
            $bayGio->greaterThanOrEqualTo($batDauHomNay)
            && $bayGio->lessThan($ketThucNgayMai)
        ) {
            return $ca;
        }

        /*
         * Trường hợp sau 00:00:
         * 19:00 hôm qua - 01:00 hôm nay
         */
        $batDauHomQua = $gioBatDauHomNay
            ->copy()
            ->subDay();

        $ketThucHomNay = $gioKetThucHomNay->copy();

        if (
            $bayGio->greaterThanOrEqualTo($batDauHomQua)
            && $bayGio->lessThan($ketThucHomNay)
        ) {
            return $ca;
        }
    }

    return null;
}
    public function index()
    {
        return view('ban_hang.dashboard');
    }

   public function banHang()
{
    $nguoiDung = Auth::user();

    if (!$nguoiDung) {
        return redirect()
            ->route('admin.login')
            ->with('error', 'Vui lòng đăng nhập.');
    }

    /*
     * Không kiểm tra chấm công nữa.
     * Chỉ xác định ca làm việc theo thời gian hiện tại.
     */
    $caHienTai = $this->timCaLamViecHienTai();

    $danhSachDanhMuc = DanhMucSanPham::query()
        ->where('trang_thai', 1)
        ->orderBy('id', 'asc')
        ->get();

    return view('ban_hang.pos_moi', compact(
        'nguoiDung',
        'caHienTai',
        'danhSachDanhMuc'
    ));
}

public function banHangMoi()
{
    $nguoiDung = Auth::user();

    if (!$nguoiDung) {
        return redirect()
            ->route('admin.login')
            ->with('error', 'Vui lòng đăng nhập.');
    }

    $caHienTai = $this->timCaLamViecHienTai();

    $danhSachDanhMuc = DanhMucSanPham::query()
        ->where('trang_thai', 1)
        ->orderBy('id', 'asc')
        ->get();

    return view('ban_hang.pos_moi', compact(
        'nguoiDung',
        'caHienTai',
        'danhSachDanhMuc'
    ));
}

public function donChoThanhToan(Request $request): \Illuminate\Http\JsonResponse
{
    // Lấy tất cả hoá đơn đang chờ thanh toán, kèm giao dịch PayOS còn `cho_xac_nhan`
    $hoaDons = DB::table('hoa_don')
        ->leftJoin('khach_hang', 'hoa_don.id_khach_hang', '=', 'khach_hang.id')
        ->where('hoa_don.trang_thai', 'Chờ thanh toán')
        ->select(
            'hoa_don.id',
            'hoa_don.khach_can_tra',
            'hoa_don.created_at',
            'khach_hang.ten_khach_hang',
            'khach_hang.so_dien_thoai'
        )
        ->orderByDesc('hoa_don.id')
        ->limit(50)
        ->get();

    $ids = $hoaDons->pluck('id')->all();

    // Map id_hoa_don => giao dịch PayOS mới nhất còn `cho_xac_nhan`
    $gdMap = [];
    if (!empty($ids)) {
        $gds = DB::table('giao_dich')
            ->whereIn('id_hoa_don', $ids)
            ->where('phuong_thuc', 'payos')
            ->where('trang_thai', 'cho_xac_nhan')
            ->orderBy('id', 'desc')
            ->get();

        foreach ($gds as $gd) {
            $dl = is_string($gd->du_lieu_phan_hoi)
                ? json_decode($gd->du_lieu_phan_hoi, true)
                : (array) $gd->du_lieu_phan_hoi;
            if (!isset($gdMap[$gd->id_hoa_don])) {
                $gdMap[$gd->id_hoa_don] = [
                    'giao_dich_id' => $gd->id,
                    'ma_tham_chieu' => $gd->ma_tham_chieu,
                    'checkout_url' => $dl['checkout_url'] ?? null,
                    'qr_code' => $dl['qr_code'] ?? null,
                    'so_tien' => $gd->so_tien,
                ];
            }
        }
    }

    $data = $hoaDons->map(function ($hd) use ($gdMap) {
        $gd = $gdMap[$hd->id] ?? null;
        return [
            'hoa_don_id' => (int) $hd->id,
            'ma_hoa_don' => '#' . $hd->id,
            'khach_can_tra' => (float) $hd->khach_can_tra,
            'created_at' => $hd->created_at,
            'ten_khach_hang' => $hd->ten_khach_hang,
            'so_dien_thoai' => $hd->so_dien_thoai,
            'has_payos' => (bool) $gd,
            'checkout_url' => $gd['checkout_url'] ?? null,
            'qr_code' => $gd['qr_code'] ?? null,
            'giao_dich_id' => $gd['giao_dich_id'] ?? null,
            'ma_tham_chieu' => $gd['ma_tham_chieu'] ?? null,
        ];
    });

    return response()->json([
        'success' => true,
        'data' => $data,
    ]);
}

public function hoaDon(Request $request)
    {
        $doiTraSummarySub = DB::table('doi_tra')
            ->selectRaw('id_hoa_don, COUNT(*) as so_lan_doi_tra')
            ->whereNull('deleted_at')
            ->groupBy('id_hoa_don');

        $query = DB::table('hoa_don')
            ->leftJoin('khach_hang', 'hoa_don.id_khach_hang', '=', 'khach_hang.id')
            ->leftJoin('nguoi_dung', 'hoa_don.id_nguoi_dung', '=', 'nguoi_dung.id')
            ->leftJoin('ca_lam_viec', 'hoa_don.id_ca_lam_viec', '=', 'ca_lam_viec.id')
            ->leftJoinSub($doiTraSummarySub, 'doi_tra_tong_hop', function ($join) {
                $join->on('hoa_don.id', '=', 'doi_tra_tong_hop.id_hoa_don');
            })
            ->select(
    'hoa_don.*',
    'khach_hang.ten_khach_hang',
    'nguoi_dung.ho_ten as ten_nhan_vien',
    'ca_lam_viec.ten_ca',
    'ca_lam_viec.gio_bat_dau',
    'ca_lam_viec.gio_ket_thuc',
    DB::raw('COALESCE(doi_tra_tong_hop.so_lan_doi_tra, 0) as so_lan_doi_tra')
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
            // Người bán: id_nguoi_ban phải tồn tại trong bảng nguoi_dung (đang hoạt động, không xoá mềm).
            // Nếu không gửi, mặc định dùng người đang đăng nhập (Auth::id()).
            'id_nguoi_ban' => 'nullable|integer|exists:nguoi_dung,id',
            'id_khuyen_mai' => 'nullable|integer|exists:khuyen_mai,id',
            'diem_su_dung' => 'nullable|integer|min:0',
        ]);

        // Xác định id người bán:
        //  - Ưu tiên id_nguoi_ban từ request (do dropdown POS chọn)
        //  - Nếu không có, fallback về người đang đăng nhập
        $idNguoiBan = $request->input('id_nguoi_ban') ?: Auth::id();

        // Đảm bảo người bán tồn tại, đang hoạt động và không bị xoá mềm
        // (rule 'exists' của Laravel chỉ kiểm tra id tồn tại, không lọc theo trang_thai/deleted_at)
        $nguoiBanHopLe = DB::table('nguoi_dung')
            ->whereNull('deleted_at')
            ->where('trang_thai', 1)
            ->where('id', $idNguoiBan)
            ->exists();

        if (!$nguoiBanHopLe) {
            return response()->json([
                'success' => false,
                'message' => 'Người bán không hợp lệ hoặc đã ngừng hoạt động.',
            ], 422);
        }

        return DB::transaction(function () use ($request, $idNguoiBan) {

    $caHienTai = $this->timCaLamViecHienTai();

    if (!$caHienTai) {
        return response()->json([
            'success' => false,
            'message' => 'Không xác định được ca làm việc hiện tại.'
        ], 422);
    }

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
            // ===============================
// KHUYẾN MÃI SẢN PHẨM TỰ ĐỘNG
// ===============================
foreach ($items as $item) {

    $idSanPham = (int) $item['bien_the']->product_id;
    $idBienThe = (int) $item['bien_the']->id;

    $soLuong = (int) $item['so_luong'];
    $giaBan = (float) $item['gia_ban'];
    $thanhTien = (float) $item['thanh_tien'];

    $khuyenMaisSanPham = DB::table('khuyen_mai')
        ->join(
            'khuyen_mai_san_pham',
            'khuyen_mai.id',
            '=',
            'khuyen_mai_san_pham.id_khuyen_mai'
        )
        ->where('khuyen_mai.trang_thai', 1)
        ->where('khuyen_mai.ngay_bat_dau', '<=', now())
        ->where('khuyen_mai.ngay_ket_thuc', '>=', now())
        ->where(function ($q) use ($idSanPham, $idBienThe) {

            $q->where(function ($sub) use ($idSanPham) {
                $sub->where(
                    'khuyen_mai_san_pham.id_san_pham',
                    $idSanPham
                )
                ->whereNull(
                    'khuyen_mai_san_pham.id_bien_the_san_pham'
                );
            })

            ->orWhere(
                'khuyen_mai_san_pham.id_bien_the_san_pham',
                $idBienThe
            );
        })
        ->select('khuyen_mai.*')
        ->get();

    $giamTotNhat = 0;

    foreach ($khuyenMaisSanPham as $km) {

        $minQty = (int) ($km->so_luong_sp_toi_thieu ?? 0);

        if ($minQty > 0 && $soLuong < $minQty) {
            continue;
        }

        if (
            $tongTienHang <
            (float) ($km->don_hang_toi_thieu ?? 0)
        ) {
            continue;
        }

        $loai = Str::of((string) $km->loai_giam_gia)
            ->trim()
            ->lower()
            ->ascii()
            ->replace([' ', '-'], '_')
            ->value();

        $giaTri = (float) ($km->gia_tri_giam ?? 0);
        $giamToiDa = (float) ($km->giam_toi_da ?? 0);

        $giam = 0;

        switch ($loai) {

            case 'phan_tram':
            case 'percent':
            case 'percentage':

                $giam =
                    $thanhTien *
                    $giaTri /
                    100;

                break;

            case 'amount':
            case 'fixed':
            case 'tien_mat':
            case 'so_tien':
            case 'giam_tien':

                // giảm theo từng sản phẩm
                $giam = min(
                    $giaTri * $soLuong,
                    $thanhTien
                );

                break;

            case 'bogo':
            case 'mua_1_tang_1':

                $giam =
                    floor($soLuong / 2) *
                    $giaBan;

                break;
        }

        if ($giamToiDa > 0) {
            $giam = min(
                $giam,
                $giamToiDa
            );
        }

        $giam = min(
            max(0, $giam),
            $thanhTien
        );

        $giamTotNhat = max(
            $giamTotNhat,
            $giam
        );
    }

    $tienGiamGia += $giamTotNhat;
}
            if ($request->id_khuyen_mai) {
    $khuyenMai = DB::table('khuyen_mai')
        ->where('id', $request->id_khuyen_mai)
        ->where('trang_thai', 1)
        ->first();

    if ($khuyenMai) {

        /*
         * Lấy phạm vi áp dụng khuyến mãi.
         */
        $phamViKhuyenMai = DB::table('khuyen_mai_san_pham')
            ->where('id_khuyen_mai', $khuyenMai->id)
            ->get();

        /*
         * Sản phẩm được áp dụng toàn bộ biến thể.
         */
        $idSanPhamsApDung = $phamViKhuyenMai
            ->whereNull('id_bien_the_san_pham')
            ->pluck('id_san_pham')
            ->map(fn ($id) => (int) $id)
            ->all();

        /*
         * Những biến thể được áp dụng riêng.
         */
        $idBienThesApDung = $phamViKhuyenMai
            ->whereNotNull('id_bien_the_san_pham')
            ->pluck('id_bien_the_san_pham')
            ->map(fn ($id) => (int) $id)
            ->all();

        /*
         * Lọc đúng các item được khuyến mãi.
         */
        $itemsDuocKhuyenMai = collect($items)
            ->filter(function ($item) use (
                $idSanPhamsApDung,
                $idBienThesApDung
            ) {
                $idSanPham = (int) $item['bien_the']->product_id;
                $idBienThe = (int) $item['bien_the']->id;

                $apDungTheoSanPham = in_array(
                    $idSanPham,
                    $idSanPhamsApDung,
                    true
                );

                $apDungTheoBienThe = in_array(
                    $idBienThe,
                    $idBienThesApDung,
                    true
                );

                return $apDungTheoSanPham || $apDungTheoBienThe;
            });

        /*
         * Tổng tiền chỉ của sản phẩm/biến thể được khuyến mãi.
         */
        $tongTienDuocKhuyenMai =
            (float) $itemsDuocKhuyenMai->sum('thanh_tien');

        /*
         * Tổng số lượng chỉ của item được khuyến mãi.
         */
        $tongSoLuongDuocKhuyenMai =
            (int) $itemsDuocKhuyenMai->sum('so_luong');

        /*
         * Chỉ tính khi trong giỏ thực sự có item
         * thuộc chương trình khuyến mãi.
         */
        if (
            $tongTienDuocKhuyenMai > 0 &&
            $tongTienHang >= ($khuyenMai->don_hang_toi_thieu ?? 0) &&
            $tongSoLuongDuocKhuyenMai >= ($khuyenMai->so_luong_sp_toi_thieu ?? 0)
        ) {
            $loaiGiamGia = Str::of(
                (string) $khuyenMai->loai_giam_gia
            )
                ->trim()
                ->lower()
                ->ascii()
                ->replace([' ', '-'], '_')
                ->value();

            $giaTriGiam =
                (float) ($khuyenMai->gia_tri_giam ?? 0);

            $giamToiDa =
                (float) ($khuyenMai->giam_toi_da ?? 0);

            switch ($loaiGiamGia) {

                case 'phan_tram':
                case 'percent':
                case 'percentage':

                    $tienGiamGia =
                        $tongTienDuocKhuyenMai
                        * $giaTriGiam
                        / 100;

                    if ($giamToiDa > 0) {
                        $tienGiamGia = min(
                            $tienGiamGia,
                            $giamToiDa
                        );
                    }

                    break;

                case 'bogo':
                case 'mua_1_tang_1':

                    foreach ($itemsDuocKhuyenMai as $item) {
                        $freeQty = floor(
                            $item['so_luong'] / 2
                        );

                        $tienGiamGia +=
                            $freeQty * $item['gia_ban'];
                    }

                    break;

              case 'amount':
case 'tien_mat':
case 'so_tien':
case 'giam_tien':
case 'fixed':

    // Giảm tiền theo từng sản phẩm
    $tienGiamGia = min(
        $giaTriGiam * $tongSoLuongDuocKhuyenMai,
        $tongTienDuocKhuyenMai
    );

    break;

                default:

                    return response()->json([
                        'success' => false,
                        'message' =>
                            'Loại giảm giá không hợp lệ: '
                            . $khuyenMai->loai_giam_gia
                    ], 422);
            }

            /*
             * Không bao giờ được giảm quá số tiền
             * của các item thuộc phạm vi khuyến mãi.
             */
            $tienGiamGia = min(
                max($tienGiamGia, 0),
                $tongTienDuocKhuyenMai
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
                'id_nguoi_dung' => $idNguoiBan,
                'id_khach_hang' => $request->id_khach_hang,
                'id_ca_lam_viec' => $caHienTai->id,
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
                $variantId = (int) $item['bien_the']->id;
                $soLuongBan = (int) $item['so_luong'];

                DB::table('chi_tiet_hoa_don')->insert([
                    'id_hoa_don' => $hoaDonId,
                    'id_bien_the_san_pham' => $item['bien_the']->id,
                    'so_luong' => $item['so_luong'],
                    'gia_ban' => $item['gia_ban'],
                    'thanh_tien' => $item['thanh_tien'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                if (!$isPayOS) {
                    // FIFO (FEFO): trừ từ lô có HSD gần nhất trước (sắp hết hạn bán trước),
                    // đồng thời ưu tiên lô nhập trước (id nhỏ hơn) khi cùng HSD.
                    // ChiTietLoHangObserver::updated() tự đồng bộ tổng tồn bien_the_san_pham.
                    $remaining = $soLuongBan;
                    $loList = ChiTietLoHang::where('variant_id', $variantId)
                        ->where('so_luong_ton', '>', 0)
                        ->orderBy('han_su_dung', 'asc')
                        ->orderBy('id', 'asc')
                        ->lockForUpdate()
                        ->get();

                    foreach ($loList as $lo) {
                        if ($remaining <= 0) {
                            break;
                        }
                        $truTuLo = (int) min($remaining, $lo->so_luong_ton);
                        $lo->so_luong_ton -= $truTuLo;
                        $lo->save();
                        $remaining -= $truTuLo;
                    }

                    if ($remaining > 0) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Sản phẩm "' . $item['bien_the']->ten_san_pham . '" không đủ tồn kho theo lô.',
                        ], 422);
                    }
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
    private function ganThuocTinhBienTheChoChiTiet($chiTiet): void
    {
        if ($chiTiet->isEmpty()) {
            return;
        }

        $variantIds = $chiTiet->pluck('id_bien_the_san_pham')->filter()->unique()->all();

        $thuocTinhRows = DB::table('bien_the_san_pham')
            ->whereIn('id', $variantIds)
            ->select('id', 'thuoc_tinh_ids')
            ->get()
            ->keyBy('id');

        $allAttrIds = $thuocTinhRows->pluck('thuoc_tinh_ids')
            ->filter()
            ->flatten()
            ->map(fn ($x) => (int) $x)
            ->unique()
            ->all();

        if (empty($allAttrIds)) {
            foreach ($chiTiet as $item) {
                $item->thuoc_tinh_hien_thi = [];
            }
            return;
        }

        $attrMap = DB::table('thuoc_tinh_san_pham')
            ->whereIn('id', $allAttrIds)
            ->select('id', 'ten_thuoc_tinh', 'thuoc_tinh_cha_id')
            ->get()
            ->keyBy('id');

        foreach ($chiTiet as $item) {
            $row = $thuocTinhRows->get($item->id_bien_the_san_pham);
            $rawIds = $row->thuoc_tinh_ids ?? [];
            $labels = [];

            foreach ((array) $rawIds as $aid) {
                $aid = (int) $aid;
                if (! isset($attrMap[$aid])) {
                    continue;
                }
                $attr = $attrMap[$aid];
                $parentId = $attr->thuoc_tinh_cha_id ? (int) $attr->thuoc_tinh_cha_id : null;
                if ($parentId && isset($attrMap[$parentId])) {
                    $labels[] = $attrMap[$parentId]->ten_thuoc_tinh . ': ' . $attr->ten_thuoc_tinh;
                } else {
                    $labels[] = $attr->ten_thuoc_tinh;
                }
            }

            $item->thuoc_tinh_hien_thi = array_values(array_filter($labels, fn ($v) => $v !== null && $v !== ''));
        }
    }

    public function chiTietHoaDon($id, DoiTraService $doiTraService)
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
            ->join('bien_the_san_pham', 'chi_tiet_hoa_don.id_bien_the_san_pham', '=', 'bien_the_san_pham.id')
            ->join('san_pham', 'bien_the_san_pham.product_id', '=', 'san_pham.id')
            ->select(
                'chi_tiet_hoa_don.*',
                'san_pham.ten_san_pham',
                'bien_the_san_pham.ten_bien_the',
                'bien_the_san_pham.ten_don_vi',
                'bien_the_san_pham.ma_vach',
                $this->tenHienThiBienTheSelect()
            )
            ->where('chi_tiet_hoa_don.id_hoa_don', $id)
            ->get();

        $returnSummary = $doiTraService->getInvoiceReturnSummary((int) $id);
        $lichSuDoiTra = $returnSummary['lichSuDoiTra'];
        $doiTraMoiNhat = session('last_doi_tra_id')
            ? $lichSuDoiTra->firstWhere('id', (int) session('last_doi_tra_id'))
            : null;
        $tongHopDoiTra = $returnSummary['tongHopDoiTra'];
        $chiTietTheoBienThe = $returnSummary['chiTietTheoBienThe'];

        foreach ($chiTiet as $item) {
            $returnItem = $chiTietTheoBienThe->get($item->id_bien_the_san_pham);
            $item->tong_da_tra = (int) ($returnItem->tong_tra_hang ?? 0);
            $item->tong_da_doi = (int) ($returnItem->tong_doi_hang ?? 0);
            $item->tong_da_doi_tra = (int) ($returnItem->tong_doi_tra ?? 0);
        }

        $this->ganThuocTinhBienTheChoChiTiet($chiTiet);

        return view('ban_hang.hoa-don.chi-tiet', compact(
            'hoaDon',
            'chiTiet',
            'lichSuDoiTra',
            'doiTraMoiNhat',
            'tongHopDoiTra'
        ))->with('auto_print', request()->boolean('print'));

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
            ->join('bien_the_san_pham', 'chi_tiet_hoa_don.id_bien_the_san_pham', '=', 'bien_the_san_pham.id')
            ->join('san_pham', 'bien_the_san_pham.product_id', '=', 'san_pham.id')
            ->select(
                'chi_tiet_hoa_don.*',
                'san_pham.ten_san_pham',
                'bien_the_san_pham.ten_bien_the',
                'bien_the_san_pham.ten_don_vi',
                'bien_the_san_pham.ma_vach',
                $this->tenHienThiBienTheSelect()
            )
            ->where('chi_tiet_hoa_don.id_hoa_don', $id)
            ->get();

        $return_invoice_id = session('return_invoice_id');
        $phieuDoiTra = null;
        if ($return_invoice_id) {
            $phieuDoiTra = DB::table('phieu')->where('id', $return_invoice_id)->first();
            if ($phieuDoiTra) {
                $phieuDoiTra->chi_tiet = DB::table('chi_tiet_phieu')
                    ->join('san_pham', 'chi_tiet_phieu.id_san_pham', '=', 'san_pham.id')
                    ->leftJoin('bien_the_san_pham', 'chi_tiet_phieu.variant_id', '=', 'bien_the_san_pham.id')
                    ->select('chi_tiet_phieu.*', 'san_pham.ten_san_pham', 'bien_the_san_pham.ten_bien_the', 'bien_the_san_pham.ten_don_vi')
                    ->where('id_phieu', $return_invoice_id)
                    ->get();
            }
        }

        return view('ban_hang.hoa-don.chi-tiet', compact('hoaDon', 'chiTiet', 'phieuDoiTra'))
            ->with('auto_print', request()->boolean('print'));
    }

    public function formDoiTra($id, DoiTraService $doiTraService)
    {
        $data = $doiTraService->getInvoiceReturnData((int) $id);
        $hoaDon = $data['hoaDon'];
        $chiTiet = $data['chiTiet'];
        $danhSachNguoiBan = $doiTraService->getEligibleSalesUsers();

        if (in_array($hoaDon->trang_thai, ['Đã hủy', 'Đã trả toàn bộ'], true)) {
            return back()->with('error', 'Hóa đơn này không thể đổi/trả hàng.');
        }

        return view('ban_hang.hoa-don.doi-tra', compact('hoaDon', 'chiTiet', 'danhSachNguoiBan'));

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
            ->join('bien_the_san_pham', 'chi_tiet_hoa_don.id_bien_the_san_pham', '=', 'bien_the_san_pham.id')
            ->join('san_pham', 'bien_the_san_pham.product_id', '=', 'san_pham.id')
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

    public function xuLyDoiTraNhanVien(XuLyDoiTraRequest $request, $id)
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
                $redirect->with('message', session('message'));
            }
            if (session()->has('last_doi_tra_id')) {
                $redirect->with('last_doi_tra_id', session('last_doi_tra_id'));
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
                DB::table('bien_the_san_pham')
                    ->where('id', $item->id_bien_the_san_pham)
                    ->increment('so_luong_ton', $item->so_luong);
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

    /**
     * Lấy danh sách người bán cho dropdown ở POS.
     * - Là NguoiDung có thông tin nhân sự (trang_thai = 1, không bị xoá mềm)
     * - Vai trò bất kỳ (Admin, Nhân viên, Trưởng ca, Bán hàng, ...)
     *   → Admin luôn xuất hiện, không cần phân ca
     */
    public function layNhanVienBanHang(): \Illuminate\Http\JsonResponse
    {
        $ds = DB::table('nguoi_dung')
            ->join('vai_tro', 'nguoi_dung.id_vai_tro', '=', 'vai_tro.id')
            ->whereNull('nguoi_dung.deleted_at')
            ->where('nguoi_dung.trang_thai', 1)
            ->orderBy('nguoi_dung.ho_ten')
            ->select(
                'nguoi_dung.id',
                'nguoi_dung.ho_ten',
                'nguoi_dung.email',
                'vai_tro.ten_vai_tro'
            )
            ->get();

        return response()->json($ds);
    }
    public function layKhuyenMai()
{
    $khuyenMais = DB::table('khuyen_mai')
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
        ->get();

    foreach ($khuyenMais as $khuyenMai) {

        $phamVi = DB::table('khuyen_mai_san_pham')
            ->where('id_khuyen_mai', $khuyenMai->id)
            ->get();

        /*
         * Có dữ liệu trong khuyen_mai_san_pham
         * => đây là khuyến mãi sản phẩm.
         */
        $khuyenMai->la_khuyen_mai_san_pham =
            $phamVi->isNotEmpty();

        /*
         * id_bien_the_san_pham = NULL
         * => áp dụng toàn bộ biến thể của sản phẩm.
         */
        $khuyenMai->id_san_phams = $phamVi
            ->whereNull('id_bien_the_san_pham')
            ->pluck('id_san_pham')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();

        /*
         * Khuyến mãi chỉ áp dụng riêng biến thể.
         */
        $khuyenMai->id_bien_thes = $phamVi
            ->whereNotNull('id_bien_the_san_pham')
            ->pluck('id_bien_the_san_pham')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();
    }

    return response()->json($khuyenMais);
}
}
