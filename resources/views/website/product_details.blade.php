@extends('website.layouts.master')
@php
    $page = \App\Support\SiteContent::first('products.page');
    $images = array_merge(
        [asset('assets/uploads/product_images/' . $product->image)],
        $product->gallary->map(fn ($g) => asset('assets/uploads/product_gallery/' . $g->image))->all()
    );
    $physical = $product->type === 'physical';
    $user = auth()->user();
    $pageUrl = url('product/' . $product->slug);
@endphp
@section('title'){{ $product->meta_title ?: $product->name }} | نفّذها@endsection
@section('meta_description'){{ \Illuminate\Support\Str::limit(strip_tags((string) ($product->meta_description ?: $product->short_description ?: $product->description)), 160) }}@endsection
@section('og_title'){{ $product->name }}@endsection
@section('og_image'){{ $images[0] }}@endsection
@section('content')
<section class="rf-pp rf-pw rf-sd rf-pr" dir="rtl">
    <div class="container">
        @if ((int) $product->status !== 1)
            <div class="rf-pp-preview" role="status">
                <i class="bi bi-eye-slash" aria-hidden="true"></i>
                <div><strong>هذا المنتج مخفي ولا يظهر للزوار.</strong></div>
            </div>
        @endif

        <nav class="rf-pp-trail" aria-label="مسار الصفحة">
            <a href="{{ url('/') }}">الرئيسية</a>
            <span aria-hidden="true">‹</span> <a href="{{ url('products') }}">{{ $page['title'] }}</a>
            <span aria-hidden="true">‹</span> <span>{{ $product->name }}</span>
        </nav>

        <div class="rf-pp-layout">
            <div class="rf-pp-main">
                <article class="rf-pp-card">
                    <h1 class="rf-pw-title">{{ $product->name }}</h1>
                    <p class="rf-pw-meta">
                        <span><i class="bi {{ $physical ? 'bi-truck' : 'bi-cloud-arrow-down' }}" aria-hidden="true"></i> {{ $physical ? 'منتج يُشحن' : 'منتج رقمي' }}</span>
                        @if (count($images) > 1)<span><i class="bi bi-images" aria-hidden="true"></i> {{ count($images) }} صور</span>@endif
                    </p>

                    <figure class="rf-pw-stage">
                        <img id="rf-pw-main" src="{{ $images[0] }}" alt="{{ $product->name }}">
                    </figure>
                    @if (count($images) > 1)
                        <div class="rf-pw-thumbs" role="list" aria-label="صور المنتج">
                            @foreach ($images as $i => $img)
                                <button type="button" class="rf-pw-thumb {{ $i === 0 ? 'on' : '' }}" data-src="{{ $img }}" aria-label="الصورة {{ $i + 1 }}" role="listitem">
                                    <img src="{{ $img }}" alt="" loading="lazy">
                                </button>
                            @endforeach
                        </div>
                    @endif

                    <h2>عن المنتج</h2>
                    {{-- The description is written by the team in the admin editor (HTML). --}}
                    <div class="rf-pr-desc">{!! $product->description !!}</div>

                    @if ($product->video)
                        <h2>فيديو المنتج</h2>
                        <video class="rf-pr-video" controls preload="metadata">
                            <source src="{{ asset('assets/uploads/product_videos/' . $product->video) }}" type="video/mp4">
                        </video>
                    @endif
                </article>

                @if ($related->isNotEmpty())
                    <section class="rf-pp-card" aria-labelledby="pr-more">
                        <h2 id="pr-more">منتجات أخرى</h2>
                        <div class="rf-pp-works">
                            @foreach ($related as $item)
                                <a class="rf-pp-work" href="{{ url('product/' . $item->slug) }}">
                                    <span class="rf-pp-work-img"><img src="{{ asset('assets/uploads/product_images/' . $item->image) }}" alt="" loading="lazy"></span>
                                    <strong>{{ $item->name }}</strong>
                                    <small>@include('website.partials.product-price', ['product' => $item])</small>
                                </a>
                            @endforeach
                        </div>
                    </section>
                @endif
            </div>

            <aside class="rf-pp-side">
                <div class="rf-pp-card rf-sd-buy">
                    <div class="rf-sd-price">
                        <small>سعر المنتج</small>
                        @include('website.partials.product-price', ['product' => $product])
                    </div>

                    @if ($user)
                        <details class="rf-pr-buy" @if ($errors->any()) open @endif>
                            <summary class="btn btn-primary w-100"><i class="bi bi-bag" aria-hidden="true"></i> شراء المنتج</summary>
                            @if ($errors->any())
                                <div class="alert alert-danger mt-3 mb-0" role="alert">
                                    @foreach ($errors->all() as $error)<div>{{ $error }}</div>@endforeach
                                </div>
                            @endif
                            <form method="post" action="{{ url('product_order') }}" data-track-start="product_order" class="rf-pr-form">
                                @csrf
                                <label for="pr-name">الاسم</label>
                                <input id="pr-name" class="form-control" name="name" required autocomplete="name" value="{{ old('name', $user->name) }}">
                                <label for="pr-phone">رقم الهاتف</label>
                                <input id="pr-phone" class="form-control" name="phone" required dir="ltr" autocomplete="tel" value="{{ old('phone', $user->phone) }}">
                                <label for="pr-email">البريد الإلكتروني</label>
                                <input id="pr-email" type="email" class="form-control" name="email" required dir="ltr" autocomplete="email" value="{{ old('email', $user->email) }}">
                                @if ($physical)
                                    <label for="pr-country">الدولة</label>
                                    <input id="pr-country" class="form-control" name="country" required autocomplete="country-name" value="{{ old('country') }}">
                                    <label for="pr-city">المدينة</label>
                                    <input id="pr-city" class="form-control" name="city" required autocomplete="address-level2" value="{{ old('city') }}">
                                    <label for="pr-address">العنوان بالتفصيل</label>
                                    <input id="pr-address" class="form-control" name="address" required autocomplete="street-address" value="{{ old('address') }}">
                                @endif
                                <label for="pr-note">ملاحظات إضافية (اختياري)</label>
                                <textarea id="pr-note" name="note" rows="3" class="form-control">{{ old('note') }}</textarea>
                                <input type="hidden" name="product_id" value="{{ $product->id }}">
                                <input type="hidden" name="product_name" value="{{ $product->name }}">
                                <input type="hidden" name="product_price" value="{{ $product->price }}">
                                <input type="hidden" name="qty" value="1">
                                <input type="hidden" name="product_type" value="{{ $product->type }}">
                                <button type="submit" class="btn btn-primary w-100 mt-3"><i class="bi bi-credit-card" aria-hidden="true"></i> {{ (float) $product->price > 0 ? 'متابعة إلى الدفع' : 'احصل عليه مجانًا' }}</button>
                            </form>
                        </details>
                    @else
                        <a href="{{ url('login') }}" class="btn btn-primary w-100"><i class="bi bi-box-arrow-in-left" aria-hidden="true"></i> سجّل دخولك لشراء المنتج</a>
                    @endif

                    <ul class="rf-pp-promise">
                        <li><i class="bi {{ $physical ? 'bi-truck' : 'bi-cloud-arrow-down' }}" aria-hidden="true"></i> {{ $physical ? $page['physical_note'] : $page['digital_note'] }}</li>
                        <li><i class="bi bi-credit-card" aria-hidden="true"></i> الدفع عبر EasyKash بالجنيه المصري</li>
                        <li><i class="bi bi-life-preserver" aria-hidden="true"></i> فريق نفّذها معك لو احتجت مساعدة</li>
                    </ul>
                </div>

                <div class="rf-pp-card rf-pp-facts">
                    <a class="btn btn-light w-100" target="_blank" rel="noopener" href="https://wa.me/?text={{ rawurlencode($product->name . ' — ' . $pageUrl) }}"><i class="bi bi-whatsapp" aria-hidden="true"></i> مشاركة على واتساب</a>
                    <button type="button" class="rf-pp-share" data-url="{{ $pageUrl }}"><i class="bi bi-link-45deg" aria-hidden="true"></i> <span>نسخ رابط المنتج</span></button>
                </div>
            </aside>
        </div>
    </div>
</section>
<script>
    (function () {
        var main = document.getElementById('rf-pw-main');
        document.querySelectorAll('.rf-pw-thumb').forEach(function (t) {
            t.addEventListener('click', function () {
                main.src = t.getAttribute('data-src');
                document.querySelectorAll('.rf-pw-thumb').forEach(function (o) { o.classList.toggle('on', o === t); });
            });
        });
        var btn = document.querySelector('.rf-pp-share');
        if (!btn) return;
        btn.addEventListener('click', function () {
            var url = btn.getAttribute('data-url');
            var label = btn.querySelector('span'), text = label.textContent;
            var done = function () { label.textContent = 'تم نسخ الرابط'; setTimeout(function () { label.textContent = text; }, 2000); };
            if (navigator.clipboard) navigator.clipboard.writeText(url).then(done, function () { window.prompt('انسخ الرابط', url); });
            else window.prompt('انسخ الرابط', url);
        });
    })();
</script>
@endsection
