<?php

namespace App\Providers;

use App\Models\Of\OrbeonFormDefinition;
use App\Policies\OFDefinitionPolicy;
use App\Services\OrbeonService;
use App\Services\OrbeonServiceContract;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{

    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(OrbeonServiceContract::class, OrbeonService::class);

        $baseUrl = env('ORBEON_BASE_URL', 'http://172.23.0.9:8080');
        $this->app->singleton(Client::class, function () use ($baseUrl) {
            return new Client([
                'base_uri' => $baseUrl,
            ]);
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(OrbeonFormDefinition::class, OFDefinitionPolicy::class);
    }
}
