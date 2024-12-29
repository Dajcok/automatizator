<?php

namespace App\Http\Controllers\Of;
use App\Http\Controllers\Controller;
use App\Services\OrbeonException;
use App\Services\OrbeonServiceContract;

class OrbeonProxyController extends Controller
{
    public function __construct(
        private readonly OrbeonServiceContract $service
    )
    {
        parent::__construct();
    }

    public function get(string $path)
    {
        try {
            $resource = $this->service->getResource($path, request()->cookie('JSESSIONID', ''));

            return response($resource['content'])
		    ->header('Content-Type', $resource['content-type'])
		    ->header('Content-Encoding', $resource['content-encoding'])
	    	    ->header('Cache-Control', 'public, max-age=31536000, immutable');
        } catch (OrbeonException $e) {
            return response($e->getMessage(), $e->getCode());
        }
    }

    public function post(string $path)
    {
        $body = request()->getContent();

        try {
            $resource = $this->service->postResource($path, request()->cookie('JSESSIONID', ''), $body);

            return response($resource['content'])
                ->header('Content-Type', $resource['content-type']);
        } catch (OrbeonException $e) {
            return response($e->getMessage(), $e->getCode());
        }
    }
}
