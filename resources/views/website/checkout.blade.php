@extends('website.layouts.master')
@section('title')إتمام الشراء | نفّذها@endsection
@section('content')
<section class="rf-pp rf-sl rf-ct" dir="rtl">
    <div class="container">
        <nav class="rf-pp-trail" aria-label="مسار الصفحة">
            <a href="{{ url('/') }}">الرئيسية</a> <span aria-hidden="true">‹</span>
            <a href="{{ url('cart') }}">سلة المشتريات</a> <span aria-hidden="true">‹</span> <span>إتمام الشراء</span>
        </nav>
        <header class="rf-sl-head">
            <div>
                <h1>إتمام الشراء</h1>
                <p>راجع طلبك ثم ادفع عبر EasyKash. يصل الطلب للمستقلين فور تأكيد الدفع.</p>
            </div>
        </header>

        @if ($errors->any())
            <div class="alert alert-danger" role="alert">@foreach ($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>
        @endif

        <div class="rf-pp-layout">
            <div class="rf-pp-main">
                <div class="rf-pp-card">
                    <h2>الخدمات المطلوبة</h2>
                    <ul class="rf-ct-list plain">
                        @foreach ($items as $item)
                            <li class="rf-ct-row">
                                <div class="rf-ct-text">
                                    <strong>{{ $item->serviceData->name }}</strong>
                                    @if ($item->userData)<small><i class="bi bi-person" aria-hidden="true"></i> {{ $item->userData->name }}</small>@endif
                                </div>
                                <span class="rf-ct-price">{{ \App\Support\Money::show($item->serviceData->price) }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
            <aside class="rf-pp-side">
                <div class="rf-pp-card rf-sd-buy">
                    <dl class="rf-ct-sum">
                        <div><dt>عدد الخدمات</dt><dd>{{ $items->count() }}</dd></div>
                        <div class="total"><dt>المجموع</dt><dd>{{ \App\Support\Money::show($total) }}</dd></div>
                    </dl>
                    <form method="post" action="{{ url('checkout/order') }}">
                        @csrf
                        <button type="submit" class="btn btn-primary w-100"><i class="bi bi-lock" aria-hidden="true"></i> ادفع وأتمّ الطلب</button>
                    </form>
                    <ul class="rf-pp-promise">
                        <li><i class="bi bi-credit-card" aria-hidden="true"></i> الدفع عبر EasyKash (بطاقة، فوري، محفظة) بالجنيه المصري</li>
                        <li><i class="bi bi-send-check" aria-hidden="true"></i> يُرسل الطلب للمستقلين فور تأكيد الدفع</li>
                    </ul>
                </div>
            </aside>
        </div>
    </div>
</section>
@endsection
