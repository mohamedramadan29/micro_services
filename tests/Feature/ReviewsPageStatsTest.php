<?php

namespace Tests\Feature;

use App\Models\front\Review;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** The public reviews page shows real rating statistics only (no invented figures). */
class ReviewsPageStatsTest extends TestCase
{
    use RefreshDatabase;

    private function makeReview(int $rating, bool $approved = true, bool $active = true): Review
    {
        return Review::create([
            'client_name' => 'عميل',
            'content' => 'تجربة',
            'rating' => $rating,
            'is_approved' => $approved,
            'is_active' => $active,
        ]);
    }

    public function test_statistics_section_is_hidden_without_approved_reviews(): void
    {
        // Unapproved / inactive reviews must not count.
        $this->makeReview(5, approved: false);
        $this->makeReview(4, active: false);

        $this->get(route('reviews.index'))
            ->assertOk()
            ->assertDontSee('التقييم الإجمالي')
            ->assertDontSee('توزيع التقييمات')
            ->assertDontSee('4.8');
    }

    public function test_statistics_reflect_approved_active_reviews(): void
    {
        // 5,5,4,3 → average 4.25 → 4.3; 5★ 50%, 4★ 25%, 3★ 25%, 2★ 0%, 1★ 0%
        $this->makeReview(5);
        $this->makeReview(5);
        $this->makeReview(4);
        $this->makeReview(3);
        // Excluded from the stats.
        $this->makeReview(1, approved: false);
        $this->makeReview(1, active: false);

        $response = $this->get(route('reviews.index'))->assertOk();

        $response->assertSee('التقييم الإجمالي')
            ->assertSee('<div class="overall-rating">4.3</div>', false)
            ->assertDontSee('4.8');

        $stats = $response->viewData('ratingStats');
        $this->assertSame('4.3', $stats['average']);
        $this->assertSame(4, $stats['total']);
        $this->assertSame([5 => 50, 4 => 25, 3 => 25, 2 => 0, 1 => 0], $stats['distribution']);
        $this->assertSame([4, 0, 1], [$stats['full_stars'], $stats['half_star'], $stats['empty_stars']]);

        $html = $response->getContent();
        $this->assertSame(4, substr_count($html, '<i class="bi bi-star-fill"></i>'));
        $this->assertStringContainsString('style="width: 50%;"', $html);
        $this->assertStringContainsString('<span class="fw-bold">25%</span>', $html);
        $this->assertStringContainsString('<span class="fw-bold">0%</span>', $html);
    }
}
