<div>
    @if(count($conversations) > 0)
    <ul>
        @foreach($conversations as $conversation)
            <li>
                <a href="javascript:;" wire:click="ChatUserSelected({{$conversation}},'{{$this->getuserinstance($conversation,$name = 'id')}}')">
                    <div class="dash-msg-avatar"><img src="{{asset('assets/uploads/users_image/'.$this->getuserinstance($conversation,$name='image'))}}"
                                                      alt=""><span
                            class="_user_status online"></span></div>

                    <div class="message-by">
                        <div class="message-by-headline">
                            <h5> {{$this->getuserinstance($conversation,$name='name')}} </h5>
                            <span> {{$conversation->messages->last()->created_at->shortAbsoluteDiffForHumans()}}  </span>
                        </div>
                        <p>  {{$conversation->messages->last()->body}} </p>
                        @php
                            if(count($conversation->messages->where('red',0)->where('receiver_id',Auth()->user()->id))){

                         echo ' <div class="unread_count badge rounded-pill text-light bg-danger">  '
                             . count($conversation->messages->where('red',0)->where('receiver_id',Auth()->user()->id)) .'</div> ';

                            }

                        @endphp
                    </div>
                </a>
            </li>
        @endforeach


    </ul>
    @else
        <div class="alert alert-info"> لا يوجد لديك محادثات في الوقت الحالي  </div>
    @endif
</div>
