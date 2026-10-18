@if ($f['title'])<div class="rf-lp-head"><h2>{{ $f['title'] }}</h2></div>@endif
<div class="rf-lp-stats">
    @foreach ($items as $stat)
        <div><strong>{{ $stat['value'] }}</strong><span>{{ $stat['label'] }}</span></div>
    @endforeach
</div>
