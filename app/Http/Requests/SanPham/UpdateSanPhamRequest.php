<?php

namespace App\Http\Requests\SanPham;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\BienTheSanPham;
use App\Models\DonViQuyDoi;
use App\Models\Product;

class UpdateSanPhamRequest extends FormRequest
{
    private ?string $currentProductName = null;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $productId = (int) $this->route('id');

        // Lay ten hien tai cua san pham trong DB
        $product = Product::withTrashed()->find($productId);
        $this->currentProductName = $product?->ten_san_pham;

        $tenSanPhamInput = $this->input('ten_san_pham');

        $tenSanPhamRules = ['required', 'string', 'max:255'];
        // Neu ten KHONG thay doi so voi DB hien tai → khong can kiem tra unique
        if ($product && trim($tenSanPhamInput) === trim($this->currentProductName)) {
            // giu nguyen ten → bo qua unique
        } else {
            // ten thay doi hoac tao moi → kiem tra unique (bo qua chinh no + soft-deleted)
            $tenSanPhamRules[] = Rule::unique('san_pham', 'ten_san_pham')
                ->ignore($productId)
                ->where(fn($q) => $q->whereNull('deleted_at'));
        }

        return [
            'ten_san_pham' => $tenSanPhamRules,
            'id_danh_muc' => 'required|integer|exists:danh_muc_san_pham,id',
            'thuong_hieu' => 'nullable|string|max:255',
            'mo_ta' => 'nullable|string',
            'trang_thai' => 'sometimes|boolean',

            // Biến thể
            'bien_the' => 'sometimes|array',
            'bien_the.*.id' => 'nullable|integer',
            'bien_the.*.ten_bien_the' => 'nullable|string|max:255',
            'bien_the.*.thuoc_tinh_ids' => 'nullable|string',
            'bien_the.*.thuoc_tinh_labels' => 'nullable|array',
            'bien_the.*.thuoc_tinh_labels.*' => 'nullable|string|max:255',
            'bien_the.*.gia_von' => 'nullable|numeric|min:0',
            'bien_the.*.gia_ban' => 'present|numeric|min:0',
            'bien_the.*.so_luong_ton' => 'nullable|integer|min:0',
            'bien_the.*.dinh_muc_toi_thieu' => 'nullable|integer|min:0',
            'bien_the.*.ma_hang' => 'nullable|string|max:255',
            'bien_the.*.ma_vach' => [
                'nullable',
                'string',
                'max:255',
            ],
            // Ảnh biến thể giờ gửi qua Base64.
            'bien_the.*.hinh_anh_base64' => 'nullable|string',
            'bien_the.*.hinh_anh_action' => 'nullable|string|in:replace,delete,keep',
            'bien_the.*.trang_thai' => 'sometimes|boolean',

            // Biến thể đơn vị (la_don_vi = true khi sp chỉ có đơn vị)
            'bien_the.*.la_don_vi' => 'nullable|boolean',
            'bien_the.*.ten_don_vi' => 'nullable|string|max:255',
            // FIX Gom-nhom: ty_le + ten_don_vi_bien_the dùng để Backend gom nhóm
            // các dòng cùng thuộc tính thành 1 biến thể CHA + N đơn vị quy đổi
            'bien_the.*.ty_le' => 'nullable|integer|min:1',
            'bien_the.*.ten_don_vi_bien_the' => 'nullable|string|max:255',

            // Đơn vị quy đổi
            'bien_the.*.units' => 'sometimes|array',
            'bien_the.*.units.*.id' => 'nullable|integer|exists:don_vi_quy_doi,id',
            'bien_the.*.units.*.ten_don_vi' => 'present|string|max:255',
            'bien_the.*.units.*.so_luong_san_pham_trong_don_vi' => 'present|integer|min:1',
            'bien_the.*.units.*.gia_von_quy_doi' => 'nullable|numeric|min:0',
            'bien_the.*.units.*.gia_ban_quy_doi' => 'present|numeric|min:0',
            'bien_the.*.units.*.gia_ban_si' => 'nullable|numeric|min:0',
            'bien_the.*.units.*.ma_vach' => [
                'nullable',
                'string',
            ],
            'bien_the.*.units.*.hinh_anh_base64' => 'nullable|string',
            'bien_the.*.units.*.hinh_anh_action' => 'nullable|string|in:replace,delete,keep',

            // Thuộc tính mới: thuoc_tinh do user gõ tạo mới
            'new_attributes' => 'sometimes|array',
            'new_attributes.*.group_name' => 'required|string|max:255',
            'new_attributes.*.label' => 'required|string|max:255',
            'new_attributes.*.parent_id' => 'nullable|integer|exists:thuoc_tinh_san_pham,id',
        ];
    }

    public function messages(): array
    {
        return [
            'ten_san_pham.required' => 'Tên sản phẩm không được để trống.',
            'ten_san_pham.unique' => 'Tên sản phẩm đã tồn tại.',
            'id_danh_muc.required' => 'Danh mục sản phẩm không được để trống.',
            'bien_the.*.gia_ban.required' => 'Giá bán biến thể không được để trống.',
            'bien_the.*.units.*.ten_don_vi.required' => 'Tên đơn vị không được để trống.',
            'bien_the.*.units.*.so_luong_san_pham_trong_don_vi.required' => 'Số lượng sản phẩm trong đơn vị không được để trống.',
            'bien_the.*.units.*.gia_ban_quy_doi.required' => 'Giá bán quy đổi không được để trống.',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $variants = $this->input('bien_the', []);

            // Check variant barcodes uniqueness in DB (ignore current variant id)
            foreach ($variants as $vIndex => $variant) {
                $barcode = trim($variant['ma_vach'] ?? '');
                $variantId = $variant['id'] ?? null;
                if ($barcode !== '') {
                    $q = BienTheSanPham::where('ma_vach', $barcode);
                    if ($variantId) $q->where('id', '!=', $variantId);
                    if ($q->exists()) {
                        $validator->errors()->add("bien_the.{$vIndex}.ma_vach", 'Mã vạch biến thể đã tồn tại.');
                    }
                }

                // Check variant ma_hang uniqueness in DB
                $maHang = trim($variant['ma_hang'] ?? '');
                if ($maHang !== '') {
                    $mhq = BienTheSanPham::where('ma_hang', $maHang);
                    if ($variantId) $mhq->where('id', '!=', $variantId);
                    if ($mhq->exists()) {
                        $validator->errors()->add("bien_the.{$vIndex}.ma_hang", 'Mã hàng biến thể đã tồn tại.');
                    }
                }

                // Check units barcodes uniqueness in DB (ignore current unit id)
                $units = $variant['units'] ?? [];
                foreach ($units as $uIndex => $unit) {
                    $uBarcode = trim($unit['ma_vach'] ?? '');
                    $unitId = $unit['id'] ?? null;
                    if ($uBarcode !== '') {
                        $uq = DonViQuyDoi::where('ma_vach', $uBarcode);
                        if ($unitId) $uq->where('id', '!=', $unitId);
                        if ($uq->exists()) {
                            $validator->errors()->add("bien_the.{$vIndex}.units.{$uIndex}.ma_vach", 'Mã vạch đơn vị quy đổi đã tồn tại.');
                        }
                    }
                }
            }
        });
    }
}
