<?php

namespace App\Providers;

use App\Models\ChiTietLoHang;
use App\Models\ChiTietPhieu;
use App\Observers\ChiTietLoHangObserver;
use App\Observers\ChiTietPhieuObserver;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useBootstrap();
        ChiTietLoHang::observe(ChiTietLoHangObserver::class);
        ChiTietPhieu::observe(ChiTietPhieuObserver::class);
    }
}
