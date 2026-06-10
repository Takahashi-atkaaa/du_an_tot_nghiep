<?php

// Khai bao namespace cho middleware
namespace App\Http\Middleware;

// Su dung middleware co so
use Closure;

// Su dung Request
use Illuminate\Http\Request;

// Dinh nghia interface tra ve Response
use Symfony\Component\HttpFoundation\Response;

class KiemTraVaiTro
{
    // Xu ly request
    public function handle(Request $request, Closure $next, ...$vaiTros): Response
    {
        // Lay nguoi dung hien tai dang nhap
        $nguoiDung = $request->user();

        // Kiem tra xem nguoi dung co vai tro duoc phep khong
        // $vaiTros la danh sach cac vai tro truyen vao route
        // VD: ->middleware('vai_tro:Admin,nhan_vien')
        if ($nguoiDung && in_array($nguoiDung->vai_tro, $vaiTros)) {
            // Co quyen -> cho phep request di tiep
            return $next($request);
        }

        // Khong co quyen -> tra ve trang 403 Forbidden
        // abort() se throw exception va terminates script
        abort(403, 'Ban khong co quyen truy cap trang nay');
    }
}
