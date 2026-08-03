<?php

namespace App\Http\Requests\KiemKho;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validate payload import Excel / CSV vào phiếu kiểm kho.
 * Hỗ trợ 2 dạng:
 *  - 'items': mảng các dòng đã parse sẵn (kèm ma_vach hoặc ma_hang + so_luong_thuc_te)
 *  - 'file':   upload file xlsx/csv trực tiếp (lưu trong tmp)
 *
 * Mỗi item là một dòng phiếu. Backend sẽ tự map ma_vach/ma_hang -> variant_id + id_chi_tiet_lo_hang.
 */
class ImportKiemKhoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            // Một trong 2 phải có
            'items' => ['nullable', 'array', 'min:1', 'max:1000'],
            'file' => ['nullable', 'file', 'mimes:xlsx,xls,csv', 'max:5120'],

            'items.*.ma_vach' => ['nullable', 'string', 'max:100', 'required_without:items.*.ma_hang'],
            'items.*.ma_hang' => ['nullable', 'string', 'max:100', 'required_without:items.*.ma_vach'],
            'items.*.so_luong_thuc_te' => ['nullable', 'integer', 'min:0', 'max:9999999'],
            'items.*.ten_san_pham' => ['nullable', 'string', 'max:255'],
            'items.*.ghi_chu' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'file.mimes' => 'Chỉ chấp nhận file .xlsx, .xls hoặc .csv.',
            'file.max' => 'File tối đa 5MB.',
            'items.max' => 'Tối đa 1000 dòng cho mỗi lần import.',
            'items.*.ma_vach.required_without' => 'Phải có mã vạch hoặc mã hàng.',
            'items.*.ma_hang.required_without' => 'Phải có mã hàng hoặc mã vạch.',
            'items.*.so_luong_thuc_te.min' => 'Số lượng thực tế không được âm.',
        ];
    }

    /**
     * Chuẩn hoá từng dòng: loại bỏ dòng rỗng, trim mã.
     */
    public function normalizedItems(): array
    {
        $items = $this->input('items', []);
        if (!is_array($items)) {
            return [];
        }
        $result = [];
        foreach ($items as $i => $it) {
            $maVach = isset($it['ma_vach']) ? trim((string) $it['ma_vach']) : '';
            $maHang = isset($it['ma_hang']) ? trim((string) $it['ma_hang']) : '';
            if ($maVach === '' && $maHang === '') {
                continue; // bỏ dòng không có mã
            }
            $result[] = [
                'ma_vach' => $maVach !== '' ? $maVach : null,
                'ma_hang' => $maHang !== '' ? $maHang : null,
                'so_luong_thuc_te' => isset($it['so_luong_thuc_te']) && $it['so_luong_thuc_te'] !== ''
                    ? (int) $it['so_luong_thuc_te']
                    : null,
                'ten_san_pham' => isset($it['ten_san_pham']) ? trim((string) $it['ten_san_pham']) : null,
                'ghi_chu' => isset($it['ghi_chu']) ? trim((string) $it['ghi_chu']) : null,
                '_row' => $i + 1,
            ];
        }
        return $result;
    }
}
