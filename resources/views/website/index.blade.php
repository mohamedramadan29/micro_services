@extends('website.layouts.master')
@section('title')
    نفذها
@endsection
@section('css')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">
    <style>
        /* Testimonials Section Styles */
        .testimonials-section {
            background: linear-gradient(135deg, #3fb699 0%, #2e8973 100%);
            color: white;
            position: relative;
            overflow: hidden;
        }

        .testimonials-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.05'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E") repeat;
        }

        .section-title {
            font-size: 2.5rem;
            font-weight: bold;
            margin-bottom: 1rem;
            color: white;
        }

        .section-subtitle {
            font-size: 1.2rem;
            color: rgba(255, 255, 255, 0.8);
        }

        .testimonial-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 2rem;
            height: 100%;
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .testimonial-card::before {
            content: '"';
            position: absolute;
            top: -20px;
            left: 20px;
            font-size: 100px;
            color: rgba(255, 255, 255, 0.1);
            font-family: Georgia, serif;
        }

        .testimonial-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
            background: rgba(255, 255, 255, 0.15);
        }

        .testimonial-content {
            margin-bottom: 1.5rem;
            position: relative;
            z-index: 1;
        }

        .stars {
            color: #d9dadb;
            font-size: 1.2rem;
        }

        .testimonial-text {
            font-size: 1.1rem;
            line-height: 1.6;
            margin: 0;
            font-style: italic;
        }

        .testimonial-author {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding-top: 1.5rem;
            border-top: 1px solid rgba(255, 255, 255, 0.2);
        }

        .author-image img {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid rgba(255, 255, 255, 0.3);
        }

        .author-name {
            margin: 0;
            font-size: 1.1rem;
            font-weight: bold;
            color: white;
        }

        .author-position {
            margin: 0;
            color: rgba(255, 255, 255, 0.8);
            font-size: 0.95rem;
        }

        .author-company {
            color: #d9dadb;
        }

        /* Swiper Custom Styles */
        .testimonialSwiper {
            padding: 2rem 0;
        }

        .swiper-pagination-bullet {
            background: white;
            opacity: 0.5;
        }

        .swiper-pagination-bullet-active {
            opacity: 1;
            background: #d9dadb;
        }

        .swiper-button-next,
        .swiper-button-prev {
            color: white;
            background: rgba(255, 255, 255, 0.1);
            width: 50px;
            height: 50px;
            border-radius: 50%;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .swiper-button-next:after,
        .swiper-button-prev:after {
            font-size: 20px;
        }

        .swiper-button-next:hover,
        .swiper-button-prev:hover {
            background: rgba(255, 255, 255, 0.2);
        }

        @media (max-width: 768px) {
            .section-title {
                font-size: 2rem;
            }

            .testimonial-card {
                padding: 1.5rem;
            }

            .testimonial-text {
                font-size: 1rem;
            }
        }
    </style>
@endsection
@section('content')
    <!-- ============================ Hero Banner  Start================================== -->
    @php
        $hero = \App\Support\SiteContent::first('home.hero');
    @endphp
    {{-- «الرسومات التنفيذية»: the hero is a drawing sheet — the headline on the right, the project drawn
         as a plan on the left, signed off in a title block (money held until the client approves). --}}
    <section class="nf-hero" dir="rtl">
        <div class="nf-cross" aria-hidden="true"><i class="h"></i><i class="v"></i><b></b></div>
        <div class="container">
            @php $plan = \App\Support\SiteContent::first('home.plan'); @endphp
            <div class="nf-hero-inner {{ $plan['show'] === '0' ? 'solo' : '' }}">
                <div class="nf-hero-copy">
                    <h1>@if (trim($hero['title']) !== ''){{ $hero['title'] }}@else{{ $setting->hero_title_1 ?? 'نفّذها منصّة تجمع خبراء التسويق والإعلام مع أصحاب المشاريع، لتسهيل الوصول إلى الكفاءات المناسبة وتنفيذ الأعمال باحترافية وسرعة' }}@if ($setting->hero_title_2 ?? false) {{ $setting->hero_title_2 }}@endif @if ($setting->hero_title_3 ?? false) {{ $setting->hero_title_3 }}@endif @endif</h1>
                    @php $heroSubtitle = trim($hero['subtitle']) !== '' ? $hero['subtitle'] : ($setting->hero_subtitle ?? null); @endphp
                    @if ($heroSubtitle)
                        <p class="nf-hero-lead">{{ $heroSubtitle }}</p>
                    @endif
                    @if ($hero['show_search'] !== '0')
                        <form class="nf-search" role="search" method="get" action="{{ url('search') }}">
                            <input type="search" name="search" aria-label="{{ $hero['search_placeholder'] ?: __('index.search_text') }}"
                                placeholder="{{ $hero['search_placeholder'] ?: __('index.search_text') }}">
                            <button type="submit" class="nf-search-btn"><i class="bi bi-search"></i> <span>{{ $hero['search_button'] ?: __('index.search') }}</span></button>
                        </form>
                    @endif
                    <div class="nf-hero-actions">
                        @php $heroPrimary = trim($hero['primary_text']) !== '' ? \App\Services\Content\HomeActions::url($hero['primary_action'] ?: 'request') : null; @endphp
                        @if ($heroPrimary)
                            <a class="nf-link-btn ink" href="{{ $heroPrimary }}"><i class="bi bi-ui-checks"></i> {{ $hero['primary_text'] }}</a>
                        @endif
                        @if ($hero['button_text'] !== '' && $hero['button_link'] !== '')
                            <a class="nf-link-btn" href="{{ \App\Support\SiteContent::url($hero['button_link']) }}">{{ $hero['button_text'] }}</a>
                        @else
                            <a class="nf-link-btn" href="{{ route('services.index') }}">تصفّح الخدمات</a>
                        @endif
                    </div>
                </div>

                @if ($plan['show'] !== '0')
                <div class="nf-plan nf-sheet" aria-label="كيف يسير مشروعك على نفّذها" data-nf-plan
                     data-status="{{ json_encode([$plan['status_1'], $plan['status_2'], $plan['status_3'], $plan['status_4']], JSON_UNESCAPED_UNICODE) }}">
                    <svg class="nf-plan-svg" viewBox="0 0 520 300" role="img" aria-labelledby="nf-plan-t">
                        <title id="nf-plan-t">{{ $plan['stage_1'] }} ← {{ $plan['stage_4'] }}</title>
                        {{-- outer frame and inner border of the sheet --}}
                        <rect class="ln thin" x="6" y="6" width="508" height="288" rx="2" style="--nf-len:1600; --d:0s"/>
                        {{-- the plan: a building block with rooms (the work) --}}
                        <path class="ln" d="M60 70 H330 V200 H60 Z" style="--nf-len:820; --d:.15s"/>
                        <path class="ln" d="M170 70 V200 M250 70 V140 H330" style="--nf-len:420; --d:.45s"/>
                        <path class="ln thin" d="M60 150 H170" style="--nf-len:120; --d:.6s"/>
                        {{-- dimension lines --}}
                        <path class="ln thin" d="M60 46 H330 M60 40 V52 M330 40 V52" style="--nf-len:320; --d:.7s"/>
                        <text class="dim" x="195" y="38" text-anchor="middle">{{ $plan['scope_label'] }}</text>
                        <path class="ln thin" d="M352 70 V200 M346 70 H358 M346 200 H358" style="--nf-len:200; --d:.8s"/>
                        {{-- the stages line, read right-to-left: request → offers → execution → you approve.
                             A pen (dot) travels it in a loop; the green progress follows the pen. --}}
                        <path class="ln" d="M470 252 H60" style="--nf-len:420; --d:.9s"/>
                        <path class="nf-prog" d="M420 252 H60" pathLength="360"/>
                        <g class="nf-st" data-step="0" tabindex="0" role="button" aria-label="{{ $plan['stage_1'] }}"><circle class="node" cx="420" cy="252" r="7"/><text class="strong" x="420" y="280" text-anchor="middle">{{ $plan['stage_1'] }}</text></g>
                        <g class="nf-st" data-step="1" tabindex="0" role="button" aria-label="{{ $plan['stage_2'] }}"><circle class="node" cx="300" cy="252" r="7"/><text class="strong" x="300" y="280" text-anchor="middle">{{ $plan['stage_2'] }}</text></g>
                        <g class="nf-st" data-step="2" tabindex="0" role="button" aria-label="{{ $plan['stage_3'] }}"><circle class="node" cx="180" cy="252" r="7"/><text class="strong" x="180" y="280" text-anchor="middle">{{ $plan['stage_3'] }}</text></g>
                        <g class="nf-st nf-st-done" data-step="3" tabindex="0" role="button" aria-label="{{ $plan['stage_4'] }}"><circle class="node done" cx="60" cy="252" r="9"/><text class="strong" x="60" y="280" text-anchor="middle">{{ $plan['stage_4'] }}</text></g>
                        <g class="nf-pen" aria-hidden="true"><circle cx="420" cy="252" r="12" class="nf-pen-halo"/><circle cx="420" cy="252" r="4.5" class="nf-pen-tip"/></g>
                        {{-- notes in the margin --}}
                        <text x="440" y="104" text-anchor="middle">{{ $plan['note_1'] }}</text>
                        <path class="ln thin" d="M392 100 H368" style="--nf-len:30; --d:1.1s"/>
                        <text x="440" y="172" text-anchor="middle">{{ $plan['note_2'] }}</text>
                        <path class="ln thin" d="M392 168 H368" style="--nf-len:30; --d:1.2s"/>
                    </svg>
                    <div class="nf-titleblock">
                        <dl>
                            @foreach ([1, 2, 3] as $row)
                                @if (trim($plan['row_' . $row . '_label'] . $plan['row_' . $row . '_value']) !== '')
                                    <dt>{{ $plan['row_' . $row . '_label'] }}</dt><dd @if ($row === 3) class="held" @endif>{{ $plan['row_' . $row . '_value'] }}</dd>
                                @endif
                            @endforeach
                            <dt>{{ $plan['status_label'] }}</dt><dd class="nf-status" aria-live="polite"><span>{{ $plan['status_1'] }}</span></dd>
                        </dl>
                        <div class="nf-titleblock-stamp"><span class="nf-stamp" aria-label="{{ $plan['stamp_text'] }}">{{ $plan['stamp_text'] }}<small>{{ $plan['stamp_small'] }}</small></span></div>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </section>
    <!-- ============================ Hero Banner End ================================== -->

    <!-- ============================ Start Paths (homepage cards in two groups, managed in admin «إدارة محتوى الواجهة» ← الرئيسية - المربعات) ============== -->
    @php
        $paths = \App\Support\SiteContent::first('home.paths');
        $pathCards = \App\Support\SiteContent::items('home.cards')->filter(fn ($card) => \App\Support\SiteContent::shownTo($card['audience'] ?: 'all'))->values();
        $cardGroups = ['primary' => $pathCards->where('group', '!=', 'secondary')->values(), 'secondary' => $pathCards->where('group', 'secondary')->values()];
        app(\App\Services\Analytics\Tracker::class)->record('home_card_view', ['meta' => ['cards' => $pathCards->map(fn ($card) => $card['_id'] ?? null)->filter()->values()->all()]]);
    @endphp
    <section class="rf-home-paths" dir="rtl">
        <div class="container">
            <div class="rf-home-paths-head">
                <h2>{{ $paths['title'] }}</h2>
                @if ($paths['subtitle'])<p>{{ $paths['subtitle'] }}</p>@endif
            </div>
            @foreach ($cardGroups as $groupKey => $groupCards)
                @continue($groupCards->isEmpty())
                @if ($groupKey === 'secondary' && ($paths['secondary_title'] || $paths['secondary_subtitle']))
                    <div class="rf-home-paths-head rf-home-paths-head-2">
                        @if ($paths['secondary_title'])<h3>{{ $paths['secondary_title'] }}</h3>@endif
                        @if ($paths['secondary_subtitle'])<p>{{ $paths['secondary_subtitle'] }}</p>@endif
                    </div>
                @endif
                <div class="rf-home-cards rf-home-cards-{{ $groupKey }} rf-home-cards-n{{ $groupCards->count() }}">
                    @foreach ($groupCards as $card)
                        @php
                            $target = \App\Services\Content\HomeActions::url($card['action']);
                            // Old saved cards may still have a typed link.
                            $target ??= ($card['action'] ?? '') === '' && ! empty($card['link']) ? \App\Support\SiteContent::url($card['link']) : null;
                            $href = $target && ! empty($card['_id']) ? route('home.card', $card['_id']) : $target;
                            $image = \App\Support\SiteContent::media($card['image']);
                        @endphp
                        <{{ $href ? 'a' : 'div' }} class="rf-home-card {{ $href ? 'rf-home-card-main' : 'rf-home-card-soon' }}"
                            @if ($href) href="{{ $href }}" @else aria-disabled="true" @endif>
                            <span class="rf-home-num">{{ $groupKey === 'secondary' ? $cardGroups['primary']->count() + $loop->iteration : $loop->iteration }}</span>
                            @if ($image)
                                <img class="rf-home-img" src="{{ $image->url() }}" alt="{{ $image->alt }}" loading="lazy">
                            @elseif ($card['icon'])
                                <span class="rf-home-icon" aria-hidden="true"><i class="bi {{ $card['icon'] }}"></i></span>
                            @endif
                            <strong>{{ $card['title'] }}</strong>
                            @if ($card['description'])<span>{{ $card['description'] }}</span>@endif
                            @if ($href)
                                <span class="rf-home-cta">{{ \App\Services\Content\HomeActions::cta($card) }} <i class="bi bi-arrow-left"></i></span>
                            @else
                                <span class="rf-home-soon">{{ $paths['soon_text'] ?: 'قريبًا' }}</span>
                            @endif
                        </{{ $href ? 'a' : 'div' }}>
                    @endforeach
                </div>
            @endforeach
            @if ($paths['footnote'])<p class="rf-home-paths-note">{{ $paths['footnote'] }}</p>@endif
        </div>
    </section>
    <!-- ============================ End Paths ==================================== -->

    <!-- =========================== Payment methods (EasyKash) ============================ -->
    @php $homeSections = \App\Support\SiteContent::first('home.sections'); @endphp
    @if ($homeSections['show_payments'] !== '0')
    <div class="container">
        <div class="rf-pay-methods" dir="rtl">
            <span class="rf-pay-methods-title"> {{ $homeSections['payments_title'] }} </span>
            <img loading="lazy" src="{{ asset('assets/website/img/pay-visa.svg') }}" alt="Visa" width="64" height="40">
            <img loading="lazy" src="{{ asset('assets/website/img/pay-mastercard.svg') }}" alt="Mastercard" width="64" height="40">
            <span class="rf-pay-chip"><i class="bi bi-shop"></i> فوري</span>
            <span class="rf-pay-chip"><i class="bi bi-phone"></i> المحافظ الإلكترونية</span>
        </div>
    </div>
    @endif




    {{-- The four promises, read like the key of a drawing (PRODUCT.md «Positioning»). --}}
    @php
        $trust = \App\Support\SiteContent::first('home.trust');
        $trustItems = \App\Support\SiteContent::items('home.trust_items');
    @endphp
    @if ($trust['show'] !== '0' && $trustItems->isNotEmpty())
    <section class="nf-legend-wrap" dir="rtl">
        <div class="container">
            <div class="nf-legend nf-sheet">
                <div class="nf-legend-title">
                    <h2>{{ $trust['title'] }}</h2>
                    @if ($trust['subtitle'])<p>{{ $trust['subtitle'] }}</p>@endif
                </div>
                <ol>
                    @foreach ($trustItems as $item)
                        <li class="nf-reveal" style="--d: {{ $loop->index * 0.08 }}s">
                            @include('website.partials.nf-icon', ['name' => $item['icon']])
                            <div>
                                <h3>{{ $item['title'] }}</h3>
                                <p>{{ $item['text'] }}@if ($item['link_text'] && $item['link']) <a href="{{ \App\Support\SiteContent::url($item['link']) }}">{{ $item['link_text'] }}</a>@endif</p>
                            </div>
                        </li>
                    @endforeach
                </ol>
            </div>
        </div>
    </section>
    @endif

    <!-- ============================ How It Work Start ==================================== -->
    <section class="nf-how" dir="rtl">
        <div class="container">
            @include('website.free_consult_model')
            @php
                $how = \App\Support\SiteContent::first('home.how');
                $steps = \App\Support\SiteContent::items('home.steps');
            @endphp
            <div class="sec-heading">
                <h2>{{ $how['title'] }} <span>{{ $how['highlight'] }}</span></h2>
            </div>
            {{-- One dimensioned line, one station per step; the last station is the approval (green). --}}
            <div class="nf-track" style="--n: {{ max(1, $steps->count()) }}">
                @foreach ($steps as $i => $step)
                    @php $stepImage = \App\Support\SiteContent::media($step['image']); @endphp
                    <div class="nf-station nf-reveal" style="--d: {{ $i * 0.12 }}s; --i: {{ $i }}; --n: {{ max(1, $steps->count()) }}">
                        <span class="nf-station-node">{{ $i + 1 }}</span>
                        @if ($stepImage)<img loading="lazy" src="{{ $stepImage->url() }}" alt="">@endif
                        <h3>{{ $step['title'] }}</h3>
                        <p>{{ $step['text'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
    <!-- ============================ How It Work End ==================================== -->

    <!-- ############################# Start Last Services ################# -->

    @if ($services->isNotEmpty())
    <section class="min-sec categories home_services" dir="rtl">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-7 col-md-9">
                    <div class="sec-heading">
                        <h2> {{ $homeSections['services_title'] }} </h2>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <div class="swiper services-slider">
                        <div class="swiper-wrapper">
                            <!-- Single Item -->
                            @foreach ($services as $serv)
                                <!-- Single Item -->
                                <div class="swiper-slide">
                                    <div class="ser_110 shadow_0 serv_data_new_details">
                                        <div class="ser_110_thumb">
                                            <a href="{{ url('service/' . $serv['id'] . '-' . $serv['slug']) }}"
                                                class="ser_100_link"><img
                                                    src="{{ asset('assets/uploads/services/' . $serv['image']) }}"
                                                    class="img-fluid" alt=""></a>
                                        </div>
                                        <div class="ser_110_footer bott">
                                            <div class="_110_foot_left">
                                                <div class="_autho098">
                                                    @if (empty($serv['user']['image']))
                                                        <img src="{{ asset('assets/website/img/avatar.png') }}"
                                                            class="img-fluid circle" alt="">
                                                    @else
                                                        <img src="{{ optional($serv['user'])->avatarUrl(128) }}"
                                                            class="img-fluid circle" alt="">
                                                    @endif

                                                    <img src="{{ asset('assets/website/img/verify.svg') }}"
                                                        class="verified" width="12" alt="">
                                                </div>

                                                <div class="_autho097">
                                                    <h5>
                                                        @if ($serv->user?->isApprovedFreelancer())
                                                            <a href="{{ url('user/' . $serv->user->user_name) }}">{{ Str::limit((string) $serv->user->name, 22, '…') }}</a>
                                                        @else
                                                            {{ Str::limit((string) $serv->user?->name, 22, '…') }}
                                                        @endif
                                                    </h5>
                                                </div>
                                            </div>

                                        </div>
                                        <div class="ser_110_caption">
                                            @if ($serv->user?->ratingLabel())
                                                <div class="ser_rev098">@include('website.freelancers.partials.stars', ['value' => $serv->user->rate]) <small>{{ $serv->user->ratingLabel() }}</small></div>
                                            @endif
                                            <div class="ser_title098">
                                                <h4 class="_ser_title rf-home-svc-title"><a href="{{ $serv->url() }}">{{ Str::limit($serv->name, 60, '…') }}</a></h4>
                                            </div>
                                            <div class="_oi0po price_section"><i class="fa fa-bolt"></i>
                                                {{ __('services.serive_price') }} <strong class="theme-cl">
                                                    {{ \App\Support\Money::show($serv['price']) }} </strong>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <!-- Add Pagination (optional) -->
                        <div class="swiper-pagination" style="margin-top: 15px"></div>
                    </div>
                </div>
            </div>
            <div class="rf-home-svc-all">
                <a href="{{ route('services.ready') }}" class="btn btn-light">{{ \App\Support\SiteContent::first('ready.page')['home_link'] }} ({{ $readyServicesCount }}) <i class="bi bi-arrow-left" aria-hidden="true"></i></a>
            </div>

        </div>
        <!-- Include Swiper JS and initialization script -->
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const swiper = new Swiper('.services-slider', {
                    slidesPerView: 1,
                    spaceBetween: 20,
                    loop: true, // Enable continuous looping
                    autoplay: {
                        delay: 3000, // Slide every 3 seconds
                        disableOnInteraction: false, // Continue autoplay after user interaction
                    },
                    pagination: {
                        el: '.swiper-pagination',
                        clickable: true,
                    },
                    breakpoints: {
                        576: {
                            slidesPerView: 2,
                        },
                        768: {
                            slidesPerView: 3,
                        },
                        992: {
                            slidesPerView: 4,
                        },
                    },
                });
            });
        </script>
        <!-- Inline CSS for basic styling -->
        <!-- Inline CSS for basic styling -->
        <style>
            .services-slider .swiper-slide {
                display: flex;
                justify-content: center;
            }

            .services-slider .ser_110 {
                max-width: 300px;
                /* Adjust as needed */
                width: 100%;
            }

            .swiper-pagination-bullet {
                background: #2b3038;
                opacity: 0.5;
            }

            .swiper-pagination-bullet-active {
                background: #3fb699;
                opacity: 1;
            }
        </style>
    </section>


    @endif
    <!-- ############################# End Last Services ################# -->

    {{-- «أحدث الكورسات» removed: course links are no longer shown anywhere (client file «تعديلات», item 02). --}}

    <!-- =========================== Start Categories ========================================== -->
    @if ($main_categories->isNotEmpty())
    <section class="min-sec categories" dir="rtl">
        <div class="container">

            <div class="row justify-content-center">
                <div class="col-lg-7 col-md-9">
                    <div class="sec-heading">

                        <h2>{{ $homeSections['categories_title'] }} @if ($homeSections['categories_highlight'])<span class="theme-cl-2"> {{ $homeSections['categories_highlight'] }}</span>@endif</h2>
                    </div>
                </div>
            </div>
            <div class="row">
                <!-- Single Item -->
                @foreach ($main_categories as $category)
                    <div class="col-lg-3 col-md-6 col-6">
                        <div class="ser_110">
                            <div class="ser_110_thumb">
                                <a href="{{ url('category/service/' . $category['slug']) }}" class="ser_100_link">
                                    <img loading="lazy"
                                        style="width: 120px; height:120px;margin:auto;padding-top: 15px;display:block;"
                                        src=" {{ asset('assets/uploads/service_category/' . $category['image']) }}"
                                        class="img-fluid" alt=""></a>
                            </div>
                            <div class="ser_110_footer bott">
                                <div class="_110_foot_left">
                                    <div>
                                        <h5>
                                            <a href="{{ url('category/' . $category['slug']) }}"> {{ $category['name'] }}
                                            </a>
                                        </h5>

                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach

            </div>

        </div>
    </section>

    @endif
    <!-- =========================== End Categories  ======================================= -->

    <!-- ============================ Start  Popular Category ==================================== -->
    {{-- <section class="gray-light">
        <div class="container">

            <div class="row justify-content-center">
                <div class="col-lg-7 col-md-9">
                    <div class="sec-heading">



                        <h2> خدمات لا غنى عنها <span class="theme-cl-2"> لأي رائد أعمال طموح </span></h2>
                    </div>
                </div>
            </div>

            <div class="row justify-content-center">

                @foreach ($sub_categories as $sub_category)
                    <!-- Single Category -->
                    <div class="col-lg-3 col-md-4 col-6">
                        <div class="urip_cated shadow">
                            <div class="urip_cated_avater">
                                <i class="ti-bar-chart"></i>
                            </div>
                            <div class="urip_cated_caps">
                                <h3 class="cats_urip_title"><a href="{{ url('services/' . $sub_category['slug']) }}">
                                        {{ $sub_category['name'] }} </a></h3>
                            </div>
                        </div>
                    </div>
                @endforeach

            </div>
        </div>
    </section> --}}
    <!-- ============================ End Popular Category ==================================== -->

    <!-- ########################################### Presit Section ######################## -->

    {{-- <section class="call-to-act gift_section"
        style="background:#3fb699 url({{ asset('assets/website/img/landing-bg.png') }}) no-repeat">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-12">
                    <div class="inner-flexible-box subscribe-box">
                        <img loading="lazy" class="animate-pulse" src="{{ asset('assets/website/img/gift.png') }}"
                            alt="">
                    </div>
                    <div class="clt-caption text-center mb-4">
                        <h2 class="text-light">هدية مميزة بانتظارك </h2>

                        <p class="text-light">


                            بمجرد أن تصل قيمة مشترياتك إلى <strong>1000 جنيه</strong>، ستحصل على رصيد مجاني بقيمة
                            <strong>50 جنيه</strong> يمكن إضافته إلى حسابك بعد طلبك القادم! استمتع بتسوقك معنا واستفد من
                            المزيد من المزايا.

                        </p>
                    </div>
                </div>
            </div>
        </div>

    </section> --}}

    <!------------------------------------- End Present Section ##################### -->

    <!-- ============================ Testimonials Section Start ============================ -->
    @php
        $reviews = \App\Models\front\Review::active()->approved()->ordered()->get();
    @endphp
    @if ($reviews->isNotEmpty())
    <section class="testimonials-section py-5 bg-light">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <div class="section-header text-center mb-5">
                        <h2 class="section-title">{{ $homeSections['reviews_title'] }}</h2>
                        @if ($homeSections['reviews_subtitle'])<p class="section-subtitle">{{ $homeSections['reviews_subtitle'] }}</p>@endif
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="testimonials-slider">
                        <div class="swiper testimonialSwiper">
                            <div class="swiper-wrapper">
                                @foreach ($reviews as $review)
                                <div class="swiper-slide">
                                    <div class="testimonial-card">
                                        <div class="testimonial-content">
                                            <div class="stars mb-3">
                                                {!! $review->rating_stars !!}
                                            </div>
                                            <p class="testimonial-text">{{ $review->content }}</p>
                                        </div>
                                        <div class="testimonial-author">
                                            <div class="author-image">
                                                @if($review->client_image)
                                                    <img src="{{ asset('assets/uploads/reviews/' . $review->client_image) }}" alt="{{ $review->client_name }}">
                                                @else
                                                    <img src="{{ asset('assets/website/img/user-default.png') }}" alt="Default">
                                                @endif
                                            </div>
                                            <div class="author-info">
                                                <h5 class="author-name">{{ $review->client_name }}</h5>
                                                <p class="author-position">
                                                    {{ $review->client_position }}
                                                    @if($review->client_company)
                                                        <span class="author-company">@ {{ $review->client_company }}</span>
                                                    @endif
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                            <div class="swiper-pagination"></div>
                            <div class="swiper-button-next"></div>
                            <div class="swiper-button-prev"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    @endif
    <!-- ============================ Testimonials Section End ============================ -->

@endsection


@section('js')
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    <script>
        // Testimonials Slider
        const testimonialSwiper = new Swiper('.testimonialSwiper', {
            slidesPerView: 1,
            spaceBetween: 30,
            loop: true,
            autoplay: {
                delay: 5000,
                disableOnInteraction: false,
            },
            pagination: {
                el: '.swiper-pagination',
                clickable: true,
            },
            navigation: {
                nextEl: '.swiper-button-next',
                prevEl: '.swiper-button-prev',
            },
            breakpoints: {
                640: {
                    slidesPerView: 2,
                    spaceBetween: 20,
                },
                768: {
                    slidesPerView: 2,
                    spaceBetween: 30,
                },
                1024: {
                    slidesPerView: 3,
                    spaceBetween: 30,
                },
            },
        });

        // ---- Living homepage (motion only when the page is visible and motion is welcome) ----
        (function () {
            var reduce = window.matchMedia && matchMedia('(prefers-reduced-motion: reduce)').matches;
            var plan = document.querySelector('[data-nf-plan]');
            if (plan) {
                var steps = plan.querySelectorAll('.nf-st');
                var pen = plan.querySelector('.nf-pen');
                var prog = plan.querySelector('.nf-prog');
                var status = plan.querySelector('.nf-status span');
                var texts = JSON.parse(plan.getAttribute('data-status') || '[]');
                var xs = [420, 300, 180, 60];
                var step = 0, held = 0, inView = true;
                var show = function (i) {
                    step = i;
                    steps.forEach(function (g, k) { g.classList.toggle('is-on', k === i); g.classList.toggle('is-past', k < i); });
                    if (pen) pen.style.transform = 'translateX(' + (xs[i] - xs[0]) + 'px)';
                    if (prog) prog.style.strokeDashoffset = String(360 - (xs[0] - xs[i]));
                    if (status && texts[i]) {
                        status.classList.remove('in'); void status.offsetWidth;
                        status.textContent = texts[i]; status.classList.add('in');
                    }
                    plan.classList.toggle('is-approved', i === xs.length - 1);
                };
                show(reduce ? xs.length - 1 : 0);
                // Hover / focus / tap on a station shows it and holds the loop a moment.
                steps.forEach(function (g, k) {
                    var pick = function () { held = Date.now() + 6000; show(k); };
                    g.addEventListener('mouseenter', pick); g.addEventListener('focus', pick); g.addEventListener('click', pick);
                    g.addEventListener('keydown', function (e) { if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); pick(); } });
                });
                if ('IntersectionObserver' in window) {
                    new IntersectionObserver(function (en) { inView = en[0].isIntersecting; }).observe(plan);
                }
                if (!reduce) {
                    setInterval(function () {
                        if (document.hidden || !inView || Date.now() < held) return;
                        show((step + 1) % xs.length);
                    }, 2600);
                }
            }

            // Drafting crosshair that follows the pointer over the hero (mouse/trackpad only).
            var hero = document.querySelector('.nf-hero'), cross = hero && hero.querySelector('.nf-cross');
            if (hero && cross && !reduce && window.matchMedia && matchMedia('(hover: hover) and (pointer: fine)').matches) {
                var label = cross.querySelector('b'), raf = 0, px = 0, py = 0;
                hero.addEventListener('pointermove', function (e) {
                    var r = hero.getBoundingClientRect(); px = e.clientX - r.left; py = e.clientY - r.top;
                    if (!raf) raf = requestAnimationFrame(function () {
                        raf = 0;
                        cross.style.setProperty('--x', px + 'px'); cross.style.setProperty('--y', py + 'px');
                        label.textContent = Math.round(px / 24) + ' × ' + Math.round(py / 24);
                    });
                    cross.classList.add('on');
                });
                hero.addEventListener('pointerleave', function () { cross.classList.remove('on'); });
            }
        })();

        // Plotter motion: stations and legend rows appear once as they enter the view.
        (function () {
            var items = document.querySelectorAll('.nf-reveal, .nf-track');
            if (!('IntersectionObserver' in window)) { items.forEach(function (el) { el.classList.add('in'); }); return; }
            var io = new IntersectionObserver(function (entries) {
                entries.forEach(function (e) { if (e.isIntersecting) { e.target.classList.add('in'); io.unobserve(e.target); } });
            }, { rootMargin: '0px 0px -10% 0px' });
            items.forEach(function (el) { io.observe(el); });
        })();
    </script>
@endsection
