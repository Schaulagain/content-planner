<?php

namespace Tests\Feature;

use App\Enums\Stage;
use App\Livewire\Board;
use App\Livewire\NewIdea;
use App\Livewire\ShowIdea;
use App\Models\Idea;
use App\Models\Series;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class IdeasTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->create());
    }

    public function test_a_new_idea_lands_on_the_board(): void
    {
        $series = Series::create(['name' => 'Places that make you slow down']);

        Livewire::test(NewIdea::class)
            ->set('place', 'Boudhanath')
            ->set('caption', 'Everyone here is walking in circles, and somehow that\'s the point.')
            ->set('seriesId', $series->id)
            ->call('save')
            ->assertRedirect('/');

        $idea = Idea::sole();
        $this->assertSame(Stage::Idea, $idea->stage);
        $this->assertTrue($idea->series->is($series));

        Livewire::test(Board::class)->assertSee('Boudhanath');
    }

    public function test_an_idea_can_start_a_new_series(): void
    {
        Livewire::test(NewIdea::class)
            ->set('place', 'Ilam tea garden')
            ->set('newSeries', 'Tea country')
            ->call('save');

        $this->assertSame('Tea country', Idea::sole()->series->name);
    }

    public function test_an_idea_keeps_its_notes(): void
    {
        Livewire::test(NewIdea::class)
            ->set('place', 'Nagarkot')
            ->set('notes', 'Go before 6, the clouds sit below the ridge.')
            ->call('save');

        $this->assertSame('Go before 6, the clouds sit below the ridge.', Idea::sole()->notes);
    }

    public function test_an_idea_can_be_edited(): void
    {
        $idea = Idea::factory()->create(['place' => 'Phewa', 'notes' => null]);
        $series = Series::create(['name' => 'Tea country']);

        Livewire::test(ShowIdea::class, ['idea' => $idea])
            ->assertSet('place', 'Phewa')
            ->set('place', 'Phewa Lake at dawn')
            ->set('caption', 'Nobody here is in a hurry.')
            ->set('notes', 'Boats start at 5:30.')
            ->set('seriesId', $series->id)
            ->call('saveDetails')
            ->assertHasNoErrors();

        $idea->refresh();
        $this->assertSame('Phewa Lake at dawn', $idea->place);
        $this->assertSame('Nobody here is in a hurry.', $idea->caption);
        $this->assertSame('Boats start at 5:30.', $idea->notes);
        $this->assertTrue($idea->series->is($series));
    }

    public function test_deleting_an_idea_removes_its_photo(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('ideas/spot.jpg', 'jpeg');
        $idea = Idea::factory()->create(['photo_path' => 'ideas/spot.jpg']);

        Livewire::test(ShowIdea::class, ['idea' => $idea])->call('deleteIdea')->assertRedirect('/');

        $this->assertNull(Idea::find($idea->id));
        Storage::disk('public')->assertMissing('ideas/spot.jpg');
    }

    public function test_an_idea_needs_a_place(): void
    {
        Livewire::test(NewIdea::class)->call('save')->assertHasErrors('place');

        $this->assertSame(0, Idea::count());
    }

    public function test_an_idea_keeps_its_photo_and_location(): void
    {
        Storage::fake('public');

        Livewire::test(NewIdea::class)
            ->set('place', 'Phewa Lake')
            ->set('photo', UploadedFile::fake()->create('spot.jpg', 200, 'image/jpeg'))
            ->set('latitude', 28.2096)
            ->set('longitude', 83.9592)
            ->call('save');

        $idea = Idea::sole();
        Storage::disk('public')->assertExists($idea->photo_path);
        $this->assertSame(28.2096, $idea->latitude);
    }

    public function test_ideas_move_along_the_stages_by_hand_up_to_edited(): void
    {
        $idea = Idea::factory()->create();

        $board = Livewire::test(Board::class);
        $board->call('advance', $idea->id);
        $this->assertSame(Stage::Filmed, $idea->refresh()->stage);

        $board->call('advance', $idea->id)->call('advance', $idea->id);
        $this->assertSame(Stage::Edited, $idea->refresh()->stage, 'posting goes through its own form');

        $board->call('back', $idea->id);
        $this->assertSame(Stage::Filmed, $idea->refresh()->stage);
    }
}
