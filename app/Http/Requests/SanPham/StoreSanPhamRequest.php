<?php

namespace App\Http\Requests\SanPham;

use Illuminate\Foundation\Http\FormRequest;

class StoreSanPhamRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'ten_san_pham'         => 'required|string|max:255',
            'id_danh_muc'          => 'required|integer|exists:danh_muc_san_pham,id',
            'thuong_hieu'          => 'nullable|string|max:255',
            'gia_von'              => 'nullable|numeric|min:0',
            'ma_vach'              => 'nullable|string|unique:san_pham,ma_vach',
            'mo_ta'                => 'nullable|string',
            'dinh_muc_toi_thieu'   => 'nullable|integer|min:0',
            'trang_thai'           => 'sometimes|boolean',
            'hinh_anh'             => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'don_vi_text'          => 'nullable|string|max:255',
            'bien_the'             => 'sometimes|array',
            'bien_the.*.ten_day_du'   => 'nullable|string|max:255',
            'bien_the.*.gia_ban'      => 'required|numeric|min:0',
            'bien_the.*.ma_vach'      => 'nullable|string',
            'bien_the.*.so_luong'     => 'nullable|integer|min:0',
            'bien_the.*.thuoc_tinh_ids' => 'nullable|string',
            'bien_the.*.hinh_anh'    => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'hang_cung_loai'        => 'sometimes|array',
            'hang_cung_loai.*.ten_don_vi'       => 'required|string|max:255',
            'hang_cung_loai.*.so_luong_quy_doi' => 'required|integer|min:1',
            'hang_cung_loai.*.gia_ban_le'       => 'required|numeric|min:0',
            'hang_cung_loai.*.gia_ban_si'       => 'nullable|numeric|min:0',
            'hang_cung_loai.*.ma_vach'          => 'nullable|string|max:100',
            'hang_cung_loai.*.so_luong'         => 'nullable|integer|min:0',
            'hang_cung_loai.*.hinh_anh'         => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ];
    }

    public function messages(): array
    {
        return [
            'ten_san_pham.required'   => 'Tên sản phẩm không được để trống',
            'id_danh_muc.required'    => 'Danh mục sản phẩm không được để trống',
            'id_danh_muc.exists'      => 'Danh mục sản phẩm không hợp lệ',
            'gia_von.numeric'         => 'Giá vốn phải là số',
            'gia_von.min'             => 'Giá vốn phải lớn hơn hoặc bằng 0',
            'ma_vach.unique'         => 'Mã vạch đã tồn tại',
            'hinh_anh.image'         => 'File phải là hình ảnh',
            'hinh_anh.mimes'         => 'Hình ảnh phải là JPEG, PNG, JPG, GIF hoặc WEBP',
            'hinh_anh.max'           => 'Kích thước hình ảnh không được vượt quá 2MB',
            'bien_the.*.gia_ban.required' => 'Giá bán biến thể không được để trống',
            'bien_the.*.gia_ban.numeric'  => 'Giá bán biến thể phải là số',
            'bien_the.*.gia_ban.min'     => 'Giá bán biến thể phải lớn hơn hoặc bằng 0',
            'hang_cung_loai.*.hinh_anh.image' => 'File ảnh đơn vị phải là hình ảnh',
            'hang_cung_loai.*.hinh_anh.mimes' => 'Ảnh đơn vị phải là JPEG, PNG, JPG, GIF hoặc WEBP',
            'hang_cung_loai.*.hinh_anh.max'  => 'Kích thước ảnh đơn vị không được vượt quá 2MB',
        ];
    }
}
