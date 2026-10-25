@extends('website.layouts.master')
@php
    // «انضم كمستقل»: texts from the content admin (areas join.page, join.benefits).
    $page = \App\Support\SiteContent::first('join.page');
    $benefits = \App\Support\SiteContent::items('join.benefits');
    $steps = [
        'email' => ['تأكيد بريدك الإلكتروني', url('update-account') . '#rf-freelancer'],
        'whatsapp' => ['رقم واتساب للتواصل مع الفريق', url('update-account') . '#rf-freelancer'],
        'photo' => ['صورتك الاحترافية (واضحة لوجهك)', url('update-account') . '#rf-freelancer'],
        'profile' => ['نبذة عنك', url('update-account') . '#rf-freelancer'],
        'specializations' => ['اختيار تخصصاتك (الخدمات التي تقدّمها)', route('specializations.edit')],
        'portfolio' => ['إضافة نموذج عمل واحد على الأقل', url('portfolio/user/index')],
        'approved' => ['مراجعة فريق نفّذها واعتماد حسابك', null],
    ];
@endphp
@section('title')
    {{ $page['title'] }}
@endsection
@section('content')
    <section class="gray-bg text-right rf-page" dir="rtl">
        <div class="container">
            <div class="rf-card rf-join">
                <div class="rf-card-header">
                    @if ($page['badge'])<span class="rf-badge">{{ $page['badge'] }}</span>@endif
                    <h1 class="rf-title">{{ $page['title'] }}</h1>
                    @if ($page['subtitle'])<p class="rf-subtitle">{{ $page['subtitle'] }}</p>@endif
                </div>

                @if ($benefits->isNotEmpty())
                    <div class="rf-join-benefits">
                        @foreach ($benefits as $benefit)
                            <div class="rf-join-benefit">
                                @if ($benefit['icon'])<i class="bi {{ $benefit['icon'] }}"></i>@endif
                                <strong>{{ $benefit['title'] }}</strong>
                                @if ($benefit['text'])<span>{{ $benefit['text'] }}</span>@endif
                            </div>
                        @endforeach
                    </div>
                @endif

                @if (! $user)
                    <div class="rf-join-actions">
                        <a href="{{ url('register') }}?type=freelancer" class="btn rf-btn-primary">{{ $page['register_button'] }}</a>
                        @if ($page['login_button'])<a href="{{ route('user_login') }}" class="btn rf-btn-outline">{{ $page['login_button'] }}</a>@endif
                    </div>
                @elseif (! $user->account_type)
                    <div class="rf-notice">اختر نوع حسابك أولًا. <a href="{{ route('account.type') }}">اختيار نوع الحساب</a></div>
                @elseif (! $user->isFreelancer())
                    <div class="rf-notice">حسابك الحالي مسجّل كعميل. للانضمام كمستقل أنشئ حسابًا مستقلًا ببريد آخر، أو تواصل مع فريق نفّذها.</div>
                @endif

                @if ($page['steps_title'])<h2 class="rf-section-title mt-4">{{ $page['steps_title'] }}</h2>@endif
                <ol class="rf-join-steps">
                    @if (! $checklist)
                        <li class="{{ $user?->isFreelancer() ? 'done' : '' }}"><span>إنشاء حسابك كمستقل</span></li>
                    @endif
                    @foreach ($steps as $key => [$label, $link])
                        @php $done = $checklist[$key] ?? false; @endphp
                        <li class="{{ $done ? 'done' : '' }}">
                            <span>{{ $label }}</span>
                            @if ($checklist && ! $done && $link)<a href="{{ $link }}" class="rf-btn-link">أكمل الآن</a>@endif
                            @if ($checklist && $key === 'approved' && ! $done)
                                <small class="rf-muted d-block">{{ $user->freelancer_status === \App\Models\User::FREELANCER_REJECTED ? 'لم يُقبل ملفك: ' . $user->freelancer_review_note . ' — عدّل ملفك واحفظه لإعادة المراجعة.' : 'بانتظار المراجعة بعد إكمال الخطوات السابقة.' }}</small>
                            @endif
                        </li>
                    @endforeach
                </ol>
                @if ($page['review_note'])<p class="rf-muted">{{ $page['review_note'] }}</p>@endif

                @if ($checklist && $checklist['approved'])
                    <div class="rf-notice rf-notice-success">ملفك معتمد. <a href="{{ route('freelancer.requests.index') }}">تصفح طلبات العملاء</a> وقدّم عروضك.</div>
                @endif
            </div>
        </div>
    </section>
@endsection
