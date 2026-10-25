@extends('website.layouts.master')
@php
    $owner = $work->User;
    $images = array_values(array_filter(array_merge([$work->image], is_array($work->more_images) ? array_map('trim', $work->more_images) : [])));
    $viewer = auth()->user();
    $isOwner = $viewer && $owner && (int) $viewer->id === (int) $owner->id;
    $profileUrl = $owner?->user_name ? url('user/' . $owner->user_name) : null;
    $skills = $work->sub_categories;
    $firstSkill = $skills->first();
    $pageUrl = url('portfolio/' . $work->id . '-' . $work->slug);
@endphp
@section('title'){{ $work->title }}{{ $owner ? ' — ' . $owner->name : '' }} | نفّذها@endsection
@section('meta_description'){{ \Illuminate\Support\Str::limit(strip_tags((string) $work->description), 160) }}@endsection
@section('og_title'){{ $work->title }}@endsection
@section('og_image'){{ asset('assets/uploads/portfolios/' . $work->image) }}@endsection
@section('content')
<section class="rf-pp rf-pw" dir="rtl">
    <div class="container">
        @unless ($work->isPublished())
            <div class="rf-pp-preview" role="status">
                <i class="bi bi-eye-slash" aria-hidden="true"></i>
                <div><strong>هذا العمل لا يظهر للزوار {{ (int) $work->status === \App\Models\front\UserPortfolio::STATUS_REJECTED ? '(غير مقبول)' : '(بانتظار مراجعة الفريق)' }}.</strong>
                    @if ($isOwner) <a href="{{ route('user.portfolio.index') }}">أعمالي</a>@endif</div>
            </div>
        @endunless

        <nav class="rf-pp-trail" aria-label="مسار الصفحة">
            <a href="{{ route('freelancers.index') }}">المستقلين</a>
            @if ($owner && $profileUrl)<span aria-hidden="true">‹</span> <a href="{{ $profileUrl }}#works">{{ $owner->name }}</a>@endif
            <span aria-hidden="true">‹</span> <span>{{ $work->title }}</span>
        </nav>

        <div class="rf-pp-layout">
            <div class="rf-pp-main">
                <article class="rf-pp-card">
                    <h1 class="rf-pw-title">{{ $work->title }}</h1>
                    <p class="rf-pw-meta">
                        @if ($work->Category)<span><i class="bi bi-folder2" aria-hidden="true"></i> {{ $work->Category->name }}</span>@endif
                        <span><i class="bi bi-calendar3" aria-hidden="true"></i> {{ $work->created_at?->translatedFormat('F Y') }}</span>
                        @if (count($images) > 1)<span><i class="bi bi-images" aria-hidden="true"></i> {{ count($images) }} صور</span>@endif
                    </p>

                    <figure class="rf-pw-stage">
                        <img id="rf-pw-main" src="{{ asset('assets/uploads/portfolios/' . $images[0]) }}" alt="{{ $work->title }}">
                    </figure>
                    @if (count($images) > 1)
                        <div class="rf-pw-thumbs" role="list" aria-label="صور العمل">
                            @foreach ($images as $i => $img)
                                <button type="button" class="rf-pw-thumb {{ $i === 0 ? 'on' : '' }}" data-src="{{ asset('assets/uploads/portfolios/' . $img) }}" aria-label="الصورة {{ $i + 1 }}" role="listitem">
                                    <img src="{{ asset('assets/uploads/portfolios/' . $img) }}" alt="" loading="lazy">
                                </button>
                            @endforeach
                        </div>
                    @endif

                    <h2>عن العمل</h2>
                    <p class="rf-pp-bio">{{ $work->description }}</p>

                    @if ($skills->isNotEmpty())
                        <h3>المهارات المستخدمة</h3>
                        <ul class="rf-pp-skills">
                            @foreach ($skills as $skill)
                                <li><a href="{{ route('freelancers.index', ['service' => $skill->id]) }}">{{ $skill->name }}</a></li>
                            @endforeach
                        </ul>
                    @endif

                    @if ($work->link)
                        <a href="{{ $work->link }}" target="_blank" rel="noopener nofollow" class="btn btn-light mt-3"><i class="bi bi-box-arrow-up-left" aria-hidden="true"></i> مشاهدة العمل على موقعه</a>
                    @endif
                </article>
            </div>

            <aside class="rf-pp-side">
                @if ($owner)
                    <div class="rf-pp-card rf-pw-owner">
                        <img src="{{ $owner->avatarUrl(256) }}" alt="" width="72" height="72">
                        <div>
                            <strong>{{ $owner->name }}</strong>
                            @if ($owner->job_title)<small>{{ $owner->job_title }}</small>@endif
                            @if ($owner->ratingLabel())
                                <span class="rf-pw-rate">@include('website.freelancers.partials.stars', ['value' => $owner->rate]) {{ $owner->ratingLabel() }}</span>
                            @endif
                        </div>
                        @if ($profileUrl && ($owner->isApprovedFreelancer() || $isOwner))
                            <a href="{{ $profileUrl }}" class="btn btn-light w-100 mt-3">عرض ملفه وأعماله</a>
                        @endif
                    </div>
                @endif

                @if ($isOwner)
                    <div class="rf-pp-card">
                        <a href="{{ route('user.portfolio.edit', [$work->id, $work->slug]) }}" class="btn btn-primary w-100"><i class="bi bi-pencil" aria-hidden="true"></i> تعديل العمل</a>
                    </div>
                @else
                    <div class="rf-pp-card rf-pp-cta">
                        <h2>تريد عملًا مثل هذا؟</h2>
                        <p>اكتب طلبك؛ يصل إلى {{ $owner?->name ?? 'المستقل' }} والمستقلين المعتمدين في نفس التخصص، وتختار العرض الأنسب.</p>
                        <a href="{{ ($firstSkill?->requestUrl()) ?: route('requests.create') }}" class="btn btn-primary w-100"><i class="bi bi-send" aria-hidden="true"></i> اطلب عرض سعر</a>
                    </div>
                @endif

                <div class="rf-pp-card rf-pp-facts">
                    <a class="btn btn-light w-100" target="_blank" rel="noopener" href="https://wa.me/?text={{ rawurlencode($work->title . ' — ' . $pageUrl) }}"><i class="bi bi-whatsapp" aria-hidden="true"></i> مشاركة على واتساب</a>
                    <button type="button" class="rf-pp-share" data-url="{{ $pageUrl }}"><i class="bi bi-link-45deg" aria-hidden="true"></i> <span>نسخ رابط العمل</span></button>
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
