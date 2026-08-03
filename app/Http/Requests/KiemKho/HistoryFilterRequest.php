<?php

namespace App\Http\Requests\KiemKho;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validate query string cho endpoint danh sách phiếu kiểm kho
 * (GET /admin/api/kiem-kho/history + các filter trang thùng rác).
 *
 * Các field hỗ trợ:
 *  - q            : tìm theo mã phiếu
 *  - trang_thai   : phieu_tam | hoan_thanh | da_huy | null
 *  - thoi_gian    : hom_nay | 7_ngay | thang_nay | thang_truoc
 *  - tu_ngay      : YYYY-MM-DD (khi thoi_gian = tuy_chinh)
 *  - den_ngay     : YYYY-MM-DD
 *  - page, per_page
 */
class HistoryFilterRequest extends FormRequest
{
    public const ALLOWED_TRANG_THAI = ['phieu_tam', 'hoan_thanh', 'da_huy'];
    public const ALLOWED_THOI_GIAN = ['hom_nay', '7_ngay', 'thang_nay', 'thang_truoc', 'tuy_chinh'];

    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'q' => ['nullable', 'string', 'max:100'],
            'trang_thai' => ['nullable', 'string', 'in:' . implode(',', self::ALLOWED_TRANG_THAI)],
            'thoi_gian' => ['nullable', 'string', 'in:' . implode(',', self::ALLOWED_THOI_GIAN)],
            'tu_ngay' => ['nullable', 'date_format:Y-m-d'],
            'den_ngay' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:tu_ngay'],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }

    public function messages(): array
    {
        return [
            'trang_thai.in' => 'Trạng thái không hợp lệ. Chỉ chấp nhận: ' . implode(', ', self::ALLOWED_TRANG_THAI),
            'thoi_gian.in' => 'Khoảng thời gian không hợp lệ.',
            'den_ngay.after_or_equal' => 'Ngày kết thúc phải sau hoặc bằng ngày bắt đầu.',
        ];
    }

    /**
     * Trả về tuple (tu_ngay, den_ngay) đã được xử lý theo thoi_gian.
     *
     * @return array{0:?string,1:?string}
     */
    public function resolvedDateRange(): array
    {
        $tuNgay = $this->input('tu_ngay');
        $denNgay = $this->input('den_ngay');
        $thoiGian = $this->input('thoi_gian');

        if (!$thoiGian) {
            return [$tuNgay, $denNgay];
        }

        $now = now();
        switch ($thoiGian) {
            case 'hom_nay':
                return [$now->copy()->startOfDay()->toDateString(), $now->copy()->endOfDay()->toDateString()];
            case '7_ngay':
                return [$now->copy()->subDays(7)->toDateString(), $now->copy()->endOfDay()->toDateString()];
            case 'thang_nay':
                return [$now->copy()->startOfMonth()->toDateString(), $now->copy()->endOfMonth()->toDateString()];
            case 'thang_truoc':
                return [
                    $now->copy()->subMonthNoOverflow()->startOfMonth()->toDateString(),
                    $now->copy()->subMonthNoOverflow()->endOfMonth()->toDateString(),
                ];
            case 'tuy_chinh':
                return [$tuNgay, $denNgay];
            default:
                return [$tuNgay, $denNgay];
        }
    }
}
