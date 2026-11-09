<?php

namespace Tests\Feature;

use App\Enums\Platform;
use App\Enums\Stage;
use App\Models\Idea;
use App\Models\Post;
use App\Models\Review;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class RemindersTest extends TestCase
{
    use RefreshDatabase;

    private int $ntfyStatus = 200;

    protected function setUp(): void
    {
        parent::setUp();
        config(['planner.ntfy.topic' => 'my-plan']);
        Http::fake(fn () => Http::response('', $this->ntfyStatus));
    }

    public function test_reminds_once_when_stats_are_due(): void
    {
        $post = Post::publish(Idea::factory()->create(['place' => 'Boudhanath', 'stage' => Stage::Edited]), 'A caption', [Platform::TikTok], now());

        $this->travel(47)->hours();
        $this->artisan('reminders:stats')->assertSuccessful();
        Http::assertNothingSent();

        $this->travel(2)->hours();
        $this->artisan('reminders:stats');
        $this->artisan('reminders:stats');

        Http::assertSentCount(1);
        Http::assertSent(fn ($request) => $request->url() === 'https://ntfy.sh/my-plan'
            && str_contains($request->body(), 'Boudhanath'));
        $this->assertNotNull($post->refresh()->reminded_at);
    }

    public function test_a_reminder_is_kept_for_later_when_ntfy_is_not_set_up(): void
    {
        config(['planner.ntfy.topic' => null]);
        $post = Post::publish(Idea::factory()->create(['stage' => Stage::Edited]), 'A caption', [Platform::TikTok], now());

        $this->travel(3)->days();
        $this->artisan('reminders:stats');
        $this->assertNull($post->refresh()->reminded_at);

        config(['planner.ntfy.topic' => 'my-plan']);
        $this->artisan('reminders:stats');
        Http::assertSentCount(1);
    }

    public function test_a_failed_send_is_tried_again_next_hour(): void
    {
        $this->ntfyStatus = 500;
        $post = Post::publish(Idea::factory()->create(['stage' => Stage::Edited]), 'A caption', [Platform::TikTok], now());

        $this->travel(3)->days();
        $this->artisan('reminders:stats');

        $this->assertNull($post->refresh()->reminded_at);
    }

    public function test_no_reminder_once_the_stats_are_in(): void
    {
        $post = Post::publish(Idea::factory()->create(['stage' => Stage::Edited]), 'A caption', [Platform::TikTok], now());
        $post->stats->first()->record(['views' => 300]);

        $this->travel(3)->days();
        $this->artisan('reminders:stats');

        Http::assertNothingSent();
    }

    public function test_sunday_nudge_only_when_the_week_has_no_review(): void
    {
        $this->travelTo(Carbon::parse('2026-11-08 18:00', 'Asia/Kathmandu'));

        $this->artisan('reminders:review')->assertSuccessful();
        Http::assertSentCount(1);
        Http::assertSent(fn ($request) => $request->header('Title')[0] === 'Time for the weekly review');

        Review::create(['week_start' => '2026-11-02', 'learning' => 'Done']);
        $this->artisan('reminders:review');
        Http::assertSentCount(1);
    }
}
