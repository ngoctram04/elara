<div class="mega-menu">

    <div class="mega-container">

        {{-- CỘT TRÁI --}}
        <ul class="mega-left">
            @foreach ($categories as $parent)
                <li class="mega-parent">

                    <div class="mega-parent-label">
                        <i class="bi bi-droplet-half"></i>
                        <span>{{ strtoupper($parent->name) }}</span>
                        <i class="bi bi-chevron-right arrow"></i>
                    </div>

                    {{-- CỘT PHẢI --}}
                    @if ($parent->children->count())
                        <div class="mega-right">
                            <div class="mega-columns">
                                @foreach ($parent->children->chunk(6) as $chunk)
                                    <ul>
                                        @foreach ($chunk as $child)
                                            <li>
                                                <a href="{{ route('category.show', $child->slug) }}">
                                                    {{ $child->name }}
                                                    <span>({{ $child->products_count }})</span>
                                                </a>
                                            </li>
                                        @endforeach
                                    </ul>
                                @endforeach
                            </div>
                        </div>
                    @endif

                </li>
            @endforeach
        </ul>

    </div>
</div>
