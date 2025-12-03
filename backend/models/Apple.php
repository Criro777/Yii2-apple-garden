<?php

namespace backend\models;

use Throwable;
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

    const ROTTEN_HOURS = 5;
    const ROTTEN_SECONDS = self::ROTTEN_HOURS * 3600;

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
     * Проверяет, не испортилось ли яблоко
     * @throws Exception
     */
    private function updateRottenStatus(): void
    {
        if ($this->status === self::STATUS_ON_GROUND && $this->date_fall && (time() - $this->date_fall) > self::ROTTEN_SECONDS) {
            $this->status = self::STATUS_ROTTEN;
            $this->save(false);
        }
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
     * Съесть часть яблока
     * @param float $percent Процент откушенной части
     * @throws Exception
     * @throws Throwable
     */
    public function eat(float $percent): bool|int
    {
        $this->updateRottenStatus();

        if ($this->status === self::STATUS_ON_TREE) {
            throw new Exception('Съесть нельзя, яблоко на дереве');
        }

        if ($this->status === self::STATUS_ROTTEN) {
            throw new Exception('Съесть нельзя, яблоко испортилось');
        }

        if ($percent <= 0 || $percent > 100) {
            throw new Exception('Некорректный процент');
        }

        $newPercent = $this->eaten_percent + $percent;

        if ($newPercent > 100) {
            throw new Exception('Нельзя съесть больше 100%');
        }

        $this->eaten_percent = $newPercent;

        if ($this->eaten_percent == 100) {
            return $this->delete() !== false;
        }

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