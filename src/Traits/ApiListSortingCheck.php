<?php

namespace Saritasa\LaravelTestbed\Traits;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/** Checking the sorting work */
trait ApiListSortingCheck
{
    /**
     * Check that API returns list sorted by specified field (order by single field - check for each of passed fields).
     *
     * @param string $url Api endpoint to check
     * @param int $count Count of created models
     * @param array|string[] $sortingFields Sorting fields to check
     * @param array|string[] $auth Auth
     */
    public function assertSortingWorks(string $url, int $count, array $sortingFields, array $auth): void
    {
        collect($sortingFields)->each(function (string $orderBy) use ($url, $auth, $count) {
            $response = $this->getJson($url."?order_by=$orderBy&per_page=$count", $auth)->assertOk();

            // Set the singular form of English words for the nested keys
            $key = collect(explode('.', $orderBy))->map(function(string $part) {
                return Str::singular($part);
            })->implode('.');

            self::assertGreaterThanOrEqual($count, $response->original->count());

            for ($current = 0; $current < $count; $current++) {
                $next = $current + 1;

                $currentValue = $response->json("results.$current.$key");
                $nextValue = $response->json("results.$next.$key");
                if ($nextValue) {
                    if (is_numeric($currentValue)) {
                        $expected = (float)$nextValue;
                        $actual = (float)$currentValue;
                    } else {
                        $expected = 0;
                        $actual = strcmp($currentValue, $nextValue);
                    }
                    self::assertLessThanOrEqual($expected, $actual, "$orderBy must be ascending");
                }
            }

            $expectedValues = collect($response->json('results'))->pluck($key)->reverse();

            $expectedKeys = $expectedValues->keys()->reverse();
            $expectedCollection = $expectedKeys->combine($expectedValues);

            $responseReverse = $this->getJson($url."?order_by=-$orderBy&per_page=$count", $auth);

            $actualCollection = collect($responseReverse->json('results'))->pluck($key);

            $this->assertCollectionsEqual($expectedCollection, $actualCollection);

            // Sorting in descending order
            for ($current = $count; $current > 0; $current--) {
                $next = $current - 1;

                $currentValue = $response->json("results.$current.$key");
                $nextValue = $response->json("results.$next.$key");

                if ($currentValue) {
                    if (is_numeric($nextValue)) {
                        $expected = (float)$nextValue;
                        $actual = (float)$currentValue;
                    } else {
                        $expected = 0;
                        $actual = strcmp($currentValue, $nextValue);
                    }
                    self::assertGreaterThanOrEqual($expected, $actual, "$orderBy must be descending");
                }
            }
        });
    }

    /**
     *  Check that API returns list sorted by specified fields (order by multiple fields - check for combinations of passed fields).
     *
     * @param string $url Api endpoint to check
     * @param int $count Count of created models
     * @param array|string[] $sortingFields Sorting fields to check
     * @param array|string[] $auth Auth
     *
     * @return void
     */
    public function assertMultiSortingWorks(string $url, int $count, array $sortingFields, array $auth): void
    {
        $selectedSorting = collect($sortingFields)->random(2);
        $sortingString = $selectedSorting->implode(',');

        $response = $this->getJson($url."?order_by=$sortingString&per_page=$count", $auth)->assertOk();

        $selectedSorting = $selectedSorting->map(function (string $field) {
            return collect(explode('.', $field))->map(function (string $part) {
                return Str::singular($part);
            })->implode('.');
        });

        $mainSortingField = $selectedSorting->first();

        $results = collect($response->json('results'));

        self::assertGreaterThanOrEqual($count, $results->count());

        $groups = $results->groupBy($mainSortingField);
        $keys = $groups->keys();

        foreach ($keys as $i => $value) {
            $nextValue = $keys[$i+1] ?? null;

            if ($nextValue) {
                if (is_numeric($value)) {
                    $expected = (float)$nextValue;
                    $actual = (float)$value;
                } else {
                    $expected = 0;
                    $actual = strcmp($value, $nextValue);
                }
                self::assertLessThanOrEqual($expected, $actual, "$mainSortingField must be ascending");
            }
        }

        $selectedSorting->except($mainSortingField)->each(function (string $field) use ($groups, $count) {
            $groups->each(function (Collection $group) use ($field, $count) {
                if ($group->count() > 1) {
                    foreach ($group as $key => $value) {
                        $currentValue = Arr::dot($value)[$field];
                        $nextValue = Arr::dot($group->offsetGet($key))[$field];

                        if ($nextValue) {
                            if (is_numeric($currentValue)) {
                                $expected = (float)$nextValue;
                                $actual = (float)$currentValue;
                            } else {
                                $expected = 0;
                                $actual = strcmp($currentValue, $nextValue);
                            }
                            self::assertLessThanOrEqual($expected, $actual, "$field must be ascending");
                        }
                    }
                }
            });
        });

        // Check descending sort
        $expectedValues = collect($response->json('results'))->pluck($mainSortingField)->reverse();

        $expectedKeys = $expectedValues->keys()->reverse();
        $expectedCollection = $expectedKeys->combine($expectedValues);

        $responseReverse = $this->getJson($url."?order_by=-$sortingString&per_page=$count", $auth);

        $actualCollection = collect($responseReverse->json('results'))->pluck($mainSortingField);

        $this->assertCollectionsEqual($expectedCollection, $actualCollection);
    }

    /**
     * Simple compare collection with single field (like id, name, etc...) by default key .
     *
     * @param Collection $expected Expected collection to compare
     * @param Collection $actual Actual collection to compare
     *
     * @return void
     */
    public function assertCollectionsEqual(Collection $expected, Collection $actual): void
    {
        $expected->each(function ($value, int $key) use ($actual) {
            self::assertEquals($value, $actual->offsetGet($key));
        });
    }
}
