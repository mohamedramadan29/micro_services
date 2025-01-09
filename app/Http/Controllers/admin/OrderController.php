<?php

namespace App\Http\Controllers\admin;

use App\Http\Traits\Message_Trait;
use Illuminate\Http\Request;
use App\Models\front\ProductOrder;
use App\Http\Controllers\Controller;

class OrderController extends Controller
{
    use Message_Trait;
    public function index()
    {
        $orders = ProductOrder::orderBy('id','DESC')->get();
        return view('admin.orders.index',compact('orders'));
    }

    public function update(Request $request,$id)
    {
        $order = ProductOrder::find($id);
        if($request->isMethod('post')){
            $data = $request->all();
            $status = $data['status_value'];
            $order->update([
                'order_status'=>$status
            ]);
            return  $this->success_message('تم تحديث حالة الطلب بنجاح ');
        }
        return view('admin.orders.update',compact('order'));
    }

    public function delete($id)
    {

        $order = ProductOrder::find($id);
        if(!$order){
            abort(404);
        }
        $order->delete();
        return  $this->success_message('تم حذف الطلب بنجاح ');
    }

}
