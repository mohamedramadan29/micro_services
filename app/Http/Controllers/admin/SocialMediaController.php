<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\SocialAccount;
use App\Models\SocialPost;
use App\Models\SocialPostResult;
use App\Services\SocialMedia\SocialMediaService;
use Illuminate\Http\Request;

class SocialMediaController extends Controller
{
    public function __construct(private SocialMediaService $socialMedia)
    {
    }

    /** لوحة التحكم الرئيسية */
    public function index()
    {
        $accounts       = SocialAccount::all();
        $totalPosts     = SocialPost::count();
        $scheduledPosts = SocialPost::where('status', 'scheduled')->count();
        $publishedPosts = SocialPost::whereIn('status', ['published', 'partial'])->count();
        $failedPosts    = SocialPost::where('status', 'failed')->count();

        $recentPosts = SocialPost::with('results.account')
            ->latest()
            ->take(10)
            ->get();

        $platforms = ['facebook', 'instagram', 'tiktok', 'youtube', 'twitter', 'linkedin'];
        $connectedPlatforms = $accounts->pluck('platform')->toArray();

        return view('admin.social-media.index', compact(
            'accounts', 'totalPosts', 'scheduledPosts',
            'publishedPosts', 'failedPosts', 'recentPosts',
            'platforms', 'connectedPlatforms'
        ));
    }

    /** صفحة إدارة الحسابات */
    public function accounts()
    {
        $accounts  = SocialAccount::all();
        $platforms = ['facebook', 'instagram', 'tiktok', 'youtube'];
        return view('admin.social-media.accounts', compact('accounts', 'platforms'));
    }

    /** تفعيل/تعطيل حساب */
    public function toggleAccount(SocialAccount $account)
    {
        $account->update(['is_active' => !$account->is_active]);
        return back()->with('success', $account->is_active ? 'تم تفعيل الحساب' : 'تم تعطيل الحساب');
    }

    /** حذف حساب */
    public function deleteAccount(SocialAccount $account)
    {
        $account->delete();
        return back()->with('success', 'تم حذف الحساب بنجاح');
    }

    // ======================== OAuth Flows ========================

    /** بدء ربط Facebook */
    public function connectFacebook()
    {
        return redirect($this->socialMedia->getAuthUrl('facebook'));
    }

    /** Callback لـ Facebook */
    public function facebookCallback(Request $request)
    {
        \Log::info('Facebook Callback Received', ['code' => $request->code ? 'Present' : 'Missing']);
        try {
            $data  = $this->socialMedia->handleCallback('facebook', $request->code);
            $pages = $data['pages'] ?? [];

            if (empty($pages)) {
                \Log::warning('Facebook Callback: No pages found for user', ['user' => $data['user_name'] ?? 'Unknown']);
                return redirect()->route('admin.social.accounts')
                    ->with('error', 'لم يتم العثور على صفحات Facebook. تأكد من أن لديك صفحة Business وأنه تم اختيارها أثناء الربط.');
            }

            // حفظ الصفحات
            foreach ($pages as $page) {
                SocialAccount::updateOrCreate(
                    ['platform' => 'facebook', 'page_id' => $page['id']],
                    [
                        'account_name' => $page['name'],
                        'account_id'   => $data['user_id'],
                        'access_token' => $data['access_token'],
                        'page_id'      => $page['id'],
                        'page_name'    => $page['name'],
                        'avatar'       => $data['avatar'] ?? null,
                        'is_active'    => true,
                    ]
                );
            }

            return redirect()->route('admin.social.accounts')
                ->with('success', 'تم ربط Facebook بنجاح! تم العثور على ' . count($pages) . ' صفحة.');
        } catch (\Exception $e) {
            \Log::error('Facebook Callback Exception: ' . $e->getMessage());
            return redirect()->route('admin.social.accounts')
                ->with('error', 'فشل ربط Facebook: ' . $e->getMessage());
        }
    }

    /** بدء ربط Instagram */
    public function connectInstagram()
    {
        return redirect($this->socialMedia->getAuthUrl('instagram'));
    }

    /** Callback لـ Instagram */
    public function instagramCallback(Request $request)
    {
        try {
            $data       = $this->socialMedia->handleCallback('instagram', $request->code);
            $igAccounts = $data['ig_accounts'] ?? [];

            if (empty($igAccounts)) {
                return redirect()->route('admin.social.accounts')
                    ->with('error', 'لم يتم العثور على حسابات Instagram Business مرتبطة بصفحات Facebook.');
            }

            foreach ($igAccounts as $ig) {
                SocialAccount::updateOrCreate(
                    ['platform' => 'instagram', 'account_id' => $ig['ig_id']],
                    [
                        'account_name'    => $ig['username'],
                        'account_id'   => $ig['ig_id'],
                        'access_token' => $ig['page_token'],
                        'page_id'      => $ig['page_id'],
                        'avatar'       => $ig['profile_picture'] ?? null,
                        'is_active'    => true,
                    ]
                );
            }

            return redirect()->route('admin.social.accounts')
                ->with('success', 'تم ربط Instagram بنجاح!');
        } catch (\Exception $e) {
            return redirect()->route('admin.social.accounts')
                ->with('error', 'فشل ربط Instagram: ' . $e->getMessage());
        }
    }

    /** بدء ربط TikTok */
    public function connectTikTok()
    {
        return redirect($this->socialMedia->getAuthUrl('tiktok'));
    }

    /** Callback لـ TikTok */
    public function tikTokCallback(Request $request)
    {
        try {
            $data = $this->socialMedia->handleCallback('tiktok', $request->code);

            SocialAccount::updateOrCreate(
                ['platform' => 'tiktok', 'account_id' => $data['open_id']],
                [
                    'account_name'    => $data['user_name'],
                    'account_id'      => $data['open_id'],
                    'access_token'    => $data['access_token'],
                    'refresh_token'   => $data['refresh_token'] ?? null,
                    'token_expires_at'=> now()->addSeconds($data['expires_in']),
                    'avatar'          => $data['avatar'] ?? null,
                    'is_active'       => true,
                ]
            );

            return redirect()->route('admin.social.accounts')->with('success', 'تم ربط TikTok بنجاح!');
        } catch (\Exception $e) {
            return redirect()->route('admin.social.accounts')
                ->with('error', 'فشل ربط TikTok: ' . $e->getMessage());
        }
    }

    /** بدء ربط YouTube */
    public function connectYouTube()
    {
        return redirect($this->socialMedia->getAuthUrl('youtube'));
    }

    /** Callback لـ YouTube */
    public function youTubeCallback(Request $request)
    {
        try {
            $data = $this->socialMedia->handleCallback('youtube', $request->code);

            SocialAccount::updateOrCreate(
                ['platform' => 'youtube', 'account_id' => $data['channel_id']],
                [
                    'account_name'    => $data['channel_name'],
                    'account_id'      => $data['channel_id'],
                    'access_token'    => $data['access_token'],
                    'refresh_token'   => $data['refresh_token'] ?? null,
                    'token_expires_at'=> now()->addSeconds($data['expires_in']),
                    'avatar'          => $data['avatar'] ?? null,
                    'is_active'       => true,
                ]
            );

            return redirect()->route('admin.social.accounts')->with('success', 'تم ربط YouTube بنجاح!');
        } catch (\Exception $e) {
            return redirect()->route('admin.social.accounts')
                ->with('error', 'فشل ربط YouTube: ' . $e->getMessage());
        }
    }

    /** ربط LinkedIn */
    public function connectLinkedIn()
    {
        return redirect($this->socialMedia->getAuthUrl('linkedin'));
    }

    public function linkedInCallback(Request $request)
    {
        try {
            $data = $this->socialMedia->handleCallback('linkedin', $request->code);
            SocialAccount::updateOrCreate(
                ['platform' => 'linkedin', 'account_id' => $data['account_id']],
                [
                    'account_name'    => $data['account_name'],
                    'access_token'    => $data['access_token'],
                    'token_expires_at'=> now()->addSeconds($data['expires_in']),
                    'avatar'          => $data['avatar'] ?? null,
                    'is_active'       => true,
                ]
            );
            return redirect()->route('admin.social.accounts')->with('success', 'تم ربط LinkedIn بنجاح!');
        } catch (\Exception $e) {
            return redirect()->route('admin.social.accounts')->with('error', 'فشل ربط LinkedIn: ' . $e->getMessage());
        }
    }

    /** ربط Twitter */
    public function connectTwitter()
    {
        return redirect($this->socialMedia->getAuthUrl('twitter'));
    }

    public function twitterCallback(Request $request)
    {
        try {
            $data = $this->socialMedia->handleCallback('twitter', $request->code);
            SocialAccount::updateOrCreate(
                ['platform' => 'twitter', 'account_id' => $data['account_id']],
                [
                    'account_name'    => $data['account_name'],
                    'access_token'    => $data['access_token'],
                    'refresh_token'   => $data['refresh_token'] ?? null,
                    'token_expires_at'=> now()->addSeconds($data['expires_in']),
                    'avatar'          => $data['avatar'] ?? null,
                    'is_active'       => true,
                ]
            );
            return redirect()->route('admin.social.accounts')->with('success', 'تم ربط Twitter بنجاح!');
        } catch (\Exception $e) {
            return redirect()->route('admin.social.accounts')->with('error', 'فشل ربط Twitter: ' . $e->getMessage());
        }
    }
}
