<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Http\Traits\Message_Trait;
use App\Models\front\WithDraw;
use Illuminate\Http\Request;

class WithdrawRequestController extends Controller
{
    use Message_Trait;
    public function index(){
        $withdraws = WithDraw::latest()->get();
        return view('admin.withdraw.index',compact('withdraws'));
    }
    public function update(Request $request, $id){
        $withdraw = WithDraw::findOrFail($id);
        if($request->isMethod('post')){
            $request->validate([
                'status' => 'required',
            ]);
            $withdraw->update([
                'status' => $request->status,
            ]);
            return $this->success_message(' تم تعديل حالة السحب بنجاح  ');
        }
        return view('admin.withdraw.edit',compact('withdraw'));
    }

    public function delete($id){
        $withdraw = WithDraw::findOrFail($id);
        if(!$withdraw){
            abort(404);
        }
        $withdraw->delete();
        return $this->success_message(' تم حذف الطلب بنجاح  ');
    }
}
