<?php

namespace App\Http\Requests\BangLuong;

use Illuminate\Foundation\Http\FormRequest;

class StoreBangLuongRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'ten_bang_luong' => 'required|string|max:255',
            'ngay_bat_dau' => 'required|date',
            'ngay_ket_thuc' => 'required|date|after_or_equal:ngay_bat_dau',
        ];
    }

    public function messages(): array
    {
        return [
            'ten_bang_luong.required' => 'Vui lòng nhập tên bảng lương.',
            'ten_bang_luong.max' => 'Tên bảng lương không được vượt quá 255 ký tự.',
            'ngay_bat_dau.required' => 'Vui lòng chọn ngày bắt đầu.',
            'ngay_bat_dau.date' => 'Ngày bắt đầu không hợp lệ.',
            'ngay_ket_thuc.required' => 'Vui lòng chọn ngày kết thúc.',
            'ngay_ket_thuc.date' => 'Ngày kết thúc không hợp lệ.',
            'ngay_ket_thuc.after_or_equal' => 'Ngày kết thúc phải lớn hơn hoặc bằng ngày bắt đầu.',
        ];
    }
}
