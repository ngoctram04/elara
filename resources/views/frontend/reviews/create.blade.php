@extends('layouts.frontend')
@section('title','Đánh giá đơn hàng')

@section('content')

<style>
.review-page{
    background: linear-gradient(180deg, #f8fbff 0%, #ffffff 100%);
    min-height: calc(100vh - 200px);
}

.review-title{
    color:#163b67;
    letter-spacing:.2px;
}

.review-card{
    background:#fff;
    border-radius:18px;
    border:1px solid #e7eef8;
    box-shadow:0 10px 30px rgba(13,110,253,.08);
    overflow:hidden;
}

.review-card-header{
    background:linear-gradient(135deg,#eef5ff 0%,#f8fbff 100%);
    border-bottom:1px solid #e6eefb;
    padding:18px 24px;
}

.review-card-body{
    padding:24px;
}

.review-order-code{
    font-size:13px;
    color:#587195;
    margin-top:4px;
}

.review-item{
    border:1px solid #e6eefb;
    background:#fff;
    border-radius:16px;
    padding:18px;
    margin-bottom:20px;
    box-shadow:0 4px 14px rgba(13,110,253,.04);
}

.review-item:last-child{
    margin-bottom:0;
}

.product-box{
    display:flex;
    gap:16px;
    align-items:center;
    padding:16px;
    border:1px solid #e6eefb;
    background:#f9fbff;
    border-radius:14px;
    margin-bottom:18px;
}

.product-box img{
    width:84px;
    height:84px;
    object-fit:cover;
    border-radius:12px;
    border:1px solid #dce8f8;
    background:#fff;
    flex-shrink:0;
}

.product-name{
    font-size:16px;
    font-weight:700;
    color:#183153;
    margin-bottom:4px;
}

.product-variant{
    color:#6b7a90;
    font-size:14px;
    line-height:1.5;
}

.section-label{
    display:block;
    font-weight:700;
    color:#183153;
    margin-bottom:10px;
    font-size:15px;
}

.star-wrap{
    display:flex;
    align-items:center;
    gap:4px;
    flex-wrap:wrap;
}

.star{
    font-size:30px;
    color:#cfd8e6;
    cursor:pointer;
    transition:.2s ease;
    user-select:none;
    line-height:1;
}

.star:hover{
    transform:scale(1.08);
}

.star.active{
    color:#ffc107;
}

.rating-note{
    margin-top:8px;
    font-size:13px;
    color:#6b7a90;
}

.quick-tags{
    display:flex;
    flex-wrap:wrap;
    gap:10px;
}

.tag-btn{
    border:1px solid #d9e6f7;
    padding:8px 14px;
    border-radius:999px;
    cursor:pointer;
    background:#fff;
    font-size:14px;
    color:#27548a;
    transition:.2s ease;
    font-weight:500;
}

.tag-btn:hover{
    border-color:#0d6efd;
    color:#0d6efd;
    background:#f4f8ff;
}

.tag-btn.active{
    border-color:#0d6efd;
    color:#0d6efd;
    background:#edf4ff;
    box-shadow:0 4px 10px rgba(13,110,253,.08);
}

.form-control.review-textarea{
    border-radius:14px;
    border:1px solid #d9e6f7;
    min-height:120px;
    padding:14px 16px;
    resize:none;
    box-shadow:none;
}

.form-control.review-textarea:focus{
    border-color:#86b7fe;
    box-shadow:0 0 0 .2rem rgba(13,110,253,.12);
}

.textarea-note{
    margin-top:8px;
    font-size:13px;
    color:#7a8798;
}

.media-label{
    font-weight:700;
    color:#183153;
    margin-bottom:12px;
}

.media-toolbar{
    display:flex;
    gap:12px;
    flex-wrap:wrap;
}

.media-btn{
    display:inline-flex;
    align-items:center;
    gap:8px;
    padding:11px 18px;
    border:1px solid #cfe0fb;
    color:#0d6efd;
    border-radius:12px;
    font-weight:600;
    cursor:pointer;
    background:#f8fbff;
    transition:.2s ease;
}

.media-btn:hover{
    background:#eef5ff;
    border-color:#0d6efd;
}

.preview-group{
    margin-top:14px;
}

.preview-title{
    font-size:13px;
    color:#6b7a90;
    margin-bottom:8px;
}

.preview-box{
    display:flex;
    gap:10px;
    flex-wrap:wrap;
}

.preview-box img{
    width:84px;
    height:84px;
    object-fit:cover;
    border-radius:10px;
    border:1px solid #dce8f8;
    box-shadow:0 4px 10px rgba(0,0,0,.04);
    background:#fff;
}

.preview-box video{
    width:180px;
    border-radius:12px;
    border:1px solid #dce8f8;
    box-shadow:0 4px 10px rgba(0,0,0,.04);
    background:#fff;
}

.field-error{
    color:#dc3545;
    font-size:13px;
    margin-top:6px;
    font-weight:500;
}

.action-row{
    display:flex;
    gap:10px;
    flex-wrap:wrap;
    margin-top:24px;
}

.btn-submit-review{
    min-width:170px;
    border:none;
    border-radius:12px;
    background:linear-gradient(90deg,#0d6efd 0%,#3e8bff 100%);
    padding:11px 20px;
    font-weight:700;
    box-shadow:0 8px 18px rgba(13,110,253,.18);
}

.btn-submit-review:hover{
    transform:translateY(-1px);
}

.btn-submit-review:disabled{
    opacity:.8;
    cursor:not-allowed;
    transform:none;
}

.btn-back-review{
    min-width:120px;
    border-radius:12px;
    padding:11px 20px;
    font-weight:600;
}

.review-tip{
    margin-top:18px;
    padding:14px 16px;
    border-radius:12px;
    background:#f8fbff;
    border:1px dashed #cfe0fb;
    color:#5c6f86;
    font-size:13px;
}

.progress{
    height:10px;
    margin-top:18px;
    display:none;
    border-radius:999px;
    background:#e8f1ff;
    overflow:hidden;
}

.progress-bar{
    background:linear-gradient(90deg,#0d6efd 0%,#64a8ff 100%);
    border-radius:999px;
}

.review-item-title{
    font-size:15px;
    font-weight:700;
    color:#163b67;
    margin-bottom:14px;
}

.empty-note{
    padding:14px 16px;
    border-radius:12px;
    background:#fff8e1;
    color:#8a5b00;
    border:1px solid #ffe08a;
    font-size:14px;
    font-weight:600;
}

@media (max-width: 576px){
    .review-card-header,
    .review-card-body{
        padding:16px;
    }

    .product-box{
        align-items:flex-start;
    }

    .star{
        font-size:28px;
    }

    .preview-box video{
        width:100%;
        max-width:220px;
    }
}
</style>

<div class="review-page">
    <div class="container py-4 py-md-5">

        <h4 class="fw-bold mb-4 review-title">Đánh giá đơn hàng</h4>

        <div class="review-card">
            <div class="review-card-header">
                <div class="fw-bold text-primary mb-1">Chia sẻ trải nghiệm của bạn</div>
                <div class="text-muted small">
                    Mỗi sản phẩm có phần đánh giá riêng, nhưng bạn chỉ cần bấm gửi một lần.
                </div>
                <div class="review-order-code">
                    Đơn hàng: DH{{ str_pad($order->id, 5, '0', STR_PAD_LEFT) }}
                </div>
            </div>

            <div class="review-card-body">
                @if($reviewableItems->isEmpty())
                    <div class="empty-note">
                        Tất cả sản phẩm trong đơn này đã được đánh giá.
                    </div>
                @else
                    <form id="reviewForm"
                          action="{{ route('reviews.store', $order->id) }}"
                          method="POST"
                          enctype="multipart/form-data">
                        @csrf

                        @foreach($reviewableItems as $index => $item)
                            @php
                                $variant = $item->variant;
                                $product = $variant->product ?? null;

                                $image = optional($variant->mainImage)->image_path
                                    ?? optional($product->mainImage)->image_path;

                                $imageUrl = $image
                                    ? asset('storage/'.$image)
                                    : asset('images/no-image.png');

                                $itemId = $item->id;
                            @endphp

                            <div class="review-item" data-item-id="{{ $itemId }}">
                                <div class="review-item-title">
                                    Sản phẩm {{ $index + 1 }}
                                </div>

                                <div class="product-box">
                                    <img src="{{ $imageUrl }}" alt="{{ $product->name ?? 'Sản phẩm' }}">

                                    <div>
                                        <div class="product-name">{{ $product->name }}</div>
                                        <div class="product-variant">
                                            @if(!empty($variant->attribute_name) || !empty($variant->attribute_value))
                                                {{ $variant->attribute_name ?? 'Phân loại' }}: {{ $variant->attribute_value ?? '-' }}
                                            @endif
                                            <br>
                                            Số lượng: x{{ $item->quantity }}
                                        </div>
                                    </div>
                                </div>

                                {{-- STAR --}}
                                <div class="mb-4">
                                    <label class="section-label">Chất lượng sản phẩm</label>

                                    <div class="star-wrap" data-star-box="{{ $itemId }}">
                                        @for($i=1;$i<=5;$i++)
                                            <span class="star"
                                                  data-item-id="{{ $itemId }}"
                                                  data-value="{{ $i }}">★</span>
                                        @endfor
                                    </div>

                                    <input type="hidden"
                                           name="reviews[{{ $itemId }}][rating]"
                                           class="rating-input"
                                           data-item-id="{{ $itemId }}"
                                           value="{{ old('reviews.'.$itemId.'.rating') }}">

                                    <div class="rating-note">
                                        Chọn số sao phù hợp với trải nghiệm của bạn.
                                    </div>

                                    <div class="field-error error-rating"
                                         data-item-id="{{ $itemId }}"></div>
                                </div>

                                {{-- QUICK TAG --}}
                                <div class="mb-4">
                                    <label class="section-label">Đánh giá nhanh</label>

                                    <div class="quick-tags" data-tags-box="{{ $itemId }}">
                                        <span class="tag-btn" data-item-id="{{ $itemId }}">Đáng tiền</span>
                                        <span class="tag-btn" data-item-id="{{ $itemId }}">Giao nhanh</span>
                                        <span class="tag-btn" data-item-id="{{ $itemId }}">Đóng gói tốt</span>
                                        <span class="tag-btn" data-item-id="{{ $itemId }}">Đúng mô tả</span>
                                    </div>
                                </div>

                                {{-- COMMENT --}}
                                <div class="mb-4">
                                    <label class="section-label">Nhận xét</label>

                                    <textarea name="reviews[{{ $itemId }}][comment]"
                                              class="form-control review-textarea comment-input"
                                              data-item-id="{{ $itemId }}"
                                              rows="4"
                                              placeholder="Ví dụ: Sản phẩm đúng mô tả, giao hàng nhanh, đóng gói cẩn thận...">{{ old('reviews.'.$itemId.'.comment') }}</textarea>

                                    <div class="textarea-note">
                                        Bạn có thể chia sẻ cảm nhận thật, nhưng vui lòng không dùng từ ngữ không phù hợp.
                                    </div>

                                    <div class="field-error error-comment"
                                         data-item-id="{{ $itemId }}"></div>
                                </div>

                                {{-- MEDIA --}}
                                <div class="mb-2">
                                    <div class="media-label">Hình ảnh / Video</div>

                                    <div class="media-toolbar">
                                        <label class="media-btn">
                                            <i class="bi bi-camera-fill"></i>
                                            Thêm hình ảnh
                                            <input type="file"
                                                   class="image-input"
                                                   data-item-id="{{ $itemId }}"
                                                   name="reviews[{{ $itemId }}][images][]"
                                                   multiple
                                                   accept="image/*"
                                                   hidden>
                                        </label>

                                        <label class="media-btn">
                                            <i class="bi bi-camera-video-fill"></i>
                                            Thêm video
                                            <input type="file"
                                                   class="video-input"
                                                   data-item-id="{{ $itemId }}"
                                                   name="reviews[{{ $itemId }}][video]"
                                                   accept="video/*"
                                                   hidden>
                                        </label>
                                    </div>

                                    <div class="field-error error-images"
                                         data-item-id="{{ $itemId }}"></div>

                                    <div class="field-error error-video"
                                         data-item-id="{{ $itemId }}"></div>

                                    <div class="preview-group">
                                        <div class="preview-title">Xem trước hình ảnh</div>
                                        <div class="preview-box image-preview"
                                             data-item-id="{{ $itemId }}"></div>
                                    </div>

                                    <div class="preview-group">
                                        <div class="preview-title">Xem trước video</div>
                                        <div class="preview-box video-preview"
                                             data-item-id="{{ $itemId }}"></div>
                                    </div>
                                </div>
                            </div>
                        @endforeach

                        <div class="progress" id="uploadProgress">
                            <div class="progress-bar" id="progressBar" style="width:0%"></div>
                        </div>

                        <div class="action-row">
                            <button type="submit" class="btn btn-primary btn-submit-review" id="submitBtn">
                                <i class="bi bi-send-fill me-1"></i> Gửi đánh giá
                            </button>

                            <a href="{{ route('orders.show', $order->id) }}"
                               class="btn btn-outline-secondary btn-back-review">
                                Quay lại
                            </a>
                        </div>

                        <div class="review-tip">
                            <i class="bi bi-info-circle me-1 text-primary"></i>
                            Đánh giá của bạn sẽ được hiển thị công khai sau khi gửi. Nội dung chứa từ ngữ không phù hợp sẽ bị từ chối.
                        </div>
                    </form>
                @endif
            </div>
        </div>
    </div>
</div>

<script>
const reviewForm = document.getElementById('reviewForm');
const submitBtn = document.getElementById('submitBtn');
const uploadProgress = document.getElementById('uploadProgress');
const progressBar = document.getElementById('progressBar');
const quickTagValues = ['Đáng tiền', 'Giao nhanh', 'Đóng gói tốt', 'Đúng mô tả'];

function getErrorBox(type, itemId) {
    return document.querySelector(`.error-${type}[data-item-id="${itemId}"]`);
}

function clearFieldErrors() {
    document.querySelectorAll('.error-rating, .error-comment, .error-images, .error-video')
        .forEach(el => el.innerText = '');
}

function setRating(itemId, value) {
    const ratingInput = document.querySelector(`.rating-input[data-item-id="${itemId}"]`);
    const stars = document.querySelectorAll(`.star[data-item-id="${itemId}"]`);

    if (!ratingInput) return;

    ratingInput.value = value;
    stars.forEach(star => {
        star.classList.remove('active');
        if (parseInt(star.dataset.value, 10) <= value) {
            star.classList.add('active');
        }
    });

    const ratingError = getErrorBox('rating', itemId);
    if (ratingError) ratingError.innerText = '';
}

function syncOldRatings() {
    document.querySelectorAll('.rating-input').forEach(input => {
        const itemId = input.dataset.itemId;
        const value = parseInt(input.value || 0, 10);
        if (value > 0) {
            setRating(itemId, value);
        }
    });
}

document.querySelectorAll('.star').forEach(star => {
    star.addEventListener('click', function () {
        const itemId = this.dataset.itemId;
        const value = parseInt(this.dataset.value, 10);
        setRating(itemId, value);
    });
});

function updateCommentFromTags(itemId) {
    const commentInput = document.querySelector(`.comment-input[data-item-id="${itemId}"]`);
    if (!commentInput) return;

    const activeTags = [];
    document.querySelectorAll(`.tag-btn.active[data-item-id="${itemId}"]`).forEach(tag => {
        activeTags.push(tag.innerText.trim());
    });

    const currentComment = commentInput.value.trim();

    const manualText = currentComment
        .split('.')
        .map(item => item.trim())
        .filter(item => item !== '' && !quickTagValues.includes(item))
        .join('. ');

    let tagText = activeTags.join('. ');
    if (tagText !== '') {
        tagText += '. ';
    }

    commentInput.value = (tagText + manualText).trim();
}

document.querySelectorAll('.tag-btn').forEach(tag => {
    tag.addEventListener('click', function () {
        this.classList.toggle('active');
        updateCommentFromTags(this.dataset.itemId);
    });
});

document.querySelectorAll('.image-input').forEach(input => {
    input.addEventListener('change', function () {
        const itemId = this.dataset.itemId;
        const imagePreview = document.querySelector(`.image-preview[data-item-id="${itemId}"]`);
        const imagesError = getErrorBox('images', itemId);

        if (imagePreview) imagePreview.innerHTML = '';
        if (imagesError) imagesError.innerText = '';

        if (this.files.length > 5) {
            this.value = '';
            if (typeof showToast === 'function') {
                showToast('Chỉ được tải lên tối đa 5 hình ảnh cho mỗi sản phẩm.', 'error');
            }
            return;
        }

        Array.from(this.files).forEach(file => {
            const reader = new FileReader();
            reader.onload = function (e) {
                const img = document.createElement('img');
                img.src = e.target.result;
                imagePreview.appendChild(img);
            };
            reader.readAsDataURL(file);
        });
    });
});

document.querySelectorAll('.video-input').forEach(input => {
    input.addEventListener('change', function () {
        const itemId = this.dataset.itemId;
        const videoPreview = document.querySelector(`.video-preview[data-item-id="${itemId}"]`);
        const videoError = getErrorBox('video', itemId);

        if (videoPreview) videoPreview.innerHTML = '';
        if (videoError) videoError.innerText = '';

        if (this.files[0]) {
            const video = document.createElement('video');
            video.src = URL.createObjectURL(this.files[0]);
            video.controls = true;
            videoPreview.appendChild(video);
        }
    });
});

if (reviewForm) {
    reviewForm.addEventListener('submit', function (e) {
        e.preventDefault();
        clearFieldErrors();

        const ratingInputs = document.querySelectorAll('.rating-input');
        let hasAnyReview = false;
        let firstMissingRatingItemId = null;

        ratingInputs.forEach(input => {
            const itemId = input.dataset.itemId;
            const rating = input.value;
            const comment = (document.querySelector(`.comment-input[data-item-id="${itemId}"]`)?.value || '').trim();
            const imageInput = document.querySelector(`.image-input[data-item-id="${itemId}"]`);
            const videoInput = document.querySelector(`.video-input[data-item-id="${itemId}"]`);

            const hasImages = imageInput && imageInput.files.length > 0;
            const hasVideo = videoInput && videoInput.files.length > 0;
            const hasContent = !!rating || comment !== '' || hasImages || hasVideo;

            if (hasContent) {
                hasAnyReview = true;
            }

            if (hasContent && !rating && firstMissingRatingItemId === null) {
                firstMissingRatingItemId = itemId;
            }
        });

        if (!hasAnyReview) {
            if (typeof showToast === 'function') {
                showToast('Bạn chưa nhập đánh giá cho sản phẩm nào.', 'error');
            }
            return;
        }

        if (firstMissingRatingItemId !== null) {
            const ratingError = getErrorBox('rating', firstMissingRatingItemId);
            if (ratingError) {
                ratingError.innerText = 'Vui lòng chọn số sao cho sản phẩm này.';
            }

            if (typeof showToast === 'function') {
                showToast('Vui lòng chọn số sao cho sản phẩm đã nhập đánh giá.', 'error');
            }
            return;
        }

        const formData = new FormData(reviewForm);
        const xhr = new XMLHttpRequest();

        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="bi bi-arrow-repeat me-1"></i> Đang gửi...';
        uploadProgress.style.display = 'block';
        progressBar.style.width = '0%';

        xhr.open('POST', reviewForm.action, true);
        xhr.setRequestHeader('X-CSRF-TOKEN', '{{ csrf_token() }}');
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        xhr.setRequestHeader('Accept', 'application/json');

        xhr.upload.onprogress = function (e) {
            if (e.lengthComputable) {
                const percent = (e.loaded / e.total) * 100;
                progressBar.style.width = percent + '%';
            }
        };

        xhr.onload = function () {
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="bi bi-send-fill me-1"></i> Gửi đánh giá';

            let response = {};
            try {
                response = JSON.parse(xhr.responseText);
            } catch (error) {
                response = {};
            }

            if (xhr.status >= 200 && xhr.status < 300) {
                if (typeof showToast === 'function') {
                    showToast(response.message ?? 'Đánh giá thành công!', 'success');
                }

                setTimeout(() => {
                    window.location.href = response.redirect ?? "{{ route('orders.show', $order->id) }}";
                }, 900);
                return;
            }

            uploadProgress.style.display = 'none';
            progressBar.style.width = '0%';

            if (xhr.status === 422) {
                if (response.errors) {
                    Object.keys(response.errors).forEach(key => {
                        const message = response.errors[key][0];

                        const ratingMatch = key.match(/^reviews\.(\d+)\.rating$/);
                        const commentMatch = key.match(/^reviews\.(\d+)\.comment$/);
                        const imagesMatch = key.match(/^reviews\.(\d+)\.images$/);
                        const imageFileMatch = key.match(/^reviews\.(\d+)\.images\.\d+$/);
                        const videoMatch = key.match(/^reviews\.(\d+)\.video$/);

                        if (ratingMatch) {
                            const box = getErrorBox('rating', ratingMatch[1]);
                            if (box) box.innerText = message;
                            return;
                        }

                        if (commentMatch) {
                            const box = getErrorBox('comment', commentMatch[1]);
                            if (box) box.innerText = message;
                            return;
                        }

                        if (imagesMatch || imageFileMatch) {
                            const itemId = imagesMatch ? imagesMatch[1] : imageFileMatch[1];
                            const box = getErrorBox('images', itemId);
                            if (box) box.innerText = message;
                            return;
                        }

                        if (videoMatch) {
                            const box = getErrorBox('video', videoMatch[1]);
                            if (box) box.innerText = message;
                            return;
                        }
                    });

                    const firstError = Object.values(response.errors)[0][0];
                    if (typeof showToast === 'function') {
                        showToast(firstError, 'error');
                    }
                    return;
                }

                if (typeof showToast === 'function') {
                    showToast(response.message ?? 'Dữ liệu không hợp lệ.', 'error');
                }
                return;
            }

            if (typeof showToast === 'function') {
                showToast(response.message ?? 'Có lỗi xảy ra khi gửi đánh giá.', 'error');
            }
        };

        xhr.onerror = function () {
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="bi bi-send-fill me-1"></i> Gửi đánh giá';
            uploadProgress.style.display = 'none';
            progressBar.style.width = '0%';

            if (typeof showToast === 'function') {
                showToast('Không thể kết nối đến máy chủ.', 'error');
            }
        };

        xhr.send(formData);
    });
}

syncOldRatings();
</script>

@endsection