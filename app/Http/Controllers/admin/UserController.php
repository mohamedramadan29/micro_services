<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Http\Traits\Message_Trait;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    use Message_Trait;
    public function index()
    {
        $users = User::orderBy("id", "desc")->paginate(10);
        return view('admin.users.index', compact('users'));
    }

    public function blockStatus(Request $request, $id)
    {
        $user = User::findOrFail(($id));
        $user->block_status = $user->block_status == 1 ? 0 : 1;
        $user->save();

        return $this->success_message(' تم تعديل حالة المستخدم بنجاح  ');
    }
}