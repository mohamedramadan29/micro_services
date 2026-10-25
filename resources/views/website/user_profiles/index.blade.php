@extends('website.layouts.master')
@php
    use App\Models\User;
    $rating = $user->ratingLabel();
    $ratingCount = (int) $user->rating_count ?: $ratings->count();
    $done = $user->completedProjects();
    $since = $user->freelancer_reviewed_at && $user->isApprovedFreelancer() ? $user->freelancer_reviewed_at : $user->created_at;
    $bio = trim((string) ($user->bio ?: $user->info));
    $headline = $user->job_title ?: $user->specializations->pluck('name')->take(2)->implode(' · ');
    $languages = collect(preg_split('/[،,]+/u', (string) $user->languages))->map(fn ($l) => trim($l))->filter();
    $requestUrl = ($firstService?->requestUrl()) ?: route('requests.create');
    $raterName = function ($rater) {
        $parts = preg_split('/\s+/u', trim((string) $rater?->name));
        if (! $parts || $parts[0] === '') return 'عميل نفّذها';
        return $parts[0] . (isset($parts[1]) ? ' ' . mb_substr($parts[1], 0, 1) . '.' : '');
    };
@endphp
@section('title'){{ $user->name }} — مستقل في نفّذها@endsection
@section('meta_description'){{ \Illuminate\Support\Str::limit($bio ?: ($user->name . ' مستقل معتمد في نفّذها: ' . $headline), 160) }}@endsection
@section('og_title'){{ $user->name }} — {{ $headline ?: 'مستقل في نفّذها' }}@endsection
@section('og_image'){{ $user->avatarUrl(512) }}@endsection
@section('content')
<section class="rf-pp" dir="rtl">
    <div class="container">
        @if (! $listed)
            <div class="rf-pp-preview" role="status">
                <i class="bi bi-eye-slash" aria-hidden="true"></i>
                <div>
                    <strong>هذه الصفحة لا تظهر للزوار بعد.</strong>
                    @if ($isOwner)
                        تظهر بعد اعتماد فريق نفّذها لحسابك. <a href="{{ url('update-account') }}#rf-freelancer">تابع خطوات ملفك</a>
                    @else
                        تراها لأنك من فريق الإدارة.
                    @endif
                </div>
            </div>
        @endif

        <nav class="rf-pp-trail" aria-label="مسار الصفحة">
            <a href="{{ route('freelancers.index') }}">المستقلين</a> <span aria-hidden="true">‹</span> <span>{{ $user->name }}</span>
        </nav>

        {{-- Title block --}}
        <header class="rf-pp-head">
            <div class="rf-pp-photo">
                <img src="{{ $user->avatarUrl(512) }}" alt="{{ $user->name }}" width="168" height="168">
                @if ($user->isApprovedFreelancer())
                    <span class="rf-pp-stamp" title="راجع فريق نفّذها هذا الملف واعتمده"><i class="bi bi-patch-check-fill" aria-hidden="true"></i> معتمد</span>
                @endif
            </div>
            <div class="rf-pp-id">
                <h1>{{ $user->name }}</h1>
                @if ($headline)<p class="rf-pp-headline">{{ $headline }}</p>@endif
                <div class="rf-pp-tags">
                    @if ($user->is_featured)<span class="rf-pp-tag gold"><i class="bi bi-award-fill" aria-hidden="true"></i> مستقل موثوق</span>@endif
                    @if ($user->availability)
                        <span class="rf-pp-tag {{ $user->availability === 'available' ? 'on' : '' }}"><i class="bi bi-circle-fill" aria-hidden="true"></i> {{ User::AVAILABILITY[$user->availability] ?? '' }}</span>
                    @endif
                    @if ($languages->isNotEmpty())<span class="rf-pp-tag"><i class="bi bi-translate" aria-hidden="true"></i> {{ $languages->implode('، ') }}</span>@endif
                </div>
            </div>
            <dl class="rf-pp-stats">
                <div>
                    <dt>التقييم</dt>
                    <dd>
                        @if ($rating)
                            <strong>{{ $rating }}</strong> @include('website.freelancers.partials.stars', ['value' => $user->rate])
                            <small>{{ $ratingCount }} {{ $ratingCount === 1 ? 'تقييم' : 'تقييمات' }}</small>
                        @else
                            <strong class="rf-pp-new">جديد</strong><small>لا تقييمات بعد</small>
                        @endif
                    </dd>
                </div>
                <div><dt>مشاريع منجزة</dt><dd><strong>{{ $done }}</strong><small>عبر نفّذها</small></dd></div>
                <div><dt>نماذج أعمال</dt><dd><strong>{{ $works->count() }}</strong><small>منشورة</small></dd></div>
                <div><dt>يبدأ سعره من</dt><dd>@if ($user->starting_price !== null)<strong>{{ \App\Support\Money::show($user->starting_price) }}</strong>@else<strong class="rf-pp-new">حسب المشروع</strong>@endif</dd></div>
            </dl>
        </header>

        <nav class="rf-pp-tabs" aria-label="أقسام الملف">
            <a href="#about">نبذة</a>
            <a href="#works">الأعمال <span>{{ $works->count() }}</span></a>
            <a href="#ratings">التقييمات <span>{{ $ratingCount }}</span></a>
            @if ($services->isNotEmpty())<a href="#services">الخدمات <span>{{ $services->count() }}</span></a>@endif
        </nav>

        <div class="rf-pp-layout">
            <div class="rf-pp-main">
                {{-- About --}}
                <section class="rf-pp-card" id="about" aria-labelledby="pp-about">
                    <h2 id="pp-about">نبذة عن {{ $user->name }}</h2>
                    @if ($bio)
                        <p class="rf-pp-bio">{{ $bio }}</p>
                    @else
                        <p class="rf-pp-empty">لم يكتب نبذة بعد.</p>
                    @endif
                    @if ($user->specializations->isNotEmpty())
                        <h3>التخصصات</h3>
                        <ul class="rf-pp-skills">
                            @foreach ($user->specializations as $skill)
                                <li><a href="{{ route('freelancers.index', ['service' => $skill->id]) }}" title="مستقلون آخرون في {{ $skill->name }}">{{ $skill->name }}</a></li>
                            @endforeach
                        </ul>
                    @endif
                </section>

                {{-- Works --}}
                <section class="rf-pp-card" id="works" aria-labelledby="pp-works">
                    <h2 id="pp-works">نماذج من أعماله</h2>
                    @if ($works->isEmpty())
                        <p class="rf-pp-empty">لا توجد أعمال منشورة بعد.</p>
                    @else
                        <div class="rf-pp-works">
                            @foreach ($works as $work)
                                @php $count = 1 + (is_array($work->more_images) ? count($work->more_images) : 0); @endphp
                                <a class="rf-pp-work" href="{{ url('portfolio/' . $work->id . '-' . $work->slug) }}">
                                    <span class="rf-pp-work-img">
                                        <img src="{{ asset('assets/uploads/portfolios/' . $work->image) }}" alt="{{ $work->title }}" loading="lazy">
                                        @if ($count > 1)<span class="rf-pp-work-count"><i class="bi bi-images" aria-hidden="true"></i> {{ $count }}</span>@endif
                                    </span>
                                    <strong>{{ $work->title }}</strong>
                                    @if ($work->Category)<small>{{ $work->Category->name }}</small>@endif
                                </a>
                            @endforeach
                        </div>
                    @endif
                </section>

                {{-- Ratings --}}
                <section class="rf-pp-card" id="ratings" aria-labelledby="pp-ratings">
                    <h2 id="pp-ratings">تقييمات العملاء</h2>
                    @if ($ratings->isEmpty())
                        <p class="rf-pp-empty">لا توجد تقييمات بعد. يقيّم العملاء المستقل بعد اكتمال المشروع عبر نفّذها.</p>
                    @else
                        <div class="rf-pp-summary">
                            <div class="rf-pp-avg">
                                <strong>{{ $rating }}</strong>
                                @include('website.freelancers.partials.stars', ['value' => $user->rate])
                                <small>من {{ $ratingCount }} {{ $ratingCount === 1 ? 'تقييم' : 'تقييمات' }}</small>
                            </div>
                            <ul class="rf-pp-bars">
                                @php $total = max(1, $distribution->sum()); @endphp
                                @for ($s = 5; $s >= 1; $s--)
                                    <li><span>{{ $s }} <i class="bi bi-star-fill" aria-hidden="true"></i></span>
                                        <span class="rf-pp-bar"><span style="width: {{ round(($distribution[$s] ?? 0) * 100 / $total) }}%"></span></span>
                                        <span>{{ $distribution[$s] ?? 0 }}</span></li>
                                @endfor
                            </ul>
                        </div>
                        <ul class="rf-pp-reviews">
                            @foreach ($ratings as $r)
                                <li>
                                    <div class="rf-pp-review-head">
                                        <strong>{{ $raterName($r->rater) }}</strong>
                                        @include('website.freelancers.partials.stars', ['value' => $r->stars])
                                        <time datetime="{{ $r->created_at->toDateString() }}">{{ $r->created_at->translatedFormat('F Y') }}</time>
                                    </div>
                                    @if ($r->comment)<p>{{ $r->comment }}</p>@endif
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </section>

                {{-- Services --}}
                @if ($services->isNotEmpty())
                    <section class="rf-pp-card" id="services" aria-labelledby="pp-services">
                        <h2 id="pp-services">خدماته الجاهزة</h2>
                        <div class="rf-pp-works">
                            @foreach ($services as $serv)
                                <a class="rf-pp-work" href="{{ url('service/' . $serv->id . '-' . $serv->slug) }}">
                                    <span class="rf-pp-work-img"><img src="{{ asset('assets/uploads/services/' . $serv->image) }}" alt="{{ $serv->name }}" loading="lazy"></span>
                                    <strong>{{ $serv->name }}</strong>
                                    <small>{{ $serv->category?->name }}@if (is_numeric($serv->price)) · {{ \App\Support\Money::show($serv->price) }}@endif</small>
                                </a>
                            @endforeach
                        </div>
                    </section>
                @endif
            </div>

            <aside class="rf-pp-side">
                <div class="rf-pp-card rf-pp-cta">
                    <h2>عندك مشروع في {{ $firstService?->name ?? 'هذا المجال' }}؟</h2>
                    <p>اكتب طلبك مرة واحدة؛ يصل إلى {{ $user->name }} والمستقلين المعتمدين في نفس التخصص، وتختار العرض الأنسب لك.</p>
                    <a href="{{ $requestUrl }}" class="btn btn-primary w-100"><i class="bi bi-send" aria-hidden="true"></i> اطلب عرض سعر</a>
                    <a href="{{ route('projects.new') }}" class="btn btn-light w-100 mt-2">انشر مشروعك</a>
                    <ul class="rf-pp-promise">
                        <li><i class="bi bi-shield-check" aria-hidden="true"></i> يبقى المبلغ لدى نفّذها حتى تعتمد التسليم</li>
                        <li><i class="bi bi-patch-check" aria-hidden="true"></i> مستقلون راجع الفريق ملفاتهم</li>
                    </ul>
                </div>
                <div class="rf-pp-card rf-pp-facts">
                    <dl>
                        <div><dt>عضو في نفّذها منذ</dt><dd>{{ $since?->translatedFormat('F Y') }}</dd></div>
                        @if ($user->availability)<div><dt>التوفر</dt><dd>{{ User::AVAILABILITY[$user->availability] ?? '—' }}</dd></div>@endif
                        @if ($languages->isNotEmpty())<div><dt>اللغات</dt><dd>{{ $languages->implode('، ') }}</dd></div>@endif
                    </dl>
                    <button type="button" class="rf-pp-share" data-url="{{ url('user/' . $user->user_name) }}"><i class="bi bi-link-45deg" aria-hidden="true"></i> <span>نسخ رابط الملف</span></button>
                </div>
            </aside>
        </div>
    </div>
</section>
<script>
    (function () {
        var btn = document.querySelector('.rf-pp-share');
        if (!btn) return;
        btn.addEventListener('click', function () {
            var url = btn.getAttribute('data-url');
            var done = function () { btn.querySelector('span').textContent = 'تم نسخ الرابط'; setTimeout(function () { btn.querySelector('span').textContent = 'نسخ رابط الملف'; }, 2000); };
            if (navigator.share && window.matchMedia('(max-width: 767px)').matches) { navigator.share({ title: document.title, url: url }).catch(function () {}); return; }
            if (navigator.clipboard) navigator.clipboard.writeText(url).then(done, function () { window.prompt('انسخ الرابط', url); });
            else window.prompt('انسخ الرابط', url);
        });
    })();
</script>
@endsection
