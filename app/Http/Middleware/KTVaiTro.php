<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class KTVaiTro
{
public function handle(Request $request, Closure $next, string $permission = null): Response
{
    if (!auth()->check()) {
        return redirect()->route('admin.login');
    }

    $user = auth()->user();

    if (!$user->vaiTro) {
        abort(403, 'Tài khoản chưa được phân quyền.');
    }

    if ($user->vaiTro->ten_vai_tro === 'Admin') {
        return $next($request);
    }

    // ❗ nếu không truyền permission thì chặn luôn hoặc cho qua tùy bạn
    if (!$permission) {
        return $next($request); // hoặc abort(403)
    }

    if (!$user->vaiTro->hasPermission($permission)) {
        abort(403, 'Bạn không có quyền truy cập');
    }

    return $next($request);
}
}