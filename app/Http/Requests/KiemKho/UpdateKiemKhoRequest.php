<?php

namespace App\Http\Requests\KiemKho;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validate payload CẬP NHẬT phiếu kiểm kho đã tồn tại.
 * - Nếu phiếu đang 'hoan_thanh' hoặc 'da_huy' thì không cho sửa.
 * - Quy tắc items tương tự StoreKiemKhoRequest.
 *
 * Phía controller cần check thêm trạng thái phiếu (chỉ 'phieu_tam' được sửa).
 */
class UpdateKiemKhoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        $phieuId = (int) $this->route('id');

        return [
            'ghi_chu' => ['nullable', 'string', 'max:2000'],

            'items' => ['required', 'array', 'min:1'],

            'items.*.id_chi_tiet_lo_hang' => [
                'required',
                'integer',
                Rule::exists('chi_tiet_lo_hang', 'id'),
            ],
            'items.*.variant_id' => [
                'required',
                'integer',
                Rule::exists('bien_the_san_pham', 'id'),
            ],
            'items.*.ma_vach' => [
                'nullable',
                'string',
                'max:100',
                Rule::exists('bien_the_san_pham', 'ma_vach'),
            ],
            'items.*.ten_san_pham' => ['nullable', 'string', 'max:255'],
            'items.*.ten_bien_the' => ['nullable', 'string', 'max:255'],
            'items.*.ten_don_vi' => ['nullable', 'string', 'max:100'],
            'items.*.han_su_dung' => ['nullable', 'date'],
            'items.*.ma_lo' => ['nullable', 'string', 'max:100'],
            'items.*.so_luong_ton' => ['required', 'integer', 'min:0'],
            'items.*.so_luong_thuc_te' => ['nullable', 'integer', 'min:0', 'max:9999999'],
            'items.*.gia_von' => ['nullable', 'numeric', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'items.required' => 'Phiếu kiểm kho phải có ít nhất 1 dòng chi tiết.',
            'items.min' => 'Phiếu kiểm kho phải có ít nhất 1 dòng chi tiết.',
            'items.*.id_chi_tiet_lo_hang.exists' => 'Lô hàng dòng :position không tồn tại.',
            'items.*.variant_id.exists' => 'Biến thể dòng :position không tồn tại.',
            'items.*.ma_vach.exists' => 'Mã vạch dòng :position không tồn tại.',
            'items.*.so_luong_thuc_te.min' => 'Số lượng thực tế dòng :position không được âm.',
        ];
    }

    public function items(): array
    {
        return collect($this->input('items', []))
            ->map(fn ($it) => [
                'id_chi_tiet_lo_hang' => (int) ($it['id_chi_tiet_lo_hang'] ?? 0),
                'variant_id' => (int) ($it['variant_id'] ?? 0),
                'ma_vach' => $it['ma_vach'] ?? null,
                'ten_san_pham' => $it['ten_san_pham'] ?? null,
                'ten_bien_the' => $it['ten_bien_the'] ?? null,
                'ten_don_vi' => $it['ten_don_vi'] ?? null,
                'han_su_dung' => $it['han_su_dung'] ?? null,
                'ma_lo' => $it['ma_lo'] ?? null,
                'so_luong_ton' => (int) ($it['so_luong_ton'] ?? 0),
                'so_luong_thuc_te' => isset($it['so_luong_thuc_te']) && $it['so_luong_thuc_te'] !== null
                    ? (int) $it['so_luong_thuc_te']
                    : null,
                'gia_von' => (float) ($it['gia_von'] ?? 0),
            ])
            ->values()
            ->all();
    }
}
