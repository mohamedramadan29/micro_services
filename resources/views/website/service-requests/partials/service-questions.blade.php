{{-- The sections and questions of one service form. Expects $form with sections.questions loaded. --}}
@foreach ($form->sections as $section)
    @continue($section->questions->isEmpty())
    <fieldset class="rf-section">
        <legend class="rf-section-title">{{ $section->title }}</legend>
        @if ($section->description)
            <p class="rf-section-desc">{{ $section->description }}</p>
        @endif
        @foreach ($section->questions as $question)
            @include('website.service-requests.partials.question', ['question' => $question])
        @endforeach
    </fieldset>
@endforeach
