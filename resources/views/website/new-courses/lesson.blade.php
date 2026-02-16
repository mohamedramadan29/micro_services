@extends('website.layouts.master')
@section('title')
    {{ $lesson->title }}
@endsection
@section('css')
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        .lesson-header {
            background: linear-gradient(135deg, #3fb697 0% 0%, #3fb697 100%);
            color: white;
            padding: 40px 0;
        }

        .video-container {
            position: relative;
            padding-bottom: 56.25%;
            height: 0;
            overflow: hidden;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            background: #000;
        }

        .video-container iframe {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            border: none;
            border-radius: 15px;
            pointer-events: none;
        }

        .video-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.8);
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 15px;
            z-index: 10;
        }

        .play-button {
            width: 80px;
            height: 80px;
            background: rgba(255, 255, 255, 0.9);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .play-button:hover {
            background: white;
            transform: scale(1.1);
        }

        .play-button i {
            color: #667eea;
            font-size: 2rem;
            margin-left: 5px;
        }

        .lesson-navigation {
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
            max-height: 400px;
            overflow-y: auto;
        }

        .lesson-list-item {
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            border-left: 3px solid transparent;
        }

        .lesson-list-item:hover {
            background: #f8f9fa;
            border-left-color: #667eea;
        }

        .lesson-list-item.active {
            background: #e3f2fd;
            border-left-color: #2196f3;
        }

        .lesson-list-item.completed {
            opacity: 0.7;
        }

        .lesson-list-item.completed::after {
            content: '✓';
            float: left;
            color: #28a745;
            font-weight: bold;
        }

        .lesson-info {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
            margin-bottom: 30px;
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

        .navigation-buttons {
            display: flex;
            justify-content: space-between;
            margin-top: 30px;
        }

        .nav-button {
            padding: 12px 24px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .nav-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        .nav-prev {
            background: #6c757d;
            color: white;
        }

        .nav-next {
            background: #007bff;
            color: white;
        }

        .video-protection {
            user-select: none;
            -webkit-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
        }

        .video-protection iframe {
            pointer-events: none;
        }

        .video-controls {
            position: absolute;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            gap: 10px;
            z-index: 20;
        }

        .control-btn {
            width: 40px;
            height: 40px;
            background: rgba(255, 255, 255, 0.9);
            border: none;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .control-btn:hover {
            background: white;
            transform: scale(1.1);
        }

        .loading-spinner {
            display: none;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 30;
        }

        .loading-spinner.active {
            display: block;
        }

        /* Hide YouTube branding */
        .video-container iframe::-webkit-scrollbar {
            display: none;
        }

        /* Prevent right-click on video */
        .video-container {
            -webkit-touch-callout: none;
            -webkit-user-select: none;
            -khtml-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
            user-select: none;
        }
    </style>
@endsection
@section('content')
    <!-- Lesson Header -->
    <section class="lesson-header" style="direction: rtl;text-align:right;padding-top:90px">
        <div class="container">
            <nav aria-label="breadcrumb" class="mb-3">
                <ol class="breadcrumb breadcrumb-custom" style="direction: rtl;text-align:right">
                    <li class="breadcrumb-item">
                        <a href="{{ route('courses.index') }}">الكورسات</a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="{{ route('courses.show', $course->slug) }}">{{ $course->title }}</a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="{{ route('courses.topic', [$course->slug, $topic->id]) }}">{{ $topic->title }}</a>
                    </li>
                    <li class="breadcrumb-item active">{{ $lesson->title }}</li>
                </ol>
            </nav>

            <div class="row align-items-center">
                <div class="col-lg-8">
                    <h1 class="display-5 fw-bold mb-2">{{ $lesson->title }}</h1>
                    <p class="lead mb-0">الدرس
                        {{ $topic->lessons->search(function ($item) use ($lesson) {return $item->id === $lesson->id;}) + 1 }}
                        من {{ $topic->lessons->count() }} في موضوع {{ $topic->title }}</p>
                </div>
                <div class="col-lg-4 text-end">
                    @if ($lesson->is_free)
                        <span class="badge bg-success fs-6">درس مجاني</span>
                    @else
                        <span class="badge bg-primary fs-6">درس مدفوع</span>
                    @endif
                    @if ($lesson->duration_minutes)
                        <span class="badge bg-info fs-6 me-2">{{ $lesson->duration_minutes }} دقيقة</span>
                    @endif
                </div>
            </div>
        </div>
    </section>

    <!-- Video Section -->
    <section class="py-5" style="direction: rtl;text-align:right">
        <div class="container">
            <div class="row">
                <!-- Main Content -->
                <div class="col-lg-8">
                    <!-- Video Player -->
                    <div class="video-container video-protection" id="videoContainer">
                        <div class="video-overlay" id="videoOverlay">
                            <div class="play-button" onclick="loadVideo()">
                                <i class="fas fa-play"></i>
                            </div>
                        </div>

                        <div class="loading-spinner" id="loadingSpinner">
                            <div class="spinner-border text-light" role="status">
                                <span class="visually-hidden">جاري التحميل...</span>
                            </div>
                        </div>

                        <div class="video-controls" id="videoControls" style="display: none;">
                            <button class="control-btn" onclick="togglePlay()" title="تشغيل/إيقاف">
                                <i class="fas fa-play" id="playIcon"></i>
                            </button>
                            <button class="control-btn" onclick="toggleMute()" title="كتم/تفعيل الصوت">
                                <i class="fas fa-volume-up" id="muteIcon"></i>
                            </button>
                            <button class="control-btn" onclick="toggleFullscreen()" title="ملء الشاشة">
                                <i class="fas fa-expand"></i>
                            </button>
                        </div>

                        <!-- Hidden iframe for YouTube -->
                        <iframe id="videoFrame" style="display: none;"
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture">
                        </iframe>
                    </div>

                    <!-- Lesson Info -->
                    <div class="lesson-info mt-4">
                        <h3 class="mb-3">عن الدرس</h3>
                        @if ($lesson->description)
                            <div class="lesson-content">
                                {!! $lesson->description !!}
                            </div>
                        @else
                            <p class="text-muted">لا يوجد وصف متاح لهذا الدرس.</p>
                        @endif

                        <div class="mt-4">
                            <h5>معلومات الدرس:</h5>
                            <div class="row">
                                <div class="col-md-4">
                                    <p><strong>الموضوع:</strong> {{ $topic->title }}</p>
                                </div>
                                <div class="col-md-4">
                                    <p><strong>المدة:</strong> {{ $lesson->formatted_duration }}</p>
                                </div>
                                <div class="col-md-4">
                                    <p><strong>النوع:</strong> {{ $lesson->is_free ? 'مجاني' : 'مدفوع' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Navigation -->
                    <div class="navigation-buttons">
                        @if ($previousLesson)
                            <a href="{{ route('courses.lesson', [$course->slug, $topic->id, $previousLesson->id]) }}"
                                class="nav-button nav-prev">
                                <i class="fas fa-arrow-right me-2"></i>
                                الدرس السابق
                            </a>
                        @else
                            <div></div>
                        @endif

                        @if ($nextLesson)
                            <a href="{{ route('courses.lesson', [$course->slug, $topic->id, $nextLesson->id]) }}"
                                class="nav-button nav-next">
                                الدرس التالي
                                <i class="fas fa-arrow-left ms-2"></i>
                            </a>
                        @endif
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="col-lg-4">
                    <div class="lesson-navigation">
                        <h5 class="mb-3">دروس الموضوع</h5>
                        <ul class="lesson-list">
                            @foreach ($topic->lessons as $index => $lessonItem)
                                <li class="lesson-list-item {{ $lessonItem->id == $lesson->id ? 'active' : '' }}"
                                    onclick="window.location.href='{{ route('courses.lesson', [$course->slug, $topic->id, $lessonItem->id]) }}'">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <div class="fw-bold">{{ $index + 1 }}. {{ $lessonItem->title }}</div>
                                            @if ($lessonItem->duration_minutes)
                                                <small class="text-muted">{{ $lessonItem->duration_minutes }} دقيقة</small>
                                            @endif
                                        </div>
                                        @if ($lessonItem->is_free)
                                            <span class="badge bg-success">مجاني</span>
                                        @endif
                                    </div>
                                </li>
                            @endforeach
                        </ul>

                        <div class="mt-4">
                            <a href="{{ route('courses.topic', [$course->slug, $topic->id]) }}"
                                class="btn btn-outline-primary w-100 mb-2">
                                <i class="fas fa-arrow-right me-2"></i>العودة للموضوع
                            </a>
                            <a href="{{ route('courses.show', $course->slug) }}" class="btn btn-outline-secondary w-100">
                                <i class="fas fa-graduation-cap me-2"></i>عرض الكورس
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
        let videoLoaded = false;
        let player = null;

        // Prevent right-click
        document.addEventListener('contextmenu', function(e) {
            if (e.target.closest('.video-protection')) {
                e.preventDefault();
                return false;
            }
        });

        // Prevent keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            if (e.target.closest('.video-protection')) {
                // Prevent F12, Ctrl+Shift+I, Ctrl+Shift+C, Ctrl+U
                if (e.keyCode === 123 ||
                    (e.ctrlKey && e.shiftKey && (e.keyCode === 73 || e.keyCode === 67)) ||
                    (e.ctrlKey && e.keyCode === 85)) {
                    e.preventDefault();
                    return false;
                }
            }
        });

        function loadVideo() {
            const overlay = document.getElementById('videoOverlay');
            const spinner = document.getElementById('loadingSpinner');
            const frame = document.getElementById('videoFrame');
            const controls = document.getElementById('videoControls');

            // Show loading
            overlay.style.display = 'none';
            spinner.classList.add('active');

            // Get video URL from server
            fetch(`/api/courses/video/{{ $lesson->id }}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Load YouTube video with enhanced privacy and API enabled
                        frame.src =
                            `https://www.youtube-nocookie.com/embed/${data.video_id}?rel=0&showinfo=0&modestbranding=1&iv_load_policy=3&controls=1&disablekb=0&fs=1&autohide=1&autoplay=1&enablejsapi=1&origin=${window.location.origin}`;
                        frame.style.display = 'block';

                        // Initialize YouTube player
                        initializeYouTubePlayer(data.video_id);

                        // Hide loading and show controls after a delay
                        setTimeout(() => {
                            spinner.classList.remove('active');
                            controls.style.display = 'flex';
                            videoLoaded = true;
                            // Add loaded class to enable pointer events
                            document.getElementById('videoContainer').classList.add('video-loaded');
                        }, 2000);
                    }
                })
                .catch(error => {
                    console.error('Error loading video:', error);
                    spinner.classList.remove('active');
                    overlay.style.display = 'flex';
                });
        }

        function initializeYouTubePlayer(videoId) {
            // Wait for YouTube API to be ready
            if (typeof YT !== 'undefined' && YT.Player) {
                createPlayer(videoId);
            } else {
                // If API not ready, wait and try again
                setTimeout(() => initializeYouTubePlayer(videoId), 1000);
            }
        }

        function createPlayer(videoId) {
            player = new YT.Player('videoFrame', {
                events: {
                    'onReady': onPlayerReady,
                    'onStateChange': onPlayerStateChange
                }
            });
        }

        function onPlayerReady(event) {
            // Player is ready
            console.log('YouTube player is ready');
        }

        function onPlayerStateChange(event) {
            // Update play/pause button based on player state
            if (event.data === YT.PlayerState.PLAYING) {
                document.getElementById('playIcon').className = 'fas fa-pause';
            } else {
                document.getElementById('playIcon').className = 'fas fa-play';
            }
        }

        function togglePlay() {
            if (player && player.getPlayerState) {
                if (player.getPlayerState() === 1) { // PLAYING
                    player.pauseVideo();
                    document.getElementById('playIcon').className = 'fas fa-play';
                } else {
                    player.playVideo();
                    document.getElementById('playIcon').className = 'fas fa-pause';
                }
            }
        }

        function toggleMute() {
            if (player && player.isMuted) {
                if (player.isMuted()) {
                    player.unMute();
                    document.getElementById('muteIcon').className = 'fas fa-volume-up';
                } else {
                    player.mute();
                    document.getElementById('muteIcon').className = 'fas fa-volume-mute';
                }
            }
        }

        function toggleFullscreen() {
            const container = document.getElementById('videoContainer');
            if (!document.fullscreenElement) {
                container.requestFullscreen().catch(err => {
                    console.log(`Error attempting to enable fullscreen: ${err.message}`);
                });
            } else {
                document.exitFullscreen();
            }
        }

        // YouTube API callback
        function onYouTubeIframeAPIReady() {
            console.log('YouTube API is ready');
        }

        // Prevent dev tools
        (function() {
            const devtools = {
                open: false,
                orientation: null
            };
            const threshold = 160;

            setInterval(() => {
                if (window.outerHeight - window.innerHeight > threshold ||
                    window.outerWidth - window.innerWidth > threshold) {
                    if (!devtools.open) {
                        devtools.open = true;
                        // Optionally redirect or show a message
                        console.log('DevTools detected');
                    }
                } else {
                    devtools.open = false;
                }
            }, 500);
        })();
    </script>

    <!-- Load YouTube API (optional - for advanced controls) -->
    <script src="https://www.youtube.com/iframe_api" async></script>
@endsection
