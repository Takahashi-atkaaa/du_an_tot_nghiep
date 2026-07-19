<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class KTVaiTro
{
    public function handle(Request $request, Closure $next, ?string $permission = null): Response
    {
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
