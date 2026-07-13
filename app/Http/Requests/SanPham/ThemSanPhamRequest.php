<?php

namespace App\Http\Requests\SanPham;

use Illuminate\Foundation\Http\FormRequest;

class ThemSanPhamRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Validation rules.
     */

public function rules(): array
{
    return [

        'ten_san_pham' => 'required|string|max:255',

        'gia_ban' => 'required|numeric|min:0',

        'dinh_muc_toi_thieu' => 'required|integer|min:0',

        'mo_ta' => 'nullable|string',

        'hinh_anh' => 'nullable|image|mimes:jpg,jpeg,png,jfif|max:2048',

        'bien_the' => 'nullable|array',

        'bien_the.*.ten_bien_the' => 'nullable|string|max:100',

        'bien_the.*.he_so_quy_doi' => 'nullable|integer|min:1',

        'bien_the.*.trang_thai' => 'nullable|boolean',

    ];
}
public function messages(): array
{
    return [

        'ten_san_pham.required' => 'Tên sản phẩm không được để trống.',

        'id_danh_muc.exists' => 'Danh mục không tồn tại.',

        'gia_ban.numeric' => 'Giá bán phải là số.',

        'dinh_muc_toi_thieu.required' => 'Định mức tối thiểu không được để trống.',

        'hinh_anh.image' => 'File phải là hình ảnh.',

        'hinh_anh.mimes' => 'Ảnh chỉ được có định dạng jpg, jpeg, png hoặc jfif.',

        'bien_the.*.he_so_quy_doi.min' => 'Hệ số quy đổi phải lớn hơn 0.',


    ];
}
}