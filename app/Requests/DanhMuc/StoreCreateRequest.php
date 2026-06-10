<?php

namespace App\Requests\DanhMuc;

use Illuminate\Foundation\Http\FormRequest;

class StoreCreateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // hoặc check quyền nếu có
    }

    public function rules(): array
    {
        return [
            'ten_danh_muc' => 'required|string|max:255',
            'trang_thai'   => 'required|boolean',
            'mau_sac'      => 'nullable|string|max:255',
            'icon'         => 'nullable|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'ten_danh_muc.required' => 'Tên danh mục không được để trống',
            'trang_thai.required'   => 'Trạng thái không được để trống',
            'trang_thai.boolean'    => 'Trạng thái phải là true hoặc false',
        ];
    }
}