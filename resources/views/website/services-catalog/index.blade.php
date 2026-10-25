@extends('website.layouts.master')
@php
    use App\Support\SiteContent;
    $page = SiteContent::first('services.page');
    $choice = SiteContent::first('services.choice');
    $help = SiteContent::first('services.help');
    $heroBackground = SiteContent::media($page['hero_background']);
    // «خدمة واحدة»، «خدمتان»، «4 خدمات»، «11 خدمة»
    $countLabel = fn (int $n) => match (true) {
        $n === 1 => 'خدمة واحدة',
        $n === 2 => 'خدمتان',
        $n >= 3 && $n <= 10 => $n . ' خدمات',
        default => $n . ' خدمة',
    };
    $catalog = $groups->flatMap(fn ($group) => $group->subCategories->map(fn ($service) => [
        'id' => $service->id,
        'name' => $service->name,
        'url' => $service->requestUrl(isset($withForm[$service->id])),
        'button' => $service->button_text ?: $choice['request_button'],
        // Freelancers' published ready-made services in this sub-service (third choice in the dialog).
        'ready' => (int) ($readyCounts[$service->id] ?? 0),
        'ready_url' => url('services/' . $service->slug),
    ]))->keyBy('id');
    $readyPage = SiteContent::first('ready.page');
    $openHelp = auth()->check() && (request()->boolean('help') || ($errors->any() && old('_help')));
@endphp
@section('title')
    {{ $page['hero_title'] }}
@endsection
@section('content')
    <section class="rf-sv-hero" dir="rtl" @if ($heroBackground) style="background-image: url({{ $heroBackground->url() }})" @endif>
        <div class="container">
            @if ($page['hero_label'])<span class="rf-sv-label">{{ $page['hero_label'] }}</span>@endif
            <h1>{{ $page['hero_title'] }}</h1>
            @if ($page['hero_text'])<p>{{ $page['hero_text'] }}</p>@endif
        </div>
    </section>

    <section class="rf-sv" dir="rtl">
        <div class="container">
            @if (session('help_request_created'))
                <div class="rf-sv-done" role="status">
                    <strong>{{ $help['confirmation'] }}</strong>
                    <div>{{ $help['number_label'] }} <span dir="ltr">{{ session('help_request_created') }}</span> · {{ session('help_request_service') }}</div>
                    @if ($help['team_whatsapp'])
                        <a class="rf-sv-btn rf-sv-btn-outline" target="_blank" rel="noopener"
                            href="https://wa.me/{{ preg_replace('/\D/', '', $help['team_whatsapp']) }}?text={{ rawurlencode(str_replace(':number', session('help_request_created'), $help['whatsapp_message'] ?: 'مرحبًا، أرسلت طلب مساعدة رقم :number على موقع نفّذها')) }}">
                            <i class="bi bi-whatsapp"></i> {{ $help['whatsapp_button'] }}
                        </a>
                    @endif
                </div>
            @endif

            <div class="rf-sv-head">
                <div>
                    @if ($page['section_title'])<h2>{{ $page['section_title'] }}</h2>@endif
                    @if ($page['section_text'])<p>{{ $page['section_text'] }}</p>@endif
                </div>
                <div>
                    <div class="rf-sv-search">
                        <i class="bi bi-search" aria-hidden="true"></i>
                        <input type="search" id="rf-sv-search" placeholder="{{ $page['search_placeholder'] }}" aria-label="{{ $page['search_placeholder'] }}" autocomplete="off">
                    </div>
                    @if ($readyCounts->sum() > 0)
                        <a href="{{ route('services.ready') }}" class="rf-sv-ready-link"><i class="bi bi-bag-check" aria-hidden="true"></i> {{ $readyPage['catalog_link'] }} ({{ $readyCounts->sum() }})</a>
                    @endif
                </div>
            </div>

            <div class="rf-sv-groups" id="rf-sv-groups">
                @foreach ($groups as $group)
                    <div class="rf-sv-group" data-group>
                        <button type="button" class="rf-sv-group-head" aria-expanded="false" aria-controls="rf-sv-list-{{ $group->id }}">
                            <span class="rf-sv-group-icon" aria-hidden="true">
                                @if ($group->image && file_exists(public_path('assets/uploads/service_category/' . $group->image)))
                                    <img src="{{ asset('assets/uploads/service_category/' . $group->image) }}" alt="">
                                @else
                                    <i class="bi {{ $group->icon ?: 'bi-grid' }}"></i>
                                @endif
                            </span>
                            <span class="rf-sv-group-text">
                                <strong>{{ $group->name }}</strong>
                                @if ($group->description)<span>{{ $group->description }}</span>@endif
                            </span>
                            <span class="rf-sv-count">{{ $countLabel($group->subCategories->count()) }}</span>
                            @if ($preview && ! $group->status)<span class="rf-sv-hidden">مخفي</span>@endif
                            <i class="bi bi-chevron-down rf-sv-chevron" aria-hidden="true"></i>
                        </button>
                        <div class="rf-sv-list" id="rf-sv-list-{{ $group->id }}" hidden>
                            @foreach ($group->subCategories as $service)
                                <button type="button" class="rf-sv-service" data-service="{{ $service->id }}" data-name="{{ $service->name }}">
                                    @if ($service->imageUrl())
                                        <img class="rf-sv-service-img" src="{{ $service->imageUrl() }}" alt="" loading="lazy" width="36" height="36">
                                    @elseif ($service->icon)<i class="bi {{ $service->icon }}" aria-hidden="true"></i>@endif
                                    <span>
                                        <strong>{{ $service->name }}</strong>
                                        @if ($service->description)<small>{{ $service->description }}</small>@endif
                                    </span>
                                    @if (($readyCounts[$service->id] ?? 0) > 0)<span class="rf-sv-ready-count">{{ $readyCounts[$service->id] }} جاهزة</span>@endif
                                    @if ($preview && ! $service->status)<span class="rf-sv-hidden">مخفي</span>@endif
                                </button>
                            @endforeach
                        </div>
                    </div>
                @endforeach
                <p class="rf-sv-empty" id="rf-sv-empty" hidden>{{ $page['no_results'] }}</p>
            </div>

            <div class="rf-sv-cta">
                <div>
                    <strong>{{ $page['cta_title'] }}</strong>
                    @if ($page['cta_text'])<span>{{ $page['cta_text'] }}</span>@endif
                </div>
                @auth
                    <button type="button" class="rf-sv-btn rf-sv-btn-lime" data-open-help>{{ $page['cta_button'] }}</button>
                @else
                    <a class="rf-sv-btn rf-sv-btn-lime" href="{{ route('services.help.login') }}">{{ $page['cta_button'] }}</a>
                @endauth
            </div>
        </div>
    </section>

    {{-- The choice window, then the help form, in one dialog (a bottom sheet on phones). --}}
    <div class="modal fade rf-sv-modal" id="rf-sv-modal" tabindex="-1" aria-labelledby="rf-sv-modal-title" aria-hidden="true" dir="rtl">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="rf-sv-modal-title" data-title-choice="{{ $choice['title'] }}" data-title-help="{{ $help['title'] }}">{{ $choice['title'] }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
                </div>
                <div class="modal-body">
                    <div data-step="choice">
                        <p class="rf-sv-selected">{{ $choice['selected_label'] }} <strong data-selected-name></strong></p>
                        <a class="rf-sv-btn rf-sv-btn-primary rf-sv-btn-block" data-request-link href="#"><span data-request-text>{{ $choice['request_button'] }}</span> <i class="bi bi-check2"></i></a>
                        <p class="rf-sv-no-request" data-no-request hidden>{{ $choice['no_request_text'] }}</p>
                        <a class="rf-sv-btn rf-sv-btn-outline rf-sv-btn-block mt-2" data-ready-link href="#" hidden><i class="bi bi-bag-check" aria-hidden="true"></i> <span>{{ $choice['ready_button'] }}</span> (<span data-ready-count></span>)</a>
                        <div class="rf-sv-paths">
                            <div class="rf-sv-path">
                                <small>{{ $choice['path_one_label'] }}</small>
                                <strong>{{ $choice['request_title'] }}</strong>
                                <span>{{ $choice['request_text'] }}</span>
                            </div>
                            <div class="rf-sv-path">
                                <small>{{ $choice['path_two_label'] }}</small>
                                <strong>{{ $choice['help_title'] }}</strong>
                                <span>{{ $choice['help_text'] }}</span>
                                @auth
                                    <button type="button" class="rf-sv-btn rf-sv-btn-outline" data-to-help>{{ $choice['help_button'] }}</button>
                                @else
                                    <a class="rf-sv-btn rf-sv-btn-outline" data-help-login href="{{ route('services.help.login') }}">{{ $choice['help_button'] }}</a>
                                @endauth
                            </div>
                        </div>
                    </div>

                    <div data-step="help" hidden>
                        @if ($help['text'])<p class="rf-sv-muted">{{ $help['text'] }}</p>@endif
                        @auth
                            <form method="POST" action="{{ route('services.help.store') }}" novalidate>
                                @csrf
                                <input type="hidden" name="_help" value="1">
                                <input type="hidden" name="service" value="{{ old('service') }}" data-help-service>
                                <p class="rf-sv-selected" data-help-service-line hidden>{{ $choice['selected_label'] }} <strong data-selected-name></strong></p>
                                <div class="mb-3">
                                    <label for="rf-sv-whatsapp" class="form-label">{{ $help['whatsapp_label'] }} <span class="text-danger">*</span></label>
                                    <input type="tel" class="form-control" id="rf-sv-whatsapp" name="whatsapp" data-country-picker dir="ltr" maxlength="30" required
                                        value="{{ old('whatsapp', auth()->user()->phone) }}" placeholder="{{ $help['whatsapp_placeholder'] }}">
                                    @error('whatsapp')<div class="text-danger small">{{ $message }}</div>@enderror
                                </div>
                                <div class="mb-3">
                                    <label for="rf-sv-message" class="form-label">{{ $help['message_label'] }} <span class="text-danger">*</span></label>
                                    <textarea class="form-control" id="rf-sv-message" name="message" rows="4" maxlength="2000" required placeholder="{{ $help['message_placeholder'] }}">{{ old('message') }}</textarea>
                                    @error('message')<div class="text-danger small">{{ $message }}</div>@enderror
                                    @error('service')<div class="text-danger small">{{ $message }}</div>@enderror
                                </div>
                                <button type="submit" class="rf-sv-btn rf-sv-btn-primary rf-sv-btn-block">{{ $help['submit'] }}</button>
                            </form>
                        @else
                            <p>{{ $help['login_text'] }}</p>
                            <a class="rf-sv-btn rf-sv-btn-primary rf-sv-btn-block" data-help-login href="{{ route('services.help.login') }}">{{ $help['login_button'] }}</a>
                        @endauth
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('js')
    <script>
        (function () {
            var catalog = @json($catalog);
            var modalEl = document.getElementById('rf-sv-modal');
            var modal = new bootstrap.Modal(modalEl);
            var title = document.getElementById('rf-sv-modal-title');
            var helpLoginBase = @json(route('services.help.login'));

            // Groups open and close.
            document.querySelectorAll('.rf-sv-group-head').forEach(function (head) {
                head.addEventListener('click', function () {
                    var open = head.getAttribute('aria-expanded') !== 'true';
                    head.setAttribute('aria-expanded', open);
                    head.parentElement.querySelector('.rf-sv-list').hidden = !open;
                });
            });

            // Instant search across all service names (and group names).
            var normalize = function (text) {
                return (text || '').toLowerCase().replace(/[ً-ْ]/g, '').replace(/[أإآ]/g, 'ا').replace(/ة/g, 'ه').replace(/ى/g, 'ي').trim();
            };
            document.getElementById('rf-sv-search').addEventListener('input', function () {
                var q = normalize(this.value), any = false;
                document.querySelectorAll('[data-group]').forEach(function (group) {
                    var groupMatch = q && normalize(group.querySelector('.rf-sv-group-text strong').textContent).indexOf(q) !== -1;
                    var shown = 0;
                    group.querySelectorAll('.rf-sv-service').forEach(function (item) {
                        var match = !q || groupMatch || normalize(item.dataset.name).indexOf(q) !== -1;
                        item.hidden = !match;
                        if (match) shown++;
                    });
                    group.hidden = q && shown === 0;
                    var head = group.querySelector('.rf-sv-group-head');
                    head.setAttribute('aria-expanded', q ? shown > 0 : false);
                    group.querySelector('.rf-sv-list').hidden = !q || shown === 0;
                    if (shown) any = true;
                });
                document.getElementById('rf-sv-empty').hidden = !q || any;
            });

            var setService = function (id) {
                var service = id ? catalog[id] : null;
                modalEl.querySelectorAll('[data-selected-name]').forEach(function (el) { el.textContent = service ? service.name : ''; });
                var line = modalEl.querySelector('[data-help-service-line]');
                if (line) line.hidden = !service;
                var input = modalEl.querySelector('[data-help-service]');
                if (input) input.value = service ? service.id : '';
                modalEl.querySelectorAll('[data-help-login]').forEach(function (link) {
                    link.href = helpLoginBase + (service ? '?service=' + service.id : '');
                });
                if (service) {
                    // A service without a request form or link is asked for through «ساعدوني أختار».
                    var link = modalEl.querySelector('[data-request-link]');
                    link.hidden = !service.url;
                    modalEl.querySelector('[data-no-request]').hidden = !!service.url;
                    if (service.url) {
                        link.href = service.url;
                        modalEl.querySelector('[data-request-text]').textContent = service.button;
                    }
                    var ready = modalEl.querySelector('[data-ready-link]');
                    ready.hidden = !service.ready;
                    if (service.ready) {
                        ready.href = service.ready_url;
                        ready.querySelector('[data-ready-count]').textContent = service.ready;
                    }
                }
            };
            var showStep = function (step) {
                modalEl.querySelectorAll('[data-step]').forEach(function (el) { el.hidden = el.dataset.step !== step; });
                title.textContent = step === 'help' ? title.dataset.titleHelp : title.dataset.titleChoice;
            };

            document.querySelectorAll('.rf-sv-service').forEach(function (item) {
                item.addEventListener('click', function () {
                    setService(item.dataset.service);
                    showStep('choice');
                    modal.show();
                });
            });
            modalEl.querySelectorAll('[data-to-help]').forEach(function (button) {
                button.addEventListener('click', function () { showStep('help'); });
            });
            document.querySelectorAll('[data-open-help]').forEach(function (button) {
                button.addEventListener('click', function () { setService(null); showStep('help'); modal.show(); });
            });

            // Back from login, or a form error: open the help form again.
            @if ($openHelp)
                setService(@json((int) (old('service') ?: request('service')) ?: null));
                showStep('help');
                modal.show();
            @endif
        })();
    </script>
@endsection
