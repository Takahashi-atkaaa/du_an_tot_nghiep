<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\CanhBao;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class CanhBaoService
{
    public function luu(AuditLog $log, string $tieuDe, string $noiDung, ?string $urlLienKet = null): ?CanhBao
    {
        try {
            return CanhBao::create([
                'id_audit_log' => $log->id,
                'id_nguoi_dung_thuc_hien' => $log->id_nguoi_dung,
                'tieu_de' => $tieuDe,
                'noi_dung' => $noiDung,
                'muc_do' => $log->muc_do,
                'hanh_dong' => $log->hanh_dong,
                'url_lien_ket' => $urlLienKet,
                'da_doc' => false,
                'created_at' => $log->created_at ?? now(),
            ]);
        } catch (\Throwable $e) {
            Log::error('CanhBaoService::luu loi: ' . $e->getMessage(), [
                'tieu_de' => $tieuDe,
            ]);
            return null;
        }
    }

    public function soChuaDoc(): int
    {
        return CanhBao::where('da_doc', false)->count();
    }

    public function danhDauDaDoc(int $id, int $idNguoiDung): bool
    {
        $cb = CanhBao::find($id);
        if (! $cb) {
            return false;
        }
        $cb->da_doc = true;
        $cb->id_nguoi_dung_da_doc = $idNguoiDung;
        $cb->thoi_gian_doc = now();
        return $cb->save();
    }

    public function danhDauTatCaDaDoc(int $idNguoiDung): int
    {
        return CanhBao::where('da_doc', false)
            ->update([
                'da_doc' => true,
                'id_nguoi_dung_da_doc' => $idNguoiDung,
                'thoi_gian_doc' => now(),
            ]);
    }
}