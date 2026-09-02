<?php

namespace Database\Seeders;

use App\Models\VaiTro;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PhanQuyenSeeder extends Seeder
{
    public function run(): void
    {
        $admin = VaiTro::where('ten_vai_tro', 'Admin')->first();
        $truongCa = VaiTro::where('ten_vai_tro', 'Trưởng ca')->first();
        $banHang = VaiTro::where('ten_vai_tro', 'Bán hàng')->first();

        $allQuyens = DB::table('quyen')->pluck('id', 'ma_quyen');

        // Admin luôn giữ toàn quyền; không xóa quyền đã cấp thủ công.
        if ($admin) {
            $admin->quyens()->syncWithoutDetaching($allQuyens->values()->all());
        }

        // Trưởng ca chỉ nhận bộ quyền trong catalog. Các quyền xóa, hủy,
        // điều chỉnh tồn kho và quản trị hệ thống không nằm trong bộ mặc định.
        if ($truongCa) {
            $truongCaIds = collect(config('permissions.truong_ca', []))
                ->map(fn (string $code) => $allQuyens->get($code))
                ->filter()
                ->values()
                ->all();
            $truongCa->quyens()->sync($truongCaIds);
        }

        // Bán hàng
        if ($banHang) {
            $banHangIds = collect(['ban_hang', 'quan_ly_khach_hang'])
                ->map(fn (string $code) => $allQuyens->get($code))
                ->filter()
                ->values()
                ->all();
            $banHang->quyens()->syncWithoutDetaching($banHangIds);
        }
    }
}
