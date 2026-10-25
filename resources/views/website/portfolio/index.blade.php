@extends('website.layouts.master')
@php
    use App\Http\Controllers\front\PortfolioController as P;
    $page = \App\Support\SiteContent::first('works.page');
    $q = (string) request('q');
    $link = fn ($g) => route('portfolios.index', array_filter(['q' => $q ?: null, 'group' => $g, 'sort' => $sort !== 'newest' ? $sort : null]));
    $countLabel = fn (int $n) => match (true) { $n === 1 => 'عمل واحد', $n === 2 => 'عملان', $n >= 3 && $n <= 10 => $n . ' أعمال', default => $n . ' عملًا' };
@endphp
@section('title'){{ $page['title'] }} | نفّذها@endsection
@section('meta_description'){{ \Illuminate\Support\Str::limit((string) $page['subtitle'], 160) }}@endsection
@section('content')
<section class="rf-pp rf-sl rf-wk" dir="rtl">
    <div class="container">
        <nav class="rf-pp-trail" aria-label="مسار الصفحة">
            <a href="{{ route('freelancers.index') }}">المستقلين</a> <span aria-hidden="true">‹</span> <span>{{ $page['title'] }}</span>
        </nav>
        <header class="rf-sl-head">
            <div>
                <h1>{{ $page['title'] }}</h1>
                @if ($page['subtitle'])<p>{{ $page['subtitle'] }}</p>@endif
            </div>
            @include('website.partials.list-search', ['placeholder' => $page['search_placeholder'], 'keep' => ['group'], 'sorts' => P::SORTS, 'sort' => $sort])
        </header>

        @if ($groups->isNotEmpty())
            <nav class="rf-fl-chips" aria-label="الأقسام">
                <a href="{{ $link(null) }}" class="{{ $group ? '' : 'active' }}">كل الأقسام <span class="rf-rs-n">{{ $counts->sum() }}</span></a>
                @foreach ($groups as $g)
                    <a href="{{ $link($g->id) }}" class="{{ $group === $g->id ? 'active' : '' }}">{{ $g->name }} <span class="rf-rs-n">{{ $counts[$g->id] ?? 0 }}</span></a>
                @endforeach
            </nav>
        @endif

        @if ($works->isEmpty())
            <div class="rf-pp-card rf-sl-empty">
                <i class="bi bi-images" aria-hidden="true"></i>
                <strong>{{ $page['empty'] }}</strong>
                <p></p>
                @if ($q !== '' || $group)<a href="{{ route('portfolios.index') }}" class="btn btn-light">كل الأعمال</a>@endif
            </div>
        @else
            <p class="rf-sl-count">{{ $countLabel($works->total()) }}</p>
            <div class="rf-sl-grid rf-wk-grid">
                @foreach ($works as $work)
                    @php $images = 1 + (is_array($work->more_images) ? count($work->more_images) : 0); @endphp
                    <article class="rf-sl-card">
                        <span class="rf-pp-work-img">
                            <img src="{{ asset('assets/uploads/portfolios/' . $work->image) }}" alt="" loading="lazy">
                            @if ($images > 1)<span class="rf-pp-work-count"><i class="bi bi-images" aria-hidden="true"></i> {{ $images }}</span>@endif
                        </span>
                        <div class="rf-sl-body">
                            @if ($work->Category)<small class="rf-rs-sub">{{ $work->Category->name }}</small>@endif
                            <h2><a href="{{ url('portfolio/' . $work->id . '-' . $work->slug) }}" class="rf-fl-link">{{ $work->title }}</a></h2>
                        </div>
                        @if ($work->User)
                            <div class="rf-sl-foot">
                                <span class="rf-sl-seller">
                                    <img src="{{ $work->User->avatarUrl(128) }}" alt="" width="26" height="26" loading="lazy">
                                    {{ $work->User->name }}
                                </span>
                                @if ($work->User->ratingLabel())<span class="rf-fl-stars"><i class="bi bi-star-fill" aria-hidden="true"></i> {{ $work->User->ratingLabel() }}</span>@endif
                            </div>
                        @endif
                    </article>
                @endforeach
            </div>
            <div class="mt-4">{{ $works->links() }}</div>
        @endif

        <div class="rf-pp-card rf-wk-cta">
            <div>
                <strong>{{ $page['cta_title'] }}</strong>
                @if ($page['cta_text'])<span>{{ $page['cta_text'] }}</span>@endif
            </div>
            <div class="rf-wk-cta-actions">
                <a href="{{ route('requests.create') }}" class="btn btn-primary"><i class="bi bi-send" aria-hidden="true"></i> اطلب عرض سعر</a>
                <a href="{{ route('freelancers.index') }}" class="btn btn-light">تصفح المستقلين</a>
            </div>
        </div>
    </div>
</section>
@endsection
