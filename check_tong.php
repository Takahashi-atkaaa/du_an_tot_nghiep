<?php
$rows = \Illuminate\Support\Facades\DB::select("SELECT id_phieu, SUM(so_luong * gia_nhap) as tong FROM chi_tiet_phieu WHERE id_phieu IN (54, 45, 42, 41) GROUP BY id_phieu");
echo json_encode($rows) . PHP_EOL;
