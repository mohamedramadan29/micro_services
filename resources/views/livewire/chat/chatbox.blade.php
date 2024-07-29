<div>
    @if($selectedConversation)
        <div class="messages-headline">
            @if($reciever_user)
                <img src="{{asset('assets/uploads/users_image/'.$reciever_user->image)}}">
                <h4>  {{ $reciever_user->name }}</h4>
            @endif
        </div>
        <div class="messages_area">
            @foreach($messages as $message)

                <div class="message-plunch {{Auth::id() == $message->sender_id?'me':''}}">
                    <div class="dash-msg-avatar">
                        @if(Auth::id() == $message->sender_id)
                            <img
                                src="{{asset('assets/uploads/users_image/'.\Illuminate\Support\Facades\Auth::user()->image)}}"
                                alt="">
                        @else
                            <img
                                src="{{asset('assets/uploads/users_image/'.$reciever_user->image)}}" alt="">
                        @endif
                    </div>
                    <div class="dash-msg-text">
                        <p> {{$message->body}} </p>
                    </div>
                    <div class="date">
                        {{$message->created_at->diffForHumans()}}
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
