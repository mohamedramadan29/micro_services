<div class="d-navigation">
    <ul id="metismenu">
        <li class="{{ request()->is('dashboard') ? 'active' : '' }}">
            <a href="{{ url('dashboard') }}"><i class="ti-dashboard"></i> الملف الشخصي </a>
        </li>
        <li class="{{ request()->is('update-account') ? 'active' : '' }}">
            <a href="{{ url('update-account') }}"> <i class="bi bi-gear-fill"></i> تعديل الملف الشخصي </a>
        </li>
        <li class="{{ request()->is('balance') ? 'active' : '' }}">
            <a href="{{ url('balance') }}"><i class="bi bi-credit-card"></i> الرصيد </a>
        </li>
        <li class="{{ request()->is('my/products/purches') ? 'active' : '' }}">
            <a href="{{ url('my/products/purches') }}"><i class="bi bi-cart-check-fill"></i> مشتريات المنتجات </a>
        </li>
        <li class="{{ request()->is('my/project/index') ? 'active' : '' }}">
            <a href="{{ url('my/project/index') }}"><i class="bi bi-cast"></i> المشاريع </a>
        </li>
        <li class="{{ request()->is('my/courses') ? 'active' : '' }}">
            <a href="{{ url('my/courses') }}"> <i class="bi bi-mortarboard-fill"></i> الكورسات </a>
        </li>
        <li class="{{ request()->is('service/index') ? 'active' : '' }}">
            <a href="{{ url('service/index') }}"><i class="bi bi-database-fill-check"></i> الخدمات </a>
        </li>
        <li class="{{ request()->is('my/properties/index') ? 'active' : '' }}">
            <a href="{{ url('my/properties/index') }}"><i class="bi bi-building"></i> العقارات </a>
        </li>
        <li class="{{ request()->is('my/property/maintain/index') ? 'active' : '' }}">
            <a href="{{ url('my/property/maintain/index') }}"><i class="ti-support"></i> خدمات صيانة العقارات  </a>
        </li> 
        <li class="{{ request()->is('my/jobs') ? 'active' : '' }}">
            <a href="{{ url('my/jobs') }}"><i class="ti-plus"></i> وظائفي </a>
        </li>
        <li class="{{ request()->is('chats') ? 'active' : '' }}">
            <a href="{{ url('chats') }}"> <i class="bi bi-chat-dots-fill"></i> المحادثات </a>
        </li>
        <li class="{{ request()->is('tickets') ? 'active' : '' }}">
            <a href="{{ url('tickets') }}"><i class="bi bi-ticket"></i> تذاكري </a>
        </li>
        <li class="{{ request()->is('logout') ? 'active' : '' }}">
            <a href="{{ url('logout') }}"><i class="ti-power-off"></i> تسجيل خروج </a>
        </li>
    </ul>
</div>
