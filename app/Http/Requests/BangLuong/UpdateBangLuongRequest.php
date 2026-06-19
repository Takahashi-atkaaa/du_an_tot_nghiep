<?php

namespace App\Requests\BangLuong;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBangLuongRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'ten_bang_luong' => 'required|string|max:255',
            'ky_lam_viec' => 'required|string|max:50',
            'trang_thai' => 'required|string|max:50',
        ];
    }

    public function messages(): array
    {
        return [
            'ten_bang_luong.required' => 'Vui lòng nhập tên bảng lương.',
            'ten_bang_luong.max' => 'Tên bảng lương không được vượt quá 255 ký tự.',
            'ky_lam_viec.required' => 'Vui lòng nhập kỳ làm việc.',
            'ky_lam_viec.max' => 'Kỳ làm việc không được vượt quá 50 ký tự.',
            'trang_thai.required' => 'Vui lòng chọn trạng thái.',
        ];
    }
}
