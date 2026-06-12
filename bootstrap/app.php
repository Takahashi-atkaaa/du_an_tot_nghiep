<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

// Khai bao su dung middleware AuthAdmin
use App\Http\Middleware\AuthAdmin;

// Khai bao su dung middleware KiemTraVaiTro
use App\Http\Middleware\KiemTraVaiTro;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Dang ky middleware alias
        $middleware->alias([
            'auth.dangnhap.admin' => AuthAdmin::class,   // Middleware kiem tra dang nhap
            'kiem_tra_vai_tro'    => KiemTraVaiTro::class, // Middleware kiem tra vai tro
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
