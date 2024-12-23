<footer class="dark-footer skin-dark-footer text-right" dir="rtl">
    <div class="help_center">
        <a href="{{ url('tickets') }}" class="btn"> مركز المساعدة <i class="bi bi-person-raised-hand"></i> </a>
    </div>
    <div>
        <div class="container">
            <div class="row">

                <div class="col-lg-4 col-md-6">
                    <div class="footer-widget">
                        <img src="{{ asset('assets/website/img/logo.png') }}" class="img-fluid f-logo" width="120"
                            alt="">
                        <p>
                             {{ __('public.footer_about') }}
                        </p>

                    </div>
                </div>
                <div class="col-lg-4 col-md-4">
                    <div class="footer-widget">
                        <h4 class="widget-title"> {{ __('public.links') }} </h4>
                        <ul class="footer-menu">
                            <li><a href="{{ '/about' }}"> {{ __('public.about_us') }} </a></li>
                            <li><a href="{{ '/faq' }}">  {{ __('public.faqs') }}  </a></li>
                            <li><a href="{{ '/privacy-policy' }}">  {{ __('public.privacy') }}  </a></li>
                            <li><a href="{{ '/terms' }}"> {{ __('public.terms') }}  </a></li>

                        </ul>
                    </div>
                </div>

                <div class="col-lg-4 col-md-4">
                    <div class="footer-widget">
                        <h4 class="widget-title"> {{ __('public.follow_us') }}  </h4>
                        <ul class="footer-bottom-social">
                            <li><a href="#"><i class="ti-facebook"></i></a></li>
                            <li><a href="#"><i class="ti-twitter"></i></a></li>
                            <li><a href="#"><i class="ti-instagram"></i></a></li>
                            <li><a href="#"><i class="ti-linkedin"></i></a></li>
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
                    <p class="mb-0">  {{ __('public.copy_right') }}  </p>
                </div>

            </div>
        </div>
    </div>
</footer>
<!-- =========================== Footer End ========================================= -->
</div>

<script src="{{ asset('assets/website/js/jquery.min.js') }}"></script>
<script src="{{ asset('assets/website/js/popper.min.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous">
</script>
<script src="{{ asset('assets/website/js/select2.min.js') }}"></script>
<script src="{{ asset('assets/website/js/owl.carousel.min.js') }}"></script>
<script src="{{ asset('assets/website/js/ion.rangeSlider.min.js') }}"></script>
<script src="{{ asset('assets/website/js/counterup.min.js') }}"></script>
<script src="{{ asset('assets/website/js/materialize.min.js') }}"></script>
<script src="{{ asset('assets/website/js/metisMenu.min.js') }}"></script>
<script src="{{ asset('assets/website/js/custom.js') }}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
@vite(['resources/css/app.css', 'resources/js/app.js'])
@notifyJs
@toastifyJs
@livewireScripts


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


</body>

</html>
