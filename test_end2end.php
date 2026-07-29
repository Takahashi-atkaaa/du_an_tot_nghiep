<?php
// Test end-to-end: gọi store() qua HTTP thật, login admin trước

require __DIR__ . '/vendor/autoload.php';

$baseUrl = 'http://127.0.0.1:8765';
$cookieJar = __DIR__ . '/cookies.txt';
@unlink($cookieJar);

// Bước 1: GET login page
$ch = curl_init($baseUrl . '/admin/login');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieJar);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieJar);
$html = curl_exec($ch);
curl_close($ch);
preg_match('/name="_token"\s+value="([^"]+)"/', $html, $m);
$csrfLogin = $m[1] ?? '';

// Bước 2: POST login (tìm user admin)
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = \App\Models\User::where('email', 'admin@admin.com')->first()
    ?? \App\Models\User::first();
if (!$user) {
    echo "Không tìm thấy user admin\n";
    exit;
}
echo "Login với user: {$user->email}\n";

$ch = curl_init($baseUrl . '/admin/login');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, [
    '_token' => $csrfLogin,
    'email' => $user->email,
    'password' => 'password', // thử password mặc định
]);
curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieJar);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieJar);
curl_setopt($ch, CURLOPT_HEADER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
$loginResp = curl_exec($ch);
$loginStatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "Login HTTP Status: $loginStatus\n";
if ($loginStatus == 302) {
    echo "Login OK\n";
} else {
    echo "Login FAIL — bỏ qua test (cần set password đúng)\n";
    @unlink($cookieJar);
    exit;
}

// Bước 3: Tạo thuộc tính
$viCha = \App\Models\ThuocTinhSanPham::firstOrCreate(
    ['ten_thuoc_tinh' => 'Vị', 'thuoc_tinh_cha_id' => null, 'deleted_at' => null],
    ['trang_thai' => true]
);
$cam = \App\Models\ThuocTinhSanPham::firstOrCreate(
    ['ten_thuoc_tinh' => 'Cam', 'thuoc_tinh_cha_id' => $viCha->id, 'deleted_at' => null],
    ['trang_thai' => true]
);
$dau = \App\Models\ThuocTinhSanPham::firstOrCreate(
    ['ten_thuoc_tinh' => 'Dâu', 'thuoc_tinh_cha_id' => $viCha->id, 'deleted_at' => null],
    ['trang_thai' => true]
);
$danhMuc = \App\Models\DanhMucSanPham::first();

// Bước 4: GET form để lấy CSRF mới
$ch = curl_init($baseUrl . '/admin/san-pham');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieJar);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieJar);
$html = curl_exec($ch);
curl_close($ch);
preg_match('/name="csrf-token"\s+content="([^"]+)"/', $html, $m);
$csrf = $m[1] ?? $csrfLogin;

// Bước 5: POST tạo sản phẩm
$postFields = [
    '_token' => $csrf,
    'ten_san_pham' => 'Vinamilk END2END ' . time(),
    'id_danh_muc' => (string)$danhMuc->id,
    'thuong_hieu' => 'Vinamilk',
    'mo_ta' => 'Test end-to-end',
    'trang_thai' => '1',
    'loai_bien_the' => 'thuoc_tinh',
    'bien_the[0][ten_bien_the]' => 'Vị Cam - hộp',
    'bien_the[0][la_don_vi]' => '0',
    'bien_the[0][ten_don_vi]' => '',
    'bien_the[0][thuoc_tinh_ids]' => (string)$cam->id,
    'bien_the[0][ty_le]' => '1',
    'bien_the[0][ten_don_vi_bien_the]' => 'hộp',
    'bien_the[0][ma_hang]' => 'E2E-CAM-HOP',
    'bien_the[0][ma_vach]' => '',
    'bien_the[0][gia_von]' => '100',
    'bien_the[0][gia_ban]' => '150',
    'bien_the[0][so_luong_ton]' => '0',
    'bien_the[0][dinh_muc_toi_thieu]' => '0',
    'bien_the[1][ten_bien_the]' => 'Vị Cam - thùng',
    'bien_the[1][la_don_vi]' => '0',
    'bien_the[1][ten_don_vi]' => '',
    'bien_the[1][thuoc_tinh_ids]' => (string)$cam->id,
    'bien_the[1][ty_le]' => '24',
    'bien_the[1][ten_don_vi_bien_the]' => 'thùng',
    'bien_the[1][ma_hang]' => 'E2E-CAM-THUNG',
    'bien_the[1][ma_vach]' => '',
    'bien_the[1][gia_von]' => '2400',
    'bien_the[1][gia_ban]' => '3500',
    'bien_the[1][so_luong_ton]' => '0',
    'bien_the[1][dinh_muc_toi_thieu]' => '0',
    'bien_the[2][ten_bien_the]' => 'Vị Dâu - hộp',
    'bien_the[2][la_don_vi]' => '0',
    'bien_the[2][ten_don_vi]' => '',
    'bien_the[2][thuoc_tinh_ids]' => (string)$dau->id,
    'bien_the[2][ty_le]' => '1',
    'bien_the[2][ten_don_vi_bien_the]' => 'hộp',
    'bien_the[2][ma_hang]' => 'E2E-DAU-HOP',
    'bien_the[2][ma_vach]' => '',
    'bien_the[2][gia_von]' => '100',
    'bien_the[2][gia_ban]' => '150',
    'bien_the[2][so_luong_ton]' => '0',
    'bien_the[2][dinh_muc_toi_thieu]' => '0',
    'bien_the[3][ten_bien_the]' => 'Vị Dâu - thùng',
    'bien_the[3][la_don_vi]' => '0',
    'bien_the[3][ten_don_vi]' => '',
    'bien_the[3][thuoc_tinh_ids]' => (string)$dau->id,
    'bien_the[3][ty_le]' => '24',
    'bien_the[3][ten_don_vi_bien_the]' => 'thùng',
    'bien_the[3][ma_hang]' => 'E2E-DAU-THUNG',
    'bien_the[3][ma_vach]' => '',
    'bien_the[3][gia_von]' => '2400',
    'bien_the[3][gia_ban]' => '3500',
    'bien_the[3][so_luong_ton]' => '0',
    'bien_the[3][dinh_muc_toi_thieu]' => '0',
];

$ch = curl_init($baseUrl . '/admin/san-pham');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieJar);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieJar);
curl_setopt($ch, CURLOPT_HEADER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
$resp = curl_exec($ch);
$status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$hSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
$headers = substr($resp, 0, $hSize);
curl_close($ch);

echo "POST /admin/san-pham Status: $status\n";
foreach (array_slice(explode("\r\n", $headers), 0, 6) as $h) echo "  $h\n";

echo "\n=== Laravel log (last 5) ===\n";
$log = file(__DIR__ . '/storage/logs/laravel.log');
foreach (array_slice($log, -5) as $line) echo $line;

echo "\n=== DB check ===\n";
$lastProduct = \App\Models\Product::orderBy('id', 'desc')->first();
if ($lastProduct && strpos($lastProduct->ten_san_pham, 'END2END') !== false) {
    echo "OK: Product mới tạo: ID={$lastProduct->id}, ten={$lastProduct->ten_san_pham}\n";
    $variants = \App\Models\BienTheSanPham::where('product_id', $lastProduct->id)->get();
    echo "BienThe count: " . $variants->count() . "\n";
    foreach ($variants as $v) {
        $units = \App\Models\DonViQuyDoi::where('variant_id', $v->id)->get();
        echo "  - Variant #{$v->id}: ten={$v->ten_bien_the}, thuoc_tinh_ids=" . json_encode($v->thuoc_tinh_ids) . ", units=" . $units->count() . "\n";
        foreach ($units as $u) {
            echo "    - Unit #{$u->id}: {$u->ten_don_vi} (ty_le={$u->so_luong_san_pham_trong_don_vi}, variant_id={$u->variant_id})\n";
        }
    }
} else {
    echo "FAIL: Không tạo được sản phẩm. Last product: " . ($lastProduct ? $lastProduct->ten_san_pham : 'none') . "\n";
}

@unlink($cookieJar);