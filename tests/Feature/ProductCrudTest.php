<?php

namespace Tests\Feature;

use App\Models\BienTheSanPham;
use App\Models\DanhMucSanPham;
use App\Models\DonViQuyDoi;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Unit test cho CRUD operations của sản phẩm.
 * Sử dụng SQLite in-memory + RefreshDatabase.
 *
 * Lưu ý: project dùng tiếng Việt cho tên bảng/cột, nên ta
 * tạo dữ liệu trực tiếp qua Eloquent thay vì gọi HTTP,
 * để tránh phụ thuộc middleware auth.
 */
class ProductCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_product_with_default_variant(): void
    {
        $category = DanhMucSanPham::create([
            'ten_danh_muc' => 'Đồ uống',
            'trang_thai' => true,
        ]);

        $product = Product::create([
            'id_danh_muc' => $category->id,
            'ten_san_pham' => 'Coca Cola 330ml',
            'thuong_hieu' => 'Coca',
            'mo_ta' => 'Nước ngọt có ga',
            'trang_thai' => true,
        ]);

        $variant = BienTheSanPham::create([
            'product_id' => $product->id,
            'ten_bien_the' => null,
            'ma_hang' => 'MHTEST01',
            'ma_vach' => 'BVTEST01',
            'gia_von' => 5000,
            'gia_ban' => 10000,
            'so_luong_ton' => 0,
            'trang_thai' => true,
        ]);

        $this->assertDatabaseHas('san_pham', ['ten_san_pham' => 'Coca Cola 330ml']);
        $this->assertDatabaseHas('bien_the_san_pham', ['ma_vach' => 'BVTEST01']);
        $this->assertEquals($product->id, $variant->product_id);
    }

    public function test_can_create_product_with_units(): void
    {
        $category = DanhMucSanPham::create([
            'ten_danh_muc' => 'Đồ uống',
            'trang_thai' => true,
        ]);

        $product = Product::create([
            'id_danh_muc' => $category->id,
            'ten_san_pham' => 'Heineken',
            'trang_thai' => true,
        ]);

        $variant = BienTheSanPham::create([
            'product_id' => $product->id,
            'ten_bien_the' => 'Lon 330ml',
            'ma_hang' => 'MHHEINEKEN',
            'ma_vach' => 'BVHEINEKEN',
            'gia_von' => 10000,
            'gia_ban' => 15000,
            'so_luong_ton' => 0,
            'trang_thai' => true,
        ]);

        DonViQuyDoi::create([
            'variant_id' => $variant->id,
            'ten_don_vi' => 'Thùng 24 lon',
            'ty_le_quy_doi' => 24,
            'ma_hang' => 'MHHEIN-THUNG',
            'gia_von_quy_doi' => 240000,
            'gia_ban_quy_doi' => 350000,
            'la_don_vi_mac_dinh' => false,
        ]);

        $this->assertCount(1, $product->variants);
        $this->assertCount(1, $variant->units);
        $this->assertEquals('Thùng 24 lon', $variant->units->first()->ten_don_vi);
    }

    public function test_can_update_product_info(): void
    {
        $category = DanhMucSanPham::create([
            'ten_danh_muc' => 'Đồ uống',
            'trang_thai' => true,
        ]);

        $product = Product::create([
            'id_danh_muc' => $category->id,
            'ten_san_pham' => 'Tên cũ',
            'trang_thai' => true,
        ]);

        $product->update([
            'ten_san_pham' => 'Tên mới',
            'thuong_hieu' => 'BrandX',
        ]);

        $this->assertDatabaseHas('san_pham', [
            'id' => $product->id,
            'ten_san_pham' => 'Tên mới',
            'thuong_hieu' => 'BrandX',
        ]);
    }

    public function test_soft_delete_product(): void
    {
        $category = DanhMucSanPham::create([
            'ten_danh_muc' => 'Đồ uống',
            'trang_thai' => true,
        ]);

        $product = Product::create([
            'id_danh_muc' => $category->id,
            'ten_san_pham' => 'Sản phẩm test',
            'trang_thai' => true,
        ]);

        $product->delete();

        $this->assertSoftDeleted('san_pham', ['id' => $product->id]);
        $this->assertNull(Product::find($product->id));
        $this->assertNotNull(Product::withTrashed()->find($product->id));
    }

    public function test_force_delete_product(): void
    {
        $category = DanhMucSanPham::create([
            'ten_danh_muc' => 'Đồ uống',
            'trang_thai' => true,
        ]);

        $product = Product::create([
            'id_danh_muc' => $category->id,
            'ten_san_pham' => 'Sản phẩm xóa vĩnh viễn',
            'trang_thai' => true,
        ]);

        $productId = $product->id;
        $product->delete();
        $product->forceDelete();

        $this->assertDatabaseMissing('san_pham', ['id' => $productId]);
    }

    public function test_product_relationship_with_variants_and_units(): void
    {
        $category = DanhMucSanPham::create([
            'ten_danh_muc' => 'Thời trang',
            'trang_thai' => true,
        ]);

        $product = Product::create([
            'id_danh_muc' => $category->id,
            'ten_san_pham' => 'Áo thun',
            'trang_thai' => true,
        ]);

        $variantRedM = BienTheSanPham::create([
            'product_id' => $product->id,
            'ten_bien_the' => 'Đỏ - M',
            'ma_hang' => 'MHAOREDM',
            'ma_vach' => 'BVAOREDM',
            'gia_von' => 50000,
            'gia_ban' => 80000,
            'thuoc_tinh_ids' => [1, 2],
            'trang_thai' => true,
        ]);

        $variantRedL = BienTheSanPham::create([
            'product_id' => $product->id,
            'ten_bien_the' => 'Đỏ - L',
            'ma_hang' => 'MHAOREDL',
            'ma_vach' => 'BVAOREDL',
            'gia_von' => 55000,
            'gia_ban' => 85000,
            'thuoc_tinh_ids' => [1, 3],
            'trang_thai' => true,
        ]);

        $this->assertCount(2, $product->variants);
        $this->assertEquals('Đỏ - M', $product->variants->first()->ten_bien_the);

        // Test cascadeOnDelete
        $product->delete();
        $this->assertDatabaseMissing('bien_the_san_pham', ['id' => $variantRedM->id]);
        $this->assertDatabaseMissing('bien_the_san_pham', ['id' => $variantRedL->id]);
    }

    public function test_ten_hien_thi_attribute(): void
    {
        $category = DanhMucSanPham::create([
            'ten_danh_muc' => 'Đồ uống',
            'trang_thai' => true,
        ]);

        $product = Product::create([
            'id_danh_muc' => $category->id,
            'ten_san_pham' => 'Pepsi',
            'trang_thai' => true,
        ]);

        $variant = BienTheSanPham::create([
            'product_id' => $product->id,
            'ten_bien_the' => 'Lon 330ml',
            'ma_hang' => 'MHPEPSI',
            'ma_vach' => 'BVPEPSI',
            'gia_ban' => 10000,
            'trang_thai' => true,
        ]);

        $this->assertEquals('Pepsi - Lon 330ml', $variant->ten_hien_thi);
    }
}