<?php

namespace App\Http\Controllers\admin;

use Illuminate\Http\Request;
use App\Models\front\Project;
use App\Http\Traits\Message_Trait;
use App\Http\Controllers\Controller;

class ProjectController extends Controller
{
    use Message_Trait;

    public function index()
    {
        $projects = Project::with('User')->orderBy('created_at','desc')->get();
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
        $project = Project::find($id);
        $project->approved = 1;
        $project->save();
        return $this->success_message(' تم تفعيل المشروع وظهورة علي الموقع ');
    }

}
