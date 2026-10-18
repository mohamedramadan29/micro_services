{{-- One dynamic question. Inputs are named answers[<id>] (files: answer_files[<id>][]). --}}
@php
    use App\Models\ServiceFormQuestion as Q;
    $id = $question->id;
    $name = "answers[$id]";
    $old = old("answers.$id");
    $inputId = "q-$id";
    $helpId = $question->help_text ? "q-$id-help" : null;
    // Edit page: a file question that already has files is not required again.
    $keptFiles = isset($existingFiles) ? $existingFiles->get($id) : null;
@endphp
<div class="rf-field" data-question-id="{{ $id }}" data-type="{{ $question->type }}"
    @if ($question->condition) data-condition='@json($question->condition)' @endif
    @if ($question->is_required && ! ($keptFiles && $keptFiles->isNotEmpty())) data-required="1" @endif>

    @if (in_array($question->type, [Q::TYPE_SINGLE_CHOICE, Q::TYPE_MULTI_CHOICE, Q::TYPE_YES_NO, Q::TYPE_RATING, Q::TYPE_NUMBER], true) && ! ($question->type === Q::TYPE_NUMBER && $question->setting('mode') !== 'range'))
        <span class="rf-field-label" id="{{ $inputId }}-label">
            {{ $question->label }} @if ($question->is_required)<span class="text-danger">*</span>@endif
        </span>
    @else
        <label class="rf-field-label" for="{{ $inputId }}">
            {{ $question->label }} @if ($question->is_required)<span class="text-danger">*</span>@endif
        </label>
    @endif
    @if ($question->help_text)
        <small class="rf-field-help" id="{{ $helpId }}">{{ $question->help_text }}</small>
    @endif

    @switch($question->type)
        @case(Q::TYPE_SHORT_TEXT)
        @case(Q::TYPE_LOCATION)
            <input type="text" class="form-control" id="{{ $inputId }}" name="{{ $name }}" value="{{ $old }}"
                maxlength="{{ $question->setting('max_length', 255) }}"
                @if ($question->setting('min_length')) minlength="{{ $question->setting('min_length') }}" @endif
                placeholder="{{ $question->placeholder }}" @if ($helpId) aria-describedby="{{ $helpId }}" @endif>
        @break

        @case(Q::TYPE_LONG_TEXT)
            <textarea class="form-control" id="{{ $inputId }}" name="{{ $name }}" rows="4"
                maxlength="{{ $question->setting('max_length', 5000) }}"
                @if ($question->setting('min_length')) minlength="{{ $question->setting('min_length') }}" @endif
                placeholder="{{ $question->placeholder }}" @if ($helpId) aria-describedby="{{ $helpId }}" @endif>{{ $old }}</textarea>
        @break

        @case(Q::TYPE_URL)
            <input type="url" class="form-control" id="{{ $inputId }}" name="{{ $name }}" value="{{ $old }}" dir="ltr"
                placeholder="{{ $question->placeholder ?: 'https://' }}" @if ($helpId) aria-describedby="{{ $helpId }}" @endif>
        @break

        @case(Q::TYPE_DATE)
            <input type="date" class="form-control" id="{{ $inputId }}" name="{{ $name }}" value="{{ $old }}"
                @if ($helpId) aria-describedby="{{ $helpId }}" @endif>
        @break

        @case(Q::TYPE_NUMBER)
            @if ($question->setting('mode') === 'range')
                <div class="rf-range" role="group" aria-labelledby="{{ $inputId }}-label">
                    <input type="number" step="any" class="form-control" name="{{ $name }}[min]" value="{{ $old['min'] ?? '' }}"
                        placeholder="من" aria-label="من"
                        @if (! is_null($question->setting('min'))) min="{{ $question->setting('min') }}" @endif
                        @if (! is_null($question->setting('max'))) max="{{ $question->setting('max') }}" @endif>
                    <input type="number" step="any" class="form-control" name="{{ $name }}[max]" value="{{ $old['max'] ?? '' }}"
                        placeholder="إلى" aria-label="إلى"
                        @if (! is_null($question->setting('min'))) min="{{ $question->setting('min') }}" @endif
                        @if (! is_null($question->setting('max'))) max="{{ $question->setting('max') }}" @endif>
                    @if ($question->setting('unit'))
                        <span class="rf-unit">{{ $question->setting('unit') }}</span>
                    @endif
                </div>
            @else
                <div class="rf-range">
                    <input type="number" step="any" class="form-control" id="{{ $inputId }}" name="{{ $name }}" value="{{ $old }}"
                        placeholder="{{ $question->placeholder }}"
                        @if (! is_null($question->setting('min'))) min="{{ $question->setting('min') }}" @endif
                        @if (! is_null($question->setting('max'))) max="{{ $question->setting('max') }}" @endif>
                    @if ($question->setting('unit'))
                        <span class="rf-unit">{{ $question->setting('unit') }}</span>
                    @endif
                </div>
            @endif
        @break

        @case(Q::TYPE_DROPDOWN)
            <select class="form-control" id="{{ $inputId }}" name="{{ $name }}" @if ($helpId) aria-describedby="{{ $helpId }}" @endif>
                <option value="">-- اختر --</option>
                @foreach ($question->options ?? [] as $option)
                    <option value="{{ $option }}" @selected($old === $option)>{{ $option }}</option>
                @endforeach
            </select>
        @break

        @case(Q::TYPE_SINGLE_CHOICE)
        @case(Q::TYPE_YES_NO)
            <div class="rf-choices" role="radiogroup" aria-labelledby="{{ $inputId }}-label">
                @foreach ($question->choiceValues() as $index => $option)
                    <div class="rf-choice">
                        <input type="radio" id="{{ $inputId }}-{{ $index }}" name="{{ $name }}" value="{{ $option }}" @checked($old === $option)>
                        <label for="{{ $inputId }}-{{ $index }}">{{ $option }}</label>
                    </div>
                @endforeach
            </div>
        @break

        @case(Q::TYPE_MULTI_CHOICE)
            <div class="rf-choices" role="group" aria-labelledby="{{ $inputId }}-label">
                @foreach ($question->options ?? [] as $index => $option)
                    <div class="rf-choice">
                        <input type="checkbox" id="{{ $inputId }}-{{ $index }}" name="{{ $name }}[]" value="{{ $option }}"
                            @checked(in_array($option, (array) $old, true))>
                        <label for="{{ $inputId }}-{{ $index }}">{{ $option }}</label>
                    </div>
                @endforeach
            </div>
        @break

        @case(Q::TYPE_RATING)
            <div class="rf-rating" role="radiogroup" aria-labelledby="{{ $inputId }}-label">
                @if ($question->setting('min_label'))
                    <span class="rf-rating-end">{{ $question->setting('min_label') }}</span>
                @endif
                @for ($i = 1; $i <= $question->setting('scale', 5); $i++)
                    <input type="radio" class="rf-rating-input" id="{{ $inputId }}-{{ $i }}" name="{{ $name }}" value="{{ $i }}" @checked((string) $old === (string) $i)>
                    <label class="rf-rating-step" for="{{ $inputId }}-{{ $i }}">{{ $i }}</label>
                @endfor
                @if ($question->setting('max_label'))
                    <span class="rf-rating-end">{{ $question->setting('max_label') }}</span>
                @endif
            </div>
        @break

        @case(Q::TYPE_FILE)
            @include('website.service-requests.partials.existing-files', ['files' => $keptFiles])
            @php($extensions = collect(explode(',', $question->setting('extensions', '')))->filter()->map(fn ($ext) => '.' . $ext)->implode(','))
            <input type="file" class="form-control" id="{{ $inputId }}" name="answer_files[{{ $id }}][]"
                @if ($question->setting('max_files', 1) > 1) multiple @endif accept="{{ $extensions }}"
                data-max-files="{{ $question->setting('max_files', 1) }}" data-max-size-mb="{{ $question->setting('max_size_mb', 5) }}">
            <small class="rf-field-help">
                حتى {{ $question->setting('max_files', 1) }} ملف، {{ $question->setting('max_size_mb', 5) }} ميجابايت لكل ملف ·
                <span dir="ltr">{{ str_replace(',', ', ', $question->setting('extensions')) }}</span>
            </small>
        @break
    @endswitch

    @error("answers.$id")
        <div class="rf-field-error">{{ $message }}</div>
    @enderror
    @error("answers.$id.*")
        <div class="rf-field-error">{{ $message }}</div>
    @enderror
    @error("answer_files.$id")
        <div class="rf-field-error">{{ $message }}</div>
    @enderror
    @error("answer_files.$id.*")
        <div class="rf-field-error">{{ $message }}</div>
    @enderror
</div>
