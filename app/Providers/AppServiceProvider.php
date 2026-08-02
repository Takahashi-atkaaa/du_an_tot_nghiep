<?php

namespace App\Providers;

use App\Models\ChiTietLoHang;
use App\Models\ChiTietPhieu;
use App\Observers\ChiTietLoHangObserver;
use App\Observers\ChiTietPhieuObserver;
use App\Services\PayOSService;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(PayOSService::class, function ($app) {
            $cfg = config('payos');

            return new PayOSService(
                clientId: (string) $cfg['client_id'],
                apiKey: (string) $cfg['api_key'],
                checksumKey: (string) $cfg['checksum_key'],
                returnUrl: (string) $cfg['return_url'],
                cancelUrl: (string) $cfg['cancel_url'],
                webhookUrl: (string) $cfg['webhook_url'],
                expireMinutes: (int) $cfg['expire_minutes'],
                orderCodePrefix: (string) $cfg['order_code_prefix'],
                apiBase: (string) $cfg['api_base'],
            );
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useBootstrap();
        ChiTietLoHang::observe(ChiTietLoHangObserver::class);
        ChiTietPhieu::observe(ChiTietPhieuObserver::class);

        // When APP_URL is https, force all generated URLs to be https so the
        // browser does not block them as mixed content. This is needed when
        // behind a reverse proxy (ngrok, Cloudflare, etc.) that forwards to
        // a plain HTTP upstream without preserving X-Forwarded-Proto.
        if (str_starts_with((string) config('app.url'), 'https://')) {
            URL::forceScheme('https');
        }

        // Trust ngrok (and other reverse proxies) so Request::isSecure() and
        // route()/url() honor the X-Forwarded-Proto header. Without this,
        // URLs are generated as http://, which the browser blocks as mixed
        // content when the page is served over https:// via ngrok.
        $trustedProxies = config('app.trusted_proxies');
        if ($trustedProxies === '*' || is_array($trustedProxies)) {
            $this->app['request']->setTrustedProxies(
                $trustedProxies === '*' ? ['*'] : $trustedProxies,
                \Illuminate\Http\Request::HEADER_X_FORWARDED_FOR
                | \Illuminate\Http\Request::HEADER_X_FORWARDED_HOST
                | \Illuminate\Http\Request::HEADER_X_FORWARDED_PORT
                | \Illuminate\Http\Request::HEADER_X_FORWARDED_PROTO
            );
        }
    }
}
