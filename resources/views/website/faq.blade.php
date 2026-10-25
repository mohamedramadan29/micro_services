@extends('website.layouts.master')
@php
    $faq = \App\Support\SiteContent::first('faq.page');
    $faqItems = \App\Support\SiteContent::items('faq.items');
@endphp
@section('title') {{ $faq['title'] }} @endsection
@section('content')
    <div class="clearfix"></div>
    <!-- ============================ Page Title Start================================== -->
    <div class="page-title" style="height: 400px; text-align: right">
        <div class="container">
            <div class="row">
                <div class="col-lg-12 col-md-12">
                    <h1 class="ipt-title"> {{ $faq['title'] }} </h1>
                    @if ($faq['text'])<p class="text-white" style="white-space: pre-line">{{ $faq['text'] }}</p>@endif
                </div>
            </div>
        </div>
    </div>
    <!-- ============================ Page Title End ================================== -->

    <!-- ============================ Main Section Start (questions managed in admin «إدارة محتوى الواجهة») ============ -->
    <section class="min-sec text-right faq_section" dir="rtl">
        <div class="container">
            <div class="row">
                <div class="col-lg-12 col-md-12 col-sm-12">
                    <div class="accordion" id="faqAccordion">
                        @foreach ($faqItems as $i => $item)
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="faq-heading-{{ $i }}">
                                    <button class="accordion-button {{ $i ? 'collapsed' : '' }}" type="button" data-bs-toggle="collapse" data-bs-target="#faq-answer-{{ $i }}" aria-expanded="{{ $i ? 'false' : 'true' }}" aria-controls="faq-answer-{{ $i }}">
                                        {{ $item['question'] }}
                                    </button>
                                </h2>
                                <div id="faq-answer-{{ $i }}" class="accordion-collapse collapse {{ $i ? '' : 'show' }}" aria-labelledby="faq-heading-{{ $i }}">
                                    <div class="accordion-body" style="white-space: pre-line">{{ $item['answer'] }}</div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
