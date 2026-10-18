@extends('website.layouts.master')
@section('title')
    محادثة العرض — {{ $offer->request->number }}
@endsection
@php
    $request = $offer->request;
    $closed = ! $offer->isActive() && $offer->status !== \App\Models\RequestOffer::STATUS_ACCEPTED;
@endphp
@section('content')
    <section class="gray-bg text-right rf-page" dir="rtl">
        <div class="container">
            <p class="mb-2">
                <a href="{{ $isClient ? route('requests.show', $request) . '#offers' : route('freelancer.requests.show', $request) }}" class="rf-muted">
                    <i class="bi bi-arrow-right"></i> {{ $isClient ? 'العودة إلى طلبك وعروضه' : 'العودة إلى الطلب' }}
                </a>
            </p>
            <div class="row">
                <div class="col-lg-4 mb-3 order-lg-2">
                    <div class="rf-card rf-card-flat">
                        <div class="rf-request-head">
                            <div>
                                <span class="rf-muted">{{ $request->number }} · {{ $request->service_name }}</span>
                                <h2 class="rf-section-title mb-0">
                                    {{ $isClient ? 'عرض ' . ($offer->freelancer->name ?? 'المستقل') : 'عرضك' }}
                                </h2>
                            </div>
                            <span class="rf-status rf-offer-{{ $offer->status }}">{{ $offer->statusLabel() }}</span>
                        </div>
                        <dl class="rf-answers">
                            <dt>السعر</dt><dd>{{ \App\Support\Money::show($offer->price) }}</dd>
                            <dt>مدة التنفيذ</dt><dd>{{ $offer->duration_days }} يوم</dd>
                        </dl>
                        <p class="rf-board-card-label mt-2">نطاق العمل والتسليمات</p>
                        <p class="rf-problem">{{ $offer->scope }}</p>
                        <p class="rf-board-card-label mt-2">رسالة العرض</p>
                        <p class="rf-problem">{{ $offer->message }}</p>
                        @if ($isClient && $offer->isActive() && $request->isOpen())
                            <div class="rf-offer-actions mb-2">
                                @include('website.offers.partials.accept-button', ['offer' => $offer])
                            </div>
                        @endif
                        @if ($offer->portfolio_links || $offer->files->isNotEmpty())
                            <p class="rf-board-card-label mt-2">نماذج أعمال</p>
                            <ul class="rf-files">
                                @foreach ($offer->portfolio_links ?? [] as $link)
                                    <li><a href="{{ $link }}" target="_blank" rel="noopener nofollow" dir="ltr">{{ \Illuminate\Support\Str::limit($link, 45) }}</a></li>
                                @endforeach
                                @foreach ($offer->files as $file)
                                    <li><a href="{{ route('offers.file', [$offer, $file]) }}"><i class="bi bi-paperclip"></i> {{ $file->original_name }}</a> <span class="rf-muted">{{ $file->humanSize() }}</span></li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                </div>

                <div class="col-lg-8 order-lg-1">
                    <div class="rf-card rf-card-flat rf-chat">
                        <h1 class="rf-section-title">المحادثة حول هذا العرض</h1>
                        <p class="rf-muted">الرسائل هنا مرتبطة بالطلب {{ $request->number }} وبهذا العرض فقط.
                            @unless ($isClient) لا تشارك بيانات تواصل خارج المنصة. @endunless
                        </p>

                        <div class="rf-messages" role="log" aria-label="الرسائل">
                            @forelse ($messages as $message)
                                @php($mine = (int) $message->sender_id === (int) Auth::id())
                                <div class="rf-message {{ $mine ? 'rf-message-mine' : '' }}" id="message-{{ $message->id }}">
                                    <span class="rf-message-author">
                                        {{ $mine ? 'أنت' : ((int) $message->sender_id === (int) $request->user_id ? 'العميل' : ($message->sender->name ?? 'المستقل')) }}
                                    </span>
                                    <p>{{ $message->body }}</p>
                                    <span class="rf-message-time">{{ $message->created_at->format('Y-m-d H:i') }}</span>
                                </div>
                            @empty
                                <p class="rf-placeholder">لا توجد رسائل بعد. {{ $isClient ? 'اسأل المستقل عن أي تفاصيل في عرضه.' : 'يمكنك توضيح عرضك للعميل هنا.' }}</p>
                            @endforelse
                        </div>

                        @if ($closed)
                            <div class="rf-notice">هذه المحادثة مغلقة لأن العرض {{ $offer->statusLabel() }}.</div>
                        @else
                            <form method="POST" action="{{ route('offers.messages.store', $offer) }}" class="rf-chat-form">
                                @csrf
                                <label for="body" class="visually-hidden">رسالتك</label>
                                <textarea id="body" name="body" rows="3" class="form-control" required maxlength="3000" placeholder="اكتب رسالتك…">{{ old('body') }}</textarea>
                                @error('body')<div class="rf-field-error">{{ $message }}</div>@enderror
                                <div class="rf-submit-actions mt-2">
                                    <button type="submit" class="btn rf-btn-primary">إرسال</button>
                                </div>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
