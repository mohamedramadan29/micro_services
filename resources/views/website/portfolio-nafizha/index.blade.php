@extends('website.layouts.master')
@php
    use App\Http\Controllers\front\NafizhaPortfolioController as P;
    $page = \App\Support\SiteContent::first('team.page');
    $q = (string) request('q');
    $link = fn ($g) => url('nafizha/portfolios') . (($query = http_build_query(array_filter(['q' => $q ?: null, 'group' => $g, 'sort' => $sort !== 'newest' ? $sort : null]))) ? '?' . $query : '');
    $countLabel = fn (int $n) => match (true) { $n === 1 => 'عمل واحد', $n === 2 => 'عملان', $n >= 3 && $n <= 10 => $n . ' أعمال', default => $n . ' عملًا' };
@endphp
@section('title'){{ $page['title'] }} | نفّذها@endsection
@section('meta_description'){{ \Illuminate\Support\Str::limit((string) $page['subtitle'], 160) }}@endsection
@section('content')
<section class="rf-pp rf-sl rf-wk rf-tw" dir="rtl">
    <div class="container">
        <nav class="rf-pp-trail" aria-label="مسار الصفحة">
            <a href="{{ url('/') }}">الرئيسية</a> <span aria-hidden="true">‹</span> <span>{{ $page['title'] }}</span>
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
                @if ($q !== '' || $group)<a href="{{ url('nafizha/portfolios') }}" class="btn btn-light">كل الأعمال</a>@endif
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
                            <h2><a href="{{ url('nafizha/portfolio/' . $work->id . '-' . $work->slug) }}" class="rf-fl-link">{{ $work->title }}</a></h2>
                        </div>
                        <div class="rf-sl-foot">
                            <span class="rf-sl-seller rf-tw-team">
                                <img src="{{ asset('assets/website/img/logo.png') }}" alt="" width="26" height="26" loading="lazy">
                                {{ $page['team_name'] }}
                            </span>
                            <span class="rf-tw-date">{{ $work->created_at?->translatedFormat('F Y') }}</span>
                        </div>
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
                <a href="{{ url('/services?help=1') }}" class="btn btn-light">ساعدوني أختار</a>
            </div>
        </div>
    </div>
</section>
@endsection
