<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthTruongCa
{
public function handle(Request $request, Closure $next): Response
{
    if(!auth()->check()){
        return redirect()->route('admin.login');
    }

    if (auth()->user()->vai_tro !== 'truongca') {
        abort(403, 'Bạn không có quyền truy cập chức năng này');
    }

    return $next($request);
}
}