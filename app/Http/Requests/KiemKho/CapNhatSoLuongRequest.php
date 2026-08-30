<?php

namespace App\Http\Requests\KiemKho;

use Illuminate\Foundation\Http\FormRequest;

class CapNhatSoLuongRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'so_luong_thuc_te' => 'required|integer|min:0',
            'ly_do' => 'nullable|string|max:500',
        ];
    }

    /**
     * Tu dong gan chi_tiet_id tu route param {itemId}
     */
    protected function prepareForValidation(): void
    {
        if ($this->route('itemId')) {
            $this->merge([
                'chi_tiet_id' => $this->route('itemId'),
            ]);
        }
    }

    public function messages(): array
    {
        return [
            'chi_tiet_id.required' => 'Thiếu mã chi tiết kiểm kho.',
            'so_luong_thuc_te.required' => 'Vui lòng nhập số lượng thực tế.',
            'so_luong_thuc_te.integer' => 'Số lượng thực tế phải là số nguyên.',
            'so_luong_thuc_te.min' => 'Số lượng thực tế phải >= 0.',
            'ly_do.max' => 'Lý do không được vượt quá 500 ký tự.',
        ];
    }
}