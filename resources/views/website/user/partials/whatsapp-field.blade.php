{{-- Freelancers must give a WhatsApp number (client file «تعديلات», item 11); shown only when «مستقل» is chosen. --}}
@php $freelancerChosen = old('account_type', request('type') === 'freelancer' ? \App\Models\User::TYPE_FREELANCER : null) === \App\Models\User::TYPE_FREELANCER; @endphp
<div class="form-group" id="rf-whatsapp-field" @unless ($freelancerChosen) hidden @endunless>
    <label for="rf-whatsapp"> رقم واتساب <span class="text-danger">*</span></label>
    <input type="tel" class="form-control" id="rf-whatsapp" name="whatsapp" data-country-picker dir="ltr" maxlength="30" @if ($freelancerChosen) required @endif
        placeholder="10X XXX XXXX" value="{{ old('whatsapp') }}">
    <small class="text-muted d-block mt-1">رقم عليه واتساب (اختر رمز الدولة من القائمة)، ليتواصل معك فريق نفّذها. يُوثَّق حسابك بتأكيد بريدك الإلكتروني.</small>
</div>
<script>
    (function () {
        var field = document.getElementById('rf-whatsapp-field');
        document.querySelectorAll('input[name="account_type"]').forEach(function (radio) {
            radio.addEventListener('change', function () {
                var freelancer = document.getElementById('type_freelancer').checked;
                field.hidden = !freelancer;
                document.getElementById('rf-whatsapp').required = freelancer;
            });
        });
    })();
</script>
