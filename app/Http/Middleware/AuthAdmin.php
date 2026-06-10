<?php

// Khai bao namespace cho middleware
namespace App\Http\Middleware;

// Su dung middleware co so
use Closure;

// Su dung Request
use Illuminate\Http\Request;

// Su dung Auth facade
use Illuminate\Support\Facades\Auth;

// Dinh nghia interface de dam bao middleware ho tro next()
use Symfony\Component\HttpFoundation\Response;

class AuthAdmin
{
    // Xu ly request
    public function handle(Request $request, Closure $next): Response
    {
        // Kiem tra xem nguoi dung da dang nhap chua bang guard 'admin'
        if (!Auth::check()) {
            // Chua dang nhap -> chuyen huong ve trang login
            return redirect('/admin/login')
                ->with('error', 'Vui long dang nhap de truy cap'); // Gui thong bao loi
        }

        // Da dang nhap -> cho phep request di tiep
        return $next($request);
    }
}
