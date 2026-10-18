<?php

namespace App\Http\Controllers\front;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

/**
 * «انضم كمستقل» (client file «المربعات الخمسة»): one page that explains joining and, for a
 * freelancer, shows what is left (the existing account, profile, specializations, portfolio
 * and admin review — nothing is duplicated).
 */
class FreelancerJoinController extends Controller
{
    public function show()
    {
        $user = Auth::user();
        $checklist = null;
        if ($user?->isFreelancer()) {
            $user->markFreelancerProfileCompleted();
            $checklist = $user->freelancerChecklist();
        }

        return view('website.freelancers.join', compact('user', 'checklist'));
    }
}
