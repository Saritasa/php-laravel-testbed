<?php

namespace Saritasa\LaravelTestbed\Tests;

/**
 * Check that unit tests configured and can be run.
 */
class DemoTest extends TestCase
{
    use CreatesApplication;

    protected function setUp()
    {
        parent::setUp();
        $this->createApplication();
    }

    /**
     * Simple test to check that unit tests working.
     */
    public function testUnitTestAvailable()
    {
        $this->assertTrue(true);
    }

    /**
     * Check, that main project class can is accessible in tests, using autoload
     */
    public function testExample()
    {
        $example = new \Saritasa\LaravelTestbed\Example();
        $this->assertEquals('test', $example->echoPhrase('test'));
    }
}
