@extends('admin_xem_truoc.layouts.admin')

@section('title', 'Chi tiết đổi/trả')

@section('content')
    @include('partials.hoa-don.chi-tiet-doi-tra-page', [
        'backUrl' => route('admin.hoa-don.show', $hoaDon->id),
        'backLabel' => 'Quay lại chi tiết hóa đơn',
    ])
@endsection
