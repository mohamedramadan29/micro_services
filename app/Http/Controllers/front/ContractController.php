<?php

namespace App\Http\Controllers\front;

use App\Http\Controllers\Controller;
use App\Models\Contract;
use App\Models\ContractAgreement;
use App\Models\ContractDelivery;
use App\Models\ContractDeliveryFile;
use App\Models\ContractStage;
use App\Services\Contracts\AgreementService;
use App\Services\Contracts\ContractLedger;
use App\Services\Contracts\StageClock;
use App\Services\Contracts\StageWorkflow;
use App\Models\ContractPause;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use RuntimeException;

/** Service 3: the contract page — agreement versions now, stages and funding next. */
class ContractController extends Controller
{
    public function __construct(private AgreementService $agreements, private ContractLedger $ledger, private StageWorkflow $workflow)
    {
    }

    /** «مشاريعي»: staged projects the user takes part in, as client or freelancer. */
    public function index(Request $request)
    {
        $tab = in_array($request->query('tab'), ['active', 'done'], true) ? $request->query('tab') : 'active';
        $user = Auth::user();
        $contracts = Contract::involving($user)
            ->whereIn('status', $tab === 'active' ? [Contract::STATUS_AGREEMENT, Contract::STATUS_ACTIVE] : [Contract::STATUS_COMPLETED, Contract::STATUS_CANCELLED, Contract::STATUS_REPLACED])
            ->with(['client', 'freelancer', 'stages.deliveries', 'agreements', 'disputes'])
            ->latest('id')
            ->paginate(10)
            ->withQueryString();
        $counts = [
            'active' => Contract::involving($user)->whereIn('status', [Contract::STATUS_AGREEMENT, Contract::STATUS_ACTIVE])->count(),
            'done' => Contract::involving($user)->whereIn('status', [Contract::STATUS_COMPLETED, Contract::STATUS_CANCELLED, Contract::STATUS_REPLACED])->count(),
        ];

        return view('website.contracts.index', compact('contracts', 'tab', 'counts'));
    }

    public function show(Contract $contract)
    {
        abort_unless($contract->visibleTo(Auth::user()), 403);
        $contract->load(['client', 'freelancer', 'request.acceptedOffer', 'agreements.proposer', 'stages.deliveries.files', 'stages.pauses.files', 'disputes.files', 'disputes.opener', 'freelancerChanges.continuation', 'ratings', 'addenda.proposer', 'replyRequests']);

        return view('website.contracts.show', [
            'contract' => $contract,
            'latest' => $contract->agreements->first(),
            'isClient' => (int) Auth::id() === (int) $contract->client_id,
            'isFreelancer' => (int) Auth::id() === (int) $contract->freelancer_id,
            'current' => $this->workflow->currentStage($contract),
            'isAdmin' => $this->isAdmin(),
        ]);
    }

    /** The client funds one or more stages (several ahead is allowed): site balance or EasyKash (+ fees). */
    public function fund(Request $request, Contract $contract)
    {
        abort_unless((int) Auth::id() === (int) $contract->client_id, 403);
        $data = $request->validate([
            'stages' => 'required|array|min:1',
            'stages.*' => 'integer',
            'source' => 'required|in:balance,easykash',
        ], ['stages.required' => 'اختر مرحلة واحدة على الأقل لتمويلها']);

        try {
            if ($data['source'] === 'easykash') {
                return redirect()->away($this->ledger->startFunding($contract, $data['stages'], Auth::user()));
            }
            $this->ledger->fundFromBalance($contract, $data['stages'], Auth::user());
            $this->workflow->advance($contract);
        } catch (RuntimeException $e) {
            return back()->withErrors($e->getMessage());
        } catch (\Throwable $e) {
            report($e);

            return back()->withErrors('تعذّر الاتصال ببوابة الدفع الآن. لم يُخصم شيء؛ حاول بعد قليل.');
        }

        return redirect()->to(route('contracts.show', $contract) . '#stages')->with('Success_message', 'تم التمويل من رصيدك. يبقى المبلغ لدى نفّذها حتى تعتمد المرحلة.');
    }

    public function deliver(Request $request, Contract $contract, ContractStage $stage)
    {
        abort_unless((int) $stage->contract_id === (int) $contract->id && (int) Auth::id() === (int) $contract->freelancer_id, 403);
        $data = $request->validate([
            'note' => 'required|string|min:5|max:3000',
            'links' => 'nullable|string|max:3000',
            'files' => 'nullable|array|max:' . \App\Services\Contracts\ContractPolicy::number('max_files'),
            'files.*' => 'file|max:' . (\App\Services\Contracts\ContractPolicy::number('file_max_mb') * 1024) . '|mimes:' . implode(',', ContractDelivery::FILE_EXTENSIONS),
        ], [
            'note.required' => 'اكتب ما سلّمته في هذه المرحلة',
            'files.max' => 'الحد الأقصى ' . \App\Services\Contracts\ContractPolicy::number('max_files') . ' ملفات؛ للملفات الكبيرة أضف رابطًا',
            'files.*.max' => 'حجم الملف يجب ألا يتجاوز ' . \App\Services\Contracts\ContractPolicy::number('file_max_mb') . ' ميجابايت؛ للأكبر أضف رابط Drive أو Dropbox أو WeTransfer',
            'files.*.mimes' => 'نوع الملف غير مسموح',
        ]);
        $links = collect(preg_split('/\s+/', (string) ($data['links'] ?? '')))->filter()->values();
        if ($links->count() > ContractDelivery::MAX_LINKS || $links->contains(fn ($link) => ! filter_var($link, FILTER_VALIDATE_URL) || ! preg_match('#^https?://#i', $link))) {
            return back()->withInput()->withErrors(['links' => 'اكتب حتى ' . ContractDelivery::MAX_LINKS . ' روابط صحيحة تبدأ بـ https://، كل رابط في سطر']);
        }

        try {
            $this->workflow->deliver($stage, Auth::user(), $data['note'], $links->all(), $request->file('files', []));
        } catch (RuntimeException $e) {
            return back()->withInput()->withErrors($e->getMessage());
        }

        return redirect()->to(route('contracts.show', $contract) . '#stages')->with('Success_message', 'تم التسليم، وسيراجعه العميل خلال ' . \App\Services\Contracts\ContractPolicy::number('review_days') . ' أيام.');
    }

    public function review(Request $request, Contract $contract, ContractDelivery $delivery)
    {
        abort_unless((int) $delivery->stage->contract_id === (int) $contract->id && (int) Auth::id() === (int) $contract->client_id, 403);
        $data = $request->validate([
            'decision' => 'required|in:approve,revision',
            'note' => 'required_if:decision,revision|nullable|string|max:3000',
        ], ['note.required_if' => 'اكتب التعديل المطلوب']);

        try {
            if ($data['decision'] === 'approve') {
                $stage = $this->workflow->approve($delivery, Auth::user());
                $message = 'اعتمدت المرحلة «' . $stage->title . '» وحُوّل مبلغها للمستقل.';
            } else {
                $this->workflow->requestRevision($delivery, Auth::user(), $data['note']);
                $message = 'أُرسل طلب التعديل للمستقل.';
            }
        } catch (RuntimeException $e) {
            return back()->withErrors($e->getMessage());
        }

        return redirect()->to(route('contracts.show', $contract) . '#stages')->with('Success_message', $message);
    }

    /** «بانتظار العميل»: the freelancer stops the clock with a specific request. */
    public function pause(Request $request, Contract $contract, ContractStage $stage, StageClock $clock)
    {
        abort_unless((int) $stage->contract_id === (int) $contract->id && (int) Auth::id() === (int) $contract->freelancer_id, 403);
        $data = $request->validate(['request' => 'required|string|min:10|max:2000'], [
            'request.required' => 'اكتب بالتحديد ما تحتاجه من العميل',
            'request.min' => 'وضّح المطلوب من العميل (10 أحرف على الأقل)',
        ]);

        return $this->clockAction($contract, fn () => $clock->pause($stage, Auth::user(), $data['request']), 'توقف العدّاد، ووصل طلبك للعميل.');
    }

    /** The client answers the pause (note + files) or objects to it; the freelancer may resume it himself. */
    public function pauseAction(Request $request, Contract $contract, ContractPause $pause, string $action, StageClock $clock)
    {
        abort_unless((int) $pause->stage->contract_id === (int) $contract->id, 404);
        if ($action === 'resume') {
            abort_unless((int) Auth::id() === (int) $contract->freelancer_id, 403);

            return $this->clockAction($contract, fn () => $clock->resumeByFreelancer($pause, Auth::user()), 'استؤنف العدّاد ومُدّ موعد المرحلة بمدة الإيقاف.');
        }
        abort_unless((int) Auth::id() === (int) $contract->client_id, 403);
        if ($action === 'object') {
            $data = $request->validate(['reason' => 'required|string|min:5|max:2000'], ['reason.required' => 'اكتب سبب اعتراضك']);

            return $this->clockAction($contract, fn () => $clock->object($pause, Auth::user(), $data['reason']), 'أُرسل اعتراضك؛ استؤنف العدّاد دون تمديد وستراجع الإدارة الأمر.');
        }
        $data = $request->validate([
            'response' => 'required|string|min:2|max:3000',
            'files' => 'nullable|array|max:' . \App\Services\Contracts\ContractPolicy::number('max_files'),
            'files.*' => 'file|max:' . (\App\Services\Contracts\ContractPolicy::number('file_max_mb') * 1024) . '|mimes:' . implode(',', ContractDelivery::FILE_EXTENSIONS),
        ], [
            'response.required' => 'اكتب ردك على طلب المستقل',
            'files.*.max' => 'حجم الملف يجب ألا يتجاوز ' . \App\Services\Contracts\ContractPolicy::number('file_max_mb') . ' ميجابايت',
            'files.*.mimes' => 'نوع الملف غير مسموح',
        ]);

        return $this->clockAction($contract, fn () => $clock->answer($pause, Auth::user(), $data['response'], $request->file('files', [])), 'أُرسل المطلوب للمستقل واستؤنف العدّاد.');
    }

    /** Either party opens a dispute (reason, description, evidence). */
    public function openDispute(Request $request, Contract $contract, \App\Services\Contracts\DisputeService $disputes)
    {
        abort_unless(in_array((int) Auth::id(), [(int) $contract->client_id, (int) $contract->freelancer_id], true), 403);
        $data = $request->validate([
            'reason' => ['required', \Illuminate\Validation\Rule::in(array_keys(\App\Services\Contracts\ContractPolicy::disputeReasons()))],
            'description' => 'required|string|min:20|max:5000',
        ] + $this->evidenceRules(), [
            'reason.required' => 'اختر سبب الخلاف',
            'description.required' => 'اشرح الخلاف',
            'description.min' => 'اشرح الخلاف بتفصيل أكثر (20 حرفًا على الأقل)',
        ] + $this->evidenceMessages());

        return $this->clockAction($contract, fn () => $disputes->open($contract, Auth::user(), $data['reason'], $data['description'], $request->file('files', [])),
            'فُتح الخلاف. أمام الطرف الآخر ' . \App\Services\Contracts\ContractPolicy::number('dispute_respond_days') . ' أيام للرد، ثم تقرر الإدارة. مبلغ المرحلة مجمّد حتى القرار.');
    }

    public function disputeAction(Request $request, Contract $contract, \App\Models\ContractDispute $dispute, string $action, \App\Services\Contracts\DisputeService $disputes)
    {
        abort_unless((int) $dispute->contract_id === (int) $contract->id, 404);
        if ($action === 'withdraw') {
            return $this->clockAction($contract, fn () => $disputes->withdraw($dispute, Auth::user()), 'سُحب الخلاف.');
        }
        if (in_array($action, ['approve-proposal', 'reject-proposal'], true)) {
            abort_unless($dispute->proposed && \App\Support\StaffPermissions::canApprove(Auth::user(), (float) ($dispute->proposed['amount'] ?? 0)), 403);

            return $this->clockAction($contract, fn () => $disputes->settleProposal($dispute, Auth::user(), $action === 'approve-proposal'),
                $action === 'approve-proposal' ? 'اعتُمد القرار ونُفّذ.' : 'رُفض القرار المقترح.');
        }
        if ($action === 'respond') {
            $data = $request->validate(['response' => 'required|string|min:10|max:5000'] + $this->evidenceRules(), ['response.required' => 'اكتب ردك على الخلاف'] + $this->evidenceMessages());

            return $this->clockAction($contract, fn () => $disputes->respond($dispute, Auth::user(), $data['response'], $request->file('files', [])), 'أُرسل ردك، والقرار الآن لدى الإدارة.');
        }
        abort_unless($this->isAdmin(), 403);
        $data = $request->validate([
            'decision' => ['required', \Illuminate\Validation\Rule::in(array_keys(\App\Models\ContractDispute::DECISIONS))],
            'note' => 'required|string|min:5|max:3000',
            'percent' => 'required_if:decision,split,change_freelancer|nullable|integer|min:0|max:100',
            'revision_due' => 'required_if:decision,revision|nullable|date_format:Y-m-d|after_or_equal:today',
        ], [
            'note.required' => 'اكتب سبب القرار؛ يظهر للطرفين',
            'percent.required_if' => 'حدد نسبة المستقل (في تغيير المستقل: نسبته من المرحلة الجارية، 0 إن لم يستحق شيئًا)',
            'revision_due.required_if' => 'حدد موعد التعديل',
        ]);
        if ($data['decision'] === 'split' && ((int) $data['percent'] <= 0 || (int) $data['percent'] >= 100)) {
            return back()->withErrors(['percent' => 'نسبة التقسيم بين 1 و99؛ للكل اختر التحرير أو الاسترجاع']);
        }

        // Staff roles: a reviewer's money decision, or one above the threshold, waits for approval.
        $money = in_array($data['decision'], \App\Support\StaffPermissions::MONEY_DECISIONS, true);
        $mode = \App\Support\StaffPermissions::mode(Auth::user(), $money, $disputes->amountAt($dispute, $data['decision']));
        abort_if($mode === 'deny', 403, 'دورك لا يسمح بقرارات الخلافات');
        if ($mode === 'propose') {
            return $this->clockAction($contract, fn () => $disputes->propose($dispute, Auth::user(), [
                'decision' => $data['decision'], 'note' => $data['note'],
                'percent' => isset($data['percent']) ? (int) $data['percent'] : null, 'revision_due' => $data['revision_due'] ?? null,
            ]), 'حُفظ القرار كمقترح بانتظار اعتماد المحاسب أو المدير.');
        }

        return $this->clockAction($contract, fn () => $disputes->decide($dispute, Auth::user(), $data['decision'], $data['note'], isset($data['percent']) ? (int) $data['percent'] : null, $data['revision_due'] ?? null),
            'سُجّل القرار ونُفّذ، ووصل للطرفين.');
    }

    /** The client (after 7 late days) or the freelancer (withdrawal) asks to change the freelancer; the admin decides. */
    public function requestChange(Request $request, Contract $contract, \App\Services\Contracts\FreelancerChangeService $changes)
    {
        $kind = match ((int) Auth::id()) {
            (int) $contract->client_id => 'client',
            (int) $contract->freelancer_id => 'withdrawal',
            default => abort(403),
        };
        $data = $request->validate(['reason' => 'required|string|min:10|max:3000'], ['reason.required' => 'اكتب السبب', 'reason.min' => 'وضّح السبب (10 أحرف على الأقل)']);

        return $this->clockAction($contract, fn () => $changes->request($contract, Auth::user(), $kind, $data['reason']), 'أُرسل الطلب، والقرار لدى الإدارة.');
    }

    /** The admin approves (with the leaving freelancer's %) or rejects a change, or changes the freelancer directly. */
    public function decideChange(Request $request, Contract $contract, \App\Services\Contracts\FreelancerChangeService $changes)
    {
        abort_unless($this->isAdmin(), 403);
        $data = $request->validate([
            'change' => 'nullable|integer',
            'decision' => 'required|in:approve,reject',
            'percent' => 'required_if:decision,approve|nullable|integer|min:0|max:100',
            'note' => 'required|string|min:5|max:3000',
        ], ['note.required' => 'اكتب سبب القرار', 'percent.required_if' => 'حدد نسبة المستقل المغادر من المرحلة الجارية (0 إن لم يستحق شيئًا)']);

        $change = ! empty($data['change']) ? $contract->freelancerChanges()->whereKey($data['change'])->firstOrFail() : null;
        if ($data['decision'] === 'approve') {
            $mode = \App\Support\StaffPermissions::mode(Auth::user(), true, (float) $contract->totals()['held']);
            abort_if($mode === 'deny', 403);
            if ($mode === 'propose') {
                return $this->clockAction($contract, function () use ($contract, $change, $data) {
                    $change ??= $contract->freelancerChanges()->create(['requested_by' => Auth::id(), 'kind' => 'admin', 'reason' => $data['note'], 'leaving_freelancer_id' => $contract->freelancer_id]);
                    $change->update(['proposed' => ['percent' => (int) $data['percent'], 'note' => $data['note'], 'amount' => (float) $contract->totals()['held']], 'proposed_by' => Auth::id()]);
                }, 'حُفظ التغيير كمقترح بانتظار اعتماد المحاسب أو المدير.');
            }
        }

        return $this->clockAction($contract, function () use ($contract, $changes, $change, $data) {
            if ($data['decision'] === 'reject') {
                abort_unless($change, 404);
                abort_if(\App\Support\StaffPermissions::mode(Auth::user(), false, 0) !== 'direct', 403, 'دورك لا يسمح بهذا القرار');
                $changes->reject($change, Auth::user(), $data['note']);

                return;
            }
            $change ??= $contract->freelancerChanges()->create(['requested_by' => Auth::id(), 'kind' => 'admin', 'reason' => $data['note'], 'leaving_freelancer_id' => $contract->freelancer_id]);
            $changes->apply($change, Auth::user(), (int) $data['percent'], $data['note']);
        }, $data['decision'] === 'reject' ? 'رُفض طلب التغيير.' : 'تم تغيير المستقل ونُشر الجزء المتبقي لعروض جديدة.');
    }

    /** After completion each side rates the other once (optional). */
    public function rate(Request $request, Contract $contract, \App\Services\Contracts\RatingService $ratings)
    {
        $data = $request->validate(['stars' => 'required|integer|min:1|max:5', 'comment' => 'nullable|string|max:1000'], ['stars.required' => 'اختر عدد النجوم']);

        return $this->clockAction($contract, fn () => $ratings->rate($contract, Auth::user(), (int) $data['stars'], $data['comment'] ?? null), 'شكرًا لتقييمك.');
    }

    /** An approver carries out (or drops) a proposed change of freelancer. */
    public function settleChangeProposal(Request $request, Contract $contract, \App\Models\ContractFreelancerChange $change, \App\Services\Contracts\FreelancerChangeService $changes)
    {
        abort_unless((int) $change->contract_id === (int) $contract->id && $change->proposed
            && \App\Support\StaffPermissions::canApprove(Auth::user(), (float) ($change->proposed['amount'] ?? 0)), 403);
        $approve = $request->input('decision') === 'approve';
        $proposed = $change->proposed;

        return $this->clockAction($contract, function () use ($change, $changes, $approve, $proposed) {
            \Illuminate\Support\Facades\DB::transaction(function () use ($change, $changes, $approve, $proposed) {
                $change->update(['proposed' => null, 'proposed_by' => null]);
                if ($approve) {
                    $changes->apply($change->fresh(), Auth::user(), (int) $proposed['percent'], $proposed['note'] . ' (اعتمده ' . Auth::user()->name . ')');
                }
            });
        }, $approve ? 'اعتُمد تغيير المستقل ونُفّذ.' : 'رُفض المقترح؛ الطلب ما زال بانتظار قرار.');
    }

    private function evidenceRules(): array
    {
        return [
            'files' => 'nullable|array|max:' . \App\Services\Contracts\ContractPolicy::number('max_files'),
            'files.*' => 'file|max:' . (\App\Services\Contracts\ContractPolicy::number('file_max_mb') * 1024) . '|mimes:' . implode(',', ContractDelivery::FILE_EXTENSIONS),
        ];
    }

    /** An addendum after the start: changes to unsettled stages and/or new stages, with a reason. */
    public function proposeAddendum(Request $request, Contract $contract, \App\Services\Contracts\AddendumService $addenda)
    {
        abort_unless(in_array((int) Auth::id(), [(int) $contract->client_id, (int) $contract->freelancer_id], true), 403);
        $data = $request->validate([
            'reason' => 'required|string|min:10|max:2000',
            'stages' => 'nullable|array',
            'stages.*.title' => 'nullable|string|max:150',
            'stages.*.deliverable' => 'nullable|string|max:2000',
            'stages.*.acceptance' => 'nullable|string|max:1000',
            'stages.*.duration_days' => 'nullable|integer|min:1|max:365',
            'stages.*.amount' => 'nullable|numeric|min:1|max:10000000',
            'stages.*.revisions' => 'nullable|integer|min:0|max:20',
            'stages.*.unlimited_revisions' => 'nullable|boolean',
            'stages.*.remove' => 'nullable|boolean',
            'new' => 'nullable|array|max:5',
            'new.*.title' => 'nullable|string|max:150',
            'new.*.deliverable' => 'nullable|required_with:new.*.title|string|min:5|max:2000',
            'new.*.acceptance' => 'nullable|string|max:1000',
            'new.*.duration_days' => 'nullable|required_with:new.*.title|integer|min:1|max:365',
            'new.*.amount' => 'nullable|required_with:new.*.title|numeric|min:1|max:10000000',
            'new.*.revisions' => 'nullable|integer|min:0|max:20',
        ], [
            'reason.required' => 'اكتب سبب الملحق',
            'reason.min' => 'وضّح سبب الملحق (10 أحرف على الأقل)',
            'new.*.deliverable.required_with' => 'اكتب ما يُسلَّم في المرحلة الجديدة',
            'new.*.deliverable.min' => 'وضّح ما يُسلَّم في المرحلة الجديدة',
            'new.*.duration_days.required_with' => 'حدد مدة المرحلة الجديدة',
            'new.*.amount.required_with' => 'حدد مبلغ المرحلة الجديدة',
        ]);

        $changes = [];
        foreach ($data['stages'] ?? [] as $id => $row) {
            if (! empty($row['remove'])) {
                $changes[] = ['type' => 'remove', 'stage_id' => (int) $id];

                continue;
            }
            $changes[] = ['type' => 'change', 'stage_id' => (int) $id] + array_filter([
                'title' => $row['title'] ?? null, 'deliverable' => $row['deliverable'] ?? null, 'acceptance' => $row['acceptance'] ?? null,
                'duration_days' => $row['duration_days'] ?? null, 'amount' => $row['amount'] ?? null,
            ], fn ($v) => $v !== null && $v !== '') + ['revisions' => ! empty($row['unlimited_revisions']) ? null : ($row['revisions'] ?? '')];
        }
        foreach ($data['new'] ?? [] as $row) {
            if (trim((string) ($row['title'] ?? '')) !== '') {
                $changes[] = ['type' => 'add'] + $row;
            }
        }
        // Rows left as they were are not changes.
        $stages = $contract->stages()->get()->keyBy('id');
        $changes = array_values(array_filter($changes, function ($c) use ($stages) {
            if ($c['type'] !== 'change') {
                return true;
            }
            $stage = $stages[$c['stage_id']] ?? null;
            if (! $stage) {
                return true;
            }
            foreach (['title', 'deliverable', 'acceptance', 'duration_days', 'amount'] as $field) {
                if (isset($c[$field]) && (string) $c[$field] !== '' && ($field === 'amount' ? round((float) $c[$field], 2) !== round((float) $stage->amount, 2) : (string) $c[$field] !== (string) $stage->{$field})) {
                    return true;
                }
            }

            return $c['revisions'] !== '' && ($c['revisions'] === null ? $stage->revisions !== null : (int) $c['revisions'] !== $stage->revisions);
        }));

        try {
            $addenda->propose($contract, Auth::user(), $changes, $data['reason']);
        } catch (RuntimeException $e) {
            return back()->withInput()->withErrors($e->getMessage());
        }

        return redirect()->to(route('contracts.show', $contract) . '#addenda')->with('Success_message', 'أُرسل الملحق للطرف الآخر، ولا يتغير شيء قبل موافقته.');
    }

    public function addendumAction(Request $request, Contract $contract, \App\Models\ContractAddendum $addendum, string $action, \App\Services\Contracts\AddendumService $addenda)
    {
        abort_unless((int) $addendum->contract_id === (int) $contract->id, 404);
        $note = $request->validate(['note' => 'nullable|string|max:2000'])['note'] ?? null;
        try {
            match ($action) {
                'approve' => $addenda->respond($addendum, Auth::user(), true, $note),
                'reject' => $addenda->respond($addendum, Auth::user(), false, $note),
                'cancel' => $addenda->cancel($addendum, Auth::user()),
            };
        } catch (RuntimeException $e) {
            return back()->withErrors($e->getMessage());
        }

        return redirect()->to(route('contracts.show', $contract) . '#addenda')->with('Success_message', [
            'approve' => 'وافقت على الملحق وطُبّق على المراحل.', 'reject' => 'رفضت الملحق.', 'cancel' => 'سحبت الملحق.',
        ][$action]);
    }

    /** A stage left «بانتظار الدفع» (the client left EasyKash): drop that payment so the stage can be funded again. */
    public function cancelFunding(Contract $contract, ContractStage $stage)
    {
        abort_unless((int) $stage->contract_id === (int) $contract->id && ((int) Auth::id() === (int) $contract->client_id || $this->isAdmin()), 403);

        return $this->clockAction($contract, fn () => $this->ledger->cancelFunding($stage), 'أُلغي الدفع المعلّق؛ يمكنك تمويل المرحلة من جديد.');
    }

    public function adminExtend(Request $request, Contract $contract, ContractStage $stage, \App\Services\Contracts\ContractAdminTools $tools)
    {
        abort_unless($this->isAdmin() && (int) $stage->contract_id === (int) $contract->id, 403);
        $data = $request->validate(['days' => 'required|integer|min:1|max:90', 'reason' => 'required|string|min:5|max:2000'], ['reason.required' => 'اكتب سبب التمديد', 'days.required' => 'حدد عدد الأيام']);

        return $this->clockAction($contract, fn () => $tools->extendDeadline($stage, (int) $data['days'], $data['reason'], Auth::user()), 'مُدّ موعد المرحلة.');
    }

    public function adminApprove(Request $request, Contract $contract, ContractDelivery $delivery, \App\Services\Contracts\ContractAdminTools $tools)
    {
        abort_unless($this->isAdmin() && (int) $delivery->stage->contract_id === (int) $contract->id, 403);
        $data = $request->validate(['reason' => 'required|string|min:10|max:2000'], ['reason.required' => 'اكتب لماذا تعتمد التسليم نيابة عن العميل', 'reason.min' => 'وضّح السبب (10 أحرف على الأقل)']);

        return $this->clockAction($contract, fn () => $tools->approveForClient($delivery, $data['reason'], Auth::user()), 'اعتُمدت المرحلة وحُوّل مبلغها للمستقل.');
    }

    public function adminEndPause(Request $request, Contract $contract, ContractPause $pause, \App\Services\Contracts\ContractAdminTools $tools)
    {
        abort_unless($this->isAdmin() && (int) $pause->stage->contract_id === (int) $contract->id, 403);
        $data = $request->validate(['reason' => 'required|string|min:5|max:2000'], ['reason.required' => 'اكتب سبب إنهاء الإيقاف']);

        return $this->clockAction($contract, fn () => $tools->endPause($pause, $request->boolean('extend'), $data['reason'], Auth::user()), 'انتهى إيقاف العدّاد.');
    }

    public function adminRequestReply(Request $request, Contract $contract, \App\Services\Contracts\ContractAdminTools $tools)
    {
        abort_unless($this->isAdmin(), 403);
        $data = $request->validate(['question' => 'required|string|min:5|max:2000', 'days' => 'required|integer|min:1|max:30'],
            ['question.required' => 'اكتب ما تريد معرفته من الطرفين', 'days.required' => 'حدد مهلة الرد بالأيام']);

        return $this->clockAction($contract, fn () => $tools->requestReply($contract, $data['question'], (int) $data['days'], Auth::user()), 'أُرسل طلب الرد للطرفين.');
    }

    public function answerReply(Request $request, Contract $contract, \App\Models\ContractReplyRequest $reply, \App\Services\Contracts\ContractAdminTools $tools)
    {
        abort_unless((int) $reply->contract_id === (int) $contract->id, 404);
        $data = $request->validate(['reply' => 'required|string|min:2|max:3000'], ['reply.required' => 'اكتب ردك']);

        return $this->clockAction($contract, fn () => $tools->reply($reply, Auth::user(), $data['reply']), 'أُرسل ردك لإدارة نفّذها.');
    }

    public function closeReply(Contract $contract, \App\Models\ContractReplyRequest $reply, \App\Services\Contracts\ContractAdminTools $tools)
    {
        abort_unless($this->isAdmin() && (int) $reply->contract_id === (int) $contract->id, 403);

        return $this->clockAction($contract, fn () => $tools->closeReply($reply, Auth::user()), 'أُغلق طلب الرد.');
    }

    private function evidenceMessages(): array
    {
        return ['files.*.max' => 'حجم الملف يجب ألا يتجاوز ' . \App\Services\Contracts\ContractPolicy::number('file_max_mb') . ' ميجابايت', 'files.*.mimes' => 'نوع الملف غير مسموح'];
    }

    private function isAdmin(): bool
    {
        $user = Auth::user();

        return $user && $user->account_type === \App\Models\User::TYPE_ADMIN && (int) $user->status === 1;
    }

    private function clockAction(Contract $contract, callable $action, string $message)
    {
        try {
            $action();
        } catch (RuntimeException $e) {
            return back()->withInput()->withErrors($e->getMessage());
        }

        return redirect()->to(route('contracts.show', $contract) . '#stages')->with('Success_message', $message);
    }

    public function file(Contract $contract, ContractDeliveryFile $file)
    {
        abort_unless($contract->visibleTo(Auth::user()) && (int) $file->contractId() === (int) $contract->id, 403);
        abort_unless(Storage::disk(ContractDeliveryFile::DISK)->exists($file->path), 404);

        return Storage::disk(ContractDeliveryFile::DISK)->download($file->path, $file->original_name);
    }

    public function propose(Request $request, Contract $contract)
    {
        abort_unless((int) Auth::id() === (int) $contract->freelancer_id, 403);
        $data = $request->validate([
            'scope' => 'required|string|min:20|max:5000',
            'out_of_scope' => 'nullable|string|max:3000',
            'client_materials' => 'nullable|string|max:3000',
            'freelancer_note' => 'nullable|string|max:2000',
            'stages' => 'required|array|min:1|max:' . \App\Services\Contracts\ContractPolicy::number('max_stages'),
            'stages.*.title' => 'required|string|max:150',
            'stages.*.deliverable' => 'required|string|min:5|max:2000',
            'stages.*.acceptance' => 'nullable|string|max:1000',
            'stages.*.duration_days' => 'required|integer|min:1|max:365',
            'stages.*.amount' => 'required|numeric|min:1|max:10000000',
            'stages.*.revisions' => 'nullable|integer|min:0|max:20',
            'stages.*.unlimited_revisions' => 'nullable|boolean',
        ], [
            'scope.required' => 'اكتب نطاق العمل: ما المطلوب بالتحديد',
            'scope.min' => 'اشرح نطاق العمل بشكل أوضح (20 حرفًا على الأقل)',
            'stages.required' => 'أضف مرحلة واحدة على الأقل',
            'stages.max' => 'الحد الأقصى ' . \App\Services\Contracts\ContractPolicy::number('max_stages') . ' مراحل',
            'stages.*.title.required' => 'اكتب عنوان كل مرحلة',
            'stages.*.deliverable.required' => 'اكتب ما سيُسلَّم في كل مرحلة',
            'stages.*.deliverable.min' => 'وضّح ما سيُسلَّم في المرحلة',
            'stages.*.duration_days.required' => 'حدد مدة كل مرحلة بالأيام',
            'stages.*.duration_days.min' => 'مدة المرحلة يوم واحد على الأقل',
            'stages.*.amount.required' => 'حدد مبلغ كل مرحلة',
            'stages.*.amount.min' => 'مبلغ المرحلة 1 ج.م على الأقل',
            'stages.*.revisions.max' => 'عدد التعديلات 20 كحد أقصى (أو اختر «غير محدود»)',
        ]);

        try {
            $this->agreements->propose($contract, Auth::user(), $data);
        } catch (RuntimeException $e) {
            return back()->withInput()->withErrors($e->getMessage());
        }

        return redirect()->route('contracts.show', $contract)->with('Success_message', 'أُرسل الاتفاق للعميل ليراجعه ويوافق أو يطلب تعديلًا.');
    }

    public function respond(Request $request, Contract $contract, ContractAgreement $agreement)
    {
        abort_unless((int) Auth::id() === (int) $contract->client_id && (int) $agreement->contract_id === (int) $contract->id, 403);
        $data = $request->validate([
            'decision' => 'required|in:approve,changes',
            'note' => 'required_if:decision,changes|nullable|string|max:3000',
            'confirm' => 'required_if:decision,approve|accepted_if:decision,approve',
        ], [
            'note.required_if' => 'اكتب التعديلات التي تريدها',
            'confirm.required_if' => 'أكّد أنك راجعت الاتفاق ومراحله',
            'confirm.accepted_if' => 'أكّد أنك راجعت الاتفاق ومراحله',
        ]);

        try {
            if ($data['decision'] === 'approve') {
                $this->agreements->approve($agreement, Auth::user(), $request->ip());
                $message = 'تمت الموافقة على الاتفاق. الخطوة التالية: تمويل المرحلة الأولى، ويبقى المبلغ لدى نفّذها حتى تعتمدها.';
            } else {
                $this->agreements->requestChanges($agreement, Auth::user(), $data['note']);
                $message = 'أُرسل طلب التعديل للمستقل، وسيصلك اتفاق معدّل.';
            }
        } catch (RuntimeException $e) {
            return back()->withErrors($e->getMessage());
        }

        return redirect()->route('contracts.show', $contract)->with('Success_message', $message);
    }
}
