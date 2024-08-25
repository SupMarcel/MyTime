<?php

namespace App\Model;

use Nette\Database\Explorer;
use Nette\Database\Table\Selection;
use Nette\Database\Table\ActiveRow;
use Nette\SmartObject;

abstract class BaseModel
{
    use SmartObject;

    protected Explorer $database;

    public function __construct(Explorer $database)
    {
        $this->database = $database;
    }

    // ====================================================================
    // ZÁKLADNÍ OPERACE S DATABÁZÍ
    // ====================================================================

    /**
     * Získá všechny záznamy z tabulky.
     * @return Selection Výběr všech záznamů.
     */
    public function getAll(): Selection
    {
        return $this->database->table(static::TABLE_NAME);
    }

    /**
     * Získá záznam podle ID.
     * @param int $id ID záznamu.
     * @return ActiveRow|null Záznam nebo null, pokud neexistuje.
     */
    public function getById(int $id): ?ActiveRow
    {
        return $this->database->table(static::TABLE_NAME)->get($id);
    }

    /**
     * Vyhledá záznam podle zadaných kritérií.
     * @param array $criteria Kritéria pro vyhledání.
     * @return ActiveRow|null První nalezený záznam nebo null.
     */
    public function findBy(array $criteria): ?ActiveRow
    {
        return $this->database->table(static::TABLE_NAME)->where($criteria)->fetch();
    }

    // ====================================================================
    // VLOŽENÍ ZÁZNAMŮ DO TABULKY
    // ====================================================================

    /**
     * Přidá nový záznam do tabulky.
     * @param array $data Data pro nový záznam.
     * @return ActiveRow Vložený záznam.
     */
    public function add(array $data): ActiveRow
    {
        return $this->database->table(static::TABLE_NAME)->insert($data);
    }

    /**
     * Přidá nový záznam a vrátí jeho ID.
     * @param array $data Data pro nový záznam.
     * @return int ID vloženého záznamu.
     */
    public function addAndReturnId(array $data): int
    {
        return $this->database->table(static::TABLE_NAME)->insert($data)->getPrimary();
    }

    /**
     * Přidá více záznamů a vrátí pole obsahující jejich ID.
     * @param array $rows Pole dat pro více záznamů.
     * @return array Pole ID vložených záznamů.
     */
    public function addMultipleAndReturnIds(array $rows): array
    {
        $ids = [];
        foreach ($rows as $data) {
            $ids[] = $this->addAndReturnId($data);
        }
        return $ids;
    }

    /**
     * Přidá více záznamů a nevrací žádné hodnoty.
     * @param array $rows Pole dat pro více záznamů.
     */
    public function addMultipleWithoutReturn(array $rows): void
    {
        foreach ($rows as $data) {
            $this->add($data);
        }
    }

    // ====================================================================
    // AKTUALIZACE ZÁZNAMŮ
    // ====================================================================

    /**
     * Aktualizuje záznam podle ID.
     * @param int $id ID záznamu, který se má aktualizovat.
     * @param array $data Nová data pro aktualizaci.
     */
    public function update(int $id, array $data): void
    {
        $this->database->table(static::TABLE_NAME)
            ->where(static::COLUMN_ID, $id)
            ->update($data);
    }

    // ====================================================================
    // VYMAZÁNÍ ZÁZNAMŮ
    // ====================================================================

    /**
     * Vymaže záznam podle ID.
     * @param int $id ID záznamu, který se má vymazat.
     */
    public function delete(int $id): void
    {
        $this->database->table(static::TABLE_NAME)
            ->where(static::COLUMN_ID, $id)
            ->delete();
    }

    /**
     * Vymaže více záznamů podle pole ID.
     * @param array $ids Pole ID záznamů, které se mají vymazat.
     */
    public function deleteMultiple(array $ids): void
    {
        $this->database->table(static::TABLE_NAME)
            ->where(static::COLUMN_ID, $ids)
            ->delete();
    }

    /**
     * Vymaže všechny záznamy v tabulce.
     */
    public function deleteAll(): void
    {
        $this->database->table(static::TABLE_NAME)->delete();

        // Resetování autoincrementu
        $this->database->query('ALTER TABLE ' . static::TABLE_NAME . ' AUTO_INCREMENT = 1');
    }

    // ====================================================================
    // DALŠÍ UŽITEČNÉ METODY
    // ====================================================================

    /**
     * Zjištění, zda je tabulka prázdná.
     * @return bool True, pokud je tabulka prázdná, jinak false.
     */
    public function isEmpty(): bool
    {
        return !$this->database->table(static::TABLE_NAME)->count('*');
    }

    /**
     * Získá ID příštího auto-incrementu v tabulce.
     * @return int Příští auto-increment ID.
     */
    public function getNextAutoIncrementId(): int
    {
        $tableName = static::TABLE_NAME;
        $result = $this->database->query("SHOW TABLE STATUS LIKE '$tableName'")->fetch();
        return (int)$result['Auto_increment'];
    }

    /**
     * Přidá více záznamů a vrátí pole objektů ActiveRow.
     * @param array $rows Pole dat pro více záznamů.
     * @return array Pole objektů ActiveRow reprezentujících vložené záznamy.
     */
    public function addMultipleAndReturnObjects(array $rows): array
    {
        $objects = [];
        foreach ($rows as $data) {
            $objects[] = $this->add($data);
        }
        return $objects;
    }
}
