<?php

namespace App\Http\Controllers\front;

use App\Http\Controllers\Controller;
use App\Http\Traits\Message_Trait;
use App\Http\Traits\Slug_Trait;
use App\Http\Traits\Upload_Images;
use App\Models\front\Project;
use App\Models\front\ProjectFiles;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;

class ProjectController extends Controller
{
    use Message_Trait;
    use Upload_Images;
    use Slug_Trait;

    public function index()
    {
        $projects = Project::where('user_id',Auth::id())->get();

        return view('website.projects.index',compact('projects'));
    }

    public function store(Request $request)
    {

        if ($request->isMethod('post')){
            try {
                $data = $request->all();
                $rules = [
                    'title'=>'required',
                    'description'=>'required|min:50',
                    'price'=>'required',
                    'days'=>'required',
                    'skills'=>'required',
                ];
                $messages = [
                    'title.required'=>' من فضلك ادخل عنوان المشروع  ',
                    'description.required'=>' من فضلك ادخل وصف كاملا للمشروع  ',
                    'description.min'=>' اقل وصف للمشروع هو 50 حرف  ' ,
                    'days.required'=>' من فضلك حدد المدة المتوقعه للتسليم  ' ,
                    'skills.required'=>' من فضلك حدد مهارات المشروع  ' ,
                ];
                $validator = Validator::make($data,$rules,$messages);
                if ($validator->fails()){
                    return Redirect::back()->withInput()->withErrors($validator);
                }

                DB::beginTransaction();
                $project = new Project();
                $project->user_id = Auth::id();
                $project->title = $data['title'];
                $project->slug = $this->CustomeSlug($data['title']);
                $project->desc = $data['description'];
                $project->price = $data['price'];
                $project->skills = implode(',',$data['skills']);
                $project->day_number = $data['days'];
                $project->status = 'مفتوح' ;
                $project->approved = 0;
                $project->save();
                if ($request->has('files')){
                    foreach ($request->file('files') as $file){
                        $file_name = $this->saveImage($file,public_path('assets/uploads/project_files'));
                        $project_file = new ProjectFiles();
                        $project_file->user_id = Auth::id();
                        $project_file->file = $file_name;
                        $project_file->project_id = $project->id;
                        $project_file->save();
                    }
                }
                DB::commit();
                return $this->success_message(' تم اضافة المشروع بنجاح من فضلك انتظر التفعيل من الادارة  ');

            }catch (\Exception $e){
                return $this->exception_message($e);
            }
        }

        return view('website.projects.add');

    }
}
