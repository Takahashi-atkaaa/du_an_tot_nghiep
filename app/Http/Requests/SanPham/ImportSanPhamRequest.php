<?php

namespace App\Http\Requests\SanPham;

use Illuminate\Foundation\Http\FormRequest;
use Maatwebsite\Excel\Facades\Excel;

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
                    if (! in_array($ext, ['csv', 'txt', 'xlsx', 'xls'])) {
                        $fail('File phải có định dạng .csv, .xlsx hoặc .xls.');
                    }
                },
                'mimes:csv,txt,xlsx,xls',
                'max:5120',
                function ($attribute, $value, $fail) {
                    try {
                        $sheets = Excel::toArray(new \stdClass, $value);
                        $rows = $sheets[0] ?? [];
                        $dataRows = array_filter(array_slice($rows, 1), static function ($row): bool {
                            if (! is_array($row)) {
                                return false;
                            }

                            return ! empty(array_filter($row, static fn ($cell) => trim((string) $cell) !== ''));
                        });

                        if (count($dataRows) > 1000) {
                            $fail('File chỉ được phép có tối đa 1000 dòng dữ liệu.');
                        }
                    } catch (\Throwable) {
                        // Import controller sẽ trả lỗi đọc file chi tiết hơn.
                    }
                },
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'excel_file.required' => 'Vui lòng chọn file để import.',
            'excel_file.mimes' => 'File phải có định dạng .csv, .xlsx hoặc .xls.',
            'excel_file.max' => 'File vượt quá dung lượng cho phép (tối đa 5MB).',
        ];
    }
}
