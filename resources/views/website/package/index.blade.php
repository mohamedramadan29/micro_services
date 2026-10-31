@extends('website.layouts.master')
@php
    $page = \App\Support\SiteContent::first('packages.page');
    $whatsapp = preg_replace('/\D/', '', (string) \App\Support\SiteContent::first('footer.main')['whatsapp']);
@endphp
@section('title'){{ $page['title'] }} | نفّذها@endsection
@section('meta_description'){{ \Illuminate\Support\Str::limit((string) $page['subtitle'], 160) }}@endsection
@section('content')
<section class="rf-pp rf-sl rf-pk" dir="rtl">
    <div class="container">
        <nav class="rf-pp-trail" aria-label="مسار الصفحة">
            <a href="{{ url('/') }}">الرئيسية</a> <span aria-hidden="true">‹</span> <span>{{ $page['title'] }}</span>
        </nav>
        <header class="rf-sl-head">
            <div>
                <h1>{{ $page['title'] }}</h1>
                @if ($page['subtitle'])<p>{{ $page['subtitle'] }}</p>@endif
            </div>
        </header>

        @forelse ($packagesGrouped as $group => $packages)
            <section class="rf-pk-group" aria-labelledby="pk-{{ $loop->index }}">
                @if (filled($group))
                    <div class="sec-heading"><h2 id="pk-{{ $loop->index }}">{{ $group }}</h2></div>
                @endif
                <div class="rf-pk-grid">
                    @foreach ($packages as $package)
                        @php $features = collect(explode(',', (string) $package->description))->map(fn ($f) => trim($f))->filter(); @endphp
                        <article class="rf-pk-card nf-sheet">
                            <h3>{{ $package->name }}</h3>
                            <p class="rf-pk-price"><strong>{{ \App\Support\Money::show($package->price) }}</strong></p>
                            @if ($features->isNotEmpty())
                                <ul class="rf-pk-features">
                                    @foreach ($features as $feature)
                                        <li><i class="bi bi-check2" aria-hidden="true"></i> {{ $feature }}</li>
                                    @endforeach
                                </ul>
                            @endif
                            <form action="{{ route('subscribe.plan', $package->id) }}" method="post">
                                @csrf
                                <button type="submit" class="btn btn-primary w-100">{{ $page['button'] }} <i class="bi bi-arrow-left" aria-hidden="true"></i></button>
                            </form>
                        </article>
                    @endforeach
                </div>
            </section>
        @empty
            <div class="rf-pp-card rf-sl-empty">
                <i class="bi bi-box" aria-hidden="true"></i>
                <strong>{{ $page['empty'] }}</strong>
            </div>
        @endforelse

        <div class="rf-pp-card rf-wk-cta">
            <div>
                <strong>{{ $page['contact_title'] }}</strong>
                @if ($page['contact_text'])<span>{{ $page['contact_text'] }}</span>@endif
            </div>
            <div class="rf-wk-cta-actions">
                @if ($whatsapp)
                    <a href="https://wa.me/{{ $whatsapp }}" target="_blank" rel="noopener" class="btn btn-primary"><i class="bi bi-whatsapp" aria-hidden="true"></i> تواصل معنا</a>
                @endif
                <a href="{{ url('/services?help=1') }}" class="btn btn-light">ساعدوني أختار</a>
            </div>
        </div>
    </div>
</section>
@endsection
