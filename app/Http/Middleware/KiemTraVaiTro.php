<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class KiemTraVaiTro
{
    public function handle(Request $request, Closure $next, ...$vaiTros): Response
    {
        $nguoiDung = $request->user();

        if (! $nguoiDung) {
            abort(403, 'Bạn không có quyền truy cập trang này');
        }

        $tenVaiTro = Str::of((string) optional($nguoiDung->vaiTro)->ten_vai_tro)
            ->lower()
            ->ascii()
            ->value();

        $vaiTrosChoPhep = collect($vaiTros)
            ->map(fn (string $vaiTro) => Str::of($vaiTro)->lower()->ascii()->value())
            ->all();

        if (in_array($tenVaiTro, $vaiTrosChoPhep, true)) {
            return $next($request);
        }

        abort(403, 'Bạn không có quyền truy cập trang này');
    }
}
