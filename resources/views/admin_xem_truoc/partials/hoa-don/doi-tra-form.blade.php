@include('partials.hoa-don.doi-tra-form', [
    'actionRoute' => route('admin.hoa-don.xu-ly-doi-tra', $hoaDon->id),
    'showRoute' => route('admin.hoa-don.show', $hoaDon->id),
])
