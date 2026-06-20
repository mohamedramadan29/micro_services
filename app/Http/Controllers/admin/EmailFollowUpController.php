<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreFollowUpRequest;
use App\Models\EmailCampaign;
use App\Models\EmailFollowUp;
use App\Models\EmailTemplate;
use Illuminate\Http\Request;

class EmailFollowUpController extends Controller
{
    public function index(EmailCampaign $emailCampaign)
    {
        $followUps = $emailCampaign->followUps()->orderBy('sort_order')->orderBy('id')->get();
        return view('admin.email.follow_ups.index', compact('emailCampaign', 'followUps'));
    }

    public function create(EmailCampaign $emailCampaign)
    {
        $templates = EmailTemplate::active()->orderBy('name')->get();
        return view('admin.email.follow_ups.create', compact('emailCampaign', 'templates'));
    }

    public function store(Request $request, EmailCampaign $emailCampaign)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'trigger_type' => 'required|in:no_open,no_click',
            'delay_days' => 'required|integer|min:1',
            'subject' => 'required|string|max:255',
            'body' => 'required|string',
            'template_id' => 'nullable|exists:email_templates,id',
        ], [
            'name.required' => 'اسم المتابعة مطلوب',
            'trigger_type.required' => 'نوع المحفز مطلوب',
            'delay_days.required' => 'عدد أيام التأخير مطلوب',
            'subject.required' => 'عنوان الإيميل مطلوب',
            'body.required' => 'محتوى الإيميل مطلوب',
        ]);

        $emailCampaign->followUps()->create([
            'name' => $request->name,
            'trigger_type' => $request->trigger_type,
            'delay_days' => $request->delay_days,
            'subject' => $request->subject,
            'body' => $request->body,
            'template_id' => $request->template_id,
            'sort_order' => $request->sort_order ?? 0,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('admin.email.campaigns.show', $emailCampaign)
            ->with('success', 'تم إنشاء المتابعة بنجاح');
    }

    public function edit(EmailCampaign $emailCampaign, EmailFollowUp $emailFollowUp)
    {
        $templates = EmailTemplate::active()->orderBy('name')->get();
        return view('admin.email.follow_ups.edit', compact('emailCampaign', 'emailFollowUp', 'templates'));
    }

    public function update(Request $request, EmailCampaign $emailCampaign, EmailFollowUp $emailFollowUp)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'trigger_type' => 'required|in:no_open,no_click',
            'delay_days' => 'required|integer|min:1',
            'subject' => 'required|string|max:255',
            'body' => 'required|string',
            'template_id' => 'nullable|exists:email_templates,id',
        ]);

        $emailFollowUp->update([
            'name' => $request->name,
            'trigger_type' => $request->trigger_type,
            'delay_days' => $request->delay_days,
            'subject' => $request->subject,
            'body' => $request->body,
            'template_id' => $request->template_id,
            'sort_order' => $request->sort_order ?? 0,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('admin.email.campaigns.show', $emailCampaign)
            ->with('success', 'تم تحديث المتابعة بنجاح');
    }

    public function destroy(EmailCampaign $emailCampaign, EmailFollowUp $emailFollowUp)
    {
        $emailFollowUp->delete();
        return redirect()->route('admin.email.campaigns.show', $emailCampaign)
            ->with('success', 'تم حذف المتابعة بنجاح');
    }

    public function toggle(EmailCampaign $emailCampaign, EmailFollowUp $emailFollowUp)
    {
        $emailFollowUp->update(['is_active' => !$emailFollowUp->is_active]);
        return redirect()->route('admin.email.campaigns.show', $emailCampaign)
            ->with('success', 'تم تغيير حالة المتابعة بنجاح');
    }
}
