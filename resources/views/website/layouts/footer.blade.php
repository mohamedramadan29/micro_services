
<footer class="dark-footer skin-dark-footer text-right" dir="rtl">
    <div>
        <div class="container">
            <div class="row">

                <div class="col-lg-4 col-md-6">
                    <div class="footer-widget">
                        <img src="{{asset('assets/website/img/khamsat.png')}}" class="img-fluid f-logo" width="120" alt="">
                        <p>
                            خمسات هو السوق العربي الأول لبيع وشراء الخدمات المصغرة، يجمع خمسات بين الشباب العربي المستعد لتقديم الخدمات وبين فئة المشترين المستعدين لشراء هذه الخدمات
                        </p>
                        <ul class="footer-bottom-social">
                            <li><a href="#"><i class="ti-facebook"></i></a></li>
                            <li><a href="#"><i class="ti-twitter"></i></a></li>
                            <li><a href="#"><i class="ti-instagram"></i></a></li>
                            <li><a href="#"><i class="ti-linkedin"></i></a></li>
                        </ul>
                    </div>
                </div>
                <div class="col-lg-4 col-md-4">
                    <div class="footer-widget">
                        <h4 class="widget-title"> روابط  </h4>
                        <ul class="footer-menu">
                            <li><a href="{{'/about'}}"> من نحن  </a></li>
                            <li><a href="{{'/faq'}}"> الاسئلة الشائعة  </a></li>
                            <li><a href="{{'/terms'}}"> سياسة الاستخدام  </a></li>
                            <li><a href="{{'/privacy-policy'}}"> سياسة الخصوصية  </a></li>
                        </ul>
                    </div>
                </div>

                <div class="col-lg-4 col-md-4">
                    <div class="footer-widget">
                        <h4 class="widget-title"> وسائل الدفع  </h4>
                         <img style="max-width:100%;" src="{{asset('assets/website/img/pay.png')}}">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="footer-bottom">
        <div class="container">
            <div class="row align-items-center">

                <div class="col-lg-12 col-md-12 text-center">
                    <p class="mb-0"> مروة @ 2024 جميع الحقوق محفوظة  <a href="#"> Mr </a> بواسطة </p>
                </div>

            </div>
        </div>
    </div>
</footer>
<!-- =========================== Footer End ========================================= -->


</div>

<script src="{{asset('assets/website/js/jquery.min.js')}}"></script>
<script src="{{asset('assets/website/js/popper.min.js')}}"></script>
<script src="{{asset('assets/website/js/bootstrap.min.js')}}"></script>
<script src="{{asset('assets/website/js/select2.min.js')}}"></script>
<script src="{{asset('assets/website/js/owl.carousel.min.js')}}"></script>
<script src="{{asset('assets/website/js/ion.rangeSlider.min.js')}}"></script>
<script src="{{asset('assets/website/js/counterup.min.js')}}"></script>
<script src="{{asset('assets/website/js/materialize.min.js')}}"></script>
<script src="{{asset('assets/website/js/metisMenu.min.js')}}"></script>
<script src="{{asset('assets/website/js/custom.js')}}"></script>
@notifyJs
<script>
    var vChipsList =  [{
        tag: 'Photoshop',
    }, {
        tag: 'WordPress',
    }, {
        tag: 'Jquery',
    }];

    // INITIALIZATION OF AUTOCOMPLETE LIST
    var vTagList =  {
        design: null,
        html: null,
        css: null,
        magento: null,
        drupal: null,
        business: null,
        java: null,
        joomla: null,
        css3: null,
        bootstrap: null,photoshop:null
    };

    function fDisplayChips() {
        // FILLS THE CHIPS ZONE FROM THE LIST
        $('#lg-Chips').material_chip({
            data: vChipsList
        });
    }


    // ADDING A NEW CHIP
    function fChipAdd(lChipName){
        lChipName = lChipName.toLowerCase();
        // test1 : minimum word size
        if (!(lChipName.length > 2)){
            return 0;
        }
        // test2 :  no duplicates
        for(i=0;i<vChipsList.length;i++) {
            if(lChipName == vChipsList[i].tag){
                return 0;
            }
        }
        // tests Okay => add the chip and refresh the view
        vChipsList.push({"tag":lChipName});
        fDisplayChips();
        return 1;
    };

    $(function() {
        // delete chip command
        $('#lg-Chips').on('chip.delete', function(e, chip){
            vChipsList = $("#lg-Chips").material_chip('data');
        });


        $("#lg-Chips").focusin(function () {
            $("#lg-input").focus();
        });


        fDisplayChips();


        // NEW CHIP COMMAND
        $("#cmd-ChipsAjout").click(function () {
            fChipAdd($("#lg-input").val()) ;
            $("#lg-input").val("");
        });

        $("#lg-input").autocomplete({
            data: vTagList
        });

    });
</script>
</body>

</html>
