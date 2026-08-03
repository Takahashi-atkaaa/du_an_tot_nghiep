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
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Test voi file CSV that cua user (cac ma_vach 789879, 11, 22, 33, 44)
 * de tim loi that su.
 */
class PhieuNhapImportUserFileTest extends TestCase
{
    protected function refreshTestDatabase(): void {}

    private NguoiDung $user;
    private Product $sp;

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

        $this->sp = Product::create([
            'ten_san_pham' => 'Kem danh rang',
            'thuong_hieu' => 'PS',
            'trang_thai' => 1,
        ]);

        // Bien the kem (ma_vach: 789879)
        BienTheSanPham::create([
            'product_id' => $this->sp->id,
            'ten_bien_the' => '',
            'ten_don_vi' => 'hop',
            'ma_vach' => '789879',
            'gia_von' => 50000,
            'gia_ban' => 60000,
            'trang_thai' => 1,
        ]);

        // 4 bien the giay
        foreach ([
            ['11', '38 - Đen'],
            ['22', '38 - Trắng'],
            ['33', '39 - Đen'],
            ['44', '39 - Trắng'],
        ] as [$mv, $ten]) {
            BienTheSanPham::create([
                'product_id' => $this->sp->id,
                'ten_bien_the' => $ten,
                'ten_don_vi' => 'doi',
                'ma_vach' => $mv,
                'gia_von' => 100000,
                'gia_ban' => 150000,
                'trang_thai' => 1,
            ]);
        }
    }

    private function buildSchema(): void
    {
        Schema::create('nguoi_dung', function ($t) {
            $t->id(); $t->string('ho_ten')->nullable(); $t->string('email')->nullable();
            $t->string('mat_khau')->nullable(); $t->boolean('trang_thai')->default(1);
            $t->timestamps(); $t->softDeletes();
        });
        Schema::create('san_pham', function ($t) {
            $t->id(); $t->string('ten_san_pham'); $t->string('thuong_hieu')->nullable();
            $t->text('mo_ta')->nullable(); $t->boolean('trang_thai')->default(1);
            $t->timestamps(); $t->softDeletes();
        });
        Schema::create('bien_the_san_pham', function ($t) {
            $t->id(); $t->unsignedBigInteger('product_id'); $t->string('ten_bien_the')->nullable();
            $t->string('ma_hang')->nullable(); $t->string('ma_vach')->nullable();
            $t->decimal('gia_von', 14, 2)->default(0); $t->decimal('gia_ban', 14, 2)->default(0);
            $t->integer('so_luong_ton')->default(0); $t->integer('dinh_muc_toi_thieu')->default(0);
            $t->string('hinh_anh')->nullable(); $t->text('thuoc_tinh_ids')->nullable();
            $t->boolean('trang_thai')->default(1); $t->boolean('la_don_vi')->default(0);
            $t->string('ten_don_vi', 100)->nullable(); $t->timestamps(); $t->softDeletes();
        });
        Schema::create('don_vi_quy_doi', function ($t) {
            $t->id(); $t->unsignedBigInteger('variant_id')->nullable();
            $t->unsignedBigInteger('product_id')->nullable();
            $t->unsignedBigInteger('don_vi_chuan_id')->nullable();
            $t->string('ten_don_vi'); $t->decimal('so_luong_san_pham_trong_don_vi', 12, 4)->default(1);
            $t->string('ma_hang')->nullable(); $t->string('ma_vach')->nullable();
            $t->decimal('gia_von_quy_doi', 14, 2)->nullable();
            $t->decimal('gia_ban_quy_doi', 14, 2)->nullable();
            $t->decimal('gia_ban_si', 14, 2)->nullable();
            $t->string('hinh_anh')->nullable(); $t->boolean('la_don_vi_mac_dinh')->default(0);
            $t->timestamps(); $t->softDeletes();
        });
        Schema::create('lo_hang', function ($t) {
            $t->id(); $t->unsignedBigInteger('id_phieu')->nullable();
            $t->unsignedBigInteger('id_nha_cung_cap')->nullable();
            $t->string('ma_lo')->nullable(); $t->date('ngay_nhap');
            $t->text('ghi_chu')->nullable(); $t->timestamps(); $t->softDeletes();
        });
        Schema::create('chi_tiet_lo_hang', function ($t) {
            $t->id(); $t->unsignedBigInteger('id_lo_hang');
            $t->unsignedBigInteger('id_san_pham'); $t->unsignedBigInteger('variant_id')->nullable();
            $t->integer('so_luong_nhap')->default(0); $t->integer('so_luong_ton')->default(0);
            $t->decimal('gia_nhap', 14, 2)->default(0); $t->date('han_su_dung');
            $t->timestamps();
            $t->unique(['id_lo_hang', 'id_san_pham', 'variant_id', 'han_su_dung'], 'chi_tiet_lo_variant_unique');
        });
        Schema::create('phieu', function ($t) {
            $t->id(); $t->string('loai_phieu'); $t->string('loai_phieu_enum')->nullable();
            $t->unsignedBigInteger('id_nguoi_dung')->nullable();
            $t->unsignedBigInteger('id_nha_cung_cap')->nullable();
            $t->unsignedBigInteger('id_hoa_don')->nullable();
            $t->text('ghi_chu')->nullable(); $t->timestamps(); $t->softDeletes();
        });
        Schema::create('phieu_nhap', function ($t) {
            $t->id(); $t->unsignedBigInteger('id_phieu');
            $t->string('loai_nhap'); $t->unsignedBigInteger('id_hoa_don')->nullable();
            $t->unsignedBigInteger('id_phieu_xuat_goc')->nullable();
            $t->text('ghi_chu')->nullable(); $t->timestamps(); $t->softDeletes();
        });
        Schema::create('chi_tiet_phieu', function ($t) {
            $t->id(); $t->unsignedBigInteger('id_phieu'); $t->unsignedBigInteger('id_san_pham');
            $t->unsignedBigInteger('variant_id')->nullable(); $t->unsignedBigInteger('id_lo_hang')->nullable();
            $t->unsignedBigInteger('id_chi_tiet_lo_hang')->nullable();
            $t->integer('so_luong')->default(0); $t->decimal('gia_nhap', 14, 2)->default(0);
            $t->string('ma_lo')->nullable(); $t->date('han_su_dung')->nullable();
            $t->integer('so_luong_con_lai')->default(0); $t->text('ghi_chu')->nullable();
            $t->timestamps(); $t->softDeletes();
        });
    }

    private function makeCsv(string $content, string $filename = 'test.csv'): UploadedFile
    {
        $tmpPath = sys_get_temp_dir() . '/' . $filename;
        file_put_contents($tmpPath, "\xEF\xBB\xBF" . $content);
        return new UploadedFile($tmpPath, $filename, 'text/csv', null, true);
    }

    public function test_import_file_user_dung_dau_cham_phay(): void
    {
        // Giong file user (anh 2): header chu thuong, dau cham phay, HSD DD/MM/YYYY
        $csv = "ma_vach;ma_san_pham;ten_san_pham;don_vi_tinh;so_luong;gia_nhap;han_su_dung\n"
             . "789879;SP001;Kem danh rang;hop;5;50000;1/1/2028\n"
             . "11;SP002;Giay 38 den;doi;10;100000;1/1/2028\n"
             . "22;SP003;Giay 38 trang;doi;15;100000;1/1/2028\n"
             . "33;SP004;Giay 39 den;doi;20;110000;1/1/2028\n"
             . "44;SP005;Giay 39 trang;doi;25;110000;1/1/2028\n";

        $file = $this->makeCsv($csv);
        $response = $this->actingAs($this->user)
            ->post('/admin/api/phieu-nhap/import', [
                'file' => $file,
                'loai_nhap' => 'mua_hang',
                'ghi_chu' => 'Test user file',
            ]);

        $body = $response->json();
        echo "\n[Response] status=" . $response->getStatusCode() . "\n";
        echo "success=" . ($body['success'] ?? 'null') . "\n";
        echo "message=" . ($body['message'] ?? 'null') . "\n";
        echo "row_count=" . ($body['row_count'] ?? 'null') . "\n";
        echo "errors=" . json_encode($body['errors'] ?? []) . "\n";

        $response->assertStatus(200);
        $this->assertSame(5, ChiTietLoHang::count(), '5 dong phai duoc import');
    }

    public function test_import_file_user_dung_dau_phaY(): void
    {
        // Giong file user neu dung dau phay
        $csv = "ma_vach,ma_san_pham,ten_san_pham,don_vi_tinh,so_luong,gia_nhap,han_su_dung\n"
             . "789879,SP001,Kem danh rang,hop,5,50000,1/1/2028\n"
             . "11,SP002,Giay 38 den,doi,10,100000,1/1/2028\n"
             . "22,SP003,Giay 38 trang,doi,15,100000,1/1/2028\n"
             . "33,SP004,Giay 39 den,doi,20,110000,1/1/2028\n"
             . "44,SP005,Giay 39 trang,doi,25,110000,1/1/2028\n";

        $file = $this->makeCsv($csv);
        $response = $this->actingAs($this->user)
            ->post('/admin/api/phieu-nhap/import', [
                'file' => $file,
                'loai_nhap' => 'mua_hang',
            ]);

        $body = $response->json();
        echo "\n[Response] status=" . $response->getStatusCode() . "\n";
        echo "success=" . ($body['success'] ?? 'null') . "\n";
        echo "message=" . ($body['message'] ?? 'null') . "\n";
        echo "row_count=" . ($body['row_count'] ?? 'null') . "\n";
        echo "errors=" . json_encode($body['errors'] ?? []) . "\n";

        $response->assertStatus(200);
    }

    public function test_import_file_user_khong_co_BOM(): void
    {
        // BOM mat - co the Excel tu xoa BOM
        $csv = "ma_vach;ma_san_pham;ten_san_pham;don_vi_tinh;so_luong;gia_nhap;han_su_dung\n"
             . "789879;SP001;Kem danh rang;hop;5;50000;1/1/2028\n";

        $tmpPath = sys_get_temp_dir() . '/test-no-bom.csv';
        file_put_contents($tmpPath, $csv);  // khong co BOM
        $file = new UploadedFile($tmpPath, 'test-no-bom.csv', 'text/csv', null, true);

        $response = $this->actingAs($this->user)
            ->post('/admin/api/phieu-nhap/import', [
                'file' => $file,
                'loai_nhap' => 'mua_hang',
            ]);

        $body = $response->json();
        echo "\n[Response] status=" . $response->getStatusCode() . "\n";
        echo "success=" . ($body['success'] ?? 'null') . "\n";
        echo "message=" . ($body['message'] ?? 'null') . "\n";
        echo "errors=" . json_encode($body['errors'] ?? []) . "\n";

        $response->assertStatus(200);
    }

    public function test_import_file_user_dong_cuoi_khong_co_HSD(): void
    {
        // User co the khong dien HSD dong cuoi
        $csv = "ma_vach;ma_san_pham;ten_san_pham;don_vi_tinh;so_luong;gia_nhap;han_su_dung\n"
             . "789879;SP001;Kem danh rang;hop;5;50000;\n"
             . "11;SP002;Giay 38 den;doi;10;100000;1/1/2028\n";

        $file = $this->makeCsv($csv);
        $response = $this->actingAs($this->user)
            ->post('/admin/api/phieu-nhap/import', [
                'file' => $file,
                'loai_nhap' => 'mua_hang',
            ]);

        $body = $response->json();
        echo "\n[Response] status=" . $response->getStatusCode() . "\n";
        echo "success=" . ($body['success'] ?? 'null') . "\n";
        echo "message=" . ($body['message'] ?? 'null') . "\n";
        echo "errors=" . json_encode($body['errors'] ?? []) . "\n";

        $response->assertStatus(200);
        $this->assertSame(2, ChiTietLoHang::count(), 'Ca 2 dong phai import duoc, HSD trong se mac dinh 2099-12-31');
    }
}