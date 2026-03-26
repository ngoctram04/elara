@extends('layouts.admin')

@section('title','Quản lý Blog')

@section('content')

<div class="card border-0 shadow-sm">
    <div class="card-body">

        {{-- HEADER --}}
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
            <div>
                <h5 class="fw-bold mb-1">Quản lý Blog</h5>
                <small class="text-muted">
                    Quản lý các bài viết blog trong hệ thống
                </small>
            </div>

            <a href="{{ route('admin.blogs.create') }}"
               class="btn btn-primary btn-sm">
                <i class="bi bi-plus-lg"></i>
                Thêm bài viết
            </a>
        </div>

        {{-- FILTER --}}
        <form method="GET" class="row g-2 mb-4 align-items-center">

            <div class="col-md-4">
                <input
                    type="text"
                    name="keyword"
                    value="{{ request('keyword') }}"
                    class="form-control form-control-sm"
                    placeholder="Tìm bài viết..."
                >
            </div>

            <div class="col-md-3">
                <select name="sort" class="form-select form-select-sm">
                    <option value="">Mặc định</option>

                    <option value="most" {{ request('sort') == 'most' ? 'selected' : '' }}>
                        Xem nhiều nhất
                    </option>

                    <option value="least" {{ request('sort') == 'least' ? 'selected' : '' }}>
                        Xem ít nhất
                    </option>
                </select>
            </div>

            <div class="col-md-5 d-flex gap-2">
                <button class="btn btn-outline-primary btn-sm">
                    <i class="bi bi-search"></i>
                    Lọc
                </button>

                <a href="{{ route('admin.blogs.index') }}"
                   class="btn btn-outline-secondary btn-sm">
                    Đặt lại
                </a>
            </div>

        </form>

        {{-- TABLE --}}
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">

                <thead class="table-light">
                    <tr>
                        <th style="width:70px">STT</th>
                        <th style="width:120px">Ảnh</th>
                        <th>Tiêu đề</th>
                        <th style="width:120px">Lượt xem</th>
                        <th style="width:120px">Trạng thái</th>
                        <th style="width:150px">Ngày tạo</th>
                        <th style="width:160px">Hành động</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($blogs as $blog)
                        <tr>
                            <td class="text-muted fw-semibold">
                                {{ $blog->id }}
                            </td>

                            <td>
                                @if($blog->thumbnail)
                                    <img
                                        src="{{ asset('storage/' . $blog->thumbnail) }}"
                                        width="80"
                                        class="rounded"
                                        alt="{{ $blog->title }}"
                                    >
                                @else
                                    <span class="text-muted small">
                                        Không có
                                    </span>
                                @endif
                            </td>

                            <td>
                                <strong>{{ $blog->title }}</strong>
                            </td>

                            <td>
                                <span class="badge bg-secondary">
                                    {{ $blog->views }}
                                </span>
                            </td>

                            <td>
                                @if($blog->is_active)
                                    <span class="badge bg-success">
                                        Hiển thị
                                    </span>
                                @else
                                    <span class="badge bg-secondary">
                                        Đã ẩn
                                    </span>
                                @endif
                            </td>

                            <td class="text-muted">
                                {{ $blog->created_at?->format('d/m/Y') }}
                            </td>

                            <td>
                                <div class="d-flex gap-1">
                                    <a href="{{ route('admin.blogs.edit', $blog->id) }}"
                                       class="btn btn-sm btn-outline-warning">
                                        <i class="bi bi-pencil"></i>
                                    </a>

                                    <form
                                        action="{{ route('admin.blogs.toggle', $blog->id) }}"
                                        method="POST"
                                        class="toggle-form">
                                        @csrf

                                        <button
                                            type="button"
                                            class="btn btn-sm {{ $blog->is_active ? 'btn-outline-secondary' : 'btn-outline-success' }} btn-toggle">
                                            @if($blog->is_active)
                                                <i class="bi bi-eye-slash"></i>
                                            @else
                                                <i class="bi bi-eye"></i>
                                            @endif
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">
                                <i class="bi bi-inbox"></i>
                                <br>
                                Chưa có bài viết nào
                            </td>
                        </tr>
                    @endforelse
                </tbody>

            </table>
        </div>

        {{-- PAGINATION --}}
        @if($blogs->hasPages())
            <div class="mt-4">
                {{ $blogs->links('vendor.pagination.custom-blue') }}
            </div>
        @endif

    </div>
</div>

@endsection

@push('scripts')
<script>
document.querySelectorAll('.btn-toggle').forEach(function (btn) {
    btn.addEventListener('click', function () {
        let form = this.closest('form');

        Swal.fire({
            title: 'Bạn muốn thay đổi trạng thái bài viết?',
            text: 'Bài viết sẽ được ẩn hoặc hiển thị',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Xác nhận',
            cancelButtonText: 'Hủy'
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit();
            }
        });
    });
});
</script>
@endpush