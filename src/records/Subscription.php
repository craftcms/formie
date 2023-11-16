<?php
namespace verbb\formie\records;

use craft\db\ActiveRecord;
use craft\records\Field;

use yii\db\ActiveQueryInterface;

class Subscription extends ActiveRecord
{
    // Static Methods
    // =========================================================================

    public static function tableName(): string
    {
        return '{{%formie_payments_subscriptions}}';
    }


    // Public Methods
    // =========================================================================

    public function getIntegration(): ActiveQueryInterface
    {
        return $this->hasOne(Integration::class, ['id' => 'integrationId']);
    }

    public function getSubmission(): ActiveQueryInterface
    {
        return $this->hasOne(Submission::class, ['id' => 'submissionId']);
    }

    public function getField(): ActiveQueryInterface
    {
        return $this->hasOne(Field::class, ['id' => 'fieldId']);
    }

    public function getPlan(): ActiveQueryInterface
    {
        return $this->hasOne(Plan::class, ['id' => 'planId']);
    }
}
