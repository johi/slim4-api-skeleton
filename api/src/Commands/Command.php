<?php
declare(strict_types=1);

namespace App\Commands;

abstract class Command
{
    public function __invoke($data = [])
    {
        return $this->run($data);
    }

    abstract public function run($data);
}