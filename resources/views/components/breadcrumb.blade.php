<nav class="breadcrumb-box">
    <a href="{{ route('home') }}">
        <i class="bi bi-house"></i> Trang chủ
    </a>

    @foreach($items as $item)
        <span class="breadcrumb-sep">›</span>

        @if(isset($item['url']))
            <a href="{{ $item['url'] }}">{{ $item['label'] }}</a>
        @else
            <span class="breadcrumb-current">{{ $item['label'] }}</span>
        @endif
    @endforeach
</nav>
