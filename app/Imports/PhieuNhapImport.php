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
    protected int $insertedCount = 0;

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
            return $data;
        }

        // Trim khoang trang
        $hanSuDung = trim((string) $hanSuDung);

        // Excel date serial number (numeric)
        if (is_numeric($hanSuDung)) {
            try {
                $date = ExcelDate::excelToDateTimeObject($hanSuDung);
                $data['han_su_dung'] = $date->format('Y-m-d');
                return $data;
            } catch (\Exception $e) {
                // fall through
            }
        }

        // Thu cac dinh dang pho bien va chuyen ve Y-m-d
        $formats = ['Y-m-d', 'd/m/Y', 'd-m-Y', 'Y/m/d', 'd.m.Y', 'Y.m.d'];
        foreach ($formats as $fmt) {
            try {
                $date = \Carbon\Carbon::createFromFormat($fmt, $hanSuDung);
                if ($date !== false) {
                    $data['han_su_dung'] = $date->format('Y-m-d');
                    return $data;
                }
            } catch (\Exception $e) {
                // tiep tuc thu format khac
            }
        }

        // Cuoi cung: de nguyen, de rules() tu check va fail neu khong hop le
        $data['han_su_dung'] = $hanSuDung;
        return $data;
    }

    public function rules(): array
    {
        return [
            // Mac dinh yeu cau Y-m-d; prepareForValidation() da convert tu DD/MM/YYYY hoac Excel serial
            'han_su_dung' => 'required|date_format:Y-m-d',
        ];
    }

    public function collection(Collection $rows)
    {
        $this->errors = [];
        $this->rowCount = 0;
        $this->insertedCount = 0;

        $rows = $rows->filter(fn($row) => !empty($row['ma_vach']) && trim((string)$row['ma_vach']) !== '');

        if ($rows->isEmpty()) {
            return;
        }

        $chiTietData = [];
        $allVariantIds = [];

        foreach ($rows as $idx => $row) {
            $lineNumber = $idx + 2;
            $this->rowCount++;

            $maVach = $this->sanitizeMaVach((string)($row['ma_vach'] ?? ''));
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

            // prepareForValidation() da chuyen HSD ve Y-m-d (hoac 2099-12-31 neu trong)
            $hanSuDungFormatted = $hanSuDung;

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

        // Validation trong vong lap: neu co loi validate (so_luong <= 0, gia_nhap am, ...)
        // ta gan vao errors nhung van KHONG throw - de cuoi cung quyet dinh rollback hay khong.
        // Muc tieu: mot dong loi khong can tro cac dong hop le duoc import.

        $validatedData = [];
        $allVariantIds = [];

        foreach ($chiTietData as $key => $ct) {
            $variant = $this->findVariant($ct['ma_vach']);
            if (!$variant) {
                // Ghi loi nhung KHONG throw o day - van tiep tuc xu ly cac dong khac
                $this->errors[] = "Dong {$ct['line_number']}: Ma vach '{$ct['ma_vach']}' khong ton tai trong he thong.";
                continue;
            }

            $allVariantIds[$variant->id] = true;
            $validatedData[] = [
                'line_number' => $ct['line_number'],
                'variant_id' => $variant->id,
                'product_id' => $variant->product_id,
                'so_luong' => $ct['so_luong'],
                'so_luong_san_pham_trong_don_vi' => $variant->so_luong_san_pham_trong_don_vi ?? 1,
                'ten_don_vi' => $variant->ten_don_vi_quy_doi ?? null,
                'gia_nhap' => $ct['gia_nhap'],
                'han_su_dung' => $ct['han_su_dung'],
            ];
        }

        // Neu khong co dong hop le nao -> that bai
        if (empty($validatedData)) {
            throw new \Exception(
                'Khong co dong hop le nao de import. Loi: ' . implode(' | ', array_slice($this->errors, 0, 5))
            );
        }

        // Neu co loi nhung van con dong hop le -> van import nhung tra ve errors de hien thi
        // (xu ly tiep ben duoi)

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
                'ma_lo' => 'PN-' . $phieu->id,
                'ngay_nhap' => now()->toDateString(),
                'ghi_chu' => 'Tạo từ import Excel',
            ]);
            $maLo = $loHang->ma_lo;

            foreach ($validatedData as $ct) {
                // Bảo vệ chia cho 0: nếu hệ số <= 0 coi như đơn vị cơ bản
                $heSoQuyDoi = (float)($ct['so_luong_san_pham_trong_don_vi'] ?? 1);
                if ($heSoQuyDoi <= 0) {
                    $heSoQuyDoi = 1;
                }
                $soLuongCoBan = $ct['so_luong'] * $heSoQuyDoi;
                // Nếu user nhập giá theo đơn vị quy đổi, đưa về giá / đơn vị cơ bản
                $giaNhapCoBan = $heSoQuyDoi > 1
                    ? $ct['gia_nhap'] / $heSoQuyDoi
                    : $ct['gia_nhap'];

                $ghiChuCt = $heSoQuyDoi > 1
                    ? sprintf('Nhập %s %s × %s (hệ số) qua import Excel',
                        rtrim(rtrim(number_format($ct['so_luong'], 4, '.', ''), '0'), '.'),
                        $ct['ten_don_vi'] ?? 'đơn vị quy đổi',
                        rtrim(rtrim(number_format($heSoQuyDoi, 4, '.', ''), '0'), '.'))
                    : null;

                // Tìm chi_tiet_lo_hang đã tồn tại
                $chiTietLoHang = ChiTietLoHang::where('id_lo_hang', $loHang->id)
                    ->where('variant_id', $ct['variant_id'])
                    ->where('han_su_dung', $ct['han_su_dung'])
                    ->first();

                if ($chiTietLoHang) {
                    // UPSERT: Cộng dồn số lượng theo đơn vị cơ bản, giá bình quân gia quyền
                    $oldQuantity = (int)$chiTietLoHang->so_luong_nhap;
                    $oldPrice = (float)$chiTietLoHang->gia_nhap;
                    $newQuantity = $oldQuantity + $soLuongCoBan;
                    $newPrice = $newQuantity > 0
                        ? (($oldPrice * $oldQuantity) + ($giaNhapCoBan * $soLuongCoBan)) / $newQuantity
                        : $giaNhapCoBan;

                    $chiTietLoHang->update([
                        'so_luong_nhap' => $newQuantity,
                        'so_luong_ton' => $chiTietLoHang->so_luong_ton + $soLuongCoBan,
                        'gia_nhap' => $newPrice,
                    ]);
                } else {
                    // INSERT: Tạo mới với số lượng & giá đã chuẩn hóa
                    $chiTietLoHang = ChiTietLoHang::create([
                        'id_lo_hang' => $loHang->id,
                        'id_san_pham' => $ct['product_id'],
                        'variant_id' => $ct['variant_id'],
                        'so_luong_nhap' => $soLuongCoBan,
                        'so_luong_ton' => $soLuongCoBan,
                        'gia_nhap' => $giaNhapCoBan,
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
                        'so_luong' => $chiTietPhieu->so_luong + $soLuongCoBan,
                        'so_luong_con_lai' => $chiTietPhieu->so_luong_con_lai + $soLuongCoBan,
                    ]);
                } else {
                    ChiTietPhieu::create([
                        'id_phieu' => $phieu->id,
                        'id_san_pham' => $ct['product_id'],
                        'variant_id' => $ct['variant_id'],
                        'id_lo_hang' => $loHang->id,
                        'id_chi_tiet_lo_hang' => $chiTietLoHang->id,
                        'so_luong' => $soLuongCoBan,
                        'gia_nhap' => $giaNhapCoBan,
                        'ma_lo' => $maLo,
                        'han_su_dung' => $ct['han_su_dung'],
                        'so_luong_con_lai' => $soLuongCoBan,
                        'ghi_chu' => $ghiChuCt,
                    ]);
                    $this->insertedCount++;
                }

                // ChiTietLoHangObserver đã tự đồng bộ tổng tồn
                // bien_the_san_pham.so_luong_ton sau khi ChiTietLoHang::update()
                // hoặc ChiTietLoHang::create() ở phía trên.
            }
        });
    }

    protected function findVariant(string $maVach): ?object
    {
        // Buoc 1: Trim khoang trang va khoang trang dac biet (BOM, NBSP...)
        $maVachClean = $this->sanitizeMaVach($maVach);

        // Buoc 2: Tim chinh xac trong bien_the_san_pham
        $variant = BienTheSanPham::where('ma_vach', $maVachClean)->first();
        if ($variant) {
            $variant->so_luong_san_pham_trong_don_vi = 1;
            $variant->ten_don_vi_quy_doi = null;
            return $variant;
        }

        // Buoc 3: Fallback - thu cac dang khac neu user nhap so co leading zero, decimal...
        // Vi du: Excel luu "00789" -> "789", hoac nguoc lai "789" -> "00789"
        $candidates = $this->generateMaVachCandidates($maVachClean);
        foreach ($candidates as $candidate) {
            if ($candidate === $maVachClean) {
                continue;
            }
            $variant = BienTheSanPham::where('ma_vach', $candidate)->first();
            if ($variant) {
                $variant->so_luong_san_pham_trong_don_vi = 1;
                $variant->ten_don_vi_quy_doi = null;
                return $variant;
            }
        }

        // Buoc 4: Tim trong bang don_vi_quy_doi
        $donViQuyDoi = DonViQuyDoi::where('ma_vach', $maVachClean)->first();
        if ($donViQuyDoi) {
            $baseVariant = BienTheSanPham::find($donViQuyDoi->variant_id);
            if ($baseVariant) {
                $baseVariant->so_luong_san_pham_trong_don_vi = $donViQuyDoi->so_luong_san_pham_trong_don_vi;
                $baseVariant->ten_don_vi_quy_doi = $donViQuyDoi->ten_don_vi;
                return $baseVariant;
            }
        }

        // Buoc 5: Fallback don_vi_quy_doi voi cac dang khac
        foreach ($candidates as $candidate) {
            if ($candidate === $maVachClean) {
                continue;
            }
            $donViQuyDoi = DonViQuyDoi::where('ma_vach', $candidate)->first();
            if ($donViQuyDoi) {
                $baseVariant = BienTheSanPham::find($donViQuyDoi->variant_id);
                if ($baseVariant) {
                    $baseVariant->so_luong_san_pham_trong_don_vi = $donViQuyDoi->so_luong_san_pham_trong_don_vi;
                    $baseVariant->ten_don_vi_quy_doi = $donViQuyDoi->ten_don_vi;
                    return $baseVariant;
                }
            }
        }

        return null;
    }

    /**
     * Lam sach ma vach: loai bo khoang trang, BOM, NBSP, zero-width.
     */
    private function sanitizeMaVach(string $maVach): string
    {
        $maVach = trim($maVach);
        // Loai BOM o dau
        $maVach = ltrim($maVach, "\xEF\xBB\xBF");
        // Loai cac ky tu dac biet
        $maVach = preg_replace('/[\x{200B}-\x{200D}\x{FEFF}\x{00A0}]/u', '', $maVach);
        return trim($maVach);
    }

    /**
     * Tao cac dang candidate cho ma_vach (fallback neu khong tim thay chinh xac).
     *
     * Vi du tu "789879":
     *  - "00789879" (them leading zero)
     *  - "789879.0" (co decimal)
     *  - "7898790" (them 0 cuoi)
     *  - " 789879" (leading space - mac du da trim)
     */
    private function generateMaVachCandidates(string $maVach): array
    {
        $candidates = [];

        // Neu la so, tao cac bien the so
        if (is_numeric($maVach)) {
            $num = (float) $maVach;
            $intVal = (int) $num;

            // Them leading zero
            $candidates[] = str_pad($maVach, 8, '0', STR_PAD_LEFT);
            $candidates[] = str_pad((string) $intVal, 8, '0', STR_PAD_LEFT);

            // Them decimal
            $candidates[] = $maVach . '.0';
            $candidates[] = $intVal . '.0';

            // Them trailing zero
            $candidates[] = $maVach . '0';
            $candidates[] = $intVal . '0';
        }

        return $candidates;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function getRowCount(): int
    {
        return $this->rowCount;
    }

    public function getInsertedCount(): int
    {
        return $this->insertedCount;
    }
}
