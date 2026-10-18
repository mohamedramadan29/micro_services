{{-- One item (card, step, question…) of a section; $index is its key in the form. --}}
<div class="lp-item">
    <div class="lp-item-head">
        <strong>{{ $itemDef['label'] }} <span class="lp-item-num">{{ $number }}</span></strong>
        <span>
            <button type="button" class="btn btn-sm btn-light lp-item-up" title="لأعلى"><i class="fas fa-arrow-up"></i></button>
            <button type="button" class="btn btn-sm btn-light lp-item-down" title="لأسفل"><i class="fas fa-arrow-down"></i></button>
            <button type="button" class="btn btn-sm btn-light text-danger lp-item-remove" title="حذف"><i class="fas fa-times"></i></button>
        </span>
    </div>
    <div class="row">
        @foreach ($itemDef['fields'] as $key => $field)
            @include('admin.landing.partials.field', ['name' => "items[$index][$key]", 'value' => $item[$key] ?? ''])
        @endforeach
    </div>
</div>
