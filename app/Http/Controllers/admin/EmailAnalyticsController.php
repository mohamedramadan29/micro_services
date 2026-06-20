<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\EmailCampaign;
use App\Models\EmailSendingLog;
use App\Services\EmailSendingService;
use Illuminate\Http\Request;

class EmailAnalyticsController extends Controller
{
    public function index(EmailSendingService $sendingService)
    {
        $totalCampaigns = EmailCampaign::count();
        $totalSent = EmailCampaign::sum('sent_count');
        $totalOpens = EmailCampaign::sum('open_count');
        $totalClicks = EmailCampaign::sum('click_count');
        $recentCampaigns = EmailCampaign::with(['emailList', 'creator'])
            ->withCount('recipients')
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        $campaignsByStatus = [
            'draft' => EmailCampaign::where('status', 'draft')->count(),
            'scheduled' => EmailCampaign::where('status', 'scheduled')->count(),
            'sending' => EmailCampaign::where('status', 'sending')->count(),
            'sent' => EmailCampaign::where('status', 'sent')->count(),
        ];

        $overallOpenRate = $totalSent > 0 ? round(($totalOpens / $totalSent) * 100, 1) : 0;
        $overallClickRate = $totalSent > 0 ? round(($totalClicks / $totalSent) * 100, 1) : 0;

        // Sending limits
        $todayCount = $sendingService->getTodayCount();
        $lastHourCount = EmailSendingLog::where('sent_at', '>=', now()->subHour())->count();
        $smtpCount = EmailSendingLog::where('mailer', 'smtp')->whereDate('sent_at', today())->count();
        $gmailCount = EmailSendingLog::where('mailer', 'gmail')->whereDate('sent_at', today())->count();

        return view('admin.email.analytics.index', compact(
            'totalCampaigns',
            'totalSent',
            'totalOpens',
            'totalClicks',
            'overallOpenRate',
            'overallClickRate',
            'recentCampaigns',
            'campaignsByStatus',
            'todayCount',
            'lastHourCount',
            'smtpCount',
            'gmailCount'
        ));
    }
}
