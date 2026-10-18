@extends('website.layouts.master')
@section('title')
    اتمام عملية الشراء
@endsection
@section('content')
    <!-- ============================ Page Title Start================================== -->
    <div class="page-title" style="height: 350px;text-align: right">
        <div class="container">
            <div class="row">
                <div class="col-lg-12 col-md-12">
                    <h2 class="ipt-title">الرئيسية </h2>
                    <span class="ipn-subtitle"> اتمام عملية الشراء  </span>

                </div>
            </div>
        </div>
    </div>
    <!-- ============================ Page Title End ================================== -->

    <!-- ============================ Main Section Start ================================== -->
    <section class="gray-light min-sec text-right" dir="rtl">
        <div class="container">
            @if (Session::has('Success_message'))
                @php
                    emotify('success', \Illuminate\Support\Facades\Session::get('Success_message'));
                @endphp
            @endif
            @if ($errors->any())
                @foreach ($errors->all() as $error)
                    @php
                        emotify('error', $error);
                    @endphp
                @endforeach
            @endif
            <div class="row form-submit">
                <div class="col-lg-8 col-md-12 col-sm-12">
                    <div class="product-wrap">
                        <h5> الخدمات المطلوبة </h5>
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th> الخدمة </th>
                                    <th> البائع </th>
                                    <th> السعر </th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($items as $item)
                                    <tr>
                                        <td> {{ $item->serviceData->name }} </td>
                                        <td> {{ $item->userData->name ?? '' }} </td>
                                        <td> {{ \App\Support\Money::show($item->serviceData->price) }} </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="col-lg-4 col-md-12 col-sm-12">
                    <div class="product-wrap">
                        <h5> معلومات الطلب </h5>
                        <ul>
                            <li><strong> عدد الخدمات </strong> {{ $items->count() }}</li>
                            <li><strong> المجموع الكلي </strong> {{ \App\Support\Money::show($total) }}</li>
                        </ul>
                        <p class="text-muted"> الدفع عبر EasyKash (بطاقة، فوري، محفظة) بالجنيه المصري، ويُرسل الطلب للبائعين فور تأكيد الدفع. </p>
                        <form method="post" action="{{ url('checkout/order') }}">
                            @csrf
                            <button type="submit" class="btn btn-primary full-width"> ادفع واتمم الطلب </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- ============================ Main Section End ================================== -->
@endsection
