<?php

namespace Database\Seeders;

use App\Models\Series;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        Series::firstOrCreate(['name' => 'Places that make you slow down']);
    }
}
