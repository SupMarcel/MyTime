<?php

namespace App\Model;

use Nette\Database\Table\ActiveRow;

class AddressModel extends BaseModel
{
    const TABLE_NAME = 'addresses';
    const COLUMN_ID = 'id';
    const COLUMN_STREET = 'street';
    const COLUMN_NUMBER_OF_STREET = 'number_of_street';
    const COLUMN_CITY = 'city';
    const COLUMN_ZIP_CODE = 'zip_code';
    const COLUMN_REGION = 'region';
    const COLUMN_STATE = 'state';
    const COLUMN_LONGITUDE = 'longitude';
    const COLUMN_LATITUDE = 'latitude';

    // Všechny funkce jsou děděny z BaseModelu

    // Například: Přidání adresy
    public function addAddress(array $data): ActiveRow
    {
        return $this->add($data);
    }

    // Například: Aktualizace adresy
    public function updateAddress(int $addressId, array $data): void
    {
        $this->update($addressId, $data);
    }

    // Například: Vymazání adresy
    public function deleteAddress(int $addressId): void
    {
        $this->delete($addressId);
    }
}

