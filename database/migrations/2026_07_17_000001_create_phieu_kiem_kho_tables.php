<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // ===========================================================
        // 1. Mở rộng enum loai_nhap của phieu_nhap để hỗ trợ "kiểm kê"
        // ===========================================================
        // MySQL enum cần ALTER + MODIFY. Với MariaDB tương tự.
        // Dùng try-catch để tương thích với các DB khác.
        try {
            DB::statement("ALTER TABLE `phieu_nhap` MODIFY COLUMN `loai_nhap` ENUM('mua_hang','tra_lai_tu_khach','kiem_ke') DEFAULT 'mua_hang'");
        } catch (\Exception $e) {
            // ignore
        }

        try {
            DB::statement("ALTER TABLE `phieu` MODIFY COLUMN `loai_phieu_enum` ENUM(
                'nhap_mua_hang',
                'nhap_tra_lai_tu_khach',
                'nhap_kiem_ke',
                'xuat_tra_hang_nha_cung_cap',
                'xuat_tieu_huy'
            ) NULL");
        } catch (\Exception $e) {
            // ignore
        }

        // ===========================================================
        // 2. Tạo bảng phieu_kiem_kho (phiếu kiểm kho)
        // ===========================================================
        Schema::create('phieu_kiem_kho', function (Blueprint $table) {
            $table->id();
            $table->string('ma_kiem_kho', 50)->unique()->comment('Mã phiếu kiểm kho, vd: KK00001');
            $table->unsignedBigInteger('id_chia_ca_lam_viec')->nullable();
            $table->unsignedBigInteger('id_nguoi_dung');
            $table->enum('trang_thai', ['phieu_tam', 'hoan_thanh', 'da_huy'])->default('phieu_tam');
            $table->integer('tong_sl_thuc_te')->default(0)->comment('Tổng SL thực tế nhân viên đếm được');
            $table->integer('tong_sl_lech')->default(0)->comment('Tổng SL lệch (âm = mất, dương = dư)');
            $table->decimal('tong_gia_tri_lech', 15, 2)->default(0)->comment('Tổng giá trị lệch (tính theo gia_von)');
            $table->text('ghi_chu')->nullable();
            $table->timestamp('hoan_thanh_luc')->nullable()->comment('Thời điểm cân bằng kho');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('id_nguoi_dung')
                ->references('id')->on('nguoi_dung')
                ->cascadeOnDelete();
            $table->foreign('id_chia_ca_lam_viec')
                ->references('id')->on('chia_ca_lam_viec')
                ->nullOnDelete();

            $table->index(['trang_thai', 'id_nguoi_dung']);
            $table->index('created_at');
        });

        // ===========================================================
        // 3. Tạo bảng chi_tiet_kiem_kho (chi tiết từng lô được kiểm)
        // ===========================================================
        Schema::create('chi_tiet_kiem_kho', function (Blueprint $table) {
            $table->id();

            // FK tới phiếu
            $table->unsignedBigInteger('id_phieu_kiem_kho');

            // FK tới biến thể sản phẩm
            $table->unsignedBigInteger('variant_id')->nullable();

            // FK tới chi tiết lô hàng cụ thể
            $table->unsignedBigInteger('id_chi_tiet_lo_hang');

            // Snapshot thông tin để hiển thị ổn định
            $table->string('ma_vach', 100)->nullable();
            $table->string('ten_san_pham', 255)->nullable();
            $table->string('ten_bien_the', 255)->nullable();
            $table->string('ten_don_vi', 100)->nullable();
            $table->date('han_su_dung')->nullable();
            $table->string('ma_lo', 100)->nullable();

            // Các số liệu kiểm
            $table->integer('so_luong_ton')->default(0)->comment('Tồn kho trên hệ thống lúc kiểm');
            $table->integer('so_luong_thuc_te')->nullable()->comment('SL thực tế nhân viên đếm (null = chưa kiểm)');
            $table->integer('so_luong_lech')->default(0)->comment('Computed: thuc_te - ton');
            $table->decimal('gia_von', 14, 2)->default(0)->comment('Giá vốn tại thời điểm kiểm (snapshot)');
            $table->decimal('gia_tri_lech', 14, 2)->default(0)->comment('Computed: sl_lech * gia_von');

            $table->timestamps();

            $table->foreign('id_phieu_kiem_kho')
                ->references('id')->on('phieu_kiem_kho')
                ->cascadeOnDelete();
            $table->foreign('variant_id')
                ->references('id')->on('bien_the_san_pham')
                ->nullOnDelete();
            $table->foreign('id_chi_tiet_lo_hang')
                ->references('id')->on('chi_tiet_lo_hang')
                ->cascadeOnDelete();

            $table->index(['id_phieu_kiem_kho']);
            $table->index('id_chi_tiet_lo_hang');
            // 1 phiếu kiểm không thể có 2 dòng cho cùng 1 lô
            $table->unique(['id_phieu_kiem_kho', 'id_chi_tiet_lo_hang'], 'chi_tiet_kiem_phieu_lo_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chi_tiet_kiem_kho');
        Schema::dropIfExists('phieu_kiem_kho');

        try {
            DB::statement("ALTER TABLE `phieu_nhap` MODIFY COLUMN `loai_nhap` ENUM('mua_hang','tra_lai_tu_khach') DEFAULT 'mua_hang'");
        } catch (\Exception $e) {
            // ignore
        }

        try {
            DB::statement("ALTER TABLE `phieu` MODIFY COLUMN `loai_phieu_enum` ENUM(
                'nhap_mua_hang',
                'nhap_tra_lai_tu_khach',
                'xuat_tra_hang_nha_cung_cap',
                'xuat_tieu_huy'
            ) NULL");
        } catch (\Exception $e) {
            // ignore
        }
    }
};
