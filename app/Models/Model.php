<?php

namespace App\Models;

use App\Core\Database;
use PDO;

abstract class Model
{
    protected PDO $connection;

    public function __construct()
    {
        $this->connection = Database::connection();
    }
}

