<!DOCTYPE html>
<html lang="ar">

<head>
    <title>{{ $course->name }}</title>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!-- Metadata for SEO and Social Sharing -->
    <meta name="description"
        content="نفذها هو منصة للخدمات المصغّرة والمشاريع، تجمع بين مقدمي الخدمات المستقلين وأصحاب المشاريع الباحثين عن إنجاز مهامهم بسرعة وكفاءة. سواء كنت تبحث عن تصميم، كتابة محتوى، أو تطوير، ستجد الخبرة المطلوبة هنا.">
    <meta name="keywords" content="نفذها, خدمات مصغرة, مشاريع, مستقلين, تصميم, كتابة, تطوير, خدمات رقمية">
    <meta name="author" content="نفذها">
    <meta property="og:title" content="نفذها - منصة الخدمات المصغرة والمشاريع">
    <meta property="og:description" content="نفذها هو وجهتك المثالية للبحث عن محترفين لتنفيذ خدماتك وأعمالك.">
    <meta property="og:image" content="{{ asset('assets/website/img/logo.png') }}">
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://nafizha.com/"> <!-- استبدل برابط موقعك -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="نفذها - منصة الخدمات المصغرة والمشاريع">
    <meta name="twitter:description"
        content="اكتشف عالمًا من الخدمات المصغّرة التي يقدّمها محترفون مستقلون جاهزون لإنجاز مشاريعك.">
    <meta name="twitter:image" content="{{ asset('assets/website/img/logo.png') }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!-- Favicon and Title -->
    <link rel="icon" href="{{ asset('assets/website/img/favicon.png') }}" type="image/x-icon">
    <title>@yield('title')</title>

    <!-- CSS Files -->
    <link href="{{ asset('assets/website/css/plugins.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/website/css/styles.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.rtl.min.css"
        integrity="sha384-gXt9imSW0VcJVHezoNQsP+TNrjYXoGcrqBZJpry9zJt8PCQjobwmhMGaDHTASo9N" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
    @yield('css')
    <!-- Notifications and Livewire -->
    @notifyCss
    @toastifyCss

    @livewireStyles
</head>
@if (Session::has('Success_message'))
    @php
        toastify()->success(\Illuminate\Support\Facades\Session::get('Success_message'));
    @endphp
@endif
@if ($errors->any())
    @foreach ($errors->all() as $error)
        @php
            toastify()->error($error);
        @endphp
    @endforeach
@endif

<body dir="rtl">
    <div class="hero">
        <img style="height: 70vh;object-fit: cover;width: 100%;"
            src="{{ asset('assets/uploads/public-courses/' . $course->image) }}" alt="">
    </div>

    <!-- Start Course Details Section  -->
    <div class="details-section">
        <div class="container">
            <div class="data" style="text-align: right;direction: rtl">
                {!! $course->description !!}
            </div>
        </div>
    </div>


    <!-- End Course Details Section   -->

    <!-- Start Form Register Section  -->
    <div class="register_form">
        <div class="container">
            <br><br>
            <hr>
            <br>
            <div class="data">
                <form action="{{ route('registercourse', $course['url']) }}" method="post">
                    @csrf
                    <div class="row">
                        <div class="col-lg-6 col-12">
                            <div class="form-group">
                                <label for="name"> الاسم </label>
                                <input type="text" class="form-control" name="name" required>
                            </div>
                        </div>
                        <div class="col-lg-6 col-12">
                            <div class="form-group">
                                <label for="phone"> رقم الهاتف </label>
                                <input type="number" class="form-control" name="phone" required>
                            </div>
                        </div>
                        <div class="col-lg-6 col-12">
                            <div class="form-group">
                                <label for="certificate"> المؤهل الدراسي </label>
                                <input type="text" class="form-control" name="certificate" required>
                            </div>
                        </div>
                        <div class="col-lg-6 col-12">
                            <div class="form-group">
                                <label for="country"> الدوله </label>
                                <input type="text" class="form-control" name="country" required>
                            </div>
                        </div>
                        <div class="col-lg-6 col-12">
                            <div class="form-group">
                                <label for="city"> المحافظه </label>
                                <input type="text" class="form-control" name="city" required>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group">
                                <label for="note"> اسئله واستفسارات </label>
                                <textarea name="note" id="note " class="form-control"></textarea>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group">
                                <button type="submit" class="btn btn-primary"> تسجيل </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- End From Register Section  -->

    <style>
        form {
            text-align: right;
            direction: rtl;
        }
    </style>

    <br><br>
    <hr>
    <br>
    <script src="{{ asset('assets/website/js/jquery.min.js') }}"></script>
    <script src="{{ asset('assets/website/js/popper.min.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous">
    </script>
    @toastifyJs
    @notifyJs
</body>
