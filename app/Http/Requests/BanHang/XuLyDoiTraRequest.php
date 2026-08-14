<?php

namespace App\Http\Requests\BanHang;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class XuLyDoiTraRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'request_token' => ['required', 'string', 'max:120'],
            'id_nguoi_dung' => [
                'required',
                'integer',
                Rule::exists('nguoi_dung', 'id')->where(function ($query) {
                    $query->where('trang_thai', 1)
                        ->whereNull('deleted_at');
                }),
            ],
            'ly_do' => ['nullable', 'string', 'max:1000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.id_chi_tiet_hoa_don' => ['required', 'integer'],
            'items.*.action' => ['nullable', 'in:none,return,exchange'],
            'items.*.so_luong' => ['nullable', 'integer', 'min:1'],
            'items.*.hang_loi' => ['nullable', 'boolean'],
            'items.*.id_bien_the_thay_the' => ['nullable', 'integer'],
        ];
    }

    public function messages(): array
    {
        return [
            'id_nguoi_dung.required' => 'Vui lòng chọn người bán.',
            'id_nguoi_dung.exists' => 'Người bán được chọn không tồn tại hoặc đã ngừng hoạt động.',
        ];
    }
}
