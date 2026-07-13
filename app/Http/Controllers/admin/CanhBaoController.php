<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\CanhBao;
use App\Services\CanhBaoService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CanhBaoController extends Controller
{
    public function __construct(private CanhBaoService $cb) {}

    public function index(Request $request): View
    {
        $query = CanhBao::with('nguoiDungThucHien', 'auditLog')
            ->orderByDesc('created_at');

        if ($request->filled('chua_doc')) {
            $query->where('da_doc', false);
        }

        if ($request->filled('tu_ngay')) {
            $query->whereDate('created_at', '>=', $request->tu_ngay);
        }
        if ($request->filled('den_ngay')) {
            $query->whereDate('created_at', '<=', $request->den_ngay);
        }

        $canhBaos = $query->paginate(15)->withQueryString();
        $soChuaDoc = CanhBao::where('da_doc', false)->count();

        return view('admin_xem_truoc.canh-bao.index', [
            'canhBaos' => $canhBaos,
            'soChuaDoc' => $soChuaDoc,
            'boLoc' => [
                'chua_doc' => $request->input('chua_doc'),
                'tu_ngay' => $request->input('tu_ngay'),
                'den_ngay' => $request->input('den_ngay'),
            ],
        ]);
    }

    public function chiTiet(int $id): View
    {
        $canhBao = CanhBao::with('nguoiDungThucHien')
            ->findOrFail($id);

        if (! $canhBao->da_doc) {
            $this->cb->danhDauDaDoc($canhBao->id, auth()->id() ?? 0);
            $canhBao->refresh();
        }

        return view('admin_xem_truoc.canh-bao.chi-tiet', [
            'canhBao' => $canhBao,
        ]);
    }

    public function danhDauDaDoc(Request $request, int $id): RedirectResponse
    {
        $this->cb->danhDauDaDoc($id, auth()->id() ?? 0);

        return back()->with('success', 'Đã đánh dấu hoạt động là đã xem.');
    }

    public function danhDauTatCaDaDoc(Request $request): RedirectResponse
    {
        $soLuong = $this->cb->danhDauTatCaDaDoc(auth()->id() ?? 0);

        return back()->with('success', 'Đã đánh dấu ' . $soLuong . ' hoạt động là đã xem.');
    }
}