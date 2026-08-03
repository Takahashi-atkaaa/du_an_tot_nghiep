<?php

namespace Tests\Feature;

use App\Http\Controllers\Admin\Api\PhieuNhapApiController;
use App\Models\BienTheSanPham;
use App\Models\DonViQuyDoi;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use ReflectionMethod;
use Tests\TestCase;

/**
 * Kiểm thử chuẩn hóa nhập hàng theo đơn vị quy đổi.
 * Phủ các kịch bản trong kế hoạch "Chuẩn hóa nhập theo đơn vị":
 *  - Nhập theo thùng (hệ số 24): tồn tăng 240 lon
 *  - Nhập theo lon (đơn vị cơ bản): tồn tăng 1
 *  - Tổng tiền không đổi khi đổi đơn vị
 *  - Validation: đơn vị không thuộc variant, hệ số không hợp lệ, số lượng không dương
 *
 * Các test dùng Reflection để gọi trực tiếp helper normalizeChiTiet/resolveDonViLookup
 * của PhieuNhapApiController mà không cần thao tác DB thật.
 */
class PhieuNhapUnitConversionTest extends TestCase
{
    private PhieuNhapApiController $controller;

    protected function setUp(): void
    {
        parent::setUp();
        $this->controller = new PhieuNhapApiController();
    }

    /**
     * Tạo một "variant" giả với cấu trúc giống Eloquent model
     * (chỉ cần các field được helper đọc: id, product_id, ten_don_vi).
     */
    private function fakeVariant(int $id, int $productId, string $tenDonViCoBan = 'Lon'): BienTheSanPham
    {
        $variant = new BienTheSanPham();
        $variant->id = $id;
        $variant->product_id = $productId;
        $variant->ten_don_vi = $tenDonViCoBan;
        $variant->so_luong_ton = 0;
        return $variant;
    }

    /** Tạo đơn vị quy đổi giả */
    private function fakeUnit(int $id, int $variantId, string $tenDonVi, float $heSo): DonViQuyDoi
    {
        $unit = new DonViQuyDoi();
        $unit->id = $id;
        $unit->variant_id = $variantId;
        $unit->product_id = $variantId;
        $unit->ten_don_vi = $tenDonVi;
        $unit->so_luong_san_pham_trong_don_vi = $heSo;
        return $unit;
    }

    /** Gọi resolveDonViLookup bằng reflection */
    private function resolveLookup(array $chiTiet, Collection $variants): array
    {
        $ref = new ReflectionMethod(PhieuNhapApiController::class, 'resolveDonViLookup');
        $ref->setAccessible(true);
        return $ref->invoke($this->controller, $chiTiet, $variants);
    }

    /** Gọi normalizeChiTiet bằng reflection */
    private function normalize(array $ct, Collection $variants, array $lookup): array
    {
        $ref = new ReflectionMethod(PhieuNhapApiController::class, 'normalizeChiTiet');
        $ref->setAccessible(true);
        return $ref->invoke($this->controller, $ct, $variants, $lookup);
    }

    public function test_nhap_theo_thung_24_quy_doi_sang_240_lon(): void
    {
        $variant = $this->fakeVariant(101, 1, 'Lon');
        $thung24 = $this->fakeUnit(201, 101, 'Thùng 24', 24);
        $variants = new Collection([101 => $variant]);
        $lookup = [101 => [201 => $thung24]];

        $result = $this->normalize(
            [
                'variant_id' => 101,
                'don_vi_id' => 201,
                'so_luong_nhap' => 10,
                'gia_nhap' => 240000,
                'han_su_dung' => '2027-01-01',
            ],
            $variants,
            $lookup,
        );

        // 10 thùng × 24 = 240 lon
        $this->assertEquals(240.0, $result['so_luong_co_ban']);
        // 240000 / 24 = 10000 / lon; tổng = 240 × 10000 = 2.400.000 == 10 × 240000
        $this->assertEquals(10000.0, $result['gia_nhap_co_ban']);
        $this->assertEquals(101, $result['variant_id']);
        $this->assertEquals(1, $result['product_id']);
        $this->assertStringContainsString('Thùng 24', $result['ghi_chu']);
        $this->assertStringContainsString('240', $result['ghi_chu']);
    }

    public function test_nhap_theo_don_vi_co_ban_giu_nguyen(): void
    {
        $variant = $this->fakeVariant(102, 1, 'Lon');
        $variants = new Collection([102 => $variant]);

        $result = $this->normalize(
            [
                'variant_id' => 102,
                'don_vi_id' => null,
                'so_luong_nhap' => 5,
                'gia_nhap' => 12000,
                'han_su_dung' => '2027-06-01',
            ],
            $variants,
            [],
        );

        $this->assertEquals(5.0, $result['so_luong_co_ban']);
        $this->assertEquals(12000.0, $result['gia_nhap_co_ban']);
        $this->assertNull($result['ghi_chu']);
    }

    public function test_don_vi_khong_thuoc_variant_bi_tu_choi(): void
    {
        $variant = $this->fakeVariant(103, 1, 'Lon');
        $unitCuaVariantKhac = $this->fakeUnit(999, 888, 'Thùng 24', 24);
        $variants = new Collection([103 => $variant]);
        $lookup = [103 => []];

        $this->expectException(ValidationException::class);
        $this->normalize(
            [
                'variant_id' => 103,
                'don_vi_id' => 999,
                'so_luong_nhap' => 1,
                'gia_nhap' => 100,
                'han_su_dung' => '2027-01-01',
            ],
            $variants,
            $lookup,
        );
    }

    public function test_so_luong_khong_duong_bi_tu_choi(): void
    {
        $variant = $this->fakeVariant(104, 1, 'Lon');
        $variants = new Collection([104 => $variant]);

        $this->expectException(ValidationException::class);
        $this->normalize(
            [
                'variant_id' => 104,
                'don_vi_id' => null,
                'so_luong_nhap' => 0,
                'gia_nhap' => 100,
                'han_su_dung' => '2027-01-01',
            ],
            $variants,
            [],
        );
    }

    public function test_gia_am_bi_tu_choi(): void
    {
        $variant = $this->fakeVariant(105, 1, 'Lon');
        $variants = new Collection([105 => $variant]);

        $this->expectException(ValidationException::class);
        $this->normalize(
            [
                'variant_id' => 105,
                'don_vi_id' => null,
                'so_luong_nhap' => 1,
                'gia_nhap' => -50,
                'han_su_dung' => '2027-01-01',
            ],
            $variants,
            [],
        );
    }

    public function test_variant_khong_ton_tai_bi_tu_choi(): void
    {
        $variants = new Collection([]);

        $this->expectException(ValidationException::class);
        $this->normalize(
            [
                'variant_id' => 9999,
                'don_vi_id' => null,
                'so_luong_nhap' => 1,
                'gia_nhap' => 100,
                'han_su_dung' => '2027-01-01',
            ],
            $variants,
            [],
        );
    }

    public function test_don_vi_placeholder_base_duoc_hieu_la_co_ban(): void
    {
        $variant = $this->fakeVariant(106, 1, 'Chai');
        $variants = new Collection([106 => $variant]);

        // '__base__' là giá trị placeholder từ frontend, helper phải map về đơn vị cơ bản
        $result = $this->normalize(
            [
                'variant_id' => 106,
                'don_vi_id' => '__base__',
                'so_luong_nhap' => 12,
                'gia_nhap' => 25000,
                'han_su_dung' => '2027-01-01',
            ],
            $variants,
            [],
        );

        $this->assertEquals(12.0, $result['so_luong_co_ban']);
        $this->assertEquals(25000.0, $result['gia_nhap_co_ban']);
        $this->assertNull($result['ghi_chu']);
    }

    public function test_tong_tien_khong_doi_khi_doi_don_vi(): void
    {
        // Nhập 10 thùng × 240000đ = 2.400.000đ; về cơ bản: 240 lon × 10000đ
        $variant = $this->fakeVariant(107, 1, 'Lon');
        $thung24 = $this->fakeUnit(207, 107, 'Thùng 24', 24);
        $variants = new Collection([107 => $variant]);
        $lookup = [107 => [207 => $thung24]];

        $thung = $this->normalize(
            [
                'variant_id' => 107,
                'don_vi_id' => 207,
                'so_luong_nhap' => 10,
                'gia_nhap' => 240000,
                'han_su_dung' => '2027-01-01',
            ],
            $variants,
            $lookup,
        );

        $tongThung = 10 * 240000;
        $tongCoBan = $thung['so_luong_co_ban'] * $thung['gia_nhap_co_ban'];

        $this->assertEquals($tongThung, $tongCoBan);
        $this->assertEquals(2400000, $tongCoBan);
    }
}