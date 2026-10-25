@php
    $footer = \App\Support\SiteContent::first('footer.main');
    $footerLinks = \App\Support\SiteContent::items('footer.links');
    $footerSocial = \App\Support\SiteContent::items('footer.social');
    $floatingWhatsapp = preg_replace('/\D/', '', (string) $footer['whatsapp']);
@endphp
<footer class="dark-footer skin-dark-footer text-right" dir="rtl">
    @if ($footer['show_tickets'] !== '0')
        <div class="help_center">
            <a href="{{ url('tickets') }}" class="btn" aria-label="الدعم"> <i class="bi bi-chat-square-dots-fill"></i> </a>
        </div>
    @endif
    @if ($floatingWhatsapp)
        <div class="help_center help_center_products">
            <a target="_blank" rel="noopener" href="https://wa.me/{{ $floatingWhatsapp }}" class="btn btn-primary" aria-label="واتساب"> <i class="bi bi-person-raised-hand"></i> </a>
        </div>
    @endif
    <div>
        <div class="container">
            <div class="row">
                <div class="col-lg-4 col-md-6">
                    <div class="footer-widget">
                        <img src="{{ asset('assets/website/img/logo.png') }}" class="img-fluid f-logo" width="120"
                            alt="">
                        @if ($footer['tagline'])<p style='line-height:1.3;font-weight:bold'> {{ $footer['tagline'] }} </p>@endif
                        @if ($footer['text'])<p style='line-height:1.3; white-space: pre-line'>{{ $footer['text'] }}</p>@endif
                    </div>
                </div>
                <div class="col-lg-2 col-md-4">
                    <div class="footer-widget">
                        <h4 class="widget-title"> {{ $footer['links_title'] ?: __('public.links') }} </h4>
                        <ul class="footer-menu">
                            @foreach ($footerLinks as $link)
                                <li><a href="{{ \App\Support\SiteContent::url($link['link']) }}"> {{ $link['label'] }} </a></li>
                            @endforeach
                        </ul>
                    </div>
                </div>
                <div class="col-lg-6 col-md-4">
                    <div class="footer-widget">
                        <h4 class="widget-title"> {{ $footer['social_title'] ?: __('public.follow_us') }} </h4>
                        <ul class="footer-bottom-social">
                            @foreach ($footerSocial as $account)
                                <li><a href="{{ \App\Support\SiteContent::url($account['link']) }}" target="_blank" rel="noopener" aria-label="{{ $account['label'] }}" title="{{ $account['label'] }}"><i
                                        class="bi {{ $account['icon'] ?: 'bi-link-45deg' }}"></i></a></li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="footer-bottom">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-12 col-md-12 text-center">
                    <p class="mb-0"> {{ str_replace(':year', date('Y'), $footer['copyright'] ?: 'جميع الحقوق محفوظة @ :year لدي نفذها') }} </p>
                </div>
            </div>
        </div>
    </div>
</footer>
@auth
<form id="site-logout-form" method="POST" action="{{ url('logout') }}" hidden>@csrf</form>
{{-- Links marked data-method="post" (deletes) are sent as POST with the CSRF token, never as a plain GET. --}}
<script>
    document.addEventListener('click', function (e) {
        var link = e.target.closest && e.target.closest('a[data-method="post"]');
        if (!link || e.defaultPrevented) return;
        e.preventDefault();
        var form = document.createElement('form');
        form.method = 'POST';
        form.action = link.href;
        form.hidden = true;
        form.innerHTML = '<input type="hidden" name="_token" value="{{ csrf_token() }}">';
        document.body.appendChild(form);
        form.submit();
    });
</script>
@endauth
<!-- =========================== Footer End ========================================= -->

<script src="{{ asset('assets/website/js/jquery.min.js') }}"></script>
<script src="{{ asset('assets/website/js/popper.min.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous">
</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<script src="{{ asset('assets/website/js/owl.carousel.min.js') }}"></script>
<script src="{{ asset('assets/website/js/ion.rangeSlider.min.js') }}"></script>
<script src="{{ asset('assets/website/js/counterup.min.js') }}"></script>
<script src="{{ asset('assets/website/js/materialize.min.js') }}"></script>
<script src="{{ asset('assets/website/js/metisMenu.min.js') }}"></script>
<script src="{{ asset('assets/website/js/custom.js') }}"></script>
<script src="{{ \App\Support\AssetVersion::url('assets/website/js/phone-picker.js') }}"></script>
<script src="{{ \App\Support\AssetVersion::url('assets/website/js/interface.js') }}" defer></script>
<script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.5.1"></script>

@notifyJs
@toastifyJs
@livewireScripts

{!! NoCaptcha::renderJs() !!}
<script>
    // Enable pusher logging - don't include this in production
    Pusher.logToConsole = true;

    var pusher = new Pusher('0b5767cf97c2b0e3dc9a', {
        cluster: 'eu'
    });
</script>
<script>
    var vChipsList = [{
        tag: 'Photoshop',
    }, {
        tag: 'WordPress',
    }, {
        tag: 'Jquery',
    }];

    // INITIALIZATION OF AUTOCOMPLETE LIST
    var vTagList = {
        design: null,
        html: null,
        css: null,
        magento: null,
        drupal: null,
        business: null,
        java: null,
        joomla: null,
        css3: null,
        bootstrap: null,
        photoshop: null
    };

    function fDisplayChips() {
        // FILLS THE CHIPS ZONE FROM THE LIST
        $('#lg-Chips').material_chip({
            data: vChipsList
        });
    }


    // ADDING A NEW CHIP
    function fChipAdd(lChipName) {
        lChipName = lChipName.toLowerCase();
        // test1 : minimum word size
        if (!(lChipName.length > 2)) {
            return 0;
        }
        // test2 :  no duplicates
        for (i = 0; i < vChipsList.length; i++) {
            if (lChipName == vChipsList[i].tag) {
                return 0;
            }
        }
        // tests Okay => add the chip and refresh the view
        vChipsList.push({
            "tag": lChipName
        });
        fDisplayChips();
        return 1;
    };

    $(function() {
        // delete chip command
        $('#lg-Chips').on('chip.delete', function(e, chip) {
            vChipsList = $("#lg-Chips").material_chip('data');
        });


        $("#lg-Chips").focusin(function() {
            $("#lg-input").focus();
        });


        fDisplayChips();


        // NEW CHIP COMMAND
        $("#cmd-ChipsAjout").click(function() {
            fChipAdd($("#lg-input").val());
            $("#lg-input").val("");
        });

        $("#lg-input").autocomplete({
            data: vTagList
        });

    });
</script>
