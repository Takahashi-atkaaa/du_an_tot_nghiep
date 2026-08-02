<?php

namespace Tests\Feature;

use App\Exports\PhieuNhapDanhSachExport;
use App\Exports\PhieuXuatDanhSachExport;
use App\Models\ChiTietPhieu;
use App\Models\LoHang;
use App\Models\NguoiDung;
use App\Models\Phieu;
use App\Models\PhieuNhap;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Test filter va export danh sach phieu nhap.
 *
 * Kiem tra:
 *  - PhieuNhapDanhSachExport::query() chi lay data theo filters truyen vao.
 *  - PhieuNhapDanhSachExport KHONG tu query all() neu filters empty -> van tra query builder (OK).
 *  - parseDateRange() chuan hoa tu_ngay ve 00:00:00, den_ngay ve 23:59:59.
 *  - parseDateRange() tra 422 neu tu_ngay > den_ngay.
 *  - Endpoint /admin/api/phieu-nhap/export nhan query va tra ve download file xlsx.
 */
class PhieuNhapExportFilterTest extends TestCase
{
    /**
     * Override RefreshDatabase de khong goi migrate:fresh (do MySQL-only migrations).
     * Schema se duoc tao thu cong trong setUp().
     */
    protected function refreshTestDatabase(): void
    {
        // No-op
    }

    private NguoiDung $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->buildSchema();
        $this->user = NguoiDung::create([
            'ho_ten' => 'Admin',
            'email' => 'admin@test.local',
            'mat_khau' => 'secret',
            'trang_thai' => 1,
        ]);
    }

    private function buildSchema(): void
    {
        Schema::create('nguoi_dung', function ($t) {
            $t->id();
            $t->string('ho_ten')->nullable();
            $t->string('email')->nullable();
            $t->string('mat_khau')->nullable();
            $t->boolean('trang_thai')->default(1);
            $t->timestamps();
            $t->softDeletes();
        });

        Schema::create('phieu', function ($t) {
            $t->id();
            $t->string('loai_phieu');
            $t->string('loai_phieu_enum')->nullable();
            $t->unsignedBigInteger('id_nguoi_dung')->nullable();
            $t->unsignedBigInteger('id_nha_cung_cap')->nullable();
            $t->unsignedBigInteger('id_hoa_don')->nullable();
            $t->text('ghi_chu')->nullable();
            $t->timestamps();
            $t->softDeletes();
        });

        Schema::create('phieu_nhap', function ($t) {
            $t->id();
            $t->unsignedBigInteger('id_phieu');
            $t->string('loai_nhap');
            $t->unsignedBigInteger('id_hoa_don')->nullable();
            $t->unsignedBigInteger('id_phieu_xuat_goc')->nullable();
            $t->text('ghi_chu')->nullable();
            $t->timestamps();
            $t->softDeletes();
        });

        Schema::create('chi_tiet_phieu', function ($t) {
            $t->id();
            $t->unsignedBigInteger('id_phieu');
            $t->unsignedBigInteger('id_san_pham');
            $t->unsignedBigInteger('variant_id')->nullable();
            $t->unsignedBigInteger('id_lo_hang')->nullable();
            $t->unsignedBigInteger('id_chi_tiet_lo_hang')->nullable();
            $t->integer('so_luong')->default(0);
            $t->decimal('gia_nhap', 14, 2)->default(0);
            $t->string('ma_lo')->nullable();
            $t->date('han_su_dung')->nullable();
            $t->integer('so_luong_con_lai')->default(0);
            $t->text('ghi_chu')->nullable();
            $t->timestamps();
            $t->softDeletes();
        });

        Schema::create('nha_cung_cap', function ($t) {
            $t->id();
            $t->string('ten_nha_cung_cap');
            $t->timestamps();
        });

        Schema::create('phieu_xuat', function ($t) {
            $t->id();
            $t->unsignedBigInteger('id_phieu');
            $t->string('loai_xuat');
            $t->unsignedBigInteger('id_hoa_don')->nullable();
            $t->unsignedBigInteger('id_phieu_nhap_goc')->nullable();
            $t->string('ly_do')->nullable();
            $t->text('ghi_chu')->nullable();
            $t->timestamps();
            $t->softDeletes();
        });
    }

    private function taoPhieu(array $opts = []): PhieuNhap
    {
        $ngay = $opts['created_at'] ?? now();
        $phieu = Phieu::create([
            'loai_phieu' => 'Nhập hàng',
            'loai_phieu_enum' => 'nhap_mua_hang',
            'id_nguoi_dung' => $this->user->id,
            'id_nha_cung_cap' => null,
        ]);
        // Set created_at thu cong (vi fillable khong bao gom)
        DB::table('phieu')->where('id', $phieu->id)->update(['created_at' => $ngay, 'updated_at' => $ngay]);

        $phieuNhap = PhieuNhap::create([
            'id_phieu' => $phieu->id,
            'loai_nhap' => $opts['loai_nhap'] ?? 'mua_hang',
        ]);
        DB::table('phieu_nhap')->where('id', $phieuNhap->id)->update(['created_at' => $ngay, 'updated_at' => $ngay]);

        // Tao it nhat 1 chi_tiet_phieu de controller khong crash khi eager load
        ChiTietPhieu::create([
            'id_phieu' => $phieu->id,
            'id_san_pham' => 1,
            'so_luong' => 10,
            'gia_nhap' => 1000,
        ]);

        return $phieuNhap;
    }

    /**
     * Test: filters empty -> query tra ve TAT CA phieu nhap.
     */
    public function test_export_query_khong_filter_tra_ve_tat_ca(): void
    {
        $this->taoPhieu(['created_at' => Carbon::parse('2026-01-01 10:00:00')]);
        $this->taoPhieu(['created_at' => Carbon::parse('2026-03-01 10:00:00')]);
        $this->taoPhieu(['created_at' => Carbon::parse('2026-06-01 10:00:00')]);

        $export = new PhieuNhapDanhSachExport([]);
        $count = $export->query()->count();
        $this->assertSame(3, $count);
    }

    /**
     * Test: filter tu_ngay + den_ngay chi lay phieu trong khoang.
     */
    public function test_export_query_loc_theo_tu_ngay_den_ngay(): void
    {
        $this->taoPhieu(['created_at' => Carbon::parse('2026-01-01 10:00:00')]); // ngoai khoang
        $this->taoPhieu(['created_at' => Carbon::parse('2026-03-05 10:00:00')]); // trong khoang
        $this->taoPhieu(['created_at' => Carbon::parse('2026-03-15 10:00:00')]); // trong khoang
        $this->taoPhieu(['created_at' => Carbon::parse('2026-04-01 10:00:00')]); // ngoai khoang

        $filters = [
            'tu_ngay' => Carbon::parse('2026-03-01')->startOfDay(),
            'den_ngay' => Carbon::parse('2026-03-31')->endOfDay(),
        ];

        $export = new PhieuNhapDanhSachExport($filters);
        $ids = $export->query()->pluck('id')->sort()->values()->all();
        $this->assertCount(2, $ids);
    }

    /**
     * Test: filter tu_ngay = '2026-03-05' -> lay tu 00:00:00 (bao gom phieu tao luc 10h cung ngay).
     */
    public function test_export_query_tu_ngay_lay_tu_dau_ngay(): void
    {
        $this->taoPhieu(['created_at' => Carbon::parse('2026-03-05 00:30:00')]); // bat dau ngay 5
        $this->taoPhieu(['created_at' => Carbon::parse('2026-03-04 23:59:59')]); // truoc 0h ngay 5

        $filters = [
            'tu_ngay' => Carbon::parse('2026-03-05')->startOfDay(),
            'den_ngay' => Carbon::parse('2026-03-05')->endOfDay(),
        ];

        $export = new PhieuNhapDanhSachExport($filters);
        $count = $export->query()->count();
        $this->assertSame(1, $count);
    }

    /**
     * Test: filter den_ngay = '2026-03-05' -> lay den cuoi ngay (23:59:59, bao gom phieu tao cuoi ngay).
     */
    public function test_export_query_den_ngay_lay_den_cuoi_ngay(): void
    {
        $this->taoPhieu(['created_at' => Carbon::parse('2026-03-05 23:30:00')]); // cuoi ngay 5
        $this->taoPhieu(['created_at' => Carbon::parse('2026-03-06 00:01:00')]); // ngay 6

        $filters = [
            'tu_ngay' => Carbon::parse('2026-03-05')->startOfDay(),
            'den_ngay' => Carbon::parse('2026-03-05')->endOfDay(),
        ];

        $export = new PhieuNhapDanhSachExport($filters);
        $count = $export->query()->count();
        $this->assertSame(1, $count);
    }

    /**
     * Test: filter loai_nhap = 'mua_hang' chi lay phieu mua hang.
     */
    public function test_export_query_loc_theo_loai_nhap(): void
    {
        $this->taoPhieu(['loai_nhap' => 'mua_hang']);
        $this->taoPhieu(['loai_nhap' => 'mua_hang']);
        $this->taoPhieu(['loai_nhap' => 'tra_lai_tu_khach']);

        $filters = ['loai_nhap' => 'mua_hang'];
        $export = new PhieuNhapDanhSachExport($filters);
        $count = $export->query()->count();
        $this->assertSame(2, $count);
    }

    /**
     * Test: endpoint /admin/api/phieu-nhap/export tra ve file xlsx.
     */
    public function test_endpoint_export_tra_ve_xlsx_file(): void
    {
        $this->taoPhieu(['created_at' => Carbon::parse('2026-03-05 10:00:00')]);

        $response = $this->actingAs($this->user)
            ->get('/admin/api/phieu-nhap/export?tu_ngay=2026-03-01&den_ngay=2026-03-31');

        // 200 + content-type xlsx (Maatwebsite tra ve BinaryFileResponse)
        $response->assertStatus(200);
        $this->assertStringContainsString(
            'spreadsheetml',
            $response->headers->get('content-type', ''),
            'Phai tra ve file xlsx'
        );
    }

    /**
     * Test: endpoint tra 422 neu tu_ngay > den_ngay.
     */
    public function test_endpoint_export_tu_ngay_sau_den_ngay_tra_422(): void
    {
        $response = $this->actingAs($this->user)
            ->get('/admin/api/phieu-nhap/export?tu_ngay=2026-12-31&den_ngay=2026-01-01');

        $response->assertStatus(422);
    }

    /**
     * Test: PhieuXuatDanhSachExport loc theo loai_xuat + khoang ngay.
     */
    public function test_phieu_xuat_export_query_loc_theo_loai_xuat(): void
    {
        // Tao phieu xuat tieu huy
        $phieuTieuHuy = Phieu::create([
            'loai_phieu' => 'Xuất hủy',
            'loai_phieu_enum' => 'xuat_tieu_huy',
            'id_nguoi_dung' => $this->user->id,
        ]);
        DB::table('phieu_xuat')->insert([
            'id_phieu' => $phieuTieuHuy->id,
            'loai_xuat' => 'tieu_huy',
            'created_at' => '2026-03-05 10:00:00',
            'updated_at' => '2026-03-05 10:00:00',
        ]);

        // Tao phieu xuat tra ncc
        $phieuTraNcc = Phieu::create([
            'loai_phieu' => 'Trả NCC',
            'loai_phieu_enum' => 'xuat_tra_ncc',
            'id_nguoi_dung' => $this->user->id,
        ]);
        DB::table('phieu_xuat')->insert([
            'id_phieu' => $phieuTraNcc->id,
            'loai_xuat' => 'tra_hang_nha_cung_cap',
            'created_at' => '2026-03-05 10:00:00',
            'updated_at' => '2026-03-05 10:00:00',
        ]);

        // Test filter
        $export = new \App\Exports\PhieuXuatDanhSachExport(['loai_xuat' => 'tieu_huy']);
        $count = $export->query()->count();
        $this->assertSame(1, $count, 'Phai chi tra ve 1 phieu tieu huy');
    }

    /**
     * Test: endpoint export phieu xuat tra file xlsx.
     */
    public function test_endpoint_phieu_xuat_export_tra_ve_xlsx(): void
    {
        $response = $this->actingAs($this->user)
            ->get('/admin/api/phieu-xuat/export?tu_ngay=2026-01-01&den_ngay=2026-12-31');

        $response->assertStatus(200);
        $this->assertStringContainsString(
            'spreadsheetml',
            $response->headers->get('content-type', ''),
            'Phai tra ve file xlsx'
        );
    }

    /**
     * Test: helper formatVnd format dung chuan VNĐ (dau phay, hau to ' d').
     */
    public function test_format_vnd_helper(): void
    {
        $this->assertSame('40,000 đ', PhieuNhapDanhSachExport::formatVnd(40000));
        $this->assertSame('1,000 đ', PhieuNhapDanhSachExport::formatVnd(1000));
        $this->assertSame('0 đ', PhieuNhapDanhSachExport::formatVnd(0));
        $this->assertSame('1,234,567 đ', PhieuNhapDanhSachExport::formatVnd(1234567));
        // Lam tron len 10,001 tu 10000.50 (mac dinh PHP round half up)
        $this->assertSame('10,001 đ', PhieuXuatDanhSachExport::formatVnd('10000.50'));
    }
}