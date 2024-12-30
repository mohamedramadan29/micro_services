@extends('website.layouts.master')
@section('title')
    رصيد الحساب
@endsection
@section('content')
    <!-- ============================ Main Section Start ================================== -->
    <section class="gray-bg pt-4 text-right profile_page" dir="rtl">
        <div class="container-fluid">
            <div class="row m-0">

                <div class="col-xl-3 col-lg-4 col-md-12 col-sm-12">
                    <div class="dashboard-navbar">

                        <div class="d-user-avater">
                            @if (Auth::user()->image != '')
                                <img src="{{ asset('assets/uploads/users_image/' . Auth::user()->image) }}"
                                    class="img-fluid rounded" alt="">
                            @else
                                <img src="{{ asset('assets/website/img/avatar.png') }}" class="img-fluid rounded"
                                    alt="">
                            @endif

                            <h4> {{ Auth::user()->user_name }} </h4>
                            <span> {{ Auth::user()->email }} </span>
                        </div>

                        <div class="d-navigation">
                            <ul id="metismenu">
                                <li><a href="{{ url('dashboard') }}"><i class="ti-dashboard"></i> الملف الشخصي </a>
                                </li>
                                <li><a href="{{ url('balance') }}"><i class="bi bi-credit-card"></i> الرصيد </a></li>
                                <li><a href="{{ url('my/project/index') }}"><i class="bi bi-cast"></i> المشاريع </a></li>
                                <li><a href="{{ url('my/project/add') }}"><i class="ti-plus"></i> اضف مشروع جديد </a></li>
                                <li><a href="{{ url('my/courses') }}"> <i class="bi bi-mortarboard-fill"></i> الكورسات </a>
                                </li>
                                <li><a href="{{ url('my/course/add') }}"><i class="ti-plus"></i> اضف كورس جديد </a></li>
                                <li><a href="{{ url('service/index') }}"><i class="bi bi-database-fill-check"></i> الخدمات
                                    </a></li>
                                <li><a href="{{ url('service/add') }}"><i class="ti-plus"></i> اضف خدمة جديدة </a></li>
                                <li><a href="{{ url('chats') }}"> <i class="bi bi-chat-dots-fill"></i> المحادثات </a>
                                </li>
                                <li><a href="{{ url('tickets') }}"><i class="bi bi-ticket"></i> تذاكري </a></li>
                                {{-- <li><a href="{{ url('reviews') }}"><i class="ti-email"></i> التقيمات </a></li> --}}
                                <li><a href="{{ url('update-account') }}"> <i class="bi bi-gear-fill"></i> تعديل الملف
                                        الشخصي
                                    </a></li>
                                <li><a href="{{ url('logout') }}"><i class="ti-power-off"></i> تسجيل خروج </a></li>
                            </ul>
                        </div>

                    </div>
                </div>

                <!-- Item Wrap Start -->
                <div class="col-xl-9 col-lg-8 col-md-12 col-sm-12">
                    <div class="row">
                        <div class="col-lg-12 col-md-12 col-sm-12">
                            <!-- Breadcrumbs -->
                            <div class="bredcrumb_wrap">
                                <nav aria-label="breadcrumb">
                                    <ol class="breadcrumb">
                                        <li class="breadcrumb-item"><a href="{{ url('/') }}"> الرئيسية </a></li>
                                        <li class="breadcrumb-item active" aria-current="page"> حسابي</li>
                                        <li class="breadcrumb-item active" aria-current="page"> رصيد الحساب</li>
                                    </ol>
                                </nav>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-lg-12 col-md-12 col-sm-12">

                            <!-- Single Wrap -->
                            <div class="_dashboard_content">
                                <div class="_dashboard_content_header justify-content-between">
                                    <div class="_dashboard__header_flex">
                                        <h4><i class="fa fa-user mr-1"></i> رصيد الحساب </h4>
                                    </div>
                                    <div class="buttons">
                                        <button style="height: 40px" type="button" class="btn btn-primary btn-sm"
                                            data-bs-toggle="modal" data-bs-target="#charge_balance">
                                            شحن رصيد <i class="bi bi-plus"></i>
                                        </button>
                                        <button style="height: 40px;background-color:#ffca2c;" type="button"
                                            class="btn btn-warning btn-sm" data-bs-toggle="modal"
                                            data-bs-target="#minus_balance"> سحب رصيد
                                            <i class="bi bi-dash"></i>
                                        </button>
                                        <!-- Modal -->
                                        <div class="modal fade buy_services_model" id="charge_balance" tabindex="-1"
                                            aria-labelledby="exampleModalLabel" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h1 class="modal-title fs-5" id="exampleModalLabel"> شحن رصيد
                                                        </h1>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                            aria-label="Close"> X </button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <ul>
                                                            <li> منصة نفذها تضمن حقوقك بنسبة 100% .</li>
                                                            <li> لا تتردد ابداً في التواصل معنا إذا احتجت أي مساعدة وسنسعد
                                                                بخدمتك.
                                                            </li>
                                                            <li class="text-danger">
                                                                سيتم خصم عمولة Stripe بنسبة 2.9%
                                                                + 0.30 دولار من المبلغ المدفوع. </li>
                                                        </ul>
                                                    </div>
                                                    <div class="modal-footer">
                                                        @if (Auth::check())
                                                            <form style="width: 100%" method="post"
                                                                action="{{ url('charge_balance') }}">
                                                                @csrf

                                                                <div class="form-group">
                                                                    <label> ادخل المبلغ </label>
                                                                    <input id="price-input" style="height: 45px"
                                                                        type="number" class="form-control" name="price"
                                                                        required>
                                                                </div>
                                                                <div class="form-group">
                                                                    <label> المبلغ الصافي بعد خصم العمولة </label>
                                                                    <input id="net-amount" style="height: 45px"
                                                                        type="text" class="form-control" readonly>
                                                                </div>
                                                                <script src="https://checkout.stripe.com/checkout.js" class="stripe-button" data-key="{{ env('STRIPE_KEY') }}"
                                                                    data-amount="" data-name="شحن رصيد" data-description="إدخال المبلغ المطلوب لشحن الرصيد"
                                                                    data-image="{{ asset('assets/website/img/favicon.png') }}" data-locale="auto" data-currency="usd"
                                                                    data-label="شحن الرصيد"></script>
                                                            </form>
                                                        @else
                                                            <a href="{{ url('login') }}" type="button"
                                                                class="btn btn-primary"> سجل دخولك الان لتكملة الشحن </a>
                                                        @endif
                                                    </div>

                                                    <script>
                                                        document.getElementById('price-input').addEventListener('input', function() {
                                                            const price = parseFloat(this.value) || 0; // المبلغ المدخل
                                                            const feePercentage = 2.9 / 100; // نسبة العمولة
                                                            const fixedFee = 0.30; // العمولة الثابتة بالدولار
                                                            const commission = (price * feePercentage) + fixedFee; // حساب العمولة
                                                            const netAmount = price - commission; // المبلغ الصافي

                                                            // عرض المبلغ الصافي
                                                            document.getElementById('net-amount').value = netAmount > 0 ? netAmount.toFixed(2) + ' USD' : '';
                                                        });
                                                    </script>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- ######### عملية سحب الرصيد   ########## -->
                                        <div class="modal fade buy_services_model" id="minus_balance" tabindex="-1"
                                            aria-labelledby="exampleModalLabel" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h1 class="modal-title fs-5" id="exampleModalLabel"> سحب رصيد
                                                        </h1>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                            aria-label="Close"> X </button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <ul>
                                                            <li> منصة نفذها تضمن حقوقك بنسبة 100% .</li>
                                                            <li> لا تتردد ابداً في التواصل معنا إذا احتجت أي مساعدة وسنسعد
                                                                بخدمتك.
                                                            </li>

                                                        </ul>
                                                    </div>
                                                    <div class="modal-footer">
                                                        @if (Auth::check())
                                                            <form style="width: 100%" method="post"
                                                                action="{{ url('withdraw_balance') }}">
                                                                @csrf
                                                                <div class="form-group">
                                                                    <label> ادخل المبلغ </label>
                                                                    <input required id="price-input" style="height: 45px"
                                                                        type="number" class="form-control"
                                                                        name="amount" required
                                                                        max="{{ Auth::user()->balance }}">
                                                                </div>
                                                                <div class="form-group">
                                                                    <label> حدد وسيلة السحب </label>
                                                                    <select required name="method" class="form-select"
                                                                        required>
                                                                        <option value="" selected disabled> - حدد
                                                                            وسيلة الدفع - </option>
                                                                        <option value="paypal"> Paypal </option>
                                                                        <option value="bank"> الحوالة البنكية </option>
                                                                    </select>
                                                                </div>
                                                                <div class="form-group">
                                                                    <label> البريد الالكتروني </label>
                                                                    <input style="text-align: right;direction:rtl" required
                                                                        id="price-input" style="height: 45px"
                                                                        type="email" class="form-control"
                                                                        name="paypal_mail" required>
                                                                </div>
                                                                <button type="submit" class="btn btn-primary"> سحب الرصيد
                                                                    <i class="bi bi-dash"></i> </button>
                                                            </form>
                                                        @else
                                                            <a href="{{ url('login') }}" type="button"
                                                                class="btn btn-primary"> سجل دخولك الان لتكملة الشحن </a>
                                                        @endif
                                                    </div>

                                                    <script>
                                                        document.getElementById('price-input').addEventListener('input', function() {
                                                            const price = parseFloat(this.value) || 0; // المبلغ المدخل
                                                            const feePercentage = 2.9 / 100; // نسبة العمولة
                                                            const fixedFee = 0.30; // العمولة الثابتة بالدولار
                                                            const commission = (price * feePercentage) + fixedFee; // حساب العمولة
                                                            const netAmount = price - commission; // المبلغ الصافي

                                                            // عرض المبلغ الصافي
                                                            document.getElementById('net-amount').value = netAmount > 0 ? netAmount.toFixed(2) + ' USD' : '';
                                                        });
                                                    </script>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="_dashboard_content balance_page">
                                    <div class="_dashboard_content_body">
                                        <div class="_dashboard_list_group">
                                            <div class="row">
                                                <div class="col-lg-4 col-6">
                                                    <div class="info">
                                                        <h4> الرصيد القابل للسحب </h4>
                                                        <span> {{Auth::user()->balance }} $ </span>
                                                    </div>
                                                </div>
                                                <div class="col-lg-4 col-6">
                                                    <div class="info">
                                                        <h4> طلبات السحب   </h4>
                                                        <span> {{ $WithDrawLastOrder['amount'] ?? '0' }} $ </span>
                                                    </div>
                                                </div>
                                                <div class="col-lg-4 col-6">
                                                    <div class="info">
                                                        <h4> الرصيد الكلي </h4>
                                                        <span> {{ number_format(Auth::user()->balance, 2) }} $ </span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="orders_payments">
                                                @foreach ($ChargeTransactions as $charge)
                                                    <div class="_list_jobs_wraps mng_list shadow_0 border">
                                                        <div class="_list_jobs_f1ex first">
                                                            <div class="_list_110">
                                                                @php
                                                                    $lasttotal =
                                                                        $charge['payment_amount'] -
                                                                        $charge['payment_fee'];
                                                                @endphp
                                                                <div class="count_add_balance"> + {{ $lasttotal }} $
                                                                </div>
                                                                <div class="_list_110_caption">
                                                                    <h4 class="_jb_title">
                                                                        <a href="#">
                                                                            شحن رصيد
                                                                        </a>
                                                                    </h4>
                                                                    <ul class="_grouping_list">
                                                                        <li><span> {{ $lasttotal }} $ <i
                                                                                    class="ti-credit-card"></i>
                                                                            </span>
                                                                        </li>
                                                                        <li>
                                                                            {{ $charge['created_at'] }}
                                                                            <span>
                                                                                <i class="ti-timer"></i>
                                                                            </span>
                                                                        </li>
                                                                    </ul>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach

                                                @foreach ($WithDrawTransactions as $transaction)
                                                    <div class="_list_jobs_wraps mng_list shadow_0 border">
                                                        <div class="_list_jobs_f1ex first">
                                                            <div class="_list_110">
                                                                <div class="minues_balance"> - {{ $transaction['amount'] }} $ </div>
                                                                <div class="_list_110_caption">
                                                                    <h4 class="_jb_title"><a href="#">
                                                                    طلب سحب اموال
                                                                    </a>
                                                                    </h4>
                                                                    <ul class="_grouping_list">
                                                                        <li><span> {{ $transaction['amount'] }}  $ <i class="ti-credit-card"></i>
                                                                            </span>
                                                                        </li>
                                                                        <li><span><i class="ti-timer"></i> {{ $transaction['created_at'] }}
                                                                            </span>
                                                                        </li>
                                                                    </ul>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach


                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

                </div>
            </div>
    </section>
    <!-- ============================ Main Section End ================================== -->
@endsection
