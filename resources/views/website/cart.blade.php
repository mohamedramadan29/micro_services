@extends('website.layouts.master')
@section('title')
    سلة المشتريات
@endsection
@section('content')
    <!-- ============================ Page Title Start================================== -->
    <div class="page-title" style="height: 350px;text-align: right">
        <div class="container">
            <div class="row">
                <div class="col-lg-12 col-md-12">

                    <h2 class="ipt-title"> الرئيسية </h2>
                    <span class="ipn-subtitle"> سلة المشتريات  </span>

                </div>
            </div>
        </div>
    </div>
    <!-- ============================ Page Title End ================================== -->

    <!-- ============================ Main Section Start ================================== -->
    <section class="gray-bg text-right" dir="rtl">
        <div class="container">
            <div class="card">
                @if(Session::has('Success_message'))
                    <div
                        class="alert alert-success"> {{Session::get('Success_message')}} </div>
                @endif
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                <div class="card-body cart_details">
                    @if($count_items > 0)
                        <table class="table table-bordered">
                            <thead>
                            <tr>
                                <th> الخدمة</th>
                                <th> مرات الطلب</th>
                                <th> التكلفة</th>
                                <th></th>
                            </tr>
                            </thead>
                            <tbody>
                            @php
                                $sub_total = 0;
                                $tax_price = 5;
                            @endphp
                            @foreach($items as $item)
                                @php
                                    $sub_total = $sub_total + $item['serviceData']['price'];
                                @endphp
                                <tr>
                                    <td>
                                        <div class="serv_data">
                                            <div class="image">
                                                <a href="{{url('service/'.$item['serviceData']['id'].'-'.$item['serviceData']['slug'])}}">
                                                    <img
                                                        src="{{asset('assets/uploads/services/'.$item['serviceData']['image'])}}"
                                                        alt="">
                                                </a>
                                            </div>
                                            <div class="info">
                                                <h4>
                                                    <a href="{{url('service/'.$item['serviceData']['id'].'-'.$item['serviceData']['slug'])}}"> {{$item['serviceData']['name']}} </a>
                                                </h4>
                                                <p><a href="{{url('user/'.$item['userData']['user_name'])}}"> <i
                                                            class="fa fa-user"></i> {{$item['userData']['name']}}
                                                    </a></p>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <select class="" name="quantity">
                                            <option value="1">1</option>
                                            <option value="2">2</option>
                                            <option value="3">3</option>
                                            <option value="4">4</option>
                                            <option value="5">5</option>
                                            <option value="6">6</option>
                                            <option value="7">7</option>
                                            <option value="8">8</option>
                                            <option value="9">9</option>
                                            <option value="10">10</option>
                                        </select>
                                    </td>
                                    <td>
                                        {{ number_format($item['serviceData']['price'],2)}} $
                                    </td>
                                    <td>
                                        <form method="post" action="{{url('cart/delete')}}">
                                            @csrf
                                            <input type="hidden" name="cartId" value="{{$item['id']}}">
                                            <button type="submit"
                                                    onclick="return confirm(' هل انت متاكد من حذف الخدمة !!  ')"
                                                    class="btn btn-outline-danger btn-sm"> حذف
                                            </button>
                                        </form>

                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                        <table class="table table-bordered">
                            <thead>
                            <tr>
                                <th> الاجمالي</th>
                                <th> {{$sub_total}} $

                                </th>
                            </tr>
                            <tr>
                                <th>
                                    الرسوم
                                </th>
                                <th> {{$tax_price}} $</th>
                            </tr>
                            <tr>
                                <th> المجموع الكلي</th>
                                <th>
                                    @php $last_total = $sub_total + $tax_price; @endphp
                                    {{$last_total}}
                                    $
                                    @php \Illuminate\Support\Facades\Session::put('sub_total',$sub_total);
                                    Session::put('tax_price',$tax_price);
                                    \Illuminate\Support\Facades\Session::put('last_total',$last_total);
                                    @endphp
                                </th>
                            </tr>
                            <tr>
                                <th></th>
                                <td>
                                    @if(\Illuminate\Support\Facades\Auth::check())
                                        <a href="{{url('checkout')}}" style="padding: 10px" type="submit"
                                           class="btn btn-primary btn-sm"> اتمام
                                            الشراء
                                        </a>
                                    @else
                                        <a href="{{url('login')}}" style="padding: 10px" type="submit"
                                           class="btn btn-primary btn-sm"> اتمام
                                            الشراء
                                        </a>
                                    @endif

                                </td>
                            </tr>
                            </thead>
                        </table>
                    @else
                        <div class="alert alert-info">
                            لا يوجد اي خدمات في سلة المشتريات <a href="{{url('services')}}"
                                                                 class="btn btn-primary btn-sm"> مشاهدة الخدمات </a>
                        </div>
                    @endif

                </div>
            </div>

        </div>
    </section>
    <!-- ============================ Main Section End ================================== -->

@endsection
