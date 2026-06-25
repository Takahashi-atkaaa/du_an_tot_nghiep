<?php

namespace App\Http\Requests\KhachHang;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class NhanvienThemKhachHangRequest extends FormRequest
{

    /**

     * Determine if the user is authorized to make this request.

     */

    public function authorize(): bool

    {

        return true;

    }



    /**

     * Get the validation rules that apply to the request.

     *

     * @return array<string, ValidationRule|array<mixed>|string>

     */

     public function rules(): array

    {

        return [

            'ten_khach_hang' => [

                'required',

                'string',

                'min:2',

                'max:100',

            ],



            'so_dien_thoai' => [

                'required',

                'regex:/^(0[3|5|7|8|9])[0-9]{8}$/',

                'unique:khach_hang,so_dien_thoai',

            ],



            'email' => [

                'nullable',

                'email',

                'max:255',

                'unique:khach_hang,email',

            ],



            'dia_chi' => [

                'nullable',

                'string',

                'max:255',

            ],

        ];

    }



    /**

     * Thông báo lỗi

     */

    public function messages(): array

    {

        return [

            'ten_khach_hang.required' => 'Vui lòng nhập tên khách hàng.',

            'ten_khach_hang.min' => 'Tên khách hàng phải từ 2 ký tự trở lên.',

            'ten_khach_hang.max' => 'Tên khách hàng không được quá 100 ký tự.',



            'so_dien_thoai.required' => 'Vui lòng nhập số điện thoại.',

            'so_dien_thoai.regex' => 'Số điện thoại không đúng định dạng Việt Nam.',

            'so_dien_thoai.unique' => 'Số điện thoại đã tồn tại.',



            'email.email' => 'Email không đúng định dạng.',

            'email.unique' => 'Email đã tồn tại.',



            'dia_chi.max' => 'Địa chỉ không được quá 255 ký tự.',

        ];

    }

}
