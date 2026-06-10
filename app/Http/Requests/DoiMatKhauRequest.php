<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class DoiMatKhauRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $user = $this->user();

            if (!$user || !Hash::check($this->mat_khau_cu, $user->mat_khau)) {
                $validator->errors()->add('mat_khau_cu', 'Mật khẩu cũ không đúng');
            }
        });
    }

    public function rules(): array
    {
        return [
            'mat_khau_cu'           => 'required|string',
            'mat_khau_moi'          => 'required|string|min:6',
            'xac_nhan_mat_khau_moi' => 'required|string|min:6|same:mat_khau_moi',
        ];
    }

    public function messages(): array
    {
        return [
            'mat_khau_cu.required'           => 'Vui lòng nhập mật khẩu cũ',
            'mat_khau_moi.required'          => 'Vui lòng nhập mật khẩu mới',
            'mat_khau_moi.min'               => 'Mật khẩu mới phải có ít nhất 6 ký tự',
            'xac_nhan_mat_khau_moi.required' => 'Vui lòng xác nhận mật khẩu mới',
            'xac_nhan_mat_khau_moi.min'      => 'Xác nhận mật khẩu phải có ít nhất 6 ký tự',
            'xac_nhan_mat_khau_moi.same'     => 'Xác nhận mật khẩu không khớp với mật khẩu mới',
        ];
    }
}
