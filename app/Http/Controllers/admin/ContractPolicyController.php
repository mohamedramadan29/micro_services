<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Services\Contracts\ContractPolicy;
use App\Support\StaffPermissions;
use Illuminate\Http\Request;

/** Service 3 «سياسة المشاريع»: deadlines, limits, dispute reasons and stage templates (manager only). */
class ContractPolicyController extends Controller
{
    public function index(Request $request)
    {
        abort_unless(StaffPermissions::isManager($request->user()), 403, 'سياسة المشاريع للمدير فقط');

        return view('admin.contracts.policy', [
            'numbers' => collect(ContractPolicy::NUMBERS)->map(fn ($d, $key) => $d + ['value' => ContractPolicy::number($key)]),
            'reasons' => ContractPolicy::disputeReasons(),
            'templates' => ContractPolicy::templates(),
            'history' => \App\Models\ContractPolicyChange::with('user')->latest('id')->limit(15)->get(),
        ]);
    }

    public function save(Request $request)
    {
        abort_unless(StaffPermissions::isManager($request->user()), 403);
        $rules = [];
        foreach (ContractPolicy::NUMBERS as $key => $definition) {
            $rules['numbers.' . $key] = 'required|integer|min:' . $definition['min'] . '|max:' . $definition['max'];
        }
        $data = $request->validate($rules + [
            'reasons' => 'array|max:15',
            'reasons.*.key' => 'nullable|string|max:40',
            'reasons.*.label' => 'nullable|string|max:150',
            'templates' => 'array|max:8',
            'templates.*.name' => 'nullable|string|max:100',
            'templates.*.stages' => 'nullable|string|max:3000',
        ], ['numbers.*.required' => 'كل القيم مطلوبة', 'numbers.*.min' => 'قيمة أقل من المسموح', 'numbers.*.max' => 'قيمة أكبر من المسموح']);

        // Dispute reasons: existing keys stay (old disputes keep their label), new ones get a key.
        $reasons = [];
        foreach ($data['reasons'] ?? [] as $index => $row) {
            $label = trim((string) ($row['label'] ?? ''));
            if ($label === '' || $request->boolean("reasons.$index.delete")) {
                continue;
            }
            $key = preg_match('/^[a-z0-9_]{1,40}$/', (string) ($row['key'] ?? '')) ? $row['key'] : 'r' . substr(md5($label . $index . microtime()), 0, 8);
            $reasons[$key] = $label;
        }
        if (count($reasons) < 2) {
            return back()->withInput()->withErrors('اترك سببين للخلاف على الأقل.');
        }

        // Templates: one stage per line «العنوان | ما يُسلَّم | النسبة %».
        $templates = [];
        foreach ($data['templates'] ?? [] as $row) {
            $name = trim((string) ($row['name'] ?? ''));
            if ($name === '') {
                continue;
            }
            $stages = [];
            foreach (preg_split('/\r?\n/', (string) ($row['stages'] ?? '')) as $line) {
                if (trim($line) === '') {
                    continue;
                }
                $parts = array_map('trim', explode('|', $line));
                $share = (float) str_replace('%', '', $parts[2] ?? '0');
                if (($parts[0] ?? '') === '' || $share <= 0) {
                    return back()->withInput()->withErrors('في قالب «' . $name . '» اكتب كل مرحلة في سطر: العنوان | ما يُسلَّم | النسبة (رقم أكبر من صفر).');
                }
                $stages[] = ['title' => mb_substr($parts[0], 0, 150), 'deliverable' => mb_substr($parts[1] ?? '', 0, 500), 'share' => $share, 'acceptance' => mb_substr($parts[3] ?? '', 0, 500)];
            }
            if (! $stages || count($stages) > ContractPolicy::number('max_stages')) {
                return back()->withInput()->withErrors('قالب «' . $name . '» يحتاج من مرحلة إلى ' . ContractPolicy::number('max_stages') . ' مراحل.');
            }
            if (abs(array_sum(array_column($stages, 'share')) - 100) > 0.01) {
                return back()->withInput()->withErrors('نِسب مراحل قالب «' . $name . '» يجب أن يكون مجموعها 100%.');
            }
            $templates[] = ['name' => $name, 'stages' => $stages];
        }
        if (! $templates) {
            return back()->withInput()->withErrors('اترك قالبًا واحدًا على الأقل.');
        }

        ContractPolicy::save(['dispute_reasons' => $reasons, 'stage_templates' => $templates] + array_map('intval', $data['numbers']));

        return back()->with('success', 'تم حفظ سياسة المشاريع');
    }
}
