<?php

namespace App\Http\Requests\SanPham;

use Illuminate\Foundation\Http\FormRequest;

class ImportSanPhamRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'excel_file' => [
                'required',
                'file',
                function ($attribute, $value, $fail) {
                    $ext = strtolower($value->getClientOriginalExtension());
                    if (!in_array($ext, ['csv', 'txt'])) {
                        $fail('File phải có định dạng .csv hoặc .txt.');
                    }
                },
                'max:5120',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'excel_file.required' => 'Vui lòng chọn file để import.',
            'excel_file.mimes' => 'File phải có định dạng .csv.',
            'excel_file.max' => 'File vượt quá dung lượng cho phép (tối đa 5MB).',
        ];
    }
}
