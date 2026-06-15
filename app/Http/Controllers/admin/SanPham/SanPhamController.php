<?php

namespace App\Http\Controllers\Admin\SanPham;

use App\Http\Controllers\Controller;
use App\Http\Requests\SanPham\StoreSanPhamRequest;
use App\Http\Requests\SanPham\UpdateSanPhamRequest;
use App\Models\DanhMucSanPham;
use App\Models\DonViSanPham;
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

        $thuocTinhs = ThuocTinhSanPham::where('trang_thai', true)
            ->orderBy('ten_thuoc_tinh')
            ->get();

        $sanPhams = SanPham::query()
            ->with(['danhMuc', 'donVi'])
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
            'thuocTinhs' => $thuocTinhs,
            'keyword' => $keyword,
            'danhMucId' => $danhMucId,
            'trangThai' => $request->input('trang_thai'),
        ]);
    }

    public function store(StoreSanPhamRequest $request)
    {
        $data = $request->validated();

        $imagePath = null;
        if ($request->hasFile('hinh_anh')) {
            $file = $request->file('hinh_anh');
            $filename = time() . '_' . preg_replace('/[^a-zA-Z0-9\.\-_]/', '_', $file->getClientOriginalName());
            $file->move($this->uploadDirectory(), $filename);
            $imagePath = 'uploads/san-pham/' . $filename;
        }

        $variantBarcodes = array_filter(array_column($data['bien_the'] ?? [], 'ma_vach'));
        if ($duplicate = collect($variantBarcodes)->duplicates()->first()) {
            return redirect()->back()->withInput()->withErrors(['bien_the' => 'Mã vạch biến thể không được trùng nhau.']);
        }

        if (!empty($variantBarcodes) && SanPham::whereIn('ma_vach', $variantBarcodes)->exists()) {
            return redirect()->back()->withInput()->withErrors(['bien_the' => 'Một hoặc nhiều mã vạch biến thể đã tồn tại trong hệ thống.']);
        }

        $baseUnit = $this->findOrCreateDonVi($data['id_don_vi'] ?? 'Cái', 1);

        $sanPhamData = [
            'id_danh_muc' => $data['id_danh_muc'],
            'ten_san_pham' => $data['ten_san_pham'],
            'ma_hang' => $this->generateUniqueMaHang(),
            'ma_vach' => $this->generateUniqueMaVach(),
            'thuong_hieu' => $data['thuong_hieu'] ?? null,
            'gia_von' => $data['gia_von'] ?? 0,
            'gia_ban' => $data['gia_ban'],
            'so_luong_ton_kho' => $data['so_luong_ton_kho'] ?? 0,
            'dinh_muc_toi_thieu' => $data['dinh_muc_toi_thieu'] ?? 0,
            'mo_ta' => $data['mo_ta'] ?? null,
            'id_thuoc_tinh' => $data['id_thuoc_tinh'] ?? null,
            'id_don_vi' => $baseUnit->id,
            'hinh_anh' => $imagePath,
            'trang_thai' => $data['trang_thai'] ?? true,
        ];

        SanPham::create($sanPhamData);

        foreach ($data['bien_the'] ?? [] as $variant) {
            if (empty($variant['ten_bien_the'])) {
                continue;
            }

            $unit = $this->findOrCreateDonVi($variant['ten_bien_the'], 1);
            SanPham::create([
                'id_danh_muc' => $data['id_danh_muc'],
                'ten_san_pham' => $data['ten_san_pham'],
                'ma_hang' => $this->generateUniqueMaHang(),
                'ma_vach' => !empty($variant['ma_vach']) ? $variant['ma_vach'] : $this->generateUniqueMaVach(),
                'thuong_hieu' => $data['thuong_hieu'] ?? null,
                'gia_von' => $data['gia_von'] ?? 0,
                'gia_ban' => $variant['gia_ban_bien'] ?? $data['gia_ban'],
                'so_luong_ton_kho' => $variant['so_luong_bien'] ?? 0,
                'dinh_muc_toi_thieu' => $data['dinh_muc_toi_thieu'] ?? 0,
                'mo_ta' => $data['mo_ta'] ?? null,
                'id_thuoc_tinh' => $data['id_thuoc_tinh'] ?? null,
                'id_don_vi' => $unit->id,
                'hinh_anh' => $imagePath,
                'trang_thai' => $data['trang_thai'] ?? true,
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

    public function update(UpdateSanPhamRequest $request, $id)
    {
        $sanPham = SanPham::findOrFail($id);
        $data = $request->validated();

        $oldImagePath = $sanPham->hinh_anh;
        $imagePath = $oldImagePath;
        if ($request->hasFile('hinh_anh')) {
            $file = $request->file('hinh_anh');
            $filename = time() . '_' . preg_replace('/[^a-zA-Z0-9\.\-_]/', '_', $file->getClientOriginalName());
            $file->move($this->uploadDirectory(), $filename);
            $imagePath = 'uploads/san-pham/' . $filename;

            if ($oldImagePath && $oldImagePath !== $imagePath) {
                $this->deleteProductImageIfUnused($oldImagePath, $sanPham->id);
            }
        }

        $baseUnit = $this->findOrCreateDonVi($data['id_don_vi'] ?? 'Cái', 1);

        $sanPham->update([
            'id_danh_muc' => $data['id_danh_muc'],
            'ten_san_pham' => $data['ten_san_pham'],
            'thuong_hieu' => $data['thuong_hieu'] ?? null,
            'gia_von' => $data['gia_von'] ?? 0,
            'gia_ban' => $data['gia_ban'],
            'so_luong_ton_kho' => $data['so_luong_ton_kho'] ?? 0,
            'dinh_muc_toi_thieu' => $data['dinh_muc_toi_thieu'] ?? 0,
            'mo_ta' => $data['mo_ta'] ?? null,
            'id_thuoc_tinh' => $data['id_thuoc_tinh'] ?? null,
            'id_don_vi' => $baseUnit->id,
            'hinh_anh' => $imagePath,
            'trang_thai' => $data['trang_thai'] ?? true,
        ]);

        return redirect(url('admin/san-pham'))->with('success', 'Cập nhật sản phẩm thành công.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $sanPham = SanPham::findOrFail($id);
        $imagePath = $sanPham->hinh_anh;

        $sanPham->delete();

        if ($imagePath) {
            $this->deleteProductImageIfUnused($imagePath);
        }

        return redirect()->route('san-pham.index')
            ->with('success', 'Đã xóa sản phẩm.');
    }

    protected function uploadDirectory(): string
    {
        $path = public_path('uploads/san-pham');

        if (! is_dir($path)) {
            mkdir($path, 0755, true);
        }

        return $path;
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
