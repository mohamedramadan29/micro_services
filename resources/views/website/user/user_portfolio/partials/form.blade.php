{{-- Add / edit a portfolio work (shared). $portfolio = null when adding. --}}
@php
    $editing = $portfolio !== null;
    $selectedSkills = old('skills', $editing ? array_filter(explode(',', (string) $portfolio->tools)) : []);
    $selectedCategory = (string) old('category', $editing ? $portfolio->category_id : '');
    $selectedSkills = array_map('strval', (array) $selectedSkills);
    // Skills grouped under their section, so the search shows where each one belongs.
    $categoryNames = $categories->pluck('name', 'id');
    $skillGroups = $sub_categories->groupBy(fn ($sub) => $categoryNames->has($sub->parent_id) ? $sub->parent_id : 0)
        ->sortBy(fn ($subs, $id) => $id ? $categories->search(fn ($c) => $c->id == $id) : PHP_INT_MAX);
@endphp
<form method="post" enctype="multipart/form-data" class="rf-pf-form" id="rf-pf-form"
      action="{{ $editing ? route('user.portfolio.update', [$portfolio->id, $portfolio->slug]) : route('user.portfolio.store') }}">
    @csrf
    @if ($errors->any())
        <div class="alert alert-danger"><ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
    @endif

    <div class="_dashboard_content">
        <div class="_dashboard_content_header">
            <div class="_dashboard__header_flex">
                <h4><i class="bi bi-images"></i> {{ $editing ? 'بيانات العمل' : 'عمل جديد' }}</h4>
                <a href="{{ route('user.portfolio.index') }}" class="btn btn-light btn-sm"><i class="bi bi-arrow-right"></i> رجوع لأعمالي</a>
            </div>
        </div>
        <div class="_dashboard_content_body">
            <div class="rf-pf-grid">
                <div class="rf-pf-main">
                    <div class="form-group">
                        <label for="pf-title">عنوان العمل <span class="text-danger">*</span></label>
                        <input type="text" id="pf-title" class="form-control with-light" name="title" required maxlength="255"
                               placeholder="مثال: متجر إلكتروني لمحل ملابس" value="{{ old('title', $editing ? $portfolio->title : '') }}">
                    </div>
                    <div class="form-group">
                        <label for="pf-description">وصف العمل <span class="text-danger">*</span></label>
                        <textarea id="pf-description" class="form-control with-light" name="description" rows="6" required
                                  placeholder="ماذا كان المطلوب؟ ماذا نفّذت؟ وما النتيجة؟">{{ old('description', $editing ? $portfolio->description : '') }}</textarea>
                    </div>
                    <div class="form-group">
                        <label for="pf-category">القسم الرئيسي <span class="text-danger">*</span></label>
                        <select id="pf-category" required class="form-control form-select rf-pf-select" name="category" data-placeholder="اختر القسم">
                            <option value="" disabled @selected($selectedCategory === '')>اختر القسم</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}" @selected($selectedCategory === (string) $category->id)>{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="pf-skills">المهارات المستخدمة <span class="text-danger">*</span></label>
                        <select id="pf-skills" required class="form-control rf-pf-select" multiple name="skills[]" data-placeholder="ابحث واختر مهارة أو أكثر">
                            @foreach ($skillGroups as $groupId => $skills)
                                <optgroup label="{{ $groupId ? $categoryNames[$groupId] : 'أخرى' }}" data-category="{{ $groupId }}">
                                    @foreach ($skills as $sub_category)
                                        <option value="{{ $sub_category->id }}" @selected(in_array((string) $sub_category->id, $selectedSkills, true))>{{ $sub_category->name }}</option>
                                    @endforeach
                                </optgroup>
                            @endforeach
                        </select>
                        <small class="text-muted d-block mt-1">اكتب للبحث؛ مهارات القسم الذي تختاره تظهر أولًا.</small>
                    </div>
                    <div class="form-group mb-0">
                        <label for="pf-link">رابط العمل <small class="text-muted">(اختياري)</small></label>
                        <input type="url" id="pf-link" dir="ltr" class="form-control with-light" name="link" maxlength="500"
                               placeholder="https://" value="{{ old('link', $editing ? $portfolio->link : '') }}">
                    </div>
                </div>
                <div class="rf-pf-media">
                    @livewire('portfolio-upload', $editing ? ['portfolio' => $portfolio] : [])
                </div>
            </div>
        </div>
    </div>

    <div class="rf-pf-actions">
        <button type="submit" class="btn btn-primary btn-save" id="rf-pf-submit">
            <span class="rf-pf-label">{{ $editing ? 'حفظ التعديلات' : 'إضافة العمل' }} <i class="bi bi-check2"></i></span>
            <span class="rf-pf-busy" hidden>جاري الحفظ…</span>
        </button>
        @unless ($editing)
            <small class="text-muted">يظهر العمل في ملفك العام بعد مراجعة فريق نفّذها.</small>
        @endunless
    </div>
</form>

<script>
    (function () {
        var form = document.getElementById('rf-pf-form');
        form.addEventListener('submit', function () {
            var btn = document.getElementById('rf-pf-submit');
            btn.disabled = true;
            btn.querySelector('.rf-pf-label').hidden = true;
            btn.querySelector('.rf-pf-busy').hidden = false;
        });
        // Back from history (bfcache): the button works again.
        window.addEventListener('pageshow', function () {
            var btn = document.getElementById('rf-pf-submit');
            btn.disabled = false;
            btn.querySelector('.rf-pf-label').hidden = false;
            btn.querySelector('.rf-pf-busy').hidden = true;
        });
        // Searchable pickers (select2 is loaded with the site scripts). Without it the native selects still work.
        window.addEventListener('load', function () {
            if (!window.jQuery || !jQuery.fn.select2) return;
            var $ = jQuery;
            // Arabic-friendly search: أ/إ/آ = ا, ة = ه, ى = ي, no tashkeel.
            var norm = function (t) {
                return String(t || '').toLowerCase().replace(/[\u064B-\u0652\u0640]/g, '')
                    .replace(/[أإآ]/g, 'ا').replace(/ة/g, 'ه').replace(/ى/g, 'ي');
            };
            var matcher = function (params, data) {
                var term = norm($.trim(params.term || ''));
                if (term === '') return data;
                if (data.children && data.children.length) {
                    var groupHit = norm(data.text).indexOf(term) > -1;
                    var kids = groupHit ? data.children : data.children.filter(function (c) { return norm(c.text).indexOf(term) > -1; });
                    return kids.length ? $.extend({}, data, { children: kids }) : null;
                }
                return norm(data.text).indexOf(term) > -1 ? data : null;
            };
            var language = {
                noResults: function () { return 'لا توجد نتائج مطابقة'; },
                searching: function () { return 'جاري البحث…'; }
            };
            var parent = $('#rf-pf-form');
            var $category = $('#pf-category');
            var $skills = $('#pf-skills');
            $category.select2({ dir: 'rtl', width: '100%', placeholder: $category.data('placeholder'), language: language,
                matcher: matcher, dropdownParent: parent, minimumResultsForSearch: 0 });
            // Open the search box focused (select2 4.0.13 does not focus it by itself under jQuery 3.6+).
            $category.on('select2:open', function () {
                setTimeout(function () { var f = document.querySelector('#rf-pf-form .select2-search__field'); if (f) f.focus(); }, 0);
            });
            $skills.select2({ dir: 'rtl', width: '100%', placeholder: $skills.data('placeholder'), language: language,
                matcher: matcher, dropdownParent: parent, closeOnSelect: false });

            // The chosen section's skills come first in the list.
            var sortSkills = function () {
                var group = $skills.find('optgroup[data-category="' + $category.val() + '"]');
                if (group.length) $skills.prepend(group);
            };
            sortSkills();
            $category.on('change', sortSkills);
        });
    })();
</script>
