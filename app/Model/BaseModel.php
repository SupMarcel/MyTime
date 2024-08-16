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

    // Získání všech záznamů z tabulky
    public function getAll(): Selection
    {
        return $this->database->table(static::TABLE_NAME);
    }

    // Získání záznamu podle ID
    public function getById(int $id): ?ActiveRow
    {
        return $this->database->table(static::TABLE_NAME)->get($id);
    }

    // Vyhledání záznamu podle kritérií
    public function findBy(array $criteria): ?ActiveRow
    {
        return $this->database->table(static::TABLE_NAME)->where($criteria)->fetch();
    }

    // Přidání nového záznamu
    public function add(array $data): ActiveRow
    {
        return $this->database->table(static::TABLE_NAME)->insert($data);
    }

    // Aktualizace záznamu podle ID
    public function update(int $id, array $data): void
    {
        $this->database->table(static::TABLE_NAME)
            ->where(static::COLUMN_ID, $id)
            ->update($data);
    }

    // Vymazání záznamu podle ID
    public function delete(int $id): void
    {
        $this->database->table(static::TABLE_NAME)
            ->where(static::COLUMN_ID, $id)
            ->delete();
    }

    // Zjištění, zda je tabulka prázdná
    public function isEmpty(): bool
    {
        return !$this->database->table(static::TABLE_NAME)->count('*');
    }
}


