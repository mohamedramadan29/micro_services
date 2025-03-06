<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Traits\Message_Trait;
use App\Models\front\ProperityMaintain;

class ProperityMaintainController extends Controller
{
    use Message_Trait;
    public function index()
    {
        $properities = ProperityMaintain::all();
        return view('admin.properity-maintain.index', compact('properities'));
    }

    public function update(Request $request, $id)
    {
        $properity = ProperityMaintain::find($id);
        if ($request->isMethod('post')) {
            $data = $request->all();
            dd($data);
        }
        return view('admin.properity-maintain.update', compact('properity'));
    }
    public function delete($id)
    {
        $properity = ProperityMaintain::find($id);

        if (!$properity) {
            abort('404');
        }
        $properity->delete();
        return $this->success_message(' تم الحذف بنجاح  ');
    }
    public function ActiveStatus(Request $request, $id)
    {
        $properity = ProperityMaintain::find($id);
        if ($request->isMethod('post')) {
            $properity->active = 1;
            $properity->save();
            return $this->success_message('تم تفعيل الخدمة ');
        }
    }
}
