<?php

namespace App\Http\Requests\KiemKho;

use Illuminate\Foundation\Http\FormRequest;

class UpdateKiemKhoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'id_nguoi_kiem' => 'nullable|integer|exists:nguoi_dung,id',
            'ngay_kiem' => 'nullable|date',
            'ghi_chu' => 'nullable|string|max:1000',
        ];
    }

    public function messages(): array
    {
        return [
            'id_nguoi_kiem.exists' => 'Người kiểm không tồn tại.',
            'ngay_kiem.date' => 'Ngày kiểm không hợp lệ.',
            'ghi_chu.max' => 'Ghi chú không được vượt quá 1000 ký tự.',
        ];
    }
}