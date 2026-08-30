<?php

namespace App\Http\Requests\KiemKho;

use Illuminate\Foundation\Http\FormRequest;

class StoreKiemKhoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'id_nguoi_kiem' => 'required|integer|exists:nguoi_dung,id',
            'pham_vi' => 'required|in:toan_bo,theo_danh_muc,chon_san_pham',
            'id_danh_muc' => 'nullable|required_if:pham_vi,theo_danh_muc|integer|exists:danh_muc_san_pham,id',
            'variant_ids' => 'nullable|required_if:pham_vi,chon_san_pham|array',
            'variant_ids.*' => 'integer|exists:bien_the_san_pham,id',
            'ngay_kiem' => 'nullable|date',
            'ghi_chu' => 'nullable|string|max:1000',
        ];
    }

    public function messages(): array
    {
        return [
            'id_nguoi_kiem.required' => 'Vui lòng chọn người kiểm.',
            'id_nguoi_kiem.exists' => 'Người kiểm không tồn tại.',
            'pham_vi.required' => 'Vui lòng chọn phạm vi kiểm.',
            'pham_vi.in' => 'Phạm vi không hợp lệ.',
            'id_danh_muc.required_if' => 'Vui lòng chọn danh mục.',
            'variant_ids.required_if' => 'Vui lòng chọn ít nhất một sản phẩm.',
            'ngay_kiem.date' => 'Ngày kiểm không hợp lệ.',
            'ghi_chu.max' => 'Ghi chú không được vượt quá 1000 ký tự.',
        ];
    }
}