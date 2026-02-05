@extends('admin.layouts.app')
@section('title')
    اضافة فاتورة بيع
@endsection
@section('content')
    <div class="app-content content">
        <div class="content-wrapper">
            <div class="content-header row">
                <div class="mb-2 content-header-left col-md-6 col-12 breadcrumb-new">
                    <h3 class="mb-0 content-header-title d-inline-block">اضافة فاتورة بيع</h3>
                    <div class="row breadcrumbs-top d-inline-block">
                        <div class="breadcrumb-wrapper col-12">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="{{ route('dashboard.welcome') }}">الرئيسية </a>
                                </li>
                                <li class="breadcrumb-item"><a href="{{ route('dashboard.selling_invoices.index') }}">فواتير
                                        البيع</a>
                                </li>
                                <li class="breadcrumb-item active"><a href="#">اضافة فاتورة بيع</a>
                                </li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
            <div class="content-body">
                <section id="basic-form-layouts">
                    <div class="row match-height">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <h4 class="card-title" id="basic-layout-colored-form-control"> <strong> تفاصيل الفاتورة
                                        </strong> </h4>
                                    <a class="heading-elements-toggle"><i class="la la-ellipsis-v font-medium-3"></i></a>
                                </div>
                                <div class="card-content collapse show">
                                    <div class="card-body">
                                        @include('admin.layouts.validation_errors')
                                        <form class="form" action="{{ route('dashboard.selling_invoices.store') }}"
                                            method="POST">
                                            @csrf
                                            <div class="form-body">
                                                @livewire('dashboard.selling-invoice-create', ['suppliers' => $suppliers, 'safes' => $safes, 'categories' => $categories, 'clients' => $clients,'purchesInvoices'=>$purchesInvoices])
                                            </div>
                                            <div class="form-actions right">
                                                <a href="{{ route('dashboard.clients.index') }}" type="button"
                                                    class="mr-1 btn btn-warning">
                                                    <i class="ft-x"></i> الغاء
                                                </a>
                                                <button type="submit" class="btn btn-primary">
                                                    <i class="la la-check-square-o"></i> حفظ
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>
@endsection
