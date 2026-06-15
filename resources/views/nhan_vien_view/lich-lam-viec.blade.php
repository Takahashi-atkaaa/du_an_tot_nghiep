@extends('nhan_vien_view.layouts.nhan_vien')

@section('title', 'Lịch làm việc')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Lịch làm việc</h1>
    <span class="text-muted">Tháng 6 / 2026</span>
</div>

{{-- Lịch tháng --}}
<div class="card mb-4">
    <div class="card-header bg-white">
        <div class="d-flex justify-content-between align-items-center">
            <button class="btn btn-outline-success btn-sm"><i class="fas fa-chevron-left"></i></button>
            <h5 class="mb-0">Tháng 6 / 2026</h5>
            <button class="btn btn-outline-success btn-sm"><i class="fas fa-chevron-right"></i></button>
        </div>
    </div>
    <div class="card-body p-0">
        <table class="table table-bordered mb-0 text-center">
            <thead class="table-light">
                <tr>
                    <th class="text-danger">CN</th>
                    <th>T2</th>
                    <th>T3</th>
                    <th>T4</th>
                    <th>T5</th>
                    <th>T6</th>
                    <th class="text-success">T7</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="text-muted p-2"></td>
                    <td class="text-muted p-2"></td>
                    <td class="p-2">1<div class="small text-success">Ca 1</div></td>
                    <td class="p-2">2<div class="small text-success">Ca 1</div></td>
                    <td class="p-2">3<div class="small text-success">Ca 1</div></td>
                    <td class="p-2">4<div class="small text-success">Ca 1</div></td>
                    <td class="p-2 text-success">5<div class="small">Ca 1</div></td>
                </tr>
                <tr>
                    <td class="text-danger p-2">6</td>
                    <td class="p-2">7<div class="small text-success">Ca 2</div></td>
                    <td class="p-2">8<div class="small text-success">Ca 2</div></td>
                    <td class="p-2">9<div class="small text-success">Ca 2</div></td>
                    <td class="p-2">10<div class="small text-success">Ca 2</div></td>
                    <td class="p-2">11<div class="small text-success">Ca 2</div></td>
                    <td class="p-2 text-success">12<div class="small">Ca 2</div></td>
                </tr>
                <tr>
                    <td class="text-danger p-2">13</td>
                    <td class="p-2">14<div class="small text-success">Ca 1</div></td>
                    <td class="p-2">15<div class="small text-success">Ca 1</div></td>
                    <td class="p-2 bg-warning bg-opacity-25">16<div class="small">Hôm nay</div></td>
                    <td class="p-2">17<div class="small text-success">Ca 1</div></td>
                    <td class="p-2">18<div class="small text-success">Ca 1</div></td>
                    <td class="p-2 text-success">19<div class="small">Ca 1</div></td>
                </tr>
                <tr>
                    <td class="text-danger p-2">20</td>
                    <td class="p-2">21<div class="small text-success">Ca 2</div></td>
                    <td class="p-2">22<div class="small text-success">Ca 2</div></td>
                    <td class="p-2">23<div class="small text-success">Ca 2</div></td>
                    <td class="p-2">24<div class="small text-success">Ca 2</div></td>
                    <td class="p-2">25<div class="small text-success">Ca 2</div></td>
                    <td class="p-2 text-success">26<div class="small">Ca 2</div></td>
                </tr>
                <tr>
                    <td class="text-danger p-2">27</td>
                    <td class="p-2">28<div class="small text-success">Ca 1</div></td>
                    <td class="p-2">29<div class="small text-success">Ca 1</div></td>
                    <td class="p-2">30<div class="small text-success">Ca 1</div></td>
                    <td class="p-2"></td>
                    <td class="p-2"></td>
                    <td class="p-2"></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

{{-- Danh sách ca làm việc --}}
<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="fas fa-clock me-2 text-success"></i>Ca làm việc</h5>
            </div>
            <div class="card-body">
                <table class="table table-nv mb-0">
                    <thead>
                        <tr>
                            <th>Ca</th>
                            <th>Giờ vào</th>
                            <th>Giờ ra</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong>Ca 1</strong></td>
                            <td>07:30</td>
                            <td>12:00</td>
                        </tr>
                        <tr>
                            <td><strong>Ca 2</strong></td>
                            <td>13:30</td>
                            <td>18:00</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="fas fa-info-circle me-2 text-success"></i>Thông tin lịch</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <strong class="d-block mb-2">Chú thích:</strong>
                    <span class="badge bg-success me-2">Ca 1</span> Sáng (07:30 - 12:00)
                </div>
                <div class="mb-3">
                    <span class="badge bg-primary me-2">Ca 2</span> Chiều (13:30 - 18:00)
                </div>
                <div class="mb-3">
                    <span class="badge bg-warning me-2">Nghỉ</span> Ngày nghỉ
                </div>
                <div class="alert alert-success mb-0">
                    <i class="fas fa-check-circle me-2"></i>
                    Bạn đã đăng ký đủ công việc trong tháng
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
