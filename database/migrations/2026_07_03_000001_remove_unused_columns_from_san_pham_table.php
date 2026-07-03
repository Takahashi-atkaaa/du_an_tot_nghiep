<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Chuẩn hóa bảng san_pham: xóa các cột thuộc cấp biến thể (variant / unit)
 * mà ta KHÔNG còn dùng ở cấp sản phẩm cha nữa.
 *
 * Sau migration này, bảng `san_pham` chỉ còn các cột thông tin chung:
 *   - id
 *   - id_danh_muc
 *   - ten_san_pham
 *   - thuong_hieu
 *   - mo_ta
 *   - trang_thai
 *   - created_at
 *   - updated_at
 *   - deleted_at (softDeletes)
 *
 * Mọi dữ liệu về giá, tồn kho, mã vạch, mã hàng, đơn vị, loại biến thể,
 * quan hệ cha-con... được lưu ở bien_the_san_pham (variants) và don_vi_quy_doi (units).
 */
return new class extends Migration
{
    public function up(): void
    {
        // Trước khi drop, gỡ các ràng buộc (unique, foreign key, index) nếu còn.
        // Dùng DB::statement vì có thể 1 số ràng buộc đã bị migration trước drop,
        // ta chỉ cần "thử" xóa an toàn, có lỗi thì bỏ qua.

        $dropUnique = function (string $table, string $indexName): void {
            try {
                DB::statement("ALTER TABLE `{$table}` DROP INDEX `{$indexName}`");
            } catch (\Throwable $e) {
                // index đã không tồn tại → bỏ qua
            }
        };

        $dropForeign = function (string $table, string $column): void {
            try {
                Schema::table($table, function (Blueprint $t) use ($column) {
                    $t->dropForeign([$column]);
                });
            } catch (\Throwable $e) {
                // FK đã không tồn tại → bỏ qua
            }
        };

        // Gỡ FK & index cũ trước khi xóa cột
        $dropForeign('san_pham', 'san_pham_cha_id');
        $dropForeign('san_pham', 'id_don_vi');
        $dropUnique('san_pham', 'san_pham_ma_vach_unique');
        $dropUnique('san_pham', 'san_pham_ma_hang_unique');

        // Sau đó drop các cột không còn dùng ở cấp sản phẩm cha.
        // Dùng Schema::hasColumn để migration an toàn nếu đã được chạy trước đó.
        Schema::table('san_pham', function (Blueprint $table) {
            $columnsToDrop = [
                'ma_hang',
                'ma_vach',
                'gia_von',
                'gia_ban',
                'gia_ban_si',
                'so_luong_ton_kho',
                'dinh_muc_toi_thieu',
                'id_don_vi',
                'san_pham_cha_id',
                'loai_bien_the',
                'la_san_pham_cha',
            ];

            $existing = array_filter($columnsToDrop, fn($col) => Schema::hasColumn('san_pham', $col));
            if (!empty($existing)) {
                $table->dropColumn($existing);
            }
        });
    }

    public function down(): void
    {
        // Rollback: tạo lại các cột (kiểu dữ liệu khớp với migration gốc).
        Schema::table('san_pham', function (Blueprint $table) {
            if (!Schema::hasColumn('san_pham', 'ma_hang')) {
                $table->string('ma_hang')->nullable()->after('ten_san_pham');
            }
            if (!Schema::hasColumn('san_pham', 'ma_vach')) {
                $table->string('ma_vach')->nullable()->after('ma_hang');
            }
            if (!Schema::hasColumn('san_pham', 'gia_von')) {
                $table->decimal('gia_von', 14, 2)->default(0)->after('thuong_hieu');
            }
            if (!Schema::hasColumn('san_pham', 'gia_ban')) {
                $table->decimal('gia_ban', 14, 2)->default(0)->after('gia_von');
            }
            if (!Schema::hasColumn('san_pham', 'gia_ban_si')) {
                $table->decimal('gia_ban_si', 14, 2)->nullable()->after('gia_ban');
            }
            if (!Schema::hasColumn('san_pham', 'so_luong_ton_kho')) {
                $table->integer('so_luong_ton_kho')->default(0)->after('gia_ban_si');
            }
            if (!Schema::hasColumn('san_pham', 'dinh_muc_toi_thieu')) {
                $table->integer('dinh_muc_toi_thieu')->default(0)->after('so_luong_ton_kho');
            }
            if (!Schema::hasColumn('san_pham', 'id_don_vi')) {
                $table->foreignId('id_don_vi')->nullable()->after('mo_ta')
                    ->constrained('don_vi_san_pham')->nullOnDelete();
            }
            if (!Schema::hasColumn('san_pham', 'san_pham_cha_id')) {
                $table->foreignId('san_pham_cha_id')->nullable()->after('trang_thai')
                    ->constrained('san_pham')->nullOnDelete();
            }
            if (!Schema::hasColumn('san_pham', 'la_san_pham_cha')) {
                $table->boolean('la_san_pham_cha')->default(false)->after('san_pham_cha_id');
            }
            if (!Schema::hasColumn('san_pham', 'loai_bien_the')) {
                $table->string('loai_bien_the')->nullable()->after('la_san_pham_cha');
            }
        });
    }
};