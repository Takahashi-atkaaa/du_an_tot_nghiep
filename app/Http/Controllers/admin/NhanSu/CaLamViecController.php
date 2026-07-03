<?php

namespace App\Http\Controllers\admin\NhanSu;

use App\Http\Controllers\Controller;
use App\Models\CaLamViec;
use App\Http\Requests\NhanSu\StoreCaLamViecRequest;
use App\Http\Requests\NhanSu\UpdateCaLamViecRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CaLamViecController extends Controller
{
    public function index(): View
    {
        // Order by fixed sequence: SA -> CH -> TO -> TO3
        $caLamViecs = CaLamViec::query()
            ->orderByRaw("FIELD(ten_ca, 'SA1','SA2','CH1','CH2','TO1','TO2','TO3')")
            ->orderBy('gio_bat_dau')
            ->paginate(5);

        return view('admin_xem_truoc.ca-lam-viec.index', compact('caLamViecs'));
    }

    public function create(): View
    {
        return view('admin_xem_truoc.ca-lam-viec.create', [
            'caLamViec' => new CaLamViec(),
        ]);
    }

    public function store(StoreCaLamViecRequest $request): RedirectResponse
    {
        CaLamViec::create($request->validated());

        return redirect()
            ->route('ca-lam-viec.index')
            ->with('success', 'Đã tạo ca làm việc mới.');
    }

    public function edit(CaLamViec $caLamViec): View
    {
        return view('admin_xem_truoc.ca-lam-viec.edit', compact('caLamViec'));
    }

    public function update(UpdateCaLamViecRequest $request, CaLamViec $caLamViec): RedirectResponse
    {
        $caLamViec->update($request->validated());

        return redirect()
            ->route('ca-lam-viec.index')
            ->with('success', 'Đã cập nhật ca làm việc.');
    }

    public function destroy(CaLamViec $caLamViec): RedirectResponse
    {
        $caLamViec->delete();

        return redirect()
            ->route('ca-lam-viec.index')
            ->with('success', 'Đã hủy ca làm việc.');
    }
}
