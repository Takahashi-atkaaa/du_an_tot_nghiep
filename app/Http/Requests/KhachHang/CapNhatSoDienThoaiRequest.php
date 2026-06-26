<?php

namespace App\Http\Requests\KhachHang;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CapNhatSoDienThoaiRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
     public function rules(): array
    {
        $khachHang = $this->route('khachHang');

        return [
            'so_dien_thoai' => [
                'required',
                'regex:/^(0[3|5|7|8|9])[0-9]{8}$/',
                Rule::unique('khach_hang', 'so_dien_thoai')
                    ->ignore($khachHang->id),
            ],
        ];
    }

    /**
     * Thông báo lỗi
     */
    public function messages(): array
    {
        return [
            'so_dien_thoai.required' => 'Vui lòng nhập số điện thoại.',
            'so_dien_thoai.regex' => 'Số điện thoại không đúng định dạng Việt Nam.',
            'so_dien_thoai.unique' => 'Số điện thoại đã tồn tại.',
        ];
    }
}
