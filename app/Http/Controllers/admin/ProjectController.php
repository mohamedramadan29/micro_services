<?php

namespace App\Http\Controllers\admin;

use Illuminate\Http\Request;
use App\Models\front\Project;
use App\Http\Traits\Message_Trait;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Notification;
use App\Notifications\AcceptProjectFromAdmin;

class ProjectController extends Controller
{
    use Message_Trait;

    public function index()
    {
        $projects = Project::with('User')->orderBy('created_at', 'desc')->get();
        return view('admin.projects.index', compact('projects'));
    }

    public function update(Request $request, $id)
    {
        $project = Project::find($id);
        if ($project) {
            if ($request->isMethod('post')) {
                $data = $request->all();
                dd($data);
            }
        }
        return view('admin.projects.edit', compact('project'));
    }
    public function update_status(Request $request, $id)
    {
        DB::beginTransaction();
        $project = Project::find($id);
        $user = User::find($project->user_id);
        $project->approved = 1;
        $project->save();
        ////////////// Send Notofication Mails And Db To Users
        Notification::send($user, new AcceptProjectFromAdmin($user->id,$project->id, $project->slug, $project->title));
        DB::commit();
        return $this->success_message(' تم تفعيل المشروع وظهورة علي الموقع ');
    }

}
