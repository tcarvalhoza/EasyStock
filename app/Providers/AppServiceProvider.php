<?php

declare(strict_types=1);

namespace App\Providers;

use App\Services\AuthService;
use App\Services\Contracts\AuthServiceInterface;
use App\Services\Contracts\ProductServiceInterface;
use App\Services\Contracts\SaleServiceInterface;
use App\Services\Contracts\UserServiceInterface;
use App\Services\ProductService;
use App\Services\SaleService;
use App\Services\UserService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(AuthServiceInterface::class, AuthService::class);
        $this->app->bind(UserServiceInterface::class, UserService::class);
        $this->app->bind(ProductServiceInterface::class, ProductService::class);
        $this->app->bind(SaleServiceInterface::class, SaleService::class);
    }

    public function boot(): void
    {
        //
    }
}
