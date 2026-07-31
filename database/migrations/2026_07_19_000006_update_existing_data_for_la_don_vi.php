<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Cập nhật dữ liệu cũ: các bien_the có ten_bien_the là tên đơn vị đơn lẻ
        // (thường là 'Lon', 'Chai', 'Hộp', 'Túi'...) → đánh dấu la_don_vi = true

        // Các tên đơn vị phổ biến
        $donViNames = ['Lon', 'Chai', 'Hộp', 'Túi', 'Lọ', 'Cái', 'Quả', 'Gói', 'Bịch', 'Cốc', 'Ly', 'Đôi', 'Chiếc', 'Que'];

        foreach ($donViNames as $tenDonVi) {
            DB::table('bien_the_san_pham')
                ->where('ten_bien_the', $tenDonVi)
                ->where('la_don_vi', null)
                ->update([
                    'la_don_vi' => true,
                    'ten_don_vi' => $tenDonVi,
                    'ten_bien_the' => null,
                ]);
        }

        // Cập nhật các bien_the có ten_bien_the ngắn (<=10 ký tự) và không phải màu/size phổ biến
        // Đây là đơn vị đơn lẻ
        $shortNames = DB::table('bien_the_san_pham')
            ->whereNull('la_don_vi')
            ->whereNotNull('ten_bien_the')
            ->whereRaw('LENGTH(ten_bien_the) <= 10')
            ->pluck('ten_bien_the')
            ->unique();

        foreach ($shortNames as $ten) {
            // Kiểm tra xem có phải là thuộc tính không (chứa dấu "-")
            if (strpos($ten, '-') === false) {
                DB::table('bien_the_san_pham')
                    ->where('ten_bien_the', $ten)
                    ->whereNull('la_don_vi')
                    ->update([
                        'la_don_vi' => true,
                        'ten_don_vi' => $ten,
                        'ten_bien_the' => null,
                    ]);
            }
        }
    }

    public function down(): void
    {
        // Khôi phục: chuyển ten_don_vi về ten_bien_the và reset la_don_vi
        DB::table('bien_the_san_pham')
            ->where('la_don_vi', true)
            ->update([
                'ten_bien_the' => DB::raw('ten_don_vi'),
                'la_don_vi' => false,
                'ten_don_vi' => null,
            ]);
    }
};
