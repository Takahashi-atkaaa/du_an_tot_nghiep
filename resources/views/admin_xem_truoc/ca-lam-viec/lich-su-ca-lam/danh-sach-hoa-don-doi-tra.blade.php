@extends('admin_xem_truoc.layouts.admin')

@section('title', 'Hóa đơn của ca đã đổi trả - SmartMart')

@section('content')
<div class="container-fluid">

    {{-- Tiêu đề --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1" style="color:red;">Hóa đơn đổi trả của ca</h4>
            <div class="text-muted">
                Ngày: {{ date('d/m/Y', strtotime($ngay)) }}
            </div>
        </div>

        <a href="{{ url()->previous() }}" class="btn btn-secondary">
            ← Quay lại
        </a>
    </div>


    {{-- Thanh tìm kiếm --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">

            <form method="GET"
                  action="{{ route('hoa-don-cua-ca.doi-tra', [
                      'ngay' => $ngay,
                      'id_ca' => $id_ca
                  ]) }}">

                <div class="row g-2">

                    <div class="col-md-10">
                        <input
                            type="text"
                            name="tu_khoa"
                            class="form-control"
                            placeholder="Tìm theo mã hóa đơn hoặc tên khách hàng..."
                            value="{{ $tuKhoa ?? '' }}"
                        >
                    </div>

                    <div class="col-md-2">
                        <button type="submit"
                                class="btn btn-primary w-100">
                            🔍 Tìm kiếm
                        </button>
                    </div>

                </div>

            </form>

        </div>
    </div>


    {{-- Danh sách hóa đơn --}}
    <div class="card border-0 shadow-sm">

        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <strong>Danh sách hóa đơn</strong>

            <span class="badge bg-primary">
                {{ $hoaDonCuaCa->count() }} hóa đơn
            </span>
        </div>

        <div class="card-body p-0">

            @if($hoaDonCuaCa->count() > 0)

                <div class="table-responsive">

                    <table class="table table-hover align-middle mb-0">

                        <thead class="table-light">
                            <tr>
                                <th class="text-center" style="width: 60px;">STT</th>

                                <th>Mã hóa đơn</th>

                                <th>Khách hàng</th>

                                <th>Thời gian</th>

                                <th class="text-end">Tổng tiền</th>

                                <th class="text-center">Trạng thái</th>

                                <th class="text-center">Thao tác</th>
                            </tr>
                        </thead>

                        <tbody>

                            @foreach($hoaDonCuaCa as $index => $hoaDon)

                                <tr>

                                    <td class="text-center">
                                        {{ $index + 1 }}
                                    </td>

                                    <td>
                                        <strong>
                                            {{ $hoaDon->ma_hoa_don }}
                                        </strong>
                                    </td>

                                    <td>
                                        {{ $hoaDon->ten_khach_hang ?? 'Khách lẻ' }}
                                    </td>

                                    <td>
                                        {{ $hoaDon->created_at->format('H:i:s') }}
                                    </td>

                                    <td class="text-end">
                                        <strong>
                                            {{ number_format($hoaDon->tong_tien) }} đ
                                        </strong>
                                    </td>

                                    <td class="text-center">
                                        <span class="badge bg-danger">
                                            {{ $hoaDon->trang_thai }}
                                        </span>
                                    </td>

                                    <td class="text-center">

                                        {{-- Thay route này bằng route xem chi tiết hóa đơn của bạn --}}
                                        <a href="#"
                                           class="btn btn-sm btn-outline-primary">
                                            Xem
                                        </a>

                                    </td>

                                </tr>

                            @endforeach

                        </tbody>

                    </table>

                </div>

            @else

                <div class="text-center py-5">

                    <div style="font-size: 45px;">
                        🧾
                    </div>

                    <h5 class="mt-3">
                        Không tìm thấy hóa đơn
                    </h5>

                    <p class="text-muted mb-0">
                        Ca này chưa có hóa đơn phù hợp với từ khóa tìm kiếm.
                    </p>

                </div>

            @endif

        </div>
    </div>

</div>


@endsection