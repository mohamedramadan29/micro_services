@extends('website.layouts.master')
@section('title')
    نفذها - الباقات
@endsection

@section('content')
    <div class="packages">
        <!-- Header Section -->
        <div class="header-section">

            <h1 class="main-title">اختر الباقة المناسبة لك</h1>
            <p class="main-subtitle">
                استمتع بأفضل الخدمات الرقمية مع باقاتنا المتنوعة. جميع الباقات تشمل دعم فني على مدار الساعة
            </p>

        </div>

        <!-- Pricing Cards -->
        <div class="pricing-container" dir="rtl" style="text-align: right">

            @foreach ($packagesGrouped as $category => $packages)
                <!-- عنوان الفئة (مثلاً: تصميم المواقع - برمجة - تطبيقات موبايل ...) -->
                <h2 style="text-align:center; margin:50px 0 30px; font-size:2rem; color:#333;">
                    {{ $category }}
                </h2>

                <!-- نقسم الباكدجات كل 3 في صف واحد -->
                @foreach ($packages->chunk(3) as $threePackages)
                    <div class="row justify-content-center" style="margin-bottom:40px;">

                        @foreach ($threePackages as $package)
                            <div class="col-lg-4 col-md-6 col-sm-12">

                                <!-- الكرت بتاعك بالظبط زي ما كتبته من غير أي تغيير -->
                                <div class="pricing-card basic">

                                    <div class="card-icon">
                                        <i class="bi bi-box"></i>
                                    </div>

                                    <h2 class="card-title"> {{ $package->name }} </h2>

                                    <div class="pricing">
                                        <div class="price">
                                            <span id="basicPrice"> {{ number_format($package->price, 2) }} </span>
                                            <span class="currency"> دولار </span>
                                        </div>
                                    </div>

                                    <ul class="features-list">
                                        @php
                                            $features = explode(',', $package->description);
                                        @endphp
                                        @foreach ($features as $feature)
                                            <li class="feature-item">
                                                <div class="feature-icon">
                                                    <i class="bi bi-check"></i>
                                                </div>
                                                <span class="feature-text"> {{ trim($feature) }} </span>
                                            </li>
                                        @endforeach
                                    </ul>

                                    <form action="{{ route('subscribe.plan', $package->id) }}" method="post">
                                        @csrf
                                        <button class="cta-button">
                                            <span> اشترك الان </span>
                                            <i class="bi bi-arrow-left"></i>
                                        </button>
                                    </form>

                                </div>
                                <!-- نهاية الكرت الأصلي بتاعك -->

                            </div>
                        @endforeach

                    </div>
                @endforeach

                <!-- مسافة بين كل فئة واللي بعدها -->
                <div style="margin-bottom:60px;"></div>
            @endforeach

        </div>

        <!-- Contact Section -->
        <div class="contact-section">
            <h2 class="contact-title">هل لديك أسئلة أخرى؟</h2>
            <p class="contact-text">
                فريقنا موجود لمساعدتك في اختيار الباقة المناسبة والإجابة على جميع استفساراتك
            </p>
            <div class="contact-buttons">
                <a href="https://wa.me/+963997610723" class="contact-btn primary">
                    <i class="bi bi-telephone-fill"></i>
                    <span>اتصل بنا</span>
                </a>

            </div>
        </div>
    </div>

    <!------------------------------------- End Present Section ##################### -->
@endsection


@section('js')
    <script>
        window.onload = () => {
            confetti({
                particleCount: 300,
                spread: 300,
                origin: {
                    y: 0.6
                }
            });
        };
    </script>
@endsection


<style>
    :root {
        --primary-color: #3FB699;
        --primary-dark: #3FB699;
        --success-color: #10B981;
        --danger-color: #EF4444;
        --warning-color: #F59E0B;
        --purple-color: #8B5CF6;
        --dark-color: #1E293B;
        --light-bg: #F8FAFC;
        --border-color: #E2E8F0;
        --text-muted: #64748B;
    }



    /* Header Section */
    .packages .header-section {
        text-align: center;
        margin-bottom: 3rem;
        animation: fadeInDown 0.6s ease-out;
        margin-top: 10rem;
    }

    @keyframes fadeInDown {
        from {
            opacity: 0;
            transform: translateY(-30px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }


    .packages .main-title {
        font-size: 2.5rem;
        font-weight: 700;
        color: var(--dark-color);
        margin-bottom: 1rem;
    }

    .packages .main-subtitle {
        font-size: 1.2rem;
        color: var(--text-muted);
        max-width: 600px;
        margin: 0 auto;
        line-height: 1.8;
    }

    /* Pricing Toggle */
    .packages .pricing-toggle {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 1rem;
        margin: 2rem 0;
        animation: fadeIn 0.6s ease-out 0.2s both;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
        }

        to {
            opacity: 1;
        }
    }

    /* Pricing Cards Container */
    .packages .pricing-container {
        max-width: 1200px;
        margin: 0 auto;
        /* display: grid; */
        /* /    grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); */
        /* gap: 2rem; */
        padding: 0 1rem;
    }

    /* Pricing Card */
    .packages .pricing-card {
        background: white;
        border-radius: 1.5rem;
        padding: 2.5rem;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
        transition: all 0.4s ease;
        position: relative;
        overflow: hidden;
        animation: fadeInUp 0.6s ease-out both;
        height: 100%;
    }

    .packages .pricing-card:nth-child(1) {
        animation-delay: 0.1s;
    }

    .packages .pricing-card:nth-child(2) {
        animation-delay: 0.2s;
    }

    .packages .pricing-card:nth-child(3) {
        animation-delay: 0.3s;
    }

    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .packages .pricing-card:hover {
        transform: translateY(-10px);
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
    }

    .packages .pricing-card.popular {
        border: 3px solid var(--primary-color);
        transform: scale(1.05);
    }

    .packages .pricing-card.popular:hover {
        transform: scale(1.08) translateY(-10px);
    }

    .packages .popular-badge {
        position: absolute;
        top: 20px;
        left: -35px;
        background: linear-gradient(135deg, var(--warning-color), #F59E0B);
        color: white;
        padding: 0.5rem 3rem;
        transform: rotate(-45deg);
        font-weight: 600;
        font-size: 0.85rem;
        box-shadow: 0 4px 10px rgba(245, 158, 11, 0.3);
    }

    /* Card Header */
    .packages .card-icon {
        width: 70px;
        height: 70px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 1.5rem;
        font-size: 2rem;
    }

    .packages .pricing-card.basic .card-icon {
        background: rgba(14, 165, 233, 0.1);
        color: var(--primary-color);
    }

    .packages .pricing-card.pro .card-icon {
        background: rgba(139, 92, 246, 0.1);
        color: var(--purple-color);
    }

    .packages .pricing-card.premium .card-icon {
        background: rgba(245, 158, 11, 0.1);
        color: var(--warning-color);
    }

    .packages .card-title {
        font-size: 1.8rem;
        font-weight: 700;
        color: var(--dark-color);
        margin-bottom: 0.5rem;
    }

    .packages .card-description {
        color: var(--text-muted);
        font-size: 0.95rem;
        margin-bottom: 2rem;
        line-height: 1.6;
    }

    /* Pricing */
    .packages .pricing {
        text-align: center;
        padding: 1.5rem 0;
        margin-bottom: 2rem;
        border-top: 2px solid var(--border-color);
        border-bottom: 2px solid var(--border-color);
    }

    .packages .price {
        font-size: 3rem;
        font-weight: 700;
        color: var(--dark-color);
        line-height: 1;
        margin-bottom: 0.5rem;
    }

    .packages .pricing-card.basic .price {
        color: var(--primary-color);
    }

    .packages .pricing-card.pro .price {
        color: var(--purple-color);
    }

    .packages .pricing-card.premium .price {
        color: var(--warning-color);
    }

    .packages .currency {
        font-size: 1.2rem;
        font-weight: 600;
        margin-right: 0.5rem;
    }

    .packages .period {
        color: var(--text-muted);
        font-size: 1rem;
        display: block;
        margin-top: 0.5rem;
    }

    .packages .original-price {
        text-decoration: line-through;
        color: var(--text-muted);
        font-size: 1.2rem;
        display: block;
        margin-top: 0.5rem;
    }

    /* Features List */
    .packages .features-list {
        list-style: none;
        padding: 0;
        margin-bottom: 2rem;
    }

    .packages .feature-item {
        display: flex;
        align-items: start;
        gap: 0.75rem;
        padding: 0.75rem 0;
        color: var(--dark-color);
        line-height: 1.6;
    }

    .packages .feature-icon {
        flex-shrink: 0;
        width: 24px;
        height: 24px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-top: 0.2rem;
    }

    .packages .pricing-card.basic .feature-icon {
        background: rgba(14, 165, 233, 0.1);
        color: var(--primary-color);
    }

    .packages .pricing-card.pro .feature-icon {
        background: rgba(139, 92, 246, 0.1);
        color: var(--purple-color);
    }

    .packages .pricing-card.premium .feature-icon {
        background: rgba(245, 158, 11, 0.1);
        color: var(--warning-color);
    }

    .packages .feature-icon.disabled {
        background: rgba(0, 0, 0, 0.05);
        color: var(--text-muted);
    }

    .packages .feature-text {
        flex: 1;
    }

    .packages .feature-text.disabled {
        color: var(--text-muted);
        text-decoration: line-through;
    }

    /* CTA Button */
    .packages .cta-button {
        width: 100%;
        padding: 1rem;
        border-radius: 0.75rem;
        font-weight: 600;
        font-size: 1.1rem;
        border: none;
        cursor: pointer;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        position: absolute;
        bottom: 0;
        left: 0;
        width: 95%;
        margin: 10px;

    }

    .packages .pricing-card.basic .cta-button {
        background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
        color: white;
    }

    .packages .pricing-card.pro .cta-button {
        background: linear-gradient(135deg, var(--purple-color), #7C3AED);
        color: white;
    }

    .packages .pricing-card.premium .cta-button {
        background: linear-gradient(135deg, var(--warning-color), #F59E0B);
        color: white;
    }

    .packages .cta-button:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
    }



    /* Contact Section */
    .packages .contact-section {
        text-align: center;
        max-width: 600px;
        margin: 3rem auto 0;
        padding: 2rem;
        background: #CCEBE6;
        border-radius: 1.5rem;
        animation: fadeInUp 0.6s ease-out 0.5s both;
        margin-bottom: 20px;
    }

    .packages .contact-title {
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--dark-color);
        margin-bottom: 1rem;
    }

    .packages .contact-text {
        color: var(--text-muted);
        margin-bottom: 1.5rem;
        line-height: 1.6;
    }

    .packages .contact-buttons {
        display: flex;
        gap: 1rem;
        justify-content: center;
        flex-wrap: wrap;
    }

    .packages .contact-btn {
        padding: 0.75rem 1.5rem;
        border-radius: 0.75rem;
        font-weight: 600;
        text-decoration: none;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .packages .contact-btn.primary {
        background: var(--primary-color);
        color: white;
    }

    .packages .contact-btn.primary:hover {
        background: var(--primary-dark);
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(14, 165, 233, 0.3);
    }

    .packages .contact-btn.secondary {
        background: white;
        color: var(--primary-color);
        border: 2px solid var(--primary-color);
    }

    .packages .contact-btn.secondary:hover {
        background: var(--primary-color);
        color: white;
    }

    /* Responsive */
    @media (max-width: 768px) {

        .packages .main-title {
            font-size: 1.8rem;
        }

        .packages .main-subtitle {
            font-size: 1rem;
        }

        .packages .pricing-container {
            grid-template-columns: 1fr;
            gap: 1.5rem;
        }

        .packages .pricing-card.popular {
            transform: scale(1);
        }

        .packages .pricing-card.popular:hover {
            transform: translateY(-10px);
        }

        .packages .price {
            font-size: 2.5rem;
        }

        .packages .faq-section {
            padding: 1.5rem;
        }

        .packages .contact-buttons {
            flex-direction: column;
        }

        .packages .contact-btn {
            width: 100%;
            justify-content: center;
        }
    }

    footer.skin-dark-footer ul.footer-bottom-social li a {
        font-size: 17px;
        background: #3fb697;
        color: #fff !important;
        text-align: center;
        width: 40px;
        height: 40px;
        display: inline;
        line-height: 136px;
        /* text-align: center; */
        border-radius: 10px 0;
        padding: 8px 0px;
        margin: 5px;
    }
</style>
