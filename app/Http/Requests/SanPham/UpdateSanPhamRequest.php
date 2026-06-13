<?php

namespace App\Http\Requests\SanPham;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSanPhamRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $sanPhamId = $this->route('id') ?? $this->route('san_pham')?->id;

        return [
            'ten_san_pham' => [
                'required',
                'string',
                'max:255',
                Rule::unique('san_pham', 'ten_san_pham')->ignore($sanPhamId),
            ],
            'id_danh_muc' => 'required|integer|exists:danh_muc_san_pham,id',
            'thuong_hieu' => 'nullable|string|max:255',
            'gia_von' => 'required|numeric|min:0',
            'gia_ban' => 'required|numeric|min:0|gt:gia_von',
            'so_luong_ton_kho' => 'required|integer|min:0',
            'mo_ta' => 'nullable|string',
            'id_thuoc_tinh' => 'nullable|integer|exists:thuoc_tinh_san_pham,id',
            'id_don_vi' => 'nullable|integer|exists:don_vi_san_pham,id',
            'dinh_muc_toi_thieu' => 'nullable|integer|min:0',
            'trang_thai' => 'sometimes|boolean',
            'hinh_anh' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'bien_the' => 'sometimes|array',
            'bien_the.*.ten_bien_the' => 'string|max:255',
            'bien_the.*.ma_vach' => [
                'string',
                Rule::unique('san_pham', 'ma_vach')->ignore($sanPhamId),
            ],
            'bien_the.*.gia_ban_bien' => 'numeric|min:0',
            'bien_the.*.so_luong_bien' => 'integer|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'ten_san_pham.required' => 'Tên sản phẩm không được để trống',
            'ten_san_pham.unique' => 'Tên sản phẩm đã tồn tại',
            'id_danh_muc.required' => 'Danh mục sản phẩm không được để trống',
            'id_danh_muc.exists' => 'Danh mục sản phẩm không hợp lệ',
            'gia_von.required' => 'Giá vốn không được để trống',
            'gia_von.numeric' => 'Giá vốn phải là số',
            'gia_von.min' => 'Giá vốn phải lớn hơn hoặc bằng 0',
            'gia_ban.required' => 'Giá bán không được để trống',
            'gia_ban.numeric' => 'Giá bán phải là số',
            'gia_ban.min' => 'Giá bán phải lớn hơn hoặc bằng 0',
            'gia_ban.gt' => 'Giá bán phải lớn hơn giá vốn',
            'so_luong_ton_kho.required' => 'Số lượng tồn kho không được để trống',
            'so_luong_ton_kho.integer' => 'Số lượng tồn kho phải là số nguyên',
            'hinh_anh.image' => 'File phải là hình ảnh',
            'hinh_anh.mimes' => 'Hình ảnh phải là JPEG, PNG, JPG, GIF hoặc WEBP',
            'hinh_anh.max' => 'Kích thước hình ảnh không được vượt quá 2MB',
            'bien_the.*.ma_vach.unique' => 'Mã vạch biến thể đã tồn tại',
        ];
    }
}
