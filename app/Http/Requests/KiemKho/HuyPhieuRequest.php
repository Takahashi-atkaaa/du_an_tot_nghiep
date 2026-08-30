<?php

namespace App\Http\Requests\KiemKho;

use Illuminate\Foundation\Http\FormRequest;

class HuyPhieuRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'ly_do' => 'required|string|min:5|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'ly_do.required' => 'Vui lòng nhập lý do hủy.',
            'ly_do.min' => 'Lý do hủy phải có ít nhất 5 ký tự.',
            'ly_do.max' => 'Lý do hủy không được vượt quá 500 ký tự.',
        ];
    }
}