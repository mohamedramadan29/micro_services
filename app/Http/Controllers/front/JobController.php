<?php

namespace App\Http\Controllers\front;

use App\Http\Traits\Slug_Trait;
use App\Models\front\Jobs;
use Illuminate\Http\Request;
use App\Http\Traits\Message_Trait;
use App\Http\Controllers\Controller;
use App\Models\admin\SubCategory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class JobController extends Controller
{
    use Message_Trait;
    use Slug_Trait;
    public function index()
    {
        $jobs = Jobs::where('user_id', Auth::user()->id)->orderBy("id", "desc")->get();
        return view('website.jobs.index', compact("jobs"));
    }

    public function store(Request $request)
    {
        if ($request->isMethod("post")) {
            $data = $request->all();
            // dd($data);
            $rules = [
                'category_id' => 'required',
                'title' => 'required|string',
                'description' => 'required|string',
                'city' => 'required|string',
                'country' => 'required|string',
                'address' => 'required|string',
                'sex' => 'required'
            ];
            $messages = [
                'category_id.required' => 'يرجى اختيار القسم',
                'title.required' => 'يرجى ادخال العنوان',
                'title.string' => 'يرجى ادخال العنوان بشكل صحيح',
                'description.required' => ' يرجي ادخال الوصف الوظيفي',
                'description.string' => 'يرجى ادخال الوصف بشكل صحيح',
                'city.required' => 'يرجى ادخال المدينة',
                'city.string' => 'يرجى ادخال المدينة بشكل صحيح',
                'country.required' => 'يرجى ادخال الدولة',
                'country.string' => 'يرجى ادخال الدولة بشكل صحيح',
                'address.required' => 'يرجى ادخال العنوان',
                'address.string' => 'يرجى ادخال العنوان بشكل صحيح',
                'sex.required' => 'يرجى ادخال النوع',
            ];
            $validator = Validator::make($data, $rules, $messages);
            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator)->withInput();
            }
            $job = new Jobs();
            $job->user_id = Auth::user()->id;
            $job->category_id = $data['category_id'];
            $job->title = $data['title'];
            $job->slug = $this->CustomeSlug($data['title']);
            $job->description = $data['description'];
            $job->experience = $data['experience'];
            $job->city = $data['city'];
            $job->country = $data['country'];
            $job->address = $data['address'];
            $job->salary = $data['salary'];
            $job->sex = $data['sex'];
            $job->save();
            return $this->success_message(' تم اضافة الاعلان الوظيفي بنجاح  ');
        }

        $categories = SubCategory::all();

        return view('website.jobs.store', compact('categories'));
    }

    public function update(Request $request, $id)
    {
        $job = Jobs::find($id);
        if (!$job) {
            abort(404);
        }
        if ($job->user_id != Auth::user()->id) {
            abort(404);
        }
        if ($request->isMethod('post')) {
            $data = $request->all();
            $rules = [
                'category_id' => 'required',
                'title' => 'required|string',
                'description' => 'required|string',
                'city' => 'required|string',
                'country' => 'required|string',
                'address' => 'required|string',
                'sex' => 'required'
            ];
            $messages = [
                'category_id.required' => 'يرجى اختيار القسم',
                'title.required' => 'يرجى ادخال العنوان',
                'title.string' => 'يرجى ادخال العنوان بشكل صحيح',
                'description.required' => ' يرجي ادخال الوصف الوظيفي',
                'description.string' => 'يرجى ادخال الوصف بشكل صحيح',
                'city.required' => 'يرجى ادخال المدينة',
                'city.string' => 'يرجى ادخال المدينة بشكل صحيح',
                'country.required' => 'يرجى ادخال الدولة',
                'country.string' => 'يرجى ادخال الدولة بشكل صحيح',
                'address.required' => 'يرجى ادخال العنوان',
                'address.string' => 'يرجى ادخال العنوان بشكل صحيح',
                'sex.required' => 'يرجى ادخال النوع',
            ];
            $validator = Validator::make($data, $rules, $messages);
            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator)->withInput();
            }
            $job->update([
                "category_id" => $data['category_id'],
                "title" => $data['title'],
                "slug" => $this->CustomeSlug($data['title']),
                "description" => $data['description'],
                'experience' => $data['experience'],
                "city" => $data['city'],
                "country" => $data['country'],
                "address" => $data['address'],
                "salary" => $data['salary'],
                "sex" => $data['sex'],
            ]);
            return $this->success_message(' تم تعديل الاعلان الوظيفي بنجاح  ');
        }

        $categories = SubCategory::all();
        return view('website.jobs.update', compact('job', 'categories'));
    }

    public function delete($id)
    {
        $job = Jobs::find($id);
        if (!$job) {
            abort(404);
        }
        if ($job->user_id != Auth::user()->id) {
            abort(404);
        }
        $job->delete();
        return $this->success_message(' تم حذف الاعلان الوظيفي بنجاح  ');
    }

    public function subscriptions(){
        return view('website.jobs.subscription');
    }

}
