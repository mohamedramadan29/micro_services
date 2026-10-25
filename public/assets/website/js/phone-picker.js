/*
 * Country-code picker for phone fields marked data-country-picker (client file «تعديلات», item 11).
 * One field, two parts: a compact code segment («+20 ▾», the native list opens with country names)
 * and the local number. The original input becomes hidden and always holds the full number that the
 * server receives («+20 1012345678»); the visible input keeps the original id, label, required flag
 * and placeholder, so labels, validation and other scripts keep working.
 */
(function () {
    var CODES = [
        ['+20', 'مصر'], ['+966', 'السعودية'], ['+971', 'الإمارات'], ['+965', 'الكويت'], ['+974', 'قطر'],
        ['+973', 'البحرين'], ['+968', 'عُمان'], ['+962', 'الأردن'], ['+963', 'سوريا'], ['+961', 'لبنان'],
        ['+964', 'العراق'], ['+970', 'فلسطين'], ['+212', 'المغرب'], ['+213', 'الجزائر'], ['+216', 'تونس'],
        ['+218', 'ليبيا'], ['+249', 'السودان'], ['+967', 'اليمن'], ['+90', 'تركيا'], ['+44', 'المملكة المتحدة'],
        ['+1', 'أمريكا / كندا'], ['+49', 'ألمانيا'], ['+33', 'فرنسا']
    ];
    var longestFirst = CODES.slice().sort(function (a, b) { return b[0].length - a[0].length; });
    var digits = function (v) { return String(v || '').replace(/[^\d+]/g, ''); };
    var findCode = function (v) { return longestFirst.find(function (c) { return v.indexOf(c[0]) === 0; }); };

    function enhance(original) {
        if (original.dataset.pickerReady) return;
        original.dataset.pickerReady = '1';

        var wrap = document.createElement('div');
        wrap.className = 'rf-phone-wrap';
        wrap.setAttribute('dir', 'ltr');

        // Code segment: the visible face shows only the code; the transparent native select on top
        // keeps keyboard, screen readers and the phone's own picker.
        var seg = document.createElement('label');
        seg.className = 'rf-phone-seg';
        var face = document.createElement('span');
        face.className = 'rf-phone-face';
        var select = document.createElement('select');
        select.className = 'rf-phone-code';
        select.setAttribute('aria-label', 'رمز الدولة');
        select.innerHTML = CODES.map(function (c) {
            return '<option value="' + c[0] + '">' + c[1] + ' (' + c[0] + ')</option>';
        }).join('');
        seg.appendChild(face);
        seg.appendChild(select);

        // Visible local-number input takes over the original's identity.
        var local = document.createElement('input');
        local.type = 'tel';
        local.className = original.className + ' rf-phone-local';
        local.id = original.id || '';
        local.required = original.required;
        local.placeholder = original.getAttribute('placeholder') && original.getAttribute('placeholder').indexOf('+') === -1
            ? original.getAttribute('placeholder') : '10 1234 5678';
        local.setAttribute('inputmode', 'tel');
        local.setAttribute('autocomplete', 'tel-national');
        local.setAttribute('dir', 'ltr');
        if (original.maxLength > 0) local.maxLength = original.maxLength;
        if (original.getAttribute('aria-describedby')) local.setAttribute('aria-describedby', original.getAttribute('aria-describedby'));

        original.parentNode.insertBefore(wrap, original);
        wrap.appendChild(seg);
        wrap.appendChild(local);
        wrap.appendChild(original);
        original.removeAttribute('id');
        original.type = 'hidden';
        original.required = false;

        // Split what the server sent (old input or saved value) into code + local number.
        var start = digits(original.value).replace(/^00/, '+');
        var match = findCode(start);
        select.value = match ? match[0] : (original.dataset.defaultCode || '+20');
        local.value = match ? start.slice(match[0].length) : String(original.value || '').trim();

        function sync() {
            var typed = local.value.trim();
            var compact = digits(typed).replace(/^00/, '+');
            if (compact.charAt(0) === '+') {          // a full international number was typed or pasted
                var m = findCode(compact);
                if (m) { select.value = m[0]; local.value = compact.slice(m[0].length); }
                original.value = compact;
            } else {
                original.value = typed ? select.value + ' ' + compact.replace(/^0+/, '') : '';
            }
            face.textContent = select.value;
        }
        select.addEventListener('change', function () { sync(); local.focus(); });
        local.addEventListener('input', sync);
        local.addEventListener('blur', sync);
        if (local.form) local.form.addEventListener('submit', sync);
        sync();
    }

    function init() { document.querySelectorAll('input[data-country-picker]').forEach(enhance); }
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init); else init();
})();
