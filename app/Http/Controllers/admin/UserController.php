<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Http\Traits\Message_Trait;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class UserController extends Controller
{
    use Message_Trait;

    /** Type tabs: key => [label, stored account_type (null = no type chosen yet, '' = all)]. */
    public const TABS = [
        'all' => ['الكل', ''],
        'clients' => ['العملاء', User::TYPE_CLIENT],
        'freelancers' => ['المستقلون', User::TYPE_FREELANCER],
        'employees' => ['الموظفون', User::TYPE_EMPLOYEE],
        'admins' => ['الإدارة', User::TYPE_ADMIN],
        'untyped' => ['لم يختاروا نوعًا', null],
    ];

    public const STATES = [
        'active' => 'نشط',
        'inactive' => 'موقوف',
        'blocked' => 'محظور',
        'unverified' => 'بريد غير مؤكد',
    ];

    public const SORTS = [
        'newest' => 'الأحدث تسجيلًا',
        'oldest' => 'الأقدم تسجيلًا',
        'balance' => 'الأعلى رصيدًا',
        'name' => 'الاسم',
    ];

    public function index(Request $request)
    {
        $tab = array_key_exists((string) $request->string('tab'), self::TABS) ? (string) $request->string('tab') : 'all';
        $filters = [
            'q' => trim((string) $request->string('q')),
            'state' => array_key_exists((string) $request->string('state'), self::STATES) ? (string) $request->string('state') : '',
            'sort' => array_key_exists((string) $request->string('sort'), self::SORTS) ? (string) $request->string('sort') : 'newest',
        ];

        $counts = [];
        foreach (self::TABS as $key => [, $type]) {
            $counts[$key] = $this->ofType(User::query(), $key)->count();
        }

        $query = $this->filtered($tab, $filters);

        if ($request->query('export') === 'csv') {
            return $this->export($query);
        }

        $users = $query->paginate(25)->withQueryString();

        return view('admin.users.index', compact('users', 'tab', 'filters', 'counts'));
    }

    public function blockStatus(Request $request, $id)
    {
        $user = User::findOrFail(($id));
        if ($user->id === $request->user()->id) {
            return $this->Error_message('لا يمكنك حظر حسابك');
        }
        $user->block_status = $user->block_status == 1 ? 0 : 1;
        $user->save();

        return $this->success_message($user->block_status == 1 ? 'تم فك حظر المستخدم' : 'تم حظر المستخدم، ولن يستطيع تسجيل الدخول');
    }

    private function filtered(string $tab, array $filters): Builder
    {
        $query = $this->ofType(User::query(), $tab);

        if ($filters['q'] !== '') {
            $term = '%' . str_replace(['%', '_'], ['\%', '\_'], $filters['q']) . '%';
            $query->where(fn ($q) => $q->where('name', 'like', $term)->orWhere('email', 'like', $term)
                ->orWhere('phone', 'like', $term)->orWhere('user_name', 'like', $term)->orWhere('whatsapp', 'like', $term));
        }

        match ($filters['state']) {
            'active' => $query->where('status', 1)->where(fn ($q) => $q->whereNull('block_status')->orWhere('block_status', '!=', 0)),
            'inactive' => $query->where('status', '!=', 1),
            'blocked' => $query->where('block_status', 0),
            'unverified' => $query->whereNull('email_verified_at'),
            default => null,
        };

        return match ($filters['sort']) {
            'oldest' => $query->orderBy('id'),
            'balance' => $query->orderByDesc('balance')->orderByDesc('id'),
            'name' => $query->orderBy('name'),
            default => $query->orderByDesc('id'),
        };
    }

    private function ofType(Builder $query, string $tab): Builder
    {
        $type = self::TABS[$tab][1];

        return match (true) {
            $type === '' => $query,
            $type === null => $query->where(fn ($q) => $q->whereNull('account_type')->orWhere('account_type', '')),
            default => $query->where('account_type', $type),
        };
    }

    private function export(Builder $query)
    {
        $cell = fn ($v) => preg_match('/^[=+\-@]/', (string) $v) ? "'" . $v : (string) $v;

        return response()->streamDownload(function () use ($query, $cell) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, ['#', 'الاسم', 'اسم المستخدم', 'البريد', 'الهاتف', 'واتساب', 'النوع', 'حالة المستقل', 'البريد مؤكد', 'الحالة', 'الرصيد', 'تاريخ التسجيل']);
            $query->chunk(500, function ($users) use ($out, $cell) {
                foreach ($users as $u) {
                    fputcsv($out, array_map($cell, [
                        $u->id, $u->name, $u->user_name, $u->email, $u->phone, $u->whatsapp, $u->typeLabel(),
                        $u->account_type === User::TYPE_FREELANCER ? (User::FREELANCER_STATUSES[$u->freelancer_status] ?? '') : '',
                        $u->email_verified_at ? 'نعم' : 'لا',
                        self::stateOf($u), $u->balance, $u->created_at?->format('Y-m-d H:i'),
                    ]));
                }
            });
            fclose($out);
        }, 'users-' . now()->format('Y-m-d') . '.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public static function stateOf(User $user): string
    {
        return match (true) {
            (string) $user->block_status === '0' => 'محظور',
            (int) $user->status !== 1 => 'موقوف',
            default => 'نشط',
        };
    }
}
