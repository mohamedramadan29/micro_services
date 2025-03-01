<?php

namespace App\Http\Controllers\admin;

use Illuminate\Http\Request;
use App\Models\front\Properity;
use App\Http\Controllers\Controller;
use App\Http\Traits\Message_Trait;

class ProperityController extends Controller
{
    use Message_Trait;

    public function index()
    {
        $properities = Properity::all();
        return view('admin.properities.index', compact('properities'));
    }

    public function update(Request $request, $id)
    {
        $properity = Properity::find($id);
        if ($request->isMethod('post')) {
            $data = $request->all();
            dd($data);
        }
        return view('admin.properities.update', compact('properity'));
    }
    public function delete($id)
    {
        $properity = Properity::find($id);

        if (!$properity) {
            abort('404');
        }
        $properity->delete();
        return $this->success_message(' تم الحذف بنجاح  ');
    }

    public function ActiveStatus(Request $request, $id)
    {
        $properity = Properity::find($id);

        if ($request->isMethod('post')) {
             $properity->active = 1;
             $properity->save();
            return $this->success_message('تم تفعيل العقار ');
        }
    }
}
