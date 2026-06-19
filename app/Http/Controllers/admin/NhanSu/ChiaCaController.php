<?php

namespace App\Http\Controllers\admin\NhanSu;

use App\Http\Controllers\Controller;
use App\Models\CaLamViec;
use App\Models\ChiaCaLamViec;
use App\Models\NguoiDung;
use App\Requests\NhanSu\PhanCongCaLamViecRequest;
use Carbon\Carbon;
use DOMDocument;
use DOMXPath;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;


class ChiaCaController extends Controller
{
    public function index(Request $request): View
    {
        $keyword = trim((string) $request->query('keyword', ''));
        $caLamViecId = $request->integer('id_ca_lam_viec');
        $ngay = $request->query('ngay');

        $weekSource = $ngay ?: $request->query('week_start');
        $weekStart = $this->resolveWeekStart($weekSource);
        $selectedWeekDate = $weekSource
            ? Carbon::parse($weekSource)->toDateString()
            : $weekStart->toDateString();
        $weekDates = $this->weekDates($weekStart);
        $caLamViecs = $this->caLamViecs();

        $nguoiDungs = NguoiDung::query()
            ->with('vaiTro')
            ->where('trang_thai', 1)
            ->whereHas('vaiTro', function ($query) {
                $query->whereIn('ten_vai_tro', ['Nhân viên', 'Trưởng ca']);
            })
            ->when($keyword !== '', function ($query) use ($keyword) {
                $query->where('ho_ten', 'like', '%' . $keyword . '%');
            })
            ->when($caLamViecId || $ngay, function ($query) use ($weekStart, $caLamViecId, $ngay) {
                $query->whereHas('chiaCaLamViecs', function ($subQuery) use ($weekStart, $caLamViecId, $ngay) {
                    $subQuery->whereBetween('ngay', [
                        $weekStart->toDateString(),
                        $weekStart->copy()->addDays(6)->toDateString(),
                    ]);

                    if ($caLamViecId) {
                        $subQuery->where('id_ca_lam_viec', $caLamViecId);
                    }

                    if ($ngay) {
                        $subQuery->whereDate('ngay', $ngay);
                    }
                });
            })
            ->orderBy('ho_ten')
            ->get(['id', 'ho_ten', 'id_vai_tro']);

        $lichTheoTuan = ChiaCaLamViec::query()
            ->with(['nguoiDung.vaiTro', 'caLamViec'])
            ->whereHas('nguoiDung', function ($query) {
                $query->whereHas('vaiTro', function ($roleQuery) {
                    $roleQuery->whereIn('ten_vai_tro', ['Nhân viên', 'Trưởng ca']);
                });
            })
            ->whereBetween('ngay', [
                $weekStart->toDateString(),
                $weekStart->copy()->addDays(6)->toDateString(),
            ])
            ->when($keyword !== '', function ($query) use ($keyword) {
                $query->whereHas('nguoiDung', function ($subQuery) use ($keyword) {
                    $subQuery->where('ho_ten', 'like', '%' . $keyword . '%');
                });
            })
            ->when($caLamViecId, function ($query) use ($caLamViecId) {
                $query->where('id_ca_lam_viec', $caLamViecId);
            })
            ->when($ngay, function ($query) use ($ngay) {
                $query->whereDate('ngay', $ngay);
            })
            ->orderBy('ngay')
            ->orderBy('id_nguoi_dung')
            ->get();

        $maTranLich = [];

        foreach ($nguoiDungs as $nguoiDung) {
            foreach ($weekDates as $date) {
                $maTranLich[$nguoiDung->id][$date->toDateString()] = [];
            }
        }

        foreach ($lichTheoTuan as $lich) {
            $ngay = Carbon::parse($lich->ngay)->toDateString();
            $maTranLich[$lich->id_nguoi_dung][$ngay][] = $lich;
        }

        $canhBaoTruongCa = $lichTheoTuan
            ->groupBy(function ($lich) {
                return Carbon::parse($lich->ngay)->toDateString() . '|' . $lich->id_ca_lam_viec;
            })
            ->filter(function ($items) {
                return ! $items->contains(function ($lich) {
                    return $this->normalizeShiftRole((string) ($lich->vai_tro_trong_ca ?? '')) === 'truong_ca';
                });
            })
            ->map(function ($items) {
                $lich = $items->first();

                return [
                    'ngay' => Carbon::parse($lich->ngay)->format('d/m/Y'),
                    'ca' => $lich->caLamViec?->ten_ca ?? 'Không xác định',
                ];
            })
            ->values();

        $lichTheoNgayVaCa = $lichTheoTuan
            ->groupBy(function ($lich) {
                return Carbon::parse($lich->ngay)->toDateString() . '|' . $lich->id_ca_lam_viec;
            });

        $chiTietCanhBaoTheoCa = [];
        $canhBaoThieuNhanSu = collect();

        foreach ($lichTheoNgayVaCa as $groupKey => $items) {
            $lichDauTien = $items->first();
            $caLamViec = $lichDauTien->caLamViec;
            $soNhanVien = $items->count();
            $soToiThieu = (int) ($caLamViec?->so_nhan_vien_toi_thieu ?? 0);
            $soTruongCa = $items->filter(function ($lich) {
                return $this->normalizeShiftRole((string) ($lich->vai_tro_trong_ca ?? '')) === 'truong_ca';
            })->count();

            $thieuTruongCa = $soTruongCa === 0;
            $nhieuTruongCa = $soTruongCa > 1;
            $thieuNhanSu = $soToiThieu > 0 && $soNhanVien < $soToiThieu;

            $chiTietCanhBaoTheoCa[$groupKey] = [
                'thieu_truong_ca' => $thieuTruongCa,
                'nhieu_truong_ca' => $nhieuTruongCa,
                'thieu_nhan_su' => $thieuNhanSu,
                'so_truong_ca' => $soTruongCa,
                'so_nhan_vien' => $soNhanVien,
                'so_toi_thieu' => $soToiThieu,
            ];

            if ($thieuNhanSu) {
                $canhBaoThieuNhanSu->push([
                    'ngay' => Carbon::parse($lichDauTien->ngay)->format('d/m/Y'),
                    'ca' => $caLamViec?->ten_ca ?? 'KhÃ´ng xÃ¡c Ä‘á»‹nh',
                    'so_nhan_vien' => $soNhanVien,
                    'so_toi_thieu' => $soToiThieu,
                ]);
            }

            if ($nhieuTruongCa) {
                $canhBaoTruongCa->push([
                    'ngay' => Carbon::parse($lichDauTien->ngay)->format('d/m/Y'),
                    'ca' => $caLamViec?->ten_ca ?? 'KhÃ´ng xÃ¡c Ä‘á»‹nh',
                    'so_truong_ca' => $soTruongCa,
                    'loai' => 'nhieu_truong_ca',
                ]);
            } elseif ($thieuTruongCa) {
                $canhBaoTruongCa->push([
                    'ngay' => Carbon::parse($lichDauTien->ngay)->format('d/m/Y'),
                    'ca' => $caLamViec?->ten_ca ?? 'KhÃ´ng xÃ¡c Ä‘á»‹nh',
                    'so_truong_ca' => $soTruongCa,
                    'loai' => 'thieu_truong_ca',
                ]);
            }
        }

        $canhBaoCaChuaCoNhanVien = collect();

        foreach ($weekDates as $date) {
            $ngayDateString = $date->toDateString();
            $ngayHienThi = $date->format('d/m/Y');

            foreach ($caLamViecs as $caLamViec) {
                $groupKey = $ngayDateString . '|' . $caLamViec->id;

                if (! $lichTheoNgayVaCa->has($groupKey)) {
                    $canhBaoCaChuaCoNhanVien->push([
                        'ngay' => $ngayHienThi,
                        'ca' => $caLamViec->ten_ca,
                    ]);
                }
            }
        }

        return view('admin_xem_truoc.chia-ca-lam-viec.danh-sach', [
            'nguoiDungs' => $nguoiDungs,
            'weekStart' => $weekStart,
            'selectedWeekDate' => $selectedWeekDate,
            'weekDates' => $weekDates,
            'maTranLich' => $maTranLich,
            'chiTietCanhBaoTheoCa' => $chiTietCanhBaoTheoCa,
            'canhBaoTruongCa' => $canhBaoTruongCa,
            'canhBaoThieuNhanSu' => $canhBaoThieuNhanSu,
            'canhBaoCaChuaCoNhanVien' => $canhBaoCaChuaCoNhanVien,
            'caLamViecs' => $caLamViecs,
            'keyword' => $keyword,
            'caLamViecId' => $caLamViecId ?: null,
            'ngay' => $ngay,
        ]);
    }

    public function create(Request $request): View
    {
        $weekSource = $request->query('week_start');
        $weekStart = $this->resolveWeekStart($weekSource);
        $selectedWeekDate = $weekSource
            ? Carbon::parse($weekSource)->toDateString()
            : $weekStart->toDateString();

        return view('admin_xem_truoc.chia-ca-lam-viec.nhap-excel', [
            'weekStart' => $weekStart,
            'selectedWeekDate' => $selectedWeekDate,
            'weekDates' => $this->weekDates($weekStart),
            'caLamViecs' => $this->caLamViecs(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'tep_lich' => ['required', 'file', 'mimes:xml,xls', 'max:5120'],
        ], [
            'tep_lich.required' => 'Vui lòng chọn file lịch làm việc.',
            'tep_lich.file' => 'File tải lên không hợp lệ.',
            'tep_lich.mimes' => 'Vui lòng tải lên file .xls hoặc .xml được xuất từ hệ thống.',
            'tep_lich.max' => 'Kích thước file không được vượt quá 5MB.',
        ]);

        $parsed = $this->parseXmlScheduleWithRoleSupport($validated['tep_lich']->getRealPath());

        if (! empty($parsed['errors'])) {
            return back()
                ->withInput()
                ->withErrors(['tep_lich' => implode(' ', $parsed['errors'])]);
        }

        if (empty($parsed['rows'])) {
            return back()
                ->withInput()
                ->withErrors([
                    'tep_lich' => 'Không tìm thấy ca làm nào hợp lệ trong file import. Hãy kiểm tra lại ô đăng ký ca và định dạng file.',
                ]);
        }

        DB::transaction(function () use ($parsed) {
            ChiaCaLamViec::query()
                ->whereIn('ngay', $parsed['dates'])
                ->delete();

            if (! empty($parsed['rows'])) {
                ChiaCaLamViec::query()->insert($parsed['rows']);
            }
        });

        return redirect()
            ->route('chia-ca-lam-viec.index', ['week_start' => $parsed['week_start']])
            ->with('success', 'Đã nhập lịch làm việc chính thức thành công.')
            ->with('warning', ! empty($parsed['warnings']) ? implode(' ', $parsed['warnings']) : null);
    }

    public function edit(ChiaCaLamViec $chiaCaLamViec): View
    {
        $weekSource = request()->query('week_start');
        $selectedWeekDate = $weekSource
            ? Carbon::parse($weekSource)->toDateString()
            : Carbon::parse($chiaCaLamViec->ngay)->toDateString();

        return view('admin_xem_truoc.chia-ca-lam-viec.cap-nhat', [
            'chiaCaLamViec' => $chiaCaLamViec,
            'nguoiDungs' => $this->nguoiDungs(),
            'caLamViecs' => $this->caLamViecs(),
            'selectedWeekDate' => $selectedWeekDate,
        ]);
    }

    public function update(PhanCongCaLamViecRequest $request, ChiaCaLamViec $chiaCaLamViec): RedirectResponse
    {
        $chiaCaLamViec->update($request->validated());

        $selectedWeekDate = $request->input('week_start')
            ? Carbon::parse($request->input('week_start'))->toDateString()
            : Carbon::parse($chiaCaLamViec->ngay)->toDateString();

        return redirect()
            ->route('chia-ca-lam-viec.index', ['week_start' => $selectedWeekDate])
            ->with('success', 'Đã cập nhật lịch phân ca thành công.');
    }

    public function destroy(ChiaCaLamViec $chiaCaLamViec): RedirectResponse
    {
        $chiaCaLamViec->delete();

        return redirect()
            ->route('chia-ca-lam-viec.index')
            ->with('success', 'Đã xóa lịch phân ca.');
    }

    public function destroyCell(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'id_nguoi_dung' => ['required', 'integer', 'exists:nguoi_dung,id'],
            'ngay' => ['required', 'date'],
            'week_start' => ['nullable', 'date'],
            'keyword' => ['nullable', 'string'],
            'id_ca_lam_viec' => ['nullable', 'integer'],
            'ngay_loc' => ['nullable', 'date'],
        ]);

        ChiaCaLamViec::query()
            ->where('id_nguoi_dung', $validated['id_nguoi_dung'])
            ->whereDate('ngay', $validated['ngay'])
            ->delete();

        return redirect()
            ->route('chia-ca-lam-viec.index', array_filter([
                'week_start' => $validated['week_start'] ?? null,
                'keyword' => $validated['keyword'] ?? null,
                'id_ca_lam_viec' => $validated['id_ca_lam_viec'] ?? null,
                'ngay' => $validated['ngay_loc'] ?? null,
            ], fn ($value) => $value !== null && $value !== ''))
            ->with('success', 'Đã xóa tất cả ca trong ô lịch của nhân viên.');
    }

    public function export(Request $request): StreamedResponse
    {
        $weekStart = $this->resolveWeekStart($request->query('week_start'));
        $xml = $this->buildExcelXmlWithRole(
            $weekStart,
            $this->weekDates($weekStart),
            $this->nguoiDungs(),
            $this->caLamViecs()
        );

        $fileName = 'lich-lam-viec-tuan-' . $weekStart->format('Y-m-d') . '.xls';

        return response()->streamDownload(function () use ($xml) {
            echo $xml;
        }, $fileName, [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    private function nguoiDungs()
    {
        return NguoiDung::query()
            ->with('vaiTro')
            ->where('trang_thai', 1)
            ->whereHas('vaiTro', function ($query) {
                $query->whereIn('ten_vai_tro', ['Nhân viên', 'Trưởng ca']);
            })
            ->orderBy('ho_ten')
            ->get(['id', 'ho_ten', 'id_vai_tro']);
    }

    private function caLamViecs()
    {
        return CaLamViec::query()
            ->orderBy('gio_bat_dau')
            ->get(['id', 'ten_ca', 'gio_bat_dau', 'gio_ket_thuc', 'so_nhan_vien_toi_thieu', 'so_nhan_vien_toi_da']);
    }

    private function resolveWeekStart(?string $weekStart): Carbon
    {
        return $weekStart
            ? Carbon::parse($weekStart)->startOfWeek(Carbon::MONDAY)
            : now()->startOfWeek(Carbon::MONDAY);
    }

    private function weekDates(Carbon $weekStart)
    {
        return collect(range(0, 6))
            ->map(fn (int $dayOffset) => $weekStart->copy()->addDays($dayOffset));
    }

    private function buildExcelXml(Carbon $weekStart, $weekDates, $nguoiDungs, $caLamViecs): string
    {
        $caTheoNgayText = $caLamViecs
            ->pluck('ten_ca')
            ->implode(', ');

        $xml = [];
        $xml[] = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml[] = '<?mso-application progid="Excel.Sheet"?>';
        $xml[] = '<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet" xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet" xmlns:html="http://www.w3.org/TR/REC-html40">';
        $xml[] = '<Styles>';
        $xml[] = '<Style ss:ID="Base"><Alignment ss:Vertical="Center" ss:WrapText="1"/><Borders><Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/><Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/><Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/><Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/></Borders><Protection ss:Protected="1"/></Style>';
        $xml[] = '<Style ss:ID="Editable"><Alignment ss:Vertical="Center" ss:WrapText="1"/><Borders><Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/><Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/><Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/><Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/></Borders><Protection ss:Protected="0"/></Style>';
        $xml[] = '<Style ss:ID="Header"><Alignment ss:Horizontal="Center" ss:Vertical="Center" ss:WrapText="1"/><Font ss:Bold="1"/><Interior ss:Color="#DCE6F1" ss:Pattern="Solid"/><Borders><Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/><Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/><Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/><Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/></Borders><Protection ss:Protected="1"/></Style>';
        $xml[] = '<Style ss:ID="Title"><Alignment ss:Vertical="Center" ss:WrapText="1"/><Font ss:Bold="1" ss:Size="14"/><Protection ss:Protected="1"/></Style>';
        $xml[] = '<Style ss:ID="Hint"><Alignment ss:Vertical="Center" ss:WrapText="1"/><Interior ss:Color="#FFF2CC" ss:Pattern="Solid"/><Borders><Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/><Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/><Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/><Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/></Borders><Protection ss:Protected="1"/></Style>';
        $xml[] = '</Styles>';

        $xml[] = '<Worksheet ss:Name="Đăng ký lịch">';
        $xml[] = '<Table>';
        $xml[] = '<Column ss:Width="50"/>';
        $xml[] = '<Column ss:Width="150"/>';
        $xml[] = '<Column ss:Width="110"/>';
        foreach ($weekDates as $date) {
            $xml[] = '<Column ss:Width="130"/>';
        }

        $xml[] = '<Row ss:Height="28">' . $this->xmlCell('Lịch làm việc tuần ' . $weekStart->format('d/m/Y') . ' - ' . $weekStart->copy()->addDays(6)->format('d/m/Y'), 'Title', 'String', null, 8) . '</Row>';
        $xml[] = '<Row ss:Height="24">' . $this->xmlCell('Nhập tên ca vào từng ô. Nếu 1 ngày có nhiều ca, ngăn cách bởi dấu phẩy.', null, 'String', null, 8) . '</Row>';
        $xml[] = '<Row ss:Height="22">' . $this->xmlCell('Ví dụ: SA1, SA2', null, 'String', null, 8) . '</Row>';

        $headerCells = [
            $this->xmlCell('Mã NV', 'Header'),
            $this->xmlCell('Họ tên', 'Header'),
        ];

        foreach ($weekDates as $date) {
            $headerCells[] = $this->xmlCell($this->weekdayLabel($date) . ' - ' . $date->format('d/m/Y'), 'Header');
        }

        $xml[] = '<Row ss:Height="30">' . implode('', $headerCells) . '</Row>';

        $hintCells = [
            $this->xmlCell('', 'Hint'),
            $this->xmlCell('Ca có thể đăng ký', 'Hint'),
        ];

        foreach ($weekDates as $date) {
            $hintCells[] = $this->xmlCell($caTheoNgayText, 'Hint');
        }

        $xml[] = '<Row ss:Height="42">' . implode('', $hintCells) . '</Row>';

        foreach ($nguoiDungs as $nguoiDung) {
            $rowCells = [
                $this->xmlCell((string) $nguoiDung->id, 'Base'),
                $this->xmlCell($nguoiDung->ho_ten, 'Base'),
            ];

            foreach ($weekDates as $date) {
                $rowCells[] = $this->xmlCell('', 'Editable');
            }

            $xml[] = '<Row ss:Height="34">' . implode('', $rowCells) . '</Row>';
        }

        $xml[] = '</Table>';
        $xml[] = '<WorksheetOptions xmlns="urn:schemas-microsoft-com:office:excel"><ProtectContents>True</ProtectContents><ProtectObjects>True</ProtectObjects><ProtectScenarios>True</ProtectScenarios></WorksheetOptions>';
        $xml[] = '</Worksheet>';

        $xml[] = '<Worksheet ss:Name="Danh mục ca">';
        $xml[] = '<Table>';
        $xml[] = '<Column ss:Width="100"/>';
        $xml[] = '<Column ss:Width="100"/>';
        $xml[] = '<Column ss:Width="100"/>';
        $xml[] = '<Column ss:Width="260"/>';
        $xml[] = '<Row ss:Height="32">' . implode('', [
            $this->xmlCell('Tên ca', 'Header'),
            $this->xmlCell('Giờ bắt đầu', 'Header'),
            $this->xmlCell('Giờ kết thúc', 'Header'),
            $this->xmlCell('Hướng dẫn', 'Header'),
        ]) . '</Row>';

        foreach ($caLamViecs as $caLamViec) {
            $xml[] = '<Row ss:Height="26">' . implode('', [
                $this->xmlCell($caLamViec->ten_ca, 'Base'),
                $this->xmlCell(Carbon::parse($caLamViec->gio_bat_dau)->format('H:i'), 'Base'),
                $this->xmlCell(Carbon::parse($caLamViec->gio_ket_thuc)->format('H:i'), 'Base'),
                $this->xmlCell('Nhập đúng tên ca này vào sheet Đăng ký lịch.', 'Base'),
            ]) . '</Row>';
        }

        $xml[] = '</Table>';
        $xml[] = '<WorksheetOptions xmlns="urn:schemas-microsoft-com:office:excel"><ProtectContents>True</ProtectContents><ProtectObjects>True</ProtectObjects><ProtectScenarios>True</ProtectScenarios></WorksheetOptions>';
        $xml[] = '</Worksheet>';
        $xml[] = '</Workbook>';

        return implode('', $xml);
    }

    private function xmlCell(string $value, ?string $styleId = null, string $type = 'String', ?int $index = null, ?int $mergeAcross = null): string
    {
        $attributes = [];

        if ($styleId) {
            $attributes[] = ' ss:StyleID="' . $styleId . '"';
        }

        if ($index) {
            $attributes[] = ' ss:Index="' . $index . '"';
        }

        if ($mergeAcross) {
            $attributes[] = ' ss:MergeAcross="' . $mergeAcross . '"';
        }

        return '<Cell' . implode('', $attributes) . '><Data ss:Type="' . $type . '">' . htmlspecialchars($value, ENT_XML1 | ENT_QUOTES, 'UTF-8') . '</Data></Cell>';
    }

    private function buildExcelXmlWithRole(Carbon $weekStart, $weekDates, $nguoiDungs, $caLamViecs): string
    {
        $caTheoNgayText = $caLamViecs
            ->pluck('ten_ca')
            ->implode(', ');

        $xml = [];
        $xml[] = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml[] = '<?mso-application progid="Excel.Sheet"?>';
        $xml[] = '<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet" xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet" xmlns:html="http://www.w3.org/TR/REC-html40">';
        $xml[] = '<Styles>';
        $xml[] = '<Style ss:ID="Base"><Alignment ss:Vertical="Center" ss:WrapText="1"/><Borders><Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/><Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/><Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/><Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/></Borders><Protection ss:Protected="1"/></Style>';
        $xml[] = '<Style ss:ID="Editable"><Alignment ss:Vertical="Center" ss:WrapText="1"/><Borders><Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/><Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/><Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/><Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/></Borders><Protection ss:Protected="0"/></Style>';
        $xml[] = '<Style ss:ID="Header"><Alignment ss:Horizontal="Center" ss:Vertical="Center" ss:WrapText="1"/><Font ss:Bold="1"/><Interior ss:Color="#DCE6F1" ss:Pattern="Solid"/><Borders><Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/><Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/><Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/><Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/></Borders><Protection ss:Protected="1"/></Style>';
        $xml[] = '<Style ss:ID="Title"><Alignment ss:Vertical="Center" ss:WrapText="1"/><Font ss:Bold="1" ss:Size="14"/><Protection ss:Protected="1"/></Style>';
        $xml[] = '<Style ss:ID="Hint"><Alignment ss:Vertical="Center" ss:WrapText="1"/><Interior ss:Color="#FFF2CC" ss:Pattern="Solid"/><Borders><Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/><Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/><Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/><Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/></Borders><Protection ss:Protected="1"/></Style>';
        $xml[] = '</Styles>';

        $xml[] = '<Worksheet ss:Name="Đăng ký lịch">';
        $xml[] = '<Table>';
        $xml[] = '<Column ss:Width="50"/>';
        $xml[] = '<Column ss:Width="150"/>';
        $xml[] = '<Column ss:Width="110"/>';
        foreach ($weekDates as $date) {
            $xml[] = '<Column ss:Width="130"/>';
        }

        $xml[] = '<Row ss:Height="28">' . $this->xmlCell('Lịch làm việc tuần ' . $weekStart->format('d/m/Y') . ' - ' . $weekStart->copy()->addDays(6)->format('d/m/Y'), 'Title', 'String', null, 9) . '</Row>';
        $xml[] = '<Row ss:Height="24">' . $this->xmlCell('Nhập tên ca vào từng ô. Nếu 1 ngày có nhiều ca, ngăn cách bởi dấu phẩy.', null, 'String', null, 9) . '</Row>';
        $xml[] = '<Row ss:Height="22">' . $this->xmlCell('Ví dụ: SA1, SA2', null, 'String', null, 9) . '</Row>';

        $headerCells = [
            $this->xmlCell('Mã NV', 'Header'),
            $this->xmlCell('Họ tên', 'Header'),
            $this->xmlCell('Vai trò', 'Header'),
        ];

        foreach ($weekDates as $date) {
            $headerCells[] = $this->xmlCell($this->weekdayLabel($date) . ' - ' . $date->format('d/m/Y'), 'Header');
        }

        $xml[] = '<Row ss:Height="30">' . implode('', $headerCells) . '</Row>';

        $hintCells = [
            $this->xmlCell('', 'Hint'),
            $this->xmlCell('', 'Hint'),
            $this->xmlCell('Ca có thể đăng ký', 'Hint'),
        ];

        foreach ($weekDates as $date) {
            $hintCells[] = $this->xmlCell($caTheoNgayText, 'Hint');
        }

        $xml[] = '<Row ss:Height="42">' . implode('', $hintCells) . '</Row>';

        foreach ($nguoiDungs as $nguoiDung) {
            $rowCells = [
                $this->xmlCell((string) $nguoiDung->id, 'Base'),
                $this->xmlCell($nguoiDung->ho_ten, 'Base'),
                $this->xmlCell($this->displayUserRole($nguoiDung), 'Base'),
            ];

            foreach ($weekDates as $date) {
                $rowCells[] = $this->xmlCell('', 'Editable');
            }

            $xml[] = '<Row ss:Height="34">' . implode('', $rowCells) . '</Row>';
        }

        $xml[] = '</Table>';
        $xml[] = '<WorksheetOptions xmlns="urn:schemas-microsoft-com:office:excel"><ProtectContents>True</ProtectContents><ProtectObjects>True</ProtectObjects><ProtectScenarios>True</ProtectScenarios></WorksheetOptions>';
        $xml[] = '</Worksheet>';

        $xml[] = '<Worksheet ss:Name="Danh mục ca">';
        $xml[] = '<Table>';
        $xml[] = '<Column ss:Width="100"/>';
        $xml[] = '<Column ss:Width="100"/>';
        $xml[] = '<Column ss:Width="100"/>';
        $xml[] = '<Column ss:Width="260"/>';
        $xml[] = '<Row ss:Height="32">' . implode('', [
            $this->xmlCell('Tên ca', 'Header'),
            $this->xmlCell('Giờ bắt đầu', 'Header'),
            $this->xmlCell('Giờ kết thúc', 'Header'),
            $this->xmlCell('Hướng dẫn', 'Header'),
        ]) . '</Row>';

        foreach ($caLamViecs as $caLamViec) {
            $xml[] = '<Row ss:Height="26">' . implode('', [
                $this->xmlCell($caLamViec->ten_ca, 'Base'),
                $this->xmlCell(Carbon::parse($caLamViec->gio_bat_dau)->format('H:i'), 'Base'),
                $this->xmlCell(Carbon::parse($caLamViec->gio_ket_thuc)->format('H:i'), 'Base'),
                $this->xmlCell('Nhập đúng tên ca này vào sheet Đăng ký lịch.', 'Base'),
            ]) . '</Row>';
        }

        $xml[] = '</Table>';
        $xml[] = '<WorksheetOptions xmlns="urn:schemas-microsoft-com:office:excel"><ProtectContents>True</ProtectContents><ProtectObjects>True</ProtectObjects><ProtectScenarios>True</ProtectScenarios></WorksheetOptions>';
        $xml[] = '</Worksheet>';
        $xml[] = '</Workbook>';

        return implode('', $xml);
    }

    private function parseXmlScheduleWithRoleSupport(string $filePath): array
    {
        $dom = new DOMDocument();
        $loaded = @$dom->load($filePath);

        if (! $loaded) {
            return [
                'errors' => ['Không đọc được file XML. Vui lòng dùng file đã xuất từ hệ thống.'],
            ];
        }

        $xpath = new DOMXPath($dom);
        $spreadsheetNamespace = 'urn:schemas-microsoft-com:office:spreadsheet';

        $worksheets = $xpath->query('//*[local-name()="Worksheet"]');
        $sheet = null;

        foreach ($worksheets as $worksheet) {
            $sheetName = $worksheet->getAttributeNS($spreadsheetNamespace, 'Name');

            if ($this->normalizeName($sheetName) === 'dang ky lich') {
                $sheet = $worksheet;
                break;
            }
        }

        if (! $sheet) {
            return [
                'errors' => ['Không tìm thấy sheet Đăng ký lịch trong file XML.'],
            ];
        }

        $rows = $xpath->query('./*[local-name()="Table"]/*[local-name()="Row"]', $sheet);
        $headerRow = null;
        $dataRows = [];

        foreach ($rows as $row) {
            $values = $this->extractRowValues($row, $spreadsheetNamespace);

            if (
                $headerRow === null
                && $this->isEmployeeCodeHeader($values[0] ?? null)
                && $this->isEmployeeNameHeader($values[1] ?? null)
            ) {
                $headerRow = $values;
                continue;
            }

            if ($headerRow !== null) {
                $dataRows[] = $values;
            }
        }

        if (! $headerRow) {
            return [
                'errors' => ['Không xác định được dòng tiêu đề trong file XML.'],
            ];
        }

        $dates = [];
        $weekStart = null;
        $dateStartIndex = $this->isRoleHeader($headerRow[2] ?? null) ? 3 : 2;

        foreach (array_slice($headerRow, $dateStartIndex, 7) as $headerValue) {
            if (! preg_match('/(\d{2}\/\d{2}\/\d{4})/', $headerValue, $matches)) {
                return [
                    'errors' => ['Không đọc được ngày trong dòng tiêu đề file XML.'],
                ];
            }

            $date = Carbon::createFromFormat('d/m/Y', $matches[1])->toDateString();
            $dates[] = $date;
        }

        if (! empty($dates)) {
            $weekStart = Carbon::parse($dates[0])->startOfWeek(Carbon::MONDAY)->toDateString();
        }

        $nguoiDungMap = NguoiDung::query()
            ->with('vaiTro')
            ->where('trang_thai', 1)
            ->whereHas('vaiTro', function ($query) {
                $query->whereIn('ten_vai_tro', ['Nhân viên', 'Trưởng ca']);
            })
            ->get(['id', 'ho_ten', 'id_vai_tro'])
            ->keyBy('id');

        $caMap = $this->caLamViecs()
            ->mapWithKeys(function ($caLamViec) {
                return [
                    $this->normalizeName($caLamViec->ten_ca) => $caLamViec,
                ];
            });

        $errors = [];
        $insertRows = [];
        $uniqueKeys = [];

        foreach ($dataRows as $rowIndex => $values) {
            if (
                $this->normalizeName((string) ($values[1] ?? '')) === 'ca co the dang ky'
                || $this->normalizeName((string) ($values[2] ?? '')) === 'ca co the dang ky'
            ) {
                continue;
            }

            $nguoiDungId = isset($values[0]) ? (int) trim($values[0]) : 0;

            if ($nguoiDungId === 0 && blank($values[1] ?? null)) {
                continue;
            }

            if (! $nguoiDungMap->has($nguoiDungId)) {
                $errors[] = 'Dòng ' . ($rowIndex + 5) . ': Mã nhân viên không tồn tại.';
                continue;
            }

            foreach ($dates as $dateIndex => $date) {
                $rawCellValue = trim((string) ($values[$dateIndex + $dateStartIndex] ?? ''));

                if ($rawCellValue === '') {
                    continue;
                }

                $shiftNames = preg_split('/[\r\n,;]+/', $rawCellValue);

                foreach ($shiftNames as $shiftName) {
                    $shiftName = trim($shiftName);

                    if ($shiftName === '') {
                        continue;
                    }

                    $normalizedName = $this->normalizeName($shiftName);

                    if (! $caMap->has($normalizedName)) {
                        $errors[] = 'Dòng ' . ($rowIndex + 5) . ': Không tìm thấy ca làm việc "' . $shiftName . '".';
                        continue;
                    }

                    $caLamViec = $caMap->get($normalizedName);
                    $uniqueKey = $nguoiDungId . '|' . $date . '|' . $caLamViec->id;

                    if (isset($uniqueKeys[$uniqueKey])) {
                        continue;
                    }

                    $uniqueKeys[$uniqueKey] = true;
                    $nguoiDung = $nguoiDungMap->get($nguoiDungId);

                    if (! $nguoiDung || ! $nguoiDung->vaiTro) {
                        $errors[] = 'Dòng ' . ($rowIndex + 5) . ': Nhân viên chưa có vai trò hợp lệ để phân ca.';
                        continue;
                    }

                    $insertRows[] = [
                        'id_nguoi_dung' => $nguoiDungId,
                        'id_ca_lam_viec' => $caLamViec->id,
                        'ngay' => $date,
                        'vai_tro_trong_ca' => $this->defaultShiftRoleFromUserRole((string) optional($nguoiDung->vaiTro)->ten_vai_tro),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }
        }

        [$gioiHanErrors, $warnings] = $this->validateImportedShiftLimits($insertRows, $caMap);
        $errors = array_merge($errors, $gioiHanErrors);

        return [
            'errors' => $errors,
            'warnings' => $warnings,
            'dates' => array_values(array_unique($dates)),
            'rows' => $insertRows,
            'week_start' => $weekStart,
        ];
    }

    private function validateImportedShiftLimits(array $insertRows, $caMap): array
    {
        $errors = [];
        $warnings = [];

        $groupedRows = collect($insertRows)->groupBy(function (array $row) {
            return $row['ngay'] . '|' . $row['id_ca_lam_viec'];
        });

        foreach ($groupedRows as $groupKey => $rows) {
            [$ngay, $caId] = explode('|', $groupKey);
            $soLuongNhanVien = $rows->count();
            $caLamViec = $caMap->firstWhere('id', (int) $caId);

            if (! $caLamViec) {
                continue;
            }

            $tenCa = $caLamViec->ten_ca;
            $ngayHienThi = Carbon::parse($ngay)->format('d/m/Y');
            $soToiThieu = (int) ($caLamViec->so_nhan_vien_toi_thieu ?? 0);
            $soToiDa = (int) ($caLamViec->so_nhan_vien_toi_da ?? 0);

            if ($soToiDa > 0 && $soLuongNhanVien > $soToiDa) {
                $errors[] = 'Ca ' . $tenCa . ' ngày ' . $ngayHienThi . ' có ' . $soLuongNhanVien . ' nhân viên, vượt quá tối đa ' . $soToiDa . ' người.';
            }

            if ($soToiThieu > 0 && $soLuongNhanVien < $soToiThieu) {
                $warnings[] = 'Ca ' . $tenCa . ' ngày ' . $ngayHienThi . ' chỉ có ' . $soLuongNhanVien . ' nhân viên, chưa đạt tối thiểu ' . $soToiThieu . ' người.';
            }
        }

        return [$errors, $warnings];
    }

    private function parseXmlSchedule(string $filePath): array
    {
        $dom = new DOMDocument();
        $loaded = @$dom->load($filePath);

        if (! $loaded) {
            return [
                'errors' => ['Không đọc được file XML. Vui lòng dùng file đã xuất từ hệ thống.'],
            ];
        }

        $xpath = new DOMXPath($dom);
        $spreadsheetNamespace = 'urn:schemas-microsoft-com:office:spreadsheet';

        $worksheets = $xpath->query('//*[local-name()="Worksheet"]');
        $sheet = null;

        foreach ($worksheets as $worksheet) {
            $sheetName = $worksheet->getAttributeNS($spreadsheetNamespace, 'Name');

            if ($this->normalizeName($sheetName) === 'dang ky lich') {
                $sheet = $worksheet;
                break;
            }
        }

        if (! $sheet) {
            return [
                'errors' => ['Không tìm thấy sheet Đăng ký lịch trong file XML.'],
            ];
        }

        $rows = $xpath->query('./*[local-name()="Table"]/*[local-name()="Row"]', $sheet);
        $headerRow = null;
        $dataRows = [];

        foreach ($rows as $row) {
            $values = $this->extractRowValues($row, $spreadsheetNamespace);

            if (
                $headerRow === null
                && $this->isEmployeeCodeHeader($values[0] ?? null)
                && $this->isEmployeeNameHeader($values[1] ?? null)
            ) {
                $headerRow = $values;
                continue;
            }

            if ($headerRow !== null) {
                $dataRows[] = $values;
            }
        }

        if (! $headerRow) {
            return [
                'errors' => ['Không xác định được dòng tiêu đề trong file XML.'],
            ];
        }

        $dates = [];
        $weekStart = null;

        foreach (array_slice($headerRow, 2, 7) as $headerValue) {
            if (! preg_match('/(\d{2}\/\d{2}\/\d{4})/', $headerValue, $matches)) {
                return [
                    'errors' => ['Không đọc được ngày trong dòng tiêu đề file XML.'],
                ];
            }

            $date = Carbon::createFromFormat('d/m/Y', $matches[1])->toDateString();
            $dates[] = $date;
        }

        if (! empty($dates)) {
            $weekStart = Carbon::parse($dates[0])->startOfWeek(Carbon::MONDAY)->toDateString();
        }

        $nguoiDungMap = NguoiDung::query()
            ->where('trang_thai', 1)
            ->get(['id', 'ho_ten'])
            ->keyBy('id');

        $caMap = $this->caLamViecs()
            ->mapWithKeys(function ($caLamViec) {
                return [
                    $this->normalizeName($caLamViec->ten_ca) => $caLamViec,
                ];
            });

        $errors = [];
        $insertRows = [];
        $uniqueKeys = [];

        foreach ($dataRows as $rowIndex => $values) {
            if ($this->normalizeName((string) ($values[1] ?? '')) === 'ca co the dang ky') {
                continue;
            }

            $nguoiDungId = isset($values[0]) ? (int) trim($values[0]) : 0;

            if ($nguoiDungId === 0 && blank($values[1] ?? null)) {
                continue;
            }

            if (! $nguoiDungMap->has($nguoiDungId)) {
                $errors[] = 'Dòng ' . ($rowIndex + 5) . ': Mã nhân viên không tồn tại.';
                continue;
            }

            foreach ($dates as $dateIndex => $date) {
                $rawCellValue = trim((string) ($values[$dateIndex + 2] ?? ''));

                if ($rawCellValue === '') {
                    continue;
                }

                $shiftNames = preg_split('/[\r\n,;]+/', $rawCellValue);

                foreach ($shiftNames as $shiftName) {
                    $shiftName = trim($shiftName);

                    if ($shiftName === '') {
                        continue;
                    }

                    $normalizedName = $this->normalizeName($shiftName);

                    if (! $caMap->has($normalizedName)) {
                        $errors[] = 'Dòng ' . ($rowIndex + 5) . ': Không tìm thấy ca làm việc "' . $shiftName . '".';
                        continue;
                    }

                    $caLamViec = $caMap->get($normalizedName);
                    $uniqueKey = $nguoiDungId . '|' . $date . '|' . $caLamViec->id;

                    if (isset($uniqueKeys[$uniqueKey])) {
                        continue;
                    }

                    $uniqueKeys[$uniqueKey] = true;
                    $insertRows[] = [
                        'id_nguoi_dung' => $nguoiDungId,
                        'id_ca_lam_viec' => $caLamViec->id,
                        'ngay' => $date,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }
        }

        return [
            'errors' => $errors,
            'dates' => array_values(array_unique($dates)),
            'rows' => $insertRows,
            'week_start' => $weekStart,
        ];
    }

    private function extractRowValues(\DOMElement $row, string $spreadsheetNamespace): array
    {
        $values = [];
        $currentIndex = 1;

        foreach ($row->childNodes as $childNode) {
            if (! $childNode instanceof \DOMElement || $childNode->localName !== 'Cell') {
                continue;
            }

            $index = $childNode->getAttributeNS($spreadsheetNamespace, 'Index');

            if ($index !== '') {
                $currentIndex = (int) $index;
            }

            while (count($values) < $currentIndex - 1) {
                $values[] = '';
            }

            $cellValue = '';

            foreach ($childNode->childNodes as $cellChild) {
                if ($cellChild instanceof \DOMElement && $cellChild->localName === 'Data') {
                    $cellValue = trim($cellChild->textContent);
                    break;
                }
            }

            $values[] = $cellValue;

            $mergeAcross = $childNode->getAttributeNS($spreadsheetNamespace, 'MergeAcross');
            $currentIndex = count($values) + 1 + ($mergeAcross !== '' ? (int) $mergeAcross : 0);
        }

        return $values;
    }

    private function normalizeName(string $value): string
    {
        return Str::lower(trim(Str::ascii($value)));
    }

    private function weekdayLabel(Carbon $date): string
    {
        return match ($date->dayOfWeekIso) {
            1 => 'Thứ 2',
            2 => 'Thứ 3',
            3 => 'Thứ 4',
            4 => 'Thứ 5',
            5 => 'Thứ 6',
            6 => 'Thứ 7',
            default => 'Chủ nhật',
        };
    }

    private function isEmployeeCodeHeader(?string $value): bool
    {
        $normalized = $this->normalizeName((string) $value);

        return in_array($normalized, [
            'ma nv',
            'ma nhan vien',
            'manv',
            'manhanvien',
        ], true);
    }

    private function isEmployeeNameHeader(?string $value): bool
    {
        $normalized = $this->normalizeName((string) $value);

        return in_array($normalized, [
            'nhan vien',
            'ten nhan vien',
            'ho ten',
            'ho va ten',
            'hoten',
            'tennhanvien',
        ], true);
    }

    private function isRoleHeader(?string $value): bool
    {
        $normalized = $this->normalizeName((string) $value);

        return in_array($normalized, [
            'vai tro',
            'vaitro',
            'chuc vu',
            'chucvu',
        ], true);
    }

    private function defaultShiftRoleFromUserRole(string $vaiTro): string
    {
        return $this->normalizeName($vaiTro) === 'truong ca' || $this->normalizeName($vaiTro) === 'truong_ca' || $this->normalizeName($vaiTro) === 'truongca'
            ? 'truong_ca'
            : 'nhan_vien';
    }

    private function displayUserRole(NguoiDung $nguoiDung): string
    {
        return optional($nguoiDung->vaiTro)->ten_vai_tro ?? 'Chưa có vai trò';
    }

    private function normalizeShiftRole(string $vaiTroTrongCa): string
    {
        $normalized = $this->normalizeName($vaiTroTrongCa);

        return in_array($normalized, ['truong ca', 'truong_ca', 'truongca'], true)
            ? 'truong_ca'
            : 'nhan_vien';
    }
}
