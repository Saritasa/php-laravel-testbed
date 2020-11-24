<?php

namespace Saritasa\LaravelTestbed\Tests\Feature;

class SampleFeatureTest extends TestCase
{
    function testHttpRequest()
    {
        return $this->getJson('api/test')->assertOk();
    }
}
