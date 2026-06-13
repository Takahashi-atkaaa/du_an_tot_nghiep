<?php

namespace App\Requests\NhanSu;

use App\Models\CaLamViec;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCaLamViecRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'ten_ca' => [
                'required',
                'string',
                'max:255',
                Rule::unique('ca_lam_viec', 'ten_ca')->whereNull('deleted_at'),
            ],
            'gio_bat_dau' => ['required', 'date_format:H:i'],
            'gio_ket_thuc' => ['required', 'date_format:H:i'],
            'so_phut_di_lam_tre_toi_da' => ['required', 'integer', 'min:0', 'max:1440'],
        ];
    }

    public function messages(): array
    {
        return [
            'ten_ca.required' => 'Vui lòng nhập tên ca làm việc.',
            'ten_ca.unique' => 'Tên ca làm việc này đã tồn tại.',
            'gio_bat_dau.required' => 'Vui lòng chọn giờ bắt đầu.',
            'gio_bat_dau.date_format' => 'Giờ bắt đầu không đúng định dạng.',
            'gio_ket_thuc.required' => 'Vui lòng chọn giờ kết thúc.',
            'gio_ket_thuc.date_format' => 'Giờ kết thúc không đúng định dạng.',
            'so_phut_di_lam_tre_toi_da.required' => 'Vui lòng nhập số phút đi làm trễ tối đa.',
            'so_phut_di_lam_tre_toi_da.integer' => 'Số phút đi làm trễ phải là số nguyên.',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($validator->errors()->has('gio_bat_dau') || $validator->errors()->has('gio_ket_thuc')) {
                return;
            }

            if ($this->input('gio_bat_dau') === $this->input('gio_ket_thuc')) {
                $validator->errors()->add('gio_ket_thuc', 'Giờ kết thúc không được trùng với giờ bắt đầu.');
                return;
            }

            $isDuplicateTimeRange = CaLamViec::query()
                ->where('gio_bat_dau', $this->input('gio_bat_dau'))
                ->where('gio_ket_thuc', $this->input('gio_ket_thuc'))
                ->whereNull('deleted_at')
                ->exists();

            if ($isDuplicateTimeRange) {
                $validator->errors()->add('gio_ket_thuc', 'Khung giờ này đã tồn tại, vui lòng chọn thời gian khác.');
            }
        });
    }
}
