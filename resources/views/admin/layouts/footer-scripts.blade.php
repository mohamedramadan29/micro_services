<!-- Back-to-top -->
<a href="#top" id="back-to-top"><i class="las la-angle-double-up"></i></a>
<!-- JQuery min js -->
<script src="{{ URL::asset('assets/admin/plugins/jquery/jquery.min.js') }}"></script>
<!-- Bootstrap Bundle js -->
<script src="{{ URL::asset('assets/admin/plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
<!-- Ionicons js -->
<script src="{{ URL::asset('assets/admin/plugins/ionicons/ionicons.js') }}"></script>
<!-- Moment js -->
<script src="{{ URL::asset('assets/admin/plugins/moment/moment.js') }}"></script>

<!-- Rating js-->
<script src="{{ URL::asset('assets/admin/plugins/rating/jquery.rating-stars.js') }}"></script>
<script src="{{ URL::asset('assets/admin/plugins/rating/jquery.barrating.js') }}"></script>

<!--Internal  Perfect-scrollbar js -->
<script src="{{ URL::asset('assets/admin/plugins/perfect-scrollbar/perfect-scrollbar.min.js') }}"></script>
<script src="{{ URL::asset('assets/admin/plugins/perfect-scrollbar/p-scroll.js') }}"></script>
<!--Internal Sparkline js -->
<script src="{{ URL::asset('assets/admin/plugins/jquery-sparkline/jquery.sparkline.min.js') }}"></script>
<!-- Custom Scroll bar Js-->
<script src="{{ URL::asset('assets/admin/plugins/mscrollbar/jquery.mCustomScrollbar.concat.min.js') }}"></script>
<!-- right-sidebar js -->
<script src="{{ URL::asset('assets/admin/plugins/sidebar/sidebar-rtl.js') }}"></script>
<script src="{{ URL::asset('assets/admin/plugins/sidebar/sidebar-custom.js') }}"></script>
<!-- Eva-icons js -->
<script src="{{ URL::asset('assets/admin/js/eva-icons.min.js') }}"></script>
<!--Internal  Datepicker js -->
<script src="{{ URL::asset('assets/admin/plugins/jquery-ui/ui/widgets/datepicker.js') }}"></script>
<!--Internal  jquery.maskedinput js -->
<script src="{{ URL::asset('assets/admin/plugins/jquery.maskedinput/jquery.maskedinput.js') }}"></script>
<!--Internal  spectrum-colorpicker js -->
<script src="{{ URL::asset('assets/admin/plugins/spectrum-colorpicker/spectrum.js') }}"></script>
<!-- Internal Select2.min js -->
<script src="{{ URL::asset('assets/admin/plugins/select2/js/select2.min.js') }}"></script>
<!--Internal Ion.rangeSlider.min js -->
<script src="{{ URL::asset('assets/admin/plugins/ion-rangeslider/js/ion.rangeSlider.min.js') }}"></script>
<!--Internal  jquery-simple-datetimepicker js -->
<script src="{{ URL::asset('assets/admin/plugins/amazeui-datetimepicker/js/amazeui.datetimepicker.min.js') }}">
</script>
<!-- Ionicons js -->
<script src="{{ URL::asset('assets/admin/plugins/jquery-simple-datetimepicker/jquery.simple-dtpicker.js') }}">
</script>
<!--Internal  pickerjs js -->
<script src="{{ URL::asset('assets/admin/plugins/pickerjs/picker.min.js') }}"></script>
<!-- Internal form-elements js -->
<script src="{{ URL::asset('assets/admin/js/form-elements.js') }}"></script>
<!-- Sticky js -->
<script src="{{ URL::asset('assets/admin/js/sticky.js') }}"></script>
<!-- custom js -->
<script src="{{ URL::asset('assets/admin/js/custom.js') }}"></script><!-- Left-menu js-->
<script src="{{ URL::asset('assets/admin/plugins/side-menu/sidemenu.js') }}"></script>
<script src="https://cdn.tiny.cloud/1/c8u902w1qjlgsxdu73djug5kw4ckg9n6ggwi5lynenmwrw25/tinymce/7/tinymce.min.js" referrerpolicy="origin"></script>
<script src="https://cdn.jsdelivr.net/gh/kartik-v/bootstrap-fileinput@5.5.0/js/fileinput.min.js"></script>
<script src="https://cdn.jsdelivr.net/gh/kartik-v/bootstrap-fileinput@5.5.0/js/locales/LANG.js"></script>
<!-- Start file Input  -->
<script>
    var lang = "ar";
    $("#single-image").fileinput({
        theme: 'bs5',
        allowedFileTypes: ['image'],
        language: lang,
        maxFileCount: 1,
        enableResumableUpload: false,
        showUpload: false,
    });
    $("#single-image2").fileinput({
        theme: 'bs5',
        allowedFileTypes: ['image'],
        language: lang,
        maxFileCount: 1,
        enableResumableUpload: false,
        showUpload: false,
    });
</script>
<!--- Start tinymce -->
<script>
    tinymce.init({
        selector: '.tinymce',
        height: 300,
        directionality: 'rtl', // لجعل المحرر يعمل من اليمين إلى اليسار
        language: 'ar',
        plugins: [
            'advlist', 'autolink', 'link', 'image', 'lists', 'charmap', 'preview', 'anchor', 'pagebreak',
            'searchreplace', 'wordcount', 'visualblocks', 'visualchars', 'code', 'fullscreen',
            'insertdatetime',
            'media', 'table', 'emoticons', 'help'
        ],
        toolbar: 'undo redo | styles | bold italic | alignleft aligncenter alignright alignjustify | ' +
            'bullist numlist outdent indent | link image | print preview media fullscreen | ' +
            'forecolor backcolor emoticons',
        menu: {
            favs: {
                title: 'My Favorites',
                items: 'code visualaid | searchreplace | emoticons'
            }
        },
        image_title: true, // السماح بتعديل العنوان
        automatic_uploads: true,
        images_upload_url: 'post_uploads', // مسار API لاستقبال الصور
        file_picker_types: 'image',
        file_picker_callback: function(cb, value, meta) {
            if (meta.filetype === 'image') {
                var input = document.createElement('input');
                input.setAttribute('type', 'file');
                input.setAttribute('accept', 'image/*');
                input.onchange = function() {
                    var file = this.files[0];
                    var reader = new FileReader();
                    reader.onload = function() {
                        cb(reader.result, {
                            title: file.name
                        });
                    };
                    reader.readAsDataURL(file);
                };
                input.click();
            }
        }
    });
</script>
<!-- End File Input -->
