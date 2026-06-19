<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class KTVaiTro
{
    public function handle(Request $request, Closure $next, ?string $permission = null): Response
    {
        if (!auth()->check()) {
            return redirect()->route('admin.login');
        }

        $user = auth()->user();
        $vaiTroQuanHe = $user->vaiTro;
        $tenVaiTro = $vaiTroQuanHe?->ten_vai_tro;
        $tenVaiTroChuanHoa = Str::of((string) $tenVaiTro)
            ->lower()
            ->ascii()
            ->replace(' ', '_')
            ->value();

        if (in_array($tenVaiTroChuanHoa, ['admin', 'quan_tri', 'quantri'], true)) {
            return $next($request);
        }

        if (!$permission) {
            return $next($request);
        }

        if (!$vaiTroQuanHe) {
            abort(403, 'Tài khoản chưa được gán vai trò hợp lệ.');
        }

        if (!$vaiTroQuanHe->hasPermission($permission)) {
            abort(403, 'Bạn không có quyền truy cập');
        }

        return $next($request);
    }
}
