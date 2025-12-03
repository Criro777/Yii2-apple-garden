<?php

namespace backend\tests\unit;

use backend\models\Apple;
use Codeception\Test\Unit;
use Exception;

class AppleExceptionTest extends Unit
{
    public function setUp(): void
    {
        parent::setUp();

        $this->apple =  new Apple([
            'color' => 'зеленое',
            'date_appear' => time(),
            'status' => Apple::STATUS_ON_TREE,
        ]);

    }

    /**
     * Тест исключения при попытке съесть яблоко на дереве
     *
     * @throws \yii\db\Exception
     */
    public function testExceptionOnEatAppleOnTree()
    {
        $this->apple->status = Apple::STATUS_ON_TREE;
        $this->apple->save(false);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Съесть нельзя, яблоко на дереве');

        $this->apple->eat(10);
    }

    /**
     * Тест исключени при попытке съесть гнилое яблоко
     *
     * @throws \yii\db\Exception
     */
    public function testExceptionOnEatRottenApple()
    {
        $this->apple->status = Apple::STATUS_ROTTEN;
        $this->apple->date_fall = time() - (6 * 3600);
        $this->apple->save(false);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Съесть нельзя, яблоко испортилось');

        $this->apple->eat(10);
    }

    /**
     * Тест исключения при падении гнилого яблока
     *
     * @throws \yii\db\Exception
     */
    public function testExceptionOnFallRottenApple()
    {
        $this->apple->status = Apple::STATUS_ROTTEN;
        $this->apple->date_fall = time() - (6 * 3600);
        $this->apple->save(false);
        
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Яблоко уже на земле или испорчено');
        
        $this->apple->fallToGround();
    }

    /**
     * Тест исключения при падении упавшего яблока
     *
     * @throws \yii\db\Exception
     */
    public function testExceptionOnAlreadyFallenApple()
    {

        $this->apple->status = Apple::STATUS_ON_GROUND;
        $this->apple->date_fall = time() - 3600;
        $this->apple->save(false);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Яблоко уже на земле или испорчено');
        
        $this->apple->fallToGround();
    }

    /**
     * Тест исключения при некорректном проценте съедания
     *
     * @throws \yii\db\Exception
     */
    public function testExceptionOnEatInvalidPercent()
    {
        $this->apple->status = Apple::STATUS_ON_GROUND;
        $this->apple->save(false);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Некорректный процент');

        $this->apple->eat(120);

    }

    /**
     * Тест исключения при съедании больше 100% в сумме
     *
     * @throws \yii\db\Exception
     */
    public function testExceptionOnEatMoreThan100Percent()
    {
        $this->apple->status = Apple::STATUS_ON_GROUND;
        $this->apple->eaten_percent = 80;
        $this->apple->save(false);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Нельзя съесть больше 100%');


        $this->apple->eat(30); // 80 + 30 = 110 > 100
    }
}