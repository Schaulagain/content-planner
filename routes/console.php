<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('reminders:stats')->hourly();
Schedule::command('reminders:review')->weeklyOn(0, '18:00')->timezone(config('planner.timezone'));
