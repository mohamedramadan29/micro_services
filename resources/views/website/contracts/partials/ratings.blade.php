{{-- Service 3, phase 6: optional ratings after completion. Expects $contract (ratings loaded), $isClient, $isFreelancer. --}}
@php
    use App\Models\ContractRating as R;
    $mine = $contract->ratings->firstWhere('rater_id', auth()->id());
    $byClient = $contract->ratings->firstWhere('direction', R::CLIENT_TO_FREELANCER);
    $byFreelancer = $contract->ratings->firstWhere('direction', R::FREELANCER_TO_CLIENT);
@endphp
<div class="rf-rating mt-3" id="rating">
    <h2 class="rf-section-title">التقييم</h2>
    @if (($isClient || $isFreelancer) && ! $mine)
        <form method="POST" action="{{ route('contracts.rate', $contract) }}">
            @csrf
            <p class="mb-1">{{ $isClient ? 'كيف كانت تجربتك مع ' . $contract->freelancer?->name . '؟ يظهر تقييمك في صفحته.' : 'كيف كانت تجربتك مع العميل؟ يراه المستقلون والإدارة فقط.' }}</p>
            <div class="rf-stars" role="radiogroup" aria-label="عدد النجوم">
                @for ($i = 5; $i >= 1; $i--)
                    <input type="radio" id="star-{{ $i }}" name="stars" value="{{ $i }}" @checked((int) old('stars') === $i)><label for="star-{{ $i }}" title="{{ $i }}">★</label>
                @endfor
            </div>
            @error('stars')<div class="rf-field-error">{{ $message }}</div>@enderror
            <textarea name="comment" rows="2" maxlength="1000" class="form-control mb-2" placeholder="رأيك (اختياري)">{{ old('comment') }}</textarea>
            <button class="btn rf-btn-primary">إرسال التقييم</button>
        </form>
    @endif
    @if ($byClient && ($isClient || $isFreelancer || $isAdmin))
        <p class="mb-1"><strong>تقييم العميل للمستقل:</strong> {{ str_repeat('★', $byClient->stars) }} @if ($byClient->comment)— {{ $byClient->comment }}@endif</p>
    @endif
    {{-- The freelancer's rating of the client is never shown to the client. --}}
    @if ($byFreelancer && ($isFreelancer || $isAdmin))
        <p class="mb-0"><strong>تقييم المستقل للعميل:</strong> {{ str_repeat('★', $byFreelancer->stars) }} @if ($byFreelancer->comment)— {{ $byFreelancer->comment }}@endif</p>
    @endif
</div>
