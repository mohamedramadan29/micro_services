{{--
    Service 3: a staged project after the client chose an offer. Nothing is paid at once:
    the agreement (stages, dates, amounts) comes first, then each stage is funded on its own.
    Expects $serviceRequest and $viewer ('client' | 'freelancer').
--}}
@php
    $contract = $serviceRequest->contract;
    $offer = $serviceRequest->acceptedOffer;
    $agreementDone = $contract && $contract->status !== \App\Models\Contract::STATUS_AGREEMENT;
    $firstFunded = $contract && $contract->stages()->whereNotIn('status', [\App\Models\ContractStage::STATUS_UNFUNDED, \App\Models\ContractStage::STATUS_FUNDING])->exists();
    $steps = [
        ['تم اختيار المستقل', true],
        ['الاتفاق على المراحل', $agreementDone],
        ['تمويل المرحلة الأولى', $firstFunded],
        ['التنفيذ مرحلة بمرحلة', $contract?->status === \App\Models\Contract::STATUS_COMPLETED],
    ];
@endphp
<ol class="rf-steps" aria-label="مراحل المشروع">
    @foreach ($steps as [$label, $done])
        <li class="{{ $done ? 'done' : '' }}">{{ $label }}</li>
    @endforeach
</ol>
<div class="rf-notice rf-notice-success">
    @if ($contract?->status === \App\Models\Contract::STATUS_COMPLETED)
        <p class="mb-0"><strong>اكتمل المشروع {{ $contract->number }}</strong>: اعتُمدت كل المراحل.</p>
    @elseif ($agreementDone)
        <p class="mb-0"><strong>المشروع {{ $contract->number }} قيد التنفيذ مرحلة بمرحلة.</strong>
            {{ $viewer === 'client' ? 'موّل المراحل وراجع التسليمات واعتمدها من صفحة المشروع.' : 'سلّم المرحلة الحالية من صفحة المشروع بعد تمويلها.' }}</p>
    @elseif ($viewer === 'client')
        <p class="mb-1"><strong>اخترت {{ $offer?->freelancer?->name }}</strong> لمشروعك {{ $contract?->number }}، بعرض {{ \App\Support\Money::show($offer?->price) }} خلال {{ $offer?->duration_days }} يوم.</p>
        <p class="mb-0">الخطوة التالية: يقترح المستقل الاتفاق (النطاق والمراحل ومواعيدها ومبالغها)، فتراجعه وتوافق أو تطلب تعديلًا. لا تدفع شيئًا قبل الاتفاق، ثم تموّل المرحلة الأولى فقط، ويبقى المبلغ لدى نفّذها حتى تعتمدها.</p>
    @else
        <p class="mb-1"><strong>اختار العميل عرضك</strong> على المشروع {{ $contract?->number }} ({{ \App\Support\Money::show($offer?->price) }}).</p>
        <p class="mb-0">الخطوة التالية: تقترح الاتفاق ومراحل الدفع والتسليم ليوافق عليها العميل. يبدأ العمل بعد الموافقة وتمويل المرحلة الأولى، ويبقى مبلغ كل مرحلة لدى نفّذها حتى يعتمدها العميل.</p>
    @endif
</div>
@if ($offer)
    <div class="rf-offer-actions">
        @if ($contract)
            <a href="{{ route('contracts.show', $contract) }}" class="btn rf-btn-accept"><i class="bi bi-file-earmark-text"></i>
                {{ $agreementDone ? 'صفحة المشروع والمراحل' : ($viewer === 'freelancer' ? 'اقترح الاتفاق والمراحل' : 'صفحة الاتفاق') }}</a>
        @endif
        <a href="{{ route('offers.chat', $offer) }}" class="rf-link-btn"><i class="bi bi-chat-dots"></i> {{ $viewer === 'client' ? 'محادثة المستقل' : 'محادثة العميل' }}</a>
    </div>
@endif
