@extends('website.layouts.master')
@section('title')
    اختر نوع الحساب
@endsection
@section('content')

    <section class="gray-bg text-right register_page" dir="rtl">
        <div class="container">
            <div class="row">
                <div class="col-lg-2 col-0"></div>
                <div class="col-lg-8 col-12">
                    <div class="modal-content" id="registermodal">
                        <div class="modal-header">
                            <h4> أكمل حسابك </h4>
                        </div>
                        <div class="modal-body">
                            <p class="mb-3"> أهلًا {{ Auth::user()->name }}، كيف تريد استخدام نفّذها؟ </p>
                            <div class="login-form">
                                <form method="post" action="{{ route('account.type.update') }}">
                                    @csrf
                                    <div class="form-group">
                                        <div class="account-type-options">
                                            <input type="radio" class="btn-check" name="account_type" id="type_client"
                                                value="{{ \App\Models\User::TYPE_CLIENT }}" required>
                                            <label class="account-type-option" for="type_client">
                                                <i class="bi bi-briefcase"></i>
                                                <strong> عميل </strong>
                                                <span> أطلب تنفيذ مشاريعي وأستقبل العروض </span>
                                            </label>

                                            <input type="radio" class="btn-check" name="account_type" id="type_freelancer"
                                                value="{{ \App\Models\User::TYPE_FREELANCER }}">
                                            <label class="account-type-option" for="type_freelancer">
                                                <i class="bi bi-person-workspace"></i>
                                                <strong> مستقل </strong>
                                                <span> أقدّم عروضي على طلبات العملاء </span>
                                            </label>
                                        </div>
                                    </div>
                                    @include('website.user.partials.whatsapp-field')
                                    <div class="form-group">
                                        <button type="submit" class="btn btn-primary dark-2 btn-md full-width pop-login">
                                            متابعة
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-2 col-0"></div>
            </div>
        </div>
    </section>
@endsection
