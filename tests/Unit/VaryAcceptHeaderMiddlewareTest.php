<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Http\Middleware\VaryAcceptHeader;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class VaryAcceptHeaderMiddlewareTest extends TestCase
{
    /**
     * Test that VaryAcceptHeader middleware sets the Vary header to Accept.
     *
     * @return void
     */
    public function testMiddlewareSetsVaryAcceptHeader()
    {
        $middleware = new VaryAcceptHeader();
        $request = Request::create('/purchases', 'GET');

        $response = $middleware->handle($request, function ($req) {
            return new Response('test content', 200);
        });

        $this->assertEquals('Accept', $response->headers->get('Vary'));
    }
}
