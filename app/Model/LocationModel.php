<?php

namespace App\Model;

use Nette\Database\Table\ActiveRow;
use Nette\Database\Table\Selection;

class LocationModel extends BaseModel
{
    // Konstanty pro názvy tabulek
    public const TABLE_NAME = 'locations';

    // Konstanty pro názvy sloupců v tabulce 'locations'
    public const COLUMN_ID = 'id';
    public const COLUMN_NAME = 'name';
    public const COLUMN_ADDRESS_ID = 'addressId';
    public const COLUMN_IMAGE = 'image';
    public const COLUMN_DESCRIPTION = 'description';
    public const COLUMN_BANNED = 'banned';
    public const COLUMN_CREATED_AT = 'created_at';
    public const COLUMN_UPDATED_AT = 'updated_at';

    // Konstanty pro defaultní hodnoty
    public const DEFAULT_LOCATION = 1;

    public function addLocation(string $name, int $addressId, ?string $image = null, ?string $description = null, bool $banned = false): int
    {
        return $this->add([
            self::COLUMN_NAME => $name,
            self::COLUMN_ADDRESS_ID => $addressId,
            self::COLUMN_IMAGE => $image,
            self::COLUMN_DESCRIPTION => $description,
            self::COLUMN_BANNED => $banned,
        ])->getPrimary();
    }

    /**
     * Získá lokaci podle ID.
     *
     * @param int $locationId
     * @return ?Nette\Database\Table\ActiveRow
     */
    public function getLocationById(int $locationId): ?ActiveRow
    {
        return $this->getById($locationId);
    }

    public function getAllLocations(): Selection
    {
        return $this->getAll()->order(self::COLUMN_NAME);
    }

    public function updateLocation(int $locationId, array $data): void
    {
        $this->update($locationId, $data);
    }

    public function deleteLocation(int $locationId): void
    {
        $this->delete($locationId);
    }

    public function banLocation(int $locationId): void
    {
        $this->updateLocation($locationId, [self::COLUMN_BANNED => true]);
    }

    public function unbanLocation(int $locationId): void
    {
        $this->updateLocation($locationId, [self::COLUMN_BANNED => false]);
    }

    public function getUnbannedLocations(): Selection
    {
        return $this->getAll()
            ->where(self::COLUMN_BANNED, false)
            ->order(self::COLUMN_NAME);
    }

    public function getBannedLocations(): Selection
    {
        return $this->getAll()
            ->where(self::COLUMN_BANNED, true)
            ->order(self::COLUMN_NAME);
    }
}

