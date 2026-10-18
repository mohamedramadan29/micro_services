@extends('website.layouts.master')
@section('title')
    {{ $serviceRequest->number }} — {{ $serviceRequest->service_name }}
@endsection
@php
    use App\Models\RequestOffer;
    $approved = auth()->user()->isApprovedFreelancer();
    $canOffer = $approved && $serviceRequest->isOpen() && (! $offer || $offer->status === RequestOffer::STATUS_WITHDRAWN);
    $canEdit = $approved && $serviceRequest->isOpen() && $offer && $offer->isActive();
    $links = old('portfolio_links', $offer?->portfolio_links ?? []);
@endphp
@section('content')
    <section class="gray-bg text-right rf-page" dir="rtl">
        <div class="container">
            <p class="mb-2"><a href="{{ route('freelancer.requests.index') }}" class="rf-muted"><i class="bi bi-arrow-right"></i> كل طلبات العملاء</a></p>
            <div class="row">
                <div class="col-lg-7 mb-3">
                    <div class="rf-card rf-card-flat">
                        <div class="rf-request-head">
                            <div>
                                <span class="rf-muted">{{ $serviceRequest->category_name }} ← {{ $serviceRequest->service_name }} ← {{ $serviceRequest->number }}</span>
                                <h1 class="rf-request-title">{{ $serviceRequest->project_title ?: $serviceRequest->service_name }}</h1>
                                <span class="rf-muted">نُشر {{ $serviceRequest->published_at?->diffForHumans() }} · {{ $offersCount }} عرض
                                    @if ($serviceRequest->user?->client_rating_count)
                                        · تقييم المستقلين لهذا العميل <span class="rf-fl-stars">★ {{ rtrim(rtrim(number_format((float) $serviceRequest->user->client_rating, 1), '0'), '.') }}</span> ({{ $serviceRequest->user->client_rating_count }})
                                    @endif
                                </span>
                            </div>
                            <span class="rf-status rf-status-{{ $serviceRequest->status }}">{{ $serviceRequest->statusLabel() }}</span>
                        </div>
                        <div class="rf-notice"><i class="bi bi-shield-lock"></i> اسم العميل وهاتفه وبريده مخفية. تواصل معه من خلال محادثة عرضك.</div>
                        @include('website.service-requests.partials.request-details', ['fileRoute' => 'freelancer.requests.file'])
                    </div>
                </div>

                <div class="col-lg-5">
                    @if ($serviceRequest->status === \App\Models\ServiceRequest::STATUS_NOT_COMPLETED)
                        <div class="rf-notice mb-3">أُغلق هذا الطلب دون تنفيذ ({{ $serviceRequest->closeReasonLabel() }}).</div>
                    @elseif ($offer && $offer->status === RequestOffer::STATUS_ACCEPTED)
                        <div class="rf-card rf-card-flat mb-3">
                            <h2 class="rf-section-title">التنفيذ</h2>
                            @if ($serviceRequest->isStaged())
                                @include('website.service-requests.partials.staged-status', ['viewer' => 'freelancer'])
                            @else
                                @include('website.service-requests.partials.progress', ['viewer' => 'freelancer'])
                                @include('website.service-requests.partials.changes', ['viewer' => 'freelancer'])
                            @endif
                        </div>
                    @endif
                    @if ($offer && $offer->status !== RequestOffer::STATUS_WITHDRAWN)
                        <div class="rf-card rf-card-flat mb-3">
                            <div class="rf-request-head">
                                <h2 class="rf-section-title mb-0">عرضك</h2>
                                <span class="rf-status rf-offer-{{ $offer->status }}">{{ $offer->statusLabel() }}</span>
                            </div>
                            @if ($offer->isActive() && $offer->requestEditedSince())
                                <div class="rf-notice rf-notice-warning" role="status">
                                    <strong>الطلب عُدّل بعد عرضك</strong> ({{ $serviceRequest->edited_at->diffForHumans() }}). راجع التفاصيل أدناه وحدّث عرضك إن لزم.
                                </div>
                            @endif
                            <dl class="rf-answers">
                                <dt>السعر</dt><dd>{{ \App\Support\Money::show($offer->price) }}</dd>
                                <dt>مدة التنفيذ</dt><dd>{{ $offer->duration_days }} يوم</dd>
                            </dl>
                            <p class="rf-board-card-label mt-2">نطاق العمل والتسليمات</p>
                            <p class="rf-problem">{{ $offer->scope }}</p>
                            <div class="rf-offer-actions">
                                <a href="{{ route('offers.chat', $offer) }}" class="rf-link-btn"><i class="bi bi-chat-dots"></i> محادثة العرض</a>
                                @if ($canEdit)
                                    <button type="button" class="btn rf-btn-outline" data-bs-toggle="collapse" data-bs-target="#offer-form" aria-expanded="{{ $errors->any() ? 'true' : 'false' }}">تعديل العرض</button>
                                    <form method="POST" action="{{ route('freelancer.offers.withdraw', $offer) }}" onsubmit="return confirm('سحب عرضك من هذا الطلب؟')">
                                        @csrf
                                        <button type="submit" class="btn rf-btn-danger-text">سحب العرض</button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    @endif

                    @include('website.freelancer-requests.partials.approval-notice')
                    @if ($canOffer || $canEdit)
                        <div class="rf-card rf-card-flat rf-offer-form {{ $canEdit ? 'collapse' : '' }} {{ $canEdit && $errors->any() ? 'show' : '' }}" id="offer-form">
                            <h2 class="rf-section-title">{{ $canEdit ? 'تعديل عرضك' : 'نموذج عرض المستقل' }}</h2>
                            <p class="rf-muted">يُرسل ردًا على هذا الطلب تحديدًا: <strong>{{ $serviceRequest->service_name }} · {{ $serviceRequest->number }}</strong></p>

                            @if ($errors->any())
                                <div class="rf-notice rf-notice-error" role="alert">راجع الحقول المظللة.</div>
                            @endif

                            <form method="POST" action="{{ route('freelancer.offers.save', $serviceRequest) }}" enctype="multipart/form-data">
                                @csrf
                                <div class="row">
                                    <div class="col-6 rf-field">
                                        <label class="rf-field-label" for="price">السعر المقترح ({{ \App\Support\Money::label() }}) <span class="text-danger">*</span></label>
                                        <input type="number" step="0.01" min="1" id="price" name="price" class="form-control" required value="{{ old('price', $offer && $canEdit ? (float) $offer->price : '') }}">
                                        @error('price')<div class="rf-field-error">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="col-6 rf-field">
                                        <label class="rf-field-label" for="duration_days">مدة التنفيذ (أيام) <span class="text-danger">*</span></label>
                                        <input type="number" min="1" max="365" id="duration_days" name="duration_days" class="form-control" required placeholder="مثال: 7" value="{{ old('duration_days', $offer && $canEdit ? $offer->duration_days : '') }}">
                                        @error('duration_days')<div class="rf-field-error">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                                <div class="rf-field">
                                    <label class="rf-field-label" for="scope">نطاق العمل والتسليمات <span class="text-danger">*</span></label>
                                    <textarea id="scope" name="scope" rows="4" class="form-control" required minlength="20" maxlength="3000" placeholder="ما الذي ستنجزه وتسلّمه؟">{{ old('scope', $offer && $canEdit ? $offer->scope : '') }}</textarea>
                                    @error('scope')<div class="rf-field-error">{{ $message }}</div>@enderror
                                </div>
                                <div class="rf-field">
                                    <label class="rf-field-label" for="message">رسالة العرض <span class="text-danger">*</span></label>
                                    <textarea id="message" name="message" rows="4" class="form-control" required minlength="20" maxlength="3000" placeholder="اشرح خطتك للعميل">{{ old('message', $offer && $canEdit ? $offer->message : '') }}</textarea>
                                    @error('message')<div class="rf-field-error">{{ $message }}</div>@enderror
                                </div>
                                <div class="rf-field">
                                    <span class="rf-field-label">نماذج أعمال مرتبطة (اختياري)</span>
                                    @for ($i = 0; $i < RequestOffer::MAX_LINKS; $i++)
                                        <input type="url" name="portfolio_links[]" class="form-control mb-2" dir="ltr" placeholder="https://"
                                            aria-label="رابط عمل {{ $i + 1 }}" value="{{ $links[$i] ?? '' }}">
                                    @endfor
                                    @error('portfolio_links.*')<div class="rf-field-error">{{ $message }}</div>@enderror
                                    @if ($offer && $canEdit && $offer->files->isNotEmpty())
                                        <p class="rf-muted mb-1">الملفات الحالية (علّم ما تريد حذفه):</p>
                                        @foreach ($offer->files as $file)
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="remove_files[]" value="{{ $file->id }}" id="rm-{{ $file->id }}">
                                                <label class="form-check-label" for="rm-{{ $file->id }}">{{ $file->original_name }}</label>
                                            </div>
                                        @endforeach
                                    @endif
                                    <input type="file" name="portfolio_files[]" multiple class="form-control mt-2" aria-label="ملفات نماذج الأعمال"
                                        accept="{{ collect(RequestOffer::FILE_EXTENSIONS)->map(fn ($ext) => '.' . $ext)->implode(',') }}">
                                    <small class="rf-field-help">حتى {{ RequestOffer::MAX_FILES }} ملفات، {{ RequestOffer::FILE_MAX_MB }} ميجابايت لكل ملف</small>
                                    @error('portfolio_files')<div class="rf-field-error">{{ $message }}</div>@enderror
                                    @error('portfolio_files.*')<div class="rf-field-error">{{ $message }}</div>@enderror
                                </div>
                                <div class="rf-submit-actions">
                                    <button type="submit" class="btn rf-btn-primary">{{ $canEdit ? 'حفظ التعديلات' : 'إرسال العرض ضمن هذا الطلب' }}</button>
                                </div>
                            </form>
                        </div>
                    @elseif (! $offer)
                        <div class="rf-notice">هذا الطلب لم يعد يستقبل عروضًا.</div>
                    @endif
                </div>
            </div>
        </div>
    </section>
@endsection
