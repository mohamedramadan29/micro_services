{{-- Account side column header: photo, name, account type (and the freelancer's approval state). --}}
@php
    $me = Auth::user();
    $statusTone = [\App\Models\User::FREELANCER_APPROVED => 'ok', \App\Models\User::FREELANCER_PENDING => 'warn', \App\Models\User::FREELANCER_REJECTED => 'bad'];
@endphp
<div class="d-user-avater rf-acc-head">
    <img src="{{ $me->image ? $me->avatarUrl(128) : asset('assets/website/img/avatar.png') }}" class="img-fluid rounded" alt="">
    <h4>{{ $me->name }}</h4>
    <span dir="ltr">{{ $me->email }}</span>
    <div class="rf-acc-tags">
        <span class="rf-acc-tag">{{ $me->typeLabel() }}</span>
        @if ($me->isFreelancer())
            <span class="rf-acc-tag {{ $statusTone[$me->freelancer_status] ?? 'warn' }}">{{ \App\Models\User::FREELANCER_STATUSES[$me->freelancer_status] ?? 'قيد المراجعة' }}</span>
        @endif
    </div>
</div>
