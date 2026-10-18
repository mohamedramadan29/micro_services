@extends('website.layouts.master')
@php
    $page = \App\Support\SiteContent::first('sitetest.page');
    $oldCategories = (array) old('categories', array_slice(array_keys($categories), 0, 2));
@endphp
@section('title')
    {{ $page['title'] }}
@endsection
@section('content')
    <section class="rf-st-hero" dir="rtl">
        <div class="container">
            @if ($page['badge'])<span class="rf-sv-label">{{ $page['badge'] }}</span>@endif
            <h1>{{ $page['title'] }}</h1>
            @if ($page['subtitle'])<p>{{ $page['subtitle'] }}</p>@endif
        </div>
    </section>

    <section class="rf-st" dir="rtl">
        <div class="container">
            <ol class="rf-st-steps">
                @foreach (['step1', 'step2', 'step3', 'step4'] as $i => $key)
                    @if ($page[$key])<li><span>{{ $i + 1 }}</span>{{ $page[$key] }}</li>@endif
                @endforeach
            </ol>

            <form method="POST" action="{{ route('site-tests.store') }}" class="rf-st-card" novalidate data-track-start="site_test">
                @csrf
                <label class="rf-st-label" for="rf-st-url">{{ $page['url_label'] }} <span class="rf-st-tag">مطلوب</span></label>
                <input type="url" id="rf-st-url" name="url" class="form-control rf-st-input" dir="ltr" required maxlength="2000"
                    placeholder="https://example.com" value="{{ old('url', request()->query('url')) }}">
                @error('url')<div class="rf-field-error">{{ $message }}</div>@enderror

                <fieldset class="rf-st-fieldset">
                    <legend class="rf-st-label">{{ $page['categories_title'] }}</legend>
                    <div class="rf-st-options">
                        @foreach ($categories as $key => $category)
                            <label class="rf-st-option">
                                <input type="checkbox" name="categories[]" value="{{ $key }}" @checked(in_array($key, $oldCategories, true))>
                                <span><strong>{{ $category['label'] }}</strong><small>{{ $category['hint'] }}</small></span>
                            </label>
                        @endforeach
                    </div>
                    @error('categories')<div class="rf-field-error">{{ $message }}</div>@enderror
                </fieldset>

                <fieldset class="rf-st-fieldset">
                    <legend class="rf-st-label">{{ $page['device_title'] }}</legend>
                    <div class="rf-st-options rf-st-options-inline">
                        @foreach ($devices as $key => $label)
                            <label class="rf-st-option">
                                <input type="radio" name="device" value="{{ $key }}" @checked(old('device', array_key_first($devices)) === $key)>
                                <span><strong>{{ $label }}</strong></span>
                            </label>
                        @endforeach
                    </div>
                </fieldset>

                @if ($page['note'])<p class="rf-st-note"><i class="bi bi-shield-lock"></i> {{ $page['note'] }}</p>@endif

                <div class="rf-st-actions">
                    <button type="submit" class="rf-sv-btn rf-sv-btn-primary">{{ $page['submit'] }}</button>
                    <button type="button" class="rf-sv-btn rf-sv-btn-outline" onclick="var e=document.getElementById('rf-st-explain'); e.hidden=!e.hidden;">{{ $page['explain_title'] }}</button>
                </div>
                <p id="rf-st-explain" class="rf-st-explain" hidden>{{ $page['explain_text'] }}</p>
            </form>
        </div>
    </section>
@endsection
