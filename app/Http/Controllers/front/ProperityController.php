<?php

namespace App\Http\Controllers\front;

use Illuminate\Http\Request;
use App\Http\Traits\Slug_Trait;
use App\Models\front\Properity;
use App\Http\Traits\Message_Trait;
use App\Http\Traits\Upload_Images;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\front\ProperityImage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ProperityController extends Controller
{
    use Message_Trait;
    use Slug_Trait;
    use Upload_Images;
    public function index()
    {
        $properties = Properity::with('ProperityFirstImage','ProperityImages')->where('user_id', Auth::user()->id)->get();
        return view('website.properities.index', compact('properties'));
    }

    ############ Start Store Properity ############
    public function store(Request $request)
    {
        if ($request->isMethod('post')) {
            $data = $request->all();
            // dd($data);
            $rules = [
                'title' => 'required',
                'description' => 'required',
                'type' => 'required',
                'category' => 'required',
                'price' => 'required',
                'area' => 'required',
                //'rooms'=>'required',
                'location' => 'required',
                'city' => 'required',
                'country' => 'required',
                'images' => 'required|array|min:2', // يجب رفع صورتين على الأقل
                'images.*' => 'mimes:jpeg,jpg,png,gif|required',
            ];
            $messages = [
                'title.required' => 'من فضلك ادخل العنوان',
                'description.required' => 'من فضلك ادخل الوصف',
                'type.required' => 'من فضلك ادخل نوع العقار',
                'category.required' => 'من فضلك ادخل القسم',
                'price.required' => 'من فضلك ادخل السعر',
                'area.required' => 'من فضلك ادخل المساحة',
                'location.required' => 'من فضلك ادخل الموقع',
                'city.required' => 'من فضلك ادخل المدينة',
                'country.required' => 'من فضلك ادخل الدولة',
                'images.required' => 'من فضلك اختر صورة للعقار',
                'images.array' => 'يجب اختيار صور متعددة للعقار',
                'images.min' => 'يجب رفع صورتين على الأقل',
                'images.*.required' => 'من فضلك اختر صورة بشكل صحيح',
                'images.*.mimes' => 'يجب أن تكون الصورة بصيغة jpeg, jpg, png, gif فقط',
            ];
            $validator = Validator::make($data, $rules, $messages);
            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator)->withInput();
            }
            ######### Add Image
            if ($request->hasFile('image')) {
                $filename = $this->saveImage($request->image, public_path('assets/uploads/properities'));
            }
            DB::beginTransaction();
            $properity = new Properity();
            $properity->user_id = Auth::user()->id;
            $properity->title = $data['title'];
            $properity->slug = $this->CustomeSlug($data['title']);
            $properity->description = $data['description'];
            $properity->type = $data['type'];
            $properity->category = $data['category'];
            $properity->price = $data['price'] ?: 0;
            $properity->area = $data['area'] ?: 0;
            $properity->rooms = $data['rooms'] ?: 0;
            $properity->bathrooms = $data['bathrooms'] ?: 0;
            $properity->features = $data['features'];
            $properity->location = $data['location'];
            $properity->city = $data['city'];
            $properity->country = $data['country'];
            $properity->status = 'متاح';
            $properity->save();
            ############## Add Images
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    $filename = $this->saveImage($image, public_path('assets/uploads/properities'));
                    $properity->ProperityImages()->create([
                        'image' => $filename,
                    ]);
                }
            }
            DB::commit();
            return $this->success_message(' تم اضافة العقار بنجاح ');
        }
        return view('website.properities.create');
    }
    ########### End Store Properity ############

    ############ Start Update Properity ############
    public function update(Request $request, $id)
    {
        $property = Properity::find($id);
        if ($request->isMethod('post')) {
            $data = $request->all();
            // dd($data);
            $rules = [
                'title' => 'required',
                'description' => 'required',
                'type' => 'required',
                'category' => 'required',
                'price' => 'required',
                'area' => 'required',
                'location' => 'required',
                'city' => 'required',
                'country' => 'required',
            ];
            $messages = [
                'title.required' => 'من فضلك ادخل العنوان',
                'description.required' => 'من فضلك ادخل الوصف',
                'type.required' => 'من فضلك ادخل نوع العقار',
                'category.required' => 'من فضلك ادخل القسم',
                'price.required' => 'من فضلك ادخل السعر',
                'area.required' => 'من فضلك ادخل المساحة',
                'location.required' => 'من فضلك ادخل الموقع',
                'city.required' => 'من فضلك ادخل المدينة',
                'country.required' => 'من فضلك ادخل الدولة',
            ];
            $validator = Validator::make($data, $rules, $messages);
            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator)->withInput();
            }
            DB::beginTransaction();
            $property->title = $data['title'];
            $property->slug = $this->CustomeSlug($data['title']);
            $property->description = $data['description'];
            $property->type = $data['type'];
            $property->category = $data['category'];
            $property->price = $data['price'] ?: 0;
            $property->area = $data['area'] ?: 0;
            $property->rooms = $data['rooms'] ?: 0;
            $property->bathrooms = $data['bathrooms'] ?: 0;
            $property->features = $data['features'];
            $property->location = $data['location'];
            $property->city = $data['city'];
            $property->country = $data['country'];
            $property->status = 'متاح';
            $property->save();
            ############## Add Images
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    $filename = $this->saveImage($image, public_path('assets/uploads/properities'));
                    $property->ProperityImages()->create([
                        'image' => $filename,
                    ]);
                }
            }
            DB::commit();
            return $this->success_message(' تم تحديث العقار بنجاح ');
        }
        return view('website.properities.edit', compact('property'));
    }
    ########### End Update Properity ############

    ############ Start Delete Properity ############
    public function delete(Request $request,$id)
    {

        $property = Properity::find($id);
        ######### Delete Properity Images
        ####### Delete Images From Images
        $images = $property->ProperityImages()->get();
        foreach ($images as $image) {
            $path = public_path('assets/uploads/properities/' . $image->image);
            if (file_exists($path)) {
                @unlink($path);
            }
        }
        $property->ProperityImages()->delete();
        $property->delete();
        return $this->success_message('تم حذف العقار بنجاح');

    }
    ########### End Delete Properity ############


    ############# DropZone ############

    public function uploadTemp(Request $request)
    {
        if ($request->hasFile('images')) {
            $file = $request->file('images');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('uploads/temp'), $filename);
            return response()->json(['fileName' => $filename]);
        }
    }

    public function removeTemp(Request $request)
    {
        $fileName = $request->fileName;
        $filePath = public_path('uploads/temp/') . $fileName;
        if (file_exists($filePath)) {
            unlink($filePath);
        }
        return response()->json(['message' => 'تم حذف الصورة']);
    }

    ########### Delete Properity Image

    public function deletePropertyImage(Request $request, $id){
        $image = ProperityImage::find($id);
        $path = public_path('assets/uploads/properities/' . $image->image);
        if (file_exists($path)) {
            @unlink($path);
        }
        $image->delete();
        return $this->success_message('تم حذف الصورة بنجاح');
    }


}
