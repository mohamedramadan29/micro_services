@extends('website.layouts.master')
@section('title')
    '{{ $topic->title }}'
@endsection
@section('css')
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        .topic-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 60px 0;
            margin-bottom: 40px;
        }

        .lesson-card {
            transition: all 0.3s ease;
            border: none;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
            cursor: pointer;
            position: relative;
        }

        .lesson-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.15);
        }

        .lesson-thumbnail {
            width: 100%;
            height: 180px;
            object-fit: cover;
            background: linear-gradient(45deg, #667eea, #764ba2);
        }

        .play-overlay {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 60px;
            height: 60px;
            background: rgba(255, 255, 255, 0.9);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #667eea;
            font-size: 1.5rem;
            transition: all 0.3s ease;
        }

        .lesson-card:hover .play-overlay {
            background: white;
            transform: translate(-50%, -50%) scale(1.1);
        }

        .duration-badge {
            position: absolute;
            bottom: 10px;
            right: 10px;
            background: rgba(0, 0, 0, 0.7);
            color: white;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
        }

        .free-badge {
            position: absolute;
            top: 10px;
            left: 10px;
            background: #28a745;
            color: white;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
        }

        .lesson-number {
            position: absolute;
            top: 10px;
            right: 10px;
            background: rgba(102, 126, 234, 0.9);
            color: white;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }

        .breadcrumb-custom {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50px;
            padding: 10px 20px;
        }

        .breadcrumb-custom .breadcrumb-item a {
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
        }

        .breadcrumb-custom .breadcrumb-item.active {
            color: white;
        }

        .topic-stats {
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
            color: white;
        }

        .stat-label {
            color: rgba(255, 255, 255, 0.8);
            font-size: 0.9rem;
        }

        .progress-section {
            background: white;
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
            margin-bottom: 30px;
        }

        .course-navigation {
            position: sticky;
            top: 20px;
            background: white;
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
        }

        .lesson-list {
            list-style: none;
            padding: 0;
        }

        .lesson-list-item {
            padding: 10px;
            border-radius: 8px;
            margin-bottom: 5px;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        .lesson-list-item:hover {
            background: #f8f9fa;
        }

        .lesson-list-item.active {
            background: #e3f2fd;
            border-left: 4px solid #2196f3;
        }
    </style>
@endsection
@section('content')
    <!-- ============================ Hero Banner  Start================================== -->
    <div class="hero-banner bg-cover"
        style="background:#00000057 url({{ asset('assets/website/img/courses-hero.jpeg') }}) no-repeat; direction: rtl;text-align:right" data-overlay="7">
        <div class="container">
            <nav aria-label="breadcrumb" class="mb-4">
                <ol class="breadcrumb breadcrumb-custom">
                    <li class="breadcrumb-item">
                        <a href="{{ route('courses.index') }}">الكورسات</a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="{{ route('courses.show', $course->slug) }}">{{ $course->title }}</a>
                    </li>
                    <li class="breadcrumb-item active">{{ $topic->title }}</li>
                </ol>
            </nav>

            <div class="row align-items-center">
                <div class="col-lg-8">
                    <h1 class="display-4 fw-bold mb-4">{{ $topic->title }}</h1>

                    @if ($topic->description)
                        <p class="lead mb-4">{{ $topic->description }}</p>
                    @endif

                    <div class="topic-stats">
                        <div class="stat-item">
                            <div class="stat-number">{{ $topic->lessons_count }}</div>
                            <div class="stat-label">درس</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-number">{{ $topic->total_duration }}</div>
                            <div class="stat-label">دقيقة</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-number">{{ round($topic->total_duration / 60, 1) }}</div>
                            <div class="stat-label">ساعة</div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="text-center">
                        <div class="topic-icon mx-auto mb-3" style="width: 100px; height: 100px; font-size: 3rem;">
                            <i class="fas fa-book"></i>
                        </div>
                        <h4>{{ $course->title }}</h4>
                        <p class="text-white-50">الموضوع {{ $topic->sort_order + 1 }} من {{ $course->topics->count() }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- ============================ Hero Banner End ================================== -->


    <!-- Progress Section -->
    @if (auth()->check())
        <section class="mb-1" style="padding:25px 0 9px;">
            <div class="container">
                <div class="progress-section">
                    <h5 class="mb-3">تقدمك في هذا الموضوع</h5>
                    <div class="progress" style="height: 25px;">
                        <div class="progress-bar bg-success" role="progressbar" style="width: 0%" aria-valuenow="0"
                            aria-valuemin="0" aria-valuemax="100">
                            0% مكتمل
                        </div>
                    </div>
                    <small class="text-muted mt-2 d-block">لقد أكملت 0 من {{ $topic->lessons_count }} درس</small>
                </div>
            </div>
        </section>
    @endif

    <!-- Lessons Grid -->
    <section class="py-5" style="text-align: right;direction:rtl">
        <div class="container">
            <div class="row">
                <!-- Main Content -->
                <div class="col-lg-8">
                    <h2 class="mb-4">دروس الموضوع</h2>

                    <div class="row">
                        @forelse($topic->lessons as $index => $lesson)
                            <div class="col-lg-6 mb-4">
                                <div class="card lesson-card"
                                    onclick="window.location.href='{{ route('courses.lesson', [$course->slug, $topic->id, $lesson->id]) }}'">
                                    <div class="position-relative">
                                        @if ($lesson->video_id)
                                            <img src="https://img.youtube.com/vi/{{ $lesson->video_id }}/hqdefault.jpg"
                                                class="lesson-thumbnail" alt="{{ $lesson->title }}">
                                        @else
                                            <div class="lesson-thumbnail d-flex align-items-center justify-content-center">
                                                <i class="fas fa-play-circle text-white" style="font-size: 3rem;"></i>
                                            </div>
                                        @endif

                                        <div class="lesson-number">{{ $index + 1 }}</div>
                                        <div class="play-overlay">
                                            <i class="fas fa-play"></i>
                                        </div>

                                        @if ($lesson->is_free)
                                            <span class="free-badge">مجاني</span>
                                        @endif

                                        @if ($lesson->duration_minutes)
                                            <span class="duration-badge">{{ $lesson->duration_minutes }} دقيقة</span>
                                        @endif
                                    </div>

                                    <div class="card-body">
                                        <h5 class="card-title">{{ $lesson->title }}</h5>
                                        @if ($lesson->description)
                                            <p class="card-text text-muted">{{ Str::limit($lesson->description, 80) }}</p>
                                        @endif

                                        <div class="d-flex justify-content-between align-items-center">
                                            <span class="badge bg-{{ $lesson->is_free ? 'success' : 'primary' }}">
                                                {{ $lesson->is_free ? 'درس مجاني' : 'درس مدفوع' }}
                                            </span>
                                            <small class="text-muted">الترتيب: {{ $lesson->sort_order + 1 }}</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="col-12 text-center py-5">
                                <i class="fas fa-video fa-3x text-muted mb-3"></i>
                                <h4 class="text-muted">لا توجد دروس متاحة حالياً</h4>
                                <p class="text-muted">سيتم إضافة الدروس قريباً</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="col-lg-4">
                    <div class="course-navigation">
                        <h5 class="mb-3">المواضيع الأخرى</h5>
                        <ul class="lesson-list">
                            @foreach ($course->topics as $otherTopic)
                                <li class="lesson-list-item {{ $otherTopic->id == $topic->id ? 'active' : '' }}">
                                    <a href="{{ route('courses.topic', [$course->slug, $otherTopic->id]) }}"
                                        class="text-decoration-none">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span>{{ $otherTopic->title }}</span>
                                            <span class="badge bg-secondary">{{ $otherTopic->lessons_count }}</span>
                                        </div>
                                    </a>
                                </li>
                            @endforeach
                        </ul>

                        <div class="mt-4">
                            <a href="{{ route('courses.show', $course->slug) }}" class="btn btn-outline-primary w-100">
                                <i class="fas fa-arrow-right me-2"></i>العودة للكورس
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
@section('js')
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function openLesson(lessonId) {
            window.location.href = `/courses/lesson/${lessonId}`;
        }

        // Add smooth animations
        document.addEventListener('DOMContentLoaded', function() {
            const cards = document.querySelectorAll('.lesson-card');
            cards.forEach((card, index) => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';

                setTimeout(() => {
                    card.style.transition = 'all 0.5s ease';
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, index * 100);
            });
        });
    </script>
@endsection
