<?php

namespace App\Http\Controllers\front;

use App\Http\Controllers\Controller;
use App\Http\Traits\Message_Trait;
use App\Models\front\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;

class ReviewsController extends Controller
{
    use Message_Trait;

    public function index()
    {
        return view('website.reviews', ['ratingStats' => $this->ratingStats()]);
    }

    /**
     * Rating statistics computed from approved + active reviews only (the client wants real data, never
     * invented figures). Returns null when there is nothing real to show.
     */
    private function ratingStats(): ?array
    {
        $counts = Review::approved()->active()
            ->whereBetween('rating', [1, 5])
            ->selectRaw('rating, COUNT(*) as total')
            ->groupBy('rating')
            ->pluck('total', 'rating');

        $total = (int) $counts->sum();
        if ($total === 0) {
            return null;
        }

        $sum = 0;
        $distribution = [];
        for ($star = 5; $star >= 1; $star--) {
            $count = (int) ($counts[$star] ?? 0);
            $sum += $star * $count;
            $distribution[$star] = (int) round($count * 100 / $total);
        }

        $average = round($sum / $total, 1);
        $full = (int) floor($average);
        $half = ($average - $full) >= 0.5 ? 1 : 0;

        return [
            'average' => number_format($average, 1),
            'total' => $total,
            'full_stars' => $full,
            'half_star' => $half,
            'empty_stars' => 5 - $full - $half,
            'distribution' => $distribution,
        ];
    }

    public function store(Request $request)
    {
        $data = $request->all();
        //dd($data);
        $rules = [
            'name' => 'required|string|max:120',
            'rating' => 'required|integer|between:1,5',
            'notes' => 'required|string|max:2000'
        ];
        $messages = [
            'name.required' => ' من فضلك ادخل الاسم الخاص بك  ',
            'rating.required' => ' من فضلك ادخل تقييمك  ',
            'notes.required' => ' من فضلك ادخل ملاحظاتك  ',
            'rating.between' => ' اختر تقييمًا من نجمة إلى 5 نجوم ',
            'rating.integer' => ' اختر تقييمًا من نجمة إلى 5 نجوم ',
        ];
        $validator = Validator::make($data, $rules, $messages);
        if ($validator->fails()) {
            return Redirect()->back()->withInput()->withErrors($validator);
        }
        $review = new Review();
        // The reviews table now holds testimonials (columns renamed in 2026_03_06);
        // site visitors' reviews wait for admin approval.
        $review->client_name = $data['name'];
        $review->rating = (int) $data['rating'];
        $review->content = $data['notes'];
        $review->is_approved = false;
        $review->save();
        return $this->success_message(' شكرا لك علي تقيمك لنا ');
    }
}
