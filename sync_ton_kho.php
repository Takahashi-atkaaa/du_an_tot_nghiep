<?php
/**
 * Script đồng bộ bien_the_san_pham.so_luong_ton = SUM(chi_tiet_lo_hang.so_luong_ton)
 *
 * Nguyên nhân: Khi user sửa DB thủ công (xóa lô, sửa số lượng),
 * cột denormalized `bien_the_san_pham.so_luong_ton` không tự cập nhật,
 * dẫn đến UI hiển thị sai (ví dụ: tồn = 10 dù đã xóa lô).
 *
 * Cách dùng:
 *   1. Chạy cho 1 sản phẩm cụ thể: php artisan tinker --execute="require 'sync_ton_kho.php'; syncTonKhoByProduct(123);"
 *   2. Chạy cho tất cả: php artisan tinker --execute="require 'sync_ton_kho.php'; syncTonKhoAll();"
 *   3. Chạy qua URL: /admin/dev/sync-ton-kho (cần bảo vệ route - xem ghi chú dưới).
 */

require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

/**
 * Đồng bộ tồn kho cho MỘT biến thể cụ thể (variant_id).
 */
function syncTonKhoByVariant(int $variantId): int
{
    $sum = (int) DB::table('chi_tiet_lo_hang')
        ->where('variant_id', $variantId)
        ->sum('so_luong_ton');

    DB::table('bien_the_san_pham')
        ->where('id', $variantId)
        ->update(['so_luong_ton' => $sum]);

    return $sum;
}

/**
 * Đồng bộ tồn kho cho TẤT CẢ biến thể của MỘT sản phẩm (product_id).
 */
function syncTonKhoByProduct(int $productId): array
{
    $variantIds = DB::table('bien_the_san_pham')
        ->where('id_san_pham', $productId)
        ->pluck('id')
        ->all();

    $result = [];
    foreach ($variantIds as $vid) {
        $result[$vid] = syncTonKhoByVariant($vid);
    }
    return $result;
}

/**
 * Đồng bộ tồn kho cho TẤT CẢ biến thể (toàn hệ thống).
 * CHẠY CHẬM với CSDL lớn - chỉ dùng khi cần repair toàn bộ.
 */
function syncTonKhoAll(): array
{
    $stats = ['updated' => 0, 'unchanged' => 0, 'total' => 0];

    $variantIds = DB::table('bien_the_san_pham')
        ->whereNull('deleted_at')
        ->pluck('id', 'so_luong_ton')
        ->all();

    foreach ($variantIds as $vid => $oldValue) {
        $newValue = syncTonKhoByVariant((int)$vid);
        $stats['total']++;
        if ((int)$oldValue !== $newValue) {
            $stats['updated']++;
        } else {
            $stats['unchanged']++;
        }
    }
    return $stats;
}

// ============================================================
// Auto-run nếu file được gọi trực tiếp qua CLI
// ============================================================
if (php_sapi_name() === 'cli') {
    $arg1 = $argv[1] ?? null;
    $arg2 = $argv[2] ?? null;

    if ($arg1 === 'all') {
        echo "Đang đồng bộ tồn kho cho TẤT CẢ biến thể...\n";
        $stats = syncTonKhoAll();
        echo "Hoàn tất:\n";
        echo "  - Tổng: {$stats['total']}\n";
        echo "  - Cập nhật: {$stats['updated']}\n";
        echo "  - Giữ nguyên: {$stats['unchanged']}\n";
    } elseif ($arg1 === 'product' && $arg2) {
        echo "Đang đồng bộ tồn kho cho sản phẩm ID={$arg2}...\n";
        $result = syncTonKhoByProduct((int)$arg2);
        foreach ($result as $vid => $ton) {
            echo "  Variant {$vid}: tồn = {$ton}\n";
        }
    } elseif ($arg1 === 'variant' && $arg2) {
        echo "Đang đồng bộ tồn kho cho biến thể ID={$arg2}...\n";
        $ton = syncTonKhoByVariant((int)$arg2);
        echo "  Tồn mới = {$ton}\n";
    } elseif ($arg1 === 'sua-chua') {
        // Tìm tất cả variant có sự chênh lệch
        echo "Đang quét chênh lệch tồn kho...\n";
        $variants = DB::table('bien_the_san_pham as bt')
            ->leftJoin('chi_tiet_lo_hang as ct', 'ct.variant_id', '=', 'bt.id')
            ->select(
                'bt.id as variant_id',
                'bt.id_san_pham',
                'bt.so_luong_ton as cached',
                DB::raw('COALESCE(SUM(ct.so_luong_ton), 0) as actual')
            )
            ->whereNull('bt.deleted_at')
            ->groupBy('bt.id', 'bt.id_san_pham', 'bt.so_luong_ton')
            ->havingRaw('bt.so_luong_ton <> COALESCE(SUM(ct.so_luong_ton), 0)')
            ->get();

        if ($variants->isEmpty()) {
            echo "Không có chênh lệch.\n";
        } else {
            echo "Có {$variants->count()} biến thể lệch:\n";
            foreach ($variants as $v) {
                echo sprintf(
                    "  Variant #%d (product #%d): cache=%d → thực tế=%d\n",
                    $v->variant_id, $v->id_san_pham, $v->cached, $v->actual
                );
            }
            echo "Bạn có thể chạy: php sync_ton_kho.php variant <id>\n";
        }
    } else {
        echo "Cú pháp:\n";
        echo "  php sync_ton_kho.php all                  # đồng bộ tất cả\n";
        echo "  php sync_ton_kho.php product <id>         # đồng bộ 1 sản phẩm\n";
        echo "  php sync_ton_kho.php variant <id>         # đồng bộ 1 biến thể\n";
        echo "  php sync_ton_kho.php sua-chua             # quét chênh lệch\n";
    }
}
