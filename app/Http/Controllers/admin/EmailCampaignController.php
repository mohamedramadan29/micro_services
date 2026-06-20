<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Http\Traits\Upload_Images;
use App\Models\EmailCampaign;
use App\Models\EmailList;
use App\Models\EmailTemplate;
use App\Models\EmailCampaignRecipient;
use App\Jobs\ProcessCampaignBatchJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class EmailCampaignController extends Controller
{
    use Upload_Images;

    public function index()
    {
        $campaigns = EmailCampaign::with(['emailList', 'creator'])
            ->withCount('recipients')
            ->orderBy('created_at', 'desc')
            ->get();
        return view('admin.email.campaigns.index', compact('campaigns'));
    }

    public function create()
    {
        $lists = EmailList::active()->orderBy('name')->get();
        $templates = EmailTemplate::active()->orderBy('name')->get();
        return view('admin.email.campaigns.create', compact('lists', 'templates'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email_list_id' => 'required|exists:email_lists,id',
            'template_id' => 'nullable|exists:email_templates,id',
            'subject' => 'required|string|max:255',
            'body' => 'required|string',
            'sender_name' => 'nullable|string|max:255',
            'sender_email' => 'nullable|email|max:255',
            'reply_to' => 'nullable|email|max:255',
            'cc_email' => 'nullable|email|max:255',
            'bcc_email' => 'nullable|email|max:255',
            'attachment' => 'nullable|file|max:10240',
            'ab_testing_enabled' => 'nullable|boolean',
            'ab_subject_b' => 'required_if:ab_testing_enabled,1|string|max:255',
            'ab_split_percent' => 'nullable|integer|min:10|max:90',
            'send_interval_seconds' => 'nullable|integer|min:1|max:3600',
            'throttle_per_hour' => 'nullable|integer|min:1|max:10000',
            'daily_limit' => 'nullable|integer|min:1|max:100000',
            'drip_enabled' => 'nullable|boolean',
            'drip_total_parts' => 'required_if:drip_enabled,1|nullable|integer|min:2|max:100',
            'drip_interval_hours' => 'required_if:drip_enabled,1|nullable|integer|min:1|max:720',
            'scheduled_at' => 'nullable|date|after_or_equal:now',
        ], [
            'name.required' => 'اسم الحملة مطلوب',
            'email_list_id.required' => 'القائمة البريدية مطلوبة',
            'subject.required' => 'عنوان الإيميل مطلوب',
            'body.required' => 'محتوى الإيميل مطلوب',
            'scheduled_at.after_or_equal' => 'وقت الجدولة يجب أن يكون في المستقبل',
        ]);

        $list = EmailList::withCount('contacts')->findOrFail($request->email_list_id);

        $campaign = EmailCampaign::create([
            'name' => $request->name,
            'email_list_id' => $request->email_list_id,
            'template_id' => $request->template_id,
            'subject' => $request->subject,
            'body' => $request->body,
            'sender_name' => $request->sender_name ?? config('mail.from.name'),
            'sender_email' => $request->sender_email ?? config('mail.from.address'),
            'reply_to' => $request->reply_to,
            'cc_email' => $request->cc_email,
            'bcc_email' => $request->bcc_email,
            'scheduled_at' => $request->scheduled_at,
            'status' => $request->scheduled_at ? 'scheduled' : 'draft',
            'total_recipients' => $list->contacts_count ?? 0,
            'tracking_enabled' => $request->boolean('tracking_enabled', true),
            'has_attachment' => $request->hasFile('attachment'),
            'attachment_path' => $request->hasFile('attachment')
                ? 'assets/uploads/campaign-attachments/' . $this->saveImage($request->file('attachment'), public_path('assets/uploads/campaign-attachments'))
                : null,
            'attachment_name' => $request->hasFile('attachment') ? $request->file('attachment')->getClientOriginalName() : null,
            'mailer' => $request->mailer ?? 'smtp',
            'filters' => $request->filled('filters') ? json_decode($request->filters, true) : null,
            'ab_testing_enabled' => $request->boolean('ab_testing_enabled'),
            'ab_subject_b' => $request->ab_subject_b,
            'ab_split_percent' => $request->ab_split_percent ?? 50,
            'send_interval_seconds' => $request->send_interval_seconds ?? 10,
            'throttle_per_hour' => $request->throttle_per_hour ?? 100,
            'daily_limit' => $request->daily_limit ?? 500,
            'drip_enabled' => $request->boolean('drip_enabled'),
            'drip_total_parts' => $request->drip_enabled ? ($request->drip_total_parts ?? 3) : 1,
            'drip_interval_hours' => $request->drip_enabled ? ($request->drip_interval_hours ?? 24) : 0,
            'created_by' => Auth::id(),
        ]);

        if ($request->action === 'save_and_send' && !$request->scheduled_at) {
            return redirect()->route('admin.email.campaigns.send', $campaign);
        }

        return redirect()->route('admin.email.campaigns.index')->with('success', 'تم إنشاء الحملة بنجاح');
    }

    public function show(EmailCampaign $emailCampaign)
    {
        $emailCampaign->load(['emailList', 'template', 'creator', 'followUps']);
        $emailCampaign->loadCount('recipients');
        $recipients = $emailCampaign->recipients()->orderBy('created_at', 'desc')->paginate(20);
        return view('admin.email.campaigns.show', compact('emailCampaign', 'recipients'));
    }

    public function edit(EmailCampaign $emailCampaign)
    {
        if (!in_array($emailCampaign->status, ['draft', 'scheduled'])) {
            return redirect()->route('admin.email.campaigns.index')
                ->with('error', 'لا يمكن تعديل حملة قيد الإرسال أو تم إرسالها');
        }

        $lists = EmailList::active()->orderBy('name')->get();
        $templates = EmailTemplate::active()->orderBy('name')->get();
        return view('admin.email.campaigns.edit', compact('emailCampaign', 'lists', 'templates'));
    }

    public function update(Request $request, EmailCampaign $emailCampaign)
    {
        if (!in_array($emailCampaign->status, ['draft', 'scheduled'])) {
            return redirect()->route('admin.email.campaigns.index')
                ->with('error', 'لا يمكن تعديل حملة قيد الإرسال أو تم إرسالها');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email_list_id' => 'required|exists:email_lists,id',
            'template_id' => 'nullable|exists:email_templates,id',
            'subject' => 'required|string|max:255',
            'body' => 'required|string',
            'sender_name' => 'nullable|string|max:255',
            'sender_email' => 'nullable|email|max:255',
            'reply_to' => 'nullable|email|max:255',
            'cc_email' => 'nullable|email|max:255',
            'bcc_email' => 'nullable|email|max:255',
            'attachment' => 'nullable|file|max:10240',
            'remove_attachment' => 'nullable|boolean',
            'ab_testing_enabled' => 'nullable|boolean',
            'ab_subject_b' => 'required_if:ab_testing_enabled,1|string|max:255',
            'ab_split_percent' => 'nullable|integer|min:10|max:90',
            'send_interval_seconds' => 'nullable|integer|min:1|max:3600',
            'throttle_per_hour' => 'nullable|integer|min:1|max:10000',
            'daily_limit' => 'nullable|integer|min:1|max:100000',
            'drip_enabled' => 'nullable|boolean',
            'drip_total_parts' => 'required_if:drip_enabled,1|nullable|integer|min:2|max:100',
            'drip_interval_hours' => 'required_if:drip_enabled,1|nullable|integer|min:1|max:720',
            'scheduled_at' => 'nullable|date',
        ]);

        $list = EmailList::withCount('contacts')->findOrFail($request->email_list_id);

        $attachmentData = [];
        if ($request->hasFile('attachment')) {
            $attachmentData = [
                'has_attachment' => true,
                'attachment_path' => 'assets/uploads/campaign-attachments/' . $this->saveImage($request->file('attachment'), public_path('assets/uploads/campaign-attachments')),
                'attachment_name' => $request->file('attachment')->getClientOriginalName(),
            ];
        } elseif ($request->boolean('remove_attachment')) {
            $attachmentData = [
                'has_attachment' => false,
                'attachment_path' => null,
                'attachment_name' => null,
            ];
        }

        $emailCampaign->update(array_merge([
            'name' => $request->name,
            'email_list_id' => $request->email_list_id,
            'template_id' => $request->template_id,
            'subject' => $request->subject,
            'body' => $request->body,
            'sender_name' => $request->sender_name ?? config('mail.from.name'),
            'sender_email' => $request->sender_email ?? config('mail.from.address'),
            'reply_to' => $request->reply_to,
            'cc_email' => $request->cc_email,
            'bcc_email' => $request->bcc_email,
            'scheduled_at' => $request->scheduled_at,
            'total_recipients' => $list->contacts_count ?? 0,
            'tracking_enabled' => $request->boolean('tracking_enabled', true),
            'mailer' => $request->mailer ?? 'smtp',
            'filters' => $request->filled('filters') ? json_decode($request->filters, true) : null,
            'ab_testing_enabled' => $request->boolean('ab_testing_enabled'),
            'ab_subject_b' => $request->ab_subject_b,
            'ab_split_percent' => $request->ab_split_percent ?? 50,
            'send_interval_seconds' => $request->send_interval_seconds ?? 10,
            'throttle_per_hour' => $request->throttle_per_hour ?? 100,
            'daily_limit' => $request->daily_limit ?? 500,
            'drip_enabled' => $request->boolean('drip_enabled'),
            'drip_total_parts' => $request->drip_enabled ? ($request->drip_total_parts ?? 3) : 1,
            'drip_interval_hours' => $request->drip_enabled ? ($request->drip_interval_hours ?? 24) : 0,
            'status' => $request->scheduled_at ? 'scheduled' : 'draft',
        ], $attachmentData));

        return redirect()->route('admin.email.campaigns.index')->with('success', 'تم تحديث الحملة بنجاح');
    }

    public function destroy(EmailCampaign $emailCampaign)
    {
        $emailCampaign->delete();
        return redirect()->route('admin.email.campaigns.index')->with('success', 'تم حذف الحملة بنجاح');
    }

    public function toggleStatus(EmailCampaign $emailCampaign)
    {
        if ($emailCampaign->status === 'paused') {
            $emailCampaign->status = 'sending';
        } elseif ($emailCampaign->status === 'draft') {
            $emailCampaign->status = 'scheduled';
            $emailCampaign->scheduled_at = now();
        } else {
            return response()->json(['success' => false, 'message' => 'لا يمكن تغيير حالة هذه الحملة']);
        }
        $emailCampaign->save();
        return response()->json(['success' => true, 'status' => $emailCampaign->status]);
    }

    public function send(EmailCampaign $emailCampaign)
    {
        if (!in_array($emailCampaign->status, ['draft', 'scheduled'])) {
            return redirect()->route('admin.email.campaigns.index')
                ->with('error', 'هذه الحملة قيد الإرسال أو تم إرسالها بالفعل');
        }

        $list = EmailList::with(['contacts' => function ($q) {
            $q->active();
        }])->findOrFail($emailCampaign->email_list_id);

        if ($list->contacts->isEmpty()) {
            return redirect()->route('admin.email.campaigns.edit', $emailCampaign)
                ->with('error', 'القائمة البريدية لا تحتوي على جهات اتصال نشطة');
        }

        $emailCampaign->update([
            'status' => 'sending',
            'total_recipients' => $list->contacts->count(),
            'scheduled_at' => $emailCampaign->scheduled_at ?? now(),
        ]);

        ProcessCampaignBatchJob::dispatch($emailCampaign);

        return redirect()->route('admin.email.campaigns.show', $emailCampaign)
            ->with('success', 'بدأ إرسال الحملة بنجاح');
    }

    public function duplicate(EmailCampaign $emailCampaign)
    {
        $newCampaign = $emailCampaign->replicate();
        $newCampaign->name = $emailCampaign->name . ' (نسخة)';
        $newCampaign->status = 'draft';
        $newCampaign->scheduled_at = null;
        $newCampaign->sent_count = 0;
        $newCampaign->open_count = 0;
        $newCampaign->click_count = 0;
        $newCampaign->created_by = Auth::id();
        $newCampaign->save();

        return redirect()->route('admin.email.campaigns.edit', $newCampaign)
            ->with('success', 'تم نسخ الحملة بنجاح');
    }
}
