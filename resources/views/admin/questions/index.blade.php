@extends('layouts.admin')

@section('title','Hỏi đáp sản phẩm')

@section('content')

<style>

.question-card{
border-bottom:1px solid #eee;
padding:18px;
}

.product-info{
display:flex;
align-items:center;
gap:12px;
}

.product-thumb{
width:60px;
height:60px;
object-fit:cover;
border-radius:6px;
border:1px solid #ddd;
}

.answer-box{
background:#f8f9fa;
border-radius:6px;
padding:10px;
margin-top:6px;
}

.answer-admin{
background:#fff4f4;
border-left:3px solid #dc3545;
}

.answer-user{
background:#f1f7ff;
border-left:3px solid #0d6efd;
}

.answer-meta{
font-size:12px;
color:#777;
}

</style>

<div class="container-fluid">

<h4 class="mb-4">
<i class="bi bi-question-circle"></i>
Hỏi đáp sản phẩm
</h4>

<div class="card shadow-sm border-0">

<div class="card-body p-0">

@forelse($questions as $q)

<div class="question-card">

<div class="row">

{{-- PRODUCT --}}
<div class="col-md-3">

<div class="product-info">

<img
src="{{ $q->product->mainImage ? asset('storage/'.$q->product->mainImage->image_path) : asset('images/no-image.png') }}"
class="product-thumb">

<div>

<strong>
{{ $q->product->name ?? 'Sản phẩm đã xoá' }}
</strong>

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

<div class="text-muted small">
{{ $q->created_at->format('d/m/Y H:i') }}
</div>

<div class="mt-2">

{{ $q->question }}

</div>

</div>


{{-- ANSWERS --}}
<div class="col-md-6">

{{-- LIST ANSWERS --}}
@foreach($q->answers as $a)

<div class="answer-box {{ $a->is_admin ? 'answer-admin' : 'answer-user' }}">

<div class="fw-semibold">

{{ $a->user->name }}

@if($a->is_admin)
<span class="badge bg-danger ms-1">Shop</span>
@endif

</div>

<div class="answer-meta">
{{ $a->created_at->format('d/m/Y H:i') }}
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
class="mt-2 d-flex gap-2">

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

<i class="bi bi-chat-left-text fs-2"></i>

<div class="mt-2">
Chưa có câu hỏi nào
</div>

</div>

@endforelse

</div>

</div>

<div class="mt-3">

{{ $questions->links() }}

</div>

</div>

@endsection