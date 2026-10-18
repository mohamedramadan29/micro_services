@extends('website.layouts.master')
@section('title')
    طلبات العملاء
@endsection
@php
    $selectOptions = fn ($values, $current) => collect($values)->map(fn ($value) => '<option value="' . e($value) . '"' . ((string) $current === (string) $value ? ' selected' : '') . '>' . e($value) . '</option>')->implode('');
    $f = fn ($key) => $filters[$key] ?? '';
@endphp
@section('content')
    <section class="gray-bg text-right rf-page" dir="rtl">
        <div class="container">
            <div class="rf-board-head">
                <div>
                    <h1 class="rf-title">طلبات العملاء</h1>
                    <p class="rf-subtitle">طلبات «أريد تنفيذ المشروع» في تخصصاتك فقط. بيانات تواصل العملاء مخفية.</p>
                </div>
                <a href="{{ route('specializations.edit') }}" class="rf-muted"><i class="bi bi-tags"></i> تعديل تخصصاتي</a>
            </div>
            @include('website.freelancer-requests.partials.approval-notice')

            @if ($specializations->isEmpty())
                <div class="rf-notice">
                    <strong>لم تختر تخصصاتك بعد.</strong>
                    اختر الخدمات التي تعمل بها لتظهر لك الطلبات المناسبة.
                    <a href="{{ route('specializations.edit') }}" class="rf-link-btn mt-2">اختر تخصصاتي</a>
                </div>
            @else
                <form method="GET" action="{{ route('freelancer.requests.index') }}" id="board-filters">
                    <div class="rf-board-search">
                        <label for="q" class="visually-hidden">ابحث</label>
                        <input type="search" id="q" name="q" value="{{ $f('q') }}" class="form-control"
                            placeholder="ابحث باسم الخدمة أو كلمة من تفاصيل المشروع">
                        <button type="submit" class="btn rf-btn-search"><i class="bi bi-search"></i> بحث</button>
                    </div>

                    <div class="rf-board">
                        <aside class="rf-filters" aria-label="تصفية الطلبات">
                            <h2 class="rf-filters-title">تصفية الطلبات</h2>

                            <label for="f-category">القسم</label>
                            <select id="f-category" name="category" class="form-control">
                                <option value="">كل الأقسام</option>
                                @foreach ($specializations->pluck('category')->filter()->unique('id') as $category)
                                    <option value="{{ $category->id }}" @selected($f('category') == $category->id)>{{ $category->name }}</option>
                                @endforeach
                            </select>

                            <label for="f-service">الخدمة</label>
                            <select id="f-service" name="service" class="form-control">
                                <option value="">كل خدماتي</option>
                                @foreach ($specializations as $service)
                                    <option value="{{ $service->id }}" @selected($f('service') == $service->id)>{{ $service->name }}</option>
                                @endforeach
                            </select>

                            <label for="f-budget-from">الميزانية من / إلى</label>
                            <div class="rf-filter-pair">
                                <input type="number" min="0" id="f-budget-from" name="budget_from" value="{{ $f('budget_from') }}" class="form-control" placeholder="حد أدنى" aria-label="الميزانية من">
                                <input type="number" min="0" name="budget_to" value="{{ $f('budget_to') }}" class="form-control" placeholder="حد أعلى" aria-label="الميزانية إلى">
                            </div>

                            <label for="f-deadline">موعد التسليم</label>
                            <select id="f-deadline" name="deadline" class="form-control">
                                <option value="">أي مدة</option>
                                <option value="week" @selected($f('deadline') === 'week')>خلال أسبوع</option>
                                <option value="month" @selected($f('deadline') === 'month')>خلال شهر</option>
                                <option value="quarter" @selected($f('deadline') === 'quarter')>خلال 3 أشهر</option>
                            </select>

                            <label for="f-published">تاريخ النشر</label>
                            <select id="f-published" name="published" class="form-control">
                                <option value="">أي وقت</option>
                                <option value="today" @selected($f('published') === 'today')>اليوم</option>
                                <option value="week" @selected($f('published') === 'week')>آخر أسبوع</option>
                                <option value="month" @selected($f('published') === 'month')>آخر شهر</option>
                            </select>

                            @foreach (['project_type' => 'نوع المشروع', 'work_mode' => 'مكان التنفيذ', 'urgency' => 'مستوى الاستعجال', 'experience' => 'الخبرة المطلوبة'] as $key => $label)
                                @if ($options[$key]->isNotEmpty())
                                    <label for="f-{{ $key }}">{{ $label }}</label>
                                    <select id="f-{{ $key }}" name="{{ $key }}" class="form-control">
                                        <option value="">الكل</option>
                                        {!! $selectOptions($options[$key], $f($key)) !!}
                                    </select>
                                @endif
                            @endforeach

                            <label for="f-city">المدينة أو الدولة</label>
                            <input type="text" id="f-city" name="city" value="{{ $f('city') }}" class="form-control" placeholder="حسب الخدمة">

                            <label for="f-files">ملفات مرفقة</label>
                            <select id="f-files" name="files" class="form-control">
                                <option value="">الكل</option>
                                <option value="yes" @selected($f('files') === 'yes')>نعم</option>
                                <option value="no" @selected($f('files') === 'no')>لا</option>
                            </select>

                            <label for="f-sort">ترتيب النتائج</label>
                            <select id="f-sort" name="sort" class="form-control">
                                <option value="newest" @selected($f('sort') === '' || $f('sort') === 'newest')>الأحدث</option>
                                <option value="best" @selected($f('sort') === 'best')>الأنسب لك (أقل عروضًا)</option>
                                <option value="budget_high" @selected($f('sort') === 'budget_high')>الميزانية الأعلى</option>
                                <option value="budget_low" @selected($f('sort') === 'budget_low')>الميزانية الأقل</option>
                                <option value="deadline" @selected($f('sort') === 'deadline')>الأسرع (أقرب موعد تسليم)</option>
                            </select>

                            <div class="rf-filter-actions">
                                <button type="submit" class="btn rf-btn-search">تطبيق</button>
                                <a href="{{ route('freelancer.requests.index') }}" class="rf-clear">مسح الفلاتر</a>
                            </div>
                        </aside>

                        <div class="rf-results">
                            <p class="rf-muted rf-results-count">{{ $requests->total() }} طلب مطابق</p>

                            @forelse ($requests as $item)
                                @php
                                    $isNew = $item->published_at && $item->published_at->gt(now()->subDay());
                                    $isUrgent = $item->urgency && preg_match('/عاجل|نعم/u', $item->urgency);
                                @endphp
                                <article class="rf-board-card">
                                    <div class="rf-board-card-top">
                                        @if ($isUrgent)
                                            <span class="rf-tag-badge rf-tag-urgent">عاجل</span>
                                        @elseif ($isNew)
                                            <span class="rf-tag-badge rf-tag-new">جديد</span>
                                        @else
                                            <span class="rf-tag-badge">{{ $item->published_at?->diffForHumans() }}</span>
                                        @endif
                                        @if (isset($myOffers[$item->id]) && $myOffers[$item->id] !== \App\Models\RequestOffer::STATUS_WITHDRAWN)
                                            <span class="rf-tag-badge rf-tag-mine"><i class="bi bi-check2"></i> قدّمت عرضًا</span>
                                        @endif
                                    </div>
                                    <h3 class="rf-board-card-title">
                                        @if ($item->isStaged())<span class="rf-tag-badge rf-tag-staged">مشروع بمراحل</span>@endif
                                        {{ $item->project_title ?: $item->service_name }}
                                    </h3>
                                    <p class="rf-muted mb-2">
                                        {{ $item->category_name }} ← {{ $item->service_name }} · {{ $item->number }}
                                        @if ($item->work_mode) · {{ $item->work_mode }} @endif
                                        · بيانات العميل مخفية
                                    </p>
                                    <p class="rf-board-card-label">ملخص المشروع:</p>
                                    <p class="rf-board-card-summary">{{ \Illuminate\Support\Str::limit($item->problem, 180) }}</p>
                                    <div class="rf-board-card-meta">
                                        <span><i class="bi bi-cash-stack"></i>
                                            @if ($item->budget_min !== null || $item->budget_max !== null)
                                                الميزانية: {{ $item->budget_min !== null ? (float) $item->budget_min : '…' }} – {{ $item->budget_max !== null ? (float) $item->budget_max : '…' }}
                                            @else
                                                الميزانية: يحددها المستقل
                                            @endif
                                        </span>
                                        @if ($item->deadline)
                                            <span><i class="bi bi-calendar-event"></i> الموعد: {{ $item->deadline->format('Y-m-d') }}</span>
                                        @endif
                                        @if ($item->files_count)
                                            <span><i class="bi bi-paperclip"></i> ملفات مرفقة</span>
                                        @endif
                                        <span><i class="bi bi-people"></i> {{ $item->offers_count }} عرض</span>
                                    </div>
                                    <a href="{{ route('freelancer.requests.show', $item) }}" class="rf-board-card-btn">
                                        {{ isset($myOffers[$item->id]) && $myOffers[$item->id] !== \App\Models\RequestOffer::STATUS_WITHDRAWN ? 'افتح التفاصيل وعرضك' : 'افتح التفاصيل وقدّم عرضًا' }}
                                    </a>
                                </article>
                            @empty
                                <div class="rf-empty-state">
                                    <p>لا توجد طلبات مطابقة الآن.</p>
                                    @if (array_filter($filters))
                                        <a href="{{ route('freelancer.requests.index') }}" class="rf-clear">مسح الفلاتر</a>
                                    @endif
                                </div>
                            @endforelse

                            <div class="mt-3">{{ $requests->links() }}</div>
                        </div>
                    </div>
                </form>
            @endif
        </div>
    </section>
@endsection
