@extends('website.layouts.master')
@section('title')
    لوحتي
@endsection
@section('content')
    @use('App\Models\RequestOffer')
    @use('App\Models\ServiceRequest')
    @use('App\Models\User')
    @php
        $isFreelancer = $user->isFreelancer();
        // Approved/finished states wear the green stamp; everything else stays ink.
        $requestTone = ['open' => 'warn', 'inquiry' => '', 'in_progress' => 'info', 'completed' => 'stamp', 'not_completed' => 'bad'];
        $offerTone = ['new' => 'info', 'viewed' => '', 'in_discussion' => 'warn', 'accepted' => 'stamp', 'rejected' => 'bad', 'withdrawn' => ''];
        $steps = [
            'email' => ['تأكيد البريد الإلكتروني', url('update-account') . '#rf-freelancer'],
            'whatsapp' => ['رقم واتساب للتواصل', url('update-account') . '#rf-freelancer'],
            'photo' => ['صورتك الاحترافية (واضحة لوجهك)', url('update-account') . '#rf-freelancer'],
            'profile' => ['نبذة عنك', url('update-account') . '#rf-freelancer'],
            'specializations' => ['اختيار تخصصاتك', route('specializations.edit')],
            'portfolio' => ['إضافة عمل واحد على الأقل لمعرض أعمالك', route('user.portfolio.create')],
            'approved' => ['اعتماد الحساب من فريق نفّذها', null],
        ];
        $doneSteps = isset($checklist) ? count(array_filter($checklist)) : 0;
    @endphp

    <section class="gray-bg pt-4 text-right profile_page" dir="rtl">
        <div class="container-fluid">
            <div class="row m-0">
                <div class="col-xl-3 col-lg-4 col-md-12 col-sm-12">
                    @include('website.service-requests.partials.dashboard-nav')
                </div>

                <div class="col-xl-9 col-lg-8 col-md-12 col-sm-12 rf-acc">
                    {{-- The account greeting as the drawing's title block (balance = money kept for this account). --}}
                    <div class="rf-acc-hello nf-sheet nf-acc-head">
                        <div>
                            <h2>أهلًا {{ $user->name }}</h2>
                            <p>{{ $isFreelancer ? 'هنا تتابع طلبات العملاء وعروضك ومشاريعك.' : 'هنا تتابع طلباتك والعروض اللي وصلتك ومشاريعك.' }}</p>
                        </div>
                        <dl class="nf-acc-tb">
                            <dt>الحساب</dt><dd>{{ $user->typeLabel() }}</dd>
                            <dt>رصيدك</dt><dd class="num">{{ \App\Support\Money::show($user->balance ?? 0) }}</dd>
                            <dt></dt><dd><a href="{{ url('balance') }}">الرصيد والسحب ←</a></dd>
                        </dl>
                    </div>

                    @if ($isFreelancer)
                        {{-- Freelancer: get approved first --}}
                        @if ($user->freelancer_status === User::FREELANCER_REJECTED)
                            <div class="rf-acc-card rf-acc-alert bad">
                                <h3>لم يُعتمد حسابك بعد</h3>
                                @if ($user->freelancer_review_note)
                                    <p><strong>ملاحظة الفريق:</strong> {{ $user->freelancer_review_note }}</p>
                                @endif
                                <p>عدّل ملفك حسب الملاحظة واحفظه، وسيُراجع من جديد.</p>
                                <a class="rf-acc-btn" href="{{ url('update-account') . '#rf-freelancer' }}">تعديل ملفي</a>
                            </div>
                        @endif
                        @if (! $checklist['approved'])
                            <div class="rf-acc-card">
                                <h3>جهّز حسابك لتقديم العروض <span class="rf-acc-count">{{ $doneSteps }} / {{ count($steps) }}</span></h3>
                                <div class="rf-acc-progress"><span style="width: {{ round($doneSteps * 100 / count($steps)) }}%"></span></div>
                                <ul class="rf-acc-steps">
                                    @foreach ($steps as $key => [$label, $link])
                                        <li class="{{ $checklist[$key] ? 'done' : '' }}">
                                            <i class="bi {{ $checklist[$key] ? 'bi-check-circle-fill' : 'bi-circle' }}"></i>
                                            @if (! $checklist[$key] && $link)
                                                <a href="{{ $link }}">{{ $label }}</a>
                                            @else
                                                <span>{{ $label }}</span>
                                            @endif
                                            @if ($key === 'approved' && ! $checklist[$key])
                                                <small>{{ $user->freelancer_status === User::FREELANCER_REJECTED ? '' : 'يراجع الفريق ملفك وصورتك بعد اكتمال الخطوات' }}</small>
                                            @endif
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        @php
                            $statLine = [
                                [$stats['matching'], 'طلبات في تخصصاتك لم تقدّم عليها', route('freelancer.requests.index')],
                                [$stats['active_offers'], 'عروض قيد المتابعة', route('freelancer.offers.index')],
                                [$stats['won'], 'عروض تم اختيارها', route('freelancer.offers.index')],
                                [$stats['contracts'], 'مشاريع بمراحل جارية', route('contracts.index')],
                                [$user->rating_count ? number_format((float) $user->rate, 1) : '—', 'تقييمك (' . (int) $user->rating_count . ')', null],
                            ];
                        @endphp
                        @include('website.user.partials.status-line', ['items' => $statLine])
                    @else
                        <div class="rf-acc-actions">
                            <a href="{{ route('requests.create') }}"><i class="bi bi-ui-checks"></i><strong>اطلب خدمة</strong><span>أسئلة بسيطة توصلك لعروض مناسبة</span></a>
                            <a href="{{ route('projects.new') }}"><i class="bi bi-megaphone-fill"></i><strong>انشر مشروعك</strong><span>اكتب فكرتك واستقبل العروض</span></a>
                            <a href="{{ route('requests.staged') }}"><i class="bi bi-kanban-fill"></i><strong>مشروع بمراحل</strong><span>تدفع على مراحل وتعتمد كل مرحلة</span></a>
                            <a href="{{ route('site-tests.index') }}"><i class="bi bi-speedometer2"></i><strong>اختبر موقعك مجانًا</strong><span>تقرير عن سرعة موقعك ومشاكله</span></a>
                        </div>

                        @include('website.user.partials.status-line', ['items' => [
                            [$stats['new_offers'], 'عروض جديدة لم تفتحها', route('requests.index')],
                            [$stats['open'], 'طلبات تستقبل العروض', route('requests.index')],
                            [$stats['in_progress'], 'قيد التنفيذ', route('requests.index')],
                            [$stats['completed'], 'تم تنفيذها', route('requests.index')],
                        ]])
                    @endif

                    {{-- Staged projects: whose turn it is --}}
                    @if ($contracts->isNotEmpty())
                        <div class="rf-acc-card">
                            <h3>مشاريعي الجارية <a href="{{ route('contracts.index') }}">كل المشاريع</a></h3>
                            <ul class="rf-acc-list">
                                @foreach ($contracts as $contract)
                                    @php
                                        $next = $contract->nextStep($user);
                                    @endphp
                                    <li>
                                        <a href="{{ route('contracts.show', $contract) }}"><strong>{{ $contract->title }}</strong></a>
                                        <small>{{ $contract->number }} · مع {{ (int) $user->id === (int) $contract->client_id ? ($contract->freelancer?->name ?? '—') : ($contract->client?->name ?? '—') }}</small>
                                        @if ($next)
                                            <span class="rf-acc-pill {{ $next[1] ? 'warn' : '' }}">{{ $next[1] ? 'دورك: ' : '' }}{{ $next[0] }}</span>
                                        @endif
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @if ($isFreelancer)
                        <div class="rf-acc-card">
                            <h3>آخر عروضي <a href="{{ route('freelancer.offers.index') }}">كل العروض</a></h3>
                            @forelse ($latestOffers as $offer)
                                <div class="rf-acc-row">
                                    <div>
                                        @if ($offer->request)
                                            <a href="{{ route('freelancer.requests.show', $offer->request) }}"><strong>{{ $offer->request->kind === ServiceRequest::KIND_STAGED ? ($offer->request->project_title ?: 'مشروع بمراحل') : $offer->request->service_name }}</strong></a>
                                            <small>{{ $offer->request->number }} · {{ $offer->created_at?->diffForHumans() }}</small>
                                        @endif
                                    </div>
                                    <span class="rf-acc-num">{{ \App\Support\Money::show($offer->price) }}</span>
                                    <span class="rf-acc-pill {{ $offerTone[$offer->status] ?? '' }}">{{ RequestOffer::STATUSES[$offer->status] ?? $offer->status }}</span>
                                </div>
                            @empty
                                <p class="rf-acc-empty">لم تقدّم أي عرض بعد. <a href="{{ route('freelancer.requests.index') }}">تصفّح طلبات العملاء</a></p>
                            @endforelse
                        </div>
                    @else
                        <div class="rf-acc-card">
                            <h3>آخر طلباتي <a href="{{ route('requests.index') }}">كل الطلبات</a></h3>
                            @forelse ($latestRequests as $r)
                                <div class="rf-acc-row">
                                    <div>
                                        <a href="{{ route('requests.show', $r) }}"><strong>{{ $r->kind === ServiceRequest::KIND_STAGED ? ($r->project_title ?: 'مشروع بمراحل') : $r->service_name }}</strong></a>
                                        <small>{{ $r->number }} · {{ $r->created_at?->diffForHumans() }}</small>
                                    </div>
                                    <span class="rf-acc-num">{{ $r->offers_count }} {{ $r->offers_count == 1 ? 'عرض' : 'عروض' }}</span>
                                    <span class="rf-acc-pill {{ $requestTone[$r->status] ?? '' }}">{{ ServiceRequest::STATUSES[$r->status] ?? $r->status }}</span>
                                </div>
                            @empty
                                <p class="rf-acc-empty">لم ترسل أي طلب بعد. <a href="{{ route('requests.create') }}">اطلب أول خدمة</a> أو <a href="{{ route('services.index') }}">تصفّح الخدمات</a>.</p>
                            @endforelse
                        </div>
                    @endif

                    @if ($services->isNotEmpty())
                        <div class="rf-acc-card">
                            <h3>خدماتي المعروضة <a href="{{ url('service/index') }}">إدارة الخدمات</a></h3>
                            <div class="rf-acc-services">
                                @foreach ($services as $serv)
                                    <a href="{{ url('service/' . $serv->id . '-' . $serv->slug) }}">
                                        <img src="{{ asset('assets/uploads/services/' . $serv->image) }}" alt="" loading="lazy">
                                        <span>{{ $serv->name }}</span>
                                        <small>{{ $serv->category?->name }}</small>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <div class="rf-acc-card rf-acc-tips">
                        <h3>{{ $isFreelancer ? 'قواعد العمل على نفّذها' : 'نصائح للحصول على أفضل نتيجة' }}</h3>
                        <ul>
                            @if ($isFreelancer)
                                <li>اكتب عرضك لكل طلب على حدة: ماذا ستنفّذ، وفي كم يوم، وما المطلوب من العميل.</li>
                                <li>كل الدفع والتواصل يتم داخل نفّذها؛ المبلغ يبقى لدى نفّذها حتى يعتمد العميل العمل، ثم يُضاف لرصيدك.</li>
                                <li>عمولة المنصة {{ rtrim(rtrim(number_format($commission, 2), '0'), '.') }}% تُخصم من قيمة العمل عند صرفه لك.</li>
                                <li>سلّم في الموعد، وإن احتجت وقتًا اطلب تعليق المرحلة أو تواصل مع العميل قبل انتهاء المهلة.</li>
                            @else
                                <li>اكتب تفاصيل كافية في الطلب (الهدف، الأمثلة، الميزانية) لتصلك عروض أدق.</li>
                                <li>ادفع دائمًا من خلال نفّذها: المبلغ يبقى لدى نفّذها حتى تعتمد العمل.</li>
                                <li>تواصل مع المستقلين من محادثة العرض، ولا تشارك بيانات الدفع خارج الموقع.</li>
                                <li>مش عارف تختار؟ <a href="{{ route('services.index') }}">اطلب مساعدة «ساعدوني أختار»</a> وفريقنا يتواصل معك.</li>
                            @endif
                        </ul>
                        @if ($user->info)
                            <p class="rf-acc-bio"><strong>نبذة عنك:</strong> {{ $user->info }} · <a href="{{ url('update-account') }}">تعديل</a></p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
