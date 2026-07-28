@extends('admin_xem_truoc.layouts.admin')

@section('title', 'Chi tiết giao ca')

@section('content')

<div class="container">

    <div class="card shadow">

        <div class="card-header">
            <h4>Chi tiết phiếu giao ca</h4>
        </div>

        <div class="card-body">

            <table class="table table-bordered">

                <tr>
                    <th width="30%">Mã giao ca</th>
                    <td>{{ $giaoCa->id }}</td>
                </tr>

                <tr>
                    <th>Trưởng ca bàn giao</th>
                    <td>{{ $giaoCa->truongCaBanGiao->ho_ten }}</td>
                </tr>

                <tr>
                    <th>Trưởng ca nhận</th>
                    <td>{{ $giaoCa->truongCaNhanCa->ho_ten }}</td>
                </tr>

                <tr>
                    <th>Ca làm việc</th>
                    <td>{{ $giaoCa->caLamViec->ten_ca }}</td>
                </tr>

                <tr>
                    <th>Tiền đầu ca</th>
                    <td>{{ number_format($giaoCa->tien_dau_ca,0,',','.') }} VNĐ</td>
                </tr>

                <tr>
                    <th>Tiền cuối ca</th>
                    <td>{{ number_format($giaoCa->tien_cuoi_ca,0,',','.') }} VNĐ</td>
                </tr>

                <tr>
                    <th>Chênh lệch</th>
                    <td>
                        @if($giaoCa->chenh_lech > 0)
                            <span class="text-success">
                                +{{ number_format($giaoCa->chenh_lech,0,',','.') }} VNĐ
                            </span>
                        @elseif($giaoCa->chenh_lech < 0)
                            <span class="text-danger">
                                {{ number_format($giaoCa->chenh_lech,0,',','.') }} VNĐ
                            </span>
                        @else
                            <span class="text-primary">
                                0 VNĐ
                            </span>
                        @endif
                    </td>
                </tr>

                <tr>
                    <th>Thời gian bắt đầu ca</th>
                    <td>{{ $giaoCa->thoi_gian_bat_dau_ca }}</td>
                </tr>

                <tr>
                    <th>Thời gian kết thúc ca</th>
                    <td>{{ $giaoCa->thoi_gian_ket_thuc_ca }}</td>
                </tr>

                <tr>
                    <th>Trạng thái</th>
                    <td>
                        @if($giaoCa->trang_thai == 0)
                            <span class="badge bg-warning">
                                Chờ xác nhận
                            </span>
                        @else
                            <span class="badge bg-success">
                                Đã xác nhận
                            </span>
                        @endif
                    </td>
                </tr>

                <tr>
                    <th>Ghi chú</th>
                    <td>{{ $giaoCa->ghi_chu ?? 'Không có' }}</td>
                </tr>

                <tr>
                    <th>Ngày tạo</th>
                    <td>{{ $giaoCa->created_at }}</td>
                </tr>

            </table>

        </div>

    </div>

    @if (
        Auth::id() == $giaoCa->id_truong_ca_nhan_ca &&
        $giaoCa->trang_thai == 0
    )

    <form action="{{ route('giao-ca.xac-nhan', $giaoCa->id) }}"
        method="POST">

        @csrf
        @method('PUT')

        <button type="submit"
                class="btn btn-success">

            <i class="bi bi-check-circle-fill"></i>

            Xác nhận giao ca

        </button>

    </form>

    @endif



</div>

@endsection