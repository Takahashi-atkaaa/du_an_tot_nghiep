<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AuditLogger
{
    public function __construct(private CanhBaoService $canhBaoService) {}

    public function ghi(string $hanhDong, string $moTa, array $opts = []): ?AuditLog
    {
        try {
            $log = AuditLog::create([
                'id_nguoi_dung' => Auth::id(),
                'hanh_dong' => $hanhDong,
                'bang_bi_tac_dong' => $opts['bang'] ?? null,
                'id_ban_ghi' => $opts['id_ban_ghi'] ?? null,
                'mo_ta' => $moTa,
                'du_lieu_cu' => $opts['du_lieu_cu'] ?? null,
                'du_lieu_moi' => $opts['du_lieu_moi'] ?? null,
                'muc_do' => 'info',
                'ip_address' => request()?->ip(),
                'user_agent' => request()?->userAgent(),
                'created_at' => now(),
            ]);

            $taoCanhBao = array_key_exists('tao_canh_bao', $opts)
                ? (bool) $opts['tao_canh_bao']
                : true;

            if ($taoCanhBao) {
                $this->canhBaoService->luu(
                    $log,
                    $opts['tieu_de_cb'] ?? $moTa,
                    $opts['noi_dung_cb'] ?? $moTa,
                    $opts['url_lien_ket'] ?? null
                );
            }

            return $log;
        } catch (\Throwable $e) {
            Log::error('AuditLogger loi: ' . $e->getMessage(), [
                'hanh_dong' => $hanhDong,
                'mo_ta' => $moTa,
            ]);
            return null;
        }
    }
}