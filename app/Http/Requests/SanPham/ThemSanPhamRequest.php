<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSanPhamRequest extends FormRequest
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
        'id_danh_muc' => 'required|exists:danh_muc,id',
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

            'id_danh_muc.required' => 'Vui lòng chọn danh mục.',

            'gia_ban.required' => 'Giá bán không được để trống.',

            'bien_the.*.ten_bien_the.required_with'
                => 'Tên biến thể không được để trống.',

            'bien_the.*.he_so_quy_doi.required_with'
                => 'Hệ số quy đổi không được để trống.',

        ];
    }
}