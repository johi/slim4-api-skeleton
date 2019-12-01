<?php
declare(strict_types=1);

namespace App\Queries;

abstract class Query
{
    public function __invoke($data = [])
    {
        return $this->run($data);
    }

    abstract public function run($data);
}