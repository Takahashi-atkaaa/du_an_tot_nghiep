<?php

namespace Tests\Feature;

use App\Imports\PhieuNhapImport;
use App\Models\BienTheSanPham;
use App\Models\ChiTietLoHang;
use App\Models\ChiTietPhieu;
use App\Models\DonViQuyDoi;
use App\Models\LoHang;
use App\Models\NguoiDung;
use App\Models\Phieu;
use App\Models\PhieuNhap;
use App\Models\Product;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Maatwebsite\Excel\Facades\Excel;
use Tests\TestCase;

/**
 * Kiem thu chuc nang Import Excel phieu nhap.
 *
 * Phu cac kich ban:
 *  - Import 1 dong don vi co ban (ma_vach cua bien_the) -> them chi_tiet_lo_hang + chi_tiet_phieu.
 *  - Import 1 dong don vi quy doi (ma_vach cua DonViQuyDoi) -> he so quy doi dung.
 *  - Import nhieu dong trung ma_vach + HSD -> gop dong (cong SL, gia BQ gia quyen).
 *  - Import khong co file / file sai dinh dang -> 422.
 *  - Import dong khong co ma_vach -> bi bo qua.
 *  - Import dong so_luong <= 0 -> bao loi.
 *  - Import dong ma_vach khong ton tai -> bao loi.
 *  - Import 2 dong khac ma_vach (khac bien_the) cung HSD -> khong loi unique.
 *  - Import 2 lan cung lo (cung ma_vach+HSD) -> UPSERT tang so luong + gia BQ.
 *  - HSD dang YYYY-MM-DD va DD/MM/YYYY deu duoc parse dung.
 *  - HSD de trang -> mac dinh '2099-12-31'.
 *  - HSD dang serial number cua Excel -> duoc convert.
 *
 * Vi cac migration cua project dung cu phap MySQL, class nay override
 * refreshTestDatabase() de bo qua migrate:fresh va tu dung schema trong setUp().
 */
class PhieuNhapImportTest extends TestCase
{
    protected function refreshTestDatabase(): void
    {
        // No-op: tranh migrate:fresh tren MySQL-only migrations.
    }

    private NguoiDung $user;
    private Product $sp;
    private BienTheSanPham $btM;
    private BienTheSanPham $btXL;
    private DonViQuyDoi $thungM;
    private DonViQuyDoi $thungXL;

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
        $data = $this->makeProductWith2Variants();
        $this->sp = $data['sp'];
        $this->btM = $data['btM'];
        $this->btXL = $data['btXL'];
        $this->thungM = $data['thungM'];
        $this->thungXL = $data['thungXL'];
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

        Schema::create('san_pham', function ($t) {
            $t->id();
            $t->string('ten_san_pham');
            $t->string('thuong_hieu')->nullable();
            $t->text('mo_ta')->nullable();
            $t->boolean('trang_thai')->default(1);
            $t->timestamps();
            $t->softDeletes();
        });

        Schema::create('bien_the_san_pham', function ($t) {
            $t->id();
            $t->unsignedBigInteger('product_id');
            $t->string('ten_bien_the')->nullable();
            $t->string('ma_hang')->nullable();
            $t->string('ma_vach')->nullable();
            $t->decimal('gia_von', 14, 2)->default(0);
            $t->decimal('gia_ban', 14, 2)->default(0);
            $t->integer('so_luong_ton')->default(0);
            $t->integer('dinh_muc_toi_thieu')->default(0);
            $t->string('hinh_anh')->nullable();
            $t->text('thuoc_tinh_ids')->nullable();
            $t->boolean('trang_thai')->default(1);
            $t->boolean('la_don_vi')->default(0);
            $t->string('ten_don_vi', 100)->nullable();
            $t->timestamps();
            $t->softDeletes();
        });

        Schema::create('don_vi_quy_doi', function ($t) {
            $t->id();
            $t->unsignedBigInteger('variant_id')->nullable();
            $t->unsignedBigInteger('product_id')->nullable();
            $t->unsignedBigInteger('don_vi_chuan_id')->nullable();
            $t->string('ten_don_vi');
            $t->decimal('so_luong_san_pham_trong_don_vi', 12, 4)->default(1);
            $t->string('ma_hang')->nullable();
            $t->string('ma_vach')->nullable();
            $t->decimal('gia_von_quy_doi', 14, 2)->nullable();
            $t->decimal('gia_ban_quy_doi', 14, 2)->nullable();
            $t->decimal('gia_ban_si', 14, 2)->nullable();
            $t->string('hinh_anh')->nullable();
            $t->boolean('la_don_vi_mac_dinh')->default(0);
            $t->timestamps();
            $t->softDeletes();
        });

        Schema::create('lo_hang', function ($t) {
            $t->id();
            $t->unsignedBigInteger('id_phieu')->nullable();
            $t->unsignedBigInteger('id_nha_cung_cap')->nullable();
            $t->string('ma_lo')->nullable();
            $t->date('ngay_nhap');
            $t->text('ghi_chu')->nullable();
            $t->timestamps();
            $t->softDeletes();
        });

        Schema::create('chi_tiet_lo_hang', function ($t) {
            $t->id();
            $t->unsignedBigInteger('id_lo_hang');
            $t->unsignedBigInteger('id_san_pham');
            $t->unsignedBigInteger('variant_id')->nullable();
            $t->integer('so_luong_nhap')->default(0);
            $t->integer('so_luong_ton')->default(0);
            $t->decimal('gia_nhap', 14, 2)->default(0);
            $t->date('han_su_dung');
            $t->timestamps();
            $t->unique(['id_lo_hang', 'id_san_pham', 'variant_id', 'han_su_dung'], 'chi_tiet_lo_variant_unique');
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
    }

    private function makeProductWith2Variants(): array
    {
        $sp = Product::create([
            'ten_san_pham' => 'Bia 333',
            'thuong_hieu' => 'NoBrand',
            'trang_thai' => 1,
        ]);

        $btM = BienTheSanPham::create([
            'product_id' => $sp->id,
            'ten_bien_the' => 'M',
            'ten_don_vi' => 'Lon',
            'ma_vach' => 'MVC-M',
            'gia_von' => 10000,
            'gia_ban' => 15000,
            'so_luong_ton' => 0,
            'trang_thai' => 1,
        ]);
        $thungM = DonViQuyDoi::create([
            'variant_id' => $btM->id,
            'product_id' => $sp->id,
            'ten_don_vi' => 'Thung 24',
            'so_luong_san_pham_trong_don_vi' => 24,
            'ma_vach' => 'MVC-M-THUNG',
            'gia_von_quy_doi' => 240000,
            'gia_ban_quy_doi' => 360000,
        ]);

        $btXL = BienTheSanPham::create([
            'product_id' => $sp->id,
            'ten_bien_the' => 'XL',
            'ten_don_vi' => 'Lon',
            'ma_vach' => 'MVC-XL',
            'gia_von' => 11000,
            'gia_ban' => 16000,
            'so_luong_ton' => 0,
            'trang_thai' => 1,
        ]);
        $thungXL = DonViQuyDoi::create([
            'variant_id' => $btXL->id,
            'product_id' => $sp->id,
            'ten_don_vi' => 'Thung 24',
            'so_luong_san_pham_trong_don_vi' => 24,
            'ma_vach' => 'MVC-XL-THUNG',
            'gia_von_quy_doi' => 264000,
            'gia_ban_quy_doi' => 384000,
        ]);

        return [
            'sp' => $sp,
            'btM' => $btM,
            'btXL' => $btXL,
            'thungM' => $thungM,
            'thungXL' => $thungXL,
        ];
    }

    /**
     * Tao file CSV tam thoi de test import.
     * Dinh dang CSV dung dau cham phay (;) de phu hop Excel VN.
     */
    private function makeCsv(string $content, string $filename = 'test-import.csv'): UploadedFile
    {
        $tmpPath = sys_get_temp_dir() . '/' . $filename;
        file_put_contents($tmpPath, "\xEF\xBB\xBF" . $content);
        return new UploadedFile($tmpPath, $filename, 'text/csv', null, true);
    }

    private function postImport(UploadedFile $file): \Illuminate\Testing\TestResponse
    {
        return $this->actingAs($this->user)
            ->post('/admin/api/phieu-nhap/import', [
                'file' => $file,
                'loai_nhap' => 'mua_hang',
                'ghi_chu' => 'Test import',
            ]);
    }

    // =====================================================================
    // 1. Endpoint co ban
    // =====================================================================

    public function test_endpoint_can_post_khi_da_login(): void
    {
        $csv = "Ma_vach;Ten_san_pham;Ten_bien_the;Thuoc_tinh;Ten_don_vi;So_luong;Gia_nhap;Han_su_dung\n"
             . "MVC-M;Bia 333;M;M;Lon;10;10000;2027-12-31\n";

        $response = $this->postImport($this->makeCsv($csv));
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
    }

    // =====================================================================
    // 2. Import don vi co ban (ma_vach cua bien_the)
    // =====================================================================

    public function test_import_1_dong_don_vi_co_ban(): void
    {
        $csv = "Ma_vach;Ten_san_pham;Ten_bien_the;Thuoc_tinh;Ten_don_vi;So_luong;Gia_nhap;Han_su_dung\n"
             . "MVC-M;Bia 333;M;M;Lon;10;10000;2027-12-31\n";

        $response = $this->postImport($this->makeCsv($csv));
        $response->assertStatus(200);

        // Phai tao 1 phieu + 1 phieu_nhap + 1 lo_hang
        $this->assertSame(1, Phieu::count());
        $this->assertSame(1, PhieuNhap::count());
        $this->assertSame(1, LoHang::count());
        $this->assertSame(1, ChiTietLoHang::count());
        $this->assertSame(1, ChiTietPhieu::count());

        // so_luong giu nguyen vi la don vi co ban (khong quy doi)
        $ctLo = ChiTietLoHang::first();
        $this->assertSame(10, (int)$ctLo->so_luong_nhap);
        $this->assertSame(10, (int)$ctLo->so_luong_ton);
        $this->assertEquals(10000.0, (float)$ctLo->gia_nhap);
        $this->assertSame($this->btM->id, $ctLo->variant_id);
        $this->assertSame('2027-12-31', $ctLo->han_su_dung->format('Y-m-d'));
    }

    // =====================================================================
    // 3. Import don vi quy doi (ma_vach cua DonViQuyDoi)
    // =====================================================================

    public function test_import_1_dong_don_vi_quy_doi_thung_24(): void
    {
        // 5 thung x 24 = 120 san pham co ban
        $csv = "Ma_vach;Ten_san_pham;Ten_bien_the;Thuoc_tinh;Ten_don_vi;So_luong;Gia_nhap;Han_su_dung\n"
             . "MVC-M-THUNG;Bia 333;M;M;Thung 24;5;240000;2027-12-31\n";

        $response = $this->postImport($this->makeCsv($csv));
        $response->assertStatus(200);

        $ctLo = ChiTietLoHang::first();
        // So luong phai duoc quy doi: 5 thung * 24 = 120
        $this->assertSame(120, (int)$ctLo->so_luong_nhap, 'Phai quy doi 5 thung thanh 120 san pham co ban');
        $this->assertSame(120, (int)$ctLo->so_luong_ton);
        // Gia: 240000 / 24 = 10000/gia co ban (test cho phep sai so 0.001)
        $this->assertEqualsWithDelta(10000.0, (float)$ctLo->gia_nhap, 0.01);

        // Chi tiet phieu cung phai duoc quy doi
        $ctPhieu = ChiTietPhieu::first();
        $this->assertSame(120, (int)$ctPhieu->so_luong);
        $this->assertEqualsWithDelta(10000.0, (float)$ctPhieu->gia_nhap, 0.01);
    }

    // =====================================================================
    // 4. Import nhieu dong trung ma_vach + HSD -> gop dong
    // =====================================================================

    public function test_import_nhieu_dong_trung_ma_vach_va_hsd_gop_thanh_1_dong(): void
    {
        // 2 dong cung MVC-M cung HSD 2027-12-31 -> gop thanh 1 dong
        // SL=10 gia=10000 va SL=20 gia=12000
        // Tong SL = 30, gia BQ = (10000*10 + 12000*20) / 30 = (100000+240000)/30 = 340000/30 = 11333.33
        $csv = "Ma_vach;Ten_san_pham;Ten_bien_the;Thuoc_tinh;Ten_don_vi;So_luong;Gia_nhap;Han_su_dung\n"
             . "MVC-M;Bia 333;M;M;Lon;10;10000;2027-12-31\n"
             . "MVC-M;Bia 333;M;M;Lon;20;12000;2027-12-31\n";

        $response = $this->postImport($this->makeCsv($csv));
        $response->assertStatus(200);

        // Phai co 1 lo_hang, 1 chi_tiet_lo_hang, 1 chi_tiet_phieu (gop)
        $this->assertSame(1, ChiTietLoHang::count());
        $this->assertSame(1, ChiTietPhieu::count());

        $ctLo = ChiTietLoHang::first();
        $this->assertSame(30, (int)$ctLo->so_luong_nhap);
        $this->assertEqualsWithDelta(11333.33, (float)$ctLo->gia_nhap, 0.01,
            'Gia BQ gia quyen = (10000*10 + 12000*20)/30');
    }

    // =====================================================================
    // 5. Import dong khong co ma_vach -> bi bo qua
    // =====================================================================

    public function test_import_dong_khong_co_ma_vach_bi_bo_qua(): void
    {
        $csv = "Ma_vach;Ten_san_pham;Ten_bien_the;Thuoc_tinh;Ten_don_vi;So_luong;Gia_nhap;Han_su_dung\n"
             . ";Bia 333;M;M;Lon;10;10000;2027-12-31\n"
             . "MVC-M;Bia 333;M;M;Lon;10;10000;2027-12-31\n";

        $response = $this->postImport($this->makeCsv($csv));
        $response->assertStatus(200);

        // Chi import dong thu 2 (co ma_vach)
        $this->assertSame(1, ChiTietLoHang::count());
    }

    // =====================================================================
    // 6. Import dong so_luong <= 0 -> bao loi
    // =====================================================================

    public function test_import_dong_so_luong_khong_duong_bao_loi(): void
    {
        $csv = "Ma_vach;Ten_san_pham;Ten_bien_the;Thuoc_tinh;Ten_don_vi;So_luong;Gia_nhap;Han_su_dung\n"
             . "MVC-M;Bia 333;M;M;Lon;0;10000;2027-12-31\n";

        $response = $this->postImport($this->makeCsv($csv));
        $response->assertStatus(422);

        // Khong tao phieu nao
        $this->assertSame(0, Phieu::count());
    }

    public function test_import_dong_gia_nhap_am_bao_loi(): void
    {
        $csv = "Ma_vach;Ten_san_pham;Ten_bien_the;Thuoc_tinh;Ten_don_vi;So_luong;Gia_nhap;Han_su_dung\n"
             . "MVC-M;Bia 333;M;M;Lon;10;-100;2027-12-31\n";

        $response = $this->postImport($this->makeCsv($csv));
        $response->assertStatus(422);

        $this->assertSame(0, Phieu::count());
    }

    // =====================================================================
    // 7. Import dong ma_vach khong ton tai -> bao loi
    // =====================================================================

    public function test_import_ma_vach_khong_ton_tai_bao_loi(): void
    {
        $csv = "Ma_vach;Ten_san_pham;Ten_bien_the;Thuoc_tinh;Ten_don_vi;So_luong;Gia_nhap;Han_su_dung\n"
             . "MAVACH-KHONG-TON-TAI;Bia 333;M;M;Lon;10;10000;2027-12-31\n";

        $response = $this->postImport($this->makeCsv($csv));
        $response->assertStatus(422);

        $this->assertSame(0, Phieu::count());
    }

    // =====================================================================
    // 8. Import 2 dong khac ma_vach (khac bien_the) cung HSD
    // =====================================================================

    public function test_import_2_bien_the_khac_nhau_cung_hsd_khong_loi_unique(): void
    {
        $csv = "Ma_vach;Ten_san_pham;Ten_bien_the;Thuoc_tinh;Ten_don_vi;So_luong;Gia_nhap;Han_su_dung\n"
             . "MVC-M;Bia 333;M;M;Lon;10;10000;2027-12-31\n"
             . "MVC-XL;Bia 333;XL;XL;Lon;20;11000;2027-12-31\n";

        $response = $this->postImport($this->makeCsv($csv));
        $response->assertStatus(200);

        // Phai tao 2 chi_tiet_lo_hang (khac variant_id)
        $this->assertSame(2, ChiTietLoHang::count());
        $this->assertSame(2, ChiTietPhieu::count());
    }

    // =====================================================================
    // 9. Import HSD voi nhieu dinh dang
    // =====================================================================

    public function test_import_hsd_dinh_dang_DD_MM_YYYY(): void
    {
        $csv = "Ma_vach;Ten_san_pham;Ten_bien_the;Thuoc_tinh;Ten_don_vi;So_luong;Gia_nhap;Han_su_dung\n"
             . "MVC-M;Bia 333;M;M;Lon;10;10000;31/12/2027\n";

        $response = $this->postImport($this->makeCsv($csv));
        $response->assertStatus(200);

        $ctLo = ChiTietLoHang::first();
        $this->assertSame('2027-12-31', $ctLo->han_su_dung->format('Y-m-d'),
            'HSD dang DD/MM/YYYY phai duoc parse thanh YYYY-MM-DD');
    }

    public function test_import_hsd_trong_mac_dinh_2099_12_31(): void
    {
        $csv = "Ma_vach;Ten_san_pham;Ten_bien_the;Thuoc_tinh;Ten_don_vi;So_luong;Gia_nhap;Han_su_dung\n"
             . "MVC-M;Bia 333;M;M;Lon;10;10000;\n";

        $response = $this->postImport($this->makeCsv($csv));
        $response->assertStatus(200);

        $ctLo = ChiTietLoHang::first();
        $this->assertSame('2099-12-31', $ctLo->han_su_dung->format('Y-m-d'),
            'HSD trong phai mac dinh 2099-12-31');
    }

    public function test_import_hsd_dinh_dang_sai_bao_loi(): void
    {
        $csv = "Ma_vach;Ten_san_pham;Ten_bien_the;Thuoc_tinh;Ten_don_vi;So_luong;Gia_nhap;Han_su_dung\n"
             . "MVC-M;Bia 333;M;M;Lon;10;10000;KHONG-PHAI-NGAY\n";

        $response = $this->postImport($this->makeCsv($csv));
        $response->assertStatus(422);

        $this->assertSame(0, Phieu::count());
    }

    // =====================================================================
    // 10. UPSERT behavior - MOI IMPORT TAO LO MOI
    // =====================================================================

    public function test_moi_import_tao_lo_hang_moi_va_phieu_moi(): void
    {
        // Lan 1: nhap 10 gia 10000
        $csv1 = "Ma_vach;Ten_san_pham;Ten_bien_the;Thuoc_tinh;Ten_don_vi;So_luong;Gia_nhap;Han_su_dung\n"
              . "MVC-M;Bia 333;M;M;Lon;10;10000;2027-12-31\n";
        $this->postImport($this->makeCsv($csv1, 'lan1.csv'))->assertStatus(200);

        // Lan 2: nhap them 20 gia 12000 (cung ma_vach + HSD)
        $csv2 = "Ma_vach;Ten_san_pham;Ten_bien_the;Thuoc_tinh;Ten_don_vi;So_luong;Gia_nhap;Han_su_dung\n"
              . "MVC-M;Bia 333;M;M;Lon;20;12000;2027-12-31\n";
        $this->postImport($this->makeCsv($csv2, 'lan2.csv'))->assertStatus(200);

        // Moi import tao 1 PHIEU MOI va 1 LO MOI rieng biet (vi moi lo co id_phieu rieng)
        $this->assertSame(2, Phieu::count());
        $this->assertSame(2, PhieuNhap::count());
        $this->assertSame(2, LoHang::count());
        $this->assertSame(2, ChiTietLoHang::count(),
            'Moi import tao 1 lo moi, nen chi_tiet_lo_hang cua moi lo doc lap');

        // Moi lo co so luong va gia cua no (khong gop)
        $loHangs = LoHang::orderBy('id')->get();
        $ct1 = ChiTietLoHang::where('id_lo_hang', $loHangs[0]->id)->first();
        $ct2 = ChiTietLoHang::where('id_lo_hang', $loHangs[1]->id)->first();
        $this->assertSame(10, (int)$ct1->so_luong_nhap);
        $this->assertEqualsWithDelta(10000.0, (float)$ct1->gia_nhap, 0.01);
        $this->assertSame(20, (int)$ct2->so_luong_nhap);
        $this->assertEqualsWithDelta(12000.0, (float)$ct2->gia_nhap, 0.01);
    }

    // =====================================================================
    // 11. PARTIAL SUCCESS - Mot dong loi khong rollback cac dong OK
    // =====================================================================

    public function test_partial_success_dong_loi_khong_rollback_dong_ok(): void
    {
        // File co 3 dong: 1 hop le (MVC-M), 1 khong ton tai (INVALID-CODE), 1 hop le (MVC-M)
        // Hai dong MVC-M cung HSD se gop thanh 1 ChiTietPhieu
        $csv = "Ma_vach;Ten_san_pham;Ten_bien_the;Thuoc_tinh;Ten_don_vi;So_luong;Gia_nhap;Han_su_dung\n"
             . "MVC-M;Bia 333;M;M;Lon;5;10000;2027-12-31\n"
             . "INVALID-CODE;Bia 333;M;M;Lon;10;10000;2027-12-31\n"
             . "MVC-M;Bia 333;M;M;Lon;15;11000;2027-12-31\n";

        $response = $this->postImport($this->makeCsv($csv));
        $body = $response->json();

        $response->assertStatus(200);
        $this->assertTrue($body['success']);
        $this->assertSame(3, $body['row_count'], 'row_count = tong so dong trong file');
        $this->assertSame(1, $body['inserted_count'],
            'inserted_count = so chi_tiet_phieu da insert (2 dong cung ma_vach+hsd gop thanh 1)');
        $this->assertSame(2, $body['skipped_count'],
            'skipped_count = so dong loi (INVALID-CODE khong ton tai)');

        // Phai tao phieu (vi co it nhat 1 dong OK)
        $this->assertSame(1, Phieu::count());
        $this->assertSame(1, PhieuNhap::count());
        $this->assertSame(1, LoHang::count());
        // ChiTietLoHang chi co 1 entry (2 dong MVC-M cung HSD gop lai)
        $this->assertSame(1, ChiTietLoHang::count());
        $this->assertSame(1, ChiTietPhieu::count());

        // ChiTietLoHang phai co tong SL = 5 + 15 = 20
        $ct = ChiTietLoHang::first();
        $this->assertSame(20, (int)$ct->so_luong_nhap);

        // errors co entry cho INVALID-CODE
        $this->assertCount(1, $body['errors']);
        $this->assertStringContainsString('INVALID-CODE', $body['errors'][0]);
        $this->assertStringContainsString('khong ton tai', $body['errors'][0]);
    }

    public function test_partial_success_tat_ca_dong_loi_thi_fail_hoan_toan(): void
    {
        // File co 2 dong, ca 2 deu khong ton tai
        $csv = "Ma_vach;Ten_san_pham;Ten_bien_the;Thuoc_tinh;Ten_don_vi;So_luong;Gia_nhap;Han_su_dung\n"
             . "INVALID-1;Bia;M;M;Lon;5;10000;2027-12-31\n"
             . "INVALID-2;Bia;M;M;Lon;10;10000;2027-12-31\n";

        $response = $this->postImport($this->makeCsv($csv));
        $body = $response->json();

        $response->assertStatus(422);
        $this->assertFalse($body['success']);

        // Khong tao phieu
        $this->assertSame(0, Phieu::count());
        $this->assertSame(0, ChiTietLoHang::count());
    }

    public function test_sanitize_ma_vach_loai_bo_bom_va_zero_width(): void
    {
        // File co BOM o dau moi cell (Excel thuong save nhu vay khi paste)
        $csv = "Ma_vach;Ten_san_pham;Ten_bien_the;Thuoc_tinh;Ten_don_vi;So_luong;Gia_nhap;Han_su_dung\n"
             . "MVC-M;Bia;M;M;Lon;5;10000;2027-12-31\n";

        // Them BOM vao truoc ma_vach (giong Excel luu)
        $csvWithBom = "\xEF\xBB\xBF" . $csv;
        $tmpPath = sys_get_temp_dir() . '/test-bom-prefix.csv';
        file_put_contents($tmpPath, $csvWithBom);
        $file = new UploadedFile($tmpPath, 'test-bom-prefix.csv', 'text/csv', null, true);

        $response = $this->postImport($file);
        $body = $response->json();

        $response->assertStatus(200);
        $this->assertSame(1, $body['inserted_count']);
    }

    public function test_fallback_ma_vach_them_leading_zero(): void
    {
        // Tao 1 variant moi voi ma_vach co leading zero
        $sp = Product::first();
        BienTheSanPham::create([
            'product_id' => $sp->id,
            'ten_bien_the' => 'Special',
            'ten_don_vi' => 'cai',
            'ma_vach' => '00789879',
            'gia_von' => 50000,
            'gia_ban' => 60000,
            'so_luong_ton' => 0,
            'trang_thai' => 1,
        ]);

        // User nhap '789879' (Excel tu bo leading zero) -> code phai fallback tim '00789879'
        $csv = "Ma_vach;Ten_san_pham;Ten_bien_the;Thuoc_tinh;Ten_don_vi;So_luong;Gia_nhap;Han_su_dung\n"
             . "789879;Bia;Special;M;cai;5;50000;2027-12-31\n";

        $response = $this->postImport($this->makeCsv($csv));
        $body = $response->json();

        $response->assertStatus(200);
        $this->assertSame(1, $body['inserted_count'],
            'Phai import duoc qua fallback leading zero (789879 -> 00789879)');
    }
}
