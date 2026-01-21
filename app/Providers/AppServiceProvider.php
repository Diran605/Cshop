<?php

namespace App\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $prefix = '';

        $appUrlPath = parse_url(config('app.url'), PHP_URL_PATH);
        $prefix = $appUrlPath ? rtrim($appUrlPath, '/') : '';

        if (! app()->runningInConsole()) {
            $requestPrefix = rtrim(request()->getBasePath(), '/');
            if ($requestPrefix !== '') {
                $prefix = $requestPrefix;
            }
        }

        Livewire::setScriptRoute(function ($handle) use ($prefix) {
            if ($prefix !== '') {
                return Route::get($prefix.'/livewire/livewire.js', $handle);
            }

            return Route::get('/livewire/livewire.js', $handle);
        });

        Livewire::setUpdateRoute(function ($handle) use ($prefix) {
            if ($prefix !== '') {
                return Route::post($prefix.'/livewire/update', $handle);
            }

            return Route::post('/livewire/update', $handle);
        });
    }
}
