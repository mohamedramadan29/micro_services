{{-- Drawn icons of the «الرسومات التنفيذية» world (ink strokes .s, one green approval stroke .g). Keys: ContentAreas::TRUST_ICONS. --}}
<svg viewBox="0 0 52 52" aria-hidden="true">
    @switch($name ?? 'lock')
        @case('compass')
            <circle class="s" cx="26" cy="26" r="17"/><path class="s" d="M26 9v5M26 38v5M9 26h5M38 26h5"/><path class="i" d="M21 31l3-8 8-3-3 8z"/>
            @break
        @case('stages')
            <path class="s" d="M6 34h40"/><circle class="s" cx="40" cy="34" r="4"/><circle class="s" cx="26" cy="34" r="4"/><path class="i" d="M8 34a4 4 0 1 0 8 0a4 4 0 1 0-8 0"/><path class="s" d="M12 24V14h28v10"/>
            @break
        @case('vetted')
            <circle class="s" cx="26" cy="19" r="8"/><path class="s" d="M11 44c2-8 8-12 15-12s13 4 15 12"/><path class="i" d="M33 11l3 3 6-6"/>
            @break
        @case('shield')
            <path class="s" d="M26 7l15 6v11c0 10-6.5 17-15 21-8.5-4-15-11-15-21V13z"/><path class="g" d="M19 26l5 5 9-10"/>
            @break
        @case('chat')
            <path class="s" d="M9 12h34v22H24l-9 8v-8H9z"/><path class="i" d="M17 21h18M17 27h11"/>
            @break
        @case('star')
            <path class="s" d="M26 8l5.4 11 12.1 1.8-8.8 8.5 2.1 12L26 35.6 15.2 41.3l2.1-12-8.8-8.5 12.1-1.8z"/>
            @break
        @case('clock')
            <circle class="s" cx="26" cy="27" r="17"/><path class="i" d="M26 17v10l7 5"/><path class="s" d="M20 6h12"/>
            @break
        @default
            <rect class="s" x="10" y="22" width="32" height="22" rx="3"/><path class="s" d="M17 22v-6a9 9 0 0 1 18 0v6"/><path class="g" d="M20 33l4.5 4.5L33 29"/>
    @endswitch
</svg>
