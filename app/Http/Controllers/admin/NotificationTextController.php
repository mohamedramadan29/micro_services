<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Services\Notifications\MessageTemplates;
use Illuminate\Http\Request;

/** «نصوص الإشعارات»: the admin rewrites any notification text, using only that message's placeholders. */
class NotificationTextController extends Controller
{
    public function index(Request $request)
    {
        $group = array_key_exists((string) $request->string('group'), MessageTemplates::GROUPS) ? (string) $request->string('group') : null;
        $messages = collect(MessageTemplates::all())
            ->when($group, fn ($all) => $all->where('group', $group))
            ->when($request->filled('q'), fn ($all) => $all->filter(fn ($m, $key) => str_contains($m['label'] . ' ' . $m['text'] . ' ' . (MessageTemplates::overrides()[$key] ?? ''), $request->query('q'))))
            ->groupBy('group', true);

        return view('admin.notification-texts.index', [
            'messages' => $messages,
            'overrides' => MessageTemplates::overrides(),
            'group' => $group,
            'groups' => MessageTemplates::GROUPS,
            'total' => count(MessageTemplates::all()),
        ]);
    }

    public function save(Request $request)
    {
        $texts = (array) $request->input('texts', []);
        $errors = [];
        foreach ($texts as $key => $text) {
            if (! array_key_exists($key, MessageTemplates::all())) {
                continue;
            }
            $text = (string) $text;
            if (mb_strlen($text) > 500) {
                $errors[] = '«' . MessageTemplates::all()[$key]['label'] . '» أطول من 500 حرف';
            }
            if ($unknown = MessageTemplates::unknownPlaceholders($key, $text)) {
                $errors[] = '«' . MessageTemplates::all()[$key]['label'] . '»: المتغيرات {' . implode('}، {', $unknown) . '} غير متاحة لهذه الرسالة';
            }
        }
        if ($errors) {
            return back()->withInput()->withErrors($errors);
        }
        MessageTemplates::save($texts);

        return back()->with('success', 'تم حفظ نصوص الإشعارات. الرسائل الجديدة تُرسل بالنص المعدّل.');
    }
}
