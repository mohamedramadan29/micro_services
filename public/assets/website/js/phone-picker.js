/*
 * Country-code picker for phone fields marked data-country-picker (client file «تعديلات», item 11).
 * The input stays the real field: a code chosen in the list is put in front of a local number
 * (its leading 0 removed) when the visitor leaves the field or sends the form.
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

    function enhance(input) {
        if (input.dataset.pickerReady) return;
        input.dataset.pickerReady = '1';
        var select = document.createElement('select');
        select.className = 'form-control rf-phone-code';
        select.setAttribute('aria-label', 'رمز الدولة');
        select.innerHTML = '<option value="">رمز الدولة</option>' + CODES.map(function (c) {
            return '<option value="' + c[0] + '">' + c[1] + ' ' + c[0] + '</option>';
        }).join('');
        var wrap = document.createElement('div');
        wrap.className = 'rf-phone-wrap';
        input.parentNode.insertBefore(wrap, input);
        wrap.appendChild(select);
        wrap.appendChild(input);

        var current = input.value.replace(/\s/g, '');
        var match = longestFirst.find(function (c) { return current.indexOf(c[0]) === 0; });
        select.value = match ? match[0] : (input.dataset.defaultCode || '+20');

        function compose() {
            var v = input.value.trim();
            if (!v || v.charAt(0) === '+' || v.indexOf('00') === 0 || !select.value) return;
            input.value = select.value + ' ' + v.replace(/^0+/, '');
        }
        select.addEventListener('change', function () {
            if (!select.value) return;
            var v = input.value.trim().replace(/\s/g, '');
            var old = longestFirst.find(function (c) { return v.indexOf(c[0]) === 0; });
            if (old) input.value = select.value + ' ' + v.slice(old[0].length);
        });
        input.addEventListener('blur', compose);
        if (input.form) input.form.addEventListener('submit', compose);
    }

    function init() { document.querySelectorAll('input[data-country-picker]').forEach(enhance); }
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init); else init();
})();
