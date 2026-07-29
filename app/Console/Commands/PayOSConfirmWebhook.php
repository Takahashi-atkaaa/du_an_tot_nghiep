<?php

namespace App\Console\Commands;

use App\Services\PayOSService;
use Illuminate\Console\Command;

class PayOSConfirmWebhook extends Command
{
    protected $signature = 'payos:webhook:confirm
        {url? : URL webhook public (mặc định lấy từ config payos.webhook_url)}
        {--show-config : In URL hiện tại trong config mà không gọi PayOS}';

    protected $description = 'Đăng ký/xác nhận webhook URL với PayOS. Mỗi lần đổi ngrok tunnel cần chạy lại.';

    public function handle(PayOSService $service): int
    {
        $url = (string) ($this->argument('url') ?: config('payos.webhook_url'));

        $this->info("Webhook URL sẽ đăng ký: {$url}");

        if ($this->option('show-config')) {
            return self::SUCCESS;
        }

        $clientId = (string) config('payos.client_id');
        $apiKey = (string) config('payos.api_key');
        $checksumKey = (string) config('payos.checksum_key');

        if ($clientId === '' || $apiKey === '' || $checksumKey === '') {
            $this->warn('PayOS credentials trống (PAYOS_CLIENT_ID/API_KEY/CHECKSUM_KEY).');
            $this->warn('Điền credentials vào .env rồi chạy lại. Hiện tại PayOS sẽ trả 401/403.');

            return self::FAILURE;
        }

        if (! str_starts_with($url, 'https://')) {
            $this->error('Webhook URL phải là HTTPS để PayOS chấp nhận.');

            return self::FAILURE;
        }

        $this->line('Đang gửi POST v2/webhooks/confirm tới PayOS...');

        try {
            $ok = $service->confirmWebhook($url);
        } catch (\Throwable $e) {
            $this->error('Lỗi khi gọi PayOS: '.$e->getMessage());
            $this->line('Kiểm tra storage/logs/laravel.log để biết chi tiết.');

            return self::FAILURE;
        }

        if ($ok) {
            $this->info('OK. PayOS đã chấp nhận webhook URL và gửi 1 POST test tới endpoint.');
            $this->line('Kiểm tra:');
            $this->line('  - storage/logs/laravel.log (signature verify)');
            $this->line('  - tail -f log webhook từ ngrok dashboard');

            return self::SUCCESS;
        }

        $this->error('PayOS từ chối. Kiểm tra:');
        $this->line('  - URL có trỏ đúng về /payos/webhook và server đang chạy');
        $this->line('  - ngrok còn sống (không bị timeout)');
        $this->line('  - response body từ PayOS (xem laravel.log)');

        return self::FAILURE;
    }
}