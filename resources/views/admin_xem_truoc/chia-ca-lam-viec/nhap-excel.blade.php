@extends('admin_xem_truoc.layouts.admin')

@section('title', 'Nhập lịch làm việc - SmartMart')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
    <div>
        <h4 class="fw-bold mb-1">Nhập lịch làm việc từ Excel</h4>
        <div class="text-muted">
            Từ {{ $weekStart->format('d/m/Y') }} đến {{ $weekStart->copy()->addDays(6)->format('d/m/Y') }}
        </div>
    </div>
    <a href="{{ route('chia-ca-lam-viec.index', ['week_start' => $weekStart->format('Y-m-d')]) }}" class="btn btn-outline-secondary">Quay lại</a>
</div>

<div class="row g-4">
    <div class="col-lg-7">
        <div class="card table-admin h-100">
            <div class="card-body">
                <h5 class="fw-bold mb-3">Các bước thực hiện</h5>
                <ol class="mb-4">
                    <li>Chọn tuần cần lập lịch.</li>
                    <li>Ấn <strong>Tải file mẫu</strong> để tải file Excel về máy.</li>
                    <li>Mở file bằng Microsoft Excel, không mở bằng trình duyệt.</li>
                    <li>Cho nhân viên điền tên ca vào từng ô trong file Excel.</li>
                    <li>Lưu lại file theo định dạng <strong>Excel 97-2003 Workbook (.xls)</strong> hoặc <strong>XML Spreadsheet 2003 (.xml)</strong>.</li>
                    <li>Nhập file vào hệ thống để cập nhật lịch chính thức.</li>
                </ol>

                <form method="POST" action="{{ route('chia-ca-lam-viec.store') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">File lịch làm việc <span class="text-danger">*</span></label>
                        <input type="file" name="tep_lich" accept=".xml,.xls" class="form-control @error('tep_lich') is-invalid @enderror">
                        @error('tep_lich')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Ưu tiên file xuất từ hệ thống và mở/chỉnh sửa bằng Microsoft Excel.</div>
                    </div>

                    <div class="d-flex gap-2">
                        <a href="{{ route('chia-ca-lam-viec.export', ['week_start' => $weekStart->format('Y-m-d')]) }}" class="btn btn-success">
                            <i class="fas fa-file-export me-2"></i>Tải file mẫu
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-file-import me-2"></i>Nhập lịch chính thức
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card table-admin h-100">
            <div class="card-body">
                <h5 class="fw-bold mb-3">Danh mục ca hợp lệ</h5>
                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Tên ca</th>
                                <th>Khung giờ</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($caLamViecs as $caLamViec)
                                <tr>
                                    <td>{{ $caLamViec->ten_ca }}</td>
                                    <td>
                                        {{ \Illuminate\Support\Carbon::parse($caLamViec->gio_bat_dau)->format('H:i') }}
                                        -
                                        {{ \Illuminate\Support\Carbon::parse($caLamViec->gio_ket_thuc)->format('H:i') }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-3 small text-muted">
                    Trong file Excel, mỗi ô của một ngày có thể nhập 1 hoặc nhiều tên ca.
                    Nếu một nhân viên làm nhiều ca trong cùng một ngày, hãy ngăn cách tên ca bằng dấu phẩy.
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
