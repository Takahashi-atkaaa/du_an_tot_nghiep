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
            'ten_ca' => ['required', 'string', 'max:255', 'unique:ca_lam_viec,ten_ca'],
            'gio_bat_dau' => ['required', 'date_format:H:i'],
            'gio_ket_thuc' => ['required', 'date_format:H:i', 'after:gio_bat_dau'],
            'so_phut_di_lam_tre_toi_da' => ['required', 'integer', 'min:0', 'max:1440'],
        ];
    }

    public function messages(): array
    {
        return [
            'ten_ca.required' => 'Vui long nhap ten ca lam viec.',
            'ten_ca.unique' => 'Ten ca lam viec nay da ton tai.',
            'gio_bat_dau.required' => 'Vui long chon gio bat dau.',
            'gio_bat_dau.date_format' => 'Gio bat dau khong dung dinh dang.',
            'gio_ket_thuc.required' => 'Vui long chon gio ket thuc.',
            'gio_ket_thuc.date_format' => 'Gio ket thuc khong dung dinh dang.',
            'gio_ket_thuc.after' => 'Gio ket thuc phai sau gio bat dau.',
            'so_phut_di_lam_tre_toi_da.required' => 'Vui long nhap so phut di lam tre toi da.',
            'so_phut_di_lam_tre_toi_da.integer' => 'So phut di lam tre phai la so nguyen.',
        ];
    }
}
