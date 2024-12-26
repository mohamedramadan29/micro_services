<?php

namespace App\Http\Controllers\admin;

use App\Models\User;
use Illuminate\Http\Request;
use App\Models\admin\Category;
use App\Models\admin\Consultant;
use App\Http\Traits\Message_Trait;
use App\Http\Traits\Upload_Images;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class ConsultController extends Controller
{
    use Message_Trait;
    use Upload_Images;
    public function index()
    {
        $consults = Consultant::with('category')->get();
        return view('admin.consultants.index', compact('consults'));
    }

    public function store(Request $request)
    {
        if ($request->isMethod('post')) {
            try {
                $data = $request->all();
                // dd($data);
                $rules = [
                    'name' => 'required',
                    'email' => 'required|email|unique:consultants',
                    'specialization' => 'required',
                    'bio' => 'required',
                    'image' => 'required|image',
                    'price' => 'required|numeric',
                    'is_active' => 'required',
                    // 'aviable_date' => 'required',
                    // 'aviable_time' => 'required',
                ];
                $messages = [
                    'name.required' => ' من فضلك ادخل الاسم',
                    'email.required' => ' من فضلك ادخل البريد الالكتروني',
                    'email.email' => ' البريد الالكتروني غير صحيح',
                    'email.unique' => ' البريد الالكتروني موجود بالفعل',
                    'specialization.required' => ' التخصص مطلوب',
                    'bio.required' => '  السيرة الذاتية مطلوبة',
                    'image.required' => ' من فضلك ادخل الصورة',
                    'image.image' => ' الصورة يجب ان تكون صورة',
                    'price.required' => '  من فضلك ادخل السعر',
                    'price.numeric' => ' السعر يجب ان يكون رقم',
                    'is_active.required' => ' من فضلك اختر حالة الاستشاري',
                    // 'aviable_date.required' => 'Aviable date is required',
                    // 'aviable_time.required' => 'Aviable time is required',
                ];
                $validator = Validator::make($data, $rules, $messages);
                if ($validator->fails()) {
                    return redirect()->back()->withInput()->withErrors($validator);
                }

                if ($request->hasFile('image')) {
                    $filename = $this->saveImage($request->image, public_path('assets/uploads/consultants'));
                }
                DB::beginTransaction();
                $consultant = new Consultant();
                $consultant->name = $request->name;
                $consultant->email = $request->email;
                $consultant->specialization = $request->specialization;
                $consultant->bio = $request->bio;
                $consultant->price = $request->price;
                $consultant->image = $filename;
                $consultant->is_active = $request->is_active;
                // $consultant->aviable_date = $request->aviable_date;
                // $consultant->aviable_time = $request->aviable_time;
                $consultant->save();
                ///////////// Insert Consultant As USer
                $user = new User();
                $user->name = $request->name;
                $user->email = $request->email;
                $user->password = bcrypt('password');
                $user->user_name =  $request->name. 'consultant';
                $user->info = $request->bio;
                $user->image = $filename;
                $user->account_type = 'consultant';
                $user->save();
                DB::commit();
                return $this->success_message(' تم اضافة الاستشاري بنجاح');

            } catch (\Exception $e) {
                return $this->exception_message($e);
            }
        }
        $categories = Category::all();
        return view('admin.consultants.add', compact('categories'));
    }

    public function update(Request $request, $id)
    {
        $consultant = Consultant::find($id);
        $categories = Category::all();
        if ($request->isMethod('post')) {
            try {
                $data = $request->all();
                $rules = [
                    'name' => 'required',
                    'email' => 'required|email|unique:consultants,email,' . $consultant->id,
                    'specialization' => 'required',
                    'bio' => 'required',
                    'price' => 'required|numeric',
                    'is_active' => 'required'
                    // 'aviable_date' => 'required',
                    // 'aviable_time' => 'required',
                ];
                $messages = [
                    'name.required' => ' من فضلك ادخل الاسم',
                    'email.required' => ' من فضلك ادخل البريد الالكتروني',
                    'email.email' => ' البريد الالكتروني غير صحيح',
                    'email.unique' => ' البريد الالكتروني موجود بالفعل',
                    'specialization.required' => ' التخصص مطلوب',
                    'bio.required' => '  السيرة الذاتية مطلوبة',
                    'price.required' => '  من فضلك ادخل السعر',
                    'price.numeric' => ' السعر يجب ان يكون رقم',
                    'is_active.required' => ' من فضلك اختر حالة الاستشاري',
                    // 'aviable_date.required' => 'Aviable date is required',
                    // 'aviable_time.required' => 'Aviable time is required',
                ];
                $validator = Validator::make($data, $rules, $messages);
                if ($validator->fails()) {
                    return redirect()->back()->withErrors($validator)->withInput();
                }

                if ($request->hasFile('image')) {
                    ////////// Delete Old Image //////////////
                    if (file_exists(public_path('assets/uploads/consultants/' . $consultant->image))) {
                        unlink(public_path('assets/uploads/consultants/' . $consultant->image));
                    }
                    $filename = $this->saveImage($request->image, public_path('assets/uploads/consultants'));
                    $consultant->image = $filename;
                }
                $consultant->name = $request->name;
                $consultant->email = $request->email;
                $consultant->specialization = $request->specialization;
                $consultant->bio = $request->bio;
                $consultant->price = $request->price;
                $consultant->is_active = $request->is_active;
                // $consultant->aviable_date = $request->aviable
                $consultant->save();
                return $this->success_message('تم تعديل الاستشاري بنجاح');
            } catch (\Exception $e) {
                return $this->exception_message($e);
            }
        }

        return view('admin.consultants.edit', compact('consultant', 'categories'));

    }

    public function delete($id)
    {
        try {
            $consultant = Consultant::find($id);
            $consultant->delete();
            return $this->success_message('تم حذف الاستشاري بنجاح');
        } catch (\Exception $e) {
            return $this->exception_message($e);
        }
    }

}
