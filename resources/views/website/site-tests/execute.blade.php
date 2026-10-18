@extends('website.layouts.master')
@php
    $service = $test->suggestedService;
    $missing = array_keys(array_filter($contact, fn ($value) => blank($value)));
    $labels = ['contact_name' => 'الاسم الكامل', 'contact_phone' => 'رقم التواصل', 'contact_email' => 'البريد الإلكتروني'];
@endphp
@section('title')
    تنفيذ {{ $service->name }}
@endsection
@section('content')
    <section class="rf-st-hero rf-st-hero-small" dir="rtl">
        <div class="container">
            <span class="rf-sv-label" dir="ltr">{{ $test->number }}</span>
            <h1>تنفيذ خدمة {{ $service->name }}</h1>
            <p>سننشر طلبك للمستقلين المختصين مع نتيجة الفحص، دون بيانات تواصلك.</p>
        </div>
    </section>
    <section class="rf-st" dir="rtl">
        <div class="container">
            <div class="rf-st-card">
                @if (! auth()->user()->isClient())
                    <p>طلب التنفيذ متاح لحسابات العملاء فقط. سجّل الدخول بحساب عميل لإكمال الطلب.</p>
                @else
                    <h2 class="rf-st-h2">{{ $missing ? 'نحتاج فقط البيانات الناقصة' : 'راجع بياناتك وانشر الطلب' }}</h2>
                    <form method="POST" action="{{ route('site-tests.publish', $test->number) }}" novalidate>
                        @csrf
                        <input type="hidden" name="t" value="{{ $test->token }}">
                        @foreach ($contact as $key => $value)
                            @if (in_array($key, $missing, true) || $errors->has($key))
                                <div class="mb-3">
                                    <label class="rf-st-label" for="rf-st-{{ $key }}">{{ $labels[$key] }} <span class="rf-st-tag">مطلوب</span></label>
                                    <input type="{{ $key === 'contact_email' ? 'email' : ($key === 'contact_phone' ? 'tel' : 'text') }}" id="rf-st-{{ $key }}" name="{{ $key }}" class="form-control"
                                        @if ($key !== 'contact_name') dir="ltr" @endif value="{{ old($key, $value) }}" maxlength="255">
                                    @error($key)<div class="rf-field-error">{{ $message }}</div>@enderror
                                </div>
                            @else
                                <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                                <p class="rf-st-note">{{ $labels[$key] }}: <span @if ($key !== 'contact_name') dir="ltr" @endif>{{ $value }}</span></p>
                            @endif
                        @endforeach
                        <p class="rf-st-note">بيانات التواصل تظهر لفريق نفّذها فقط، ولا تظهر للمستقلين.</p>
                        <button class="rf-sv-btn rf-sv-btn-primary">انشر طلب {{ $service->name }}</button>
                    </form>
                @endif
            </div>
        </div>
    </section>
@endsection
