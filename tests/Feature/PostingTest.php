<?php

namespace Tests\Feature;

use App\Enums\Platform;
use App\Enums\Stage;
use App\Livewire\ShowIdea;
use App\Models\Idea;
use App\Models\Series;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PostingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->create());
    }

    public function test_posting_records_the_caption_used_and_the_platforms(): void
    {
        $idea = Idea::factory()->create(['stage' => Stage::Edited, 'caption' => 'First draft']);

        Livewire::test(ShowIdea::class, ['idea' => $idea])
            ->assertSet('postCaption', 'First draft')
            ->set('postCaption', 'Nobody here is in a hurry, and nobody is late.')
            ->set('platforms', ['tiktok', 'instagram'])
            ->call('publish')
            ->assertHasNoErrors();

        $idea->refresh();
        $this->assertSame(Stage::Posted, $idea->stage);
        $this->assertSame('Nobody here is in a hurry, and nobody is late.', $idea->post->caption);
        $this->assertSame([Platform::TikTok, Platform::Instagram], $idea->post->stats->pluck('platform')->all());
    }

    public function test_posts_in_a_series_are_numbered_in_order(): void
    {
        $series = Series::create(['name' => 'Places that make you slow down']);
        [$first, $second] = Idea::factory()->count(2)->create(['series_id' => $series->id, 'stage' => Stage::Edited]);

        foreach ([$first, $second] as $idea) {
            Livewire::test(ShowIdea::class, ['idea' => $idea])->set('platforms', ['tiktok'])->call('publish');
        }

        $this->assertSame(1, $first->refresh()->post->series_number);
        $this->assertSame(2, $second->refresh()->post->series_number);
        $this->assertSame('Boudhanath · Places that make you slow down #2', $second->fill(['place' => 'Boudhanath'])->heading());
    }

    public function test_posting_needs_at_least_one_platform(): void
    {
        $idea = Idea::factory()->create(['stage' => Stage::Edited]);

        Livewire::test(ShowIdea::class, ['idea' => $idea])
            ->set('platforms', [])
            ->call('publish')
            ->assertHasErrors('platforms');

        $this->assertNull($idea->refresh()->post);
    }

    public function test_an_empty_stats_form_does_not_count(): void
    {
        $idea = Idea::factory()->create(['stage' => Stage::Edited]);
        $page = Livewire::test(ShowIdea::class, ['idea' => $idea])->set('platforms', ['tiktok'])->call('publish');

        $page->set('numbers.tiktok', ['views' => '', 'likes' => null])->call('recordStats', 'tiktok')->assertHasErrors('numbers.tiktok');

        $this->assertSame(Stage::Posted, $idea->refresh()->stage);
        $this->assertNull($idea->post->stats->first()->recorded_at);
    }

    public function test_the_idea_is_reviewed_once_every_platform_has_its_stats(): void
    {
        $idea = Idea::factory()->create(['stage' => Stage::Edited]);
        $page = Livewire::test(ShowIdea::class, ['idea' => $idea])
            ->set('platforms', ['tiktok', 'youtube'])
            ->call('publish');

        $page->set('numbers.tiktok', ['views' => 10000, 'likes' => 694, 'comments' => 5, 'shares' => 54, 'saves' => 37, 'average_watch_seconds' => 2.68])
            ->call('recordStats', 'tiktok')
            ->assertHasNoErrors();

        $this->assertSame(Stage::Posted, $idea->refresh()->stage);
        $this->assertSame(54, $idea->post->stats->firstWhere('platform', Platform::TikTok)->shares);

        $page->set('numbers.youtube', ['views' => 120])->call('recordStats', 'youtube');

        $this->assertSame(Stage::Reviewed, $idea->refresh()->stage);
    }
}
