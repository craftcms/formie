<?php
namespace verbb\formie\records;

use craft\db\ActiveRecord;

class Row extends ActiveRecord
{
    // Static Methods
    // =========================================================================

    public static function tableName(): string
    {
        return '{{%formie_rows}}';
    }
}
