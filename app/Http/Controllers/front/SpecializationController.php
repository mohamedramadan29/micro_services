<?php

namespace App\Http\Controllers\front;

use App\Http\Controllers\Controller;
use App\Http\Traits\Message_Trait;
use App\Models\admin\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class SpecializationController extends Controller
{
    use Message_Trait;

    public function edit()
    {
        $user = Auth::user();
        if (! $user->isFreelancer()) {
            return redirect('dashboard')->withErrors('صفحة التخصصات متاحة لحسابات المستقلين فقط');
        }

        $categories = Category::where('status', 1)
            ->with(['subCategories' => fn ($query) => $query->where('status', 1)->orderBy('name')])
            ->orderBy('name')
            ->get()
            ->filter(fn ($category) => $category->subCategories->isNotEmpty());

        $selected = $user->specializations()->pluck('sub_categories.id')->all();

        return view('website.user.specializations', compact('categories', 'selected'));
    }

    public function update(Request $request)
    {
        $user = Auth::user();
        if (! $user->isFreelancer()) {
            return redirect('dashboard')->withErrors('صفحة التخصصات متاحة لحسابات المستقلين فقط');
        }

        $validator = Validator::make($request->all(), [
            'specializations' => 'required|array|min:1',
            'specializations.*' => ['integer', Rule::exists('sub_categories', 'id')->where('status', 1)],
        ], [
            'specializations.required' => 'من فضلك اختر تخصصًا واحدًا على الأقل',
            'specializations.min' => 'من فضلك اختر تخصصًا واحدًا على الأقل',
            'specializations.*.exists' => 'أحد التخصصات المختارة غير متاح',
            'specializations.*.integer' => 'أحد التخصصات المختارة غير متاح',
        ]);
        if ($validator->fails()) {
            return Redirect::back()->withErrors($validator);
        }

        $user->specializations()->sync($request->input('specializations'));
        $user->fresh()->markFreelancerProfileCompleted();

        return $this->success_message('تم حفظ تخصصاتك بنجاح');
    }
}
