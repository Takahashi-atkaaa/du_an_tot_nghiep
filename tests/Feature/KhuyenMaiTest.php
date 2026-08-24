<?php

namespace Tests\Feature;

use App\Models\KhuyenMai;
use App\Models\KhuyenMaiSanPham;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;

class KhuyenMaiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Tạo user admin để test
        $this->artisan('db:seed', ['--class' => 'DatabaseSeeder'])->run();
    }

    /**
     * Test: Tạo khuyến mãi hóa đơn (giảm %)
     */
    public function test_tao_khuyen_mai_hoa_don_giam_phan_tram(): void
    {
        $data = [
            'ten_chuong_trinh' => 'Test Giảm 10% Hóa Đơn',
            'loai_giam_gia' => 'percent',
            'gia_tri_giam' => 10,
            'giam_toi_da' => 50000,
            'ngay_bat_dau' => Carbon::now()->format('Y-m-d'),
            'ngay_ket_thuc' => Carbon::now()->addMonth()->format('Y-m-d'),
            'trang_thai' => true,
            'ghi_chu' => 'Test khuyến mãi hóa đơn',
            'pham_vi' => 'hoa_don',
        ];

        $response = $this->postJson('/admin/khuyen-mai', $data);

        $response->assertStatus(302);
        $this->assertDatabaseHas('khuyen_mai', [
            'ten_chuong_trinh' => 'Test Giảm 10% Hóa Đơn',
            'loai_giam_gia' => 'percent',
            'gia_tri_giam' => 10,
        ]);
    }

    /**
     * Test: Tạo khuyến mãi hóa đơn (giảm tiền)
     */
    public function test_tao_khuyen_mai_hoa_don_giam_tien(): void
    {
        $data = [
            'ten_chuong_trinh' => 'Test Giảm 20.000đ Hóa Đơn',
            'loai_giam_gia' => 'amount',
            'gia_tri_giam' => 20000,
            'ngay_bat_dau' => Carbon::now()->format('Y-m-d'),
            'ngay_ket_thuc' => Carbon::now()->addMonth()->format('Y-m-d'),
            'trang_thai' => true,
            'ghi_chu' => 'Test giảm tiền',
            'pham_vi' => 'hoa_don',
        ];

        $response = $this->postJson('/admin/khuyen-mai', $data);

        $response->assertStatus(302);
        $this->assertDatabaseHas('khuyen_mai', [
            'ten_chuong_trinh' => 'Test Giảm 20.000đ Hóa Đơn',
            'loai_giam_gia' => 'amount',
            'gia_tri_giam' => 20000,
        ]);
    }

    /**
     * Test: Validation - phần trăm > 100%
     */
    public function test_khong_cho_phep_phan_tram_lon_hon_100(): void
    {
        $data = [
            'ten_chuong_trinh' => 'Test Phần Trăm Lớn',
            'loai_giam_gia' => 'percent',
            'gia_tri_giam' => 150,
            'ngay_bat_dau' => Carbon::now()->format('Y-m-d'),
            'ngay_ket_thuc' => Carbon::now()->addMonth()->format('Y-m-d'),
            'trang_thai' => true,
            'pham_vi' => 'hoa_don',
        ];

        $response = $this->postJson('/admin/khuyen-mai', $data);

        $response->assertSessionHasErrors(['gia_tri_giam']);
    }

    /**
     * Test: Validation - ngày kết thúc trước ngày bắt đầu
     */
    public function test_ngay_ket_thuc_phai_sau_ngay_bat_dau(): void
    {
        $data = [
            'ten_chuong_trinh' => 'Test Ngày Sai',
            'loai_giam_gia' => 'percent',
            'gia_tri_giam' => 10,
            'ngay_bat_dau' => Carbon::now()->addMonth()->format('Y-m-d'),
            'ngay_ket_thuc' => Carbon::now()->format('Y-m-d'),
            'trang_thai' => true,
            'pham_vi' => 'hoa_don',
        ];

        $response = $this->postJson('/admin/khuyen-mai', $data);

        $response->assertSessionHasErrors(['ngay_ket_thuc']);
    }

    /**
     * Test: Validation - thiếu tên chương trình
     */
    public function test_ten_chuong_trinh_bat_buoc(): void
    {
        $data = [
            'loai_giam_gia' => 'percent',
            'gia_tri_giam' => 10,
            'ngay_bat_dau' => Carbon::now()->format('Y-m-d'),
            'ngay_ket_thuc' => Carbon::now()->addMonth()->format('Y-m-d'),
            'trang_thai' => true,
            'pham_vi' => 'hoa_don',
        ];

        $response = $this->postJson('/admin/khuyen-mai', $data);

        $response->assertSessionHasErrors(['ten_chuong_trinh']);
    }

    /**
     * Test: Xem danh sách khuyến mãi
     */
    public function test_xem_danh_sach_khuyen_mai(): void
    {
        $response = $this->get('/admin/khuyen-mai');

        $response->assertStatus(200);
    }

    /**
     * Test: Tạo khuyến mãi sản phẩm
     */
    public function test_tao_khuyen_mai_san_pham(): void
    {
        // Tạo sản phẩm test
        $sanPham = \App\Models\SanPham::create([
            'ten_san_pham' => 'Sản phẩm Test',
            'gia_nhap' => 50000,
            'gia_ban' => 100000,
            'so_luong' => 100,
            'trang_thai' => true,
        ]);

        $data = [
            'ten_chuong_trinh' => 'Test Khuyến Mãi Sản Phẩm',
            'loai_giam_gia' => 'percent',
            'gia_tri_giam' => 15,
            'ngay_bat_dau' => Carbon::now()->format('Y-m-d'),
            'ngay_ket_thuc' => Carbon::now()->addMonth()->format('Y-m-d'),
            'trang_thai' => true,
            'ghi_chu' => 'Test khuyến mãi sản phẩm',
            'pham_vi' => 'san_pham',
            'id_san_phams' => [$sanPham->id],
        ];

        $response = $this->postJson('/admin/khuyen-mai', $data);

        $response->assertStatus(302);
        $this->assertDatabaseHas('khuyen_mai', [
            'ten_chuong_trinh' => 'Test Khuyến Mãi Sản Phẩm',
        ]);
        $this->assertDatabaseHas('khuyen_mai_san_pham', [
            'id_san_pham' => $sanPham->id,
        ]);
    }

    /**
     * Test: Xem chi tiết khuyến mãi
     */
    public function test_xem_chi_tiet_khuyen_mai(): void
    {
        $khuyenMai = KhuyenMai::create([
            'ten_chuong_trinh' => 'Test Chi Tiết',
            'loai_giam_gia' => 'percent',
            'gia_tri_giam' => 10,
            'ngay_bat_dau' => Carbon::now(),
            'ngay_ket_thuc' => Carbon::now()->addMonth(),
            'trang_thai' => true,
        ]);

        $response = $this->get("/admin/khuyen-mai/{$khuyenMai->id}");

        $response->assertStatus(200);
    }

    /**
     * Test: Sửa khuyến mãi
     */
    public function test_sua_khuyen_mai(): void
    {
        $khuyenMai = KhuyenMai::create([
            'ten_chuong_trinh' => 'Test Sửa',
            'loai_giam_gia' => 'percent',
            'gia_tri_giam' => 10,
            'ngay_bat_dau' => Carbon::now(),
            'ngay_ket_thuc' => Carbon::now()->addMonth(),
            'trang_thai' => true,
        ]);

        $data = [
            'ten_chuong_trinh' => 'Test Sửa - Đã Cập Nhật',
            'loai_giam_gia' => 'percent',
            'gia_tri_giam' => 15,
            'ngay_bat_dau' => Carbon::now()->format('Y-m-d'),
            'ngay_ket_thuc' => Carbon::now()->addMonth()->format('Y-m-d'),
            'trang_thai' => true,
            'pham_vi' => 'hoa_don',
        ];

        $response = $this->putJson("/admin/khuyen-mai/{$khuyenMai->id}", $data);

        $response->assertStatus(302);
        $this->assertDatabaseHas('khuyen_mai', [
            'id' => $khuyenMai->id,
            'ten_chuong_trinh' => 'Test Sửa - Đã Cập Nhật',
            'gia_tri_giam' => 15,
        ]);
    }

    /**
     * Test: Xóa mềm khuyến mãi
     */
    public function test_xoa_mem_khuyen_mai(): void
    {
        $khuyenMai = KhuyenMai::create([
            'ten_chuong_trinh' => 'Test Xóa',
            'loai_giam_gia' => 'percent',
            'gia_tri_giam' => 10,
            'ngay_bat_dau' => Carbon::now(),
            'ngay_ket_thuc' => Carbon::now()->addMonth(),
            'trang_thai' => true,
        ]);

        $response = $this->delete("/admin/khuyen-mai/{$khuyenMai->id}");

        $response->assertStatus(302);
        $this->assertSoftDeleted('khuyen_mai', ['id' => $khuyenMai->id]);
    }

    /**
     * Test: Toggle khuyến mãi - Bật/Tắt
     */
    public function test_toggle_khuyen_mai(): void
    {
        $khuyenMai = KhuyenMai::create([
            'ten_chuong_trinh' => 'Test Toggle',
            'loai_giam_gia' => 'percent',
            'gia_tri_giam' => 10,
            'ngay_bat_dau' => Carbon::now()->subDay(),
            'ngay_ket_thuc' => Carbon::now()->addMonth(),
            'trang_thai' => true,
        ]);

        $response = $this->post("/admin/khuyen-mai/{$khuyenMai->id}/toggle");

        $response->assertStatus(302);
        $khuyenMai->refresh();
        $this->assertFalse($khuyenMai->trang_thai);
    }

    /**
     * Test: Khôi phục khuyến mãi
     */
    public function test_khoi_phuc_khuyen_mai(): void
    {
        $khuyenMai = KhuyenMai::create([
            'ten_chuong_trinh' => 'Test Khôi Phục',
            'loai_giam_gia' => 'percent',
            'gia_tri_giam' => 10,
            'ngay_bat_dau' => Carbon::now(),
            'ngay_ket_thuc' => Carbon::now()->addMonth(),
            'trang_thai' => false,
        ]);
        $khuyenMai->delete();

        $response = $this->post("/admin/khuyen-mai/{$khuyenMai->id}/restore");

        $response->assertStatus(302);
        $this->assertDatabaseHas('khuyen_mai', [
            'id' => $khuyenMai->id,
            'deleted_at' => null,
        ]);
    }

    /**
     * Test: Thùng rác khuyến mãi
     */
    public function test_xem_thung_rac(): void
    {
        $khuyenMai = KhuyenMai::create([
            'ten_chuong_trinh' => 'Test Thùng Rác',
            'loai_giam_gia' => 'percent',
            'gia_tri_giam' => 10,
            'ngay_bat_dau' => Carbon::now(),
            'ngay_ket_thuc' => Carbon::now()->addMonth(),
            'trang_thai' => true,
        ]);
        $khuyenMai->delete();

        $response = $this->get('/admin/khuyen-mai/thung-rac');

        $response->assertStatus(200);
    }

    /**
     * Test: Model - scopeCurrentlyActive
     */
    public function test_scope_dang_hoat_dong(): void
    {
        // Khuyến mãi đang hoạt động
        KhuyenMai::create([
            'ten_chuong_trinh' => 'Đang Hoạt Động',
            'loai_giam_gia' => 'percent',
            'gia_tri_giam' => 10,
            'ngay_bat_dau' => Carbon::now()->subDay(),
            'ngay_ket_thuc' => Carbon::now()->addMonth(),
            'trang_thai' => true,
        ]);

        // Khuyến mãi hết hạn
        KhuyenMai::create([
            'ten_chuong_trinh' => 'Hết Hạn',
            'loai_giam_gia' => 'percent',
            'gia_tri_giam' => 10,
            'ngay_bat_dau' => Carbon::now()->subMonth(2),
            'ngay_ket_thuc' => Carbon::now()->subDay(),
            'trang_thai' => true,
        ]);

        // Khuyến mãi bị tắt
        KhuyenMai::create([
            'ten_chuong_trinh' => 'Bị Tắt',
            'loai_giam_gia' => 'percent',
            'gia_tri_giam' => 10,
            'ngay_bat_dau' => Carbon::now()->subDay(),
            'ngay_ket_thuc' => Carbon::now()->addMonth(),
            'trang_thai' => false,
        ]);

        $activePromos = KhuyenMai::currentlyActive()->get();

        $this->assertCount(1, $activePromos);
        $this->assertEquals('Đang Hoạt Động', $activePromos->first()->ten_chuong_trinh);
    }

    /**
     * Test: Model - isCurrentlyActive
     */
    public function test_kiem_tra_trang_thai_hoat_dong(): void
    {
        $kmHoatDong = KhuyenMai::create([
            'ten_chuong_trinh' => 'Test Hoạt Động',
            'loai_giam_gia' => 'percent',
            'gia_tri_giam' => 10,
            'ngay_bat_dau' => Carbon::now()->subDay(),
            'ngay_ket_thuc' => Carbon::now()->addMonth(),
            'trang_thai' => true,
        ]);

        $this->assertTrue($kmHoatDong->isCurrentlyActive());

        $kmBiKhoa = KhuyenMai::create([
            'ten_chuong_trinh' => 'Test Bị Khóa',
            'loai_giam_gia' => 'percent',
            'gia_tri_giam' => 10,
            'ngay_bat_dau' => Carbon::now()->subDay(),
            'ngay_ket_thuc' => Carbon::now()->addMonth(),
            'trang_thai' => false,
        ]);

        $this->assertFalse($kmBiKhoa->isCurrentlyActive());
    }

    /**
     * Test: Model - applicableToOrder
     */
    public function test_ap_dung_cho_don_hang(): void
    {
        $km = KhuyenMai::create([
            'ten_chuong_trinh' => 'Test Áp Dụng',
            'loai_giam_gia' => 'percent',
            'gia_tri_giam' => 10,
            'ngay_bat_dau' => Carbon::now()->subDay(),
            'ngay_ket_thuc' => Carbon::now()->addMonth(),
            'trang_thai' => true,
            'don_hang_toi_thieu' => 100000,
            'so_luong_sp_toi_thieu' => 2,
        ]);

        // Đơn hàng đủ điều kiện
        $this->assertTrue($km->applicableToOrder(150000, 3));

        // Đơn hàng không đủ giá trị
        $this->assertFalse($km->applicableToOrder(50000, 3));

        // Đơn hàng không đủ số lượng
        $this->assertFalse($km->applicableToOrder(150000, 1));
    }

    /**
     * Test: Lọc theo loại khuyến mãi
     */
    public function test_loc_theo_loai_khuyen_mai(): void
    {
        KhuyenMai::create([
            'ten_chuong_trinh' => 'Giảm %',
            'loai_giam_gia' => 'percent',
            'gia_tri_giam' => 10,
            'ngay_bat_dau' => Carbon::now(),
            'ngay_ket_thuc' => Carbon::now()->addMonth(),
            'trang_thai' => true,
        ]);

        KhuyenMai::create([
            'ten_chuong_trinh' => 'Giảm Tiền',
            'loai_giam_gia' => 'amount',
            'gia_tri_giam' => 20000,
            'ngay_bat_dau' => Carbon::now(),
            'ngay_ket_thuc' => Carbon::now()->addMonth(),
            'trang_thai' => true,
        ]);

        $response = $this->get('/admin/khuyen-mai?loai=percent');

        $response->assertStatus(200);
    }

    /**
     * Test: Lọc theo trạng thái
     */
    public function test_loc_theo_trang_thai(): void
    {
        // Active
        KhuyenMai::create([
            'ten_chuong_trinh' => 'Active',
            'loai_giam_gia' => 'percent',
            'gia_tri_giam' => 10,
            'ngay_bat_dau' => Carbon::now()->subDay(),
            'ngay_ket_thuc' => Carbon::now()->addMonth(),
            'trang_thai' => true,
        ]);

        // Upcoming
        KhuyenMai::create([
            'ten_chuong_trinh' => 'Upcoming',
            'loai_giam_gia' => 'percent',
            'gia_tri_giam' => 10,
            'ngay_bat_dau' => Carbon::now()->addDay(),
            'ngay_ket_thuc' => Carbon::now()->addMonth(),
            'trang_thai' => true,
        ]);

        $response = $this->get('/admin/khuyen-mai?trang_thai=active');

        $response->assertStatus(200);
    }

    /**
     * Test: Tìm kiếm khuyến mãi
     */
    public function test_tim_kiem_khuyen_mai(): void
    {
        KhuyenMai::create([
            'ten_chuong_trinh' => 'Siêu Sale 2024',
            'loai_giam_gia' => 'percent',
            'gia_tri_giam' => 50,
            'ngay_bat_dau' => Carbon::now(),
            'ngay_ket_thuc' => Carbon::now()->addMonth(),
            'trang_thai' => true,
            'ghi_chu' => 'Khuyến mãi lớn nhất năm',
        ]);

        $response = $this->get('/admin/khuyen-mai?q=Sieu Sale');

        $response->assertStatus(200);
    }

    /**
     * Test: Tạo khuyến mãi với giảm tối đa
     */
    public function test_khuyen_mai_co_giam_toi_da(): void
    {
        $data = [
            'ten_chuong_trinh' => 'Test Giảm Tối Đa',
            'loai_giam_gia' => 'percent',
            'gia_tri_giam' => 20,
            'giam_toi_da' => 100000,
            'ngay_bat_dau' => Carbon::now()->format('Y-m-d'),
            'ngay_ket_thuc' => Carbon::now()->addMonth()->format('Y-m-d'),
            'trang_thai' => true,
            'pham_vi' => 'hoa_don',
        ];

        $response = $this->postJson('/admin/khuyen-mai', $data);

        $response->assertStatus(302);
        $this->assertDatabaseHas('khuyen_mai', [
            'ten_chuong_trinh' => 'Test Giảm Tối Đa',
            'giam_toi_da' => 100000,
        ]);
    }

    /**
     * Test: Tạo khuyến mãi với đơn hàng tối thiểu
     */
    public function test_khuyen_mai_don_hang_toi_thieu(): void
    {
        $data = [
            'ten_chuong_trinh' => 'Test Đơn Hàng Tối Thiểu',
            'loai_giam_gia' => 'amount',
            'gia_tri_giam' => 30000,
            'don_hang_toi_thieu' => 200000,
            'ngay_bat_dau' => Carbon::now()->format('Y-m-d'),
            'ngay_ket_thuc' => Carbon::now()->addMonth()->format('Y-m-d'),
            'trang_thai' => true,
            'pham_vi' => 'hoa_don',
        ];

        $response = $this->postJson('/admin/khuyen-mai', $data);

        $response->assertStatus(302);
        $this->assertDatabaseHas('khuyen_mai', [
            'ten_chuong_trinh' => 'Test Đơn Hàng Tối Thiểu',
            'don_hang_toi_thieu' => 200000,
        ]);
    }

    /**
     * Test: Toggle khi không trong thời gian áp dụng
     */
    public function test_toggle_khong_trong_thoi_gian_ap_dung(): void
    {
        $khuyenMai = KhuyenMai::create([
            'ten_chuong_trinh' => 'Test Toggle Thất Bại',
            'loai_giam_gia' => 'percent',
            'gia_tri_giam' => 10,
            'ngay_bat_dau' => Carbon::now()->subMonth(),
            'ngay_ket_thuc' => Carbon::now()->subDay(),
            'trang_thai' => true,
        ]);

        $response = $this->post("/admin/khuyen-mai/{$khuyenMai->id}/toggle");

        $response->assertStatus(302);
        $response->assertSessionHas('error');
    }
}
