<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use App\Console\Commands\WatchInterpelliCommand;
use Illuminate\Support\Facades\Schedule;

Schedule::command('app:watch-interpelli-command')->everyFiveMinutes();
