@extends('website.layouts.master')
@section('title')
    '{{ $course->title }}'
@endsection
@section('css')
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        .course-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 60px 0;
            margin-bottom: 40px;
        }

        .course-image-header {
            width: 100%;
            max-height: 400px;
            object-fit: cover;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }

        .topic-card {
            transition: all 0.3s ease;
            border: none;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
            cursor: pointer;
        }

        .topic-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.15);
        }

        .topic-icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.5rem;
            margin-bottom: 1rem;
        }

        .course-stats {
            display: flex;
            gap: 2rem;
            margin-bottom: 2rem;
        }

        .stat-item {
            text-align: center;
        }

        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            color: #007bff;
        }

        .stat-label {
            color: #6c757d;
            font-size: 0.9rem;
        }

        .course-description {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
            margin-bottom: 40px;
        }

        .price-badge {
            font-size: 2rem;
            font-weight: bold;
            padding: 10px 20px;
            border-radius: 50px;
            display: inline-block;
        }

        .free-badge {
            background: #3fb697;
            color: white;
        }

        .paid-badge {
            background: #007bff;
            color: white;
        }

        .breadcrumb-custom {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50px;
            padding: 10px 20px;
            text-align: right !important;
            direction: rtl !important;

        }

        .breadcrumb-custom .breadcrumb-item a {
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
        }

        .breadcrumb-custom .breadcrumb-item.active {
            color: white;
        }
    </style>
@endsection
@section('content')

    <!-- ============================ Hero Banner  Start================================== -->
    <div class="hero-banner bg-cover"
        style="background:#00000057 url({{ asset('assets/website/img/courses-hero.jpeg') }}) no-repeat;" data-overlay="7">
        <div class="container">
            <nav aria-label="breadcrumb" class="mb-4">
                <ol class="breadcrumb breadcrumb-custom">
                    <li class="breadcrumb-item">
                        <a href="{{ route('courses.index') }}">الكورسات</a>
                    </li>
                    <li class="breadcrumb-item active">{{ $course->title }}</li>
                </ol>
            </nav>

            <div class="row align-items-center">
                <div class="col-lg-8">
                    <h1 class="display-4 fw-bold mb-4">{{ $course->title }}</h1>
                    @if ($course->is_free)
                        <div class="price-badge free-badge mb-3">
                            <i class="fas fa-gift me-2"></i>مجاني
                        </div>
                    @else
                        <div class="price-badge paid-badge mb-3">
                            <i class="fas fa-crown me-2"></i>{{ number_format($course->price, 2) }} ريال
                        </div>
                    @endif
                </div>

                <div class="col-lg-4">
                    @if ($course->image)
                        <img src="{{ asset($course->image) }}" class="course-image-header" alt="{{ $course->title }}">
                    @else
                        <img src="{{ asset('assets/admin/img/default-course.jpg') }}" class="course-image-header"
                            alt="Default Course">
                    @endif
                </div>
            </div>
        </div>
    </div>
    <!-- ============================ Hero Banner End ================================== -->


    <!-- Course Description -->
    {{-- <section class="mb-5">
        <div class="container">
            <nav aria-label="breadcrumb" class="mb-4">
                <ol class="breadcrumb breadcrumb-custom">
                    <li class="breadcrumb-item">
                        <a href="{{ route('courses.index') }}">الكورسات</a>
                    </li>
                    <li class="breadcrumb-item active">{{ $course->title }}</li>
                </ol>
            </nav>

            <div class="row align-items-center">
                <div class="col-lg-8">
                    <h1 class="display-4 fw-bold mb-4">{{ $course->title }}</h1>
                    @if ($course->is_free)
                        <div class="price-badge free-badge mb-3">
                            <i class="fas fa-gift me-2"></i>مجاني
                        </div>
                    @else
                        <div class="price-badge paid-badge mb-3">
                            <i class="fas fa-crown me-2"></i>{{ number_format($course->price, 2) }} ريال
                        </div>
                    @endif
                </div>

                <div class="col-lg-4">
                    @if ($course->image)
                        <img src="{{ asset($course->image) }}" class="course-image-header" alt="{{ $course->title }}">
                    @else
                        <img src="{{ asset('assets/admin/img/default-course.jpg') }}" class="course-image-header"
                            alt="Default Course">
                    @endif
                </div>
            </div>
        </div>
    </section> --}}

    <!-- Course Topics -->
    <section class="py-5 bg-light">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="display-5 fw-bold">محتوى الكورس</h2>
                <p class="lead text-muted">استكشف المواضيع والدروس المتاحة في هذا الكورس</p>
            </div>

            <div class="row">
                @forelse($course->topics as $index => $topic)
                    <div class="col-lg-6 mb-4">
                        <div class="card topic-card" style="direction: rtl;text-align:right"
                            onclick="window.location.href='{{ route('courses.topic', [$course->slug, $topic->id]) }}'">
                            <div class="card-body">
                                <div class="d-flex align-items-start">
                                    <div class="topic-icon">
                                        <i
                                            class="fas fa-{{ ['book', 'graduation-cap', 'laptop-code', 'chart-line', 'shield-alt', 'database'][$index % 6] }}"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h4 class="card-title">{{ $topic->title }}</h4>
                                        @if ($topic->description)
                                            <p class="card-text text-muted">{{ Str::limit($topic->description, 120) }}</p>
                                        @endif

                                        <div class="d-flex justify-content-between align-items-center mt-3">
                                            <div class="d-flex align-items-center">
                                                <i class="fas fa-play-circle me-2 text-primary"></i>
                                                <span>{{ $topic->lessons_count }} درس</span>
                                            </div>
                                            <div class="d-flex align-items-center">
                                                <i class="fas fa-clock me-2 text-info"></i>
                                                <span>{{ $topic->total_duration }} دقيقة</span>
                                            </div>
                                        </div>

                                        <div class="mt-3">
                                            <a href="{{ route('courses.topic', [$course->slug, $topic->id]) }}"
                                                class="btn btn-primary">
                                                <i class="fas fa-arrow-left me-2"></i>استكشف الموضوع
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12 text-center py-5">
                        <i class="fas fa-folder-open fa-3x text-muted mb-3"></i>
                        <h4 class="text-muted">لا توجد مواضيع متاحة حالياً</h4>
                        <p class="text-muted">سيتم إضافة المحتوى قريباً</p>
                    </div>
                @endforelse
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    @if ($course->topics->count() > 0)
        <section class="py-5">
            <div class="container">
                <div class="text-center">
                    <h2 class="display-5 fw-bold mb-4">ابدأ رحلتك التعليمية الآن</h2>
                    <p class="lead text-muted mb-4">انضم إلى آلاف الطلاب الذين استفادوا من كورساتنا</p>

                    @if ($course->is_free)
                        <a href="{{ route('courses.topic', [$course->slug, $course->topics->first()->id]) }}"
                            class="btn btn-success btn-lg me-3">
                            <i class="fas fa-play me-2"></i>ابدأ الكورس المجانى
                        </a>
                    @else
                        <button class="btn btn-primary btn-lg me-3" onclick="showPurchaseModal()">
                            <i class="fas fa-shopping-cart me-2"></i>اشترك الآن
                        </button>
                    @endif

                    <a href="{{ route('courses.index') }}" class="btn btn-outline-secondary btn-lg">
                        <i class="fas fa-arrow-right me-2"></i>المزيد من الكورسات
                    </a>
                </div>
            </div>
        </section>
    @endif
@endsection
@section('js')
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function showPurchaseModal() {
            // You can implement a purchase modal here
            alert('سيتم تفعيل نظام الشراء قريباً');
        }

        // Add smooth scrolling
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                e.preventDefault();
                document.querySelector(this.getAttribute('href')).scrollIntoView({
                    behavior: 'smooth'
                });
            });
        });
    </script>
@endsection
