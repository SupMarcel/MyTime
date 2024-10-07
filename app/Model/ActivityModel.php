<?php

namespace App\Model;

use Nette\Database\Explorer;
use Nette\Database\Table\Selection;

class ActivityModel extends BaseModel
{
    // Definice názvů tabulky a sloupců
    const TABLE_NAME = 'activities';
    const COLUMN_ID = 'id';
    const COLUMN_NAME = 'activity_name';
    const COLUMN_DESCRIPTION = 'activity_description';
    const COLUMN_PRICE = 'price_per_time_slot';
    const COLUMN_CATEGORY_ID = 'category_id';
    const COLUMN_WORKER_ID = 'worker_id';

    public function __construct(Explorer $database)
    {
        parent::__construct($database);
    }

    /**
     * Získá všechny aktivity s přiřazenými kategoriemi a pracovníky.
     * @return \Nette\Database\Table\Selection
     */
    public function getAllWithCategoriesAndWorkers(): Selection
    {
        return $this->database->table(self::TABLE_NAME)
            ->select('activities.*, categories.category_name AS category, users.username AS worker')
            ->joinWhere('categories', 'categories.id = activities.category_id')
            ->joinWhere('users', 'users.id = activities.worker_id');
    }

    /**
     * Získá aktivity podle ID kategorie.
     * @param int $categoryId ID kategorie.
     * @return \Nette\Database\Table\Selection
     */
    public function getByCategoryId(int $categoryId): Selection
    {
        return $this->database->table(self::TABLE_NAME)
            ->where(self::COLUMN_CATEGORY_ID, $categoryId);
    }

    /**
     * Získá aktivity podle ID pracovníka.
     * @param int $workerId ID pracovníka (user_id).
     * @return \Nette\Database\Table\Selection
     */
    public function getByWorkerId(int $workerId): Selection
    {
        return $this->database->table(self::TABLE_NAME)
            ->where(self::COLUMN_WORKER_ID, $workerId);
    }

    /**
     * Přidá novou aktivitu.
     * @param string $name Název aktivity.
     * @param string|null $description Popis aktivity (volitelný).
     * @param float|null $price Cena za časový slot (volitelná).
     * @param int|null $categoryId ID kategorie (volitelný).
     * @param int $workerId ID pracovníka.
     * @return \Nette\Database\Table\ActiveRow|false Nově vložená aktivita nebo false v případě chyby.
     */
    public function addActivity(string $name, ?string $description, ?float $price, ?int $categoryId, int $workerId)
    {
        return $this->database->table(self::TABLE_NAME)->insert([
            self::COLUMN_NAME => $name,
            self::COLUMN_DESCRIPTION => $description,
            self::COLUMN_PRICE => $price,
            self::COLUMN_CATEGORY_ID => $categoryId,
            self::COLUMN_WORKER_ID => $workerId,
        ]);
    }

    /**
     * Aktualizuje existující aktivitu.
     * @param int $id ID aktivity.
     * @param array $data Data pro aktualizaci.
     * @return void
     */
    public function updateActivity(int $id, array $data): void
    {
        $this->database->table(self::TABLE_NAME)
            ->where(self::COLUMN_ID, $id)
            ->update($data);
    }

    /**
     * Odstraní aktivitu podle ID.
     * @param int $id ID aktivity.
     * @return void
     */
    public function deleteActivity(int $id): void
    {
        $this->database->table(self::TABLE_NAME)
            ->where(self::COLUMN_ID, $id)
            ->delete();
    }
}
