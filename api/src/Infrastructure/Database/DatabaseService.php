<?php

namespace App\Infrastructure\Database;

interface DatabaseService
{
    public function getConnection();

    public function fetchUuid(): string;
}