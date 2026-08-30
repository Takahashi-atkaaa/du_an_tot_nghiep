<?php

namespace App\Http\Requests\KiemKho;

use Illuminate\Foundation\Http\FormRequest;

class TuChoiPhieuRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'ly_do' => 'required|string|min:10|max:1000',
        ];
    }

    public function messages(): array
    {
        return [
            'ly_do.required' => 'Vui lòng nhập lý do từ chối.',
            'ly_do.min' => 'Lý do từ chối phải có ít nhất 10 ký tự.',
            'ly_do.max' => 'Lý do từ chối không được vượt quá 1000 ký tự.',
        ];
    }
}