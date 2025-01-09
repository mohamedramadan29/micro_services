<div>
    @if ($selectedConversation)
        <div class="message_headline_chatbox">
            <div class="messages-headline">
                @if ($reciever_user)
                    <img src="{{ asset('assets/uploads/users_image/' . $reciever_user->image) }}">
                    <h4> {{ $reciever_user->name }}</h4>
                @endif
            </div>
            <div class="project_details">
                @if ($selectedConversation->project_id != null)
                    @php
                        $project_id = $selectedConversation->project_id;
                        $project = \App\Models\front\Project::findOrFail($project_id);
                        $project_title = $project['title'];
                    @endphp
                    <ul>
                        <li> بخصوص :: <span> <a href="{{ url('project/' . $project['id'] . '-' . $project['slug']) }}">
                                    {{ $project_title }} </a></span>
                        </li>
                        @if ($project->freelancer_id != null && $project['freelancer_id'] == Auth::user()->id)
                            <li> سعر الصفقة :: <span> {{ $project['offer_budget'] }} $ </span></li>
                            <li> عدد الايام :: <span> {{ $project['offer_days'] }} ايام </span></li>
                            <li> حالة المشروع :: <span> {{ $project['status'] }} </span></li>
                        @endif
                        @if ($project['user_id'] == Auth::user()->id && $project['freelancer_id'] != null)
                            <li> سعر الصفقة :: <span> {{ $project['offer_budget'] }} $ </span></li>
                            <li> عدد الايام :: <span> {{ $project['offer_days'] }} ايام </span></li>
                            <li> حالة المشروع :: <span> {{ $project['status'] }} </span></li>
                            <li> تسليم المشروع :: <span>
                                    @if ($project['status'] != 'تم الاستلام')
                                        <button class="btn btn-primary btn-sm" data-bs-toggle="modal"
                                            data-bs-target="#accept_project"> تسليم المشروع </button>
                                    @elseif($project['status'] == 'تم الاستلام')
                                        <button class="btn btn-success btn-sm"> تم تسليم المشروع </button>
                                    @endif
                                </span></li>

                            <div class="modal fade buy_services_model" id="accept_project" tabindex="-1"
                                aria-labelledby="exampleModalLabel" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h1 class="modal-title fs-5" id="exampleModalLabel"> تسليم المشروع </h1>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                aria-label="Close"> X
                                            </button>
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
                                            @if (Auth::check() && $project['user_id'] == Auth::user()->id)
                                                <form style="width: 100%" method="post"
                                                    action="{{ url('accept_project/' . $project['id']) }}">
                                                    <h4> هل انت متاكد من استلام المشروع بشكل نهائي </h4>
                                                    <br>
                                                    <br>
                                                    <br>
                                                    @csrf
                                                    <button type="submit" class="btn global_button"> تسليم المشروع <i
                                                            class="bi bi-check"></i></button>
                                                </form>
                                            @else
                                                <a href="{{ url('login') }}" type="button" class="btn btn-primary">
                                                    سجل دخولك اولا </a>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </ul>
                @endif
            </div>
        </div>

        <div class="messages_area" id="messagesContainer">
            @foreach ($messages as $message)
                <div
                    class="message {{ $loop->last ? 'new' : '' }} message-plunch {{ Auth::id() == $message->sender_id ? 'me' : '' }}">
                    <div class="dash-msg-avatar">
                        @if (Auth::id() == $message->sender_id)
                            <img src="{{ asset('assets/uploads/users_image/' . \Illuminate\Support\Facades\Auth::user()->image) }}"
                                alt="">
                        @else
                            <img src="{{ asset('assets/uploads/users_image/' . $reciever_user->image) }}" alt="">
                        @endif
                    </div>
                    <div class="dash-msg-text">
                        <p> {{ $message->body }} </p>
                    </div>
                    <div class="date">
                        {{ $message->created_at->diffForHumans() }}
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
