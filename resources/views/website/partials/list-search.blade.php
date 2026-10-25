{{--
    One search bar for list pages (works, ready services, a sub-service's services):
    the field, its icon and the button in one box; the sort as segmented links under it.
    $name (query key, default q), $placeholder, $keep (other query keys kept by the search), $sorts (key => label) or [], $sort (current).
--}}
@php
    $name = $name ?? 'q';
    $value = (string) request($name);
    $keep = array_filter(request()->only($keep ?? []), fn ($v) => $v !== null && $v !== '');
    $sorts = $sorts ?? [];
    $defaultSort = array_key_first($sorts);
@endphp
<div class="rf-ls">
    <form method="GET" class="rf-ls-bar" role="search">
        @foreach ($keep as $k => $v)
            @if (is_scalar($v))<input type="hidden" name="{{ $k }}" value="{{ $v }}">@endif
        @endforeach
        @if ($sorts && $sort !== $defaultSort)<input type="hidden" name="sort" value="{{ $sort }}">@endif
        <i class="bi bi-search" aria-hidden="true"></i>
        <input type="search" name="{{ $name }}" value="{{ $value }}" placeholder="{{ $placeholder }}" aria-label="{{ $placeholder }}" enterkeyhint="search">
        @if ($value !== '')
            <a class="rf-ls-clear" href="{{ request()->fullUrlWithQuery([$name => null, 'page' => null]) }}" aria-label="مسح البحث"><i class="bi bi-x-lg" aria-hidden="true"></i></a>
        @endif
        <button type="submit" class="rf-ls-go">بحث</button>
    </form>
    @if ($sorts)
        <div class="rf-ls-sort" role="group" aria-label="الترتيب">
            <span>الترتيب</span>
            @foreach ($sorts as $key => $label)
                <a href="{{ request()->fullUrlWithQuery(['sort' => $key === $defaultSort ? null : $key, 'page' => null]) }}"
                   class="{{ $sort === $key ? 'on' : '' }}" @if ($sort === $key) aria-current="true" @endif>{{ $label }}</a>
            @endforeach
        </div>
    @endif
</div>
