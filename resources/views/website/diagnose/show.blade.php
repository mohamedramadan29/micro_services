@extends('website.layouts.master')
@php
    // «شخّص مشروعك مجانًا»: texts from the content admin (area diagnose.page); groups/services from the catalog.
    $page = \App\Support\SiteContent::first('diagnose.page');
@endphp
@section('title')
    {{ $page['title'] }}
@endsection
@section('content')
    <section class="gray-bg text-right rf-page" dir="rtl">
        <div class="container">
            <div class="rf-card rf-dx">
                <div class="rf-card-header">
                    @if ($page['badge'])<span class="rf-badge">{{ $page['badge'] }}</span>@endif
                    <h1 class="rf-title">{{ $page['title'] }}</h1>
                    @if ($page['subtitle'])<p class="rf-subtitle">{{ $page['subtitle'] }}</p>@endif
                </div>

                @if ($errors->any())
                    <div class="rf-notice rf-notice-error" role="alert">{{ $errors->first() }}</div>
                @endif

                {{-- Step 1: a website to check, or help choosing a service. --}}
                <div class="rf-dx-step" data-step="site">
                    <h2 class="rf-section-title">{{ $page['site_question'] }}</h2>
                    <div class="rf-dx-choices">
                        <button type="button" class="rf-dx-choice" data-go="site-yes"><i class="bi bi-globe2"></i> {{ $page['site_yes'] }}</button>
                        <button type="button" class="rf-dx-choice" data-go="goal"><i class="bi bi-compass"></i> {{ $page['site_no'] }}</button>
                    </div>
                </div>

                <div class="rf-dx-step" data-step="site-yes" hidden>
                    <p>{{ $page['site_hint'] }}</p>
                    <form method="GET" action="{{ route('site-tests.index') }}" class="rf-dx-site">
                        <input type="url" name="url" class="form-control" dir="ltr" placeholder="https://example.com" required maxlength="2000">
                        <button class="btn rf-btn-primary">افحص موقعي مجانًا</button>
                    </form>
                    <button type="button" class="rf-btn-link mt-2" data-go="site">رجوع</button>
                </div>

                <form method="POST" action="{{ route('diagnose.store') }}" id="dx-form" data-track-start="diagnose">
                    @csrf
                    {{-- Step 2: the goal = a service group. --}}
                    <div class="rf-dx-step" data-step="goal" hidden>
                        <h2 class="rf-section-title">{{ $page['goal_question'] }}</h2>
                        <div class="rf-dx-grid">
                            @foreach ($groups as $group)
                                <label class="rf-dx-option">
                                    <input type="radio" name="group" value="{{ $group->id }}" @checked((string) old('group') === (string) $group->id)>
                                    <span>
                                        <i class="bi {{ $group->icon ?: 'bi-grid' }}"></i>
                                        <strong>{{ $group->name }}</strong>
                                        @if ($group->description)<small>{{ \Illuminate\Support\Str::limit($group->description, 90) }}</small>@endif
                                    </span>
                                </label>
                            @endforeach
                        </div>
                        <div class="rf-dx-nav">
                            <button type="button" class="rf-btn-link" data-go="site">رجوع</button>
                        </div>
                    </div>

                    {{-- Step 3: the closest service in that group, or «not sure». --}}
                    <div class="rf-dx-step" data-step="service" hidden>
                        <h2 class="rf-section-title">{{ $page['service_question'] }}</h2>
                        @foreach ($groups as $group)
                            <div class="rf-dx-grid rf-dx-services" data-group="{{ $group->id }}" hidden>
                                @foreach ($group->subCategories as $service)
                                    <label class="rf-dx-option rf-dx-option-sm">
                                        <input type="radio" name="service" value="{{ $service->id }}" @checked((string) old('service') === (string) $service->id)>
                                        <span><strong>{{ $service->name }}</strong>@if ($service->description)<small>{{ \Illuminate\Support\Str::limit($service->description, 80) }}</small>@endif</span>
                                    </label>
                                @endforeach
                            </div>
                        @endforeach
                        <label class="rf-dx-option rf-dx-option-sm rf-dx-unsure">
                            <input type="radio" name="service" value="" @checked(old('group') && ! old('service'))>
                            <span><strong>{{ $page['unsure_label'] }}</strong></span>
                        </label>
                        <div class="rf-dx-nav">
                            <button type="button" class="rf-btn-link" data-go="goal">رجوع</button>
                        </div>
                    </div>

                    {{-- Step 4: a few words and the timing, then the summary. --}}
                    <div class="rf-dx-step" data-step="describe" hidden>
                        <h2 class="rf-section-title">{{ $page['describe_question'] }}</h2>
                        <textarea name="description" rows="4" maxlength="2000" class="form-control" placeholder="مثال: عندي محل ملابس وعايز أبيع أونلاين">{{ old('description') }}</textarea>
                        <p class="rf-field-label mt-3 mb-1">متى تحتاج البدء؟</p>
                        <div class="rf-dx-chips">
                            @foreach ($urgency as $key => $label)
                                <label class="rf-dx-chip"><input type="radio" name="urgency" value="{{ $key }}" @checked(old('urgency') === $key)> <span>{{ $label }}</span></label>
                            @endforeach
                        </div>
                        <div class="rf-dx-nav">
                            <button type="button" class="rf-btn-link" data-go="service">رجوع</button>
                            <button type="submit" class="btn rf-btn-primary">اعرض ملخص احتياجي</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </section>
@endsection
@section('js')
    <script>
        (function () {
            var steps = document.querySelectorAll('.rf-dx-step');
            var show = function (name) {
                steps.forEach(function (s) { s.hidden = s.dataset.step !== name; });
                var current = document.querySelector('.rf-dx-step[data-step="' + name + '"]');
                if (current) current.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
            };
            document.querySelectorAll('[data-go]').forEach(function (b) { b.addEventListener('click', function () { show(b.dataset.go); }); });
            var form = document.getElementById('dx-form');
            var showServices = function (group) {
                document.querySelectorAll('.rf-dx-services').forEach(function (g) { g.hidden = g.dataset.group !== group; });
            };
            form.querySelectorAll('input[name=group]').forEach(function (r) {
                r.addEventListener('change', function () {
                    form.querySelectorAll('input[name=service]').forEach(function (s) { s.checked = false; });
                    showServices(r.value); show('service');
                });
            });
            form.querySelectorAll('input[name=service]').forEach(function (r) { r.addEventListener('change', function () { show('describe'); }); });
            // Back after a validation error: continue where the visitor was.
            var picked = form.querySelector('input[name=group]:checked');
            if (picked) { showServices(picked.value); show(form.querySelector('input[name=service]:checked') ? 'describe' : 'service'); }
        })();
    </script>
@endsection
