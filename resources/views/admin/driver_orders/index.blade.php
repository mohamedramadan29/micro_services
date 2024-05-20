@extends('admin.layouts.master')
@section('title')
    ادارة شحناتي
@endsection
@section('css')
    <link href="{{ URL::asset('assets/admin.php/plugins/datatable/css/dataTables.bootstrap4.min.css') }}" rel="stylesheet"/>
    <link href="{{ URL::asset('assets/admin.php/plugins/datatable/css/buttons.bootstrap4.min.css') }}" rel="stylesheet">
    <link href="{{ URL::asset('assets/admin.php/plugins/datatable/css/responsive.bootstrap4.min.css') }}" rel="stylesheet"/>
    <link href="{{ URL::asset('assets/admin.php/plugins/datatable/css/jquery.dataTables.min.css') }}" rel="stylesheet">
    <link href="{{ URL::asset('assets/admin.php/plugins/datatable/css/responsive.dataTables.min.css') }}" rel="stylesheet">
    <link href="{{ URL::asset('assets/admin.php/plugins/select2/css/select2.min.css') }}" rel="stylesheet">
@endsection
@section('content')
    <!-- breadcrumb -->
    <div class="breadcrumb-header justify-content-between">
        <div class="my-auto">
            <div class="d-flex">
                <h4 class="content-title mb-0 my-auto">الرئيسية </h4><span
                        class="text-muted mt-1 tx-13 mr-2 mb-0">/     ادارة شحناتي    </span>
            </div>
        </div>
    </div>
    <!-- breadcrumb -->
    <!-- row -->
    <div class="row row-sm">

        <!-- Col -->
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
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
                    <div class="mb-4 main-content-label"> ادارة شحناتي</div>
                    <div class="card-body">
                        <button class="btn btn-warning-gradient btn-sm"
                                data-target="#send_order_to_client"
                                data-toggle="modal" class="dropdown-item">
                             تسليم الشحنة الي العميل
                        </button>

                        <button class="btn btn-warning-gradient btn-sm"
                                data-target="#send_allorder_to_supervisor"
                                data-toggle="modal" class="dropdown-item">
                             ارسال الشحنة الي المندوب
                        </button>

                        <form style="margin-top: 10px" method="post" class="change_orders_status_form"
                              action="{{url('admin.php/orders/confirm_all')}}">
                            @csrf
                            <input type="hidden" name="orders" id="selected_orders" class="selected_orders"
                                   value="">
                            <button type="submit" class="btn btn-primary-gradient btn-sm change_orders_status2">
                                تاكيد استلام الشحنات بعد الالتقاط
                            </button>
                        </form>
                        <form method="post" action="{{url('admin.php/orders/driver_search')}}">
                            @csrf
                            <div class="d-flex justify-content-center align-items-center flex-wrap">
                                <div style="margin: 10px">
                                    <label class="form-label"> نوع الشحنة </label>
                                    <select class="form-control select2" name="order_type">
                                        <option value=""> -- نوع الشحنة --</option>
                                        <option @if(isset($_REQUEST['order_type']) && $_REQUEST['order_type'] == 'استلام') selected
                                                @endif  value="استلام"> استلام
                                        </option>
                                        <option @if(isset($_REQUEST['order_type']) && $_REQUEST['order_type'] == 'ارسال') selected
                                                @endif  value="ارسال"> ارسال
                                        </option>
                                    </select>
                                </div>
                                <div style="margin: 10px">
                                    <label class="form-label"> المدينة </label>
                                    <select class="form-control select2" name="city">
                                        <option value=""> -- حدد المدنية --</option>
                                        @foreach($citizen as $city)
                                            <option @if(isset($_REQUEST['city']) && $_REQUEST['city'] == $city['name']) selected
                                                    @endif value="{{$city['name']}}">  {{$city['name']}} </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div style="margin: 10px">
                                    <label class="form-label"> حالة الشحنة </label>
                                    <select class="form-control select2" name="order_status">
                                        <option value=""> -- حدد حالة الشحنة --</option>
                                        <option @if(isset($_REQUEST['order_status']) && $_REQUEST['order_status'] == 'في انتظار الموافقة') selected
                                                @endif  value="في انتظار الموافقة"> في انتظار الموافقة
                                        </option>
                                        <option @if(isset($_REQUEST['order_status']) && $_REQUEST['order_status'] == 'تمت الموافقة') selected
                                                @endif value="تمت الموافقة"> تمت الموافقة
                                        </option>
                                    </select>
                                </div>
                                <div style="">
                                    <label> <br></label>
                                    <button style="margin-top: 21px;width: 80px;" type="submit" name="search"
                                            class="btn btn-primary-gradient btn-sm"> بحث <i class="fa fa-search"></i>
                                    </button>
                                </div>
                            </div>
                        </form>
                        <div class="table-responsive">
                            <table class="table text-md-nowrap" id="example2">
                                <thead>
                                <tr>
                                    <th class="wd-15p border-bottom-0"> رقم الشحنة</th>
                                    <th>
                                        <input style="cursor: pointer" id="order_check_select_all" type="checkbox"
                                               name="select_all">
                                    </th>
                                    <th class="wd-15p border-bottom-0"> نوع الشحنة</th>
                                    <th class="wd-15p border-bottom-0"> المدينه</th>
                                    <th class="wd-15p border-bottom-0"> السعر</th>
                                    <th class="wd-15p border-bottom-0"> حاله الشحنة</th>
                                    <th class="wd-15p border-bottom-0"> العمليات</th>
                                </tr>
                                </thead>
                                <tbody>
                                @php
                                    $i = 1;
                                @endphp
                                @foreach($orders as $order)
                                    <tr>
                                        <td> {{$order['id']}} </td>

                                        <td><input style="cursor: pointer" id="order_check_select"
                                                   class="order_check_single" type="checkbox"
                                                   name="select_row" value="{{$order['id']}}">
                                        </td>
                                        <td> {{$order['order_type']}} </td>
                                        <td> {{$order['city']}}  </td>
                                        <td> {{$order['price']}}  </td>
                                        <td><span class="badge badge-danger"> {{$order['status']}} </span></td>
                                        <td>
                                            <a class="btn btn-primary btn-sm"
                                               href="{{url('admin.php/orders/edit/'.$order['id'])}}"> تفاصيل الشحنة </a>

                                            @if(Auth::user()->type=='driver' && $order['order_type'] == 'ارسال' && $order['status'] !='تمت التصفية المالية' && $order['status'] !='تسليم الشحنة فقط' && $order['status'] !='تسليم الشحنة واستلام القيمة')
                                                <button style="margin-top: 10px"
                                                        data-target="#change_status_order_model_{{$order['id']}}"
                                                        data-toggle="modal" class="btn btn-warning btn-sm"> تسليم الشحنة
                                                    الي العميل <i
                                                            class="fa fa-save"></i>
                                                </button>
                                            @endif

                                            <!--  Send Order To Supervisor -->
                                            @if(\Illuminate\Support\Facades\Auth::user()->type == 'driver' && $order['order_type'] == 'استلام' )
                                                <button style="margin-top: 10px"
                                                        data-target="#send_order_to_supervisor_model_{{$order['id']}}"
                                                        data-toggle="modal" class="btn btn-warning btn-sm"> ارسال الشحنة
                                                    الي المندوب <i
                                                            class="fa fa-save"></i>
                                                </button>
                                            @endif
                                            <!----------------  Driver Confrim   - ------->
                                            @if(Auth::user()->type == 'driver' && $order['driver_id'] == Auth::user()->id && $order['order_type'] == 'ارسال')
                                                @if($order['driver_confirm'] == 1)
                                                @else
                                                    <br>
                                                    <form method="post"
                                                          action="{{url('admin.php/orders/driver_confirm')}}">
                                                        @csrf
                                                        <input type="hidden" name="order_id"
                                                               value="{{$order['id']}}">
                                                        <input type="hidden" name="driver_confirm" value="1">
                                                        <button style="margin-top:10px"
                                                                class="btn btn-warning btn-sm"> تاكيد استلام
                                                        </button>
                                                    </form>

                                                @endif
                                            @endif


                                        </td>
                                    </tr>
                                    @include('admin.driver_orders.change_status')
                                    @include('admin.driver_orders.send_order_to_supervisor')
                                    @include('admin.driver_orders.send_orders_clients')
                                    @include('admin.driver_orders.send_allorder_to_supervisor')
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div><!-- bd -->
                </div>

            </div>
        </div>
        <!-- /Col -->
    </div>
    <!-- row closed -->
    </div>
    <!-- Container closed -->
    </div>
    <!-- main-content closed -->
@endsection

@section('js')
    <script src="{{ URL::asset('assets/admin.php/plugins/datatable/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ URL::asset('assets/admin.php/plugins/datatable/js/dataTables.dataTables.min.js') }}"></script>
    <script src="{{ URL::asset('assets/admin.php/plugins/datatable/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ URL::asset('assets/admin.php/plugins/datatable/js/responsive.dataTables.min.js') }}"></script>
    <script src="{{ URL::asset('assets/admin.php/plugins/datatable/js/jquery.dataTables.js') }}"></script>
    <script src="{{ URL::asset('assets/admin.php/plugins/datatable/js/dataTables.bootstrap4.js') }}"></script>
    <script src="{{ URL::asset('assets/admin.php/plugins/datatable/js/dataTables.buttons.min.js') }}"></script>
    <script src="{{ URL::asset('assets/admin.php/plugins/datatable/js/buttons.bootstrap4.min.js') }}"></script>
    <script src="{{ URL::asset('assets/admin.php/plugins/datatable/js/jszip.min.js') }}"></script>
    <script src="{{ URL::asset('assets/admin.php/plugins/datatable/js/pdfmake.min.js') }}"></script>
    <script src="{{ URL::asset('assets/admin.php/plugins/datatable/js/vfs_fonts.js') }}"></script>
    <script src="{{ URL::asset('assets/admin.php/plugins/datatable/js/buttons.html5.min.js') }}"></script>
    <script src="{{ URL::asset('assets/admin.php/plugins/datatable/js/buttons.print.min.js') }}"></script>
    <script src="{{ URL::asset('assets/admin.php/plugins/datatable/js/buttons.colVis.min.js') }}"></script>
    <script src="{{ URL::asset('assets/admin.php/plugins/datatable/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ URL::asset('assets/admin.php/plugins/datatable/js/responsive.bootstrap4.min.js') }}"></script>
    <!--Internal  Datatable js -->
    <script src="{{ URL::asset('assets/admin.php/js/table-data.js') }}"></script>
@endsection



<!-- تضمين jQuery -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>

<script type="text/javascript">
    $(document).ready(function () {
        // عند النقر على زر تغيير حالة الشحنات
        $('.change_orders_status2').on('click', function () {
            var selectedOrders = [];
            // الحصول على الطلبات المحددة
            $('input[name="select_row"]:checked').each(function () {
                selectedOrders.push($(this).val());
            });
            // تحديث قيمة حقل الإدخال الخفي
            $('.selected_orders').val(selectedOrders.join(','));

            // إرسال الطلبات المحددة إلى الخادم
            $('.change_orders_status_form').submit();
        });
        // تحديد الكل
        document.getElementById("order_check_select_all").addEventListener("change", function () {
            var checkboxes = document.querySelectorAll('.order_check_single');
            checkboxes.forEach(function (checkbox) {
                checkbox.checked = document.getElementById("order_check_select_all").checked;
            });
        });

        // إلغاء تحديد الكل عند إلغاء تحديد أحد الـ checkbox
        document.querySelectorAll('.order_check_single').forEach(function (checkbox) {
            checkbox.addEventListener('change', function () {
                var allChecked = true;
                document.querySelectorAll('.order_check_single').forEach(function (checkbox) {
                    if (!checkbox.checked) {
                        allChecked = false;
                    }
                });
                document.getElementById("order_check_select_all").checked = allChecked;
            });
        });

    });
</script>
