<?php

namespace App\Imports;

use App\Models\BienTheSanPham;
use App\Models\ChiTietLoHang;
use App\Models\ChiTietPhieu;
use App\Models\DonViQuyDoi;
use App\Models\LoHang;
use App\Models\Phieu;
use App\Models\PhieuNhap;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class PhieuNhapImport implements ToCollection, WithHeadingRow, WithValidation
{
    protected int $idNhaCungCap;
    protected string $loaiNhap;
    protected ?string $ghiChu;
    protected int $idNguoiDung;
    protected array $errors = [];
    protected int $rowCount = 0;

    public function __construct(
        int $idNhaCungCap = null,
        string $loaiNhap = 'mua_hang',
        ?string $ghiChu = null,
        int $idNguoiDung = null
    ) {
        $this->idNhaCungCap = $idNhaCungCap ?? 0;
        $this->loaiNhap = $loaiNhap;
        $this->ghiChu = $ghiChu;
        $this->idNguoiDung = $idNguoiDung ?? auth()->id() ?? 0;
    }

    public function prepareForValidation($data, $index)
    {
        $hanSuDung = $data['han_su_dung'] ?? null;

        if (empty($hanSuDung)) {
            $data['han_su_dung'] = '2099-12-31';
        } elseif (is_numeric($hanSuDung)) {
            try {
                $date = ExcelDate::excelToDateTimeObject($hanSuDung);
                $data['han_su_dung'] = $date->format('Y-m-d');
            } catch (\Exception $e) {
                // Keep original if conversion fails
            }
        }

        return $data;
    }

    public function rules(): array
    {
        return [
            'han_su_dung' => 'required|date_format:Y-m-d',
        ];
    }

    public function collection(Collection $rows)
    {
        $this->errors = [];
        $this->rowCount = 0;

        $rows = $rows->filter(fn($row) => !empty($row['ma_vach']) && trim((string)$row['ma_vach']) !== '');

        if ($rows->isEmpty()) {
            return;
        }

        $chiTietData = [];
        $allVariantIds = [];

        foreach ($rows as $idx => $row) {
            $lineNumber = $idx + 2;
            $this->rowCount++;

            $maVach = trim((string)($row['ma_vach'] ?? ''));
            $soLuong = (int)($row['so_luong'] ?? 0);
            $giaNhap = (float)($row['gia_nhap'] ?? 0);
            $hanSuDung = trim((string)($row['han_su_dung'] ?? ''));

            if ($maVach === '') {
                $this->errors[] = "Dong {$lineNumber}: Thieu Ma_vach.";
                continue;
            }

            if ($soLuong <= 0) {
                $this->errors[] = "Dong {$lineNumber}: So luong phai lon hon 0.";
                continue;
            }

            if ($giaNhap < 0) {
                $this->errors[] = "Dong {$lineNumber}: Gia nhap khong duoc am.";
                continue;
            }

            if ($hanSuDung === '') {
                $this->errors[] = "Dong {$lineNumber}: Thieu Han_su_dung.";
                continue;
            }

            try {
                $date = \Carbon\Carbon::createFromFormat('Y-m-d', $hanSuDung);
                $hanSuDungFormatted = $date->format('Y-m-d');
            } catch (\Exception $e) {
                try {
                    $date = \Carbon\Carbon::createFromFormat('d/m/Y', $hanSuDung);
                    $hanSuDungFormatted = $date->format('Y-m-d');
                } catch (\Exception $e2) {
                    $this->errors[] = "Dong {$lineNumber}: Dinh dang Han_su_dung khong hop le (VD: 2025-12-31).";
                    continue;
                }
            }

            $key = $maVach . '|' . $hanSuDungFormatted;

            if (!isset($chiTietData[$key])) {
                $chiTietData[$key] = [
                    'line_number' => $lineNumber,
                    'ma_vach' => $maVach,
                    'so_luong' => $soLuong,
                    'gia_nhap' => $giaNhap,
                    'han_su_dung' => $hanSuDungFormatted,
                    'line_numbers' => [$lineNumber],
                ];
            } else {
                $chiTietData[$key]['so_luong'] += $soLuong;
                $chiTietData[$key]['gia_nhap'] = (($chiTietData[$key]['gia_nhap'] * ($chiTietData[$key]['so_luong'] - $soLuong)) + ($giaNhap * $soLuong)) / $chiTietData[$key]['so_luong'];
                $chiTietData[$key]['line_numbers'][] = $lineNumber;
                $chiTietData[$key]['line_number'] = $chiTietData[$key]['line_numbers'][0];
            }
        }

        if (!empty($this->errors)) {
            throw new \Exception(implode(' | ', $this->errors));
        }

        $validatedData = [];
        foreach ($chiTietData as $key => $ct) {
            $variant = $this->findVariant($ct['ma_vach']);
            if (!$variant) {
                $this->errors[] = "Dong {$ct['line_number']}: Ma vach '{$ct['ma_vach']}' khong ton tai trong he thong.";
                continue;
            }

            $allVariantIds[$variant->id] = true;
            $validatedData[] = [
                'line_number' => $ct['line_number'],
                'variant_id' => $variant->id,
                'product_id' => $variant->product_id,
                'so_luong' => $ct['so_luong'],
                'ty_le_quy_doi' => $variant->ty_le_quy_doi ?? 1,
                'gia_nhap' => $ct['gia_nhap'],
                'han_su_dung' => $ct['han_su_dung'],
            ];
        }

        if (!empty($this->errors)) {
            throw new \Exception(implode(' | ', $this->errors));
        }

        $loaiPhieuEnum = $this->loaiNhap === 'mua_hang' ? 'nhap_mua_hang' : 'nhap_tra_lai_tu_khach';
        $loaiPhieuLabel = $this->loaiNhap === 'mua_hang' ? 'Nhập hàng' : 'Trả hàng từ khách';

        DB::transaction(function () use ($validatedData, $loaiPhieuEnum, $loaiPhieuLabel) {
            $phieu = Phieu::create([
                'loai_phieu' => $loaiPhieuLabel,
                'loai_phieu_enum' => $loaiPhieuEnum,
                'id_nguoi_dung' => $this->idNguoiDung,
                'id_nha_cung_cap' => $this->idNhaCungCap ?: null,
                'ghi_chu' => $this->ghiChu,
            ]);

            PhieuNhap::create([
                'id_phieu' => $phieu->id,
                'loai_nhap' => $this->loaiNhap,
                'ghi_chu' => $this->ghiChu,
            ]);

            $loHang = LoHang::create([
                'id_phieu' => $phieu->id,
                'id_nha_cung_cap' => $this->idNhaCungCap ?: null,
                'ngay_nhap' => now()->toDateString(),
                'ghi_chu' => 'Tạo từ import Excel',
            ]);

            foreach ($validatedData as $ct) {
                $soLuongGoc = $ct['so_luong'] * $ct['ty_le_quy_doi'];
                $giaNhapGoc = $ct['gia_nhap'] / $ct['ty_le_quy_doi'];

                // Tìm chi_tiet_lo_hang đã tồn tại
                $chiTietLoHang = ChiTietLoHang::where('id_lo_hang', $loHang->id)
                    ->where('variant_id', $ct['variant_id'])
                    ->where('han_su_dung', $ct['han_su_dung'])
                    ->first();

                if ($chiTietLoHang) {
                    // UPSERT: Cộng dồn số lượng
                    $oldQuantity = $chiTietLoHang->so_luong_nhap;
                    $newQuantity = $oldQuantity + $soLuongGoc;
                    $newPrice = (($chiTietLoHang->gia_nhap * $oldQuantity) + ($giaNhapGoc * $soLuongGoc)) / $newQuantity;

                    $chiTietLoHang->update([
                        'so_luong_nhap' => $newQuantity,
                        'so_luong_ton' => $chiTietLoHang->so_luong_ton + $soLuongGoc,
                        'gia_nhap' => $newPrice,
                    ]);
                } else {
                    // INSERT: Tạo mới
                    $chiTietLoHang = ChiTietLoHang::create([
                        'id_lo_hang' => $loHang->id,
                        'id_san_pham' => $ct['product_id'],
                        'variant_id' => $ct['variant_id'],
                        'so_luong_nhap' => $soLuongGoc,
                        'so_luong_ton' => $soLuongGoc,
                        'gia_nhap' => $giaNhapGoc,
                        'han_su_dung' => $ct['han_su_dung'],
                    ]);
                }

                // Tương tự cho chi_tiet_phieu
                $chiTietPhieu = ChiTietPhieu::where('id_phieu', $phieu->id)
                    ->where('variant_id', $ct['variant_id'])
                    ->where('han_su_dung', $ct['han_su_dung'])
                    ->first();

                if ($chiTietPhieu) {
                    $chiTietPhieu->update([
                        'so_luong' => $chiTietPhieu->so_luong + $soLuongGoc,
                        'so_luong_con_lai' => $chiTietPhieu->so_luong_con_lai + $soLuongGoc,
                    ]);
                } else {
                    ChiTietPhieu::create([
                        'id_phieu' => $phieu->id,
                        'id_san_pham' => $ct['product_id'],
                        'variant_id' => $ct['variant_id'],
                        'id_lo_hang' => $loHang->id,
                        'id_chi_tiet_lo_hang' => $chiTietLoHang->id,
                        'so_luong' => $soLuongGoc,
                        'gia_nhap' => $giaNhapGoc,
                        'han_su_dung' => $ct['han_su_dung'],
                        'so_luong_con_lai' => $soLuongGoc,
                    ]);
                }

                // Cập nhật tồn kho biến thể
                BienTheSanPham::where('id', $ct['variant_id'])
                    ->increment('so_luong_ton', $soLuongGoc);
            }
        });
    }

    protected function findVariant(string $maVach): ?object
    {
        // Bước 1: Tìm trong bảng Biến thể gốc
        $variant = BienTheSanPham::where('ma_vach', $maVach)->first();
        if ($variant) {
            $variant->ty_le_quy_doi = 1;
            return $variant;
        }

        // Bước 2: Tìm trong bảng Đơn vị quy đổi
        $donViQuyDoi = DonViQuyDoi::where('ma_vach', $maVach)->first();
        if ($donViQuyDoi) {
            $baseVariant = BienTheSanPham::find($donViQuyDoi->variant_id);
            if ($baseVariant) {
                $baseVariant->ty_le_quy_doi = $donViQuyDoi->ty_le_quy_doi;
                return $baseVariant;
            }
        }

        return null;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function getRowCount(): int
    {
        return $this->rowCount;
    }
}
