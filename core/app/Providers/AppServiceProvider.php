<?php

namespace App\Providers;

use App\Http\Resources\OFDefinitionResource;
use App\Models\OrbeonFormDefinition;
use App\Policies\OFDefinitionPolicy;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use App\Services\OrbeonService;
use App\Services\OrbeonServiceContract;

class AppServiceProvider extends ServiceProvider
{

    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(OFDefinitionResource::class);
        $this->app->bind(OrbeonServiceContract::class, OrbeonService::class);

        $baseUrl = env('ORBEON_BASE_URL', 'http://localhost:8080');
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
