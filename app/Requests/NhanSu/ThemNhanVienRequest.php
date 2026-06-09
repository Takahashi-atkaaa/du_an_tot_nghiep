<?php

namespace App\Requests\NhanSu;

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
			'mat_khau' => ['required', 'string', 'min:6'],
			'vai_tro' => ['required', 'string', 'max:255'],
			'trang_thai' => ['required', 'boolean'],
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
			'mat_khau.required' => 'Vui lòng nhập mật khẩu.',
			'mat_khau.min' => 'Mật khẩu phải có ít nhất 6 ký tự.',
			'vai_tro.required' => 'Vui lòng chọn vai trò.',
			'trang_thai.required' => 'Vui lòng chọn trạng thái.',
		];
	}

	public function attributes(): array
	{
		return [
			'ho_ten' => 'họ tên',
			'email' => 'email',
			'sdt' => 'số điện thoại',
			'mat_khau' => 'mật khẩu',
			'vai_tro' => 'vai trò',
			'trang_thai' => 'trạng thái',
		];
	}
}
