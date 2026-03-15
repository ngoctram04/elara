@extends('layouts.admin')

@section('title','Hỏi đáp sản phẩm')

@section('content')

<style>

.qa-card{
border-bottom:1px solid #eee;
padding:20px;
}

.product-info{
display:flex;
gap:12px;
align-items:center;
}

.product-thumb{
width:70px;
height:70px;
border-radius:8px;
object-fit:cover;
border:1px solid #ddd;
}

.question-text{
font-size:14px;
margin-top:6px;
}

.answer-box{
background:#f8f9fa;
border-radius:8px;
padding:10px 12px;
margin-top:8px;
font-size:14px;
}

.answer-admin{
background:#fff4f4;
border-left:3px solid #dc3545;
}

.answer-user{
background:#eef5ff;
border-left:3px solid #0d6efd;
}

.answer-meta{
font-size:12px;
color:#777;
margin-bottom:3px;
}

.reply-box{
margin-top:10px;
display:flex;
gap:8px;
}

.filter-bar{
background:#fafafa;
border:1px solid #eee;
border-radius:8px;
padding:12px;
}

</style>


<div class="card border-0 shadow-sm">
<div class="card-body">

{{-- HEADER --}}

<div class="d-flex justify-content-between align-items-center mb-4">

<div>
<h5 class="fw-bold mb-1">
<i class="bi bi-chat-left-text"></i>
Hỏi đáp sản phẩm
</h5>

<small class="text-muted">
Quản lý câu hỏi và phản hồi khách hàng
</small>
</div>

</div>


{{-- FILTER --}}

<div class="filter-bar mb-3">

<form method="GET" class="row g-2">

<div class="col-md-5">

<input
type="text"
name="keyword"
class="form-control form-control-sm"
placeholder="Tìm sản phẩm / câu hỏi / khách hàng..."
value="{{ request('keyword') }}">

</div>

<div class="col-md-3">

<select name="status" class="form-select form-select-sm">

<option value="">-- Tất cả trạng thái --</option>

<option value="answered"
{{ request('status')=='answered'?'selected':'' }}>
Đã trả lời
</option>

<option value="pending"
{{ request('status')=='pending'?'selected':'' }}>
Chưa trả lời
</option>

</select>

</div>

<div class="col-md-2">

<select name="sort" class="form-select form-select-sm">

<option value="new">Mới nhất</option>
<option value="old"
{{ request('sort')=='old'?'selected':'' }}>
Cũ nhất
</option>

</select>

</div>

<div class="col-md-2 d-flex gap-2">

<button class="btn btn-primary btn-sm">
<i class="bi bi-search"></i>
Lọc
</button>

<a href="{{ route('admin.questions.index') }}"
class="btn btn-secondary btn-sm">

<i class="bi bi-arrow-clockwise"></i>
Đặt lại
</a>

</div>

</form>

</div>



{{-- LIST QUESTION --}}

<div class="card border-0 shadow-sm">

<div class="card-body p-0">

@forelse($questions as $q)

<div class="qa-card">

<div class="row">

{{-- PRODUCT --}}

<div class="col-md-3">

<div class="product-info">

<img
src="{{ $q->product->mainImage ? asset('storage/'.$q->product->mainImage->image_path) : asset('images/no-image.png') }}"
class="product-thumb">

<div>

<div class="fw-semibold">

{{ $q->product->name ?? 'Sản phẩm đã xoá' }}

</div>

<div class="small text-muted">
ID: {{ $q->product_id }}
</div>

</div>

</div>

</div>


{{-- QUESTION --}}

<div class="col-md-3">

<div class="fw-semibold">

{{ $q->user->name ?? 'User' }}

</div>

<div class="small text-muted">

{{ $q->created_at->format('d/m/Y H:i') }}

</div>

<div class="question-text">

{{ $q->question }}

</div>

@if($q->answers->count())

<span class="badge bg-success mt-2">
Đã trả lời
</span>

@else

<span class="badge bg-warning text-dark mt-2">
Chưa trả lời
</span>

@endif

</div>


{{-- ANSWERS --}}

<div class="col-md-6">

@foreach($q->answers as $a)

<div class="answer-box {{ $a->is_admin ? 'answer-admin' : 'answer-user' }}">

<div class="answer-meta">

<strong>{{ $a->user->name }}</strong>

@if($a->is_admin)
<span class="badge bg-danger ms-1">Shop</span>
@endif

• {{ $a->created_at->format('d/m/Y H:i') }}

</div>

<div>

{{ $a->answer }}

</div>

</div>

@endforeach


{{-- FORM REPLY --}}

<form
action="{{ route('admin.questions.answer') }}"
method="POST"
class="reply-box">

@csrf

<input
type="hidden"
name="question_id"
value="{{ $q->id }}">

<input
type="text"
name="answer"
class="form-control form-control-sm"
placeholder="Trả lời câu hỏi..."
required>

<button class="btn btn-sm btn-primary">

<i class="bi bi-send"></i>

</button>

</form>

</div>

</div>

</div>

@empty

<div class="text-center py-5 text-muted">

<i class="bi bi-chat-left-text fs-1"></i>

<div class="mt-2">

Chưa có câu hỏi nào

</div>

</div>

@endforelse

</div>

</div>


{{-- PAGINATION --}}

@if($questions->hasPages())

<div class="mt-4">

{{ $questions->links('pagination::bootstrap-5') }}

</div>

@endif


</div>
</div>

@endsection