{{-- Admin: approve (with the leaving freelancer's %) or reject a change; $change null = change directly. --}}
<form method="POST" action="{{ route('contracts.change.decide', $contract) }}" class="rf-admin-box mt-2">
    @csrf
    @if ($change)<input type="hidden" name="change" value="{{ $change->id }}">@endif
    <label class="rf-field-label">نسبة المستقل المغادر من المرحلة الجارية غير المعتمدة (%)</label>
    <input type="number" name="percent" min="0" max="100" class="form-control mb-2" value="{{ old('percent', 0) }}">
    <textarea name="note" rows="2" maxlength="3000" class="form-control mb-2" placeholder="سبب القرار (يظهر للطرفين)"></textarea>
    @error('note')<div class="rf-field-error">{{ $message }}</div>@enderror
    @error('percent')<div class="rf-field-error">{{ $message }}</div>@enderror
    <p class="rf-muted mb-2">عند الاعتماد: نسبته تُحوّل له والباقي لرصيد العميل، والمراحل المموّلة مقدمًا تعود لرصيد العميل، ويُنشر الجزء المتبقي في طلب جديد لعروض جديدة.</p>
    <button name="decision" value="approve" class="btn rf-btn-primary" onclick="return confirm('اعتماد تغيير المستقل وتسوية المبالغ؟ لا يمكن التراجع.')">اعتماد التغيير</button>
    @if ($change)<button name="decision" value="reject" class="btn rf-btn-danger-text">رفض الطلب</button>@endif
</form>
