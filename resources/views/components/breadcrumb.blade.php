@props([
    'items' => [],
    'class' => '',
])

@if(!empty($items) && count($items))
    <div class="container pt-2">
        <nav class="breadcrumb-custom {{ $class }}" aria-label="breadcrumb">
            @foreach($items as $item)
                @php
                    $label = $item['label'] ?? '';
                    $url = $item['url'] ?? null;
                    $isLast = $loop->last;
                @endphp

                @if(!$loop->first)
                    <span class="breadcrumb-separator" aria-hidden="true">
                        <i class="bi bi-chevron-right"></i>
                    </span>
                @endif

                @if($url && !$isLast)
                    <a href="{{ $url }}" class="breadcrumb-link">
                        @if($loop->first)
                            <i class="bi bi-house-door me-1"></i>
                        @endif
                        <span>{{ $label }}</span>
                    </a>
                @elseif($url && $isLast)
                    <a href="{{ $url }}" class="breadcrumb-link current-link" aria-current="page">
                        @if($loop->first)
                            <i class="bi bi-house-door me-1"></i>
                        @endif
                        <span>{{ $label }}</span>
                    </a>
                @else
                    <span class="breadcrumb-current" aria-current="page">
                        {{ $label }}
                    </span>
                @endif
            @endforeach
        </nav>
    </div>
@endif

@once
    @push('styles')
        <style>
            .breadcrumb-custom {
                display: flex;
                align-items: center;
                flex-wrap: wrap;
                gap: 8px;
                font-size: 14px;
                color: #6b7280;
                margin-bottom: 1.5rem;
            }

            .breadcrumb-custom .breadcrumb-link,
            .breadcrumb-custom .current-link {
                display: inline-flex;
                align-items: center;
                text-decoration: none;
                color: #374151;
                font-weight: 500;
                line-height: 1.4;
                transition: all 0.2s ease;
            }

            .breadcrumb-custom .breadcrumb-link i,
            .breadcrumb-custom .current-link i {
                color: #6b7280;
                transition: all 0.2s ease;
            }

            .breadcrumb-custom .breadcrumb-link:hover,
            .breadcrumb-custom .current-link:hover {
                color: #0d6efd;
            }

            .breadcrumb-custom .breadcrumb-link:hover i,
            .breadcrumb-custom .current-link:hover i {
                color: #0d6efd;
            }

            .breadcrumb-custom .breadcrumb-current {
                color: #111827;
                font-weight: 700;
                line-height: 1.4;
                word-break: break-word;
            }

            .breadcrumb-custom .breadcrumb-separator {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                color: #adb5bd;
                font-size: 12px;
                line-height: 1;
            }

            @media (max-width: 767.98px) {
                .breadcrumb-custom {
                    font-size: 13px;
                    gap: 6px;
                    margin-bottom: 1rem;
                }

                .breadcrumb-custom .breadcrumb-separator {
                    font-size: 11px;
                }
            }
        </style>
    @endpush
@endonce