<?php

namespace App\Model;

use Nette\Database\Explorer;
use Nette\Database\Table\Selection;
use Nette\Database\Table\ActiveRow;

abstract class BaseModel
{
    protected Explorer $database;

    public function __construct(Explorer $database)
    {
        $this->database = $database;
    }

    public function getAll(): Selection
    {
        return $this->database->table(static::TABLE_NAME);
    }

    public function getById(int $id): ?ActiveRow
    {
        return $this->database->table(static::TABLE_NAME)->get($id);
    }
}

