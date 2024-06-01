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

                    <h2 class="ipt-title">الرئيسية  </h2>
                    <span class="ipn-subtitle"> اتمام عملية الشراء  </span>

                </div>
            </div>
        </div>
    </div>
    <!-- ============================ Page Title End ================================== -->

    <!-- ============================ Main Section Start ================================== -->
    <section class="gray-light min-sec text-right" dir="rtl" >
        <div class="container">
            <div class="row form-submit">
                <div class="col-lg-8 col-md-12 col-sm-12">
                    <!-- row -->
                    <div class="row m-0">
                        <div class="panel-group pay_opy980" id="payaccordion">

                            <!-- Pay By Paypal -->
                            <div class="panel panel-default">
                                <div class="panel-heading" id="pay">
                                    <h4 class="panel-title">
                                        <a data-toggle="collapse" role="button" data-parent="#payaccordion" href="#payPal" aria-expanded="true"  aria-controls="payPal" class="">PayPal<img src="assets/img/paypal.png" class="img-fluid" alt=""></a>
                                    </h4>
                                </div>
                                <div id="payPal" class="panel-collapse collapse show" aria-labelledby="pay" data-parent="#payaccordion">
                                    <div class="panel-body">
                                        <form>
                                            <div class="form-group">
                                                <label>PayPal Email</label>
                                                <input type="text" class="form-control  with-light" placeholder="paypal@gmail.com">
                                            </div>
                                            <div class="form-group">
                                                <button type="submit" class="btn dark-2 btm-md full-width">Pay 400.00 USD</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <!-- Pay By Debit or credtit -->
                            <div class="panel panel-default">
                                <div class="panel-heading" id="dabit">
                                    <h4 class="panel-title">
                                        <a data-toggle="collapse"  role="button" href="#payaccordion" data-target="#debitPay" aria-expanded="flase"  aria-controls="debitPay" class="">Debit Or Credit<img src="assets/img/debit.png" class="img-fluid" alt=""></a>
                                    </h4>
                                </div>
                                <div id="debitPay" class="panel-collapse collapse" aria-labelledby="dabit" data-parent="#payaccordion">
                                    <div class="panel-body">
                                        <form>

                                            <div class="row">
                                                <div class="col-lg-6 col-md-6 col-sm-12">
                                                    <div class="form-group">
                                                        <label>Card Holder Name</label>
                                                        <input type="text" class="form-control with-light">
                                                    </div>
                                                </div>

                                                <div class="col-lg-6 col-md-6 col-sm-12">
                                                    <div class="form-group">
                                                        <label>Card Number</label>
                                                        <input type="text" class="form-control with-light">
                                                    </div>
                                                </div>

                                                <div class="col-lg-5 col-md-5 col-sm-6">
                                                    <div class="form-group">
                                                        <label>Expire Month</label>
                                                        <input type="text" class="form-control with-light">
                                                    </div>
                                                </div>

                                                <div class="col-lg-5 col-md-5 col-sm-6">
                                                    <div class="form-group">
                                                        <label>Expire Year</label>
                                                        <input type="text" class="form-control with-light">
                                                    </div>
                                                </div>

                                                <div class="col-lg-2 col-md-2 col-sm-12">
                                                    <div class="form-group">
                                                        <label>CVC</label>
                                                        <input type="text" class="form-control with-light">
                                                    </div>
                                                </div>

                                                <div class="col-lg-12 col-md-12 col-sm-12">
                                                    <div class="form-group">
                                                        <input id="ct-2" class="checkbox-custom" name="ct-2" type="checkbox">
                                                        <label for="ct-2" class="checkbox-custom-label">By Continuing, you ar'e agree to conditions</label>
                                                    </div>
                                                </div>

                                                <div class="col-lg-12 col-md-12 col-sm-12">
                                                    <div class="form-group text-center">
                                                        <a href="#" class="btn dark-2 full-width">Pay 202.00 USD</a>
                                                    </div>
                                                </div>

                                            </div>

                                        </form>
                                    </div>
                                </div>
                            </div>

                        </div>

                    </div>
                    <!--/row -->

                </div>

                <!-- Col-lg 4 -->
                <div class="col-lg-4 col-md-12 col-sm-12">
                    <div class="row">
                        <div class="col-lg-12 col-md-12 col-sm-12">
                            <div class="product-wrap">
                                <h5> معلومات الطلب  </h5>
                                <ul>
                                    <li><strong> المجموع الفرعي </strong> {{ Session::get('sub_total')}} $ </li>
                                    <li><strong> الرسوم </strong> {{ Session::get('tax_price') }} $ </li>
                                    <li><strong>المجموع الكلي </strong> {{Session::get('last_total')}} $ </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- /col-lg-4 -->

            </div>
        </div>
    </section>
    <!-- ============================ Main Section End ================================== -->
    @endsection
