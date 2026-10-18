@extends('website.layouts.master')
@section('title')
    {{ $serviceRequest->number }} — {{ $serviceRequest->service_name }}
@endsection
@section('content')
    <section class="gray-bg pt-4 text-right profile_page" dir="rtl">
        <div class="container-fluid">
            <div class="row m-0">
                <div class="col-xl-3 col-lg-4 col-md-12 col-sm-12">
                    @include('website.service-requests.partials.dashboard-nav')
                </div>

                <div class="col-xl-9 col-lg-8 col-md-12 col-sm-12">
                    <div class="bredcrumb_wrap">
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="{{ url('/') }}"> الرئيسية </a></li>
                                <li class="breadcrumb-item"><a href="{{ route('requests.index') }}"> طلباتي </a></li>
                                <li class="breadcrumb-item active" aria-current="page"> {{ $serviceRequest->number }} </li>
                            </ol>
                        </nav>
                    </div>

                    {{-- Summary --}}
                    <div class="_dashboard_content">
                        <div class="_dashboard_content_body">
                            <div class="rf-request-head">
                                <div>
                                    <span class="rf-muted">{{ $serviceRequest->category_name }} ← {{ $serviceRequest->service_name }}</span>
                                    <h1 class="rf-request-title">
                                        @if ($serviceRequest->isStaged())<span class="rf-tag-badge rf-tag-staged">مشروع بمراحل</span>@endif
                                        {{ $serviceRequest->project_title ?: $serviceRequest->service_name }}
                                    </h1>
                                    <span class="rf-muted">{{ $serviceRequest->number }} · أُرسل {{ $serviceRequest->created_at->format('Y-m-d H:i') }}</span>
                                </div>
                                <span class="rf-status rf-status-{{ $serviceRequest->status }}">{{ $serviceRequest->statusLabel() }}</span>
                            </div>
                            @include('website.service-requests.partials.timeline')

                            @if ($serviceRequest->isInquiry())
                                <div class="rf-notice">
                                    <p class="mb-2"><strong>استفسار أسعار خاص.</strong> طلبك محفوظ لدى فريقنا فقط، ولا يظهر للمستقلين. سنتواصل معك بالأسعار.</p>
                                    <form method="POST" action="{{ route('requests.publish', $serviceRequest) }}"
                                        onsubmit="return confirm('سيُنشر طلبك للمستقلين المختصين بنفس البيانات (دون بيانات تواصلك). متابعة؟')">
                                        @csrf
                                        <button type="submit" class="btn rf-link-btn">قررت التنفيذ: انشر طلبي لاستقبال العروض</button>
                                    </form>
                                </div>
                            @elseif ($serviceRequest->status === \App\Models\ServiceRequest::STATUS_NOT_COMPLETED)
                                <div class="rf-notice">
                                    <strong>أُغلق هذا الطلب دون تنفيذ</strong> ({{ $serviceRequest->closeReasonLabel() }}) {{ $serviceRequest->closed_at?->format('Y-m-d') }}.
                                    @if ($serviceRequest->payments()->where('status', \App\Models\RequestPayment::STATUS_REFUNDED)->exists())
                                        تم استرجاع المبلغ المدفوع إلى وسيلة الدفع.
                                    @endif
                                </div>
                            @elseif ($serviceRequest->isInProgress() || $serviceRequest->status === \App\Models\ServiceRequest::STATUS_COMPLETED)
                                @if ($serviceRequest->isStaged())
                                    @include('website.service-requests.partials.staged-status', ['viewer' => 'client'])
                                @else
                                    @include('website.service-requests.partials.progress', ['viewer' => 'client'])
                                    @include('website.service-requests.partials.changes', ['viewer' => 'client'])
                                @endif
                            @else
                                <div class="rf-notice rf-notice-success">
                                    <strong>طلبك منشور للمستقلين المختصين</strong> ({{ $serviceRequest->published_at?->diffForHumans() }}).
                                    ستظهر العروض هنا لتقارن بينها. بيانات تواصلك لا تظهر لهم.
                                </div>
                            @endif
                        </div>
                    </div>

                    @if (in_array($serviceRequest->status, [\App\Models\ServiceRequest::STATUS_OPEN, \App\Models\ServiceRequest::STATUS_INQUIRY], true) && (int) $serviceRequest->user_id === (int) Auth::id())
                        <div class="rf-offer-actions mb-2">
                            <a href="{{ route('requests.edit', $serviceRequest) }}" class="rf-link-btn"><i class="bi bi-pencil-square"></i> تعديل الطلب</a>
                        </div>
                        <details class="rf-cancel">
                            <summary>لا أحتاج هذا الطلب: إلغاء الطلب</summary>
                            <form method="POST" action="{{ route('requests.cancel', $serviceRequest) }}" onsubmit="return confirm(@js('إلغاء الطلب ' . ($serviceRequest->number) . '؟ ستُغلق كل العروض عليه.'))">
                                @csrf
                                <label class="rf-field-label" for="cancel-note">سبب الإلغاء (اختياري)</label>
                                <textarea id="cancel-note" name="note" rows="2" maxlength="1000" class="form-control"></textarea>
                                <div class="rf-offer-actions"><button type="submit" class="btn rf-btn-danger-text">تأكيد إلغاء الطلب</button></div>
                            </form>
                        </details>
                    @endif

                    @if ((int) $serviceRequest->user_id === (int) Auth::id())
                        @include('website.service-requests.partials.team-messages')
                    @endif

                    {{-- Offers (published requests only) --}}
                    @unless ($serviceRequest->isInquiry())
                        <div class="_dashboard_content" id="offers">
                            <div class="_dashboard_content_header">
                                <div class="_dashboard__header_flex">
                                    <h4><i class="bi bi-people mr-1"></i> عروض المستقلين <span class="rf-muted">({{ $tabs['all']->count() }})</span></h4>
                                </div>
                            </div>
                            <div class="_dashboard_content_body">
                                @if ($tabs['all']->isEmpty())
                                    <p class="rf-placeholder">لم تصل عروض بعد. سنرسل لك تنبيهًا عند وصول أول عرض.</p>
                                @else
                                    <div class="rf-offer-toolbar">
                                        <nav class="rf-tabs" aria-label="تصنيف العروض">
                                            @foreach (['all' => 'الكل', 'new' => 'جديدة', 'saved' => 'محفوظة', 'discussion' => 'قيد النقاش', 'selected' => 'تم الاختيار', 'rejected' => 'لم يتم اختيارها'] as $key => $label)
                                                <a href="{{ route('requests.show', [$serviceRequest, 'tab' => $key, 'sort' => $sort]) }}#offers"
                                                    class="rf-tab {{ $tab === $key ? 'active' : '' }}" @if ($tab === $key) aria-current="page" @endif>
                                                    {{ $label }} {{ $tabs[$key]->count() }}
                                                </a>
                                            @endforeach
                                        </nav>
                                        <form method="GET" action="{{ route('requests.show', $serviceRequest) }}#offers" class="rf-sort">
                                            <input type="hidden" name="tab" value="{{ $tab }}">
                                            <label for="offer-sort" class="rf-muted">ترتيب حسب</label>
                                            <select id="offer-sort" name="sort" class="form-control" onchange="this.form.submit()">
                                                <option value="best" @selected($sort === 'best')>أفضل تطابق</option>
                                                <option value="price" @selected($sort === 'price')>أقل سعر</option>
                                                <option value="fastest" @selected($sort === 'fastest')>الأسرع</option>
                                                <option value="newest" @selected($sort === 'newest')>الأحدث</option>
                                            </select>
                                            <noscript><button type="submit" class="btn rf-btn-outline">ترتيب</button></noscript>
                                        </form>
                                    </div>

                                    @forelse ($offers as $index => $offer)
                                        <article class="rf-offer-card {{ $index === 0 && $sort === 'best' ? 'rf-offer-best' : '' }}">
                                            <div class="rf-offer-card-head">
                                                <div>
                                                    @if ($index === 0 && $offers->count() > 1)
                                                        <span class="rf-tag-badge rf-tag-new">{{ ['best' => 'أفضل تطابق', 'price' => 'أقل سعر', 'fastest' => 'الأسرع', 'newest' => 'الأحدث'][$sort] }}</span>
                                                    @endif
                                                    @if (in_array($offer->id, $newOfferIds))
                                                        <span class="rf-tag-badge">جديد</span>
                                                    @endif
                                                    @if ($offer->is_saved)
                                                        <span class="rf-tag-badge"><i class="bi bi-bookmark-fill"></i> محفوظ</span>
                                                    @endif
                                                    <div class="rf-offer-person">
                                                        <img src="{{ $offer->freelancer->avatarUrl(128) }}" alt="" class="rf-offer-avatar" loading="lazy">
                                                        <div>
                                                            <h5 class="rf-offer-name">
                                                                <a href="{{ url('user/' . $offer->freelancer->user_name) }}" target="_blank">{{ $offer->freelancer->name }}</a>
                                                            </h5>
                                                            <span class="rf-muted">{{ $offer->freelancer->job_title ?: 'متخصص بالخدمة المطلوبة' }}</span>
                                                            <span class="rf-offer-rating">
                                                                @if ((int) $offer->freelancer->rating_count > 0)
                                                                    <i class="bi bi-star-fill"></i> {{ number_format((float) $offer->freelancer->rate, 1) }}
                                                                    <span class="rf-muted">({{ $offer->freelancer->rating_count }} تقييم)</span>
                                                                @else
                                                                    <span class="rf-muted">مستقل جديد — لا تقييمات بعد</span>
                                                                @endif
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <span class="rf-status rf-offer-{{ $offer->status }}">{{ $offer->statusLabel() }}</span>
                                            </div>
                                            <div class="rf-offer-numbers">
                                                <span>السعر <strong>{{ \App\Support\Money::show($offer->price) }}</strong></span>
                                                <span>التسليم <strong>{{ $offer->duration_days }} يوم</strong></span>
                                            </div>
                                            <p class="rf-request-snippet">{{ \Illuminate\Support\Str::limit($offer->scope, 220) }}</p>
                                            <div class="rf-offer-actions">
                                                <a href="{{ route('offers.chat', $offer) }}" class="rf-link-btn">
                                                    <i class="bi bi-chat-dots"></i> التفاصيل والمحادثة
                                                    @if ($offer->unread_count)
                                                        <span class="rf-unread">{{ $offer->unread_count }}</span>
                                                    @endif
                                                </a>
                                                @if ($offer->isActive() && $serviceRequest->isOpen())
                                                    @include('website.offers.partials.accept-button', ['offer' => $offer])
                                                @endif
                                                @if ($offer->isActive())
                                                    <form method="POST" action="{{ route('offers.save', $offer) }}">
                                                        @csrf
                                                        <button type="submit" class="btn rf-btn-outline">
                                                            <i class="bi {{ $offer->is_saved ? 'bi-bookmark-x' : 'bi-bookmark' }}"></i>
                                                            {{ $offer->is_saved ? 'إزالة من المحفوظة' : 'حفظ للمقارنة' }}
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        </article>
                                    @empty
                                        <p class="rf-placeholder">لا توجد عروض في هذا التصنيف.</p>
                                    @endforelse
                                @endif
                            </div>
                        </div>
                    @endunless

                    {{-- Answers --}}
                    <div class="_dashboard_content">
                        <div class="_dashboard_content_header">
                            <div class="_dashboard__header_flex"><h4><i class="bi bi-list-check mr-1"></i> تفاصيل الطلب </h4></div>
                        </div>
                        <div class="_dashboard_content_body">
                            @include('website.service-requests.partials.request-details', ['fileRoute' => 'requests.file'])
                        </div>
                    </div>

                    {{-- Contact (owner only; never shown to freelancers) --}}
                    <div class="_dashboard_content">
                        <div class="_dashboard_content_header">
                            <div class="_dashboard__header_flex"><h4><i class="bi bi-person-lines-fill mr-1"></i> بيانات التواصل </h4></div>
                        </div>
                        <div class="_dashboard_content_body">
                            <dl class="rf-answers">
                                <dt>الاسم</dt><dd>{{ $serviceRequest->contact_name }}</dd>
                                <dt>رقم التواصل</dt><dd><span dir="ltr">{{ $serviceRequest->contact_phone }}</span></dd>
                                <dt>البريد الإلكتروني</dt><dd><span dir="ltr">{{ $serviceRequest->contact_email }}</span></dd>
                            </dl>
                            <p class="rf-muted mb-0"><i class="bi bi-shield-lock"></i> هذه البيانات تظهر لك ولفريق نفّذها فقط.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
@section('js')
    @if (session('request_created'))
        {{-- The request was sent: drop the draft the request page kept in this browser. --}}
        <script>
            try {
                // The request (guided, staged or direct) is sent: its drafts are done.
                ['rf-draft:', 'rf-draft-staged:', 'rf-project-draft:'].forEach(function (prefix) { localStorage.removeItem(prefix + @json((string) Auth::id())); });
                localStorage.removeItem('rf-project-draft:guest');
            } catch (e) { /* storage unavailable */ }
        </script>
    @endif
@endsection
