<?php

namespace App\Http\Requests\BanHang;

use Illuminate\Foundation\Http\FormRequest;

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
        ];
    }
}
