@extends('admin.layouts.master')
@section('title')
    ما يبحث عنه الناس في Google
@endsection
@section('content')
    @include('admin.seo.partials.head')

    <div class="card">
        <div class="card-body">
            <p class="text-muted">عبارات البحث التي ظهر فيها موقعك في Google خلال آخر 28 يومًا، مرتبة حسب مرات الظهور. أضف المهم منها لمتابعته.</p>
            <form method="GET" class="form-inline mb-3" style="gap: 8px">
                <input type="search" name="q" value="{{ request('q') }}" class="form-control" placeholder="ابحث في العبارات">
                <button class="btn btn-primary">بحث</button>
            </form>
            <div class="table-responsive">
                <table class="table table-bordered mb-0">
                    <thead><tr><th>عبارة البحث</th><th>مرات الظهور</th><th>النقرات</th><th>نسبة النقر</th><th>متوسط الترتيب</th><th></th></tr></thead>
                    <tbody>
                        @forelse ($queries as $row)
                            <tr>
                                <td>{{ $row->query }}</td>
                                <td>{{ number_format($row->impressions) }}</td>
                                <td>{{ number_format($row->clicks) }}</td>
                                <td>{{ round((float) $row->ctr * 100, 1) }}%</td>
                                <td>{{ (float) $row->position }}</td>
                                <td>
                                    @if (in_array(\App\Models\SeoKeyword::normalize($row->query), $tracked, true))
                                        <span class="badge badge-success">متابَعة</span>
                                    @else
                                        <form method="POST" action="{{ route('admin.seo.track') }}">@csrf<input type="hidden" name="query" value="{{ $row->query }}"><button class="btn btn-sm btn-outline-primary">متابعة الكلمة</button></form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted">{{ $connected ? 'لا توجد بيانات بعد. اضغط «تحديث الآن».' : 'تظهر هنا بعد ربط Google Search Console.' }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3">{{ $queries->links() }}</div>
        </div>
    </div>
@endsection
