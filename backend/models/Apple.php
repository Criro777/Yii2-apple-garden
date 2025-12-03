<?php

namespace backend\models;

use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use yii\db\Exception;

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
    const STATUS_ON_TREE = 1;
    const STATUS_ON_GROUND = 2;
    const STATUS_ROTTEN = 3;

    const THIRTY_DAYS = 30 * 24 * 3600;

    private const COLOR_MAP = [
        'зеленое' => '#7CFC00',
        'красное' => '#FF0000',
        'желтое' => '#FFFF00',
        'зелено-красное' => 'linear-gradient(45deg, #7CFC00 50%, #FF0000 50%)',
        'желто-красное' => 'linear-gradient(45deg, #FFFF00 50%, #FF0000 50%)',
        'розовое' => '#FFC0CB',
        'оранжевое' => '#FFA500'
    ];


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

    public function rules(): array
    {
        return [
            [['color', 'date_appear', 'status'], 'required'],
            [['date_fall', 'date_appear'], 'integer'],
            [['eaten_percent'], 'number', 'min' => 0, 'max' => 100],
            [['color'], 'string', 'max' => 50],
            ['status', 'default', 'value' => self::STATUS_ON_TREE],
            ['eaten_percent', 'default', 'value' => 0]
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'id' => 'ID',
            'color' => 'Цвет',
            'date_appear' => 'Дата появления',
            'date_fall' => 'Дата падения',
            'status' => 'Статус',
            'eaten_percent' => 'Съедено (%)',
        ];
    }

    /**
     * Упасть на землю
     * @throws Exception
     */
    public function fallToGround(): bool
    {
        if ($this->status !== self::STATUS_ON_TREE) {
            throw new Exception('Яблоко уже на земле или испорчено');
        }

        $this->status = self::STATUS_ON_GROUND;
        $this->date_fall = time();

        return $this->save(false);
    }

    /**
     * Массовое создание случайных яблок
     * @throws Exception
     */
    public static function generateMultiple(int $count): int
    {
        $generated = 0;
        $colors = array_keys(self::COLOR_MAP);

        $now = time();
        $thirtyDaysAgo = $now - self::THIRTY_DAYS;

        for ($i = 0; $i < $count; $i++) {
            $apple = new self();
            $apple->color = $colors[rand(0, count($colors) - 1)];
            $apple->date_appear = rand($thirtyDaysAgo, $now); // генерим случайную дату в диапазоне 30 дней назад от текущей
            $apple->status = self::STATUS_ON_TREE;
            $apple->eaten_percent = 0;

            if ($apple->save()) {
                $generated++;
            }
        }

        return $generated;
    }
}