<?php

namespace App\Http\Controllers\admin;

use Illuminate\Http\Request;
use App\Models\admin\Package;
use App\Http\Traits\Slug_Trait;
use App\Http\Traits\Message_Trait;
use App\Models\admin\PackageTitle;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class PackageController extends Controller
{
    use Message_Trait;
    use Slug_Trait;
    public function index()
    {
        $packages = Package::all();
        return view("admin.package.index", compact("packages"));
    }

    public function create()
    {
        $titles = PackageTitle::all();
        return view("admin.package.create",compact('titles'));
    }

    public function store(Request $request)
    {
        $data = $request->all();
        $rules = [
            'title' => 'required',
            'description' => 'required',
            'price' => 'required',
            'category'=>'required'
        ];
        $messages = [
            'title.required' => ' من فضلك ادخل العنوان',
            'description.required' => ' من فضلك ادخل محتوي الباقة',
            'price.required' => ' من فضلك ادخل سعر الباقة',
            'category.required'=>' من فضلك حدد قسم الباقة  ',
        ];
        $validator = Validator::make($data, $rules, $messages);
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }
        $package = new Package();
        $package->name = $data['title'];
        $package->slug = $this->CustomeSlug($data['title']);
        $package->description = $data['description'];
        $package->price = $data['price'];
        $package->title = $data['category'];
        $package->save();
        return $this->success_message(' تم اضافة الباقة بنجاح  ');
    }

    public function edit($id)
    {
        $package = Package::findOrFail($id);
           $titles = PackageTitle::all();
        return view('admin.package.edit', compact('package','titles'));
    }

    public function update(Request $request, $id)
    {
        $data = $request->all();
        $rules = [
            'title' => 'required',
            'description' => 'required',
            'price' => 'required',
            'category'=>'required'
        ];
        $messages = [
            'title.required' => ' من فضلك ادخل العنوان  ',
            'description.required' => ' من فضلك ادخل محتوي الباقة  ',
            'price.required' => ' من فضلك ادخل سعر الباقة  ',
            'category.required'=>' من فضلك حدد قسم الباقة  ',
        ];
        $validator = Validator::make($data, $rules, $messages);
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }
        $package = Package::findOrFail($id);
        $package->name = $data['title'];
        $package->description = $data['description'];
        $package->price = $data['price'];
        $package->title = $data['category'];
        $package->save();
        return $this->success_message(' تم تعديل الباقة بنجاح  ');
    }
    public function delete($id)
    {
        $package = Package::findOrFail($id);
        $package->delete();
        return $this->success_message(' تم حذف الباقة بنجاح  ');
    }

    public function showSubscribe($id){
        $package = Package::findOrFail($id);
        $subscribes = $package->subscribes()->orderBy('id','desc')->get();
        return view('admin.package.subscribes', compact('subscribes','package'));
    }


}
