@extends('website.layouts.master')
@php
    $sort = (string) request('sort');
    // «خدمة واحدة»، «خدمتان»، «4 خدمات»، «11 خدمة»
    $countLabel = fn (int $n) => match (true) { $n === 1 => 'خدمة واحدة', $n === 2 => 'خدمتان', $n >= 3 && $n <= 10 => $n . ' خدمات', default => $n . ' خدمة' };
@endphp
@section('title'){{ $category->name }} — خدمات جاهزة | نفّذها@endsection
@section('meta_description')خدمات {{ $category->name }} جاهزة بسعر ثابت من مستقلين معتمدين في نفّذها، الدفع بالجنيه المصري.@endsection
@section('content')
<section class="rf-pp rf-sl" dir="rtl">
    <div class="container">
        <nav class="rf-pp-trail" aria-label="مسار الصفحة">
            <a href="{{ route('services.index') }}">الخدمات</a> <span aria-hidden="true">‹</span> <span>{{ $category->name }}</span>
        </nav>
        <header class="rf-sl-head">
            <div>
                <h1>{{ $category->name }}</h1>
                <p>خدمات جاهزة بسعر ثابت من مستقلين معتمدين. تفضّل أن يصلك أكثر من عرض؟ <a href="{{ ($category->requestUrl()) ?: route('requests.create') }}">اطلب عرض سعر</a>.</p>
            </div>
            @include('website.partials.list-search', ['name' => 'search', 'placeholder' => 'ابحث في خدمات ' . $category->name . '…',
                'sorts' => ['' => 'الأحدث', 'popular' => 'الأكثر طلبًا', 'price' => 'الأقل سعرًا'], 'sort' => $sort])
        </header>

        @if ($services->isEmpty())
            <div class="rf-pp-card rf-sl-empty">
                <i class="bi bi-grid" aria-hidden="true"></i>
                <strong>{{ request('search') ? 'لا توجد خدمات تطابق بحثك.' : 'لا توجد خدمات جاهزة في هذا القسم بعد.' }}</strong>
                <p>اكتب ما تحتاجه، ويرسل لك المستقلون المعتمدون عروضهم.</p>
                <a href="{{ ($category->requestUrl()) ?: route('requests.create') }}" class="btn btn-primary">اطلب عرض سعر</a>
            </div>
        @else
            <p class="rf-sl-count">{{ $countLabel($services->total()) }}</p>
            <div class="rf-sl-grid">
                @foreach ($services as $serv)
                    <article class="rf-sl-card">
                        <span class="rf-pp-work-img"><img src="{{ asset('assets/uploads/services/' . $serv->image) }}" alt="" loading="lazy"></span>
                        <div class="rf-sl-body">
                            <h2><a href="{{ $serv->url() }}" class="rf-fl-link">{{ $serv->name }}</a></h2>
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
</section>
@endsection
