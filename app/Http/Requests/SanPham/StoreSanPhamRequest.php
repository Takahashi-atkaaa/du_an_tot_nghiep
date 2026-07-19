<?php

namespace App\Http\Requests\SanPham;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSanPhamRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'ten_san_pham' => 'required|string|max:255',
            'id_danh_muc' => 'required|integer|exists:danh_muc_san_pham,id',
            'thuong_hieu' => 'nullable|string|max:255',
            'mo_ta' => 'nullable|string',
            'trang_thai' => 'sometimes|boolean',

            // Biến thể (variants)
            'bien_the' => 'sometimes|array',
            'bien_the.*.id' => 'nullable|integer',
            'bien_the.*.ten_bien_the' => 'nullable|string|max:255',
            'bien_the.*.thuoc_tinh_ids' => 'nullable|string',
            'bien_the.*.gia_von' => 'nullable|numeric|min:0',
            'bien_the.*.gia_ban' => 'present|numeric|min:0',
            'bien_the.*.so_luong_ton' => 'nullable|integer|min:0',
            'bien_the.*.dinh_muc_toi_thieu' => 'nullable|integer|min:0',
            'bien_the.*.ma_hang' => 'nullable|string|max:255',
            'bien_the.*.ma_vach' => [
                'nullable',
                'string',
                Rule::unique('bien_the_san_pham', 'ma_vach'),
            ],
            'bien_the.*.hinh_anh' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',

            // Biến thể đơn vị (la_don_vi = true khi sp chỉ có đơn vị)
            'bien_the.*.la_don_vi' => 'nullable|boolean',
            'bien_the.*.ten_don_vi' => 'nullable|string|max:255',

            // Đơn vị quy đổi (nested trong bien_the)
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
                Rule::unique('don_vi_quy_doi', 'ma_vach'),
            ],
            'bien_the.*.units.*.hinh_anh' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',

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
            'id_danh_muc.required' => 'Danh mục sản phẩm không được để trống.',
            'id_danh_muc.exists' => 'Danh mục sản phẩm không hợp lệ.',
            'bien_the.*.gia_ban.required' => 'Giá bán biến thể không được để trống.',
            'bien_the.*.units.*.ten_don_vi.required' => 'Tên đơn vị không được để trống.',
            'bien_the.*.units.*.so_luong_san_pham_trong_don_vi.required' => 'Số lượng sản phẩm trong đơn vị không được để trống.',
            'bien_the.*.units.*.gia_ban_quy_doi.required' => 'Giá bán quy đổi không được để trống.',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // Validate ma_vach khong trung trong cung array bien_the
            $variants = $this->input('bien_the', []);
            $barcodes = array_filter(array_column($variants, 'ma_vach'));
            if (collect($barcodes)->duplicates()->isNotEmpty()) {
                $validator->errors()->add('bien_the', 'Mã vạch biến thể không được trùng nhau.');
            }
        });
    }
}
