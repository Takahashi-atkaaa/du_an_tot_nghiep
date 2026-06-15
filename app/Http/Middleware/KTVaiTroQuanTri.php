<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class KTVaiTroQuanTri
{
public function handle(Request $request, Closure $next, string $permission = null): Response
{
    if (!auth()->check()) {
        return redirect()->route('admin.login');
    }

    $user = auth()->user();

    if ($user->vai_tro === 'admin') {
        return $next($request);
    }

    // ❗ nếu không truyền permission thì chặn luôn hoặc cho qua tùy bạn
    if (!$permission) {
        return $next($request); // hoặc abort(403)
    }

    if (!$user->hasPermission($permission)) {
        abort(403, 'Bạn không có quyền truy cập');
    }

    return $next($request);
}
}