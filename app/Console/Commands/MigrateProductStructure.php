<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\Product;
use App\Models\BienTheSanPham;
use App\Models\DonViQuyDoi;

class MigrateProductStructure extends Command
{
    protected $signature = 'products:migrate-structure {--fresh : Xoa tat ca du lieu cu truoc khi migrate}';

    protected $description = 'Migrate product data from old single-table structure to 3-table model';

    public function handle(): int
    {
        if (!DB::getSchemaBuilder()->hasTable('bien_the_san_pham')) {
            $this->error('Vui long chay migration truoc: php artisan migrate');
            return 1;
        }

        if ($this->option('fresh')) {
            BienTheSanPham::truncate();
            DonViQuyDoi::truncate();
            Product::withTrashed()->update(['deleted_at' => null]);
            $this->info('Da xoa du lieu cu (fresh mode).');
        }

        // Kiểm tra cấu trúc bảng cũ còn không
        $columns = DB::getSchemaBuilder()->getColumnListing('san_pham');
        $hasChaId = in_array('san_pham_cha_id', $columns);
        $hasLaCha = in_array('la_san_pham_cha', $columns);

        // Đếm tổng items cần xử lý
        if ($hasChaId && $hasLaCha) {
            $total = DB::table('san_pham')->count();
        } else {
            // Cấu trúc đã được cleanup, mỗi san_pham là 1 product
            $total = DB::table('san_pham')->count();
        }

        $this->info('Bat dau migrate du lieu san pham...');
        $bar = $this->output->createProgressBar($total);
        $bar->start();

        $count = 0;
        $errors = collect();

        DB::transaction(function () use (&$count, &$errors, $bar, $columns, $hasChaId, $hasLaCha) {

            if ($hasChaId && $hasLaCha) {
                // Cấu trúc CŨ: có san_pham_cha_id và la_san_pham_cha
                $this->migrateOldStructure($columns, $bar, $count, $errors);
            } else {
                // Cấu trúc ĐÃ CLEANUP: mỗi san_pham là 1 product, tạo 1 variant
                $this->migrateNewStructure($bar, $count, $errors);
            }
        });

        $bar->finish();
        $this->newLine(2);

        $this->info("Migrate hoan tat: {$count} variants da tao.");

        if ($errors->isNotEmpty()) {
            $this->warn("Co {$errors->count()} loi:");
            foreach ($errors as $err) {
                $this->line("  - {$err}");
            }
        }

        $dangThieu = DB::table('chi_tiet_lo_hang')->whereNull('variant_id')->count();
        $phieuDangThieu = DB::table('chi_tiet_phieu')->whereNull('variant_id')->count();

        if ($dangThieu > 0) {
            $this->warn("Con {$dangThieu} chi_tiet_lo_hang chua co variant_id (can xu ly thu cong).");
        }
        if ($phieuDangThieu > 0) {
            $this->warn("Con {$phieuDangThieu} chi_tiet_phieu chua co variant_id (can xu ly thu cong).");
        }

        $this->newLine();
        $this->info('Sau khi xac nhan du lieu dung, chay lenh sau de xoa cot id_san_pham cu:');
        $this->line('  php artisan products:cleanup-variant-id --force');

        return 0;
    }

    private function migrateOldStructure(array $columns, $bar, &$count, &$errors)
    {
        // Lấy tất cả sản phẩm cha
        $sanPhamChas = DB::table('san_pham')
            ->where('la_san_pham_cha', true)
            ->get();

        // Lấy tất cả sản phẩm con
        $sanPhamCons = DB::table('san_pham')
            ->whereNotNull('san_pham_cha_id')
            ->get();

        // Group con theo cha
        $conGrouped = $sanPhamCons->groupBy('san_pham_cha_id');

        foreach ($sanPhamChas as $cha) {
            try {
                $cacBienTheCu = $conGrouped->get($cha->id, collect());

                if ($cacBienTheCu->isNotEmpty()) {
                    foreach ($cacBienTheCu as $bienTheCu) {
                        $variant = BienTheSanPham::create([
                            'product_id' => $cha->id,
                            'ten_bien_the' => $bienTheCu->ten_san_pham,
                            'ma_hang' => $bienTheCu->ma_hang ?? null,
                            'ma_vach' => $bienTheCu->ma_vach ?? null,
                            'gia_von' => $bienTheCu->gia_von ?? 0,
                            'gia_ban' => $bienTheCu->gia_ban ?? 0,
                            'so_luong_ton' => $bienTheCu->so_luong_ton_kho ?? 0,
                            'hinh_anh' => $bienTheCu->hinh_anh ?? null,
                            'thuoc_tinh_ids' => null,
                            'trang_thai' => (bool) ($bienTheCu->trang_thai ?? true),
                        ]);

                        DB::table('chi_tiet_lo_hang')
                            ->where('id_san_pham', $bienTheCu->id)
                            ->update(['variant_id' => $variant->id]);

                        DB::table('chi_tiet_phieu')
                            ->where('id_san_pham', $bienTheCu->id)
                            ->update(['variant_id' => $variant->id]);

                        // Migrate units cũ
                        $cacDonViCu = DB::table('don_vi_san_pham_san_pham')
                            ->where('id_san_pham', $bienTheCu->id)
                            ->get();

                        foreach ($cacDonViCu as $donViCu) {
                            DonViQuyDoi::create([
                                'variant_id' => $variant->id,
                                'ten_don_vi' => $donViCu->ten_don_vi,
                                'ty_le_quy_doi' => $donViCu->so_luong_quy_doi ?? 1,
                                'ma_hang' => 'DU' . $donViCu->id_don_vi . 'V' . $variant->id,
                                'ma_vach' => $donViCu->ma_vach ?? null,
                                'gia_von_quy_doi' => $bienTheCu->gia_von ?? 0,
                                'gia_ban_quy_doi' => $donViCu->gia_ban_le ?? 0,
                                'gia_ban_si' => $donViCu->gia_ban_si ?? null,
                                'hinh_anh' => $donViCu->hinh_anh ?? null,
                                'la_don_vi_mac_dinh' => (bool) ($donViCu->la_don_vi_mac_dinh ?? false),
                            ]);
                        }

                        $count++;
                        $bar->advance();
                    }
                } else {
                    $variant = BienTheSanPham::create([
                        'product_id' => $cha->id,
                        'ten_bien_the' => null,
                        'ma_hang' => $cha->ma_hang ?? null,
                        'ma_vach' => $cha->ma_vach ?? null,
                        'gia_von' => $cha->gia_von ?? 0,
                        'gia_ban' => $cha->gia_ban ?? 0,
                        'so_luong_ton' => $cha->so_luong_ton_kho ?? 0,
                        'hinh_anh' => $cha->hinh_anh ?? null,
                        'thuoc_tinh_ids' => null,
                        'trang_thai' => (bool) ($cha->trang_thai ?? true),
                    ]);

                    DB::table('chi_tiet_lo_hang')
                        ->where('id_san_pham', $cha->id)
                        ->update(['variant_id' => $variant->id]);

                    DB::table('chi_tiet_phieu')
                        ->where('id_san_pham', $cha->id)
                        ->update(['variant_id' => $variant->id]);

                    $count++;
                    $bar->advance();
                }
            } catch (\Exception $e) {
                $errors->push("Loi voi san pham ID {$cha->id}: " . $e->getMessage());
                $bar->advance();
            }
        }

        // Orphan: con không có cha
        $chaIds = $sanPhamChas->pluck('id')->toArray();
        $orphanCons = $sanPhamCons->filter(fn($item) => !in_array($item->san_pham_cha_id, $chaIds));

        foreach ($orphanCons as $orphan) {
            try {
                $product = Product::find($orphan->san_pham_cha_id);
                if (!$product) {
                    $product = Product::create([
                        'id_danh_muc' => $orphan->id_danh_muc,
                        'ten_san_pham' => $orphan->ten_san_pham,
                        'thuong_hieu' => $orphan->thuong_hieu ?? null,
                        'mo_ta' => $orphan->mo_ta ?? null,
                        'trang_thai' => (bool) ($orphan->trang_thai ?? true),
                    ]);
                }

                $variant = BienTheSanPham::create([
                    'product_id' => $product->id,
                    'ten_bien_the' => $orphan->ten_san_pham,
                    'ma_hang' => $orphan->ma_hang ?? null,
                    'ma_vach' => $orphan->ma_vach ?? null,
                    'gia_von' => $orphan->gia_von ?? 0,
                    'gia_ban' => $orphan->gia_ban ?? 0,
                    'so_luong_ton' => $orphan->so_luong_ton_kho ?? 0,
                    'hinh_anh' => $orphan->hinh_anh ?? null,
                    'thuoc_tinh_ids' => null,
                    'trang_thai' => (bool) ($orphan->trang_thai ?? true),
                ]);

                DB::table('chi_tiet_lo_hang')
                    ->where('id_san_pham', $orphan->id)
                    ->update(['variant_id' => $variant->id]);
                DB::table('chi_tiet_phieu')
                    ->where('id_san_pham', $orphan->id)
                    ->update(['variant_id' => $variant->id]);

                $count++;
                $bar->advance();
            } catch (\Exception $e) {
                $errors->push("Loi voi orphan ID {$orphan->id}: " . $e->getMessage());
                $bar->advance();
            }
        }
    }

    private function migrateNewStructure($bar, &$count, &$errors)
    {
        // Cấu trúc đã được cleanup: mỗi san_pham là 1 product
        // Tạo 1 variant cho mỗi product
        $sanPhams = DB::table('san_pham')->get();

        foreach ($sanPhams as $sp) {
            try {
                $variant = BienTheSanPham::create([
                    'product_id' => $sp->id,
                    'ten_bien_the' => null,
                    'ma_hang' => $sp->ma_hang ?? null,
                    'ma_vach' => $sp->ma_vach ?? null,
                    'gia_von' => $sp->gia_von ?? 0,
                    'gia_ban' => $sp->gia_ban ?? 0,
                    'so_luong_ton' => $sp->so_luong_ton_kho ?? 0,
                    'hinh_anh' => $sp->hinh_anh ?? null,
                    'thuoc_tinh_ids' => null,
                    'trang_thai' => (bool) ($sp->trang_thai ?? true),
                ]);

                // Link chi_tiet_lo_hang
                DB::table('chi_tiet_lo_hang')
                    ->where('id_san_pham', $sp->id)
                    ->update(['variant_id' => $variant->id]);

                // Link chi_tiet_phieu
                DB::table('chi_tiet_phieu')
                    ->where('id_san_pham', $sp->id)
                    ->update(['variant_id' => $variant->id]);

                // Migrate units cũ nếu bảng pivot còn tồn tại
                if (DB::getSchemaBuilder()->hasTable('don_vi_san_pham_san_pham')) {
                    $cacDonViCu = DB::table('don_vi_san_pham_san_pham')
                        ->where('id_san_pham', $sp->id)
                        ->get();

                    foreach ($cacDonViCu as $donViCu) {
                        DonViQuyDoi::create([
                            'variant_id' => $variant->id,
                            'ten_don_vi' => $donViCu->ten_don_vi,
                            'ty_le_quy_doi' => $donViCu->so_luong_quy_doi ?? 1,
                            'ma_hang' => 'DU' . $donViCu->id_don_vi . 'V' . $variant->id,
                            'ma_vach' => $donViCu->ma_vach ?? null,
                            'gia_von_quy_doi' => $sp->gia_von ?? 0,
                            'gia_ban_quy_doi' => $donViCu->gia_ban_le ?? 0,
                            'gia_ban_si' => $donViCu->gia_ban_si ?? null,
                            'hinh_anh' => $donViCu->hinh_anh ?? null,
                            'la_don_vi_mac_dinh' => (bool) ($donViCu->la_don_vi_mac_dinh ?? false),
                        ]);
                    }
                }

                $count++;
                $bar->advance();
            } catch (\Exception $e) {
                $errors->push("Loi voi san pham ID {$sp->id}: " . $e->getMessage());
                $bar->advance();
            }
        }
    }
}
