<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class PhieuNhapTemplateExport implements FromView, ShouldAutoSize, WithEvents
{
    public function view(): View
    {
        return view('exports.phieu-nhap-template');
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                // Freeze header row
                $event->sheet->freezePane('A2');
                
                // Style header
                $event->sheet->getStyle('A1:H1')->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '4472C4']],
                    'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
                    'borders' => ['allBorders' => ['borderStyle' => 'thin', 'color' => ['rgb' => '000000']]],
                ]);
                
                // Text format for Ma_vach (column A) to preserve leading zeros
                $event->sheet->getStyle('A:A')->getNumberFormat()
                    ->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_TEXT);
            },
        ];
    }
}
