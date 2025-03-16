@extends('admin.layouts.master')
@section('title')
   تفاصيل التذكرة
@endsection

<style>


</style>
@section('content')
    <!-- breadcrumb -->
    <div class="breadcrumb-header justify-content-between">
        <div class="my-auto">
            <div class="d-flex">
                <h4 class="content-title mb-0 my-auto">الرئيسية </h4><span class="text-muted mt-1 tx-13 mr-2 mb-0">/ تفاصيل التذكرة   </span>
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
                    <div class="mb-4 main-content-label">  تفاصيل التذكرة  </div>

                        <div class="messages-container margin-top-0">

                            <div class="messages-container-inner">
                                <!-- Message Content -->
                                <div class="dash-msg-content">
                                    @foreach ($messages as $message)
                                        <div
                                            class="message-plunch {{ $message['sender_type'] == 'user' ? 'me' : '' }}">
                                            <div class="dash-msg-avatar">
                                                @if ($message['sender_type'] == 'user' && Auth::user()->image != '')
                                                    <img src="{{ asset('assets/uploads/users_image/' . Auth::user()->image) }}"
                                                         class="img-fluid rounded" alt="">
                                                @else
                                                    <img src="{{ asset('assets/website/img/favicon.png') }}"
                                                         class="img-fluid rounded" alt="">
                                                @endif
                                            </div>

                                            <div class="dash-msg-text">
                                                <h6 class="user_name">
                                                    {{ $message['sender_type'] == 'user' ? Auth::user()->user_name : 'فريق دعم نفذها' }}
                                                </h6>
                                                <p> {{ $message['content'] }} </p>
                                                @if ($message['file'])
                                                    <a href="{{ asset('assets/uploads/tickets/' . $message['file']) }}" target="_blank">
                                                        <button class="btn btn-primary btn-sm mt-2">
                                                            <i class="bi bi-file-earmark-pdf"></i>  عرض الملف
                                                        </button>
                                                    </a>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach

                                    <div class="clearfix"></div>
                                    <form method="POST" action="{{ url('admin/message/create/'.$ticket_id) }}" enctype="multipart/form-data">
                                        @csrf
                                        <div class="message-reply">
                                            <textarea name="message" required class="form-control with-light" placeholder=" اكتب رسالتك  "></textarea>
                                            <br>
                                            <div class="form-group">
                                                <label for="file">إضافة ملف</label>
                                                <input type="file" name="file" class="form-control" accept="image/*">
                                            </div>
                                            <button type="submit" class="btn dark-2 btn-primary"> ارسال <i class="bi bi-send"></i> </button>
                                        </div>
                                    </form>
                                </div>
                            </div>

                        </div>
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

