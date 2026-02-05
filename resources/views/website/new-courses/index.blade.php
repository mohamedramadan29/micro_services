@extends('website.layouts.master')
@section('title')
    الكورسات
@endsection
@section('css')
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        .course-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border: none;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
        }

        .course-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.15);
        }

        .course-image {
            height: 200px;
            object-fit: cover;
            transition: transform 0.3s ease;
        }

        .course-card:hover .course-image {
            transform: scale(1.05);
        }

        .course-badge {
            position: absolute;
            top: 10px;
            left: 10px;
            z-index: 10;
        }

        .course-price {
            font-size: 1.5rem;
            font-weight: bold;
            color: #007bff;
        }

        .course-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 1px solid #eee;
        }

        .hero-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 80px 0;
            margin-bottom: 50px;
        }

        .search-box {
            max-width: 500px;
            margin: 0 auto;
        }

        .filter-section {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 30px;
        }

        .stats-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
        }

        .stats-number {
            font-size: 2rem;
            font-weight: bold;
            color: #007bff;
        }
    </style>
@endsection
@section('content')
    <!-- Hero Section -->
    <!-- ============================ Hero Banner  Start================================== -->
    <div class="hero-banner bg-cover center"
        style="background:#00000057 url({{ asset('assets/website/img/courses-hero.jpeg') }}) no-repeat;" data-overlay="7">
        <div class="container">
            <h1> الكورسات التدريبية المتقدمة</h1>
            <p class="lead mb-4">احصل على المعرفة والمهارات التي تحتاجها للنجاح </p>
            <div class="search-box">
                <div class="input-group">
                    <button class="btn bt-round btn--2" type="button">
                        <i class="fas fa-search"></i>
                    </button>
                    <input type="text" class="form-control lio-rad text-right" style="direction: rtl"
                        placeholder="ابحث عن كورس..." id="searchInput">
                </div>
            </div>
        </div>
    </div>
    <!-- ============================ Hero Banner End ================================== -->

    <!-- Main Content -->
    <section class="py-5" style="direction: rtl">
        <div class="container">
            <!-- Filter Section -->
            <div class="filter-section">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <h5 class="mb-0">تصفية الكورسات</h5>
                    </div>
                    <div class="col-md-6">
                        <select class="form-select form-control" id="sortSelect">
                            <option value="default">الترتيب الافتراضي</option>
                            <option value="newest">الأحدث أولاً</option>
                            <option value="oldest">الأقدم أولاً</option>
                            {{-- <option value="price-low">السعر: من الأقل للأعلى</option>
                            <option value="price-high">السعر: من الأعلى للأقل</option> --}}
                            <option value="popular">الأكثر شعبية</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Courses Grid -->
            <div class="row" id="coursesContainer">
                @forelse($courses as $course)
                    <div class="col-lg-4 col-md-6 mb-4 course-item" data-price="{{ $course->is_free ? 0 : $course->price }}"
                        data-date="{{ $course->created_at->timestamp }}">
                        <div class="card course-card h-100">
                            <div class="position-relative">
                                @if ($course->image)
                                    <img src="{{ asset($course->image) }}" class="card-img-top course-image"
                                        alt="{{ $course->title }}">
                                @else
                                    <img src="{{ asset('assets/admin/img/default-course.jpg') }}"
                                        class="card-img-top course-image" alt="Default Course">
                                @endif

                                @if ($course->is_free)
                                    <span class="badge bg-success course-badge">مجاني</span>
                                @else
                                    <span class="badge bg-primary course-badge">{{ number_format($course->price, 2) }}
                                        ريال</span>
                                @endif
                            </div>

                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title">{{ $course->title }}</h5>

                                @if ($course->short_description)
                                    <p class="card-text text-muted">{{ Str::limit($course->short_description, 100) }}</p>
                                @endif

                                <div class="course-meta">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-book me-2 text-primary"></i>
                                        <span>{{ $course->topics_count }} موضوع</span>
                                    </div>
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-play-circle me-2 text-info"></i>
                                        <span>{{ $course->lessons_count }} درس</span>
                                    </div>
                                </div>

                                <div class="mt-auto">
                                    <a href="{{ route('courses.show', $course->slug) }}" class="btn btn-primary w-100">
                                        <i class="fas fa-arrow-left me-2"></i>عرض التفاصيل
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12 text-center py-5">
                        <i class="fas fa-graduation-cap fa-3x text-muted mb-3"></i>
                        <h4 class="text-muted">لا توجد كورسات متاحة حالياً</h4>
                        <p class="text-muted">سيتم إضافة كورسات جديدة قريباً</p>
                    </div>
                @endforelse
            </div>
        </div>
    </section>
@endsection
@section('js')
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        $(document).ready(function() {
            // Search functionality
            $('#searchInput').on('input', function() {
                var searchTerm = $(this).val().toLowerCase();

                $('.course-item').each(function() {
                    var title = $(this).find('.card-title').text().toLowerCase();
                    var description = $(this).find('.card-text').text().toLowerCase();

                    if (title.includes(searchTerm) || description.includes(searchTerm)) {
                        $(this).show();
                    } else {
                        $(this).hide();
                    }
                });
            });

            // Sort functionality
            $('#sortSelect').on('change', function() {
                var sortBy = $(this).val();
                var container = $('#coursesContainer');
                var items = container.find('.course-item').get();

                items.sort(function(a, b) {
                    var aVal, bVal;

                    switch (sortBy) {
                        case 'newest':
                            aVal = parseInt($(a).data('date'));
                            bVal = parseInt($(b).data('date'));
                            return bVal - aVal;
                        case 'oldest':
                            aVal = parseInt($(a).data('date'));
                            bVal = parseInt($(b).data('date'));
                            return aVal - bVal;
                        case 'price-low':
                            aVal = parseFloat($(a).data('price'));
                            bVal = parseFloat($(b).data('price'));
                            return aVal - bVal;
                        case 'price-high':
                            aVal = parseFloat($(a).data('price'));
                            bVal = parseFloat($(b).data('price'));
                            return bVal - aVal;
                        default:
                            return 0;
                    }
                });

                container.empty().append(items);
            });
        });
    </script>
@endsection
