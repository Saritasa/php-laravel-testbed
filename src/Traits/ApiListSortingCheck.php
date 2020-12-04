<?php

namespace Saritasa\LaravelTestbed\Traits;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use PHPUnit\Framework\AssertionFailedError;

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
     * @param string|null $envelope Results envelope (like 'results', 'items', etc.)
     *
     * @return void
     */
    public function assertSortingWorks(
        string $url,
        int $count,
        array $sortingFields,
        array $auth,
        ?string $envelope = null
    ): void {
        collect($sortingFields)->each(function (string $sortingField) use ($url, $auth, $count, $envelope) {
            $response = $this->getJson($url."?order_by=$sortingField&per_page=$count", $auth)->assertOk();

            $results = collect($response->json($envelope));

            self::assertGreaterThanOrEqual($count, $results->count());

            $this->assertBaseSorting($results, $sortingField);

            $reverseResponse = $this->getJson($url."?order_by=-$sortingField&per_page=$count", $auth);

            $this->assertReverseSorting($results, collect($reverseResponse->json($envelope)), $sortingField);
        });
    }

    /**
     * Check that API returns list sorted by specified fields
     *  (order by multiple fields - check for combinations of passed fields).
     *
     * @param string $url Api endpoint to check
     * @param int $count Count of created models
     * @param array|string[] $sortingFields Sorting fields to check
     * @param array|string[] $auth Auth
     * @param string|null $envelope Results envelope (like 'results', 'items', etc.)
     *
     * @return void
     */
    public function assertMultiSortingWorks(
        string $url,
        int $count,
        array $sortingFields,
        array $auth,
        ?string $envelope
    ): void {
        $selectedSorting = collect($sortingFields)->random(2);
        $sortingString = $selectedSorting->implode(',');

        $response = $this->getJson($url."?order_by=$sortingString&per_page=$count", $auth)->assertOk();

        $mainSortingField = $selectedSorting->first();

        $results = collect($response->json($envelope));

        $this->assertBaseSorting($results, $mainSortingField);

        self::assertGreaterThanOrEqual($count, $results->count());

        $groups = $results->groupBy($this->setSingularWordForm($mainSortingField));

        $selectedSorting->except(0)->each(function (string $field) use ($groups, $count) {
            $groups->each(function (Collection $group) use ($field, $count) {
                $groupCount = $group->count();
                if ($groupCount > 1) {
                    $this->assertBaseSorting($group, $field);
                }
            });
        });

        $responseReverse = $this->getJson($url."?order_by=-$sortingString&per_page=$count", $auth);

        $this->assertReverseSorting($results, collect($responseReverse->json($envelope)), $mainSortingField);
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

    /**
     * Check that the basic sort is working (nullable values must be at the beginning or at the end).
     *
     * @param Collection $data Sorted list
     * @param string $sortingField Sorting field
     *
     * @return void
     */
    public function assertBaseSorting(Collection $data, string $sortingField): void
    {
        $field = $this->setSingularWordForm($sortingField);

        $filteredData = $data->whereNotNull($field);

        $firstKey = $filteredData->keys()->first();
        $lastKey = $filteredData->keys()->last();

        for ($i = $firstKey; $i < $lastKey; $i++) {
            $current = $filteredData[$i] ?? null;
            $next = $filteredData[$i+1] ?? null;

            if (!$next) {
                throw new AssertionFailedError('Nullable values should be at the beginning or at the end');
            }

            $currentValue = Arr::dot($current)[$field];
            $nextValue = Arr::dot($next)[$field];

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

    /**
     * Compare the actual reversed results and results from the reversed response (descending sort).
     *
     * @param Collection $results Actual results to reverse
     * @param Collection $reverseResults Results from the reversed response
     * @param string $sortingField Sorting field
     */
    public function assertReverseSorting(Collection $results, Collection $reverseResults, string $sortingField)
    {
        $field = $this->setSingularWordForm($sortingField);

        $expectedValues = $results->pluck($field)->reverse();
        $expectedKeys = $expectedValues->keys()->reverse();
        $expectedCollection = $expectedKeys->combine($expectedValues);

        $actualCollection = $reverseResults->pluck($field);

        $this->assertCollectionsEqual($expectedCollection, $actualCollection);
    }

    /**
     * Set the singular form of English words.
     *
     * @param string $field Field to modify
     *
     * @return string
     */
    public function setSingularWordForm(string $field): string
    {
        return collect(explode('.', $field))->map(function (string $part) {
            return Str::singular($part);
        })->implode('.');
    }
}
