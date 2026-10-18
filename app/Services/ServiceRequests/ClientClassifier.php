<?php

namespace App\Services\ServiceRequests;

use App\Models\admin\Setting;
use App\Models\HelpRequest;
use App\Models\RequestAdminMessage;
use App\Models\ServiceRequest;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Client classifications (spec pages 8 and 20), always computed from the
 * records so they follow every new request, completion and message. A client
 * can carry several at once. A "client" is any user with at least one request
 * (or, when the admin counts them, one «ساعدوني أختار» help request).
 */
class ClientClassifier
{
    /** In the fixed order the spec asks for. */
    public const CATEGORIES = [
        'first_time' => ['label' => 'عميل أول مرة', 'hint' => 'أول طلب أو تواصل مسجل'],
        'repeat' => ['label' => 'عميل متكرر', 'hint' => 'تكررت طلباته حسب حد الأدمن'],
        'second_time' => ['label' => 'عميل ثاني مرة', 'hint' => 'وصل لطلبه أو تواصله الثاني'],
        'completed_before' => ['label' => 'عميل نفّذ قبل هالمرة', 'hint' => 'لديه طلب مكتمل سابقًا'],
        'never_completed' => ['label' => 'عميل لم ينفّذ معنا', 'hint' => 'لا يوجد له طلب مكتمل حتى الآن'],
        'asking' => ['label' => 'عميل عم يسأل', 'hint' => 'سؤاله بانتظار رد الأدمن'],
        'answering' => ['label' => 'عميل عم يجاوب', 'hint' => 'بانتظار إجابة العميل'],
    ];

    public const DEFAULT_REPEAT_THRESHOLD = 2;

    /** What counts as one contact of a client (admin setting `client_count_mode`). */
    public const COUNT_MODES = [
        'requests' => 'طلبات الخدمة فقط',
        'requests_and_help' => 'طلبات الخدمة + طلبات «ساعدوني أختار»',
    ];

    /** Cached classification of all clients; cleared when requests, team messages or settings change. */
    public const CACHE_KEY = 'service-requests.client-classifications';

    public const CACHE_SECONDS = 300;

    public function cached(): Collection
    {
        return collect(Cache::remember(self::CACHE_KEY, self::CACHE_SECONDS, fn () => $this->classify()->all()));
    }

    public static function forget(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    public function repeatThreshold(): int
    {
        $value = (int) (Setting::first()?->repeat_client_threshold ?? 0);

        return $value >= 2 ? $value : self::DEFAULT_REPEAT_THRESHOLD;
    }

    public function countsHelpRequests(): bool
    {
        return Setting::first()?->client_count_mode === 'requests_and_help';
    }

    /** Whether «متكرر» and «ثاني مرة» may show together (else «متكرر» replaces it). */
    public function tagsOverlap(): bool
    {
        return (bool) (Setting::first()?->client_tags_overlap ?? true);
    }

    /**
     * Classify every client (or only the given user ids).
     *
     * @return Collection<int, array{tags: string[], requests: int, contacts: int, completed: int, last_activity: ?Carbon}>
     */
    public function classify(?array $userIds = null): Collection
    {
        $requests = ServiceRequest::query()
            ->when($userIds !== null, fn ($q) => $q->whereIn('user_id', $userIds))
            ->groupBy('user_id')
            ->select('user_id', DB::raw('COUNT(*) as total'), DB::raw("SUM(CASE WHEN status = '" . ServiceRequest::STATUS_COMPLETED . "' THEN 1 ELSE 0 END) as completed"), DB::raw('MAX(created_at) as last_request'))
            ->get()
            ->keyBy('user_id');

        // Who spoke last in each request's conversation with the team.
        $latestIds = RequestAdminMessage::query()
            ->when($userIds !== null, fn ($q) => $q->whereIn('service_request_id', ServiceRequest::whereIn('user_id', $userIds)->select('id')))
            ->groupBy('service_request_id')
            ->select(DB::raw('MAX(id) as id'));
        $lastMessages = RequestAdminMessage::whereIn('request_admin_messages.id', $latestIds)
            ->join('service_requests', 'service_requests.id', '=', 'request_admin_messages.service_request_id')
            ->get(['service_requests.user_id', 'request_admin_messages.from_admin', 'request_admin_messages.created_at'])
            ->groupBy('user_id');

        // «ساعدوني أختار» help requests count as contacts when the admin chose so.
        $help = $this->countsHelpRequests()
            ? HelpRequest::query()->whereNotNull('user_id')
                ->when($userIds !== null, fn ($q) => $q->whereIn('user_id', $userIds))
                ->groupBy('user_id')
                ->select('user_id', DB::raw('COUNT(*) as total'), DB::raw('MAX(created_at) as last_help'))
                ->get()->keyBy('user_id')
            : collect();

        $threshold = $this->repeatThreshold();
        $overlap = $this->tagsOverlap();
        $userIdsSeen = $requests->keys()->merge($help->keys())->unique();

        return $userIdsSeen->mapWithKeys(fn ($userId) => [$userId => (object) [
            'requests' => $requests->get($userId),
            'help' => $help->get($userId),
            'user_id' => $userId,
        ]])->map(function ($row) use ($lastMessages, $threshold, $overlap) {
            $requestCount = (int) ($row->requests->total ?? 0);
            $total = $requestCount + (int) ($row->help->total ?? 0);
            $completed = (int) ($row->requests->completed ?? 0);
            $messages = $lastMessages->get($row->user_id, collect());

            $tags = [];
            if ($total === 1) {
                $tags[] = 'first_time';
            }
            if ($total >= $threshold) {
                $tags[] = 'repeat';
            }
            if ($total === 2 && ($overlap || $total < $threshold)) {
                $tags[] = 'second_time';
            }
            $tags[] = $completed > 0 ? 'completed_before' : 'never_completed';
            if ($messages->contains(fn ($m) => ! $m->from_admin)) {
                $tags[] = 'asking';
            }
            if ($messages->contains(fn ($m) => $m->from_admin)) {
                $tags[] = 'answering';
            }

            $lastActivity = collect([$row->requests->last_request ?? null, $row->help->last_help ?? null, $messages->max('created_at')])->filter()->map(fn ($d) => Carbon::parse($d))->max();

            return [
                // Keep the spec's order whatever order the rules ran in.
                'tags' => array_values(array_intersect(array_keys(self::CATEGORIES), $tags)),
                'requests' => $requestCount,
                'contacts' => $total,
                'completed' => $completed,
                'last_activity' => $lastActivity,
            ];
        });
    }

    /** Number of clients in each category (a client may count in several). */
    public function counts(Collection $classified): array
    {
        $counts = array_fill_keys(array_keys(self::CATEGORIES), 0);
        foreach ($classified as $client) {
            foreach ($client['tags'] as $tag) {
                $counts[$tag]++;
            }
        }

        return $counts;
    }
}
