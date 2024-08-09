<?php

namespace App\Model;

class LanguageModel extends BaseModel
{
    const TABLE_NAME = 'languages';
    const COLUMN_ID = 'id';
    const COLUMN_CODE = 'code';
    const COLUMN_NAME = 'name';

    public function getLanguages(): Selection
    {
        return $this->database->table(self::TABLE_NAME);
    }
}

