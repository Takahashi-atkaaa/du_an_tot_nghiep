<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class SanPhamImportTemplateExport implements FromView, ShouldAutoSize, WithEvents
{
    public function view(): View
    {
        return view('exports.san-pham-import-template');
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => static function (AfterSheet $event): void {
                $event->sheet->freezePane('A2');
                $event->sheet->getDelegate()->getStyle('A1:Q1')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'color' => ['rgb' => 'FFFFFF'],
                    ],
                    'fill' => [
                        'fillType' => 'solid',
                        'startColor' => ['rgb' => '4472C4'],
                    ],
                    'alignment' => [
                        'horizontal' => 'center',
                        'vertical' => 'center',
                        'wrapText' => true,
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => 'thin',
                            'color' => ['rgb' => '000000'],
                        ],
                    ],
                ]);
                $event->sheet->getDelegate()->getRowDimension(1)->setRowHeight(32);
            },
        ];
    }
}
