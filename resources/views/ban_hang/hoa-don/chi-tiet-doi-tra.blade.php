@extends('ban_hang.layouts.ban_hang')

@section('title', 'Chi tiết đổi/trả')

@section('content')
    @include('partials.hoa-don.chi-tiet-doi-tra-page', [
        'backUrl' => route('nhan-vien.hoa-don.chi-tiet', $hoaDon->id),
        'backLabel' => 'Quay lại chi tiết hóa đơn',
    ])
@endsection
