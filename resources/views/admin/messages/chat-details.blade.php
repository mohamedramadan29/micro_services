@extends('admin.layouts.master')
@section('css')
@endsection
@section('page-header')
    <!-- breadcrumb -->
    <div class="breadcrumb-header justify-content-between">
        <div class="my-auto">
            <div class="d-flex">
                <h4 class="content-title mb-0 my-auto">الرئيسية</h4><span class="text-muted mt-1 tx-13 mr-2 mb-0">/ تفاصيل
                    المحادثة</span>
            </div>
        </div>
    </div>
    <!-- breadcrumb -->
@endsection
@section('content')
    <!-- row -->
    <div class="row row-sm main-content-app mb-4">
        <div class="col-xl-12 col-lg-12">
            <div class="card">
                <a class="main-header-arrow" href="" id="ChatBodyHide"><i class="icon ion-md-arrow-back"></i></a>
                <div class="main-content-body main-content-body-chat">
                    
                    <div class="main-chat-body" id="ChatBody">
                        <div class="content-inner">

                            @foreach ($messages as $message)
                                <div class="media {{ $message->sender_id == $chat->sender_id ? 'flex-row-reverse' : '' }}">
                                    {{-- <div class="main-img-user online"><img alt=""
                                        src="{{ URL::asset('assets/img/faces/9.jpg') }}"></div> --}}
                                    <div class="media-body">
                                        @if ($message->sender_id == $chat->sender_id)
                                        <span>{{ $chat->sender->name }}</span>
                                    @else
                                        <span>{{ $chat->receiver->name }}</span>
                                    @endif

                                        <div class="main-msg-wrapper right">

                                            {{ $message->body }}
                                        </div>
                                        <span>{{ $message->created_at->diffForHumans() }}</span> <a href=""><i
                                                class="icon ion-android-more-horizontal"></i></a>
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
    <!-- row -->
    </div>
    <!-- Container closed -->
    </div>
    <!-- main-content closed -->
@endsection
@section('js')
    <!--Internal  lightslider js -->
    <script src="{{ URL::asset('assets/plugins/lightslider/js/lightslider.min.js') }}"></script>
    <!--Internal  Chat js -->
    <script src="{{ URL::asset('assets/js/chat.js') }}"></script>
@endsection
