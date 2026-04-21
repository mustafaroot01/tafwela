<?php

namespace App\Providers;

use App\Events\StationUpdated;
use App\Listeners\NotifyFavoriteUsers;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
    }

    public function boot(): void
    {
        Event::listen(StationUpdated::class, NotifyFavoriteUsers::class);
    }
}
