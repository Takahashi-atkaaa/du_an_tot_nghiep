<?php

namespace App\Http\Controllers\admin\Api;

use App\Http\Controllers\Controller;
use App\Models\ThuocTinhSanPham;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ThuocTinhApiController extends Controller
{
    public function getThuocTinhCon($id): JsonResponse
    {
        $thuocTinhCons = ThuocTinhSanPham::where('thuoc_tinh_cha_id', $id)
            ->where('trang_thai', true)
            ->orderBy('ten_thuoc_tinh')
            ->get(['id', 'ten_thuoc_tinh']);

        return response()->json($thuocTinhCons);
    }

    public function getAllThuocTinhCon(): JsonResponse
    {
        $thuocTinhCons = ThuocTinhSanPham::whereNotNull('thuoc_tinh_cha_id')
            ->where('trang_thai', true)
            ->orderBy('ten_thuoc_tinh')
            ->get(['id', 'ten_thuoc_tinh', 'thuoc_tinh_cha_id']);

        return response()->json($thuocTinhCons);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'ten_thuoc_tinh' => 'required|string|max:255',
            'thuoc_tinh_cha_id' => 'nullable|integer|exists:thuoc_tinh_san_pham,id',
            'loai' => 'required|in:cha,con',
        ]);

        $thuocTinh = ThuocTinhSanPham::create([
            'ten_thuoc_tinh' => $data['ten_thuoc_tinh'],
            'thuoc_tinh_cha_id' => $data['loai'] === 'con' ? ($data['thuoc_tinh_cha_id'] ?? null) : null,
            'trang_thai' => true,
        ]);

        return response()->json([
            'success' => true,
            'data' => $thuocTinh,
            'message' => 'Đã tạo thuộc tính thành công.',
        ]);
    }
}
