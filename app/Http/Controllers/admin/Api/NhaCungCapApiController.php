<?php

namespace App\Http\Controllers\admin\Api;

use App\Http\Controllers\Controller;
use App\Models\NhaCungCap;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class NhaCungCapApiController extends Controller
{
    // GET /admin/api/nha-cung-cap
    public function index(Request $request)
    {
        $query = NhaCungCap::query();

        if ($request->filled('q')) {
            $q = $request->input('q');
            $query->where(function ($w) use ($q) {
                $w->where('ten_nha_cung_cap', 'like', "%{$q}%")
                  ->orWhere('email', 'like', "%{$q}%")
                  ->orWhere('so_dien_thoai', 'like', "%{$q}%");
            });
        }

        $items = $query->orderBy('ten_nha_cung_cap', 'asc')
            ->paginate(15, ['*'], 'page', $request->input('page', 1))
            ->withQueryString();

        return response()->json([
            'success' => true,
            'data' => $items,
        ]);
    }

    // POST /admin/api/nha-cung-cap
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ten_nha_cung_cap' => 'required|string|max:255|unique:nha_cung_cap,ten_nha_cung_cap',
            'email'            => 'nullable|email|max:255',
            'so_dien_thoai'    => 'required|string|max:50',
            'nguoi_dai_dien'   => 'nullable|string|max:255',
        ], [
            'ten_nha_cung_cap.required' => 'Vui lòng nhập tên nhà cung cấp.',
            'ten_nha_cung_cap.unique'  => 'Tên nhà cung cấp này đã tồn tại.',
            'so_dien_thoai.required'    => 'Vui lòng nhập số điện thoại.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'errors'  => $validator->errors(),
            ], 422);
        }

        $item = NhaCungCap::create($validator->validated());

        return response()->json([
            'success' => true,
            'message' => 'Thêm nhà cung cấp thành công.',
            'item'    => $item,
        ], 201);
    }

    // PUT /admin/api/nha-cung-cap/{id}
    public function update(Request $request, $id)
    {
        $item = NhaCungCap::find($id);
        if (!$item) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy nhà cung cấp.',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'ten_nha_cung_cap' => 'required|string|max:255|unique:nha_cung_cap,ten_nha_cung_cap,' . $id,
            'email'            => 'nullable|email|max:255',
            'so_dien_thoai'    => 'required|string|max:50',
            'nguoi_dai_dien'   => 'nullable|string|max:255',
        ], [
            'ten_nha_cung_cap.required' => 'Vui lòng nhập tên nhà cung cấp.',
            'ten_nha_cung_cap.unique'  => 'Tên nhà cung cấp này đã tồn tại.',
            'so_dien_thoai.required'    => 'Vui lòng nhập số điện thoại.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'errors'  => $validator->errors(),
            ], 422);
        }

        $item->update($validator->validated());

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật nhà cung cấp thành công.',
            'item'    => $item->fresh(),
        ]);
    }

    // DELETE /admin/api/nha-cung-cap/{id}
    public function destroy($id)
    {
        $item = NhaCungCap::find($id);
        if (!$item) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy nhà cung cấp.',
            ], 404);
        }

        $item->delete();

        return response()->json([
            'success' => true,
            'message' => 'Xóa nhà cung cấp thành công.',
        ]);
    }

    // GET /admin/api/nha-cung-cap/dropdown
    public function dropdown()
    {
        $items = NhaCungCap::orderBy('ten_nha_cung_cap', 'asc')
            ->get(['id', 'ten_nha_cung_cap'])
            ->map(fn($ncc) => [
                'id'   => $ncc->id,
                'ten_nha_cung_cap' => $ncc->ten_nha_cung_cap,
            ]);

        return response()->json([
            'success' => true,
            'data'    => $items,
        ]);
    }
}
