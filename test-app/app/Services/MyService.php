<?php

namespace TestApp\Services;

use Illuminate\Database\Eloquent\Collection;

class MyService
{
    public function getData($orderBy): Collection
    {
        return new Collection([]);
    }
}
