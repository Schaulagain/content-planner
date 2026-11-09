<?php

use App\Livewire\Board;
use App\Livewire\Login;
use App\Livewire\NewIdea;
use App\Livewire\ShowIdea;
use App\Livewire\Week;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::livewire('/login', Login::class)->name('login')->middleware('guest');

Route::middleware('auth')->group(function () {
    Route::livewire('/', Board::class)->name('board');
    Route::livewire('/ideas/new', NewIdea::class)->name('ideas.new');
    Route::livewire('/ideas/{idea}', ShowIdea::class)->name('ideas.show');
    Route::livewire('/week', Week::class)->name('week');

    Route::post('/logout', function () {
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return redirect()->route('login');
    })->name('logout');
});
