<?php

namespace App\Providers;

use App\Repositories\Contracts\StationRepositoryInterface;
use App\Repositories\Contracts\UpdateRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Repositories\StationRepository;
use App\Repositories\UpdateRepository;
use App\Repositories\UserRepository;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        $this->app->bind(StationRepositoryInterface::class, StationRepository::class);
        $this->app->bind(UpdateRepositoryInterface::class, UpdateRepository::class);
    }
}
