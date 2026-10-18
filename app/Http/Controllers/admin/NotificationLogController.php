<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\NotificationLog;
use Illuminate\Http\Request;

/** «سجل الإشعارات»: did each bell entry / email go out, and why not. */
class NotificationLogController extends Controller
{
    public function index(Request $request)
    {
        $logs = NotificationLog::query()
            ->when(in_array($request->query('status'), ['sent', 'failed'], true), fn ($q) => $q->where('status', $request->query('status')))
            ->when(in_array($request->query('channel'), ['database', 'mail'], true), fn ($q) => $q->where('channel', $request->query('channel')))
            ->latest('id')->paginate(50)->withQueryString();

        return view('admin.notification-log.index', [
            'logs' => $logs,
            'failed' => NotificationLog::where('status', 'failed')->where('created_at', '>=', now()->subDays(7))->count(),
        ]);
    }
}
