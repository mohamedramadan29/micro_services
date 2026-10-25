@extends('website.layouts.master')
@php
    use App\Http\Controllers\front\ReadyServicesController as R;
    $page = \App\Support\SiteContent::first('ready.page');
    $q = (string) request('q');
    $maxPrice = (string) request('max_price');
    // «خدمة واحدة»، «خدمتان»، «4 خدمات»، «11 خدمة»
    $countLabel = fn (int $n) => match (true) { $n === 1 => 'خدمة واحدة', $n === 2 => 'خدمتان', $n >= 3 && $n <= 10 => $n . ' خدمات', default => $n . ' خدمة' };
    $anyFilter = $q !== '' || $group || $service || $maxPrice !== '';
    $groupLink = fn ($id) => route('services.ready', array_filter(['q' => $q ?: null, 'group' => $id, 'sort' => $sort !== 'newest' ? $sort : null]));
@endphp
@section('title'){{ $page['title'] }} | نفّذها@endsection
@section('meta_description'){{ \Illuminate\Support\Str::limit((string) $page['subtitle'], 160) }}@endsection
@section('content')
<section class="rf-pp rf-sl" dir="rtl">
    <div class="container">
        <nav class="rf-pp-trail" aria-label="مسار الصفحة">
            <a href="{{ route('services.index') }}">الخدمات</a> <span aria-hidden="true">‹</span> <span>{{ $page['title'] }}</span>
        </nav>
        <header class="rf-sl-head">
            <div>
                <h1>{{ $page['title'] }}</h1>
                @if ($page['subtitle'])<p>{{ $page['subtitle'] }}</p>@endif
            </div>
            @include('website.partials.list-search', ['placeholder' => $page['search_placeholder'], 'keep' => ['group', 'service', 'max_price'], 'sorts' => R::SORTS, 'sort' => $sort])
        </header>

        @if ($groups->isNotEmpty())
            <nav class="rf-fl-chips" aria-label="الأقسام">
                <a href="{{ $groupLink(null) }}" class="{{ $group ? '' : 'active' }}">كل الأقسام</a>
                @foreach ($groups as $g)
                    <a href="{{ $groupLink($g->id) }}" class="{{ $group === $g->id ? 'active' : '' }}">{{ $g->name }} <span class="rf-rs-n">{{ $g->subCategories->sum(fn ($s) => $counts[$s->id] ?? 0) }}</span></a>
                @endforeach
            </nav>
        @endif

        <div class="rf-fl-layout">
            <details class="nf-filter-panel" open data-responsive-filters>
                <summary><i class="bi bi-sliders" aria-hidden="true"></i> تصفية النتائج{{ $anyFilter ? ' — فلاتر مفعّلة' : '' }} <i class="bi bi-chevron-down" aria-hidden="true"></i></summary>
                <form method="GET" class="rf-fl-filters">
                    @if ($q !== '')<input type="hidden" name="q" value="{{ $q }}">@endif
                    @if ($group)<input type="hidden" name="group" value="{{ $group }}">@endif
                    <label>الخدمة
                        <select name="service" class="form-control">
                            <option value="">الكل</option>
                            @foreach ($groups as $g)
                                @if (! $group || $group === $g->id)
                                    <optgroup label="{{ $g->name }}">
                                        @foreach ($g->subCategories as $s)
                                            <option value="{{ $s->id }}" @selected($service === $s->id)>{{ $s->name }} ({{ $counts[$s->id] ?? 0 }})</option>
                                        @endforeach
                                    </optgroup>
                                @endif
                            @endforeach
                        </select>
                    </label>
                    <label>أقصى سعر (ج.م)
                        <input type="number" name="max_price" min="0" step="50" class="form-control" value="{{ $maxPrice }}" placeholder="مثال: 1000">
                    </label>
                    @if ($sort !== 'newest')<input type="hidden" name="sort" value="{{ $sort }}">@endif
                    <button class="rf-sv-btn rf-sv-btn-primary rf-sv-btn-block">تطبيق</button>
                    @if ($anyFilter)<a href="{{ route('services.ready') }}" class="rf-fl-clear">مسح الفلاتر</a>@endif
                </form>
            </details>

            <div class="nf-directory-results">
                <div class="rf-fl-section-head"><h2>{{ $countLabel($services->total()) }}</h2></div>
                @if ($services->isEmpty())
                    <div class="rf-pp-card rf-sl-empty">
                        <i class="bi bi-grid" aria-hidden="true"></i>
                        <strong>{{ $page['empty'] }}</strong>
                        <p></p>
                        <a href="{{ route('requests.create') }}" class="btn btn-primary">اطلب عرض سعر</a>
                    </div>
                @else
                    <div class="rf-sl-grid">
                        @foreach ($services as $serv)
                            <article class="rf-sl-card">
                                <span class="rf-pp-work-img"><img src="{{ asset('assets/uploads/services/' . $serv->image) }}" alt="" loading="lazy"></span>
                                <div class="rf-sl-body">
                                    @if ($serv->subcategory)<small class="rf-rs-sub">{{ $serv->subcategory->name }}</small>@endif
                                    <h3><a href="{{ $serv->url() }}" class="rf-fl-link">{{ $serv->name }}</a></h3>
                                    @if ($serv->user)
                                        <span class="rf-sl-seller">
                                            <img src="{{ $serv->user->avatarUrl(128) }}" alt="" width="26" height="26" loading="lazy">
                                            {{ $serv->user->name }}
                                            @if ($serv->user->ratingLabel())<span class="rf-fl-stars"><i class="bi bi-star-fill" aria-hidden="true"></i> {{ $serv->user->ratingLabel() }}</span>@endif
                                        </span>
                                    @endif
                                </div>
                                <div class="rf-sl-foot">
                                    <span>@if ((int) $serv->users_num_buy > 0)<i class="bi bi-bag-check" aria-hidden="true"></i> {{ (int) $serv->users_num_buy }} طلب@endif</span>
                                    <strong>{{ \App\Support\Money::show($serv->price) }}</strong>
                                </div>
                            </article>
                        @endforeach
                    </div>
                    <div class="mt-4">{{ $services->links() }}</div>
                @endif
            </div>
        </div>
    </div>
</section>
@endsection
