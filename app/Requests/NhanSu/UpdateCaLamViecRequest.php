<?php

namespace App\Requests\NhanSu;

use App\Models\CaLamViec;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCaLamViecRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $caLamViecId = $this->route('caLamViec')?->id ?? $this->route('caLamViec');

        return [
            'ten_ca' => [
                'required',
                'string',
                'max:255',
                Rule::unique('ca_lam_viec', 'ten_ca')
                    ->ignore($caLamViecId)
                    ->whereNull('deleted_at'),
            ],
            'gio_bat_dau' => ['required', 'date_format:H:i'],
            'gio_ket_thuc' => ['required', 'date_format:H:i'],
            'so_nhan_vien_toi_thieu' => ['required', 'integer', 'min:0'],
            'so_nhan_vien_toi_da' => ['required', 'integer', 'min:0', 'gte:so_nhan_vien_toi_thieu'],
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
            'so_nhan_vien_toi_thieu.required' => 'Vui lòng nhập số nhân viên tối thiểu.',
            'so_nhan_vien_toi_thieu.integer' => 'Số nhân viên tối thiểu phải là số nguyên.',
            'so_nhan_vien_toi_thieu.min' => 'Số nhân viên tối thiểu không được âm.',
            'so_nhan_vien_toi_da.required' => 'Vui lòng nhập số nhân viên tối đa.',
            'so_nhan_vien_toi_da.integer' => 'Số nhân viên tối đa phải là số nguyên.',
            'so_nhan_vien_toi_da.min' => 'Số nhân viên tối đa không được âm.',
            'so_nhan_vien_toi_da.gte' => 'Số nhân viên tối đa phải lớn hơn hoặc bằng số nhân viên tối thiểu.',
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

            $caLamViecId = $this->route('caLamViec')?->id ?? $this->route('caLamViec');

            $isDuplicateTimeRange = CaLamViec::query()
                ->where('gio_bat_dau', $this->input('gio_bat_dau'))
                ->where('gio_ket_thuc', $this->input('gio_ket_thuc'))
                ->where('id', '!=', $caLamViecId)
                ->whereNull('deleted_at')
                ->exists();

            if ($isDuplicateTimeRange) {
                $validator->errors()->add('gio_ket_thuc', 'Khung giờ này đã tồn tại, vui lòng chọn thời gian khác.');
            }
        });
    }
}
