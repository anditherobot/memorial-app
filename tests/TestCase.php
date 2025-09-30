<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    public function createApplication()
    {
        $app = require __DIR__.'/../bootstrap/app.php';

        $app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);

        $app->register(\Intervention\Image\Laravel\ServiceProvider::class);
        $app->boot();

        return $app;
    }
}