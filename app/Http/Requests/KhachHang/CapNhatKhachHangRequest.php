<?php

namespace App\Http\Requests\KhachHang;

use Illuminate\Foundation\Http\FormRequest;

class CapNhatKhachHangRequest extends FormRequest
{
	public function authorize(): bool
	{
		return true;
	}

	public function rules(): array
	{
		return [
			'ten_khach_hang' => ['required', 'string', 'max:255'],
			'so_dien_thoai' => ['required', 'string', 'max:20'],
			'email' => ['nullable', 'email', 'max:255'],
			'dia_chi' => ['nullable', 'string', 'max:255'],
			'cong_no' => ['required', 'numeric', 'min:0'],
			'phi_chu' => ['nullable', 'string', 'max:1000'],
			'trang_thai' => ['required', 'boolean'],
		];
	}

	public function messages(): array
	{
		return [
			'ten_khach_hang.required' => 'Vui lòng nhập họ và tên.',
			'so_dien_thoai.required' => 'Vui lòng nhập số điện thoại.',
			'email.email' => 'Email không đúng định dạng.',
			'cong_no.required' => 'Vui lòng nhập công nợ.',
			'cong_no.min' => 'Công nợ không được nhỏ hơn 0.',
			'trang_thai.required' => 'Vui lòng chọn trạng thái.',
		];
	}
}
