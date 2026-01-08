<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Http\Traits\Message_Trait;
use App\Models\admin\PackageTitle;
use Illuminate\Http\Request;

class PackagetitleController extends Controller
{
    use Message_Trait;
    public function index()
    {
        $titles = PackageTitle::latest()->get();
        return view('admin.package-titles.index', compact('titles'));
    }

    public function create(Request $request)
    {
        if ($request->isMethod('POST')) {
            $packageTitle = new PackageTitle();
            $packageTitle->title = $request->title;
            $packageTitle->save();
            return $this->success_message('تم اضافة العنوان بنجاح');
        }
    }

    public function update(Request $request)
    {
        if ($request->isMethod('POST')) {
            $title = PackageTitle::find($request->id);
            $title->title = $request->title;
            $title->save();
            return $this->success_message('تم تعديل العنوان بنجاح');
        }
    }

    public function delete($id)
    {
        $title = PackageTitle::find($id);
        $title->delete();
        return $this->success_message('تم حذف العنوان بنجاح');
    }
}
