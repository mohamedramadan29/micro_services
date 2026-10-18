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
    /** Status 0 = pending, 1 = paid out, 2 = rejected. A rejection (or deleting a pending/rejected row) gives the money back. */
    public function update(Request $request, $id){
        $withdraw = WithDraw::findOrFail($id);
        if($request->isMethod('post')){
            $request->validate([
                'status' => 'required|in:0,1,2',
            ]);
            $this->transition($withdraw, (int) $request->status);
            return $this->success_message(' تم تعديل حالة السحب بنجاح  ');
        }
        return view('admin.withdraw.edit',compact('withdraw'));
    }

    public function delete($id){
        $withdraw = WithDraw::findOrFail($id);
        \Illuminate\Support\Facades\DB::transaction(function () use ($withdraw) {
            $withdraw = WithDraw::whereKey($withdraw->id)->lockForUpdate()->first();
            if ((int) $withdraw->status === 0) {
                $this->giveBack($withdraw, 'حُذف طلب السحب وأُعيد المبلغ إلى رصيدك');
            }
            $withdraw->delete();
        });
        return $this->success_message(' تم حذف الطلب بنجاح  ');
    }

    private function transition(WithDraw $withdraw, int $status): void
    {
        \Illuminate\Support\Facades\DB::transaction(function () use ($withdraw, $status) {
            $withdraw = WithDraw::whereKey($withdraw->id)->lockForUpdate()->first();
            $from = (int) $withdraw->status;
            if ($from === $status) {
                return;
            }
            // Money was deducted when the request was made: rejecting returns it; re-opening a rejection takes it again.
            if ($status === 2 && $from !== 2) {
                $this->giveBack($withdraw, 'رُفض طلب السحب وأُعيد المبلغ إلى رصيدك');
            } elseif ($from === 2) {
                $user = \App\Models\User::whereKey($withdraw->user_id)->lockForUpdate()->first();
                $user?->update(['balance' => round((float) $user->balance - (float) $withdraw->amount, 2)]);
                \App\Models\WalletTransaction::create(['user_id' => $withdraw->user_id, 'type' => 'withdraw', 'amount' => (float) $withdraw->amount, 'description' => 'أُعيد فتح طلب السحب وخُصم المبلغ من رصيدك']);
            }
            $withdraw->update(['status' => $status]);
        });
    }

    private function giveBack(WithDraw $withdraw, string $why): void
    {
        $user = \App\Models\User::whereKey($withdraw->user_id)->lockForUpdate()->first();
        if ($user) {
            $user->update(['balance' => round((float) $user->balance + (float) $withdraw->amount, 2)]);
            \App\Models\WalletTransaction::create(['user_id' => $user->id, 'type' => \App\Models\WalletTransaction::TYPE_REFUND, 'amount' => (float) $withdraw->amount, 'description' => $why . ' (' . \App\Support\Money::format($withdraw->amount) . ')']);
        }
    }
}
