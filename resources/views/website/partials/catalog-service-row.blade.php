{{--
    One catalog service (sub_categories row) with its actions: «اطلب الخدمة» (admin link or the
    request form), else «ساعدوني أختار»; plus the ready-made services when $ready > 0.
    $sub, $group (name|null), $hasForm (bool), $ready (int|null)
--}}
@php $requestUrl = $sub->requestUrl($hasForm); @endphp
<li class="rf-sg-row">
    <span class="rf-sg-icon" aria-hidden="true">
        @if ($sub->imageUrl())<img src="{{ $sub->imageUrl() }}" alt="" loading="lazy">@else<i class="bi {{ $sub->icon ?: 'bi-check2-square' }}"></i>@endif
    </span>
    <div class="rf-sg-text">
        @if ($group)<small class="rf-rs-sub">{{ $group }}</small>@endif
        <strong>{{ $sub->name }}</strong>
        @if ($sub->description)<span>{{ \Illuminate\Support\Str::limit(strip_tags((string) $sub->description), 140) }}</span>@endif
    </div>
    <div class="rf-sg-actions">
        @if ($requestUrl)
            <a href="{{ $requestUrl }}" class="btn btn-primary btn-sm">{{ $sub->button_text ?: 'اطلب الخدمة' }}</a>
        @else
            <a href="{{ url('/services?help=1&service=' . $sub->id) }}" class="btn btn-light btn-sm">ساعدوني أختار</a>
        @endif
        @if ($ready)
            <a href="{{ url('services/' . $sub->slug) }}" class="btn btn-light btn-sm">{{ $ready }} جاهزة</a>
        @endif
    </div>
</li>
