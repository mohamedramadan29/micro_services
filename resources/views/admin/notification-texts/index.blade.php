@extends('admin.layouts.master')
@section('title')
    نصوص الإشعارات
@endsection
@section('content')
    <div class="breadcrumb-header justify-content-between">
        <div class="my-auto">
            <div class="d-flex">
                <h4 class="content-title mb-0 my-auto">نصوص الإشعارات</h4>
                <span class="text-muted mt-1 tx-13 mr-2 mb-0">/ {{ $total }} رسالة — الجرس وعنوان البريد وسطره</span>
            </div>
        </div>
    </div>
    @include('admin.catalog.partials.flash')
    <div class="alert alert-light">
        اكتب النص الجديد في المربع؛ اتركه فارغًا ليُستخدم النص الأصلي. استخدم المتغيرات المكتوبة تحت كل رسالة فقط (مثل <code>{number}</code>)، وتُستبدل تلقائيًا عند الإرسال.
        الرسائل التي أُرسلت سابقًا لا تتغير.
    </div>
    <form method="GET" class="d-flex flex-wrap mb-3" style="gap: 6px">
        <select name="group" class="form-control" style="max-width: 260px">
            <option value="">كل الأقسام</option>
            @foreach ($groups as $key => $label)<option value="{{ $key }}" @selected($group === $key)>{{ $label }}</option>@endforeach
        </select>
        <input type="search" name="q" value="{{ request('q') }}" class="form-control" style="max-width: 260px" placeholder="ابحث في النصوص">
        <button class="btn btn-light">عرض</button>
    </form>

    <form method="POST" action="{{ route('admin.notification-texts.save') }}">
        @csrf
        @foreach ($messages as $groupKey => $items)
            <div class="card">
                <div class="card-header"><h6 class="mb-0">{{ $groups[$groupKey] ?? $groupKey }} ({{ $items->count() }})</h6></div>
                <div class="card-body">
                    @foreach ($items as $key => $message)
                        <div class="border-bottom pb-3 mb-3">
                            <div class="d-flex justify-content-between flex-wrap">
                                <strong>{{ $message['label'] }}</strong>
                                @if (isset($overrides[$key]))<span class="badge badge-warning">معدّل</span>@endif
                            </div>
                            <div class="small text-muted mb-1">النص الأصلي: {{ $message['text'] }}</div>
                            <textarea name="texts[{{ $key }}]" rows="2" maxlength="500" class="form-control" placeholder="{{ $message['text'] }}">{{ old('texts.' . $key, $overrides[$key] ?? '') }}</textarea>
                            @if (! empty($message['vars']))
                                <div class="small mt-1">المتغيرات:
                                    @foreach ($message['vars'] as $var => $meaning)<code class="ml-2" title="{{ $meaning }}">{{ '{' . $var . '}' }}</code> {{ $meaning }}@if (! $loop->last) · @endif @endforeach
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
        <button class="btn btn-primary">حفظ النصوص</button>
    </form>
@endsection
