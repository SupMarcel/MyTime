<?php

namespace App\Model;

use Nette\Database\Explorer;

class StatusModel extends BaseModel
{
    // Konstanty pro názvy sloupců
    const TABLE_NAME = 'statuses';
    const COLUMN_ID = 'id';
    const COLUMN_STATUS_NAME = 'status_name';
    const COLUMN_STATUS_COLOR = 'status_color';
    const COLUMN_COLOR_CSS = 'color_css';

    // Definice statusů jako konstant
    const FREE = 'free';
    const ORDERED = 'ordered';
    const UNAVAILABLE = 'unavailable';
    const MIX = 'mix';
    
     // Konstanty pro ID statusů
    const FREEID = 1;
    const ORDEREDID = 2;
    const UNAVAILABLEID = 3;
    const MIXID = 4;
    
    
    public function __construct(Explorer $database)
    {
        parent::__construct($database);
    }

    /**
     * Přidá nový status do tabulky.
     * 
     * @param string $statusName
     * @param string $statusColor
     * @param string $colorCss
     * @return int ID vloženého statusu.
     */
    public function addStatus(string $statusName, string $statusColor, string $colorCss): int
    {
        return $this->addAndReturnId([
            self::COLUMN_STATUS_NAME => $statusName,
            self::COLUMN_STATUS_COLOR => $statusColor,
            self::COLUMN_COLOR_CSS => $colorCss,
        ]);
    }

    /**
     * Odebere status podle ID.
     * 
     * @param int $id
     */
    public function deleteStatus(int $id): void
    {
        $this->delete($id);
    }

    /**
     * Získá ID statusu podle názvu statusu.
     * 
     * @param string $statusName
     * @return int|null ID statusu nebo null, pokud status neexistuje.
     */
    public function getStatusIdByName(string $statusName): ?int
    {
        $status = $this->findBy([self::COLUMN_STATUS_NAME => $statusName]);
        return $status ? $status->{self::COLUMN_ID} : null;
    }

    /**
     * Získá ID statusu s názvem 'free'.
     * 
     * @return int|null ID statusu nebo null, pokud status neexistuje.
     */
    public function getFreeStatusId(): ?int
    {
        return $this->getStatusIdByName(self::FREE);
    }

    /**
     * Získá ID statusu s názvem 'ordered'.
     * 
     * @return int|null ID statusu nebo null, pokud status neexistuje.
     */
    public function getOrderedStatusId(): ?int
    {
        return $this->getStatusIdByName(self::ORDERED);
    }
}


