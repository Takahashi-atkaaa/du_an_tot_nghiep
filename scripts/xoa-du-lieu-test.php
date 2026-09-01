<?php
/**
 * Script xóa toàn bộ dữ liệu test
 * Chạy: php scripts/xoa-du-lieu-test.php
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== BẮT ĐẦU XÓA DỮ LIỆU TEST ===" . PHP_EOL . PHP_EOL;

try {
    DB::beginTransaction();
    
    // 1. Xóa thanh toán hóa đơn
    echo "1. Xóa thanh toán hóa đơn...";
    $count = DB::table('thanh_toan_hoa_don')->delete();
    echo " ✓ Đã xóa {$count} dòng" . PHP_EOL;
    
    // 2. Xóa Chi tiết hóa đơn
    echo "2. Xóa chi tiết hóa đơn...";
    $count = DB::table('chi_tiet_hoa_don')->delete();
    echo " ✓ Đã xóa {$count} dòng" . PHP_EOL;
    
    // 3. Xóa Hóa đơn khuyến mãi
    echo "3. Xóa hóa đơn khuyến mãi...";
    $count = DB::table('hoa_don_khuyen_mai')->delete();
    echo " ✓ Đã xóa {$count} dòng" . PHP_EOL;
    
    // 4. Xóa Hóa đơn
    echo "4. Xóa hóa đơn...";
    $count = DB::table('hoa_don')->delete();
    echo " ✓ Đã xóa {$count} dòng" . PHP_EOL;
    
    // 5. Xóa Chi tiết lô hàng
    echo "5. Xóa chi tiết lô hàng...";
    $count = DB::table('chi_tiet_lo_hang')->delete();
    echo " ✓ Đã xóa {$count} dòng" . PHP_EOL;
    
    // 6. Xóa Lô hàng
    echo "6. Xóa lô hàng...";
    $count = DB::table('lo_hang')->delete();
    echo " ✓ Đã xóa {$count} dòng" . PHP_EOL;
    
    // 7. Xóa Chi tiết phiếu
    echo "7. Xóa chi tiết phiếu...";
    $count = DB::table('chi_tiet_phieu')->delete();
    echo " ✓ Đã xóa {$count} dòng" . PHP_EOL;
    
    // 8. Xóa Phiếu nhập
    echo "8. Xóa phiếu nhập...";
    $count = DB::table('phieu_nhap')->delete();
    echo " ✓ Đã xóa {$count} dòng" . PHP_EOL;
    
    // 9. Xóa Phiếu xuất
    echo "9. Xóa phiếu xuất...";
    $count = DB::table('phieu_xuat')->delete();
    echo " ✓ Đã xóa {$count} dòng" . PHP_EOL;
    
    // 10. Xóa Phiếu
    echo "10. Xóa phiếu...";
    $count = DB::table('phieu')->delete();
    echo " ✓ Đã xóa {$count} dòng" . PHP_EOL;
    
    // 11. Xóa Đơn vị quy đổi
    echo "11. Xóa đơn vị quy đổi...";
    $count = DB::table('don_vi_quy_doi')->delete();
    echo " ✓ Đã xóa {$count} dòng" . PHP_EOL;
    
    // 12. Xóa Biến thể sản phẩm
    echo "12. Xóa biến thể sản phẩm...";
    $count = DB::table('bien_the_san_pham')->delete();
    echo " ✓ Đã xóa {$count} dòng" . PHP_EOL;
    
    // 13. Xóa Sản phẩm
    echo "13. Xóa sản phẩm...";
    $count = DB::table('san_pham')->delete();
    echo " ✓ Đã xóa {$count} dòng" . PHP_EOL;
    
    // 14. Reset auto increment
    echo PHP_EOL . "14. Reset auto increment..." . PHP_EOL;
    $tables = [
        'san_pham',
        'bien_the_san_pham',
        'don_vi_quy_doi',
        'phieu',
        'phieu_nhap',
        'phieu_xuat',
        'chi_tiet_phieu',
        'lo_hang',
        'chi_tiet_lo_hang',
        'hoa_don',
        'chi_tiet_hoa_don',
        'hoa_don_khuyen_mai',
        'thanh_toan_hoa_don'
    ];
    
    foreach ($tables as $table) {
        DB::statement("ALTER TABLE {$table} AUTO_INCREMENT = 1");
        echo "   - {$table} ✓" . PHP_EOL;
    }
    
    DB::commit();
    
    echo PHP_EOL . "=== HOÀN TẤT! ===" . PHP_EOL;
    echo "Tất cả dữ liệu test đã được xóa sạch." . PHP_EOL;
    echo "Bạn có thể nhập lại dữ liệu mới với code đã fix." . PHP_EOL;
    
} catch (\Exception $e) {
    DB::rollBack();
    echo PHP_EOL . "❌ LỖI: " . $e->getMessage() . PHP_EOL;
    exit(1);
}
