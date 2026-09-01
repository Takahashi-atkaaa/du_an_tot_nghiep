<?php

namespace App\Http\Controllers\Admin\KhoHang;

use App\Http\Controllers\Controller;
use App\Models\NhaCungCap;
use App\Models\SanPham;
use App\Models\LoHang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Trang Quản lý Kho hàng (Blade view) — góc nhìn Kế toán / Vận hành kho.
 *
 * Render server-side danh sách SanPham (sản phẩm cha) kèm:
 *  - with('bienTheSanPhams') eager load các biến thể
 *  - withSum('bienTheSanPhams', 'so_luong_ton') tính tổng tồn kho
 *  - selectSub: SUM(so_luong_ton * gia_von) → tính tổng giá trị vốn tồn kho
 *  - paginate() theo sản phẩm cha
 *
 * Mỗi <tr> cha có thể expand để hiện bảng báo cáo biến thể (đơn, không tab).
 */
class KhoHangController extends Controller
{
    public function index(Request $request)
    {
        $query = SanPham::query()
            ->with([
                'danhMuc:id,ten_danh_muc',
                'bienTheSanPhams' => function ($q) {
                    $q->orderBy('id', 'asc');
                },
            ])
            ->withSum('bienTheSanPhams', 'so_luong_ton')
            // Tổng giá trị vốn tồn kho của SẢN PHẨM CHA = SUM(so_luong_ton * gia_von) các biến thể.
            ->selectSub(
                DB::table('bien_the_san_pham')
                    ->selectRaw('COALESCE(SUM(so_luong_ton * gia_von), 0)')
                    ->whereColumn('product_id', 'san_pham.id'),
                'tong_gia_tri_von'
            )
            ->orderBy('id', 'desc');

        // 1) Tìm kiếm nhanh theo tên / thương hiệu / mã vạch / mã hàng
        if ($keyword = trim((string) $request->query('keyword', ''))) {
            $query->where(function ($q) use ($keyword) {
                $q->where('ten_san_pham', 'like', "%{$keyword}%")
                    ->orWhere('thuong_hieu', 'like', "%{$keyword}%")
                    ->orWhereHas('bienTheSanPhams', function ($v) use ($keyword) {
                        $v->where('ma_vach', 'like', "%{$keyword}%")
                            ->orWhere('ma_hang', 'like', "%{$keyword}%")
                            ->orWhere('ten_bien_the', 'like', "%{$keyword}%");
                    });
            });
        }

        // 2) Lọc theo danh mục (theo id)
        if ($danhMucId = $request->query('danh_muc_id')) {
            $query->where('id_danh_muc', (int) $danhMucId);
        }

        // 3) Lọc theo nhà cung cấp (theo tên) — sản phẩm có biến thể từng
        //    nhập từ NCC đó (chi_tiet_lo_hang → lo_hang → nha_cung_cap).
        if ($nccName = trim((string) $request->query('nha_cung_cap', ''))) {
            $query->whereHas('bienTheSanPhams.chiTietLoHang.loHang.nhaCungCap', function ($q) use ($nccName) {
                $q->where('ten_nha_cung_cap', $nccName);
            });
        }

        $sanPhams = $query->paginate(20)->withQueryString();

        // Map thêm các field tiện dụng cho Blade + tính "tình trạng kho"
        $trangThaiFilter = $request->query('trang_thai_ton', '');
        $sanPhams->getCollection()->transform(function ($sp) use ($trangThaiFilter) {
            $tongTon = (int) ($sp->bien_the_san_phams_sum_so_luong_ton ?? 0);
            $tongGiaTriVon = (float) ($sp->tong_gia_tri_von ?? 0);



            $sp->tong_ton = $tongTon;
            $sp->tong_gia_tri_ton = $tongGiaTriVon;

            // Đánh dấu tình trạng kho dựa trên sức khỏe tồn kho
            $sp->tinh_trang = $this->resolveTinhTrangKho($sp);

            // Áp dụng filter theo trạng thái tồn (nếu có)
            if ($trangThaiFilter === 'het-hang') {
                $sp->khong_phu_hop = $tongTon > 0;
            } elseif ($trangThaiFilter === 'binh-thuong') {
                $sp->khong_phu_hop = $tongTon <= 0;
            } elseif ($trangThaiFilter === 'duoi-dinh-muc') {
                $hasLow = $sp->bienTheSanPhams->contains(function ($bt) {
                    return $bt->so_luong_ton > 0 && $bt->so_luong_ton < $bt->dinh_muc_toi_thieu;
                });
                $sp->khong_phu_hop = !$hasLow;
            } else {
                $sp->khong_phu_hop = false;
            }

            return $sp;
        });

        if (in_array($trangThaiFilter, ['binh-thuong', 'het-hang', 'duoi-dinh-muc'], true)) {
            $filtered = $sanPhams->getCollection()->reject(fn($sp) => $sp->khong_phu_hop)->values();
            $sanPhams->setCollection($filtered);
        }

        // ===== DẢI KPI — góc nhìn Giám đốc / Kế toán =====
        // 1) Tổng giá trị kho (VND) = tổng các SUM(so_luong_ton * gia_von) trên toàn bộ SP
        
        $tongGiaTriKho = (float) DB::table('bien_the_san_pham')
            ->whereNull('deleted_at')
            ->sum(DB::raw('so_luong_ton * gia_von'));

        // 2) Số SẢN PHẨM CHA có ít nhất 1 biến thể dưới định mức (còn tồn nhưng < định mức)
        $spDuoiDinhMuc = DB::table('bien_the_san_pham as bt')
            ->join('san_pham as sp', 'sp.id', '=', 'bt.product_id')
            ->whereNull('bt.deleted_at')
            ->whereNull('sp.deleted_at')
            ->where('bt.so_luong_ton', '>', 0)
            ->whereColumn('bt.so_luong_ton', '<', 'bt.dinh_muc_toi_thieu')
            ->distinct()
            ->count('sp.id');

        // 3) Số SẢN PHẨM CHA có tổng tồn = 0 (hết sạch)
        $spHetHang = (clone $sanPhams->getCollection())
            ->filter(fn($sp) => (int) $sp->tong_ton === 0)
            ->count();

        $nhaCungCaps = NhaCungCap::orderBy('id', 'asc')->get();

        return view('admin_xem_truoc.kho-hang.index', [
            'sanPhams' => $sanPhams,
            'nhaCungCaps' => $nhaCungCaps,
            'kpiTongGiaTriKho' => $tongGiaTriKho,
            'kpiSpDuoiDinhMuc' => $spDuoiDinhMuc,
            'kpiSpHetHang' => $spHetHang,
        ]);
    }

    /**
     * Phân loại "Tình trạng kho" cho sản phẩm cha:
     *  - 'het-sach'  : Tổng tồn == 0
     *  - 'co-thieu'  : Có ≥1 biến thể dưới định mức (mà vẫn còn tồn)
     *  - 'an-toan'   : Còn tồn và tất cả biến thể đều đạt định mức
     */
    private function resolveTinhTrangKho($sp): string
    {
        $tongTon = (int) ($sp->bien_the_san_phams_sum_so_luong_ton ?? 0);
        if ($tongTon <= 0) {
            return 'het-sach';
        }
        $hasLow = $sp->bienTheSanPhams->contains(function ($bt) {
            return $bt->so_luong_ton > 0 && $bt->so_luong_ton < $bt->dinh_muc_toi_thieu;
        });
        return $hasLow ? 'co-thieu' : 'an-toan';
    }

    /**
     * Chi tiết lô hàng - trang riêng biệt
     */
    public function chiTietLoHang($id)
    {
        $loHang = LoHang::with([
            'nhaCungCap:id,ten_nha_cung_cap,email,so_dien_thoai',
            'phieu.nguoiDung:id,ho_ten',
            'chiTietLoHang' => function ($q) {
                $q->orderBy('han_su_dung', 'asc');
            },
            'chiTietLoHang.variant.product.danhMuc:id,ten_danh_muc',
            'chiTietLoHang.product.danhMuc:id,ten_danh_muc',
        ])->findOrFail($id);

        // Tính toán các giá trị
        $chiTiet = $loHang->chiTietLoHang;
        
        $tongGiaTriBanDau = 0;
        $tongGiaTriConLai = 0;
        $tongSlNhap = 0;
        $tongSlTon = 0;
        $tongSlXuat = 0;

        foreach ($chiTiet as $ct) {
            $slNhap = (int) $ct->so_luong_nhap;
            $slTon = (int) $ct->so_luong_ton;
            $giaNhap = (float) $ct->gia_nhap;

            $tongGiaTriBanDau += $slNhap * $giaNhap;
            $tongGiaTriConLai += $slTon * $giaNhap;
            $tongSlNhap += $slNhap;
            $tongSlTon += $slTon;
            $tongSlXuat += ($slNhap - $slTon);
        }

        // Tỷ lệ tồn kho
        $tyLeTonKho = $tongSlNhap > 0 ? ($tongSlTon / $tongSlNhap) * 100 : 0;

        // Lịch sử xuất hàng từ lô này
        // Tìm các phiếu xuất đã trừ từ chi_tiet_lo_hang của lô này
        $lichSuXuat = DB::table('chi_tiet_phieu as ctp')
            ->join('chi_tiet_lo_hang as ctlh', 'ctp.id_chi_tiet_lo_hang', '=', 'ctlh.id')
            ->join('phieu as p', 'ctp.id_phieu', '=', 'p.id')
            ->join('phieu_xuat as px', 'p.id', '=', 'px.id_phieu')
            ->leftJoin('nguoi_dung as u', 'p.id_nguoi_dung', '=', 'u.id')
            ->leftJoin('bien_the_san_pham as bt', 'ctlh.variant_id', '=', 'bt.id')
            ->leftJoin('san_pham as sp', function ($join) {
                $join->on('bt.product_id', '=', 'sp.id')
                     ->orOn('ctlh.id_san_pham', '=', 'sp.id');
            })
            ->where('ctlh.id_lo_hang', $id)
            ->where('p.loai_phieu', 'xuat')
            ->select(
                'px.id as phieu_xuat_id',
                'p.id as ma_phieu',
                'px.loai_xuat',
                'p.created_at as ngay_xuat',
                'u.ho_ten as nguoi_tao',
                'sp.ten_san_pham',
                'bt.ten_bien_the',
                'ctp.so_luong as so_luong_xuat'
            )
            ->orderBy('p.created_at', 'desc')
            ->get();

        return view('admin_xem_truoc.kho-hang.chi-tiet', [
            'loHang' => $loHang,
            'chiTiet' => $chiTiet,
            'tongGiaTriBanDau' => $tongGiaTriBanDau,
            'tongGiaTriConLai' => $tongGiaTriConLai,
            'tongSlNhap' => $tongSlNhap,
            'tongSlTon' => $tongSlTon,
            'tongSlXuat' => $tongSlXuat,
            'tyLeTonKho' => $tyLeTonKho,
            'lichSuXuat' => $lichSuXuat,
        ]);
    }
}
