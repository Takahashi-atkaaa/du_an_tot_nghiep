<?php

namespace App\Requests\PhieuLuong;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePhieuLuongRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'luong_tam_tinh' => 'nullable|numeric|min:0',
            'trang_thai' => 'nullable|string|max:50',
            'tong_gio_lam_thuc_te' => 'nullable|numeric|min:0',
            'tong_luong' => 'nullable|numeric|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'luong_tam_tinh.numeric' => 'Lương tạm tính phải là số.',
            'luong_tam_tinh.min' => 'Lương tạm tính không được nhỏ hơn 0.',
            'tong_gio_lam_thuc_te.numeric' => 'Tổng giờ làm thực tế phải là số.',
            'tong_gio_lam_thuc_te.min' => 'Tổng giờ làm không được nhỏ hơn 0.',
            'tong_luong.numeric' => 'Tổng lương phải là số.',
            'tong_luong.min' => 'Tổng lương không được nhỏ hơn 0.',
        ];
    }
}
