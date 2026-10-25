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
                            @if ($freelancer->ratingLabel())<span class="rf-fl-stars"><i class="bi bi-star-fill" aria-hidden="true"></i> {{ $freelancer->ratingLabel() }}</span>@endif
                        </a>
                    @endforeach
                </div>
            @endif

            <div class="rf-fl-layout">
                <details class="nf-filter-panel" open data-responsive-filters>
                    <summary><i class="bi bi-sliders" aria-hidden="true"></i> تصفية النتائج{{ $anyFilter ? ' — فلاتر مفعّلة' : '' }} <i class="bi bi-chevron-down" aria-hidden="true"></i></summary>
                <form method="GET" class="rf-fl-filters">
                    @if ($f('group'))<input type="hidden" name="group" value="{{ $f('group') }}">@endif
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
                    <label>أقصى سعر مبدئي (ج.م)
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
                </details>

                <div class="nf-directory-results">
                    <div class="rf-fl-section-head"><h2>{{ $page['all_title'] }}</h2><span>{{ $freelancers->total() }} مستقل</span></div>
                    @if ($freelancers->isEmpty())
                        <p class="rf-sv-empty">{{ $page['empty'] }} <a href="{{ route('requests.create') }}">اطلب عرضًا</a></p>
                    @else
                        <div class="rf-fl-grid">
                            @foreach ($freelancers as $freelancer)
                                @php
                                    $profileUrl = url('user/' . $freelancer->user_name);
                                    $rating = $freelancer->ratingLabel();
                                    $skills = $freelancer->specializations;
                                @endphp
                                <article class="rf-fl-card">
                                    @if ($freelancer->is_featured)<span class="rf-fl-badge"><i class="bi bi-award-fill" aria-hidden="true"></i> موثوق</span>@endif
                                    <div class="rf-fl-photo">
                                        <img src="{{ $freelancer->avatarUrl(256) }}" alt="" loading="lazy" width="96" height="96">
                                        @if ($freelancer->availability === 'available')<span class="rf-fl-dot" title="{{ User::AVAILABILITY['available'] }}"></span>@endif
                                    </div>
                                    <h3><a href="{{ $profileUrl }}" class="rf-fl-link">{{ $freelancer->name }}</a></h3>
                                    <p class="rf-fl-spec">{{ $freelancer->job_title ?: $skills->pluck('name')->take(2)->implode(' · ') }}</p>
                                    <div class="rf-fl-rating">
                                        @if ($rating)
                                            @include('website.freelancers.partials.stars', ['value' => $freelancer->rate])
                                            <strong>{{ $rating }}</strong>@if ($freelancer->rating_count)<span>({{ $freelancer->rating_count }})</span>@endif
                                        @else
                                            <span class="rf-fl-new">مستقل جديد</span>
                                        @endif
                                    </div>
                                    <ul class="rf-fl-figures">
                                        <li><strong>{{ $freelancer->completedProjects() }}</strong> مشروع منجز</li>
                                        <li><strong>{{ (int) $freelancer->works_count }}</strong> {{ (int) $freelancer->works_count === 1 ? 'عمل' : 'أعمال' }}</li>
                                    </ul>
                                    @if ($freelancer->bio)<p class="rf-fl-bio">{{ \Illuminate\Support\Str::limit($freelancer->bio, 110) }}</p>@endif
                                    @if ($skills->isNotEmpty())
                                        <ul class="rf-fl-skills">
                                            @foreach ($skills->take(3) as $skill)<li>{{ $skill->name }}</li>@endforeach
                                            @if ($skills->count() > 3)<li dir="ltr">+{{ $skills->count() - 3 }}</li>@endif
                                        </ul>
                                    @endif
                                    <div class="rf-fl-foot">
                                        <span class="rf-fl-price">@if ($freelancer->starting_price !== null)يبدأ من <strong>{{ \App\Support\Money::show($freelancer->starting_price) }}</strong>@else السعر حسب المشروع @endif</span>
                                        <span class="rf-fl-go" aria-hidden="true">{{ $page['card_button'] }} <i class="bi bi-arrow-left"></i></span>
                                    </div>
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
