<?php

namespace App\Model;

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

    public function addAddress(array $data): ActiveRow
    {
        return $this->database->table(self::TABLE_NAME)->insert($data);
    }

    public function findBy(array $criteria): ?ActiveRow
    {
        return $this->database->table(self::TABLE_NAME)->where($criteria)->fetch();
    }
}
