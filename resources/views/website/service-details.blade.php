@extends('website.layouts.master')
@php
    use App\Models\admin\Service;
    $tags = collect(explode(',', (string) $service->tags))->map(fn ($t) => trim($t))->filter();
    $sellerUrl = $seller?->user_name && $seller->isApprovedFreelancer() ? url('user/' . $seller->user_name) : null;
    $canBuy = $service->isActive() && ! $isOwner;
@endphp
@section('title'){{ $service->name }} | نفّذها@endsection
@section('meta_description'){{ \Illuminate\Support\Str::limit(strip_tags((string) $service->description), 160) }}@endsection
@section('og_title'){{ $service->name }} — {{ \App\Support\Money::format($service->price) }}@endsection
@section('og_image'){{ asset('assets/uploads/services/' . $service->image) }}@endsection
@section('content')
<section class="rf-pp rf-pw rf-sd" dir="rtl">
    <div class="container">
        @unless ($service->isActive())
            <div class="rf-pp-preview" role="status">
                <i class="bi bi-eye-slash" aria-hidden="true"></i>
                <div><strong>هذه الخدمة لا تظهر للعملاء ({{ Service::STATUSES[(int) $service->status] ?? '' }}).</strong>
                    @if ($isOwner && $service->review_note) ملاحظة الفريق: {{ $service->review_note }} @endif
                    @if ($isOwner) <a href="{{ url('service/update/' . $service->id) }}">تعديل الخدمة</a>@endif</div>
            </div>
        @endunless

        <nav class="rf-pp-trail" aria-label="مسار الصفحة">
            <a href="{{ route('services.index') }}">الخدمات</a>
            @if ($service->subcategory)<span aria-hidden="true">‹</span> <a href="{{ url('services/' . $service->subcategory->slug) }}">{{ $service->subcategory->name }}</a>@endif
            <span aria-hidden="true">‹</span> <span>{{ $service->name }}</span>
        </nav>

        <div class="rf-pp-layout">
            <div class="rf-pp-main">
                <article class="rf-pp-card">
                    <h1 class="rf-pw-title">{{ $service->name }}</h1>
                    <p class="rf-pw-meta">
                        @if ($service->subcategory || $service->category)<span><i class="bi bi-folder2" aria-hidden="true"></i> {{ $service->subcategory?->name ?? $service->category?->name }}</span>@endif
                        @if ((int) $service->users_num_buy > 0)<span><i class="bi bi-bag-check" aria-hidden="true"></i> اشتراها {{ (int) $service->users_num_buy }}</span>@endif
                        <span><i class="bi bi-calendar3" aria-hidden="true"></i> {{ $service->created_at?->translatedFormat('F Y') }}</span>
                    </p>
                    <figure class="rf-pw-stage">
                        <img src="{{ asset('assets/uploads/services/' . $service->image) }}" alt="{{ $service->name }}">
                    </figure>

                    <h2>تفاصيل الخدمة</h2>
                    <p class="rf-pp-bio">{{ $service->description }}</p>

                    @if ($tags->isNotEmpty())
                        <h3>كلمات مفتاحية</h3>
                        <ul class="rf-pp-skills rf-sd-tags">
                            @foreach ($tags as $tag)<li><span>{{ $tag }}</span></li>@endforeach
                        </ul>
                    @endif
                </article>

                @if ($more_servicess->isNotEmpty())
                    <section class="rf-pp-card" aria-labelledby="sd-more">
                        <h2 id="sd-more">خدمات مشابهة</h2>
                        <div class="rf-pp-works">
                            @foreach ($more_servicess as $serv)
                                <a class="rf-pp-work" href="{{ $serv->url() }}">
                                    <span class="rf-pp-work-img"><img src="{{ asset('assets/uploads/services/' . $serv->image) }}" alt="" loading="lazy"></span>
                                    <strong>{{ $serv->name }}</strong>
                                    <small>{{ $serv->user?->name }} · <b class="rf-sd-mini-price">{{ \App\Support\Money::show($serv->price) }}</b></small>
                                </a>
                            @endforeach
                        </div>
                    </section>
                @endif
            </div>

            <aside class="rf-pp-side">
                <div class="rf-pp-card rf-sd-buy">
                    <div class="rf-sd-price">
                        <small>سعر الخدمة</small>
                        <strong>{{ \App\Support\Money::show($service->price) }}</strong>
                    </div>
                    @if ($isOwner)
                        <a href="{{ url('service/update/' . $service->id) }}" class="btn btn-primary w-100"><i class="bi bi-pencil" aria-hidden="true"></i> تعديل الخدمة</a>
                        <a href="{{ url('service/index') }}" class="btn btn-light w-100 mt-2">خدماتي المعروضة</a>
                    @elseif ($canBuy)
                        @auth
                            <form method="post" action="{{ url('create_order') }}">
                                @csrf
                                <input type="hidden" name="service_id" value="{{ $service->id }}">
                                <button type="submit" class="btn btn-primary w-100 rf-sd-pay"><i class="bi bi-lightning-charge" aria-hidden="true"></i> اطلب الخدمة الآن</button>
                            </form>
                        @else
                            <a href="{{ url('login') }}" class="btn btn-primary w-100"><i class="bi bi-lightning-charge" aria-hidden="true"></i> سجّل دخولك لطلب الخدمة</a>
                        @endauth
                        <form method="post" action="{{ url('cart/add') }}" class="mt-2">
                            @csrf
                            <input type="hidden" name="service_id" value="{{ $service->id }}">
                            <button type="submit" class="btn btn-light w-100"><i class="bi bi-cart-plus" aria-hidden="true"></i> أضف إلى السلة</button>
                        </form>
                        <ul class="rf-pp-promise">
                            <li><i class="bi bi-credit-card" aria-hidden="true"></i> الدفع عبر EasyKash بالجنيه المصري</li>
                            <li><i class="bi bi-chat-dots" aria-hidden="true"></i> تتواصل مع المستقل داخل نفّذها بعد الطلب</li>
                            <li><i class="bi bi-life-preserver" aria-hidden="true"></i> فريق نفّذها معك لو احتجت مساعدة</li>
                        </ul>
                    @endif
                </div>

                @if ($seller)
                    <div class="rf-pp-card rf-pw-owner">
                        <img src="{{ $seller->avatarUrl(256) }}" alt="" width="72" height="72">
                        <div>
                            <strong>{{ $seller->name }}</strong>
                            @if ($seller->job_title)<small>{{ $seller->job_title }}</small>@endif
                            <span class="rf-pw-rate">
                                @if ($seller->ratingLabel())@include('website.freelancers.partials.stars', ['value' => $seller->rate]) {{ $seller->ratingLabel() }} ·@endif
                                {{ $seller->completedProjects() }} مشروع منجز
                            </span>
                        </div>
                        @if ($sellerUrl)<a href="{{ $sellerUrl }}" class="btn btn-light w-100 mt-3">عرض ملفه وأعماله</a>@endif
                        @if ($canBuy)
                            @auth
                                <form action="{{ url('conversation/start') }}" method="post" class="w-100 mt-2">
                                    @csrf
                                    <input type="hidden" name="service_id" value="{{ $service->id }}">
                                    <input type="hidden" name="receiver_id" value="{{ $service->user_id }}">
                                    <button type="submit" class="btn btn-light w-100"><i class="bi bi-chat-dots" aria-hidden="true"></i> اسأل المستقل قبل الطلب</button>
                                </form>
                            @else
                                <a href="{{ url('login') }}" class="btn btn-light w-100 mt-2"><i class="bi bi-chat-dots" aria-hidden="true"></i> اسأل المستقل قبل الطلب</a>
                            @endauth
                        @endif
                    </div>
                @endif

                <div class="rf-pp-card rf-pp-facts">
                    <a class="btn btn-light w-100" target="_blank" rel="noopener" href="https://wa.me/?text={{ rawurlencode($service->name . ' — ' . $service->url()) }}"><i class="bi bi-whatsapp" aria-hidden="true"></i> مشاركة على واتساب</a>
                    <button type="button" class="rf-pp-share" data-url="{{ $service->url() }}"><i class="bi bi-link-45deg" aria-hidden="true"></i> <span>نسخ رابط الخدمة</span></button>
                </div>
            </aside>
        </div>
    </div>
</section>
<script>
    (function () {
        var btn = document.querySelector('.rf-pp-share');
        if (btn) btn.addEventListener('click', function () {
            var url = btn.getAttribute('data-url'), label = btn.querySelector('span'), text = label.textContent;
            var done = function () { label.textContent = 'تم نسخ الرابط'; setTimeout(function () { label.textContent = text; }, 2000); };
            if (navigator.clipboard) navigator.clipboard.writeText(url).then(done, function () { window.prompt('انسخ الرابط', url); });
            else window.prompt('انسخ الرابط', url);
        });
        // One click on «اطلب الخدمة الآن» (it leaves for the payment page).
        document.querySelectorAll('.rf-sd-pay').forEach(function (b) {
            b.form.addEventListener('submit', function () { b.disabled = true; b.textContent = 'جاري التحويل للدفع…'; });
        });
    })();
</script>
@endsection
