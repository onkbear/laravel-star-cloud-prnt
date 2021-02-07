<?php

namespace Onkbear\StarCloudPRNT;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class StarCloudPRNTServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/star-cloud-prnt.php' => config_path('star-cloud-prnt.php'),
            ], 'config');
        }

        Route::macro('starCloudPRNT', function ($url) {
            Route::post($url, '\Onkbear\StarCloudPRNT\StarCloudPRNTController@handlePoll');
            Route::get($url, '\Onkbear\StarCloudPRNT\StarCloudPRNTController@handleJob');
            Route::delete($url, '\Onkbear\StarCloudPRNT\StarCloudPRNTController@handleDelete');
        });
    }

    /**
     * Register any package services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/star-cloud-prnt.php', 'star-cloud-prnt');
    }
}
