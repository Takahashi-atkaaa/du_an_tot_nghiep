@extends('admin_xem_truoc.layouts.admin')

@section('title', 'Đổi / Trả Hàng')

@section('content')
<div class="container-fluid">
    <h4 class="mb-4">Xử lý đổi / trả hàng - Hóa đơn #HD{{ str_pad($hoaDon->id, 4, '0', STR_PAD_LEFT) }}</h4>

    @include('partials.hoa-don.doi-tra-form')
</div>
@endsection

@section('scripts')
    {{-- scripts are included in the partial --}}
@endsection
