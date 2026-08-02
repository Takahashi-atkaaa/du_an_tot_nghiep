<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class KTVaiTro
{
    public function handle(Request $request, Closure $next, ?string $permission = null): Response
    {
        // #region agent log (H5: log middleware auth check + permission check)
        try {
            $logFile = '/Applications/XAMPP/xamppfiles/htdocs/SmartMart/.cursor/debug-d7dcc7.log';
            $payload = [
                'sessionId' => 'd7dcc7',
                'runId' => 'run1',
                'hypothesisId' => 'H5',
                'location' => 'KTVaiTro.php:handle:entry',
                'message' => 'KTVaiTro middleware entered',
                'data' => [
                    'path' => $request->path(),
                    'method' => $request->method(),
                    'auth_check' => auth()->check(),
                    'user_id' => auth()->id(),
                    'session_id' => $request->session()->getId(),
                    'permission_required' => $permission,
                ],
                'timestamp' => time() * 1000,
            ];
            file_put_contents($logFile, json_encode($payload) . PHP_EOL, FILE_APPEND);
        } catch (\Throwable $e) {}
        // #endregion

        if (!auth()->check()) {
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
