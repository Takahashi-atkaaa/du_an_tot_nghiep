<?php

namespace App\Http\Requests\KiemKho;

use Illuminate\Foundation\Http\FormRequest;

class HistoryFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'ma_phieu' => 'nullable|string|max:50',
            'trang_thai' => 'nullable|in:phieu_tam,counting,cho_duyet,da_duyet,hoan_thanh,tu_choi,da_huy',
            'tu_ngay' => 'nullable|date',
            'den_ngay' => 'nullable|date|after_or_equal:tu_ngay',
            'id_nguoi_kiem' => 'nullable|integer',
            'per_page' => 'nullable|integer|min:1|max:100',
        ];
    }

    public function messages(): array
    {
        return [
            'den_ngay.after_or_equal' => 'Ngày đến phải >= ngày từ.',
            'per_page.max' => 'Số bản ghi / trang tối đa là 100.',
        ];
    }
}