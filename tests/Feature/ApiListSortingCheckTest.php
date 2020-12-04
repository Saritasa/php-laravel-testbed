<?php

namespace Saritasa\LaravelTestbed\Tests\Feature;

use Mockery;
use PHPUnit\Framework\AssertionFailedError;
use Saritasa\LaravelTestbed\Traits\ApiListSortingCheck;
use TestApp\Services\MyService;

class ApiListSortingCheckTest extends TestCase
{
    use ApiListSortingCheck;

    /**
     * Test sorting by single column.
     *
     * @param mixed $resultData Array of data
     * @param string $sortingField Sorting field
     * @param \Exception|null $expectedException Expected exception
     *
     * @dataProvider sortBySingleFieldDataExamples
     */
    public function testSortBySingleField(array $resultData, string $sortingField, $expectedException = null)
    {
        if ($expectedException) {
            $this->expectException($expectedException);
        }

        $serviceMock = Mockery::mock(MyService::class);
        $serviceMock->shouldReceive('getData')->withArgs([$sortingField])->andReturn($resultData);

        $resultCollection = collect($resultData);
        $serviceMock->shouldReceive('getData')->withArgs(["-".$sortingField])
            ->andReturn($resultCollection->reverse()->toArray());

        $this->app->bind(MyService::class, function () use ($serviceMock) {
            return $serviceMock;
        });

        $count = $resultCollection->count();

        $envelope = 'results';

        $this->assertSortingWorks("api/test-order-by", $count, [$sortingField], [], $envelope);
    }

    /** Data for testing sorting by single field */
    public function sortBySingleFieldDataExamples(): array
    {
        return [
            'correct sorted list (by name)' => [
               [
                   ['id' => 1, 'name' => "Alex"],
                   ['id' => 2, 'name' => "Bill"],
                   ['id' => 3, 'name' => "Chuck"],
                   ['id' => 4, 'name' => "Tim"],
                   ['id' => 5, 'name' => "Wood"],
               ],
                'name'
            ],
            'incorrect sorted list (by name)' => [
                [
                    ['id' => 2, 'name' => "Bill"],
                    ['id' => 1, 'name' => "Alex"],
                    ['id' => 5, 'name' => "Wood"],
                    ['id' => 3, 'name' => "Chuck"],
                    ['id' => 4, 'name' => "Tim"],
                ],
                'name',
                AssertionFailedError::class,
            ],
            'correct sorted list with nullable values (by name)' => [
                [
                    ['id' => 5, 'name' => null],
                    ['id' => 1, 'name' => null],
                    ['id' => 2, 'name' => "Bill"],
                    ['id' => 3, 'name' => "Chuck"],
                    ['id' => 4, 'name' => "Tim"],
                ],
                'name',
            ],
            'incorrect sorted list with nullable values (by name)' => [
               [
                    ['id' => 2, 'name' => "Bill"],
                    ['id' => 3, 'name' => "Chuck"],
                    ['id' => 1, 'name' => null],
                    ['id' => 5, 'name' => null],
                    ['id' => 4, 'name' => "Tim"],
                ],
                'name',
                AssertionFailedError::class,
            ],
            'correct sorted list with nested keys (by contact.name)' => [
                [
                    ['id' => 1, 'contact' => ['name' => 'Alex']],
                    ['id' => 2, 'contact' => ['name' => 'Bill']],
                    ['id' => 3, 'contact' => ['name' => 'Chuck']],
                    ['id' => 4, 'contact' => ['name' => 'Tim']],
                    ['id' => 5, 'contact' => ['name' => 'Wood']],
                ],
                'contacts.name'
            ],
            'incorrect sorted list with nested keys (by contact.name)' => [
                [
                    ['id' => 2, 'contact' => ['name' => 'Bill']],
                    ['id' => 3, 'contact' => ['name' => 'Chuck']],
                    ['id' => 4, 'contact' => ['name' => 'Tim']],
                    ['id' => 5, 'contact' => ['name' => 'Wood']],
                    ['id' => 1, 'contact' => ['name' => 'Alex']],
                ],
                'contacts.name',
                AssertionFailedError::class
            ],
            'correct sorted list with nested keys and nullable values (by contact.name)' => [
                [
                    ['id' => 3, 'contact' => ['name' => null]],
                    ['id' => 4, 'contact' => ['name' => null]],
                    ['id' => 1, 'contact' => ['name' => 'Alex']],
                    ['id' => 2, 'contact' => ['name' => 'Bill']],
                    ['id' => 5, 'contact' => ['name' => 'Wood']],
                ],
                'contacts.name'
            ],
            'incorrect sorted list with nested keys and nullable values (by contact.name)' => [
                [
                    ['id' => 1, 'contact' => ['name' => 'Alex']],
                    ['id' => 2, 'contact' => ['name' => 'Bill']],
                    ['id' => 4, 'contact' => ['name' => null]],
                    ['id' => 5, 'contact' => ['name' => null]],
                    ['id' => 3, 'contact' => ['name' => 'Chuck']],
                ],
                'contacts.name',
                AssertionFailedError::class
            ],
            'correct sorted list with 2 level nested keys (by company.contact.name)' => [
                [
                    ['id' => 3, 'company' => [
                        'name' => 'Alex inc.',
                        'contact' => ['name' => 'Alex'],
                    ]],
                    ['id' => 4, 'company' => [
                        'name' => 'Bill inc.',
                        'contact' => ['name' => 'Bill'],
                    ]],
                    ['id' => 1, 'company' => [
                        'name' => 'Chuck inc.',
                        'contact' => ['name' => 'Chuck'],
                    ]],
                    ['id' => 2, 'company' => [
                        'name' => 'Tim inc.',
                        'contact' => ['name' => 'Tim'],
                    ]],
                    ['id' => 5, 'company' => [
                        'name' => 'Wood inc.',
                        'contact' => ['name' => 'Wood'],
                    ]],
                ],
                'companies.contacts.name'
            ],
        ];
    }

    /**
     * Test sorting by several columns.
     *
     * @param mixed $resultData Array of data
     * @param string[] $sortingFields Sorting fields
     * @param \Exception|null $expectedException Expected exception
     *
     * @dataProvider sortByMultipleFieldsDataExamples
     */
    public function testSortByMultipleFields(array $resultData, array $sortingFields, $expectedException = null)
    {
        if ($expectedException) {
            $this->expectException($expectedException);
        }

        $sortingString = collect($sortingFields)->implode(',');

        $serviceMock = Mockery::mock(MyService::class);
        $serviceMock->shouldReceive('getData')->with($sortingString)->andReturn($resultData);

        $resultCollection = collect($resultData);
        $serviceMock->shouldReceive('getData')->with("-".$sortingString)
            ->andReturn($resultCollection->reverse()->toArray());

        $this->app->bind(MyService::class, function () use ($serviceMock) {
            return $serviceMock;
        });

        $count = $resultCollection->count();

        $envelope = 'results';

        $this->assertMultiSortingWorks("api/test-order-by", $count, $sortingFields, [], $envelope);
    }

    /** Data for testing sorting by several fields */
    public function sortByMultipleFieldsDataExamples(): array
    {
        return [
            'correct sorted list (by name and id)' => [
                [
                    ['id' => 1, 'name' => "Alex"],
                    ['id' => 2, 'name' => "Alex"],
                    ['id' => 3, 'name' => "Chuck"],
                    ['id' => 4, 'name' => "Tim"],
                    ['id' => 5, 'name' => "Wood"],
                ],
                ['name', 'id']
            ],
            'incorrect sorted list (by name and id)' => [
                [
                    ['id' => 2, 'name' => "Alex"],
                    ['id' => 1, 'name' => "Alex"],
                    ['id' => 3, 'name' => "Chuck"],
                    ['id' => 4, 'name' => "Tim"],
                    ['id' => 5, 'name' => "Wood"],
                ],
                ['name', 'id'],
                AssertionFailedError::class
            ],
            'correct sorted list (by id and name)' => [
                [
                    ['id' => 1, 'name' => "Bill"],
                    ['id' => 1, 'name' => "Chuck"],
                    ['id' => 1, 'name' => "Tim"],
                    ['id' => 1, 'name' => "Wood"],
                    ['id' => 2, 'name' => "Alex"],
                ],
                ['id','name']
            ],
            'incorrect sorted list (by id and name)' => [
                [
                    ['id' => 1, 'name' => "Bill"],
                    ['id' => 1, 'name' => "Chuck"],
                    ['id' => 2, 'name' => "Alex"],
                    ['id' => 1, 'name' => "Tim"],
                    ['id' => 1, 'name' => "Wood"],
                ],
                ['id','name'],
                AssertionFailedError::class
            ],
            'correct sorted list with nullable values (by id and name)' => [
                [
                    ['id' => 1, 'name' => "Bill"],
                    ['id' => 1, 'name' => "Chuck"],
                    ['id' => 1, 'name' => "Tim"],
                    ['id' => 1, 'name' => null],
                    ['id' => null, 'name' => null],

                ],
                ['id','name']
            ],
            'incorrect sorted list with nullable values in first field (by id and name)' => [
                [
                    ['id' => 1, 'name' => "Bill"],
                    ['id' => null, 'name' => null],
                    ['id' => 1, 'name' => "Chuck"],
                    ['id' => 1, 'name' => "Tim"],
                    ['id' => 1, 'name' => null],

                ],
                ['id','name'],
                AssertionFailedError::class,
            ],
            'incorrect sorted list with nullable values in second field (by id and name)' => [
                [
                    ['id' => 1, 'name' => "Bill"],
                    ['id' => 1, 'name' => null],
                    ['id' => 1, 'name' => "Chuck"],
                    ['id' => 1, 'name' => "Tim"],
                    ['id' => null, 'name' => null],
                ],
                ['id','name'],
                AssertionFailedError::class,
            ],
            'correct sorted list with 2 level nested keys (by company.contact.name, name)' => [
               [
                    ['id' => 3, 'company' => [
                        'name' => 'Alex inc.',
                        'contact' => ['name' => 'Alex'],
                    ]],
                    ['id' => 4, 'company' => [
                        'name' => 'Bill inc.',
                        'contact' => ['name' => 'Alex'],
                    ]],
                    ['id' => 1, 'company' => [
                        'name' => 'Chuck inc.',
                        'contact' => ['name' => 'Alex'],
                    ]],
                    ['id' => 2, 'company' => [
                        'name' => 'Tim inc.',
                        'contact' => ['name' => 'Tim'],
                    ]],
                    ['id' => 5, 'company' => [
                        'name' => 'Wood inc.',
                        'contact' => ['name' => 'Wood'],
                    ]],
                ],
                ['companies.contacts.name', 'name']
            ],
        ];
    }
}
