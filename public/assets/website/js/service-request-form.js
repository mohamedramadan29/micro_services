/*
 * Dynamic service request form: conditional questions + client-side checks.
 * A question with data-condition='{"question_id":1,"values":["نعم"],"negate":false}'
 * is shown only when question 1 is visible, answered, and its answer matches one
 * of the values (or matches none of them when negate is true). Hidden questions
 * are disabled so they are neither required nor submitted.
 * The server applies the same rule when validating.
 */
(function () {
    'use strict';

    function inputsOf(field) {
        return Array.prototype.slice.call(field.querySelectorAll('input, select, textarea'));
    }

    function answerOf(field) {
        if (!field || field.hidden) {
            return [];
        }
        var values = [];
        inputsOf(field).forEach(function (input) {
            if (input.type === 'radio' || input.type === 'checkbox') {
                if (input.checked) { values.push(input.value); }
            } else if (input.type !== 'file' && input.value !== '') {
                values.push(input.value);
            }
        });
        return values;
    }

    function setRequired(field, required) {
        var inputs = inputsOf(field);
        var type = field.dataset.type;
        inputs.forEach(function (input) { input.required = false; });
        if (!required) {
            return;
        }
        if (type === 'multi_choice') {
            return; // checked on submit: HTML cannot require "at least one" checkbox
        }
        if (inputs.length && (inputs[0].type === 'radio')) {
            inputs[0].required = true;
            return;
        }
        inputs.forEach(function (input) { input.required = true; });
    }

    function setVisible(field, visible) {
        field.hidden = !visible;
        inputsOf(field).forEach(function (input) { input.disabled = !visible; });
        setRequired(field, visible && field.dataset.required === '1');
    }

    function evaluate(root) {
        var fields = root.querySelectorAll('.rf-field[data-question-id]');
        var byId = {};
        fields.forEach(function (field) { byId[field.dataset.questionId] = field; });

        // Repeat so chains (A → B → C) settle in any order.
        for (var pass = 0; pass < fields.length + 1; pass++) {
            var changed = false;
            fields.forEach(function (field) {
                if (!field.dataset.condition) { return; }
                var condition = JSON.parse(field.dataset.condition);
                var answer = answerOf(byId[condition.question_id]);
                var matches = answer.some(function (value) { return condition.values.indexOf(value) !== -1; });
                var visible = answer.length > 0 && (condition.negate ? !matches : matches);
                if (visible === field.hidden) {
                    setVisible(field, visible);
                    changed = true;
                }
            });
            if (!changed) { break; }
        }
    }

    function showError(field, message) {
        var box = field.querySelector('.rf-field-error[data-client]');
        if (!box) {
            box = document.createElement('div');
            box.className = 'rf-field-error';
            box.setAttribute('data-client', '1');
            box.setAttribute('role', 'alert');
            field.appendChild(box);
        }
        box.textContent = message;
    }

    function clearErrors(root) {
        root.querySelectorAll('.rf-field-error[data-client]').forEach(function (box) { box.remove(); });
    }

    // Checks the browser cannot express with attributes alone.
    function validate(root) {
        clearErrors(root);
        var firstInvalid = null;
        root.querySelectorAll('.rf-field').forEach(function (field) {
            if (field.hidden) { return; }
            var type = field.dataset.type;
            var error = null;

            if (type === 'multi_choice' && field.dataset.required === '1' && answerOf(field).length === 0) {
                error = 'من فضلك اختر إجابة واحدة على الأقل';
            }
            if (type === 'number') {
                var min = field.querySelector('input[name$="[min]"]');
                var max = field.querySelector('input[name$="[max]"]');
                if (min && max && min.value !== '' && max.value !== '' && parseFloat(min.value) > parseFloat(max.value)) {
                    error = 'القيمة «من» يجب ألا تكون أكبر من «إلى»';
                }
                if (min && max && field.dataset.required === '1' && (min.value === '' || max.value === '')) {
                    error = 'من فضلك أدخل القيمتين «من» و«إلى»';
                }
            }
            var file = field.querySelector('input[type="file"][data-max-files]');
            if (file && file.files.length) {
                var maxFiles = parseInt(file.dataset.maxFiles, 10);
                var maxBytes = parseInt(file.dataset.maxSizeMb, 10) * 1024 * 1024;
                if (file.files.length > maxFiles) {
                    error = 'الحد الأقصى ' + maxFiles + ' ملفات';
                }
                Array.prototype.forEach.call(file.files, function (f) {
                    if (f.size > maxBytes) { error = 'الملف «' + f.name + '» أكبر من ' + file.dataset.maxSizeMb + ' ميجابايت'; }
                });
            }

            if (error) {
                showError(field, error);
                firstInvalid = firstInvalid || field;
            }
        });
        if (firstInvalid) {
            firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
        return !firstInvalid;
    }

    // Set required/visibility for the fields currently in the form (run again
    // after the service's questions are swapped in).
    function prepare(root) {
        root.querySelectorAll('.rf-field').forEach(function (field) {
            if (field.dataset.prepared) { return; }
            field.dataset.prepared = '1';
            setRequired(field, field.dataset.required === '1');
            if (field.dataset.condition) { setVisible(field, false); }
        });
        clearErrors(root);
        evaluate(root);
    }

    function init(root) {
        prepare(root);
        root.addEventListener('change', function () { evaluate(root); });
        root.addEventListener('input', function () { evaluate(root); });

        var form = root.closest('form');
        if (form) {
            form.addEventListener('submit', function (event) {
                if (!validate(root)) {
                    event.preventDefault();
                    return;
                }
                // One submission only (a double click must not send the request twice).
                if (form.dataset.submitting === '1') {
                    event.preventDefault();
                    return;
                }
                form.dataset.submitting = '1';
                form.setAttribute('aria-busy', 'true');
            });
            // Back from the browser cache: the form must be sendable again.
            window.addEventListener('pageshow', function (event) {
                if (event.persisted) { delete form.dataset.submitting; form.removeAttribute('aria-busy'); }
            });
        }
    }

    window.ServiceRequestForm = { prepare: prepare };
    document.querySelectorAll('[data-request-form]').forEach(init);
})();
