@extends('website.layouts.master')

@section('title', 'آراء العملاء')

@section('css')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">
    <style>
        /* Testimonials Page Styles */
        .testimonials-hero {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 80px 0;
            position: relative;
            overflow: hidden;
        }
        
        .testimonials-hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.05'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E") repeat;
        }
        
        .page-title {
            font-size: 3rem;
            font-weight: bold;
            margin-bottom: 1rem;
        }
        
        .page-subtitle {
            font-size: 1.3rem;
            color: rgba(255, 255, 255, 0.9);
        }
        
        .testimonials-section {
            padding: 80px 0;
            background: #f8f9fa;
        }
        
        .testimonial-card {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            height: 100%;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            border: 1px solid #e9ecef;
        }
        
        .testimonial-card::before {
            content: '"';
            position: absolute;
            top: -20px;
            left: 20px;
            font-size: 100px;
            color: #667eea;
            font-family: Georgia, serif;
            opacity: 0.1;
        }
        
        .testimonial-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
        }
        
        .testimonial-content {
            margin-bottom: 1.5rem;
            position: relative;
            z-index: 1;
        }
        
        .stars {
            color: #ffd700;
            font-size: 1.2rem;
            margin-bottom: 1rem;
        }
        
        .testimonial-text {
            font-size: 1.1rem;
            line-height: 1.8;
            margin: 0;
            color: #495057;
            font-style: italic;
        }
        
        .testimonial-author {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding-top: 1.5rem;
            border-top: 1px solid #e9ecef;
        }
        
        .author-image img {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #667eea;
        }
        
        .author-name {
            margin: 0;
            font-size: 1.1rem;
            font-weight: bold;
            color: #2c3e50;
        }
        
        .author-position {
            margin: 0;
            color: #6c757d;
            font-size: 0.95rem;
        }
        
        .author-company {
            color: #667eea;
            font-weight: 500;
        }
        
        .featured-badge {
            position: absolute;
            top: 20px;
            right: 20px;
            background: linear-gradient(135deg, #ffd700, #ffed4e);
            color: #333;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: bold;
            box-shadow: 0 4px 10px rgba(255, 215, 0, 0.3);
        }
        
        .filter-buttons {
            text-align: center;
            margin-bottom: 3rem;
        }
        
        .filter-btn {
            background: white;
            border: 2px solid #667eea;
            color: #667eea;
            padding: 10px 25px;
            margin: 0 5px;
            border-radius: 25px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: 500;
        }
        
        .filter-btn:hover,
        .filter-btn.active {
            background: #667eea;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
        }
        
        @media (max-width: 768px) {
            .page-title {
                font-size: 2rem;
            }
            
            .testimonial-card {
                padding: 1.5rem;
            }
            
            .testimonial-text {
                font-size: 1rem;
            }
            
            .filter-btn {
                margin: 5px;
                display: inline-block;
            }
        }
    </style>
@endsection

@section('content')
    <!-- Testimonials Hero Section -->
    <section class="testimonials-hero">
        <div class="container">
            <div class="row">
                <div class="col-12 text-center">
                    <h1 class="page-title">آراء عملائنا</h1>
                    <p class="page-subtitle">نفتخر بثقة عملائنا ونسعى دائماً لتقديم أفضل الخدمات</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonials Section -->
    <section class="testimonials-section">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <div class="filter-buttons">
                        <button class="filter-btn active" data-filter="all">الكل</button>
                        <button class="filter-btn" data-filter="featured">مميز</button>
                        <button class="filter-btn" data-filter="5">5 نجوم</button>
                        <button class="filter-btn" data-filter="4">4 نجوم</button>
                    </div>
                </div>
            </div>
            
            <div class="row" id="testimonials-container">
                @php
                    $reviews = \App\Models\front\Review::active()->approved()->ordered()->get();
                @endphp
                @forelse($reviews as $review)
                <div class="col-lg-4 col-md-6 mb-4 testimonial-item" 
                     data-rating="{{ $review->rating }}" 
                     data-featured="{{ $review->is_featured ? 'true' : 'false' }}">
                    <div class="testimonial-card">
                        @if($review->is_featured)
                            <span class="featured-badge">مميز</span>
                        @endif
                        <div class="testimonial-content">
                            <div class="stars">
                                {!! $review->rating_stars !!}
                            </div>
                            <p class="testimonial-text">"{{ $review->content }}"</p>
                        </div>
                        <div class="testimonial-author">
                            <div class="author-image">
                                @if($review->client_image)
                                    <img src="{{ asset($review->client_image) }}" alt="{{ $review->client_name }}">
                                @else
                                    <img src="{{ asset('assets/website/img/user-default.png') }}" alt="Default">
                                @endif
                            </div>
                            <div class="author-info">
                                <h5 class="author-name">{{ $review->client_name }}</h5>
                                <p class="author-position">
                                    {{ $review->client_position }}
                                    @if($review->client_company)
                                        <span class="author-company">@ {{ $review->client_company }}</span>
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                @empty
                <div class="col-12 text-center">
                    <div class="alert alert-info">
                        <h4>لا توجد آراء حالياً</h4>
                        <p>سيتم إضافة آراء العملاء قريباً</p>
                    </div>
                </div>
                @endforelse
            </div>
        </div>
    </section>
@endsection

@section('js')
<script>
$(document).ready(function() {
    // Filter functionality
    $('.filter-btn').click(function() {
        $('.filter-btn').removeClass('active');
        $(this).addClass('active');
        
        var filter = $(this).data('filter');
        
        $('.testimonial-item').each(function() {
            var item = $(this);
            var rating = item.data('rating');
            var featured = item.data('featured');
            
            if (filter === 'all') {
                item.show();
            } else if (filter === 'featured' && featured === 'true') {
                item.show();
            } else if (filter === rating.toString()) {
                item.show();
            } else {
                item.hide();
            }
        });
    });
});
</script>
@endsection
