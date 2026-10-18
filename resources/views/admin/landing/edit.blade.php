@extends('admin.layouts.master')
@php
    use App\Services\Landing\LandingSections;
    $styleKeys = array_keys(LandingSections::STYLE);
@endphp
@section('title')
    {{ $page->title }} - صفحات الهبوط
@endsection
@section('css')
    <style>
        .lp-section { border: 1px solid #e6ebf0; border-radius: 10px; background: #fff; margin-bottom: 12px; }
        .lp-section.lp-hidden { opacity: .6; background: #fafbfc; }
        .lp-section > summary { list-style: none; cursor: pointer; padding: 12px 14px; display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
        .lp-section > summary::-webkit-details-marker { display: none; }
        .lp-section-body { border-top: 1px solid #eef1f4; padding: 14px; }
        .lp-actions { margin-inline-start: auto; display: flex; gap: 4px; flex-wrap: wrap; }
        .lp-actions form { margin: 0; }
        .lp-item { border: 1px dashed #d5dde5; border-radius: 8px; padding: 10px 10px 0; margin-bottom: 10px; background: #fcfdfd; }
        .lp-item-head { display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px; }
        .lp-thumb { max-height: 60px; margin-top: 6px; border-radius: 6px; }
        .lp-muted { color: #6b7785; font-size: 12px; }
    </style>
@endsection
@section('content')
    <div class="breadcrumb-header justify-content-between">
        <div class="my-auto">
            <div class="d-flex">
                <h4 class="content-title mb-0 my-auto"><a href="{{ route('admin.landing.index') }}">صفحات الهبوط</a></h4>
                <span class="text-muted mt-1 tx-13 mr-2 mb-0">/ {{ $page->title }}</span>
            </div>
        </div>
    </div>
    @include('admin.catalog.partials.flash')

    <div class="card">
        <div class="card-body d-flex flex-wrap align-items-center" style="gap: 8px">
            <div>
                @if ($page->is_published)
                    <span class="badge badge-success">منشورة</span> <a href="{{ $page->url() }}" target="_blank" dir="ltr">{{ $page->url() }}</a>
                @else
                    <span class="badge badge-secondary">غير منشورة</span> <span class="lp-muted">الزوار لا يرونها حتى تضغط «نشر».</span>
                @endif
                @if ($page->hasDraft())<div class="text-warning small mt-1"><i class="fas fa-pen"></i> توجد تعديلات في المسودة لم تُنشر بعد.</div>@endif
            </div>
            <div class="mr-auto d-flex flex-wrap" style="gap: 6px">
                <a href="{{ $page->url() }}?preview=1" target="_blank" class="btn btn-light"><i class="fas fa-eye"></i> معاينة المسودة</a>
                @if ($page->hasDraft() || ! $page->is_published)
                    <form method="POST" action="{{ route('admin.landing.publish', $page) }}">@csrf<button class="btn btn-success"><i class="fas fa-upload"></i> نشر</button></form>
                @endif
                @if ($page->hasDraft() && $page->content !== null)
                    <form method="POST" action="{{ route('admin.landing.discard', $page) }}" onsubmit="return confirm('تجاهل كل التعديلات غير المنشورة؟')">@csrf<button class="btn btn-outline-secondary">تجاهل التعديلات</button></form>
                @endif
                @if ($page->is_published)
                    <form method="POST" action="{{ route('admin.landing.unpublish', $page) }}" onsubmit="return confirm('إيقاف الصفحة عن الزوار؟')">@csrf<button class="btn btn-outline-warning">إيقاف الصفحة</button></form>
                @endif
            </div>
        </div>
    </div>

    <div class="row row-sm">
        <div class="col-lg-8">
            @forelse ($sections as $section)
                @php
                    $type = LandingSections::get($section['type']);
                    $fields = LandingSections::fields($section['type']);
                    $visible = $section['visible'] ?? true;
                    $open = old('_section') === $section['id'];
                @endphp
                <details class="lp-section {{ $visible ? '' : 'lp-hidden' }}" id="section-{{ $section['id'] }}" @if ($open) open @endif>
                    <summary>
                        <i class="fas {{ $type['icon'] }} text-primary"></i>
                        <strong>{{ $type['label'] }}</strong>
                        <span class="lp-muted">{{ \Illuminate\Support\Str::limit($section['fields']['title'] ?? '', 50) }}</span>
                        @unless ($visible)<span class="badge badge-secondary">مخفي</span>@endunless
                        <span class="lp-actions" onclick="event.stopPropagation()">
                            @foreach ([['up', 'fa-arrow-up', 'لأعلى', $loop->first], ['down', 'fa-arrow-down', 'لأسفل', $loop->last], ['toggle', $visible ? 'fa-eye-slash' : 'fa-eye', $visible ? 'إخفاء' : 'إظهار', false], ['duplicate', 'fa-copy', 'نسخ', false]] as [$action, $icon, $label, $disabled])
                                <form method="POST" action="{{ route('admin.landing.sections.action', [$page, $section['id'], $action]) }}">@csrf
                                    <button class="btn btn-sm btn-light" title="{{ $label }}" @disabled($disabled)><i class="fas {{ $icon }}"></i></button></form>
                            @endforeach
                            <form method="POST" action="{{ route('admin.landing.sections.action', [$page, $section['id'], 'delete']) }}" onsubmit="return confirm('حذف هذا القسم؟ (يُطبَّق على الزوار عند النشر)')">@csrf
                                <button class="btn btn-sm btn-light text-danger" title="حذف"><i class="fas fa-trash"></i></button></form>
                        </span>
                    </summary>
                    <div class="lp-section-body">
                        <form method="POST" action="{{ route('admin.landing.sections.update', [$page, $section['id']]) }}">
                            @csrf
                            <input type="hidden" name="_section" value="{{ $section['id'] }}">
                            <div class="row">
                                @foreach (array_diff_key($fields, LandingSections::STYLE) as $key => $field)
                                    @include('admin.landing.partials.field', ['name' => "fields[$key]", 'value' => $open ? old("fields.$key") : ($section['fields'][$key] ?? '')])
                                @endforeach
                            </div>

                            @if (isset($type['items']))
                                @php $itemDef = $type['items']; $items = $open ? array_values((array) old('items', [])) : ($section['items'] ?? []); @endphp
                                <h6 class="mt-2">عناصر القسم <span class="lp-muted">(حتى {{ $itemDef['max'] }} — رتّبها بالأسهم)</span></h6>
                                <div class="lp-items" data-max="{{ $itemDef['max'] }}">
                                    @foreach ($items as $i => $item)
                                        @include('admin.landing.partials.item', ['index' => $i, 'number' => $loop->iteration])
                                    @endforeach
                                </div>
                                <template class="lp-item-template">
                                    @include('admin.landing.partials.item', ['index' => '__i__', 'number' => '', 'item' => []])
                                </template>
                                <button type="button" class="btn btn-sm btn-outline-primary lp-add-item mb-3"><i class="fas fa-plus"></i> إضافة {{ $itemDef['label'] }}</button>
                            @endif

                            <details class="mb-3">
                                <summary class="lp-muted" style="cursor: pointer">الألوان والخلفية ومعرّف القسم</summary>
                                <div class="row mt-2">
                                    @foreach (LandingSections::STYLE as $key => $field)
                                        @include('admin.landing.partials.field', ['name' => "fields[$key]", 'value' => $open ? old("fields.$key") : ($section['fields'][$key] ?? '')])
                                    @endforeach
                                </div>
                            </details>
                            <button class="btn btn-primary">حفظ القسم (مسودة)</button>
                        </form>
                    </div>
                </details>
            @empty
                <div class="card"><div class="card-body text-center text-muted">الصفحة فارغة. أضف أول قسم من اليسار.</div></div>
            @endforelse
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-header"><h6 class="mb-0">إضافة قسم</h6></div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.landing.sections.store', $page) }}">
                        @csrf
                        <select name="type" class="form-control mb-2" required>
                            @foreach ($types as $key => $type)<option value="{{ $key }}">{{ $type['label'] }}</option>@endforeach
                        </select>
                        <button class="btn btn-outline-primary btn-block"><i class="fas fa-plus"></i> إضافة في آخر الصفحة</button>
                        <small class="lp-muted d-block mt-1">ثم حرّكه بالأسهم إلى مكانه.</small>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-header"><h6 class="mb-0">إعدادات الصفحة</h6></div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.landing.settings', $page) }}">
                        @csrf
                        <div class="form-group"><label>اسم الصفحة</label>
                            <input type="text" name="title" class="form-control" required value="{{ old('title', $page->title) }}"></div>
                        <div class="form-group"><label>الرابط</label>
                            <div class="input-group" dir="ltr">
                                <div class="input-group-prepend"><span class="input-group-text">/lp/</span></div>
                                <input type="text" name="slug" class="form-control" required value="{{ old('slug', $page->slug) }}">
                            </div></div>
                        <div class="form-group"><label>وصف محركات البحث والمشاركة</label>
                            <textarea name="meta_description" rows="2" maxlength="300" class="form-control">{{ old('meta_description', $page->meta_description) }}</textarea></div>
                        <div class="form-group"><label>صورة المشاركة على السوشيال</label>
                            <select name="og_image_id" class="form-control lp-media-select">
                                <option value="">— شعار الموقع —</option>
                                @foreach ($media as $item)<option value="{{ $item->id }}" data-url="{{ $item->url() }}" @selected($page->og_image_id === $item->id)>{{ $item->alt ?: ($item->original_name ?: basename($item->path)) }}</option>@endforeach
                            </select>
                            <img class="lp-thumb" alt="" @if ($page->ogImage) src="{{ $page->ogImage->url() }}" @else hidden @endif></div>
                        <div class="form-group">
                            <label class="d-flex align-items-center" style="gap: 6px"><input type="checkbox" name="show_site_nav" value="1" @checked(old('show_site_nav', $page->show_site_nav))> إظهار قائمة الموقع الرئيسية</label>
                            <small class="lp-muted">بدونها يظهر شريط بسيط: الشعار + زر واحد (أفضل للإعلانات).</small>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-6"><label>زر الشريط البسيط</label>
                                <input type="text" name="header_button_text" class="form-control" maxlength="60" value="{{ old('header_button_text', $page->header_button_text) }}"></div>
                            <div class="form-group col-6"><label>رابطه</label>
                                <input type="text" name="header_button_link" class="form-control" dir="ltr" placeholder="/request" value="{{ old('header_button_link', $page->header_button_link) }}"></div>
                        </div>
                        <button class="btn btn-primary btn-block">حفظ الإعدادات</button>
                        <small class="lp-muted d-block mt-1">الإعدادات تُحفظ مباشرة؛ الأقسام وحدها تمر بالمسودة والنشر.</small>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <a href="{{ route('admin.media.index') }}" target="_blank" class="btn btn-light btn-block"><i class="fas fa-images"></i> مكتبة الصور والخلفيات</a>
                    <form method="POST" action="{{ route('admin.landing.destroy', $page) }}" class="mt-2" onsubmit="return confirm('حذف الصفحة نهائيًا؟ رابطها سيتوقف.')">@csrf
                        <button class="btn btn-outline-danger btn-block">حذف الصفحة</button></form>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('js')
    <script>
        document.addEventListener('change', function (e) {
            if (!e.target.classList.contains('lp-media-select')) return;
            var img = e.target.parentElement.querySelector('.lp-thumb');
            var url = e.target.options[e.target.selectedIndex].dataset.url;
            if (img) { img.hidden = !url; if (url) img.src = url; }
        });
        // Items: add / remove / move rows; the server keeps the order they arrive in.
        var lpCounter = 1000;
        function lpRenumber(list) {
            list.querySelectorAll(':scope > .lp-item').forEach(function (item, i) { item.querySelector('.lp-item-num').textContent = i + 1; });
        }
        document.addEventListener('click', function (e) {
            var btn = e.target.closest('.lp-add-item, .lp-item-remove, .lp-item-up, .lp-item-down');
            if (!btn) return;
            e.preventDefault();
            if (btn.classList.contains('lp-add-item')) {
                var form = btn.closest('form');
                var list = form.querySelector('.lp-items');
                if (list.children.length >= parseInt(list.dataset.max, 10)) { alert('وصلت للحد الأقصى في هذا القسم'); return; }
                var html = form.querySelector('.lp-item-template').innerHTML.replace(/__i__/g, 'n' + (lpCounter++));
                list.insertAdjacentHTML('beforeend', html);
                lpRenumber(list);
                return;
            }
            var item = btn.closest('.lp-item');
            var list = item.parentElement;
            if (btn.classList.contains('lp-item-remove')) item.remove();
            if (btn.classList.contains('lp-item-up') && item.previousElementSibling) list.insertBefore(item, item.previousElementSibling);
            if (btn.classList.contains('lp-item-down') && item.nextElementSibling) list.insertBefore(item.nextElementSibling, item);
            lpRenumber(list);
        });
        if (location.hash.indexOf('#section-') === 0) {
            var el = document.getElementById(location.hash.slice(1));
            if (el) el.open = true;
        }
    </script>
@endsection
