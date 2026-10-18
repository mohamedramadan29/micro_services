@extends('admin.layouts.master')
@section('title')
    سياسة المشاريع
@endsection
@section('content')
    <div class="breadcrumb-header justify-content-between">
        <div class="my-auto">
            <div class="d-flex">
                <h4 class="content-title mb-0 my-auto">سياسة المشاريع</h4>
                <span class="text-muted mt-1 tx-13 mr-2 mb-0">/ «مشروعك ما بيوقف»: المهل والحدود وأسباب الخلاف وقوالب المراحل — للمدير فقط</span>
            </div>
        </div>
        <div class="my-auto d-flex" style="gap: 6px">
            <a href="{{ route('admin.content.index', ['page' => 'policy']) }}" class="btn btn-light">نص صفحة سياسة الخلافات</a>
            <a href="{{ route('admin.contracts.index') }}" class="btn btn-light"><i class="fas fa-arrow-right"></i> العقود</a>
        </div>
    </div>
    @include('admin.catalog.partials.flash')
    <p class="text-muted small">التغيير يسري على ما يحدث بعد الحفظ (مهلة مراجعة جديدة، خلاف جديد…)؛ المواعيد المحسوبة سابقًا لا تتغير. حدّث نص صفحة «سياسة الخلافات» إن غيّرت أرقامًا مذكورة فيها.</p>

    <form method="POST" action="{{ route('admin.contract-policy.save') }}">
        @csrf
        <div class="row row-sm">
            <div class="col-lg-5">
                <div class="card">
                    <div class="card-header"><h6 class="mb-0">المهل والحدود</h6></div>
                    <div class="card-body">
                        @foreach ($numbers as $key => $number)
                            <div class="form-group d-flex align-items-center justify-content-between" style="gap: 10px">
                                <label class="mb-0" for="n-{{ $key }}">{{ $number['label'] }}</label>
                                <input type="number" id="n-{{ $key }}" name="numbers[{{ $key }}]" min="{{ $number['min'] }}" max="{{ $number['max'] }}" class="form-control" style="max-width: 100px" value="{{ old('numbers.' . $key, $number['value']) }}">
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="card">
                    <div class="card-header"><h6 class="mb-0">أسباب الخلاف</h6></div>
                    <div class="card-body">
                        @php $reasonRows = collect($reasons)->map(fn ($label, $key) => ['key' => $key, 'label' => $label])->values()->push(['key' => '', 'label' => '']); @endphp
                        @foreach ($reasonRows as $i => $reason)
                            <div class="d-flex align-items-center mb-1" style="gap: 6px">
                                <input type="hidden" name="reasons[{{ $i }}][key]" value="{{ $reason['key'] }}">
                                <input type="text" name="reasons[{{ $i }}][label]" class="form-control" value="{{ $reason['label'] }}" placeholder="{{ $reason['key'] === '' ? 'سبب جديد…' : '' }}">
                                @if ($reason['key'] !== '')<label class="mb-0 text-danger small text-nowrap"><input type="checkbox" name="reasons[{{ $i }}][delete]" value="1"> حذف</label>@endif
                            </div>
                        @endforeach
                        <p class="small text-muted mb-0 mt-2">الخلافات القديمة تحتفظ باسم سببها حتى لو حذفته.</p>
                    </div>
                </div>
            </div>

            <div class="col-lg-7">
                <div class="card">
                    <div class="card-header"><h6 class="mb-0">قوالب المراحل</h6></div>
                    <div class="card-body">
                        <p class="small text-muted">يختار المستقل قالبًا عند كتابة الاتفاق، فيُقسَّم سعر العرض ومدته على المراحل بالنِّسب. القالب الأول هو ما يبدأ به الاتفاق. كل مرحلة في سطر: <code>العنوان | ما يُسلَّم | النسبة | معيار القبول (اختياري)</code>، ومجموع النِّسب 100. اترك الاسم فارغًا لحذف القالب.</p>
                        @php $templateRows = array_merge($templates, [['name' => '', 'stages' => []]]); @endphp
                        @foreach ($templateRows as $i => $template)
                            <div class="border rounded p-2 mb-2 {{ $template['name'] === '' ? 'bg-light' : '' }}">
                                <input type="text" name="templates[{{ $i }}][name]" class="form-control mb-1" value="{{ old('templates.' . $i . '.name', $template['name']) }}" placeholder="{{ $template['name'] === '' ? 'قالب جديد…' : 'اسم القالب' }}">
                                <textarea name="templates[{{ $i }}][stages]" rows="3" class="form-control" dir="rtl">{{ old('templates.' . $i . '.stages', collect($template['stages'])->map(fn ($s) => $s['title'] . ' | ' . ($s['deliverable'] ?? '') . ' | ' . rtrim(rtrim(number_format((float) $s['share'], 2, '.', ''), '0'), '.') . (! empty($s['acceptance']) ? ' | ' . $s['acceptance'] : ''))->implode("\n")) }}</textarea>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
        <button class="btn btn-primary">حفظ سياسة المشاريع</button>
    </form>

    <div class="card mt-3">
        <div class="card-header"><h6 class="mb-0">سجل التعديلات</h6></div>
        <div class="card-body">
            @forelse ($history as $change)
                <div class="border-bottom py-1 small">
                    <strong>{{ $change->created_at->format('Y-m-d H:i') }} · {{ $change->user?->name ?? '—' }}</strong>
                    @foreach ($change->changes as $key => [$old, $new])
                        <div>{{ \App\Services\Contracts\ContractPolicy::NUMBERS[$key]['label'] ?? ['dispute_reasons' => 'أسباب الخلاف', 'stage_templates' => 'قوالب المراحل'][$key] ?? $key }}:
                            @if (is_array($new)) تغيّرت @else {{ $old }} ← {{ $new }} @endif</div>
                    @endforeach
                </div>
            @empty
                <p class="text-muted mb-0">لا تعديلات بعد.</p>
            @endforelse
        </div>
    </div>
@endsection
