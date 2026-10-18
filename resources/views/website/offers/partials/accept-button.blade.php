{{-- The client picks this offer and pays its price (held by the site until they confirm the work).
     On a staged project (service 3) nothing is paid now: the agreement and stage funding come next. --}}
@if ($offer->request->isStaged())
<form method="POST" action="{{ route('offers.accept', $offer) }}"
    onsubmit="return confirm(@js('اختيار ' . ($offer->freelancer->name) . ' لمشروعك؟ لن تدفع الآن: يقترح المستقل الاتفاق ومراحله، ثم توافق وتموّل المرحلة الأولى فقط. تُغلق بقية العروض.'))">
    @csrf
    <button type="submit" class="btn rf-btn-accept"><i class="bi bi-check2-circle"></i> اختيار المستقل والبدء في الاتفاق</button>
</form>
@else
<form method="POST" action="{{ route('offers.accept', $offer) }}"
    onsubmit="return confirm(@js('اختيار عرض ' . ($offer->freelancer->name) . ' ودفع ' . (\App\Support\Money::format($offer->price)) . '؟ يبقى المبلغ محجوزًا لدى نفّذها ولا يُحوّل للمستقل إلا بعد تأكيدك استلام العمل، وتُغلق بقية العروض.'))">
    @csrf
    <button type="submit" class="btn rf-btn-accept"><i class="bi bi-check2-circle"></i> اختيار العرض والدفع</button>
</form>
@endif
