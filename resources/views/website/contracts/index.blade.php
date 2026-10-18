@extends('website.layouts.master')
@section('title')
    مشاريعي
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
                                <li class="breadcrumb-item active" aria-current="page"> مشاريعي </li>
                            </ol>
                        </nav>
                    </div>

                    <div class="_dashboard_content">
                        <div class="_dashboard_content_header">
                            <div class="_dashboard__header_flex">
                                <h4><i class="bi bi-kanban-fill mr-1"></i> مشاريعي (مشروعك ما بيوقف)</h4>
                                @if (Auth::user()->isClient())
                                    <a href="{{ route('requests.staged') }}" class="btn rf-link-btn"><i class="bi bi-plus-lg"></i> مشروع جديد</a>
                                @endif
                            </div>
                        </div>
                        <div class="_dashboard_content_body">
                            <div class="rf-tabs mb-3">
                                <a href="{{ route('contracts.index', ['tab' => 'active']) }}" class="rf-tab {{ $tab === 'active' ? 'active' : '' }}">الجارية ({{ $counts['active'] }})</a>
                                <a href="{{ route('contracts.index', ['tab' => 'done']) }}" class="rf-tab {{ $tab === 'done' ? 'active' : '' }}">المنتهية ({{ $counts['done'] }})</a>
                            </div>

                            @if ($contracts->isEmpty())
                                <div class="rf-empty-state">
                                    <p>{{ $tab === 'active' ? 'لا توجد مشاريع جارية.' : 'لا توجد مشاريع منتهية بعد.' }}</p>
                                </div>
                            @else
                                <div class="rf-request-list">
                                    @foreach ($contracts as $contract)
                                        @php
                                            $isClient = (int) $contract->client_id === (int) Auth::id();
                                            $other = $isClient ? $contract->freelancer : $contract->client;
                                            $settled = $contract->stages->filter(fn ($s) => $s->isSettled())->count();
                                            $step = $contract->nextStep(Auth::user());
                                        @endphp
                                        <a class="rf-request-row" href="{{ route('contracts.show', $contract) }}">
                                            <div>
                                                <strong>{{ $contract->title }}</strong>
                                                <span class="rf-muted">
                                                    {{ $contract->number }} · {{ $isClient ? 'المستقل' : 'العميل' }}: {{ $other?->name }}
                                                    @if ($contract->stages->isNotEmpty()) · المراحل المعتمدة {{ $settled }} من {{ $contract->stages->count() }} · {{ \App\Support\Money::show($contract->stages->sum('amount')) }}@endif
                                                </span>
                                                @if ($step)
                                                    <p class="rf-request-snippet {{ $step[1] ? 'rf-your-turn' : '' }}">
                                                        @if ($step[1])<i class="bi bi-arrow-left-circle-fill"></i>@endif {{ $step[0] }}
                                                    </p>
                                                @endif
                                            </div>
                                            <span class="rf-status rf-status-{{ $contract->status }}">{{ $contract->statusLabel() }}</span>
                                        </a>
                                    @endforeach
                                </div>
                                <div class="mt-3">{{ $contracts->links() }}</div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
