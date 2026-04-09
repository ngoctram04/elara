@extends('layouts.admin')

@section('title','Thêm bài viết')

@section('content')

<div class="card border-0 shadow-sm">
<div class="card-body">


<div class="d-flex justify-content-between align-items-center mb-4">

<div>
<h5 class="fw-bold mb-1">
Thêm bài viết
</h5>

<small class="text-muted">
Tạo bài viết blog mới cho hệ thống
</small>
</div>

<a href="{{ route('admin.blogs.index') }}"
class="btn btn-outline-secondary btn-sm">

<i class="bi bi-arrow-left"></i>
Quay lại

</a>

</div>

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
action="{{ route('admin.blogs.store') }}"
enctype="multipart/form-data">

@csrf



<div class="mb-3">

<label class="form-label fw-semibold">
Tiêu đề
</label>

<input type="text"
name="title"
class="form-control form-control-sm"
value="{{ old('title') }}"
required>

</div>



<div class="mb-3">

<label class="form-label fw-semibold">
Mô tả ngắn
</label>

<textarea
name="excerpt"
class="form-control form-control-sm"
rows="3">{{ old('excerpt') }}</textarea>

</div>



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

<img id="thumbnailPreview"
style="max-width:220px;display:none;border-radius:8px;border:1px solid #ddd">

<p id="noImageText"
class="text-muted small mt-2">

Chưa có ảnh

</p>

</div>

</div>



<div class="mb-4">

<label class="form-label fw-semibold">
Nội dung bài viết
</label>

<textarea
name="content"
id="editor"
rows="10">{{ old('content') }}</textarea>

<small class="text-muted">

Bạn có thể upload ảnh hoặc video trực tiếp vào nội dung.

</small>

</div>



<div class="d-flex gap-2">

<button class="btn btn-primary btn-sm">

<i class="bi bi-check-lg"></i>
Lưu bài viết

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
