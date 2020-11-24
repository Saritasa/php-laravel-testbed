<?php

namespace Saritasa\LaravelTestbed\Tests\Feature;

use Mockery;
use TestApp\Services\MyService;

class SampleFeatureTest extends TestCase
{
    public function testHttpRequest()
    {
        return $this->getJson('api/test')->assertOk();
    }

    public function testWithMock()
    {
        $serviceMock = Mockery::mock(MyService::class);
        $serviceMock->shouldReceive('getData')->withArgs(['field1'])->andReturn([
            ['id' => 1, 'name' => 'Wood'],
            ['id' => 2, 'name' => 'Chuck'],
        ]);

        $this->app->bind(MyService::class, function () use ($serviceMock) {
            return $serviceMock;
        });

        $response = $this->getJson('api/test-order-by?order_by=field1');

        $data = $response->assertOk()->assertJsonCount(2, 'results')->json('results');
        [$user1, $user2] = $data;
        self::assertEquals(1, $user1['id']);
        self::assertEquals(2, $user2['id']);
    }
}
