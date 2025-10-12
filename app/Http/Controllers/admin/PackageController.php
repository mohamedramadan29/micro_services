<?php

namespace App\Http\Controllers\admin;

use App\Http\Traits\Message_Trait;
use App\Http\Traits\Slug_Trait;
use Illuminate\Http\Request;
use App\Models\admin\Package;
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
        return view("admin.package.create");
    }

    public function store(Request $request)
    {
        $data = $request->all();
        $rules = [
            'title' => 'required',
            'description' => 'required',
            'price' => 'required',
        ];
        $messages = [
            'title.required' => ' من فضلك ادخل العنوان',
            'description.required' => ' من فضلك ادخل محتوي الباقة',
            'price.required' => ' من فضلك ادخل سعر الباقة',
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
        $package->save();
        return $this->success_message(' تم اضافة الباقة بنجاح  ');
    }

    public function edit($id)
    {
        $package = Package::findOrFail($id);
        return view('admin.package.edit', compact('package'));
    }

    public function update(Request $request, $id)
    {
        $data = $request->all();
        $rules = [
            'title' => 'required',
            'description' => 'required',
            'price' => 'required',
        ];
        $messages = [
            'title.required' => ' من فضلك ادخل العنوان  ',
            'description.required' => ' من فضلك ادخل محتوي الباقة  ',
            'price.required' => ' من فضلك ادخل سعر الباقة  ',
        ];
        $validator = Validator::make($data, $rules, $messages);
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }
        $package = Package::findOrFail($id);
        $package->name = $data['title'];
        $package->description = $data['description'];
        $package->price = $data['price'];
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
