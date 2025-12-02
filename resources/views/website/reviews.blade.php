@extends('website.layouts.master')
@section('title')
    اراء العملاء
@endsection
@section('content')
    <div class="review_page">
        <!-- Hero Section -->
        <section class="reviews-hero">
            <div class="container position-relative">
                <div class="text-center" data-aos="fade-up">
                    <div class="section-badge bg-opacity-20 mb-4">
                        <i class="bi bi-star"></i>
                        <span>رأيك يهمنا</span>
                    </div>
                    <h1 class="display-4 fw-bold mb-4">شاركنا تقييمك</h1>
                    <p class="lead mb-0">
                        نريد أن نسمع رأيك عن خدماتنا لنستمر في التحسين والتطوير
                    </p>
                </div>
            </div>
        </section>

        <!-- Review Form -->
        <section class="py-5 bg-white">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-lg-8">
                        <div class="review-form-card" data-aos="zoom-in">
                            <div class="text-center mb-4">
                                <i class="bi bi-pencil-square" style="font-size: 3rem; color: #3fb697;"></i>
                                <h2 class="h3 mt-3 mb-2">أضف تقييمك الآن</h2>
                                <p class="text-muted">رأيك يساعدنا على تقديم خدمة أفضل</p>
                            </div>

                            <form method="post" action="{{ route('front.reviews.post') }}"
                                style="text-align: right; direction: rtl;">
                                @csrf
                                <!-- Name Input -->
                                <div class="mb-4">
                                    <label class="form-label fw-bold">
                                        <i class="bi bi-person text-primary ms-2"></i>
                                        الاسم *
                                    </label>
                                    <input type="text" id="reviewerName" class="form-control" name="name"
                                        placeholder="أدخل اسمك الكامل" required
                                        style="border-radius: 15px; border: 2px solid #e5e7eb; padding: 1rem 1.5rem; font-size: 1.05rem;">
                                </div>

                                <!-- Star Rating -->
                                <div class="mb-4">
                                    <label class="form-label fw-bold text-center d-block">
                                        <i class="bi bi-star text-warning ms-2"></i>
                                        التقييم *
                                    </label>

                                    <div class="star-rating" id="starRating">
                                        <input class="d-none" type="radio" name="rating" value="5" id="star5"
                                            required>
                                        <label for="star5"><i class="bi bi-star"></i></label>

                                        <input class="d-none" type="radio" name="rating" value="4" id="star4">
                                        <label for="star4"><i class="bi bi-star"></i></label>

                                        <input class="d-none" type="radio" name="rating" value="3" id="star3">
                                        <label for="star3"><i class="bi bi-star"></i></label>

                                        <input class="d-none" type="radio" name="rating" value="2" id="star2">
                                        <label for="star2"><i class="bi bi-star"></i></label>

                                        <input class="d-none" type="radio" name="rating" value="1" id="star1">
                                        <label for="star1"><i class="bi bi-star"></i></label>
                                    </div>

                                    <div class="rating-labels">
                                        <small>ضعيف جداً</small>
                                        <small>ممتاز جداً</small>
                                    </div>

                                    <div id="selectedRating" class="selected-rating"></div>
                                    <div id="emojiRating" class="emoji-rating"></div>
                                </div>

                                <!-- Comments Textarea -->
                                <div class="mb-4">
                                    <label class="form-label fw-bold">
                                        <i class="fas fa-comment-dots text-info ms-2"></i>
                                        ملاحظاتك (اختياري)
                                    </label>
                                    <textarea id="reviewComments" class="form-control" rows="5" placeholder="شاركنا تجربتك وملاحظاتك حول خدماتنا..."
                                        style="border-radius: 15px; border: 2px solid #e5e7eb; padding: 1rem 1.5rem; font-size: 1.05rem;" name="notes"></textarea>
                                    <small class="text-muted">
                                        <i class="bi bi-info-circle ms-1"></i>
                                        ملاحظاتك ستساعدنا على تحسين خدماتنا
                                    </small>
                                </div>

                                <!-- Submit Button -->
                                <div class="d-grid gap-2">
                                    <button type="submit" class="btn btn-lg"
                                        style="background: linear-gradient(135deg, #3fb697, #20a280); color: white; border-radius: 15px; padding: 1.2rem; font-weight: 700; font-size: 1.1rem;">
                                        <i class="bi bi-send ms-2"></i>
                                        <span>إرسال التقييم</span>
                                    </button>
                                </div>
                            </form>

                            <!-- Success Message -->
                            <div id="successMessage" class="success-message mt-4">
                                <div class="success-icon">
                                    <i class="fas fa-check-circle"></i>
                                </div>
                                <h4 class="text-success mt-3 mb-2">شكراً لك!</h4>
                                <p class="mb-0">تم إرسال تقييمك بنجاح. نقدر وقتك وآرائك القيمة.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Rating Statistics -->
        <section class="py-5 bg-light">
            <div class="container">
                <div class="row justify-content-center mb-5">
                    <div class="col-lg-4 col-md-6 mb-4" data-aos="zoom-in">
                        <div class="rating-stats">
                            <h3 class="mb-3">التقييم الإجمالي</h3>
                            <div class="overall-rating">4.8</div>
                            <div class="stars-display">
                                <i class="bi bi-star"></i>
                                <i class="bi bi-star"></i>
                                <i class="bi bi-star"></i>
                                <i class="bi bi-star"></i>
                                <i class="bi bi-star-half-alt"></i>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-6 col-md-6" data-aos="fade-left">
                        <div class="rating-stats text-start">
                            <h4 class="mb-4">توزيع التقييمات</h4>

                            <div class="rating-bar">
                                <span class="text-muted" style="min-width: 80px;">5 نجوم</span>
                                <div class="bar-container">
                                    <div class="bar-fill" style="width: 75%;" data-aos="slide-left"
                                        data-aos-delay="200">
                                    </div>
                                </div>
                                <span class="fw-bold">75%</span>
                            </div>

                            <div class="rating-bar">
                                <span class="text-muted" style="min-width: 80px;">4 نجوم</span>
                                <div class="bar-container">
                                    <div class="bar-fill" style="width: 18%;" data-aos="slide-left"
                                        data-aos-delay="300">
                                    </div>
                                </div>
                                <span class="fw-bold">18%</span>
                            </div>

                            <div class="rating-bar">
                                <span class="text-muted" style="min-width: 80px;">3 نجوم</span>
                                <div class="bar-container">
                                    <div class="bar-fill" style="width: 5%;" data-aos="slide-left" data-aos-delay="400">
                                    </div>
                                </div>
                                <span class="fw-bold">5%</span>
                            </div>

                            <div class="rating-bar">
                                <span class="text-muted" style="min-width: 80px;">2 نجمتان</span>
                                <div class="bar-container">
                                    <div class="bar-fill" style="width: 1%;" data-aos="slide-left" data-aos-delay="500">
                                    </div>
                                </div>
                                <span class="fw-bold">1%</span>
                            </div>

                            <div class="rating-bar">
                                <span class="text-muted" style="min-width: 80px;">نجمة واحدة</span>
                                <div class="bar-container">
                                    <div class="bar-fill" style="width: 1%;" data-aos="slide-left" data-aos-delay="600">
                                    </div>
                                </div>
                                <span class="fw-bold">1%</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>


    </div>
@endsection

@section('js')
    <!-- AOS Animation -->
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>

    <!-- Custom JS -->
    <script src="assets/js/script.js"></script>

    <!-- Review Form JS -->
    <script>
        // Initialize AOS
        AOS.init({
            duration: 800,
            once: true
        });

        const reviewForm = document.getElementById('reviewForm');
        const starRating = document.getElementById('starRating');
        const selectedRating = document.getElementById('selectedRating');
        const emojiRating = document.getElementById('emojiRating');
        const successMessage = document.getElementById('successMessage');

        // Rating text and emojis
        const ratingTexts = {
            1: 'ضعيف جداً 😞',
            2: 'ضعيف 😕',
            3: 'مقبول 😐',
            4: 'جيد 😊',
            5: 'ممتاز 🤩'
        };

        const ratingEmojis = {
            1: '😞',
            2: '😕',
            3: '😐',
            4: '😊',
            5: '🤩'
        };

        // Handle star rating selection
        starRating.addEventListener('change', function(e) {
            if (e.target.type === 'radio') {
                const rating = e.target.value;
                selectedRating.textContent = ratingTexts[rating];
                emojiRating.textContent = ratingEmojis[rating];

                // Add animation
                selectedRating.style.animation = 'none';
                emojiRating.style.animation = 'none';
                setTimeout(() => {
                    selectedRating.style.animation = '';
                    emojiRating.style.animation = '';
                }, 10);
            }
        });

        // Hover effect for stars
        const stars = starRating.querySelectorAll('label');
        stars.forEach((star, index) => {
            star.addEventListener('mouseenter', function() {
                const value = 5 - index;
                selectedRating.textContent = ratingTexts[value];
                selectedRating.style.opacity = '0.7';
            });

            star.addEventListener('mouseleave', function() {
                const checked = starRating.querySelector('input:checked');
                if (checked) {
                    selectedRating.textContent = ratingTexts[checked.value];
                    selectedRating.style.opacity = '1';
                } else {
                    selectedRating.textContent = '';
                }
            });
        });

        // Handle form submission
        reviewForm.addEventListener('submit', function(e) {
            e.preventDefault();

            const name = document.getElementById('reviewerName').value.trim();
            const rating = starRating.querySelector('input:checked');
            const comments = document.getElementById('reviewComments').value.trim();

            // Validation
            if (!name) {
                alert('الرجاء إدخال اسمك');
                return;
            }

            if (!rating) {
                alert('الرجاء اختيار تقييم');
                return;
            }

            // Collect data
            const reviewData = {
                name: name,
                rating: rating.value,
                comments: comments,
                date: new Date().toISOString()
            };

            // Here you would normally send data to server
            console.log('Review Data:', reviewData);

            // Show loading
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalHTML = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin ms-2"></i> جاري الإرسال...';
            submitBtn.disabled = true;

            // Simulate API call
            setTimeout(() => {
                // Hide form
                reviewForm.style.display = 'none';

                // Show success message
                successMessage.style.display = 'block';
                successMessage.scrollIntoView({
                    behavior: 'smooth',
                    block: 'center'
                });

                // Save to localStorage (optional)
                const reviews = JSON.parse(localStorage.getItem('monajezReviews') || '[]');
                reviews.unshift(reviewData);
                localStorage.setItem('monajezReviews', JSON.stringify(reviews));

                // Reset after 5 seconds
                setTimeout(() => {
                    reviewForm.reset();
                    selectedRating.textContent = '';
                    emojiRating.textContent = '';
                    reviewForm.style.display = 'block';
                    successMessage.style.display = 'none';
                    submitBtn.innerHTML = originalHTML;
                    submitBtn.disabled = false;
                }, 5000);
            }, 1500);
        });

        // Load saved reviews count (optional)
        const savedReviews = JSON.parse(localStorage.getItem('monajezReviews') || '[]');
        if (savedReviews.length > 0) {
            console.log(`Total reviews: ${savedReviews.length}`);
        }
    </script>
@endsection
