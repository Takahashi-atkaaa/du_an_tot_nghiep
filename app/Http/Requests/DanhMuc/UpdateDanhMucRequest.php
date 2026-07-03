<?php

namespace App\Http\Requests\DanhMuc;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDanhMucRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'ten_danh_muc' => 'required|string|max:255',
            'trang_thai'   => 'required|boolean',
            'mau_sac'      => 'nullable|string|max:20',
            'icon'         => 'nullable|string|max:50',
        ];
    }

    public function messages(): array
    {
        return [
            'ten_danh_muc.required' => 'Tên danh mục không được để trống',
            'ten_danh_muc.max'      => 'Tên danh mục tối đa 255 ký tự',
            'trang_thai.required'   => 'Vui lòng chọn trạng thái',
        ];
    }
}
