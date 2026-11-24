<?php

namespace Tests\Webfox\Xero\Feature\Routes;

use Illuminate\Support\Facades\Route;
use Orchestra\Testbench\Attributes\DefineEnvironment;
use Tests\Webfox\Xero\TestCase;

class RouteMiddlewareTest extends TestCase
{
    public function test_routes_use_the_web_middleware_by_default()
    {
        $this->assertEquals(['web'], Route::getRoutes()->getByName('xero.auth.authorize')->middleware());
        $this->assertEquals(['web'], Route::getRoutes()->getByName('xero.auth.callback')->middleware());
    }

    #[DefineEnvironment('useAuthMiddleware')]
    public function test_routes_use_the_configured_middleware()
    {
        $this->assertEquals(['web', 'auth'], Route::getRoutes()->getByName('xero.auth.authorize')->middleware());
        $this->assertEquals(['web', 'auth'], Route::getRoutes()->getByName('xero.auth.callback')->middleware());
    }

    #[DefineEnvironment('useAuthMiddleware')]
    public function test_guests_cannot_visit_routes_protected_by_the_configured_middleware()
    {
        $this->getJson(route('xero.auth.authorize'))->assertUnauthorized();
        $this->getJson(route('xero.auth.callback', ['state' => 'foo', 'code' => 'bar']))->assertUnauthorized();
    }

    protected function useAuthMiddleware($app): void
    {
        $app['config']->set('xero.routes.middleware', ['web', 'auth']);
    }
}
