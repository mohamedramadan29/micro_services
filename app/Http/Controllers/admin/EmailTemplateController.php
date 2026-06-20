<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\EmailTemplate;
use Illuminate\Http\Request;

class EmailTemplateController extends Controller
{
    public function index()
    {
        $templates = EmailTemplate::orderBy('created_at', 'desc')->get();
        return view('admin.email.templates.index', compact('templates'));
    }

    public function create()
    {
        return view('admin.email.templates.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'subject' => 'required|string|max:255',
            'body' => 'required|string',
        ], [
            'name.required' => 'اسم القالب مطلوب',
            'subject.required' => 'عنوان الإيميل مطلوب',
            'body.required' => 'محتوى الإيميل مطلوب',
        ]);

        $variables = $this->extractVariables($request->subject . ' ' . $request->body);

        EmailTemplate::create([
            'name' => $request->name,
            'subject' => $request->subject,
            'body' => $request->body,
            'variables' => !empty($variables) ? $variables : null,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('admin.email.templates.index')->with('success', 'تم إنشاء القالب بنجاح');
    }

    public function show(EmailTemplate $emailTemplate)
    {
        if (request()->wantsJson()) {
            return response()->json($emailTemplate);
        }
        return view('admin.email.templates.show', compact('emailTemplate'));
    }

    public function edit(EmailTemplate $emailTemplate)
    {
        return view('admin.email.templates.edit', compact('emailTemplate'));
    }

    public function update(Request $request, EmailTemplate $emailTemplate)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'subject' => 'required|string|max:255',
            'body' => 'required|string',
        ], [
            'name.required' => 'اسم القالب مطلوب',
            'subject.required' => 'عنوان الإيميل مطلوب',
            'body.required' => 'محتوى الإيميل مطلوب',
        ]);

        $variables = $this->extractVariables($request->subject . ' ' . $request->body);

        $emailTemplate->update([
            'name' => $request->name,
            'subject' => $request->subject,
            'body' => $request->body,
            'variables' => !empty($variables) ? $variables : null,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('admin.email.templates.index')->with('success', 'تم تحديث القالب بنجاح');
    }

    public function destroy(EmailTemplate $emailTemplate)
    {
        $emailTemplate->delete();
        return redirect()->route('admin.email.templates.index')->with('success', 'تم حذف القالب بنجاح');
    }

    public function toggleStatus(EmailTemplate $emailTemplate)
    {
        $emailTemplate->is_active = !$emailTemplate->is_active;
        $emailTemplate->save();
        return response()->json(['success' => true, 'status' => $emailTemplate->is_active]);
    }

    private function extractVariables($text): array
    {
        preg_match_all('/\{\{(\w+)\}\}/', $text, $matches);
        return array_unique($matches[1]);
    }
}
