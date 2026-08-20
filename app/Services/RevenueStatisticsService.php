<?php

namespace App\Services;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class RevenueStatisticsService
{
    public function salesRevenueStatuses(): array
    {
        return [
            'Hoàn thành',
            'Hoàn Thành',
            'Đã đổi/trả hàng',
            'Đã trả toàn bộ',
        ];
    }

    public function returnedAmountPerInvoiceSubquery(): Builder
    {
        return DB::table('doi_tra')
            ->join('chi_tiet_doi_tra', 'doi_tra.id', '=', 'chi_tiet_doi_tra.id_doi_tra')
            ->whereNull('doi_tra.deleted_at')
            ->whereNull('chi_tiet_doi_tra.deleted_at')
            ->whereRaw($this->chiTietLoaiSql() . " = 'tra_hang'")
            ->groupBy('doi_tra.id_hoa_don')
            ->selectRaw('doi_tra.id_hoa_don, SUM(chi_tiet_doi_tra.thanh_tien) as tong_tien_tra');
    }

    public function returnedAmountPerInvoiceVariantSubquery(): Builder
    {
        return DB::table('doi_tra')
            ->join('chi_tiet_doi_tra', 'doi_tra.id', '=', 'chi_tiet_doi_tra.id_doi_tra')
            ->whereNull('doi_tra.deleted_at')
            ->whereNull('chi_tiet_doi_tra.deleted_at')
            ->whereRaw($this->chiTietLoaiSql() . " = 'tra_hang'")
            ->groupBy('doi_tra.id_hoa_don', 'chi_tiet_doi_tra.id_bien_the')
            ->selectRaw('doi_tra.id_hoa_don, chi_tiet_doi_tra.id_bien_the, SUM(chi_tiet_doi_tra.thanh_tien) as tong_tien_tra');
    }

    private function chiTietLoaiSql(): string
    {
        return "COALESCE(chi_tiet_doi_tra.loai, CASE WHEN doi_tra.Loai = 'doi_tra' THEN 'doi_hang' ELSE 'tra_hang' END)";
    }

    public function invoiceNetRevenueExpression(
        string $grossColumn = 'hoa_don.tong_tien_hang',
        string $returnedColumn = 'doi_tra_tra_hang.tong_tien_tra'
    ): string {
        return "GREATEST(COALESCE({$grossColumn}, 0) - COALESCE({$returnedColumn}, 0), 0)";
    }

    public function lineNetRevenueExpression(
        string $grossColumn = 'chi_tiet_hoa_don.thanh_tien',
        string $returnedColumn = 'doi_tra_bien_the.tong_tien_tra'
    ): string {
        return "GREATEST(COALESCE({$grossColumn}, 0) - COALESCE({$returnedColumn}, 0), 0)";
    }

    public function invoiceNetRevenueQuery(): Builder
    {
        $returnedSub = $this->returnedAmountPerInvoiceSubquery();

        return DB::table('hoa_don')
            ->leftJoinSub($returnedSub, 'doi_tra_tra_hang', function ($join) {
                $join->on('hoa_don.id', '=', 'doi_tra_tra_hang.id_hoa_don');
            });
    }

    public function sumInvoiceNetRevenue(
        Builder $query,
        string $grossColumn = 'hoa_don.tong_tien_hang',
        string $returnedColumn = 'doi_tra_tra_hang.tong_tien_tra'
    ): float {
        $expression = $this->invoiceNetRevenueExpression($grossColumn, $returnedColumn);

        return (float) (clone $query)
            ->selectRaw("SUM({$expression}) as tong_doanh_thu_thuan")
            ->value('tong_doanh_thu_thuan');
    }
}
