<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Http\Traits\Message_Trait;
use App\Models\admin\SubCategory;
use App\Models\front\Jobs;
use Illuminate\Contracts\Queue\Job;
use Illuminate\Http\Request;

class JobController extends Controller
{
    use Message_Trait;
    public function index(){
        $jobs = Jobs::all();
        return view('admin.jobs.index',compact('jobs'));
    }

    public function update(Request $request, $id)
    {
        $job = Jobs::find($id);
        if ($request->isMethod('post')) {
            $data = $request->all();
            dd($data);
        }
        $categories = SubCategory::all();
        return view('admin.jobs.update', compact('job','categories'));
    }
    public function delete($id)
    {
        $job = jobs::find($id);

        if (!$job) {
            abort('404');
        }
        $job->delete();
        return $this->success_message(' تم الحذف بنجاح  ');
    }

    public function ActiveStatus(Request $request, $id)
    {
        $job = jobs::find($id);

        if ($request->isMethod('post')) {
            $job->status = 1;
            $job->save();
            return $this->success_message('تم تفعيل العقار ');
        }
    }

}
