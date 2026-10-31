<?php

namespace App\Http\Controllers\front;

use App\Http\Controllers\Controller;
use App\Models\admin\Category;
use App\Models\admin\SubCategory;
use App\Models\HelpRequest;
use App\Models\User;
use App\Notifications\HelpRequestNotification;
use App\Support\SiteContent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\Rule;

/**
 * Client brief «متطلبات تطوير قسم الخدمات»: one services page (groups,
 * instant search, choice window) and the «ساعدوني أختار» help form, whose
 * requests go to the admin box «طلبات المساعدة», never to service requests.
 */
class ServicesPageController extends Controller
{
    public function index()
    {
        // An admin previewing sees hidden groups/services too (marked), to check them before publishing.
        $preview = SiteContent::previewing();
        $groups = Category::ordered()
            ->when(! $preview, fn ($q) => $q->where('status', 1))
            ->with(['subCategories' => fn ($q) => $q->ordered()->when(! $preview, fn ($q) => $q->where('status', 1))])
            ->get()
            ->filter(fn (Category $group) => $group->subCategories->isNotEmpty())
            ->values();

        $withForm = \App\Models\ServiceForm::published()->pluck('sub_category_id')->flip();
        // Freelancers' published ready-made services per sub-service (a third choice in the dialog).
        $readyCounts = \App\Models\admin\Service::active()->selectRaw('sub_cat_id, COUNT(*) as total')->groupBy('sub_cat_id')->pluck('total', 'sub_cat_id');

        // Approved freelancers listed in the directory per sub-service (the «المستقلين» choice in the dialog).
        $freelancerCounts = \Illuminate\Support\Facades\DB::table('freelancer_specializations')
            ->whereIn('user_id', \App\Models\User::listedFreelancers()->select('id'))
            ->selectRaw('sub_category_id, COUNT(*) as total')->groupBy('sub_category_id')->pluck('total', 'sub_category_id');

        return view('website.services-catalog.index', compact('groups', 'preview', 'withForm', 'readyCounts', 'freelancerCounts'));
    }

    /** Guests choosing «ساعدوني أختار» log in first, then come back with the form open. */
    public function helpLogin(Request $request)
    {
        return redirect()->route('services.index', array_filter(['help' => 1, 'service' => $request->integer('service') ?: null]));
    }

    public function storeHelp(Request $request)
    {
        // From the request form («اتعثرت؟»): its own error bag, so the request form keeps what was written.
        $fromForm = in_array($request->input('source'), ['request_form', 'staged_form'], true);
        $request->merge(['whatsapp' => \App\Support\Phone::normalize($request->input('whatsapp'))]);
        $data = $request->validateWithBag($fromForm ? 'stuck' : 'default', [
            'whatsapp' => ['required', 'string', 'max:30', \App\Support\Phone::RULE],
            'message' => 'required|string|min:5|max:2000',
            'service' => ['nullable', 'integer', Rule::exists('sub_categories', 'id')->where('status', 1)],
            // «اتعثرت؟» from the request form: where the client got stuck and what he wrote so far.
            'source' => ['nullable', Rule::in(['request_form', 'staged_form'])],
            'context' => 'nullable|string|max:3000',
        ], [
            'whatsapp.required' => 'من فضلك اكتب رقم الواتساب',
            'whatsapp.regex' => \App\Support\Phone::MESSAGE,
            'message.required' => 'من فضلك اكتب الخدمة أو المشكلة اللي عندك',
            'message.min' => 'اكتب تفاصيل أكثر قليلًا',
            'message.max' => 'الوصف أطول من المسموح (2000 حرف)',
            'service.exists' => 'الخدمة المختارة غير متاحة',
        ]);

        $service = isset($data['service']) ? SubCategory::find($data['service']) : null;
        $help = HelpRequest::create([
            'user_id' => Auth::id(),
            'sub_category_id' => $service?->id,
            'service_name' => $service?->name,
            'whatsapp' => trim($data['whatsapp']),
            'message' => $data['message'],
            'status' => HelpRequest::STATUS_NEW,
            'source' => $data['source'] ?? null,
            'context' => $data['context'] ?? null,
        ]);
        $help->update(['number' => 'HLP-' . (1000 + $help->id)]);

        Notification::send(User::activeAdmins()->get(), new HelpRequestNotification($help));
        app(\App\Services\Analytics\Tracker::class)->record('support_requested', ['sub_category_id' => $help->sub_category_id, 'meta' => ['source' => $help->source ?? 'services']]);
        if ($help->source) {
            return back()->withInput()->with('Success_message', 'وصل طلب المساعدة ' . $help->number . ' لفريق نفّذها، وسيتواصل معك على الواتساب. ما كتبته في الطلب ما زال هنا.');
        }

        // The confirmation is shown only once the request is saved.
        return redirect()->route('services.index')->with('help_request_created', $help->number)->with('help_request_service', $help->serviceLabel());
    }
}
