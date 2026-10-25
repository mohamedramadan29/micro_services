<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Http\Traits\Message_Trait;
use App\Http\Traits\Slug_Trait;
use App\Http\Traits\Upload_Images;
use App\Models\admin\Category;
use App\Models\admin\Service;
use App\Models\admin\SubCategory;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;

class ServiceController extends Controller
{
    use Message_Trait;
    use Slug_Trait;
    use Upload_Images;

    public const TABS = ['pending' => Service::STATUS_PENDING, 'active' => Service::STATUS_ACTIVE, 'rejected' => Service::STATUS_REJECTED];

    /** Freelancers' services by review state (pending first): publish or reject with a reason from the list. */
    public function index(Request $request)
    {
        $tab = array_key_exists((string) $request->string('tab'), self::TABS) ? (string) $request->string('tab') : 'pending';
        $services = Service::with('category', 'subcategory', 'user:id,name,email,user_name')
            ->where('status', self::TABS[$tab])
            ->when($request->filled('q'), function ($query) use ($request) {
                $term = '%' . str_replace(['%', '_'], ['\%', '\_'], (string) $request->string('q')) . '%';
                $query->where(fn ($q) => $q->where('name', 'like', $term)->orWhereHas('user', fn ($u) => $u->where('name', 'like', $term)->orWhere('email', 'like', $term)));
            })
            ->when($tab === 'pending', fn ($q) => $q->oldest('updated_at'), fn ($q) => $q->latest('id'))
            ->paginate(30)->withQueryString();
        $counts = Service::selectRaw('status, COUNT(*) as total')->groupBy('status')->pluck('total', 'status');

        return view('admin.services.index', compact('services', 'tab', 'counts'));
    }

    public function publish($id)
    {
        $service = Service::findOrFail($id);
        $this->decide($service, Service::STATUS_ACTIVE, null);

        return back()->with('Success_message', 'تم نشر الخدمة وإبلاغ المستقل.');
    }

    public function reject(Request $request, $id)
    {
        $service = Service::findOrFail($id);
        $data = $request->validate(['note' => 'required|string|max:500'], ['note.required' => 'اكتب سبب عدم القبول ليعرف المستقل ما يعدّله']);
        $this->decide($service, Service::STATUS_REJECTED, $data['note']);

        return back()->with('Success_message', 'تم تسجيل عدم القبول وإبلاغ المستقل.');
    }

    private function decide(Service $service, int $status, ?string $note): void
    {
        $service->forceFill(['status' => $status, 'review_note' => $note, 'reviewed_at' => now(), 'reviewed_by' => Auth::id()])->save();
        \Illuminate\Support\Facades\Cache::forget('admin-menu-badges');
        $service->user?->notify(new \App\Notifications\RequestUpdateNotification(
            $status === Service::STATUS_ACTIVE
                ? \App\Support\Msg::t('requests.service_published', ['title' => $service->name])
                : \App\Support\Msg::t('requests.service_rejected', ['title' => $service->name, 'reason' => $note]),
            $status === Service::STATUS_ACTIVE ? '/service/' . $service->id . '-' . $service->slug : '/service/index',
            'request_updates'
        ));
    }

    public function store(Request $request)
    {
        $categories = Category::where('status', '1')->get();
        $subCategories = SubCategory::where('status', '1')->get();
        try {
            if ($request->isMethod('post')) {
                $data = $request->all();
                $rules = [
                    'name' => 'required',
                    'cat_id' => 'required',
                    'user_id' => 'required',
                    'image' => 'required|image',
                    'description' => 'required',
                    'price' => 'required'
                ];
                $messages = [
                    'name.required' => 'من فضلك ادخل اسم الخدمة ',
                    'cat_id.required' => 'من فضلك حدد القسم '
                ];
                $validator = Validator::make($data, $rules, $messages);
                if ($validator->fails()) {
                    return Redirect::back()->withInput()->withErrors($validator);
                }
                $service = new Service();
                if ($request->hasFile('image')) {
                    $filename = $this->saveImage($request->image, public_path('assets/uploads/services'));
                }
                $service->create([
                    'name' => $data['name'],
                    'slug' => $this->CustomeSlug($data['name']),
                    'cat_id' => $data['cat_id'],
                    'sub_cat_id' => $data['sub_cat_id'],
                    'user_id' => Auth::id(),
                    'image' => $filename,
                    'description' => $data['description'],
                    'price' => $data['price'],
                    'status' => $data['status'],
                    'tags' => $data['tags'],
                ]);
                return $this->success_message('تم اضافة خدمة جديدة بنجاح');
            }
        } catch (\Exception $e) {
            return $this->exception_message($e);
        }
        return view('admin.services.add', compact('categories', 'subCategories'));

    }

    public function update(Request $request, $id)
    {
        $categories = Category::where('status', '1')->get();
        $subCategories = SubCategory::where('status', '1')->get();
        $service = Service::findOrFail($id);
       // $user = User::where('id',$service['user_id'])->get();
        $user = User::find($service['user_id']);
        try {
            if ($request->isMethod('post')) {
                $data = $request->all();
                $rules = [
                    'name' => 'required', 'cat_id' => 'required',
                    'description' => 'required', 'price' => 'required'
                ];
                if ($request->hasFile('image')) {
                    $rules['image'] = 'image|required';
                }
                $messages = [
                    'name.required' => 'من فضلك ادخل اسم الخدمة ',
                    'cat_id.required' => 'من فضلك حدد القسم '
                ];
                $validator = Validator::make($data, $rules, $messages);
                if ($validator->fails()) {
                    return Redirect::back()->withInput()->withErrors($validator);
                }
                if ($request->hasFile('image')) {
                    $filename = $this->saveImage($request->image, public_path('assets/uploads/services'));
                    if ($service['image'] != '') {
                        unlink(public_path('assets/uploads/services/' . $service['image']));
                        $service->update([
                            'image' => $filename
                        ]);
                    }
                }
                $service->update([
                    'name' => $data['name'],
                    'slug' => $this->CustomeSlug($data['name']),
                    'cat_id' => $data['cat_id'],
                    'sub_cat_id' => $data['sub_cat_id'],
                    'description' => $data['description'],
                    'price' => $data['price'],
                    'tags' => $data['tags'],
                ]);
                // Publishing from the edit page works like «نشر الخدمة» on the list (the freelancer is told);
                // a rejection needs a reason, so it is done from the list.
                $newStatus = (int) ($data['status'] ?? $service->status);
                if ($newStatus === Service::STATUS_ACTIVE && ! $service->isActive()) {
                    $this->decide($service->fresh(), Service::STATUS_ACTIVE, null);
                } elseif ($newStatus === Service::STATUS_PENDING && $service->isActive()) { // unpublish (a rejected one stays rejected)
                    $service->forceFill(['status' => Service::STATUS_PENDING])->save();
                    \Illuminate\Support\Facades\Cache::forget('admin-menu-badges');
                }
                return $this->success_message('تم تعديل  الخدمة بنجاح ');
            }
        } catch (\Exception $e) {
            return $this->exception_message($e);
        }
        return view('admin.services.edit', compact('service', 'categories', 'subCategories'));
    }


    public function getSubCategories($category_id)
    {
        $subCategories = SubCategory::where('parent_id', $category_id)->get();
        return response()->json($subCategories);
    }

    public function destroy($id)
    {
        try {
            $service = Service::findOrFail($id);
            if ($service['image'] != '') {
                @unlink(public_path('assets/uploads/services/' . $service['image']));
            }
            $service->delete();
            return $this->success_message('تم حذف الخدمة بنجاح ');
        } catch (\Exception $e) {
            return $this->exception_message($e);
        }

    }
}
