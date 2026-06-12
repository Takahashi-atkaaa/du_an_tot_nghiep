<?php

namespace App\Http\Controllers\Admin\SanPham;

use App\Http\Controllers\Controller;
use App\Models\DanhMucSanPham;
use App\Models\DonViSanPham;
use App\Models\ThuocTinhSanPham;
use App\Models\SanPham;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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

        $thuocTinhs = ThuocTinhSanPham::where('trang_thai', true)
            ->orderBy('ten_thuoc_tinh')
            ->get();

        $sanPhams = SanPham::query()
            ->with(['danhMuc', 'donVi'])
            ->when($keyword, function ($query, $keyword) {
                $query->searchByFields($keyword, ['ten_san_pham', 'ma_vach', 'thuong_hieu']);
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
            'thuocTinhs' => $thuocTinhs,
            'keyword' => $keyword,
            'danhMucId' => $danhMucId,
            'trangThai' => $request->input('trang_thai'),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'ten_san_pham' => 'required|string|max:255',
            'ma_vach' => 'required|string|max:255|unique:san_pham,ma_vach',
            'thuong_hieu' => 'nullable|string|max:255',
            'id_danh_muc' => 'required|exists:danh_muc_san_pham,id',
            'id_thuoc_tinh' => 'nullable|exists:thuoc_tinh_san_pham,id',
            'don_vi_co_ban' => 'required|string|max:255',
            'gia_von' => 'nullable|numeric|min:0',
            'gia_ban' => 'required|numeric|min:0',
            'so_luong_ton_kho' => 'nullable|integer|min:0',
            'dinh_muc_toi_thieu' => 'nullable|integer|min:0',
            'mo_ta' => 'nullable|string',
            'hinh_anh' => 'nullable|image|max:2048',
            'variants' => 'nullable|array',
            'variants.*.ten_don_vi' => 'required_with:variants|string|max:255',
            'variants.*.so_luong_san_pham_trong_don_vi' => 'required_with:variants|integer|min:1',
            'variants.*.gia_ban' => 'required_with:variants|numeric|min:0',
            'variants.*.ma_vach' => 'nullable|string|max:255',
        ]);

        $imagePath = null;
        if ($request->hasFile('hinh_anh')) {
            $file = $request->file('hinh_anh');
            $filename = time() . '_' . preg_replace('/[^a-zA-Z0-9\.\-_]/', '_', $file->getClientOriginalName());
            $file->move(public_path('uploads/san-pham'), $filename);
            $imagePath = 'uploads/san-pham/' . $filename;
        }

        $variantBarcodes = array_filter(array_column($data['variants'] ?? [], 'ma_vach'));
        if ($duplicate = collect($variantBarcodes)->duplicates()->first()) {
            return redirect()->back()->withInput()->withErrors(['variants' => 'Mã vạch biến thể không được trùng nhau.']);
        }

        if (!empty($variantBarcodes) && SanPham::whereIn('ma_vach', $variantBarcodes)->exists()) {
            return redirect()->back()->withInput()->withErrors(['variants' => 'Một hoặc nhiều mã vạch biến thể đã tồn tại trong hệ thống.']);
        }

        $baseUnit = $this->findOrCreateDonVi($data['don_vi_co_ban'], 1);

        $sanPhamData = [
            'id_danh_muc' => $data['id_danh_muc'],
            'ten_san_pham' => $data['ten_san_pham'],
            'ma_hang' => $this->generateUniqueMaHang(),
            'ma_vach' => $data['ma_vach'],
            'thuong_hieu' => $data['thuong_hieu'] ?? null,
            'gia_von' => $data['gia_von'] ?? 0,
            'gia_ban' => $data['gia_ban'],
            'so_luong_ton_kho' => $data['so_luong_ton_kho'] ?? 0,
            'dinh_muc_toi_thieu' => $data['dinh_muc_toi_thieu'] ?? 0,
            'mo_ta' => $data['mo_ta'] ?? null,
            'id_thuoc_tinh' => $data['id_thuoc_tinh'] ?? null,
            'id_don_vi' => $baseUnit->id,
            'hinh_anh' => $imagePath,
            'trang_thai' => true,
        ];

        SanPham::create($sanPhamData);

        foreach ($data['variants'] ?? [] as $variant) {
            if (empty($variant['ten_don_vi'])) {
                continue;
            }

            $unit = $this->findOrCreateDonVi($variant['ten_don_vi'], (int) $variant['so_luong_san_pham_trong_don_vi']);
            SanPham::create([
                'id_danh_muc' => $data['id_danh_muc'],
                'ten_san_pham' => $data['ten_san_pham'],
                'ma_hang' => $this->generateUniqueMaHang(),
                'ma_vach' => trim($variant['ma_vach']) !== '' ? $variant['ma_vach'] : $this->generateUniqueMaVach(),
                'thuong_hieu' => $data['thuong_hieu'] ?? null,
                'gia_von' => $data['gia_von'] ?? 0,
                'gia_ban' => $variant['gia_ban'],
                'so_luong_ton_kho' => 0,
                'dinh_muc_toi_thieu' => $data['dinh_muc_toi_thieu'] ?? 0,
                'mo_ta' => $data['mo_ta'] ?? null,
                'id_thuoc_tinh' => $data['id_thuoc_tinh'] ?? null,
                'id_don_vi' => $unit->id,
                'hinh_anh' => $imagePath,
                'trang_thai' => true,
            ]);
        }

        return redirect()->back()->with('success', 'Đã thêm sản phẩm mới.');
    }

    public function edit($id)
    {
        $sanPham = SanPham::findOrFail($id);
        $danhMucs = DanhMucSanPham::orderBy('ten_danh_muc')->get();
        $donVis = DonViSanPham::where('trang_thai', true)->orderBy('ten_don_vi')->get();
        $thuocTinhs = ThuocTinhSanPham::where('trang_thai', true)->orderBy('ten_thuoc_tinh')->get();

        return view('admin_xem_truoc.san-pham-sua', compact('sanPham', 'danhMucs', 'donVis', 'thuocTinhs'));
    }

    public function update(Request $request, $id)
    {
        $sanPham = SanPham::findOrFail($id);

        $data = $request->validate([
            'ten_san_pham' => 'required|string|max:255',
            'ma_vach' => 'required|string|max:255|unique:san_pham,ma_vach,' . $sanPham->id,
            'thuong_hieu' => 'nullable|string|max:255',
            'id_danh_muc' => 'required|exists:danh_muc_san_pham,id',
            'id_thuoc_tinh' => 'nullable|exists:thuoc_tinh_san_pham,id',
            'don_vi_co_ban' => 'required|string|max:255',
            'gia_von' => 'nullable|numeric|min:0',
            'gia_ban' => 'required|numeric|min:0',
            'so_luong_ton_kho' => 'nullable|integer|min:0',
            'dinh_muc_toi_thieu' => 'nullable|integer|min:0',
            'mo_ta' => 'nullable|string',
            'hinh_anh' => 'nullable|image|max:2048',
        ]);

        $imagePath = $sanPham->hinh_anh;
        if ($request->hasFile('hinh_anh')) {
            $file = $request->file('hinh_anh');
            $filename = time() . '_' . preg_replace('/[^a-zA-Z0-9\.\-_]/', '_', $file->getClientOriginalName());
            $file->move(public_path('uploads/san-pham'), $filename);
            $imagePath = 'uploads/san-pham/' . $filename;
        }

        $baseUnit = $this->findOrCreateDonVi($data['don_vi_co_ban'], 1);

        $sanPham->update([
            'id_danh_muc' => $data['id_danh_muc'],
            'ten_san_pham' => $data['ten_san_pham'],
            'ma_vach' => $data['ma_vach'],
            'thuong_hieu' => $data['thuong_hieu'] ?? null,
            'gia_von' => $data['gia_von'] ?? 0,
            'gia_ban' => $data['gia_ban'],
            'so_luong_ton_kho' => $data['so_luong_ton_kho'] ?? 0,
            'dinh_muc_toi_thieu' => $data['dinh_muc_toi_thieu'] ?? 0,
            'mo_ta' => $data['mo_ta'] ?? null,
            'id_thuoc_tinh' => $data['id_thuoc_tinh'] ?? null,
            'id_don_vi' => $baseUnit->id,
            'hinh_anh' => $imagePath,
        ]);

        return redirect(url('admin/san-pham'))->with('success', 'Cập nhật sản phẩm thành công.');
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
        $sanPham = SanPham::with(['danhMuc', 'donVi', 'thuocTinh'])->findOrFail($id);

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
}
