@extends('admin_xem_truoc.layouts.admin')

@section('title', 'Cập nhật nhân sự - SmartMart')

@section('content')

<style>
    .quyen-grid {
        display: grid;
        grid-template-columns: repeat(5, 1fr);
        gap: 12px;
    }

    .quyen-item {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 8px 10px;
        border: 1px solid #e5e5e5;
        border-radius: 6px;
        background: #f9f9f9;
        font-size: 14px;
    }
</style>

<h2>Phân quyền {{$vaiTro->ten_vai_tro}}</h2>

<form action="{{ route('admin.quyen.update', $vaiTro->id) }}" method="POST">
    @csrf

    <div class="quyen-grid">
        @foreach($quyens as $quyen)
            {{-- #region agent log --}}
            @php
            if ($quyen->ma_quyen === 'quan_ly_nhan_su') {
                file_put_contents('/Applications/XAMPP/xamppfiles/htdocs/SmartMart/.cursor/debug-c60244.log', json_encode(['sessionId'=>'c60244','location'=>'phan-quyen.blade.php:28','message'=>'nhan_su checkbox render','data'=>['quyen_id'=>$quyen->id,'quyen_id_type'=>gettype($quyen->id),'in_array'=>in_array($quyen->id, $quyen_thuoc_vai_tro),'quyen_thuoc_vai_tro'=>$quyen_thuoc_vai_tro,'will_be_checked'=>in_array($quyen->id, $quyen_thuoc_vai_tro) ? 'YES' : 'NO'],'timestamp'=>round(microtime(true)*1000),'hypothesisId'=>'F'])."\n", FILE_APPEND);
            }
            @endphp
            {{-- #endregion --}}
            <label class="quyen-item" for="quyen-{{ $quyen->id }}">
                <input class="form-check-input"
                       type="checkbox"
                       id="quyen-{{ $quyen->id }}"
                       name="quyens[]"
                       value="{{ $quyen->id }}"
                       {{ in_array($quyen->id, $quyen_thuoc_vai_tro) ? 'checked' : '' }}
                       data-debug-quyen-id="{{ $quyen->id }}"
                       data-debug-ma-quyen="{{ $quyen->ma_quyen }}">

                <span>{{ $quyen->ten_quyen }}</span>
            </label>
        @endforeach
    </div>

    <div class="d-flex justify-content-end mt-3">
        <button type="submit" class="btn btn-primary" onclick="return confirm('Bạn có chắc muốn lưu thay đổi phân quyền cho nhân viên này không?')">
            Lưu quyền
        </button>
    </div>
</form>

@endsection
