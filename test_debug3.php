<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$controller = new App\Http\Controllers\Admin\Api\SanPhamApiController();

// Test 1: CHỈ product, không có variant_id
$request = new Illuminate\Http\Request();
$response = $controller->show(13, $request);
echo "=== Test 1: No variant_id ===\n";
$data = json_decode($response->getContent(), true);
if (isset($data['data']['product'])) {
    echo "keys: " . implode(", ", array_keys($data['data'])) . "\n";
    echo "sanPham.ten_don_vi: " . ($data['data']['sanPham']['ten_don_vi'] ?? 'NULL') . "\n";
    echo "sanPham.ma_vach: " . ($data['data']['sanPham']['ma_vach'] ?? 'NULL') . "\n";
    echo "product.ten_don_vi: " . ($data['data']['product']['ten_don_vi'] ?? 'NULL') . "\n";
    echo "product.donVi: " . (isset($data['data']['product']['donVi']) ? json_encode($data['data']['product']['donVi']) : 'NO') . "\n";
}

echo "\n";

// Test 2: với variant_id = 26
$request = new Illuminate\Http\Request();
$request->merge(['variant_id' => '26']);
$response = $controller->show(13, $request);
echo "=== Test 2: variant_id=26 ===\n";
$data = json_decode($response->getContent(), true);
echo "sanPham.ten_don_vi: " . ($data['data']['sanPham']['ten_don_vi'] ?? 'NULL') . "\n";
echo "sanPham.ma_vach: " . ($data['data']['sanPham']['ma_vach'] ?? 'NULL') . "\n";
echo "don_vi: " . (isset($data['data']['don_vi']) ? json_encode($data['data']['don_vi']) : 'NO') . "\n";