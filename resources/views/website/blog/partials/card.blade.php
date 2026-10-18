{{-- One article card, the same everywhere (client file «تعديلات», item 04). $blog, $readButton --}}
<article class="rf-bl-card">
    <a href="{{ route('blogDetails', $blog->slug) }}" class="rf-bl-img" tabindex="-1" aria-hidden="true">
        @if ($blog->image)
            <img src="{{ $blog->Image() }}" alt="" loading="lazy">
        @else
            <span><i class="bi bi-journal-text"></i></span>
        @endif
    </a>
    <div class="rf-bl-body">
        @if ($blog->Category)<span class="rf-bl-cat">{{ $blog->Category->name }}</span>@endif
        <h3><a href="{{ route('blogDetails', $blog->slug) }}">{{ $blog->name }}</a></h3>
        <p>{{ $blog->excerpt(140) }}</p>
        <div class="rf-bl-meta">
            <span><i class="bi bi-calendar3"></i> {{ \Illuminate\Support\Carbon::parse($blog->publish_date)->format('Y-m-d') }}</span>
            <span><i class="bi bi-clock"></i> {{ $blog->readMinutes() }} د قراءة</span>
        </div>
        <a href="{{ route('blogDetails', $blog->slug) }}" class="rf-bl-read">{{ $readButton }} <i class="bi bi-arrow-left"></i></a>
    </div>
</article>
