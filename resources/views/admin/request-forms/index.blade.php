@extends('admin.layouts.master')
@section('title')
    نماذج الخدمات
@endsection
@section('content')
    <div class="breadcrumb-header justify-content-between">
        <div class="my-auto">
            <div class="d-flex">
                <h4 class="content-title mb-0 my-auto">الرئيسية </h4><span class="text-muted mt-1 tx-13 mr-2 mb-0">/
                    نماذج الخدمات </span>
            </div>
        </div>
    </div>

    <div class="row row-sm">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-1">نماذج طلب الخدمات</h5>
                    <p class="text-muted mb-0 tx-13">لكل خدمة نموذج أسئلة يملؤه العميل عند طلبها. الخدمات التي ليس لها نموذج منشور لا تظهر في صفحة الطلب.</p>
                </div>
                <div class="card-body">
                    @if (session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    @forelse ($categories as $category)
                        <h6 class="mt-3 mb-2">
                            {{ $category->name }}
                            @if (! $category->status)
                                <span class="badge badge-light">القسم غير مفعل</span>
                            @endif
                        </h6>
                        @if ($category->subCategories->isEmpty())
                            <p class="text-muted tx-13">لا توجد خدمات في هذا القسم.</p>
                        @else
                            <div class="table-responsive">
                                <table class="table table-bordered mb-3">
                                    <thead>
                                        <tr>
                                            <th>الخدمة</th>
                                            <th style="width: 140px">حالة النموذج</th>
                                            <th style="width: 110px">عدد الأسئلة</th>
                                            <th style="width: 160px">آخر تعديل</th>
                                            <th style="width: 150px"></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($category->subCategories as $subCategory)
                                            @php($form = $forms[$subCategory->id] ?? null)
                                            <tr>
                                                <td>
                                                    {{ $subCategory->name }}
                                                    @if (! $subCategory->status)
                                                        <span class="badge badge-light">الخدمة غير مفعلة</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if ($form?->revision)
                                                        <span class="badge badge-light d-block mb-1">+ مسودة تعديل</span>
                                                    @endif
                                                    @if (! $form)
                                                        <span class="text-muted">لا يوجد نموذج</span>
                                                    @elseif ($form->status === \App\Models\ServiceForm::STATUS_PUBLISHED)
                                                        <span class="badge badge-success">منشور</span>
                                                    @elseif ($form->status === \App\Models\ServiceForm::STATUS_PAUSED)
                                                        <span class="badge badge-warning">متوقف</span>
                                                    @else
                                                        <span class="badge badge-secondary">مسودة</span>
                                                    @endif
                                                </td>
                                                <td>{{ $form?->questions_count ?? '—' }}</td>
                                                <td>{{ $form?->updated_at?->format('Y-m-d H:i') ?? '—' }}</td>
                                                <td>
                                                    <a href="{{ route('admin.request-forms.show', $subCategory) }}"
                                                        class="btn btn-sm {{ $form ? 'btn-primary' : 'btn-outline-primary' }}">
                                                        <i class="fas {{ $form ? 'fa-edit' : 'fa-plus' }}"></i>
                                                        {{ $form ? 'تعديل النموذج' : 'إنشاء نموذج' }}
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    @empty
                        <p class="text-muted">لا توجد أقسام بعد. أضف الأقسام والخدمات أولًا من «الأقسام».</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
@endsection
