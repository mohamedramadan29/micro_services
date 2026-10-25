/* Nafizha admin: filter the sidebar menu as you type. */
(function () {
    var input = document.getElementById('rf-an-filter');
    if (!input) return;
    var menu = document.querySelector('.rf-admin-nav .side-menu');
    var empty = document.getElementById('rf-an-empty');
    var norm = function (s) { return (s || '').toLowerCase().replace(/[أإآ]/g, 'ا').replace(/ة/g, 'ه').replace(/ى/g, 'ي').trim(); };

    input.addEventListener('input', function () {
        var q = norm(input.value);
        var any = false;
        menu.classList.toggle('rf-an-filtering', q !== '');
        menu.querySelectorAll(':scope > li').forEach(function (group) {
            var items = group.querySelectorAll('.slide-item');
            var label = norm((group.querySelector('.side-menu__label') || {}).textContent);
            if (!items.length) { // the dashboard link
                var hit = q === '' || label.indexOf(q) !== -1;
                group.style.display = hit ? '' : 'none';
                any = any || hit;
                return;
            }
            var groupHit = q !== '' && label.indexOf(q) !== -1;
            var shown = 0;
            items.forEach(function (a) {
                var hit = q === '' || groupHit || norm(a.textContent).indexOf(q) !== -1;
                a.parentElement.style.display = hit ? '' : 'none';
                if (hit) shown++;
            });
            group.style.display = shown ? '' : 'none';
            any = any || shown > 0;
        });
        if (empty) empty.hidden = any;
    });

    input.addEventListener('keydown', function (e) {
        if (e.key === 'Enter') {
            var first = Array.prototype.find.call(menu.querySelectorAll('a[href]:not([href="#"])'), function (a) {
                return a.offsetParent !== null && a.closest('li').style.display !== 'none';
            });
            if (first) { e.preventDefault(); window.location = first.href; }
        }
        if (e.key === 'Escape') { input.value = ''; input.dispatchEvent(new Event('input')); }
    });
})();
