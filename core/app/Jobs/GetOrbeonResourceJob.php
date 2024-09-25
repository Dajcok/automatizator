<?php

namespace App\Jobs;

use App\Services\OrbeonException;
use App\Services\OrbeonServiceContract;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

class GetOrbeonResourceJob implements ShouldQueue
{
    use Queueable;

    protected string $path;
    protected string $jsessionId;

    public function __construct(string $path, string $jsessionId)
    {
        $this->path = $path;
        $this->jsessionId = $jsessionId;
    }

    public function handle(OrbeonServiceContract $service): Exception|array|OrbeonException
    {
        try {
            return $service->getResource($this->path, $this->jsessionId);
        } catch (OrbeonException $e) {
            return $e;
        }
    }
}
