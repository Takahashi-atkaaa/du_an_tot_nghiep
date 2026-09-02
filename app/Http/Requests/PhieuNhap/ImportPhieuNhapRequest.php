<?php

namespace App\Http\Requests\PhieuNhap;

use Illuminate\Foundation\Http\FormRequest;
use Maatwebsite\Excel\Facades\Excel;

class ImportPhieuNhapRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'file' => [
                'required',
                'file',
                'mimes:csv,xlsx,xls',
                'max:5120', // 5MB
                function ($attribute, $value, $fail) {
                    try {
                        $sheets = Excel::toArray(new \stdClass, $value);
                        if (empty($sheets) || empty($sheets[0])) {
                            $fail('File Excel không có dữ liệu.');
                            return;
                        }
                        
                        // Bỏ qua header row
                        $dataRows = array_slice($sheets[0], 1);
                        
                        if (count($dataRows) > 1000) {
                            $fail('File tối đa 1000 dòng dữ liệu (không tính header).');
                        }
                        
                        if (count($dataRows) === 0) {
                            $fail('File không có dòng dữ liệu nào.');
                        }
                    } catch (\Exception $e) {
                        $fail('Không thể đọc file Excel: ' . $e->getMessage());
                    }
                },
            ],
            'id_nha_cung_cap' => 'nullable|integer|exists:nha_cung_cap,id',
            'loai_nhap' => 'required|in:mua_hang,tra_lai_tu_khach',
            'ghi_chu' => 'nullable|string|max:1000',
        ];
    }

    public function messages(): array
    {
        return [
            'file.required' => 'Vui lòng chọn file Excel để import.',
            'file.file' => 'File không hợp lệ.',
            'file.mimes' => 'File phải có định dạng: csv, xlsx, xls.',
            'file.max' => 'File không được vượt quá 5MB.',
            'loai_nhap.required' => 'Vui lòng chọn loại nhập.',
            'loai_nhap.in' => 'Loại nhập không hợp lệ.',
            'id_nha_cung_cap.exists' => 'Nhà cung cấp không tồn tại trong hệ thống.',
        ];
    }
}
