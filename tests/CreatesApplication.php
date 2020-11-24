<?php

namespace Saritasa\LaravelTestbed\Tests;

use Illuminate\Contracts\Console\Kernel;
use Symfony\Component\HttpKernel\HttpKernelInterface;

trait CreatesApplication
{
    /**
     * Creates the application.
     *
     * @return HttpKernelInterface
     */
    public function createApplication()
    {
        $app = require __DIR__.'/../test-app/bootstrap/app.php';

        $app->make(Kernel::class)->bootstrap();

        return $app;
    }
}
