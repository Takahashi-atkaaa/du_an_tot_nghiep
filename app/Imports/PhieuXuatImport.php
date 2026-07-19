<?php

namespace App\Imports;

use App\Models\BienTheSanPham;
use App\Models\ChiTietLoHang;
use App\Models\ChiTietPhieu;
use App\Models\Phieu;
use App\Models\PhieuXuat;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;

class PhieuXuatImport implements ToCollection
{
    protected string $loaiXuat;
    protected ?string $lyDo;
    protected ?string $ghiChu;
    protected int $idNguoiDung;
    protected int $idNhaCungCap;
    protected array $errors = [];
    protected int $rowCount = 0;

    public function __construct(
        string $loaiXuat = 'tieu_huy',
        ?string $lyDo = null,
        ?string $ghiChu = null,
        int $idNguoiDung = null,
        int $idNhaCungCap = 0
    ) {
        $this->loaiXuat = $loaiXuat;
        $this->lyDo = $lyDo;
        $this->ghiChu = $ghiChu;
        $this->idNguoiDung = $idNguoiDung ?? auth()->id() ?? 0;
        $this->idNhaCungCap = $idNhaCungCap;
    }

    public function collection(Collection $rows)
    {
        $this->errors = [];
        $this->rowCount = 0;

        $rows = $rows->skip(1);
        $rows = $rows->filter(fn($row) => !empty($row[0]) && trim((string)$row[0]) !== '');

        if ($rows->isEmpty()) {
            return;
        }

        $chiTietData = [];
        $groupedByVariant = [];

        foreach ($rows as $idx => $row) {
            $lineNumber = $idx + 2;
            $this->rowCount++;

            $maVach = trim((string)($row[0] ?? ''));
            $maHang = trim((string)($row[1] ?? ''));
            $soLuong = (int)($row[2] ?? 0);

            if ($maVach === '' && $maHang === '') {
                $this->errors[] = "Dòng {$lineNumber}: Thiếu Mã vạch và Mã hàng.";
                continue;
            }

            if ($soLuong <= 0) {
                $this->errors[] = "Dòng {$lineNumber}: Số lượng phải lớn hơn 0.";
                continue;
            }

            $variant = $this->findVariant($maVach, $maHang);
            if (!$variant) {
                $this->errors[] = "Dòng {$lineNumber}: Mã vạch/Mã hàng '{$maVach}{$maHang}' không tồn tại trong hệ thống.";
                continue;
            }

            if (!isset($groupedByVariant[$variant->id])) {
                $groupedByVariant[$variant->id] = [
                    'variant_id' => $variant->id,
                    'product_id' => $variant->product_id,
                    'total_so_luong' => 0,
                    'lines' => [],
                ];
            }

            $groupedByVariant[$variant->id]['total_so_luong'] += $soLuong;
            $groupedByVariant[$variant->id]['lines'][] = $lineNumber;
        }

        if (!empty($this->errors)) {
            throw new \Exception(implode(' | ', $this->errors));
        }

        $loaiPhieuEnum = $this->loaiXuat === 'tra_hang_nha_cung_cap'
            ? 'xuat_tra_hang_nha_cung_cap'
            : 'xuat_tieu_huy';
        $loaiPhieuLabel = $this->loaiXuat === 'tra_hang_nha_cung_cap'
            ? 'Trả hàng NCC'
            : 'Tiêu hủy';

        DB::transaction(function () use ($groupedByVariant, $loaiPhieuEnum, $loaiPhieuLabel) {
            $phieu = Phieu::create([
                'loai_phieu' => $loaiPhieuLabel,
                'loai_phieu_enum' => $loaiPhieuEnum,
                'id_nguoi_dung' => $this->idNguoiDung,
                'id_nha_cung_cap' => $this->idNhaCungCap ?: null,
                'ly_do' => $this->lyDo,
                'ghi_chu' => $this->ghiChu,
            ]);

            $phieuXuat = PhieuXuat::create([
                'id_phieu' => $phieu->id,
                'loai_xuat' => $this->loaiXuat,
                'ly_do' => $this->lyDo,
                'ghi_chu' => $this->ghiChu,
            ]);

            foreach ($groupedByVariant as $variantId => $data) {
                $this->xuLyXuatFifo($phieu->id, $data);
            }
        });
    }

    protected function xuLyXuatFifo(int $phieuId, array $data): void
    {
        $soLuongCanXuat = $data['total_so_luong'];
        $lines = implode(', ', $data['lines']);

        $cacLoTon = ChiTietLoHang::where('variant_id', $data['variant_id'])
            ->where('so_luong_ton', '>', 0)
            ->orderBy('han_su_dung', 'asc')
            ->lockForUpdate()
            ->get();

        $tongTonKho = $cacLoTon->sum('so_luong_ton');

        if ($tongTonKho < $soLuongCanXuat) {
            throw new \Exception(
                "Mã variant_id {$data['variant_id']}: Tổng tồn kho ({$tongTonKho}) không đủ để xuất ({$soLuongCanXuat}). Dòng: {$lines}"
            );
        }

        $soLuongConLai = $soLuongCanXuat;

        foreach ($cacLoTon as $ctLo) {
            if ($soLuongConLai <= 0) {
                break;
            }

            $soLuongXuatTuLo = min($soLuongConLai, $ctLo->so_luong_ton);

            $ctLo->decrement('so_luong_ton', $soLuongXuatTuLo);

            BienTheSanPham::where('id', $data['variant_id'])
                ->decrement('so_luong_ton', $soLuongXuatTuLo);

            ChiTietPhieu::create([
                'id_phieu' => $phieuId,
                'id_san_pham' => $data['product_id'],
                'variant_id' => $data['variant_id'],
                'id_lo_hang' => $ctLo->id_lo_hang,
                'id_chi_tiet_lo_hang' => $ctLo->id,
                'so_luong' => $soLuongXuatTuLo,
                'gia_nhap' => $ctLo->gia_nhap ?? 0,
                'han_su_dung' => $ctLo->han_su_dung,
                'so_luong_con_lai' => $ctLo->so_luong_ton - $soLuongXuatTuLo,
                'ghi_chu' => 'Xuất từ dòng: ' . $lines,
            ]);

            $soLuongConLai -= $soLuongXuatTuLo;
        }
    }

    protected function findVariant(string $maVach, string $maHang): ?BienTheSanPham
    {
        if ($maVach !== '') {
            $variant = BienTheSanPham::where('ma_vach', $maVach)->first();
            if ($variant) {
                return $variant;
            }
        }

        if ($maHang !== '') {
            return BienTheSanPham::where('ma_hang', $maHang)->first();
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
