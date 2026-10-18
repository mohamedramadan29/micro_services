@include('website.landing.sections._head')
<div class="rf-lp-faq">
    @foreach ($items as $item)
        <details @if ($loop->first) open @endif>
            <summary>{{ $item['question'] }}</summary>
            <p>{!! nl2br(e($item['answer'])) !!}</p>
        </details>
    @endforeach
</div>
