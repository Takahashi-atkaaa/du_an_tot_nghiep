<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class KTVaiTro
{
    public function handle(Request $request, Closure $next, ?string $permission = null): Response
    {
        // #region agent log
        try {
            $logPath = base_path('debug-80bdd0.log');
            $payload = [
                'sessionId' => '80bdd0',
                'id' => 'log_' . time() . '_' . substr(md5(uniqid()), 0, 6),
                'timestamp' => (int)(microtime(true) * 1000),
                'location' => 'KTVaiTro.php:11',
                'message' => 'KTVaiTro entry',
                'data' => [
                    'route' => $request->path(),
                    'method' => $request->method(),
                    'authenticated' => auth()->check(),
                    'user_id' => auth()->check() ? auth()->id() : null,
                ],
                'runId' => 'initial',
                'hypothesisId' => 'H8',
            ];
            file_put_contents($logPath, json_encode($payload, JSON_UNESCAPED_UNICODE) . "\n", FILE_APPEND | LOCK_EX);
        } catch (\Throwable $logEx) {}
        // #endregion
        if (!auth()->check()) {
            // #region agent log
            try {
                $logPath = base_path('debug-80bdd0.log');
                $payload = [
                    'sessionId' => '80bdd0',
                    'id' => 'log_' . time() . '_' . substr(md5(uniqid()), 0, 6),
                    'timestamp' => (int)(microtime(true) * 1000),
                    'location' => 'KTVaiTro.php:14',
                    'message' => 'NOT AUTHENTICATED - redirect to login',
                    'data' => [ 'route' => $request->path() ],
                    'runId' => 'initial', 'hypothesisId' => 'H8',
                ];
                file_put_contents($logPath, json_encode($payload, JSON_UNESCAPED_UNICODE) . "\n", FILE_APPEND | LOCK_EX);
            } catch (\Throwable $logEx) {}
            // #endregion
            return redirect()->route('admin.login');
        }

        $user = auth()->user();

        if($user->trang_thai == 2){
            return redirect('/admin/login')
                ->with('error', 'Tài khoản của bạn đã bị khóa!'); // Gui thong bao loi
        }

        if ($user->id_vai_tro === 1) {
            return $next($request);
        }

        if (!$permission) {
            return $next($request);
        }

        $vaiTroQuanHe = $user->vaiTro;

        if (!$vaiTroQuanHe) {
            abort(403, 'Tài khoản chưa được gán vai trò hợp lệ.');
        }

        if (!$vaiTroQuanHe->hasPermission($permission)) {
            abort(403, 'Bạn không có quyền truy cập');
        }

        return $next($request);
    }
}
