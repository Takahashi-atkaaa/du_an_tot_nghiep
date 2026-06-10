<?php

namespace App\Requests\NhanSu;

use Illuminate\Foundation\Http\FormRequest;

class StoreCaLamViecRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'ten_ca' => ['required', 'string', 'max:255', 'in:Ca sáng,Ca chiều,Ca tối,Ca đêm'],
            'gio_bat_dau' => ['required', 'date_format:H:i'],
            'gio_ket_thuc' => ['required', 'date_format:H:i', 'after:gio_bat_dau'],
            'so_phut_di_lam_tre_toi_da' => ['required', 'integer', 'min:0', 'max:1440'],
        ];
    }

    public function messages(): array
    {
        return [
            'ten_ca.required' => 'Vui lòng nhập tên ca làm việc.',
            'ten_ca.unique' => 'ên ca làm việc này đã tồn tại.',
            'gio_bat_dau.required' => 'Vui lòng chọn giờ bắt đầu.',
            'gio_bat_dau.date_format' => 'Giờ bắt đầu không đúng định dạng.',
            'gio_ket_thuc.required' => 'Vui lòng chọn giờ kết thúc.',
            'gio_ket_thuc.date_format' => 'Giờ kết thúc không đúng định dạng.',
            'gio_ket_thuc.after' => 'Giờ kết thúc phải sau giờ bắt đầu.',
            'so_phut_di_lam_tre_toi_da.required' => 'Vui lòng nhập số phút đi làm trễ tối đa.',
            'so_phut_di_lam_tre_toi_da.integer' => 'Số phút đi làm trễ phải là số nguyên.',
        ];
    }
}
