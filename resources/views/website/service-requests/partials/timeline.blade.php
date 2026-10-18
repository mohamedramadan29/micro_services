{{-- The request's path for the client: sent → open for offers → offers arrived → freelancer chosen → done (spec page 4). --}}
@php
    use App\Models\ServiceRequest as SR;
    $firstOffer = $serviceRequest->offers()->where('status', '!=', \App\Models\RequestOffer::STATUS_WITHDRAWN)->oldest('id')->first();
    $chosen = $serviceRequest->acceptedOffer;
    $closed = $serviceRequest->status === SR::STATUS_NOT_COMPLETED;
    $steps = [
        ['تم الإرسال', $serviceRequest->created_at],
        [$serviceRequest->isInquiry() ? 'استفسار خاص (غير منشور)' : 'مفتوح للعروض', $serviceRequest->published_at],
        ['وصلت عروض', $firstOffer?->created_at],
        ['تم اختيار مستقل', $chosen ? ($chosen->updated_at ?? $chosen->created_at) : null],
        [$serviceRequest->isStaged() ? 'اكتمل المشروع' : 'تم التنفيذ', $serviceRequest->completed_at],
    ];
@endphp
<ol class="rf-timeline {{ $closed ? 'rf-timeline-closed' : '' }}">
    @foreach ($steps as [$label, $at])
        <li class="{{ $at ? 'done' : '' }}">
            <span class="rf-timeline-dot"></span>
            <strong>{{ $label }}</strong>
            @if ($at)<small>{{ $at->format('Y-m-d') }}</small>@endif
        </li>
    @endforeach
    @if ($closed)
        <li class="done rf-timeline-end"><span class="rf-timeline-dot"></span><strong>لم يتم التنفيذ</strong>@if ($serviceRequest->closed_at)<small>{{ $serviceRequest->closed_at->format('Y-m-d') }}</small>@endif</li>
    @endif
</ol>
