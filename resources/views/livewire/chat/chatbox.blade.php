<div>
    @if($selectedConversation)
        <div class="messages-headline">
            @if($reciever_user)
                <img src="{{asset('assets/uploads/users_image/'.$reciever_user->image)}}">
                <h4>  {{ $reciever_user->name }}</h4>
            @endif
        </div>
        <div class="messages_area" id="messagesContainer" >
            @foreach($messages as $message)
                <div class="message {{ $loop->last ? 'new' : '' }} message-plunch {{Auth::id() == $message->sender_id?'me':''}}">
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
                <style>
                    .message {
                        transition: transform 0.3s ease-in-out;
                    }

                    .message.new {
                        transform: translateY(-40px);
                    }
                </style>
                <script>
                    window.addEventListener('scrollMessages', () => {
                        const messagesContainer = document.getElementById('messagesContainer');
                        messagesContainer.scrollTo({
                            top: messagesContainer.scrollTop + 40, // تحريك الرسائل 30 بكسل للأعلى
                            behavior: 'smooth' // إضافة تأثير الحركة السلسة
                        });
                    });
                </script>
        </div>
    @endif
</div>

