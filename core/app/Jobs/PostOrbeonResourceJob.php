<?php

namespace App\Jobs;

use App\Services\OrbeonException;
use App\Services\OrbeonServiceContract;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

class PostOrbeonResourceJob implements ShouldQueue
{
    use Queueable;

    protected string $path;
    protected string $jsessionId;
    protected string $body;

    public function __construct(string $path, string $jsessionId, string $body)
    {
        $this->path = $path;
        $this->jsessionId = $jsessionId;
        $this->body = $body;
    }

    public function handle(OrbeonServiceContract $service): Exception|array|OrbeonException
    {
        try {
            return $service->postResource($this->path, $this->jsessionId, $this->body);
        } catch (OrbeonException $e) {
            return $e;
        }
    }
}
