@extends('website.layouts.master')
@php
    use App\Models\User;
    use App\Http\Controllers\front\FreelancerDirectoryController as D;
    $page = \App\Support\SiteContent::first('freelancers.page');
    $heroBackground = \App\Support\SiteContent::media($page['hero_background']);
    $f = fn ($key) => $filters[$key] ?? '';
    $anyFilter = collect($filters)->except('sort')->filter(fn ($v) => $v !== '' && $v !== null)->isNotEmpty();
@endphp
@section('title')
    المستقلين
@endsection
@section('content')
    <section class="rf-sv-hero" dir="rtl" @if ($heroBackground) style="background-image: url({{ $heroBackground->url() }})" @endif>
        <div class="container">
            <h1>{{ $page['title'] }}</h1>
            @if ($page['subtitle'])<p>{{ $page['subtitle'] }}</p>@endif
            <form method="GET" class="rf-fl-search" role="search">
                <input type="search" name="q" value="{{ $f('q') }}" placeholder="{{ $page['search_placeholder'] }}" aria-label="{{ $page['search_placeholder'] }}">
                <button type="submit" class="rf-sv-btn rf-sv-btn-primary"><i class="bi bi-search"></i> بحث</button>
            </form>
        </div>
    </section>

    <section class="rf-sv" dir="rtl">
        <div class="container">
            <nav class="rf-fl-chips" aria-label="التخصصات">
                <a href="{{ route('freelancers.index', array_filter(['q' => $f('q')])) }}" class="{{ $f('group') ? '' : 'active' }}">كل التخصصات</a>
                @foreach ($groups as $group)
                    <a href="{{ route('freelancers.index', array_filter(['q' => $f('q'), 'group' => $group->id])) }}" class="{{ (int) $f('group') === $group->id ? 'active' : '' }}">{{ $group->name }}</a>
                @endforeach
            </nav>

            @if ($featured->isNotEmpty() && ! $anyFilter)
                <div class="rf-fl-section-head"><h2>{{ $page['featured_title'] }}</h2></div>
                <div class="rf-fl-featured">
                    @foreach ($featured as $freelancer)
                        <a class="rf-fl-mini" href="{{ url('user/' . $freelancer->user_name) }}">
                            <img src="{{ $freelancer->avatarUrl(128) }}" alt="{{ $freelancer->name }}" loading="lazy" width="64" height="64">
                            <strong>{{ $freelancer->name }}</strong>
                            <span>{{ $freelancer->specializations->first()?->name }}</span>
                            @if ($freelancer->rate)<span class="rf-fl-stars">★ {{ $freelancer->rate }}</span>@endif
                        </a>
                    @endforeach
                </div>
            @endif

            <div class="rf-fl-layout">
                <form method="GET" class="rf-fl-filters">
                    <h3>تصفية النتائج</h3>
                    @if ($f('q'))<input type="hidden" name="q" value="{{ $f('q') }}">@endif
                    <label>التخصص
                        <select name="service" class="form-control">
                            <option value="">الكل</option>
                            @foreach ($groups as $group)
                                <optgroup label="{{ $group->name }}">
                                    @foreach ($group->subCategories as $service)
                                        <option value="{{ $service->id }}" @selected((int) $f('service') === $service->id)>{{ $service->name }}</option>
                                    @endforeach
                                </optgroup>
                            @endforeach
                        </select>
                    </label>
                    <label>التقييم
                        <select name="rating" class="form-control">
                            <option value="">الكل</option>
                            @foreach ([4, 3, 2] as $stars)<option value="{{ $stars }}" @selected((int) $f('rating') === $stars)>{{ $stars }} نجوم فأكثر</option>@endforeach
                        </select>
                    </label>
                    <label>الميزانية بالجنيه المصري (يبدأ من حتى)
                        <input type="number" name="max_price" min="0" step="50" class="form-control" value="{{ $f('max_price') }}" placeholder="مثال: 2000">
                    </label>
                    <label>حالة التوفر
                        <select name="availability" class="form-control">
                            <option value="">الكل</option>
                            @foreach (User::AVAILABILITY as $key => $label)<option value="{{ $key }}" @selected($f('availability') === $key)>{{ $label }}</option>@endforeach
                        </select>
                    </label>
                    <label>اللغة
                        <input type="text" name="language" class="form-control" value="{{ $f('language') }}" placeholder="مثال: الإنجليزية">
                    </label>
                    <label>الترتيب
                        <select name="sort" class="form-control">
                            @foreach (D::SORTS as $key => $label)<option value="{{ $key }}" @selected($sort === $key)>{{ $label }}</option>@endforeach
                        </select>
                    </label>
                    <button class="rf-sv-btn rf-sv-btn-primary rf-sv-btn-block">تطبيق</button>
                    @if ($anyFilter)<a href="{{ route('freelancers.index') }}" class="rf-fl-clear">مسح الفلاتر</a>@endif
                </form>

                <div>
                    <div class="rf-fl-section-head"><h2>{{ $page['all_title'] }}</h2><span>{{ $freelancers->total() }} مستقل</span></div>
                    @if ($freelancers->isEmpty())
                        <p class="rf-sv-empty">{{ $page['empty'] }} <a href="{{ route('requests.create') }}">اطلب عرضًا</a></p>
                    @else
                        <div class="rf-fl-grid">
                            @foreach ($freelancers as $freelancer)
                                <article class="rf-fl-card">
                                    <img src="{{ $freelancer->avatarUrl(256) }}" alt="{{ $freelancer->name }}" loading="lazy" width="96" height="96">
                                    <h3>{{ $freelancer->name }}</h3>
                                    <p class="rf-fl-spec">{{ $freelancer->specializations->pluck('name')->take(2)->implode(' · ') ?: $freelancer->job_title }}</p>
                                    <div class="rf-fl-meta">
                                        <span class="rf-fl-stars">{{ $freelancer->rate ? '★ ' . rtrim(rtrim(number_format((float) $freelancer->rate, 1), '0'), '.') . ($freelancer->rating_count ? ' (' . $freelancer->rating_count . ')' : '') : 'جديد' }}</span>
                                        @if ($freelancer->availability)<span class="rf-fl-avail {{ $freelancer->availability }}">{{ User::AVAILABILITY[$freelancer->availability] ?? '' }}</span>@endif
                                    </div>
                                    @if ($freelancer->bio)<p class="rf-fl-bio">{{ \Illuminate\Support\Str::limit($freelancer->bio, 110) }}</p>@endif
                                    @if ($freelancer->starting_price !== null)<p class="rf-fl-price">يبدأ من {{ \App\Support\Money::show($freelancer->starting_price) }}</p>@endif
                                    <a href="{{ url('user/' . $freelancer->user_name) }}" class="rf-sv-btn rf-sv-btn-outline rf-sv-btn-block">{{ $page['card_button'] }}</a>
                                </article>
                            @endforeach
                        </div>
                        <div class="mt-3">{{ $freelancers->links() }}</div>
                    @endif
                </div>
            </div>
        </div>
    </section>
@endsection
