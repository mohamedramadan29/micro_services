{{-- Add / edit a service (shared). $service = null when adding. --}}
@php
    $editing = $service !== null;
    $selCat = (string) old('cat_id', $editing ? $service->cat_id : '');
    $selSub = (string) old('sub_cat_id', $editing ? $service->sub_cat_id : '');
    $price = old('price', $editing ? $service->price : '');
@endphp
<form method="post" enctype="multipart/form-data" class="rf-pf-form rf-sf-form" id="rf-pf-form" novalidate
      action="{{ $editing ? url('service/update/' . $service->id) : url('service/add') }}" data-commission="{{ (float) $commission }}">
    @csrf
    @if ($errors->any())
        <div class="alert alert-danger"><ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
    @endif
    @if ($editing && (int) $service->status === \App\Models\admin\Service::STATUS_REJECTED && $service->review_note)
        <p class="rf-pf-note"><strong>ملاحظة الفريق:</strong> {{ $service->review_note }} — عدّل الخدمة واحفظها لتُراجع من جديد.</p>
    @endif

    <div class="_dashboard_content">
        <div class="_dashboard_content_header">
            <div class="_dashboard__header_flex">
                <h4><i class="bi bi-grid"></i> {{ $editing ? 'بيانات الخدمة' : 'خدمة جديدة' }}</h4>
                <a href="{{ url('service/index') }}" class="btn btn-light btn-sm"><i class="bi bi-arrow-right"></i> رجوع لخدماتي</a>
            </div>
        </div>
        <div class="_dashboard_content_body">
            <div class="rf-pf-grid">
                <div class="rf-pf-main">
                    <div class="form-group">
                        <label for="sf-name">اسم الخدمة <span class="text-danger">*</span></label>
                        <input type="text" id="sf-name" class="form-control with-light" name="name" required minlength="8" maxlength="150"
                               placeholder="مثال: تصميم شعار احترافي مع 3 نماذج وملفات مصدرية" value="{{ old('name', $editing ? $service->name : '') }}">
                        <small class="text-muted d-block mt-1">اكتب ما سيستلمه العميل بالضبط.</small>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="sf-cat">القسم <span class="text-danger">*</span></label>
                                <select id="sf-cat" required class="form-control rf-pf-select" name="cat_id" data-placeholder="اختر القسم">
                                    <option value="" @selected($selCat === '')>اختر القسم</option>
                                    @foreach ($categories as $category)
                                        <option value="{{ $category->id }}" @selected($selCat === (string) $category->id)>{{ $category->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="sf-sub">الخدمة الفرعية <span class="text-danger">*</span></label>
                                <select id="sf-sub" required class="form-control rf-pf-select" name="sub_cat_id" data-placeholder="اختر القسم أولًا">
                                    <option value=""></option>
                                    @foreach ($subCategories as $sub)
                                        <option value="{{ $sub->id }}" data-parent="{{ $sub->parent_id }}" @selected($selSub === (string) $sub->id)>{{ $sub->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="sf-description">وصف الخدمة <span class="text-danger">*</span></label>
                        <textarea id="sf-description" class="form-control with-light" name="description" rows="7" required minlength="20" maxlength="5000"
                                  placeholder="ماذا يشمل السعر؟ ماذا تحتاج من العميل؟ كم تستغرق؟ كم مرة تعديل؟">{{ old('description', $editing ? $service->description : '') }}</textarea>
                        <small class="text-muted d-block mt-1"><span id="sf-count">0</span> / 5000 حرف</small>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="sf-price">السعر (ج.م) <span class="text-danger">*</span></label>
                                <input type="number" id="sf-price" dir="ltr" class="form-control with-light" name="price" required min="5" max="1000000" step="1" value="{{ $price }}" placeholder="مثال: 500">
                                <small class="text-muted d-block mt-1" id="sf-net" @if ($price === '' || $price === null) hidden @endif>
                                    تستلم <strong id="sf-net-value">{{ \App\Support\Money::format(round((float) $price * (1 - (float) $commission / 100), 2)) }}</strong> بعد عمولة نفّذها ({{ rtrim(rtrim(number_format((float) $commission, 2), '0'), '.') }}%).
                                </small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-0">
                                <label for="sf-tags">كلمات مفتاحية <small class="text-muted">(اختياري)</small></label>
                                <input type="text" id="sf-tags" class="form-control with-light" name="tags" maxlength="500" placeholder="شعار، هوية بصرية، تصميم"
                                       value="{{ old('tags', $editing ? str_replace(',', '، ', (string) $service->tags) : '') }}">
                                <small class="text-muted d-block mt-1">افصل بينها بفاصلة؛ تساعد العملاء في البحث.</small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="rf-pf-media">
                    <div class="rf-pf-upload">
                        <label>صورة الخدمة @unless ($editing)<span class="text-danger">*</span>@endunless</label>
                        <div class="rf-pf-cover" id="sf-preview" @unless ($editing) hidden @endunless>
                            <img src="{{ $editing ? asset('assets/uploads/services/' . $service->image) : '' }}" alt="صورة الخدمة">
                        </div>
                        <label class="rf-pf-drop {{ $editing ? 'rf-pf-drop-sm mt-2' : '' }}">
                            <input type="file" id="sf-image" name="image" accept="image/jpeg,image/png,image/webp" @unless ($editing) required @endunless>
                            <i class="bi bi-cloud-arrow-up" aria-hidden="true"></i>
                            <strong id="sf-image-label">{{ $editing ? 'تغيير الصورة' : 'اختر صورة الخدمة' }}</strong>
                            <small>jpg أو png أو webp، حتى 4 ميجابايت. يُفضّل 1200×800</small>
                        </label>
                    </div>
                    <ul class="rf-sf-tips">
                        <li><i class="bi bi-check2" aria-hidden="true"></i> صورة من عملك الحقيقي، بلا أرقام هاتف أو روابط</li>
                        <li><i class="bi bi-check2" aria-hidden="true"></i> وضّح ما يشمله السعر ومدة التنفيذ</li>
                        <li><i class="bi bi-check2" aria-hidden="true"></i> التواصل والدفع يتمّان داخل نفّذها فقط</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="rf-pf-actions">
        <button type="submit" class="btn btn-primary btn-save" id="rf-pf-submit">
            <span class="rf-pf-label">{{ $editing ? 'حفظ التعديلات' : 'إضافة الخدمة' }} <i class="bi bi-check2"></i></span>
            <span class="rf-pf-busy" hidden>جاري الحفظ…</span>
        </button>
        <small class="text-muted">تظهر الخدمة للعملاء بعد مراجعة فريق نفّذها{{ $editing ? '، وأي تعديل يعيدها للمراجعة' : '' }}. بإرسالها تؤكد أن المحتوى من عملك.</small>
    </div>
</form>

<script>
    (function () {
        var form = document.getElementById('rf-pf-form');
        var btn = document.getElementById('rf-pf-submit');
        form.addEventListener('submit', function () {
            btn.disabled = true; btn.querySelector('.rf-pf-label').hidden = true; btn.querySelector('.rf-pf-busy').hidden = false;
        });
        window.addEventListener('pageshow', function () {
            btn.disabled = false; btn.querySelector('.rf-pf-label').hidden = false; btn.querySelector('.rf-pf-busy').hidden = true;
        });

        // Description counter
        var desc = document.getElementById('sf-description'), count = document.getElementById('sf-count');
        var updateCount = function () { count.textContent = desc.value.length; };
        desc.addEventListener('input', updateCount); updateCount();

        // What the freelancer receives after the commission
        var price = document.getElementById('sf-price'), net = document.getElementById('sf-net'), netValue = document.getElementById('sf-net-value');
        var rate = parseFloat(form.getAttribute('data-commission')) || 0;
        price.addEventListener('input', function () {
            var v = parseFloat(price.value);
            net.hidden = !(v > 0);
            if (v > 0) netValue.textContent = (Math.round(v * (1 - rate / 100) * 100) / 100).toLocaleString('en-US') + ' ج.م';
        });

        // Image preview
        var file = document.getElementById('sf-image'), preview = document.getElementById('sf-preview');
        file.addEventListener('change', function () {
            if (!file.files || !file.files[0]) return;
            preview.querySelector('img').src = URL.createObjectURL(file.files[0]);
            preview.hidden = false;
            document.getElementById('sf-image-label').textContent = file.files[0].name;
        });

        // Sub-services of the chosen category only (all options stay in the page, filtered here).
        var cat = document.getElementById('sf-cat'), sub = document.getElementById('sf-sub');
        var all = Array.prototype.slice.call(sub.querySelectorAll('option[data-parent]')).map(function (o) { return o.cloneNode(true); });
        var fillSubs = function (keep) {
            var chosen = keep ? sub.value : '';
            sub.querySelectorAll('option[data-parent]').forEach(function (o) { o.remove(); });
            all.filter(function (o) { return o.getAttribute('data-parent') === cat.value; }).forEach(function (o) { sub.appendChild(o.cloneNode(true)); });
            sub.value = chosen && sub.querySelector('option[value="' + chosen + '"]') ? chosen : '';
            sub.disabled = !cat.value;
        };
        fillSubs(true);

        window.addEventListener('load', function () {
            if (!window.jQuery || !jQuery.fn.select2) { cat.addEventListener('change', function () { fillSubs(false); }); return; }
            var $ = jQuery, parent = $('#rf-pf-form');
            var norm = function (t) { return String(t || '').toLowerCase().replace(/[ً-ْـ]/g, '').replace(/[أإآ]/g, 'ا').replace(/ة/g, 'ه').replace(/ى/g, 'ي'); };
            var matcher = function (params, data) {
                var term = norm($.trim(params.term || ''));
                return term === '' || norm(data.text).indexOf(term) > -1 ? data : null;
            };
            var language = { noResults: function () { return 'لا توجد نتائج مطابقة'; } };
            var opts = function (el) { return { dir: 'rtl', width: '100%', placeholder: $(el).data('placeholder'), language: language, matcher: matcher, dropdownParent: parent, minimumResultsForSearch: 0 }; };
            // select2 reads data-placeholder before the options, so the attribute itself is updated.
            var subPlaceholder = function () {
                var text = cat.value ? 'اختر الخدمة الفرعية' : 'اختر القسم أولًا';
                sub.setAttribute('data-placeholder', text);
                $(sub).data('placeholder', text); // jQuery keeps its own copy of data-* values
            };
            subPlaceholder();
            $(cat).select2(opts(cat));
            $(sub).select2(opts(sub));
            $(cat).on('change', function () {
                fillSubs(false);
                subPlaceholder();
                $(sub).select2('destroy').select2(opts(sub));
            });
            $(cat).add(sub).on('select2:open', function () {
                setTimeout(function () { var f = document.querySelector('#rf-pf-form .select2-search__field'); if (f) f.focus(); }, 0);
            });
        });
    })();
</script>
