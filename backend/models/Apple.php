<?php

namespace backend\models;

use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;

/**
 * @property int $id
 * @property string $color
 * @property int $date_appear
 * @property int|null $date_fall
 * @property int $status
 * @property float $eaten_percent
 * @property int $created_at
 * @property int $updated_at
 */
class Apple extends ActiveRecord {
    public static function tableName(): string
    {
        return '{{%apple}}';
    }

    public function behaviors(): array
    {
        return [
            TimestampBehavior::class
        ];
    }

}