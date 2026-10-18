{{-- Answers, problem and files of a request. Expects $serviceRequest, $filesByQuestion and $fileRoute (download route name). --}}
@foreach ($serviceRequest->answers->groupBy('section_title') as $sectionTitle => $answers)
    @if ($sectionTitle)
        <h5 class="rf-answers-section">{{ $sectionTitle }}</h5>
    @endif
    <dl class="rf-answers">
        @foreach ($answers as $answer)
            <dt>{{ $answer->label }}</dt>
            <dd>
                @if ($answer->type === \App\Models\ServiceFormQuestion::TYPE_FILE)
                    @include('website.service-requests.partials.file-list', ['files' => $filesByQuestion[$answer->question_id] ?? collect(), 'fileRoute' => $fileRoute])
                @elseif ($answer->type === \App\Models\ServiceFormQuestion::TYPE_URL)
                    <a href="{{ $answer->value }}" target="_blank" rel="noopener nofollow" dir="ltr">{{ $answer->value }}</a>
                @else
                    {{ $answer->display() }}
                @endif
            </dd>
        @endforeach
    </dl>
@endforeach

<h5 class="rf-answers-section">شرح المشكلة أو ما ترغب بتنفيذه</h5>
<p class="rf-problem">{{ $serviceRequest->problem }}</p>

@if ($serviceRequest->isStaged() && $serviceRequest->stopped_project)
    <h5 class="rf-answers-section">مشروع متوقف: ما أُنجز قبل نفّذها</h5>
    <p class="rf-problem">{{ $serviceRequest->done_before }}</p>
    <p class="rf-muted">العروض على الجزء المتبقي فقط.</p>
@endif

@if (isset($filesByQuestion['attachments']))
    <h5 class="rf-answers-section">مرفقات المشروع</h5>
    @include('website.service-requests.partials.file-list', ['files' => $filesByQuestion['attachments'], 'fileRoute' => $fileRoute])
@endif
