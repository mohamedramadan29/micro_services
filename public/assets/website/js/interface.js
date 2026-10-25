/* Shared interface behaviour. No request, payment, or authentication state is changed here. */
(function () {
    'use strict';
    function enhance() {
        var compact = window.matchMedia('(max-width: 991px)');
        document.querySelectorAll('[data-responsive-filters]').forEach(function (panel) {
            function adapt() { panel.open = !compact.matches; }
            adapt();
            compact.addEventListener('change', adapt);
        });

        // Reflect the existing navigation plugin's state for keyboard and screen-reader users.
        var nav = document.getElementById('site-navigation');
        var toggle = document.querySelector('.nav-toggle');
        if (nav && toggle) {
            var close = nav.querySelector('.nav-menus-wrapper-close-button');
            var wasOpen = false;
            function update() {
                var open = nav.classList.contains('nav-menus-wrapper-open');
                toggle.setAttribute('aria-expanded', String(open));
                toggle.setAttribute('aria-label', open ? 'إغلاق قائمة الموقع' : 'فتح قائمة الموقع');
                nav.inert = compact.matches && !open;
                if (wasOpen && !open && nav.contains(document.activeElement)) toggle.focus();
                wasOpen = open;
            }
            if (close) {
                close.setAttribute('role', 'button');
                close.setAttribute('tabindex', '0');
                close.setAttribute('aria-label', 'إغلاق قائمة الموقع');
                close.addEventListener('keydown', function (event) {
                    if (event.key === 'Enter' || event.key === ' ') { event.preventDefault(); close.click(); }
                });
            }
            new MutationObserver(update).observe(nav, { attributes: true, attributeFilter: ['class'] });
            compact.addEventListener('change', update);
            document.addEventListener('keydown', function (event) {
                if (event.key === 'Escape' && wasOpen && close) close.click();
            });
            update();
        }
    }
    if (window.jQuery) window.jQuery(enhance);
    else if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', enhance);
    else enhance();
})();
