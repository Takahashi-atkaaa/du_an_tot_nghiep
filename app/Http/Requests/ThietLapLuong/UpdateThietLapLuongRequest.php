<?php

namespace App\Http\Requests\ThietLapLuong;

use Illuminate\Foundation\Http\FormRequest;

class UpdateThietLapLuongRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id_nguoi_dung' => 'required|exists:nguoi_dung,id',
            'luong_theo_gio' => 'required|numeric|min:0',
            'phu_cap' => 'nullable|numeric|min:0',
            'phat_di_muon' => 'nullable|numeric|min:0',
            'luong_tang_ca' => 'nullable|numeric|min:0',
            'thuong' => 'nullable|numeric|min:0',
            'phat_bo_ca' => 'nullable|numeric|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'id_nguoi_dung.required' => 'Vui lòng chọn nhân viên.',
            'id_nguoi_dung.exists' => 'Nhân viên không tồn tại.',
            'luong_theo_gio.required' => 'Vui lòng nhập lương theo giờ.',
            'luong_theo_gio.numeric' => 'Lương theo giờ phải là số.',
            'luong_theo_gio.min' => 'Lương theo giờ không được nhỏ hơn 0.',
        ];
    }
}
