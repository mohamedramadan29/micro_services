@extends('admin.layouts.master')
@php use App\Services\Content\ContentAreas; @endphp
@section('title')
    إدارة محتوى الواجهة
@endsection
@section('css')
    <style>
        .ct-tabs { display: flex; flex-wrap: wrap; gap: 6px; margin-bottom: 16px; }
        .ct-tabs a { padding: 8px 16px; border-radius: 20px; border: 1px solid #dfe5ec; color: #4a5562; }
        .ct-tabs a.active { background: #3FB699; border-color: #3FB699; color: #fff; }
        .ct-block { border: 1px solid #e6ebf0; border-radius: 10px; padding: 14px; margin-bottom: 14px; background: #fff; }
        .ct-block.ct-hidden { opacity: .6; }
        .ct-block.ct-deleted { opacity: .45; background: #fff5f5; }
        .ct-block-head { display: flex; justify-content: space-between; align-items: center; gap: 8px; margin-bottom: 10px; flex-wrap: wrap; }
        .ct-actions { display: flex; gap: 4px; flex-wrap: wrap; }
        .ct-actions form { margin: 0; }
        .ct-thumb { max-width: 120px; max-height: 70px; border-radius: 6px; border: 1px solid #e6ebf0; margin-top: 6px; object-fit: cover; }
        .ct-preview-frame { width: 100%; height: 680px; border: 1px solid #e6ebf0; border-radius: 10px; background: #fff; }
        .ct-muted { color: #6b7785; font-size: 12px; }
    </style>
@endsection
@section('content')
    <div class="breadcrumb-header justify-content-between">
        <div class="my-auto">
            <div class="d-flex">
                <h4 class="content-title mb-0 my-auto">إدارة محتوى الواجهة</h4>
                <span class="text-muted mt-1 tx-13 mr-2 mb-0">/ النصوص والروابط والصور بدون الرجوع للمبرمج</span>
            </div>
        </div>
        <div class="my-auto">
            <a href="{{ route('admin.media.index') }}" class="btn btn-light"><i class="fas fa-images"></i> مكتبة الصور والخلفيات</a>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger">
            @foreach ($errors->all() as $error)<div>{{ $error }}</div>@endforeach
        </div>
    @endif

    <nav class="ct-tabs">
        @foreach (ContentAreas::PAGES as $key => $meta)
            <a href="{{ route('admin.content.index', ['page' => $key]) }}" class="{{ $page === $key ? 'active' : '' }}">{{ $meta['label'] }}</a>
        @endforeach
    </nav>

    <div class="card">
        <div class="card-body d-flex justify-content-between align-items-center flex-wrap" style="gap: 8px">
            <div>
                @if ($hasDrafts)
                    <strong class="text-warning"><i class="fas fa-pen"></i> توجد تعديلات غير منشورة</strong>
                    <div class="ct-muted">الزوار يرون النسخة المنشورة حتى تضغط «نشر».</div>
                @else
                    <strong class="text-success"><i class="fas fa-check"></i> كل التعديلات منشورة</strong>
                @endif
            </div>
            <div class="d-flex" style="gap: 6px">
                <a href="{{ route('admin.content.preview', $page) }}" target="_blank" class="btn btn-outline-primary"><i class="fas fa-eye"></i> معاينة في الموقع</a>
                @if ($access === 'view')
                    <span class="badge badge-light align-self-center">دورك: اطلاع فقط</span>
                @elseif ($access === 'edit')
                    <span class="badge badge-light align-self-center">محرر محتوى: التعديلات مسودة حتى ينشرها المدير</span>
                @endif
                @if ($hasDrafts && $access !== 'view')
                    <form method="POST" action="{{ route('admin.content.discard', $page) }}" onsubmit="return confirm('تجاهل كل التعديلات غير المنشورة في هذا القسم؟')">
                        @csrf
                        <button class="btn btn-light">تجاهل التعديلات</button>
                    </form>
                    @if ($access === 'publish')
                        <form method="POST" action="{{ route('admin.content.publish', $page) }}" onsubmit="return confirm('نشر التعديلات للزوار الآن؟')">
                            @csrf
                            <button class="btn btn-success"><i class="fas fa-upload"></i> نشر</button>
                        </form>
                    @endif
                @endif
            </div>
        </div>
    </div>

    <div class="row row-sm">
        <div class="col-xl-7">
            @foreach ($areas as $area => $info)
                @php $definition = $info['definition']; @endphp
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h6 class="mb-0">{{ $definition['label'] }}</h6>
                        @if (! empty($definition['repeatable']) && ($access === 'publish' || ($access === 'edit' && empty($definition['sensitive']))))
                            <form method="POST" action="{{ route('admin.content.store', $area) }}">
                                @csrf
                                <button class="btn btn-sm btn-primary"><i class="fas fa-plus"></i> إضافة {{ $definition['item'] ?? 'عنصر' }}</button>
                            </form>
                        @endif
                    </div>
                    <div class="card-body">
                        @foreach ($info['blocks'] as $index => $block)
                            @php
                                $fields = $block->working();
                                $deleted = $block->isDeletedInDraft();
                                $visible = $fields['_visible'] ?? true;
                            @endphp
                            <div class="ct-block {{ $deleted ? 'ct-deleted' : (! $visible ? 'ct-hidden' : '') }}" id="block-{{ $block->id }}">
                                <div class="ct-block-head">
                                    <div>
                                        <strong>{{ ! empty($definition['repeatable']) ? ($definition['item'] ?? 'عنصر') . ' ' . ($index + 1) : $definition['label'] }}</strong>
                                        @if ($deleted)
                                            <span class="badge badge-danger">سيُحذف عند النشر</span>
                                        @elseif ($block->isNew())
                                            <span class="badge badge-info">جديد — غير منشور</span>
                                        @elseif ($block->hasDraft())
                                            <span class="badge badge-warning">مسودة</span>
                                        @endif
                                        @if (! $deleted && ! $visible)
                                            <span class="badge badge-secondary">مخفي</span>
                                        @endif
                                    </div>
                                    @unless ($deleted || $access === 'view')
                                        <div class="ct-actions">
                                            @if (! empty($definition['repeatable']))
                                                <form method="POST" action="{{ route('admin.content.move', [$block, 'up']) }}">@csrf<button class="btn btn-sm btn-light" title="لأعلى" @disabled($index === 0)><i class="fas fa-arrow-up"></i></button></form>
                                                <form method="POST" action="{{ route('admin.content.move', [$block, 'down']) }}">@csrf<button class="btn btn-sm btn-light" title="لأسفل" @disabled($loop->last)><i class="fas fa-arrow-down"></i></button></form>
                                            @endif
                                            <form method="POST" action="{{ route('admin.content.toggle', $block) }}">@csrf<button class="btn btn-sm btn-light">{{ $visible ? 'إخفاء' : 'إظهار' }}</button></form>
                                            @if (! empty($definition['repeatable']) && ($access === 'publish' || empty($definition['sensitive'])))
                                                <form method="POST" action="{{ route('admin.content.destroy', $block) }}" onsubmit="return confirm('حذف هذا العنصر؟ يُحذف من الموقع عند النشر.')">@csrf<button class="btn btn-sm btn-outline-danger">حذف</button></form>
                                            @endif
                                        </div>
                                    @endunless
                                </div>

                                @unless ($deleted)
                                    <form method="POST" action="{{ route('admin.content.update', $block) }}">
                                        @csrf
                                        <div class="row">
                                            @foreach ($definition['fields'] as $key => $field)
                                                @php
                                                    $value = old('_block') == $block->id ? old($key, $fields[$key] ?? '') : ($fields[$key] ?? '');
                                                    $locked = $access === 'view' || ($access === 'edit' && in_array($key, $definition['sensitive'] ?? [], true));
                                                @endphp
                                                <div class="form-group {{ $field['type'] === 'textarea' ? 'col-12' : 'col-md-6' }}">
                                                    <label>{{ $field['label'] }} @if (! empty($field['required']))<span class="text-danger">*</span>@endif</label>
                                                    @switch($field['type'])
                                                        @case('textarea')
                                                            <textarea @disabled($locked) name="{{ $key }}" rows="2" class="form-control">{{ $value }}</textarea>
                                                            @break
                                                        @case('select')
                                                            <select @disabled($locked) name="{{ $key }}" class="form-control">
                                                                @foreach ($field['options'] as $optionKey => $optionLabel)
                                                                    <option value="{{ $optionKey }}" @selected((string) $value === (string) $optionKey)>{{ $optionLabel }}</option>
                                                                @endforeach
                                                            </select>
                                                            @break
                                                        @case('color')
                                                            <div class="d-flex" style="gap: 6px">
                                                                <input @disabled($locked) type="color" class="form-control" style="max-width: 70px" value="{{ $value ?: '#ffffff' }}" oninput="this.nextElementSibling.value = this.value">
                                                                <input @disabled($locked) type="text" name="{{ $key }}" class="form-control" dir="ltr" value="{{ $value }}" placeholder="بدون لون (اتركه فارغًا)">
                                                            </div>
                                                            @break
                                                        @case('image')
                                                        @case('background')
                                                            <select @disabled($locked) name="{{ $key }}" class="form-control ct-media-select">
                                                                <option value="">— بدون —</option>
                                                                @foreach ($media as $item)
                                                                    <option value="{{ $item->id }}" data-url="{{ $item->url() }}" @selected((int) $value === $item->id)>
                                                                        {{ $item->alt ?: $item->original_name }} ({{ \App\Models\ContentMedia::KINDS[$item->kind] ?? $item->kind }})
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                            <img class="ct-thumb" alt="" @if ($value && ($m = $media->firstWhere('id', (int) $value))) src="{{ $m->url() }}" @else hidden @endif>
                                                            <div class="ct-muted"><a href="{{ route('admin.media.index') }}" target="_blank">رفع صورة جديدة</a></div>
                                                            @break
                                                        @default
                                                            <input @disabled($locked) type="text" name="{{ $key }}" class="form-control" value="{{ $value }}" @if (in_array($field['type'], ['link', 'icon'])) dir="ltr" @endif>
                                                    @endswitch
                                                    @if (! empty($field['help']))
                                                        <small class="ct-muted">{{ $field['help'] }}</small>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                        <input type="hidden" name="_block" value="{{ $block->id }}">
                                        @if ($access !== 'view')<button class="btn btn-primary btn-sm">حفظ كمسودة</button>@endif
                                    </form>
                                @endunless
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>

        <div class="col-xl-5">
            <div class="card">
                <div class="card-header"><h6 class="mb-0">سجل النشر</h6></div>
                <div class="card-body">
                    @forelse ($revisions as $revision)
                        <div class="d-flex justify-content-between align-items-center border-bottom py-1">
                            <div class="small">
                                {{ $revision->created_at->format('Y-m-d H:i') }} · {{ $revision->action === 'rollback' ? 'استرجاع' : 'نشر' }} · {{ $revision->user?->name ?? '—' }}
                            </div>
                            @if ($access === 'publish' && ! $loop->first)
                                <form method="POST" action="{{ route('admin.content.rollback', $revision) }}" onsubmit="return confirm('إعادة القسم كما كان في هذه النسخة ونشره للزوار الآن؟')">
                                    @csrf
                                    <button class="btn btn-sm btn-outline-secondary">استرجاع هذه النسخة</button>
                                </form>
                            @elseif ($loop->first)
                                <span class="badge badge-success">المنشور الآن</span>
                            @endif
                        </div>
                    @empty
                        <p class="ct-muted mb-0">لا نشر بعد في هذا القسم. كل نشر يُسجَّل هنا ويمكن استرجاعه.</p>
                    @endforelse
                </div>
            </div>
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">معاينة مباشرة (تشمل المسودة)</h6>
                    <button type="button" class="btn btn-sm btn-light" onclick="document.getElementById('ct-preview').src = document.getElementById('ct-preview').src">تحديث</button>
                </div>
                <div class="card-body p-2">
                    <iframe id="ct-preview" class="ct-preview-frame" src="{{ route('admin.content.preview', $page) }}" title="معاينة"></iframe>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('js')
    <script>
        document.querySelectorAll('.ct-media-select').forEach(function (select) {
            select.addEventListener('change', function () {
                var img = select.parentElement.querySelector('.ct-thumb');
                var option = select.options[select.selectedIndex];
                if (option && option.dataset.url) { img.src = option.dataset.url; img.hidden = false; } else { img.hidden = true; }
            });
        });
    </script>
@endsection
