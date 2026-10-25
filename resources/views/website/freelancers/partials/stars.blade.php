{{-- Five drawn stars for a rating ($value 0–5, halves rounded). --}}
@php $v = round(((float) ($value ?? 0)) * 2) / 2; @endphp
<span class="rf-stars" role="img" aria-label="التقييم {{ rtrim(rtrim(number_format($v, 1), '0'), '.') ?: 0 }} من 5">
    @for ($i = 1; $i <= 5; $i++)
        <i class="bi {{ $v >= $i ? 'bi-star-fill' : ($v >= $i - 0.5 ? 'bi-star-half' : 'bi-star') }}" aria-hidden="true"></i>
    @endfor
</span>
