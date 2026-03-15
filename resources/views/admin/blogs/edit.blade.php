@extends('layouts.admin')

@section('title','Sửa bài viết')

@section('content')

<div class="card border-0 shadow-sm">
<div class="card-body">

{{-- HEADER --}}

<div class="d-flex justify-content-between align-items-center mb-4">

<div>
<h5 class="fw-bold mb-1">
Sửa bài viết
</h5>

<small class="text-muted">
Cập nhật nội dung bài viết blog
</small>
</div>

<a href="{{ route('admin.blogs.index') }}"
class="btn btn-outline-secondary btn-sm">

<i class="bi bi-arrow-left"></i>
Quay lại

</a>

</div>

{{-- ERROR --}}
@if ($errors->any())

<div class="alert alert-danger">

<ul class="mb-0">

@foreach ($errors->all() as $error)

<li>{{ $error }}</li>

@endforeach

</ul>

</div>

@endif

<form method="POST"
action="{{ route('admin.blogs.update',$blog->id) }}"
enctype="multipart/form-data">

@csrf
@method('PUT')

{{-- TIÊU ĐỀ --}}

<div class="mb-3">

<label class="form-label fw-semibold">
Tiêu đề
</label>

<input type="text"
name="title"
class="form-control form-control-sm"
value="{{ old('title',$blog->title) }}"
required>

</div>

{{-- MÔ TẢ NGẮN --}}

<div class="mb-3">

<label class="form-label fw-semibold">
Mô tả ngắn
</label>

<textarea
name="excerpt"
class="form-control form-control-sm"
rows="3">{{ old('excerpt',$blog->excerpt) }}</textarea>

</div>

{{-- THUMBNAIL --}}

<div class="mb-4">

<label class="form-label fw-semibold">
Ảnh thumbnail
</label>

<input type="file"
name="thumbnail"
class="form-control form-control-sm"
id="thumbnailInput"
accept="image/*">

<div class="mt-3">

@if($blog->thumbnail)

<img
id="thumbnailPreview"
src="{{ asset('storage/'.$blog->thumbnail) }}"
style="max-width:220px;border-radius:8px;border:1px solid #ddd">

<p id="noImageText"
class="text-muted small mt-2"
style="display:none">

Chưa có ảnh

</p>

@else

<img id="thumbnailPreview"
style="max-width:220px;display:none;border-radius:8px;border:1px solid #ddd">

<p id="noImageText"
class="text-muted small mt-2">

Chưa có ảnh

</p>

@endif

</div>

</div>

{{-- NỘI DUNG --}}

<div class="mb-4">

<label class="form-label fw-semibold">
Nội dung bài viết
</label>

<textarea
name="content"
id="editor"
rows="10">{{ old('content',$blog->content) }}</textarea>

<small class="text-muted">

Bạn có thể upload ảnh hoặc video trực tiếp vào nội dung.

</small>

</div>

{{-- ACTION --}}

<div class="d-flex gap-2">

<button class="btn btn-primary btn-sm">

<i class="bi bi-check-lg"></i>
Cập nhật

</button>

<a href="{{ route('admin.blogs.index') }}"
class="btn btn-outline-secondary btn-sm">

Hủy

</a>

</div>

</form>

</div>
</div>

@endsection

@push('scripts')

<script src="https://cdn.tiny.cloud/1/jwomqz1th2yort3qvmafmfznineezcj658afe7o681atrhff/tinymce/6/tinymce.min.js"></script>

<script>

tinymce.init({

selector:'#editor',

height:500,

document_base_url:"{{ url('/') }}/",

relative_urls:false,
remove_script_host:false,
convert_urls:false,

plugins:[
'image',
'media',
'table',
'lists',
'link',
'code'
],

toolbar:
'undo redo | styles | bold italic underline | alignleft aligncenter alignright | bullist numlist | image media table | code',

automatic_uploads:true,


/* upload ảnh */
images_upload_handler:function(blobInfo){

return new Promise(function(resolve,reject){

let xhr = new XMLHttpRequest();

xhr.open('POST','{{ route("admin.blogs.uploadImage") }}');

xhr.onload=function(){

if(xhr.status !== 200){
reject('Upload failed');
return;
}

let json = JSON.parse(xhr.responseText);

if(!json.location){
reject('Invalid response');
return;
}

resolve(json.location);

};

let formData = new FormData();

formData.append('file',blobInfo.blob());
formData.append('_token','{{ csrf_token() }}');

xhr.send(formData);

});

},


/* upload video */
file_picker_types:'media',

file_picker_callback:function(callback,value,meta){

if(meta.filetype === 'media'){

let input = document.createElement('input');

input.setAttribute('type','file');
input.setAttribute('accept','video/mp4,video/webm,video/mov');

input.onchange=function(){

let file = this.files[0];

let formData = new FormData();

formData.append('file',file);
formData.append('_token','{{ csrf_token() }}');

fetch("{{ route('admin.blogs.uploadImage') }}",{
method:'POST',
body:formData
})
.then(res => res.json())
.then(json => {

callback(json.location,{
source2:json.location,
poster:''
});

});

};

input.click();

}

}

});


/* preview thumbnail */

document.getElementById('thumbnailInput')
.addEventListener('change',function(e){

let file = e.target.files[0];

if(!file) return;

let reader = new FileReader();

reader.onload=function(event){

let img = document.getElementById('thumbnailPreview');
let text = document.getElementById('noImageText');

img.src = event.target.result;
img.style.display = 'block';

text.style.display = 'none';

}

reader.readAsDataURL(file);

});

</script>

@endpush
