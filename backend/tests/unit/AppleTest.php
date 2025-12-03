<?php

namespace backend\tests\unit;

use backend\models\Apple;
use Codeception\Test\Unit;
use yii\db\Exception;

class AppleTest extends Unit
{
    public function setUp(): void
    {
        parent::setUp();

        $this->apple =  new Apple([
            'color' => 'красное',
            'date_appear' => time(),
            'status' => Apple::STATUS_ON_TREE,
        ]);

    }

    /**
     * Тест создания нового яблока
     */
    public function testCreateApple()
    {
        $this->assertTrue($this->apple->save());
        $this->assertEquals('красное', $this->apple->color);
        $this->assertEquals(Apple::STATUS_ON_TREE, $this->apple->status);
        $this->assertEquals(0, $this->apple->eaten_percent);
    }

    /**
     * Тест падения яблока с дерева
     */
    public function testFallToGround()
    {
        $this->assertTrue($this->apple->fallToGround());
        $this->assertEquals(Apple::STATUS_ON_GROUND, $this->apple->status);
        $this->assertNotNull($this->apple->date_fall);
    }

    /**
     * Тест съедания части яблока
     */
    public function testEatPartOfApple()
    {
        $this->apple->status = Apple::STATUS_ON_GROUND;
        $this->assertTrue($this->apple->save());

        $this->assertTrue($this->apple->save());

        // Съедаем 30%
        $this->assertTrue($this->apple->eat(30));
        $this->assertEquals(30, $this->apple->eaten_percent);

        // Съедаем еще 20%
        $this->assertTrue($this->apple->eat(20));
        $this->assertEquals(50, $this->apple->eaten_percent);
    }

    /**
     * Тест съедания 100% яблока
     * @throws Exception
     */
    public function testEatWholeApple()
    {
        $this->apple->status = Apple::STATUS_ON_GROUND;
        $this->apple->eaten_percent = 80;
        $this->assertTrue($this->apple->save());
        $id = $this->apple->id;

        // Съедаем оставшиеся 20% - должно вернуть true и удалить яблоко
        $result = $this->apple->eat(20);
        $this->assertTrue($result);

        // Проверяем, что яблоко удалено из БД
        $this->assertNull(Apple::findOne($id));
    }

    /**
     * Тест получения размера оставшейся части
     */
    public function testGetSize()
    {
        $this->apple->eaten_percent = 0;
        $this->assertEquals(1, $this->apple->getSize());

        $this->apple->eaten_percent = 50;
        $this->assertEquals(0.5, $this->apple->getSize());

        $this->apple->eaten_percent = 75;
        $this->assertEquals(0.25, $this->apple->getSize());

        $this->apple->eaten_percent = 100;
        $this->assertEquals(0, $this->apple->getSize());
    }

    /**
     * Тест получения текста статуса
     */
    public function testGetStatusText()
    {
        $this->apple->status = Apple::STATUS_ON_TREE;
        $this->assertEquals('На дереве', $this->apple->getStatusText());

        $this->apple->status = Apple::STATUS_ON_GROUND;
        $this->assertEquals('Упало', $this->apple->getStatusText());

        $this->apple->status = Apple::STATUS_ROTTEN;
        $this->assertEquals('Гнилое', $this->apple->getStatusText());

        $this->apple->status = 999; // Неизвестный статус
        $this->assertEquals('Неизвестно', $this->apple->getStatusText());
    }

    /**
     * Тест получения иконки статуса
     */
    public function testGetStatusIcon()
    {
        $this->apple->status = Apple::STATUS_ON_TREE;
        $this->assertEquals('🌳', $this->apple->getStatusIcon());

        $this->apple->status = Apple::STATUS_ON_GROUND;
        $this->assertEquals('🍎', $this->apple->getStatusIcon());

        $this->apple->status = Apple::STATUS_ROTTEN;
        $this->assertEquals('🤢', $this->apple->getStatusIcon());

        $this->apple->status = 999; // Неизвестный статус
        $this->assertEquals('❓', $this->apple->getStatusIcon());
    }

    /**
     * Тест получения CSS класса статуса
     */
    public function testGetStatusClass()
    {
        $this->apple->status = Apple::STATUS_ON_TREE;
        $this->assertEquals('apple-on-tree', $this->apple->getStatusClass());

        $this->apple->status = Apple::STATUS_ON_GROUND;
        $this->assertEquals('apple-on-ground', $this->apple->getStatusClass());

        $this->apple->status = Apple::STATUS_ROTTEN;
        $this->assertEquals('apple-rotten', $this->apple->getStatusClass());

        $this->apple->status = 999; // Неизвестный статус
        $this->assertEquals('', $this->apple->getStatusClass());
    }

    /**
     * Тест получения HEX цвета
     */
    public function testGetColorHex()
    {
        $this->apple->color = 'желтое';
        $this->assertEquals('#FFFF00', $this->apple->getColorHex());

        $this->apple->color = 'розовое';
        $this->assertEquals('#FFC0CB', $this->apple->getColorHex());

        $this->apple->color = 'неизвестный цвет';
        $this->assertEquals('#FFFFFF', $this->apple->getColorHex());
    }

    /**
     * Тест получения времени до гниения
     */
    public function testGetTimeToRot()
    {
        // Яблоко на дереве
        $this->apple->status = Apple::STATUS_ON_TREE;
        $this->assertNull($this->apple->getTimeToRot());

        // Яблоко на земле, только что упало
        $this->apple->status = Apple::STATUS_ON_GROUND;
        $this->apple->date_fall = time();

        $timeToRot = $this->apple->getTimeToRot();

        $this->assertNotNull($timeToRot);
        $this->assertGreaterThan(0, $timeToRot);
        $this->assertLessThanOrEqual(Apple::ROTTEN_SECONDS, $timeToRot);

        // Яблоко гнилое
        $this->apple->status = Apple::STATUS_ROTTEN;
        $this->assertNull($this->apple->getTimeToRot());
    }

    /**
     * Тест получения текста времени до гниения
     */
    public function testGetTimeToRotText()
    {
        // Яблоко на дереве
        $this->apple->status = Apple::STATUS_ON_TREE;
        $this->assertEquals('', $this->apple->getTimeToRotText());

        // Яблоко на земле, упало 30 минут назад
        $this->apple->status = Apple::STATUS_ON_GROUND;
        $this->apple->date_fall = time() - 1800; // 30 минут назад

        $text = $this->apple->getTimeToRotText();
        $this->assertStringContainsString('ч', $text);
        $this->assertStringContainsString('м', $text);
        $this->assertStringContainsString('до гниения', $text);

        // Яблоко испортилось
        $this->apple->date_fall = time() - Apple::ROTTEN_SECONDS - 100;
        $this->assertEquals('Испортилось', $this->apple->getTimeToRotText());
    }

    /**
     * Тест массовой генерации яблок
     */
    public function testGenerateMultiple()
    {
        $initialCount = Apple::find()->count();
        $countToGenerate = 5;

        $generated = Apple::generateMultiple($countToGenerate);
        $this->assertEquals($countToGenerate, $generated);

        $newCount = Apple::find()->count();
        $this->assertEquals($initialCount + $countToGenerate, $newCount);
    }

}