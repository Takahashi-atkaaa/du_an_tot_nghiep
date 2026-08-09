<?php

namespace Tests\Feature;

use App\Models\BienTheSanPham;
use App\Models\ChiTietLoHang;
use App\Models\ChiTietPhieu;
use App\Models\DonViQuyDoi;
use App\Models\LoHang;
use App\Models\NguoiDung;
use App\Models\Phieu;
use App\Models\PhieuNhap;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Kiem thu end-to-end PhieuNhapApiController::store() va destroy().
 *
 * Phu cac kich ban:
 *  - Test A: nhap 2 bien the KHAC NHAU cua cung san pham + cung HSD trong 1 phieu
 *            (truoc day loi QueryException do trung unique).
 *  - Test C: nhap cung (variant + HSD) vao lo co san -> UPSERT tang so luong + gia BQ gia quyen.
 *  - Test D: destroy phieu -> chi_tiet_phieu + chi_tiet_lo_hang + lo_hang deu xoa,
 *            tong ton bien_the_san_pham.so_luong_ton KHONG tang ao.
 *
 * Vi nhieu migration cua project dung cu phap MySQL (MODIFY COLUMN ...) khong tuong
 * thich SQLite trong moi truong testing, class nay override refreshTestDatabase()
 * de bo qua migrate:fresh va tu dung schema toi gian trong setUp().
 */
class PhieuNhapMultipleVariantsTest extends TestCase
{
    /**
     * Tu dinh nghia ham refreshTestDatabase() de thay the behavior mac dinh
     * cua RefreshDatabase trait (goi migrate:fresh).
     *
     * Vi trait RefreshDatabase cung cap ham cung ten, viec define lai ham
     * nay se shadow behavior cua trait (do trait chi la "use" interface).
     */
    protected function refreshTestDatabase(): void
    {
        // No-op: tranh migrate:fresh tren MySQL-only migrations.
        // Schema se duoc tao trong setUp() boi buildSchema().
    }

    private NguoiDung $user;

    protected function setUp(): void
    {
        // parent::setUp() truoc (khoi tao app + facade), ROI MOI build schema.
        parent::setUp();
        $this->buildSchema();
        $this->user = NguoiDung::create([
            'ho_ten' => 'Test Admin',
            'email' => 'admin@test.local',
            'mat_khau' => 'secret123',
            'trang_thai' => 1,
        ]);
    }

    /**
     * Dung schema toi gian (SQLite-compatible) cho cac bang lien quan den phieu nhap.
     */
    private function buildSchema(): void
    {
        Schema::create('nguoi_dung', function ($t) {
            $t->id();
            $t->string('ho_ten')->nullable();
            $t->string('email')->nullable();
            $t->string('mat_khau')->nullable();
            $t->boolean('trang_thai')->default(1);
            $t->timestamps();
        });

        Schema::create('san_pham', function ($t) {
            $t->id();
            $t->string('ten_san_pham');
            $t->string('thuong_hieu')->nullable();
            $t->text('mo_ta')->nullable();
            $t->boolean('trang_thai')->default(1);
            $t->timestamps();
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
            // Chi dung 1 unique key (giong production sau khi da drop key cu).
            // Key cu `chi_tiet_lo_unique (id_lo_hang, id_san_pham, han_su_dung)` da bi drop boi
            // migration 2026_08_02_210000_drop_old_chi_tiet_lo_unique_key.php de tranh
            // can tro khi nhap nhieu bien the cung (lo, san pham, HSD).
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

    /** Tao 1 san pham co 2 bien the (M va XL), moi bien the co 1 don vi quy doi (Thung 24). */
    private function makeProductWith2Variants(): array
    {
        $sp = Product::create([
            'ten_san_pham' => 'Ao thun test',
            'thuong_hieu' => 'NoBrand',
            'trang_thai' => 1,
        ]);

        $btM = BienTheSanPham::create([
            'product_id' => $sp->id,
            'ten_bien_the' => 'M',
            'ten_don_vi' => 'Cai',
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
            'gia_von_quy_doi' => 240000,
            'gia_ban_quy_doi' => 360000,
        ]);

        $btXL = BienTheSanPham::create([
            'product_id' => $sp->id,
            'ten_bien_the' => 'XL',
            'ten_don_vi' => 'Cai',
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
     * Test A: 2 bien the khac nhau, cung HSD, cung phieu -> khong trung unique,
     * tao duoc 2 chi_tiet_lo_hang, tong ton dung.
     */
    public function test_A_nhap_2_bien_the_khac_nhau_cung_HSD_trong_1_phieu(): void
    {
        $data = $this->makeProductWith2Variants();

        $payload = [
            'loai_nhap' => 'mua_hang',
            'id_nha_cung_cap' => null,
            'ghi_chu' => 'Test A',
            'tao_lo_moi' => '1',
            'id_lo_hang' => '',
            'chi_tiet' => [
                [
                    'variant_id' => $data['btM']->id,
                    'don_vi_id' => null,
                    'so_luong_nhap' => 100,
                    'gia_nhap' => 10000,
                    'han_su_dung' => '2028-01-01',
                ],
                [
                    'variant_id' => $data['btXL']->id,
                    'don_vi_id' => null,
                    'so_luong_nhap' => 50,
                    'gia_nhap' => 11000,
                    'han_su_dung' => '2028-01-01',
                ],
            ],
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/admin/api/phieu-nhap', $payload);

        $response->assertStatus(201);
        $response->assertJson(['success' => true]);

        // Tao 2 chi_tiet_lo_hang (khac variant_id)
        $this->assertSame(2, ChiTietLoHang::count());
        $this->assertSame(2, ChiTietPhieu::count());

        // Lo chi co 1
        $this->assertSame(1, LoHang::count());
        $lo = LoHang::first();
        $this->assertNotEmpty($lo->ma_lo);
        $this->assertSame($lo->ma_lo, ChiTietPhieu::first()->ma_lo);

        // Tong ton bien_the dung
        $data['btM']->refresh();
        $data['btXL']->refresh();
        $this->assertSame(100, $data['btM']->so_luong_ton);
        $this->assertSame(50, $data['btXL']->so_luong_ton);
    }

    /**
     * Test C: them vao lo co san, nhap them cung (variant + HSD) -> UPSERT
     * so_luong_nhap + gia_binh_quan_gia_quyen, ma_lo dung.
     */
    public function test_C_them_vao_lo_co_san_voi_cung_variant_HSD_thi_UPSERT(): void
    {
        $data = $this->makeProductWith2Variants();

        // Tao lo co san voi san pham M, so luong 100, gia 10000
        $phieuBanDau = Phieu::create([
            'loai_phieu' => 'Nhập hàng',
            'loai_phieu_enum' => 'nhap_mua_hang',
            'id_nguoi_dung' => $this->user->id,
        ]);
        PhieuNhap::create(['id_phieu' => $phieuBanDau->id, 'loai_nhap' => 'mua_hang']);
        $loCu = LoHang::create([
            'id_phieu' => $phieuBanDau->id,
            'ma_lo' => 'PN-OLD',
            'ngay_nhap' => '2026-01-01',
        ]);
        $ctLoCu = ChiTietLoHang::create([
            'id_lo_hang' => $loCu->id,
            'id_san_pham' => $data['sp']->id,
            'variant_id' => $data['btM']->id,
            'so_luong_nhap' => 100,
            'so_luong_ton' => 100,
            'gia_nhap' => 10000,
            'han_su_dung' => '2028-01-01 00:00:00',
        ]);

        // Dong bo tong ton vi khong qua controller observer
        $data['btM']->so_luong_ton = 100;
        $data['btM']->save();

        // Bay gio tao phieu moi, them vao lo cu, nhap them 50 cai voi gia 12000
        $payload = [
            'loai_nhap' => 'mua_hang',
            'tao_lo_moi' => '0',
            'id_lo_hang' => $loCu->id,
            'chi_tiet' => [
                [
                    'variant_id' => $data['btM']->id,
                    'don_vi_id' => null,
                    'so_luong_nhap' => 50,
                    'gia_nhap' => 12000,
                    'han_su_dung' => '2028-01-01 00:00:00',
                ],
            ],
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/admin/api/phieu-nhap', $payload);
        $response->assertStatus(201);

        // Van chi 1 chi_tiet_lo_hang cho (variant, HSD) -> UPSERT dung
        $this->assertSame(1, ChiTietLoHang::where('variant_id', $data['btM']->id)->count());

        $ctLoCu->refresh();
        $this->assertSame(150, $ctLoCu->so_luong_nhap);
        $this->assertSame(150, $ctLoCu->so_luong_ton);
        // Gia BQ gia quyen: (10000*100 + 12000*50) / 150 = (1.000.000 + 600.000) / 150 = 10.666.67
        $this->assertEquals(10666.67, round((float)$ctLoCu->gia_nhap, 2));

        // Lo van giu nguyen ma_lo
        $this->assertSame('PN-OLD', $loCu->fresh()->ma_lo);
        // chi_tiet_phieu moi co ma_lo = 'PN-OLD'
        $ctpMoi = ChiTietPhieu::where('id_phieu', '!=', $phieuBanDau->id)->first();
        $this->assertNotNull($ctpMoi);
        $this->assertSame('PN-OLD', $ctpMoi->ma_lo);
        $this->assertSame($ctLoCu->id, $ctpMoi->id_chi_tiet_lo_hang);

        // Tong ton btM tang 50 (chi them phan moi)
        $this->assertSame(150, $data['btM']->fresh()->so_luong_ton);
    }

    /**
     * Test D: destroy phieu nhap -> sach chi_tiet_phieu + chi_tiet_lo_hang + lo_hang,
     * tong ton KHONG tang ao.
     */
    public function test_D_destroy_phieu_khong_de_lai_chi_tiet_lo_hang_va_khong_tang_ton_ao(): void
    {
        $data = $this->makeProductWith2Variants();

        // Snapshot ton ban dau
        $this->assertSame(0, $data['btM']->fresh()->so_luong_ton);
        $this->assertSame(0, $data['btXL']->fresh()->so_luong_ton);

        // Tao phieu co 1 san pham (M), so luong 100, gia 10000
        $payload = [
            'loai_nhap' => 'mua_hang',
            'tao_lo_moi' => '1',
            'chi_tiet' => [
                [
                    'variant_id' => $data['btM']->id,
                    'don_vi_id' => null,
                    'so_luong_nhap' => 100,
                    'gia_nhap' => 10000,
                    'han_su_dung' => '2028-01-01',
                ],
            ],
        ];

        $this->actingAs($this->user)
            ->postJson('/admin/api/phieu-nhap', $payload)
            ->assertStatus(201);

        $phieuNhapId = PhieuNhap::first()->id;
        $this->assertSame(100, $data['btM']->fresh()->so_luong_ton);

        // Xoa het ton truoc khi destroy (mo phong da xuat hang)
        ChiTietLoHang::query()->update(['so_luong_ton' => 0, 'so_luong_nhap' => 0]);
        $data['btM']->refresh();
        $data['btM']->so_luong_ton = 0;
        $data['btM']->save();

        // Destroy phieu
        $response = $this->actingAs($this->user)
            ->deleteJson('/admin/api/phieu-nhap/' . $phieuNhapId);
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        // Khong con chi_tiet_phieu
        $this->assertSame(0, ChiTietPhieu::count());
        // Khong con chi_tiet_lo_hang
        $this->assertSame(0, ChiTietLoHang::count());
        // Khong con lo_hang
        $this->assertSame(0, LoHang::count());
        // PhieuNhap da xoa
        $this->assertSame(0, PhieuNhap::count());

        // Tong ton KHONG bi cong nguoc (truoc day observer loi lam tang ao)
        $this->assertSame(0, $data['btM']->fresh()->so_luong_ton);
    }

    /**
     * Bonus: destroy phieu KHONG thanh cong neu lo con ton > 0.
     */
    public function test_destroy_phieu_that_bai_khi_lo_con_ton(): void
    {
        $data = $this->makeProductWith2Variants();

        $payload = [
            'loai_nhap' => 'mua_hang',
            'tao_lo_moi' => '1',
            'chi_tiet' => [
                [
                    'variant_id' => $data['btM']->id,
                    'don_vi_id' => null,
                    'so_luong_nhap' => 100,
                    'gia_nhap' => 10000,
                    'han_su_dung' => '2028-01-01',
                ],
            ],
        ];

        $this->actingAs($this->user)
            ->postJson('/admin/api/phieu-nhap', $payload)
            ->assertStatus(201);

        $phieuNhapId = PhieuNhap::first()->id;

        // Phieu co ton 100 -> destroy phai fail 422
        $response = $this->actingAs($this->user)
            ->deleteJson('/admin/api/phieu-nhap/' . $phieuNhapId);
        $response->assertStatus(422);
        $response->assertJson(['success' => false]);

        // Phieu van ton
        $this->assertSame(1, PhieuNhap::count());
        $this->assertSame(1, ChiTietLoHang::count());
    }
}
