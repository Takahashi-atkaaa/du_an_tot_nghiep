<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class PhieuXuatTemplateExport implements FromView, ShouldAutoSize
{
    public function view(): View
    {
        return view('exports.phieu-xuat-template');
    }
}
