<?php

namespace App\Http\Requests\SanPham;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\BienTheSanPham;
use App\Models\DonViQuyDoi;

class UpdateSanPhamRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $productId = $this->route('id');

        return [
            'ten_san_pham' => [
                'required',
                'string',
                'max:255',
                Rule::unique('san_pham', 'ten_san_pham')->ignore($productId),
            ],
            'id_danh_muc' => 'required|integer|exists:danh_muc_san_pham,id',
            'thuong_hieu' => 'nullable|string|max:255',
            'mo_ta' => 'nullable|string',
            'trang_thai' => 'sometimes|boolean',

            // Biến thể
            'bien_the' => 'sometimes|array',
            'bien_the.*.id' => 'nullable|integer|exists:bien_the_san_pham,id',
            'bien_the.*.ten_bien_the' => 'nullable|string|max:255',
            'bien_the.*.thuoc_tinh_ids' => 'nullable|string',
            'bien_the.*.gia_von' => 'nullable|numeric|min:0',
            'bien_the.*.gia_ban' => 'required|numeric|min:0',
            'bien_the.*.so_luong_ton' => 'nullable|integer|min:0',
            'bien_the.*.dinh_muc_toi_thieu' => 'nullable|integer|min:0',
            'bien_the.*.ma_hang' => 'nullable|string|max:255',
            'bien_the.*.ma_vach' => [
                'nullable',
                'string',
                'max:255',
            ],
            'bien_the.*.hinh_anh' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'bien_the.*.trang_thai' => 'sometimes|boolean',

            // Đơn vị quy đổi
            'bien_the.*.units' => 'sometimes|array',
            'bien_the.*.units.*.id' => 'nullable|integer|exists:don_vi_quy_doi,id',
            'bien_the.*.units.*.ten_don_vi' => 'required|string|max:255',
            'bien_the.*.units.*.ty_le_quy_doi' => 'required|integer|min:1',
            'bien_the.*.units.*.gia_von_quy_doi' => 'nullable|numeric|min:0',
            'bien_the.*.units.*.gia_ban_quy_doi' => 'required|numeric|min:0',
            'bien_the.*.units.*.gia_ban_si' => 'nullable|numeric|min:0',
            'bien_the.*.units.*.ma_vach' => [
                'nullable',
                'string',
            ],
            'bien_the.*.units.*.hinh_anh' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
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
            'bien_the.*.units.*.ty_le_quy_doi.required' => 'Tỷ lệ quy đổi không được để trống.',
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
