<?php

namespace App\Requests\NhanSu;

use App\Models\ChiaCaLamViec;
use Illuminate\Foundation\Http\FormRequest;

class PhanCongCaLamViecRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id_nguoi_dung' => ['required', 'integer', 'exists:nguoi_dung,id'],
            'id_ca_lam_viec' => ['required', 'integer', 'exists:ca_lam_viec,id'],
            'ngay' => ['required', 'date'],
        ];
    }

    public function messages(): array
    {
        return [
            'id_nguoi_dung.required' => 'Vui lòng chọn nhân viên.',
            'id_nguoi_dung.exists' => 'Nhân viên được chọn không tồn tại.',
            'id_ca_lam_viec.required' => 'Vui lòng chọn ca làm việc.',
            'id_ca_lam_viec.exists' => 'Ca làm việc được chọn không tồn tại.',
            'ngay.required' => 'Vui lòng chọn ngày làm việc.',
            'ngay.date' => 'Ngày làm việc không đúng định dạng.',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $chiaCaId = $this->route('chiaCaLamViec')?->id ?? $this->route('chiaCaLamViec');

            $isDuplicate = ChiaCaLamViec::query()
                ->where('id_nguoi_dung', $this->integer('id_nguoi_dung'))
                ->where('id_ca_lam_viec', $this->integer('id_ca_lam_viec'))
                ->whereDate('ngay', $this->input('ngay'))
                ->when($chiaCaId, fn ($query) => $query->where('id', '!=', $chiaCaId))
                ->whereNull('deleted_at')
                ->exists();

            if ($isDuplicate) {
                $validator->errors()->add('id_ca_lam_viec', 'Nhân viên này đã được phân công ca làm việc này trong ngày đã chọn.');
            }
        });
    }
}
