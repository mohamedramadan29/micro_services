<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\EmailList;
use App\Models\EmailContact;
use App\Services\GoogleSheetsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EmailListController extends Controller
{
    public function index()
    {
        $emailLists = EmailList::withCount('contacts')->orderBy('created_at', 'desc')->get();
        return view('admin.email.lists.index', compact('emailLists'));
    }

    public function create()
    {
        return view('admin.email.lists.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ], [
            'name.required' => 'اسم القائمة مطلوب',
        ]);

        EmailList::create([
            'name' => $request->name,
            'description' => $request->description,
            'is_active' => $request->boolean('is_active', true),
            'created_by' => Auth::id(),
        ]);

        return redirect()->route('admin.email.lists.index')->with('success', 'تم إنشاء القائمة البريدية بنجاح');
    }

    public function show(EmailList $emailList)
    {
        $emailList->loadCount('contacts');
        $contacts = $emailList->contacts()->orderBy('created_at', 'desc')->paginate(20);
        return view('admin.email.lists.show', compact('emailList', 'contacts'));
    }

    public function edit(EmailList $emailList)
    {
        return view('admin.email.lists.edit', compact('emailList'));
    }

    public function update(Request $request, EmailList $emailList)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ], [
            'name.required' => 'اسم القائمة مطلوب',
        ]);

        $emailList->update([
            'name' => $request->name,
            'description' => $request->description,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('admin.email.lists.index')->with('success', 'تم تحديث القائمة البريدية بنجاح');
    }

    public function destroy(EmailList $emailList)
    {
        $emailList->delete();
        return redirect()->route('admin.email.lists.index')->with('success', 'تم حذف القائمة البريدية بنجاح');
    }

    public function toggleStatus(EmailList $emailList)
    {
        $emailList->is_active = !$emailList->is_active;
        $emailList->save();
        return response()->json(['success' => true, 'status' => $emailList->is_active]);
    }

    public function importForm(EmailList $emailList)
    {
        return view('admin.email.lists.import', compact('emailList'));
    }

    public function importStore(Request $request, EmailList $emailList)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:5120',
        ], [
            'csv_file.required' => 'ملف CSV مطلوب',
            'csv_file.mimes' => 'يرجى رفع ملف CSV',
        ]);

        $file = $request->file('csv_file');
        $path = $file->getRealPath();
        $handle = fopen($path, 'r');
        $header = fgetcsv($handle, 0, ',');
        $header = array_map('trim', $header);

        $emailIndex = array_search('email', array_map('strtolower', $header));
        $nameIndex = array_search('name', array_map('strtolower', $header));

        if ($emailIndex === false) {
            fclose($handle);
            return back()->withErrors(['csv_file' => 'الملف يجب أن يحتوي على عمود email']);
        }

        $imported = 0;
        $skipped = 0;

        while (($row = fgetcsv($handle, 0, ',')) !== false) {
            $row = array_map('trim', $row);
            $email = $row[$emailIndex] ?? '';

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $skipped++;
                continue;
            }

            $exists = EmailContact::where('email_list_id', $emailList->id)
                ->where('email', $email)
                ->exists();

            if ($exists) {
                $skipped++;
                continue;
            }

            $customFields = [];
            foreach ($header as $i => $field) {
                if ($i === $emailIndex || $i === $nameIndex) continue;
                if (isset($row[$i]) && !empty($row[$i])) {
                    $customFields[$field] = $row[$i];
                }
            }

            EmailContact::create([
                'email_list_id' => $emailList->id,
                'email' => $email,
                'name' => $nameIndex !== false && isset($row[$nameIndex]) ? $row[$nameIndex] : null,
                'custom_fields' => !empty($customFields) ? $customFields : null,
            ]);

            $imported++;
        }

        fclose($handle);

        $emailList->update(['total_contacts' => $emailList->contacts()->count()]);

        return redirect()->route('admin.email.lists.show', $emailList)
            ->with('success', "تم استيراد {$imported} جهة اتصال بنجاح. تم تخطي {$skipped}");
    }

    public function contactStore(Request $request, EmailList $emailList)
    {
        $request->validate([
            'email' => 'required|email',
            'name' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
        ], [
            'email.required' => 'البريد الإلكتروني مطلوب',
            'email.email' => 'البريد الإلكتروني غير صحيح',
        ]);

        $exists = EmailContact::where('email_list_id', $emailList->id)
            ->where('email', $request->email)
            ->exists();

        if ($exists) {
            return back()->withErrors(['email' => 'جهة الاتصال هذه موجودة بالفعل في القائمة']);
        }

        EmailContact::create([
            'email_list_id' => $emailList->id,
            'email' => $request->email,
            'name' => $request->name,
            'phone' => $request->phone,
        ]);

        $emailList->increment('total_contacts');

        return redirect()->route('admin.email.lists.show', $emailList)
            ->with('success', 'تم إضافة جهة الاتصال بنجاح');
    }

    public function contactDestroy(EmailList $emailList, EmailContact $contact)
    {
        if ($contact->email_list_id !== $emailList->id) {
            abort(404);
        }
        $contact->delete();
        $emailList->decrement('total_contacts');
        return redirect()->route('admin.email.lists.show', $emailList)
            ->with('success', 'تم حذف جهة الاتصال بنجاح');
    }

    public function importUsers(EmailList $emailList)
    {
        $users = \App\Models\User::whereNotNull('email')->where('email', '!=', '')->get();
        $imported = 0;
        $skipped = 0;

        foreach ($users as $user) {
            $exists = EmailContact::where('email_list_id', $emailList->id)
                ->where('email', $user->email)
                ->exists();

            if ($exists) {
                $skipped++;
                continue;
            }

            EmailContact::create([
                'email_list_id' => $emailList->id,
                'email' => $user->email,
                'name' => $user->name,
                'phone' => $user->phone,
            ]);
            $imported++;
        }

        $emailList->update(['total_contacts' => $emailList->contacts()->count()]);

        return redirect()->route('admin.email.lists.show', $emailList)
            ->with('success', "تم استيراد {$imported} مستخدم بنجاح. تم تخطي {$skipped} (موجودين مسبقاً)");
    }

    public function importSheets(Request $request, EmailList $emailList)
    {
        $request->validate([
            'spreadsheet_id' => 'required|string|max:500',
            'range' => 'nullable|string|max:50',
        ], [
            'spreadsheet_id.required' => 'معرّف الجدول مطلوب',
        ]);

        $sheetsService = app(GoogleSheetsService::class);

        try {
            if ($request->boolean('is_public')) {
                $rows = $sheetsService->readPublicSheet($request->spreadsheet_id, $request->gid ?? '0');
            } else {
                if (!$sheetsService->isReady()) {
                    return back()->with('error', 'يرجى ربط Google Sheets أولاً من إعدادات Gmail.');
                }
                $rows = $sheetsService->readSheet($request->spreadsheet_id, $request->range ?? 'A:Z');
            }

            if (empty($rows)) {
                return back()->with('error', 'الجدول فارغ أو لم يتم العثور على بيانات.');
            }

            $header = array_map('trim', $rows[0]);
            $emailIndex = array_search('email', array_map('strtolower', $header));
            $nameIndex = array_search('name', array_map('strtolower', $header));

            if ($emailIndex === false) {
                return back()->with('error', 'الجدول يجب أن يحتوي على عمود "email"');
            }

            $imported = 0;
            $skipped = 0;

            for ($i = 1; $i < count($rows); $i++) {
                $row = $rows[$i];
                if (count($row) <= $emailIndex) continue;

                $email = trim($row[$emailIndex] ?? '');
                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $skipped++;
                    continue;
                }

                $exists = EmailContact::where('email_list_id', $emailList->id)
                    ->where('email', $email)
                    ->exists();

                if ($exists) {
                    $skipped++;
                    continue;
                }

                $customFields = [];
                foreach ($header as $j => $field) {
                    if ($j === $emailIndex || $j === $nameIndex) continue;
                    if (isset($row[$j]) && !empty(trim($row[$j]))) {
                        $customFields[$field] = trim($row[$j]);
                    }
                }

                EmailContact::create([
                    'email_list_id' => $emailList->id,
                    'email' => $email,
                    'name' => $nameIndex !== false && isset($row[$nameIndex]) ? trim($row[$nameIndex]) : null,
                    'custom_fields' => !empty($customFields) ? $customFields : null,
                ]);

                $imported++;
            }

            $emailList->update(['total_contacts' => $emailList->contacts()->count()]);

            return redirect()->route('admin.email.lists.show', $emailList)
                ->with('success', "تم استيراد {$imported} جهة اتصال من Google Sheets. تم تخطي {$skipped} (موجودين مسبقاً أو إيميل غير صحيح)");

        } catch (\Exception $e) {
            return back()->with('error', 'فشل استيراد البيانات من Google Sheets: ' . $e->getMessage());
        }
    }
}
