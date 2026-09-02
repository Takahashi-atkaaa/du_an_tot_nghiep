@include('partials.hoa-don.doi-tra-form', [
    'actionRoute' => route('nhan-vien.hoa-don.xu-ly-doi-tra', $hoaDon->id),
    'showRoute' => route('nhan-vien.hoa-don.chi-tiet', $hoaDon->id),
])
