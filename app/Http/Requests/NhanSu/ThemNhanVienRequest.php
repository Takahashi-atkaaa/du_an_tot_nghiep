<?php

namespace App\Http\Requests\NhanSu;

use Illuminate\Foundation\Http\FormRequest;

class ThemNhanVienRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'ho_ten' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:nguoi_dung,email'],
            'sdt' => ['required', 'string', 'max:20'],

            'gioi_tinh' => [
                'required',
                'in:Nam,Nữ,Khác'
            ],

            'cccd' => [
                'required',
                'digits_between:9,12',
                'unique:nguoi_dung,cccd'
            ],

            'anh_dai_dien' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048'
            ],

            'anh_cccd_mat_truoc' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:4096'
            ],

            'anh_cccd_mat_sau' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:4096'
            ],

            'mat_khau' => [
                'required',
                'string',
                'min:6'
            ],

            'id_vai_tro' => [
                'required',
                'string',
                'max:255'
            ],

            'trang_thai' => [
                'required',
                'boolean'
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'ho_ten.required' => 'Vui lòng nhập họ tên.',
            'email.required' => 'Vui lòng nhập email.',
            'email.email' => 'Email không đúng định dạng.',
            'email.unique' => 'Email này đã tồn tại.',
            'sdt.required' => 'Vui lòng nhập số điện thoại.',
            'gioi_tinh.required' => 'Vui lòng chọn giới tính.',
            'gioi_tinh.in' => 'Giới tính không hợp lệ.',
            'cccd.required' => 'Vui lòng nhập số CCCD.',
            'cccd.unique' => 'Số CCCD đã tồn tại.',
            'cccd.digits_between' => 'CCCD phải từ 9 đến 12 số.',
            'anh_dai_dien.image' => 'Ảnh đại diện phải là hình ảnh.',
            'anh_dai_dien.mimes' => 'Ảnh đại diện chỉ hỗ trợ JPG, PNG, WEBP.',
            'anh_dai_dien.max' => 'Ảnh đại diện tối đa 2MB.',
            'anh_cccd_mat_truoc.image' => 'CCCD mặt trước phải là hình ảnh.',
            'anh_cccd_mat_truoc.mimes' => 'CCCD mặt trước chỉ hỗ trợ JPG, PNG, WEBP.',
            'anh_cccd_mat_truoc.max' => 'CCCD mặt trước tối đa 4MB.',
            'anh_cccd_mat_sau.image' => 'CCCD mặt sau phải là hình ảnh.',
            'anh_cccd_mat_sau.mimes' => 'CCCD mặt sau chỉ hỗ trợ JPG, PNG, WEBP.',
            'anh_cccd_mat_sau.max' => 'CCCD mặt sau tối đa 4MB.',
            'mat_khau.required' => 'Vui lòng nhập mật khẩu.',
            'mat_khau.min' => 'Mật khẩu phải có ít nhất 6 ký tự.',

            'id_vai_tro.required' => 'Vui lòng chọn vai trò.',

            'trang_thai.required' => 'Vui lòng chọn trạng thái.',
        ];
    }

    public function attributes(): array
    {
        return [
            'ho_ten' => 'họ tên',
            'email' => 'email',
            'sdt' => 'số điện thoại',
            'gioi_tinh' => 'giới tính',
            'cccd' => 'CCCD',
            'anh_dai_dien' => 'ảnh đại diện',
            'anh_cccd_mat_truoc' => 'ảnh CCCD mặt trước',
            'anh_cccd_mat_sau' => 'ảnh CCCD mặt sau',
            'mat_khau' => 'mật khẩu',
            'id_vai_tro' => 'vai trò',
            'trang_thai' => 'trạng thái',
        ];
    }
}
