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

    // Přidání nového záznamu a vrácení jeho ID
    public function addAndReturnId(array $data): int
    {
        return $this->database->table(static::TABLE_NAME)->insert($data)->getPrimary();
    }

    // Přidání více záznamů a vrácení pole obsahujícího jejich ID
    public function addMultipleAndReturnIds(array $rows): array
    {
        $ids = [];
        foreach ($rows as $data) {
            $ids[] = $this->addAndReturnId($data);
        }
        return $ids;
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

    // Vymazání více záznamů podle pole ID
    public function deleteMultiple(array $ids): void
    {
        $this->database->table(static::TABLE_NAME)
            ->where(static::COLUMN_ID, $ids)
            ->delete();
    }

    // Vymazání všech záznamů v tabulce
    public function deleteAll(): void
    {
        $this->database->table(static::TABLE_NAME)->delete();
    }

    // Zjištění, zda je tabulka prázdná
    public function isEmpty(): bool
    {
        return !$this->database->table(static::TABLE_NAME)->count('*');
    }
}



