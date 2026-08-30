<?php

namespace App\Services;

use App\Models\ChiTietKiemKho;
use App\Models\PhieuKiemKho;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class KiemKhoImportService
{
    /**
     * Validate & preview dữ liệu import từ Excel
     * Định dạng Excel: ma_hang | ma_vach | ten_san_pham | so_luong_thuc_te | ly_do
     *
     * @param array $rows Mỗi row là mảng các cột
     * @return array Thông tin preview
     */
    public function preview(array $rows): array
    {
        $preview = [];
        $errors = [];
        $soLuongCapNhat = 0;
        $soLuongKhongTimThay = 0;

        foreach ($rows as $index => $row) {
            $rowNum = $index + 2; // Excel row 1 là header
            $row = array_map('trim', $row);

            // Skip empty rows
            if (empty(array_filter($row))) {
                continue;
            }

            $maHang = $row[0] ?? null;
            $maVach = $row[1] ?? null;
            $tenSanPham = $row[2] ?? null;
            $soLuongThucTe = $row[3] ?? null;
            $lyDo = $row[4] ?? null;

            $item = [
                'row' => $rowNum,
                'ma_hang' => $maHang,
                'ma_vach' => $maVach,
                'ten_san_pham' => $tenSanPham,
                'so_luong_thuc_te' => is_numeric($soLuongThucTe) ? (int) $soLuongThucTe : null,
                'ly_do' => $lyDo,
                'chi_tiet_id' => null,
                'so_luong_he_thong' => null,
                'chenh_lech' => null,
                'matched' => false,
                'error' => null,
            ];

            if ($soLuongThucTe !== null && (int) $soLuongThucTe < 0) {
                $item['error'] = 'Số lượng thực tế phải >= 0';
                $errors[] = "Dòng {$rowNum}: {$item['error']}";
            }

            $preview[] = $item;
        }

        return [
            'items' => $preview,
            'errors' => $errors,
            'total' => count($preview),
            'success' => empty($errors),
        ];
    }

    /**
     * Thực thi cập nhật số lượng thực tế từ Excel đã preview
     */
    public function executeImport(int $phieuId, array $items, KiemKhoService $kiemKhoService, $user = null): array
    {
        $success = 0;
        $errors = [];

        foreach ($items as $item) {
            try {
                if (empty($item['chi_tiet_id'])) {
                    $errors[] = "Dòng {$item['row']}: Không tìm thấy sản phẩm khớp";
                    continue;
                }

                $kiemKhoService->capNhatSoLuongThucTe(
                    $phieuId,
                    (int) $item['chi_tiet_id'],
                    (int) $item['so_luong_thuc_te'],
                    $item['ly_do'] ?? null,
                    $user
                );
                $success++;
            } catch (\Exception $e) {
                $errors[] = "Dòng {$item['row']}: " . $e->getMessage();
            }
        }

        return [
            'success' => $success,
            'errors' => $errors,
            'total' => count($items),
        ];
    }
}