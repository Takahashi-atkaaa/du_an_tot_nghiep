@extends('admin_xem_truoc.layouts.admin')

@section('title', 'Cập nhật ca làm việc - SmartMart')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
	<div>
		<h4 class="fw-bold mb-1">Cập nhật ca làm việc</h4>
		<nav aria-label="breadcrumb">
			<ol class="breadcrumb mb-0">
				<li class="breadcrumb-item"><a href="{{ url('admin/dashboard') }}">Admin</a></li>
				<li class="breadcrumb-item"><a href="{{ route('ca-lam-viec.index') }}">Ca làm việc</a></li>
				<li class="breadcrumb-item active">Chỉnh sửa</li>
			</ol>
		</nav>
	</div>
	<a href="{{ route('ca-lam-viec.index') }}" class="btn btn-outline-secondary">Quay lại</a>
</div>

<div class="card table-admin">
	<div class="card-body">
		<form method="POST" action="{{ route('ca-lam-viec.update', $caLamViec) }}">
			@csrf
			@method('PUT')
			@include('admin_xem_truoc.ca-lam-viec._form', ['caLamViec' => $caLamViec])
			<div class="mt-4 d-flex justify-content-end gap-2">
				<a href="{{ route('ca-lam-viec.index') }}" class="btn btn-light">Hủy</a>
				<button type="submit" class="btn btn-primary">
					<i class="fas fa-save me-2"></i>Cập nhật
				</button>
			</div>
		</form>
	</div>
</div>
@endsection
