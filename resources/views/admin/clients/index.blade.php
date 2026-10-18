@extends('admin.layouts.master')
@php use App\Services\ServiceRequests\ClientClassifier as C; @endphp
@section('title')
    العملاء{{ $category ? ' — ' . C::CATEGORIES[$category]['label'] : '' }}
@endsection
@section('css')
    @include('admin.clients.partials.style')
@endsection
@section('content')
    <div class="breadcrumb-header justify-content-between">
        <div class="my-auto">
            <div class="d-flex">
                <h4 class="content-title mb-0 my-auto">العملاء</h4>
                <span class="text-muted mt-1 tx-13 mr-2 mb-0">/ بحث، وسوم، وسجل تواصل وطلبات</span>
            </div>
        </div>
    </div>

    <div class="row row-sm">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header"><h6 class="mb-0">قائمة العملاء {{ $category ? '— ' . C::CATEGORIES[$category]['label'] : '' }} ({{ $clients->total() }})</h6></div>
                <div class="card-body">
                    <form method="GET" class="mb-3 d-flex" style="gap: 8px">
                        @if ($category)<input type="hidden" name="category" value="{{ $category }}">@endif
                        <input type="search" name="q" value="{{ request('q') }}" class="form-control" placeholder="ابحث بالاسم أو الهاتف أو البريد">
                        <select name="service" class="form-control" style="max-width: 220px">
                            <option value="">كل الخدمات</option>
                            @foreach ($services as $item)
                                <option value="{{ $item->id }}" @selected($service === $item->id)>{{ $item->name }}</option>
                            @endforeach
                        </select>
                        <button class="btn btn-primary">بحث</button>
                    </form>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead><tr><th>العميل ووسومه</th><th style="width:140px">آخر تواصل</th><th style="width:90px">الطلبات</th><th style="width:110px"></th></tr></thead>
                            <tbody>
                                @forelse ($clients as $row)
                                    <tr>
                                        <td>
                                            <strong>{{ $row['user']->name }}</strong>
                                            <div class="cl-muted"><span dir="ltr">{{ $row['user']->email }}</span>@if ($row['user']->phone) · <span dir="ltr">{{ $row['user']->phone }}</span>@endif</div>
                                            <div class="mt-1">
                                                @foreach ($row['tags'] as $tag)
                                                    <span class="cl-tag cl-tag-{{ $tag }}">{{ C::CATEGORIES[$tag]['label'] }}</span>
                                                @endforeach
                                            </div>
                                        </td>
                                        <td>{{ $row['last_activity']?->diffForHumans() }}</td>
                                        <td>{{ $row['requests'] }}</td>
                                        <td><a href="{{ route('admin.clients.show', $row['user']) }}" class="btn btn-sm btn-primary">ملف العميل</a></td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="text-center text-muted">لا يوجد عملاء</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    {{ $clients->links() }}
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-header"><h6 class="mb-0">التصنيفات (بالترتيب المطلوب)</h6></div>
                <div class="list-group list-group-flush cl-cats">
                    <a href="{{ route('admin.clients.index', array_filter(['q' => request('q'), 'service' => $service])) }}" class="list-group-item list-group-item-action {{ $category ? '' : 'active' }}">
                        <span class="cl-num">∗</span><span class="flex-fill">كل العملاء</span><span class="badge badge-light">{{ $total }}</span>
                    </a>
                    @foreach (C::CATEGORIES as $key => $meta)
                        <a href="{{ route('admin.clients.index', array_filter(['category' => $key, 'q' => request('q'), 'service' => $service])) }}" class="list-group-item list-group-item-action {{ $category === $key ? 'active' : '' }}">
                            <span class="cl-num">{{ $loop->iteration }}</span>
                            <span class="flex-fill">{{ $meta['label'] }}<br><small class="{{ $category === $key ? '' : 'cl-muted' }}">{{ $meta['hint'] }}{{ $key === 'repeat' ? ' (' . $threshold . ' طلبات أو أكثر)' : '' }}</small></span>
                            <span class="badge badge-light">{{ $counts[$key] }}</span>
                        </a>
                    @endforeach
                </div>
            </div>
            <div class="card">
                <div class="card-body tx-13">
                    قد يجتمع أكثر من تصنيف على العميل نفسه، مثل «عميل متكرر» و«نفّذ قبل هالمرة» و«عم يجاوب». التصنيفات تُحسب تلقائيًا من الطلبات والرسائل، ولا يُحذف أي سجل.
                    حد «العميل المتكرر» يُعدّل من <a href="{{ url('admin/public_settings') }}">الإعدادات العامة</a>.
                </div>
            </div>
        </div>
    </div>
@endsection
