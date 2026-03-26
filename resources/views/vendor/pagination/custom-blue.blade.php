@once
<style>
    .custom-pagination-wrap{
        display:flex;
        justify-content:center;
        margin-top:24px;
    }

    .custom-pagination{
        display:flex;
        align-items:center;
        justify-content:center;
        gap:12px;
        list-style:none;
        padding:0;
        margin:0;
        flex-wrap:wrap;
    }

    .custom-pagination li{
        margin:0;
        padding:0;
    }

    .custom-pagination li a,
    .custom-pagination li span{
        display:flex;
        align-items:center;
        justify-content:center;
        min-width:42px;
        height:42px;
        padding:0 12px;
        border-radius:999px;
        text-decoration:none;
        font-size:15px;
        font-weight:600;
        border:none;
        background:transparent;
        color:#2563eb;
        transition:all .2s ease;
    }

    .custom-pagination li a:hover{
        background:#dbeafe;
        color:#1d4ed8;
    }

    .custom-pagination li.active span{
        background:#2563eb;
        color:#fff;
        box-shadow:0 6px 16px rgba(37, 99, 235, 0.18);
    }

    .custom-pagination li.dots span{
        min-width:auto;
        height:42px;
        padding:0 4px;
        color:#64748b;
        background:transparent;
    }

    .custom-pagination li.disabled span{
        color:#9ca3af;
        background:transparent;
        cursor:not-allowed;
    }

    .custom-pagination li.arrow a,
    .custom-pagination li.arrow span{
        font-size:18px;
    }

    @media (max-width: 576px){
        .custom-pagination{
            gap:8px;
        }

        .custom-pagination li a,
        .custom-pagination li span{
            min-width:36px;
            height:36px;
            font-size:14px;
            padding:0 10px;
        }

        .custom-pagination li.arrow a,
        .custom-pagination li.arrow span{
            font-size:16px;
        }
    }
</style>
@endonce

@if ($paginator->hasPages())
    <nav class="custom-pagination-wrap">
        <ul class="custom-pagination">

            {{-- Previous --}}
            @if ($paginator->onFirstPage())
                <li class="arrow disabled" aria-disabled="true" aria-label="@lang('pagination.previous')">
                    <span aria-hidden="true">&laquo;</span>
                </li>
            @else
                <li class="arrow">
                    <a href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="@lang('pagination.previous')">
                        &laquo;
                    </a>
                </li>
            @endif

            {{-- Pagination Elements --}}
            @foreach ($elements as $element)
                {{-- Dots --}}
                @if (is_string($element))
                    <li class="dots" aria-disabled="true">
                        <span>{{ $element }}</span>
                    </li>
                @endif

                {{-- Array of links --}}
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <li class="active" aria-current="page">
                                <span>{{ $page }}</span>
                            </li>
                        @else
                            <li>
                                <a href="{{ $url }}">{{ $page }}</a>
                            </li>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Next --}}
            @if ($paginator->hasMorePages())
                <li class="arrow">
                    <a href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="@lang('pagination.next')">
                        &raquo;
                    </a>
                </li>
            @else
                <li class="arrow disabled" aria-disabled="true" aria-label="@lang('pagination.next')">
                    <span aria-hidden="true">&raquo;</span>
                </li>
            @endif

        </ul>
    </nav>
@endif