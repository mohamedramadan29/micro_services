<div class="d-navigation">
    <ul id="metismenu">
        <li><a href="{{ url('dashboard') }}"><i class="ti-dashboard"></i> الملف الشخصي </a>
        </li>
        <li><a href="{{ url('update-account') }}"> <i class="bi bi-gear-fill"></i> تعديل الملف
                الشخصي
            </a></li>
        <li><a href="{{ url('balance') }}"><i class="bi bi-credit-card"></i> الرصيد </a></li>
        <li><a href="{{ url('my/products/purches') }}"><i class="bi bi-cart-check-fill"></i> مشتريات المنتجات  </a></li>
        <li><a href="{{ url('my/project/index') }}"><i class="bi bi-cast"></i> المشاريع </a></li>
        <li><a href="{{ url('my/project/add') }}"><i class="ti-plus"></i> اضف مشروع جديد </a></li>
        <li><a href="{{ url('my/courses') }}"> <i class="bi bi-mortarboard-fill"></i> الكورسات </a>
        </li>
        <li><a href="{{ url('my/course/add') }}"><i class="ti-plus"></i> اضف كورس جديد </a></li>
        <li><a href="{{ url('service/index') }}"><i class="bi bi-database-fill-check"></i> الخدمات
            </a>
        </li>
        <li><a href="{{ url('my/properties/index') }}"><i class="bi bi-building"></i> العقارات
            </a>
        </li>
        <li><a href="{{ url('my/property/add') }}"><i class="ti-plus"></i> اضف عقار جديد </a></li>
        <li><a href="{{ url('my/property/maintain/add') }}"><i class="ti-plus"></i> اضف خدمة صيانة
                للعقارات </a></li>
        <li><a href="{{ url('my/property/maintain/index') }}"><i class="ti-plus"></i> خدمات الصيانة
            </a></li>
        <li><a href="{{ url('my/job/add') }}"><i class="ti-plus"></i> اضافة وظيفة جديدة
            </a></li>
        <li><a href="{{ url('my/jobs') }}"><i class="ti-plus"></i> وظائفي
            </a></li>
        <li><a href="{{ url('chats') }}"> <i class="bi bi-chat-dots-fill"></i> المحادثات </a>
        </li>
        <li><a href="{{ url('tickets') }}"><i class="bi bi-ticket"></i> تذاكري </a></li>
        {{-- <li><a href="{{ url('reviews') }}"><i class="ti-email"></i> التقيمات </a></li> --}}
        <li><a href="{{ url('logout') }}"><i class="ti-power-off"></i> تسجيل خروج </a></li>
    </ul>
</div>
