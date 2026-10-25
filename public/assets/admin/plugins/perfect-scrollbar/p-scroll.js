(function ($) {
    "use strict";
    if (typeof PerfectScrollbar === 'undefined') return;
    document.querySelectorAll('.chat-scroll, .Notification-scroll').forEach(function (element) {
        new PerfectScrollbar(element, { useBothWheelAxes: true, suppressScrollX: true });
    });
})(jQuery);
