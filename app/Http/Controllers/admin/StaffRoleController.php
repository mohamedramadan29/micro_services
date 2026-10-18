<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\admin\Setting;
use App\Models\User;
use App\Support\StaffPermissions;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** Staff roles for service 3 decisions and the approval threshold (manager only). */
class StaffRoleController extends Controller
{
    public function index(Request $request)
    {
        abort_unless(StaffPermissions::isManager($request->user()), 403, 'إدارة الأدوار للمدير فقط');

        return view('admin.staff.index', [
            'admins' => User::where('account_type', User::TYPE_ADMIN)->where('status', 1)->orderBy('name')->get(),
            'threshold' => StaffPermissions::threshold(),
        ]);
    }

    public function update(Request $request)
    {
        abort_unless(StaffPermissions::isManager($request->user()), 403);
        $data = $request->validate([
            'roles' => 'array',
            'roles.*' => ['required', Rule::in(array_keys(StaffPermissions::ROLES))],
            'threshold' => 'required|numeric|min:0|max:100000000',
        ], ['threshold.required' => 'اكتب حد الموافقة (0 = بلا حد)']);

        $admins = User::where('account_type', User::TYPE_ADMIN)->where('status', 1)->get()->keyBy('id');
        $roles = collect($data['roles'] ?? [])->only($admins->keys()->all());
        // Someone must stay a manager.
        $managers = $admins->keys()->filter(fn ($id) => ($roles[$id] ?? StaffPermissions::role($admins[$id])) === 'manager');
        if ($managers->isEmpty()) {
            return back()->withErrors('يجب أن يبقى مدير واحد على الأقل');
        }
        foreach ($roles as $id => $role) {
            $admins[$id]->forceFill(['admin_role' => $role])->save();
        }
        (Setting::first() ?? Setting::create([]))->update(['contract_approval_threshold' => $data['threshold']]);

        return back()->with('success', 'حُفظت الأدوار وحد الموافقة.');
    }
}
