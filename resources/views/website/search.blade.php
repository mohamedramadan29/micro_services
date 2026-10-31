@extends('website.layouts.master')
@php
    $countLabel = fn (int $n) => match (true) { $n === 1 => 'خدمة واحدة', $n === 2 => 'خدمتان', $n >= 3 && $n <= 10 => $n . ' خدمات', default => $n . ' خدمة' };
@endphp
@section('title'){{ $search !== '' ? 'نتائج البحث عن «' . $search . '»' : 'البحث في الخدمات' }} | نفّذها@endsection
@section('content')
<section class="rf-pp rf-sl rf-se" dir="rtl">
    <div class="container">
        <nav class="rf-pp-trail" aria-label="مسار الصفحة">
            <a href="{{ url('/') }}">الرئيسية</a> <span aria-hidden="true">‹</span> <span>البحث</span>
        </nav>
        <header class="rf-sl-head">
            <div>
                <h1>{{ $search !== '' ? 'نتائج البحث عن «' . $search . '»' : 'البحث في الخدمات' }}</h1>
                <p>اطلب الخدمة من المستقلين المعتمدين، أو اشترِ خدمة جاهزة بسعر ثابت.</p>
            </div>
            @include('website.partials.list-search', ['name' => 'search', 'placeholder' => 'ابحث عن خدمة: تصميم شعار، متجر، إعلانات…'])
        </header>

        @if ($catalog->isNotEmpty())
            <section class="rf-pp-card rf-se-catalog" aria-labelledby="se-catalog">
                <h2 id="se-catalog">اطلب الخدمة</h2>
                <ul class="rf-sg-list">
                    @foreach ($catalog as $sub)
                        @include('website.partials.catalog-service-row', ['sub' => $sub, 'group' => $sub->category?->name, 'hasForm' => isset($withForm[$sub->id]), 'ready' => null])
                    @endforeach
                </ul>
            </section>
        @endif

        <section aria-labelledby="se-ready" class="rf-se-ready">
            <div class="rf-se-ready-head">
                <h2 id="se-ready">خدمات جاهزة</h2>
                <p class="rf-sl-count">{{ $countLabel($services->total()) }}</p>
            </div>
            @if ($services->isEmpty())
                <div class="rf-pp-card rf-sl-empty">
                    <i class="bi bi-search" aria-hidden="true"></i>
                    <strong>لا توجد خدمات جاهزة تطابق بحثك.</strong>
                    <p>اكتب طلبك ويصلك أكثر من عرض، أو اطلب من فريقنا يساعدك تختار.</p>
                    <div class="rf-wk-cta-actions justify-content-center">
                        <a href="{{ route('projects.new') }}" class="btn btn-primary"><i class="bi bi-send" aria-hidden="true"></i> انشر مشروعك</a>
                        <a href="{{ url('/services?help=1') }}" class="btn btn-light">ساعدوني أختار</a>
                    </div>
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
        </section>
    </div>
</section>
@endsection
