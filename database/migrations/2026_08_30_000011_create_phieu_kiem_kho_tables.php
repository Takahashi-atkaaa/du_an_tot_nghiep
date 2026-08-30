<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('phieu_kiem_kho', function (Blueprint $table) {
            $table->id();
            $table->string('ma_kiem_kho', 50)->unique()->comment('Mã phiếu kiểm kho, vd: KK00001');
            $table->unsignedBigInteger('id_nguoi_tao')->nullable();
            $table->unsignedBigInteger('id_nguoi_kiem')->nullable();
            $table->unsignedBigInteger('id_nguoi_duyet')->nullable();
            $table->unsignedBigInteger('id_chia_ca_lam_viec')->nullable();

            // Pham vi kiem kho
            $table->enum('pham_vi', ['toan_bo', 'theo_danh_muc', 'chon_san_pham'])->default('toan_bo');
            $table->unsignedBigInteger('id_danh_muc')->nullable();
            $table->json('variant_ids')->nullable()->comment('Danh sách variant_id khi pham_vi = chon_san_pham');

            $table->date('ngay_kiem')->nullable();

            // Trang thai - 7 trang thai moi
            $table->enum('trang_thai', [
                'phieu_tam',     // draft
                'counting',      // dang dem
                'cho_duyet',     // pending_approval
                'da_duyet',      // approved
                'hoan_thanh',    // completed
                'tu_choi',       // rejected
                'da_huy',        // cancelled
            ])->default('phieu_tam');

            $table->integer('tong_so_san_pham')->default(0)->comment('Tổng số biến thể được kiểm');
            $table->integer('tong_sl_thuc_te')->default(0)->comment('Tổng SL thực tế');
            $table->integer('tong_sl_he_thong')->default(0)->comment('Tổng SL hệ thống tại thời điểm tạo phiếu');
            $table->integer('tong_sl_lech')->default(0)->comment('Tổng SL lệch (âm = mất, dương = dư)');
            $table->integer('so_sp_thieu')->default(0);
            $table->integer('so_sp_thua')->default(0);
            $table->integer('so_sp_dung')->default(0);
            $table->decimal('tong_gia_tri_lech', 15, 2)->default(0)->comment('Tổng giá trị lệch (tính theo gia_von)');

            $table->text('ghi_chu')->nullable();
            $table->text('ly_do_huy')->nullable();
            $table->text('ly_do_tu_choi')->nullable();

            $table->timestamp('bat_dau_luc')->nullable();
            $table->timestamp('hoan_tat_dem_luc')->nullable();
            $table->timestamp('duyet_luc')->nullable();
            $table->timestamp('hoan_thanh_luc')->nullable();
            $table->timestamp('huy_luc')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('id_nguoi_tao')->references('id')->on('nguoi_dung')->nullOnDelete();
            $table->foreign('id_nguoi_kiem')->references('id')->on('nguoi_dung')->nullOnDelete();
            $table->foreign('id_nguoi_duyet')->references('id')->on('nguoi_dung')->nullOnDelete();
            $table->foreign('id_chia_ca_lam_viec')->references('id')->on('chia_ca_lam_viec')->nullOnDelete();

            $table->index(['trang_thai', 'id_nguoi_tao']);
            $table->index('created_at');
        });

        Schema::create('chi_tiet_kiem_kho', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_phieu_kiem_kho');
            $table->unsignedBigInteger('variant_id')->nullable();

            // Snapshot thong tin de hien thi on dinh
            $table->string('ma_vach', 100)->nullable();
            $table->string('ma_hang', 100)->nullable();
            $table->string('ten_san_pham', 255)->nullable();
            $table->string('ten_bien_the', 255)->nullable();
            $table->string('ten_don_vi', 100)->nullable();
            $table->date('han_su_dung_gan_nhat')->nullable();
            $table->integer('so_lo_con_ton')->default(0)->comment('Số lô còn tồn của biến thể');

            // So lieu kiem
            $table->integer('so_luong_he_thong')->default(0)->comment('Tổng tồn hệ thống lúc tạo phiếu (snapshot)');
            $table->integer('so_luong_thuc_te')->nullable()->comment('SL thực tế nhân viên đếm (null = chưa kiểm)');
            $table->integer('so_luong_lech')->default(0)->comment('Computed: thuc_te - he_thong');
            $table->decimal('gia_von', 14, 2)->default(0)->comment('Giá vốn trung bình tại thời điểm kiểm (snapshot)');
            $table->decimal('gia_tri_lech', 14, 2)->default(0)->comment('Computed: sl_lech * gia_von');

            $table->text('ly_do')->nullable();
            $table->text('ghi_chu')->nullable();

            $table->timestamp('dem_luc')->nullable();
            $table->unsignedBigInteger('id_nguoi_dem')->nullable();

            $table->timestamps();

            $table->foreign('id_phieu_kiem_kho')
                ->references('id')->on('phieu_kiem_kho')
                ->cascadeOnDelete();
            $table->foreign('variant_id')
                ->references('id')->on('bien_the_san_pham')
                ->nullOnDelete();
            $table->foreign('id_nguoi_dem')
                ->references('id')->on('nguoi_dung')
                ->nullOnDelete();

            $table->index(['id_phieu_kiem_kho']);
            $table->index('variant_id');
            // 1 phieu kiem khong the co 2 dong cho cung 1 bien the
            $table->unique(['id_phieu_kiem_kho', 'variant_id'], 'chi_tiet_kiem_phieu_variant_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chi_tiet_kiem_kho');
        Schema::dropIfExists('phieu_kiem_kho');
    }
};