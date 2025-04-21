@extends('admin.layouts.master')
@section('title')
    المحادثات
@endsection
@section('css')
@endsection
@section('page-header')
    <!-- breadcrumb -->
    <div class="breadcrumb-header justify-content-between">
        <div class="my-auto">
            <div class="d-flex">
                <h4 class="content-title mb-0 my-auto"> الرئيسية </h4><span class="text-muted mt-1 tx-13 mr-2 mb-0">/
                    المحادثات </span>
            </div>
        </div>
    </div>
    <!-- breadcrumb -->
@endsection
@section('content')
    <!--Row-->
    <div class="row row-sm">
        <div class="col-sm-12 col-md-12 col-lg-12 col-xl-12 grid-margin">
            <div class="card">
                <div class="card-header pb-0">
                    <div class="d-flex justify-content-between">
                        <h4 class="card-title mg-b-0"> المحادثات </h4>
                        <i class="mdi mdi-dots-horizontal text-gray"></i>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive border-top userlist-table">
                        <table class="table card-table table-striped table-vcenter text-nowrap mb-0">
                            <thead>
                                <tr>
                                    <th> # </th>
                                    <th class="wd-lg-8p"><span>الراسل </span></th>
                                    <th class="wd-lg-20p"><span> المستقل </span></th>
                                    <th class="wd-lg-20p"><span> اخر توقيت للرسالة </span></th>
                                    <th class="wd-lg-20p"><span> قسم الرسالة </span></th>
                                    <th class="wd-lg-20p">العمليات </th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($chats as $chat)
                                    <tr>
                                        <th> {{ $loop->iteration }} </th>
                                        <th> {{ $chat->sender->name }} </th>
                                        <th> {{ $chat->receiver->name }} </th>
                                        <th> {{ $chat->last_time_message }} </th>
                                        <th> {{ $chat->chat_type }} </th>
                                        <th> <a href="{{ url('admin/chat/details/' . $chat->id) }}" class="btn btn-info"> تفاصيل
                                                المحادثة </a> </th>
                                    </tr>
                                @endforeach

                            </tbody>
                        </table>
                    </div>
                    {{ $chats->links() }}
                </div>
            </div>
        </div><!-- COL END -->
    </div>
    <!-- row closed  -->
    </div>
    <!-- Container closed -->
    </div>
    <!-- main-content closed -->
@endsection
@section('js')
    <!--Internal  Datepicker js -->
    <script src="{{ URL::asset('assets/plugins/jquery-ui/ui/widgets/datepicker.js') }}"></script>
    <!-- Internal Select2 js-->
    <script src="{{ URL::asset('assets/plugins/select2/js/select2.min.js') }}"></script>
@endsection
