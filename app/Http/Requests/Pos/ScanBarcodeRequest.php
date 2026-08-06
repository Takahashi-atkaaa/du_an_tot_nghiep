<?php

namespace App\Http\Requests\Pos;

use Illuminate\Foundation\Http\FormRequest;

class ScanBarcodeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'barcode' => 'required|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'barcode.required' => 'Mã vạch là bắt buộc.',
            'barcode.string' => 'Mã vạch không hợp lệ.',
            'barcode.max' => 'Mã vạch quá dài.',
        ];
    }
}
