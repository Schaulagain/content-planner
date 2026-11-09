<?php

namespace Tests\Feature;

use App\Livewire\Login;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_every_page_needs_a_login(): void
    {
        foreach (['/', '/ideas/new', '/week'] as $page) {
            $this->get($page)->assertRedirect('/login');
        }
    }

    public function test_logging_in_opens_the_board(): void
    {
        User::factory()->create(['email' => 'me@example.com', 'password' => 'secret-password']);

        Livewire::test(Login::class)
            ->set('email', 'me@example.com')->set('password', 'wrong')
            ->call('login')
            ->assertHasErrors('email');

        Livewire::test(Login::class)
            ->set('email', 'me@example.com')->set('password', 'secret-password')
            ->call('login')
            ->assertRedirect('/');
    }

    public function test_the_login_can_be_created_from_the_command_line(): void
    {
        $this->artisan('user:create', ['email' => 'me@example.com'])
            ->expectsQuestion('Password', 'secret-password')
            ->assertSuccessful();

        $this->assertTrue(User::where('email', 'me@example.com')->exists());
    }
}
