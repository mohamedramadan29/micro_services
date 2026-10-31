@extends('website.layouts.master')
@php
    // A service that was hidden or removed after it was added is shown but never counted (checkout skips it too).
    $available = fn ($item) => $item->serviceData && $item->serviceData->isActive() && (float) $item->serviceData->price > 0
        && ! (auth()->check() && (int) $item->serviceData->user_id === (int) auth()->id());
    $payable = $items->filter($available);
    $total = $payable->sum(fn ($item) => (float) $item->serviceData->price);
@endphp
@section('title')سلة المشتريات | نفّذها@endsection
@section('content')
<section class="rf-pp rf-sl rf-ct" dir="rtl">
    <div class="container">
        <nav class="rf-pp-trail" aria-label="مسار الصفحة">
            <a href="{{ url('/') }}">الرئيسية</a> <span aria-hidden="true">‹</span> <span>سلة المشتريات</span>
        </nav>
        <header class="rf-sl-head">
            <div>
                <h1>سلة المشتريات</h1>
                <p>الخدمات الجاهزة التي اخترتها. تدفع مرة واحدة عبر EasyKash بالجنيه المصري.</p>
            </div>
        </header>

        @if ($errors->any())
            <div class="alert alert-danger" role="alert">@foreach ($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>
        @endif

        @if ($items->isEmpty())
            <div class="rf-pp-card rf-sl-empty">
                <i class="bi bi-cart3" aria-hidden="true"></i>
                <strong>سلتك فارغة.</strong>
                <p>تصفح الخدمات الجاهزة وأضف ما يناسبك.</p>
                <a href="{{ route('services.ready') }}" class="btn btn-primary">تصفح الخدمات الجاهزة</a>
            </div>
        @else
            <div class="rf-pp-layout">
                <div class="rf-pp-main">
                    <ul class="rf-pp-card rf-ct-list">
                        @foreach ($items as $item)
                            @php $service = $item->serviceData; $ok = $available($item); @endphp
                            <li class="rf-ct-row {{ $ok ? '' : 'off' }}">
                                @if ($service)
                                    <a href="{{ $service->url() }}" class="rf-ct-img"><img src="{{ asset('assets/uploads/services/' . $service->image) }}" alt="" loading="lazy"></a>
                                @endif
                                <div class="rf-ct-text">
                                    <strong>@if ($service)<a href="{{ $service->url() }}" class="rf-fl-link">{{ $service->name }}</a>@else{{ $item->service_name }}@endif</strong>
                                    @if ($item->userData)<small><i class="bi bi-person" aria-hidden="true"></i> {{ $item->userData->name }}</small>@endif
                                    @unless ($ok)<span class="rf-acc-pill bad">غير متاحة للشراء الآن</span>@endunless
                                </div>
                                <span class="rf-ct-price">@if ($service){{ \App\Support\Money::show($service->price) }}@endif</span>
                                <form method="post" action="{{ url('cart/delete') }}" onsubmit="return confirm('حذف الخدمة من السلة؟')">
                                    @csrf
                                    <input type="hidden" name="cartId" value="{{ $item->id }}">
                                    <button type="submit" class="rf-ct-remove" aria-label="حذف {{ $service->name ?? $item->service_name }} من السلة"><i class="bi bi-trash3" aria-hidden="true"></i></button>
                                </form>
                            </li>
                        @endforeach
                    </ul>
                </div>
                <aside class="rf-pp-side">
                    <div class="rf-pp-card rf-sd-buy">
                        <dl class="rf-ct-sum">
                            <div><dt>عدد الخدمات</dt><dd>{{ $payable->count() }}</dd></div>
                            <div class="total"><dt>المجموع</dt><dd>{{ \App\Support\Money::show($total) }}</dd></div>
                        </dl>
                        @if ($payable->isEmpty())
                            <p class="rf-ct-note">لا توجد في السلة خدمة متاحة للشراء.</p>
                        @elseif (auth()->check())
                            <a href="{{ url('checkout') }}" class="btn btn-primary w-100"><i class="bi bi-credit-card" aria-hidden="true"></i> إتمام الشراء</a>
                        @else
                            <a href="{{ url('login') }}" class="btn btn-primary w-100"><i class="bi bi-box-arrow-in-left" aria-hidden="true"></i> سجّل دخولك لإتمام الشراء</a>
                        @endif
                        <ul class="rf-pp-promise">
                            <li><i class="bi bi-credit-card" aria-hidden="true"></i> بطاقة، فوري أو محفظة عبر EasyKash</li>
                            <li><i class="bi bi-chat-dots" aria-hidden="true"></i> تتواصل مع المستقل داخل نفّذها بعد الطلب</li>
                        </ul>
                    </div>
                    <a href="{{ route('services.ready') }}" class="btn btn-light w-100 mt-3">أضف خدمات أخرى</a>
                </aside>
            </div>
        @endif
    </div>
</section>
@endsection
