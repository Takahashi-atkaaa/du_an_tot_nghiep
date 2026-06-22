<?php

namespace App\Requests\KhachHang;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ThemKhachHangRequest extends FormRequest
{
	public function authorize(): bool
	{
		return true;
	}

	public function rules(): array
	{
		return [
			'ten_khach_hang' => [
				'required',
				'string',
				'max:255'
			],

			'so_dien_thoai' => [
			'required',
			'regex:/^(0[3|5|7|8|9])[0-9]{8}$/',
			Rule::unique('khach_hang', 'so_dien_thoai'),
			],
			'email' => [
				'nullable',
				'email',
				'max:255'
			],

			'dia_chi' => [
				'nullable',
				'string',
				'max:255'
			],

			'phi_chu' => [
				'nullable',
				'string',
				'max:1000'
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
			'ten_khach_hang.required' => 'Vui lòng nhập tên khách hàng.',

			'so_dien_thoai.required' => 'Vui lòng nhập số điện thoại.',
			'so_dien_thoai.regex' => 'Số điện thoại không đúng định dạng.',
			'so_dien_thoai.unique' => 'Số điện thoại đã tồn tại trong hệ thống.',

			'email.email' => 'Email không đúng định dạng.',

			'trang_thai.required' => 'Vui lòng chọn trạng thái.',
		];
	}
}