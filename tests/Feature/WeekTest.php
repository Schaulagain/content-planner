<?php

namespace Tests\Feature;

use App\Enums\Platform;
use App\Enums\Stage;
use App\Livewire\Week;
use App\Models\Idea;
use App\Models\Post;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Livewire\Livewire;
use Tests\TestCase;

class WeekTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->create());
        // Sunday 8 Nov 2026, 7 pm in Kathmandu.
        $this->travelTo(Carbon::parse('2026-11-08 19:00', 'Asia/Kathmandu'));
    }

    public function test_shows_this_weeks_posts_against_the_target(): void
    {
        $this->postIdea('Boudhanath', '2026-11-04 20:00');
        $this->postIdea('Swayambhu', '2026-11-07 19:30');
        $this->postIdea('Last week', '2026-11-01 19:00');

        Livewire::test(Week::class)
            ->assertSee('Boudhanath')
            ->assertSee('Swayambhu')
            ->assertDontSee('Last week')
            ->assertSee('2 of 3');
    }

    public function test_saving_the_learning_keeps_one_review_per_week(): void
    {
        Livewire::test(Week::class)
            ->set('learning', 'Captions with a thought get shared more.')
            ->call('save')
            ->assertHasNoErrors();

        Livewire::test(Week::class)
            ->assertSet('learning', 'Captions with a thought get shared more.')
            ->set('learning', 'Captions with a thought get shared a lot more.')
            ->call('save');

        $review = Review::sole();
        $this->assertSame('2026-11-02', $review->week_start->toDateString());
        $this->assertSame('Captions with a thought get shared a lot more.', $review->learning);
    }

    public function test_earlier_learnings_stay_visible(): void
    {
        Review::create(['week_start' => '2026-10-26', 'learning' => 'Shorter clips get watched to the end.']);

        Livewire::test(Week::class)->assertSee('Shorter clips get watched to the end.');
    }

    private function postIdea(string $place, string $at): void
    {
        Post::publish(
            Idea::factory()->create(['place' => $place, 'stage' => Stage::Edited]),
            'A caption',
            [Platform::TikTok],
            Carbon::parse($at, 'Asia/Kathmandu'),
        );
    }
}
