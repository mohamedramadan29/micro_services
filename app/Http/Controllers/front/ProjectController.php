<?php

namespace App\Http\Controllers\front;

use App\Models\User;
use Illuminate\Http\Request;
use App\Models\front\Project;
use App\Models\admin\Category;
use App\Http\Traits\Slug_Trait;
use App\Models\admin\SubCategory;
use App\Http\Traits\Message_Trait;
use App\Http\Traits\Upload_Images;
use App\Models\front\ProjectFiles;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;

class ProjectController extends Controller
{
    use Message_Trait;
    use Upload_Images;
    use Slug_Trait;

    public function index()
    {
        $projects = Project::where('user_id', Auth::id())->orderBy('created_at', 'desc')->get();

        return view('website.projects.index', compact('projects'));
    }

    public function store(Request $request)
    {
        if ($request->isMethod('post')) {
            try {
                $data = $request->all();
                $rules = [
                    'title' => 'required',
                    'description' => 'required|min:50',
                    'price' => 'required',
                    'days' => 'required',
                    'skills' => 'required',
                ];
                $messages = [
                    'title.required' => ' من فضلك ادخل عنوان المشروع  ',
                    'description.required' => ' من فضلك ادخل وصف كاملا للمشروع  ',
                    'description.min' => ' اقل وصف للمشروع هو 50 حرف  ',
                    'days.required' => ' من فضلك حدد المدة المتوقعه للتسليم  ',
                    'skills.required' => ' من فضلك حدد مهارات المشروع  ',
                ];
                $validator = Validator::make($data, $rules, $messages);
                if ($validator->fails()) {
                    return Redirect::back()->withInput()->withErrors($validator);
                }

                DB::beginTransaction();
                $project = new Project();
                $project->user_id = Auth::id();
                $project->title = $data['title'];
                $project->slug = $this->CustomeSlug($data['title']);
                $project->desc = $data['description'];
                $project->price = $data['price'];
                $project->skills = implode(',', $data['skills']);
                $project->day_number = $data['days'];
                $project->status = 'مفتوح';
                $project->approved = 0;
                $project->save();
                if ($request->has('files')) {
                    foreach ($request->file('files') as $file) {
                        $file_name = $this->saveImage($file, public_path('assets/uploads/project_files'));
                        $project_file = new ProjectFiles();
                        $project_file->user_id = Auth::id();
                        $project_file->file = $file_name;
                        $project_file->project_id = $project->id;
                        $project_file->save();
                    }
                }
                DB::commit();
                return $this->success_message(' تم اضافة المشروع بنجاح من فضلك انتظر التفعيل من الادارة  ');
            } catch (\Exception $e) {
                return $this->exception_message($e);
            }
        }

        $categories = Category::where('status', 1)->get();
        $sub_categories = SubCategory::where('status', 1)->get();
        return view('website.projects.add', compact('categories', 'sub_categories'));
    }

    public function update(Request $request, $id)
    {

        $project = Project::with('files')->where('id', $id)->first();
        $sub_categories = SubCategory::where('status', 1)->get();

        if ($project) {
            if ($project['user_id'] == Auth::id()) {
                if ($request->isMethod('post')) {
                    try {
                        $data = $request->all();
                        $rules = [
                            'title' => 'required',
                            'description' => 'required|min:50',
                            'price' => 'required',
                            'days' => 'required',
                            'skills' => 'required',
                        ];
                        $messages = [
                            'title.required' => ' من فضلك ادخل عنوان المشروع  ',
                            'description.required' => ' من فضلك ادخل وصف كاملا للمشروع  ',
                            'description.min' => ' اقل وصف للمشروع هو 50 حرف  ',
                            'days.required' => ' من فضلك حدد المدة المتوقعه للتسليم  ',
                            'skills.required' => ' من فضلك حدد مهارات المشروع  ',
                        ];
                        $validator = Validator::make($data, $rules, $messages);
                        if ($validator->fails()) {
                            return Redirect::back()->withInput()->withErrors($validator);
                        }

                        DB::beginTransaction();
                        $project->update([
                            'title' => $data['title'],
                            'slug' => $this->CustomeSlug($data['title']),
                            'desc' => $data['description'],
                            'price' => $data['price'],
                            'skills' => implode(',', $data['skills']),
                            'day_number' => $data['days'],
                            'status' => 'مفتوح',
                            'approved' => 0,
                        ]);
                        if ($request->has('files')) {
                            // Delete Old Files
                            $old_files = ProjectFiles::where('project_id', $project['id'])->get();
                            foreach ($old_files as $old_file) {
                                $file = public_path('assets/uploads/project_files/' . $old_file);
                                if (file_exists($file)) {
                                    @unlink($file);
                                }
                                $old_file->delete();
                            }
                            foreach ($request->file('files') as $file) {
                                $file_name = $this->saveImage($file, public_path('assets/uploads/project_files'));
                                $project_file = new ProjectFiles();
                                $project_file->user_id = Auth::id();
                                $project_file->file = $file_name;
                                $project_file->project_id = $project->id;
                                $project_file->save();
                            }
                        }
                        DB::commit();
                        return $this->success_message(' تم تعديل المشروع بنجاح من فضلك انتظر التفعيل من الادارة  ');
                    } catch (\Exception $e) {
                        return $this->exception_message($e);
                    }
                }
            } else {
                abort(404);
            }
            return view('website.projects.update', compact('project', 'sub_categories'));
        }
        abort(404);
    }

    public function delete(Request $request, $id)
    {
        $project = Project::find($id);
        if ($project['user_id'] == Auth::id()) {
            if (!$project)
                abort(404);
            ////// delete Project Files
            $project_file = ProjectFiles::where('project_id', $id)->get();
            foreach ($project_file as $old_file) {
                $file = public_path('assets/uploads/project_files/' . $old_file);
                if (file_exists($file)) {
                    @unlink($file);
                }
                $old_file->delete();
            }
            $project->delete();
            return $this->success_message('تم حذف المشروع بنجاح ');
        }
        abort(404);
    }

    ////////////////////////////////// Start Project In Front ///////////

    public function website_project()
    {
        $projects = Project::with('User')->orderBy('created_at', 'desc')->where('approved', 1)->paginate(10);
        $categories = Category::where('status', 1)->get();

        return view('website.projects', compact('projects', 'categories'));
    }

    public function ProjectDetails($id, $slug)
    {
        $project = Project::with('User', 'Offers.User', 'freelancer')->where('id', $id)->where('slug', $slug)->first();

        //////////////// Mark Project Notifications as Read
        if (Auth::check()) {
            $user = Auth::user();
            if ($user->unreadNotifications->isNotEmpty()) {
                // Filter notifications related to projects only
                $projectNotifications = $user->unreadNotifications->filter(function ($notification) {
                    return $notification['type'] === 'App\Notifications\OfferAccepted';
                });
                // Mark these notifications as read
                foreach ($projectNotifications as $notification) {
                    $notification->markAsRead();
                }
            }
        }
        if ($project) {
            //////////// Make Notification Is Read
            $user = User::find(Auth::id());
            if ($user) {
                $notification_type = 'App\Notifications\AcceptProjectFromAdmin';
                $notifications = $user->unreadNotifications->where('type', $notification_type);
                foreach ($notifications as $notification) {
                    $notification->markAsRead();
                }
                $accepted_type = 'App\Notifications\ProjectDelivery';
                $notifications = $user->unreadNotifications->where('type', $accepted_type);
                foreach ($notifications as $notification) {
                    $notification->markAsRead();
                }
            }

            return view('website.project_details', compact('project'));
        }
        abort(404);
    }
}
