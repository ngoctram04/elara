@extends('layouts.admin')

@section('title','Thêm bài viết')

@section('content')

<h4 class="mb-3">Thêm bài viết</h4>

@if ($errors->any())
<div class="alert alert-danger">
<ul class="mb-0">
@foreach ($errors->all() as $error)
<li>{{ $error }}</li>
@endforeach
</ul>
</div>
@endif

<div class="card shadow-sm border-0">
<div class="card-body">

<form method="POST"
action="{{ route('admin.blogs.store') }}"
enctype="multipart/form-data">

@csrf

<div class="mb-3">
<label class="form-label">Tiêu đề</label>

<input type="text"
name="title"
class="form-control"
value="{{ old('title') }}"
required>
</div>


<div class="mb-3">
<label class="form-label">Mô tả ngắn</label>

<textarea
name="excerpt"
class="form-control"
rows="3">{{ old('excerpt') }}</textarea>
</div>


<div class="mb-3">
<label class="form-label">Ảnh thumbnail</label>

<input type="file"
name="thumbnail"
class="form-control"
id="thumbnailInput"
accept="image/*">

<div class="mt-3">

<img id="thumbnailPreview"
style="max-width:220px; display:none; border-radius:8px; border:1px solid #ddd;">

<p id="noImageText"
class="text-muted small mt-2">
Chưa có ảnh
</p>

</div>
</div>


<div class="mb-3">

<label class="form-label">Nội dung bài viết</label>

<textarea
name="content"
id="editor"
rows="10">{{ old('content') }}</textarea>

<small class="text-muted">
Bạn có thể upload ảnh hoặc video trực tiếp vào nội dung.
</small>

</div>

<button class="btn btn-primary">
Lưu bài viết
</button>

<a href="{{ route('admin.blogs.index') }}"
class="btn btn-secondary">
Quay lại
</a>

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